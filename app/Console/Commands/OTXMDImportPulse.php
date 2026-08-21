<?php

namespace App\Console\Commands;

use App\Services\OtxHeavyPulseQueue;
use App\Services\OtxPulseStagingStore;
use Artisan;
use Exception;
use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;

class OTXMDImportPulse extends Command
{
    protected $signature = 'app:OTXMDImportPulse
                            {--run= : Staging run_id to import}
                            {--database=sosecure_threatintelligent : Mongo database name}
                            {--limit= : Limit pulses to import}
                            {--skip-count : Skip MDCountIndicator after import}
                            {--keep : Keep staging files after successful import (default: delete)}';

    protected $description = 'Import staged OTX pulses into MongoDB (default: sosecure_threatintelligent). Deletes staging after success unless --keep.';

    protected $dbName;
    protected $totalPulsesProcessed = 0;
    protected $totalPulsesSkippedFilter = 0;
    protected $totalPulsesError = 0;
    protected $totalIndicatorsProcessed = 0;
    protected $totalIndicatorsSkipped = 0;
    protected $totalExpectedIndicators = 0; // staged indicators we intend to import (today-only)
    protected $totalApiListedIndicators = 0; // sum of indicator_count from OTX list
    protected $failedPulses = [];
    /** @var array<int, array{pulse_id:string,name:string,indicator_count:int,skip_reason:string}> */
    protected $skippedHeavy = [];
    /** @var array<int, array{pulse_id:string,name:string,indicator_count:int,skip_reason:string}> */
    protected $skippedFilter = [];

    public function handle()
    {
        $this->dbName = $this->option('database') ?: 'sosecure_threatintelligent';
        $runId = $this->option('run') ?: OtxPulseStagingStore::latestFetchedRunId();

        if (!$runId) {
            $this->error('No staging run found. Run app:OTXMDFetchPulse first.');
            return 1;
        }

        $manifest = OtxPulseStagingStore::loadManifest($runId);
        if (!$manifest) {
            $this->error("Run not found: {$runId}");
            return 1;
        }

        if (!in_array($manifest['status'] ?? '', ['fetched', 'partial'], true)) {
            $this->error("Run status is '{$manifest['status']}'. Fetch must finish (or be partial) before import.");
            return 1;
        }

        $this->info("Importing run: {$runId}");
        $this->info("Mongo database: {$this->dbName}");

        $DB_MONGO_KEY = env('DB_MONGO_STOREDATA', '');
        if ($DB_MONGO_KEY === '') {
            $this->error('DB_MONGO_STOREDATA is empty');
            return 1;
        }

        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $db = $clientMD->{$this->dbName};
        $date_now = new UTCDateTime(strtotime(date('Y-m-d H:i:s')) * 1000);

        $collectionStamp = $db->fx_transaction_otx_event_stamp;
        $startedIso = date('c');
        OtxPulseStagingStore::stampImportStart($manifest);
        OtxPulseStagingStore::saveManifest($runId, $manifest);
        $saveIntendedAtStart = 0;
        foreach ($manifest['pulses'] ?? [] as $meta) {
            if (($meta['status'] ?? '') === 'fetched') {
                $saveIntendedAtStart++;
            }
        }

        $insertOneResult = $collectionStamp->insertOne([
            'code' => generator_uuid(),
            'transaction_date' => date('Y-m-d'),
            'status' => 1,
            'created_at' => $date_now,
            'created_by' => 'system',
            'updated_at' => $date_now,
            'updated_by' => 'system',
            'deleted_at' => null,
            'source' => 'otx.alienvault',
            'staging_run_id' => $runId,
            'pipeline' => 'staging_import',
            'started_at' => $date_now,
            'started_at_iso' => $startedIso,
            'fetch_started_at' => $manifest['started_at'] ?? null,
            'api_total_pulses' => (int) ($manifest['api_total_pulses'] ?? 0),
            'limit' => $manifest['limit'] ?? null,
            'save_intended_pulses' => $saveIntendedAtStart,
            'save_actual_pulses' => 0,
            'complete' => false,
        ]);
        $stampId = $insertOneResult->getInsertedId();

        $limit = $this->option('limit') !== null && $this->option('limit') !== ''
            ? (int) $this->option('limit')
            : null;
        $imported = 0;

        $manifest['import_status'] = 'importing';
        OtxPulseStagingStore::saveManifest($runId, $manifest);

        foreach ($manifest['pulses'] as $pulseId => $meta) {
            if ($limit !== null && $imported >= $limit) {
                break;
            }

            $status = $meta['status'] ?? '';
            if ($status === 'skipped') {
                $this->totalPulsesSkippedFilter++;
                $skipEntry = [
                    'pulse_id' => (string) $pulseId,
                    'name' => $meta['name'] ?? '',
                    'indicator_count' => (int) ($meta['indicator_count'] ?? 0),
                    'skip_reason' => $meta['skip_reason'] ?? '',
                ];
                $reason = (string) ($meta['skip_reason'] ?? '');
                if (stripos($reason, 'max-indicators') !== false || stripos($reason, 'indicator_count') !== false) {
                    $this->skippedHeavy[] = $skipEntry;
                    $this->info("Skip pulse (heavy queue): {$pulseId} | {$skipEntry['name']} | indicators={$skipEntry['indicator_count']}");
                } else {
                    $this->skippedFilter[] = $skipEntry;
                    $this->info("Skip pulse (filter): {$pulseId} | {$skipEntry['name']} | {$reason}");
                }
                continue;
            }
            if ($status === 'failed') {
                // Fetch failed — retried on leftover resume; do not block import complete.
                continue;
            }
            if ($status !== 'fetched') {
                continue;
            }
            // Already imported (resume) — count as saved so Verified/Complete stay correct.
            if (($meta['import_status'] ?? null) === 'done') {
                $this->totalPulsesProcessed++;
                if (isset($meta['indicator_count'])) {
                    $this->totalApiListedIndicators += (int) $meta['indicator_count'];
                }
                continue;
            }

            if (isset($meta['indicator_count'])) {
                $this->totalApiListedIndicators += (int) $meta['indicator_count'];
            }

            try {
                $ok = $this->importOnePulse($runId, $pulseId, $stampId, $clientMD, $date_now);
                if ($ok) {
                    $this->totalPulsesProcessed++;
                    $imported++;
                    $manifest['pulses'][$pulseId]['import_status'] = 'done';
                } else {
                    $this->totalPulsesError++;
                    $this->failedPulses[] = $pulseId;
                    $manifest['pulses'][$pulseId]['import_status'] = 'failed';
                }
            } catch (Exception $e) {
                $this->totalPulsesError++;
                $this->failedPulses[] = $pulseId;
                $manifest['pulses'][$pulseId]['import_status'] = 'failed';
                $manifest['pulses'][$pulseId]['import_error'] = $e->getMessage();
                $this->error("Import failed {$pulseId}: " . $e->getMessage());
            }

            OtxPulseStagingStore::saveManifest($runId, $manifest);
        }

        $apiTotal = (int) ($manifest['api_total_pulses'] ?? 0);
        $verified = $this->totalPulsesProcessed + $this->totalPulsesSkippedFilter + $this->totalPulsesError;
        $allOk = $this->totalPulsesError === 0;

        // Handled = fetched + skipped (audit of everything this run touched).
        $handledPulses = 0;
        $saveIntended = 0;
        foreach ($manifest['pulses'] ?? [] as $meta) {
            $st = $meta['status'] ?? '';
            if (in_array($st, ['fetched', 'skipped'], true)) {
                $handledPulses++;
            }
            if ($st === 'fetched') {
                $saveIntended++;
            }
        }
        if ($limit !== null) {
            $saveIntended = min($saveIntended, $limit);
        }
        if ($handledPulses === 0) {
            $handledPulses = $verified;
        }
        $pulsePct = $saveIntended > 0
            ? round(($this->totalPulsesProcessed / $saveIntended) * 100, 2)
            : ($this->totalPulsesError === 0 ? 100 : 0);

        // Indicators: % vs staged today-only set (fair). API listed count shown separately.
        $notInScope = max(0, $this->totalApiListedIndicators - $this->totalExpectedIndicators);
        $indiVerified = $this->totalIndicatorsProcessed + $this->totalIndicatorsSkipped;
        $indiExpected = (int) $this->totalExpectedIndicators;
        $indiPct = $indiExpected > 0 ? round(($indiVerified / $indiExpected) * 100, 2) : ($this->totalApiListedIndicators === 0 ? 100 : 0);
        $indiComplete = $indiExpected === 0 || $indiVerified >= $indiExpected;
        $complete = $allOk && $this->totalPulsesProcessed >= $saveIntended && $indiComplete;

        $skippedHeavyIds = array_column($this->skippedHeavy, 'pulse_id');
        $skippedFilterIds = array_column($this->skippedFilter, 'pulse_id');
        $importLimit = $limit;
        $fetchLimit = array_key_exists('limit', $manifest) ? $manifest['limit'] : null;

        $finishedIso = date('c');
        $finishedAt = new UTCDateTime(strtotime($finishedIso) * 1000);
        $collectionStamp->updateOne(
            ['_id' => $stampId],
            ['$set' => [
                'status' => $complete ? 2 : 3,
                'audit_note' => OtxPulseStagingStore::AUDIT_NOTE,
                'audit' => OtxPulseStagingStore::mongoAudit($manifest, [
                    'api_total' => $apiTotal,
                    'limit' => $importLimit !== null ? $importLimit : $fetchLimit,
                    'intended' => $saveIntended,
                    'saved' => $this->totalPulsesProcessed,
                    'skipped' => $this->totalPulsesSkippedFilter,
                    'failed' => $this->totalPulsesError,
                ], [
                    'indicators' => [
                        'api_listed' => $this->totalApiListedIndicators,
                        'save_intended' => $indiExpected,
                        'save_actual' => $this->totalIndicatorsProcessed,
                        'skipped' => $this->totalIndicatorsSkipped,
                        'not_in_scope' => $notInScope,
                    ],
                    'duration' => [
                        'note' => 'duration_seconds = this session (fetch + import + MDCount). import_duration_seconds is Mongo write only.',
                    ],
                ]),
                'api_total_pulses' => $apiTotal,
                'limit' => $importLimit !== null ? $importLimit : $fetchLimit,
                'save_intended_pulses' => $saveIntended,
                'save_actual_pulses' => $this->totalPulsesProcessed,
                'expected_pulses' => $saveIntended,
                'handled_pulses' => $handledPulses,
                'processed_pulses' => $this->totalPulsesProcessed,
                'skipped_pulses' => $this->totalPulsesSkippedFilter,
                'skipped_heavy_pulses' => count($this->skippedHeavy),
                'skipped_heavy_ids' => $skippedHeavyIds,
                'skipped_heavy' => $this->skippedHeavy,
                'skipped_filter_pulses' => count($this->skippedFilter),
                'skipped_filter_ids' => $skippedFilterIds,
                'skipped_filter' => $this->skippedFilter,
                'save_intended_indicators' => $indiExpected,
                'save_actual_indicators' => $this->totalIndicatorsProcessed,
                'processed_indicators' => $this->totalIndicatorsProcessed,
                'skipped_indicators' => $this->totalIndicatorsSkipped,
                'api_listed_indicators' => $this->totalApiListedIndicators,
                'api_expected_indicators' => $this->totalExpectedIndicators,
                'indicators_not_in_scope' => $notInScope,
                'pulse_percent' => $pulsePct,
                'indicator_percent' => $indiPct,
                'failed_pulses' => $this->failedPulses,
                'error_msg' => $complete ? '' : 'Partial failure during staging import',
                'staging_run_id' => $runId,
                'finished_at' => $finishedAt,
                'finished_at_iso' => $finishedIso,
                'complete' => $complete,
                'updated_at' => $finishedAt,
            ]]
        );

        $manifest['import_status'] = $complete ? 'done' : 'partial';
        OtxPulseStagingStore::stampImportFinish($manifest, $complete);
        $manifest['import_stats'] = [
            'save_intended_pulses' => $saveIntended,
            'save_actual_pulses' => $this->totalPulsesProcessed,
            'expected_pulses' => $saveIntended,
            'handled_pulses' => $handledPulses,
            'saved_pulses' => $this->totalPulsesProcessed,
            'skipped_pulses' => $this->totalPulsesSkippedFilter,
            'skipped_heavy_pulses' => count($this->skippedHeavy),
            'skipped_heavy' => $this->skippedHeavy,
            'skipped_filter_pulses' => count($this->skippedFilter),
            'skipped_filter' => $this->skippedFilter,
            'failed_pulses' => $this->totalPulsesError,
            'verified_pulses' => $verified,
            'pulse_percent' => $pulsePct,
            'api_listed_indicators' => $this->totalApiListedIndicators,
            'save_intended_indicators' => $indiExpected,
            'save_actual_indicators' => $this->totalIndicatorsProcessed,
            'expected_indicators' => $indiExpected,
            'saved_indicators' => $this->totalIndicatorsProcessed,
            'skipped_indicators' => $this->totalIndicatorsSkipped,
            'not_in_scope_indicators' => $notInScope,
            'verified_indicators' => $indiVerified,
            'indicator_percent' => $indiPct,
            'complete' => $complete,
        ];
        OtxPulseStagingStore::saveManifest($runId, $manifest);

        $this->info('=========================================');
        $this->info('IMPORT SUMMARY (staging → ' . $this->dbName . ')');
        $this->info('=========================================');
        $this->info('Run ID              : ' . $runId);
        $this->info('PULSES:');
        $this->info('  - API total (OTX catalog, not this run) : ' . number_format($apiTotal));
        $this->info('  - Save intended (this run)              : ' . number_format($saveIntended) . ($importLimit !== null ? " (limit={$importLimit})" : ''));
        $this->info('  - Save actual                           : ' . number_format($this->totalPulsesProcessed));
        $this->info('  - Skipped         : ' . number_format($this->totalPulsesSkippedFilter)
            . ' (heavy=' . count($this->skippedHeavy) . ', filter=' . count($this->skippedFilter) . ')');
        foreach ($this->skippedHeavy as $row) {
            $this->info('      heavy         : ' . $row['pulse_id'] . ' | ' . $row['name']
                . ' | indicators=' . $row['indicator_count'] . ' | ' . $row['skip_reason']);
        }
        foreach ($this->skippedFilter as $row) {
            $this->info('      filter        : ' . $row['pulse_id'] . ' | ' . $row['name']
                . ' | ' . $row['skip_reason']);
        }
        $this->info('  - Errors          : ' . number_format($this->totalPulsesError));
        $this->info('  - Verified        : ' . number_format($this->totalPulsesProcessed) . ' / ' . number_format($saveIntended) . " ({$pulsePct}%)");
        $this->info('  - Handled         : ' . number_format($verified) . ' / ' . number_format($handledPulses) . ' (includes skipped)');
        $this->info('INDICATORS:');
        $this->info('  - API listed (full pulse count from OTX, not this run) : ' . number_format($this->totalApiListedIndicators));
        $this->info('  - Save intended (today / in-scope)                     : ' . number_format($indiExpected));
        $this->info('  - Save actual                                          : ' . number_format($this->totalIndicatorsProcessed));
        $this->info('  - Not in scope (older than today, not fetched)         : ' . number_format($notInScope));
        $this->info('  - Skipped / failed rows                                : ' . number_format($this->totalIndicatorsSkipped));
        $this->info('  - Verified        : ' . number_format($indiVerified) . ' / ' . number_format($indiExpected) . " ({$indiPct}%)");
        $this->info('Complete            : ' . ($complete ? 'YES' : 'NO'));
        $this->info('Import started at   : ' . $startedIso);
        $this->info('Import finished at  : ' . $finishedIso);
        $this->info('Fetch duration      : ' . OtxPulseStagingStore::elapsed($manifest['last_started_at'] ?? $manifest['started_at'] ?? null, $manifest['fetch_finished_at'] ?? null));
        $this->info('Import duration     : ' . OtxPulseStagingStore::elapsed($startedIso, $finishedIso));
        $this->info('Import status       : ' . $manifest['import_status']);

        if (!$this->option('skip-count')) {
            Artisan::call('app:MDCountIndicator', [], $this->output);
        }

        $endIso = date('c');
        $durFields = OtxPulseStagingStore::stampDurationFields($manifest, $endIso);
        $collectionStamp->updateOne(
            ['_id' => $stampId],
            ['$set' => $durFields]
        );
        $this->info('Total duration      : ' . $durFields['duration_human']
            . ' (fetch ' . $durFields['fetch_duration_human']
            . ', import ' . $durFields['import_duration_human']
            . ', count ' . $durFields['count_duration_human'] . ')');

        $markedHeavy = OtxHeavyPulseQueue::markImportedByRun($runId);
        if ($markedHeavy > 0) {
            $this->info('Heavy queue marked imported: ' . $markedHeavy);
        }

        // Fresh fetch every round — delete staging after success to avoid disk bloat.
        // Keep on partial failure so the run can be inspected / retried.
        if ($complete && !$this->option('keep')) {
            if (OtxPulseStagingStore::deleteRun($runId)) {
                $this->info('Staging deleted    : ' . $runId);
            } else {
                $this->warn('Staging delete failed: ' . $runId);
            }
        } elseif (!$complete) {
            $this->warn('Staging kept (import partial/failed): ' . OtxPulseStagingStore::runPath($runId));
        } else {
            $this->info('Staging kept (--keep): ' . OtxPulseStagingStore::runPath($runId));
        }

        return $complete ? 0 : 2;
    }

    protected function importOnePulse($runId, $pulseId, $stampId, $clientMD, UTCDateTime $date_now)
    {
        $pulseDir = OtxPulseStagingStore::pulsePath($runId, $pulseId);
        $listItem = OtxPulseStagingStore::readJson($pulseDir . '/list_item.json');
        $detail = OtxPulseStagingStore::readJson($pulseDir . '/detail.json');
        $indicators = OtxPulseStagingStore::readJson($pulseDir . '/indicators.json');
        $related = OtxPulseStagingStore::readJson($pulseDir . '/related.json');

        if (!$listItem) {
            $this->error("Missing list_item for {$pulseId}");
            return false;
        }

        $value = $listItem;
        $db = $clientMD->{$this->dbName};
        $col_fx_otx_events = $db->fx_otx_events;

        $groups = '';
        if (is_array($detail) && !empty($detail['groups'])) {
            $groups = implode(', ', array_column($detail['groups'], 'name'));
        }

        $references = implode(', ', isset($value['references']) ? $value['references'] : []);
        $tags = implode(', ', isset($value['tags']) ? $value['tags'] : []);
        $industries = implode(', ', isset($value['industries']) ? $value['industries'] : []);
        $malware_families = implode(', ', array_column(isset($value['malware_families']) ? $value['malware_families'] : [], 'display_name'));

        $this->info("Import pulse: {$pulseId} | " . ($value['name'] ?? ''));

        $col_fx_otx_events->updateOne(
            ['pulse_id' => $pulseId],
            [
                '$set' => [
                    'name' => isset($value['name']) ? $value['name'] : '',
                    'description' => isset($value['description']) ? $value['description'] : '',
                    'modified' => isset($value['modified']) ? new UTCDateTime(strtotime($value['modified']) * 1000) : null,
                    'created' => isset($value['created']) ? new UTCDateTime(strtotime($value['created']) * 1000) : null,
                    'public' => 0,
                    'TLP' => isset($value['TLP']) ? $value['TLP'] : '',
                    'is_modified' => isset($value['is_modified']) ? $value['is_modified'] : '',
                    'references' => $references,
                    'tags' => $tags,
                    'industries' => $industries,
                    'malware_families' => $malware_families,
                    'groups' => $groups,
                    'author_username' => isset($value['author']['username']) ? $value['author']['username'] : '',
                    'updated_at' => $date_now,
                    'updated_by' => 'system',
                    'creator_org' => 'OTX',
                ],
                '$setOnInsert' => [
                    'indicator_type_counts' => [],
                    'indicator_count' => 0,
                    'transcation_id' => $stampId,
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

        $dateModified = isset($value['modified']) ? new UTCDateTime(strtotime($value['modified']) * 1000) : null;
        $stagedCount = is_array($indicators) ? count($indicators['results'] ?? []) : 0;
        $this->totalExpectedIndicators += $stagedCount;
        $this->importIndicators($pulseId, $indicators, $dateModified, $clientMD, $date_now, $stagedCount);
        $this->countAttr($pulseId, $clientMD);
        $this->importRelated($pulseId, $related, $clientMD, $date_now);

        return true;
    }

    /**
     * Same business rule as OTXMDFeedPulse::saveIndicator_ref —
     * only indicators created today (sorted newest first in staging).
     */
    protected function importIndicators($pulseID, $indicatorsPayload, $dateModified, $clientMD, UTCDateTime $date_now, $expectedCount = 0)
    {
        $dayMoreThan = 6;
        $allRow = (object) [];
        $db = $clientMD->{$this->dbName};
        $collectionBasic = $db->fx_otx_indicator_detail;
        $col_fx_otx_events_indicator_ref = $db->fx_otx_events_indicator_ref;

        $results = is_array($indicatorsPayload) ? ($indicatorsPayload['results'] ?? []) : [];
        $pulseIndicatorProcessed = 0;
        $today = date('Y-m-d');

        foreach ($results as $value) {
            $created = $value['created'] ?? null;
            if (!$created) {
                continue;
            }

            $createdDay = explode('T', $created)[0];
            if ($createdDay !== $today) {
                $remaining = $expectedCount - $pulseIndicatorProcessed;
                if ($remaining > 0) {
                    $this->totalIndicatorsSkipped += $remaining;
                }
                break;
            }

            $date1 = date_create($created);
            $date2 = date_create(date('Y-m-d H:i:s'));
            $diff = date_diff($date1, $date2);
            if ($diff->format('%R%a') > $dayMoreThan) {
                break;
            }

            $this->totalIndicatorsProcessed++;
            $pulseIndicatorProcessed++;

            $collectionBasic->updateOne(
                ['indicator_id' => $value['id'] . ''],
                [
                    '$set' => [
                        'indicator_name' => $value['indicator'],
                        'type' => $value['type'],
                        'updated_by' => 'system',
                        'updated_at' => $date_now,
                    ],
                    '$setOnInsert' => [
                        'transcation_id' => null,
                        'allrow' => $allRow,
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

            $col_fx_otx_events_indicator_ref->updateOne(
                [
                    'indicator_id' => isset($value['id']) ? $value['id'] . '' : '',
                    'pulse_id' => $pulseID,
                ],
                [
                    '$set' => [
                        'pulse_modified' => $dateModified,
                        'role' => isset($value['role']) ? $value['role'] : '',
                        'created' => isset($value['created']) ? new UTCDateTime(strtotime($value['created']) * 1000) : null,
                        'expiration' => isset($value['expiration']) ? new UTCDateTime(strtotime($value['expiration']) * 1000) : null,
                        'is_active' => isset($value['is_active']) ? $value['is_active'] : '',
                    ],
                    '$setOnInsert' => [
                        'status' => 1,
                        'created_at' => $date_now,
                        'created_by' => 'system',
                        'deleted_at' => null,
                        'transaction_date' => date('Y-m-d'),
                        'updated_at' => $date_now,
                        'updated_by' => 'system',
                        'source' => 'otx.alienvault',
                        'indicator' => $value['indicator'],
                        'type' => $value['type'],
                    ],
                ],
                ['upsert' => true]
            );
        }
    }

    /**
     * Same business rule as OTXMDFeedPulse::savePulse_related —
     * only related pulses modified today.
     */
    protected function importRelated($pulseID, $relatedPayload, $clientMD, UTCDateTime $date_now)
    {
        $dayMoreThan = 6;
        $db = $clientMD->{$this->dbName};
        $col_fx_otx_events = $db->fx_otx_events;
        $col_fx_otx_events_event_ref = $db->fx_otx_events_event_ref;
        $results = is_array($relatedPayload) ? ($relatedPayload['results'] ?? []) : [];
        $today = date('Y-m-d');

        foreach ($results as $value) {
            $modified = $value['modified'] ?? null;
            if (!$modified) {
                continue;
            }
            $modifiedDay = explode('T', $modified)[0];
            if ($modifiedDay !== $today) {
                break;
            }

            $date1 = date_create($modified);
            $date2 = date_create(date('Y-m-d H:i:s'));
            $diff = date_diff($date1, $date2);
            if ($diff->format('%R%a') > $dayMoreThan) {
                break;
            }

            $references = implode(', ', isset($value['references']) ? $value['references'] : []);
            $tags = implode(', ', isset($value['tags']) ? $value['tags'] : []);
            $industries = implode(', ', isset($value['industries']) ? $value['industries'] : []);
            $malware_families = implode(', ', array_column(isset($value['malware_families']) ? $value['malware_families'] : [], 'display_name'));

            $col_fx_otx_events->updateOne(
                ['pulse_id' => isset($value['id']) ? $value['id'] : ''],
                [
                    '$set' => [
                        'name' => isset($value['name']) ? $value['name'] : '',
                        'description' => isset($value['description']) ? $value['description'] : '',
                        'modified' => isset($value['modified']) ? new UTCDateTime(strtotime($value['modified']) * 1000) : null,
                        'created' => isset($value['created']) ? new UTCDateTime(strtotime($value['created']) * 1000) : null,
                        'public' => 0,
                        'TLP' => isset($value['TLP']) ? $value['TLP'] : '',
                        'is_modified' => isset($value['is_modified']) ? $value['is_modified'] : '',
                        'references' => $references,
                        'tags' => $tags,
                        'industries' => $industries,
                        'malware_families' => $malware_families,
                        'author_username' => isset($value['author']['username']) ? $value['author']['username'] : '',
                        'updated_at' => $date_now,
                        'updated_by' => 'system',
                        'creator_org' => 'OTX',
                    ],
                    '$setOnInsert' => [
                        'indicator_count' => 0,
                        'indicator_type_counts' => [],
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

            $col_fx_otx_events_event_ref->updateOne(
                [
                    'main_pulse_id' => $pulseID,
                    'pulse_id' => isset($value['id']) ? $value['id'] : '',
                ],
                [
                    '$set' => [
                        'sub_pulse_modified' => isset($value['modified']) ? new UTCDateTime(strtotime($value['modified']) * 1000) : null,
                        'updated_at' => $date_now,
                        'updated_by' => 'system',
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
        }

        $countRelated = $col_fx_otx_events_event_ref->count(['main_pulse_id' => $pulseID]);
        $col_fx_otx_events->updateOne(
            ['pulse_id' => $pulseID],
            ['$set' => ['count_related_pulse' => $countRelated]]
        );
    }

    protected function countAttr($pulseID_, $clientMD)
    {
        $pulseID = $pulseID_ . '';
        $db = $clientMD->{$this->dbName};
        $col_fx_otx_events = $db->fx_otx_events;
        $col_fx_otx_events_indicator_ref = $db->fx_otx_events_indicator_ref;
        $col_fx_otx_indicator_detail = $db->fx_otx_indicator_detail;

        $findOne_col_fx_otx_events = $col_fx_otx_events->findOne(['pulse_id' => $pulseID]);
        if (empty($findOne_col_fx_otx_events)) {
            return;
        }

        $query2 = [
            '$and' => [
                ['pulse_id' => $pulseID],
                ['is_count_attr' => ['$exists' => true]],
            ],
        ];
        $find_col_fx_otx_events_indicator_ref_2 = $col_fx_otx_events_indicator_ref->count($query2);

        if ($find_col_fx_otx_events_indicator_ref_2 == ($findOne_col_fx_otx_events['indicator_count'] ?? 0)) {
            $query = [
                '$and' => [
                    ['pulse_id' => $pulseID],
                    ['is_count_attr' => ['$exists' => false]],
                ],
            ];
            $find_col_fx_otx_events_indicator_ref = $col_fx_otx_events_indicator_ref->find($query)->toArray();
            $countAttrArray = $findOne_col_fx_otx_events['indicator_type_counts'] ?? [];
            $countAttrAll = $findOne_col_fx_otx_events['indicator_count'] ?? 0;
            foreach ($find_col_fx_otx_events_indicator_ref as $value) {
                $countAttrAll++;
                if (!empty($value)) {
                    $type = $value['type'] ?? null;
                    if ($type) {
                        $countAttrArray[$type] = ($countAttrArray[$type] ?? 0) + 1;
                    }
                }
            }
            $col_fx_otx_events->updateOne(
                ['pulse_id' => $pulseID],
                ['$set' => [
                    'indicator_count' => $countAttrAll,
                    'indicator_type_counts' => $countAttrArray,
                ]]
            );
            $col_fx_otx_events_indicator_ref->updateMany($query, ['$set' => ['is_count_attr' => 1]]);
        } else {
            $query = ['pulse_id' => $pulseID];
            $find_col_fx_otx_events_indicator_ref = $col_fx_otx_events_indicator_ref->find($query)->toArray();
            $countAttrArray = [];
            $countAttrAll = 0;
            foreach ($find_col_fx_otx_events_indicator_ref as $value) {
                $findOne_col_fx_otx_indicator_detail = $col_fx_otx_indicator_detail->findOne(['indicator_id' => $value['indicator_id']]);
                $countAttrAll++;
                if (!empty($findOne_col_fx_otx_indicator_detail)) {
                    $type = $findOne_col_fx_otx_indicator_detail['type'] ?? null;
                    if ($type) {
                        $countAttrArray[$type] = ($countAttrArray[$type] ?? 0) + 1;
                    }
                }
            }
            $col_fx_otx_events->updateOne(
                ['pulse_id' => $pulseID],
                ['$set' => [
                    'indicator_count' => $countAttrAll,
                    'indicator_type_counts' => $countAttrArray,
                ]]
            );
            $col_fx_otx_events_indicator_ref->updateMany($query, ['$set' => ['is_count_attr' => 1]]);
        }
    }
}
