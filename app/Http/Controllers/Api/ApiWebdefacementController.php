<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Bookmark;
use App\DataLeakFeed;
use App\DataLeakFeedTemp;
use App\DataLeakSocialRef;
use App\Entities\IndicatorSummaryYear;
use App\leak_socail_ref_temp;
use App\R_s_s_news;
use App\ReadCategories;
use App\ReadNews;
use App\TransactionTimeStampScans;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Assets\Entities\OSType;
use Modules\CategorySettings\Entities\CategorySettings;
use Modules\MonitoringVulnerabilitys\Entities\CVEAssets;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
use Modules\RSSFeedSettings\Entities\RSSNews;
use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Modules\Scans\Entities\Assets;
use Modules\Scans\Entities\AssetsData;
use Modules\Scans\Entities\CPE;
use Modules\SiteSettings\Entities\DataCveven;
use Modules\SiteSettings\Entities\Domain;
use Modules\SiteSettings\Entities\SiteNewsRelated;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\Users\Entities\UserSite;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\UTCDateTime;
use Yajra\DataTables\DataTables;
use Modules\Users\Entities\User;
use Modules\WebDefacement\Entities\WebdefacmentDataCheck;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use Symfony\Polyfill\Intl\Idn\Resources\unidata\Regex;

class ApiWebdefacementController extends ApiController
{
    public function web_defacement_load_card(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'web_defacement'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $keywords = $data['data']['keywords'];
                    $datatype = $data['data']['datatype'];
                    $site = $data['data']['site'];
                    $level = $data['data']['level'];
                    $search = $data['data']['search'];
                    $url = $data['data']['url'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];

                    $html = '';
     

                    $modal = WebdefacmentSetting::where("active", '=', 1)->where("deleted_at",null);

                    $site_id_arr = @$get_role_custom_first['site_id_arr'];
                    if(@$get_role_custom_first['superadmin'] == 1) {
                        
                    }else if(@$get_role_custom_first['client'] == 1) {
                        $modal = $modal->whereIn('site_id', $site_id_arr);
                    }else if(@$get_role_custom_first['site_support'] == 1) {
                        $modal = $modal->whereIn('site_id', $site_id_arr);
                    }else if(@$get_role_custom_first['site_admin'] == 1) {
                        $modal = $modal->whereIn('site_id', $site_id_arr);
                    }else if(@$get_role_custom_first['site_client'] == 1) {
                        $modal = $modal->whereIn('site_id', $site_id_arr);
                    }


                    if ($search == 1) {
                

                        if ($keywords) {
                            $modal = $modal->where('name', 'LIKE', '%' . $keywords . '%')
                            ->orWhere('url', 'LIKE', '%' . $keywords . '%');
                        }

                        if ($datatype) {
                            // dd($datatype);
                            
                            $modal = $modal->whereIn('status_val', $datatype);
                            // dd($modal);
                        }
                        
                        
                    }

                    if ($site) {
                        $modal = $modal->where('site_id', '=', $site);
                        
                    }

                    if($level){
                        if($level =='High'){
                            $modal = $modal->where('status_val', 'High');
                        }
                        else if($level =='Normal'){
                            $modal = $modal->where('status_val', 'Normal');
                        }
                        else if($level =='Medium'){
                            $modal = $modal->where('status_val', 'Medium');
                        }
                    }
                        
                    $modal = $modal->get();


                    foreach ($modal as $key) {
                        $html .= 
                        '<div class="item-wdfm wdfm-inner-4">
                            <div class="wdfm-card">
                                <div class="wdfm-header">
                                    <div class="wdfm-img">
                                        <a href="'.env('URL_CENTER_PUBLISH').@$key->image_last.'" data-lightbox="name-img-2" >
                                            <img src="'.env('URL_CENTER_PUBLISH').@$key->image_last.'" onerror="setDefaultPic(this)"/>
                                        </a>
                                    </div>
                                </div>
                                <div class="wdfm-body">
                                    <div class="wdfm-btn">
                                        <a href="'.$url.'/webdefacement/detail/'.@$key->code.'" class="btn btn-icon btn-default btn-sm" data-rel="tooltip" title="View" data-placement="bottom">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                    <h4 class="wdfm-elip">'.@$key->name.'</h4>
                                    <p class="mdfm-text-muted">'.@$key->url.'</p>
                                </div>
                                <div class="wdfm-footer">
                                    <div class="wdfm-ft-left flex">
                                        <div><strong>Site </strong>: '.@$key->get_site->name.'</div>
                                        <div class="status-flex mr-2"><strong>Status</strong> : &nbsp; '.@get_webdefacment_status($key->status_val,'color').'</div>
                                        <div class="text-sm-date">Last Online: '.@$key->last_online.'</div>
                                        <div class="text-sm-date">Last Check: '.@$key->last_check.'</div>
                                    </div>
                                </div>
                                <div class="wdfm-footer-action">
                                    <div style="display: flex;justify-content:center;">';
                            
                            
                        
                            $html .= '<a href="'.$url.'/webdefacement/detail/'.@$key->code.'" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a>';
                            if(@$get_role_custom_first['superadmin'] == 1 || @$get_role_custom_first['client'] == 1) {
                                $html .= '<a href="#" onclick="btn_click_edit_webdefacement(\''.$key->code.'\')" class="btn btn-info btn-sm"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.94 74.17l-60.11-60.11c-18.75-18.75-49.16-18.75-67.91 0l-56.55 56.55 128.02 128.02 56.55-56.55c18.75-18.75 18.75-49.15 0-67.91zm-246.8-20.53c-15.62-15.62-40.94-15.62-56.56 0L75.8 172.43c-6.25 6.25-6.25 16.38 0 22.62l22.63 22.63c6.25 6.25 16.38 6.25 22.63 0l101.82-101.82 22.63 22.62L93.95 290.03A327.038 327.038 0 0 0 .17 485.11l-.03.23c-1.7 15.28 11.21 28.2 26.49 26.51a327.02 327.02 0 0 0 195.34-93.8l196.79-196.79-82.77-82.77-84.85-84.85z"></path></svg> Edit</a>
                                    <a href="#" onclick="btn_click_del_webdefacement('.$key->id.')" class="btn btn-danger btn-sm btn_del_webdefacment"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z"></path></svg> Delete</a>
                                ';
                            }
                    

                            $html .= '
                                    </div>
                                </div>
                            </div>
                        </div>';                    
                    }


                    $response = [
                        "html" => $html,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
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

    public function web_defacement_detail(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'web_defacement'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $site_code = $data['data']['site_code'];
                    $code = $data['data']['code'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];

                    $WebdefacmentSetting = WebdefacmentSetting::where("code", $code)->where('deleted_at', null)->where('active', 1)->with('get_site')->with('get_webdefacment_data_original_detail')->with('get_webdefacment_data_check_detail')->with('get_webdefacment_data_log_detail');
        
                    $site_id_arr = @$get_role_custom_first['site_id_arr'];
                    if(@$get_role_custom_first['superadmin'] == 1) {
                        
                    }else if(@$get_role_custom_first['client'] == 1) {
                        $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
                        ->whereIn('id', $site_id_arr)//['49', '56']
                        ->get();
                    }else if(@$get_role_custom_first['site_support'] == 1) {
                        $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
                        ->whereIn('id', $site_id_arr)//['49', '56']
                        ->get();
                    }else if(@$get_role_custom_first['site_admin'] == 1) {
                        $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
                        ->whereIn('id', $site_id_arr)//['49', '56']
                        ->get();
                    }else if(@$get_role_custom_first['site_client'] == 1) {
                        $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
                        ->whereIn('id', $site_id_arr)//['49', '56']
                        ->get();
                    }

                    if(count($site_id_arr) > 0) {
                        $WebdefacmentSetting = $WebdefacmentSetting->whereIn('site_id' , $site_id_arr);
                    }
                    $WebdefacmentSetting = $WebdefacmentSetting->first();

                    $response = [
                        "code" => @$code,
                        "webdefacement" => $WebdefacmentSetting,
                        "webdefacment_data_original" => @$SiteSettings['webdefacement']->get_webdefacment_data_original_detail[0],
                        "webdefacment_data_check" => @$SiteSettings['webdefacement']->get_webdefacment_data_check_detail[0],
                        "webdefacment_data_log" => @$SiteSettings['webdefacement']->get_webdefacment_data_log_detail,
                        "site_code" => @$site_code,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
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

    public function web_defacement_update_original(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'web_defacement'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    ini_set('max_execution_time', '1000');
                    $webdefacment_id = $data['data']['webdefacment_id'];
                    $command = 'app:WebDefacementUpdateOriginal';
                    $params = [
                            'webdefacment_id' => $webdefacment_id,
                    ];
                    Artisan::call($command, $params);
                    $result = Artisan::output();
                    
                    $response = [
                        "data" => $result,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
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

    public function web_defacement_update_original_detail(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'web_defacement'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $id = $data['data']['id'];

                    $webdefacement = WebdefacmentSetting::where('id', $id)->first();
                    $webdefacement_original = WebdefacmentDataOriginal::where('webdefacment_setting_id', $id)->orderBy('created_at', 'desc')->first();
                    $html_h = $webdefacement_original->hash;
                    $html_f = $webdefacement_original->filesize;
                    $html_e = $webdefacement_original->element;
                    $html_b = @$webdefacement->blacklist_keyword_content;
                    $html_l = $webdefacement_original->last_update;

                    $response = [
                        "html_h" => @$html_h,
                        "html_f" => $html_f,
                        "html_e" => @$html_e,
                        "html_b" => @$html_b,
                        "html_l" => @$html_l,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
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

    public function web_defacement_deface_now(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'web_defacement'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    ini_set('max_execution_time', '1000');
                    $webdefacment_id = $data['data']['webdefacment_id'];
                    $command = 'app:WebDefacementProccessbyWebdefacment_id';
                    $params = [
                        'webdefacment_id' => $webdefacment_id,
                    ];

                    Artisan::call($command, $params);
                    
                    $response = [
                        "data" => 'success',
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
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

    public function web_defacement_deface_now_detail(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'web_defacement'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $id = $data['data']['id'];

                    $webdefacement = WebdefacmentSetting::where('id', $id)->first();
                    $webdefacement_check = WebdefacmentDataCheck::where('webdefacment_setting_id', $id)->first();
                    $html_h = @$webdefacement_check->hash_new;
                    $html_f = @formatSizeUnits(@$webdefacement_check->filesize_new) . ' (Difference ' . @$webdefacement_check->filesize_percent . '%)';
                    $html_e = @$webdefacement_check->element_new;
                    $html_b = @$webdefacement->blacklist_keyword_current;
                    $html_l = @$webdefacement_check->last_update;

                    $response = [
                        "html_h" => @$html_h,
                        "html_f" => @$html_f,
                        "html_e" => @$html_e,
                        "html_b" => @$html_b,
                        "html_l" => @$html_l,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
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

    public function web_defacement_update_image(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'web_defacement'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    ini_set('max_execution_time', '1000');
                    $webdefacment_id = $data['data']['id'];
                    $webdefacement = WebdefacmentSetting::where('id', $webdefacment_id)->first();

                    if($webdefacement->get_webdefacment_data_original_detail[0]->url_id){
                        $url_id = $webdefacement->get_webdefacment_data_original_detail[0]->url_id;
                    }else{
                        $url_id = 0;
                    }

                    $site_id = $webdefacement->site_id;
                    $url_web = $webdefacement->url;
                    $port_web = $webdefacement->port;
                    $delay_screenshot_val = $webdefacement->delay_screen_shot_val;
               
                    $command = 'app:WebDefacementsCreenshotCheck';
            
                    $params = [
                            'url' => $url_web,
                            'port' => $port_web,
                            'site_id' => $site_id,
                            'url_id' => $url_id,
                            'delay' => $delay_screenshot_val,
                    ];
            
                        Artisan::call($command, $params);
                        $result = Artisan::output();
                    
                    $response = [
                        "data" => $result,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
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
