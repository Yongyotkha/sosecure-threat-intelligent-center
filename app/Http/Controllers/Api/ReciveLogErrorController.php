<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

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
            try {
                $attemptToWriteText = $data_key['content'];
                Storage::disk('local')->put('/logs_dialy/'.$site['data']['id'].'/laravel-' . Carbon::now()->format('Y-m-d') . '.log', $attemptToWriteText);
           } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage(), 'status_code' => '500']);
           }
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200']);
        }
    } 
}
