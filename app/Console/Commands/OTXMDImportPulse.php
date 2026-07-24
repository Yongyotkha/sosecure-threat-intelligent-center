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
                            {--database=sosecure_threatintelligent_dev : Mongo database name}
                            {--limit= : Limit pulses to import}
                            {--skip-count : Skip MDCountIndicator after import}
                            {--keep : Keep staging files after successful import (default: delete)}';

    protected $description = 'Import staged OTX pulses into MongoDB (default: sosecure_threatintelligent_dev). Deletes staging after success unless --keep.';

    protected $dbName;
    protected $totalPulsesProcessed = 0;
    protected $totalPulsesSkippedFilter = 0;
    protected $totalPulsesError = 0;
    protected $totalIndicatorsProcessed = 0;
    protected $totalIndicatorsSkipped = 0;
    protected $totalExpectedIndicators = 0; // staged indicators we intend to import (today-only)
    protected $totalApiListedIndicators = 0; // sum of indicator_count from OTX list
    protected $failedPulses = [];

    public function handle()
    {
        $this->dbName = $this->option('database') ?: 'sosecure_threatintelligent_dev';
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
                continue;
            }
            if ($status !== 'fetched') {
                if ($status === 'failed') {
                    $this->totalPulsesError++;
                    $this->failedPulses[] = $pulseId;
                }
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

        // Expected for this import: pulses marked fetched/skipped/failed in staging (not always full API list when --limit was used on fetch).
        $stagingExpected = 0;
        foreach ($manifest['pulses'] ?? [] as $meta) {
            $st = $meta['status'] ?? '';
            if (in_array($st, ['fetched', 'skipped', 'failed'], true)) {
                $stagingExpected++;
            }
        }
        if ($stagingExpected === 0) {
            $stagingExpected = $verified;
        }
        $pulsePct = $stagingExpected > 0 ? round(($verified / $stagingExpected) * 100, 2) : 0;

        // Indicators: % vs staged today-only set (fair). API listed count shown separately.
        $notInScope = max(0, $this->totalApiListedIndicators - $this->totalExpectedIndicators);
        $indiVerified = $this->totalIndicatorsProcessed + $this->totalIndicatorsSkipped;
        $indiExpected = (int) $this->totalExpectedIndicators;
        $indiPct = $indiExpected > 0 ? round(($indiVerified / $indiExpected) * 100, 2) : ($this->totalApiListedIndicators === 0 ? 100 : 0);
        $indiComplete = $indiExpected === 0 || $indiVerified >= $indiExpected;
        $complete = $allOk && $verified >= $stagingExpected && $indiComplete;

        $collectionStamp->updateOne(
            ['_id' => $stampId],
            ['$set' => [
                'status' => $complete ? 2 : 3,
                'api_total_pulses' => $apiTotal,
                'expected_pulses' => $stagingExpected,
                'processed_pulses' => $this->totalPulsesProcessed,
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
            ]]
        );

        $manifest['import_status'] = $complete ? 'done' : 'partial';
        $manifest['import_stats'] = [
            'expected_pulses' => $stagingExpected,
            'saved_pulses' => $this->totalPulsesProcessed,
            'skipped_pulses' => $this->totalPulsesSkippedFilter,
            'failed_pulses' => $this->totalPulsesError,
            'verified_pulses' => $verified,
            'pulse_percent' => $pulsePct,
            'api_listed_indicators' => $this->totalApiListedIndicators,
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
        $this->info('  - API total       : ' . number_format($apiTotal));
        $this->info('  - Expected        : ' . number_format($stagingExpected));
        $this->info('  - Saved           : ' . number_format($this->totalPulsesProcessed));
        $this->info('  - Skipped         : ' . number_format($this->totalPulsesSkippedFilter));
        $this->info('  - Errors          : ' . number_format($this->totalPulsesError));
        $this->info('  - Verified        : ' . number_format($verified) . ' / ' . number_format($stagingExpected) . " ({$pulsePct}%)");
        $this->info('INDICATORS:');
        $this->info('  - API listed      : ' . number_format($this->totalApiListedIndicators) . ' (full pulse count from OTX)');
        $this->info('  - Expected (today): ' . number_format($indiExpected) . ' (staged / in-scope)');
        $this->info('  - Not in scope    : ' . number_format($notInScope) . ' (older than today, not fetched)');
        $this->info('  - Saved           : ' . number_format($this->totalIndicatorsProcessed));
        $this->info('  - Failed/skipped  : ' . number_format($this->totalIndicatorsSkipped));
        $this->info('  - Verified        : ' . number_format($indiVerified) . ' / ' . number_format($indiExpected) . " ({$indiPct}%)");
        $this->info('Complete            : ' . ($complete ? 'YES' : 'NO'));
        $this->info('Import status       : ' . $manifest['import_status']);

        if (!$this->option('skip-count')) {
            Artisan::call('app:MDCountIndicator', [], $this->output);
        }

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
