<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Modules\RSSFeedSettings\Entities\RSS;
use Modules\RSSFeedSettings\Entities\TransactionRssData;
use Carbon\Carbon;
use GuzzleHttp\Client;

class FeedDarkWeb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:FeedDarkWeb';

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
	    //'q' => 't.me'

        ];
        $response = $this->perform_query($payload);

       
        $this->info('Update check completed');
    }
    
    public function payloadToString($payload){

        $search = '';
        $count = 0;
        foreach ($payload as $key => $value) {
            if($count==0){
                $search .= '?'.$key.'='.$value;
                $count++;
            }else{
                $search .= '&'.$key.'='.$value;
            }
        }
        return $search;
    }


    public function perform_query($payload){
        $publicKey = '+x4QtLeFMejTD6kYel4aYA==';
        $privateKey = 'L57IL/Kt7PMZFMrZXNiSD5YFZrMSc6kQUmAu6/oS9Qk=';
        $host = 'api.darkowl.com';
        $endpoint = '/api/v1/search';
        $search = $this->payloadToString($payload);
        $absPath = $endpoint . $search;
        $date =   Carbon::now('UTC')->format('D\\, d M Y h:i:s \\G\\M\\T');
        $auth = $this->generate_auth_header($absPath, 'GET', $privateKey, $publicKey, $date);

        $reconnectLimit = 3;
        $url = 'https://'.$host.$endpoint.$search;
        $headers = [
            'Authorization' => $auth,
            'X-VISION-DATE' => $date,
            'Accept' => 'application/json'
        ];
        $_clientHttp = $this->reconnnect($url,$headers,$reconnectLimit);
        $result = json_decode($_clientHttp["result"]);

        echo json_encode($_clientHttp);


    }

    public function generate_auth_header($abs_path, $http_method, $private_key, $public_key, $time_stamp){
        $string2hash = $http_method . $abs_path . $time_stamp;
        //$bkey = utf8_encode ($private_key);
        //$bpayload = utf8_encode ($string2hash);
        $bkey = $private_key;
        $bpayload = $string2hash;
        $hash = hash_hmac('sha1', $bpayload, $bkey,true);
        $base64encoded = utf8_decode(base64_encode($hash));
        $auth_header = 'OWL '.$public_key.':'.$base64encoded;
        return $auth_header;

    }


    public function reconnnect($url,$header,$limit)
    {
 	echo json_encode($url);
	
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
                //echo "  Fail : " . $_reconnect;
            }
            $_reconnect++;
        }
        return $_dataOut;
    }
}
