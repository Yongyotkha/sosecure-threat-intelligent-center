<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Log;
use App\LogPhishing;
use Yajra\DataTables\DataTables;

class ApiPhishingController extends ApiController
{
    public function table(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                try {
                    $logPhishing = LogPhishing::where('transaction_status', 3)->where('url_is_work', 1)->get();
                    $res = DataTables::of($logPhishing)
                    ->editColumn('site_name', function ($collection) {
                        return '-';
                    })
                    ->editColumn('url_detection', function ($collection) {
                        return '-';
                    })
                    ->editColumn('status', function ($collection) {
                        $html = '';
                        $html .= '<label class="switch">
                                    <input type="checkbox" id="status_' . $collection->id . '" onchange="change_status(\'' . $collection->id . '\')" name="status" value="1" checked>
                                    <span></span>
                                </label>';
                        return $html;
                    })
                    ->addColumn('severity', function ( $collection) {
                        if($collection -> is_found == 1){
                            $html = '<span class="badge" style="background-color: #e64732;">High</span>';
                        }else{
                            $html = '<span class="badge" style="background-color: #88ce4f;">Low</span>';
                        }
                        return $html;
                    })
            
                    ->addColumn('action', function ( $collection) {
                        return '<a target="_blank" href="'.$collection -> url.'" class="btn btn-info btn-xs">
                                    <i class="fas fa-eye"></i>
                                </a>
            
                                <a href="#" class="btn btn-danger btn-xs">
                                    <i class="fas fa-trash"></i>
                                </a>
                                ';
                    })
                    ->rawColumns(['severity','status', 'action'])
                    ->toJson();

                    $response = [
                        "data" => $res,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
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
