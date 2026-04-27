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

class ApiCompromisedController extends ApiController
{
    public function compromised_count_val(Request $request){
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

                    $date_start = $data['data']['date_start'] ?? null;
                    $date_end = $data['data']['date_end'] ?? null;
                    $site_code = $data['data']['site_code'] ?? null;
                    $title = $data['data']['title'] ?? null;
                    $social = $data['data']['social'] ?? null;
                    $keywords = $data['data']['keywords'] ?? null;
                    $f_search = $data['data']['f_search'] ?? null;
                    $isDateSearch = $data['data']['isDateSearch'] ?? null;
                    $check_type = $data['data']['check_type'] ?? null;
                    $check_serverity = isset($data['data']['check_serverity']) ? strtolower(trim($data['data']['check_serverity'])) : (isset($data['data']['level']) ? strtolower(trim($data['data']['level'])) : null);

                    $user = User::where('id', $data['data']['user_id'])->first();

                    $site_id = '';
            
                    if($site_code) {
                        $site_id_m = SiteSettings::where('code',$site_code)->first();
                        $site_id = @$site_id_m->id;
                    }
            
                    $title = $request ->title;
                    $social = $request ->social;
                   
            
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
            
            
                    if(  $f_search == 1 && ($keywords || $social || $date_start || $date_end || $site_id || $check_type) ){
            
                        $model = DataLeakSocialRef::where('deleted_at', null)
                        ->whereHas('get_data_leak_feed_one', function ($query) {
                            $query->whereIn('feel_type', ['darkweb', 'compromise', 'webserver', 'server']);
                        })
                        ->with('get_site')
                        ->with('get_data_leak_feed_one');
            
                        $countGroupBy = DataLeakSocialRef::where('deleted_at', null)
                        ->whereHas('get_data_leak_feed_one', function ($query) {
                            $query->whereIn('feel_type', ['darkweb', 'compromise', 'webserver', 'server']);
                        })
                        ->with('get_site')
                        ->with('get_data_leak_feed_one');
            
            
                        if ($keywords) {

                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($keywords) {
                                $query->where('keyword', 'LIKE', '%' . $keywords . '%')
                                    ->orWhere('feedcontent', 'LIKE', '%' . $keywords . '%');
                            });
            
                            $countGroupBy->whereHas('get_data_leak_feed_one', function ($query) use ($keywords) {
                                $query->where('keyword', 'LIKE', '%' . $keywords . '%')
                                    ->orWhere('feedcontent', 'LIKE', '%' . $keywords . '%');
                            });
            
                        }
            
                        
            

                          
                            if($isDateSearch==1){
                            
                                $countGroupBy = $countGroupBy->whereHas('get_data_leak_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
                                    $qq->whereBetween('feedtimepost', array($date_start_datetime_format, $date_end_datetime_format));
                                });
            
                                $model = $model->whereHas('get_data_leak_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
                                    $qq->whereBetween('feedtimepost', array($date_start_datetime_format, $date_end_datetime_format));
                                });
                            
                            }
            

            
                        if($request ->check_type) {
            
                            $model = $model-> where('feel_type', '=' ,$check_type);
                            $countGroupBy = $countGroupBy -> where('feel_type', '=' ,$check_type);
            
                        }

                        if ($check_serverity) {
                            $model = $model->where('serverity', '=', $check_serverity);
                            $countGroupBy = $countGroupBy->where('serverity', '=', $check_serverity);
                        }
            
            
                        if($site_id) {
                            $model = $model->where('site_id', $site_id);
                            $countGroupBy = $countGroupBy->where('site_id', $site_id);
                        }
                        $get_role_custom_first = $data['data']['get_role_custom_first'];
                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if(@$get_role_custom_first['superadmin'] == 1) {
                            
            
                        }else if(@$get_role_custom_first['client'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr)->where('status', 1);
                            $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);
            
                        }else if(@$get_role_custom_first['site_support'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);
            
                        }else if(@$get_role_custom_first['site_admin'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);
            
                        }else if(@$get_role_custom_first['site_client'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr)->where('status', 1);
                            $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);
            
                        }



                        $Data_leak_feed_all = $model->count();
                        $countGroupBy = $countGroupBy->select( 'feel_type',DB::raw('count(*) as total'))->groupBy('feel_type')->get();
                        $model = $model->with('get_data_leak_feed_one')->orderBy('id','desc')->paginate(PAGINATE_NUM);
                    }else{
                        $Data_leak_feed_all = DataLeakSocialRef::where('deleted_at', null)->where('status', 1)->whereIn('feel_type', ['darkweb', 'compromise','webserver','server']);
                        $news = DataLeakSocialRef::where('deleted_at', null)->where('status', 1)->whereIn('feel_type', ['darkweb', 'compromise','webserver','server']);//->get()
                        $countGroupBy = DataLeakSocialRef::select( 'feel_type',DB::raw('count(*) as total'))->where('deleted_at', null)->where('status', 1)->whereIn('feel_type', ['darkweb', 'compromise','webserver','server'])->groupBy('feel_type');
                       
            
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
                        
            
                        if($site_id) {
                            $Data_leak_feed_all = $Data_leak_feed_all->where('site_id', $site_id);
                            $countGroupBy = $countGroupBy->where('site_id', $site_id);
                        }

                        if ($check_serverity) {
                            $Data_leak_feed_all = $Data_leak_feed_all->where('serverity', '=', $check_serverity);
                            $countGroupBy = $countGroupBy->where('serverity', '=', $check_serverity);
                        }

                        $get_role_custom_first = $data['data']['get_role_custom_first'];
                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if(@$get_role_custom_first['superadmin'] == 1) {
                            
            
                        }else if(@$get_role_custom_first['client'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr)->where('status', 1);
                            $Data_leak_feed_all = $Data_leak_feed_all->whereIn('site_id', $site_id_arr)->where('status', 1);
            
                        }else if(@$get_role_custom_first['site_support'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            $Data_leak_feed_all = $Data_leak_feed_all->whereIn('site_id', $site_id_arr)->where('status', 1);
            
                        }else if(@$get_role_custom_first['site_admin'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            $Data_leak_feed_all = $Data_leak_feed_all->whereIn('site_id', $site_id_arr)->where('status', 1);
            
                        }else if(@$get_role_custom_first['site_client'] == 1) {
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr)->where('status', 1);
                            $Data_leak_feed_all = $Data_leak_feed_all->whereIn('site_id', $site_id_arr)->where('status', 1);
            
                        }
                        $news = $news->with('get_data_leak_feed_one')->orderBy('id','desc')->paginate(PAGINATE_NUM);
                        $Data_leak_feed_all = $Data_leak_feed_all->count();
                        $countGroupBy = $countGroupBy->get();
            
            
                    }
                    $content = [];
                    $count_sub_type["webserver"] = 0;
                    $count_sub_type["darkweb"] = 0;
                    $count_sub_type["compromise"] = 0;
                    
                    foreach ($countGroupBy as $countGroup) {
                        $count_sub_type[$countGroup->feel_type] = $countGroup->total;
                    }   
                    
                    $response = [
                        "html" => $html,
                        "count" => $Data_leak_feed_all,
                        "webserver" => $count_sub_type["webserver"],
                        "darkweb" => $count_sub_type["darkweb"],
                        "compromise" => $count_sub_type["compromise"],
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

    public function compromised_view(Request $request){
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

    public function compromised_table(Request $request){
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

                    $startDate = $data['data']['startDate'] ?? null;
                    $endDate = $data['data']['endDate'] ?? null;
                    $search_val = $data['data']['search_val'] ?? null;
                    $site = $data['data']['site'] ?? null;
                    $get_role_custom_first = $data['data']['get_role_custom_first'] ?? null;
                    $keywords = $data['data']['keywords'] ?? null;
                    $assets = $data['data']['assets'] ?? null;
                    $isDateSearch = $data['data']['isDateSearch'] ?? null;
                    $check = $data['data']['check'] ?? null;
                    $level = $data['data']['level'] ?? null;
                    $source = $data['data']['source'] ?? null;
                    $click_type = $data['data']['click_type'] ?? null;
                    $check_type = $data['data']['check_type'] ?? null;
                    $click_key = $data['data']['click_key'] ?? null;
                    $check_serverity = isset($data['data']['check_serverity']) ? strtolower(trim($data['data']['check_serverity'])) : (isset($data['data']['level']) ? strtolower(trim($data['data']['level'])) : null);

                    
                    $where1 = ['deleted_at' => null, 'feel_type' => 'darkweb'];
                    $where = ['deleted_at' => null];
                    $orwhere = ['deleted_at' => null, 'feel_type' => 'compromise'];
                    $orwhere2 = ['deleted_at' => null, 'feel_type' => 'webserver'];
                    $orwhere3 = ['deleted_at' => null, 'feel_type' => 'server'];

                    if ($search_val == 'true') {
                        $model = DataLeakSocialRef::where('deleted_at', null)
                        ->whereHas('get_data_leak_feed_one', function ($query) {
                            $query->whereIn('feel_type', ['darkweb', 'compromise', 'webserver', 'server']);
                        })
                        ->with('get_site')
                        ->with('get_data_leak_feed_one');

                        if ($keywords) {

                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($keywords) {
                                $query->where('keyword', 'LIKE', '%' . $keywords . '%')
                                ->orWhere('feedcontent', 'LIKE', '%' . $keywords . '%');

                            });
                        }


                        if($check_type) {

                            $model = $model-> where('feel_type', '=' ,$check_type);

                        }

                        if ($check_serverity) {
                            $model = $model->where('serverity', '=', $check_serverity);
                        }

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


                        if ($site) {
                            
                            $SiteSettings = SiteSettings::where('code', @$site)->first();
                            $model = $model->where('site_id', $SiteSettings->id);
                        }

                        if ($isDateSearch == 1 && $startDate) {
                            $date_start = $startDate;
                            $date_end = $endDate;

                            $date_start_explode = explode(" ", $date_start);
                            $date_start_date = @$date_start_explode[0];
                            $date_start_time = @$date_start_explode[1] . ' ' . @$date_start_explode[2];
                            // dd($date_start_time);
                            $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                            // dd($date_start_date_format);
                            $date_start_time_time = date("H:i", strtotime($date_start_time));
                            $date_start_datetime_format = $date_start_date_format . ' ' . $date_start_time_time . ':00';
                            // dd($date_start);

                            $date_end_explode = explode(" ", $date_end);
                            $date_end_date = @$date_end_explode[0];
                            $date_end_time = @$date_end_explode[1] . ' ' . @$date_end_explode[2];
                            // dd($date_end_time);
                            $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                            $date_end_time_time = date("H:i", strtotime($date_end_time));
                            $date_end_datetime_format = $date_end_date_format . ' ' . $date_end_time_time . ':00';
                            // dd($date_end_time_time);

                            // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
                            $model = $model->whereHas('get_data_leak_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
                                $qq->whereBetween('feedtimepost', array($date_start_datetime_format, $date_end_datetime_format));
                            });
                        }


                        $model->orderBy('created_at','desc')->get();
                    } else {

                        $model = DataLeakSocialRef::where('deleted_at', null)->whereIn('feel_type', ['darkweb', 'compromise', 'webserver', 'server'])
                            ->whereHas('get_data_leak_feed_one', function ($q) use ($where1, $orwhere, $orwhere2, $orwhere3) {
                                // $q->where($where1);
                                // $q->orwhere($orwhere);
                                // $q->orwhere($orwhere2);
                                // $q->orwhere($orwhere3);
                            })
                            ->with('get_site')->with('get_data_leak_feed_one');

                            if ($click_key) {
                                $model = $model->where('keyword', $click_key);
                                // });
                            }

                            if($click_type) {

                                $model = $model-> where('feel_type', '=' ,$click_type);
    
    
                            }

                            if ($check_serverity) {
                                $model = $model->where('serverity', '=', $check_serverity);
                            }

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

                        if ($site) {
                            
                            $SiteSettings = SiteSettings::where('code', @$site)->first();
                            // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
                            $model = $model->where('site_id', $SiteSettings->id);
                            // });
                    
                        }

                        $model->orderBy('created_at','desc')->get();
                    }

                    \Log::info('Compromised Query SQL: ' . $model->toSql());
                    \Log::info('Compromised Query Bindings: ' . json_encode($model->getBindings()));
                    $res = DataTables::of($model)->make(true)->getData();
                    $res->draw = (int)($data['data']['draw'] ?? 0);

                    $response = $res;

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

    public function compromised_delete(Request $request){
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

    public function compromised_delete_select(Request $request){
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

    public function compromised_delete_change(Request $request){
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

    public function count_keyword_darkweb(Request $request){
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

                    $get_role_custom_first = $data['data']['get_role_custom_first'] ?? null;
                    $check_serverity = isset($data['data']['check_serverity']) ? strtolower(trim($data['data']['check_serverity'])) : (isset($data['data']['level']) ? strtolower(trim($data['data']['level'])) : null);

                    $model = DataLeakSocialRef::select('keyword',DB::raw('count(*)  as count_keyword'))
                            ->where('status',1)->where('deleted_at',null)
                            ->whereIn('feel_type', ['darkweb', 'compromise', 'webserver', 'server'])
                            ->groupBy('keyword');

                    if ($check_serverity) {
                        $model = $model->where('serverity', '=', $check_serverity);
                    }

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
}
