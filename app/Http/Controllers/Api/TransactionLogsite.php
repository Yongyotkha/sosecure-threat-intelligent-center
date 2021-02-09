<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\transcation_jobs_clients;
use App\Entities\TF_Center_transaction_batchjob;
use App\Log;
use App\HeadLogSendTransaction;
use App\LogSendTransaction;
use Modules\SiteSettings\Entities\LogsSetting;

class TransactionLogsite extends ApiController
{
    public function transaction_log_site(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                if($data['data']['mode'] == 'complete'){
                    $LogSendTransactions = LogSendTransaction::where('site_id', $data['site']['data']['id'])->where('status_progrss' , 2)->get();
                    foreach($LogSendTransactions as $LogSendTransaction){
                        $LogSendTransaction -> transaction_data_status = 3;
                        $LogSendTransaction -> save();
                    }

                    $HeadLogSendTransaction = HeadLogSendTransaction::where('site_id', $data['site']['data']['id'])->first();
                    $HeadLogSendTransaction -> transaction_status = 3;
                    $HeadLogSendTransaction -> save();
                    
                    $data_LogsSents = json_encode([]);

                    $datas = encrypt_decrypt('encrypt', $data_LogsSents, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
                $HeadLogSendTransaction = HeadLogSendTransaction::where('site_id', $data['site']['data']['id'])->first();
                if(empty($HeadLogSendTransaction)){
                    $HeadLogSendTransaction = new HeadLogSendTransaction;
                    $HeadLogSendTransaction -> site_id = $data['site']['data']['id'];
                    $HeadLogSendTransaction -> transaction_status = 3;
                    $HeadLogSendTransaction -> save();
                }
                if($HeadLogSendTransaction->transaction_status == 3){
                    try {
                        if($data['data']['mode'] == 'wait'){
                            $LogSendTransactions = LogSendTransaction::where('site_id', $data['site']['data']['id'])->where('status_progrss' , 1)->take(20);
                            $LogsSentID = [];
                            foreach($LogSendTransactions as $LogSendTransaction){
                                $LogSendTransaction -> status_progrss = 2;
                                $LogSendTransaction -> save();
                                $LogsSentID[] = $LogSendTransaction -> type;
                            }
                            $LogsSettings = LogsSetting::where('site_id', $data['site']['data']['id'])->whereIn('type', $LogsSentID)->get();
                            $data_LogsSents = json_encode($LogsSettings);
                        }
                        $datas = encrypt_decrypt('encrypt', $data_LogsSents, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
    
                    } catch (\Exception $e) {
                        $response = array(
                            'status_code' => 500,
                            'message' => $e -> getMessage(),
                        );
                        return response()->json($response);
                    }
                }else{
                    $response = array(
                        'status_code' => 204,
                        'message' => 'No Content',
                    );
                    return response()->json($response);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }

    public function tranfer_db(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                try {
                    if($data['data']){
                        foreach($data['data'] as $logs){
                            $log = new Log;
                            $log -> site_id = $data['site']['data']['id'];
                            $log -> file = $logs['file'];
                            $log -> error_summary = $logs['error_summary'];
                            $log -> log_trace = $logs['log_trace'];
                            $log -> save();
                        }
                    }
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200']);
                } catch (\Exception $e) {
                    $response = array(
                        'status_code' => 500,
                        'message' => $e -> getMessage(),
                    );
                    return response()->json($response);
                }
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }

    private function dataFalse($bearerToken, $mode, $data){
        try {
            $header = $bearerToken;
            $site = $this->AuthorizationRegister($header, $mode);
            if($site['status_code'] !== '200'){
                return $this->AuthorizationRegister($header, $mode);
            }
            $value = $data;
            $data = encrypt_decrypt('decrypt', $value, $header, $site['data']['ip_key'],  $site['data']['mac_address_key']);

            if($data === false){
                return $data;
            }else{
                $data_return = [
                    'site' => $site,
                    'data' => json_decode($data, true),
                ];
                return $data_return;
            }

        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }
}
