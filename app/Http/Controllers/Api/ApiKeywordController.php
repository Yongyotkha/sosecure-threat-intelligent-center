<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\SiteSettings\Entities\LogsSetting;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\site_config_email_alert;
use Modules\SiteSettings\Entities\Site_keywords;
use Modules\SiteSettings\Entities\site_keywords_main;
use MongoDB\Client as MongoClient;

class ApiKeywordController extends Controller
{
    public function get_keyword_main(Request $request)
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
         
            if(!$data){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $message = '';
                $status = 0;

                $code_site = $data['data']['code_site'];
                $site = SiteSettings::select('id')->where('code', $code_site)->first();

                $site_keywords_main = site_keywords_main::where('site_id', $site->id)->where('status',1)->whereNull('deleted_at')->orderBy('order','asc')->get();
                if(!$site_keywords_main) {
                    $message = langapp('changes_saved_successful');
                    $status = 1;
                } else {
                    $message = '';
                    $status = 1;
                }

                $response = [
                    "data" => @$site_keywords_main,
                    "message" => $message,
                    "status" => $status,
                ];

                $data_transcation = json_encode($response);
                $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e -> getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function get_keyword_sub(Request $request)
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
         
            if(!$data){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $message = '';
                $status = 0;

                $type = $data['data']['type'];
                $code_site = $data['data']['code_site'];

                $site = SiteSettings::select('id')->where('code', $code_site)->first();

                $Site_keywords = Site_keywords::where('site_id', $site->id)->where('status',1)->whereNull('deleted_at')->where('type',$type)->orderBy('order','asc')->get();
                if(!$Site_keywords) {
                    $message = langapp('changes_saved_successful');
                    $status = 1;
                } else {
                    $message = '';
                    $status = 1;
                }
                $response = [
                    "data" => @$Site_keywords,
                    "message" => $message,
                    "status" => $status,
                ];

                $data_transcation = json_encode($response);
                $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e -> getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    private function dataFalse($data){
        try {
            $data_return = [
                'data' => json_decode($data, true),
            ];
            return $data_return;
        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }

    private function explode_val($val,$type=null,$url) {
        $result = '';
        if($val) {
            $val_arr = explode(",",$val);
            if($val_arr) {
                foreach($val_arr as $tag) {
                    if($type == 'tags') {
                        $result .=  '<a href="'.$url.'/indicators/tags/'.$tag.'">'.$tag.'</a> ,';
                    } else if ($type == 'groups') {
                        $result .=  '<a href="'.$url.'/indicators/groups/'.$tag.'">'.$tag.'</a> ,';
                    } else {
                        $result .=  '<a href="#">'.$tag.'</a> ,';
                    }
    
                }
                $result = rtrim($result,',');
            }
        } else {
            $result = '';
        }
        return $result;
    }
}
