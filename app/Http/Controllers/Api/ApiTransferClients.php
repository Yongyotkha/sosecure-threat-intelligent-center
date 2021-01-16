<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\ApiController;


use phpseclib\Net\SSH2;
use Exception;

use Modules\SiteSettings\Entities\SiteSettings;

use App\TransactionClientNews;
use App\R_s_s_news;

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
                    $model_getData = new TransactionClientNews;
                } else if ($nameTable == 'fx_transaction_client_news2') {
                    $model_getData = new TransactionClientNews;
                } else {
                    $connect = false;
                    $result = false;
                }
                
                if ($result == true) {
                    $TransactionClient = new $model_getData;
                    $TransactionClient->setConnection($this->dbName);
                    $TransactionClient = $TransactionClient->where('site_id',$site->id)->where('status',1)->where('transaction_data_status',1)->with('get_transfer_client')->orderBy('id','asc')->get()->toArray();
                    
                    $updater = new $model_getData;
                    $updater->setConnection($this->dbName);
                    $updater->where('site_id',$site->id)->where('status',1)->where('transaction_data_status',1)->update(['transaction_data_status' => 2]);
                }
            }else{
                $connect = false;
                $result = false;
            }
            // $TransactionClientNews = TransactionClientNews::where('site_id',$site_id)->where('status',1)->where('transaction_data_status',1)->with('get_Transaction_client_news')->get();
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
