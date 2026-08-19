<?php

namespace App\Console\Commands;

use App\Services\OtxHttpClient;
use App\Services\OtxPulseStagingStore;
use Artisan;
use Exception;
use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;

/**
 * Staging pipeline for OTX indicator feed (list modified:<12h).
 * One command: fetch → staging → auto-import (same idea as OTXMDFetchPulse).
 */
class OTXMDFetchIndicator extends Command
{
    protected $signature = 'app:OTXMDFetchIndicator
                            {--limit= : Limit indicators to process (testing)}
                            {--resume : Resume leftover run (default: auto if leftover exists)}
                            {--fresh : Ignore leftover staging and start a new run}
                            {--run= : Resume a specific run_id}
                            {--timeout=45 : HTTP timeout seconds for indicator detail}
                            {--list-timeout=180 : HTTP timeout seconds for indicator list pages}
                            {--retries=2 : Max HTTP retries per URL}
                            {--skip-import : Fetch only; do not auto-import}
                            {--database=sosecure_threatintelligent : Mongo database}';

    protected $description = 'Fetch OTX indicators (finish leftover first) then auto-import';

    /** @var OtxHttpClient */
    protected $http;
    protected $dbName;
    protected $kind = 'indicators';

    public function handle()
    {
        $lock = OtxPulseStagingStore::acquireLock('indicators');
        if (!$lock) {
            $this->warn('Another indicator fetch is already running. Leftover will be finished on the next run.');
            return 0;
        }

        try {
            return $this->handleLocked();
        } finally {
            OtxPulseStagingStore::releaseLock($lock);
        }
    }

    protected function handleLocked()
    {
        $this->http = new OtxHttpClient(
            (int) $this->option('timeout'),
            (int) $this->option('retries')
        );
        $this->dbName = $this->option('database') ?: 'sosecure_threatintelligent';

        $query = 'modified:<12h';
        $explicitRun = $this->option('run');
        $fresh = (bool) $this->option('fresh');
        $limit = $this->option('limit') !== null && $this->option('limit') !== ''
            ? (int) $this->option('limit')
            : null;

        if ($explicitRun) {
            return $this->processIndicatorRun($explicitRun, $query, $limit);
        }

        if (!$fresh) {
            $leftover = OtxPulseStagingStore::findIncompleteRunId($this->kind);
            if ($leftover) {
                $this->info("Leftover indicator staging found — finishing before a new window: {$leftover}");
                $exit = $this->processIndicatorRun($leftover, $query, $limit);
                $still = OtxPulseStagingStore::loadManifest($leftover, $this->kind);
                if ($still && OtxPulseStagingStore::isIncomplete($still)) {
                    $this->warn('Leftover not complete — skip new window so data is not abandoned.');
                    return $exit;
                }
                $this->info('Leftover finished. Starting new modified:<12h window...');
            }
        } else {
            $this->warn('--fresh: ignoring leftover staging (may leave disk garbage / missed import).');
        }

        return $this->processIndicatorRun(null, $query, $limit);
    }

    protected function processIndicatorRun($runId, $query, $limit)
    {
        if ($runId) {
            $manifest = OtxPulseStagingStore::loadManifest($runId, $this->kind);
            if (!$manifest) {
                $this->error("Run not found: {$runId}");
                return 1;
            }
            OtxPulseStagingStore::stampSessionStart($manifest, true);
            OtxPulseStagingStore::saveManifest($runId, $manifest, $this->kind);
            $this->info("Resuming indicator run: {$runId} (resume #" . (int) ($manifest['resume_count'] ?? 0) . ')');
        } else {
            $runId = OtxPulseStagingStore::createRun($query, $this->kind, $limit);
            $manifest = OtxPulseStagingStore::loadManifest($runId, $this->kind);
            OtxPulseStagingStore::stampSessionStart($manifest, false);
            OtxPulseStagingStore::saveManifest($runId, $manifest, $this->kind);
        $this->info("Created indicator run: {$runId}");
        }
        $this->info('Started at          : ' . ($manifest['started_at'] ?? '-'));
        $this->info('HTTP detail timeout : ' . (int) $this->option('timeout') . 's / list timeout: ' . (int) $this->option('list-timeout') . 's');

        try {
            $this->fetchListPages($runId, $manifest, $limit);
            $this->fetchDetails($runId, $manifest, $limit);
        } catch (Exception $e) {
            $manifest['status'] = 'partial';
            $manifest['last_error'] = $e->getMessage();
            $manifest['complete'] = false;
            OtxPulseStagingStore::saveManifest($runId, $manifest, $this->kind);
            $this->error('Fetch aborted: ' . $e->getMessage());
            $this->warn('Staging kept for resume: ' . OtxPulseStagingStore::runPath($runId, $this->kind));
            return 1;
        }

        $fetched = 0;
        $skipped = 0;
        $failed = 0;
        $pending = 0;
        foreach ($manifest['items'] ?? [] as $meta) {
            $st = $meta['status'] ?? 'pending';
            if ($st === 'fetched') {
                $fetched++;
            } elseif ($st === 'skipped') {
                $skipped++;
            } elseif ($st === 'failed') {
                $failed++;
            } else {
                $pending++;
            }
        }
        $manifest['items_fetched'] = $fetched;
        $manifest['items_skipped'] = $skipped;
        $manifest['items_failed'] = $failed;

        $apiTotal = (int) ($manifest['api_total'] ?? 0);
        $inScope = $fetched + $skipped + $failed + $pending;
        $verified = $fetched + $skipped + $failed;
        $isPartialList = !empty($manifest['checkpoint']['list_done']) && $inScope < $apiTotal;
        if ($limit !== null) {
            $expected = max($inScope, $limit);
        } elseif ($isPartialList) {
            $expected = $inScope;
        } else {
            $expected = $apiTotal > 0 ? $apiTotal : $inScope;
        }
        $pct = $expected > 0 ? round(($verified / $expected) * 100, 2) : 0;
        $fetchComplete = $failed === 0 && $pending === 0 && $verified >= $expected;

        if ($limit !== null) {
            $manifest['limit'] = $limit;
        }
        $manifest['status'] = ($failed > 0 || $pending > 0) ? 'partial' : 'fetched';
        $manifest['stats'] = [
            'api_total' => $apiTotal,
            'limit' => $limit,
            'expected' => $expected,
            'fetched' => $fetched,
            'skipped' => $skipped,
            'failed' => $failed,
            'pending' => $pending,
            'verified' => $verified,
            'pct' => $pct,
            'complete' => $fetchComplete,
        ];
        OtxPulseStagingStore::stampFetchFinish($manifest, $fetchComplete);
        OtxPulseStagingStore::saveManifest($runId, $manifest, $this->kind);

        $this->info('=========================================');
        $this->info('INDICATOR FETCH SUMMARY');
        $this->info('=========================================');
        $this->info("Run ID              : {$runId}");
        $this->info('Started at          : ' . ($manifest['started_at'] ?? '-'));
        $this->info('Last started        : ' . ($manifest['last_started_at'] ?? '-'));
        $this->info('Fetch finished at   : ' . ($manifest['fetch_finished_at'] ?? '-'));
        $this->info('Resume count        : ' . (int) ($manifest['resume_count'] ?? 0));
        $this->info('API total (OTX catalog, not this run) : ' . number_format($apiTotal));
        $this->info('Intended this run                     : ' . number_format($expected) . ($limit !== null ? " (limit={$limit})" : ''));
        $this->info('Fetched to staging                    : ' . number_format($fetched));
        $this->info('Skipped                               : ' . number_format($skipped));
        $this->info('Failed                                : ' . number_format($failed));
        $this->info('Verified                              : ' . number_format($verified) . ' / ' . number_format($expected) . " ({$pct}%)");
        $this->info('Fetch complete      : ' . ($fetchComplete ? 'YES' : 'NO'));

        $exit = ($failed > 0 || $pending > 0) ? 2 : 0;
        $importPending = ($manifest['import_status'] ?? 'pending') !== 'done';
        if (($fetched > 0 || $skipped > 0) && $importPending) {
            $imp = $this->runImport($runId, $manifest);
            if ($imp !== 0 && $exit === 0) {
                $exit = $imp;
            }
        } else {
            $this->info('Skip auto-import    : nothing to import');
        }

        $after = OtxPulseStagingStore::loadManifest($runId, $this->kind);
        if ($after) {
            $pipelineComplete = ($after['import_status'] ?? '') === 'done' && ($after['status'] ?? '') === 'fetched';
            if ($this->option('skip-import')) {
                $pipelineComplete = false;
            }
            OtxPulseStagingStore::stampFinish($after, $pipelineComplete);
            OtxPulseStagingStore::saveManifest($runId, $after, $this->kind);
            $this->info('Finished at         : ' . ($after['finished_at'] ?? '-'));
            $this->info('Pipeline complete   : ' . ($pipelineComplete ? 'YES' : 'NO'));
            $this->info('Duration            : ' . OtxPulseStagingStore::elapsed($after['started_at'] ?? null, $after['finished_at'] ?? null));
        } else {
            $this->info('Finished at         : ' . date('c') . ' (staging deleted after complete import)');
            $this->info('Pipeline complete   : YES');
            $this->info('Duration            : ' . OtxPulseStagingStore::elapsed($manifest['started_at'] ?? null));
        }

        return $exit;
    }

    protected function fetchListPages($runId, array &$manifest, $limit)
    {
        if (!empty($manifest['checkpoint']['list_done'])) {
            $this->info('List pages already complete, skipping.');
            return;
        }

        $pageSize = ($limit !== null && $limit > 0 && $limit < 100) ? max($limit, 10) : 100;
        $query = $manifest['query'] ?? 'modified:<12h';

        // OTX caps page at 100 (~10k rows). Cover totals above that by
        // newest-first pass then oldest-first pass (dedupe by id).
        $passes = [
            'newest' => '-modified',
            'oldest' => 'modified',
        ];

        foreach ($passes as $passName => $sort) {
            $doneKey = 'list_pass_' . $passName . '_done';
            if (!empty($manifest['checkpoint'][$doneKey])) {
                $this->info("List pass '{$passName}' already done, skipping.");
                continue;
            }

            $prefix = $passName === 'newest' ? '' : 'old-';
            $checkpointUrlKey = 'list_next_url_' . $passName;
            $checkpointPageKey = 'list_page_' . $passName;

            $page = (int) ($manifest['checkpoint'][$checkpointPageKey] ?? 0);
            $nextUrl = $manifest['checkpoint'][$checkpointUrlKey] ?? null;

            // Resume after legacy single-pass abort (checkpoint.list_next_url often page=101).
            if ($passName === 'newest' && !$nextUrl && !empty($manifest['checkpoint']['list_next_url'])) {
                $legacy = $manifest['checkpoint']['list_next_url'];
                if ($this->urlPageNumber($legacy) > 100) {
                    $this->warn('Previous run hit OTX page cap on newest pass; continuing with oldest pass.');
                    $manifest['checkpoint']['list_pass_newest_done'] = true;
                    $manifest['checkpoint']['list_pass_newest_capped'] = true;
                    $manifest['checkpoint']['list_next_url'] = null;
                    OtxPulseStagingStore::saveManifest($runId, $manifest, $this->kind);
                    continue;
                }
                $nextUrl = $legacy;
            }

            if ($nextUrl && $this->urlPageNumber($nextUrl) > 100) {
                $this->warn("OTX page cap on {$passName} pass — ending this pass.");
                $manifest['checkpoint']['list_pass_' . $passName . '_capped'] = true;
                $manifest['checkpoint'][$doneKey] = true;
                $manifest['checkpoint'][$checkpointUrlKey] = null;
                unset($manifest['checkpoint']['list_next_url']);
                OtxPulseStagingStore::saveManifest($runId, $manifest, $this->kind);
                continue;
            }

            if (!$nextUrl) {
                $nextUrl = 'https://otx.alienvault.com/otxapi/indicators/?include_inactive=0&sort=' . rawurlencode($sort)
                    . '&q=' . rawurlencode($query)
                    . '&page=1&limit=' . $pageSize;
            }

            $this->info("List pass '{$passName}' (sort={$sort}) ...");

            while ($nextUrl) {
                $requestedPage = $this->urlPageNumber($nextUrl);
                if ($requestedPage > 100) {
                    $this->warn("OTX page cap (max 100) on {$passName} pass — will use complementary sort if needed.");
                    $manifest['checkpoint']['list_pass_' . $passName . '_capped'] = true;
                    $nextUrl = null;
                    break;
                }

                $page++;
                $t0 = microtime(true);
                $listTimeout = (int) $this->option('list-timeout');
                $this->info("Fetching indicator list {$passName} page {$requestedPage} (timeout={$listTimeout}s) ...");
                $resp = $this->http->get($nextUrl, $listTimeout);
                $this->info(sprintf(
                    '  list %s page %d done in %.1fs%s',
                    $passName,
                    $requestedPage,
                    microtime(true) - $t0,
                    !empty($resp['success']) ? '' : ' (failed)'
                ));

                if (!$resp['success']) {
                    $err = (string) ($resp['error'] ?? '');
                    // Cap hit returned as 400 — treat as end of this pass, not fatal.
                    if (stripos($err, 'Maximum allowed value for page') !== false || stripos($err, '400 Bad Request') !== false) {
                        $this->warn("OTX rejected page>100 on {$passName} — ending this pass.");
                        $manifest['checkpoint']['list_pass_' . $passName . '_capped'] = true;
                        $nextUrl = null;
                        break;
                    }
                    $manifest['checkpoint'][$checkpointUrlKey] = $nextUrl;
                    $manifest['checkpoint'][$checkpointPageKey] = $page - 1;
                    $manifest['status'] = 'partial';
                    $manifest['last_error'] = 'List page failed: ' . $err;
                    OtxPulseStagingStore::saveManifest($runId, $manifest, $this->kind);
                    throw new Exception($manifest['last_error']);
                }

                $payload = json_decode($resp['result'], true);
                if (!is_array($payload)) {
                    throw new Exception("Invalid JSON on list page {$passName}/{$requestedPage}");
                }
                if ($passName === 'newest' && ($page === 1 || empty($manifest['api_total']))) {
                    $manifest['api_total'] = $payload['count'] ?? 0;
                }

                OtxPulseStagingStore::writeJson(
                    OtxPulseStagingStore::runPath($runId, $this->kind) . '/list/' . $prefix . 'page-' . str_pad((string) $requestedPage, 4, '0', STR_PAD_LEFT) . '.json',
                    $payload
                );

                $stop = false;
                foreach ($payload['results'] ?? [] as $item) {
                    $id = isset($item['id']) ? $item['id'] . '' : null;
                    if (!$id || isset($manifest['items'][$id])) {
                        continue;
                    }
                    if ($limit !== null && $this->countActionable($manifest) >= $limit) {
                        $stop = true;
                        break;
                    }

                    $manifest['items'][$id] = [
                        'status' => 'pending',
                        'type' => $item['type'] ?? '',
                        'indicator' => $item['indicator'] ?? '',
                        'name' => $item['name'] ?? ($item['title'] ?? ''),
                    ];
                    $dir = OtxPulseStagingStore::indicatorPath($runId, $id);
                    OtxPulseStagingStore::writeJson($dir . '/list_item.json', $item);
                }

                $manifest['pages_fetched'] = (int) ($manifest['pages_fetched'] ?? 0) + 1;
                $manifest['checkpoint'][$checkpointPageKey] = $requestedPage;
                $nextCandidate = $payload['next'] ?? null;
                if ($nextCandidate && $this->urlPageNumber($nextCandidate) > 100) {
                    $manifest['checkpoint']['list_pass_' . $passName . '_capped'] = true;
                    $nextCandidate = null;
                }
                $manifest['checkpoint'][$checkpointUrlKey] = $nextCandidate;
                OtxPulseStagingStore::saveManifest($runId, $manifest, $this->kind);

                if ($stop || ($limit !== null && $this->countActionable($manifest) >= $limit)) {
                    $this->info("Limit reached ({$limit}). Stopping list fetch.");
                    $manifest['checkpoint']['list_done'] = true;
                    $manifest['checkpoint'][$doneKey] = true;
                    $manifest['checkpoint'][$checkpointUrlKey] = null;
                    OtxPulseStagingStore::saveManifest($runId, $manifest, $this->kind);
                    return;
                }
                $nextUrl = $nextCandidate;
            }

            $manifest['checkpoint'][$doneKey] = true;
            $manifest['checkpoint'][$checkpointUrlKey] = null;
            OtxPulseStagingStore::saveManifest($runId, $manifest, $this->kind);

            $collected = count($manifest['items'] ?? []);
            $apiTotal = (int) ($manifest['api_total'] ?? 0);
            // Oldest pass only needed when newest could not cover API total.
            if ($passName === 'newest' && ($limit !== null || ($apiTotal > 0 && $collected >= $apiTotal))) {
                $this->info("Newest pass covered all listed indicators ({$collected}/{$apiTotal}).");
                $manifest['checkpoint']['list_pass_oldest_done'] = true;
                break;
            }
            if ($passName === 'newest' && empty($manifest['checkpoint']['list_pass_newest_capped']) && $apiTotal <= 10000) {
                // Finished newest without cap and total fits in 100 pages.
                $manifest['checkpoint']['list_pass_oldest_done'] = true;
                break;
            }
        }

        $collected = count($manifest['items'] ?? []);
        $apiTotal = (int) ($manifest['api_total'] ?? 0);
        $manifest['checkpoint']['list_done'] = true;
        $manifest['checkpoint']['list_next_url'] = null;
        if ($apiTotal > 0 && $collected < $apiTotal && $limit === null) {
            // Extremely large window (>~20k): still partial after both passes.
            $this->warn("List incomplete after newest+oldest passes: {$collected}/{$apiTotal}. Covered by dual sort window.");
            $manifest['checkpoint']['list_done'] = true; // proceed with what we have; details still useful
        }
        OtxPulseStagingStore::saveManifest($runId, $manifest, $this->kind);
    }

    protected function urlPageNumber($url)
    {
        if (!$url || !is_string($url)) {
            return 0;
        }
        $parts = parse_url($url);
        if (empty($parts['query'])) {
            return 0;
        }
        parse_str($parts['query'], $q);
        return isset($q['page']) ? (int) $q['page'] : 0;
    }

    protected function fetchDetails($runId, array &$manifest, $limit)
    {
        $done = 0;
        foreach ($manifest['items'] as $id => $meta) {
            $status = $meta['status'] ?? 'pending';
            if (!in_array($status, ['pending', 'failed'], true)) {
                continue;
            }
            if ($limit !== null && $done >= $limit) {
                break;
            }

            $type = $meta['type'] ?? '';
            $name = $meta['indicator'] ?? '';
            if ($status === 'failed') {
                $this->info("Retry detail: {$id} | {$type} | {$name}");
            } else {
                $this->info("Detail: {$id} | {$type} | {$name}");
            }

            $t0 = microtime(true);
            $bundle = $this->buildDetailBundle($id, $type, $name);
            $this->info(sprintf('  detail done in %.1fs%s', microtime(true) - $t0, !empty($bundle['failed']) ? ' (failed)' : ''));
            $dir = OtxPulseStagingStore::indicatorPath($runId, $id);
            OtxPulseStagingStore::writeJson($dir . '/detail_bundle.json', $bundle);

            if (!empty($bundle['failed'])) {
                $manifest['items'][$id]['status'] = 'failed';
                $manifest['items'][$id]['error'] = $bundle['error'] ?? 'detail failed';
            } elseif (!empty($bundle['skipped_fresh'])) {
                $manifest['items'][$id]['status'] = 'skipped';
                $manifest['items'][$id]['skip_reason'] = 'detail fresh (<=5d)';
            } else {
                $manifest['items'][$id]['status'] = 'fetched';
            }
            $done++;
            OtxPulseStagingStore::saveManifest($runId, $manifest, $this->kind);
        }
    }

    /**
     * Port of OTXMDFeedIndicator::caseByType — fetch only, write bundle (no DB write).
     */
    protected function buildDetailBundle($indicatorID, $type, $indicatorName)
    {
        $bundle = [
            'indicator_id' => $indicatorID . '',
            'type' => $type,
            'indicator_name' => $indicatorName,
            'allrow' => new \stdClass(),
            'description_key' => null,
            'description_value' => null,
            'pulses' => [],
            'no_detail' => false,
            'skipped_fresh' => false,
            'failed' => false,
            'error' => null,
        ];

        // Types with no useful detail (same as original).
        $noDetail = [
            'CIDR', 'FileHash-IMPHASH', 'FileHash-PEHASH', 'FilePath', 'Mutex', 'URI',
            'JA3', 'osquery', 'SSLCertFingerprint', 'BitcoinAddress',
        ];
        if (in_array($type, $noDetail, true)) {
            $bundle['no_detail'] = true;
            return $this->stringifyAllrow($bundle);
        }

        // Fresh detail skip (same 5-day rule).
        try {
            $clientMD = new \MongoDB\Client(env('DB_MONGO_STOREDATA', ''));
            $doc = $clientMD->{$this->dbName}->fx_otx_indicator_detail->findOne(
                ['indicator_id' => $indicatorID . ''],
                ['projection' => ['updated_at' => 1, 'transcation_id' => 1]]
            );
            if (!empty($doc) && !empty($doc->updated_at)) {
                $date1 = $doc->updated_at->toDateTime();
                $diff = date_diff($date1, date_create(date('Y-m-d H:i:s')));
                if ($doc->transcation_id != null && (int) $diff->format('%a') <= 5) {
                    $bundle['skipped_fresh'] = true;
                    return $this->stringifyAllrow($bundle);
                }
            }
        } catch (Exception $e) {
            // continue fetch
        }

        try {
            if ($type === 'CVE') {
                $r = $this->http->get('https://otx.alienvault.com/otxapi/indicators/cve/general/' . rawurlencode($indicatorName));
                if (!$r['success']) {
                    return $this->failBundle($bundle, $r['error']);
                }
                $d = json_decode($r['result'], true) ?: [];
                $bundle['allrow'] = [
                    'description' => $d['description'] ?? '',
                    'CWE' => $d['cwe'] ?? '',
                    'CVE' => $d['cve'] ?? '',
                    'CREATION DATE' => $d['date_created'] ?? '',
                    'LAST MODIFIED DATE' => $d['date_modified'] ?? '',
                ];
                $bundle['pulses'] = $d['pulse_info']['pulses'] ?? [];
            } elseif ($type === 'domain') {
                $r1 = $this->http->get('https://otx.alienvault.com/otxapi/indicators/domain/general/' . rawurlencode($indicatorName));
                $r2 = $this->http->get('https://otx.alienvault.com/otxapi/indicators/domain/url_list/' . rawurlencode($indicatorName));
                if (!$r2['success']) {
                    return $this->failBundle($bundle, $r2['error']);
                }
                $d1 = json_decode($r1['result'] ?? '[]', true) ?: [];
                $d2 = json_decode($r2['result'], true) ?: [];
                $bundle['allrow'] = [
                    'IP ADDRESS' => $d2['url_list'][0]['result']['urlworker']['ip'] ?? '',
                ];
                $bundle['pulses'] = $d1['pulse_info']['pulses'] ?? [];
            } elseif ($type === 'email') {
                $r1 = $this->http->get('https://otx.alienvault.com/otxapi/indicators/email/general/' . rawurlencode($indicatorName));
                if (!$r1['success']) {
                    return $this->failBundle($bundle, $r1['error']);
                }
                $d1 = json_decode($r1['result'], true) ?: [];
                $bundle['allrow'] = [];
                $bundle['pulses'] = $d1['pulse_info']['pulses'] ?? [];
            } elseif (in_array($type, ['FileHash-MD5', 'FileHash-SHA1', 'FileHash-SHA256'], true)) {
                $r1 = $this->http->get('https://otx.alienvault.com/otxapi/indicator/file/general/' . rawurlencode($indicatorName));
                $r2 = $this->http->get('https://otx.alienvault.com/otxapi/indicator/file/analysis/' . rawurlencode($indicatorName));
                if (!$r2['success']) {
                    return $this->failBundle($bundle, $r2['error']);
                }
                $d1 = json_decode($r1['result'] ?? '[]', true) ?: [];
                $d2 = json_decode($r2['result'], true) ?: [];
                $bundle['allrow'] = $this->mapFileAnalysis($d2);
                $bundle['pulses'] = $d1['pulse_info']['pulses'] ?? [];
            } elseif ($type === 'hostname') {
                $r1 = $this->http->get('https://otx.alienvault.com/otxapi/indicator/hostname/general/' . rawurlencode($indicatorName));
                $r2 = $this->http->get('https://otx.alienvault.com/otxapi/indicator/hostname/url_list/' . rawurlencode($indicatorName));
                if (!$r2['success']) {
                    return $this->failBundle($bundle, $r2['error']);
                }
                $d1 = json_decode($r1['result'] ?? '[]', true) ?: [];
                $d2 = json_decode($r2['result'], true) ?: [];
                $bundle['allrow'] = [
                    'IP ADDRESS' => $d2['url_list'][0]['result']['urlworker']['ip'] ?? '',
                    'DOMAIN' => $d2['url_list'][0]['domain'] ?? '',
                ];
                $bundle['pulses'] = $d1['pulse_info']['pulses'] ?? [];
            } elseif ($type === 'IPv4' || $type === 'IPv6') {
                $r1 = $this->http->get('https://otx.alienvault.com/otxapi/indicator/' . $type . '/general/' . rawurlencode($indicatorName));
                $r2 = $this->http->get('https://otx.alienvault.com/otxapi/indicator/' . $type . '/geo/' . rawurlencode($indicatorName));
                if (!$r2['success']) {
                    return $this->failBundle($bundle, $r2['error']);
                }
                $d1 = json_decode($r1['result'] ?? '[]', true) ?: [];
                $d2 = json_decode($r2['result'], true) ?: [];
                $location = (isset($d2['city']) ? $d2['city'] . ', ' : '')
                    . ($d2['country_name'] ?? '')
                    . (isset($d2['country_code']) ? ' --' . $d2['country_code'] : '');
                $bundle['allrow'] = [
                    'LOCATION' => $location,
                    'ASN/OWNER' => $d2['asn'] ?? '',
                ];
                $bundle['pulses'] = $d1['pulse_info']['pulses'] ?? [];
            } elseif ($type === 'NIDS') {
                $r1 = $this->http->get('https://otx.alienvault.com/otxapi/indicator/nids/general/' . rawurlencode($indicatorName));
                if (!$r1['success']) {
                    return $this->failBundle($bundle, $r1['error']);
                }
                $d1 = json_decode($r1['result'], true) ?: [];
                $bundle['allrow'] = [
                    'CATEGORY' => $d1['category'] ?? '',
                    'SUBCATION' => $d1['subcategory'] ?? '',
                    'ACTIVITY' => $d1['event_activity'] ?? '',
                    'MALWARE NAME' => $d1['malware_name'] ?? '',
                ];
                $bundle['description_key'] = 'rowDescription';
                $bundle['description_value'] = $d1['base_indicator']['description'] ?? '';
                $bundle['pulses'] = $d1['pulse_info']['pulses'] ?? [];
            } elseif ($type === 'URL') {
                $r1 = $this->http->get('https://otx.alienvault.com/otxapi/indicator/url/general/' . rawurlencode($indicatorName));
                $r2 = $this->http->get('https://otx.alienvault.com/otxapi/indicator/url/url_list/' . rawurlencode($indicatorName) . '?limit=10&page=1');
                if (!$r2['success']) {
                    return $this->failBundle($bundle, $r2['error']);
                }
                $d1 = json_decode($r1['result'] ?? '[]', true) ?: [];
                $d2 = json_decode($r2['result'], true) ?: [];
                $location = (isset($d2['city']) ? $d2['city'] . ', ' : '')
                    . ($d2['country_name'] ?? '')
                    . (isset($d2['country_code']) ? ' --' . $d2['country_code'] : '');
                $bundle['allrow'] = [
                    'IP ADDRESS' => $d2['url_list'][0]['result']['urlworker']['ip'] ?? '',
                    'LOCATION' => $location,
                    'HOSTNAME' => $d1['hostname'] ?? '',
                    'DOMAIN' => $d1['domain'] ?? '',
                    'LAST ANALYZED DATE' => $d2['url_list'][0]['date'] ?? '',
                ];
                $bundle['pulses'] = $d1['pulse_info']['pulses'] ?? [];
            } elseif ($type === 'YARA') {
                $r1 = $this->http->get('https://otx.alienvault.com/otxapi/indicator/yara/general/' . rawurlencode($indicatorName));
                $r2 = $this->http->get('https://otx.alienvault.com/otxapi/indicator/yara/raw/' . rawurlencode($indicatorName));
                if (!$r2['success']) {
                    return $this->failBundle($bundle, $r2['error']);
                }
                $d1 = json_decode($r1['result'] ?? '[]', true) ?: [];
                $bundle['allrow'] = [];
                $bundle['description_key'] = 'ruleRow';
                $bundle['description_value'] = (string) ($r2['result'] ?? '');
                $bundle['pulses'] = $d1['pulse_info']['pulses'] ?? [];
            } else {
                $bundle['no_detail'] = true;
            }
        } catch (Exception $e) {
            return $this->failBundle($bundle, $e->getMessage());
        }

        return $this->stringifyAllrow($bundle);
    }

    protected function mapFileAnalysis(array $d2)
    {
        if (($d2['analysis'] ?? null) === null) {
            return [];
        }
        $a = $d2['analysis'];
        $External_Hosts = implode(', ', array_column($a['plugins']['cuckoo']['result']['network']['tcp'] ?? [], 'dst'));
        $External_Domains = implode(', ', array_column($a['plugins']['cuckoo']['result']['network']['domains'] ?? [], 'domain'));
        $File_Type = (!empty($a['info']['results']['file_class']) ? $a['info']['results']['file_class'] . ' - ' : '')
            . ($a['info']['results']['file_type'] ?? '');
        $Antivirus_Detection = $a['plugins']['msdefender']['results']['detection'] ?? '';
        if ($Antivirus_Detection === '') {
            $Antivirus_Detection = $a['plugins']['avast']['results']['detection'] ?? '';
        }
        return [
            'Analysis Date' => $a['datetime_int'] ?? '',
            'Score' => $a['plugins']['cuckoo']['result']['info']['combined_score'] ?? '',
            'Antivirus Detection' => $Antivirus_Detection,
            'External Hosts' => $External_Hosts,
            'External Domains' => $External_Domains,
            'File Type' => $File_Type,
            'Size' => $a['info']['results']['filesize'] ?? '',
            'MD5' => $a['info']['results']['md5'] ?? '',
            'SHA1' => $a['info']['results']['sha1'] ?? '',
            'SHA256' => $a['info']['results']['sha256'] ?? '',
            'IMPHASH' => $a['plugins']['pe32info']['results']['imphash'] ?? '',
            'PEHASH' => $a['plugins']['pe32info']['results']['pehash'] ?? '',
            'RichHash' => $a['plugins']['pe32info']['results']['richhash'] ?? '',
        ];
    }

    protected function failBundle(array $bundle, $error)
    {
        $bundle['failed'] = true;
        $bundle['error'] = $error;
        return $this->stringifyAllrow($bundle);
    }

    protected function stringifyAllrow(array $bundle)
    {
        // Keep allrow JSON-serializable (object becomes {}).
        if ($bundle['allrow'] instanceof \stdClass) {
            $bundle['allrow'] = (array) $bundle['allrow'];
        }
        return $bundle;
    }

    protected function runImport($runId, array $manifest)
    {
        if ($this->option('skip-import')) {
            $this->info('Next: resume import later (or re-run without --skip-import)');
            return 0;
        }

        $this->info('=========================================');
        $this->info('AUTO IMPORT indicators: ' . $runId);
        $this->info('=========================================');

        $DB_MONGO_KEY = env('DB_MONGO_STOREDATA', '');
        if ($DB_MONGO_KEY === '') {
            $this->error('DB_MONGO_STOREDATA empty');
            return 1;
        }

        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $db = $clientMD->{$this->dbName};
        $date_now = new UTCDateTime(strtotime(date('Y-m-d H:i:s')) * 1000);

        $stampCol = $db->fx_transaction_otx_indicator_stamp;
        OtxPulseStagingStore::stampImportStart($manifest);
        OtxPulseStagingStore::saveManifest($runId, $manifest, $this->kind);

        $importStartedIso = date('c');
        $stamp = $stampCol->insertOne([
            'code' => generator_uuid(),
            'transaction_date' => date('Y-m-d'),
            'status' => 1,
            'created_at' => $date_now,
            'created_by' => 'system',
            'updated_at' => $date_now,
            'updated_by' => 'system',
            'deleted_at' => null,
            'source' => 'otx.alienvault',
            'creator_org' => 'OTX',
            'staging_run_id' => $runId,
            'pipeline' => 'staging_import',
            'started_at' => $date_now,
            'started_at_iso' => $importStartedIso,
            'fetch_started_at' => $manifest['started_at'] ?? null,
            'api_total_indicators' => (int) ($manifest['api_total'] ?? 0),
            'limit' => $manifest['limit'] ?? null,
            'save_intended' => (int) (($manifest['stats']['expected'] ?? 0) ?: count($manifest['items'] ?? [])),
            'save_actual' => 0,
            'complete' => false,
        ]);
        $stampId = $stamp->getInsertedId();

        $saved = 0;
        $skipped = 0;
        $errors = 0;
        $listCol = $db->fx_transaction_otx_indicators_data;

        foreach ($manifest['items'] ?? [] as $id => $meta) {
            $status = $meta['status'] ?? '';
            if ($status === 'failed') {
                $errors++;
                continue;
            }
            if ($status === 'skipped') {
                $skipped++;
                // still upsert list row if present
            }

            $dir = OtxPulseStagingStore::indicatorPath($runId, $id);
            $listItem = OtxPulseStagingStore::readJson($dir . '/list_item.json');
            $bundle = OtxPulseStagingStore::readJson($dir . '/detail_bundle.json');

            if (!$listItem) {
                $errors++;
                continue;
            }

            try {
                $listCol->updateOne(
                    ['indicator_id' => $id],
                    [
                        '$set' => [
                            'indicator' => $listItem['indicator'] ?? '',
                            'type' => $listItem['type'] ?? '',
                            'tile' => $listItem['title'] ?? '',
                            'desciption' => $listItem['description'] ?? '',
                            'slug' => $listItem['slug'] ?? '',
                            'name' => $listItem['name'] ?? '',
                            'updated_at' => $date_now,
                            'updated_by' => 'system',
                            'creator_org' => 'OTX',
                            'transcation_id' => $stampId,
                        ],
                        '$setOnInsert' => [
                            'status' => 1,
                            'created_at' => $date_now,
                            'created_by' => 'system',
                            'deleted_at' => null,
                            'transaction_date' => date('Y-m-d'),
                            'source' => 'otx.alienvault',
                        ],
                    ],
                    ['upsert' => true]
                );

                if ($bundle && empty($bundle['skipped_fresh']) && empty($bundle['failed'])) {
                    $this->importDetailBundle($db, $bundle, $date_now);
                }
                if ($status === 'fetched' || $status === 'skipped') {
                    $saved++;
                }
                $this->info("Import IOC: {$id} | " . ($listItem['type'] ?? '') . ' | ' . ($listItem['indicator'] ?? ''));
            } catch (Exception $e) {
                $errors++;
                $this->error("Import fail {$id}: " . $e->getMessage());
            }
        }

        $expected = $saved + $errors;
        // count skipped in expected for %
        $verified = $saved + $errors;
        $fromManifest = 0;
        foreach ($manifest['items'] ?? [] as $meta) {
            if (in_array($meta['status'] ?? '', ['fetched', 'skipped', 'failed'], true)) {
                $fromManifest++;
            }
        }
        $expected = max($fromManifest, $verified);
        $pct = $expected > 0 ? round(($verified / $expected) * 100, 2) : 0;
        $complete = $errors === 0 && $verified >= $expected;

        $apiTotal = (int) ($manifest['api_total'] ?? 0);
        $limit = array_key_exists('limit', $manifest) ? $manifest['limit'] : null;
        $saveIntended = $expected;
        $saveActual = $saved;

        $finishedIso = date('c');
        $finishedAt = new UTCDateTime(strtotime($finishedIso) * 1000);
        $stampCol->updateOne(
            ['_id' => $stampId],
            ['$set' => [
                'status' => $complete ? 2 : 3,
                'audit_note' => OtxPulseStagingStore::AUDIT_NOTE,
                'audit' => OtxPulseStagingStore::mongoAudit($manifest, [
                    'api_total' => $apiTotal,
                    'limit' => $limit,
                    'intended' => $saveIntended,
                    'saved' => $saveActual,
                    'skipped' => $skipped,
                    'failed' => $errors,
                ]),
                'api_total_indicators' => $apiTotal,
                'limit' => $limit,
                'save_intended' => $saveIntended,
                'save_actual' => $saveActual,
                'intended_indicators' => $saveIntended,
                'saved_indicators' => $saveActual,
                'processed_indicators' => $saveActual,
                'skipped_indicators' => $skipped,
                'failed_indicators' => $errors,
                'staging_run_id' => $runId,
                'finished_at' => $finishedAt,
                'finished_at_iso' => $finishedIso,
                'complete' => $complete,
                'duration_seconds' => max(0, strtotime($finishedIso) - strtotime($importStartedIso)),
                'updated_at' => $finishedAt,
            ]]
        );

        $manifest['import_status'] = $complete ? 'done' : 'partial';
        OtxPulseStagingStore::stampImportFinish($manifest, $complete);
        OtxPulseStagingStore::saveManifest($runId, $manifest, $this->kind);

        $this->info('=========================================');
        $this->info('INDICATOR IMPORT SUMMARY → ' . $this->dbName);
        $this->info('=========================================');
        $this->info('API total (OTX catalog, not this run) : ' . number_format($apiTotal));
        $this->info('Save intended (this run)              : ' . number_format($saveIntended) . ($limit !== null ? " (limit={$limit})" : ''));
        $this->info('Save actual                           : ' . number_format($saveActual));
        $this->info('Skipped (fresh)                       : ' . $skipped);
        $this->info('Failed                                : ' . $errors);
        $this->info('Verified                              : ' . $verified . ' / ' . $expected . " ({$pct}%)");
        $this->info('Complete             : ' . ($complete ? 'YES' : 'NO'));
        $this->info('Import started at    : ' . $importStartedIso);
        $this->info('Import finished at   : ' . $finishedIso);

        Artisan::call('app:MDCountIndicator', [], $this->output);
        $this->output->write(Artisan::output());

        if ($complete && !$this->option('skip-import')) {
            // always delete on complete (no --keep on this command)
            if (OtxPulseStagingStore::deleteRun($runId, $this->kind)) {
                $this->info('Staging deleted     : ' . $runId);
            }
        } elseif (!$complete) {
            $this->warn('Staging kept (partial): ' . OtxPulseStagingStore::runPath($runId, $this->kind));
        }

        return $complete ? 0 : 2;
    }

    protected function importDetailBundle($db, array $bundle, UTCDateTime $date_now)
    {
        if (!empty($bundle['no_detail'])) {
            return;
        }

        $id = $bundle['indicator_id'] . '';
        $allrow = $bundle['allrow'] ?? [];
        if (is_array($allrow)) {
            $allrow = (object) $allrow;
        }

        $set = [
            'indicator_name' => $bundle['indicator_name'] ?? '',
            'type' => $bundle['type'] ?? '',
            'allrow' => $allrow,
            'updated_at' => $date_now,
            'updated_by' => 'system',
            'creator_org' => 'OTX',
        ];
        if (!empty($bundle['description_key'])) {
            $set[$bundle['description_key']] = $bundle['description_value'] ?? '';
        }

        $db->fx_otx_indicator_detail->updateOne(
            ['indicator_id' => $id],
            [
                '$set' => $set,
                '$setOnInsert' => [
                    'status' => 1,
                    'created_at' => $date_now,
                    'created_by' => 'system',
                    'deleted_at' => null,
                    'transaction_date' => date('Y-m-d'),
                    'source' => 'otx.alienvault',
                ],
            ],
            ['upsert' => true]
        );

        foreach ($bundle['pulses'] ?? [] as $value) {
            if (empty($value['id'])) {
                continue;
            }
            $references = implode(', ', $value['references'] ?? []);
            $tags = implode(', ', $value['tags'] ?? []);
            $industries = implode(', ', $value['industries'] ?? []);
            $malware_families = implode(', ', array_column($value['malware_families'] ?? [], 'display_name'));

            $db->fx_otx_events->updateOne(
                ['pulse_id' => $value['id']],
                [
                    '$set' => [
                        'name' => $value['name'] ?? '',
                        'description' => $value['description'] ?? '',
                        'modified' => isset($value['modified']) ? new UTCDateTime(strtotime($value['modified']) * 1000) : null,
                        'created' => isset($value['created']) ? new UTCDateTime(strtotime($value['created']) * 1000) : null,
                        'public' => $value['public'] ?? '',
                        'TLP' => $value['TLP'] ?? '',
                        'is_modified' => $value['is_modified'] ?? '',
                        'references' => $references,
                        'tags' => $tags,
                        'industries' => $industries,
                        'malware_families' => $malware_families,
                        'author_username' => $value['author']['username'] ?? '',
                        'updated_at' => $date_now,
                        'updated_by' => 'system',
                        'creator_org' => 'OTX',
                    ],
                    '$setOnInsert' => [
                        'indicator_type_counts' => [],
                        'indicator_count' => 0,
                        'groups' => '',
                        'transcation_id' => null,
                        'status' => 1,
                        'created_at' => $date_now,
                        'created_by' => 'system',
                        'deleted_at' => null,
                        'transaction_date' => date('Y-m-d'),
                        'count_view' => 0,
                        'source' => 'otx.alienvault',
                    ],
                ],
                ['upsert' => true]
            );

            $db->fx_otx_events_indicator_ref->updateOne(
                [
                    'indicator_id' => $id,
                    'pulse_id' => $value['id'],
                ],
                [
                    '$set' => [
                        'pulse_modified' => isset($value['modified']) ? new UTCDateTime(strtotime($value['modified']) * 1000) : null,
                        'updated_at' => $date_now,
                        'updated_by' => 'system',
                    ],
                    '$setOnInsert' => [
                        'role' => null,
                        'created' => null,
                        'expiration' => null,
                        'is_active' => null,
                        'status' => 1,
                        'created_at' => $date_now,
                        'created_by' => 'system',
                        'deleted_at' => null,
                        'transaction_date' => date('Y-m-d'),
                        'source' => 'otx.alienvault',
                        'indicator' => $bundle['indicator_name'] ?? '',
                        'type' => $bundle['type'] ?? '',
                    ],
                ],
                ['upsert' => true]
            );
        }
    }

    protected function countActionable(array $manifest)
    {
        $n = 0;
        foreach ($manifest['items'] ?? [] as $meta) {
            if (in_array($meta['status'] ?? '', ['pending', 'fetched', 'skipped', 'failed'], true)) {
                $n++;
            }
        }
        return $n;
    }
}
