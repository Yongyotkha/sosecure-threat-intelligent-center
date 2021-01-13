<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Exception;
use GuzzleHttp\Client as HttpClient;
use DB;
class ClientTransferRSSNEWS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ClientTransferRSSNEWS';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FeedCompromisedServer';
    private $urlLimit = 3;
    private $site_id = 49;
    private $url = 'http://127.0.0.2/api/v1/client-transfer/rss_news';
    private $tbName = 'fx_r_s_s_news';
    /**
     * Create a new command instance.
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
        $ip = '127.0.0.1';
        $mac = 'abcd';
        $header ='header';
        $site = [
            'site_id' => $this->site_id,
        ];
        $dataEncode = encrypt_decrypt('encrypt', json_encode($site), $header,$ip,$mac);

        $database = '';
        if($dataEncode){
            $passBody = [
                'site_id' => $dataEncode
            ];
            $httpData = $this->reconnnect($this->url,$passBody,$this->urlLimit);
            if($httpData["success"]){
                $returnData = json_decode($httpData["result"],true);
                if($returnData["connect"]){
                    if(!empty($returnData["result"])){
                        $dataBase = DB::connection('dummyDatabase');
                        foreach ($returnData["result"] as $value) {
                            $dataBase = $dataBase->select('select * from '.$this->tbName.' WHERE center_id = "'.$value["news_id"].'" limit 1');
                            
                            if($value["transaction_mode"]=='insert'||$value["transaction_mode"]=='update'){
                                if(empty($dataBase)){
                                    // DB::insert('insert into users (id, name) values (?, ?)', [1, 'Dayle']);
                                    foreach ($value["get_Transaction_client_news"] as $key => $subValue) {
                                        
                                    }
                                }else{
                                    // DB::update('update users set votes = 100 where name = ?', ['John']);
                                    foreach ($value["get_Transaction_client_news"] as $key => $subValue) {
                                        
                                    }
                                }
                            }else if($value["transaction_mode"]=='delete'){
                                

                            }


                        }
                    }
                }
            }
           
        }

        // $users = DB::connection('dummyDatabase')->select('select * from fx_r_s_s_news WHERE code="1" limit 1');

        // if(!empty($users)){
        //     echo json_encode($users);
        // }else{
        //     echo json_encode("nodata");
        // }
        
    }

    public function reconnnect($url,$passBody, $limit)
    {
        $_OTX_KEY = env("OTX_KEY", "");
        $_clientHttp = new HttpClient;
        $_reconnect = 0;
        $_otxReconnect = true;
        $_dataOut["result"] = null;
        $_dataOut["success"] = false;
        $_sleeptime = rand(0,2000);
        while ($_otxReconnect && $_reconnect < $limit) {
            try {
                $_bodyData = $_clientHttp->request(
                    'POST',
                    $url,
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Content-type' => 'application/json',
                        ],
                        'delay' => $_sleeptime, //millisec == ms
                        'timeout' => 59, //sec == 100sec
                        'body' => json_encode($passBody)
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
