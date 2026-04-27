<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateIocFeed extends Command
{
    protected $signature = 'ioc-feed:update {--all : Sync all data instead of just recent} {--lookback=300 : Lookback window in seconds (default 5 mins)} {--days= : Sync data from the last X days} {--weeks= : Sync data from the last X weeks} {--months= : Sync data from the last X months} {--limit= : Limit the number of indicators to sync}';
    protected $description = 'Sync fresh IoCs with enriched scores via delta-sync (Micro-Batching)';

    protected $totalSynced = 0;
    protected $totalSkippedNoEvent = 0;
    protected $totalSkippedNoScore = 0;

    public function handle()
    {
        ini_set('memory_limit', '1024M');
        $this->info('Starting IoC Feed Update...');

        $mongo_uri = config('app.DB_MONGO_DEV');
        $client = new \MongoDB\Client($mongo_uri);
        
        $sourceDb = 'sosecure_threatintelligent';
        $targetDb = config('iocfeed.mongodb.database', 'sosecure_threatintelligent_dev');
        
        $sourceColl = $client->{$sourceDb}->fx_transaction_otx_indicators_data;
        $refColl = $client->{$sourceDb}->fx_otx_events_indicator_ref;
        $detailColl = $client->{$sourceDb}->fx_otx_indicator_detail;
        $eventsColl = $client->{$sourceDb}->fx_otx_events;
        $targetColl = $client->{$targetDb}->fx_ioc_feeds;
        $logColl = $client->{$targetDb}->fx_ioc_export_logs;
        $progressColl = $client->{$targetDb}->job_progress;

        // ========== STEP 0: Manage Unique Index (Aggressive Cleanup for Dev) ==========
        $this->info("Refreshing unique index structure on target collection...");
        try {
            $indexes = iterator_to_array($targetColl->listIndexes());
            $hasUnique = false;
            foreach ($indexes as $index) {
                $name = $index->getName();
                if ($name === 'indicator_unique') {
                    $hasUnique = true;
                } else if ($name !== '_id_') {
                    $targetColl->dropIndex($name);
                    $this->info("Dropped old index: $name");
                }
            }

            if (!$hasUnique) {
                $this->warn("Unique rule not found. Cleaning up all records to enforce uniqueness on 'indicator'...");
                $targetColl->deleteMany([]); // Clear for a fresh start with the new structure
                $targetColl->createIndex(['indicator' => 1], ['unique' => true, 'name' => 'indicator_unique']);
                $this->info("Unique index created and collection cleared successfully.");
            }
        } catch (\Exception $e) {
            $this->warn("Index check failed, attempting fresh start: " . $e->getMessage());
            $targetColl->deleteMany([]);
            $targetColl->createIndex(['indicator' => 1], ['unique' => true, 'name' => 'indicator_unique']);
        }

        // ========== STEP 1: Manage Checkpoints (Resume Capability) ==========
        $jobName = 'ioc_feed_sync_all';
        $checkpoint = null;
        if ($this->option('all')) {
            $checkpoint = $progressColl->findOne(['job_name' => $jobName]);
            if ($checkpoint) {
                $this->warn("Checkpoint found! Resuming from _id: " . ($checkpoint['last_id'] ?? 'unknown'));
            }
        }

        // ========== STEP 1: บันทึก Log ข้อมูลเก่าก่อนเคลียร์ (ทำแค่วันละ 1 ครั้งตอนเที่ยงคืน หรือเมื่อสั่ง --all) ==========
        $existingCount = $targetColl->countDocuments();
        $isMidnight = date('H:i') === '00:00';
        
        if ($existingCount > 0 && ($this->option('all') && !$checkpoint || $isMidnight)) {
            $this->info("Logging {$existingCount} existing records before clearing...");

            $categoryCounts = [];
            $agg = $targetColl->aggregate([
                ['$group' => ['_id' => '$type', 'count' => ['$sum' => 1]]]
            ]);
            foreach ($agg as $row) {
                $key = (string)$row['_id'];
                if ($key === '') $key = 'unknown';
                $categoryCounts[$key] = $row['count'];
            }

            $logColl->insertOne([
                'action' => 'daily_refresh',
                'synced_at' => new \MongoDB\BSON\UTCDateTime(),
                'total_records' => $existingCount,
                'category_counts' => $categoryCounts,
                'note' => 'Snapshot before daily clear & re-sync',
                'created_at' => new \MongoDB\BSON\UTCDateTime(),
            ]);
            $this->info('Export log saved.');
        }

        // ========== STEP 2: เคลียร์ข้อมูลเก่า (ห้ามลบ - เก็บไว้ทั้งหมดตามที่ระบุ) ==========

        // ========== STEP 2.5: โหลดรายการ Whitelist เตรียมรอไว้ ==========
        $whitelistColl = $client->{$targetDb}->fx_ioc_whitelist;
        $whitelistDocs = $whitelistColl->find([], ['projection' => ['indicator' => 1]])->toArray();
        $whitelistList = [];
        foreach ($whitelistDocs as $wd) {
            $whitelistList[$wd['indicator']] = true;
        }
        $this->info("Loaded " . count($whitelistList) . " whitelisted indicators to bypass.");

        // ========== STEP 3: ดึงข้อมูลที่อัปเดตใหม่ในกรอบเวลา (Delta Sync) ==========
        $startOfDay = strtotime('today midnight');

        // Priority calculation for lookback
        $lookbackSeconds = (int)$this->option('lookback');
        $label = $lookbackSeconds . ' seconds';

        if ($this->option('months')) {
            $lookbackSeconds = (int)$this->option('months') * 30 * 86400;
            $label = $this->option('months') . ' months';
        } elseif ($this->option('weeks')) {
            $lookbackSeconds = (int)$this->option('weeks') * 7 * 86400;
            $label = $this->option('weeks') . ' weeks';
        } elseif ($this->option('days')) {
            $lookbackSeconds = (int)$this->option('days') * 86400;
            $label = $this->option('days') . ' days';
        }

        $lookbackWindow = new \MongoDB\BSON\UTCDateTime((time() - $lookbackSeconds) * 1000);
        
        $query = [];
        if (!$this->option('all')) {
            $this->info("Scanning for updates in the last $label...");
            
            // 1. ตรวจสอบข้อมูลจากตารางแม่ (Source) - รวมทั้งสร้างใหม่ และอัปเดต
            $query['$or'] = [
                ['created_at' => ['$gte' => clone $lookbackWindow]],
                ['updated_at' => ['$gte' => clone $lookbackWindow]],
                ['enriched_at' => ['$gte' => clone $lookbackWindow]]
            ];

            // 2. ตรวจสอบจากตาราง Detail (Enrichment Global) - ตารางนี้มักจะไม่ใหญ่มาก
            $detailColl = $client->{$sourceDb}->fx_otx_indicator_detail;
            $recentEnrichedDetail = $detailColl->find(
                ['$or' => [
                    ['updated_at' => ['$gte' => clone $lookbackWindow]],
                    ['enriched_at' => ['$gte' => clone $lookbackWindow]]
                ]],
                ['projection' => ['indicator_id' => 1], 'limit' => 5000]
            )->toArray();

            $enrichedIds = array_filter(array_column($recentEnrichedDetail, 'indicator_id'));
            $uniqueEnrichedIds = array_values(array_unique($enrichedIds));

            if (!empty($uniqueEnrichedIds)) {
                // ⚠️ แก้ไขจุดสำคัญ: แปลง ID ให้รองรับทั้งแบบ String และ Integer (เพื่อแก้ปัญหาหาไม่เจอ)
                $mixedIds = [];
                foreach ($uniqueEnrichedIds as $id) {
                    $mixedIds[] = $id; // ต้นฉบับ
                    if (is_numeric($id)) {
                        $mixedIds[] = (int)$id;     // แบบตัวเลข
                        $mixedIds[] = (string)$id;  // แบบข้อความ
                    }
                }
                $finalIds = array_values(array_unique($mixedIds));
                $query['$or'][] = ['indicator_id' => ['$in' => $finalIds]];
            }
        } elseif ($checkpoint && isset($checkpoint['last_id'])) {
            // ✅ Resume from checkpoint
            $query['_id'] = ['$gt' => $checkpoint['last_id']];
        }
        
        $this->info("Syncing indicators updated since " . ($this->option('all') ? 'All Time' : $label) . "...");

        $limit = (int) $this->option('limit');
        $options = [
            'sort' => ['_id' => 1] // 🔥 Mandatory for checkpointing
        ];
        if ($limit > 0) {
            $options['limit'] = $limit;
            $this->info("Limited to {$limit} records.");
        }

        $cursor = $sourceColl->find($query, $options);
        $chunk = [];
        foreach ($cursor as $doc) {
            $chunk[] = $doc;
            if (count($chunk) >= 1000) {
                $this->processChunk($chunk, $detailColl, $refColl, $eventsColl, $targetColl, $whitelistList, $progressColl, $jobName);
                $chunk = [];
            }
        }
        if (!empty($chunk)) {
            $this->processChunk($chunk, $detailColl, $refColl, $eventsColl, $targetColl, $whitelistList, $progressColl, $jobName);
        }

        // ========== STEP 5: บันทึก Log ข้อมูลใหม่ & Clear Checkpoint ==========
        if ($this->option('all')) {
            $progressColl->deleteOne(['job_name' => $jobName]);
        }

        $newCategoryCounts = [];
        $agg = $targetColl->aggregate([
            ['$group' => ['_id' => '$type', 'count' => ['$sum' => 1]]]
        ]);
        foreach ($agg as $row) {
            $key = (string)$row['_id'];
            if ($key === '') $key = 'unknown';
            $newCategoryCounts[$key] = $row['count'];
        }

        $logColl->insertOne([
            'action' => 'sync_completed',
            'synced_at' => new \MongoDB\BSON\UTCDateTime(),
            'total_records' => $this->totalSynced,
            'category_counts' => $newCategoryCounts,
            'note' => 'Fresh data synced with enriched scores (Chunked)',
            'created_at' => new \MongoDB\BSON\UTCDateTime(),
        ]);

        $this->info("Successfully synced {$this->totalSynced} indicators. Log saved.");

        // ========== STEP 6: Generate Static Feeds for Fast Export ==========
        $this->exportStaticFeeds($targetColl);
    }

    /**
     * Generate pre-formatted CSV files for fast API serving
     */
    private function exportStaticFeeds($targetColl)
    {
        $categories = ['all', 'ip_address', 'domain', 'hashfile'];
        $baseDir = base_path('Modules/IocFeed/Exports/');

        foreach ($categories as $cat) {
            $this->info("Generating static feed for category: $cat...");
            $filename = "{$cat}.csv";
            $filepath = $baseDir . $filename;
            
            // Default condition: Active and not whitelisted
            $query = ['status' => 1, 'is_whitelisted' => ['$ne' => true]];
            if ($cat !== 'all') {
                $query['type'] = $cat;
            }

            $handle = fopen($filepath, 'w');
            if ($handle === false) {
                $this->error("Failed to open file for writing: $filepath");
                continue;
            }

            // Get all indicators sorted by latest activity
            $cursor = $targetColl->find($query, ['sort' => ['timestamp_val' => -1]]);
            $count = 1;
            
            foreach ($cursor as $row) {
                $ip = trim($row['indicator'] ?? '');
                $score = $row['score'] ?? 0;
                $startTime = $row['ioc_timestamp'] ?? date('d/m/Y H:i');
                $endTime = $row['sending_timestamp'] ?? date('d/m/Y H:i');
                $category = trim($row['category'] ?? ($row['type'] ?? '-'));
                $sev = trim($row['severity'] ?? 'Low');
                $indId = trim($row['indicator_id'] ?? '');
                $evId = trim($row['event_id'] ?? '');
                $evName = str_replace(['|', "\r", "\n"], [' ', '', ''], $row['event_name'] ?? '');

                // รูปแบบ: Count,IP,score|start_time|end_time|category|severity|indicator_id|event_id|event_name
                $line = "{$count},{$ip},{$score}|{$startTime}|{$endTime}|{$category}|{$sev}|{$indId}|{$evId}|{$evName}";
                $line = str_replace(["\r", "\n"], ["", ""], $line);
                
                fwrite($handle, $line . PHP_EOL);
                $count++;
            }
            fclose($handle);
            $this->info("Created $filename with " . ($count - 1) . " rows.");
        }
    }

    private function processChunk($sourceData, $detailColl, $refColl, $eventsColl, $targetColl, $whitelistList, $progressColl = null, $jobName = null)
    {
        $indicatorIds = [];
        foreach ($sourceData as $doc) {
            if (!empty($doc['indicator_id'])) {
                $indicatorIds[] = $doc['indicator_id'];
                if (is_numeric($doc['indicator_id'])) {
                    $indicatorIds[] = (int)$doc['indicator_id'];
                    $indicatorIds[] = (string)$doc['indicator_id'];
                }
            }
        }
        $uniqueIds = array_values(array_unique((array) $indicatorIds));

        // ------ Batch lookup: indicator_detail (enriched scores) ------
        $detailMap = [];
        if (!empty($uniqueIds)) {
            $details = $detailColl->find(
                ['indicator_id' => ['$in' => $uniqueIds]],
                ['projection' => ['indicator_id' => 1, 'attribute_score' => 1, 'attribute_serverity' => 1, 'updated_at' => 1, 'enriched_at' => 1]]
            );
            foreach ($details as $d) {
                $detailMap[(string)$d['indicator_id']] = [
                    'score' => $d['attribute_score'] ?? null,
                    'severity' => $d['attribute_serverity'] ?? null,
                    'updated_at' => $d['updated_at'] ?? null,
                    'enriched_at' => $d['enriched_at'] ?? null,
                ];
                // Store both mapping types to be safe
                if (is_numeric($d['indicator_id'])) {
                    $detailMap[(int)$d['indicator_id']] = &$detailMap[(string)$d['indicator_id']];
                }
            }
        }

        // ------ Batch lookup: ref (pulse_id + score) ------
        $refMap = [];
        $allPulseIds = [];
        if (!empty($uniqueIds)) {
            $refs = $refColl->find(
                ['indicator_id' => ['$in' => $uniqueIds]],
                ['projection' => [
                    'indicator_id' => 1, 'pulse_id' => 1, 
                    'attribute_score' => 1, 'attribute_serverity' => 1, 
                    'score' => 1, 'severity' => 1,
                    'enriched_at' => 1, 'updated_at' => 1
                ]]
            );
            foreach ($refs as $ref) {
                $key = (string)$ref['indicator_id'];
                $pId = (string)($ref['pulse_id'] ?? '');
                if ($pId !== '') {
                    $allPulseIds[] = $pId;
                    $refMap[$key][$pId] = [
                        'score' => $ref['score'] ?? ($ref['attribute_score'] ?? null),
                        'severity' => $ref['severity'] ?? ($ref['attribute_serverity'] ?? null),
                        'enriched_at' => $ref['enriched_at'] ?? ($ref['updated_at'] ?? null),
                    ];
                    // Also map for numeric key if applicable
                    if (is_numeric($key)) {
                        $refMap[(int)$key][$pId] = &$refMap[$key][$pId];
                    }
                }
            }
        }

        // ------ Batch lookup: events (pulse_id → name) ------
        foreach ($sourceData as $doc) {
            $pId = (string)($doc['pulse_id'] ?? '');
            if ($pId !== '') {
                $allPulseIds[] = $pId;
            }
        }
        $pulseIds = array_values(array_unique($allPulseIds));
        $pulseMap = [];
        if (!empty($pulseIds)) {
            $events = $eventsColl->find(
                ['pulse_id' => ['$in' => $pulseIds]],
                ['projection' => ['pulse_id' => 1, 'name' => 1, 'public' => 1]]
            );
            foreach ($events as $ev) {
                // $eid = (string)($ev['id'] ?? '');
                $eid = (string)($ev['pulse_id'] ?? '');
                if ($eid !== '') {
                    $pulseMap[$eid] = [
                        'name' => $ev['name'] ?? '',
                        'public' => $ev['public'] ?? 0
                    ];
                }
            }
        }

        // ========== สร้าง batch upsert พร้อม enriched data ==========
        $batch = [];
        foreach ($sourceData as $doc) {
            $indId = (string)($doc['indicator_id'] ?? '');
            $type = $this->mapType($doc['type'] ?? '');
            $indicatorValue = (string)($doc['indicator'] ?? '');

            if (empty($indicatorValue)) continue;

            // ⛔️ เทียบ Whitelist ว่ามีอยู่ไหม
            $isWhitelisted = isset($whitelistList[$indicatorValue]);

            // ค้นหาคดีทั้งหมด (pulse_ids) ที่ IP ตัวนี้เข้าไปมีส่วนพัวพัน (1-to-Many)
            $eventsForInd = $refMap[$indId] ?? [];

            // ถ้าไม่มีประวัติคดีเลย ให้เช็คเผื่อมีติดมาจาก Source เพียวๆ
            $sourcePulseId = (string)($doc['pulse_id'] ?? '');
            if (empty($eventsForInd) && $sourcePulseId !== '') {
                $eventsForInd[$sourcePulseId] = [
                    'score' => $doc['score'] ?? null,
                    'severity' => null,
                    'enriched_at' => null
                ];
            }

            if (empty($eventsForInd)) {
                $this->totalSkippedNoEvent++;
                continue;
            }

            // 🔍 คัดเลือก Event ที่ "ล่าสุด" ที่สุดมาเพียงหนึ่งเดียว สำหรับ IP นี้
            $latestPulseId = null;
            $latestRefData = null;
            $maxTs = 0;

            foreach ($eventsForInd as $pulseId => $refData) {
                $ts = $this->getLatestTimestamp($doc, $refData, $detailMap[$indId] ?? null);
                if ($ts >= $maxTs) {
                    $maxTs = $ts;
                    $latestPulseId = $pulseId;
                    $latestRefData = $refData;
                }
            }

            if (!$latestPulseId) continue;

            $evtInfo = $pulseMap[$latestPulseId] ?? null;
            $eventName = $evtInfo['name'] ?? '';
            $isPublic = (int)($evtInfo['public'] ?? 0);

            if (empty($eventName)) {
                $this->totalSkippedNoEvent++;
                continue;
            }

            // 🚩 ตรวจสอบว่ามีข้อมูล Score หรือ Severity หรือไม่ (ถ้าเป็น 0 เอามา แต่ถ้าเป็น null ทั้งคู่ไม่เอา)
            $rawScore = $latestRefData['score'] ?? ($detail['score'] ?? ($doc['score'] ?? null));
            $rawSeverity = $latestRefData['severity'] ?? ($detail['severity'] ?? null);

            // ต้องมี Score (จะ 0 ก็ได้) หรือมี Severity
            if ($rawScore === null && $rawSeverity === null) {
                $this->totalSkippedNoScore++;
                continue;
            }

            $score = (int)($rawScore ?? 0);
            $severity = $rawSeverity;
            
            if (!$severity || $severity === 'Informational') {
                $severity = $this->mapSeverity($score);
            }

            // เลือกวันที่ที่เหมาะสมสำหรับ ioc_timestamp (ลำดับ: Ref Update → Source Created)
            $iocDateDoc = $latestRefData['enriched_at'] ?? ($doc['created_at'] ?? null);
            $iocTimestamp = $iocDateDoc ? date('d/m/Y H:i', $iocDateDoc->toDateTime()->getTimestamp()) : date('d/m/Y H:i');

            // ⚠️ ใช้ 'indicator' เป็น Unique Filter (1 IP ต่อ 1 แถว)
            // ถ้ามี IP เดียวกันซ้ำใน Chunk นี้ ตัวที่มาทีหลัง (หรือมี timestamp ใหม่กว่า) จะทับตัวเก่า
            $batch[$indicatorValue] = [
                'updateOne' => [
                    ['indicator' => $indicatorValue],
                    ['$set' => [
                        'indicator' => $indicatorValue,
                        'type' => $type,
                        'score' => $score,
                        'severity' => $severity,
                        'ioc_timestamp' => $iocTimestamp,
                        'sending_timestamp' => date('d/m/Y H:i'),
                        'timestamp_val' => time(),
                        'category' => $doc['type'] ?? '-',
                        'indicator_id' => $indId,
                        'event_id' => $latestPulseId,
                        'event_name' => str_replace(["\r", "\n"], ['', ''], $eventName),
                        'status' => 1,
                        'is_whitelisted' => $isWhitelisted,
                        'is_public' => $isPublic,
                        'updated_at' => new \MongoDB\BSON\UTCDateTime(),
                    ],
                    '$setOnInsert' => [
                        'created_at' => new \MongoDB\BSON\UTCDateTime(),
                    ]],
                    ['upsert' => true]
                ]
            ];
        }

        if (!empty($batch)) {
            try {
                $targetColl->bulkWrite(array_values($batch));
                $this->totalSynced += count($batch);
                $this->info("Synced " . count($batch) . " unique indicators (Total: {$this->totalSynced})");
                
                // 💾 บันทึก Checkpoint ทันทีที่ Sync แต่ละ Chunk สำเร็จ (เฉพาะเคส --all)
                if ($progressColl && $jobName) {
                    $lastDoc = end($sourceData);
                    if ($lastDoc && isset($lastDoc['_id'])) {
                        $progressColl->updateOne(
                            ['job_name' => $jobName],
                            ['$set' => [
                                'job_name' => $jobName,
                                'last_id' => $lastDoc['_id'],
                                'total_processed' => $this->totalSynced,
                                'updated_at' => new \MongoDB\BSON\UTCDateTime(),
                                'status' => 'running'
                            ]],
                            ['upsert' => true]
                        );
                    }
                }
            } catch (\MongoDB\Driver\Exception\BulkWriteException $e) {
                $this->error("BulkWrite error: " . $e->getMessage());
                // ถ้ายังเฟลอยู่ ให้ลองประมวลผลทีละรายการ (ช่วยให้ทราบตัวที่มีปัญหา)
                foreach (array_values($batch) as $op) {
                    try {
                        $targetColl->bulkWrite([$op]);
                    } catch (\Exception $ex) {
                        $this->warn("Skipping record due to error: " . $ex->getMessage());
                    }
                }
            }
        }
    }

    private function getLatestTimestamp($doc, $refData, $detail = null)
    {
        // 1. เริ่มจากเวลาอัปเดตของตารางแม่ (Source)
        $timestamp = 0;
        if (!empty($doc['updated_at'])) $timestamp = $doc['updated_at']->toDateTime()->getTimestamp();
        elseif (!empty($doc['enriched_at'])) $timestamp = $doc['enriched_at']->toDateTime()->getTimestamp();
        elseif (!empty($doc['created_at'])) $timestamp = $doc['created_at']->toDateTime()->getTimestamp();
        else $timestamp = time();
        
        // 2. ถ้าเวลาการกระทำใน Ref (Pulse) ใหม่กว่า ให้ใช้ค่านั้น (เคสส่วนใหญ่ของการกด Enrich)
        if (!empty($refData['enriched_at'])) {
            $enrichTime = $refData['enriched_at']->toDateTime()->getTimestamp();
            if ($enrichTime > $timestamp) $timestamp = $enrichTime;
        }

        // 3. ถ้าเวลาการกด Enrich ในตาราง Detail ใหม่กว่า ให้ใช้ค่านั้นด้วย
        if (!empty($detail['updated_at'])) {
            $detailUpTime = $detail['updated_at']->toDateTime()->getTimestamp();
            if ($detailUpTime > $timestamp) $timestamp = $detailUpTime;
        }
        if (!empty($detail['enriched_at'])) {
            $detailEnTime = $detail['enriched_at']->toDateTime()->getTimestamp();
            if ($detailEnTime > $timestamp) $timestamp = $detailEnTime;
        }
        
        return $timestamp;
    }

    private function mapType($otxType)
    {
        $mapping = [
            'IPv4' => 'ip_address',
            'IPv6' => 'ip_address',
            'domain' => 'domain',
            'hostname' => 'domain',
            'FileHash-MD5' => 'hashfile',
            'FileHash-SHA1' => 'hashfile',
            'FileHash-SHA256' => 'hashfile',
        ];
        return $mapping[$otxType] ?? null;
    }

    private function mapSeverity($score)
    {
        if ($score >= 8) return 'Critical';
        if ($score >= 6) return 'High';
        if ($score >= 4) return 'Medium';
        if ($score > 0) return 'Low';
        return 'Informational';
    }
}
