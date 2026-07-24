<?php

namespace App\Console\Commands;

use App\Services\OtxHeavyPulseQueue;
use App\Services\OtxHttpClient;
use App\Services\OtxPulseStagingStore;
use Exception;

/**
 * Slow-lane fetch for pulses skipped by main fetch due to high indicator_count.
 * Same indicator rules (created today only). Related skipped by default.
 */
class OTXMDFetchPulseHeavy extends OTXMDFetchPulse
{
    protected $signature = 'app:OTXMDFetchPulseHeavy
                            {--limit=1 : Number of heavy pulses to process this run}
                            {--with-related : Also fetch related pulses (default: skip)}
                            {--timeout=180 : HTTP timeout seconds}
                            {--retries=5 : Max HTTP retries per URL}
                            {--status : Only show heavy queue counts}
                            {--cleanup : Remove imported/failed queue entries older than 7 days}
                            {--skip-import : Fetch only; do not auto-import to Mongo}';

    protected $description = 'Fetch heavy OTX pulses from queue, then auto-import (unless --skip-import)';

    public function handle()
    {
        if ($this->option('cleanup')) {
            $removed = OtxHeavyPulseQueue::cleanup(7);
            $this->info("Heavy queue cleanup removed: {$removed}");
            return 0;
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

        $items = OtxHeavyPulseQueue::listByStatus('pending');
        if (empty($items)) {
            $this->info('No pending heavy pulses.');
            return 0;
        }

        $limit = max(1, (int) $this->option('limit'));
        $items = array_slice($items, 0, $limit);

        $this->http = new OtxHttpClient(
            (int) $this->option('timeout'),
            (int) $this->option('retries')
        );

        $runId = OtxPulseStagingStore::createRun('heavy-queue');
        $manifest = OtxPulseStagingStore::loadManifest($runId);
        $manifest['queue'] = 'heavy';
        $manifest['checkpoint']['list_done'] = true;
        $manifest['api_total_pulses'] = count($items);

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
        $this->info('Processing ' . count($items) . ' heavy pulse(s)...');

        // Default: skip related (parent checks --skip-related).
        // Heavy signature uses --with-related instead; synthesize skip-related behavior below.
        try {
            foreach ($manifest['pulses'] as $pulseId => $meta) {
                if (($meta['status'] ?? '') !== 'pending') {
                    continue;
                }
                $this->info("Fetching heavy pulse: {$pulseId} | indicators~"
                    . ($meta['indicator_count'] ?? 0) . ' | ' . ($meta['name'] ?? ''));

                // Temporarily fake skip-related via option bag when not --with-related.
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
            OtxPulseStagingStore::saveManifest($runId, $manifest);
            $this->error('Heavy fetch aborted: ' . $e->getMessage());
            return 1;
        }

        $fetched = 0;
        $failedCount = 0;
        foreach ($manifest['pulses'] as $meta) {
            if (($meta['status'] ?? '') === 'fetched') {
                $fetched++;
            } elseif (($meta['status'] ?? '') === 'failed') {
                $failedCount++;
            }
        }

        $expected = count($manifest['pulses']);
        $verified = $fetched + $failedCount;
        $pct = $expected > 0 ? round(($verified / $expected) * 100, 2) : 0;
        $complete = $failedCount === 0 && $fetched === $expected;

        $manifest['pulses_fetched'] = $fetched;
        $manifest['pulses_failed'] = $failedCount;
        $manifest['status'] = $complete ? 'fetched' : 'partial';
        $manifest['stats'] = [
            'expected' => $expected,
            'fetched' => $fetched,
            'failed' => $failedCount,
            'percent' => $pct,
            'complete' => $complete,
            'queue' => 'heavy',
        ];
        OtxPulseStagingStore::saveManifest($runId, $manifest);

        $this->info('=========================================');
        $this->info('HEAVY FETCH SUMMARY');
        $this->info('=========================================');
        $this->info("Run ID              : {$runId}");
        $this->info('Expected            : ' . $expected);
        $this->info('Successfully fetched: ' . $fetched);
        $this->info('Failed              : ' . $failedCount);
        $this->info('Verified            : ' . $verified . ' / ' . $expected . " ({$pct}%)");
        $this->info('Complete            : ' . ($complete ? 'YES' : 'NO'));
        $this->info('Heavy queue pending : ' . OtxHeavyPulseQueue::countByStatus('pending'));

        $fetchExit = $failedCount > 0 ? 2 : 0;
        if ($fetched > 0) {
            $importExit = $this->runImportAfterFetch($runId);
            if ($importExit !== 0 && $fetchExit === 0) {
                $fetchExit = $importExit;
            }
        } else {
            $this->info('Skip auto-import    : no fetched pulses');
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

        return $this->fetchOnePulse($runId, $pulseId, $manifest);
    }
}
