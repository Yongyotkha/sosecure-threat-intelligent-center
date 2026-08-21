<?php

namespace App\Console\Commands;

use App\Services\OtxHeavyPulseQueue;
use App\Services\OtxHttpClient;
use App\Services\OtxPulseStagingStore;
use Exception;

/**
 * Slow-lane fetch for pulses skipped by main fetch due to high indicator_count.
 * Same indicator rules (created today only). Related skipped by default.
 * Leftover heavy staging is finished before taking new queue items.
 */
class OTXMDFetchPulseHeavy extends OTXMDFetchPulse
{
    protected $signature = 'app:OTXMDFetchPulseHeavy
                            {--limit=1 : Number of heavy pulses to process this run}
                            {--with-related : Also fetch related pulses (default: skip)}
                            {--timeout=180 : HTTP timeout seconds}
                            {--retries=5 : Max HTTP retries per URL}
                            {--status : Only show heavy queue counts}
                            {--mark-failed= : Park pulse id(s) as failed (comma-separated). Does not fetch}
                            {--cleanup : Remove imported/failed queue entries older than 7 days}
                            {--database=sosecure_threatintelligent : Mongo database for auto-import}
                            {--skip-import : Fetch only; do not auto-import to Mongo}';

    protected $description = 'Fetch leftover/queued heavy OTX pulses, then auto-import (unless --skip-import)';

    public function handle()
    {
        if ($this->option('cleanup')) {
            $removed = OtxHeavyPulseQueue::cleanup(7);
            $this->info("Heavy queue cleanup removed: {$removed}");
            return 0;
        }

        $markFailed = $this->option('mark-failed');
        if ($markFailed !== null && $markFailed !== '') {
            foreach (preg_split('/\s*,\s*/', (string) $markFailed) as $pulseId) {
                if ($pulseId === '') {
                    continue;
                }
                OtxHeavyPulseQueue::markFailed($pulseId, 'parked: do not auto-retry');
                $this->info("Marked failed: {$pulseId}");
            }
            $this->info('Heavy queue: pending=' . OtxHeavyPulseQueue::countByStatus('pending')
                . ' failed=' . OtxHeavyPulseQueue::countByStatus('failed')
                . ' imported=' . OtxHeavyPulseQueue::countByStatus('imported'));
            return 0;
        }

        $lock = OtxPulseStagingStore::acquireLock('heavy');
        if (!$lock) {
            $this->warn('Another heavy fetch is already running. Leftover will be finished on the next run.');
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
        $reclaimed = OtxHeavyPulseQueue::reclaimStale(30);
        if ($reclaimed > 0) {
            $this->warn("Reclaimed stale heavy queue rows back to pending: {$reclaimed}");
        }

        $pending = OtxHeavyPulseQueue::countByStatus('pending');
        $processing = OtxHeavyPulseQueue::countByStatus('processing');
        $staged = OtxHeavyPulseQueue::countByStatus('staged');
        $failed = OtxHeavyPulseQueue::countByStatus('failed');
        $imported = OtxHeavyPulseQueue::countByStatus('imported');

        $this->info('Heavy queue: pending=' . $pending
            . ' processing=' . $processing
            . ' staged=' . $staged
            . ' failed=' . $failed
            . ' imported=' . $imported);

        if ($this->option('status')) {
            return 0;
        }

        $this->http = new OtxHttpClient(
            (int) $this->option('timeout'),
            (int) $this->option('retries')
        );

        $leftover = OtxPulseStagingStore::findIncompleteRunId('pulses', 'heavy');
        if ($leftover) {
            $this->info("Leftover heavy staging found — finishing before new queue items: {$leftover}");
            $exit = $this->processHeavyRun($leftover, true);
            $still = OtxPulseStagingStore::loadManifest($leftover);
            if ($still && OtxPulseStagingStore::isIncomplete($still)) {
                $this->warn('Leftover heavy run not complete — skip new queue items.');
                return $exit;
            }
            $this->info('Leftover heavy run finished.');
        }

        $items = OtxHeavyPulseQueue::listByStatus('pending');
        if (empty($items)) {
            $this->info('No pending heavy pulses.');
            return isset($exit) ? $exit : 0;
        }

        $limit = max(1, (int) $this->option('limit'));
        $items = array_slice($items, 0, $limit);

        $runId = OtxPulseStagingStore::createRun('heavy-queue', 'pulses', $limit);
        $manifest = OtxPulseStagingStore::loadManifest($runId);
        $manifest['queue'] = 'heavy';
        $manifest['limit'] = $limit;
        $manifest['checkpoint']['list_done'] = true;
        $manifest['api_total_pulses'] = count($items);
        OtxPulseStagingStore::stampSessionStart($manifest, false);

        foreach ($items as $entry) {
            $pulseId = $entry['pulse_id'];
            $listItem = $entry['list_item'] ?? null;
            if (!$listItem) {
                OtxHeavyPulseQueue::markFailed($pulseId, 'missing list_item');
                continue;
            }

            OtxHeavyPulseQueue::markProcessing($pulseId, $runId);

            $manifest['pulses'][$pulseId] = [
                'status' => 'pending',
                'skip_reason' => null,
                'indicator_count' => (int) ($entry['indicator_count'] ?? 0),
                'name' => $entry['name'] ?? '',
                'modified' => $entry['modified'] ?? null,
                'created' => $entry['created'] ?? null,
                'queue' => 'heavy',
            ];

            $pulseDir = OtxPulseStagingStore::pulsePath($runId, $pulseId);
            OtxPulseStagingStore::writeJson($pulseDir . '/list_item.json', $listItem);
        }

        OtxPulseStagingStore::saveManifest($runId, $manifest);
        $this->info("Created heavy run: {$runId}");
        $this->info('Started at          : ' . ($manifest['started_at'] ?? '-'));
        $this->info('Processing ' . count($manifest['pulses'] ?? []) . ' heavy pulse(s)...');

        return $this->processHeavyRun($runId, false);
    }

    /**
     * @param bool $isResume when true, load existing manifest and retry pending/failed
     */
    protected function processHeavyRun($runId, $isResume)
    {
        $manifest = OtxPulseStagingStore::loadManifest($runId);
        if (!$manifest) {
            $this->error("Heavy run not found: {$runId}");
            return 1;
        }

        if ($isResume) {
            OtxPulseStagingStore::stampSessionStart($manifest, true);
            OtxPulseStagingStore::saveManifest($runId, $manifest);
            $this->info("Resuming heavy run: {$runId} (resume #" . (int) ($manifest['resume_count'] ?? 0) . ')');
            $this->info('Started at          : ' . ($manifest['started_at'] ?? '-'));
        }

        try {
            foreach ($manifest['pulses'] as $pulseId => $meta) {
                $status = $meta['status'] ?? 'pending';
                if ($status === 'skipped') {
                    continue;
                }
                if ($status === 'fetched' && !$this->pulseNeedsRefetch($runId, $pulseId)) {
                    continue;
                }
                $this->info(($status === 'failed' ? 'Retrying' : 'Fetching') . " heavy pulse: {$pulseId} | indicators~"
                    . ($meta['indicator_count'] ?? 0) . ' | ' . ($meta['name'] ?? ''));

                $ok = $this->fetchOnePulseHeavy($runId, $pulseId, $manifest);
                if ($ok) {
                    $manifest['pulses'][$pulseId]['status'] = 'fetched';
                    unset($manifest['pulses'][$pulseId]['error']);
                    OtxHeavyPulseQueue::markStaged($pulseId, $runId);
                } else {
                    $manifest['pulses'][$pulseId]['status'] = 'failed';
                    OtxHeavyPulseQueue::markFailed($pulseId, $manifest['pulses'][$pulseId]['error'] ?? 'fetch failed');
                }
                OtxPulseStagingStore::saveManifest($runId, $manifest);
            }
        } catch (Exception $e) {
            $manifest['status'] = 'partial';
            $manifest['last_error'] = $e->getMessage();
            $manifest['complete'] = false;
            OtxPulseStagingStore::saveManifest($runId, $manifest);
            $this->error('Heavy fetch aborted: ' . $e->getMessage());
            $this->warn('Staging kept for resume: ' . OtxPulseStagingStore::runPath($runId));
            return 1;
        }

        $fetched = 0;
        $failedCount = 0;
        $pending = 0;
        foreach ($manifest['pulses'] as $meta) {
            $st = $meta['status'] ?? '';
            if ($st === 'fetched') {
                $fetched++;
            } elseif ($st === 'failed') {
                $failedCount++;
            } else {
                $pending++;
            }
        }

        $expected = count($manifest['pulses']);
        $verified = $fetched + $failedCount;
        $pct = $expected > 0 ? round(($verified / $expected) * 100, 2) : 0;
        $fetchComplete = $failedCount === 0 && $pending === 0 && $fetched === $expected;

        $manifest['pulses_fetched'] = $fetched;
        $manifest['pulses_failed'] = $failedCount;
        $manifest['status'] = $fetchComplete ? 'fetched' : 'partial';
        $manifest['stats'] = [
            'api_total' => $expected,
            'limit' => $manifest['limit'] ?? $expected,
            'expected' => $expected,
            'fetched' => $fetched,
            'failed' => $failedCount,
            'pending' => $pending,
            'percent' => $pct,
            'complete' => $fetchComplete,
            'queue' => 'heavy',
        ];
        OtxPulseStagingStore::stampFetchFinish($manifest, $fetchComplete);
        OtxPulseStagingStore::saveManifest($runId, $manifest);

        $this->info('=========================================');
        $this->info('HEAVY FETCH SUMMARY');
        $this->info('=========================================');
        $this->info("Run ID              : {$runId}");
        $this->info('Started at          : ' . ($manifest['started_at'] ?? '-'));
        $this->info('Last started        : ' . ($manifest['last_started_at'] ?? '-'));
        $this->info('Fetch finished at   : ' . ($manifest['fetch_finished_at'] ?? '-'));
        $this->info('Fetch duration      : ' . OtxPulseStagingStore::elapsed($manifest['last_started_at'] ?? $manifest['started_at'] ?? null, $manifest['fetch_finished_at'] ?? null));
        $this->info('Resume count        : ' . (int) ($manifest['resume_count'] ?? 0));
        $this->info('Intended this run    : ' . $expected . ' (heavy queue batch, not OTX catalog)');
        $this->info('Fetched to staging   : ' . $fetched);
        $this->info('Failed              : ' . $failedCount);
        $this->info('Pending             : ' . $pending);
        $this->info('Verified            : ' . $verified . ' / ' . $expected . " ({$pct}%)");
        $this->info('Fetch complete      : ' . ($fetchComplete ? 'YES' : 'NO'));
        $this->info('Heavy queue pending : ' . OtxHeavyPulseQueue::countByStatus('pending'));

        $fetchExit = ($failedCount > 0 || $pending > 0) ? 2 : 0;
        $importPending = ($manifest['import_status'] ?? 'pending') !== 'done';
        if ($fetched > 0 && $importPending) {
            $importExit = $this->runImportAfterFetch($runId);
            if ($importExit !== 0 && $fetchExit === 0) {
                $fetchExit = $importExit;
            }
        } else {
            $this->info('Skip auto-import    : no fetched pulses');
        }

        $after = OtxPulseStagingStore::loadManifest($runId);
        if ($after) {
            $pipelineComplete = ($after['import_status'] ?? '') === 'done' && ($after['status'] ?? '') === 'fetched';
            if ($this->option('skip-import')) {
                $pipelineComplete = false;
            }
            OtxPulseStagingStore::stampFinish($after, $pipelineComplete);
            OtxPulseStagingStore::saveManifest($runId, $after);
            $this->info('Finished at         : ' . ($after['finished_at'] ?? '-'));
            $this->info('Pipeline complete   : ' . ($pipelineComplete ? 'YES' : 'NO'));
            $this->info('Duration            : ' . OtxPulseStagingStore::elapsed($after['last_started_at'] ?? $after['started_at'] ?? null, $after['finished_at'] ?? null));
        } else {
            $this->info('Finished at         : ' . date('c') . ' (staging deleted after complete import)');
            $this->info('Pipeline complete   : YES');
            $this->info('Duration            : ' . OtxPulseStagingStore::elapsed($manifest['last_started_at'] ?? $manifest['started_at'] ?? null));
        }

        return $fetchExit;
    }

    /**
     * Same as parent fetchOnePulse, but pre-skip related unless --with-related
     * (OTX often 504s related on huge pulses).
     */
    protected function fetchOnePulseHeavy($runId, $pulseId, array &$manifest)
    {
        if (!$this->option('with-related')) {
            $pulseDir = OtxPulseStagingStore::pulsePath($runId, $pulseId);
            if (!file_exists($pulseDir . '/related.json')) {
                $this->info('  → related skipped (heavy default)');
                OtxPulseStagingStore::writeJson($pulseDir . '/related.json', [
                    'count' => 0,
                    'results' => [],
                    'truncated_mode' => 'today_only',
                    'skipped' => true,
                ]);
            }
        }

        $ok = $this->fetchOnePulse($runId, $pulseId, $manifest);
        $ind = OtxPulseStagingStore::readJson($pulseDir . '/indicators.json');
        if (!empty($ind['fetch_failed']) && empty($ind['results'])) {
            $this->error("  heavy pulse has no indicators after fetch failure: {$pulseId}");
            $manifest['pulses'][$pulseId]['error'] = 'indicators empty after OTX failure';
            return false;
        }

        return $ok;
    }
}
