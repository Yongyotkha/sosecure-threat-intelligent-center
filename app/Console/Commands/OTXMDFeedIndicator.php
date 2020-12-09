<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;

class OTXMDFeedIndicator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:OTXMDFeedIndicator';

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

                if (!isset($insertOneResult)) {
                    $collectionStamp = $clientMD->sosecure_threatintelligent->fx_transaction_otx_indicator_stamp;
                    $insertOneResult = $collectionStamp->insertOne([
                        'code' => generator_uuid(),
                        'transaction_date' => date("Y-m-d"),
                        'status' => 1,
                        'created_at' => date("Y-m-d H:i:s"),
                        'created_by' => "system",
                        'updated_at' => date("Y-m-d H:i:s"),
                        'updated_by' => "system",
                        'deleted_at' => null,
                    ]);
                }
                //$reconCall = $this->reconnnect('https://otx.alienvault.com/otxapi/indicators/?include_inactive=0&sort=-modified&q=modified:%3C3h&page=1&limit=100', $urlLimit);
                $reconCall = $this->reconnnect('https://otx.alienvault.com/otxapi/indicators/?type=CVE&include_inactive=0&sort=-modified&q=modified:""&page=1&limit=100', $urlLimit);
                if ($reconCall["success"]) {
                    $otxFeedData = json_decode($reconCall["result"], true);
                } else {
                    $otxFeedDataCheck = false;
                    $otxSuccessCheck = false;
                    $this->info("FAIL");
                }

                while ($otxFeedDataCheck) {
                    foreach ($otxFeedData["results"] as $value) {
                        try {
                            $collectionData = $clientMD->sosecure_threatintelligent->fx_transaction_otx_indicatiors_data;
                            $updateResult = $collectionData->updateOne(
                                ['indicator_id' => $value["id"]],
                                ['$set' => [
                                    'indicatior' => $value["indicator"],
                                    'type' => $value["type"],
                                    'tile' => $value["title"],
                                    'desciption' => $value["description"],
                                    'slug' => $value["slug"],
                                    'name' => $value["name"],
                                    'updated_at' => date("Y-m-d H:i:s"),
                                    'updated_by' => "system",
                                    'transcation_id' => $insertOneResult->getInsertedId(),
                                ],
                                    '$setOnInsert' => [
                                        'status' => 1,
                                        'created_at' => date("Y-m-d H:i:s"),
                                        'created_by' => "system",
                                        'deleted_at' => null,
                                        'transaction_date' => date("Y-m-d"),
                                    ],
                                ],
                                ['upsert' => true]
                            );

                            $check_caseByType = $this->caseByType($value["type"], $value["indicator"], $value["id"], $urlLimit)["success"];
                            if (!$check_caseByType) {
                                $otxSuccessCheck = false;
                            }

                        } catch (Exception $e) {
                            $error["Exception"] = $e;
                            $otxSuccessCheck = false;
                            $this->info("FAIL");
                        }
                    }
                    $loop++;
                    //echo $loop . "-";
                    if (isset($otxFeedData["next"])) {
                        echo ($otxFeedData["next"]);
                        $reconCall = $this->reconnnect($otxFeedData["next"], $urlLimit);
                        if ($reconCall["success"]) {
                            $otxFeedData = json_decode($reconCall["result"], true);
                        } else {
                            $otxSuccessCheck = false;
                            $otxFeedDataCheck = false;
                            $this->info("FAIL");
                        }
                    } else {
                        $otxFeedDataCheck = false;
                    }
                }

            } catch (Exception $e) {
                $error["Exception"] = $e;
                $otxSuccessCheck = false;
                $this->info("FAIL");
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

    public function caseByType($type, $indicatorName, $indicatorID, $urlLimit)
    {
        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $dayMoreThan = 29;
        $checkSuccess = true;

        try {
            $collectionBasic = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;
            $document = $collectionBasic->findOne(['indicator_id' => $indicatorID], [
                'projection' => [
                    "updated_at" => 1,
                ]]
            );
            $goOn = true;
            if(!empty($document)){
                $date1 = date_create($document->updated_at);
                $date2 = date_create(date("Y-m-d H:i:s"));
                $diff = date_diff($date1, $date2);
                if ($diff->format("%R%a") > $dayMoreThan) {
                    $goOn = true;
                }else{
                    $goOn = false;
                }
            }
            
            if ($goOn) {
                if ($type == "CIDR" || $type == "FileHash-IMPHASH" || $type == "FileHash-PEHASH" || $type == "FilePath" || $type == "Mutex" || $type == "URI") {
                    //noclick
                } else if ($type == "JA3" || $type == "osquery") {
                    //nodata
                } else if ($type == "SSLCertFingerprint" || $type == "BitcoinAddress") {
                    //canclick but nodata
                } else if ($type == "CVE") {
                    //caseByType
                    //https://otx.alienvault.com/otxapi/indicators/cve/general/CVE-2017-0199
                    $url_1 = "https://otx.alienvault.com/otxapi/indicators/cve/general/" . $indicatorName;
                    $reconCall = $this->reconnnect($url_1, $urlLimit);
                    if ($reconCall["success"]) {
                        $otxBasicData = json_decode($reconCall["result"], true);
                        $allRow = (object) array("description" => isset($otxBasicData["description"]) ? $otxBasicData["description"] : "",
                            "CWE" => isset($otxBasicData["cwe"]) ? $otxBasicData["cwe"] : "",
                            "CVE" => isset($otxBasicData["cve"]) ? $otxBasicData["cve"] : "",
                            "CREATION DATE" => isset($otxBasicData["date_created"]) ? $otxBasicData["date_created"] : "",
                            "LAST MODIFIED DATE" => isset($otxBasicData["date_modified"]) ? $otxBasicData["date_modified"] : "",
                        );
                        $checkSuccess = $this->saveIndicator_detail($indicatorID, $indicatorName, $type, $allRow)["success"];
                        if (!empty($otxBasicData["pulse_info"]["pulses"])) {
                            $checkSuccess = $this->savePulseRef($otxBasicData["pulse_info"]["pulses"], $indicatorID, $urlLimit)["success"];
                        }
                    } else {
                        $checkSuccess = false;
                    }
                }

            }
        } catch (Exception $e) {
            $checkSuccess = false;
        }

        $dataOut["success"] = $checkSuccess;
        return $dataOut;
    }

    public function saveIndicator_detail($indicatorID, $indicatorName, $type, $allRow)
    {
        try {
            $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
            $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
            $collectionBasic = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;
            $updateResult = $collectionBasic->updateOne(
                ['indicator_id' => $indicatorID],
                ['$set' => [
                    'indicatior_name' => $indicatorName,
                    'type' => $type,
                    'allrow' => $allRow,
                    'updated_at' => date("Y-m-d H:i:s"),
                    'updated_by' => "system",
                ],
                    '$setOnInsert' => [
                        'status' => 1,
                        'created_at' => date("Y-m-d H:i:s"),
                        'created_by' => "system",
                        'deleted_at' => null,
                        'transaction_date' => date("Y-m-d"),
                    ],
                ],
                ['upsert' => true]
            );
            $checkSuccess = true;
        } catch (Exception $e) {
            $checkSuccess = false;
        }

        $dataOut["success"] = $checkSuccess;
        return $dataOut;
    }

    public function savePulseRef($pulses, $indicatorID, $urlLimit)
    {
        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $checkSuccess = true;
        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
        $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;

        //$col_fx_otx_events_event_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_event_ref;
        if (!empty($pulses)) {
            foreach ($pulses as $value) {
                try {
                    //$url_1 = "https://otx.alienvault.com/otxapi/pulses/" . $value["id"];
                    //$reconCall = $this->reconnnect($url_1, $urlLimit);
                    //if ($reconCall["success"]) {
                    //$otxPulseDetail = json_decode($reconCall["result"], true);
                    //$groups = implode(', ', array_column(isset($otxPulseDetail["groups"])?$otxPulseDetail["groups"]:[] , 'name'));
                    //} else {
                    //    $checkSuccess = false;
                    //}
                    $references = implode(', ', isset($value["references"]) ? $value["references"] : []);
                    $tags = implode(', ', isset($value["tags"]) ? $value["tags"] : []);
                    $industries = implode(', ', isset($value["industries"]) ? $value["industries"] : []);
                    $malware_families = implode(', ', array_column(isset($value["malware_families"]) ? $value["malware_families"] : [], 'display_name'));

                    $update_fx_otx_events = $col_fx_otx_events->updateOne(
                        ['pulse_id' => isset($value["id"]) ? $value["id"] : ""],
                        ['$set' => [
                            'name' => isset($value["name"]) ? $value["name"] : "",
                            'description' => isset($value["description"]) ? $value["description"] : "",
                            'modified' => isset($value["modified"]) ? $value["modified"] : "",
                            'created' => isset($value["created"]) ? $value["created"] : "",
                            'public' => isset($value["public"]) ? $value["public"] : "",
                            'TLP' => isset($value["TLP"]) ? $value["TLP"] : "",
                            'indicator_count' => isset($value["indicator_count"]) ? $value["indicator_count"] : "",
                            'is_modified' => isset($value["is_modified"]) ? $value["is_modified"] : "",
                            'indicator_type_counts' => isset($value["indicator_type_counts"]) ? $value["indicator_type_counts"] : "",
                            'references' => isset($references) ? $references : "",
                            'tags' => isset($tags) ? $tags : "",
                            'groups' => isset($groups) ? $groups : "",
                            'industries' => isset($industries) ? $industries : "",
                            'malware_families' => isset($malware_families) ? $malware_families : "",
                            'author_username' => isset($value["author"]["username"]) ? $value["author"]["username"] : "",
                            'updated_at' => date("Y-m-d H:i:s"),
                            'updated_by' => "system",
                        ],
                            '$setOnInsert' => [
                                'transcation_id' => null,
                                'status' => 1,
                                'created_at' => date("Y-m-d H:i:s"),
                                'created_by' => "system",
                                'deleted_at' => null,
                                'transaction_date' => date("Y-m-d"),
                            ],
                        ],
                        ['upsert' => true]
                    );

                    $update_fx_otx_events_indicator_ref = $col_fx_otx_events_indicator_ref->updateOne(
                        ['indicator_id' => (isset($indicatorID) ? $indicatorID : ""),
                            'pulse_id' => (isset($value["id"]) ? $value["id"] : "")],
                        ['$set' => [
                            'updated_at' => date("Y-m-d H:i:s"),
                            'updated_by' => "system",
                        ],
                            '$setOnInsert' => [
                                'status' => 1,
                                'created_at' => date("Y-m-d H:i:s"),
                                'created_by' => "system",
                                'deleted_at' => null,
                                'transaction_date' => date("Y-m-d"),
                            ],
                        ],
                        ['upsert' => true]
                    );
                } catch (Exception $e) {
                    $checkSuccess = false;
                }

            }
        }
        $dataOut["success"] = $checkSuccess;
        return $dataOut;
    }

}
