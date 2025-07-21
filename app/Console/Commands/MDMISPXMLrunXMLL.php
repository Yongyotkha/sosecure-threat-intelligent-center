<?php

namespace App\Console\Commands;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;

class MDMISPXMLrunXMLL extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:MDMISPXMLrunXMLL';

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
     * @return mixed
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');
        echo app_path() . "5555555555555";

        $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);
        $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $col_fx_transaction_otx_event_stamp = $clientMD->sosecure_threatintelligent->fx_transaction_otx_event_stamp;
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
        ]);
        $col_fx_transaction_otx_indicator_stamp = $clientMD->sosecure_threatintelligent->fx_transaction_otx_indicator_stamp;
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
        ]);
        $stamp_event_id = $ins_fx_transaction_otx_event_stamp->getInsertedId();
        $stamp_indicator_id = $ins_fx_transaction_otx_indicator_stamp->getInsertedId();

        try {
            $this->saveJson($stamp_event_id, $stamp_indicator_id);
        } catch (Exception $e) {
            echo json_encode($e->getMessage());
        }

    }

    public function saveJson($stamp_event_id, $stamp_indicator_id, $json_o = null)
    {

        $dir_folder = app_path() . "\\Console\\Commands\\temp\\otx_export2\\";
        //$dir_folder = "127.0.0.1" . "\\Newfolder\\otx_export2\\";
        if (is_dir($dir_folder)) {
            if ($dh = opendir($dir_folder)) {
                    // $start = 667-1;
                    // $stop = 700+1;
                    $start = 492-1;
                    $stop = 600+1;
                    $loop = 0;
                    // rematch 0-300
                while (($file = readdir($dh)) !== false) {
                    if ($file != "." && $file != "..") {
                        $pieces = explode("_", $file)[1];
                        $pieces = (int)(explode(".", $pieces)[0]);
                        $this->info($pieces);
                    if($pieces>$start&&$pieces<$stop){
                        try {
                            $dir_xmlfile = $dir_folder . $file;
                            echo $dir_xmlfile;
                            
                                $content = file_get_contents($dir_xmlfile);
                                $xmlIndex = strpos($content, "<?xml");
                                $content = substr($content, $xmlIndex);
                                $xmlIndex = strrpos($content, ">") + 1;
                                $content = substr($content, 0, $xmlIndex);

                                $t_xml = new \DOMDocument();
                                //$t_xml->load($dir_folder);
                                $t_xml->loadXML($content);
                                unset($content);
                                $responseEvents = $t_xml->getElementsByTagName('response');

                                foreach ($responseEvents as $Events_) {

                                    $EventCheck = $Events_->getElementsByTagName('Event');
                                    $item = array();
                                    if ($EventCheck->length > 0) {
                                        $Event = $EventCheck->item(0);

                                        $tag = array();
                                        foreach ($Event->getElementsByTagName('Tag') as $Tags) {
                                            if($Tags->nodeValue){
                                                $tag[] = array('name' => $Tags->getElementsByTagName('name')->item(0)->nodeValue);
                                            }
                                        }

                                        $relatedevent = array();
                                        foreach ($Event->getElementsByTagName('RelatedEvent') as $RelatedEvents) {
                                            $RelatedEventsCheck = $RelatedEvents->getElementsByTagName('Event');
                                            if ($RelatedEventsCheck->length > 0) {
                                                $RelatedEvent = $RelatedEventsCheck->item(0);
                                                $relatedevent[] = array('Event' => array(
                                                    'id' => $RelatedEvent->getElementsByTagName('id')->item(0)->nodeValue,
                                                    'published' => $RelatedEvent->getElementsByTagName('published')->item(0)->nodeValue,
                                                    'date' => $RelatedEvent->getElementsByTagName('date')->item(0)->nodeValue,
                                                    'timestamp' => $RelatedEvent->getElementsByTagName('timestamp')->item(0)->nodeValue,
                                                    'info' => $RelatedEvent->getElementsByTagName('info')->item(0)->nodeValue,
                                                ),
                                                );
                                            }
                                        }

                                        $relatedattribute = array();

                                        foreach ($Event->getElementsByTagName('Attribute') as $RelatedAttributes) {
                                            if($RelatedAttributes->nodeValue){
                                                $relatedattribute[] = array(
                                                    'type' => $RelatedAttributes->getElementsByTagName('type')->item(0)->nodeValue,
                                                    'value' => $RelatedAttributes->getElementsByTagName('value')->item(0)->nodeValue,
                                                    'id' => $RelatedAttributes->getElementsByTagName('id')->item(0)->nodeValue,
                                                    'category' => $RelatedAttributes->getElementsByTagName('category')->item(0)->nodeValue,
                                                );
                                            }
                                        }
                                        $item = array(
                                            'id' => $Event->getElementsByTagName('id')->item(0)->nodeValue,
                                            'info' => $Event->getElementsByTagName('info')->item(0)->nodeValue,
                                            'publish_timestamp' => $Event->getElementsByTagName('publish_timestamp')->item(0)->nodeValue,
                                            'date' => $Event->getElementsByTagName('date')->item(0)->nodeValue,
                                            'published' => $Event->getElementsByTagName('published')->item(0)->nodeValue,
                                            'timestamp' => $Event->getElementsByTagName('timestamp')->item(0)->nodeValue,
                                            'Tag' => $tag,
                                            'RelatedEvent' => $relatedevent,
                                            'Attribute' => $relatedattribute,
                                        );

                                    }

                                }

                                if (!empty($item)) {
                                    try {
                                        $this->info(" id" . ": " . $item["id"]);
                                        $countAttr = $this->saveRelatedIndicator($item, $stamp_event_id, $stamp_indicator_id);
                                        $countEvent = $this->saveRelatedEvent($item, $stamp_event_id, $stamp_indicator_id);
                                        $this->saveEvent($item, $stamp_event_id, $stamp_indicator_id, $countAttr, $countEvent);
                                    } catch (Exception $e) {
                                        echo json_encode($e->getMessage());
                                    }
                                }

                            

                        } catch (Exception $e) {
                            echo json_encode($e->getMessage());
                        }
                    }


                }

                }


                $this->info("END");
                closedir($dh);
            }
        }

    }

    public function saveEvent($valueEvent, $stamp_event_id, $stamp_indicator_id, $countAttr, $countEvent)
    {
        $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
        $tlpcolor = null;
        $tags = null;
        $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);
        if (!empty($valueEvent["Tag"])) {
            foreach ($valueEvent["Tag"] as $key => $value) {
                if (strpos($value["name"], "tlp:") === false) {
                    $tags .= $value["name"] . ", ";
                } else {
                    $tlpcolor = explode(":", $value["name"])[1];
                }
            }
            $tags = rtrim($tags, ", ");
        }
        $update_fx_otx_events = $col_fx_otx_events->updateOne(
            ['pulse_id' => "misp_" . @$valueEvent["id"]],
            ['$set' => [
                'name' => @$valueEvent["info"],
                'description' => @$valueEvent["info"],
                'modified' => isset($valueEvent["publish_timestamp"]) ? new UTCDateTime($valueEvent["publish_timestamp"] * 1000) : null,
                'created' => isset($valueEvent["date"]) ? new UTCDateTime(strtotime($valueEvent["date"]) * 1000) : null,
                'public' => ($valueEvent["published"] == true) ? 1 : 0,
                'TLP' => @$tlpcolor,
                'indicator_count' => @$countAttr["all"],
                'count_related_pulse' => @$countEvent["all"],
                'is_modified' => ($valueEvent["timestamp"] == $valueEvent["publish_timestamp"]) ? false : true,
                'indicator_type_counts' => @$countAttr["byType"],
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
                ],
            ],
            ['upsert' => true]
        );
        return 0;
    }

    public function saveRelatedEvent($valueEvent, $stamp_event_id, $stamp_indicator_id)
    {
        $DB_MONGO_KEY = env("DB_MONGO_STOREDATAB", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);
        $data["all"] = 0;
        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
        $col_fx_otx_events_event_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_event_ref;

        if (!empty($valueEvent["RelatedEvent"])) {
            foreach ($valueEvent["RelatedEvent"] as $key => $value) {
                $data["all"]++;

                $update_fx_otx_events = $col_fx_otx_events->updateOne(
                    ['pulse_id' => "misp_" . @$value["Event"]["id"]],
                    ['$set' => [
                        'name' => @$value["Event"]["info"],
                        'description' => @$value["Event"]["info"],
                        'modified' => isset($value["Event"]["timestamp"]) ? new UTCDateTime($value["Event"]["timestamp"] * 1000) : null,
                        'created' => isset($value["Event"]["date"]) ? new UTCDateTime(strtotime($value["Event"]["date"]) * 1000) : null,
                        'public' => ($value["Event"]["published"] == true) ? 1 : 0,
                        'is_modified' => false,
                        'updated_at' => $date_now,
                        'updated_by' => "system",
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
                        ],
                    ],
                    ['upsert' => true]
                );

                $update_fx_otx_events_event_ref = $col_fx_otx_events_event_ref->updateOne(
                    [
                        'main_pulse_id' => "misp_" . @$valueEvent["id"],
                        'pulse_id' => "misp_" . @$value["Event"]["id"]],
                    ['$set' => [
                        'sub_pulse_modified' => isset($value["Event"]["timestamp"]) ? new UTCDateTime($value["Event"]["timestamp"] * 1000) : null,
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

        return $data;
    }

    public function saveRelatedIndicator($valueEvent, $stamp_event_id, $stamp_indicator_id)
    {
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

                // $document = $col_fx_otx_indicator_detail->findOne(['indicator_id' => "misp_".@$value["id"]],
                //     [
                //         'projection' => [
                //             "_id" => 1,
                //         ]
                //     ]
                // );
                if (true) {
                    $update_fx_otx_indicator_detail = $col_fx_otx_indicator_detail->updateOne(
                        [
                            'indicator_id' => "misp_" . @$value["id"],
                        ],
                        [
                            '$set' => [
                                'indicator_name' => @$value["value"],
                                'role' => @$value["category"],
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
                            ],
                        ],
                        ['upsert' => true]
                    );

                    $update_fx_transaction_otx_indicators_data = $col_fx_transaction_otx_indicators_data->updateOne(
                        ['indicator_id' => "misp_" . @$value["id"]],
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

                $update_fx_otx_events_indicator_ref = $col_fx_otx_events_indicator_ref->updateOne(
                    [
                        'indicator_id' => "misp_" . @$value["id"],
                        'pulse_id' => "misp_" . @$valueEvent["id"],
                    ],
                    [
                        '$set' => [
                            'pulse_modified' => isset($valueEvent["date"]) ? new UTCDateTime($valueEvent["publish_timestamp"] * 1000) : null,
                            'role' => @$value["category"],
                            'created' => isset($value["timestamp"]) ? new UTCDateTime($value["timestamp"] * 1000) : null,
                            'expiration' => null,
                            'is_active' => null,
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
                        ],
                    ],
                    ['upsert' => true]
                );

            }
        }

        return $data;
    }
}
