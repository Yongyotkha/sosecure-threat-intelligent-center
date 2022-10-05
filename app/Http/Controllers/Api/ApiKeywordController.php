<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\Site_keywords;
use Modules\SiteSettings\Entities\site_keywords_main;

use Illuminate\Support\Facades\DB;

class ApiKeywordController extends ApiController
{
    public function show_keywords(Request $request)
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

                $user_id = $data['data']['user_id'];
                $type = ['social', 'darkweb', 'defacement', 'credit_card'];

                $query_user =  DB::table('users')
                    ->select('site.id', 'site.code')
                    ->leftjoin('site', 'users.site_id', 'site.id')
                    ->where(['users.id' => $user_id])
                    ->first();

                // $site = SiteSettings::select(
                //         'code'
                //     )
                //     ->where('id', $query_user->id)
                //     ->first();

                $arr_data = [];
                foreach($type as $data_type)
                {
                    $data_keywords = Site_keywords::select(
                            'id', 
                            'name', 
                            'keywords_main_id', 
                            'site_id', 
                            'status', 
                            'deleted_at', 
                            'type', 
                            'order'
                        )
                        ->where('site_id', $query_user->id)
                        ->where('status', 1)
                        ->whereNull('deleted_at')
                        ->where('type', $data_type)
                        ->orderBy('order', 'asc')
                        ->get();

                    $html = '';
                    if(count($data_keywords) > 0)
                    {
                        foreach($data_keywords as $keywords)
                        {
                            $html .= '
                                <li class="item-list item--keyword" data-id="'.$keywords->id.'">
                                    <div class="left-side-item">
                                        <span class="text-keyword">'.$keywords->name.'</span>
                                    </div>
                                </li>
                            ';
                        }
                    }

                    $arr_data[$data_type] = $html;

                    // $arr = [];
                    // $arr['type'] = $data_type;
                    // $arr['data_keyword'] = $data_keywords;

                    // $arr_data[$data_type] = $data_keywords;
                }

                $response = [
                    "data_keyword" => @$arr_data,
                    "site_id" => @$query_user->id,
                    "site_code" => @$query_user->code,
                    "message" => '',
                    "status" => 1,
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

                $Site_keywords = Site_keywords::select('id', 'name', 'keywords_main_id', 'site_id', 'status', 'deleted_at', 'type', 'order')->where('site_id', $site->id)->where('status',1)->whereNull('deleted_at')->where('type',$type)->orderBy('order','asc')->get();
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
