<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Support\Facades\Log;

class AuditPublishedFeeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:AuditPublishedFeeds 
                            {--source= : Filter by source (otx.alienvault or misp)}
                            {--mode=list : Audit mode (list or manifest)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sweep published indicators and log their status to MongoDB';

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
        $mode = $this->option('mode') ?: 'list';
        $this->info("🚀 Starting Feed Audit Sweep [Mode: $mode]...");
        
        try {
            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            
            $client = new MongoClient($DB_MONGO_KEY);
            $db = $client->sosecure_threatintelligent;
            $colEvents = $db->fx_otx_events;
            $colLogs = $db->fx_feed_published_logs;
            
            $sourceFilter = $this->option('source');
            
            // ── กำหนดช่วงเวลาตาม Mode ── 
            // ใช้เกณฑ์ 24 ชม. ย้อนหลัง (Rolling 24 Hours) ให้เท่ากันทั้งระบบเพื่อความแม่นยำในการ Compare
            $start = strtotime('-1 day') * 1000;
            $end = round(microtime(true) * 1000);

            // ── Query ──
            $query = [
                'status'          => ['$in' => [1, "1", true]],
                'public'          => ['$in' => [1, "1", true]],
                'modified'        => [
                    '$gte' => new UTCDateTime($start),
                    '$lte' => new UTCDateTime($end),
                ],
                'indicator_count' => ['$gt' => 0],
                '$or' => [
                    ['deleted_at' => null],
                    ['deleted_at' => ['$exists' => false]],
                ],
            ];
            
            // กรองเฉพาะ OTX เป็นหลักตามนโยบายการส่งออก
            $query['creator_org'] = 'OTX';
            
            if ($sourceFilter) {
                $query['source'] = $sourceFilter;
            }
            
            $this->info("🔍 Searching for events [Start: " . date('Y-m-d H:i:s', $start/1000) . "]...");
            
            $publicEvents = $colEvents->find($query, [
                'projection' => [
                    'pulse_id' => 1,
                    'attrCount' => 1,
                    'indicator_count' => 1,
                    'source' => 1,
                    'name' => 1,
                    'mips_uuid' => 1
                ]
            ]);
            
            $count = 0;
            $totalIndicators = 0;
            $eventList = []; 
            $now = now();
            $timestamp = new UTCDateTime(strtotime($now) * 1000);
            $actionTime = $now->format('Y-m-d H:i:s');
            
            foreach ($publicEvents as $event) {
                $count++;
                $indicatorCount = (int) ($event['attrCount'] ?? $event['indicator_count'] ?? 0);
                $totalIndicators += $indicatorCount;
                
                $eventName = $event['name'] ?? 'No Name';
                $eventList[] = [
                    'pulse_id' => $event['pulse_id'],
                    'mips_uuid' => $event['mips_uuid'] ?? null,
                    'name' => $eventName,
                    'count' => $indicatorCount
                ];

                $this->line("   - [{$event['pulse_id']}] $eventName ($indicatorCount indicators)");
            }
            
            $this->info("✅ Sweep Completed!");
            
            // บันทึก Summary แยกตาม Mode
            $logType = ($mode === 'manifest') ? 'manifest_report' : 'summary_report';
            $todayRegex = '^' . date('Y-m-d');
            
            // ค้นหา log ของวันนี้ที่มีอยู่แล้ว เพื่อนำมารวมกัน (Merge) ไม่ให้โดนทับ
            $existingLog = $colLogs->findOne(['type' => $logType, 'action_time' => ['$regex' => $todayRegex]]);
            
            $mergedEvents = [];
            if ($existingLog && isset($existingLog['events'])) {
                foreach ($existingLog['events'] as $e) {
                    $mergedEvents[(string)$e['pulse_id']] = [
                        'pulse_id' => $e['pulse_id'],
                        'mips_uuid' => $e['mips_uuid'] ?? null,
                        'name' => $e['name'] ?? 'No Name',
                        'count' => (int)($e['count'] ?? 0)
                    ];
                }
            }
            
            // นำ events ของรอบล่าสุดเข้าไปรวม (ถ้ามี pulse_id ซ้ำ จะใช้ข้อมูลล่าสุด)
            foreach ($eventList as $e) {
                $mergedEvents[(string)$e['pulse_id']] = $e;
            }
            
            $finalEvents = array_values($mergedEvents);
            
            // คำนวณยอดรวมใหม่ เพื่อป้องกันการบวกซ้ำซ้อน (เนื่องจาก query ดึงย้อนหลัง 24 ชม.)
            $finalTotalEvents = count($finalEvents);
            $finalTotalIndicators = 0;
            foreach ($finalEvents as $e) {
                $finalTotalIndicators += (int)($e['count'] ?? 0);
            }

            $this->info("📊 Events in this run: " . number_format($count) . " | Total today: " . number_format($finalTotalEvents));
            $this->info("💎 Indicators in this run: " . number_format($totalIndicators) . " | Total today: " . number_format($finalTotalIndicators));
            
            $colLogs->updateOne(
                ['type' => $logType, 'action_time' => ['$regex' => $todayRegex]],
                ['$set' => [
                    'timestamp' => $timestamp,
                    'action_time' => $actionTime,
                    'type' => $logType,
                    'mode' => $mode,
                    'total_public_events' => $finalTotalEvents,
                    'total_indicators' => $finalTotalIndicators,
                    'events' => $finalEvents,
                    'source' => 'audit_sweep_' . $mode
                ]],
                ['upsert' => true]
            );

        } catch (\Exception $e) {
            $this->error("❌ Error during sweep: " . $e->getMessage());
            Log::error("Audit Sweep Failed [$mode]: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
