<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Support\Facades\Log;
use App\Services\PublishedFeedsService;

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
            $db = PublishedFeedsService::database($client);
            $colEvents = $db->fx_otx_events;
            $colLogs = $db->fx_feed_published_logs;
            
            $sourceFilter = $this->option('source');
            
            $window = PublishedFeedsService::rollingWindow();
            $start = $window['start'];
            $end = $window['end'];
            $query = PublishedFeedsService::buildQuery($start, $end, $sourceFilter);
            
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
                
                $eventEntry = PublishedFeedsService::eventFromDocument((array) $event);
                $eventList[] = $eventEntry;

                $this->line("   - [{$eventEntry['pulse_id']}] {$eventEntry['name']} ($indicatorCount indicators)");
            }
            
            $this->info("✅ Sweep Completed!");
            
            // บันทึก Summary แยกตาม Mode (นับ events ที่ "พร้อมส่ง" ไม่ใช่ events ที่ MISP save จริง)
            $logType = ($mode === 'manifest') ? 'manifest_report' : 'summary_report';
            $merged = PublishedFeedsService::persistLog(
                $colLogs,
                $logType,
                $eventList,
                'audit_sweep_' . $mode,
                ['mode' => $mode]
            );

            $this->info("📊 Events in this run: " . number_format($count) . " | Total today: " . number_format($merged['total_public_events']));
            $this->info("💎 Indicators in this run: " . number_format($totalIndicators) . " | Total today: " . number_format($merged['total_indicators']));

        } catch (\Exception $e) {
            $this->error("❌ Error during sweep: " . $e->getMessage());
            Log::error("Audit Sweep Failed [$mode]: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
