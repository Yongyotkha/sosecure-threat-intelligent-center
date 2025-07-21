<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Log;

class TranferFromClient extends ApiController
{
    public function tranferDB(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                try {
                    $log = new Log;
                    $log -> site_id = $data['site']['data']['id'];
                    $log -> file = $data['data']['return_data'];
                    $log -> error_summary = $data['data']['error_summary'];
                    $log -> log_trace = $data['data']['log_trace'];
                    $log -> save();
                    
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
}
