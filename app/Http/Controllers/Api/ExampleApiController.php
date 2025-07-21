<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;

class ExampleApiController extends ApiController
{
    function test_get(Request $request){
        $header = $request->bearerToken();
        $site = $this->Authorization($header, $request->mode);
        if($site['status_code'] !== '200'){
            return $this->Authorization($header, $request->mode);
        }
        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $site]);
    }
}
