<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\ApiController;


use phpseclib\Net\SSH2;
use Exception;

use Modules\SiteSettings\Entities\SiteSettings;

use App\Entities\Transaction_client_News;
use App\Entities\Transaction_client_webdefacment_data_check;
use App\Entities\Transaction_client_webdefacment_data_logs;
use App\Entities\Transaction_client_webdefacment_data_original;
use App\Entities\Transaction_client_webdefacment_image_mark;
use App\Entities\Transaction_client_webdefacment_setting;
use App\Entities\Transaction_client_data_datacve_mapping;
use App\Entities\Transaction_client_cve_assets;
class ApiTransferClients extends Controller
{

    private $ip = '127.0.0.1';
    private $mac = 'abcd';
    private $header = 'header';
    private $dbName = 'mysql';
    private $center_site_id = '0';


    protected function getTranferData(Request $request)
    {
        $ip = $this->ip;
        $mac = $this->mac;
        $header = $this->header;
        $code = $request->site_code_en;

        $TransactionClient = null;
        $messageErr = '';
        $connect = true;
        $result = true;
        
        $dataEncode = $code;
        $dataDecode = encrypt_decrypt('decrypt', $dataEncode, $header, $ip, $mac);
        
        if($dataDecode){
            $site = SiteSettings::where('code', $dataDecode)->first();
            
            if($site){
                $nameTable = $request->tbName;
                if ($nameTable == 'fx_transaction_client_news') {
                    $model_getData = new Transaction_client_News;
                } else if ($nameTable == 'fx_transaction_client_webdefacment_data_check') {
                    $model_getData = new Transaction_client_webdefacment_data_check;
                } else if ($nameTable == 'fx_transaction_client_webdefacment_data_logs') {
                    $model_getData = new Transaction_client_webdefacment_data_logs;
                } else if ($nameTable == 'fx_transaction_client_webdefacment_data_original') {
                    $model_getData = new Transaction_client_webdefacment_data_original;
                } else if ($nameTable == 'fx_transaction_client_webdefacment_image_mark') {
                    $model_getData = new Transaction_client_webdefacment_image_mark;
                } else if ($nameTable == 'fx_transaction_client_webdefacment_setting') {
                    $model_getData = new Transaction_client_webdefacment_setting;
                } else if ($nameTable == 'fx_transaction_client_data_datacve_mapping') {
                    $model_getData = new Transaction_client_data_datacve_mapping;
                } else if ($nameTable == 'fx_transaction_client_cve_assets') {
                    $model_getData = new Transaction_client_cve_assets;
                } else {
                    $connect = false;
                    $result = false;
                }
                
                if ($result == true) {
                    $TransactionClient = new $model_getData;
                    $TransactionClient->setConnection($this->dbName);
                    $TransactionClient = $TransactionClient->where('site_id',$site->id)->where('status',1)->where('transaction_data_status',1)->with('get_transfer_client')->orderBy('id','asc')->get()->toArray();
  
                    // $updater = new $model_getData;
                    // $updater->setConnection($this->dbName);
                    // $updater->where('site_id',$site->id)->where('status',1)->where('transaction_data_status',1)->update(['transaction_data_status' => 2]);
                }
            }else{
                $connect = false;
                $result = false;
            }
        }else{
            $connect = false;
            $result = false;
        }

        $site_center = $this->center_site_id;
        $dataout = [
            'connect' => $connect,
            'result' => $result,
            'queryData' => $TransactionClient,
            'site_code_en' => $site_center,
            'messageErr' => $messageErr,
        ];
        return response()->json($dataout); 
    }

    protected function get_encode(Request $request)
    {
        $ip = '127.0.0.1';
        $mac = 'abcd';
        $header ='header';
        $site = $request->site_id;
        $dataEncode = encrypt_decrypt('encrypt', $site, $header,$ip,$mac);
        // $dataEncode = encrypt_decrypt('decrypt', $dataEncode, $header,$ip,$mac);
        $dataout = [
            'connect' => true,
            'result' => $dataEncode,
        ];
       
        return response()->json($dataout); 
    }

    protected function checkWebserverIP(Request $request)
    {
        $ip = @$request->ip;
        $port = @$request->port;
        $user = @$request->user;
        $pass = @$request->password;
        $os = @$request->os;
        $checkConnect = null;
        $message = '';
        try {   
            if($os=="Linux"){
                $ssh = new SSH2($ip,$port);
                $ssh->setTimeout(60);
               
                if (!$ssh->login($user, $pass)) {
                    $checkConnect = false;
                    $message = 'no login';
                } else {
                    $checkConnect = true;
                    $message = 'success';
                }
            }else if($os=="Windows"){
                $checkConnect = false;
                $message = 'no make';
            }else{
                $checkConnect = false;
                $message = 'no os';
            }
        } catch (Exception $e) {
            $checkConnect = false;
            $message = $e->getMessage();
        }

        $dataout = [
            'webserverConnect' => $checkConnect,
            'message' => $message
        ];
        return response()->json($dataout); 
    }
    
}
