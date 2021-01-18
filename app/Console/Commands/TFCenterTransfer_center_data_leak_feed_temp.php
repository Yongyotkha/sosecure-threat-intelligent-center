<?php

namespace App\Console\Commands;

use App\Entities\Transaction_center_data_leak_feed_temp as SubTB;
use Exception;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Console\Command;

class TFCenterTransfer_center_data_leak_feed_temp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:TFCenterTransfer_center_data_leak_feed_temp';
    protected $description = 'TFCenterTransfer_center_data_leak_feed_temp';

    /**
     * The console command description.
     *
     * @var string
     */

    private $urlLimit = 3;
    private $url = PATH_CENTER_IP_TF.'/api/v1/centerinto-transfer/insertToRef';
    private $ip = '127.0.0.1';
    private $mac = 'abcd';
    private $header = 'header';
    private $dbName = 'dummyDatabase';
    private $site_code = '';
    private $site_mode = '';
    private $insertToTB = 'fx_transaction_center_data_leak_feed_temp';
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
        $tableData = new SubTB;
        $tableData->setConnection($this->dbName);
        $tableData = $tableData->where('status', 1)->where('transaction_data_status', 1)->with('get_transfer')->with('get_transfer_ref')->orderBy('id', 'asc')->get()->toArray();
        // print_r($tableData);

        if (!$tableData) {
            
        } else {
           
            $dataEncode = encrypt_decrypt('encrypt', $this->site_code, $header, $ip, $mac);
            $passBody = [
                'site_code_en' => $dataEncode,
                'queryData' => $tableData,
                'tbName' => $this->insertToTB,
            ];

            $httpData = $this->reconnnect($this->url, $passBody, $this->urlLimit);

            print_r($httpData);
            if ($httpData["success"]) {

                if (!empty($httpData["result"]["returnUpdate"])) {
                    $returnUpdate = $httpData["result"]["returnUpdate"];
                    foreach ($returnUpdate as $valueReturn) {
                        $updater = new SubTB;
                        $updater->setConnection($this->dbName);
                        $updater = $updater->where('id', $valueReturn)->first();
                        $updater->transaction_data_status = 2;
                        $updater->save();
                    }
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
                        'timeout' => 180, //sec == 100sec
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
