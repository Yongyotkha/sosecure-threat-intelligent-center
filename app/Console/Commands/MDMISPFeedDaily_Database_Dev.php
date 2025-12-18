<?php

namespace App\Console\Commands;

// use App\Log;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client as MongoClient;
use Illuminate\Support\Facades\DB;
use App\Services\SSHTunnelService;
use Illuminate\Support\Facades\Log;

class MDMISPFeedDaily_Database_Dev extends Command
{
    protected $signature = 'app:MDMISPFeedDaily_Database_Dev';
    protected $description = 'Pull MISP data 3 months back in 6h chunks with resume';

    public function handle()
    {
        ini_set('memory_limit', '-1');
        $this->info("=== Start pulling ALL MISP data (with resume support) ===");
        Log::info("=== Start Pulling ALL MISP data  at " . now() . " ===");

        $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
        $clientMD = new MongoClient($DB_MONGO_KEY);
        $progressCol = $clientMD->sosecure_threatintelligent->job_progress;

        // 🧠 1️⃣ เช็ค progress ล่าสุดใน Mongo
        $progress = $progressCol->findOne(['job' => 'pull_misp_data']);
        if ($progress && isset($progress['last_ts'])) {
            $startTs = $progress['last_ts'];
            $this->info("Resuming from last timestamp: " . Carbon::createFromTimestamp($startTs));
        } else {
            // 🧠 2️⃣ ถ้าไม่เคยรันมาก่อน → เริ่มจาก event ที่เก่าสุด
            $startTs = $this->queryWithRetry(function () {
                return DB::connection('mysql_misp')->table('events')->min('timestamp');
            });
            $this->warn("Running FULL mode from oldest timestamp: " . Carbon::createFromTimestamp($startTs));
        }

        if (!$startTs) {
            $this->error("❌ ไม่พบข้อมูล timestamp ในตาราง events");
            return;
        }

        $endTs = Carbon::now()->timestamp;
        $cursor = $startTs;

        while ($cursor < $endTs) {
            $chunkStart = $cursor;
            $chunkEnd = min($cursor + 6 * 3600, $endTs);

            $this->info(
                "Processing chunk "
                    . Carbon::createFromTimestamp($chunkStart)
                    . " - "
                    . Carbon::createFromTimestamp($chunkEnd)
            );

            try {
                $this->queryWithRetry(function () use ($chunkStart, $chunkEnd) {
                    $this->saveJson($chunkStart, $chunkEnd);
                });

                // ✅ บันทึก progress หลังจบแต่ละ chunk
                $progressCol->updateOne(
                    ['job' => 'pull_misp_data'],
                    [
                        '$set' => [
                            'last_ts' => $chunkEnd,
                            'updated_at' => new UTCDateTime(strtotime(now()) * 1000),
                        ]
                    ],
                    ['upsert' => true]
                );
            } catch (Exception $e) {
                \Log::error("Chunk $chunkStart - $chunkEnd failed: " . $e->getMessage());
                $this->warn("Chunk failed, stopping to avoid skipping data");
                return;
            }

            $cursor = $chunkEnd;
        }

        $this->info("=== Finished pulling ALL MISP data ===");
    }


    public function saveJson($startTs, $endTs)
    {
        $this->info("Preparing data between "
            . Carbon::createFromTimestamp($startTs)
            . " - "
            . Carbon::createFromTimestamp($endTs));

        // 🔹 step 1: ดึง tag otx
        $chk_tag = $this->queryWithRetry(function () {
            return DB::connection('mysql_misp')->table('tags')
                ->select('id')
                ->whereRaw('LOWER(name) LIKE ?', ['%otx%'])
                ->pluck('id')
                ->toArray();
        });

        // 🔹 step 2: ดึง event ในช่วงเวลา
        $mysqlEvents = $this->queryWithRetry(function () use ($chk_tag, $startTs, $endTs) {
            return DB::connection('mysql_misp')->table('events')
                ->select('id', 'info', 'date', 'published', 'publish_timestamp', 'timestamp', 'orgc_id', 'org_id')
                ->whereBetween('timestamp', [$startTs, $endTs])
                ->whereNotExists(function ($query) use ($chk_tag) {
                    $query->select(DB::raw(1))
                        ->from('event_tags')
                        ->whereColumn('event_tags.event_id', 'events.id')
                        ->whereIn('event_tags.tag_id', $chk_tag);
                })
                ->orderBy('id')
                ->get();
        });

        // 🔹 step 3: เตรียมเชื่อม MongoDB
        $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
        $clientMD = new MongoClient($DB_MONGO_KEY);
        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
        $progressCol = $clientMD->sosecure_threatintelligent->job_progress;

        // 🔹 step 4: โหลด progress ล่าสุด (resume point)
        $resume = $progressCol->findOne(['job' => 'pull_misp_data']) ?? [];
        $lastEventId = $resume['last_event_id'] ?? 0;
        $lastAttrId = $resume['last_attr_id'] ?? 0;

        $insertedCount = 0;

        // 🔹 step 5: loop events ในช่วงเวลา
        foreach ($mysqlEvents as $event) {

            // ✅ เช็กใน Mongo ก่อน
            $exists = $col_fx_otx_events->findOne(
                ['pulse_id' => 'misp.' . $event->id],
                ['projection' => ['_id' => 1]]
            );

            if ($exists) {
                $this->info("⚡ Skip event {$event->id} (already in MongoDB)");
                continue;
            }

            $this->info("🚀 Processing event {$event->id} (resume attr_id > {$lastAttrId})");

            try {
                // ✅ ดึง attributes ทั้งหมดแบบ chunk
                DB::connection('mysql_misp')->table('attributes')
                    ->where('event_id', $event->id)
                    ->where('id', '>', $lastAttrId)
                    ->orderBy('id')
                    ->chunk(1000, function ($rows) use ($event, $col_fx_otx_events, $progressCol, &$insertedCount) {

                        $attributes = $rows->map(function ($attr) {
                            return [
                                'type' => $attr->type,
                                'event_id' => $attr->event_id,
                                'value' => $attr->value1,
                                'id' => $attr->id,
                                'category' => $attr->category,
                                'timestamp' => $attr->timestamp,
                            ];
                        })->toArray();

                        // ✅ ดึง tags
                        $tags = DB::connection('mysql_misp')->table('event_tags')
                            ->where('event_id', '=', $event->id)->get();

                        $tags_list = [];
                        foreach ($tags as $t) {
                            $tagData = DB::connection('mysql_misp')->table('tags')
                                ->select('name')->where('id', '=', $t->tag_id)->first();
                            if ($tagData && !in_array($tagData->name, array_column($tags_list, 'name'))) {
                                $tags_list[] = $tagData;
                            }
                        }

                        // ✅ สร้าง item
                        $item = [
                            'id' => $event->id,
                            'pulse_id' => 'misp.' . $event->id,
                            'info' => $event->info,
                            'date' => $event->date,
                            'published' => $event->published,
                            'publish_timestamp' => $event->publish_timestamp,
                            'timestamp' => $event->timestamp,
                            'orgc_id' => $this->findOrgName($event->orgc_id),
                            'org_id' => $this->findOrgName($event->org_id),
                            'attribute_count' => count($attributes),
                            'Tag' => $tags_list,
                            'Attribute' => $attributes,
                        ];

                        // ✅ save ลง Mongo
                        $countAttr = $this->saveRelatedIndicator($item);
                        $this->saveEvent($item, $countAttr);

                        $insertedCount++;
                        $lastAttrId = end($attributes)['id'] ?? 0;

                        // ✅ บันทึก progress ระหว่าง chunk
                        $progressCol->updateOne(
                            ['job' => 'pull_misp_data'],
                            [
                                '$set' => [
                                    'last_event_id' => $event->id,
                                    'last_attr_id' => $lastAttrId,
                                    'last_ts' => $event->timestamp,
                                    'updated_at' => new UTCDateTime(),
                                ]
                            ],
                            ['upsert' => true]
                        );

                        $this->info("💾 Saved chunk for event {$event->id}, last_attr_id={$lastAttrId}");
                    });

                // ✅ event เสร็จ reset attr id และบันทึก timestamp
                $progressCol->updateOne(
                    ['job' => 'pull_misp_data'],
                    [
                        '$set' => [
                            'last_event_id' => $event->id + 1,
                            'last_attr_id' => 0,
                            'last_ts' => $event->timestamp,
                            'updated_at' => new UTCDateTime(),
                        ]
                    ]
                );
            } catch (\Exception $e) {
                $this->error("❌ Error processing event {$event->id}: " . $e->getMessage());
                $this->warn("🕹️ Progress saved. Resume next run from this event.");
                break;
            }
        }


        $this->info("✅ Saved {$insertedCount} events to MongoDB between "
            . Carbon::createFromTimestamp($startTs)
            . " - " . Carbon::createFromTimestamp($endTs));
    }


    public function saveEvent($valueEvent, $countAttr)
    {
        try {

            $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
            $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
            $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
            $no_Indicator = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;
            $countPulse = $no_Indicator->countDocuments([
                'pulse_id' => 'misp.' . $valueEvent["id"]
            ]);
            $tlpcolor = null;
            $tags = null;
            $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);

            // $indicatorTypeCounts = isset($countAttr['byType']) ? $countAttr['byType'] : [];
            // $indicatorCount = isset($countAttr['all']) ? $countAttr['all'] : 0;


            // print_r($indicatorTypeCounts);
            // return;



            $incData = [];
            if (!empty($countAttr['byType'])) {
                foreach ($countAttr['byType'] as $type => $count) {
                    $incData["indicator_type_counts.$type"] = $count;
                }
            }

            if (!empty($valueEvent["Tag"])) {
                foreach ($valueEvent["Tag"] as $key => $value) {
                    // print_r($value->name);
                    // return;
                    // $this->info($value["name"]);
                    if (strpos($value->name, "tlp:") === false) {
                        $tags .= $value->name . ", ";
                    } else {
                        $tlpcolor = explode(":", $value->name)[1];
                    }
                }
                $tags = rtrim($tags, ", ");
            }

            if (!empty($incData)) {

                $update_fx_otx_events = $col_fx_otx_events->updateOne(
                    ['pulse_id' => "misp." . @$valueEvent["id"]],
                    [
                        '$inc' => $incData,
                        '$set' => [
                            'name' => @$valueEvent["info"],
                            'description' => @$valueEvent["info"],
                            'modified' => isset($valueEvent["timestamp"]) ? new UTCDateTime($valueEvent["timestamp"] * 1000) : null,
                            'created' => isset($valueEvent["date"]) ? new UTCDateTime(strtotime($valueEvent["date"]) * 1000) : null,
                            'public' => ($valueEvent["published"] == true) ? 1 : 0,
                            // 'public' => 0,
                            'TLP' => @$tlpcolor,
                            'indicator_count' => $countPulse,
                            'count_related_pulse' => @$countEvent["all"],
                            'is_modified' => ($valueEvent["timestamp"] == $valueEvent["publish_timestamp"]) ? false : true,
                            // 'indicator_type_counts' => [],
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
                            'transcation_id' => '',
                            'status' => 1,
                            'created_at' => $date_now,
                            'created_by' => "system",
                            'deleted_at' => null,
                            'transaction_date' => date("Y-m-d"),
                            'count_view' => 0,
                            'source' => "misp",
                            'creator_org' => @$valueEvent["orgc_id"],
                        ],
                    ],
                    ['upsert' => true]
                );
            } else {
                $update_fx_otx_events = $col_fx_otx_events->updateOne(
                    ['pulse_id' => "misp." . @$valueEvent["id"]],
                    [
                        // '$inc' => $incData,
                        '$set' => [
                            'name' => @$valueEvent["info"],
                            'description' => @$valueEvent["info"],
                            'modified' => isset($valueEvent["timestamp"]) ? new UTCDateTime($valueEvent["timestamp"] * 1000) : null,
                            'created' => isset($valueEvent["date"]) ? new UTCDateTime(strtotime($valueEvent["date"]) * 1000) : null,
                            'public' => ($valueEvent["published"] == true) ? 1 : 0,
                            // 'public' => 0,
                            'TLP' => @$tlpcolor,
                            'indicator_count' => $countPulse,
                            'count_related_pulse' => @$countEvent["all"],
                            'is_modified' => ($valueEvent["timestamp"] == $valueEvent["publish_timestamp"]) ? false : true,
                            // 'indicator_type_counts' => [],
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
                            'transcation_id' => '',
                            'status' => 1,
                            'created_at' => $date_now,
                            'created_by' => "system",
                            'deleted_at' => null,
                            'transaction_date' => date("Y-m-d"),
                            'count_view' => 0,
                            'source' => "misp",
                            'creator_org' => @$valueEvent["orgc_id"],
                        ],
                    ],
                    ['upsert' => true]
                );
            }
            // print_r($update_fx_otx_events);
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        return 0;
    }

    public function saveRelatedIndicator($valueEvent)
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
                                    'created_at' => isset($value["timestamp"]) ? new UTCDateTime($value["timestamp"] * 1000) : $date_now, 
                                ],
                                '$setOnInsert' => [
                                    'transcation_id' => '',
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
                                    'transcation_id' => '',
                                    'created_at' => isset($value["timestamp"]) ? new UTCDateTime($value["timestamp"] * 1000) : $date_now, 
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

    public function findOrgName($org_id)
    {
        if (!$org_id) return null;
        $org = DB::connection('mysql_misp')->table('organisations')
            ->select('name')
            ->where('id', '=', $org_id)
            ->first();
        return $org ? $org->name : null;
    }

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
