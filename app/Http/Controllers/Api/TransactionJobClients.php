<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\transcation_jobs_clients;
use App\Entities\TF_Center_transaction_batchjob;
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




                            $TF_Center_transaction_batchjob = TF_Center_transaction_batchjob::where('name', $transcation_jobs_client->mode)->where('site_id', $transcation_jobs_client->site_id)->first();

                            if(!$TF_Center_transaction_batchjob){
                                $TF_Center_transaction_batchjob = new TF_Center_transaction_batchjob;
                                $TF_Center_transaction_batchjob->status = 1;
                                $TF_Center_transaction_batchjob->code = generator_uuid();

                                $TF_Center_transaction_batchjob->site_id = $transcation_jobs_client->site_id;
                            }
                            $TF_Center_transaction_batchjob->mode = $transcation_jobs_client->id;
                            $TF_Center_transaction_batchjob->name = $transcation_jobs_client->mode;
                            $TF_Center_transaction_batchjob->transcation_date = date('Y-m-d');
                            $TF_Center_transaction_batchjob->progress = 2;
                            $TF_Center_transaction_batchjob->transcation_date_start = date('Y-m-d H:i:s');
                            $TF_Center_transaction_batchjob->save();


                            




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






                        $TF_Center_transaction_batchjob = TF_Center_transaction_batchjob::where('name', $transcation_jobs_client->mode)->where('site_id', $transcation_jobs_client->site_id)->first();

                        if(!$TF_Center_transaction_batchjob){
                            $TF_Center_transaction_batchjob = new TF_Center_transaction_batchjob;
                            $TF_Center_transaction_batchjob->status = 1;
                            $TF_Center_transaction_batchjob->code = generator_uuid();

                            $TF_Center_transaction_batchjob->site_id = $transcation_jobs_client->site_id;
                        }
                        $TF_Center_transaction_batchjob->mode = $transcation_jobs_client->id;
                        $TF_Center_transaction_batchjob->name = $transcation_jobs_client->mode;
                        $TF_Center_transaction_batchjob->transcation_date = date('Y-m-d');
                        $TF_Center_transaction_batchjob->progress = 3;
                        $TF_Center_transaction_batchjob->transcation_date_end = date('Y-m-d H:i:s');
                        $TF_Center_transaction_batchjob->message =$transcation_jobs_client ->return_data;
                        $TF_Center_transaction_batchjob->save();







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
