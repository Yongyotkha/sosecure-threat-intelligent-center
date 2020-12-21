<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Modules\SiteSettings\Entities\SiteSettings;
use MongoDB\BSON\UTCDateTime;

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
        $payload = [
            'domain' => 't.me',
            'q' => 't.me',
        ];
        $SiteSettings = SiteSettings::where('active', '1')->whereNull('deleted_at')->with('get_keywords_darkweb')->with('get_domains_default');
        $SiteSettings = $SiteSettings->get();
        foreach ($SiteSettings as $value) {
            $response = $this->perform_query($value);
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
            //echo json_encode($value);
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

    public function perform_query($Site_payload)
    {
        // $publicKey = '+x4QtLeFMejTD6kYel4aYA==';
        // $privateKey = 'L57IL/Kt7PMZFMrZXNiSD5YFZrMSc6kQUmAu6/oS9Qk=';
        //$search = $this->querysToString($payload,'d',500);
        $reconnectLimit = 3;
        if (!empty($Site_payload["get_keywords_darkweb"]) > 0) {
            foreach ($Site_payload["get_keywords_darkweb"] as $value) {

                try {
                    $payload = array();
                    if ($value["name"] == 'email') {
                        $payload[] = array('emailDomain' => $Site_payload["get_domains_default"][0]["domain"]);
                        $payload[] = array('emailDomain' => '*.' . $Site_payload["get_domains_default"][0]["domain"]);
                    } else {
                        $payload[] = array($value["name"] => $Site_payload["get_domains_default"][0]["domain"]);
                    }
                    $payload[] = array('count' => '20');
                    $payload[] = array('sort' => 'd');
                    $search = $this->querysToString($payload, '0000-12-18T00:00:00Z', '3000-01-01T00:00:00Z');
                    $_clientHttp = $this->getInitialNumbers($search, 'GET', $reconnectLimit);
                    echo $_clientHttp["total"] . "";
                    if ($_clientHttp["total"] <= 20) {
                        //echo json_encode($_clientHttp["alldata"]);
                        $saveCheck = $this->saveDarkwebDetail($_clientHttp["alldata"],
                        $value["name"], $value["site_id"],$Site_payload["name"], $Site_payload["get_domains_default"][0]["domain"], $Site_payload["get_domains_default"][0]["name"]
                        );
                    } else {

                    }
                } catch (Exception $e) {
                    echo "  Fail : " . $e->getMessage();
                }

            }
        }

        $this->info('^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^Update check completed');

    }

    public function getInitialNumbers($search, $http_method, $reconnectLimit)
    {
        //offset 0
        $authHeader_url = $this->generate_auth_header_URL($search, $http_method);
        $_clientHttp = $this->reconnnect($authHeader_url["header"], $authHeader_url["url"], $reconnectLimit);
        if ($_clientHttp["success"]) {
            $this->info('PASS');
            $data_clientHttp = json_decode($_clientHttp["result"], true);
            $data["total"] = $data_clientHttp["total"];
            $data["alldata"] = $data_clientHttp;
        } else {
            $this->info('ERROR' . $_clientHttp["exception"]);
            $data["total"] = 0;
            $data["alldata"] = null;
        }
        return $data;
    }

    public function saveDarkwebDetail($all_data, $site_type_search, $site_id, $site_name, $site_domain, $site_domain_name)
    {
        //offset 0
        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        $col_fx_transaction_darkweb_data = $clientMD->sosecure_threatintelligent->fx_transaction_darkweb_data_nodata;
        $col_fx_transaction_darkweb_stamp = $clientMD->sosecure_threatintelligent->fx_transaction_darkweb_stamp;
        $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s")) * 1000);
        $insertStamp = $col_fx_transaction_darkweb_stamp->insertOne([
            'code' => generator_uuid(),
            'transaction_site_type_search' => @$site_type_search,
            'transaction_site_id' => @$site_id,
            'transaction_site_name' => @$site_name,
            'transaction_site_domain' => @$site_domain,
            'transaction_site_domain_name' => @$site_domain_name,
            'transaction_date' => date("Y-m-d"),
            'status' => 1,
            'created_at' => $date_now,
            'created_by' => "system",
            'updated_at' => $date_now,
            'updated_by' => "system",
            'deleted_at' => null,
        ]);
        $get_InsertedId = $insertStamp->getInsertedId();
        if (count($all_data["results"]) > 0) {
            foreach ($all_data["results"] as $value) {
               
                //$implodeValue = explode("\n", $value["body"]);
                $implodeValue = preg_split("/\\r\\n|\\r|\\n/", $value["body"]);
                $keyIndex = array_keys(array_filter($implodeValue, function($var) use ($site_domain){
                    return stripos($var, $site_domain) !== false;
                }));
                if (!is_bool($keyIndex)) {
                    $body_search = array();
                    foreach ($keyIndex as $index_key) {
                        $body_search[] = $implodeValue[$index_key];
                    }
                   
                    $findUnique = $col_fx_transaction_darkweb_data->findOne(
                        [
                            'darkweb_id' => @$value["id"]
                        ]
                    );
                    if (empty($findUnique)) {
                        $insert_col_fx_transaction_darkweb_data = $col_fx_transaction_darkweb_data->insertOne([
                            'darkweb_id' => $value["id"],
                            'body_search' => @$body_search,
                            'site_get' =>  $site_id,
                            //'body' => @$value["body"],
                            'hackishness' => @$value["hackishness"],
                            'title' => @$value["title"],
                            'url' => @$value["url"],
                            'crawlDate' => @$value["crawlDate"],
                            'fileSize' => @$value["fileSize"],
                            'domain' => @$value["domain"],
                            'emails' => @$value["emails"],
                            'headers' => @$value["headers"],
                            'transaction_site_type_search' => @$site_type_search,
                            'transaction_site_id' => @$site_id,
                            'transaction_site_name' => @$site_name,
                            'transaction_site_domain' => @$site_domain,
                            'transaction_site_domain_name' => @$site_domain_name,
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
        }

        return $get_InsertedId;
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
        while ($_otxReconnect && $_reconnect < $limit) {
            try {
                $_bodyData = $_clientHttp->request(
                    'GET',
                    $url,
                    [
                        'headers' => $header,
                        'delay' => 200, //millisec == 1sec
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
