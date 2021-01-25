<?php

namespace App\Console\Commands;

use Exception;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Console\Command;

class TFClientTransfer_NEWS_Categories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:TFClientTransfer_NEWS_Categories';
    protected $description = 'TFClientTransfer_NEWS_Categories';

    /**
     * The console command description.
     *
     * @var string
     */

    // private $urlLimit = 3;
    // private $urlCenterData = PATH_CENTER_IP_TF.'/api/v1/client-transfer/getTranferData'; //center ip path
    // // private $url = 'http://127.0.0.2/api/v1/clientinto-transfer/insertToNoRef'; //my ip path
    // private $url = PATH_MY_IP_TF.'/api/v1/clientinto-transfer/insertToNoRefWithID'; //my ip path
    // private $ip = '127.0.0.1';
    // private $mac = 'abcd';
    // private $header = '';
    // private $site_code = '';
    // private $site_mode = '';
    // private $insertToTB = 'fx_transaction_client_news_categories';
    // private $urlUpdateBatchJob = PATH_CENTER_IP_TF.'/api/v1/centerinto-transfer/updateTFBatchJob';
    /**
     * Create a new command instance.
     */
    
    public function __construct()
    {
       parent::__construct();
        // $this->site_code = config('app.site_code');
        // $this->site_mode = config('app.mode');
        // $this->header = config('app.site_key');
        // $this->urlCenterData = $this->urlCenterData.'?code='.$this->site_code;
        // $this->ip =exec("hostname -I");
        // $this->mac = exec("cat /sys/class/net/ens33/address");
   }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo "123";
//         $ip = $this->ip;
//         $mac = $this->mac;
//         $header = $this->header;


//         $passBody =  array(
//             'site_code_en' =>$this->site_code,
//             'tbName' => $this->insertToTB,
//         );
//         $dataEncode = encrypt_decrypt('encrypt', json_encode($passBody), $header, $ip, $mac);


//         $passBody_send = [
//          'data' =>$dataEncode
//      ];

//      print_r($this->site_code);
//      $httpData = $this->reconnnect($this->urlCenterData, $passBody_send, $this->urlLimit);




//      if ($httpData["success"]) {
//         $dataDecode_data_return = encrypt_decrypt('decrypt', $httpData["result"]["queryData"], $header, $ip, $mac);
//         $httpData_return = json_decode($dataDecode_data_return);
//         $httpData["result"]["queryData"] =  $httpData_return;
//         print_r($httpData_return);
//         if (!empty($httpData["result"]["queryData"])) {



//          $tableData = $httpData["result"]["queryData"];
//          $dataEncode = encrypt_decrypt('encrypt', $httpData["result"]["site_code_en"], $header, $ip, $mac);
//          $passBody = [
//             'site_code_en' => $dataEncode,
//             'queryData' => $tableData,
//             'tbName' => $this->insertToTB,
//         ];

//         $httpDataRecon = $this->reconnnect($this->url, $passBody, $this->urlLimit);
//         print_r($httpDataRecon);
//                 // echo json_encode($httpData);
//         if ($httpDataRecon["success"]) {
//             if($httpDataRecon["result"]["connect"]){
//                 $passBody = [
//                     'modeFor' => 'done',
//                     'modeInsert' => 'fx_transaction_client_news_categories',
//                     'nameBJ' => 'Transaction Client client_news - everyMinute()  Or Request',
//                     'sitecode' => config('app.site_code'),
//                 ];
//                 $httpDataUpdate = $this->reconnnect($this->urlUpdateBatchJob, $passBody, $this->urlLimit);
//             }
//         } else {

//         }
//                 // if($httpData["success"]){
//                 //     $returnData = json_decode($httpData["result"],true);
//                 //     if($returnData["connect"]){
//                 //         if(!empty($returnData["result"])){
//                 //             $dataBase = DB::connection('dummyDatabase');
//                 //             foreach ($returnData["result"] as $value) {
//                 //                 $dataBase = $dataBase->select('select * from '.$this->tbName.' WHERE center_id = "'.$value["news_id"].'" limit 1');

//                 //                 if($value["transaction_mode"]=='insert'||$value["transaction_mode"]=='update'){
//                 //                     if(empty($dataBase)){
//                 //                         // DB::insert('insert into users (id, name) values (?, ?)', [1, 'Dayle']);
//                 //                         foreach ($value["get_Transaction_client_news"] as $key => $subValue) {

//                 //                         }
//                 //                     }else{
//                 //                         // DB::update('update users set votes = 100 where name = ?', ['John']);
//                 //                         foreach ($value["get_Transaction_client_news"] as $key => $subValue) {

//                 //                         }
//                 //                     }
//                 //                 }else if($value["transaction_mode"]=='delete'){

//                 //                 }

//                 //             }
//                 //         }
//                 //     }
//                 // }
//     } else {

//     }
// }

    }

    public function reconnnect($url, $passBody, $limit)
    {
        $_OTX_KEY = env("OTX_KEY", "");
        $_clientHttp = new HttpClient;
        $_reconnect = 0;
        $_otxReconnect = true;
        $_dataOut["result"] = null;
        $_dataOut["success"] = false;
        $_sleeptime = rand(0, 2000);
        while ($_otxReconnect && $_reconnect < $limit) {
            try {
                $_bodyData = $_clientHttp->request(
                    'POST',
                    $url,
                    [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Content-type' => 'application/json',
                            'Authorization' => 'Bearer '.$this->header,
                        ],
                        'delay' => $_sleeptime, //millisec == ms
                        'timeout' => 59, //sec == 100sec
                        'verify' => false,
                        'body' => json_encode($passBody),
                    ]
                )->getBody();
                $_dataOut["result"] = json_decode($_bodyData, true);
                $_dataOut["success"] = true;
                $_otxReconnect = false;
                //echo "  Pass : " . $_reconnect;
            } catch (Exception $e) {
                echo "  Fail : " . $e->getMessage();
            }
            $_reconnect++;
        }
        return $_dataOut;
    }

}
