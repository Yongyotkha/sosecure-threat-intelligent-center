<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\BrandAbuseFeed;
use App\BrandAbuseFeedTemp;
use App\BrandAbuseSocial;
use App\BrandAbuseSocialRef;
use App\BrandAbuseSocialRefTemp;

use App\DataLeakFeed;
use App\DataLeakSocialRef;
use Modules\Social\Entities\Data_leak_social;
use Illuminate\Support\Facades\DB;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\Users\Entities\UserSite;
use Yajra\DataTables\DataTables;
use Modules\Users\Entities\User;
use Modules\SiteSettings\Entities\Activity;

class ApiBrandabuseController extends ApiController
{

    public function brand_abuse_count_val(Request $request) // count_val
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
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
                        $model = BrandAbuseSocialRef::where('deleted_at', null)
                        ->where('status',1)
                        ->whereIn('feel_type', ['social', 'darkweb_public'])->with('get_site')->with('get_brand_abuse_feed_one');
                        $countGroupBy = BrandAbuseSocialRef::where('deleted_at', null)
                        ->where('status',1)
                        ->whereIn('feel_type', ['social', 'darkweb_public'])->with('get_site')->with('get_brand_abuse_feed_one');


                        if ($title) {
                            $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($title) {
                                $query->where('keyword', 'LIKE', '%' . $title . '%')
                                    ->orWhere('feedcontent', 'LIKE', '%' . $title . '%');
                            });
            
                            $countGroupBy->whereHas('get_brand_abuse_feed_one', function ($query) use ($title) {
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
                            
                                $countGroupBy = $countGroupBy->whereHas('get_brand_abuse_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
                                    $qq->whereBetween('feedtimepost', array($date_start_datetime_format, $date_end_datetime_format));
                                });
            
                                $model = $model->whereHas('get_brand_abuse_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
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

                        $brand_abuse_feed_all = $model->count();
                        $countGroupBy = $countGroupBy->select( 'feel_type',DB::raw('count(*) as total'))->groupBy('feel_type')->get();
                        $model = $model->with('get_brand_abuse_feed_one')->orderBy('id','desc')->paginate(PAGINATE_NUM);
                    }else{
                        $brand_abuse_feed_all = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public']);
                        $news = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public']);//->get()
                        $countGroupBy = BrandAbuseSocialRef::select( 'feel_type',DB::raw('count(*) as total'))
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
                            $brand_abuse_feed_all = $brand_abuse_feed_all->whereIn('site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_support'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            $brand_abuse_feed_all = $brand_abuse_feed_all->whereIn('site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_admin'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            $brand_abuse_feed_all = $brand_abuse_feed_all->whereIn('site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_client'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            $brand_abuse_feed_all = $brand_abuse_feed_all->whereIn('site_id', $site_id_arr);
            
                        }
                        $news = $news->with('get_brand_abuse_feed_one')->orderBy('id','desc')->paginate(PAGINATE_NUM);
                        $brand_abuse_feed_all = $brand_abuse_feed_all->count();
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
                        "count" => $brand_abuse_feed_all,
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

    public function data_leak_view(Request $request)
    {
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

    public function socialdatas_all_site()
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $SiteSettings = $data['data']['SiteSettings'];
                    $page = $data['data']['page'];

                    $response = [];
                    $response['source'] = BrandAbuseSocial::where("status", '=', 1)->get();
                    $response['SiteSettings'] = $SiteSettings;
                    $response['page'] = $page;

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

    public function socialdatas_all_site_tb(Request $request) // socialdatas_all_site_tb
    { 
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
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


                    $model = BrandAbuseSocialRef::where('deleted_at', null)
                    ->where('status',1)
                    ->whereHas('get_brand_abuse_feed_one', function ($query) {
                        $query->whereIn('feel_type', ['social','darkweb_public']);
                    })
                    ->with('get_site')
                    ->with('get_brand_abuse_feed_one');
        

                    $BrandAbuseSocialRef_data = BrandAbuseSocialRef::join('brand_abuse_feed', 'brand_abuse_socail_ref.brand_abuse_feed_id', '=', 'brand_abuse_feed.id')
                    ->whereIn('brand_abuse_feed.feel_type', ['social','darkweb_public'])->join('site','site.id','brand_abuse_socail_ref.site_id')
                    ->select('brand_abuse_socail_ref.*','brand_abuse_feed.*','site.name as site_name','brand_abuse_socail_ref.code as code_data');


                    if ($search_val == 1) {
            
                    

                        $BrandAbuseFeed_Data =   BrandAbuseFeed::where('deleted_at', null)->where('status','1')->whereIn('feel_type', ['social','darkweb_public']);
            
            
                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if(@$get_role_custom_first['superadmin'] == 1) {

                        }else if(@$get_role_custom_first['client'] == 1) {
                            $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);
                            $BrandAbuseSocialRef_data->whereIn('brand_abuse_socail_ref.site_id', $site_id_arr)->where('brand_abuse_feed.status', 1);
                            $BrandAbuseFeed_Data->whereIn('site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_support'] == 1) {
                            $model = $model->whereIn('site_id', $site_id_arr);
                            $BrandAbuseSocialRef_data->whereIn('brand_abuse_socail_ref.site_id', $site_id_arr);
                            $BrandAbuseFeed_Data->whereIn('site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_admin'] == 1) {
                            $model = $model->whereIn('site_id', $site_id_arr);
                            $BrandAbuseSocialRef_data->whereIn('brand_abuse_socail_ref.site_id', $site_id_arr);
                            $BrandAbuseFeed_Data->whereIn('site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_client'] == 1) {
                            $model = $model->whereIn('site_id', $site_id_arr)->where('brand_abuse_feed.status', 1);
                            $BrandAbuseSocialRef_data->whereIn('brand_abuse_socail_ref.site_id', $site_id_arr)->where('fx_brand_abuse_feed.status', 1);
                            $BrandAbuseFeed_Data->whereIn('site_id', $site_id_arr);
                        }

                        if ($keywords) {
                        
                         /*   $BrandAbuseFeed_data  = $BrandAbuseFeed_Data->get();
                            foreach ($BrandAbuseFeed_data as $value_data) {
                                  $value_data->feedcontent_decode = html_entity_decode($value_data->feedcontent);
                            }
                            $BrandAbuseFeed_data_id = array();
                            array_push($BrandAbuseFeed_data_id, 0);
                            foreach($BrandAbuseFeed_data as $a) {
                                if(strpos(strtoupper($a->feedcontent_decode), strtoupper($keywords)) !== false) {
                                    array_push($BrandAbuseFeed_data_id, $a->id);
                                } 
                            }
                            */

                            $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($keywords) {
                                $query->where('keyword', 'LIKE', '%' . $keywords . '%');
                                    //->orWhere('feedcontent', 'LIKE', '%' . $keywords . '%');
                                   
                            });
                            $BrandAbuseSocialRef_data->whereRaw('(LOWER(fx_brand_abuse_feed.keyword) LIKE ? or LOWER(fnStripTags(entity_decode(fx_brand_abuse_feed.feedcontent))) LIKE ? )', array([trim(strtolower('%' .$keywords.'%'))],[trim(strtolower('%' .$keywords.'%'))]));
                         //   $BrandAbuseSocialRef_data->orWhereIn('brand_abuse_feed.id', $BrandAbuseFeed_data_id);
                        }
            
                
            
                        if ($check_type) {
                        
                            $type = $check_type;
                            $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($type) {
                                $query->where('feel_type', 'LIKE', '%' . $type . '%');
                            });
                            $BrandAbuseSocialRef_data->where('brand_abuse_feed.feel_type', 'LIKE', '%' . $type . '%');
                        }
                  
                        if ($check_social && $check_type =="social") {
              
                            $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($check_social) {
                                if($check_social =="other"){
                                    $query->whereNotIn('keyword', ['Mobile','Facebook','Line','Twitter','Website']);
                                }else{
                                    $query->where('keyword', $check_social);
                                }
                              
                            });
                            if($check_social =="other"){
                                $BrandAbuseSocialRef_data->whereNotIn('brand_abuse_feed.keyword', ['Mobile','Facebook','Line','Twitter','Website']);
                            }else{
                                    $BrandAbuseSocialRef_data->where('brand_abuse_feed.keyword', $check_social);
            
                            }
            
                        }
                        if ($check_serverity) {
                            $model = $model->where('serverity', $check_serverity);
                            $BrandAbuseSocialRef_data->where('brand_abuse_socail_ref.serverity', 'LIKE', '%' . $check_serverity . '%');
                            // });
                        }
            
                        if ($check_monitoring) {
                            $model = $model->where('status_monitoring', $check_monitoring);
                            $BrandAbuseSocialRef_data->where('brand_abuse_socail_ref.status_monitoring', 'LIKE', '%' . $check_monitoring . '%');
                            // });
                        }
                        // if ($source) {
            
                        //     $source = $source;
                        //     $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($source) {
                        //         $query->where('sourceid', 'LIKE', '%' . $source . '%');
                        //     });
            
                        // }
            
                      //  if ($isDateSearch == 1) {
                            $date_start = $startDate;
                            $date_end = $endDate;
            
                            $date_start_explode = explode(" ", $date_start);
                            $date_start_date = @$date_start_explode[0];
                            $date_start_time = @$date_start_explode[1] . ' ' . @$date_start_explode[2];
            
                            $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
            
                            $date_start_time_time = date("H:i", strtotime($date_start_time));
                           // $date_start_datetime_format = $date_start_date_format . ' ' . $date_start_time_time . ':00';
                           $date_start_datetime_format = $date_start_date_format . ' '  . '00:00:01';
            
                            $date_end_explode = explode(" ", $date_end);
                            $date_end_date = @$date_end_explode[0];
                            $date_end_time = @$date_end_explode[1] . ' ' . @$date_end_explode[2];
                            // dd($date_end_time);
                            $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                            $date_end_time_time = date("H:i", strtotime($date_end_time));
                           // $date_end_datetime_format = $date_end_date_format . ' ' . $date_end_time_time . ':00';
                            $date_end_datetime_format = $date_end_date_format . ' '  . '23:59:59';
            
                            $source = $source;
                            $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($date_start_date_format, $date_end_date_format) {
                                $query->whereBetween('feedtimepost', array($date_start_date_format, $date_end_date_format));
                            });
                            $BrandAbuseSocialRef_data->whereBetween('brand_abuse_feed.feedtimepost',array($date_start_datetime_format, $date_end_datetime_format));
                      //  }

                       // $model->orderBy('created_at','desc')->get();
                    } else {

                        if ($click_type2) {
                            $keywords = $click_type2;
                            $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($keywords) {
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
                                $BrandAbuseSocialRef_data->whereRaw('LOWER(fx_brand_abuse_feed.keyword) != ? ',[trim(strtolower('mobile'))])
                                                        ->whereRaw('LOWER(fx_brand_abuse_feed.keyword) != ? ',[trim(strtolower('facebook'))])
                                                        ->whereRaw('LOWER(fx_brand_abuse_feed.keyword) != ? ',[trim(strtolower('line'))])
                                                        ->whereRaw('LOWER(fx_brand_abuse_feed.keyword) != ? ',[trim(strtolower('twitter'))])
                                                        ->whereRaw('LOWER(fx_brand_abuse_feed.keyword) != ? ',[trim(strtolower('website'))]);
                            } else {
                                if($keywords == 'in_progress') {
                                    $BrandAbuseSocialRef_data->where('brand_abuse_socail_ref.status_monitoring', 'LIKE', '%' . $keywords . '%');
                                } else if($keywords == 'reported') {
                                    $BrandAbuseSocialRef_data->where('brand_abuse_socail_ref.status_monitoring', 'LIKE', '%' . $keywords . '%');
                                } else if($keywords == 'close') {
                                    $BrandAbuseSocialRef_data->where('brand_abuse_socail_ref.status_monitoring', 'LIKE', '%' . $keywords . '%');
                                } else {
                                    $BrandAbuseSocialRef_data->where('brand_abuse_feed.keyword', 'LIKE', '%' . $keywords . '%');
                                }
                            }
                        }

                        
                        if($click_type) {
    
                            $model = $model-> where('feel_type', '=' ,$click_type);
                            $BrandAbuseSocialRef_data->where('brand_abuse_socail_ref.feel_type',$click_type);
            
                        }
            
                        if ($click_key) {
                            $model = $model->where('keyword', $click_key);
                            $BrandAbuseSocialRef_data->where('LOWER(`brand_abuse_feed.keyword`)','LIKE',[trim(strtolower($click_key))]);
                        }

                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if(@$get_role_custom_first['superadmin'] == 1) {

                        }else if(@$get_role_custom_first['client'] == 1) {
                            $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);
                            $BrandAbuseSocialRef_data->whereIn('brand_abuse_socail_ref.site_id', $site_id_arr)->where('brand_abuse_feed.status', 1);
            
                        }else if(@$get_role_custom_first['site_support'] == 1) {
                            $model = $model->whereIn('site_id', $site_id_arr);
                            $BrandAbuseSocialRef_data->whereIn('brand_abuse_socail_ref.site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_admin'] == 1) {
                            $model = $model->whereIn('site_id', $site_id_arr);
                            $BrandAbuseSocialRef_data->whereIn('brand_abuse_socail_ref.site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_client'] == 1) {
                            $model = $model->whereIn('site_id', $site_id_arr)->where('brand_abuse_feed.status', 1);
                            $BrandAbuseSocialRef_data->whereIn('brand_abuse_socail_ref.site_id', $site_id_arr)->where('fx_brand_abuse_feed.status', 1);
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
                            $BrandAbuseSocialRef_data->orderBy('brand_abuse_socail_ref.site_id', $column_dir);
                        }
                        else if($column_order == "8"){
                            $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($column_dir) {
                                      $query->orderBy('feedtimepost',$column_dir);
                            });
                            $BrandAbuseSocialRef_data->orderBy('brand_abuse_feed.feedtimepost', $column_dir);
                        }
                        else if($column_order == "7"){
            
                            $model->orderBy('status_monitoring',$column_dir);
                            $BrandAbuseSocialRef_data->orderBy('brand_abuse_socail_ref.status_monitoring', $column_dir);
                        }
                        else if($column_order == "6"){
            
                            $model->orderBy('serverity',$column_dir);
                            $BrandAbuseSocialRef_data->orderBy('brand_abuse_socail_ref.serverity', $column_dir);
                        }
                        else if($column_order == "5"){
            
                            $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($column_dir) {
                                $query->orderBy('feedcontent',$column_dir);
                            });
                            $BrandAbuseSocialRef_data->orderBy('brand_abuse_feed.feedcontent', $column_dir);
                        }
                        else if($column_order == "4"){
            
                            $model->orderBy('keyword',$column_dir);
                            $BrandAbuseSocialRef_data->orderBy('brand_abuse_feed.keyword', $column_dir);
                        }
                        else if($column_order == "3"){
            
                            $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($column_dir) {
                                $query->orderBy('source_name',$column_dir);
                            });
                            $BrandAbuseSocialRef_data->orderBy('brand_abuse_feed.source_name', $column_dir);
                        }
                        else if($column_order == "2"){
            
                            $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($column_dir) {
                                $query->orderBy('feel_type',$column_dir);
                            });
                            $BrandAbuseSocialRef_data->orderBy('brand_abuse_socail_ref.feel_type', $column_dir);
                        }
                        else if($column_order == "1"){
            
                            $model->whereHas('get_site', function ($query) use ($column_dir) {
                                $query->orderBy('name',$column_dir);
                            });
                            $BrandAbuseSocialRef_data->orderBy('site.name', $column_dir);
                       
                        }else{
                            $model->orderBy('created_at', 'desc');
                            $BrandAbuseSocialRef_data->orderBy('brand_abuse_socail_ref.created_at', $column_dir);
                        }
                    }else{
                        $model->orderBy('created_at', 'desc');
                        $BrandAbuseSocialRef_data->orderBy('brand_abuse_socail_ref.created_at', $column_dir);
                    }
                  //  $model->where('status_monitoring', 'in_progress');
                 //   $res = DataTables::of($model)->toJson(); 
                 //  $res =    $data['data']['order_column'];
                   $data_count = $BrandAbuseSocialRef_data->count();

                    $response = [
                        "recordsFiltered_count"=> $data_count,
                        "recordsTotal_count" => $data_count,
                        "data" => DataTables::of($BrandAbuseSocialRef_data->skip($data['data']['start'])->take($data['data']['length'])->get())->rawColumns(['feedcontent','get_brand_abuse_feed_one.feedcontent'])->toJson(),
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

    public function add_brandabuse(Request $request)
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $keyword = $data['data']['keyword'];
                    $other = $data['data']['other'];
                    $type = $data['data']['type'];
                    // $content = $data['data']['content'];
                    $source = $data['data']['source'];
                    $sites = $data['data']['site'];
                    $monitoring = $data['data']['monitoring'];
                    $serverity = $data['data']['serverity'];
                    $sent_mail = $data['data']['sent_mail'];
                    $site_code = $data['data']['site_code'];

                    // $keyword = @$request->keyword;
                    // $other = @$request->other;

                    if($keyword == 'Other') {
                        $keyword_i = @$other;
                    } else {
                        $keyword_i = @$keyword;
                    }
            
                    $BrandAbuseFeed = new BrandAbuseFeed();
                    $BrandAbuseFeed->code = generator_uuid();
                    $BrandAbuseFeed->feel_type = @$type;
                    // $BrandAbuseFeed->feedcontent = @$request->content;

                    $BrandAbuseFeed->keyword = @$keyword_i;

                    $BrandAbuseFeed->source_name = @$source;
                    $BrandAbuseFeed->feedtimepost = Carbon::now();
                    $BrandAbuseFeed->status = 1;
                    $content = @$_POST['content']; //รับค่าจาก messageInput
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
                                $data = $img->getattribute('src');

                                //Link url
                                $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                                if(preg_match($reg_exUrl, $data, $url_image)) {
                                    $url = $url_image[0];
                                    $image = file_get_contents($url);
                                    if ($image !== false){
                                        $data = 'data:image/jpg;base64,'.base64_encode($image);
                                    }
                                }

                                $img_check_src = explode(";",$data);
                                if(@$img_check_src[1]) {
                                    list($type, $data) = explode(';', $data);
                                    list(, $data)= explode(',', $data);
                                    $data = base64_decode($data);
                                //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                                    $image_name= time().$k.'.png';
                                //อัพโหลดภาพไปยัง public
                                    $path = public_path('images/file_editor') .'/'. $image_name;
                                //ทำการอัพโหลดภาพ
                                    file_put_contents($path, $data);
                                    $img->removeattribute('src');
                                    $img->setattribute('src', config('app.URL_CENTER_PUBLISH').'/images/file_editor/'.$image_name);
                                } else {

                                }
                            }
                            $content = $dom->savehtml();

                        }
                    }
                    $BrandAbuseFeed->feedcontent = $content;
                    $BrandAbuseFeed->save();
                    $BrandAbuseFeed_send_mail[] = $BrandAbuseFeed;

                    if($sites){
                        foreach($sites as $site){
                            $SiteSettings = SiteSettings::where('code', $site)->first();
                            $BrandAbuseSocialRefs = new BrandAbuseSocialRef;
                            $BrandAbuseSocialRefs->code = generator_uuid();
                            $BrandAbuseSocialRefs->site_id = $SiteSettings->id;
                            $BrandAbuseSocialRefs->brand_abuse_feed_id = $BrandAbuseFeed->id;
                            $BrandAbuseSocialRefs->keyword = $BrandAbuseFeed->keyword;
                            $BrandAbuseSocialRefs->feel_type = $BrandAbuseFeed->feel_type;
                            $BrandAbuseSocialRefs->status_monitoring = @$monitoring;
                            $BrandAbuseSocialRefs->serverity = @$serverity;
                            $BrandAbuseSocialRefs->status = 1;
                            $BrandAbuseSocialRefs->save();
                            if ($sent_mail == true) {
                                $site_email_alert = site_config_email_alert::where("site_id", $SiteSettings->id)->get();
                                if ($site_email_alert) {
                                    $email_site_a = [];
                                    foreach ($site_email_alert as $site_email_alert_val) {
                                        $email_site_a[] = $site_email_alert_val->email;
                                    }
                                    $email_site_alert = array_unique($email_site_a);
                                    foreach($email_site_alert as $email){
                                        Mail::to($email)->send(new CompromisedMail($BrandAbuseFeed_send_mail, 'brand_abuse'));
                                        if( count(Mail::failures()) == 0 ) {
                                            LogEmail::Create([
                                                'to' => $email,
                                                'status' => 'Success',
                                                'subject' => 'brand_abuse'
                                            ]);
                                        }
                                    }
                                    if( count(Mail::failures()) > 0 ) {
                                        foreach(Mail::failures() as $email_address) {
                                            LogEmail::Create([
                                                'to' => $email_address,
                                                'status' => 'Fail',
                                                'subject' => 'brand_abuse'
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                        $site = route('brandabuse.index_all_site');
                    }else{
                        $SiteSettings = SiteSettings::where('code', @$site_code)->first();
                        $BrandAbuseSocialRefs = new BrandAbuseSocialRef;
                        $BrandAbuseSocialRefs->code = generator_uuid();
                        $BrandAbuseSocialRefs->site_id = $SiteSettings->id;
                        $BrandAbuseSocialRefs->brand_abuse_feed_id = $BrandAbuseFeed->id;
                        $BrandAbuseSocialRefs->keyword = $BrandAbuseFeed->keyword;
                        $BrandAbuseSocialRefs->feel_type = $BrandAbuseFeed->feel_type;
                        $BrandAbuseSocialRefs->status_monitoring = @$monitoring;
                        $BrandAbuseSocialRefs->serverity = @$serverity;
                        $BrandAbuseSocialRefs->status = 1;
                        $BrandAbuseSocialRefs->save();
                        $site = route('socialdatas.index', ['id' => @$site_code]);
                        if ($sent_mail == true) {
                            $site_email_alert = site_config_email_alert::where("site_id", $SiteSettings->id)->get();
                            if ($site_email_alert) {
                                $email_site_a = [];
                                foreach ($site_email_alert as $site_email_alert_val) {
                                    $email_site_a[] = $site_email_alert_val->email;
                                }
                                $email_site_alert = array_unique($email_site_a);
                                foreach($email_site_alert as $email){
                                    Mail::to($email)->send(new CompromisedMail($BrandAbuseFeed_send_mail, 'brand_abuse'));
                                    if( count(Mail::failures()) == 0 ) {
                                        LogEmail::Create([
                                            'to' => $email,
                                            'status' => 'Success',
                                            'subject' => 'brand_abuse'
                                        ]);
                                    }
                                }
                                if( count(Mail::failures()) > 0 ) {
                                    foreach(Mail::failures() as $email_address) {
                                        LogEmail::Create([
                                            'to' => $email_address,
                                            'status' => 'Fail',
                                            'subject' => 'brand_abuse'
                                        ]);
                                    }
                                }
                            }
                        }
                    }

                    $response = [
                        'message' => langapp('changes_saved_successful'),
                        'redirect' => $site,
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

    public function edit_brandabuse_modal($code,Request $request){

        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code = $data['data']['code'];
                    $site = $data['data']['user_id'];

                    $BrandAbuseSocialRefs = BrandAbuseSocialRef::where('code',$code)->first();
                    $BrandAbuseFeed = BrandAbuseFeed::where('id',$BrandAbuseSocialRefs->brand_abuse_feed_id)->first();

                    $response = [];
                    $response['BrandAbuseFeed'] = $BrandAbuseFeed;
                    $response['BrandAbuseSocialRefs'] = $BrandAbuseSocialRefs;

                    $response['site'] = @$site;

                    // return view('brandabuse::modal.edit_brandabuse')->with($response);

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

    public function edit_brandabuse(Request $request){

        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $id_BrandAbuseFeed = $data['data']['id_BrandAbuseFeed'];
                    $type = $data['data']['type'];
                    // $content = $data['data']['content'];
                    $other = $data['data']['other'];
                    $keyword = $data['data']['keyword'];
                    $source = $data['data']['source'];
                    $sent_mail = $data['data']['sent_mail'];
                    $monitoring = $data['data']['monitoring'];
                    $serverity = $data['data']['serverity'];
                    $site_code = $data['data']['site_code'];

                    $BrandAbuseFeed = BrandAbuseFeed::where('id',@$id_BrandAbuseFeed)->first();
                    $BrandAbuseFeed->feel_type = @$type;
                    // $BrandAbuseFeed->feedcontent = @$content;
                    if(@$other){
                        $BrandAbuseFeed->keyword = @$other;
                    }else{
                        $BrandAbuseFeed->keyword = @$keyword;
                    }
                    
                    $BrandAbuseFeed->source_name = @$source;
                    if ($sent_mail == true) {
                        $BrandAbuseFeed->feedtimepost = Carbon::now();
                    }
                    $content = @$_POST['content']; //รับค่าจาก messageInput
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
                                $data = $img->getattribute('src');

                                //Link url
                                $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                                if(preg_match($reg_exUrl, $data, $url_image)) {
                                    $url = $url_image[0];
                                    $image = file_get_contents($url);
                                    if ($image !== false){
                                        $data = 'data:image/jpg;base64,'.base64_encode($image);
                                    }
                                }

                                $img_check_src = explode(";",$data);
                                if(@$img_check_src[1]) {
                                    list($type, $data) = explode(';', $data);
                                    list(, $data)= explode(',', $data);
                                    $data = base64_decode($data);
                                //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                                    $image_name= time().$k.'.png';
                                //อัพโหลดภาพไปยัง public
                                    $path = public_path('images/file_editor') .'/'. $image_name;
                                //ทำการอัพโหลดภาพ
                                    file_put_contents($path, $data);
                                    $img->removeattribute('src');
                                    $img->setattribute('src', config('app.URL_CENTER_PUBLISH').'/images/file_editor/'.$image_name);
                                } else {

                                }
                            }
                            $content = $dom->savehtml();

                        }
                    }
                    $BrandAbuseFeed->feedcontent = $content;        
                    $BrandAbuseFeed->save();
                    $BrandAbuseFeed_send_mail[] = $BrandAbuseFeed;

                    $BrandAbuseSocialRefs = BrandAbuseSocialRef::where('brand_abuse_feed_id',$BrandAbuseFeed->id)->get();
                    if($BrandAbuseSocialRefs){
                        foreach($BrandAbuseSocialRefs as $BrandAbuseSocialRefs){
                            $BrandAbuseSocialRefs->keyword = $BrandAbuseFeed->keyword;
                            $BrandAbuseSocialRefs->feel_type = $BrandAbuseFeed->feel_type;
                            $BrandAbuseSocialRefs->status_monitoring = @$monitoring;
                            $BrandAbuseSocialRefs->serverity = @$serverity;
                            $BrandAbuseSocialRefs->save();
                            if ($sent_mail == true) {
                                $site_email_alert = site_config_email_alert::where("site_id", $BrandAbuseSocialRefs->site_id)->get();
                                if ($site_email_alert) {
                                    $email_site_a = [];
                                    foreach ($site_email_alert as $site_email_alert_val) {
                                        $email_site_a[] = $site_email_alert_val->email;
                                    }
                                    $email_site_alert = array_unique($email_site_a);
                                    foreach($email_site_alert as $email){
                                        Mail::to($email)->send(new CompromisedMail($BrandAbuseFeed_send_mail, 'brand_abuse'));
                                        if( count(Mail::failures()) == 0 ) {
                                            LogEmail::Create([
                                                'to' => $email,
                                                'status' => 'Success',
                                                'subject' => 'brand_abuse'
                                            ]);
                                        }
                                    }
                                    if( count(Mail::failures()) > 0 ) {
                                        foreach(Mail::failures() as $email_address) {
                                            LogEmail::Create([
                                                'to' => $email_address,
                                                'status' => 'Fail',
                                                'subject' => 'brand_abuse'
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                        
                    }

                    if($site_code){
                        $site = route('socialdatas.index', ['id' => @$site_code]);
                    }else{
                        $site = route('brandabuse.index_all_site');
                    }

                    $response = [
                        'message' => langapp('changes_saved_successful'),
                        'redirect' => $site
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

    public function change_status_brandabusedata(Request $request)
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code = $data['data']['code'];
                    $status = $data['data']['status'];

                    $BrandAbuseSocialRef = BrandAbuseSocialRef::where('id', $code)->first();
                    $BrandAbuseSocialRef->status = $status;
                    $BrandAbuseSocialRef->save();

                    $response = [
                        'message' => langapp('changes_saved_successful'),
                        'redirect' => route('brandabuse.index_all_site')
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

    public function delete_brandabusedata(Request $request)
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code = $data['data']['code'];

                    // dd($code);
                    $BrandAbuseSocialRef = BrandAbuseSocialRef::where('code', $code)->first();
                    $BrandAbuseFeedTemp = BrandAbuseFeedTemp::where('id', $BrandAbuseSocialRef->temp_id)->first();
                    if($BrandAbuseFeedTemp){
                        $BrandAbuseFeeds = BrandAbuseFeed::where('temp_id', $BrandAbuseFeedTemp->id)->get();
                        if($BrandAbuseFeeds){
                            foreach($BrandAbuseFeeds as $BrandAbuseFeed){
                                $leak_socail_ref_temp = leak_socail_ref_temp::where('brand_abuse_feed_id', $BrandAbuseFeedTemp->id)->first();
                                $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $BrandAbuseFeed->id)->first();
                                if($transaction_client_leak_feed){
                                    $transaction_client_leak_feed -> transaction_mode = 'delete';
                                    $transaction_client_leak_feed -> transaction_data_status = 1;
                                    $transaction_client_leak_feed -> status = 1;
                                    $transaction_client_leak_feed -> save();
                                }else{
                                    $transaction_client_leak_feed = new transaction_client_leak_feed();
                                    $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                                    $transaction_client_leak_feed -> transaction_id = $BrandAbuseFeed->id;
                                    $transaction_client_leak_feed -> transaction_mode = 'delete';
                                    $transaction_client_leak_feed -> transaction_data_status = 1;
                                    $transaction_client_leak_feed -> status = 1;
                                    $transaction_client_leak_feed -> save();
                                }

                                $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $BrandAbuseSocialRef->id)->first();
                                if($transaction_client_leak_social_ref){
                                    $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                                    $transaction_client_leak_social_ref -> transaction_data_status = 1;
                                    $transaction_client_leak_social_ref -> status = 1;
                                    $transaction_client_leak_social_ref -> save();
                                }else{
                                    $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                                    $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                                    $transaction_client_leak_social_ref -> transaction_id = $BrandAbuseSocialRef->id;
                                    $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                                    $transaction_client_leak_social_ref -> transaction_data_status = 1;
                                    $transaction_client_leak_social_ref -> status = 1;
                                    $transaction_client_leak_social_ref -> save();
                                }
                            }
                        }
                        BrandAbuseFeed::where('temp_id', $BrandAbuseFeedTemp->id)->delete();
                        $BrandAbuseSocialRef->delete();
                        $BrandAbuseFeedTemp->approve = 0;
                        $BrandAbuseFeedTemp->save();
                    }else{
                        $BrandAbuseSocialRef->delete();
                    }

                    $response  = [
                        'message' => langapp('changes_saved_successful'),
                        'redirect' => route('brandabuse.index_all_site'),
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

    public function change_delete_brandabusedata(Request $request)
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $ids = $data['data']['ids'];

                    foreach ($ids as $social_id) {
                        // $BrandAbuseSocialRef = BrandAbuseSocialRef::where('id', (int)$social_id)->first();
                        $BrandAbuseSocialRef = DB::table('brand_abuse_socail_ref')->where('id', (int)$social_id)->first();
                        $BrandAbuseFeedTemp = BrandAbuseFeedTemp::where('id', @$BrandAbuseSocialRef->temp_id)->first();
                        if($BrandAbuseFeedTemp){
                            $BrandAbuseFeeds = BrandAbuseFeed::where('temp_id', $BrandAbuseFeedTemp->id)->get();
                            if($BrandAbuseFeeds){
                                foreach($BrandAbuseFeeds as $BrandAbuseFeed){
                                    $leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $BrandAbuseFeedTemp->id)->first();
                                    $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $BrandAbuseFeed->id)->first();
                                    if($transaction_client_leak_feed){
                                        $transaction_client_leak_feed -> transaction_mode = 'delete';
                                        $transaction_client_leak_feed -> transaction_data_status = 1;
                                        $transaction_client_leak_feed -> status = 1;
                                        $transaction_client_leak_feed -> save();
                                    }else{
                                        $transaction_client_leak_feed = new transaction_client_leak_feed();
                                        $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                                        $transaction_client_leak_feed -> transaction_id = $BrandAbuseFeed->id;
                                        $transaction_client_leak_feed -> transaction_mode = 'delete';
                                        $transaction_client_leak_feed -> transaction_data_status = 1;
                                        $transaction_client_leak_feed -> status = 1;
                                        $transaction_client_leak_feed -> save();
                                    }

                                    $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $BrandAbuseSocialRef->id)->first();
                                    if($transaction_client_leak_social_ref){
                                        $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                                        $transaction_client_leak_social_ref -> transaction_data_status = 1;
                                        $transaction_client_leak_social_ref -> status = 1;
                                        $transaction_client_leak_social_ref -> save();
                                    }else{
                                        $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                                        $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                                        $transaction_client_leak_social_ref -> transaction_id = $BrandAbuseSocialRef->id;
                                        $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                                        $transaction_client_leak_social_ref -> transaction_data_status = 1;
                                        $transaction_client_leak_social_ref -> status = 1;
                                        $transaction_client_leak_social_ref -> save();
                                    }
                        
                                }
                            }
                            BrandAbuseFeed::where('temp_id', $BrandAbuseFeedTemp->id)->delete();
                            // $data = BrandAbuseSocialRef::where('id', $social_id)->delete();
                            $data = DB::table('brand_abuse_socail_ref')->where('id', $social_id)->delete();
                            $BrandAbuseFeedTemp->approve = 0;
                            $BrandAbuseFeedTemp->save();
                        }else{
                            // $data = BrandAbuseSocialRef::where('id', $social_id)->delete();
                            $data = DB::table('brand_abuse_socail_ref')->where('id', $social_id)->delete();
                        }
                    }

                    $response  = [
                        'message' => langapp('changes_saved_successful'),
                        'redirect' => route('brandabuse.index_all_site'),
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

    public function activity_brandabuse_modal($code,Request $request)
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code = $data['data']['code'];
                    $site = $data['data']['site'];

                    $BrandAbuseSocialRefs = BrandAbuseSocialRef::where('code',$code)->first();
                    $ActivityHistory = Activity::select('activity.*','users.name as users_name')->where('activity.data_leak_socail_ref_id',$BrandAbuseSocialRefs->id)->whereNull('activity.deleted_at')->leftJoin('users', 'activity.user_id', '=', 'users.id')->orderBy('created_at','desc')->get();

                    $response  = [
                        'BrandAbuseSocialRefs' => $BrandAbuseSocialRefs,
                        'site' => $site,
                        'ActivityHistory' => $ActivityHistory,
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

    public function activity_save(Request $request)
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $id_BrandAbuseSocialRefs = $data['data']['id_BrandAbuseSocialRefs'];
                    $status_activity = $data['data']['status_activity'];
                    $title = $data['data']['title'];
                    $check_active = $data['data']['check_active'];
                    $code_edited_activity = $data['data']['code_edited_activity'];
                    $site_code = $data['data']['site_code'];

                    if(!$title&&!@$_POST['content']){
                        return response()->json(['message' => 'You have to fill Title', 'errors' => ['missing' => ["You have to fill Title"],'missing2' => ["You have to fill content"]]], 500);
                    }else if(!$title){
                        return response()->json(['message' => 'You have to fill Title', 'errors' => ['missing' => ["You have to fill Title"]]], 500);
                    }else if(!@$_POST['content']){
                        return response()->json(['message' => 'You have to fill content', 'errors' => ['missing' => ["You have to fill content"]]], 500);
                    }

                    $content = @$_POST['content']; //รับค่าจาก messageInput

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
                                $data = $img->getattribute('src');

                                //Link url
                                $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                                if(preg_match($reg_exUrl, $data, $url_image)) {
                                    $url = $url_image[0];
                                    $image = file_get_contents($url);
                                    if ($image !== false){
                                        $data = 'data:image/jpg;base64,'.base64_encode($image);
                                    }
                                }

                                $img_check_src = explode(";",$data);
                                if(@$img_check_src[1]) {
                                    list($type, $data) = explode(';', $data);
                                    list(, $data)= explode(',', $data);
                                    $data = base64_decode($data);
                                //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                                    $image_name= time().$k.'.png';
                                //อัพโหลดภาพไปยัง public
                                    $path = public_path('images/file_editor') .'/'. $image_name;
                                //ทำการอัพโหลดภาพ
                                    file_put_contents($path, $data);
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
                        $Activity->data_leak_socail_ref_id = $id_BrandAbuseSocialRefs;
                        $Activity->title = $title;
                        $Activity->content = $content;
                        $Activity->user_id = Auth::user()->id;
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
                    $Activity_check = Activity::where('data_leak_socail_ref_id',$id_BrandAbuseSocialRefs)->where('status_activity','close')->first();

                    $BrandAbuseSocialRef = BrandAbuseSocialRef::where('id',$id_BrandAbuseSocialRefs)->first();

                    if($status_activity) {
                        $BrandAbuseSocialRef->status_monitoring = $status_activity;
                        $BrandAbuseSocialRef->save();
                    }
                    

                    if($site_code){
                        $site = route('socialdatas.index', ['id' => @$site_code]);
                    }else{
                        $site = route('brandabuse.index_all_site');
                    }

                    $response  = [
                        'message' => langapp('changes_saved_successful'),
                        'redirect' => $site,
                        'socail_ref_id' => $Activity->data_leak_socail_ref_id,
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

    public function activity_history_reload(Request $request)
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code = $data['data']['code'];

                    $BrandAbuseSocialRefs = BrandAbuseSocialRef::where('id',$code)->first();
                    $ActivityHistory = Activity::select('activity.*','users.name as users_name')->where('activity.data_leak_socail_ref_id',$BrandAbuseSocialRefs->id)->whereNull('activity.deleted_at')->leftJoin('users', 'activity.user_id', '=', 'users.id')->orderBy('created_at','desc')->get();
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
                                if(TYPE_WEB == 'center'){
                                    $html .= '
                                    <span class="float-right">
                                        <a href="#gototop" class="btn btn-info btn-xs disable_atag" onclick="edit_activity( \''. $value->id .'\');">
                                            <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                                        </a>
                                        <a href="javascript:void(0)" class="btn btn-danger btn-xs disable_atag" onclick="delete_activity(\''.$value->id.'\',\''.$value->code.'\');"><i class="fas fa-trash-alt"></i></a>
                                    </span>';
                                    
                                }else{
                                    if($value->user_id==Auth::user()->id){
                                        $html .= '
                                        <span class="float-right">
                                            <a href="#gototop" class="btn btn-info btn-xs disable_atag" onclick="edit_activity( \''. $value->id .'\');">
                                                <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                                            </a>
                                            <a href="javascript:void(0)" class="btn btn-danger btn-xs disable_atag" onclick="delete_activity(\''.$value->id.'\',\''.$value->code.'\');"><i class="fas fa-trash-alt"></i></a>
                                        </span>';
                                    }
                                }
                            $html .= '
                                </div>
                            </li>';
                        }
                    }else{
                        $html .= '<li class="list-group-item"> </li>';
                    }

                    $response  = [
                        "html" => $html,
                        "ActivityHistory" => $ActivityHistory
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

    public function activity_get_edit_data(Request $request)
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code_activity = $data['data']['code_activity'];
                    
                    $Activity = Activity::where('id',$code_activity)->first()->toArray();

                    $response  = [
                        "ActivityHistory" => $Activity
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

    public function activity_delete(Request $request)
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $site_code = $data['data']['site_code'];
                    $code_activity = $data['data']['code_activity'];

                    if($site_code){
                        $site = route('socialdatas.index', ['id' => @$site_code]);
                    }else{
                        $site = route('brandabuse.index_all_site');
                    }
                    $Activity = Activity::where('id',$code_activity)->first();
                    $Activity->delete();

                    $Activity_check = Activity::where('data_leak_socail_ref_id',$Activity->data_leak_socail_ref_id)->where('status_activity','close')->first();
                    $Activity_check_last = Activity::where('data_leak_socail_ref_id',$Activity->data_leak_socail_ref_id)->where('deleted_at',null)->orderBy('id','desc')->first();
                    $status_activity = $Activity_check_last->status_activity;

                    $BrandAbuseSocialRef = BrandAbuseSocialRef::where('id',$Activity->data_leak_socail_ref_id)->first();
                    // if($BrandAbuseSocialRef->status_monitoring != 'close') {
                        if($Activity_check_last) {
                            if($status_activity) {
                                $BrandAbuseSocialRef->status_monitoring = $status_activity;
                                $BrandAbuseSocialRef->save();
                            } else {
                                $BrandAbuseSocialRef->status_monitoring = 'in_progress';
                                $BrandAbuseSocialRef->save();
                            }
                        } else {
                            $BrandAbuseSocialRef->status_monitoring = 'in_progress';
                            $BrandAbuseSocialRef->save();
                        } 
                    // }

                    $response = [];

                    if($Activity)
                    {
                        $response  = [
                            'message' => langapp('changes_saved_successful'),
                            'redirect' => $site
                        ];
                    }
                    else
                    {
                        $response  = [
                            'message' => 'Error Delete Activity Please Contact Admin', 
                            'errors' => [
                                'missing' => "Error Delete Activity Please Contact Admin"
                            ], 
                            500
                        ];
                    }  

                    $response  = [
                        'message' => langapp('changes_saved_successful'),
                        'redirect' => $site
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

    // brandabuse feed ---------------------------------------------------------------------------------------------------------------------------

    public function brandabusefeed()
    {
        // $SiteSettings = @get_role_custom()['SiteSettings'];
        // $site_id_arr = @get_role_custom()['site_id_arr'];
        // if(@get_role_custom()['superadmin'] == 1) {
        //     $SiteSettings = @get_role_custom()['SiteSettings'];
        // }else if(@get_role_custom()['client'] == 1) {
        //     $SiteSettings = @get_role_custom()['SiteSettings'];
        // }else if(@get_role_custom()['site_support'] == 1) {
        //     $SiteSettings = @get_role_custom()['SiteSettings'];
        // }else if(@get_role_custom()['site_admin'] == 1) {
        //     $SiteSettings = @get_role_custom()['SiteSettings'];
        // }else if(@get_role_custom()['site_client'] == 1) {
        //     $SiteSettings = @get_role_custom()['SiteSettings'];
        // }

        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $SiteSettings = $data['data']['SiteSettings'];
                    $page = $data['data']['page'];
                    $user_id = $data['data']['user_id'];

                    $BrandAbuseSocial = BrandAbuseSocial::where('deleted_at', null)->where('status', 1)->get();
                    $site_settings = SiteSettings::where('deleted_at', null)->where('active', 1)->get();

                    $response  = [
                        'site_settings' => $site_settings,
                        'BrandAbuseSocial' => $BrandAbuseSocial,
                        'page' => $page,
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

    public function brandabusefeedsocial_datatables(Request $request)
    {
        ini_set('max_execution_time', 180);
        set_time_limit(200);

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

                    $search_val = $data['data']['search_val'];
                    $sites = $data['data']['site'];
                    $search = $data['data']['search'];
                    $type = $data['data']['type'];
                    $check_type = $data['data']['check_type'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $start_date = $data['data']['start_date'];
                    $end_date = $data['data']['end_date'];

                    if ($search_val == 1) {

                        $model = BrandAbuseFeedTemp::where('keyword', '!=', null)->where('keyword', '!=', '')->whereIn('feed_type', ['social','darkweb_public']);

                        if ($sites) {
                            $site = SiteSettings::select('id')->where('code', $sites)->first();

                            $model->whereHas('get_socail_ref_temp', function ($query) use ($site) {

                                $query->where('site_id', 'LIKE', '%' . $site->id . '%');
                            });
                        }

                        if ($search) {
                            $model = $model->where('keyword', 'LIKE', '%' . $search . '%');
                        }

                        if ($type) {
                            $model = $model->where('feed_type', $type);
                        }

                        if ($check_type) {
                            if($check_type==1){
                                $model = $model->where('approve', '0');
                            }else if($check_type==2){
                                $model = $model->where('approve', '1');
                            }
                        }


                        if ($isDateSearch == 1) {
                            $date_start = $start_date;
                            $date_end = $end_date;

                            $date_start_explode = explode(" ", $date_start);
                            $date_start_date = @$date_start_explode[0];
                            $date_start_date_format = date("Y-m-d", strtotime($date_start_date));

                            $date_end_explode = explode(" ", $date_end);
                            $date_end_date = @$date_end_explode[0];
                            $date_end_date_format = date("Y-m-d", strtotime($date_end_date));

                            $model = $model->whereBetween('feedtimepost', array($date_start_date_format, $date_end_date_format));
                        }

                        $model = $model->get();
                    } else {

                        $model = BrandAbuseFeedTemp::where('keyword', '!=', null)->where('keyword', '!=', '')->whereIn('feed_type', ['social','darkweb_public']);
                        if ($sites) {
                            $site = SiteSettings::select('id')->where('code', $sites)->first();

                            $model->whereHas('get_socail_ref_temp', function ($query) use ($site) {

                                $query->where('site_id', 'LIKE', '%' . $site->id . '%');
                            });
                        }
                        $model = $model->get();
                        // $model = $model->limit(100)->get();
                        
                    }
                    

                    $response = [
                        "data" => DataTables::of($model)
                            ->editColumn(
                                'chk',
                                function (BrandAbuseFeedTemp $model) {
                                    return '<label><input type="checkbox" name="data_feed_id" class="data_feed_id" value="' . $model->id . '"><span class="label-text"></span></label>';
                                }
                            )
                            ->editColumn(
                                'site',
                                function (BrandAbuseFeedTemp $model) {
                                    $name_site = '';
                                    $leak_socail_ref_temps = leak_socail_ref_temp::select('site_id')->where('data_leak_feed_id', $model->id)->where('keyword', '!=', 'scanner')->first();
                                    if($leak_socail_ref_temps){
                                        $site = SiteSettings::select('name')->whereIn('id', [$leak_socail_ref_temps->site_id])->get();
                                        if($site){
                                            foreach ($site as $data) {
                                                $name_site .= $data->name . ' ,';
                                            }
                                            return rtrim($name_site, ", ");
                                        }else{
                                            return '-';
                                        }
                                    }else{
                                        return '-';
                                    }


                                }
                            )
                            ->editColumn(
                                'source',
                                function (BrandAbuseFeedTemp $model) {
                                    if ($model->feed_type) {
                                        return get_word_leak_compromise($model->feed_type,'data_leak');
                                    } else {
                                        return '-';
                                    }

                                }
                            )
                            ->editColumn(
                                'keyword',
                                function (BrandAbuseFeedTemp $model) {
                                    if ($model->keyword) {
                                        return $model->keyword;
                                    } else {
                                        return '-';
                                    }
                                }
                            )
                            ->editColumn(
                                'content',
                                function (BrandAbuseFeedTemp $model) {
                                    return '<div>' . $model->feedcontent . '</div>';
                                }
                            )
                            ->editColumn(
                                'data_feed',
                                function (BrandAbuseFeedTemp $model) {
                                    return $model->feedtimestamp;
                                }
                            )
                            ->editColumn(
                                'url',
                                function (BrandAbuseFeedTemp $model) {
                                    return '<a href="' . $model->feedlink . '" target="_blank"><i class="fas fa-link"></i></a>';
                                }
                            )
                            ->editColumn(
                                'action',
                                function (BrandAbuseFeedTemp $model) {
                                    $html = '';
                                    if ($model->approve == 0) {
                                        $html .= '<button class="btn btn-success btn-xs" data-toggle="modal" data-target="#confirm-change-status" onclick="approve_dataFeed(' . $model->id . ')">
                                        Approve
                                    </button>';
                                    } else {
                                        $html .= '<button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#confirm-change-status-cancle" onclick="cancle_dataFeed(' . $model->id . ')">
                                        Cancel
                                    </button>';
                                    }
                                    return $html;
                                }
                            )
                            ->rawColumns(['chk', 'site', 'source', 'keyword', 'content', 'data_feed', 'url', 'action'])
                            ->make(true),
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

    public function approve_data_feed(Request $request)
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $sent_mail = $data['data']['sent_mail'];
                    $id = $data['data']['id'];
                    $sites = $data['data']['sites'];

                    $site_id = 0;
                    $BrandAbuseFeed_send_mail = [];
                    $BrandAbuseFeedTemps = BrandAbuseFeedTemp::whereIn('id', $id)->get();
                    $type = @$BrandAbuseFeedTemps[0]->feed_type;
                    foreach ($BrandAbuseFeedTemps as $BrandAbuseFeedTemp) {
                        $check_BrandAbuseFeed = BrandAbuseFeed::where('temp_id', $BrandAbuseFeedTemp->id)->first();
                        if (empty($check_BrandAbuseFeed)) {
                            $BrandAbuseFeed = new BrandAbuseFeed();
                            $BrandAbuseFeed->code = generator_uuid();
                            $BrandAbuseFeed->temp_id = $BrandAbuseFeedTemp->id;
                            $BrandAbuseFeed->data_id = $BrandAbuseFeedTemp->data_id;
                            $BrandAbuseFeed->sourceid = $BrandAbuseFeedTemp->sourceid;
                            $BrandAbuseFeed->keyword = $BrandAbuseFeedTemp->keyword;
                            $BrandAbuseFeed->source_name = $BrandAbuseFeedTemp->source_name;
                            $BrandAbuseFeed->feedcontent = $BrandAbuseFeedTemp->feedcontent;
                            $BrandAbuseFeed->feedlink = $BrandAbuseFeedTemp->feedlink;
                            $BrandAbuseFeed->feedtimepost = $BrandAbuseFeedTemp->feedtimepost;
                            $BrandAbuseFeed->feedtimestamp = $BrandAbuseFeedTemp->feedtimestamp;
                            $BrandAbuseFeed->feeduser = $BrandAbuseFeedTemp->feeduser;
                            $BrandAbuseFeed->tag = $BrandAbuseFeedTemp->tag;
                            $BrandAbuseFeed->feel_type = $BrandAbuseFeedTemp->feed_type;
                            $BrandAbuseFeed->status = 1;
                            $BrandAbuseFeed->save();

                            // $BrandAbuseFeed_send_mail[] = $BrandAbuseFeed;

                            $leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $BrandAbuseFeedTemp->id)->first();
                            if (!empty($leak_socail_ref_temp)) {
                                $BrandAbuseSocialRef = new BrandAbuseSocialRef;
                                $BrandAbuseSocialRef->code = generator_uuid();
                                $BrandAbuseSocialRef->temp_id = $BrandAbuseFeedTemp->id;
                                $BrandAbuseSocialRef->brand_abuse_feed_id = $BrandAbuseFeed->id;
                                $BrandAbuseSocialRef->site_id = $leak_socail_ref_temp->site_id;
                                $BrandAbuseSocialRef->keyword = $leak_socail_ref_temp->keyword;
                                $BrandAbuseSocialRef->feel_type = $BrandAbuseFeedTemp->feed_type;
                                $BrandAbuseSocialRef->status = 1;
                                $BrandAbuseSocialRef->view = 0;
                                $BrandAbuseSocialRef->save();

                                if ($site_id == 0) {
                                    $site_id = $leak_socail_ref_temp->site_id;
                                }
                                
                                if(!isset($BrandAbuseFeed_send_mail[(string)$leak_socail_ref_temp->site_id])){
                                    $BrandAbuseFeed_send_mail[(string)$leak_socail_ref_temp->site_id] = [];
                                }
                                array_push($BrandAbuseFeed_send_mail[(string)$leak_socail_ref_temp->site_id], $BrandAbuseFeed);


                                $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $BrandAbuseFeed->id)->first();
                                if($transaction_client_leak_feed){
                                    $transaction_client_leak_feed -> transaction_mode = 'insert';
                                    $transaction_client_leak_feed -> transaction_data_status = 1;
                                    $transaction_client_leak_feed -> status = 1;
                                    $transaction_client_leak_feed -> save();
                                }else{
                                    $transaction_client_leak_feed = new transaction_client_leak_feed();
                                    $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                                    $transaction_client_leak_feed -> transaction_id = $BrandAbuseFeed->id;
                                    $transaction_client_leak_feed -> transaction_mode = 'insert';
                                    $transaction_client_leak_feed -> transaction_data_status = 1;
                                    $transaction_client_leak_feed -> status = 1;
                                    $transaction_client_leak_feed -> save();
                                }

                                
                                $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $BrandAbuseSocialRef->id)->first();
                                if($transaction_client_leak_social_ref){
                                    $transaction_client_leak_social_ref -> transaction_mode = 'insert';
                                    $transaction_client_leak_social_ref -> transaction_data_status = 1;
                                    $transaction_client_leak_social_ref -> status = 1;
                                    $transaction_client_leak_social_ref -> save();
                                }else{
                                    $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                                    $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                                    $transaction_client_leak_social_ref -> transaction_id = $BrandAbuseSocialRef->id;
                                    $transaction_client_leak_social_ref -> transaction_mode = 'insert';
                                    $transaction_client_leak_social_ref -> transaction_data_status = 1;
                                    $transaction_client_leak_social_ref -> status = 1;
                                    $transaction_client_leak_social_ref -> save();
                                }
                            }

                            $BrandAbuseFeedTemp->approve = 1;
                            $BrandAbuseFeedTemp->save();
                        }
                    }
                    
                    if ($this->request->sent_mail == 1) {
                        foreach ($BrandAbuseFeed_send_mail as $key => $value) {
                            $site_email_alert = site_config_email_alert::where("site_id", $key)->get();
                            if ($site_email_alert) {
                                $email_site_a = [];
                                foreach ($site_email_alert as $site_email_alert_val) {
                                    $email_site_a[] = $site_email_alert_val->email;
                                }
                                $email_site_alert = array_unique($email_site_a);
                                foreach($email_site_alert as $email){
                                    Mail::to($email)->send(new CompromisedMail($value, 'brand_abuse'));
                                    if( count(Mail::failures()) == 0 ) {
                                        LogEmail::Create([
                                            'to' => $email,
                                            'status' => 'Success',
                                            'subject' => 'brand_abuse'
                                        ]);
                                    }
                                }
                                if( count(Mail::failures()) > 0 ) {
                                    foreach(Mail::failures() as $email_address) {
                                        LogEmail::Create([
                                            'to' => $email_address,
                                            'status' => 'Fail',
                                            'subject' => 'brand_abuse'
                                        ]);
                                    }
                                }
                            }
                        }
                    }

                    if(@$type == 'social' || @$type == 'darkweb_public') {
                        if($sites){
                            $site = route('darkweb_datas.index',['id'=>$sites]);
                        }else{
                            $site = route('brandabusefeed.index');
                        }
                    } else {
                        if($sites){
                            $site = route('compromised_feed.index',['id'=>$sites]);
                        }else{
                            $site = route('datafeed.darkweb_index');
                        }
                    }

                    $response  = [
                        'message' => langapp('changes_saved_successful'),
                        'redirect' => @$site,
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

    public function cancle_data_feed(Request $request)
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $id = $data['data']['id'];
                    $sites = $data['data']['sites'];

                    $BrandAbuseFeedTemps = BrandAbuseFeedTemp::whereIn('id', $id)->get();
                    $type = @$BrandAbuseFeedTemps[0]->feed_type;
                    foreach ($BrandAbuseFeedTemps as $BrandAbuseFeedTemp) {
                        $BrandAbuseFeeds = BrandAbuseFeed::where('temp_id', $BrandAbuseFeedTemp->id)->get();
                        foreach($BrandAbuseFeeds as $BrandAbuseFeed){
                            $leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $BrandAbuseFeedTemp->id)->first();
                            $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $BrandAbuseFeed->id)->first();
                            if($transaction_client_leak_feed){
                                $transaction_client_leak_feed -> transaction_mode = 'delete';
                                $transaction_client_leak_feed -> transaction_data_status = 1;
                                $transaction_client_leak_feed -> status = 1;
                                $transaction_client_leak_feed -> save();
                            }else{
                                $transaction_client_leak_feed = new transaction_client_leak_feed();
                                $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                                $transaction_client_leak_feed -> transaction_id = $BrandAbuseFeed->id;
                                $transaction_client_leak_feed -> transaction_mode = 'delete';
                                $transaction_client_leak_feed -> transaction_data_status = 1;
                                $transaction_client_leak_feed -> status = 1;
                                $transaction_client_leak_feed -> save();
                            } 
                        }
                        BrandAbuseFeed::where('temp_id', $BrandAbuseFeedTemp->id)->delete();
                        $BrandAbuseSocialRefs = BrandAbuseSocialRef::where('temp_id', $BrandAbuseFeedTemp->id)->get();
                        foreach($BrandAbuseSocialRefs as $BrandAbuseSocialRef){
                            $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $BrandAbuseSocialRef->id)->first();
                            if($transaction_client_leak_social_ref){
                                $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                                $transaction_client_leak_social_ref -> transaction_data_status = 1;
                                $transaction_client_leak_social_ref -> status = 1;
                                $transaction_client_leak_social_ref -> save();
                            }else{
                                $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                                $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                                $transaction_client_leak_social_ref -> transaction_id = $BrandAbuseSocialRef->id;
                                $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                                $transaction_client_leak_social_ref -> transaction_data_status = 1;
                                $transaction_client_leak_social_ref -> status = 1;
                                $transaction_client_leak_social_ref -> save();
                            }
                        }
                        BrandAbuseSocialRef::where('temp_id', $BrandAbuseFeedTemp->id)->delete();
                        $BrandAbuseFeedTemp->approve = 0;
                        $BrandAbuseFeedTemp->save();
                    }

                    if(@$type == 'social' || @$type == 'darkweb_public') {
                        if($sites){
                            $site = route('darkweb_datas.index',['id'=>$sites]);
                        }else{
                            $site = route('datafeed.index');
                        }
                    } else {
                        if($sites){
                            $site = route('compromised_feed.index',['id'=>$sites]);
                        }else{
                            $site = route('datafeed.darkweb_index');
                        }
                    }

                    $response  = [
                        'message' => langapp('changes_saved_successful'),
                        'redirect' => $site
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
    
    // private function dataFalse($bearerToken, $mode, $data){
    //     try {
    //         $header = $bearerToken;
    //         $site = $this->AuthorizationRegister($header, $mode);
    //         if($site['status_code'] !== '200'){
    //             return $this->AuthorizationRegister($header, $mode);
    //         }
    //         $value = $data;
    //         $data = encrypt_decrypt('decrypt', $value, $header, $site['data']['ip_key'],  $site['data']['mac_address_key']);

    //         if($data === false){
    //             return $data;
    //         }else{
    //             $data_return = [
    //                 'site' => $site,
    //                 'data' => json_decode($data, true),
    //             ];
    //             return $data_return;
    //         }

    //     } catch (\Exception $e) {
    //         $response = array(
    //             'status' => 0,
    //             'message' => $e -> getMessage(),
    //         );
    //         return response()->json($response);
    //     }
    // }

    // private function explode_val($val,$type=null,$url) {
    //     $result = '';
    //     if($val) {
    //         $val_arr = explode(",",$val);
    //         if($val_arr) {
    //             foreach($val_arr as $tag) {
    //                 if($type == 'tags') {
    //                     $result .=  '<a href="'.$url.'/indicators/tags/'.$tag.'">'.$tag.'</a> ,';
    //                 } else if ($type == 'groups') {
    //                     $result .=  '<a href="'.$url.'/indicators/groups/'.$tag.'">'.$tag.'</a> ,';
    //                 } else {
    //                     $result .=  '<a href="#">'.$tag.'</a> ,';
    //                 }
    
    //             }
    //             $result = rtrim($result,',');
    //         }
    //     } else {
    //         $result = '';
    //     }
    //     return $result;
    // }


    public function brand_abuse_delete(Request $request) // delete_brandabusedata_modal
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                     $code = $data['data']['code'];
                    
                    // $model1 = BrandAbuseSocialRef::select('brand_abuse_feed.feedcontent')
                    // ->join('brand_abuse_feed', 'brand_abuse_feed.id', '=','brand_abuse_socail_ref.brand_abuse_feed_id')
                    // ->where('brand_abuse_socail_ref.code',$code)->first();

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

    public function brand_abuse_delete_select(Request $request) // delete_brandabusedata
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code = $data['data']['code'];
                    
                    $BrandAbuseSocialRef = BrandAbuseSocialRef::where('code', $code)->first();
                    if($BrandAbuseSocialRef){
                        $BrandAbuseSocialRef->delete();
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

    public function brand_abuse_delete_change(Request $request) // change_delete_brandabusedata
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code = $data['data']['code'];
                    foreach($code as $id){
                        $BrandAbuseSocialRef = BrandAbuseSocialRef::where('id', $id)->delete();
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

    public function count_keyword(Request $request)
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $get_role_custom_first = $data['data']['get_role_custom_first'];

                    $model = BrandAbuseSocialRef::select('keyword',DB::raw('count(*)  as count_keyword'))
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

    public function count_icon(Request $request) // count_icon
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
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
                            $icon_mobile = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('mobile')).'%'])->count();//->get()
                            $icon_facebook = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('facebook')).'%'])->count();//->get()
                            $icon_line = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('line')).'%'])->count();//->get()
                            $icon_twitter = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('twitter')).'%'])->count();//->get()
                            $icon_website = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('website')).'%'])->count();//->get()
                            $icon_other = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('mobile'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('facebook'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('line'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('twitter'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                            $number_in_progress = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('in_progress')).'%'])->count();//->get()
                            $number_reported = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('reported')).'%'])->count();//->get()
                            $number_close = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('close')).'%'])->count();//->get()                                                                                                                                    ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                        } else {
                            $icon_mobile = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('mobile')).'%'])->count();//->get()
                            $icon_facebook = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('facebook')).'%'])->count();//->get()
                            $icon_line = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('line')).'%'])->count();//->get()
                            $icon_twitter = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('twitter')).'%'])->count();//->get()
                            $icon_website = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('website')).'%'])->count();//->get()
                            $icon_other = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('mobile'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('facebook'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('line'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('twitter'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                            $number_in_progress = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('in_progress')).'%'])->count();//->get()
                            $number_reported = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('reported')).'%'])->count();//->get()
                            $number_close = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('close')).'%'])->count();//->get()                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                        }
                    } else {
                        if(!$site_code) {
                            $icon_mobile = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('mobile')).'%'])->count();//->get()
                            $icon_facebook = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('facebook')).'%'])->count();//->get()
                            $icon_line = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('line')).'%'])->count();//->get()
                            $icon_twitter = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('twitter')).'%'])->count();//->get()
                            $icon_website = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('website')).'%'])->count();//->get()
                            $icon_other = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('mobile'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('facebook'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('line'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('twitter'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                         
                            $number_in_progress = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('in_progress')).'%'])->count();//->get()
                            $number_reported = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('reported')).'%'])->count();//->get()
                            $number_close = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('close')).'%'])->count();//->get()                                                                                                                                   ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                        } else {
                            $icon_mobile = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('mobile')).'%'])->count();//->get()
                            $icon_facebook = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('facebook')).'%'])->count();//->get()
                            $icon_line = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('line')).'%'])->count();//->get()
                            $icon_twitter = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('twitter')).'%'])->count();//->get()
                            $icon_website = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('website')).'%'])->count();//->get()
                            $icon_other = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('mobile'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('facebook'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('line'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('twitter'))])
                                                                                                                                                              ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                        
                            $number_in_progress = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('in_progress')).'%'])->count();//->get()
                            $number_reported = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('reported')).'%'])->count();//->get()
                            $number_close = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('close')).'%'])->count();//->get()      
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

    public function getBrandAbuseSocial(Request $request)
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'brand_abuse'){
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
                            $Brand_Abuse_social = BrandAbuseSocial::where('deleted_at', null)->where('status',1)->get();
                           
                    }

                    $response = [
                        "Brand_Abuse_social" => $Brand_Abuse_social,
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


    // public function activity_brandabuse_modal(Request $request) // activity_brandabuse_modal
    // {
    //     try{
    //         $header = $request->bearerToken();
    //         $mode = $request->mode;
    //         $data_request = $request -> data;
    //         $data = $this -> dataFalse($header, $mode, $data_request);
    //         if($data === false){
    //             return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
    //         }else{ 
    //             if($data['data']['menu'] !== 'data_leak'){
    //                 return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
    //             }else{
    //                 $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
    //                 if($auth_site['status_code'] !== '200'){
    //                     return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
    //                 }

    //                 $BrandAbuseSocialRef_code = $data['data']['BrandAbuseSocialRef_code'];
    //                 $site = $data['data']['site'];
                  

    //                 $user = User::where('id', $data['data']['user_id'])->first();

    //                 // $site_id = '';
            
    //                 // if($site_code) {
    //                 //     $site_id_m = SiteSettings::where('code',$site_code)->first();
    //                 //     $site_id = @$site_id_m->id;
    //                 // }


    //                 $BrandAbuseSocialRefs = BrandAbuseSocialRef::where('code',$BrandAbuseSocialRef_code)->first();
    //                 $ActivityHistory = Activity::select('activity.*','users.name as users_name')->where('activity.data_leak_socail_ref_id',$BrandAbuseSocialRefs->id)->whereNull('activity.deleted_at')->leftJoin('users', 'activity.user_id', '=', 'users.id')->orderBy('created_at','desc')->get();
    //                 // $BrandAbuseFeed = BrandAbuseFeed::where('id',$BrandAbuseSocialRefs->data_leak_feed_id)->first();
    //                 $response['BrandAbuseSocialRefs'] = $BrandAbuseSocialRefs;
    //                 $response['site'] = @$site;
    //                 $response['ActivityHistory'] = $ActivityHistory;
               
            

    //                 $data_transcation = json_encode($response);
    //                 $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
    //                 return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
    //             }
    //         }
    //     } catch (\Exception $e) {
    //         $response = array(
    //             'status_code' => 500,
    //             'message' => $e -> getMessage(),
    //         );

    //         $header = $request->bearerToken();
    //         $mode = $request->mode;
    //         $data_request = $request -> data;
    //         $data = $this -> dataFalse($header, $mode, $data_request);
    //         $this->saveLog($data['site']['data']['id'], json_encode($response));

    //         return response()->json($response);
    //     }
    // }

    // public function activity_history_reload(Request $request) // activity_history_reload
    // {
    //     try{
    //         $header = $request->bearerToken();
    //         $mode = $request->mode;
    //         $data_request = $request -> data;
    //         $data = $this -> dataFalse($header, $mode, $data_request);
    //         if($data === false){
    //             return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
    //         }else{ 
    //             if($data['data']['menu'] !== 'brand_abuse'){
    //                 return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
    //             }else{
    //                 $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
    //                 if($auth_site['status_code'] !== '200'){
    //                     return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
    //                 }

    //                 $BrandAbuseSocialRef_code = $data['data']['BrandAbuseSocialRef_code'];
    //                 // $site = $data['data']['site'];
                  

    //                 $user = User::where('id', $data['data']['user_id'])->first();

    //                 // $site_id = '';
            
    //                 // if($site_code) {
    //                 //     $site_id_m = SiteSettings::where('code',$site_code)->first();
    //                 //     $site_id = @$site_id_m->id;
    //                 // }


    //                 $BrandAbuseSocialRefs = BrandAbuseSocialRef::where('id',$BrandAbuseSocialRef_code)->first();
    //                 $ActivityHistory = Activity::select('activity.*','users.name as users_name')->where('activity.data_leak_socail_ref_id',$DataLeakSocialRefs->id)->whereNull('activity.deleted_at')->leftJoin('users', 'activity.user_id', '=', 'users.id')->orderBy('created_at','desc')->get();
    //                 $html = '';
    //                 if(!empty($ActivityHistory)){
    //                     foreach ($ActivityHistory as $key => $value) {
    //                             $html_status_activity = '';
    //                             $activity_color = '';
    //                             $activity_name = '';
    //                             if($value->status_activity) {
    //                                 if($value->status_activity == 'in_progress') {
    //                                     $activity_color = '#FFC107';
    //                                     $activity_name = 'Progress';
    //                                 } else if ($value->status_activity == 'reported') {
    //                                     $activity_color = '#28A745';
    //                                     $activity_name = 'Reported';
    //                                 } else if ($value->status_activity == 'close') {
    //                                     $activity_color = '#DC3545';
    //                                     $activity_name = 'Close';
    //                                 }
    //                                 $html_status_activity = '<span class="badge" style="background-color: '.$activity_color.'; display: block;">'.$activity_name.'</span>';
    //                             }
    //                         $html .= '
    //                         <li class="work" id="list_activity_'.$value->code.'">
    //                             <input class="radio" id="work_'.$key.'" name="works" type="radio">
    //                             <div class="relative">
    //                                 <label for="work_'.$key.'" class="label_custom" style="font-weight: 900;">'.$value->title.'</label>
    //                                 <span class="date_custom" style="text-align:center;">'.$value->updated_at.$html_status_activity.'</span>
    //                                 <span class="circle_custom"></span>
    //                             </div>
    //                             <div class="content_custom">
    //                                 <p>
    //                                     '.$value->content.'
    //                                 </p>
    //                             </div>
    //                             <div>
    //                                 <strong>Post By</strong> '.$value->users_name;
    //                                 if($value->user_id == $user->id){
    //                                     $html .= '
    //                                     <span class="float-right">
    //                                         <a href="#gototop" class="btn btn-info btn-xs disable_atag" onclick="edit_activity( \''. $value->id .'\');">
    //                                             <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
    //                                         </a>
    //                                         <a href="javascript:void(0)" class="btn btn-danger btn-xs disable_atag" onclick="delete_activity(\''.$value->id.'\',\''.$value->code.'\');"><i class="fas fa-trash-alt"></i></a>
    //                                     </span>';
    //                                 }
                                
    //                         $html .= '
    //                             </div>
    //                         </li>';
    //                     }
    //                 }else{
    //                     $html .= '<li class="list-group-item"> </li>';
    //                 }
               
    //                 $response = [
    //                     "html" => $html,
    //                     "ActivityHistory" => $ActivityHistory,
    //                 ];

    //                 $data_transcation = json_encode($response);
    //                 $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
    //                 return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
    //             }
    //         }
    //     } catch (\Exception $e) {
    //         $response = array(
    //             'status_code' => 500,
    //             'message' => $e -> getMessage(),
    //         );

    //         $header = $request->bearerToken();
    //         $mode = $request->mode;
    //         $data_request = $request -> data;
    //         $data = $this -> dataFalse($header, $mode, $data_request);
    //         $this->saveLog($data['site']['data']['id'], json_encode($response));

    //         return response()->json($response);
    //     }
    // }

    // public function activity_get_edit_data(Request $request) // activity_get_edit_data
    // {
    //     try{
    //         $header = $request->bearerToken();
    //         $mode = $request->mode;
    //         $data_request = $request -> data;
    //         $data = $this -> dataFalse($header, $mode, $data_request);
    //         if($data === false){
    //             return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
    //         }else{ 
    //             if($data['data']['menu'] !== 'brand_abuse'){
    //                 return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
    //             }else{
    //                 $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
    //                 if($auth_site['status_code'] !== '200'){
    //                     return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
    //                 }

    //                 $code_activity = $data['data']['code_activity'];
    //                 // $site = $data['data']['site'];
                  

    //                 $user = User::where('id', $data['data']['user_id'])->first();

    //                 // $site_id = '';
            
    //                 // if($site_code) {
    //                 //     $site_id_m = SiteSettings::where('code',$site_code)->first();
    //                 //     $site_id = @$site_id_m->id;
    //                 // }


    //                 $Activity = Activity::where('id',$code_activity)->first()->toArray();
   
               
    //                 $response = [
    //                     "ActivityHistory" => $Activity,
    //                 ];

    //                 $data_transcation = json_encode($response);
    //                 $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
    //                 return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
    //             }
    //         }
    //     } catch (\Exception $e) {
    //         $response = array(
    //             'status_code' => 500,
    //             'message' => $e -> getMessage(),
    //         );

    //         $header = $request->bearerToken();
    //         $mode = $request->mode;
    //         $data_request = $request -> data;
    //         $data = $this -> dataFalse($header, $mode, $data_request);
    //         $this->saveLog($data['site']['data']['id'], json_encode($response));

    //         return response()->json($response);
    //     }
    // }

    // public function activity_save(Request $request) // activity_save
    // {
    //     try{
    //         $header = $request->bearerToken();
    //         $mode = $request->mode;
    //         $data_request = $request -> data;
    //         $data = $this -> dataFalse($header, $mode, $data_request);
    //         if($data === false){
    //             return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
    //         }else{
    //             if($data['data']['menu'] !== 'brand_abuse'){
    //                 return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
    //             }else{
    //                 $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
    //                 if($auth_site['status_code'] !== '200'){
    //                     return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
    //                 }

    //                 // $code_activity = $data['data']['code_activity'];
    //                 $code_edited_activity = $data['data']['code_edited_activity'];
    //                 $status_activity = $data['data']['status_activity'];
    //                 $content = $data['data']['content'];
    //                 $check_active = $data['data']['check_active'];
    //                 $id_BrandAbuseSocialRefs = $data['data']['id_BrandAbuseSocialRefs'];
    //                 $title = $data['data']['title'];
    //                 $site_code = $data['data']['site_code'];
    //                 // $site = $data['data']['site'];
                  
    //                 //--------------------------------//
    //                 $status_activity = $status_activity;
    //                 $content = @$content; //รับค่าจาก messageInput
    //                 if($content) {
    //                     $dom = new \domdocument();
    //                     if($dom->getelementsbytagname('img')){
    //                         $dom->loadHtml('<?xml encoding="UTF-8">'.$content,
    //                         LIBXML_HTML_NOIMPLIED |
    //                         LIBXML_HTML_NODEFDTD |
    //                         LIBXML_NOERROR |
    //                         LIBXML_NOWARNING 
    //                     );
    //                         //ดึงเอาส่วนที่เป็นรูปภาพมาจาก summernote
    //                         $images = $dom->getelementsbytagname('img');
    //                         //ลูปรูปภาพและทำการเข้ารหัสรูปภาพ
    //                         foreach($images as $k => $img){
    //                             $data_content = $img->getattribute('src');

    //                             //Link url
    //                             $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
    //                             if(preg_match($reg_exUrl, $data_content, $url_image)) {
    //                                 $url = $url_image[0];
    //                                 $image = file_get_contents($url);
    //                                 if ($image !== false){
    //                                     $data_content = 'data:image/jpg;base64,'.base64_encode($image);
    //                                 }
    //                             }

    //                             $img_check_src = explode(";",$data_content);
    //                             if(@$img_check_src[1]) {
    //                                 list($type, $data_content) = explode(';', $data_content);
    //                                 list(, $data_content)= explode(',', $data_content);
    //                                 $data_content = base64_decode($data_content);
    //                             //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
    //                                 $image_name= time().$k.'.png';
    //                             //อัพโหลดภาพไปยัง public
    //                                 $path = public_path('images/file_editor') .'/'. $image_name;
    //                             //ทำการอัพโหลดภาพ
    //                                 file_put_contents($path, $data_content);
    //                                 $img->removeattribute('src');
    //                                 $img->setattribute('src', config('app.URL_CENTER_PUBLISH').'/images/file_editor/'.$image_name);
    //                             } else {
            
    //                             }
    //                         }
    //                         $content = $dom->savehtml();
            
    //                     }
    //                 }
            
    //                 if($check_active=="1"){
    //                     $Activity = new Activity;
    //                     $Activity->code = generator_uuid();
    //                     $Activity->data_leak_socail_ref_id = $id_BrandAbuseSocialRefs;
    //                     $Activity->title = $title;
    //                     $Activity->content = $content;
    //                     $Activity->user_id = $data['data']['user_id'];
    //                     if($status_activity) {
    //                         $Activity->status_activity = $status_activity;
    //                     }
    //                     $Activity->save();
    //                 }else if($check_active=="2"){
    //                     $Activity = Activity::where('id',$code_edited_activity)->first();
    //                     $Activity->title = $title;
    //                     $Activity->content = $content;
    //                     if($status_activity) {
    //                         $Activity->status_activity = $status_activity;
    //                     }
    //                     $Activity->save();
    //                 }
    //                 $Activity_check = Activity::where('data_leak_socail_ref_id',$id_BrandAbuseSocialRefs)->where('status_activity','close')->first();

    //                 $BrandAbuseSocialRef = BrandAbuseSocialRef::where('id',$id_BrandAbuseSocialRefs)->first();
    //                 // if($BrandAbuseSocialRef->status_monitoring != 'close') {
    //                     if($Activity->status_activity == 'close') {
    //                         $BrandAbuseSocialRef->status_monitoring = 'close';
    //                         $BrandAbuseSocialRef->save();
    //                     } else if ($Activity->status_activity == 'in_progress') {//reported
    //                         if($Activity_check) {
    //                             $BrandAbuseSocialRef->status_monitoring = 'close';
    //                             $BrandAbuseSocialRef->save();
    //                         } else {
    //                             $BrandAbuseSocialRef->status_monitoring = 'in_progress';//reported
    //                             $BrandAbuseSocialRef->save();
    //                         }
    //                     } else if ($Activity->status_activity == 'in_progress') {
    //                         if($Activity_check) {
    //                             $BrandAbuseSocialRef->status_monitoring = 'close';
    //                             $BrandAbuseSocialRef->save();
    //                         } else {
    //                             $BrandAbuseSocialRef->status_monitoring = 'in_progress';
    //                             $BrandAbuseSocialRef->save();
    //                         }
    //                     }   
    //                 // }
            

    //                 //--------------------------------//
   
               
    //                 $response = [
    //                     "socail_ref_id" => $Activity->data_leak_socail_ref_id,
    //                 ];

    //                 $data_transcation = json_encode($response);
    //                 $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
    //                 return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
    //             }
    //         }
    //     } catch (\Exception $e) {
    //         $response = array(
    //             'status_code' => 500,
    //             'message' => $e -> getMessage(),
    //         );

    //         $header = $request->bearerToken();
    //         $mode = $request->mode;
    //         $data_request = $request -> data;
    //         $data = $this -> dataFalse($header, $mode, $data_request);
    //         $this->saveLog($data['site']['data']['id'], json_encode($response));

    //         return response()->json($response);
    //     }
    // }

    // public function activity_delete(Request $request) // activity_delete
    // {
    //     try{
    //         $header = $request->bearerToken();
    //         $mode = $request->mode;
    //         $data_request = $request -> data;
    //         $data = $this -> dataFalse($header, $mode, $data_request);
    //         if($data === false){
    //             return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
    //         }else{
    //             if($data['data']['menu'] !== 'brand_abuse'){
    //                 return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
    //             }else{
    //                 $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
    //                 if($auth_site['status_code'] !== '200'){
    //                     return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
    //                 }

    //                 // $code_activity = $data['data']['code_activity'];
    //                 $code_activity = $data['data']['code_activity'];
    //                 // $site = $data['data']['site'];
                  
    //                 //--------------------------------//
    //                 $Activity = Activity::where('id',$code_activity)->first();
    //                 $Activity->delete();

    //                 $Activity_check = Activity::where('data_leak_socail_ref_id',$Activity->data_leak_socail_ref_id)->where('status_activity','close')->first();
    //                 $Activity_check_last = Activity::where('data_leak_socail_ref_id',$Activity->data_leak_socail_ref_id)->where('deleted_at',null)->orderBy('id','desc')->first();
    //                 $status_activity = $Activity_check_last->status_activity;

    //                 $BrandAbuseSocialRef = BrandAbuseSocialRef::where('id',$Activity->data_leak_socail_ref_id)->first();
    //                 // if($BrandAbuseSocialRef->status_monitoring != 'close') {
    //                     if($Activity_check_last) {
    //                         if($status_activity) {
    //                             $BrandAbuseSocialRef->status_monitoring = $status_activity;
    //                             $BrandAbuseSocialRef->save();
    //                         } else {
    //                             $BrandAbuseSocialRef->status_monitoring = 'in_progress';
    //                             $BrandAbuseSocialRef->save();
    //                         }
    //                     } else {
    //                         $BrandAbuseSocialRef->status_monitoring = 'in_progress';
    //                         $BrandAbuseSocialRef->save();
    //                     } 
    //                 // }
    //                 //--------------------------------//
   
               
    //                 $response = [
    //                     "Activity" => $Activity,
    //                 ];

    //                 $data_transcation = json_encode($response);
    //                 $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
    //                 return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
    //             }
    //         }
    //     } catch (\Exception $e) {
    //         $response = array(
    //             'status_code' => 500,
    //             'message' => $e -> getMessage(),
    //         );

    //         $header = $request->bearerToken();
    //         $mode = $request->mode;
    //         $data_request = $request -> data;
    //         $data = $this -> dataFalse($header, $mode, $data_request);
    //         $this->saveLog($data['site']['data']['id'], json_encode($response));

    //         return response()->json($response);
    //     }
    // }





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
