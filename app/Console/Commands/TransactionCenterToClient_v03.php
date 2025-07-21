<?php

namespace App\Console\Commands;

use Exception;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Console\Command;

class TransactionCenterToClient_v03 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:TransactionCenterToClient_v03';
    protected $description = 'TransactionCenterToClient_v03';

    /**
     * The console command description.
     *
     * @var string
     */

    private $urlLimit = 3;
    private $urlCenterData = PATH_CENTER_IP_TF.'/api/v1/client-transfer/getTranferData'; //center ip path
    // private $url = 'http://127.0.0.2/api/v1/clientinto-transfer/insertToNoRef'; //my ip path
    private $url = PATH_MY_IP_TF.'/api/v1/clientinto-transfer/insertToNoRefWithID'; //my ip path
    private $ip = '127.0.0.1';
    private $mac = 'abcd';
    private $header = '';
    private $site_code = '';
    private $site_mode = '';
    private $insertToTB = 'fx_transaction_client_news_categories';
    private $urlUpdateBatchJob = PATH_CENTER_IP_TF.'/api/v1/centerinto-transfer/updateTFBatchJob';
    private $PATH_CENTER = '';
    private $PATH_CLIENT = '';
    /**
     * Create a new command instance.
     */
    
    public function __construct()
    {
        parent::__construct();
        $this->site_code = config('app.site_code');
        $this->site_mode = config('app.mode');
        $this->header = config('app.site_key');
        $this->urlCenterData = $this->urlCenterData.'?code='.$this->site_code;
        $this->ip =exec("hostname -I");
      //  $this->mac = exec("cat /sys/class/net/ens33/address");
        $this->mac = exec('cat /sys/class/net/eth0/address');
        $this->PATH_CENTER = config('app.PATH_CENTER');
        $this->PATH_CLIENT = config('app.PATH_CLIENT');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ip = $this->ip;
        $mac = $this->mac;
        $header = $this->header;


        $data_methed_funciton_list = [];



        $data_methed_funciton= array();
        $data_methed_funciton['PATH_CENTER_IP_TF_getTranferData'] = $this->PATH_CENTER.'/api/v1/client-transfer/getTranferData'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CLIENT_IP_TF_insertdata'] = $this->PATH_CLIENT.'/api/v1/clientinto-transfer/insertToNoRefWithID'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CENTER_IP_TF_updateTFBatchJob'] = $this->PATH_CENTER.'/api/v1/centerinto-transfer/updateTFBatchJob'.'?code='.$this->site_code;
        $data_methed_funciton['insertToTB'] ='fx_transaction_client_users';
        array_push($data_methed_funciton_list, $data_methed_funciton);

        $data_methed_funciton= array();
        $data_methed_funciton['PATH_CENTER_IP_TF_getTranferData'] = $this->PATH_CENTER.'/api/v1/client-transfer/getTranferData'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CLIENT_IP_TF_insertdata'] = $this->PATH_CLIENT.'/api/v1/clientinto-transfer/insertToNoRefWithID'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CENTER_IP_TF_updateTFBatchJob'] = $this->PATH_CENTER.'/api/v1/centerinto-transfer/updateTFBatchJob'.'?code='.$this->site_code;
        $data_methed_funciton['insertToTB'] ='fx_transaction_client_users_site';
        array_push($data_methed_funciton_list, $data_methed_funciton);


        $data_methed_funciton= array();
        $data_methed_funciton['PATH_CENTER_IP_TF_getTranferData'] = $this->PATH_CENTER.'/api/v1/client-transfer/getTranferData'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CLIENT_IP_TF_insertdata'] = $this->PATH_CLIENT.'/api/v1/clientinto-transfer/insertToNoRefWithID'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CENTER_IP_TF_updateTFBatchJob'] = $this->PATH_CENTER.'/api/v1/centerinto-transfer/updateTFBatchJob'.'?code='.$this->site_code;
        $data_methed_funciton['insertToTB'] ='fx_transaction_client_roles';
        array_push($data_methed_funciton_list, $data_methed_funciton);


        $data_methed_funciton= array();
        $data_methed_funciton['PATH_CENTER_IP_TF_getTranferData'] = $this->PATH_CENTER.'/api/v1/client-transfer/getTranferData'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CLIENT_IP_TF_insertdata'] = $this->PATH_CLIENT.'/api/v1/clientinto-transfer/insertToNoRefWithID'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CENTER_IP_TF_updateTFBatchJob'] = $this->PATH_CENTER.'/api/v1/centerinto-transfer/updateTFBatchJob'.'?code='.$this->site_code;
        $data_methed_funciton['insertToTB'] ='fx_transaction_client_permissions';
        array_push($data_methed_funciton_list, $data_methed_funciton);

        $data_methed_funciton= array();
        $data_methed_funciton['PATH_CENTER_IP_TF_getTranferData'] = $this->PATH_CENTER.'/api/v1/client-transfer/getTranferData'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CLIENT_IP_TF_insertdata'] = $this->PATH_CLIENT.'/api/v1/clientinto-transfer/insertToNoRefWithID'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CENTER_IP_TF_updateTFBatchJob'] = $this->PATH_CENTER.'/api/v1/centerinto-transfer/updateTFBatchJob'.'?code='.$this->site_code;
        $data_methed_funciton['insertToTB'] ='fx_transaction_client_role_permissions';
        array_push($data_methed_funciton_list, $data_methed_funciton);


        $data_methed_funciton= array();
        $data_methed_funciton['PATH_CENTER_IP_TF_getTranferData'] = $this->PATH_CENTER.'/api/v1/client-transfer/getTranferData'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CLIENT_IP_TF_insertdata'] = $this->PATH_CLIENT.'/api/v1/clientinto-transfer/insertToNoRefWithID'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CENTER_IP_TF_updateTFBatchJob'] = $this->PATH_CENTER.'/api/v1/centerinto-transfer/updateTFBatchJob'.'?code='.$this->site_code;
        $data_methed_funciton['insertToTB'] ='fx_transaction_client_model_has_roles';
        array_push($data_methed_funciton_list, $data_methed_funciton);




        $data_methed_funciton= array();
        $data_methed_funciton['PATH_CENTER_IP_TF_getTranferData'] = $this->PATH_CENTER.'/api/v1/client-transfer/getTranferData'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CLIENT_IP_TF_insertdata'] = $this->PATH_CLIENT.'/api/v1/clientinto-transfer/insertToNoRefWithID'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CENTER_IP_TF_updateTFBatchJob'] = $this->PATH_CENTER.'/api/v1/centerinto-transfer/updateTFBatchJob'.'?code='.$this->site_code;
        $data_methed_funciton['insertToTB'] ='fx_transaction_client_profiles';
        array_push($data_methed_funciton_list, $data_methed_funciton);





        $data_methed_funciton= array();
        $data_methed_funciton['PATH_CENTER_IP_TF_getTranferData'] = $this->PATH_CENTER.'/api/v1/client-transfer/getTranferData'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CLIENT_IP_TF_insertdata'] = $this->PATH_CLIENT.'/api/v1/clientinto-transfer/insertToNoRefWithID'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CENTER_IP_TF_updateTFBatchJob'] = $this->PATH_CENTER.'/api/v1/centerinto-transfer/updateTFBatchJob'.'?code='.$this->site_code;
        $data_methed_funciton['insertToTB'] ='fx_transaction_client_site';
        array_push($data_methed_funciton_list, $data_methed_funciton);




        $data_methed_funciton= array();
        $data_methed_funciton['PATH_CENTER_IP_TF_getTranferData'] = $this->PATH_CENTER.'/api/v1/client-transfer/getTranferData'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CLIENT_IP_TF_insertdata'] = $this->PATH_CLIENT.'/api/v1/clientinto-transfer/insertToNoRefWithID'.'?code='.$this->site_code;
        $data_methed_funciton['PATH_CENTER_IP_TF_updateTFBatchJob'] = $this->PATH_CENTER.'/api/v1/centerinto-transfer/updateTFBatchJob'.'?code='.$this->site_code;
        $data_methed_funciton['insertToTB'] ='fx_transaction_client_site_category';
        array_push($data_methed_funciton_list, $data_methed_funciton);

        foreach ($data_methed_funciton_list   as $key => $value) {


            echo "|".$value['insertToTB']."|";

            $passBody =  array(
                'site_code_en' =>$this->site_code,
                'tbName' =>$value['insertToTB'],
            );
            $dataEncode = encrypt_decrypt('encrypt', json_encode($passBody), $header, $ip, $mac);


            $passBody_send = [
             'data' =>$dataEncode
         ];


         $httpData = $this->reconnnect($value['PATH_CENTER_IP_TF_getTranferData'], $passBody_send, $this->urlLimit);

        //   print_r($httpData);


         if ($httpData["success"]) {
            $body_data = $httpData["result"]["queryData"];
            $dataDecode_data_return = encrypt_decrypt('decrypt', $httpData["result"]["queryData"], $header, $ip, $mac);
            $httpData_return = json_decode($dataDecode_data_return);
            $httpData["result"]["queryData"] =  $httpData_return;

            if (!empty($httpData["result"]["queryData"])) {
             $tableData = $body_data;
             $dataEncode = encrypt_decrypt('encrypt', $httpData["result"]["site_code_en"], $header, $ip, $mac);

             $dataEncode_queryData = encrypt_decrypt('encrypt', json_encode($httpData["result"]["queryData"]), $header, $ip, $mac);
             $passBody = [
                'site_code_en' => $dataEncode,
                'queryData' => $dataEncode_queryData,
                'tbName' =>$value['insertToTB'],
            ];
            //print_r($passBody);
            $httpDataRecon = $this->reconnnect($value['PATH_CLIENT_IP_TF_insertdata'], $passBody, $this->urlLimit);
            print_r($httpDataRecon);
         // echo json_encode($httpData);
            if ($httpDataRecon["success"]) {
                if($httpDataRecon["result"]["connect"]){
                    $passBody = [
                        'modeFor' => 'done',
                        'modeInsert' => $value['insertToTB'],
                        'nameBJ' => 'Transaction Client  - everyMinute()  Or Request',
                        'sitecode' => config('app.site_code'),
                    ];
                    $httpDataUpdate = $this->reconnnect($value['PATH_CENTER_IP_TF_updateTFBatchJob'], $passBody, $this->urlLimit);
                }
            } else {

            }

        } else {

        }
    }
}

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
