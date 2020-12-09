<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;

class OTXFeedPulse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:OTXFeedPulse';

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
        $retryLimit = 3;

        $roundRetry = 0;
        $loop = 0;
        //https://otx.alienvault.com/otxapi/indicators/cve/general/CVE-2017-0199
        //echo json_encode($this->caseByType("CVE","CVE-2017-0199","11502",$urlLimit));
        $this->testfun();
    }

    public function testfun(){
        $dayMoreThan = 31;
        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $collectionBasic = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;
        $document = "5555";
        $document = $collectionBasic->findOne(['indicator_id' => 9992680006873], [
            'projection' => [
                "updated_at" => 1,
            ]]);

        if(!empty($document)){
            $date1 = date_create($document->updated_at);
            $date2 = date_create(date("Y-m-d H:i:s"));
            $diff = date_diff($date1, $date2);
            if ($diff->format("%R%a") > $dayMoreThan) {
                echo ($diff->format("%R%a")."");
            }else{
                echo "negative";
                echo ($diff->format("%R%a")."");
            }
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
                        'delay' => 500, //millisec == 1sec
                        'timeout' => 59, //sec == 100sec
                    ]
                )->getBody();
                $_dataOut["result"] = $_bodyData;
                $_dataOut["success"] = true;
                $_otxReconnect = false;
                echo "  Pass : " . $_reconnect;
            } catch (Exception $e) {
                echo "  Fail : " . $_reconnect;
            }
            $_reconnect++;
        }
        return $_dataOut;
    }

    public function caseByType($type, $indicatorName, $indicatorID,$urlLimit)
    {
        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
       
        $checkSuccess = true;
        try {
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
                    $allRow = (object) array("description" => isset($otxBasicData["description"])?$otxBasicData["description"]:"", 
                    "CWE" => isset($otxBasicData["cwe"])?$otxBasicData["cwe"]:"", 
                    "CVE" => isset($otxBasicData["cve"])?$otxBasicData["cve"]:"", 
                    "CREATION DATE" => isset($otxBasicData["date_created"])?$otxBasicData["date_created"]:"", 
                    "LAST MODIFIED DATE" => isset($otxBasicData["date_modified"])?$otxBasicData["date_modified"]:""
                    );
                    $checkSuccess = $this->saveIndicator_detail($indicatorID,$indicatorName,$type,$allRow)["success"];
                    if(!empty($otxBasicData["pulse_info"]["pulses"])){
                       $checkSuccess = $this->savePulseRef($otxBasicData["pulse_info"]["pulses"],$indicatorID,$urlLimit)["success"];
                    }
                } else {
                    $checkSuccess = false;
                }
            }
        } catch (Exception $e) {
            $checkSuccess = false;
        }

        $dataOut["success"] = $checkSuccess;
        return $dataOut;
    }


    public function saveIndicator_detail($indicatorID,$indicatorName,$type,$allRow)
    {
        try{
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


    public function savePulseRef($pulses, $indicatorID,$urlLimit)
    { 
        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $checkSuccess = true;
        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
        $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;

       
        //$col_fx_otx_events_event_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_event_ref;
        if(!empty($pulses)){
            foreach ($pulses as $value) {
                try{
                    //$url_1 = "https://otx.alienvault.com/otxapi/pulses/" . $value["id"];
                    //$reconCall = $this->reconnnect($url_1, $urlLimit);
                    //if ($reconCall["success"]) {
                        //$otxPulseDetail = json_decode($reconCall["result"], true);
                        //$groups = implode(', ', array_column(isset($otxPulseDetail["groups"])?$otxPulseDetail["groups"]:[] , 'name'));
                    //} else {
                    //    $checkSuccess = false;
                    //}
                    $references = implode(', ' , isset($value["references"])?$value["references"]:[]);
                    $tags = implode(', ' , isset($value["tags"])?$value["tags"]:[]);
                    $industries = implode(', ' , isset($value["industries"])?$value["industries"]:[]);
                    $malware_families = implode(', ', array_column(isset($value["malware_families"])?$value["malware_families"]:[] , 'display_name'));
                    
                    $update_fx_otx_events = $col_fx_otx_events->updateOne(
                        ['pulse_id' => isset($value["id"])?$value["id"]:""],
                        ['$set' => [
                            'name' => isset($value["name"])?$value["name"]:"",
                            'description' => isset($value["description"])?$value["description"]:"",
                            'modified' => isset($value["modified"])?$value["modified"]:"",
                            'created' => isset($value["created"])?$value["created"]:"",
                            'public' => isset($value["public"])?$value["public"]:"",
                            'TLP' => isset($value["TLP"])?$value["TLP"]:"",
                            'indicator_count' => isset($value["indicator_count"])?$value["indicator_count"]:"",
                            'is_modified' => isset($value["is_modified"])?$value["is_modified"]:"",
                            'indicator_type_counts' => isset($value["indicator_type_counts"])?$value["indicator_type_counts"]:"",
                            'references' => isset($references)?$references:"",
                            'tags' => isset($tags)?$tags:"",
                            'groups' => isset($groups)?$groups:"",
                            'industries' => isset($industries)?$industries:"",
                            'malware_families' => isset($malware_families)?$malware_families:"",
                            'author_username' => isset($value["author"]["username"])?$value["author"]["username"]:"",
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
                        ['indicator_id' => (isset($indicatorID)?$indicatorID:"") ,
                         'pulse_id' =>  (isset($value["id"])?$value["id"]:"")],
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

    public function all_code($url, $limit)
    {
        // try {
        //     $OTX_KEY = env("OTX_KEY", "");
        //     $client = new \GuzzleHttp\Client();
        //     $bodyData = $client->request(
        //         'GET',
        //         'https://otx.alienvault.com/api/v1/pulses/indicators/types',
        //         [
        //             'headers' => [
        //                 'Accept' => 'application/json',
        //                 'Content-type' => 'application/json',
        //                 'X-OTX-API-KEY' => $OTX_KEY,
        //             ],
        //             'delay' => 10000 , //millisec == 10sec
        //             'timeout' => 100 //sec == 100sec
        //         ]
        //     )->getBody();
        //     $otxFeedType = json_decode($bodyData, true);
        // } catch (Exception $e) {
        //     $error["Exception"] = $e;
        // }

        // if(isset($error["Exception"])){
        //     echo json_encode($error);
        // }else{
        //     echo  $bodyData;
        //     $this->info("Success Fully");
        // }

        // $client = new \MongoDB\Client("mongodb://localhost:27017");//Client
        // $collection = $client->myDBtest->out2;
        // $insertOneResult = $collection->insertOne([
        // 'user_id' => 1,
        // 'username' => 'admin',
        // 'email' => 'admin@example.com',
        // 'name' => 'Admin User',
        // ]);
        // $updateResult = $collectionStamp->updateOne(
        //     ['_id' => new \MongoDB\BSON\ObjectId("5fce10aff47d0000500048e2")],
        //     ['$set' => ['status' => 1]]
        // );
        //     $client = new \MongoDB\Client("mongodb://localhost:27017");//Client
        //     $collection = $client->myDBtest->out2;
        //     // // $collection->drop();
        //     // $updateResult = $collection->updateOne(
        //     //     ['user_id' => 7],
        //     //     ['$set' => [
        //     //         'username' => '23',],
        //     //     '$setOnInsert' => [
        //     //             'email' => '3admin@example.com',
        //     //             'name' => '23Admin User',
        //     //     ]
        //     //     ],
        //     //     ['upsert' => true]
        //     // );
        //     // echo json_encode($updateResult->getupsertedId());

        //     // echo json_encode($updateResult->getUpsertedCount());
        //     try{
        //     $insertOneResult = $collection->insertOne([
        //         'user_id' => 1002251131,
        //         'username' => 'admin1',
        //         'email' => 'admin@example.com',
        //         'name' => 'Admin User',
        //     ]);
        //     if(isset($insertOneResult)){
        //         echo "nice";
        //     }else{
        //         echo "noo";
        //     }
        // }catch (Exception $e) {
        //     $error["Exception"] = $e;

        // }
        // if(isset($insertOneResult)){
        //     $document = $collection->findOne(['_id' => $insertOneResult->getInsertedId()]);
        //     echo json_encode($document->_id->__toString());
        // }else{
        //     echo "noo";
        // }
        // $mng = new MongoDB\Driver\Manager("mongodb://localhost:27017");
        // $stats = new MongoDB\Driver\Query(["dbstats" => 1]);
        // $res = $mng->executeCommand("myDBtest", $stats);

        // $client = new \MongoDB\Driver\Manager("mongodb://localhost:27017");//Client
        // $bulkWrite=new \MongoDB\Driver\BulkWrite();
        // $doc=array(["dbstats" => 1,"dbstatssssss" => 1]);
        // $bulkWrite->insert($doc);
        // $client->executeBulkWrite('myDBtest.out', $bulkWrite);
        //     $data['filter'] = $this->request->filter;
        //     $data['page']   = $this->getPage();
        //    return view('sitesettings::index')->with($data);
        // $data = $this->reconnnect('https://otx.alienvault.com/api/v1/pulses/indicators/types',1000);
        //$data = $this->reconnnect('https://otx.alienvault.com/otxapi/indicators/?include_inactive=0&sort=-modified&q=modified:%3C1d&page=1&limit=100',1000);
        //echo json_encode($data);

        // $updateResult = $collectionStamp->updateOne(
        //     ['_id' => new \MongoDB\BSON\ObjectId("5fce10aff47d0000500048e2")],
        //     ['$set' => ['status' => 1]]
        // );
    }
}
