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
        //echo json_encode($this->caseByType("domain","strtbiz.site","2701613156",$urlLimit));
        //echo json_encode($this->caseByType("email","ganaolian0439@163.com","2735305400",$urlLimit));
        
        //echo json_encode($this->caseByType("FileHash-MD5","f1b6ed2624583c913392dcd7e3ea6ae1","1",$urlLimit));
        //echo json_encode($this->caseByType("FileHash-MD5","277c10ae03a3921e32a583433bf9da1b","2",$urlLimit));
        //echo json_encode($this->caseByType("FileHash-MD5","81232f4c5c7810939b3486fa78d666c2","2",$urlLimit));
        //echo json_encode($this->caseByType("hostname","sdvsrgter.gb.net","2",$urlLimit));
        //echo json_encode($this->caseByType("IPv4","91.62.197.13","2",$urlLimit));
        // echo json_encode($this->caseByType("IPv6","2604:a880:0:1010::b:4001","2",$urlLimit));
        // echo json_encode($this->caseByType("NIDS","2808228","2",$urlLimit));

        // echo json_encode($this->caseByType("URL","http%3A%252F%252Fwww.pooya.novin52.com%252F","2",$urlLimit));
        //echo json_encode($this->caseByType("YARA","e0f74136e9edcb8b4c67274fb5c3f5885270de33","2",$urlLimit));
        $this->testfun();
    }

    public function testfun(){
        $dayMoreThan = 29;
        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $collectionBasic = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;
        $document = "5555";
        $options = [];
        $document = $collectionBasic->findOne(['indicator_id' => 1679044], [
           
            'projection' => [
                "updated_at" => 1,
            ]]);
           // $document->_id->__toString()
        //echo json_encode(date("Y-m-d H:i:s", $document->updated_at->__toString()));
       // echo json_encode($document->updated_at->toDateTime()['date']);
       //echo json_encode($document->updated_at->__toString()/1000);
        if(empty($document)){
            
            // $date1 = $document->updated_at->toDateTime();
            $date1 = date_create("2018-02-10T23:01:05.367000"); 
            $date2 = date_create(date("Y-m-d H:i:s"));  
          
            // $date1 = date_create("2020-12-14T03:16:54");
            echo json_encode($date1);
            // $date2 = date_create(gmdate("c"));
            echo json_encode($date2);
            // $date3 = date_create(date("Y-m-d H:i:s"));
            // echo json_encode($date3);
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
                $allRow = (object) array();
                $checkSuccess = $this->saveIndicator_detail($indicatorID,$indicatorName,$type,$allRow)["success"];

            } else if ($type == "CVE") {
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
            } else if ($type == "domain") {
                $url_1 = "https://otx.alienvault.com/otxapi/indicators/domain/general/" . $indicatorName;
                $url_2 = "https://otx.alienvault.com/otxapi/indicators/domain/url_list/" . $indicatorName;
                $reconCall_1 = $this->reconnnect($url_1, $urlLimit);
                $reconCall_2 = $this->reconnnect($url_2, $urlLimit);
                if ($reconCall_2["success"]) {
                    $otxBasicData_1 = json_decode($reconCall_1["result"], true);
                    $otxBasicData_2 = json_decode($reconCall_2["result"], true);
                    $allRow = (object) array(
                    "IP ADDRESS" => isset($otxBasicData_2["url_list"][0]["result"]["urlworker"]["ip"])?$otxBasicData_2["url_list"][0]["result"]["urlworker"]["ip"]:""
                    );
                    $checkSuccess = $this->saveIndicator_detail($indicatorID,$indicatorName,$type,$allRow)["success"];
                    if(!empty($otxBasicData_1["pulse_info"]["pulses"])){
                       $checkSuccess = $this->savePulseRef($otxBasicData_1["pulse_info"]["pulses"],$indicatorID,$urlLimit)["success"];
                    }
                } else {
                    $checkSuccess = false;
                }
            } else if ($type == "email") {
                $url_1 = "https://otx.alienvault.com/otxapi/indicators/email/general/" . $indicatorName;
                $reconCall_1 = $this->reconnnect($url_1, $urlLimit);
                if ($reconCall_1["success"]) {
                    $otxBasicData_1 = json_decode($reconCall_1["result"], true);

                    $allRow = (object) array();

                    $checkSuccess = $this->saveIndicator_detail($indicatorID,$indicatorName,$type,$allRow)["success"];

                    if(!empty($otxBasicData_1["pulse_info"]["pulses"])){
                       $checkSuccess = $this->savePulseRef($otxBasicData_1["pulse_info"]["pulses"],$indicatorID,$urlLimit)["success"];
                    }
                } else {
                    $checkSuccess = false;
                }
            } else if ($type == "FileHash-MD5" ||$type == "FileHash-SHA1" ||$type == "FileHash-SHA256") {
                $url_1 = "https://otx.alienvault.com/otxapi/indicator/file/general/" . $indicatorName;
                $reconCall_1 = $this->reconnnect($url_1, $urlLimit);

                $url_2 = "https://otx.alienvault.com/otxapi/indicator/file/analysis/" . $indicatorName;
                $reconCall_2 = $this->reconnnect($url_2, $urlLimit);

                if ($reconCall_2["success"]) {
                    $otxBasicData_1 = json_decode($reconCall_1["result"], true);
                    $otxBasicData_2 = json_decode($reconCall_2["result"], true);

                    if($otxBasicData_2["analysis"] != null){
                        $External_Hosts = implode(', ', array_column(isset($otxBasicData_2["analysis"]["plugins"]["cuckoo"]["result"]["network"]["tcp"])?$otxBasicData_2["analysis"]["plugins"]["cuckoo"]["result"]["network"]["tcp"]:[] , 'dst'));
                        $External_Domains = implode(', ', array_column(isset($otxBasicData_2["analysis"]["plugins"]["cuckoo"]["result"]["network"]["domains"])?$otxBasicData_2["analysis"]["plugins"]["cuckoo"]["result"]["network"]["domains"]:[] , 'domain'));
                        $File_Type = (!empty($otxBasicData_2["analysis"]["info"]["results"]["file_class"])?$otxBasicData_2["analysis"]["info"]["results"]["file_class"]." - ":"").(isset($otxBasicData_2["analysis"]["info"]["results"]["file_type"])?$otxBasicData_2["analysis"]["info"]["results"]["file_type"]:"");
                        $Antivirus_Detection = isset($otxBasicData_2["analysis"]["plugins"]["msdefender"]["results"]["detection"])?$otxBasicData_2["analysis"]["plugins"]["msdefender"]["results"]["detection"]:"";
                        if($Antivirus_Detection=="")
                            $Antivirus_Detection = isset($otxBasicData_2["analysis"]["plugins"]["avast"]["results"]["detection"])?$otxBasicData_2["analysis"]["plugins"]["avast"]["results"]["detection"]:"";
                        $allRow = (object) array(
                            "Analysis Date" => isset($otxBasicData_2["analysis"]["datetime_int"])?$otxBasicData_2["analysis"]["datetime_int"]:"",
                            "Score" => isset($otxBasicData_2["analysis"]["plugins"]["cuckoo"]["result"]["info"]["combined_score"])?$otxBasicData_2["analysis"]["plugins"]["cuckoo"]["result"]["info"]["combined_score"]:"",
                            "Antivirus Detection" => $Antivirus_Detection,
                            "External Hosts" => $External_Hosts,
                            "External Domains" =>  $External_Domains,
                            "File Type" => $File_Type,
                            "Size" => isset($otxBasicData_2["analysis"]["info"]["results"]["filesize"])?$otxBasicData_2["analysis"]["info"]["results"]["filesize"]:"",
                            "MD5" => isset($otxBasicData_2["analysis"]["info"]["results"]["md5"])?$otxBasicData_2["analysis"]["info"]["results"]["md5"]:"",
                            "SHA1" => isset($otxBasicData_2["analysis"]["info"]["results"]["sha1"])?$otxBasicData_2["analysis"]["info"]["results"]["sha1"]:"",
                            "SHA256" => isset($otxBasicData_2["analysis"]["info"]["results"]["sha256"])?$otxBasicData_2["analysis"]["info"]["results"]["sha256"]:"",
                            "IMPHASH" => isset($otxBasicData_2["analysis"]["plugins"]["pe32info"]["results"]["imphash"])?$otxBasicData_2["analysis"]["plugins"]["pe32info"]["results"]["imphash"]:"",
                            "PEHASH" => isset($otxBasicData_2["analysis"]["plugins"]["pe32info"]["results"]["pehash"])?$otxBasicData_2["analysis"]["plugins"]["pe32info"]["results"]["pehash"]:"",
                            "RichHash" => isset($otxBasicData_2["analysis"]["plugins"]["pe32info"]["results"]["richhash"])?$otxBasicData_2["analysis"]["plugins"]["pe32info"]["results"]["richhash"]:""
                        );
                    }else{
                        $allRow = (object) array();
                    }
                   
                    $checkSuccess = $this->saveIndicator_detail($indicatorID,$indicatorName,$type,$allRow)["success"];
                    if(!empty($otxBasicData_1["pulse_info"]["pulses"])){
                       $checkSuccess = $this->savePulseRef($otxBasicData_1["pulse_info"]["pulses"],$indicatorID,$urlLimit)["success"];
                    }
                } else {
                    $checkSuccess = false;
                }
            } else if ($type == "hostname") {
                $url_1 = "https://otx.alienvault.com/otxapi/indicator/hostname/general/" . $indicatorName;
                $url_2 = "https://otx.alienvault.com/otxapi/indicator/hostname/url_list/" . $indicatorName;
                $reconCall_1 = $this->reconnnect($url_1, $urlLimit);
                $reconCall_2 = $this->reconnnect($url_2, $urlLimit);
                if ($reconCall_2["success"]) {
                    $otxBasicData_1 = json_decode($reconCall_1["result"], true);
                    $otxBasicData_2 = json_decode($reconCall_2["result"], true);
                    $allRow = (object) array(
                    "IP ADDRESS" => isset($otxBasicData_2["url_list"][0]["result"]["urlworker"]["ip"])?$otxBasicData_2["url_list"][0]["result"]["urlworker"]["ip"]:"",
                    "DOMAIN" => isset($otxBasicData_2["url_list"][0]["domain"])?$otxBasicData_2["url_list"][0]["domain"]:""
                    );
                    $checkSuccess = $this->saveIndicator_detail($indicatorID,$indicatorName,$type,$allRow)["success"];
                    if(!empty($otxBasicData_1["pulse_info"]["pulses"])){
                       $checkSuccess = $this->savePulseRef($otxBasicData_1["pulse_info"]["pulses"],$indicatorID,$urlLimit)["success"];
                    }
                } else {
                    $checkSuccess = false;
                }
            } else if ($type == "IPv4") {
                $url_1 = "https://otx.alienvault.com/otxapi/indicator/IPv4/general/" . $indicatorName;
                $url_2 = "https://otx.alienvault.com/otxapi/indicator/IPv4/geo/" . $indicatorName;
                $reconCall_1 = $this->reconnnect($url_1, $urlLimit);
                $reconCall_2 = $this->reconnnect($url_2, $urlLimit);
                if ($reconCall_2["success"]) {
                    $otxBasicData_1 = json_decode($reconCall_1["result"], true);
                    $otxBasicData_2 = json_decode($reconCall_2["result"], true);
                    $LOCATION =  (isset($otxBasicData_2["city"])?$otxBasicData_2["city"].", ":"").
                    (isset($otxBasicData_2["country_name"])?$otxBasicData_2["country_name"]:"").
                    (isset($otxBasicData_2["country_code"])?" --".$otxBasicData_2["country_code"]:"");
                    $allRow = (object) array(
                    "LOCATION" => $LOCATION,
                    "ASN/OWNER" => isset($otxBasicData_2["asn"])?$otxBasicData_2["asn"]:""
                    );
                    $checkSuccess = $this->saveIndicator_detail($indicatorID,$indicatorName,$type,$allRow)["success"];
                    if(!empty($otxBasicData_1["pulse_info"]["pulses"])){
                       $checkSuccess = $this->savePulseRef($otxBasicData_1["pulse_info"]["pulses"],$indicatorID,$urlLimit)["success"];
                    }
                } else {
                    $checkSuccess = false;
                }
            } else if ($type == "IPv6") {
                $url_1 = "https://otx.alienvault.com/otxapi/indicator/IPv6/general/" . $indicatorName;
                $url_2 = "https://otx.alienvault.com/otxapi/indicator/IPv6/geo/" . $indicatorName;
                $reconCall_1 = $this->reconnnect($url_1, $urlLimit);
                $reconCall_2 = $this->reconnnect($url_2, $urlLimit);
                if ($reconCall_2["success"]) {
                    $otxBasicData_1 = json_decode($reconCall_1["result"], true);
                    $otxBasicData_2 = json_decode($reconCall_2["result"], true);
                    $LOCATION =  (isset($otxBasicData_2["city"])?$otxBasicData_2["city"].", ":"").
                    (isset($otxBasicData_2["country_name"])?$otxBasicData_2["country_name"]:"").
                    (isset($otxBasicData_2["country_code"])?" --".$otxBasicData_2["country_code"]:"");
                    $allRow = (object) array(
                    "LOCATION" => $LOCATION,
                    "ASN/OWNER" => isset($otxBasicData_2["asn"])?$otxBasicData_2["asn"]:""
                    );
                    $checkSuccess = $this->saveIndicator_detail($indicatorID,$indicatorName,$type,$allRow)["success"];
                    if(!empty($otxBasicData_1["pulse_info"]["pulses"])){
                       $checkSuccess = $this->savePulseRef($otxBasicData_1["pulse_info"]["pulses"],$indicatorID,$urlLimit)["success"];
                    }
                } else {
                    $checkSuccess = false;
                }
            } else if ($type == "NIDS") {
                $url_1 = "https://otx.alienvault.com/otxapi/indicator/nids/general/" . $indicatorName;
                $reconCall_1 = $this->reconnnect($url_1, $urlLimit);
                if ($reconCall_1["success"]) {
                    $otxBasicData_1 = json_decode($reconCall_1["result"], true);

                    $description = isset($otxBasicData_1["base_indicator"]["description"])?$otxBasicData_1["base_indicator"]["description"]:"";
                    $allRow = (object) array(
                        "CATEGORY" => isset($otxBasicData_1["category"])?$otxBasicData_1["category"]:"",
                        "SUBCATION" => isset($otxBasicData_1["subcategory"])?$otxBasicData_1["subcategory"]:"",
                        "ACTIVITY" => isset($otxBasicData_1["event_activity"])?$otxBasicData_1["event_activity"]:"",
                        "MALWARE NAME" => isset($otxBasicData_1["malware_name"])?$otxBasicData_1["malware_name"]:""
                    );
                    $checkSuccess = $this->saveIndicator_detail_description($indicatorID,$indicatorName,$type,$allRow,$description,'rowDescription')["success"];

                    if(!empty($otxBasicData_1["pulse_info"]["pulses"])){
                       $checkSuccess = $this->savePulseRef($otxBasicData_1["pulse_info"]["pulses"],$indicatorID,$urlLimit)["success"];
                    }
                } else {
                    $checkSuccess = false;
                }
            } else if ($type == "URL") {
                $url_1 = "https://otx.alienvault.com/otxapi/indicator/url/general/" . $indicatorName;
                $url_2 = "https://otx.alienvault.com/otxapi/indicator/url/url_list/" . $indicatorName . "?limit=10&page=1";
                $reconCall_1 = $this->reconnnect($url_1, $urlLimit);
                $reconCall_2 = $this->reconnnect($url_2, $urlLimit);
                if ($reconCall_2["success"]) {
                    $otxBasicData_1 = json_decode($reconCall_1["result"], true);
                    $otxBasicData_2 = json_decode($reconCall_2["result"], true);

                    $LOCATION =  (isset($otxBasicData_2["city"])?$otxBasicData_2["city"].", ":"").
                    (isset($otxBasicData_2["country_name"])?$otxBasicData_2["country_name"]:"").
                    (isset($otxBasicData_2["country_code"])?" --".$otxBasicData_2["country_code"]:"");

                    $allRow = (object) array(
                    "IP ADDRESS" => isset($otxBasicData_2["url_list"][0]["result"]["urlworker"]["ip"])?$otxBasicData_2["url_list"][0]["result"]["urlworker"]["ip"]:"",
                    "LOCATION" => $LOCATION,
                    "HOSTNAME" => isset($otxBasicData_1["hostname"])?$otxBasicData_1["hostname"]:"",
                    "DOMAIN" => isset($otxBasicData_1["domain"])?$otxBasicData_1["domain"]:"",
                    "LAST ANALYZED DATE" => isset($otxBasicData_2["url_list"][0]["date"])?$otxBasicData_2["url_list"][0]["date"]:""
                    );
                    $checkSuccess = $this->saveIndicator_detail($indicatorID,$indicatorName,$type,$allRow)["success"];

                    if(!empty($otxBasicData_1["pulse_info"]["pulses"])){
                       $checkSuccess = $this->savePulseRef($otxBasicData_1["pulse_info"]["pulses"],$indicatorID,$urlLimit)["success"];
                    }
                } else {
                    $checkSuccess = false;
                }
            } else if ($type == "YARA") {
                $url_1 = "https://otx.alienvault.com/otxapi/indicator/yara/general/" . $indicatorName;
                $url_2 = "https://otx.alienvault.com/otxapi/indicator/yara/raw/" . $indicatorName;
                $reconCall_1 = $this->reconnnect($url_1, $urlLimit);
                $reconCall_2 = $this->reconnnect($url_2, $urlLimit);
                if ($reconCall_2["success"]) {
                    $otxBasicData_1 = json_decode($reconCall_1["result"], true);
                    $otxBasicData_2 = (string)$reconCall_2["result"];
                    $allRow = (object) array();
                    $checkSuccess = $this->saveIndicator_detail_description($indicatorID,$indicatorName,$type,$allRow,$otxBasicData_2,'ruleRow')["success"];
                    if(!empty($otxBasicData_1["pulse_info"]["pulses"])){
                       $checkSuccess = $this->savePulseRef($otxBasicData_1["pulse_info"]["pulses"],$indicatorID,$urlLimit)["success"];
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

    public function saveIndicator_detail_description($indicatorID,$indicatorName,$type,$allRow,$description,$nameDescription)
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
                    $nameDescription => $description,
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
