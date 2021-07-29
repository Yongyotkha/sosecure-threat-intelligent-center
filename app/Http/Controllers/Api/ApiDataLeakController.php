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
use Modules\Social\Entities\Data_leak_social;
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
use Modules\SiteSettings\Entities\Activity;

class ApiDataLeakController extends ApiController
{
    public function data_leak_view(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code = $data['data']['code'];
                    
                    $model1 = DataLeakSocialRef::select('data_leak_feed.feedcontent')
                    ->join('data_leak_feed', 'data_leak_feed.id', '=','data_leak_socail_ref.data_leak_feed_id')
                    ->where('data_leak_socail_ref.code',$code)->first();

                    $response = [
                        "code" => $code,
                        "feedcontent" => $model1->feedcontent,
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

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    
    public function data_leak_table(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $startDate = $data['data']['startDate'];
                    $endDate = $data['data']['endDate'];
                    $search_val = $data['data']['search_val'];
                    $site = $data['data']['site'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $keywords = $data['data']['keywords'];
                    $type = $data['data']['type'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $check_type = $data['data']['check_type'];
                    $source = $data['data']['source'];
                    $click_type = $data['data']['click_type'];
                    $click_type2 = $data['data']['click_type2'];
                    $click_key = $data['data']['click_key'];


                    $check_serverity = $data['data']['check_serverity'];
                    $check_monitoring = $data['data']['check_monitoring'];
                    $check_social = $data['data']['check_social'];


                    $model = DataLeakSocialRef::where('deleted_at', null)
                    ->where('status',1)
                    ->whereHas('get_data_leak_feed_one', function ($query) {
                        $query->whereIn('feel_type', ['social','darkweb_public']);
                    })
                    ->with('get_site')
                    ->with('get_data_leak_feed_one');
        

                    $DataLeakSocialRef_data = DataLeakSocialRef::join('data_leak_feed', 'data_leak_socail_ref.data_leak_feed_id', '=', 'data_leak_feed.id')
                    ->whereIn('data_leak_feed.feel_type', ['social','darkweb_public'])->join('site','site.id','data_leak_socail_ref.site_id')
                    ->select('data_leak_socail_ref.*','data_leak_feed.*','site.name as site_name');


                    if ($search_val == 1) {
            
                        if ($keywords) {
                            $keywords = $keywords;
                            $DataLeakFeed_data  = DataLeakFeed::where('deleted_at', null)->where('status','1')->whereIn('feel_type', ['social','darkweb_public'])->get();
                            foreach ($DataLeakFeed_data as $value_data) {
                                  $value_data->feedcontent_decode = html_entity_decode($value_data->feedcontent);
                            }
                            $DataLeakFeed_data_id = array();
                            array_push($DataLeakFeed_data_id, 0);
                            foreach($DataLeakFeed_data as $a) {
                                if(strpos(strtoupper($a->feedcontent_decode), strtoupper($keywords)) !== false) {
                                    array_push($DataLeakFeed_data_id, $a->id);
                                } 
                            }

                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($keywords,$DataLeakFeed_data_id) {
                                $query->where('keyword', 'LIKE', '%' . $keywords . '%');
                                    //->orWhere('feedcontent', 'LIKE', '%' . $keywords . '%');
                                    $query->orWhereIn('id', $DataLeakFeed_data_id);
                            });
                            $DataLeakSocialRef_data->whereRaw('(LOWER(fx_data_leak_feed.keyword) LIKE ? or LOWER(fx_data_leak_feed.feedcontent) LIKE ? )', array([trim(strtolower('%' .$keywords.'%'))],[trim(strtolower('%' .$keywords.'%'))]));
                            $DataLeakSocialRef_data->orWhereIn('data_leak_feed.id', $DataLeakFeed_data_id);
                        }


            
            
                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if(@$get_role_custom_first['superadmin'] == 1) {

                        }else if(@$get_role_custom_first['client'] == 1) {
                            $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);
                            $DataLeakSocialRef_data->whereIn('data_leak_socail_ref.site_id', $site_id_arr)->where('data_leak_feed.status', 1);
            
                        }else if(@$get_role_custom_first['site_support'] == 1) {
                            $model = $model->whereIn('site_id', $site_id_arr);
                            $DataLeakSocialRef_data->whereIn('data_leak_socail_ref.site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_admin'] == 1) {
                            $model = $model->whereIn('site_id', $site_id_arr);
                            $DataLeakSocialRef_data->whereIn('data_leak_socail_ref.site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_client'] == 1) {
                            $model = $model->whereIn('site_id', $site_id_arr)->where('data_leak_feed.status', 1);
                            $DataLeakSocialRef_data->whereIn('data_leak_socail_ref.site_id', $site_id_arr)->where('fx_data_leak_feed.status', 1);
                        }
            
                
            
                        if ($check_type) {
                        
                            $type = $check_type;
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($type) {
                                $query->where('feel_type', 'LIKE', '%' . $type . '%');
                            });
                            $DataLeakSocialRef_data->where('data_leak_feed.feel_type', 'LIKE', '%' . $type . '%');
                        }
                  
                        if ($check_social && $check_type =="social") {
              
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($check_social) {
                                if($check_social =="other"){
                                    $query->whereNotIn('keyword', ['Mobile','Facebook','Line','Twitter','Website']);
                                }else{
                                    $query->where('keyword', $check_social);
                                }
                              
                            });
                            if($check_social =="other"){
                                $DataLeakSocialRef_data->whereNotIn('data_leak_feed.keyword', ['Mobile','Facebook','Line','Twitter','Website']);
                            }else{
                                    $DataLeakSocialRef_data->where('data_leak_feed.keyword', $check_social);
            
                            }
            
                        }
                        if ($check_serverity) {
                            $model = $model->where('serverity', $check_serverity);
                            $DataLeakSocialRef_data->where('data_leak_socail_ref.serverity', 'LIKE', '%' . $check_serverity . '%');
                            // });
                        }
            
                        if ($check_monitoring) {
                            $model = $model->where('status_monitoring', $check_monitoring);
                            $DataLeakSocialRef_data->where('data_leak_socail_ref.status_monitoring', 'LIKE', '%' . $check_monitoring . '%');
                            // });
                        }
                        // if ($source) {
            
                        //     $source = $source;
                        //     $model->whereHas('get_data_leak_feed_one', function ($query) use ($source) {
                        //         $query->where('sourceid', 'LIKE', '%' . $source . '%');
                        //     });
            
                        // }
            
                        if ($isDateSearch == 1) {
                            $date_start = $startDate;
                            $date_end = $endDate;
            
                            $date_start_explode = explode(" ", $date_start);
                            $date_start_date = @$date_start_explode[0];
                            $date_start_time = @$date_start_explode[1] . ' ' . @$date_start_explode[2];
            
                            $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
            
                            $date_start_time_time = date("H:i", strtotime($date_start_time));
                            $date_start_datetime_format = $date_start_date_format . ' ' . $date_start_time_time . ':00';
            
                            $date_end_explode = explode(" ", $date_end);
                            $date_end_date = @$date_end_explode[0];
                            $date_end_time = @$date_end_explode[1] . ' ' . @$date_end_explode[2];
                            // dd($date_end_time);
                            $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                            $date_end_time_time = date("H:i", strtotime($date_end_time));
                            $date_end_datetime_format = $date_end_date_format . ' ' . $date_end_time_time . ':00';
            
                            $source = $source;
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($date_start_date_format, $date_end_date_format) {
                                $query->whereBetween('feedtimepost', array($date_start_date_format, $date_end_date_format));
                            });
                            $DataLeakSocialRef_data->whereBetween('data_leak_feed.feedtimepost',array($date_start_date_format, $date_end_date_format));
                        }

                       // $model->orderBy('created_at','desc')->get();
                    } else {

                        if ($click_type2) {
                            $keywords = $click_type2;
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($keywords) {
                                if($keywords == 'other') {

                                    $query->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('mobile'))])
                                            ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('facebook'))])
                                            ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('line'))])
                                            ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('twitter'))])
                                            ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))]);


            
                                } else {
                                    if($keywords == 'in_progress') {
                                        $query->where('status_monitoring', 'LIKE', '%' . $keywords . '%');
                                    } else if($keywords == 'reported') {
                                        $query->where('status_monitoring', 'LIKE', '%' . $keywords . '%');
                                    } else if($keywords == 'close') {
                                        $query->where('status_monitoring', 'LIKE', '%' . $keywords . '%');
                                    } else {
                                        $query->where('keyword', 'LIKE', '%' . $keywords . '%');
                                    }
                                }
                            });
                            
                            if($keywords == 'other') {
                                $DataLeakSocialRef_data->whereRaw('LOWER(fx_data_leak_feed.keyword) != ? ',[trim(strtolower('mobile'))])
                                                        ->whereRaw('LOWER(fx_data_leak_feed.keyword) != ? ',[trim(strtolower('facebook'))])
                                                        ->whereRaw('LOWER(fx_data_leak_feed.keyword) != ? ',[trim(strtolower('line'))])
                                                        ->whereRaw('LOWER(fx_data_leak_feed.keyword) != ? ',[trim(strtolower('twitter'))])
                                                        ->whereRaw('LOWER(fx_data_leak_feed.keyword) != ? ',[trim(strtolower('website'))]);
                            } else {
                                if($keywords == 'in_progress') {
                                    $DataLeakSocialRef_data->where('data_leak_socail_ref.status_monitoring', 'LIKE', '%' . $keywords . '%');
                                } else if($keywords == 'reported') {
                                    $DataLeakSocialRef_data->where('data_leak_socail_ref.status_monitoring', 'LIKE', '%' . $keywords . '%');
                                } else if($keywords == 'close') {
                                    $DataLeakSocialRef_data->where('data_leak_socail_ref.status_monitoring', 'LIKE', '%' . $keywords . '%');
                                } else {
                                    $DataLeakSocialRef_data->where('data_leak_feed.keyword', 'LIKE', '%' . $keywords . '%');
                                }
                            }
                        }

                        
                        if($click_type) {
    
                            $model = $model-> where('feel_type', '=' ,$click_type);
                            $DataLeakSocialRef_data->where('data_leak_socail_ref.feel_type',$click_type);
            
                        }
            
                        if ($click_key) {
                            $model = $model->where('keyword', $click_key);
                            $DataLeakSocialRef_data->where('LOWER(`data_leak_feed.keyword`)','LIKE',[trim(strtolower($click_key))]);
                        }

                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if(@$get_role_custom_first['superadmin'] == 1) {

                        }else if(@$get_role_custom_first['client'] == 1) {
                            $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);
                            $DataLeakSocialRef_data->whereIn('data_leak_socail_ref.site_id', $site_id_arr)->where('data_leak_feed.status', 1);
            
                        }else if(@$get_role_custom_first['site_support'] == 1) {
                            $model = $model->whereIn('site_id', $site_id_arr);
                            $DataLeakSocialRef_data->whereIn('data_leak_socail_ref.site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_admin'] == 1) {
                            $model = $model->whereIn('site_id', $site_id_arr);
                            $DataLeakSocialRef_data->whereIn('data_leak_socail_ref.site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_client'] == 1) {
                            $model = $model->whereIn('site_id', $site_id_arr)->where('data_leak_feed.status', 1);
                            $DataLeakSocialRef_data->whereIn('data_leak_socail_ref.site_id', $site_id_arr)->where('fx_data_leak_feed.status', 1);
                        }

                       // $model->orderBy('created_at','desc')->get();
                    }


                    $order_column = $data['data']['order_column'];
                    $order_dir = $data['data']['order_dir'];
                 //   $res =  "";
                    if($order_column){
                        $column_order =$order_column;
                        $column_dir =  $order_dir;
                        if($column_order == "9"){
                            $model->orderBy('status',$column_dir);
                            $DataLeakSocialRef_data->orderBy('data_leak_socail_ref.site_id', $column_dir);
                        }
                        else if($column_order == "8"){
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($column_dir) {
                                      $query->orderBy('feedtimepost',$column_dir);
                            });
                            $DataLeakSocialRef_data->orderBy('data_leak_feed.feedtimepost', $column_dir);
                        }
                        else if($column_order == "7"){
            
                            $model->orderBy('status_monitoring',$column_dir);
                            $DataLeakSocialRef_data->orderBy('data_leak_socail_ref.status_monitoring', $column_dir);
                        }
                        else if($column_order == "6"){
            
                            $model->orderBy('serverity',$column_dir);
                            $DataLeakSocialRef_data->orderBy('data_leak_socail_ref.serverity', $column_dir);
                        }
                        else if($column_order == "5"){
            
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($column_dir) {
                                $query->orderBy('feedcontent',$column_dir);
                            });
                            $DataLeakSocialRef_data->orderBy('data_leak_feed.feedcontent', $column_dir);
                        }
                        else if($column_order == "4"){
            
                            $model->orderBy('keyword',$column_dir);
                            $DataLeakSocialRef_data->orderBy('data_leak_feed.keyword', $column_dir);
                        }
                        else if($column_order == "3"){
            
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($column_dir) {
                                $query->orderBy('source_name',$column_dir);
                            });
                            $DataLeakSocialRef_data->orderBy('data_leak_feed.source_name', $column_dir);
                        }
                        else if($column_order == "2"){
            
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($column_dir) {
                                $query->orderBy('feel_type',$column_dir);
                            });
                            $DataLeakSocialRef_data->orderBy('data_leak_socail_ref.feel_type', $column_dir);
                        }
                        else if($column_order == "1"){
            
                            $model->whereHas('get_site', function ($query) use ($column_dir) {
                                $query->orderBy('name',$column_dir);
                            });
                            $DataLeakSocialRef_data->orderBy('site.name', $column_dir);
                       
                        }else{
                            $model->orderBy('created_at', 'desc');
                            $DataLeakSocialRef_data->orderBy('data_leak_socail_ref.created_at', $column_dir);
                        }
                    }else{
                        $model->orderBy('created_at', 'desc');
                        $DataLeakSocialRef_data->orderBy('data_leak_socail_ref.created_at', $column_dir);
                    }
                  //  $model->where('status_monitoring', 'in_progress');
                 //   $res = DataTables::of($model)->toJson(); 
                 //  $res =    $data['data']['order_column'];
                   $data_count = $DataLeakSocialRef_data->count();

                    $response = [
                        "recordsFiltered_count"=> $data_count,
                        "recordsTotal_count" => $data_count,
                        "data" => DataTables::of($DataLeakSocialRef_data->skip($data['data']['start'])->take($data['data']['length'])->get())->rawColumns(['feedcontent','get_data_leak_feed_one.feedcontent'])->toJson(),
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

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function data_leak_count_val(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $date_start = $data['data']['date_start'];
                    $date_end = $data['data']['date_end'];
                    $site_code = $data['data']['site_code'];
                    $title = $data['data']['title'];
                    $social = $data['data']['social'];
                    $f_search = $data['data']['f_search'];
                    $type = $data['data']['type'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $check_type = $data['data']['check_type'];

                    $user = User::where('id', $data['data']['user_id'])->first();

                    $site_id = '';
            
                    if($site_code) {
                        $site_id_m = SiteSettings::where('code',$site_code)->first();
                        $site_id = @$site_id_m->id;
                    }
               
            
                    $date_start_explode = explode(" ",$date_start);
                    $date_start_date = @$date_start_explode[0];
                    $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                    // dd($date_start_time);
                    $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                    // dd($date_start_date_format);
                    $date_start_time_time = date("H:i", strtotime($date_start_time));
                    $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                    // dd($date_start_time_time);
            
                    $date_end_explode = explode(" ",$date_end);
                    $date_end_date = @$date_end_explode[0];
                    $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                    // dd($date_end_time);
                    $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                    $date_end_time_time = date("H:i", strtotime($date_end_time));
                    $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
                    // dd($date_end_time_time);
            
                    // date("H:i", strtotime("04:25 PM"))
                    $html = '';
            
                    
            
                    if( $f_search == 1){
                        $model = DataLeakSocialRef::where('deleted_at', null)
                        ->where('status',1)
                        ->whereIn('feel_type', ['social', 'darkweb_public'])->with('get_site')->with('get_data_leak_feed_one');
                        $countGroupBy = DataLeakSocialRef::where('deleted_at', null)
                        ->where('status',1)
                        ->whereIn('feel_type', ['social', 'darkweb_public'])->with('get_site')->with('get_data_leak_feed_one');


                        if ($title) {
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($title) {
                                $query->where('keyword', 'LIKE', '%' . $title . '%')
                                    ->orWhere('feedcontent', 'LIKE', '%' . $title . '%');
                            });
            
                            $countGroupBy->whereHas('get_data_leak_feed_one', function ($query) use ($title) {
                                $query->where('keyword', 'LIKE', '%' . $title . '%')
                                    ->orWhere('feedcontent', 'LIKE', '%' . $title . '%');
                            });
                        }

                        if($check_type) {
                            $model = $model-> where('feel_type', '=' ,$check_type);
                            $countGroupBy = $countGroupBy -> where('feel_type', '=' ,$check_type);
                        }
            
                        
            

                          
                            if($isDateSearch==1){
                                // $news = $news -> whereBetween('feedtimepost',array($date_start_datetime_format,$date_end_datetime_format));
                                // $countGroupBy = $countGroupBy -> whereBetween('feedtimepost',array($date_start_datetime_format,$date_end_datetime_format));
                            
                                $countGroupBy = $countGroupBy->whereHas('get_data_leak_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
                                    $qq->whereBetween('feedtimepost', array($date_start_datetime_format, $date_end_datetime_format));
                                });
            
                                $model = $model->whereHas('get_data_leak_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
                                    $qq->whereBetween('feedtimepost', array($date_start_datetime_format, $date_end_datetime_format));
                                });
                            
                            }

            
                        $site_id_arr = UserSite::select('site_id')->where('user_id', @$user->id)->get();
                        if(@$user->site_role_id && @$user->site_id) {
                            if(@$user->site_role_id == 99 || @$user->site_role_id == 4) {//support and admin
                                // dd(99);
        
                                $model = $model->whereIn('site_id', $site_id_arr);
                
                                $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                        
        
                            } else {//not support and admin
                                $model = $model->whereIn('site_id', $site_id_arr);
                
                                $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            }
                        }
            
            
            
                        // if($site_id) {
                        //     $model = $model->where('site_id', $site_id);
            
                        //     $countGroupBy = $countGroupBy->where('site_id', $site_id);
                        // }

                        $get_role_custom_first = $data['data']['get_role_custom_first'];
                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if(@$get_role_custom_first['superadmin'] == 1) {
                            
            
                        }else if(@$get_role_custom_first['client'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            $model = $model->whereIn('site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_support'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            $model = $model->whereIn('site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_admin'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            $model = $model->whereIn('site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_client'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            $model = $model->whereIn('site_id', $site_id_arr);
            
                        }

                        $Data_leak_feed_all = $model->count();
                        $countGroupBy = $countGroupBy->select( 'feel_type',DB::raw('count(*) as total'))->groupBy('feel_type')->get();
                        $model = $model->with('get_data_leak_feed_one')->orderBy('id','desc')->paginate(PAGINATE_NUM);
                    }else{
                        $Data_leak_feed_all = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public']);
                        $news = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public']);//->get()
                        $countGroupBy = DataLeakSocialRef::select( 'feel_type',DB::raw('count(*) as total'))
                        ->where('status',1)
                        ->where('deleted_at', null)
                        ->whereIn('feel_type', ['social', 'darkweb_public'])
                        ->groupBy('feel_type');
                       
            
                        //     $site_id_arr = UserSite::select('site_id')->where('user_id', $user -> id)->get();
                        //     if(@$user->site_role_id && @$user->site_id) {
                        //         if(@$user->site_role_id == 99 || @$user->site_role_id == 4) {//support and admin
                        //             $news = $news->whereIn('site_id', $site_id_arr);
                        //             $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            
                        //         } else {//not support and admin
                        //             $news = $news->whereIn('site_id', $site_id_arr);
                        //             $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                        //         }
                        //     }           
                        
            
                        // if($site_id) {
                        //     $news = $news->where('site_id', $site_id);
                        //     $countGroupBy = $countGroupBy->where('site_id', $site_id);
                        // }
                        $get_role_custom_first = $data['data']['get_role_custom_first'];
                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if(@$get_role_custom_first['superadmin'] == 1) {
                            
            
                        }else if(@$get_role_custom_first['client'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            $Data_leak_feed_all = $Data_leak_feed_all->whereIn('site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_support'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            $Data_leak_feed_all = $Data_leak_feed_all->whereIn('site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_admin'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            $Data_leak_feed_all = $Data_leak_feed_all->whereIn('site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_client'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            $Data_leak_feed_all = $Data_leak_feed_all->whereIn('site_id', $site_id_arr);
            
                        }
                        $news = $news->with('get_data_leak_feed_one')->orderBy('id','desc')->paginate(PAGINATE_NUM);
                        $Data_leak_feed_all = $Data_leak_feed_all->count();
                        $countGroupBy = $countGroupBy->get();
            
            
                    }

                    $content = [];

                    $count_sub_type["darkweb_public"] = 0;
                    $count_sub_type["social"] = 0;
                    
                    foreach ($countGroupBy as $countGroup) {
                        $count_sub_type[$countGroup->feel_type] = $countGroup->total;
                    }

                    $response = [
                        "html" => $html,
                        "count" => $Data_leak_feed_all,
                        "darkweb" => $count_sub_type["darkweb_public"],
                        "social" => $count_sub_type["social"],

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


    public function data_leak_delete(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                     $code = $data['data']['code'];
                    
                    // $model1 = DataLeakSocialRef::select('data_leak_feed.feedcontent')
                    // ->join('data_leak_feed', 'data_leak_feed.id', '=','data_leak_socail_ref.data_leak_feed_id')
                    // ->where('data_leak_socail_ref.code',$code)->first();

                    $response = [
                        "code" => $code,
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

    public function data_leak_delete_select(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code = $data['data']['code'];
                    
                    $DataLeakSocialRef = DataLeakSocialRef::where('code', $code)->first();
                    if($DataLeakSocialRef){
                        $DataLeakSocialRef->delete();
                    }

                    $response = [
                        "code" => $code,
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

    public function data_leak_delete_change(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code = $data['data']['code'];
                    foreach($code as $id){
                        $DataLeakSocialRef = DataLeakSocialRef::where('id', $id)->delete();
                    }


                    $response = [
                        "code" => $code,

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

    public function count_keyword(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $get_role_custom_first = $data['data']['get_role_custom_first'];

                    $model = DataLeakSocialRef::select('keyword',DB::raw('count(*)  as count_keyword'))
                            ->where('deleted_at',null)
                            ->whereIn('feel_type', ['social', 'darkweb_public'])
                            ->groupBy('keyword');

                    $site_id_arr = @$get_role_custom_first['site_id_arr'];
                    if(@$get_role_custom_first['superadmin'] == 1) {
        
                    }else if(@$get_role_custom_first['client'] == 1) {
                        $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);
        
                    }else if(@$get_role_custom_first['site_support'] == 1) {
                        $model = $model->whereIn('site_id', $site_id_arr);
        
                    }else if(@$get_role_custom_first['site_admin'] == 1) {
                        $model = $model->whereIn('site_id', $site_id_arr);
        
                    }else if(@$get_role_custom_first['site_client'] == 1) {
                        $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);
        
                    }

                    $model = $model->get()->toArray();
                    

                    $response = [
                        "model" => $model,

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

    public function count_icon(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $site_id_arr = @$get_role_custom_first['site_id_arr'];

                    $site_code = $data['data']['site_code'];
                    if($site_code) {
                        $SiteSettings = SiteSettings::where('code',$site_code)->first();
                    }

                    if(@$get_role_custom_first['superadmin'] == 1) {
                        if(!$site_code) {
                            $icon_mobile = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('mobile')).'%'])->count();//->get()
                            $icon_facebook = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('facebook')).'%'])->count();//->get()
                            $icon_line = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('line')).'%'])->count();//->get()
                            $icon_twitter = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('twitter')).'%'])->count();//->get()
                            $icon_website = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('website')).'%'])->count();//->get()
                            $icon_other = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('mobile'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('facebook'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('line'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('twitter'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                            $number_in_progress = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('in_progress')).'%'])->count();//->get()
                            $number_reported = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('reported')).'%'])->count();//->get()
                            $number_close = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('close')).'%'])->count();//->get()                                                                                                                                    ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                        } else {
                            $icon_mobile = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('mobile')).'%'])->count();//->get()
                            $icon_facebook = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('facebook')).'%'])->count();//->get()
                            $icon_line = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('line')).'%'])->count();//->get()
                            $icon_twitter = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('twitter')).'%'])->count();//->get()
                            $icon_website = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('website')).'%'])->count();//->get()
                            $icon_other = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('mobile'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('facebook'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('line'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('twitter'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                            $number_in_progress = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('in_progress')).'%'])->count();//->get()
                            $number_reported = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('reported')).'%'])->count();//->get()
                            $number_close = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('close')).'%'])->count();//->get()                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                        }
                    } else {
                        if(!$site_code) {
                            $icon_mobile = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('mobile')).'%'])->count();//->get()
                            $icon_facebook = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('facebook')).'%'])->count();//->get()
                            $icon_line = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('line')).'%'])->count();//->get()
                            $icon_twitter = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('twitter')).'%'])->count();//->get()
                            $icon_website = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('website')).'%'])->count();//->get()
                            $icon_other = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('mobile'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('facebook'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('line'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('twitter'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                         
                            $number_in_progress = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('in_progress')).'%'])->count();//->get()
                            $number_reported = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('reported')).'%'])->count();//->get()
                            $number_close = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('close')).'%'])->count();//->get()                                                                                                                                   ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                        } else {
                            $icon_mobile = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('mobile')).'%'])->count();//->get()
                            $icon_facebook = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('facebook')).'%'])->count();//->get()
                            $icon_line = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('line')).'%'])->count();//->get()
                            $icon_twitter = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('twitter')).'%'])->count();//->get()
                            $icon_website = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('website')).'%'])->count();//->get()
                            $icon_other = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('mobile'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('facebook'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('line'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('twitter'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                        
                            $number_in_progress = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('in_progress')).'%'])->count();//->get()
                            $number_reported = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('reported')).'%'])->count();//->get()
                            $number_close = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('close')).'%'])->count();//->get()      
                        }
                    }


                    $response = [
                        "icon_mobile" => $icon_mobile,
                        "icon_facebook" => $icon_facebook,
                        "icon_line" => $icon_line,
                        "icon_twitter" => $icon_twitter,
                        "icon_website" => $icon_website,
                        "icon_other" => $icon_other,
                        "number_in_progress" => @$number_in_progress,
                        "number_reported" => @$number_reported,
                        "number_close" => @$number_close,

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

    public function getDataLeakSocial(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $site_id_arr = @$get_role_custom_first['site_id_arr'];

                    // $site_code = $data['data']['site_code'];
                    // if($site_code) {
                    //     $SiteSettings = SiteSettings::where('code',$site_code)->first();
                    // }

                    if(@$get_role_custom_first['superadmin'] == 1) {
                    } else {
                            $Data_leak_social = Data_leak_social::where('deleted_at', null)->where('status',1)->get();
                           
                    }

                    $response = [
                        "Data_leak_social" => $Data_leak_social,
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


    public function activity_dataleak_modal(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $DataLeakSocialRef_code = $data['data']['DataLeakSocialRef_code'];
                    $site = $data['data']['site'];
                  

                    $user = User::where('id', $data['data']['user_id'])->first();

                    // $site_id = '';
            
                    // if($site_code) {
                    //     $site_id_m = SiteSettings::where('code',$site_code)->first();
                    //     $site_id = @$site_id_m->id;
                    // }


                    $DataLeakSocialRefs = DataLeakSocialRef::where('code',$DataLeakSocialRef_code)->first();
                    $ActivityHistory = Activity::select('activity.*','users.name as users_name')->where('activity.data_leak_socail_ref_id',$DataLeakSocialRefs->id)->whereNull('activity.deleted_at')->leftJoin('users', 'activity.user_id', '=', 'users.id')->orderBy('created_at','desc')->get();
                    // $DataLeakFeed = DataLeakFeed::where('id',$DataLeakSocialRefs->data_leak_feed_id)->first();
                    $response['DataLeakSocialRefs'] = $DataLeakSocialRefs;
                    $response['site'] = @$site;
                    $response['ActivityHistory'] = $ActivityHistory;
               
            

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

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function activity_history_reload(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $DataLeakSocialRef_code = $data['data']['DataLeakSocialRef_code'];
                    // $site = $data['data']['site'];
                  

                    $user = User::where('id', $data['data']['user_id'])->first();

                    // $site_id = '';
            
                    // if($site_code) {
                    //     $site_id_m = SiteSettings::where('code',$site_code)->first();
                    //     $site_id = @$site_id_m->id;
                    // }


                    $DataLeakSocialRefs = DataLeakSocialRef::where('id',$DataLeakSocialRef_code)->first();
                    $ActivityHistory = Activity::select('activity.*','users.name as users_name')->where('activity.data_leak_socail_ref_id',$DataLeakSocialRefs->id)->whereNull('activity.deleted_at')->leftJoin('users', 'activity.user_id', '=', 'users.id')->orderBy('created_at','desc')->get();
                    $html = '';
                    if(!empty($ActivityHistory)){
                        foreach ($ActivityHistory as $key => $value) {
                                $html_status_activity = '';
                                $activity_color = '';
                                $activity_name = '';
                                if($value->status_activity) {
                                    if($value->status_activity == 'in_progress') {
                                        $activity_color = '#FFC107';
                                        $activity_name = 'Progress';
                                    } else if ($value->status_activity == 'reported') {
                                        $activity_color = '#28A745';
                                        $activity_name = 'Reported';
                                    } else if ($value->status_activity == 'close') {
                                        $activity_color = '#DC3545';
                                        $activity_name = 'Close';
                                    }
                                    $html_status_activity = '<span class="badge" style="background-color: '.$activity_color.'; display: block;">'.$activity_name.'</span>';
                                }
                            $html .= '
                            <li class="work" id="list_activity_'.$value->code.'">
                                <input class="radio" id="work_'.$key.'" name="works" type="radio">
                                <div class="relative">
                                    <label for="work_'.$key.'" class="label_custom" style="font-weight: 900;">'.$value->title.'</label>
                                    <span class="date_custom" style="text-align:center;">'.$value->updated_at.$html_status_activity.'</span>
                                    <span class="circle_custom"></span>
                                </div>
                                <div class="content_custom">
                                    <p>
                                        '.$value->content.'
                                    </p>
                                </div>
                                <div>
                                    <strong>Post By</strong> '.$value->users_name;
                                    if($value->user_id == $user->id){
                                        $html .= '
                                        <span class="float-right">
                                            <a href="#gototop" class="btn btn-info btn-xs disable_atag" onclick="edit_activity( \''. $value->id .'\');">
                                                <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                                            </a>
                                            <a href="javascript:void(0)" class="btn btn-danger btn-xs disable_atag" onclick="delete_activity(\''.$value->id.'\',\''.$value->code.'\');"><i class="fas fa-trash-alt"></i></a>
                                        </span>';
                                    }
                                
                            $html .= '
                                </div>
                            </li>';
                        }
                    }else{
                        $html .= '<li class="list-group-item"> </li>';
                    }
               
                    $response = [
                        "html" => $html,
                        "ActivityHistory" => $ActivityHistory,
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

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function activity_get_edit_data(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code_activity = $data['data']['code_activity'];
                    // $site = $data['data']['site'];
                  

                    $user = User::where('id', $data['data']['user_id'])->first();

                    // $site_id = '';
            
                    // if($site_code) {
                    //     $site_id_m = SiteSettings::where('code',$site_code)->first();
                    //     $site_id = @$site_id_m->id;
                    // }


                    $Activity = Activity::where('id',$code_activity)->first()->toArray();
   
               
                    $response = [
                        "ActivityHistory" => $Activity,
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

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function activity_save(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    // $code_activity = $data['data']['code_activity'];
                    $code_edited_activity = $data['data']['code_edited_activity'];
                    $status_activity = $data['data']['status_activity'];
                    $content = $data['data']['content'];
                    $check_active = $data['data']['check_active'];
                    $id_DataLeakSocialRefs = $data['data']['id_DataLeakSocialRefs'];
                    $title = $data['data']['title'];
                    $site_code = $data['data']['site_code'];
                    // $site = $data['data']['site'];
                  
                    //--------------------------------//
                    $status_activity = $status_activity;
                    $content = @$content; //รับค่าจาก messageInput
                    if($content) {
                        $dom = new \domdocument();
                        if($dom->getelementsbytagname('img')){
                            $dom->loadHtml('<?xml encoding="UTF-8">'.$content,
                            LIBXML_HTML_NOIMPLIED |
                            LIBXML_HTML_NODEFDTD |
                            LIBXML_NOERROR |
                            LIBXML_NOWARNING 
                        );
                            //ดึงเอาส่วนที่เป็นรูปภาพมาจาก summernote
                            $images = $dom->getelementsbytagname('img');
                            //ลูปรูปภาพและทำการเข้ารหัสรูปภาพ
                            foreach($images as $k => $img){
                                $data_content = $img->getattribute('src');

                                //Link url
                                $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                                if(preg_match($reg_exUrl, $data_content, $url_image)) {
                                    $url = $url_image[0];
                                    $image = file_get_contents($url);
                                    if ($image !== false){
                                        $data_content = 'data:image/jpg;base64,'.base64_encode($image);
                                    }
                                }

                                $img_check_src = explode(";",$data_content);
                                if(@$img_check_src[1]) {
                                    list($type, $data_content) = explode(';', $data_content);
                                    list(, $data_content)= explode(',', $data_content);
                                    $data_content = base64_decode($data_content);
                                //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                                    $image_name= time().$k.'.png';
                                //อัพโหลดภาพไปยัง public
                                    $path = public_path('images/file_editor') .'/'. $image_name;
                                //ทำการอัพโหลดภาพ
                                    file_put_contents($path, $data_content);
                                    $img->removeattribute('src');
                                    $img->setattribute('src', config('app.URL_CENTER_PUBLISH').'/images/file_editor/'.$image_name);
                                } else {
            
                                }
                            }
                            $content = $dom->savehtml();
            
                        }
                    }
            
                    if($check_active=="1"){
                        $Activity = new Activity;
                        $Activity->code = generator_uuid();
                        $Activity->data_leak_socail_ref_id = $id_DataLeakSocialRefs;
                        $Activity->title = $title;
                        $Activity->content = $content;
                        $Activity->user_id = $data['data']['user_id'];
                        if($status_activity) {
                            $Activity->status_activity = $status_activity;
                        }
                        $Activity->save();
                    }else if($check_active=="2"){
                        $Activity = Activity::where('id',$code_edited_activity)->first();
                        $Activity->title = $title;
                        $Activity->content = $content;
                        if($status_activity) {
                            $Activity->status_activity = $status_activity;
                        }
                        $Activity->save();
                    }
                    $Activity_check = Activity::where('data_leak_socail_ref_id',$id_DataLeakSocialRefs)->where('status_activity','close')->first();

                    $DataLeakSocialRef = DataLeakSocialRef::where('id',$id_DataLeakSocialRefs)->first();
                    // if($DataLeakSocialRef->status_monitoring != 'close') {
                        if($Activity->status_activity == 'close') {
                            $DataLeakSocialRef->status_monitoring = 'close';
                            $DataLeakSocialRef->save();
                        } else if ($Activity->status_activity == 'in_progress') {//reported
                            if($Activity_check) {
                                $DataLeakSocialRef->status_monitoring = 'close';
                                $DataLeakSocialRef->save();
                            } else {
                                $DataLeakSocialRef->status_monitoring = 'in_progress';//reported
                                $DataLeakSocialRef->save();
                            }
                        } else if ($Activity->status_activity == 'in_progress') {
                            if($Activity_check) {
                                $DataLeakSocialRef->status_monitoring = 'close';
                                $DataLeakSocialRef->save();
                            } else {
                                $DataLeakSocialRef->status_monitoring = 'in_progress';
                                $DataLeakSocialRef->save();
                            }
                        }   
                    // }
            

                    //--------------------------------//
   
               
                    $response = [
                        "socail_ref_id" => $Activity->data_leak_socail_ref_id,
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

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function activity_delete(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    // $code_activity = $data['data']['code_activity'];
                    $code_activity = $data['data']['code_activity'];
                    // $site = $data['data']['site'];
                  
                    //--------------------------------//
                    $Activity = Activity::where('id',$code_activity)->first();
                    $Activity->delete();

                    $Activity_check = Activity::where('data_leak_socail_ref_id',$Activity->data_leak_socail_ref_id)->where('status_activity','close')->first();
                    $Activity_check_last = Activity::where('data_leak_socail_ref_id',$Activity->data_leak_socail_ref_id)->where('deleted_at',null)->orderBy('id','desc')->first();
                    $status_activity = $Activity_check_last->status_activity;

                    $DataLeakSocialRef = DataLeakSocialRef::where('id',$Activity->data_leak_socail_ref_id)->first();
                    // if($DataLeakSocialRef->status_monitoring != 'close') {
                        if($Activity_check_last) {
                            if($status_activity) {
                                $DataLeakSocialRef->status_monitoring = $status_activity;
                                $DataLeakSocialRef->save();
                            } else {
                                $DataLeakSocialRef->status_monitoring = 'in_progress';
                                $DataLeakSocialRef->save();
                            }
                        } else {
                            $DataLeakSocialRef->status_monitoring = 'in_progress';
                            $DataLeakSocialRef->save();
                        } 
                    // }
                    //--------------------------------//
   
               
                    $response = [
                        "Activity" => $Activity,
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

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function compromise_activity_dataleak_modal(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'compromised'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $DataLeakSocialRef_code = $data['data']['DataLeakSocialRef_code'];
                    $site = $data['data']['site'];
                  

                    $user = User::where('id', $data['data']['user_id'])->first();

                    // $site_id = '';
            
                    // if($site_code) {
                    //     $site_id_m = SiteSettings::where('code',$site_code)->first();
                    //     $site_id = @$site_id_m->id;
                    // }


                    $DataLeakSocialRefs = DataLeakSocialRef::where('code',$DataLeakSocialRef_code)->first();
                    $ActivityHistory = Activity::select('activity.*','users.name as users_name')->where('activity.data_leak_socail_ref_id',$DataLeakSocialRefs->id)->whereNull('activity.deleted_at')->leftJoin('users', 'activity.user_id', '=', 'users.id')->orderBy('created_at','desc')->get();
                    // $DataLeakFeed = DataLeakFeed::where('id',$DataLeakSocialRefs->data_leak_feed_id)->first();
                    $response['DataLeakSocialRefs'] = $DataLeakSocialRefs;
                    $response['site'] = @$site;
                    $response['ActivityHistory'] = $ActivityHistory;
               
            

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

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function compromise_activity_history_reload(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'compromised'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $DataLeakSocialRef_code = $data['data']['DataLeakSocialRef_code'];
                    // $site = $data['data']['site'];
                  

                    $user = User::where('id', $data['data']['user_id'])->first();

                    // $site_id = '';
            
                    // if($site_code) {
                    //     $site_id_m = SiteSettings::where('code',$site_code)->first();
                    //     $site_id = @$site_id_m->id;
                    // }


                    $DataLeakSocialRefs = DataLeakSocialRef::where('id',$DataLeakSocialRef_code)->first();
                    $ActivityHistory = Activity::select('activity.*','users.name as users_name')->where('activity.data_leak_socail_ref_id',$DataLeakSocialRefs->id)->whereNull('activity.deleted_at')->leftJoin('users', 'activity.user_id', '=', 'users.id')->orderBy('created_at','desc')->get();
                    $html = '';
                    if(!empty($ActivityHistory)){
                        foreach ($ActivityHistory as $key => $value) {
                                $html_status_activity = '';
                                $activity_color = '';
                                $activity_name = '';
                                if($value->status_activity) {
                                    if($value->status_activity == 'in_progress') {
                                        $activity_color = '#FFC107';
                                        $activity_name = 'Progress';
                                    } else if ($value->status_activity == 'reported') {
                                        $activity_color = '#28A745';
                                        $activity_name = 'Reported';
                                    } else if ($value->status_activity == 'close') {
                                        $activity_color = '#DC3545';
                                        $activity_name = 'Close';
                                    }
                                    $html_status_activity = '<span class="badge" style="background-color: '.$activity_color.'; display: block;">'.$activity_name.'</span>';
                                }
                            $html .= '
                            <li class="work" id="list_activity_'.$value->code.'">
                                <input class="radio" id="work_'.$key.'" name="works" type="radio">
                                <div class="relative">
                                    <label for="work_'.$key.'" class="label_custom" style="font-weight: 900;">'.$value->title.'</label>
                                    <span class="date_custom" style="text-align:center;">'.$value->updated_at.$html_status_activity.'</span>
                                    <span class="circle_custom"></span>
                                </div>
                                <div class="content_custom">
                                    <p>
                                        '.$value->content.'
                                    </p>
                                </div>
                                <div>
                                    <strong>Post By</strong> '.$value->users_name;
                                    if($value->user_id == $user->id){
                                        $html .= '
                                        <span class="float-right">
                                            <a href="#gototop" class="btn btn-info btn-xs disable_atag" onclick="edit_activity( \''. $value->id .'\');">
                                                <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                                            </a>
                                            <a href="javascript:void(0)" class="btn btn-danger btn-xs disable_atag" onclick="delete_activity(\''.$value->id.'\',\''.$value->code.'\');"><i class="fas fa-trash-alt"></i></a>
                                        </span>';
                                    }
                                
                            $html .= '
                                </div>
                            </li>';
                        }
                    }else{
                        $html .= '<li class="list-group-item"> </li>';
                    }
               
                    $response = [
                        "html" => $html,
                        "ActivityHistory" => $ActivityHistory,
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

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function compromise_activity_get_edit_data(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'compromised'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code_activity = $data['data']['code_activity'];
                    // $site = $data['data']['site'];
                  

                    $user = User::where('id', $data['data']['user_id'])->first();

                    // $site_id = '';
            
                    // if($site_code) {
                    //     $site_id_m = SiteSettings::where('code',$site_code)->first();
                    //     $site_id = @$site_id_m->id;
                    // }


                    $Activity = Activity::where('id',$code_activity)->first()->toArray();
   
               
                    $response = [
                        "ActivityHistory" => $Activity,
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

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function compromise_activity_save(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                if($data['data']['menu'] !== 'compromised'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    // $code_activity = $data['data']['code_activity'];
                    $code_edited_activity = $data['data']['code_edited_activity'];
                    $status_activity = $data['data']['status_activity'];
                    $content = $data['data']['content'];
                    $check_active = $data['data']['check_active'];
                    $id_DataLeakSocialRefs = $data['data']['id_DataLeakSocialRefs'];
                    $title = $data['data']['title'];
                    $site_code = $data['data']['site_code'];
                    // $site = $data['data']['site'];
                  
                    //--------------------------------//
                    $status_activity = $status_activity;
                    $content = @$content; //รับค่าจาก messageInput
                    if($content) {
                        $dom = new \domdocument();
                        if($dom->getelementsbytagname('img')){
                            $dom->loadHtml('<?xml encoding="UTF-8">'.$content,
                            LIBXML_HTML_NOIMPLIED |
                            LIBXML_HTML_NODEFDTD |
                            LIBXML_NOERROR |
                            LIBXML_NOWARNING 
                        );
                            //ดึงเอาส่วนที่เป็นรูปภาพมาจาก summernote
                            $images = $dom->getelementsbytagname('img');
                            //ลูปรูปภาพและทำการเข้ารหัสรูปภาพ
                            foreach($images as $k => $img){
                                $data_content = $img->getattribute('src');

                                //Link url
                                $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                                if(preg_match($reg_exUrl, $data_content, $url_image)) {
                                    $url = $url_image[0];
                                    $image = file_get_contents($url);
                                    if ($image !== false){
                                        $data_content = 'data:image/jpg;base64,'.base64_encode($image);
                                    }
                                }

                                $img_check_src = explode(";",$data_content);
                                if(@$img_check_src[1]) {
                                    list($type, $data_content) = explode(';', $data_content);
                                    list(, $data_content)= explode(',', $data_content);
                                    $data_content = base64_decode($data_content);
                                //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                                    $image_name= time().$k.'.png';
                                //อัพโหลดภาพไปยัง public
                                    $path = public_path('images/file_editor') .'/'. $image_name;
                                //ทำการอัพโหลดภาพ
                                    file_put_contents($path, $data_content);
                                    $img->removeattribute('src');
                                    $img->setattribute('src', config('app.URL_CENTER_PUBLISH').'/images/file_editor/'.$image_name);
                                } else {
            
                                }
                            }
                            $content = $dom->savehtml();
            
                        }
                    }
            
                    if($check_active=="1"){
                        $Activity = new Activity;
                        $Activity->code = generator_uuid();
                        $Activity->data_leak_socail_ref_id = $id_DataLeakSocialRefs;
                        $Activity->title = $title;
                        $Activity->content = $content;
                        $Activity->user_id = $data['data']['user_id'];
                        if($status_activity) {
                            $Activity->status_activity = $status_activity;
                        }
                        $Activity->save();
                    }else if($check_active=="2"){
                        $Activity = Activity::where('id',$code_edited_activity)->first();
                        $Activity->title = $title;
                        $Activity->content = $content;
                        if($status_activity) {
                            $Activity->status_activity = $status_activity;
                        }
                        $Activity->save();
                    }
            

                    //--------------------------------//
   
               
                    $response = [
                        "socail_ref_id" => $Activity->data_leak_socail_ref_id,
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

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function compromise_activity_delete(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                if($data['data']['menu'] !== 'compromised'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    // $code_activity = $data['data']['code_activity'];
                    $code_activity = $data['data']['code_activity'];
                    // $site = $data['data']['site'];
                  
                    //--------------------------------//
                    $Activity = Activity::where('id',$code_activity)->first();
                    $Activity->delete();
                    //--------------------------------//
   
               
                    $response = [
                        "Activity" => $Activity,
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

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    
}
