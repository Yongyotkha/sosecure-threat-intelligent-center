<?php

namespace App\Console\Commands;

use Exception;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Console\Command;

class TFClientTransfer_client_webdefacment_data_check extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:TFClientTransfer_client_webdefacment_data_check';
    protected $description = 'TFClientTransfer_client_webdefacment_data_check';

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
    private $header = 'header';
    private $site_code = '';
    private $site_mode = '';
    private $insertToTB = 'fx_transaction_client_webdefacment_data_check';
    /**
     * Create a new command instance.
     */
    
    public function __construct()
    {
        parent::__construct();
        $this->site_code = config('app.site_code');
        $this->site_mode = config('app.mode');
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

        $dataEncode = encrypt_decrypt('encrypt', $this->site_code, $header, $ip, $mac);
        $passBody = [
            'site_code_en' => $dataEncode,
            'tbName' => $this->insertToTB,
        ];


        $httpData = $this->reconnnect($this->urlCenterData, $passBody, $this->urlLimit);
        // print_r($tableData);

        if ($httpData["success"]) {
            if (!empty($httpData["result"]["queryData"])) {
                
                $tableData = $httpData["result"]["queryData"];
                $dataEncode = encrypt_decrypt('encrypt', $httpData["result"]["site_code_en"], $header, $ip, $mac);
                $passBody = [
                    'site_code_en' => $dataEncode,
                    'queryData' => $tableData,
                    'tbName' => $this->insertToTB,
                ];

                $httpDataRecon = $this->reconnnect($this->url, $passBody, $this->urlLimit);
                print_r($httpDataRecon);
                // echo json_encode($httpData);
                if ($httpDataRecon["success"]) {

                } else {

                }
            } else {

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
