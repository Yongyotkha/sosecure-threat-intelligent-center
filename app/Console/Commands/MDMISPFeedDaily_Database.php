<?php

namespace App\Console\Commands;



use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;
use Artisan;

use Illuminate\Support\Facades\DB;
use App\Services\SSHTunnelService;
use Braintree\Result\Successful;
use DateTime;
use DateInterval;
use Sabberworm\CSS\Value\Value;
use Illuminate\Support\Facades\Log;


class MDMISPFeedDaily_Database extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:MDMISPFeedDaily_Database {--limit= : Limit the number of events to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attr Feed';

    protected $orgNames = [];
    protected $totalEventsFromMysql = 0;
    protected $totalEventsProcessed = 0;
    protected $totalIndicatorsFromMysql = 0;
    protected $totalIndicatorsProcessed = 0;
    protected $totalIndicatorsSkipped = 0;
    protected $failedEvents = [];

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
     * @return mixed===
     */
    public function handle()
    {
        Log::info("=== Job started at " . now() . " ===");
        ini_set('memory_limit', '-1');
        $this->info("=== Job started at " . now() . " ===");

        // Reset counters
        $this->totalEventsFromMysql = 0;
        $this->totalEventsProcessed = 0;
        $this->totalIndicatorsFromMysql = 0;
        $this->totalIndicatorsProcessed = 0;
        $this->totalIndicatorsSkipped = 0;
        $this->failedEvents = [];

        // ✅ ตรวจสอบ MySQL ถ้า DB ล่มจะ retry ภายใน
        if (!$this->checkDbConnection()) {
            $this->info("DB connection failed after retries, job stop.");
            return;
        }

        $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $db = $clientMD->sosecure_threatintelligent;
        $progressCol = $db->job_progress;

        $end = new \DateTime();

        // ✅ อ่าน progress ล่าสุด — เพื่อ resume ถ้า job ล่มไปกลางทาง
        $progress = $progressCol->findOne(['job' => 'daily_misp_feed']);

        if ($progress && isset($progress['last_ts'])) {
            // ✅ แปลง timestamp เป็นเวลาไทย “โดยไม่ให้บวกซ้ำ”
            $start = (new \DateTime('@' . $progress['last_ts']))   // ตีความ timestamp เป็น UTC ก่อน
                ->setTimezone(new \DateTimeZone('UTC'));   // ⚠️ ใช้ UTC เพราะตอนนี้มันถูกบวกเกินไปแล้ว

            $this->info("➡️ Resume from: " . $start->format('Y-m-d H:i:s'));
        } else {
            // 🟢 ถ้าไม่เคยมี progress ให้เริ่มย้อนหลัง 6 ชม.
            $start = clone $end;
            $start->sub(new \DateInterval('PT6H'));
            $this->info("➡️ Start new job from: " . $start->format('Y-m-d H:i:s'));
        }

        $date_now = new \MongoDB\BSON\UTCDateTime(strtotime(now()) * 1000);

        try {
            // ==== Insert Event Stamp ====
            $insEvent = $db->fx_transaction_otx_event_stamp->insertOne([
                'code' => generator_uuid(),
                'transaction_date' => date("Y-m-d"),
                'status' => 1,
                'created_at' => $date_now,
                'created_by' => "system",
                'updated_at' => $date_now,
                'updated_by' => "system",
                'deleted_at' => null,
                'source' => "misp",
                'start_date_job' => $start->format('Y-m-d H:i:s'),
                'end_date_job' => $end->format('Y-m-d H:i:s'),
            ]);
            $stamp_event_id = $insEvent->getInsertedId();
            $this->info("Inserted Event ID: " . $stamp_event_id);

            // ==== Insert Indicator Stamp ====
            $insIndicator = $db->fx_transaction_otx_indicator_stamp->insertOne([
                'code' => generator_uuid(),
                'transaction_date' => date("Y-m-d"),
                'status' => 1,
                'created_at' => $date_now,
                'created_by' => "system",
                'updated_at' => $date_now,
                'updated_by' => "system",
                'deleted_at' => null,
                'source' => "misp",
                'start_date_job' => $start->format('Y-m-d H:i:s'),
                'end_date_job' => $end->format('Y-m-d H:i:s'),
            ]);
            $stamp_indicator_id = $insIndicator->getInsertedId();
            $this->info("Inserted Indicator ID: " . $stamp_indicator_id);

            $limit = $this->option('limit');

            // ✅ saveJson จะต้องอัปเดต progress ภายในเองทุกครั้งที่ดึงเสร็จ
            $this->queryWithRetry(function () use ($stamp_event_id, $stamp_indicator_id, $start, $end, $progressCol, $limit, $db) {
                $this->saveJson($stamp_event_id, $stamp_indicator_id, $start, $end, $progressCol, $limit, $db);
            });

            // Final update for daily stats count
            $today = date("Y-m-d");
            $daily = $db->fx_events_daily_stats->findOne(['date' => $today], ['projection' => ['pulse_ids' => 1]]);
            if ($daily && isset($daily['pulse_ids'])) {
                $db->fx_events_daily_stats->updateOne(
                    ['date' => $today],
                    ['$set' => ['count' => count($daily['pulse_ids'])]]
                );
            }

            // Update stamps with counts
            $db->fx_transaction_otx_event_stamp->updateOne(
                ['_id' => $stamp_event_id],
                ['$set' => [
                    'status' => 2,
                    'api_total_pulses' => $this->totalEventsFromMysql,
                    'processed_pulses' => $this->totalEventsProcessed,
                    'processed_indicators' => $this->totalIndicatorsProcessed,
                    'skipped_indicators' => $this->totalIndicatorsSkipped,
                    'api_expected_indicators' => $this->totalIndicatorsFromMysql,
                    'updated_at' => new \MongoDB\BSON\UTCDateTime(strtotime(now()) * 1000)
                ]]
            );

            $db->fx_transaction_otx_indicator_stamp->updateOne(
                ['_id' => $stamp_indicator_id],
                ['$set' => [
                    'status' => 2,
                    'processed_indicators' => $this->totalIndicatorsProcessed,
                    'skipped_indicators' => $this->totalIndicatorsSkipped,
                    'api_expected_indicators' => $this->totalIndicatorsFromMysql,
                    'updated_at' => new \MongoDB\BSON\UTCDateTime(strtotime(now()) * 1000)
                ]]
            );

            // ✅ เรียก Artisan อื่นต่อได้ (เช่นนับจำนวน indicator)
            $this->queryWithRetry(function () {
                \Artisan::call('app:MDCountIndicator');
            });
        } catch (\Throwable $e) {
            \Log::error("Job failed: " . $e->getMessage());
            $this->error("Job failed: " . $e->getMessage());
        }

        // Summary Report
        $this->info("\n=========================================");
        $this->info("SUMMARY REPORT (MISP FEED DAILY)");
        $this->info("=========================================");
        $this->info("EVENTS STATUS:");
        $this->info("  - Total from MySQL   : " . number_format($this->totalEventsFromMysql));
        $this->info("  - Successfully Saved : " . number_format($this->totalEventsProcessed));
        $this->info("  - Total Verified     : " . number_format($this->totalEventsProcessed) . " / " . number_format($this->totalEventsFromMysql) . " (" . ($this->totalEventsFromMysql > 0 ? round(($this->totalEventsProcessed / $this->totalEventsFromMysql) * 100, 2) : 0) . "%)");
        
        $this->info("-----------------------------------------");
        $this->info("INDICATORS STATUS:");
        $this->info("  - Total from MySQL   : " . number_format($this->totalIndicatorsFromMysql));
        $this->info("  - Newly Saved (Today): " . number_format($this->totalIndicatorsProcessed));
        $this->info("  - Skipped (Old Data) : " . number_format($this->totalIndicatorsSkipped));
        $totalIndiVerified = $this->totalIndicatorsProcessed + $this->totalIndicatorsSkipped;
        $this->info("  - Total Verified     : " . number_format($totalIndiVerified) . " / " . number_format($this->totalIndicatorsFromMysql) . " (" . ($this->totalIndicatorsFromMysql > 0 ? round(($totalIndiVerified / $this->totalIndicatorsFromMysql) * 100, 2) : 0) . "%)");
        $this->info("=========================================");

        $this->info("=== Job finished at " . now() . " ===");
        Log::info("=== Job finished at " . now() . " ===");
    }



    // public function saveJson($stamp_event_id, $stamp_indicator_id, $json_o = null)
    // {

    //     // PREPARE DATA //
    //     $this->info('Prepaing DATA ...');


    //     $chk_tag = DB::connection('mysql_misp')->table('tags')
    //         ->select('id')
    //         ->whereRaw('LOWER(name) LIKE ?', ['%otx%'])
    //         ->pluck('id')
    //         ->toArray();

    //     // $chk_tag = [];
    //     // $chk_tag = DB::connection('mysql_misp')->table('tags')
    //     //     ->select('id')
    //     //     ->whereRaw('LOWER(name) LIKE ?', ['%otx%'])
    //     //     ->get();

    //     // $sixHoursAgo = Carbon::now()->subHours(6)->timestamp;
    //     // $excludedTagIds = [3293, 7162];
    //     // $mysqlEvents = DB::connection('mysql_misp')->table('events')
    //     //     ->select('id', 'info', 'date', 'published', 'publish_timestamp', 'timestamp', 'orgc_id', 'org_id')
    //     //     // ->where('id', '=',     646)
    //     //     ->where('timestamp', '>=', $sixHoursAgo)
    //     //     // ->limit(1)
    //     //     ->whereNotExists(function ($query) use ($excludedTagIds) {
    //     //         $query->select(DB::raw(1))
    //     //             ->from('event_tags')
    //     //             ->whereColumn('event_tags.event_id', 'events.id')
    //     //             ->whereIn('event_tags.tag_id', $excludedTagIds);
    //     //     })
    //     //     // ->limit(1)
    //     //     ->get();


    //     $chk_tag = DB::connection('mysql_misp')->table('tags')
    //         ->select('id')
    //         ->whereRaw('LOWER(name) LIKE ?', ['%otx%'])
    //         ->pluck('id')
    //         ->toArray();


    //     $sixHoursAgo = Carbon::now()->subHours(11)->timestamp;
    //     $mysqlEvents = DB::connection('mysql_misp')->table('events')
    //         ->select('id', 'info', 'date', 'published', 'publish_timestamp', 'timestamp', 'orgc_id', 'org_id')
    //         ->where('timestamp', '>=', $sixHoursAgo)
    //         // ->where('id', '=',     573)
    //         ->whereNotExists(function ($query) use ($chk_tag) {
    //             $query->select(DB::raw(1))
    //                 ->from('event_tags')
    //                 ->whereColumn('event_tags.event_id', 'events.id')
    //                 ->whereIn('event_tags.tag_id', $chk_tag);
    //         })
    //         ->get();

    //     // $startDate = Carbon::createFromFormat('d/m/Y', '23/09/2025')->startOfDay()->timestamp;
    //     // $endDate   = Carbon::createFromFormat('d/m/Y', '23/09/2025')->endOfDay()->timestamp;

    //     // $mysqlEvents = DB::connection('mysql_misp')->table('events')
    //     //     ->select('id', 'info', 'date', 'published', 'publish_timestamp', 'timestamp', 'orgc_id', 'org_id')
    //     //     ->whereBetween('timestamp', [$startDate, $endDate])
    //     //     ->where('id', '=', 	573)
    //     //     ->whereNotExists(function ($query) use ($chk_tag) {
    //     //         $query->select(DB::raw(1))
    //     //             ->from('event_tags')
    //     //             ->whereColumn('event_tags.event_id', 'events.id')
    //     //             ->whereIn('event_tags.tag_id', $chk_tag);
    //     //     })
    //     //     ->get();

    //     // \Log::info($mysqlEvents);
    //     // return;

    //     $insertedCount = 0;

    //     // ATTRIBUTE START //


    //     $attributes = [];

    //     foreach ($mysqlEvents as $event) {
    //         $sixHoursAgo = Carbon::now()->subHours(11)->timestamp;

    //         $startDate = Carbon::createFromFormat('d/m/Y', '23/09/2025')->startOfDay()->timestamp;
    //         $endDate   = Carbon::createFromFormat('d/m/Y', '23/09/2025')->endOfDay()->timestamp;

    //         $attributes = DB::connection('mysql_misp')->table('attributes')
    //             ->where('event_id', $event->id)
    //             // ->whereBetween('timestamp', [$startDate, $endDate])
    //             ->where('timestamp', '>=', $sixHoursAgo)
    //             ->limit(2)
    //             ->get()
    //             ->map(function ($attr) {
    //                 return [
    //                     'type' => $attr->type,
    //                     'event_id' => $attr->event_id,
    //                     'value' => $attr->value1,
    //                     'id' => $attr->id,
    //                     'category' => $attr->category,
    //                     'timestamp' => $attr->timestamp,
    //                 ];
    //             })->toArray();



    //         if (empty($attributes)) {
    //             continue;
    //         }


    //         // Attribute ที่ กรองไม่เอา ID OTX //

    //         // $excludedAttributePairs = DB::connection('mysql_misp')->table('attribute_tags')
    //         //     ->whereIn('tag_id', $excludedTagIds)
    //         //     ->select('event_id', 'attribute_id')
    //         //     ->get()
    //         //     ->map(function ($row) {
    //         //         return $row->event_id . ':' . $row->attribute_id;
    //         //     })
    //         //     ->toArray();

    //         // $attributes = [];

    //         // foreach ($mysqlEvents as $event) {
    //         //     $attrs = DB::connection('mysql_misp')->table('attributes')
    //         //         ->where('event_id', $event->id)
    //         //         ->where('timestamp', '>=', $sixHoursAgo)
    //         //         ->get()
    //         //         ->filter(function ($attr) use ($excludedAttributePairs) {
    //         //             // ประกอบ key เพื่อเช็คว่า attribute นี้อยู่ในรายการห้ามหรือไม่
    //         //             $key = $attr->event_id . ':' . $attr->id;
    //         //             return !in_array($key, $excludedAttributePairs);
    //         //         })
    //         //         ->map(function ($attr) {
    //         //             return [
    //         //                 'type' => $attr->type,
    //         //                 'event_id' => $attr->event_id,
    //         //                 'value' => $attr->value1,
    //         //                 'id' => $attr->id,
    //         //                 'category' => $attr->category,
    //         //                 'timestamp' => $attr->timestamp,
    //         //             ];
    //         //         })
    //         //         ->toArray();

    //         //     $attributes = array_merge($attributes, $attrs);

    //         // Attribute ที่ กรองไม่เอา ID OTX //

    //         // ATTRIBUTE END //

    //         // Event Tags START //

    //         $tags = [];
    //         $tags_list = [];
    //         if ($event) {
    //             $tag = DB::connection('mysql_misp')->table('event_tags')
    //                 ->where('event_id', '=', $event->id)
    //                 ->get();
    //         }

    //         if (count($tag)) {
    //             foreach ($tag as $tags) {
    //                 $tagData = DB::connection('mysql_misp')->table('tags')
    //                     ->select('name')
    //                     ->where('id', '=', $tags->tag_id)
    //                     ->first();

    //                 if ($tagData && !in_array($tagData->name, array_column($tags_list, 'name'))) {
    //                     $tags_list[] = $tagData;
    //                 }
    //             }
    //         }

    //         // Event Tags END //

    //         // Related Event START //

    //         // $relatedevent_ = [];
    //         // if ($event) {
    //         //     $correlations = DB::connection('mysql_misp')->table('default_correlations')
    //         //         ->where('event_id', '=', $event->id)
    //         //         ->get()
    //         //         ->unique('1_event_id')
    //         //         ->map(function ($item) {
    //         //             return [
    //         //                 'id' => $item->{'1_event_id'},
    //         //                 'attribute_id' => $item->{'1_attribute_id'},
    //         //             ];
    //         //         })
    //         //         ->toArray();

    //         //     $relatedevent_ = array_merge($relatedevent_, $correlations);
    //         // }

    //         // // $excludedTagIds = [3293, 7162];
    //         // $event_related = [];
    //         // if (count($relatedevent_)) {
    //         //     foreach ($relatedevent_ as $relatedevent) {
    //         //         $event_e = DB::connection('mysql_misp')->table('events')
    //         //             ->where('id', '=', $relatedevent['id'])
    //         //             ->first();

    //         //         if ($event_e) {
    //         //             $hasExcludedTags = DB::connection('mysql_misp')->table('event_tags')
    //         //                 ->where('event_id', $event_e->id)
    //         //                 ->whereIn('tag_id', $excludedTagIds)
    //         //                 ->exists();

    //         //             if ($hasExcludedTags) {
    //         //                 continue;
    //         //             }
    //         //             if ($event_e) {
    //         //                 $event_related[] = [
    //         //                     'id' => $event_e->id,
    //         //                     'info' => $event_e->info,
    //         //                     'org_id' => $this->findOrgName($event_e->org_id),
    //         //                     'orgc_id' => $this->findOrgName($event_e->orgc_id),
    //         //                     'date' => $event_e->date,
    //         //                     'timestamp' => $event_e->timestamp,
    //         //                     'publish_timestamp' => $event_e->publish_timestamp,
    //         //                     'published' => $event_e->published,
    //         //                     'attribute_id' => $relatedevent['attribute_id'],
    //         //                 ];
    //         //             }
    //         //         }
    //         //     }
    //         // }

    //         // Related Event END //

    //         // ITEM START //
    //         $item = [
    //             'id' => $event->id,
    //             'info' => $event->info,
    //             'date' => $event->date,
    //             'published' => $event->published,
    //             'publish_timestamp' => $event->publish_timestamp,
    //             'timestamp' => $event->timestamp,
    //             'orgc_id' => $this->findOrgName($event->orgc_id),
    //             'org_id' => $this->findOrgName($event->org_id),
    //             'created_at' => $event->date,
    //             'updated_at' => $event->timestamp,
    //             'attribute_count' => count($attributes),
    //             'Tag' => $tags_list,
    //             'Attribute' => $attributes,
    //             // 'RelatedEvent' => $event_related,
    //             // 'RelatedEventAttr' => $event_related_attr
    //         ];
    //         // ITEM END //

    //         $countEvent = 0;

    //         // INSERT TO MONGODB START //
    //         $this->info("\n" . "Saving data to Sosecure TreatIntelligent....");

    //         $countAttr = $this->saveRelatedIndicator($item, $stamp_event_id, $stamp_indicator_id);
    //         // $countEvent = $this->saveRelatedEvent($item, $stamp_event_id, $stamp_indicator_id);
    //         $this->saveEvent($item, $stamp_event_id, $stamp_indicator_id, $countAttr, 0);

    //         // INSERT TO MONGODB END //

    //         $insertedCount++;
    //         $this->info("Completed " . $insertedCount . " events.");
    //     }


    //     $this->info("Saved " . $insertedCount . " events to MongoDB.");
    // }


    public function saveJson($stamp_event_id, $stamp_indicator_id, $start, $end, $progressCol, $limit = null, $db = null)
    {
        $this->info('Preparing DATA ...');

        // ✅ ดึง tag ที่ต้องตัดออก
        $chk_tag = $this->queryWithRetry(function () {
            return DB::connection('mysql_misp')->table('tags')
                ->select('id')
                ->whereRaw('LOWER(name) LIKE ?', ['%otx%'])
                ->pluck('id')
                ->toArray();
        });

        $startTs = $start->getTimestamp();
        $endTs   = $end->getTimestamp();

        // ✅ ดึง Event ในช่วงเวลา start - end
        $mysqlEvents = $this->queryWithRetry(function () use ($chk_tag, $startTs, $endTs, $limit) {
            $query = DB::connection('mysql_misp')->table('events')
                ->select('id', 'info', 'date', 'published', 'publish_timestamp', 'timestamp', 'orgc_id', 'org_id')
                ->whereBetween('timestamp', [$startTs, $endTs])
                ->whereNotExists(function ($query) use ($chk_tag) {
                    $query->select(DB::raw(1))
                        ->from('event_tags')
                        ->whereColumn('event_tags.event_id', 'events.id')
                        ->whereIn('event_tags.tag_id', $chk_tag);
                });

            if ($limit) {
                $query->limit((int)$limit);
            }

            return $query->get();
        });

        $this->totalEventsFromMysql = $mysqlEvents->count();
        $this->info("Total events to process: " . $this->totalEventsFromMysql);

        foreach ($mysqlEvents as $event) {
            $startTime = microtime(true);

            // ✅ ดึง attributes ของ event
            $attributes = $this->queryWithRetry(function () use ($event, $startTs, $endTs) {
                return DB::connection('mysql_misp')->table('attributes')
                    ->where('event_id', $event->id)
                    ->whereBetween('timestamp', [$startTs, $endTs])
                    ->get()
                    ->map(function ($attr) {
                        return [
                            'type' => $attr->type,
                            'event_id' => $attr->event_id,
                            'value' => $attr->value1,
                            'id' => $attr->id,
                            'category' => $attr->category,
                            'timestamp' => $attr->timestamp,
                        ];
                    })->toArray();
            });

            $attrCount = count($attributes);
            $this->totalIndicatorsFromMysql += $attrCount;

            // ✅ ดึง tags ของ event
            $tag = $this->queryWithRetry(function () use ($event) {
                return DB::connection('mysql_misp')->table('event_tags')
                    ->where('event_id', '=', $event->id)
                    ->get();
            });

            $tags_list = [];
            if (count($tag)) {
                foreach ($tag as $tags) {
                    $tagData = $this->queryWithRetry(function () use ($tags) {
                        return DB::connection('mysql_misp')->table('tags')
                            ->select('name')
                            ->where('id', '=', $tags->tag_id)
                            ->first();
                    });
                    if ($tagData && !in_array($tagData->name, array_column($tags_list, 'name'))) {
                        $tags_list[] = $tagData;
                    }
                }
            }

            // ✅ เตรียมข้อมูล event
            $item = [
                'id' => $event->id,
                'info' => $event->info,
                'date' => $event->date,
                'published' => $event->published,
                'publish_timestamp' => $event->publish_timestamp,
                'timestamp' => $event->timestamp,
                'orgc_id' => $this->findOrgName($event->orgc_id),
                'org_id' => $this->findOrgName($event->org_id),
                'created_at' => $event->date,
                'updated_at' => $event->timestamp,
                'attribute_count' => $attrCount,
                'Tag' => $tags_list,
                'Attribute' => $attributes,
            ];

            // ✅ บันทึก Indicator & Event
            $countAttr = $this->saveRelatedIndicator($item, $stamp_event_id, $stamp_indicator_id, $db);
            $this->saveEvent($item, $stamp_event_id, $stamp_indicator_id, $countAttr, 0, $db);

            $this->totalEventsProcessed++;

            // ✅ อัปเดต progress ระหว่างทาง
            $progressCol->updateOne(
                ['job' => 'daily_misp_feed'],
                [
                    '$set' => [
                        'last_ts' => $event->timestamp, 
                        'updated_at' => new \MongoDB\BSON\UTCDateTime(strtotime(now()) * 1000)
                    ]
                ],
                ['upsert' => true]
            );

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            $this->info("Completed {$this->totalEventsProcessed} / {$this->totalEventsFromMysql} events (ID: {$event->id}, Indicators: {$attrCount}, Time: {$duration}s)");
        }

        $this->info("Saved {$this->totalEventsProcessed} events to MongoDB.");
    }


    public function updateEventYes($id)
    {
        $results = DB::connection('mysql_misp')->table('events')
            ->where('id', '=', $id)
            ->update([
                'published' => 1
            ]);
    }

    public function updateEventNo($id)
    {
        $results = DB::connection('mysql_misp')->table('events')
            ->where('id', '=', $id)
            ->update([
                'published' => 0
            ]);
    }

    public function findTags($item)
    {
        $data = [];
        if ($item) {
            foreach ($item as $value) {
                $results = DB::connection('mysql_misp')->table('event_tags')
                    ->where('id', '=', $id)
                    ->update([
                        'published' => 0
                    ]);
            }
        }
    }


    public function findOrgName($org_id)
    {
        if ($org_id == null) return '';
        
        if (isset($this->orgNames[$org_id])) {
            return $this->orgNames[$org_id];
        }

        $org = DB::connection('mysql_misp')->table('organisations')
            ->select('name')
            ->where('id', '=', $org_id)
            ->first();
            
        $name = $org ? $org->name : 'Unknown';
        $this->orgNames[$org_id] = $name;
        
        return $name;
    }

    // public function saveEvent($valueEvent, $stamp_event_id, $stamp_indicator_id)


    public function saveEvent($valueEvent, $stamp_event_id, $stamp_indicator_id, $countAttr, $countEvent, $db = null)
    {
        try {
            if (!$db) {
                $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
                $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
                $db = $clientMD->sosecure_threatintelligent;
            }
            
            $col_fx_otx_events = $db->fx_otx_events;
            $no_Indicator = $db->fx_otx_events_indicator_ref;
            
            $pulse_id = 'misp.' . $valueEvent["id"];
            
            $countPulse = $no_Indicator->countDocuments([
                'pulse_id' => $pulse_id
            ]);
            
            $tlpcolor = null;
            $tags = null;
            $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);

            $incData = [];
            if (!empty($countAttr['byType'])) {
                foreach ($countAttr['byType'] as $type => $count) {
                    $incData["indicator_type_counts.$type"] = $count;
                }
            }

            if (!empty($valueEvent["Tag"])) {
                foreach ($valueEvent["Tag"] as $key => $value) {
                    if (strpos($value->name, "tlp:") === false) {
                        $tags .= $value->name . ", ";
                    } else {
                        $tlpcolor = explode(":", $value->name)[1];
                    }
                }
                $tags = rtrim($tags, ", ");
            }

            $updateData = [
                '$set' => [
                    'name' => @$valueEvent["info"],
                    'description' => @$valueEvent["info"],
                    'modified' => isset($valueEvent["publish_timestamp"]) ? new UTCDateTime($valueEvent["publish_timestamp"] * 1000) : null,
                    'created' => isset($valueEvent["date"]) ? new UTCDateTime(strtotime($valueEvent["date"]) * 1000) : null,
                    'public' => ($valueEvent["published"] == true) ? 1 : 0,
                    'TLP' => @$tlpcolor,
                    'indicator_count' => $countPulse,
                    'count_related_pulse' => @$countEvent["all"],
                    'is_modified' => ($valueEvent["timestamp"] == $valueEvent["publish_timestamp"]) ? false : true,
                    'tags' => @$tags,
                    'updated_at' => $date_now,
                    'updated_by' => "system",
                ],
                '$setOnInsert' => [
                    'author_username' => null,
                    'groups' => null,
                    'malware_families' => null,
                    'industries' => null,
                    'references' => null,
                    'transcation_id' => $stamp_event_id,
                    'status' => 1,
                    'created_at' => $date_now,
                    'created_by' => "system",
                    'deleted_at' => null,
                    'transaction_date' => date("Y-m-d"),
                    'count_view' => 0,
                    'source' => "misp",
                    'creator_org' => @$valueEvent["orgc_id"],
                ],
            ];

            if (!empty($incData)) {
                $updateData['$inc'] = $incData;
            }

            $col_fx_otx_events->updateOne(['pulse_id' => $pulse_id], $updateData, ['upsert' => true]);

            // ✅ นับ Event ต่อวัน (แบบสะสม pulse_ids)
            $today = date("Y-m-d");
            $col_daily_stats = $db->fx_events_daily_stats;

            $col_daily_stats->updateOne(
                ['date' => $today],
                [
                    '$addToSet' => ['pulse_ids' => $pulse_id],
                    '$setOnInsert' => [
                        'date' => $today,
                        'created_at' => $date_now,
                        'count' => 0
                    ]
                ],
                ['upsert' => true]
            );

        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
            \Log::error("saveEvent error: " . $e->getMessage());
        }
        return 0;
    }

    public function saveRelatedEvent($valueEvent, $stamp_event_id, $stamp_indicator_id)
    {
        try {

            $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
            $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
            $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;
            $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;
            $col_fx_transaction_otx_indicators_data = $clientMD->sosecure_threatintelligent->fx_transaction_otx_indicators_data;
            $col_fx_otx_type = $clientMD->sosecure_threatintelligent->fx_otx_type;
            $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);
            $data["all"] = 0;
            $data["byType"] = array();


            if (!empty($valueEvent["Attribute"])) {
                foreach ($valueEvent["Attribute"] as $key => $value) {

                    $pulse_id = "misp." . $valueEvent["id"];
                    $indicator_id = "misp." . $value["id"];

                    $isAlreadyCounted = $col_fx_otx_events_indicator_ref->findOne([
                        'indicator_id' => $indicator_id,
                        'pulse_id' => $pulse_id,
                        'is_count_attr' => ['$exists' => true]
                    ]);
                    $allRow = [];
                    if (!$isAlreadyCounted) {
                        $data["all"]++;
                        if (isset($data["byType"][$value["type"]])) {
                            $data["byType"][$value["type"]] = $data["byType"][$value["type"]] + 1;
                        } else {
                            $data["byType"][$value["type"]] = 1;
                        }
                        $allRow = array();
                        if (isset($value["value"])) {
                            $allRow["detail"] = @$value["value"];
                        }
                    }

                    // $document = $col_fx_otx_indicator_detail->findOne(['indicator_id' => "misp_".@$value["id"]],
                    //     [
                    //         'projection' => [
                    //             "_id" => 1,
                    //         ]
                    //     ]
                    // );



                    if (true) {
                        $update_fx_otx_indicator_detail = $col_fx_otx_indicator_detail->updateOne(
                            ['indicator_id' => "misp." . @$value["id"]],
                            [
                                '$set' => [
                                    'indicator_name' => @$value["value"],
                                    'type' => @$value["type"],
                                    'updated_by' => "system",
                                    'updated_at' => isset($value["timestamp"]) ? new UTCDateTime($value["timestamp"] * 1000) : $date_now,
                                    'created_at' => isset($value["timestamp"]) ? new UTCDateTime($value["timestamp"] * 1000) : $date_now, //
                                ],
                                '$setOnInsert' => [
                                    'transcation_id' => $stamp_indicator_id,
                                    'allrow' => $allRow,
                                    'status' => 1,
                                    'created_by' => "system",
                                    'deleted_at' => null,
                                    'transaction_date' => date("Y-m-d"),
                                    'source' => "misp",
                                    'creator_org' => $valueEvent["orgc_id"]
                                ],
                            ],
                            ['upsert' => true]
                        );

                        $update_fx_transaction_otx_indicators_data = $col_fx_transaction_otx_indicators_data->updateOne(
                            ['indicator_id' => "misp." . @$value["id"]],
                            [
                                '$set' => [
                                    'indicator' => @$value["value"],
                                    'type' => @$value["type"],
                                    'tile' => null,
                                    'desciption' => null,
                                    'slug' => null,
                                    'name' => null,
                                    'updated_at' => isset($value["timestamp"]) ? new UTCDateTime($value["timestamp"] * 1000) : $date_now,
                                    'updated_by' => "system",
                                    'transcation_id' => $stamp_indicator_id,
                                    'created_at' => isset($value["timestamp"]) ? new UTCDateTime($value["timestamp"] * 1000) : $date_now, //
                                ],
                                '$setOnInsert' => [
                                    'status' => 1,

                                    'created_by' => "system",
                                    'deleted_at' => null,
                                    'transaction_date' => date("Y-m-d"),
                                    'source' => "misp",
                                ],
                            ],
                            ['upsert' => true]
                        );

                        $update_fx_otx_type = $col_fx_otx_type->updateOne(
                            [
                                'name' => @$value["type"],
                            ],
                            [
                                '$setOnInsert' => [
                                    'transcation_id' => null,
                                    'updated_at' => $date_now,
                                    'updated_by' => "system",
                                    'slug' => null,
                                    'description' => null,
                                    'code' => generator_uuid(),
                                    'remark' => "system",
                                    'element_count' => 0,
                                    'status' => 1,
                                    'created_at' => $date_now,
                                    'created_by' => "system",
                                    'deleted_at' => null,
                                    'source' => 'misp',
                                ],
                            ],
                            ['upsert' => true]
                        );
                    }
                    //   print_r($value);
                    echo  'indicator_id:' . "misp." . @$value["id"] . '|' . "misp." . @$valueEvent["id"] . '|indicator:' . @$value["value"];
                    //   break;
                    $pulse_id  = "misp." . $valueEvent["id"];
                    $indicator_id  =  "misp." . $value["id"];
                    $update_fx_otx_events_indicator_ref = $col_fx_otx_events_indicator_ref->updateOne(
                        [
                            'indicator_id' => $indicator_id,
                            'pulse_id' =>  $pulse_id,

                        ],
                        [
                            '$set' => [
                                'pulse_modified' => isset($valueEvent["date"]) ? new UTCDateTime($valueEvent["publish_timestamp"] * 1000) : null,
                                'role' => @$value["category"],
                                'created' => isset($value["timestamp"]) ? new UTCDateTime($value["timestamp"] * 1000) : null,
                                'expiration' => null,
                                'is_active' => 1,
                                // 'indicator' => $value["value"],
                                //'type' => $value["type"],
                            ],
                            '$setOnInsert' => [
                                'status' => 1,
                                'created_at' => $date_now,
                                'created_by' => "system",
                                'deleted_at' => null,
                                'transaction_date' => date("Y-m-d"),
                                'updated_at' => $date_now,
                                'updated_by' => "system",
                                'source' => "misp",
                                'indicator' => $value["value"],
                                'type' => $value["type"],
                                'is_count_attr' => 1,
                                'creator_org' => $valueEvent["orgc_id"],
                            ],
                        ],
                        ['upsert' => true]
                    );
                    //  print_r($value);
                    //  echo  'indicator_id:'.@$value["id"].'|'. "misp." . @$valueEvent["id"].'|indicator:'.@$value["value"];
                    //   break;

                    $this->info("Saving.....");
                }
            }
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

        return $data;
    }

    public function saveRelatedIndicator($valueEvent, $stamp_event_id, $stamp_indicator_id, $db = null)
    {
        try {
            if (!$db) {
                $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
                $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
                $db = $clientMD->sosecure_threatintelligent;
            }
            
            $col_fx_otx_indicator_detail = $db->fx_otx_indicator_detail;
            $col_fx_otx_events_indicator_ref = $db->fx_otx_events_indicator_ref;
            $col_fx_transaction_otx_indicators_data = $db->fx_transaction_otx_indicators_data;
            $col_fx_otx_type = $db->fx_otx_type;
            
            $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);
            $data["all"] = 0;
            $data["byType"] = array();

            if (empty($valueEvent["Attribute"])) {
                return $data;
            }

            $pulse_id = "misp." . $valueEvent["id"];
            
            // Batch fetch existing indicators for this pulse to avoid findOne inside loop
            $existingRefs = $col_fx_otx_events_indicator_ref->find([
                'pulse_id' => $pulse_id,
                'is_count_attr' => ['$exists' => true]
            ])->toArray();
            $existingIndicatorIds = array_column($existingRefs, 'indicator_id');

            $opsDetail = [];
            $opsData = [];
            $opsType = [];
            $opsRef = [];

            $batchSize = 1000;
            $count = 0;

            foreach ($valueEvent["Attribute"] as $key => $value) {
                $indicator_id = "misp." . $value["id"];
                $isAlreadyCounted = in_array($indicator_id, $existingIndicatorIds);

                $allRow = [];
                if (!$isAlreadyCounted) {
                    $data["all"]++;
                    $this->totalIndicatorsProcessed++;
                    if (isset($data["byType"][$value["type"]])) {
                        $data["byType"][$value["type"]] = $data["byType"][$value["type"]] + 1;
                    } else {
                        $data["byType"][$value["type"]] = 1;
                    }
                    if (isset($value["value"])) {
                        $allRow["detail"] = @$value["value"];
                    }
                } else {
                    $this->totalIndicatorsSkipped++;
                }

                $attrTimestamp = isset($value["timestamp"]) ? new UTCDateTime($value["timestamp"] * 1000) : $date_now;

                // Operations for fx_otx_indicator_detail
                $opsDetail[] = [
                    'updateOne' => [
                        ['indicator_id' => $indicator_id],
                        [
                            '$set' => [
                                'indicator_name' => @$value["value"],
                                'type' => @$value["type"],
                                'updated_by' => "system",
                                'updated_at' => $attrTimestamp,
                                'created_at' => $attrTimestamp,
                            ],
                            '$setOnInsert' => [
                                'transcation_id' => $stamp_indicator_id,
                                'allrow' => $allRow,
                                'status' => 1,
                                'created_by' => "system",
                                'deleted_at' => null,
                                'transaction_date' => date("Y-m-d"),
                                'source' => "misp",
                                'creator_org' => $valueEvent["orgc_id"]
                            ],
                        ],
                        ['upsert' => true]
                    ]
                ];

                // Operations for fx_transaction_otx_indicators_data
                $opsData[] = [
                    'updateOne' => [
                        ['indicator_id' => $indicator_id],
                        [
                            '$set' => [
                                'indicator' => @$value["value"],
                                'type' => @$value["type"],
                                'tile' => null,
                                'desciption' => null,
                                'slug' => null,
                                'name' => null,
                                'updated_at' => $attrTimestamp,
                                'updated_by' => "system",
                                'transcation_id' => $stamp_indicator_id,
                                'created_at' => $attrTimestamp,
                            ],
                            '$setOnInsert' => [
                                'status' => 1,
                                'created_by' => "system",
                                'deleted_at' => null,
                                'transaction_date' => date("Y-m-d"),
                                'source' => "misp",
                            ],
                        ],
                        ['upsert' => true]
                    ]
                ];

                // Operations for fx_otx_type
                $opsType[] = [
                    'updateOne' => [
                        ['name' => @$value["type"]],
                        [
                            '$setOnInsert' => [
                                'transcation_id' => null,
                                'updated_at' => $date_now,
                                'updated_by' => "system",
                                'slug' => null,
                                'description' => null,
                                'code' => generator_uuid(),
                                'remark' => "system",
                                'element_count' => 0,
                                'status' => 1,
                                'created_at' => $date_now,
                                'created_by' => "system",
                                'deleted_at' => null,
                                'source' => 'misp',
                            ],
                        ],
                        ['upsert' => true]
                    ]
                ];

                // Operations for fx_otx_events_indicator_ref
                $opsRef[] = [
                    'updateOne' => [
                        ['indicator_id' => $indicator_id, 'pulse_id' => $pulse_id],
                        [
                            '$set' => [
                                'pulse_modified' => isset($valueEvent["date"]) ? new UTCDateTime($valueEvent["publish_timestamp"] * 1000) : null,
                                'role' => @$value["category"],
                                'created' => isset($value["timestamp"]) ? new UTCDateTime($value["timestamp"] * 1000) : null,
                                'expiration' => null,
                                'is_active' => 1,
                            ],
                            '$setOnInsert' => [
                                'status' => 1,
                                'created_at' => $date_now,
                                'created_by' => "system",
                                'deleted_at' => null,
                                'transaction_date' => date("Y-m-d"),
                                'updated_at' => $date_now,
                                'updated_by' => "system",
                                'source' => "misp",
                                'indicator' => $value["value"],
                                'type' => $value["type"],
                                'is_count_attr' => 1,
                                'creator_org' => $valueEvent["orgc_id"],
                            ],
                        ],
                        ['upsert' => true]
                    ]
                ];

                $count++;
                if ($count % $batchSize == 0) {
                    $col_fx_otx_indicator_detail->bulkWrite($opsDetail);
                    $col_fx_transaction_otx_indicators_data->bulkWrite($opsData);
                    $col_fx_otx_type->bulkWrite($opsType);
                    $col_fx_otx_events_indicator_ref->bulkWrite($opsRef);
                    $opsDetail = []; $opsData = []; $opsType = []; $opsRef = [];
                }
            }

            // Execute remaining Bulk Writes
            if (!empty($opsDetail)) $col_fx_otx_indicator_detail->bulkWrite($opsDetail);
            if (!empty($opsData)) $col_fx_transaction_otx_indicators_data->bulkWrite($opsData);
            if (!empty($opsType)) $col_fx_otx_type->bulkWrite($opsType);
            if (!empty($opsRef)) $col_fx_otx_events_indicator_ref->bulkWrite($opsRef);

        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
            \Log::error("saveRelatedIndicator error: " . $e->getMessage());
        }

        return $data;
    }


    //     public function saveRelatedIndicatorOfRelatedEvent($valueEvent, $re_value, $stamp_event_id, $stamp_indicator_id)
    //     {
    //         try {

    //             $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
    //             $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
    //             $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;
    //             $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;
    //             $col_fx_transaction_otx_indicators_data = $clientMD->sosecure_threatintelligent->fx_transaction_otx_indicators_data;
    //             $col_fx_otx_type = $clientMD->sosecure_threatintelligent->fx_otx_type;
    //             $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);
    //             $data["all"] = 0;
    //             $data["byType"] = array();


    //             if (!empty($valueEvent["Attribute"])) {
    //                 foreach ($valueEvent["Attribute"] as $key => $value) {

    //                     $data["all"]++;
    //                     if (isset($data["byType"][$value["type"]])) {
    //                         $data["byType"][$value["type"]] = $data["byType"][$value["type"]] + 1;
    //                     } else {
    //                         $data["byType"][$value["type"]] = 1;
    //                     }
    //                     $allRow = array();
    //                     if (isset($value["value"])) {
    //                         $allRow["detail"] = @$value["value"];
    //                     }

    //                     // $document = $col_fx_otx_indicator_detail->findOne(['indicator_id' => "misp_".@$value["id"]],
    //                     //     [
    //                     //         'projection' => [
    //                     //             "_id" => 1,
    //                     //         ]
    //                     //     ]
    //                     // );



    //                     if (true) {
    //                         //  print_r('You are Here 1');
    //                         // return;
    //                         $update_fx_otx_indicator_detail = $col_fx_otx_indicator_detail->updateOne(
    //                             ['indicator_id' => "misp." . @$value["id"]],
    //                             [
    //                                 '$set' => [
    //                                     'indicator_name' => @$value["value"],
    //                                     'type' => @$value["type"],
    //                                     'updated_by' => "system",
    //                                     'updated_at' => isset($value["timestamp"]) ? new UTCDateTime($value["timestamp"] * 1000) : $date_now,
    //                                     'created_at' => isset($value["timestamp"]) ? new UTCDateTime($value["timestamp"] * 1000) : $date_now, //
    //                                 ],
    //                                 '$setOnInsert' => [
    //                                     'transcation_id' => $stamp_indicator_id,
    //                                     'allrow' => $allRow,
    //                                     'status' => 1,

    //                                     'created_by' => "system",
    //                                     'deleted_at' => null,
    //                                     'transaction_date' => date("Y-m-d"),
    //                                     'source' => "misp",
    //                                     'creator_org' => $valueEvent["orgc_id"]
    //                                 ],
    //                             ],
    //                             ['upsert' => true]
    //                         );

    //                         $update_fx_transaction_otx_indicators_data = $col_fx_transaction_otx_indicators_data->updateOne(
    //                             ['indicator_id' => "misp." . @$value["id"]],
    //                             [
    //                                 '$set' => [
    //                                     'indicator' => @$value["value"],
    //                                     'type' => @$value["type"],
    //                                     'tile' => null,
    //                                     'desciption' => null,
    //                                     'slug' => null,
    //                                     'name' => null,
    //                                     'updated_at' => isset($value["timestamp"]) ? new UTCDateTime($value["timestamp"] * 1000) : $date_now,
    //                                     'updated_by' => "system",
    //                                     'transcation_id' => $stamp_indicator_id,
    //                                     'created_at' => isset($value["timestamp"]) ? new UTCDateTime($value["timestamp"] * 1000) : $date_now, //
    //                                 ],
    //                                 '$setOnInsert' => [
    //                                     'status' => 1,

    //                                     'created_by' => "system",
    //                                     'deleted_at' => null,
    //                                     'transaction_date' => date("Y-m-d"),
    //                                     'source' => "misp",
    //                                 ],
    //                             ],
    //                             ['upsert' => true]
    //                         );

    //                         $update_fx_otx_type = $col_fx_otx_type->updateOne(
    //                             [
    //                                 'name' => @$value["type"],
    //                             ],
    //                             [
    //                                 '$setOnInsert' => [
    //                                     'transcation_id' => null,
    //                                     'updated_at' => $date_now,
    //                                     'updated_by' => "system",
    //                                     'slug' => null,
    //                                     'description' => null,
    //                                     'code' => generator_uuid(),
    //                                     'remark' => "system",
    //                                     'element_count' => 0,
    //                                     'status' => 1,
    //                                     'created_at' => $date_now,
    //                                     'created_by' => "system",
    //                                     'deleted_at' => null,
    //                                     'source' => 'misp',
    //                                 ],
    //                             ],
    //                             ['upsert' => true]
    //                         );
    //                     }
    //                     //   print_r($value);
    //                     echo  'indicator_id:' . "misp." . @$value["id"] . '|' . "misp." . @$valueEvent["id"] . '|indicator:' . @$value["value"];
    //                     //   break;
    //                     $pulse_id  = "misp." . $valueEvent["id"];
    //                     $indicator_id  =  "misp." . $value["id"];
    //                     $update_fx_otx_events_indicator_ref = $col_fx_otx_events_indicator_ref->updateOne(
    //                         [
    //                             'indicator_id' => $indicator_id,
    //                             'pulse_id' =>  $pulse_id,

    //                         ],
    //                         [
    //                             '$set' => [
    //                                 'pulse_modified' => isset($valueEvent["date"]) ? new UTCDateTime($valueEvent["publish_timestamp"] * 1000) : null,
    //                                 'role' => @$value["category"],
    //                                 'created' => isset($value["timestamp"]) ? new UTCDateTime($value["timestamp"] * 1000) : null,
    //                                 'expiration' => null,
    //                                 'is_active' => 1,
    //                                 // 'indicator' => $value["value"],
    //                                 //'type' => $value["type"],
    //                             ],
    //                             '$setOnInsert' => [
    //                                 'status' => 1,
    //                                 'created_at' => $date_now,
    //                                 'created_by' => "system",
    //                                 'deleted_at' => null,
    //                                 'transaction_date' => date("Y-m-d"),
    //                                 'updated_at' => $date_now,
    //                                 'updated_by' => "system",
    //                                 'source' => "misp",
    //                                 'indicator' => $value["value"],
    //                                 'type' => $value["type"],
    //                                 'is_count_attr' => 1,
    //                                 'creator_org' => $valueEvent["orgc_id"],
    //                             ],
    //                         ],
    //                         ['upsert' => true]
    //                     );
    //                     //  print_r($value);
    //                     //  echo  'indicator_id:'.@$value["id"].'|'. "misp." . @$valueEvent["id"].'|indicator:'.@$value["value"];
    //                     //   break;

    //                     $this->info("Saving.....");
    //                 }
    //             }
    //         } catch (Exception $e) {
    //             echo 'Caught exception: ',  $e->getMessage(), "\n";
    //         }

    //         return $data;
    //     }

    protected function checkDbConnection($maxTry = 5, $delay = 30)
    {
        for ($i = 1; $i <= $maxTry; $i++) {
            try {
                DB::connection()->getPdo();
                return true;
            } catch (\Throwable $e) {
                \Log::warning("DB connection attempt {$i}/{$maxTry} failed: " . $e->getMessage());
                if ($i < $maxTry) {
                    sleep($delay);
                    DB::reconnect();
                }
            }
        }
        return false;
    }

    /**
     * ครอบ query ให้ retry หลายรอบถ้าเจอ connection error
     */
    protected function queryWithRetry(callable $callback, $maxTry = 5, $delay = 30)
    {
        $sshService = new SSHTunnelService();

        for ($i = 1; $i <= $maxTry; $i++) {
            try {
                DB::purge('mysql_misp');
                DB::reconnect('mysql_misp');
                return $callback();
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = $e->getMessage();
                // \Log::info($msg);

                if (
                    str_contains($msg, 'server has gone away') ||
                    str_contains($msg, 'Connection refused') ||
                    str_contains($msg, 'Lost connection') ||
                    str_contains($msg, 'actively refused') ||
                    str_contains($msg, 'No connection could be made')
                ) {
                    \Log::warning("mysql_misp lost, retry {$i}/{$maxTry}...");

                    // 🔥 ถ้าเจอ connection refused ให้ลอง restart SSH tunnel ใหม่
                    if (str_contains($msg, 'No connection could be made')) {
                        \Log::warning("🔄 SSH tunnel lost — restarting...");
                        $sshService->createTunnel();
                        sleep(10); // รอให้ tunnel เปิดก่อน
                    }

                    if ($i < $maxTry) {
                        sleep($delay);
                        continue;
                    }
                }

                throw $e; // ถ้าครบ maxTry แล้วยัง error ก็โยนออกไป
            }
        }
    }
}
