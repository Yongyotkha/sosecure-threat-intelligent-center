<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReciveLogErrorController extends ApiController
{
    public function recive_log_error(Request $request){
        $header = $request->bearerToken();
        $site = $this->AuthorizationRegister($header, $request->mode);
        if($site['status_code'] !== '200'){
            return $this->AuthorizationRegister($header, $request->mode);
        }
        
        $value = $request -> data;
        $data = encrypt_decrypt('decrypt', $value, $header, $site['data']['ip_key'],  $site['data']['mac_address_key']);
        if($data === false){
            return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
        }else{
            $data_key = json_decode($data, true);
            foreach($data_key['code'] as $code){
                $DeployCode = DeployCode::where('code', $code)->first();
                $DeployHistory = new DeployHistory;
                $DeployHistory->code = $DeployCode -> code;
                $DeployHistory->path = $DeployCode -> path;
                $DeployHistory->version = $DeployCode -> version;
                $DeployHistory->status = 1;
                $DeployHistory->site_id = $site['data']['id'];
                $DeployHistory->save(); 
            }
            $site = SiteSettings::where('id', $site['data']['id'])->first(); 
            $site->installed = 1;
            $site->save();
            
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'code' => $site->code, 'key' => $site->public_key]);
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
