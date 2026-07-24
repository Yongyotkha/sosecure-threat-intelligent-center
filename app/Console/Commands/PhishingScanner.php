<?php

namespace App\Console\Commands;

use App\LogPhishing;
use App\Services\PhishingDetectionService;
use App\Services\SystemApiKeyService;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PhishingScanner extends Command
{
    protected $signature = 'phishing:scan {--site_id=all} {--domain=} {--brand=} {--offline : Skip external discovery APIs (crt.sh, urlscan)} {--dry-run : Preview results without saving to database}';
    protected $description = 'Scan for phishing sites using domain discovery and multi-provider analysis (Python-compatible flow)';

    /** @var PhishingDetectionService */
    private $phishingService;

    /** @var bool */
    private $dryRun = false;

    public function handle()
    {
        $apiKeyService = new SystemApiKeyService();
        if (!$apiKeyService->hasKeys('virustotal')) {
            $this->error('Missing VirusTotal keys. Configure api_tokens (type=virustotal, scope=system) or VIRUSTOTAL_API_KEYS in .env');
            return 1;
        }

        $httpClient = new Client(['timeout' => 30, 'http_errors' => false]);
        $this->phishingService = new PhishingDetectionService($apiKeyService, $httpClient);

        $domainsToScan = $this->resolveDomainsToScan();
        if ($domainsToScan->isEmpty()) {
            $this->info('No eligible domains found to scan.');
            return 0;
        }

        $offlineMode = (bool) $this->option('offline');
        $this->dryRun = (bool) $this->option('dry-run') || (bool) $this->option('domain');

        if ($this->dryRun) {
            $this->warn('DRY-RUN mode: results will be displayed only, nothing saved to database.');
        }

        foreach ($domainsToScan as $domainInfo) {
            $officialDomain = strtolower(preg_replace('/^www\./', '', $domainInfo->official_domain));
            $officialUrl = strpos($domainInfo->official_domain, 'http') === 0
                ? $domainInfo->official_domain
                : 'https://' . $domainInfo->official_domain;

            $this->info('--------------------------------------------------');
            $this->info("Starting phishing scan for Site ID: {$domainInfo->site_id} - Brand: {$domainInfo->brand_name} ({$officialDomain})");

            $this->info('Fetching official site content...');
            $officialText = $this->phishingService->fetchOfficialText($officialUrl);

            $this->info('Discovering suspicious domains (typo squatting, crt.sh, DNS, urlscan)...');
            $discoveryResult = $this->phishingService->discoverCandidates($officialDomain, $offlineMode);
            $candidates = $discoveryResult['candidates'] ?? [];

            $this->info('Found ' . count($candidates) . " resolvable candidate domain(s).");

            $maxCandidates = 25;
            if (count($candidates) > $maxCandidates) {
                $this->warn("Limiting analysis to top {$maxCandidates} candidates by suspicious score.");
                usort($candidates, function ($a, $b) {
                    return ($b['suspicious_score'] ?? 0) <=> ($a['suspicious_score'] ?? 0);
                });
                $candidates = array_slice($candidates, 0, $maxCandidates);
            }

            $summary = ['total' => 0, 'phishing' => 0, 'suspicious' => 0, 'benign' => 0, 'saved' => 0];
            $allResults = [];

            foreach ($candidates as $index => $candidate) {
                $candidateDomain = $candidate['domain'] ?? '';
                if (!$candidateDomain) {
                    continue;
                }

                if ($this->phishingService->isOfficialOrTrusted($candidateDomain, $officialDomain)) {
                    continue;
                }

                $candidateUrl = 'https://' . $candidateDomain . '/';
                if (!$this->dryRun && $this->urlAlreadyLogged($domainInfo->site_id, $candidateUrl)) {
                    $this->info("Skipped: {$candidateUrl} already logged.");
                    continue;
                }

                $position = $index + 1;
                $source = $candidate['source'] ?? 'unknown';
                $this->info("[{$position}/" . count($candidates) . "] Analyzing: {$candidateDomain} (source: {$source})");

                $result = $this->phishingService->analyzeCandidate(
                    $candidateDomain,
                    $officialDomain,
                    $officialUrl,
                    $officialText
                );

                $summary['total']++;
                $label = $result['label'] ?? 'Benign';
                if ($label === 'Phishing') {
                    $summary['phishing']++;
                } elseif ($label === 'Suspicious') {
                    $summary['suspicious']++;
                } else {
                    $summary['benign']++;
                }

                $result['source'] = $source;
                $allResults[] = $result;

                $this->line("    Score: {$result['score']} ({$label})");

                if ($this->dryRun) {
                    if ($label !== 'Benign') {
                        $this->printManualAnalysisResult($result, $officialDomain);
                    }
                    continue;
                }

                if ($label === 'Benign') {
                    continue;
                }

                $severityInfo = $this->phishingService->labelToSeverity($label);
                $reputationHit = !empty($result['signals']['reputation']);

                LogPhishing::create([
                    'code' => (string) Str::uuid(),
                    'site_id' => $domainInfo->site_id,
                    'url' => $result['url'],
                    'url_detection' => $officialDomain,
                    'ip' => $result['ip'],
                    'score' => $severityInfo['db_score'],
                    'serverity' => $severityInfo['severity'],
                    'type' => 'OSINT Scanner',
                    'status' => 1,
                    'transaction_status' => 3,
                    'url_is_work' => 1,
                    'is_found' => $reputationHit ? 1 : 0,
                    'analysis_data' => json_encode($result),
                ]);

                $summary['saved']++;
                $this->info("Saved: {$result['url']} [{$label}]");
            }

            $this->printSummary($officialDomain, $summary, $allResults);
        }

        $this->info('--------------------------------------------------');
        $this->info('All phishing scans completed.');

        return 0;
    }

    private function resolveDomainsToScan()
    {
        $siteId = $this->option('site_id');
        $domainOpt = $this->option('domain');
        $brandOpt = $this->option('brand');

        if ($domainOpt) {
            $brandName = $brandOpt ?: explode('.', str_replace('www.', '', strtolower($domainOpt)))[0];
            return collect([
                (object) [
                    'site_id' => 0,
                    'brand_name' => ucfirst($brandName),
                    'official_domain' => $domainOpt,
                ],
            ]);
        }

        $query = \Illuminate\Support\Facades\DB::table('domain')
            ->join('site_menu_permission', 'domain.site_id', '=', 'site_menu_permission.site_id')
            ->join('site', 'site.id', '=', 'domain.site_id')
            ->where('site_menu_permission.menu_id', 15)
            ->whereNull('site_menu_permission.deleted_at')
            ->where('site.active', 1)
            ->whereNull('site.deleted_at')
            ->where('domain.status', 1)
            ->whereNull('domain.deleted_at')
            ->select('domain.site_id', 'site.name as brand_name', 'domain.domain as official_domain');

        if ($siteId && $siteId !== 'all') {
            $query->where('domain.site_id', $siteId);
        }

        return $query->get();
    }

    private function urlAlreadyLogged($siteId, $url)
    {
        if ($siteId === 0) {
            return false;
        }

        return LogPhishing::where('site_id', $siteId)
            ->where('url', $url)
            ->whereNull('deleted_at')
            ->exists();
    }

    private function printSummary($officialDomain, array $summary, array $allResults)
    {
        $this->info("\n========== SUMMARY: {$officialDomain} ==========");
        $this->line("Total analyzed : {$summary['total']}");
        $this->line("Phishing       : {$summary['phishing']}");
        $this->line("Suspicious     : {$summary['suspicious']}");
        $this->line("Benign         : {$summary['benign']}");
        if (!$this->dryRun) {
            $this->line("Saved to DB    : {$summary['saved']}");
        }

        $threats = array_filter($allResults, function ($r) {
            return ($r['label'] ?? 'Benign') !== 'Benign';
        });

        if (empty($threats)) {
            $this->info('No phishing or suspicious URLs detected.');
            $this->info('================================================');
            return;
        }

        usort($threats, function ($a, $b) {
            return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
        });

        $this->info("\n--- Threats (Phishing / Suspicious) ---");
        $headers = ['Label', 'Score', 'Domain', 'IP', 'Providers', 'Source'];
        $rows = [];
        foreach ($threats as $r) {
            $rows[] = [
                $r['label'] ?? '',
                $r['score'] ?? 0,
                $r['domain'] ?? '',
                $r['ip'] ?? '',
                implode(', ', $r['signals']['reputation'] ?? []) ?: '-',
                $r['source'] ?? '',
            ];
        }
        $this->table($headers, $rows);
        $this->info('================================================');
    }

    private function printManualAnalysisResult(array $result, $officialDomain)
    {
        $this->info('=================== [ANALYSIS RESULT] ===================');
        $this->line('Official:    ' . $officialDomain);
        $this->line('URL:         ' . ($result['url'] ?? ''));
        $this->line('Domain:      ' . ($result['domain'] ?? ''));
        $this->line('IP Address:  ' . ($result['ip'] ?? 'Unknown'));
        $this->line('Score:       ' . ($result['score'] ?? 0));
        $this->line('Label:       ' . ($result['label'] ?? 'Benign'));

        $this->line("\n--- Reputation Providers ---");
        $reputation = $result['signals']['reputation'] ?? [];
        if (empty($reputation)) {
            $this->line('None');
        } else {
            foreach ($reputation as $source) {
                $this->warn('  * ' . $source);
            }
        }

        $this->line("\n--- Heuristics ---");
        $heuristics = $result['signals']['heuristics'] ?? [];
        if (empty($heuristics)) {
            $this->line('None');
        } else {
            foreach ($heuristics as $hit) {
                $this->line('  - ' . $hit);
            }
        }

        $this->line("\n--- Advanced Analysis ---");
        $advanced = $result['evidence']['advanced_analysis'] ?? [];
        foreach ($advanced as $key => $value) {
            $display = is_bool($value) ? ($value ? 'Yes' : 'No') : $value;
            $this->line('  ' . $key . ': ' . $display);
        }

        $this->line("\n--- ML Analysis ---");
        $ml = $result['evidence']['ml_analysis'] ?? [];
        $this->line('  ml_score: ' . ($ml['ml_score'] ?? 0));
        $this->line('  ml_label: ' . ($ml['ml_label'] ?? 'Unknown'));

        $this->info('=========================================================');
    }
}
