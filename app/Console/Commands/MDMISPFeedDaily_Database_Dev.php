<?php

namespace App\Console\Commands;

require 'vendor/autoload.php';

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

class MDMISPFeedDaily_Database_Dev extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:MDMISPFeedDaily_Database_Dev';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attr Feed';

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
        ini_set('memory_limit', '-1');
        //  echo app_path() . "5555555555555";


        //---------------------------------------------------------------

        /*
$pipeline = [
    [
        '$group' => [
            '_id' => [
                'month'=>['$month'=>'$created_at'],
                'year'=>['$year'=>'$created_at'],
            ],
            'COUNT(*)' => [
                '$sum' => 1
            ]
        ]
    ],
    [
        '$project' => [
            'COUNT_Attr' => '$COUNT(*)',
            'year' => '$_id.year',
            'month' => '$_id.month',
            'type' => 'Attr',
            '_id' => 0
        ]
    ],
    [
        '$sort' => [
            'year' =>-1,
            'month' => -1,
        ]
    ]
];

$options = [
    'allowDiskUse' => TRUE
];
$DB_MONGO_KEY = env("DB_MONGO_STOREDATA", "");
$clientMD = new \MongoDB\Client($DB_MONGO_KEY);
$col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;
$col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
$countAttr = $col_fx_otx_indicator_detail->aggregate($pipeline, $options);
$countAttr = $countAttr->toArray();

//print_r($countAttr);
//echo '================================================================';
//---------------------------------------------------------------
*/


        // Code Test
        // $events = DB::connection('mysql_misp')->table('events')
        //     ->select('id', 'info', 'date', 'orgc_id', 'published', 'attribute_count', 'timestamp')
        //     ->limit(1)
        //     ->get();

        // $attr = DB::connection('mysql_misp')->table('attributes')->where('event_id', '=', '1836')->limit(5)->get();
        // print_r($events);
        // return;

        // Code Test 

        // $id = 33420;
        // $result = $this->updateEventYes($id);
        // $this->info('Yes');
        // return;

        // $id = 33420;
        // $result = $this->updateEventNo($id);
        // $this->info('No');
        // return;


        $end = new DateTime();
        $start = clone $end;
        $start->sub(new DateInterval('PT6H'));

        $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);
        $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $col_fx_transaction_otx_event_stamp = $clientMD->sosecure_threatintelligent_dev->fx_transaction_otx_event_stamp;
        $ins_fx_transaction_otx_event_stamp = $col_fx_transaction_otx_event_stamp->insertOne([
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
        echo "Inserted ID: " . $ins_fx_transaction_otx_event_stamp->getInsertedId();

        $col_fx_transaction_otx_indicator_stamp = $clientMD->sosecure_threatintelligent_dev->fx_transaction_otx_indicator_stamp;
        $ins_fx_transaction_otx_indicator_stamp = $col_fx_transaction_otx_indicator_stamp->insertOne([
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
        echo "Inserted ID: " . $ins_fx_transaction_otx_indicator_stamp->getInsertedId();

        $stamp_event_id = $ins_fx_transaction_otx_event_stamp->getInsertedId();
        $stamp_indicator_id = $ins_fx_transaction_otx_indicator_stamp->getInsertedId();



        $this->saveJson($stamp_event_id, $stamp_indicator_id);


        $commandArtisan = 'app:MDCountIndicator';
        Artisan::call($commandArtisan);
    }


    public function saveJson($stamp_event_id, $stamp_indicator_id, $json_o = null)
    {

        // PREPARE DATA //
        $this->info('Prepaing DATA ...');

        // $sixHoursAgo = Carbon::now()->subHours(6)->timestamp;
        // $excludedTagIds = [3293, 7162];
        // $mysqlEvents = DB::connection('mysql_misp')->table('events')
        //     ->select('id', 'info', 'date', 'published', 'publish_timestamp', 'timestamp', 'orgc_id', 'org_id')
        //     // ->where('id', '=',     646)
        //     ->where('timestamp', '>=', $sixHoursAgo)
        //     // ->limit(1)
        //     ->whereNotExists(function ($query) use ($excludedTagIds) {
        //         $query->select(DB::raw(1))
        //             ->from('event_tags')
        //             ->whereColumn('event_tags.event_id', 'events.id')
        //             ->whereIn('event_tags.tag_id', $excludedTagIds);
        //     })
        //     // ->limit(1)
        //     ->get();


        $chk_tag = DB::connection('mysql_misp')->table('tags')
            ->select('id')
            ->whereRaw('LOWER(name) LIKE ?', ['%otx%'])
            ->pluck('id')
            ->toArray();

        $sixHoursAgo = Carbon::now()->subHours(6)->timestamp;
        $mysqlEvents = DB::connection('mysql_misp')->table('events')
            ->select('id', 'info', 'date', 'published', 'publish_timestamp', 'timestamp', 'orgc_id', 'org_id')
            ->where('timestamp', '>=', $sixHoursAgo)
            // ->where('id', '=', 	646)
            ->whereNotExists(function ($query) use ($chk_tag) {
                $query->select(DB::raw(1))
                    ->from('event_tags')
                    ->whereColumn('event_tags.event_id', 'events.id')
                    ->whereIn('event_tags.tag_id', $chk_tag);
            })
            ->get();

        $insertedCount = 0;

        // ATTRIBUTE START //


        $attributes = [];

        foreach ($mysqlEvents as $event) {
            $sixHoursAgo = Carbon::now()->subHours(6)->timestamp;
            // $val_ = 15982;

            $attributes = DB::connection('mysql_misp')->table('attributes')
                ->leftJoin('attribute_tags', 'attributes.id', '=', 'attribute_tags.attribute_id')
                ->leftJoin('tags', 'attribute_tags.tag_id', '=', 'tags.id')
                ->where('attributes.event_id', $event->id)
                ->where('attributes.timestamp', '>=', $sixHoursAgo)
                // ->where('attributes.id', $val_)
                ->select(
                    'attributes.id',
                    'attributes.event_id',
                    'attributes.type',
                    'attributes.value1',
                    'attributes.category',
                    'attributes.timestamp',
                    'tags.name as tag_name'
                )
                ->limit(30000)
                ->get()
                ->groupBy('id') // group by attribute id
                ->map(function ($items) {
                    $first = $items->first();
                    return [
                        'id' => $first->id,
                        'event_id' => $first->event_id,
                        'type' => $first->type,
                        'value' => $first->value1,
                        'category' => $first->category,
                        'timestamp' => $first->timestamp,
                        'tags' => $items->pluck('tag_name')->filter()->unique()->values()->all(),
                    ];
                })
                ->values()
                ->toArray();
        
            if (empty($attributes)) {
                continue;
            }

            // ATTRIBUTE END //

            // Event Tags START //

            $tags = [];
            $tags_list = [];
            if ($event) {
                $tag = DB::connection('mysql_misp')->table('event_tags')
                    ->where('event_id', '=', $event->id)
                    ->get();
            }

            if (count($tag)) {
                foreach ($tag as $tags) {
                    $tagData = DB::connection('mysql_misp')->table('tags')
                        ->select('name')
                        ->where('id', '=', $tags->tag_id)
                        ->first();

                    if ($tagData && !in_array($tagData->name, array_column($tags_list, 'name'))) {
                        $tags_list[] = $tagData;
                    }
                }
            }

            // ITEM START //
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
                'attribute_count' => count($attributes),
                'Tag' => $tags_list,
                'Attribute' => $attributes,
            ];
            // ITEM END //

            $countEvent = 0;


            // INSERT TO MONGODB START //
            $this->info("\n" . "Saving data to Sosecure TreatIntelligent....");

            $countAttr = $this->saveRelatedIndicator($item, $stamp_event_id, $stamp_indicator_id);
            // $countEvent = $this->saveRelatedEvent($item, $stamp_event_id, $stamp_indicator_id);
            $this->saveEvent($item, $stamp_event_id, $stamp_indicator_id, $countAttr, 0);

            // INSERT TO MONGODB END //

            $insertedCount++;
            $this->info("Completed " . $insertedCount . " events.");
            // return;
        }


        $this->info("Saved " . $insertedCount . " events to MongoDB.");
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
        $orgc_name = '';
        if ($org_id != null) {
            $orgc_name = DB::connection('mysql_misp')->table('organisations')
                ->select('name')
                ->where('id', '=', $org_id)
                ->get();
        }
        return $orgc_name[0]->name;
    }

    public function saveEvent($valueEvent, $stamp_event_id, $stamp_indicator_id, $countAttr, $countEvent)
    {
        try {

            $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
            $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
            $col_fx_otx_events = $clientMD->sosecure_threatintelligent_dev->fx_otx_events;
            $no_Indicator = $clientMD->sosecure_threatintelligent_dev->fx_otx_events_indicator_ref;
            $countPulse = $no_Indicator->countDocuments([
                'pulse_id' => 'misp.' . $valueEvent["id"]
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
                            'modified' => isset($valueEvent["publish_timestamp"]) ? new UTCDateTime($valueEvent["publish_timestamp"] * 1000) : null,
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
                            'modified' => isset($valueEvent["publish_timestamp"]) ? new UTCDateTime($valueEvent["publish_timestamp"] * 1000) : null,
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

    public function saveRelatedEvent($valueEvent, $stamp_event_id, $stamp_indicator_id)
    {
        try {
            //  echo "111111111111111";
            $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
            //  echo $DB_MONGO_KEY;
            $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
            $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);
            $data["all"] = 0;
            $col_fx_otx_events = $clientMD->sosecure_threatintelligent_dev->fx_otx_events;
            $col_fx_otx_events_event_ref = $clientMD->sosecure_threatintelligent_dev->fx_otx_events_event_ref;

            if (!empty($valueEvent["RelatedEvent"])) {
                foreach ($valueEvent["RelatedEvent"] as $key => $value) {
                    $data["all"]++;

                    // Test Save Related Attr   
                    // $countAttr = $this->saveRelatedIndicatorOfRelatedEvent($value, $valueEvent['RelatedEventAttr'], $stamp_event_id, $stamp_indicator_id);
                    // End Test

                    $update_fx_otx_events = $col_fx_otx_events->updateOne(
                        ['pulse_id' => "misp." . @$value["id"]],
                        [
                            '$set' => [
                                'name' => @$value["info"],
                                'description' => @$value["info"],
                                'modified' => isset($value["timestamp"]) ? new UTCDateTime($value["timestamp"] * 1000) : null,
                                'created' => isset($value["date"]) ? new UTCDateTime(strtotime($value["date"]) * 1000) : null,
                                'public' => ($value["published"] == true) ? 1 : 0,
                                'is_modified' => false,
                                'updated_at' => $date_now,
                                'updated_by' => "system",
                                // 'creator_org' => @$value["orgc_id"],
                                // 'public' => ($value["Event"]["published"] == true) ? 1 : 0,
                            ],
                            '$setOnInsert' => [
                                'references' => null,
                                'tags' => null,
                                'industries' => null,
                                'malware_families' => null,
                                'author_username' => null,
                                'indicator_type_counts' => array(),
                                'TLP' => null,
                                'indicator_count' => 0,
                                'groups' => null,
                                'transcation_id' => null,
                                'status' => 1,
                                'created_at' => $date_now,
                                'created_by' => "system",
                                'deleted_at' => null,
                                'transaction_date' => date("Y-m-d"),
                                'count_view' => 0,
                                'source' => "misp",
                                'creator_org' => @$value["orgc_id"],
                            ],
                        ],
                        ['upsert' => true]
                    );

                    $update_fx_otx_events_event_ref = $col_fx_otx_events_event_ref->updateOne(
                        [
                            'main_pulse_id' => "misp." . @$valueEvent["id"],
                            'pulse_id' => "misp." . @$value["id"]
                        ],
                        [
                            '$set' => [
                                'sub_pulse_modified' => isset($value["timestamp"]) ? new UTCDateTime($value["timestamp"] * 1000) : null,
                                'updated_at' => $date_now,
                                'updated_by' => "system",
                            ],
                            '$setOnInsert' => [
                                'status' => 1,
                                'created_at' => $date_now,
                                'created_by' => "system",
                                'deleted_at' => null,
                                'transaction_date' => date("Y-m-d"),
                                'source' => "misp",
                            ],
                        ],
                        ['upsert' => true]
                    );
                }
            }
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }

        return $data;
    }

    public function saveRelatedIndicator($valueEvent, $stamp_event_id, $stamp_indicator_id)
    {
        try {

            $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
            $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
            $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent_dev->fx_otx_indicator_detail;
            $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent_dev->fx_otx_events_indicator_ref;
            $col_fx_transaction_otx_indicators_data = $clientMD->sosecure_threatintelligent_dev->fx_transaction_otx_indicators_data;
            $col_fx_otx_type = $clientMD->sosecure_threatintelligent_dev->fx_otx_type;
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

                    $tagsString = "";
                    if (isset($value["tags"]) && is_array($value["tags"])) {
                        $cleanedTags = array_map('trim', $value["tags"]); 
                        $tagsString = implode(",", array_unique($cleanedTags)); 
                    }

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
                                    'indicator_tags' => isset($tagsString) ? $tagsString : '',
                                ],
                                '$setOnInsert' => [
                                    'transcation_id' => $stamp_indicator_id,
                                    'allrow' => $allRow,
                                    'status' => 1,
                                    'created_by' => "system",
                                    'deleted_at' => null,
                                    'transaction_date' => date("Y-m-d"),
                                    'source' => "misp",
                                    'creator_org' => $valueEvent["orgc_id"],
                                    // 'indicator_tags' => '',
                                    // 'indicator_tags' => isset($tagsString) ? $tagsString : '',
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
                                    // 'indicator_tags' => $tagsString,
                                    'indicator_tags' => isset($tagsString) ? $tagsString : '',
                                ],
                                '$setOnInsert' => [
                                    'status' => 1,

                                    'created_by' => "system",
                                    'deleted_at' => null,
                                    'transaction_date' => date("Y-m-d"),
                                    'source' => "misp",
                                    // 'indicator_tags' => '',
                                    // 'indicator_tags' => isset($tagsString) ? $tagsString : '',
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
                                // 'tags' => $tagsString,
                                'indicator_tags' => isset($tagsString) ? $tagsString : '',
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


    //     public function saveRelatedIndicatorOfRelatedEvent($valueEvent, $re_value, $stamp_event_id, $stamp_indicator_id)
    //     {
    //         try {

    //             $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
    //             $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
    //             $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent_dev->fx_otx_indicator_detail;
    //             $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent_dev->fx_otx_events_indicator_ref;
    //             $col_fx_transaction_otx_indicators_data = $clientMD->sosecure_threatintelligent_dev->fx_transaction_otx_indicators_data;
    //             $col_fx_otx_type = $clientMD->sosecure_threatintelligent_dev->fx_otx_type;
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
}
