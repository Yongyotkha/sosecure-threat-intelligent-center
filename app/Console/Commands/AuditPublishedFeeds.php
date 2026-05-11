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
    protected $signature = 'app:AuditPublishedFeeds {--source= : Filter by source (otx.alienvault or misp)}';

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
        $this->info("🚀 Starting Feed Audit Sweep...");
        
        try {
            $DB_MONGO_KEY = env("DB_MONGO_STOREDATA", "");
            if (empty($DB_MONGO_KEY)) {
                $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            }
            
            $client = new MongoClient($DB_MONGO_KEY);
            $db = $client->sosecure_threatintelligent;
            $colEvents = $db->fx_otx_events;
            $colLogs = $db->fx_feed_published_logs;
            
            $sourceFilter = $this->option('source');
            
            $todayStart = new UTCDateTime(strtotime(date('Y-m-d 00:00:00')) * 1000);
            $todayEnd = new UTCDateTime(strtotime(date('Y-m-d 23:59:59')) * 1000);

            $query = [
                'public' => ['$in' => [1, "1"]],
                'modified' => ['$gte' => $todayStart, '$lte' => $todayEnd],
                'indicator_count' => ['$ne' => 0],
                'creator_org' => 'OTX',
                '$or' => [
                    ['deleted_at' => null],
                    ['deleted_at' => ['$exists' => false]],
                ],
            ];
            
            if ($sourceFilter) {
                $query['source'] = $sourceFilter;
            }
            
            $this->info("🔍 Searching for public events" . ($sourceFilter ? " from source: $sourceFilter" : "") . "...");
            
            $publicEvents = $colEvents->find($query, [
                'projection' => [
                    'pulse_id' => 1,
                    'attrCount' => 1,
                    'indicator_count' => 1,
                    'source' => 1,
                    'name' => 1
                ]
            ]);
            
            $count = 0;
            $totalIndicators = 0;
            $eventList = []; // เก็บรายชื่อเพื่อใส่ใน summary log
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
                    'name' => $eventName,
                    'count' => $indicatorCount
                ];

                $this->line("   - [{$event['pulse_id']}] $eventName ($indicatorCount indicators)");
            }
            
            $this->info("✅ Sweep Completed!");
            $this->info("📊 Total Public Events: " . number_format($count));
            $this->info("💎 Total Indicators: " . number_format($totalIndicators));
            
            // บันทึก Summary ลง Log ของวันนี้ด้วย (ใช้ updateOne + upsert เพื่อไม่ให้เกิด record ซ้ำถ้าเรียกหลายครั้งในวันเดียวกัน)
            $todayRegex = '^' . date('Y-m-d');
            $colLogs->updateOne(
                ['type' => 'summary_report', 'action_time' => ['$regex' => $todayRegex]],
                ['$set' => [
                    'timestamp' => $timestamp,
                    'action_time' => $actionTime,
                    'type' => 'summary_report',
                    'total_public_events' => $count,
                    'total_indicators' => $totalIndicators,
                    'events' => $eventList,
                    'source' => 'audit_sweep_summary'
                ]],
                ['upsert' => true]
            );

        } catch (\Exception $e) {
            $this->error("❌ Error during sweep: " . $e->getMessage());
            Log::error("Audit Sweep Failed: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
