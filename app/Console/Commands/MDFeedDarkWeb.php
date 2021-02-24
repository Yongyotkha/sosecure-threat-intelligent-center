<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Modules\SiteSettings\Entities\SiteSettings;
use MongoDB\BSON\UTCDateTime;
use App\Entities\TransactionBatchjob;
use App\Menu;
use App\Menu_permission_site;
class MDFeedDarkWeb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:MDFeedDarkWeb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DarkWeb Feed';

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
        // $payload = [
        //     'domain' => 't.me',
        //     'q' => 't.me',
        // ];
        $SiteSettings = SiteSettings::where('active', '1')->whereNull('deleted_at')->where('system_site_online',1)->where('start_active', '<=', date("Y-m-d H:i:s"))->where('end_active', ">=", date("Y-m-d H:i:s"))->with('get_keywords_darkweb')->with('get_domains_default');




        $SiteSettings = $SiteSettings->get();
        // $time_stamp = Carbon::now('UTC')->addDays(1)->format('Y-m-d\\TH:i:s\\Z');
        // $time_stamp = Carbon::now('UTC')->subDays(1)->format('Y-m-d\\TH:i:s\\Z');

        foreach ($SiteSettings as $value) {
            try{
                // $Menu_permission_site_data =   Menu_permission_site::where('site_id',$value->id)->where(function ($query) {
                //     $query->where('menu_code', '=', '854a1e60-9abf-4263-a187-60aec8cd4fb1')
                //     ->orWhere('menu_code', '=', '79b362a5-3789-445a-bd6c-846393ffd19d');
                // })->get();
            // print_r($value);
    //           if (count($Menu_permission_site_data) > 0) {
    //             echo "ok";
                $response = $this->perform_query($value);
    //         }



            } catch (Exception $e) {
                echo "Fail handle : " . $e->getMessage();
            }
        }
        //use ($site_id)

        // $SiteSettings->whereHas('get_keywords', function ($query) {
        //     $query->where('type', 'darkweb');
        // });

        //echo(json_encode($SiteSettings->get()));
        ///$response = $this->perform_query($payload);

        $this->info('END------------------------------------------------------------END');
    }

    public function payloadToString($payload)
    {

        $search = '';
        $count = 0;
        foreach ($payload as $key => $value) {
            if ($count == 0) {
                $search .= '?' . $key . '=' . $value;
                $count++;
            } else {
                $search .= '&' . $key . '=' . $value;
            }
        }
        return $search;
    }

    public function querysToString($payload, $fromDate, $toDate)
    {
        $search = '';
        $count = 0;
        foreach ($payload as $value) {
            foreach ($value as $key => $value2) {
                if ($count == 0) {
                    $count = 1;
                    $search .= '?' . $key . '=' . $value2;
                } else {
                    $search .= '&' . $key . '=' . $value2;
                }
            }

        }
        $search .= '&from=' . $fromDate;
        $search .= '&to=' . $toDate;
        return $search;
    }

    public function addOffset($search, $offset)
    {
        $search = $search;
        $search .= '&offset=' . $offset;
        return $search;
    }

    public function mapTypeDomain($typeSearch,$domain,$ip){
        $payload = array();
        if ($typeSearch == 'email') {
            $payload[] = array('emailDomain' => $domain);
            $payload[] = array('emailDomain' => '*.' . $domain);
        } else if($typeSearch == 'ip') {
            if(isset($ip)){
                $payload[] = array($typeSearch => $ip);
            }else{
                $payload[] = array($typeSearch => "");
            }
        }else if($typeSearch == 'q') {
            if(isset($ip)){
                $payload[] = array($typeSearch =>  '"'.$domain.'" OR "'.$ip.'"');
            }else{
                $payload[] = array($typeSearch =>  '"'.$domain.'"');
            }
        }else {
            //q only search domain
            $payload[] = array($typeSearch => $domain);
        }
        $payload[] = array('count' => '20');
        $payload[] = array('sort' => 'd');
        return $payload;
    }

    public function perform_query($Site_payload)
    {
        // $publicKey = '+x4QtLeFMejTD6kYel4aYA==';
        // $privateKey = 'L57IL/Kt7PMZFMrZXNiSD5YFZrMSc6kQUmAu6/oS9Qk=';
        $DB_MONGO_KEY = env("DB_MONGO_STOREDATA", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $col_fx_transaction_darkweb_stamp = $clientMD->sosecure_threatintelligent->fx_transaction_darkweb_stamp;//*9000
        $reconnectLimit = 3;
        if (!empty($Site_payload["get_keywords_darkweb"]) > 0) {
            $keywords_list = array();
            foreach ($Site_payload["get_keywords_darkweb"] as $value) {
               // print_r($value["name"]);
                array_push($keywords_list, $value["name"]);
            }

            $keywords_string = implode (", ", $keywords_list);

            try {
                $payload = $this->mapTypeDomain('q',$Site_payload["get_domains_default"][0]["domain"],$Site_payload["get_domains_default"][0]["IP"]);


            //         // $payload = array();
            //         // if ($value["name"] == 'email') {
            //         //     $payload[] = array('emailDomain' => $Site_payload["get_domains_default"][0]["domain"]);
            //         //     $payload[] = array('emailDomain' => '*.' . $Site_payload["get_domains_default"][0]["domain"]);
            //         // } else {
            //         //     $payload[] = array($value["name"] => $Site_payload["get_domains_default"][0]["domain"]);
            //         // }
            //         // $payload[] = array('count' => '20');
            //         // $payload[] = array('sort' => 'd');


                $time_stamp_from = Carbon::now('UTC')->subDays(2)->format('Y-m-d\\TH:i:s\\Z');
               // $time_stamp_from = "2021-01-01T07:52:25Z";
                $time_stamp_to = Carbon::now('UTC')->addDays(1)->format('Y-m-d\\TH:i:s\\Z');
                $search = $this->querysToString($payload, $time_stamp_from, $time_stamp_to);
                print_r($search);
                $_clientHttp = $this->getInitialNumbers($search, 'GET', $reconnectLimit);
                $site_Data["site_type_search"] = $value["name"];
                $site_Data["site_id"] = $value["site_id"];
                $site_Data["site_name"] = $Site_payload["name"];
                $site_Data["site_domain"] = $Site_payload["get_domains_default"][0]["domain"];
                $site_Data["site_domain_name"] = $Site_payload["get_domains_default"][0]["name"];
                $site_Data["site_domain_ip"] = $Site_payload["get_domains_default"][0]["IP"];
                $getIDStamp = $this->createStamp($site_Data);
                $saveCheck = $_clientHttp["success"];
                echo $_clientHttp["total"] . " : IS All_DATA";
                $this->info(json_encode($site_Data));
                if($_clientHttp["total"]>0){
                    if ($_clientHttp["total"] <= 20) {
                            //echo json_encode($_clientHttp["alldata"]);
                        $this->saveDarkwebDetail_2($getIDStamp,$site_Data,$_clientHttp["alldata"]);
                            // $saveCheck = $this->saveDarkwebDetail($_clientHttp["alldata"],
                            // $value["name"], $value["site_id"],$Site_payload["name"], $Site_payload["get_domains_default"][0]["domain"], $Site_payload["get_domains_default"][0]["name"]
                            // );
                    } else {
                        $firstCrawlDate  = $_clientHttp["alldata"]["results"][0]["crawlDate"];
                        $this->saveDarkwebDetail_2($getIDStamp,$site_Data,$_clientHttp["alldata"]);
                        $saveCheck = $this->paginate($site_Data,$getIDStamp,
                            $_clientHttp["total"],$payload,$search, 'GET', $reconnectLimit,2,$firstCrawlDate
                        );
                    }
                }
                if ($saveCheck==true) {
                    $updateResult2 = $col_fx_transaction_darkweb_stamp->updateOne(
                        ['_id' => $getIDStamp],
                        ['$set' => ['status' => 2]]
                    );
                    $this->info("app:MDFeedDarkWeb SUCCESS");
                } else {
                    $this->info("app:MDFeedDarkWeb FAIL SOME CONTENT");
                }
            } catch (Exception $e) {
                echo "Fail Perform : " . $e->getMessage();
            }

       // }
        }

        $this->info('SUCCESS ONE SEARCH');

    }

    public function getInitialNumbers($search, $http_method, $reconnectLimit)
    {
        $authHeader_url = $this->generate_auth_header_URL($search, $http_method);
        $_clientHttp = $this->reconnnect($authHeader_url["header"], $authHeader_url["url"], $reconnectLimit);
        if ($_clientHttp["success"]) {
            //$this->info('PASS');
            $data_clientHttp = json_decode($_clientHttp["result"], true);
            $data["resultCount"] = $data_clientHttp["resultCount"];
            $data["total"] = $data_clientHttp["total"];
            $data["alldata"] = $data_clientHttp;
            $data["success"] = $_clientHttp["success"];
        } else {
            $this->info('Fail getData:' . $search);
            $this->info($_clientHttp["exception"]);
            $data["resultCount"] = 0;
            $data["total"] = 0;
            $data["alldata"] = null;
            $data["success"] = $_clientHttp["success"];
        }
        return $data;
    }

    public function createStamp($site_Data)
    {
        $DB_MONGO_KEY = env("DB_MONGO_STOREDATA", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $col_fx_transaction_darkweb_stamp = $clientMD->sosecure_threatintelligent->fx_transaction_darkweb_stamp;//*9000
        $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);
        $insertStamp = $col_fx_transaction_darkweb_stamp->insertOne([
            'code' => generator_uuid(),
            'transaction_site_type_search' => @$site_Data["site_type_search"],
            'transaction_site_id' => @$site_Data["site_id"],
            'transaction_site_name' => @$site_Data["site_name"],
            'transaction_site_domain' => @$site_Data["site_domain"],
            'transaction_site_domain_name' => @$site_Data["site_domain_name"],
            'transaction_date' => date("Y-m-d"),
            'status' => 1,
            'created_at' => $date_now,
            'created_by' => "system",
            'updated_at' => $date_now,
            'updated_by' => "system",
            'deleted_at' => null,
        ]);
        $get_InsertedId = $insertStamp->getInsertedId();
        return $get_InsertedId;
    }

    public function paginate($site_Data,$getIDStamp,$totalResults,$payload,$search, $http_method, $reconnectLimit,$masterPage,$firstCrawlDate)
    {
        $offset = 0;
        $lastCrawlDate = '';
        if($masterPage==2){
            $offset = 20;
        }

        $_clientHttp["success"] = true;
        while ($offset < 5000) {
            if($masterPage==2){ //-1*20 data and break;
                return $_clientHttp["success"];
            }
            $search_offset = $this->addOffset($search, $offset);
            $_clientHttp = $this->getInitialNumbers($search_offset, 'GET', $reconnectLimit);
            
            if($_clientHttp["resultCount"]< 1){
                $this->info("GET_OUT:".$search);
                return $_clientHttp["success"];
            }else if($_clientHttp["resultCount"]<20){
                $saveCheck = $this->saveDarkwebDetail_2($getIDStamp,$site_Data,$_clientHttp["alldata"]);
                return $_clientHttp["success"];
            }
            $this->saveDarkwebDetail_2($getIDStamp,$site_Data,$_clientHttp["alldata"]);
            
            $offset =  $offset + 20;
            $masterPage = $masterPage + 1;
            if($offset==5000) {
                $lastCrawlDate = $_clientHttp["alldata"]["results"][count($_clientHttp["alldata"]["results"])-1]["crawlDate"];
                if($firstCrawlDate==$lastCrawlDate){
                    return $_clientHttp["success"];
                }
            }
        }
        
        return $_clientHttp["success"];//comment and uncomment below line for save all data if get unlimit id user
        
        // $time_stamp_from = Carbon::now('UTC')->subDays(1)->format('Y-m-d\\TH:i:s\\Z');
        // $time_stamp_from = "0001-08-26T00:00:00Z";
        // $boolResult = $this->paginate($site_Data,$getIDStamp,
        // $totalResults,$payload,$this->querysToString($payload, $time_stamp_from, $lastCrawlDate), $http_method, $reconnectLimit,$masterPage,$firstCrawlDate
        // );
        // return $boolResult;
    }

    public function saveDarkwebDetail_2($getIDStamp,$site_Data,$all_data)
    {
        $DB_MONGO_KEY = env("DB_MONGO_STOREDATA", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $col_fx_transaction_darkweb_data = $clientMD->sosecure_threatintelligent->fx_transaction_darkweb_data;//*9000
        $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);
        $get_InsertedId = $getIDStamp;
        if (count($all_data["results"]) > 0) {
            foreach ($all_data["results"] as $value) {
                $implodeValue = preg_split("/\\r\\n|\\r|\\n/", $value["body"]);
                
                if($site_Data["site_type_search"] == "ip"){
                    $site_domain = $site_Data["site_domain_ip"];
                }else{
                    $site_domain = $site_Data["site_domain"];
                }
                $keyIndex = array_keys(array_filter($implodeValue, function($var) use ($site_domain){
                    return stripos($var, $site_domain) !== false;
                }));

                $body_search = array();
                if (!is_bool($keyIndex)) {
                    foreach ($keyIndex as $index_key) {
                        $body_search[] = $implodeValue[$index_key];
                    }
                }

                $site_domain = $site_Data["site_domain"];

                if(!empty($value["emails"])){
                    $keyIndex_2 = array_keys(array_filter($value["emails"], function($var) use ($site_domain){
                        return stripos($var, $site_domain) !== false;
                    }));
                }
                $emails_search = array();
                if (!empty($keyIndex_2)) {
                    foreach ($keyIndex_2 as $index_key) {
                        $emails_search[] = $value["emails"][$index_key];
                    }
                }



                $findUnique = $col_fx_transaction_darkweb_data->findOne(
                    [
                        'darkweb_id' => @$value["id"],
                        'transaction_site_id' => @$site_Data["site_id"],
                        'transaction_site_type_search' => @$site_Data["site_type_search"],
                        'transaction_site_domain' => @$site_Data["site_domain"]
                    ], 
                    [
                        'projection' => [
                            "_id" => 1
                        ]
                    ]
                );
                echo json_encode($findUnique);
                if (empty($findUnique)) {
                    $this->info(" : INSERTED");
                    $insert_col_fx_transaction_darkweb_data = $col_fx_transaction_darkweb_data->insertOne([
                        'darkweb_id' => @$value["id"],
                        'body_search' => @$body_search,
                            //'body' => @$value["body"],
                        'hackishness' => @$value["hackishness"],
                        'title' => @$value["title"],
                        'url' => @$value["url"],
                        'crawlDate' => @$value["crawlDate"],
                        'fileSize' => @$value["fileSize"],
                        'domain' => @$value["domain"],
                        'emails' => @$emails_search,
                        'headers' => @$value["headers"],
                        'transaction_site_type_search' => @$site_Data["site_type_search"],
                        'transaction_site_id' => @$site_Data["site_id"],
                        'transaction_site_name' => @$site_Data["site_name"],
                        'transaction_site_domain' => @$site_Data["site_domain"],
                        'transaction_site_domain_name' => @$site_Data["site_domain_name"],
                        'transaction_site_domain_ip' => @$site_Data["site_domain_ip"],
                        'updated_at' => $date_now,
                        'updated_by' => "system",
                        'transcation_id' => $get_InsertedId,
                        'status' => 1,
                        'created_at' => $date_now,
                        'created_by' => "system",
                        'deleted_at' => null,
                        'transaction_date' => date("Y-m-d"),
                        'count_view' => 0,
                    ]);
                }
                

            }
        }
        return 0;
    }


    public function generate_auth_header_URL($search, $http_method)
    {

        $public_key = env("DARKOWL_PUBLIC_KEY", "");
        $private_key = env("DARKOWL_PRIVATE_KEY", "");
        $host = 'api.darkowl.com';
        $endpoint = '/api/v1/search';
        $absPath = $endpoint . $search;
        $time_stamp = Carbon::now('UTC')->format('D\\, d M Y H:i:s \\G\\M\\T');

        $string2hash = $http_method . $absPath . $time_stamp;
        $bkey = utf8_encode($private_key);
        $bpayload = utf8_encode($string2hash);
        //$bkey = $private_key;
        //$bpayload = $string2hash;
        $hash = hash_hmac('sha1', $bpayload, $bkey, true);
        $base64encoded = utf8_decode(base64_encode($hash));

        $auth_header = 'OWL ' . $public_key . ':' . $base64encoded;
        $headers = [
            'Authorization' => $auth_header,
            'X-VISION-DATE' => $time_stamp,
            'Accept' => 'application/json',
        ];
        $data["header"] = $headers;
        $data["url"] = 'https://' . $host . $endpoint . $search;
        return $data;

    }

    public function reconnnect($header, $url, $limit)
    {
        $_clientHttp = new Client();
        $_reconnect = 0;
        $_otxReconnect = true;
        $_dataOut["result"] = "";
        $_dataOut["success"] = false;
        $_sleeptime = rand(0,2000); 
        while ($_otxReconnect && $_reconnect < $limit) {
            sleep(2);
            try {
                $_bodyData = $_clientHttp->request(
                    'GET',
                    $url,
                    [
                        'headers' => $header,
                        'delay' => $_sleeptime, //millisec == 1sec
                        'timeout' => 59, //sec == 100sec
                    ]
                )->getBody();
                $_dataOut["result"] = $_bodyData;
                $_dataOut["success"] = true;
                $_otxReconnect = false;
                //echo "  Pass : " . $_reconnect;
            } catch (Exception $e) {
                $_dataOut["exception"] = $e->getMessage();
                //echo "  Fail : " . $_reconnect;
            }
            $_reconnect++;
        }
        return $_dataOut;
    }
}
