<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;

class IOCCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:IOCCleanup
                            {--batch=2000 : Batch size for deletion}
                            {--dry-run : Show counts without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup OTX MongoDB collections based on retention policy';

    /**
     * Retention days per severity level (for enriched refs).
     * Each entry maps a display label to ['days' => int, 'values' => [...variants in DB]]
     */
    protected $retentionBySeverity = [
        'Critical'      => ['days' => 730, 'values' => ['Critical', 'critical']],
        'High'          => ['days' => 365, 'values' => ['High', 'high']],
        'Medium'        => ['days' => 180, 'values' => ['Medium', 'medium']],
        'Low'           => ['days' => 90,  'values' => ['Low', 'low']],
        'Very Low'      => ['days' => 30,  'values' => ['Very Low', 'Very low', 'very low']],
        'Informational' => ['days' => 7,   'values' => ['Informational', 'informational', 'Info', 'info']],
    ];

    /**
     * Retention days for non-enriched refs (imported_at == null).
     */
    protected $nonEnrichedRetentionDays = 90;

    /**
     * Safety: only delete orphan events older than this many days.
     * Set to 0 to delete immediately.
     */
    protected $orphanEventSafetyDays = 0;

    /**
     * Safety: only delete orphan details older than this many days.
     * Set to 0 to delete immediately.
     */
    protected $orphanDetailSafetyDays = 0;

    /**
     * MongoDB client instance.
     */
    protected $clientMD;

    /**
     * MongoDB database instance.
     */
    protected $db;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $startTime = microtime(true);
        $batchSize = (int) $this->option('batch');
        $dryRun    = (bool) $this->option('dry-run');

        if ($batchSize < 1) {
            $this->error('Batch size must be at least 1.');
            return 1;
        }

        $this->info('============================================================');
        $this->info('  IOC MongoDB Cleanup Command');
        $this->info('============================================================');
        $this->info('  Batch size : ' . $batchSize);
        $this->info('  Mode       : ' . ($dryRun ? 'DRY-RUN (no data will be deleted)' : 'LIVE DELETE'));
        $this->info('  Time       : ' . now()->format('Y-m-d H:i:s'));
        $this->info('============================================================');
        $this->line('');

        // --- Connect to MongoDB ---
        try {
            $DB_MONGO_KEY = env('DB_MONGO_STOREDATA', '');
            if (empty($DB_MONGO_KEY)) {
                $this->error('DB_MONGO_STOREDATA is not set in .env');
                return 1;
            }
            $this->clientMD = new \MongoDB\Client($DB_MONGO_KEY);
            $this->db = $this->clientMD->sosecure_threatintelligent;
            $this->info('[Connected] MongoDB: sosecure_threatintelligent');
            $this->line('');
        } catch (Exception $e) {
            $this->error('MongoDB connection failed: ' . $e->getMessage());
            return 1;
        }

        // --- Show collection sizes (estimated = fast, uses metadata) ---
        $totalRefs    = $this->db->fx_otx_events_indicator_ref->estimatedDocumentCount();
        $totalEvents  = $this->db->fx_otx_events->estimatedDocumentCount();
        $totalDetails = $this->db->fx_otx_indicator_detail->estimatedDocumentCount();
        $this->info('  Collection sizes:');
        $this->info("    fx_otx_events_indicator_ref : {$totalRefs}");
        $this->info("    fx_otx_events               : {$totalEvents}");
        $this->info("    fx_otx_indicator_detail      : {$totalDetails}");
        $this->line('');

        // --- Ensure indexes for fast queries ---
        $this->info('  Ensuring indexes for cleanup queries...');
        $this->ensureIndexes();
        $this->info('  Indexes ready.');
        $this->line('');

        // --- Phase 1: Cleanup indicator refs ---
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  PHASE 1: Cleanup fx_otx_events_indicator_ref (total: ' . $totalRefs . ')');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('');

        $refStats = $this->cleanupIndicatorRefs($batchSize, $dryRun);

        // --- Phase 2: Cleanup orphan events ---
        $this->line('');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  PHASE 2: Cleanup orphan fx_otx_events');
        $this->info('  (events with no refs, created_at > ' . $this->orphanEventSafetyDays . ' days ago)');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('');

        $orphanEventsDeleted = $this->cleanupOrphanEvents($batchSize, $dryRun);

        // --- Phase 3: Cleanup orphan indicator details ---
        $this->line('');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  PHASE 3: Cleanup orphan fx_otx_indicator_detail');
        $this->info('  (details with no refs, created_at > ' . $this->orphanDetailSafetyDays . ' days ago)');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line('');

        $orphanDetailsDeleted = $this->cleanupOrphanDetails($batchSize, $dryRun);

        // --- Summary ---
        $elapsed = round(microtime(true) - $startTime, 2);

        $this->line('');
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                        SUMMARY                              ║');
        $this->info('╠══════════════════════════════════════════════════════════════╣');

        if ($dryRun) {
            $this->warn('║  ⚠  DRY-RUN MODE — No data was deleted                     ║');
            $this->info('╠══════════════════════════════════════════════════════════════╣');
        }

        // Phase 1 summary table
        $this->info('║  Phase 1: Indicator Refs                                     ║');
        $this->info('╟──────────────────────────────────────────────────────────────╢');

        $totalRefsDeleted = 0;
        foreach ($this->retentionBySeverity as $severity => $config) {
            $days  = $config['days'];
            $countEnriched  = $refStats['enriched'][$severity] ?? 0;
            $countScoreOnly = $refStats['score_only'][$severity] ?? 0;
            $count = $countEnriched + $countScoreOnly;
            $totalRefsDeleted += $count;
            $label = str_pad("    {$severity} ({$days}d)", 40);
            $val   = str_pad((string)$count, 10, ' ', STR_PAD_LEFT);
            $this->info("║  {$label} {$val}    ║");
        }
        $nonEnrichedCount = $refStats['non_enriched'] ?? 0;
        $totalRefsDeleted += $nonEnrichedCount;
        $label = str_pad("    Non-enriched ({$this->nonEnrichedRetentionDays}d)", 40);
        $val   = str_pad((string)$nonEnrichedCount, 10, ' ', STR_PAD_LEFT);
        $this->info("║  {$label} {$val}    ║");

        $this->info("╟──────────────────────────────────────────────────────────────╢");
        $label = str_pad("    DELETE total", 40);
        $val   = str_pad((string)$totalRefsDeleted, 10, ' ', STR_PAD_LEFT);
        $this->info("║  {$label} {$val}    ║");

        $remainingRefs = $totalRefs - $totalRefsDeleted;
        $label = str_pad("    KEEP (remaining)", 40);
        $val   = str_pad((string)$remainingRefs, 10, ' ', STR_PAD_LEFT);
        $this->info("║  {$label} {$val}    ║");

        $label = str_pad("    Total in DB", 40);
        $val   = str_pad((string)$totalRefs, 10, ' ', STR_PAD_LEFT);
        $this->info("║  {$label} {$val}    ║");

        // Phase 2 & 3 summary
        $this->info('╟──────────────────────────────────────────────────────────────╢');
        $label = str_pad("  Phase 2: Orphan events", 40);
        $val   = str_pad((string)$orphanEventsDeleted, 10, ' ', STR_PAD_LEFT);
        $this->info("║  {$label} {$val}    ║");

        $label = str_pad("  Phase 3: Orphan indicator details", 40);
        $val   = str_pad((string)$orphanDetailsDeleted, 10, ' ', STR_PAD_LEFT);
        $this->info("║  {$label} {$val}    ║");

        $this->info('╟──────────────────────────────────────────────────────────────╢');
        $label = str_pad("  Elapsed time", 40);
        $val   = str_pad("{$elapsed}s", 10, ' ', STR_PAD_LEFT);
        $this->info("║  {$label} {$val}    ║");
        $this->info('╚══════════════════════════════════════════════════════════════╝');

        $this->line('');
        $this->info($dryRun ? 'Dry-run completed.' : 'Cleanup completed successfully.');

        return 0;
    }

    /**
     * Phase 1: Cleanup fx_otx_events_indicator_ref
     *
     * 1A) Enriched refs — มี attribute_serverity → ลบตาม severity + created_at cutoff
     * 1B) Score-only refs — มี attribute_score แต่ไม่มี attribute_serverity → อิงตาม score
     * 1C) Non-enriched refs — ไม่มี attribute_score → ลบตาม created_at > 90 days
     */
    protected function cleanupIndicatorRefs(int $batchSize, bool $dryRun): array
    {
        $collection = $this->db->fx_otx_events_indicator_ref;
        $stats = [
            'enriched'     => [],
            'score_only'   => [],
            'non_enriched' => 0,
        ];

        // Score ranges → severity mapping
        $scoreRanges = [
            'Critical'      => ['min' => 9, 'max' => 99],
            'High'          => ['min' => 7, 'max' => 8],
            'Medium'        => ['min' => 4, 'max' => 6],
            'Low'           => ['min' => 2, 'max' => 3],
            'Very Low'      => ['min' => 1, 'max' => 1],
            'Informational' => ['min' => 0, 'max' => 0],
        ];

        // Common condition for "no severity"
        $noSeverityCondition = ['$or' => [
            ['attribute_serverity' => null],
            ['attribute_serverity' => ''],
            ['attribute_serverity' => ['$exists' => false]],
        ]];

        // --- 1A: Enriched refs (by severity) ---
        $this->info('  [1A] Enriched refs (by severity, based on created_at):');
        foreach ($this->retentionBySeverity as $severity => $config) {
            $cutoff = $this->makeCutoffDate($config['days']);
            $query = [
                'attribute_serverity' => ['$in' => $config['values']],
                'created_at' => ['$lt' => $cutoff]
            ];

            if ($dryRun) {
                // Use countDocuments which is fast on indexed fields
                $count = $collection->countDocuments($query, ['maxTimeMS' => 60000]);
                $stats['enriched'][$severity] = $count;
                $this->info("      {$severity} (>{$config['days']}d): would delete {$count} records");
            } else {
                $deleted = $this->batchDelete($collection, $query, $batchSize, false);
                $stats['enriched'][$severity] = $deleted;
                $this->info("      {$severity} (>{$config['days']}d): deleted {$deleted} records");
            }
        }

        // --- 1B: Score-only refs (no severity) ---
        $this->line('');
        $this->info('  [1B] Score-only refs (has score, no severity):');
        foreach ($scoreRanges as $severityLabel => $range) {
            $config = $this->retentionBySeverity[$severityLabel];
            $cutoff = $this->makeCutoffDate($config['days']);

            $scoreValues = [];
            for ($s = $range['min']; $s <= $range['max']; $s++) {
                $scoreValues[] = (string)$s;
            }

            if ($dryRun) {
                // For Dry-run speed: Query ONLY by score + created_at
                // This matches the index {attribute_score:1, created_at:1} perfectly.
                // We skip checking "severity is null" to avoid slow $or scans.
                // It might slightly overcount (if a record has BOTH score & severity),
                // but that's acceptable for a fast dry-run estimate on 14M records.
                $query = [
                    'attribute_score' => ['$in' => $scoreValues],
                    'created_at'      => ['$lt' => $cutoff],
                ];
                $count = $collection->countDocuments($query, ['maxTimeMS' => 60000]);
                $stats['score_only'][$severityLabel] = $count;
                $this->info("      {$severityLabel} (score {$range['min']}-{$range['max']}, >{$config['days']}d): would delete ~{$count} records");
            } else {
                // Live mode uses FULL ACCURACY
                $query = array_merge($noSeverityCondition, [
                    'attribute_score' => ['$in' => $scoreValues],
                    'created_at'      => ['$lt' => $cutoff],
                ]);
                $deleted = $this->batchDelete($collection, $query, $batchSize, false);
                $stats['score_only'][$severityLabel] = $deleted;
                $this->info("      {$severityLabel} (score {$range['min']}-{$range['max']}, >{$config['days']}d): deleted {$deleted} records");
            }
        }

        // --- 1C: Non-enriched refs ---
        $this->line('');
        $this->info('  [1C] Non-enriched refs (no score/severity, created_at > ' . $this->nonEnrichedRetentionDays . 'd):');
        $cutoff = $this->makeCutoffDate($this->nonEnrichedRetentionDays);

        if ($dryRun) {
            // For Dry-run speed: Simplified query
            // We only check if attribute_score exists:false or is null.
            // We skip the komplex $or checks for empty strings '0' etc to avoid slow scans.
            // This is an ESTIMATE for speed.
            $query = [
                'attribute_score' => ['$exists' => false],
                'created_at'      => ['$lt' => $cutoff],
            ];
            // If the count is still too slow, we catch the timeout and show a message
            try {
                // Increase timeout to 5 minutes (300000ms) to ensure we get a result
                $count = $collection->countDocuments($query, ['maxTimeMS' => 300000]);
                $stats['non_enriched'] = $count;
                $this->info("      Non-enriched (>{$this->nonEnrichedRetentionDays}d): would delete ~{$count} records (estimate)");
            } catch (\Exception $e) {
                $this->warn("      Non-enriched: prediction skipped (timed out). Live run will process correctly.");
                $stats['non_enriched'] = -1; // Indicate unknown
            }
        } else {
            // Live mode uses FULL ACCURACY
            $query = [
                '$and' => [
                    ['$or' => [
                        ['attribute_score' => null],
                        ['attribute_score' => ''],
                        ['attribute_score' => '0'],
                        ['attribute_score' => ['$exists' => false]],
                    ]],
                    $noSeverityCondition,
                    ['created_at' => ['$lt' => $cutoff]],
                ],
            ];
            $deleted = $this->batchDelete($collection, $query, $batchSize, false);
            $stats['non_enriched'] = $deleted;
            $this->info("      Non-enriched (>{$this->nonEnrichedRetentionDays}d): deleted {$deleted} records");
        }

        return $stats;
    }



    /**
     * Phase 2: Cleanup orphan events
     *
     * Delete fx_otx_events that have no matching pulse_id in fx_otx_events_indicator_ref
     * AND created_at is older than safety threshold (30 days).
     *
     * @param int  $batchSize
     * @param bool $dryRun
     * @return int
     */
    protected function cleanupOrphanEvents(int $batchSize, bool $dryRun): int
    {
        $eventsCol = $this->db->fx_otx_events;
        $refCol    = $this->db->fx_otx_events_indicator_ref;

        $safetyCutoff = $this->makeCutoffDate($this->orphanEventSafetyDays);

        $eventQuery = [
            'created_at' => ['$lt' => $safetyCutoff],
        ];

        $totalDeleted = 0;
        $totalScanned = 0;
        $lastId = null;

        while (true) {
            $batchQuery = $eventQuery;
            if ($lastId !== null) {
                $batchQuery['_id'] = ['$gt' => $lastId];
            }

            $events = $eventsCol->find($batchQuery, [
                'projection' => ['_id' => 1, 'pulse_id' => 1],
                'sort'       => ['_id' => 1],
                'limit'      => $batchSize,
            ])->toArray();

            if (empty($events)) {
                break;
            }

            $totalScanned += count($events);
            $lastId = end($events)['_id'];

            $pulseIds = [];
            foreach ($events as $event) {
                $pid = $event['pulse_id'] ?? null;
                if ($pid !== null) {
                    $pulseIds[] = $pid;
                }
            }

            if (empty($pulseIds)) {
                continue;
            }

            $existingRefs = $refCol->distinct('pulse_id', [
                'pulse_id' => ['$in' => $pulseIds],
            ]);
            $existingRefSet = array_flip($existingRefs);

            $orphanIds = [];
            foreach ($events as $event) {
                $pid = $event['pulse_id'] ?? null;
                if ($pid !== null && !isset($existingRefSet[$pid])) {
                    $orphanIds[] = $event['_id'];
                }
            }

            if (!empty($orphanIds)) {
                if ($dryRun) {
                    $totalDeleted += count($orphanIds);
                } else {
                    $result = $eventsCol->deleteMany([
                        '_id' => ['$in' => $orphanIds],
                    ]);
                    $totalDeleted += $result->getDeletedCount();
                }
            }

            $this->output->write("\r      Scanned: {$totalScanned}, Orphans found: {$totalDeleted}");
        }

        $this->line('');
        $action = $dryRun ? 'would delete' : 'deleted';
        $this->info("      Total orphan events: {$action} {$totalDeleted} records (scanned {$totalScanned})");

        return $totalDeleted;
    }

    /**
     * Phase 3: Cleanup orphan indicator details
     *
     * Delete fx_otx_indicator_detail that have no matching indicator_id in fx_otx_events_indicator_ref
     * AND created_at is older than safety threshold (60 days).
     *
     * @param int  $batchSize
     * @param bool $dryRun
     * @return int
     */
    protected function cleanupOrphanDetails(int $batchSize, bool $dryRun): int
    {
        $detailCol = $this->db->fx_otx_indicator_detail;
        $refCol    = $this->db->fx_otx_events_indicator_ref;

        $safetyCutoff = $this->makeCutoffDate($this->orphanDetailSafetyDays);

        $detailQuery = [
            'created_at' => ['$lt' => $safetyCutoff],
        ];

        $totalDeleted = 0;
        $totalScanned = 0;
        $lastId = null;

        while (true) {
            $batchQuery = $detailQuery;
            if ($lastId !== null) {
                $batchQuery['_id'] = ['$gt' => $lastId];
            }

            $details = $detailCol->find($batchQuery, [
                'projection' => ['_id' => 1, 'indicator_id' => 1],
                'sort'       => ['_id' => 1],
                'limit'      => $batchSize,
            ])->toArray();

            if (empty($details)) {
                break;
            }

            $totalScanned += count($details);
            $lastId = end($details)['_id'];

            $indicatorIds = [];
            foreach ($details as $detail) {
                $iid = $detail['indicator_id'] ?? null;
                if ($iid !== null) {
                    $indicatorIds[] = $iid;
                }
            }

            if (empty($indicatorIds)) {
                continue;
            }

            $existingRefs = $refCol->distinct('indicator_id', [
                'indicator_id' => ['$in' => $indicatorIds],
            ]);
            $existingRefSet = array_flip($existingRefs);

            $orphanIds = [];
            foreach ($details as $detail) {
                $iid = $detail['indicator_id'] ?? null;
                if ($iid !== null && !isset($existingRefSet[$iid])) {
                    $orphanIds[] = $detail['_id'];
                }
            }

            if (!empty($orphanIds)) {
                if ($dryRun) {
                    $totalDeleted += count($orphanIds);
                } else {
                    $result = $detailCol->deleteMany([
                        '_id' => ['$in' => $orphanIds],
                    ]);
                    $totalDeleted += $result->getDeletedCount();
                }
            }

            $this->output->write("\r      Scanned: {$totalScanned}, Orphans found: {$totalDeleted}");
        }

        $this->line('');
        $action = $dryRun ? 'would delete' : 'deleted';
        $this->info("      Total orphan details: {$action} {$totalDeleted} records (scanned {$totalScanned})");

        return $totalDeleted;
    }

    /**
     * Batch delete records matching query.
     *
     * @param \MongoDB\Collection $collection
     * @param array               $query
     * @param int                 $batchSize
     * @param bool                $dryRun
     * @return int Total deleted (or would-delete) count
     */
    protected function batchDelete($collection, array $query, int $batchSize, bool $dryRun): int
    {
        if ($dryRun) {
            return $collection->countDocuments($query);
        }

        $totalDeleted = 0;

        while (true) {
            $docs = $collection->find($query, [
                'projection' => ['_id' => 1],
                'limit'      => $batchSize,
            ])->toArray();

            if (empty($docs)) {
                break;
            }

            $ids = array_map(function ($doc) {
                return $doc['_id'];
            }, $docs);

            $result = $collection->deleteMany([
                '_id' => ['$in' => $ids],
            ]);

            $totalDeleted += $result->getDeletedCount();
        }

        return $totalDeleted;
    }

    /**
     * Create a MongoDB UTCDateTime for (now - $days).
     *
     * @param int $days
     * @return UTCDateTime
     */
    protected function makeCutoffDate(int $days): UTCDateTime
    {
        $timestamp = strtotime("-{$days} days");
        return new UTCDateTime($timestamp * 1000);
    }

    /**
     * Ensure MongoDB indexes exist for fast cleanup queries.
     * createIndex is idempotent — if index already exists, it's a no-op.
     */
    protected function ensureIndexes(): void
    {
        $ref = $this->db->fx_otx_events_indicator_ref;
        $indexOpts = ['background' => true];

        $indexes = [
            ['col' => $ref, 'keys' => ['attribute_serverity' => 1, 'created_at' => 1], 'name' => 'cleanup_severity_created'],
            ['col' => $ref, 'keys' => ['attribute_score' => 1, 'created_at' => 1], 'name' => 'cleanup_score_created'],
            ['col' => $ref, 'keys' => ['pulse_id' => 1], 'name' => 'cleanup_pulse_id'],
            ['col' => $ref, 'keys' => ['indicator_id' => 1], 'name' => 'cleanup_indicator_id'],
            ['col' => $this->db->fx_otx_events, 'keys' => ['created_at' => 1], 'name' => 'cleanup_created_at'],
            ['col' => $this->db->fx_otx_indicator_detail, 'keys' => ['created_at' => 1], 'name' => 'cleanup_created_at'],
        ];

        foreach ($indexes as $idx) {
            try {
                $idx['col']->createIndex($idx['keys'], array_merge($indexOpts, ['name' => $idx['name']]));
                $this->info("    ✓ {$idx['name']}");
            } catch (\Exception $e) {
                $this->warn("    ⚠ {$idx['name']}: skipped ({$e->getMessage()})");
            }
        }
    }
}
