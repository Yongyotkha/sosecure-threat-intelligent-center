<?php

namespace App\Console\Commands;

use Exception;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Console\Command;
use Modules\Scans\Entities\Assets;
use Modules\Scans\Entities\AssetsData;


class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';
    protected $description = 'test';

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
        $Assets_list = [];
        $Assets_data = Assets::where('status',1)->get();
        foreach ($Assets_data as $key => $value) {
         $AssetsData_data = AssetsData::where('site_id',$value->site_id)->where('asset_id',$value->id)->where('status',1)->get();
         $Domain_list = [];
         $IP_List =[];
         foreach ($AssetsData_data as $AssetsData_datakey => $AssetsData_datavalue) {
            if ($AssetsData_datavalue->data_type_id == 1 || $AssetsData_datavalue->data_type_id == 4) {
                //Domain
                array_push($Domain_list, $AssetsData_datavalue);

            }elseif ($AssetsData_datavalue->data_type_id == 5 || $AssetsData_datavalue->data_type_id == 6) {
                //IP Asset
             array_push($IP_List, $AssetsData_datavalue);

         }else{

         }
     }


     foreach ($IP_List as $IP_Listkey => $IP_Listvalue) {
        if (count($Domain_list) == 0) {
          $Assets_data_list = array();
          $Assets_data_list['id'] = $IP_Listvalue->id;
          $Assets_data_list['code'] = $IP_Listvalue->code;
          $Assets_data_list['site_id'] = $IP_Listvalue->site_id;
          $Assets_data_list['status'] = $IP_Listvalue->status;
          $Assets_data_list['created_at'] = $IP_Listvalue->created_at;
          $Assets_data_list['updated_at'] = $IP_Listvalue->updated_at;
          $Assets_data_list['domain'] = "";
          $Assets_data_list['ip'] = $IP_Listvalue->value;
          array_push($Assets_list, $Assets_data_list);

      }else{

         foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
          $Assets_data_list = array();
          $Assets_data_list['id'] = $IP_Listvalue->id;
          $Assets_data_list['code'] = $IP_Listvalue->code;
          $Assets_data_list['site_id'] = $IP_Listvalue->site_id;
          $Assets_data_list['status'] = $IP_Listvalue->status;
          $Assets_data_list['created_at'] = $IP_Listvalue->created_at;
          $Assets_data_list['updated_at'] = $IP_Listvalue->updated_at;
          $Assets_data_list['domain'] = $Domain_listvalue->value;
          $Assets_data_list['ip'] = $IP_Listvalue->value;
          array_push($Assets_list, $Assets_data_list);

      }
  }


}


}
print_r($Assets_list);

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
