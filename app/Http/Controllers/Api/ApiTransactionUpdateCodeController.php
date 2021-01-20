<?php

namespace App\Http\Controllers\Api;

use App\DeployCode;
use App\DeployHistory;
use Illuminate\Http\Request;
use Modules\SiteSettings\Entities\SiteSettings;

class ApiTransactionUpdateCodeController extends ApiController
{
    public function tranfer_transaction_update_code(Request $request){
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
            $DeployCode = DeployCode::select('code', 'site_id', 'version', 'path')->where('site_id', $site['data']['id'])->get();
            $code = [];
            if(!empty($DeployCode)){
                foreach($DeployCode as $item){
                    $DeployHistory = DeployHistory::where('site_id', $item -> site_id)->where('version', $item -> version)->first();
                    if(empty($DeployHistory)){
                        $code[] = $item;
                    }
                }
            }
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $code]);
        }
    }
}
