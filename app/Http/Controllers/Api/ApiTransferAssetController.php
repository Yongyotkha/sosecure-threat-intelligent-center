<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\TransectionAssest;
use App\Log;

class ApiTransferAssetController extends ApiController
{
    public function tranferAsset(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                try {
                    $id = [];
                    foreach($data['data']['assets'] as $item){
                        if($item['transaction_mode'] == 'insert'){
                            $transectionAssest = TransectionAssest::where('id', $item['id'])->where('code', $item['code'])->first();
                            if(empty($transectionAssest)){
                                $transectionAssest = new TransectionAssest;
                                $transectionAssest -> id = $item['id'];
                                $transectionAssest -> code = $item['code'];
                                $transectionAssest -> vendor = $item['vendor'];
                                $transectionAssest -> title = $item['title'];
                                $transectionAssest -> version = $item['version'];
                                $transectionAssest -> edition = $item['edition'];
                                $transectionAssest -> hostname = $item['hostname'];
                                $transectionAssest -> site = $item['site'];
                                $transectionAssest -> ip = $item['ip'];
                                $transectionAssest -> remark = $item['remark'];
                                $transectionAssest -> Create_at = $item['Create_at'];
                                $transectionAssest -> Update_at = $item['Update_at'];
                                $transectionAssest -> Create_by = $item['Create_by'];
                                $transectionAssest -> Update_by = $item['Update_by'];
                                $transectionAssest -> transaction_status = $item['transaction_status'];
                                $transectionAssest -> transaction_mode = $item['transaction_mode'];
                                $transectionAssest -> sync_insight_status = $item['sync_insight_status'];
                                $transectionAssest -> sync_insight_date = $item['sync_insight_date'];
                                $transectionAssest -> sync_insight_resualt = $item['sync_insight_resualt'];
                                $transectionAssest -> save();
                            }
                            $id[] = $item['id'];
                        }else if($item['transaction_mode'] == 'update'){
                            $transectionAssest = TransectionAssest::where('id', $item['id'])->where('code', $item['code'])->first();
                            if(!empty($transectionAssest)){
                                $transectionAssest -> id = $item['id'];
                                $transectionAssest -> code = $item['code'];
                                $transectionAssest -> vendor = $item['vendor'];
                                $transectionAssest -> title = $item['title'];
                                $transectionAssest -> version = $item['version'];
                                $transectionAssest -> edition = $item['edition'];
                                $transectionAssest -> hostname = $item['hostname'];
                                $transectionAssest -> site = $item['site'];
                                $transectionAssest -> ip = $item['ip'];
                                $transectionAssest -> remark = $item['remark'];
                                $transectionAssest -> Create_at = $item['Create_at'];
                                $transectionAssest -> Update_at = $item['Update_at'];
                                $transectionAssest -> Create_by = $item['Create_by'];
                                $transectionAssest -> Update_by = $item['Update_by'];
                                $transectionAssest -> transaction_status = $item['transaction_status'];
                                $transectionAssest -> transaction_mode = $item['transaction_mode'];
                                $transectionAssest -> sync_insight_status = $item['sync_insight_status'];
                                $transectionAssest -> sync_insight_date = $item['sync_insight_date'];
                                $transectionAssest -> sync_insight_resualt = $item['sync_insight_resualt'];
                                $transectionAssest -> save();
                            }
                            $id[] = $item['id'];
                        }else if($item['transaction_mode'] == 'delete'){
                            $id[] = $item['id'];
                            TransectionAssest::where('id', $item['id'])->where('code', $item['code'])->delete();
                        }
                    }
                    $data_transcation_jobs_clients = json_encode(['id' => $id]);
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
