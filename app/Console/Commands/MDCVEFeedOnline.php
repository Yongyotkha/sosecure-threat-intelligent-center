<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use Exception;
use App\DataCveSource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\MonitoringVulnerabilitys\Entities\CVEAssets;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
use Modules\MonitoringVulnerabilitys\Entities\CVEMappingAssets;
use Modules\SiteSettings\Entities\DataCveven;
use Modules\SiteSettings\Entities\SiteSettings;

class MDCVEFeedOnline extends Command
{
    protected $signature = 'app:MDCVEFeedOnline';
    protected $description = 'Fetch CVE data from NVD API and update system safely';

    public function handle()
    {
        $conn = mysqli_connect(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'), env('DB_PORT'));
        if (!$conn) {
            $this->error("❌ Connection failed: " . mysqli_connect_error());
            return;
        }

        date_default_timezone_set("Asia/Bangkok");
        $this->info("=== 🟢 Starting MDCVEFeedOnline (Safe Mode) ===");

        $startTime = microtime(true);
        $sitesUpdated = [];  

        try {
            $client = new Client([
                'base_uri' => 'https://services.nvd.nist.gov/',
                'timeout'  => 60,
                'verify'   => false,
            ]);

            $cpeList = DB::table('cve_assets')
                ->select('vendor', 'title', 'version', 'edition', 'site_id')
                ->where('active', 1)
                ->groupBy('vendor', 'title', 'version', 'edition', 'site_id')
                ->get();


            if ($cpeList->isEmpty()) {
                $this->warn("⚠️ No CPE data found.");
                return;
            }

            $cpeIndex = 0;
            foreach ($cpeList as $row) {

                $cpeIndex++;
                $vendor  = trim($row->vendor);
                $product = trim($row->title);
                $version = trim($row->version ?: '*');
                $edition = trim($row->edition ?: '*');

                $part = $this->detectCPEPart($vendor, $product);
                $cpeName = "cpe:2.3:{$part}:{$vendor}:{$product}:{$version}:{$edition}";

                $this->info("🔍 Fetching CVE for: {$cpeName}");

                $pageSize     = 2000;
                $startIndex   = 0;
                $totalResults = null;
                $page         = 1;

                // do {
                //     $url = "rest/json/cves/2.0?cpeName=" . urlencode($cpeName)
                //         . "&startIndex={$startIndex}&resultsPerPage={$pageSize}";

                // $currentYear = date("Y");

                // $pubStart = "{$currentYear}-01-01T00:00:00.000";
                // $pubEnd   = "{$currentYear}-12-31T23:59:59.000";

                // $url = "rest/json/cves/2.0?cpeName=" . urlencode($cpeName)
                //     . "&startIndex={$startIndex}&resultsPerPage={$pageSize}"
                //     . "&pubStartDate={$pubStart}&pubEndDate={$pubEnd}";


                $end = now()->endOfDay();
                $start = now()->subMonths(3)->startOfDay();

                $pubStartDate = $start->format("Y-m-d\\TH:i:s.000\\Z");
                $pubEndDate   = $end->format("Y-m-d\\TH:i:s.000\\Z");

                $this->info("📅 Date range: $pubStartDate → $pubEndDate");

                do {

                    // NEW: เพิ่ม date filter ลงใน URL
                    $url = "rest/json/cves/2.0?"
                        . "cpeName=" . urlencode($cpeName)
                        . "&pubStartDate={$pubStartDate}"
                        . "&pubEndDate={$pubEndDate}"
                        . "&startIndex={$startIndex}"
                        . "&resultsPerPage={$pageSize}";


                    $this->info("🌐 Calling NVD API (safe mode)...");
                    Log::info('🌐 Calling NVD API (safe mode)');

                    // 🔥 ใช้ safeGet() แทนทุกอย่าง
                    $response = $this->safeGet($client, $url);

                    // ถ้าเจอ 404 หรือเจอ error ที่ safeGet คืน null → skip CPE นี้
                    if (!$response) {
                        $this->info("ℹ️  No CVE found or request failed → skip {$cpeName}");
                        break;
                    }

                    $this->info("✅ API responded. Processing...");
                    Log::info('✅ API responded. Processing...');

                    $data = json_decode($response->getBody(), true);
                    $vulns = $data['vulnerabilities'] ?? [];

                    if ($totalResults === null) {
                        $totalResults = $data['totalResults'] ?? 0;
                    }

                    if (empty($vulns)) break;
                    foreach ($vulns as $vuln) {

                        $cve = $vuln['cve'] ?? null;
                        if (!$cve) continue;

                        $cveId     = $cve['id'] ?? '-';
                        $published = explode('T', $cve['published'] ?? '')[0];
                        $modified  = explode('T', $cve['lastModified'] ?? '')[0];

                        // description EN
                        $desc = '';
                        foreach ($cve['descriptions'] ?? [] as $d) {
                            if (($d['lang'] ?? '') === 'en') {
                                $desc = $d['value'];
                                break;
                            }
                        }

                        // CVSS
                        $cvssScore = '';
                        $cvssSeverity = '';
                        $metrics = $cve['metrics'] ?? [];

                        foreach (['cvssMetricV40', 'cvssMetricV31', 'cvssMetricV30'] as $ver) {
                            if (isset($metrics[$ver][0]['cvssData'])) {
                                $cvss = $metrics[$ver][0]['cvssData'];
                                $cvssScore    = $cvss['baseScore'] ?? '';
                                $cvssSeverity = $cvss['baseSeverity'] ?? '';
                                break;
                            }
                        }

                        // fallback v2
                        if ($cvssScore === '' && isset($metrics['cvssMetricV2'][0])) {
                            $cvss = $metrics['cvssMetricV2'][0]['cvssData'];
                            $cvssScore    = $cvss['baseScore'] ?? '';
                            $cvssSeverity = $metrics['cvssMetricV2'][0]['baseSeverity'] ?? '';
                        }

                        // ----- save data_cveven -----
                        $cpeMatches = [];
                        foreach ($cve['configurations'] ?? [] as $config) {
                            foreach ($config['nodes'] ?? [] as $node) {
                                foreach ($node['cpeMatch'] ?? [] as $match) {
                                    if (!empty($match['vulnerable']) && isset($match['criteria'])) {
                                        $cpeMatches[] = $match['criteria'];
                                    }
                                }
                            }
                        }

                        DB::beginTransaction();
                        try {
                            foreach ($cpeMatches as $uri) {
                                $parts = explode(':', $uri);
                                DB::table('data_cveven')->updateOrInsert(
                                    [
                                        'namecve' => $cveId,
                                        'vendor'  => $parts[3] ?? '',
                                        'title'   => $parts[4] ?? '',
                                        'version' => $parts[5] ?? '',
                                    ],
                                    [
                                        'edition'    => (($parts[6] ?? '') === '*' ? '' : ($parts[6] ?? '')),
                                        'updated_at' => now(),
                                        'created_at' => DB::raw('IFNULL(created_at, NOW())')
                                    ]
                                );
                            }

                            // 1. ดึงข้อมูล source record
                            $cveSource = DB::table('data_cve_sources')
                                ->where('namecve', $cveId)
                                ->where('source', 'online')
                                ->first();

                            $latestCve = null;
                            if ($cveSource && $cveSource->datacve_id) {
                                $latestCve = DB::table('data_datacve')
                                    ->where('id', $cveSource->datacve_id)
                                    ->first();
                            }

                            $needNewRecord = false;

                            // 2. เช็คว่าต้องสร้าง record ใหม่ไหม
                            if ($latestCve) {
                                if ($latestCve->cvss_score != $cvssScore || $latestCve->severity != $cvssSeverity) {
                                    $needNewRecord = true;
                                }
                            } else {
                                $needNewRecord = true;
                            }

                            // 3. บันทึก data_datacve
                            if ($needNewRecord) {
                                // สร้าง record ใหม่
                                $newCveId = DB::table('data_datacve')->insertGetId([
                                    'namecve'     => $cveId,
                                    'published'   => $published,
                                    'modified'    => $modified,
                                    'description' => $desc,
                                    'cvss_score'  => $cvssScore,
                                    'severity'    => $cvssSeverity,
                                    'created_at'  => now(),
                                    'updated_at'  => now(),
                                ]);

                                // Update หรือสร้าง source record
                                if ($cveSource) {
                                    DB::table('data_cve_sources')
                                        ->where('id', $cveSource->id)
                                        ->update([
                                            'datacve_id' => $newCveId,
                                            'updated_at' => now(),
                                        ]);
                                } else {
                                    DB::table('data_cve_sources')->insert([
                                        'namecve'    => $cveId,
                                        'source'     => 'online',
                                        'datacve_id' => $newCveId,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                                }
                            } else {
                                // Update เฉพาะข้อมูลที่ไม่ใช่ score/severity
                                DB::table('data_datacve')
                                    ->where('id', $latestCve->id)
                                    ->update([
                                        'published'   => $published,
                                        'modified'    => $modified,
                                        'description' => $desc,
                                        'updated_at'  => now(),
                                    ]);

                                // ⭐ เพิ่มตรงนี้ - Update updated_at ของ source ด้วย
                                if ($cveSource) {
                                    DB::table('data_cve_sources')
                                        ->where('id', $cveSource->id)
                                        ->update(['updated_at' => now()]);
                                }
                            }

                            // mirror global
                            DB::table('data_datacve_mapping')->updateOrInsert(
                                ['namecve' => $cveId, 'site_id' => 0],
                                [
                                    'published'   => $published,
                                    'modified'    => $modified,
                                    'description' => $desc,
                                    'cvss_score'  => $cvssScore,
                                    'severity'    => $cvssSeverity,
                                    'updated_at'  => now(),
                                    'created_at'  => DB::raw('IFNULL(created_at, NOW())')
                                ]
                            );

                            DB::commit();
                        } catch (Exception $e) {
                            DB::rollBack();
                            Log::error("CVE Save Error: {$cveId}", [
                                'error' => $e->getMessage(),
                                'line' => $e->getLine(),
                            ]);
                            continue;
                        }
                        // === Source tracking ===
                        // DataCveSource::firstOrCreate([
                        //     'namecve' => $cveId,
                        //     'source'  => 'online'
                        // ]);

                        // === Mapping ===
                        $siteId = $row->site_id;
                        $assetsMatched = DB::table('cve_assets')
                            ->where('vendor', $vendor)
                            ->where('title', $product)
                            ->where('site_id', $siteId)
                            ->get();

                        if (!$assetsMatched->isEmpty()) {
                            foreach ($assetsMatched as $asset) {

                                $exists = DB::table('data_datacve_mapping_assets')
                                    ->where('namecve', $cveId)
                                    ->where('site_id', $asset->site_id)
                                    ->where('cve_asset_id', $asset->id)
                                    ->exists();

                                if (!$exists) {

                                    DB::table('data_datacve_mapping_assets')->insert([
                                        'namecve'      => $cveId,
                                        'site_id'      => $asset->site_id,
                                        'cve_asset_id' => $asset->id,
                                        'code'         => (string) Str::uuid(),
                                        'created_at'   => now(),
                                        'updated_at'   => now(),
                                    ]);

                                    // บันทึก site ที่มี mapping ใหม่
                                    $sitesUpdated[$asset->site_id] = true;
                                }
                            }
                        }

                        usleep(800000);
                    }

                    $startIndex += $pageSize;
                    $page++;
                    usleep(200000);
                } while ($startIndex < $totalResults);
            }

            // ================================
            // 🔥 SUMMARY UPDATE แบบปลอดภัย
            // ================================
            if (!empty($sitesUpdated)) {

                $this->info("🔄 Updating summary only for affected sites...");
                Log::info('🔄 Updating summary only for affected sites...');

                foreach (array_keys($sitesUpdated) as $siteId) {
                    $siteCode = DB::table('site')->where('id', $siteId)->value('code');

                    if (!$siteCode) continue;

                    $this->generateSummary($conn, $siteId, $siteCode);
                }
            } else {
                $this->info("ℹ️ No new mapping → skip summary update.");
                Log::info('ℹ️ No new mapping → skip summary update.');
            }
        } catch (Exception $e) {
            $this->error("❌ Fatal: " . $e->getMessage());
        }
    }

    // ------------------------------
    // ใช้ logic เดิม 100% ไม่แตะ
    // ------------------------------
    private function generateSummary($conn, $site_id, $site_code)
    {
        $this->info("🔁 generateSummary(site_id={$site_id}, code={$site_code}) ...");
        Log::info('🔁 generateSummary(site_id=' . $site_id . ', code=' . $site_code . ') ...');

        // ดึง namecve ทั้งหมดของ site (distinct)
        $mappedCve = DB::table('data_datacve_mapping_assets')
            ->where('site_id', $site_id)
            ->distinct()
            ->pluck('namecve');

        if ($mappedCve->isEmpty()) {
            $this->info("ℹ️ No CVE mapping for site={$site_code}");
            return;
        }

        // ดึง severity ตาม CVE แบบ DISTINCT
        $severityList = DB::table('data_datacve_mapping')
            ->whereIn('namecve', $mappedCve)
            ->select('namecve', 'severity')
            ->groupBy('namecve', 'severity')
            ->get();

        // เคลียร์ summary เดิม
        DB::table('summary')
            ->where('site', $site_code)
            ->where('data_key_2', 'Vulnerability')
            ->delete();

        // นับตาม severity
        $crit = $high = $med = $low = $info = 0;

        foreach ($severityList as $s) {
            switch (strtoupper($s->severity)) {
                case 'CRITICAL':
                    $crit++;
                    break;
                case 'HIGH':
                    $high++;
                    break;
                case 'MEDIUM':
                    $med++;
                    break;
                case 'LOW':
                    $low++;
                    break;
                default:
                    $info++;
                    break;
            }
        }

        // เขียน summary
        DB::table('summary')->insert([
            ['site' => $site_code, 'data_key' => 'dashboardnew', 'data_key_2' => 'Vulnerability', 'data_text' => 'Critical', 'data_value' => $crit, 'created_at' => now(), 'updated_at' => now()],
            ['site' => $site_code, 'data_key' => 'dashboardnew', 'data_key_2' => 'Vulnerability', 'data_text' => 'High', 'data_value' => $high, 'created_at' => now(), 'updated_at' => now()],
            ['site' => $site_code, 'data_key' => 'dashboardnew', 'data_key_2' => 'Vulnerability', 'data_text' => 'Medium', 'data_value' => $med, 'created_at' => now(), 'updated_at' => now()],
            ['site' => $site_code, 'data_key' => 'dashboardnew', 'data_key_2' => 'Vulnerability', 'data_text' => 'Low', 'data_value' => $low, 'created_at' => now(), 'updated_at' => now()],
            ['site' => $site_code, 'data_key' => 'dashboardnew', 'data_key_2' => 'Vulnerability', 'data_text' => 'Information', 'data_value' => $info, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->info("   ✔ Summary updated for {$site_code} — Total CVEs = " . ($crit + $high + $med + $low + $info));
        Log::info('   ✔ Summary updated for ' . $site_code . ' — Total CVEs = ' . ($crit + $high + $med + $low + $info));
    }

    private function safeGet($client, $url, $retry = 5)
    {
        for ($i = 0; $i < $retry; $i++) {
            try {
                return $client->get($url, [
                    'headers' => [
                        'apiKey' => env('NVD_API_KEY')
                    ]
                ]);
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                $code = $e->getCode();

                // 🔥 404 = ไม่มี CVE ของ product นี้ → return null = ให้ loop skip ไป
                if ($code == 404) {
                    echo "ℹ️  404 Not Found → Skip CPE\n";
                    return null;
                }

                // 🔥 429 = rate limit → รอแล้ว retry
                if ($code == 429) {
                    echo "⏳ Hit rate limit (429) → waiting 15 seconds...\n";
                    sleep(15);
                    continue;
                }

                // อื่นๆ → ส่ง error ออกไป
                throw $e;
            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                echo "❌ Connection error, retrying...\n";
                sleep(3);
                continue;
            }
        }

        // retry ครบ → skip item นี้
        return null;
    }

    private function detectCPEPart($vendor, $product)
    {
        $p = strtolower($product);

        // OS Keywords
        $osKeywords = ['windows', 'linux', 'ubuntu', 'debian', 'redhat', 'centos', 'esxi'];

        foreach ($osKeywords as $os) {
            if (str_contains($p, $os)) {
                return 'o';
            }
        }

        return 'a'; // default application
    }
}
