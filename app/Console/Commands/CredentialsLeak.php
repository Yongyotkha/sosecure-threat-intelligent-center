<?php

namespace App\Console\Commands;

use App\CredentialLeakRef;
use App\DataLeakFeed;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use App\Mail\CompromisedMail;


class CredentialsLeak extends Command
{
    protected $signature = 'app:credentials-leak {--domain=} {--limit=100}';
    protected $description = 'Fetch credentials leak by ACTIVE sites and their domains; save to data_leak_feed + credential_leak_ref';

    public function handle()
    {
        Log::info('CredentialsLeak started...');

        $apiKey = env('LEAKCHECK_API_KEY');
        if (!$apiKey) {
            $this->error('Missing LEAKCHECK_API_KEY in .env');
            return 1;
        }

        // 1) Active sites
        $activeSites = $this->getActiveSiteIds();
        if ($activeSites->isEmpty()) {
            $this->warn('No ACTIVE sites found.');
            return 0;
        }

        // 2) Domains by site
        $domains = $this->getDomainsBySites($activeSites, $this->option('domain'));
        if ($domains->isEmpty()) {
            $this->warn('No domains found for active sites.');
            return 0;
        }

        $http = new Client([
            'base_uri' => 'https://leakcheck.io',
            'timeout'  => 20,
            'headers'  => [
                'Accept'    => 'application/json',
                'X-API-Key' => $apiKey,
            ],
        ]);

        $domainsBySite = $domains->groupBy('site_id');
        $totalSaved = 0;

        foreach ($domainsBySite as $siteId => $rows) {

            $hasPermission = DB::table('site_menu_permission')
                ->where('site_id', $siteId)
                ->where('menu_id', 6)
                ->whereNull('deleted_at') 
                ->exists();

            if (!$hasPermission) {
                $this->warn("🚫 Skip site_id={$siteId} (no permission for data leak)");
                // Log::info("🚫 Skip site_id={$siteId} (no permission for data leak)");
                continue;
            }

            $this->info("== Processing site_id={$siteId} with " . count($rows) . " domains ==");
            $siteItems = [];

            foreach ($rows as $r) {
                $domain = (string) $r->domain;
                if ($domain === '') continue;

                $this->info("  - Query domain: {$domain}");

                $json = $this->queryLeakcheck($http, $domain, 'domain');
                if (!$json || empty($json['found']) || empty($json['result'])) {
                    usleep(350000);
                    continue;
                }

                foreach ($json['result'] as $entry) {
                    $item = $this->saveOne((int)$siteId, $domain, $entry);
                    if (is_array($item)) {
                        $siteItems[] = (object) $item;
                        $totalSaved++;
                    }
                }

                usleep(350000); // throttle
            }

            if (!empty($siteItems)) {
                $recipients = $this->getAlertEmailsBySite((int)$siteId);

                if (!empty($recipients)) {
                    try {
                        Mail::to($recipients)
                            ->send(new CompromisedMail($siteItems, 'credential_leak'));

                        $this->info("📧 Sent email to site {$siteId} (" . count($siteItems) . " items)");
                    } catch (\Throwable $e) {
                        $this->error("Mail failed for site {$siteId}: " . $e->getMessage());
                    }
                } else {
                    $this->warn("No recipients configured for site {$siteId}");
                }
            } else {
                $this->info("No new items for site {$siteId}");
            }
        }

        $this->info("✅ Done. Total saved items: {$totalSaved}");
        Log::info('CredentialsLeak completed.');
        return 0;
    }


    /**
     * ดึง site_id จากตาราง site ที่ active = 1 และ deleted_at IS NULL
     * รองรับ prefix อัตโนมัติ (เช่น fx_site)
     */
    protected function getActiveSiteIds()
    {
        if (!Schema::hasTable('site')) {
            $this->error('❌ Table `site` not found.');
            return collect();
        }

        $q = DB::table('site')->select('id')->where('active', 1);

        if (Schema::hasColumn('site', 'deleted_at')) {
            $q->whereNull('deleted_at');
        }

        return $q->pluck('id');
    }

    /**
     * ดึงโดเมนจากตารางโดเมน ด้วย site_id ที่กำหนด
     * - รองรับหลายชื่อ table: fx_site_domain, site_domain, domain, fx_domain
     * - ต้องมีคอลัมน์ site_id และคอลัมน์โดเมนสักตัวหนึ่งในชุด candidates
     * - ถ้า --domain= ถูกส่งมา จะ filter ด้วย like
     *
     * คืนค่า Collection ของ (object){ site_id, domain }
     */
    protected function getDomainsBySites($siteIds, $onlyDomain = null)
    {
        $tables = ['fx_site_domain', 'site_domain', 'domain', 'fx_domain'];
        $domainCols = ['domain', 'host', 'hostname', 'name', 'url', 'website', 'site_url'];

        $out = collect();

        foreach ($tables as $tbl) {
            if (!Schema::hasTable($tbl)) continue;
            if (!Schema::hasColumn($tbl, 'site_id')) continue;

            // เลือกคอลัมน์โดเมนที่มีอยู่จริง
            $domainCol = null;
            foreach ($domainCols as $c) {
                if (Schema::hasColumn($tbl, $c)) {
                    $domainCol = $c;
                    break;
                }
            }
            if (!$domainCol) continue;

            $q = DB::table($tbl)->select('site_id', DB::raw("`{$domainCol}` as raw_domain"))
                ->whereIn('site_id', $siteIds);

            if (Schema::hasColumn($tbl, 'deleted_at')) {
                $q->whereNull('deleted_at');
            }

            if ($onlyDomain) {
                $q->where($domainCol, 'like', "%{$onlyDomain}%");
            }

            $rows = $q->get();

            foreach ($rows as $r) {
                $d = $this->extractDomain($r->raw_domain);
                if (!$d) continue;
                $out->push((object)[
                    'site_id' => (int)$r->site_id,
                    'domain'  => $d,
                ]);
            }
        }

        // unique: site_id + domain
        return $out->unique(function ($x) {
            return $x->site_id . '|' . $x->domain;
        })->values();
    }

    /**
     * LeakCheck API
     */
    protected function queryLeakcheck(Client $http, $target, $type = 'domain')
    {
        try {
            $res = $http->get("/api/v2/query/{$target}", [
                'query' => ['type' => $type],
            ]);
            if ($res->getStatusCode() !== 200) return null;
            return json_decode($res->getBody()->getContents(), true);
        } catch (\Throwable $e) {
            $this->error("API error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * บันทึก 1 รายการ ลง data_leak_feed + credential_leak_ref
     */
    protected function saveOne($siteId, $domain, array $entry)
    {
        $email      = trim((string) ($entry['email'] ?? ''));
        $password   = (string) ($entry['password'] ?? '');
        $origin     = $this->normalizeOrigin($entry['origin'] ?? '');
        $src        = $entry['source']['name'] ?? ($entry['breach_source'] ?? 'leakcheck.io');
        $breachDate = $entry['source']['breach_date'] ?? ($entry['breach_date'] ?? null);
        $fields     = $entry['fields'] ?? [];

        if (is_string($fields)) {
            $fields = array_map('trim', explode(',', $fields));
        }

        $feedlink = $this->buildFeedlink($domain, $email, $src, $breachDate);

        if (DataLeakFeed::where('feedlink', $feedlink)->exists()) {
            return false; // skip ซ้ำ
        }

        $feedtimepost = $this->parseBreachDate($breachDate);

        $feedHtml = $this->buildFeedHtml(
            $email,
            $this->maskPassword($password),
            $origin,
            $src,
            $fields
        );

        // Classify feel_type based on source
        $feelType = $this->isSurfaceWeb(strtolower($src)) ? 'surface_web' : 'darkweb';

        $feed = new DataLeakFeed();
        $feed->feedlink       = $feedlink;
        $feed->code           = $this->generateFeedCode();
        $feed->keyword        = 'Credential';
        $feed->feel_type      = $feelType;
        $feed->sourceid       = 0;
        $feed->source_name    = $src;
        $feed->feedtimepost   = $feedtimepost ?: Carbon::now();
        $feed->feedtimestamp  = Carbon::now();
        $feed->feel_type      = $feelType;
        $feed->feedcontent    = $feedHtml;
        $feed->status         = 1;
        $feed->save();

        CredentialLeakRef::create([
            'code'              => $feed->code,
            'data_leak_feed_id' => $feed->id,
            'site_id'           => $siteId,
            'keyword'           => 'Credential',
            'feel_type'         => 'credential', // Keep as credential per requirement
            'serverity'         => 'critical',
            'content'           => [
                'target'       => $domain,
                'email'        => $email,
                'password'     => $password,
                'origin'       => $origin,
                'breachSource' => $src,
                'breachDate'   => $breachDate,
                'fields'       => $fields,
            ],
            'status'           => 1,
        ]);

        $siteName = $this->getSiteNameById($siteId);

        return [
            'id'        => $feed->id,
            'feel_type' => $feelType,
            'keyword'   => 'Credential',
            'email'     => $email,
            'password'  => $password,         // raw เก็บไว้ให้ Blade
            'feedcontent' => $feedHtml,       // HTML
            'source_name' => $src,
            'created_at'  => $feed->created_at->toDateTimeString(),
            'site_name'   => $siteName ?: $domain,
        ];
    }

    /**
     * Determine if the source name belongs to Surface Web.
     * Copied logic from UpdateCredentialFeedType
     *
     * @param string $sourceName
     * @return bool
     */
    private function isSurfaceWeb($sourceName)
    {
        // 1. If it contains '.onion', it is definitely Dark Web (not Surface).
        if (strpos($sourceName, '.onion') !== false) {
            return false; 
        }

        // 2. Check explicit keywords (Social, Shopping, Etc.)
        $surfaceKeywords = [
            'facebook',
            'twitter',
            'linkedin',
            'instagram',
            'youtube',
            'gmail',
            'yahoo',
            'hotmail',
            'outlook',
            'amazon',
            'ebay',
            'paypal',
            'netflix',
            'uber',
            'grab',
            'vk.com',
            'ok.ru',
            'weibo',
            'telegram',
            'discord',
            'line',
            'whatsapp',
            'pinterest',
            'tumblr',
            'reddit',
            'tiktok',
            'snapchat',
            'twitch',
            'skype',
            'viber',
            'wechat',
            'messenger',
            // Dev / Work
            'github',
            'gitlab',
            'bitbucket',
            'stackoverflow',
            'trello',
            'slack',
            'zoom',
            'microsoft',
            'apple',
            'google',
            'dropbox',
            'adobe',
            // Shopping
            'shopee',
            'lazada',
            'alibaba',
            'aliexpress',
            // Thai specific
            'pantip',
            'sanook',
            'kapook',
            'dek-d',
            'mthai',
            'wongnai',
            'kaidee',
            'blockdit',
            'trueid',
        ];

        foreach ($surfaceKeywords as $keyword) {
            if (strpos($sourceName, $keyword) !== false) {
                return true;
            }
        }

        // 3. Logic: If it looks like a domain (has a dot and typical TLD), it's likely Surface Web.
        // e.g. "something.com", "shop.co.th"
        // But exclude simple filenames or versions if possible. 
        // Simple regex for domain-like string:
        // At least one dot, no spaces (usually), ends with 2-6 letters.
        if (preg_match('/^[a-z0-9.-]+\.[a-z]{2,10}$/i', $sourceName)) {
            return true;
        }

        // 4. Fallback: If it's just a name like "Collection #1", "AntiPublic", "Exploit.in" (no TLD),
        // we assume it's a Breach Compilation / Dark Web source.
        return false;
    }



    protected function buildFeedlink($domain, $email, $src, $breachDate)
    {
        $key = mb_strtolower($email) . '|' . mb_strtolower($src ? $src : 'leakcheck.io') . '|' . ($breachDate ? $breachDate : '');
        return 'credleak://' . mb_strtolower($domain) . '/' . md5($key);
    }

    protected function parseBreachDate($breachDate)
    {
        if (!$breachDate) return null;
        if (preg_match('/^\d{4}-\d{2}$/', $breachDate)) {
            try {
                return Carbon::parse($breachDate . '-01');
            } catch (\Throwable $e) {
                return null;
            }
        }
        try {
            return Carbon::parse($breachDate);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function extractDomain($val)
    {
        $val = trim((string) $val);
        if ($val === '') return null;

        // email → เอาหลัง @
        if (strpos($val, '@') !== false) {
            $pos = strrpos($val, '@');
            if ($pos !== false) $val = substr($val, $pos + 1);
        }

        // URL → parse host
        if (preg_match('/^[a-z]+:\/\//i', $val)) {
            $host = parse_url($val, PHP_URL_HOST);
            if ($host) $val = $host;
        }

        // ตัด www.
        $val = preg_replace('/^www\./i', '', $val);

        // validate คร่าว ๆ
        if (!preg_match('/^[A-Z0-9.-]+\.[A-Z]{2,}$/i', $val)) {
            return null;
        }

        return strtolower($val);
    }

    protected function normalizeOrigin($origin)
    {
        if (is_array($origin)) $origin = implode(', ', $origin);
        $origin = trim((string)$origin);
        return $origin !== '' ? $origin : 'leakcheck.io';
    }

    protected function maskPassword($pw)
    {
        if ($pw === null || $pw === '') return '';

        $encoding = 'UTF-8';
        $len  = mb_strlen($pw, $encoding);
        $half = (int) ceil($len / 2); // ปัดขึ้นเพื่อให้ครึ่งแรกถูกซ่อนมากกว่าเมื่อความยาวเป็นเลขคี่

        return str_repeat('*', $half) . mb_substr($pw, $half, null, $encoding);
    }


    protected function buildFeedHtml($email, $passwordMasked, $origin, $breachSource, array $fields)
    {
        $src = $breachSource ? $breachSource : 'leakcheck.io';
        $f   = implode(', ', $fields);
        return sprintf(
            '<p>Email: %s<br>Password: %s</p>',
            e($email),
            e($passwordMasked),
        );
    }
    protected function generateFeedCode(): string
    {
        // สุ่มจนกว่าจะไม่ซ้ำใน DB
        do {
            $code = (string) Str::uuid();
        } while (DataLeakFeed::where('code', $code)->exists());

        // Log::info("Generated feed code: {$code}");
        return $code;
    }
    protected function getSiteNameById(int $siteId): ?string
    {
        // ลองเดาชื่อคอลัมน์ที่มีบ่อย
        $nameCols = ['name', 'site_name', 'title'];
        if (!Schema::hasTable('site')) return null;

        foreach ($nameCols as $col) {
            if (Schema::hasColumn('site', $col)) {
                $name = DB::table('site')->where('id', $siteId)->value($col);
                if (!empty($name)) return $name;
            }
        }
        return null;
    }
    protected function getAlertEmailsBySite(int $siteId): array
    {
        $emails = DB::table('site_config_email_alert_defacement')
            ->where('site_id', $siteId)
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->all();

        return array_values(array_filter($emails, fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL)));
    }
}
