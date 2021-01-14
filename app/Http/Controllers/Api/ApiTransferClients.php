<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\TransactionClientNews;
use App\Http\Controllers\Api\ApiController;

use phpseclib\Net\SSH2;
use Exception;


class ApiTransferClients extends ApiController
{
    protected function getTranfer_News(Request $request)
    {
        $TransactionClientNews = null;
        $connect = null;
        $ip = '127.0.0.1';
        $mac = 'abcd';
        $header ='header';
        $dataEncode = $request->site_id;
        $dataDecode = encrypt_decrypt('decrypt', $dataEncode, $header,$ip,$mac);

        if($dataDecode){
            $connect = true;
            $dataJson = json_decode($dataDecode,true);
            $site_id = $dataJson["site_id"];
            $TransactionClientNews = TransactionClientNews::where('site_id',$site_id)->where('status',1)->where('transaction_data_status',1)->with('get_Transaction_client_news')->orderBy('id','asc')->get();
            // $TransactionClientNews = TransactionClientNews::where('site_id',$site_id)->where('status',1)->where('transaction_data_status',1)->with('get_Transaction_client_news')->get();
        }else{
            $connect = false;
        }

        $dataout = [
            'connect' => $connect,
            'result' => $TransactionClientNews,
        ];
        return response()->json($dataout); 
    }

    protected function get_encode(Request $request)
    {
        $ip = '127.0.0.1';
        $mac = 'abcd';
        $header ='header';
        $site = [
            'site_id' => $request->site_id,
        ];
        $dataEncode = encrypt_decrypt('encrypt', json_encode($site), $header,$ip,$mac);
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
