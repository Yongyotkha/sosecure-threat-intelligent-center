<?php

namespace App\Console\Commands;

use App\Services\OtxHeavyPulseQueue;
use App\Services\OtxHttpClient;
use App\Services\OtxPulseStagingStore;
use Artisan;
use Exception;
use Illuminate\Console\Command;

class OTXMDFetchPulse extends Command
{
    protected $signature = 'app:OTXMDFetchPulse
                            {--limit= : Limit number of pulses to fetch (for testing)}
                            {--max-indicators=5000 : Skip pulses with indicator_count above this (0 = no skip)}
                            {--resume : Resume leftover run (default: auto if leftover exists)}
                            {--fresh : Ignore leftover staging and start a new run}
                            {--run= : Resume a specific run_id}
                            {--timeout=180 : HTTP timeout seconds}
                            {--retries=5 : Max HTTP retries per URL}
                            {--database=sosecure_threatintelligent : Mongo database for auto-import}
                            {--skip-import : Fetch only; do not auto-import to Mongo}';

    protected $description = 'Fetch OTX pulses into staging (finish leftover first), then auto-import unless --skip-import';

    /** @var OtxHttpClient */
    protected $http;

    public function handle()
    {
        $lock = OtxPulseStagingStore::acquireLock('pulses');
        if (!$lock) {
            $this->warn('Another pulse fetch is already running. Leftover will be finished on the next run.');
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

        $query = 'modified:<12h';
        $explicitRun = $this->option('run');
        $fresh = (bool) $this->option('fresh');
        $limit = $this->option('limit') !== null && $this->option('limit') !== ''
            ? (int) $this->option('limit')
            : null;

        if ($explicitRun) {
            return $this->processPulseRun($explicitRun, true, $query, $limit);
        }

        if (!$fresh) {
            $leftover = OtxPulseStagingStore::findIncompleteRunId('pulses');
            if ($leftover) {
                $this->info("Leftover staging found — finishing before a new window: {$leftover}");
                $exit = $this->processPulseRun($leftover, true, $query, $limit);
                $still = OtxPulseStagingStore::loadManifest($leftover);
                if ($still && OtxPulseStagingStore::isIncomplete($still)) {
                    $this->warn('Leftover not complete — skip new window so data is not abandoned.');
                    return $exit;
                }
                $this->info('Leftover finished. Starting new modified:<12h window...');
            }
        } else {
            $this->warn('--fresh: ignoring leftover staging (may leave disk garbage / missed import).');
        }

        return $this->processPulseRun(null, false, $query, $limit);
    }

    /**
     * @param string|null $runId existing run, or null to create
     */
    protected function processPulseRun($runId, $isResume, $query, $limit)
    {
        if ($runId) {
            $manifest = OtxPulseStagingStore::loadManifest($runId);
            if (!$manifest) {
                $this->error("Run not found: {$runId}");
                return 1;
            }
            OtxPulseStagingStore::stampSessionStart($manifest, $isResume);
            OtxPulseStagingStore::saveManifest($runId, $manifest);
            $this->info("Resuming run: {$runId} (resume #" . (int) ($manifest['resume_count'] ?? 0) . ')');
        } else {
            $runId = OtxPulseStagingStore::createRun($query, 'pulses', $limit);
            $manifest = OtxPulseStagingStore::loadManifest($runId);
            OtxPulseStagingStore::stampSessionStart($manifest, false);
            OtxPulseStagingStore::saveManifest($runId, $manifest);
            $this->info("Created run: {$runId}");
        }
        $this->info('Started at          : ' . ($manifest['started_at'] ?? '-'));

        $maxIndicators = (int) $this->option('max-indicators');

        try {
            $this->fetchListPages($runId, $manifest, $query, $limit, $maxIndicators);
            $this->fetchPulseDetails($runId, $manifest, $limit);
        } catch (Exception $e) {
            $manifest['status'] = 'partial';
            $manifest['last_error'] = $e->getMessage();
            $manifest['complete'] = false;
            OtxPulseStagingStore::saveManifest($runId, $manifest);
            $this->error('Fetch aborted: ' . $e->getMessage());
            $this->warn('Staging kept for resume: ' . OtxPulseStagingStore::runPath($runId));
            return 1;
        }

        // Recount from pulse statuses so resume/retry does not leave stale failed counters.
        $fetched = 0;
        $skipped = 0;
        $failed = 0;
        $pending = 0;
        foreach ($manifest['pulses'] ?? [] as $meta) {
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
        $manifest['pulses_fetched'] = $fetched;
        $manifest['pulses_skipped'] = $skipped;
        $manifest['pulses_failed'] = $failed;

        $apiTotal = (int) ($manifest['api_total_pulses'] ?? 0);
        $inScope = $fetched + $skipped + $failed + $pending;
        $verified = $fetched + $skipped + $failed;

        // Limited / partial list runs: expect = pulses registered in this run, not full API total.
        $isPartialList = !empty($manifest['checkpoint']['list_done']) && $inScope < $apiTotal;
        if ($limit !== null) {
            $expected = max($inScope, $limit);
        } elseif ($isPartialList || $inScope > 0 && $inScope < $apiTotal) {
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
            'percent' => $pct,
            'complete' => $fetchComplete,
        ];
        OtxPulseStagingStore::stampFetchFinish($manifest, $fetchComplete);
        OtxPulseStagingStore::saveManifest($runId, $manifest);

        $this->info('=========================================');
        $this->info('FETCH SUMMARY');
        $this->info('=========================================');
        $this->info("Run ID              : {$runId}");
        $this->info('Started at          : ' . ($manifest['started_at'] ?? '-'));
        $this->info('Last started        : ' . ($manifest['last_started_at'] ?? '-'));
        $this->info('Fetch finished at   : ' . ($manifest['fetch_finished_at'] ?? '-'));
        $this->info('Resume count        : ' . (int) ($manifest['resume_count'] ?? 0));
        $this->info('API total (OTX catalog, not this run) : ' . number_format($apiTotal));
        $this->info('Save intended (will import)           : ' . number_format($fetched) . ($limit !== null ? " (limit={$limit})" : ($isPartialList ? ' (scoped run)' : '')));
        $this->info('Fetched to staging                    : ' . number_format($fetched));
        $this->info('Skipped (filter)    : ' . number_format($skipped));
        $this->info('Failed              : ' . number_format($failed));
        $this->info('Pending             : ' . number_format($pending));
        $this->info('Verified            : ' . number_format($verified) . ' / ' . number_format($expected) . " ({$pct}%)");
        $this->info('Fetch complete      : ' . ($fetchComplete ? 'YES' : 'NO'));
        $this->info('Status              : ' . $manifest['status']);
        $this->info('Path                : ' . OtxPulseStagingStore::runPath($runId));
        $heavyPending = OtxHeavyPulseQueue::countByStatus('pending');
        if ($heavyPending > 0) {
            $this->info('Heavy queue pending : ' . $heavyPending);
            $this->info('Heavy next          : php artisan app:OTXMDFetchPulseHeavy --limit=1');
        }

        $fetchExit = ($failed > 0 || $pending > 0) ? 2 : 0;
        $importPending = ($manifest['import_status'] ?? 'pending') !== 'done';
        if ($fetched > 0 && $importPending) {
            $importExit = $this->runImportAfterFetch($runId);
            if ($importExit !== 0 && $fetchExit === 0) {
                $fetchExit = $importExit;
            }
        } elseif ($this->option('skip-import')) {
            $this->info('Next                : php artisan app:OTXMDImportPulse --run=' . $runId);
            $this->info('(auto-import skipped via --skip-import)');
        } else {
            $this->info('Skip auto-import    : no fetched pulses');
        }

        $after = OtxPulseStagingStore::loadManifest($runId);
        if ($after) {
            $pipelineComplete = empty($after['complete'])
                ? (($after['import_status'] ?? '') === 'done' && ($after['status'] ?? '') === 'fetched')
                : (bool) $after['complete'];
            if ($this->option('skip-import')) {
                $pipelineComplete = false;
            }
            OtxPulseStagingStore::stampFinish($after, $pipelineComplete);
            OtxPulseStagingStore::saveManifest($runId, $after);
            $this->info('Finished at         : ' . ($after['finished_at'] ?? '-'));
            $this->info('Pipeline complete   : ' . ($pipelineComplete ? 'YES' : 'NO'));
            $this->info('Duration            : ' . OtxPulseStagingStore::elapsed($after['started_at'] ?? null, $after['finished_at'] ?? null));
        } else {
            $this->info('Finished at         : ' . date('c') . ' (staging deleted after complete import)');
            $this->info('Pipeline complete   : YES');
            $this->info('Duration            : ' . OtxPulseStagingStore::elapsed($manifest['started_at'] ?? null));
        }

        return $fetchExit;
    }

    /**
     * Import this run immediately after fetch (default). Use --skip-import to disable.
     */
    protected function runImportAfterFetch($runId)
    {
        $dbName = $this->option('database') ?: 'sosecure_threatintelligent';

        if ($this->option('skip-import')) {
            $this->info('Next                : php artisan app:OTXMDImportPulse --run=' . $runId . ' --database=' . $dbName);
            $this->info('(auto-import skipped via --skip-import)');
            return 0;
        }

        $this->info('=========================================');
        $this->info('AUTO IMPORT after fetch: ' . $runId);
        $this->info('Mongo database        : ' . $dbName);
        $this->info('=========================================');

        // Pass $this->output so Import streams live (and nested MDCount does not
        // wipe Artisan::output() buffer — previously only END---- was visible).
        $exit = (int) Artisan::call('app:OTXMDImportPulse', [
            '--run' => $runId,
            '--database' => $dbName,
        ], $this->output);

        if ($exit === 0) {
            $this->info('AUTO IMPORT complete : OK');
        } else {
            $this->error('AUTO IMPORT complete : FAILED (exit=' . $exit . ')');
            $this->warn('Retry: php artisan app:OTXMDImportPulse --run=' . $runId);
        }

        return $exit;
    }

    protected function fetchListPages($runId, array &$manifest, $query, $limit, $maxIndicators)
    {
        if (!empty($manifest['checkpoint']['list_done'])) {
            $this->info('List pages already complete, skipping.');
            return;
        }

        $page = (int) ($manifest['checkpoint']['list_page'] ?? 0);
        $nextUrl = $manifest['checkpoint']['list_next_url'] ?? null;
        $pageSize = ($limit !== null && $limit > 0 && $limit < 100) ? max($limit, 10) : 100;

        if (!$nextUrl && $page === 0) {
            $nextUrl = 'https://otx.alienvault.com/otxapi/pulses/?limit=' . $pageSize
                . '&page=1&sort=-modified&q=' . rawurlencode($query);
        }

        while ($nextUrl) {
            $page++;
            $t0 = microtime(true);
            $this->info("Fetching list page {$page} ...");
            $resp = $this->http->get($nextUrl);
            $this->info(sprintf('  list page %d done in %.1fs', $page, microtime(true) - $t0));

            if (!$resp['success']) {
                $manifest['checkpoint']['list_next_url'] = $nextUrl;
                $manifest['checkpoint']['list_page'] = $page - 1;
                $manifest['status'] = 'partial';
                $manifest['last_error'] = 'List page failed: ' . ($resp['error'] ?? 'unknown');
                OtxPulseStagingStore::saveManifest($runId, $manifest);
                throw new Exception($manifest['last_error']);
            }

            $payload = json_decode($resp['result'], true);
            if (!is_array($payload)) {
                throw new Exception("Invalid JSON on list page {$page}");
            }

            if ($page === 1 || empty($manifest['api_total_pulses'])) {
                $manifest['api_total_pulses'] = $payload['count'] ?? 0;
            }

            OtxPulseStagingStore::writeJson(
                OtxPulseStagingStore::runPath($runId) . '/list/page-' . str_pad((string) $page, 4, '0', STR_PAD_LEFT) . '.json',
                $payload
            );

            $stopList = false;
            foreach ($payload['results'] ?? [] as $item) {
                $pulseId = $item['id'] ?? null;
                if (!$pulseId || isset($manifest['pulses'][$pulseId])) {
                    continue;
                }

                $indicatorCount = (int) ($item['indicator_count'] ?? 0);
                $isPublicDns = isset($item['name']) && strpos($item['name'], 'Public DNS') !== false;
                $isTooHeavy = $maxIndicators > 0 && $indicatorCount > $maxIndicators;

                if ($isPublicDns || $isTooHeavy) {
                    $reason = $isPublicDns
                        ? 'Public DNS'
                        : "indicator_count {$indicatorCount} > max-indicators {$maxIndicators}";
                    $manifest['pulses'][$pulseId] = [
                        'status' => 'skipped',
                        'skip_reason' => $reason,
                        'indicator_count' => $indicatorCount,
                        'name' => $item['name'] ?? '',
                        'modified' => $item['modified'] ?? null,
                        'created' => $item['created'] ?? null,
                    ];
                    $manifest['pulses_skipped'] = ($manifest['pulses_skipped'] ?? 0) + 1;
                    $this->warn("  skip {$pulseId}: {$reason}");

                    // Heavy pulses go to slow-lane queue (Public DNS stays skipped only).
                    if ($isTooHeavy && !$isPublicDns) {
                        if (OtxHeavyPulseQueue::enqueue($item, $runId, $reason)) {
                            $this->info("  → queued for heavy: {$pulseId}");
                        } else {
                            $q = OtxHeavyPulseQueue::get($pulseId);
                            $qStatus = $q['status'] ?? 'unknown';
                            $this->info("  → already in heavy queue ({$qStatus}): {$pulseId}");
                        }
                    }
                } else {
                    if ($limit !== null && $this->countPendingPulses($manifest) >= $limit) {
                        $stopList = true;
                        break;
                    }
                    $manifest['pulses'][$pulseId] = [
                        'status' => 'pending',
                        'skip_reason' => null,
                        'indicator_count' => $indicatorCount,
                        'name' => $item['name'] ?? '',
                        'modified' => $item['modified'] ?? null,
                        'created' => $item['created'] ?? null,
                    ];
                }

                $pulseDir = OtxPulseStagingStore::pulsePath($runId, $pulseId);
                OtxPulseStagingStore::writeJson($pulseDir . '/list_item.json', $item);
            }

            $manifest['pages_fetched'] = $page;
            $manifest['checkpoint']['list_page'] = $page;
            $manifest['checkpoint']['list_next_url'] = $payload['next'] ?? null;
            OtxPulseStagingStore::saveManifest($runId, $manifest);

            if ($stopList || ($limit !== null && $this->countPendingPulses($manifest) >= $limit)) {
                $this->info("Limit reached for pending pulses ({$limit}). Stopping list fetch.");
                break;
            }

            $nextUrl = $payload['next'] ?? null;
        }

        // Limited runs are complete for list purposes; full runs keep next URL only if unfinished.
        if ($limit !== null) {
            $manifest['checkpoint']['list_done'] = true;
            $manifest['checkpoint']['list_next_url'] = null;
        } else {
            $manifest['checkpoint']['list_done'] = empty($manifest['checkpoint']['list_next_url']);
            if ($manifest['checkpoint']['list_done']) {
                $manifest['checkpoint']['list_next_url'] = null;
            }
        }
        OtxPulseStagingStore::saveManifest($runId, $manifest);
    }

    protected function fetchPulseDetails($runId, array &$manifest, $limit)
    {
        $fetchedThisRun = 0;

        foreach ($manifest['pulses'] as $pulseId => $meta) {
            $status = $meta['status'] ?? 'pending';
            if ($status === 'skipped') {
                continue;
            }
            if ($status === 'fetched' && !$this->pulseNeedsRefetch($runId, $pulseId)) {
                continue;
            }
            if ($limit !== null && $fetchedThisRun >= $limit) {
                $this->info("Pulse detail limit reached ({$limit}).");
                break;
            }

            $name = $meta['name'] ?? '';
            $count = $meta['indicator_count'] ?? 0;
            if ($status === 'failed') {
                $this->info("Retrying failed pulse: {$pulseId} | indicators~{$count} | {$name}");
            } else {
                $this->info("Fetching pulse: {$pulseId} | indicators~{$count} | {$name}");
            }
            $ok = $this->fetchOnePulse($runId, $pulseId, $manifest);
            if ($ok) {
                $fetchedThisRun++;
                $manifest['pulses'][$pulseId]['status'] = 'fetched';
                unset($manifest['pulses'][$pulseId]['error']);
            } else {
                $manifest['pulses'][$pulseId]['status'] = 'failed';
            }
            OtxPulseStagingStore::saveManifest($runId, $manifest);
        }
    }

    protected function fetchOnePulse($runId, $pulseId, array &$manifest)
    {
        $pulseDir = OtxPulseStagingStore::pulsePath($runId, $pulseId);

        try {
            $detailPath = $pulseDir . '/detail.json';
            $detailCached = OtxPulseStagingStore::readJson($detailPath);
            if (!$detailCached || !empty($detailCached['fetch_failed'])) {
                $t0 = microtime(true);
                $this->info('  → detail ...');
                $detailResp = $this->http->get('https://otx.alienvault.com/otxapi/pulses/' . $pulseId . '/');
                $this->info(sprintf('  → detail done in %.1fs', microtime(true) - $t0));
                if (!$detailResp['success']) {
                    // Non-fatal: list_item.json is enough for import; groups may be empty.
                    $this->warn("  detail failed (non-fatal): {$pulseId} — saving empty detail");
                    $manifest['pulses'][$pulseId]['detail_warning'] = 'detail fetch failed: ' . ($detailResp['error'] ?? 'fail');
                    OtxPulseStagingStore::writeJson($detailPath, [
                        'groups' => [],
                        'fetch_failed' => true,
                    ]);
                } else {
                    unset($manifest['pulses'][$pulseId]['detail_warning']);
                    OtxPulseStagingStore::writeJson($detailPath, json_decode($detailResp['result'], true));
                }
            } else {
                $this->info('  → detail (cached)');
            }

            // Only indicators created today (same rule as OTXMDFeedPulse) — do NOT download entire history.
            $indPath = $pulseDir . '/indicators.json';
            $indCached = OtxPulseStagingStore::readJson($indPath);
            $indDone = $indCached && empty($indCached['fetch_failed']);
            if ($indDone) {
                $this->info('  → indicators (cached)');
            } else {
                $t0 = microtime(true);
                $seed = [];
                $seedCount = 0;
                $startUrl = 'https://otx.alienvault.com/otxapi/pulses/' . $pulseId . '/indicators/?sort=-created&limit=100&page=1';
                if ($indCached && !empty($indCached['results']) && !empty($indCached['next_url'])) {
                    $seed = $indCached['results'];
                    $seedCount = (int) ($indCached['count'] ?? 0);
                    $startUrl = $indCached['next_url'];
                    $this->info('  → indicators (resume today-only, already ' . count($seed) . ' rows) ...');
                } else {
                    $this->info('  → indicators (today only) ...');
                }
                $indicators = $this->fetchPagesUntil(
                    $startUrl,
                    function ($row) {
                        $created = $row['created'] ?? null;
                        if (!$created) {
                            return false;
                        }
                        return explode('T', $created)[0] === date('Y-m-d');
                    },
                    'indicators',
                    $seed,
                    $seedCount
                );
                if ($indicators === null) {
                    $this->warn("  indicators failed: {$pulseId} — no rows kept");
                    $indicators = ['count' => 0, 'results' => [], 'truncated_mode' => 'today_only', 'fetch_failed' => true];
                    $manifest['pulses'][$pulseId]['indicators_warning'] = 'indicators fetch failed';
                } elseif (!empty($indicators['fetch_failed'])) {
                    $n = count($indicators['results'] ?? []);
                    $this->warn("  indicators partial: {$pulseId} — kept {$n} row(s), will retry remaining pages on resume");
                    $manifest['pulses'][$pulseId]['indicators_warning'] = 'indicators partial after page failure';
                } else {
                    unset($manifest['pulses'][$pulseId]['indicators_warning']);
                }
                $this->info(sprintf(
                    '  → indicators done in %.1fs (%d rows)',
                    microtime(true) - $t0,
                    count($indicators['results'] ?? [])
                ));
                OtxPulseStagingStore::writeJson($indPath, $indicators);
            }

            // Related (today only). Non-fatal like original OTXMDFeedPulse — empty on failure.
            // Heavy queue can skip related (--skip-related) because OTX often 504s on large pulses.
            $relatedPath = $pulseDir . '/related.json';
            $relatedCached = OtxPulseStagingStore::readJson($relatedPath);
            if ($relatedCached && (empty($relatedCached['fetch_failed']) || !empty($relatedCached['skipped']))) {
                $this->info('  → related (cached)');
            } elseif (!file_exists($relatedPath) || !empty($relatedCached['fetch_failed'])) {
                $skipRelated = $this->hasOption('skip-related') && $this->option('skip-related');
                if ($skipRelated) {
                    $this->info('  → related skipped (--skip-related)');
                    OtxPulseStagingStore::writeJson($pulseDir . '/related.json', [
                        'count' => 0,
                        'results' => [],
                        'truncated_mode' => 'today_only',
                        'skipped' => true,
                    ]);
                } else {
                    $t0 = microtime(true);
                    $this->info('  → related (today only) ...');
                    $related = $this->fetchPagesUntil(
                        'https://otx.alienvault.com/otxapi/pulses/' . $pulseId . '/related?limit=100&sort=-modified',
                        function ($row) {
                            $modified = $row['modified'] ?? null;
                            if (!$modified) {
                                return false;
                            }
                            return explode('T', $modified)[0] === date('Y-m-d');
                        },
                        'related',
                        [],
                        0,
                        20,
                        1
                    );
                    if ($related === null) {
                        $this->warn("  related failed (non-fatal): {$pulseId} — saving empty related");
                        $related = ['count' => 0, 'results' => [], 'truncated_mode' => 'today_only', 'fetch_failed' => true];
                        $manifest['pulses'][$pulseId]['related_warning'] = 'related fetch failed';
                    } elseif (!empty($related['fetch_failed'])) {
                        $manifest['pulses'][$pulseId]['related_warning'] = 'related partial after page failure';
                    } else {
                        unset($manifest['pulses'][$pulseId]['related_warning']);
                    }
                    $this->info(sprintf(
                        '  → related done in %.1fs (%d rows)',
                        microtime(true) - $t0,
                        count($related['results'] ?? [])
                    ));
                    OtxPulseStagingStore::writeJson($pulseDir . '/related.json', $related);
                }
            } else {
                $this->info('  → related (cached)');
            }

            unset($manifest['pulses'][$pulseId]['error']);
            $this->info("  OK: {$pulseId}");
            return true;
        } catch (Exception $e) {
            $manifest['pulses'][$pulseId]['error'] = $e->getMessage();
            $this->error("  exception {$pulseId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Paginate while keepRow(row) is true. Stop at first row that fails the predicate
     * (API is sorted newest-first, same as original feed command).
     * If a later page fails, keep rows already fetched instead of discarding them.
     *
     * @return array|null
     */
    protected function fetchPagesUntil($startUrl, callable $keepRow, $label = 'page', array $seed = [], $seedCount = 0, $timeout = null, $retries = null)
    {
        $all = $seed;
        $count = $seedCount;
        $url = $startUrl;
        $page = 0;

        while ($url) {
            $page++;
            $t0 = microtime(true);
            $resp = $this->http->get($url, $timeout, $retries);
            if (!$resp['success']) {
                $this->error("  {$label} page {$page} failed: " . ($resp['error'] ?? ''));
                if (!empty($all)) {
                    $this->warn('  keeping ' . count($all) . " {$label} row(s) already fetched");
                    return [
                        'count' => $count,
                        'results' => $all,
                        'truncated_mode' => 'today_only',
                        'fetch_failed' => true,
                        'next_url' => $url,
                    ];
                }
                return null;
            }
            $payload = json_decode($resp['result'], true);
            if (!is_array($payload)) {
                if (!empty($all)) {
                    return [
                        'count' => $count,
                        'results' => $all,
                        'truncated_mode' => 'today_only',
                        'fetch_failed' => true,
                        'next_url' => $url,
                    ];
                }
                return null;
            }
            if ($page === 1 && $seedCount === 0) {
                $count = $payload['count'] ?? 0;
            }

            $stop = false;
            $kept = 0;
            foreach ($payload['results'] ?? [] as $row) {
                if (!$keepRow($row)) {
                    $stop = true;
                    break;
                }
                $all[] = $row;
                $kept++;
            }

            $this->info(sprintf(
                '    %s page %d: kept %d (%.1fs)',
                $label,
                $page,
                $kept,
                microtime(true) - $t0
            ));

            if ($stop) {
                break;
            }
            $url = $payload['next'] ?? null;
        }

        return [
            'count' => $count,
            'results' => $all,
            'truncated_mode' => 'today_only',
            'fetch_failed' => false,
            'next_url' => null,
        ];
    }

    /**
     * Resume should retry pulses whose indicator/detail files are empty or partial after OTX 504.
     */
    protected function pulseNeedsRefetch($runId, $pulseId)
    {
        $pulseDir = OtxPulseStagingStore::pulsePath($runId, $pulseId);
        $ind = OtxPulseStagingStore::readJson($pulseDir . '/indicators.json');
        if (!$ind || !empty($ind['fetch_failed'])) {
            return true;
        }
        $detail = OtxPulseStagingStore::readJson($pulseDir . '/detail.json');
        if ($detail && !empty($detail['fetch_failed'])) {
            return true;
        }
        return false;
    }

    protected function countPendingPulses(array $manifest)
    {
        $n = 0;
        foreach ($manifest['pulses'] ?? [] as $meta) {
            if (($meta['status'] ?? '') === 'pending') {
                $n++;
            }
        }
        return $n;
    }
}
