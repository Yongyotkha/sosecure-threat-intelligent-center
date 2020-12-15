<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use MongoDB\BSON\UTCDateTime;
class OTXMDFeedPulse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:OTXMDFeedPulse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        $urlLimit = 3;
        $retryLimit = 1;

        $roundRetry = 0;
        $loop = 0;

        do {
            try {
                $otxFeedDataCheck = true;
                $otxSuccessCheck = true;
                $OTX_KEY = env("OTX_KEY", "");
                $DB_MONGO_KEY = env("DB_MONGO_DEV", "");

                $clientHttp = new \GuzzleHttp\Client();
                $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
                $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);
                if (!isset($insertOneResult)) {
                    $collectionStamp = $clientMD->sosecure_threatintelligent->fx_transaction_otx_event_stamp;
                    $insertOneResult = $collectionStamp->insertOne([
                        'code' => generator_uuid(),
                        'transaction_date' => date("Y-m-d"),
                        'status' => 1,
                        'created_at' => $date_now,
                        'created_by' => "system",
                        'updated_at' => $date_now ,
                        'updated_by' => "system",
                        'deleted_at' => null,
                    ]);
                }//modified:%3C1d
                $reconCall = $this->reconnnect('https://otx.alienvault.com/otxapi/pulses/?limit=100&page=1&sort=-modified&q=modified:%3C1d', $urlLimit);
                if ($reconCall["success"]) {
                    $otxFeedData = json_decode($reconCall["result"], true);
                } else {
                    $otxFeedDataCheck = false;
                    $otxSuccessCheck = false;
                    $this->info("FAIL1");
                }

                while ($otxFeedDataCheck) {
                    $loop++;
                    if (!empty($otxFeedData["results"])) {
                        $checkSuccessDummy = $this->savePulseRef($otxFeedData["results"],$insertOneResult->getInsertedId(), $urlLimit)["success"];
                        if(!$checkSuccessDummy){
                            $checkSuccess = false;
                        }
                    }
                    if (isset($otxFeedData["next"])) {
                        $this->info($otxFeedData["next"]);
                        $reconCall = $this->reconnnect($otxFeedData["next"], $urlLimit);
                        if ($reconCall["success"]) {
                            $otxFeedData = json_decode($reconCall["result"], true);
                        } else {
                            $otxSuccessCheck = false;
                            $otxFeedDataCheck = false;
                            $this->info("FAIL2");
                        }
                    } else {
                        $otxFeedDataCheck = false;
                    }
                }

            } catch (Exception $e) {
                $error["Exception"] = $e->getMessage();
                $otxSuccessCheck = false;
                $this->info("FAIL3");
            }
            $roundRetry++;
        } while ($roundRetry < $retryLimit && !$otxSuccessCheck);
        if ($otxSuccessCheck && isset($insertOneResult)) {
            $updateResult2 = $collectionStamp->updateOne(
                ['_id' => $insertOneResult->getInsertedId()],
                ['$set' => ['status' => 2]]
            );
            $this->info("app:OTXMDFeedIndicator SUCCESS ALL CONTENT");
        } else {
            $this->info("app:OTXMDFeedIndicator FAIL SOME CONTENT");
        }
    }

    public function reconnnect($url, $limit)
    {
        $_OTX_KEY = env("OTX_KEY", "");
        $_clientHttp = new \GuzzleHttp\Client();
        $_reconnect = 0;
        $_otxReconnect = true;
        $_dataOut["result"] = "";
        $_dataOut["success"] = false;
        while ($_otxReconnect && $_reconnect < $limit) {
            try {
                $_bodyData = $_clientHttp->request(
                    'GET',
                    $url,
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Content-type' => 'application/json',
                            'X-OTX-API-KEY' => $_OTX_KEY,
                        ],
                        'delay' => 200, //millisec == 1sec
                        'timeout' => 59, //sec == 100sec
                    ]
                )->getBody();
                $_dataOut["result"] = $_bodyData;
                $_dataOut["success"] = true;
                $_otxReconnect = false;
                //echo "  Pass : " . $_reconnect;
            } catch (Exception $e) {
                //echo "  Fail : " . $_reconnect;
            }
            $_reconnect++;
        }
        return $_dataOut;
    }

    public function savePulseRef($pulses,$InsertedId, $urlLimit)
    {
        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $checkSuccess = true;
        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
        $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;
        $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);
        $loop = 0;
        if (!empty($pulses)) {
            foreach ($pulses as $value) {
                try {
                    $url_1 = "https://otx.alienvault.com/otxapi/pulses/" . $value["id"] . "/";
                    $reconCall = $this->reconnnect($url_1, $urlLimit);
                    if ($reconCall["success"]) {
                        $otxPulseDetail = json_decode($reconCall["result"], true);
                        $groups = implode(', ', array_column(isset($otxPulseDetail["groups"])?$otxPulseDetail["groups"]:[] , 'name'));
                    } else {
                       $checkSuccess = false;
                    }
                    $references = implode(', ', isset($value["references"]) ? $value["references"] : []);
                    $tags = implode(', ', isset($value["tags"]) ? $value["tags"] : []);
                    $industries = implode(', ', isset($value["industries"]) ? $value["industries"] : []);
                    $malware_families = implode(', ', array_column(isset($value["malware_families"]) ? $value["malware_families"] : [], 'display_name'));

                    $update_fx_otx_events = $col_fx_otx_events->updateOne(
                        ['pulse_id' => isset($value["id"]) ? $value["id"] : ""],
                        ['$set' => [
                            'name' => isset($value["name"]) ? $value["name"] : "",
                            'description' => isset($value["description"]) ? $value["description"] : "",
                            'modified' => isset($value["modified"]) ? new UTCDateTime(strtotime($value["modified"])*1000) : null,
                            'created' => isset($value["created"]) ? new UTCDateTime(strtotime($value["created"])*1000) : null,
                            'public' => isset($value["public"]) ? $value["public"] : "",
                            'TLP' => isset($value["TLP"]) ? $value["TLP"] : "",
                            'indicator_count' => isset($value["indicator_count"]) ? $value["indicator_count"] : "",
                            'is_modified' => isset($value["is_modified"]) ? $value["is_modified"] : "",
                            'indicator_type_counts' => isset($value["indicator_type_counts"]) ? $value["indicator_type_counts"] : "",
                            'references' => isset($references) ? $references : "",
                            'tags' => isset($tags) ? $tags : "",
                            'industries' => isset($industries) ? $industries : "",
                            'malware_families' => isset($malware_families) ? $malware_families : "",
                            'groups' => isset($groups) ? $groups : "",
                            'author_username' => isset($value["author"]["username"]) ? $value["author"]["username"] : "",
                            'updated_at' => $date_now ,
                            'updated_by' => "system",
                        ],
                            '$setOnInsert' => [
                                'transcation_id' => $InsertedId,
                                'status' => 1,
                                'created_at' => $date_now ,
                                'created_by' => "system",
                                'deleted_at' => null,
                                'transaction_date' => date("Y-m-d"),
                                'count_view' => 0,
                            ],
                        ],
                        ['upsert' => true]
                    );
                    if(isset($value["id"])){
                        $checkSuccessDummy = $this->saveIndicator_ref($value["id"],$urlLimit)["success"];
                        if(!$checkSuccessDummy){
                            $checkSuccess = false;
                        }
                        $checkSuccessDummy = $this->savePulse_related($value["id"],$urlLimit)["success"];
                        if(!$checkSuccessDummy){
                            $checkSuccess = false;
                        }
                    }
                    
                } catch (Exception $e) {
                    $checkSuccess = false;
                }
            }

        }
        $dataOut["success"] = $checkSuccess;
        return $dataOut;
    }

    public function saveIndicator_ref($pulseID,$urlLimit)
    {
        $allRow = (object) array();
        $dayMoreThan = 9;
        $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);
        try {
            $otxSuccessCheck = true;
            $otxFeedDataCheck = true;
            $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
            $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
            $collectionBasic = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;
            $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;
            $loop = 0;
            $reconCall = $this->reconnnect('https://otx.alienvault.com/otxapi/pulses/'.$pulseID.'/indicators/?sort=-created&limit=5000&page=1', $urlLimit);
            if ($reconCall["success"]) {
                $otxFeedData = json_decode($reconCall["result"], true);
            } else {
                $otxFeedDataCheck = false;
                $otxSuccessCheck = false;
                $this->info("FAIL41");
                echo 'https://otx.alienvault.com/otxapi/pulses/'.$pulseID.'/indicators/?sort=-created&limit=5000&page=1';
            }
            while ($otxFeedDataCheck) {
                $loop++;
                if (!empty($otxFeedData["results"])) {
                    
                    foreach ($otxFeedData["results"] as $value) {
                        $date1 = date_create($value["created"]);
                        $date2 = date_create(date("Y-m-d H:i:s"));
                        $diff = date_diff($date1, $date2);
                        if ($diff->format("%R%a") > $dayMoreThan) {
                            // echo json_encode($date1);
                            // echo json_encode($date2);
                            // echo "break++++++++";
                            $otxFeedDataCheck = false;
                            break;
                        }
                        try {
                            $updateResult = $collectionBasic->updateOne(
                                ['indicator_id' => $value["id"]],
                                ['$set' => [
                                    'indicator_name' => $value["indicator"],
                                    'type' => $value["type"],
                                    'updated_at' => $date_now,
                                    'updated_by' => "system",
                                ],
                                    '$setOnInsert' => [
                                        'transcation_id' => null,
                                        'allrow' => $allRow,
                                        'status' => 1,
                                        'created_at' => $date_now,
                                        'created_by' => "system",
                                        'deleted_at' => null,
                                        'transaction_date' => date("Y-m-d"),
                                    ],
                                ],
                                ['upsert' => true]
                            );

                            $update_fx_otx_events_indicator_ref = $col_fx_otx_events_indicator_ref->updateOne(
                                ['indicator_id' => (isset($value["id"]) ? $value["id"] : ""),
                                    'pulse_id' => (isset($pulseID) ? $pulseID : "")],
                                ['$set' => [
                                    'created' => (isset($value["created"]) ? new UTCDateTime(strtotime($value["created"])*1000) : null),
                                    'expiration' => (isset($value["expiration"]) ? new UTCDateTime(strtotime($value["expiration"])*1000) : null),
                                    'is_active' => (isset($value["is_active"]) ? $value["is_active"] : ""),
                                    'updated_at' => $date_now,
                                    'updated_by' => "system",
                                ],
                                    '$setOnInsert' => [
                                        'status' => 1,
                                        'created_at' => $date_now,
                                        'created_by' => "system",
                                        'deleted_at' => null,
                                        'transaction_date' => date("Y-m-d"),
                                    ],
                                ],
                                ['upsert' => true]
                            );

                        } catch (Exception $e) {
                            $otxFeedDataCheck = false;
                            $error["Exception"] = $e->getMessage();
                            $otxSuccessCheck = false;
                            $this->info("FAIL5");
                            // echo json_encode($error["Exception"]);
                            // echo json_encode($value);
                            break;
                        }
                    }
                }

               
                if (isset($otxFeedData["next"])&&$otxFeedDataCheck) {
                    echo ($otxFeedData["next"]);
                    $reconCall = $this->reconnnect($otxFeedData["next"], $urlLimit);
                    if ($reconCall["success"]) {
                        $otxFeedData = json_decode($reconCall["result"], true);
                    } else {
                        $otxSuccessCheck = false;
                        $otxFeedDataCheck = false;
                        $this->info("FAIL6");
                    }
                } else {
                    $otxFeedDataCheck = false;
                }
            }
        } catch (Exception $e) {
            $otxSuccessCheck = false;
        }
        $dataOut["success"] = $otxSuccessCheck;
        return $dataOut;
    }

    public function savePulse_related($pulseID,$urlLimit)
    {
        $allRow = (object) array();
        $dayMoreThan = 9;
        $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);
        try {
            $otxSuccessCheck = true;
            $otxFeedDataCheck = true;
            $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
            $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
            $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
            $col_fx_otx_events_event_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_event_ref;
            $loop = 0;
            $reconCall = $this->reconnnect('https://otx.alienvault.com/otxapi/pulses/'.$pulseID.'/related?limit=100', $urlLimit);
            if ($reconCall["success"]) {
                $otxFeedData = json_decode($reconCall["result"], true);
            } else {
                $otxFeedDataCheck = false;
                $otxSuccessCheck = false;
                $this->info("FAIL42");
                echo 'https://otx.alienvault.com/otxapi/pulses/'.$pulseID.'/related?limit=100';
            }
            while ($otxFeedDataCheck) {
                $loop++;
                if (!empty($otxFeedData["results"])) {
                    foreach ($otxFeedData["results"] as $value) {
                        $date1 = date_create($value["modified"]);
                        $date2 = date_create(date("Y-m-d H:i:s"));
                        $diff = date_diff($date1, $date2);
                        if ($diff->format("%R%a") > $dayMoreThan) {
                            // echo json_encode($date1);
                            // echo json_encode($date2);
                            // echo "break++++++++";
                            $otxFeedDataCheck = false;
                            break;
                           
                        }
                        try {
                            $references = implode(', ', isset($value["references"]) ? $value["references"] : []);
                            $tags = implode(', ', isset($value["tags"]) ? $value["tags"] : []);
                            $industries = implode(', ', isset($value["industries"]) ? $value["industries"] : []);
                            $malware_families = implode(', ', array_column(isset($value["malware_families"]) ? $value["malware_families"] : [], 'display_name'));
                            
                            $update_fx_otx_events = $col_fx_otx_events->updateOne(
                                ['pulse_id' => isset($value["id"]) ? $value["id"] : ""],
                                ['$set' => [
                                    'name' => isset($value["name"]) ? $value["name"] : "",
                                    'description' => isset($value["description"]) ? $value["description"] : "",
                                    'modified' => isset($value["modified"]) ? new UTCDateTime(strtotime($value["modified"])*1000) : null,
                                    'created' => isset($value["created"]) ? new UTCDateTime(strtotime($value["created"])*1000) : null,
                                    'public' => isset($value["public"]) ? $value["public"] : "",
                                    'TLP' => isset($value["TLP"]) ? $value["TLP"] : "",
                                    'indicator_count' => isset($value["indicator_count"]) ? $value["indicator_count"] : "",
                                    'is_modified' => isset($value["is_modified"]) ? $value["is_modified"] : "",
                                    'indicator_type_counts' => isset($value["indicator_type_counts"]) ? $value["indicator_type_counts"] : "",
                                    'references' => isset($references) ? $references : "",
                                    'tags' => isset($tags) ? $tags : "",
                                    'industries' => isset($industries) ? $industries : "",
                                    'malware_families' => isset($malware_families) ? $malware_families : "",
                                    'author_username' => isset($value["author"]["username"]) ? $value["author"]["username"] : "",
                                    'updated_at' => $date_now ,
                                    'updated_by' => "system",
                                ],
                                    '$setOnInsert' => [
                                        'groups' => isset($groups) ? $groups : "",
                                        'transcation_id' => null,
                                        'status' => 1,
                                        'created_at' => $date_now ,
                                        'created_by' => "system",
                                        'deleted_at' => null,
                                        'transaction_date' => date("Y-m-d"),
                                        'count_view' => 0,
                                    ],
                                ],
                                ['upsert' => true]
                            );

                            $update_fx_otx_events_event_ref = $col_fx_otx_events_event_ref->updateOne(
                                [   'main_pulse_id' => (isset($pulseID) ? $pulseID : ""),
                                    'pulse_id' => (isset($value["id"]) ? $value["id"] : "")],
                                ['$set' => [
                                    'updated_at' => $date_now,
                                    'updated_by' => "system",
                                ],
                                    '$setOnInsert' => [
                                        'status' => 1,
                                        'created_at' => $date_now,
                                        'created_by' => "system",
                                        'deleted_at' => null,
                                        'transaction_date' => date("Y-m-d"),
                                    ],
                                ],
                                ['upsert' => true]
                            );

                        } catch (Exception $e) {
                            $otxFeedDataCheck = false;
                            $error["Exception"] = $e->getMessage();
                            $otxSuccessCheck = false;
                            $this->info("FAIL9");
                            // echo json_encode($error["Exception"]);
                            // echo json_encode($value);
                            break;
                        }
                    }
                }

               
                if (isset($otxFeedData["next"])&&$otxFeedDataCheck) {
                    echo ($otxFeedData["next"]);
                    $reconCall = $this->reconnnect($otxFeedData["next"], $urlLimit);
                    if ($reconCall["success"]) {
                        $otxFeedData = json_decode($reconCall["result"], true);
                    } else {
                        $otxSuccessCheck = false;
                        $otxFeedDataCheck = false;
                        $this->info("FAIL10");
                    }
                } else {
                    $otxFeedDataCheck = false;
                }
            }
        } catch (Exception $e) {
            $otxSuccessCheck = false;
        }
        $dataOut["success"] = $otxSuccessCheck;
        return $dataOut;
    }

}
