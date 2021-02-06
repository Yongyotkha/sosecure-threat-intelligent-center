<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\transcation_jobs_clients;

class TransactionJobClients extends ApiController
{
    public function transaction_job_clients(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                try {
                    if($data['data']['mode'] == 'wait'){
                        $transcation_jobs_clients = transcation_jobs_clients::where('site_id', $data['site']['data']['id'])->where('status' , 1)->where('transaction_data_status' , 1)->get();
                        foreach($transcation_jobs_clients as $transcation_jobs_client){
                            $transcation_jobs_client -> transaction_data_status = 2;
                            $transcation_jobs_client -> save();
                        }
                        $data_transcation_jobs_clients = json_encode($transcation_jobs_clients);
                    }else if($data['data']['mode'] == 'complete'){
                        $transcation_jobs_client = transcation_jobs_clients::where('id', $data['data']['id'])->first();
                        $transcation_jobs_client -> transaction_data_status = 3;
                        if($data['data']['return_data']){
                            $transcation_jobs_client -> return_data = $data['data']['return_data'];
                        }
                        if($data['data']['return_error']){
                            $transcation_jobs_client -> return_error = $data['data']['return_error'];
                        }
                        $transcation_jobs_client -> save();
                        $data_transcation_jobs_clients = json_encode([]);
                    }
                    $datas = encrypt_decrypt('encrypt', $data_transcation_jobs_clients, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                   
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
