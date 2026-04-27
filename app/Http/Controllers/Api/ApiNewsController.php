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
use Modules\RSSFeedSettings\Entities\TransactionRssData;
use Modules\RSSFeedSettings\Entities\RSSData;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\UTCDateTime;
use Yajra\DataTables\DataTables;
use Modules\Users\Entities\User;
use Modules\WebDefacement\Entities\WebdefacmentDataCheck;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use Symfony\Polyfill\Intl\Idn\Resources\unidata\Regex;

class ApiNewsController extends ApiController
{
    public function index_client(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $RSSNews_count = RSSNews::where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now())->count();
                // dd($news_all);
                // $RSSNews_count = RSSNews::count("id");
                $RSSNews_all = RSSNews::where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now())->orderBy('public_date', 'desc')->take(PAGINATE_NUM)->get();
        
                //<><><div>
                // if(Auth::check()) {
        
                //     $site_id_arr = UserSite::select('site_id')->where('user_id', @$user_id)->get();
                //     if(Auth::user()->hasRole('admin')) {//if admin
                //         // dd(777);
                //         $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->get();
        
                //     } else { //if notAdmin
                //         // dd(888);
                //         if(@Auth::user()->site_role_id && @Auth::user()->site_id) {
                //             if(@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) {//support and admin
                //                 // dd(99);
        
                //                 $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
                //                 ->whereIn('id', $site_id_arr)//['49', '56']
                //                 ->get();
                    
        
                //             } else {//not support and admin
                //                 $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
                //                 ->whereIn('id', $site_id_arr)//['49', '56']
                //                 ->get();
                //             }
                //         }
                //     }
                // }

        
                // dd($RSSNews_all[0]->get_cate);
                // dd($RSSNews_all[0]->get_cate[0]->get_cate_name->name);
                // dd($RSSNews_count);
            $Category = CategorySettings::where('active', 1)->get();
            $NewsCategory = [];
            $ReadCategories = [];
        
            if(!empty($Category)){
                foreach($Category as $item){
                    $newsCat = RSSNewsCategory::where('news_category_id', @$item->id)->wherehas('news', function($q){
                        $q->where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now());
                    })->first();
                    
                    if ($newsCat) {
                        $NewsCategory[] = $newsCat;
                    }
                }
            } 
            if(!empty($NewsCategory)){
                    foreach($NewsCategory as $item){
                        $ReadCategories[] = ReadCategories::where('categories_id', '=',@$item['news_category_id'])->where('news_id', '=',@$item['rss_news_id'])->first();
                    }
            }
        
            $RSSNews_all = $RSSNews_all;
            $RSSNews_count = $RSSNews_count;
            $page = langapp('news');
            $Category = $Category;
            $NewsCategory = $NewsCategory;
            $ReadCategories = $ReadCategories;

            $dataOut = [
                'RSSNews_all' => $RSSNews_all,
                'RSSNews_count' => $RSSNews_count,
                'page' => $page,
                'Category' => $Category,
                'NewsCategory' => $NewsCategory,
                'ReadCategories' => $ReadCategories,
            ];

                $data_transcation = json_encode($dataOut);
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

    public function jqueryLoadMoreNews(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'news'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $title = $data['data']['title'];
                    $cate = $data['data']['cate'];
                    $related_news = $data['data']['related_news'];
                    $lang_th = $data['data']['lang_th'];
                    $lang_en = $data['data']['lang_en'];
                    $date_start = $data['data']['date_start'];
                    $date_end = $data['data']['date_end'];
                    $f_search = $data['data']['f_search'];
                    $status_serverity = $data['data']['status_serverity'];
                    $site_code = $data['data']['site_code'];
                    $user_id = $data['data']['user_id'];
                    $page = $data['data']['page'];
                    $url = $data['data']['url'];

                    $site_id = '';
        
                    if($site_code) {
                        $site_id_m = SiteSettings::where('id',$site_code)->first();
                        $site_id = @$site_id_m->id;
                    }
        
                    // $date_start_explode = explode(" ",$date_start);
                    // $date_start_date = @$date_start_explode[0];
                    // $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                    // // dd($date_start_time);
                    // $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                    // // dd($date_start_date_format);
                    // $date_start_time_time = date("H:i", strtotime($date_start_time));
                    // $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                    // // dd($date_start_time_time);
        
                    // $date_end_explode = explode(" ",$date_end);
                    // $date_end_date = @$date_end_explode[0];
                    // $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                    // // dd($date_end_time);
                    // $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                    // $date_end_time_time = date("H:i", strtotime($date_end_time));
                    // $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
                    // // dd($date_end_time_time);
        
                    // date("H:i", strtotime("04:25 PM"))
                    $html = '';
        
                    $news_all = RSSNews::where('save_draft',0)->orwhere('save_draft',null)->where('status', 1)->where('public_date', '<=', Carbon::now());
                    $news_all = $news_all->get()->count();
                    // $news_all = $news_all->where(function($q) ) {
                    //     $q->where('save_draft',1);
                    // }
        
                    // $news_all = $news_all->where(function ($query) {
                    //     $query->orwhere('save_draft', 0);
                    // });
        
                    // $news_all->get()->count();
                    // dd($news_all->get()->count());
        
                    if(($title || $cate || $related_news || $lang_th || $lang_en || $date_start || $date_end) && $f_search == 1){
        
                        $news = RSSNews::where(function ($query) {
                            $query->where('save_draft',  0)
                                ->orWhere('save_draft',  null);
                        })->where('status', 1)->where('public_date', '<=', Carbon::now());//->get() ->orderBy('created_at','desc')->paginate(10)  // selectRaw('*, count(id) as rss_new_count')
                        
                        if($title){
                            $news = $news -> where('title_th', 'LIKE' ,'%'.$title.'%');
                        }
        
                        if($lang_th=='true' && $lang_en=='true') {
                            $news = $news -> where('title_th', 'LIKE' ,'%'.$title.'%');
                        } else if($lang_th || $lang_en) {
                            if($lang_th=='true') {
                                $news = $news -> where('title_th', 'LIKE' ,'%'.$title.'%');
                            } else if ($lang_en=='true') {
                                $news = $news -> where('title_en', 'LIKE' ,'%'.$title.'%');
                            }
                        } else {
                            if($title) {
                                $news = $news -> where('title_th', 'LIKE' ,'%'.$title.'%');
                            } else {
        
                            }
                            
                        }
                    
                        // if($site_id) {
                        //     $news = $news->wherehas('get_site_news_related', function($q) use ($site_id) {
                        //         $q->where('site_id', $site_id)->where('deleted_at', null);
                        //     });
                        // }
        
        
        
                        // if($related_news == 'true') {
                        //     $news = $news->wherehas('get_site_news_related', function($q) use ($site_id) {
                        //         $q->where('site_id', $site_id)->where('deleted_at', null);
                        //     });
        
                        // }
        
                        if($cate) {
                            // dd($cate);
                            $cate_id_m = CategorySettings::where('code',$cate)->first();
                            $cate_id = @$cate_id_m->id;
                            // dd($cate_id);
                            $news = $news->whereHas('get_cate', function ($query) use ($cate_id) {
                                $query->where('news_category_id', '=', $cate_id);
                            });
        
                        }
        
                        if($date_start) {

                            $date_start_datetime_format = Carbon::createFromFormat('Y-m-d h:i A', $date_start)->format('Y-m-d H:i:s');
                            $date_end_datetime_format = Carbon::createFromFormat('Y-m-d h:i A', $date_end)->format('Y-m-d H:i:s');

                            // $news = $news -> whereDate('created_at','>', $date_start_datetime_format);
                            $news = $news -> whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format));
                        //     ->where(function($query) use ($date_start_datetime_format,$date_end_datetime_format){
                        //         $query->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))
                        //               ->whereBetween('time',array($timfrom,$timto));
                        //    })
        
                        }
        
                        // if($date_end) {
        
                        // }

                        if($status_serverity){
                            $news = $news -> where('serverity', $status_serverity);
                        }
        
                        // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
        
                        //  $news = $news->get();
                        // $news->orderBy('created_at','desc')->paginate(10);
                        // $news = RSSNews::where('save_draft', 0);//->get()
                        // $news -> paginate(10);//->get()
                        // $news = RSSNews::where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now())->orderBy('created_at','desc')->paginate(10);//->get()
                        // $news = $news->get();
                        // dd($news->get());
                        // dd($news);
                        // dd($news->total);

                        $news_all = $news->count();
                        
                        $news = $news->orderBy('public_date','desc')->take(PAGINATE_NUM)->offset($page >= 1 ? $page * 10 : 0)->get();

                        $count_model = count($news);

                        if($count_model > 0)
                        {
                            $id = '';

                            $DB_MONGO_KEY = config('app.DB_MONGO_DEV');
                            $client = new MongoClient($DB_MONGO_KEY);
                            if(app()->environment('local'))
                            {
                                $collection_actor = $client->sosecure_threatintelligent->fx_otx_adversaries;
                                $conn = $client->sosecure_threatintelligent->fx_otx_adversaries_related;
                                // $collection_actor = $client->sosecure_threatintelligent_test->fx_otx_adversaries;
                                // $conn = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
                            }
                            else
                            {
                                $collection_actor = $client->sosecure_threatintelligent_test->fx_otx_adversaries;
                                $conn = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
                            }


                            for($i=0;$i<$count_model;$i++)
                            {
                                $query_camp= [
                                    'pulse_id' => $news[$i]['id'],
                                    'mode' => 'news',
                                    'join' => 'campainge',
                                    'delete_at'  => null
                                ];
                                $option_camp = [];
                        
                                $final_camp = $conn->find($query_camp,$option_camp);
                                $result_camp = $final_camp->toArray();
                                $count_result_camp = count($result_camp);

                                $news[$i]['campainge'] = $result_camp;
                                $news[$i]['count_campainge'] = $count_result_camp;

                                // $query= [
                                //     'pulse_id' => $news[$i]['id'],
                                //     'mode' => 'news',
                                //     'join' => 'actor',
                                //     'delete_at'  => null
                                // ];
                                // $option = [];
                        
                                // $final_test = $conn->find($query,$option);
                                // $result_test = $final_test->toArray();
                                // $count_result_test = count($result_test);
                                
                                // $news[$i]['actor'] = $result_test;
                                // $news[$i]['count_result'] = $count_result_test;
                                
                                // $logo_actor = array();
                                // if(is_array($result_test) || is_object($result_test)){
                                //     foreach(@$result_test as $sel_data_act)
                                //     {
                                //         $query_sel_act = [
                                //             'adversary_uuid' => $sel_data_act['adversary_uuid']
                                //         ];
                                //         $option_sel_act = [];
                                //         $result_sel_act = $collection_actor->findOne($query_sel_act,$option_sel_act);
                                        
                                //         if(@$result_sel_act['logo'])
                                //         {
                                //             $logo_actor[] = $result_sel_act['logo'];
                                //         }
                                //         else
                                //         {
                                //             $logo_actor[] = '/asset_salepage/images/AgentBasedDetection.png';
                                //         }
                                        
                                //         // $news[$i]['logo'] = $logo;
                                //         $news[$i]['logo_actor'] = $logo_actor;
                                //     }
                                // }
                        
                            }
                        }

                    }else{
                        $news = RSSNews::where(function ($query) {
                            $query->where('save_draft',  0)
                                ->orWhere('save_draft',  null);
                        })->where('status', 1)->where('public_date', '<=', Carbon::now());//->get()
        
                        if($site_id) {
                            $news = $news->wherehas('get_site_news_related', function($q) use ($site_id) {
                                $q->where('site_id', $site_id)->where('deleted_at', null);
                            });
                        }

                        
                        $news_all = $news->count();
                        
                        $news = $news->orderBy('public_date','desc')->take(PAGINATE_NUM)->offset($page >= 1 ? $page * 10 : 0)->get();
                        
                        $count_model = count($news);

                        if($count_model > 0)
                        {
                            $id = '';

                            $DB_MONGO_KEY = config('app.DB_MONGO_DEV');
                            $client = new MongoClient($DB_MONGO_KEY);
                            if(app()->environment('local'))
                            {
                                $collection_actor = $client->sosecure_threatintelligent->fx_otx_adversaries;
                                $conn = $client->sosecure_threatintelligent->fx_otx_adversaries_related;
                            }
                            else
                            {
                                $collection_actor = $client->sosecure_threatintelligent_test->fx_otx_adversaries;
                                $conn = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
                            }


                            for($i=0;$i<$count_model;$i++)
                            {
                                $query_camp= [
                                    'pulse_id' => $news[$i]['id'],
                                    'mode' => 'news',
                                    'join' => 'campainge',
                                    'delete_at'  => null
                                ];
                                $option_camp = [];
                        
                                $final_camp = $conn->find($query_camp,$option_camp);
                                $result_camp = $final_camp->toArray();
                                $count_result_camp = count($result_camp);

                                $news[$i]['campainge'] = $result_camp;
                                $news[$i]['count_campainge'] = $count_result_camp;

                                // $query= [
                                //     'pulse_id' => $news[$i]['id'],
                                //     'mode' => 'news',
                                //     'join' => 'actor',
                                //     'delete_at'  => null
                                // ];
                                // $option = [];
                        
                                // $final_test = $conn->find($query,$option);
                                // $result_test = $final_test->toArray();
                                // $count_result_test = count($result_test);
                                
                                // $news[$i]['actor'] = $result_test;
                                // $news[$i]['count_result'] = $count_result_test;
                                
                                // $logo_actor = array();
                                // if(is_array($result_test) || is_object($result_test)){
                                //     foreach(@$result_test as $sel_data_act)
                                //     {
                                //         $query_sel_act = [
                                //             'adversary_uuid' => $sel_data_act['adversary_uuid']
                                //         ];
                                //         $option_sel_act = [];
                                //         $result_sel_act = $collection_actor->findOne($query_sel_act,$option_sel_act);
                                        
                                //         if(@$result_sel_act['logo'])
                                //         {
                                //             $logo_actor[] = $result_sel_act['logo'];
                                //         }
                                //         else
                                //         {
                                //             $logo_actor[] = '/asset_salepage/images/AgentBasedDetection.png';
                                //         }
                                        
                                //         // $news[$i]['logo'] = $logo;
                                //         $news[$i]['logo_actor'] = $logo_actor;
                                //     }
                                // }
                        
                            }
                        }
                        
                        
                    }
        
                    // dd($news);
                    $content = [];
                    $tz = new \DateTimeZone('Asia/Bangkok');
                    foreach($news as $item){
                        $related_news_site = '';
                        $icon_related= '';
                        $n_title = @$item -> title_th;
                        $n_detail = @$item -> detail_th;
                        if($site_id) {
                            $related_news_site = SiteNewsRelated::where("news_id",$item -> id)->where("site_id",$site_id)->first();
                        }
                        
                        if($related_news_site) {
                            $icon_related = '<i class="fas fa-newspaper"></i>';
                        } else {
                            $icon_related = '';
                        }
        
                        if($lang_th=='true' && $lang_en=='true') {
                            $n_title = $item -> title_th;
                            $n_detail = $item -> detail_th;
        
                            // $n_title = $data -> title_en;
                        } else if($lang_th=='true') {
                            if($lang_th=='true') {
                                $n_title = $item -> title_th;
                                $n_detail = $item -> detail_th;
                            } else if ($lang_en=='true') {
                                $n_title = $item -> title_en;
                                $n_detail = $item -> detail_en;
                            }
        
                            // $n_title = $data -> title_en;
                        } else if ($lang_en=='true') {
                            if ($lang_en=='true') {
                                $n_title = $item -> title_en;
                                $n_detail = $item -> detail_en;
                            } else if ($lang_th=='true') {
                                $n_title = $item -> title_th;
                                $n_detail = $item -> detail_th;
                            }
        
                            // $n_title = $item -> title_en;
                        } else {
                            if(@$item -> title_th) {
                                $n_title = $item -> title_th;
                                $n_detail = $item -> detail_th;
                            } else {
                                $n_title = $item -> title_en;
                                $n_detail = $item -> detail_en;
                            }
        
                            // $n_title = $item -> title_en;
            
                        }
        
                        // $n_detail = strip_tags($n_detail);
                        // dd($n_detail);
        
                        $content[] = strip_tags($n_detail);
                        // $content[] = $item -> detail_en;
                        // dd($content);
        
                        $new_html = '';
                        $date_day = '1900-01-01 12:51:17';
                        if(!empty($item -> public_date)){
                            $date_day = $item -> public_date;
                        }
                        $datework = Carbon::parse($date_day)->startOfDay();
                        $datework = $datework->setTimezone($tz);
            
                        $date_now = Carbon::now()->startOfDay();
                        $date_now = $date_now->setTimezone($tz);
                        $carbondiff = $datework->diffInDays($date_now);
                        if($carbondiff === 0){
                            $new_html.= '<span class="badge" style="background-color: #2196f3;">New</span>';
                        }
        
        
                        $check_read_news = ReadNews::where('user_id', $user_id)->where('news_id', $item -> id)->first();
                        $checkBookmark = Bookmark::where('user_id', $user_id)->where('news_id', $item -> id)->first();
                        if($check_read_news){
                            $html .= '<div class="list-news">';
                            $font_weight = '';
                        }else{
                            $html .= '<div class="list-news" style="background-color:#ececec">';
                            $font_weight = 'font-weight: bold !important;';
                        }
        
                        if(@$item->transaction_rss_id) {
                            if(@$item->logo) {
                                $logo_url = @$item->logo;
                            } else {
                                $logo_url = @$item->logo_rss;
                            }
                        } else {
                            $logo_url = @$item->logo;
                        }

                        $html_cate_all = '';
                        if(!empty($item->get_cate)) {
                            $num = count($item->get_cate);
                            $i = 0;
                            foreach($item->get_cate as $cate_id_val) {
                                ++$i;
                                if(!empty($cate_id_val)) {
                                    if($i === $num){
                                        $html_cate_all .= @$cate_id_val->get_cate_name->name;
                                    }
                                    else{
                                        $html_cate_all .= @$cate_id_val->get_cate_name->name.",";
                                    }
                                    
                                }
                            }
                        }

                        $html_source_all_full = '';
                        $html_source_all = '';
                        if(!empty($item->source)) {
                            $html_source_all .= '<span class="badge badge-info" style="background-color:#17a2b8;">'.@$item->source.'</span> &nbsp;';  
                        }
                        if(!empty($html_source_all)) {
                            $html_source_all_full = '<b>Source: </b>'.$html_source_all;
                        } else {
                            $html_source_all_full = '<b>Source: </b><span class="badge badge-info" style="background-color:#17a2b8;">None</span> &nbsp;';
                        }


                        $html .= '
                            <!--<div class="checkbox-news-select">
                                <label class="mr-3">
                                    <input type="checkbox" name="" class="chk-bookmark">
                                    <span class="label-text checkbox-news-input"></span>
                                </label>
                            </div>-->
                            <div class="content-news-text">
                                '.$new_html.'';
                                // <a href="'.$url.'/news/detail/'.$item -> code.'">
                        $html .= '<span class="head-news-text text-elip-ovf" style="'.@$font_weight.'">'.$icon_related.' '.$n_title.'</span>';
                                // </a>
                                // <div class="entry-meta">
                        $html .= '<div class="">
                                <span class="entry-view"> <i class="fas fa-eye"></i> '.$item -> view.'</span>
                                <span class="entry-date"> <i class="fas fa-calendar-alt"></i> '.$item -> public_date.'</span>
                                <span> <b>Serverity: </b> ';
                                    if($item->serverity=='critical'){
                                        $html .=  '<span class="badge" style="background-color: #b93624;">Critical</span>';
                                    }else if($item->serverity=='high'){
                                        $html .= '<span class="badge" style="background-color: #fcc838;">High</span>';
                                    }else if($item->serverity=='medium'){
                                        $html .= '<span class="badge" style="background-color: #f2ff15;color:#333;">Medium</span>';
                                    }else if($item->serverity=='low'){
                                        $html .= '<span class="badge" style="background-color: #88ce4f;">Low</span>';
                                    }else if($item->serverity=='information'){
                                        $html .= '<span class="badge" style="background-color: #00dcff;">Information</span>';
                                    }else{
                                        $html .= '-';
                                    }
            
                        $html .= '
                                </span> &nbsp; 
                                <span class="entry-cate"> <b>Categories: </b>'.'<span style="word-break: break-all;display: inline;" class="showmore">'.$html_cate_all.'<button class="btn-showmore btn-link text-primary" style="padding:0;padding-left:1px;padding-right:1px;" onclick="showMore(this)">ดูเพิ่มเติม</button></span>&nbsp;</span>&nbsp;'.@$html_source_all_full.'
                        ';
                        if($n_detail)
                        {
                            $html .= '<span><p class="details-news-elip">&nbsp;</p></span>';
                            // $html .= '<span><p class="details-news-elip">&nbsp;'.strip_tags($n_detail).'</p></span>';
                        }

                                // if($item->count_campainge > 0)
                                // {
                                // $html .= '<span class="m-r-5 m-l-xs" style="display: inline-flex;align-items: center;">
                                //             <b>Actor : </b>
                                //             <div class="m-l-xs">';
                                //             $array_row = 1;
                                //             $count_result = $item->count_result;
                                //             for($i = 0 ; $i < $item->count_result ; $i++)
                                //             {
                                //                 if($array_row == $count_result)
                                //                 {
                                //                     $html .= '<span><img class="icon_sm_actor m-r-xs" src="'.$item->logo_actor[$i].'"><span>
                                //                             '.$item->actor[$i]->adversary_name.'';
                                //                 }
                                //                 else
                                //                 {
                                //                     $html .= '<span><img class="icon_sm_actor m-r-xs" src="'.$item->logo_actor[$i].'"><span>
                                //                             '.$item->actor[$i]->adversary_name.', ';
                                //                 }
                                //                 $array_row = $array_row+1;
                                //             }

                                //     $html .= '
                                //             </div>
                                //         </span>
                                //     ';
                                // }

                                if($item->count_campainge > 0)
                                {
                                    $html .= ' <span class="m-r-md">
                                            <b>Campainge : </b>';
                    
                                            $array_row = 1;
                                            $count_campainge = $item->count_campainge;
                                            for($i = 0 ; $i < @$item->count_campainge ; $i++)
                                            {
                                                if($array_row == $count_campainge)
                                                {
                                                    $html .= ''.$item->campainge[$i]->adversary_name.'';
                                                }
                                                else
                                                {
                                                    $html .= ''.$item->campainge[$i]->adversary_name.' , ';
                                                }
                                                $array_row = $array_row+1;
                                            }
                                            
                                    $html .='</span> ';
                                }

                            $html .='</div>
                            </div>
                            <div class="" style="text-align: right; padding: 0rem 3rem; width: 20%;">';
                            // <div class="content-news-text" style="text-align: right; padding: 0rem 3rem; width: 20%;">';
                            // <div class="content-news-image">';
                                // <a href="'.$url.'/news/detail/'.$item -> code.'">
                                //     <img src="'.$logo_url.'" alt="" onerror="setDefaultPic(this)">
                                // </a>
                            $link = '';
                            $link_th = '';
                            $link_en = '';
                            $link_line = '';

                            if($item->title_th) {
                                $link_th = '<a class="btn btn-sm btn-info" href="public/news/detail/'.$item -> code.'/th" target="_blank">TH</a>';
                                
                            }
                            if($item->title_en) {
                                $link_en = '<a class="btn btn-sm btn-info" href="public/news/detail/'.$item -> code.'/en" target="_blank">EN</a>';
                                // $link_line = ' | ';
                            }
                            $html .= '
                                <div style="display: flex; align-items: center; justify-content: flex-end;">
                                    <div style="text-align: right;">
                            ';
                            $html .= $link_th.$link_en;
                            $html .= '</div>
                                    <div class="content-news-image" style="padding: 0rem 1rem;">';
                            if($item->title_th) 
                            {
                                $html .=  '<a href="public/news/detail/'.$item -> code.'/th" target="_blank">';
                            }
                            else if($item->title_en)
                            {
                                $html .=  '<a href="public/news/detail/'.$item -> code.'/en" target="_blank">';
                            }
                            else
                            {
                                $html .=  '<a href="#">';
                            }
                            $html .= '      <img src="'.$logo_url.'" alt="" onerror="setDefaultPic(this)" style="left: 0;">
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="action-bookmark">';
                            if(!empty($checkBookmark)){
                                $html .= '<i class="fas fa-bookmark bookmark-active" id="mark'.$item -> id.'" onclick="Bookmarks(this, '.$item -> id.')"></i>';
                            }else{
                                $html .= '<i class="fas fa-bookmark" id="mark'.$item -> id.'" onclick="Bookmarks(this, '.$item -> id.')"></i>';
                            }
                                $html .= '</div>
                        </div>
                        ';
                    }
                    $dataOut = [
                        "html" => $html,
                        "count" => $news_all,
                        "news" => $news
                    ];

                    $data_transcation = json_encode($dataOut);
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

    public function url_bookmark(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'news'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $user_id = $data['data']['user_id'];
                    $news_id = $data['data']['news_id'];

                    $checkBookmark = Bookmark::where('user_id', $user_id)->where('news_id', $news_id)->first();
                    if($checkBookmark){
                        $checkBookmark -> delete();
                    }else{
                        $Bookmark = new Bookmark();
                        $Bookmark -> code = generator_uuid();
                        $Bookmark -> user_id = $user_id;
                        $Bookmark -> news_id = $news_id;
                        $Bookmark -> save();
                    }

                    $dataOut = [
                        "data" => '',
                    ];
                    $data_transcation = json_encode($dataOut);
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

    public function url_news_detail_code(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'news'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $code = $data['data']['code'];
                    $user_id = $data['data']['user_id'];
                    $RSSNews_prev = '';
                    $RSSNews_next = '';
                    $RSSNews_last10 = '';
                    $lang = 'th';
                    $RSSNews = RSSNews::where("code",$code)->with('get_cate')->first();

                    if (!$RSSNews) {
                        return response()->json(['error' => 'News not found', 'status_code' => '404']);
                    }

                    $cate_id_all = [];
                    if($RSSNews->get_cate) {
                        foreach($RSSNews->get_cate as $cate) {
                            $cate->get_cate_name->id;
                            $cate_id_all[] = intval($cate->get_cate_name->id);
                            // dd($cate->get_cate_name->id);
                        }
                    }
                    // dd($cate_id_all);


                    if($cate_id_all) {
                        $NewsCategory = RSSNewsCategory::whereIn('news_category_id', $cate_id_all)->where('status',1)->get();
                        // dd($NewsCategory);
                        
                        
                        $rss_news_id_array = [];
                        if($NewsCategory) {
                            foreach($NewsCategory as $NewsCategory_val) {
                                if($NewsCategory_val->rss_news_id == $RSSNews->id) {

                                } else {
                                    $rss_news_id_array[] = intval($NewsCategory_val->rss_news_id);
                                }
                                // dd($topic->topic->id);
                            }
                        }
                        // dd($rss_news_id_array);
                        if($rss_news_id_array) {
                            $RSSNews_last10 = RSSNews::whereIn('id', $rss_news_id_array)->where('status',1)->where('public_date', '<=', Carbon::now())->orderBy('created_at','DESC')->limit(10)->get();
                            // dd($RSSNews_last10);
                        }


                        $rss_news_id_all_array = [];
                        if($NewsCategory) {
                            foreach($NewsCategory as $NewsCategory_val) {
                                
                                    $rss_news_id_all_array[] = intval($NewsCategory_val->rss_news_id);
                                
                                // dd($topic->topic->id);
                            }
                        }
                        // dd($rss_news_id_all_array);
                        if($rss_news_id_all_array) {
                            $RSSNews_prev = RSSNews::whereIn('id', $rss_news_id_all_array)->where('status',1)->where('id','<',$RSSNews->id)->orderBy('created_at','DESC')->limit(1)->first();
                            $RSSNews_next = RSSNews::whereIn('id', $rss_news_id_all_array)->where('status',1)->where('id','>',$RSSNews->id)->orderBy('created_at','DESC')->limit(1)->first();
                            // dd($RSSNews_last10);
                        }
                        
                    }

                    // dd($RSSNews_prev);
                    // dd($RSSNews_next);
                    if($lang == 'th') {
                        $RSSNews_name = $RSSNews->title_th;
                        $RSSNews_detail = $RSSNews->detail_th;
                    } else {
                        $RSSNews_name = $RSSNews->title_en;
                        $RSSNews_detail = $RSSNews->detail_en;
                    }


                    $dataOut['RSSNews_prev'] = $RSSNews_prev;
                    $dataOut['RSSNews_next'] = $RSSNews_next;
                    $dataOut['RSSNews_last10'] = $RSSNews_last10;
                    $dataOut['RSSNews_name'] = $RSSNews_name;
                    $dataOut['RSSNews_detail'] = $RSSNews_detail;
                    $dataOut['lang'] = $lang;
                    $dataOut['RSSNews'] = $RSSNews;
                    $dataOut['page'] = langapp('news_detail');
                    // $RSSNews;
                    $ReadNews_data = ReadNews::where('user_id',$user_id)->where('news_id',$RSSNews->id)->where('status',1)->first();
                    if($ReadNews_data) {

                    } else {
                        $ReadNews = new ReadNews;
                        $ReadNews->code = generator_uuid();
                        $ReadNews->site_id = null;
                        $ReadNews->user_id = $user_id;
                        $ReadNews->news_id = $RSSNews->id;
                        $ReadNews->save();
                    }


                    $RSSNews->view = $RSSNews->view+1;
                    $RSSNews->save();

                    $data_transcation = json_encode($dataOut);
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

    public function jqueryLoadMoreNewsBookmark(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'news'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $html = '';
                    $user_id = $data['data']['user_id'];
                    $url = $data['data']['url'];
                    $Bookmark = Bookmark::where('user_id',@$user_id)->orderBy('created_at','desc')->with('news')->get();
                    if($Bookmark) {
                        foreach($Bookmark as $item){
                            $check_read_news = ReadNews::where('user_id', $user_id)->where('news_id', $item -> rss_news_id)->first();
                            if($check_read_news){
                                $html .= '<div class="list-news">';
                                $font_weight = 'font-weight: bold !important;';
                            }else{
                                $html .= '<div class="list-news" style="background-color:#ececec">';
                                $font_weight = '';
                            }
        
                            if(@$item -> news -> transaction_rss_id) {
                                if(@$item -> news -> logo) {
                                    $url_logo = config('app.URL_CENTER_PUBLISH').@$item -> news -> logo;
                                    $url_logo = '/images/icon/news_default.png';
                                } else {
                                    $url_logo = @$item -> news -> logo_rss;
                                    
                                }
                            } else {
                                $url_logo = config('app.URL_CENTER_PUBLISH').@$item -> news -> logo;
                                $url_logo = '/images/icon/news_default.png';
                            }


                            $html_cate_all = '';
                            if(!empty($item->news->get_cate)) {
                                foreach(@$item->news->get_cate as $cate_id_val) {
                                    if(!empty($cate_id_val)) {
                                        $html_cate_all .= '<span class="badge badge-primary" style="background-color:#007bff;">'.@$cate_id_val->get_cate_name->name.'</span> &nbsp;';
                                    }
                                }
                            }

                            $html_source_all_full = '';
                            $html_source_all = '';
                            if(!empty($item->news->source)) {
                                $html_source_all .= '<span class="badge badge-info" style="background-color:#17a2b8;">'.@$item->news->source.'</span> &nbsp;';  
                            }
                            if(!empty($html_source_all)) {
                                $html_source_all_full = 'Source: '.$html_source_all;
                            } else {
                                $html_source_all_full = 'Source: <span class="badge badge-info" style="background-color:#17a2b8;">None</span> &nbsp;';
                            }
        
        
                            $html .= '
                            <!--<div class="checkbox-news-select">
                                    <label class="mr-3">
                                        <input type="checkbox" name="" class="chk-bookmark">
                                        <span class="label-text checkbox-news-input"></span>
                                    </label>
                                </div>-->
                                <div class="content-news-text">
                                    <a href="'.$url.'/news/detail/'.@$item -> news -> code.'">
                                        <span class="head-news-text" style="'.@$font_weight.'">'.@$item -> news -> title_th.'</span>
                                    </a>
                                    <div class="entry-meta">
                                        <span class="entry-date"> <i class="fas fa-calendar-alt"></i> '.@$item -> news -> public_date.'</span>
                                        <span class="entry-view"> <i class="fas fa-eye"></i> '.@$item -> news -> view.'</span>
                                        <span> <b>Serverity: </b> ';
                                        if($item->news->serverity=='critical'){
                                            $html .=  '<span class="badge" style="background-color: #b93624;">Critical</span>';
                                        }else if($item->news->serverity=='high'){
                                            $html .= '<span class="badge" style="background-color: #fcc838;">High</span>';
                                        }else if($item->news->serverity=='medium'){
                                            $html .= '<span class="badge" style="background-color: #f2ff15;color:#333;">Medium</span>';
                                        }else if($item->news->serverity=='low'){
                                            $html .= '<span class="badge" style="background-color: #88ce4f;">Low</span>';
                                        }else if($item->news->serverity=='information'){
                                            $html .= '<span class="badge" style="background-color: #00dcff;">Information</span>';
                                        }else{
                                            $html .= '-';
                                        }
                                        $html .='</span> &nbsp; <span class="entry-cate"> <b>Categories: </b>'.$html_cate_all.'</span>'
                                        .@$html_source_all_full.
                                        '<span><p></p>&nbsp;'.strip_tags(@$item -> news -> detail_th).'</p></span>
                                    </div>
                                </div>
                                <div class="content-news-image">
                                    <a href="'.$url.'/news/detail/'.@$item -> news -> code.'">
                                        <img src="'.$url_logo.'" alt="" onerror="setDefaultPic(this)">
                                    </a>
                                </div>
                                <div class="action-bookmark">';
                                    $html .= '<i class="fas fa-bookmark bookmark-active" id="mark'.@$item -> news -> id.'" onclick="Bookmarks(this, '.@$item -> news -> id.')"></i>';
                                    $html .= '</div>
                            </div>
                            ';
                        }
                    }
                    $dataOut = [
                        "html" => $html,
                        "count" => count($Bookmark)
                    ];

                    $data_transcation = json_encode($dataOut);
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

    public function news_load_top_source(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'news'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $get_role_custom = $data['data']['get_role_custom'];
                    $site = $data['data']['site'];
                    $keywords = $data['data']['keywords'];
                    $status_news = $data['data']['status_news'];
                    $news_source = $data['data']['news_source'];
                    $news_category = $data['data']['news_category'];
                    $search_val = $data['data']['search_val'];
                    $startDate = $data['data']['startDate'];
                    $endDate = $data['data']['endDate'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $status_serverity = $data['data']['status_serverity'];
                    $site_id_arr = $data['data']['site_id_arr'];
                    $user_id = $data['data']['user_id'];
     

                    // $site_id = '';
        
                    // if($site) {
                    //     // $site_id_m = SiteSettings::where('id',$site)->first();
                    //     $site_id = @$site;
                    // }
        
                    // $model = new RSSNews();
                    // if($search_val == 1){

                    //     $model = $model->where(function ($query) {
                    //                             $query->where('save_draft',  0)
                    //                                   ->orWhere('save_draft',  null);
                    //                     })->where('status', 1)->where('public_date', '<=', Carbon::now());
                        
                    //     if($keywords){
                    //         $model_where = RSSNews::where('title_en', 'LIKE' ,'%'.$keywords.'%')->first();
                            
                    //         if($model_where) {
                    //             $model = $model -> where('title_en', 'LIKE' ,'%'.$keywords.'%');
                    //         } else {
                    //             $model = $model -> where('title_th', 'LIKE' ,'%'.$keywords.'%');
                    //         }
                            
                    //     }

                    //     if($isDateSearch == 1){
                    //         $date_start = $startDate;
                    //         $date_end = $endDate;

                    //         $date_start_explode = explode(" ",$date_start);
                    //         $date_start_date = @$date_start_explode[0];
                    //         $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                    //         // dd($date_start_time);
                    //         $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                    //         // dd($date_start_date_format);
                    //         $date_start_time_time = date("H:i", strtotime($date_start_time));
                    //         $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                    //         // dd($date_start);

                    //         $date_end_explode = explode(" ",$date_end);
                    //         $date_end_date = @$date_end_explode[0];
                    //         $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                    //         // dd($date_end_time);
                    //         $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                    //         $date_end_time_time = date("H:i", strtotime($date_end_time));
                    //         $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
                    //         // dd($date_end_time_time);

                    //         // $model -> whereDate('transcation_date', Carbon::parse($public_date)->format('Y-m-d'));
                    //         $model = $model -> whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format));
                    //     }

                    //     // if($status_news){
                    //     //     if($status_news == 1 || $status_news == 2){
                    //     //         if($status_news == 1) {
                    //     //             $model =  $model -> where('save_draft','=',0);
                                    
                    //     //         } else if ($status_news == 2) {
                    //     //             $model =  $model -> where('save_draft',1);
                    //     //             // dd($model);
                    //     //         }
                                
                    //     //     }  
                    //     // }

                    //     if($news_source){

                    //         // $model -> where('source', 'LIKE' ,'%'.$news_source.'%');
                    //         $model = $model -> whereIn('source', $news_source);
                    //     }

                    //     // if($news_category){
                    //     //     $news_cate_id = $news_category;
                    //     //     $model =  $model -> whereHas('get_cate', function ($query) use ($news_cate_id) {
                    //     //         $query->whereIn('news_category_id', $news_cate_id);
                    //     //     });
                    //     // }

                    //     // if($site_id) {
                    //     //     $model = $model->wherehas('get_site_news_related', function($q) use ($site_id) {
                    //     //         $q->where('site_id', $site_id)->where('deleted_at', null);
                    //     //     });
                    //     // }

                    //     if($news_category) {
                    //         // dd($news_category);
                    //         $news_category_id_m = CategorySettings::where('code',$news_category)->first();
                    //         $news_category_id = @$news_category_id_m->id;
                    //         // dd($news_category_id);
                    //         $model = $model->whereHas('get_cate', function ($query) use ($news_category_id) {
                    //             $query->where('news_category_id', '=', $news_category_id);
                    //         });
        
                    //     }

                    //     if($status_serverity){

                    //         $model = $model -> where('serverity', $status_serverity);
                    //     }
                        
                    // }

                    // $model = $model         
                    // ->select(DB::raw('count(*) as source_count , source as source'))
                    // ->groupBy('source')
                    // ->orderBy('source_count', 'desc')
                    // ->limit(11)
                    // ->get();

                    // $host2 = array();
                    // foreach($model as $value){
                    //     if(empty($value->source)||$value->source=='None'){
                    //         if(!isset($host2['None'])){
                    //             $host2['None'] = 0;
                    //         }
                    //         $host2['None'] = $host2['None']+(int)$value->source_count;
                    //     }else{
                    //         $host2[$value->source] = (int)$value->source_count;
                    //     }
                    // }
                    // arsort($host2);
                    // $countLimit = 0;
                    // $host = array();
                    // foreach ($host2 as $key => $value) {
                    //     $countLimit++;
                    //     if($countLimit<11){
                    //         $host[] = [$key,$value];
                    //     }
                    // }
        
       
                    // $dataOut = [
                    //     "html" => $html,
                    //     "count" => $news_all
                    // ];

                    $data_summary = DB::Table('summary')
                        ->where([
                            'data_key' => 'new', 
                            'data_key_2' => 'top_10_source', 
                            'status' => 'Y'
                        ])
                        ->first();

                    $host = @$data_summary->data_value ? json_decode($data_summary->data_value, true) : ' ';

                    $data_transcation = json_encode($host);
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

    public function news_load_top_category(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'news'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $get_role_custom = $data['data']['get_role_custom'];
                    $site = $data['data']['site'];
                    $keywords = $data['data']['keywords'];
                    $status_news = $data['data']['status_news'];
                    $news_source = $data['data']['news_source'];
                    $news_category = $data['data']['news_category'];
                    $search_val = $data['data']['search_val'];
                    $startDate = $data['data']['startDate'];
                    $endDate = $data['data']['endDate'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $status_serverity = $data['data']['status_serverity'];
                    $site_id_arr = $data['data']['site_id_arr'];
                    $user_id = $data['data']['user_id'];


                    // $site_id = '';
        
                    // if($site) {
                    //     // $site_id_m = SiteSettings::where('id',$site_code)->first();
                    //     $site_id = @$site;
                    // }
        
                    
                    // $model = new RSSNews();
                    // if($search_val == 1){

                    //     $model = $model->where(function ($query) {
                    //                             $query->where('save_draft',  0)
                    //                                 ->orWhere('save_draft',  null);
                    //                     })->where('status', 1)->where('public_date', '<=', Carbon::now());

                    //     if($keywords){
                    //         $model_where = RSSNews::where('title_en', 'LIKE' ,'%'.$keywords.'%')->first();
                            
                    //         if($model_where) {
                    //             $model = $model -> where('title_en', 'LIKE' ,'%'.$keywords.'%');
                    //         } else {
                    //             $model = $model -> where('title_th', 'LIKE' ,'%'.$keywords.'%');
                    //         }
                            
                    //     }
            
                    //     if($isDateSearch == 1){
                    //         $date_start = $startDate;
                    //         $date_end = $endDate;
            
                    //         $date_start_explode = explode(" ",$date_start);
                    //         $date_start_date = @$date_start_explode[0];
                    //         $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                    //         // dd($date_start_time);
                    //         $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                    //         // dd($date_start_date_format);
                    //         $date_start_time_time = date("H:i", strtotime($date_start_time));
                    //         $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                    //         // dd($date_start);
            
                    //         $date_end_explode = explode(" ",$date_end);
                    //         $date_end_date = @$date_end_explode[0];
                    //         $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                    //         // dd($date_end_time);
                    //         $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                    //         $date_end_time_time = date("H:i", strtotime($date_end_time));
                    //         $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
                    //         // dd($date_end_time_time);
            
                    //         // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
                    //         $model = $model -> whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format));
                    //     }
            
                    //     // if($status_news){
                    //     //     if($status_news == 1 || $status_news == 2){
                    //     //         if($status_news == 1) {
                    //     //             $model =  $model -> where('save_draft','=',0);
                                    
                    //     //         } else if ($status_news == 2) {
                    //     //             $model =  $model -> where('save_draft',1);
                    //     //             // dd($model);
                    //     //         }
                                
                    //     //     }  
                    //     // }

                    //     if($news_source){
            
                    //         // $model -> where('source', 'LIKE' ,'%'.$news_source.'%');
                    //         $model = $model -> whereIn('source', $news_source);
                    //     }
            
                    //     // if($news_category){
                    //     //     $news_cate_id = $news_category;
                    //     //     $model =  $model -> whereHas('get_cate', function ($query) use ($news_cate_id) {
                    //     //         $query->whereIn('news_category_id', $news_cate_id);
                    //     //     });
                    //     // }

                    //     // if($site_id) {
                    //     //     $model = $model->wherehas('get_site_news_related', function($q) use ($site_id) {
                    //     //         $q->where('site_id', $site_id)->where('deleted_at', null);
                    //     //     });
                    //     // }

                    //     if($news_category) {
                    //         // dd($news_category);
                    //         $news_category_id_m = CategorySettings::where('code',$news_category)->first();
                    //         $news_category_id = @$news_category_id_m->id;
                    //         // dd($news_category_id);
                    //         $model = $model->whereHas('get_cate', function ($query) use ($news_category_id) {
                    //             $query->where('news_category_id', '=', $news_category_id);
                    //         });
        
                    //     }

                    //     if($status_serverity){
                    //         $model = $model -> where('serverity', $status_serverity);
                    //     }
                        
                    // }
                   
                    // // $RSSNewsCategory = RSSNewsCategory::select('rss_news_id','categories.name')->join('categories','categories.id','=','r_s_s_news_categories.news_category_id')->where('categories.active',1);
                    // // dd($RSSNewsCategory->get());
                    // // $model = $model         
                    // // ->select('categories.name as categories_name_',DB::raw('count(*) as categories_count'))
                    // // ->leftjoin('r_s_s_news_categories','r_s_s_news.id','=','r_s_s_news_categories.rss_news_id')
                    // // ->leftjoin('categories','categories.id','=','r_s_s_news_categories.news_category_id')
                    // // ->where('categories.active',1)
                    // // ->where('categories.deleted_at',null)
                    // // ->groupBy('categories.name')
                    // // ->orderBy('categories_count', 'desc')
                    // // ->limit(10)
                    // // ->get();
            
                    // $model = $model
                    // ->select('name_cat as categories_name_',DB::raw('count(*) as categories_count'))
                    // ->leftjoin(DB::raw('(SELECT fx_r_s_s_news_categories.rss_news_id as rssid ,fx_r_s_s_news_categories.news_category_id as category_id, fx_categories.name as name_cat FROM fx_r_s_s_news_categories,fx_categories
                    // where fx_r_s_s_news_categories.news_category_id = fx_categories.id 
                    // and fx_categories.active=1 and fx_categories.deleted_at is null) as fx_TotalCatches'), 
                    // function($join)
                    // {
                    //    $join->on('r_s_s_news.id', '=', 'TotalCatches.rssid');
                    // })
                    // ->addSelect('category_id')
                    // ->groupBy('name_cat')
                    // ->orderBy('categories_count', 'desc')
                    // ->limit(10)
                    // ->get();
            
            
                    // // $sql = "SELECT count(*) as categories_count ,name_cat FROM fx_r_s_s_news LEFT JOIN
                    // // (SELECT fx_r_s_s_news_categories.rss_news_id as rssid,fx_categories.name as name_cat FROM fx_r_s_s_news_categories,fx_categories
                    // // where fx_r_s_s_news_categories.news_category_id = fx_categories.id 
                    // // and fx_categories.active=1) as test
                    // // on fx_r_s_s_news.id = test.rssid
                    // // group by name_cat
                    // // order by categories_count desc
                    // // limit 10" ;
                    // // $model = DB::select( DB::raw($sql));
            
            
                    // $host = array();
                    // $color=['#3B3D50','#ECC44D','#DA4C62','#E95C83','#6F57E9','#7698A0','#02CCCD','#A8C5CC','#A0D0C8','#E7DED4'];
                    // foreach($model as $key => $value){
            
                    //     $host[] = array(
                    //         'name' => empty($value->categories_name_)?'None':$value->categories_name_,
                    //         'y' => (int)$value->categories_count,
                    //         'color' => $color[$key] ,
                    //         'data' => $value->category_id
                    //     );
            
                        
                    // }
            
        
       
                    // $dataOut = [
                    //     "html" => $html,
                    //     "count" => $news_all
                    // ];

                    $data_summary = DB::Table('summary')
                        ->where([
                            'data_key' => 'new', 
                            'data_key_2' => 'top_10_categories', 
                            'status' => 'Y'
                        ])
                        ->first();

                    $host = @$data_summary->data_value ? json_decode($data_summary->data_value, true) : ' ';

                    $data_transcation = json_encode($host);
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
    public function rss_news_table(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);

            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }

            $input = $data['data'] ?? [];
            $request->merge($input);
            $model = new RSSNews();

            if ($input['keywords'] ?? null) {
                $kw = $input['keywords'];
                $model = $model->where(function($q) use ($kw) {
                    $q->where('title_en', 'LIKE', '%' . $kw . '%')
                      ->orWhere('title_th', 'LIKE', '%' . $kw . '%');
                });
            }

            if (($input['isDateSearch'] ?? null) == 1) {
                $date_start = $input['startDate'];
                $date_end = $input['endDate'];
                $date_start_f = Carbon::parse($date_start)->format('Y-m-d H:i:s');
                $date_end_f = Carbon::parse($date_end)->format('Y-m-d H:i:s');
                $model = $model->whereBetween('created_at', [$date_start_f, $date_end_f]);
            }

            if (isset($input['status_news']) && ($input['status_news'] == 1 || $input['status_news'] == 2)) {
                $model = $model->where('save_draft', ($input['status_news'] == 1 ? 0 : 1));
            }

            if ($input['news_source'] ?? null) {
                $news_source_id = is_array($input['news_source']) ? $input['news_source'] : [$input['news_source']];
                $news_source_id = array_filter($news_source_id);
                if (!empty($news_source_id)) {
                    $model = $model->whereIn('source', $news_source_id);
                }
            }

            if ($input['news_category'] ?? null) {
                $news_cate_id = is_array($input['news_category']) ? $input['news_category'] : [$input['news_category']];
                $news_cate_id = array_filter($news_cate_id);
                if (!empty($news_cate_id)) {
                    $model = $model->whereHas('get_cate', function ($query) use ($news_cate_id) {
                        $query->whereIn('news_category_id', $news_cate_id);
                    });
                }
            }

            if ($input['status_serverity'] ?? null) {
                $model = $model->where('serverity', $input['status_serverity']);
            }

            $model = $model->select('id','code','title_th','detail_th','title_en','detail_en','source','save_draft','serverity','public_date','status','created_at')
                           ->orderBy('public_date', 'desc');

            $result = DataTables::of($model)
                ->addColumn('content_detail', function ($model) {
                    $html = '';
                    $html .= '<div>';
                    if ($model->title_th) {
                        $html .= '<a style="font-size:16px;" href="' . route('news.public_detail_select', ['code' => $model->code, 'lang' => 'th']) . '" target="_blank" data-rel="tooltip" title="' . $model->title_th . '">' . $model->title_th . '</a>';
                    } else if ($model->title_en) {
                        $html .= '<a style="font-size:16px;" href="' . route('news.public_detail_select', ['code' => $model->code, 'lang' => 'en']) . '" target="_blank" data-rel="tooltip" title="' . $model->title_en . '">' . $model->title_en . '</a>';
                    } else {
                        $html .= '<span style="font-size:16px;">No Title</span>';
                    }
                    $html .= '</div>';

                    if ($model->source) {
                        // return '<div class="text-elip" data-rel="tooltip" title="'.$model -> source.'"><a href="javascript:void(0);" onclick="find_source(\''.$model -> source.'\')">'.$model -> source.'</a></div>';
                        $html .= '<span data-rel="tooltip" title="' . $model->source . '"><span class="m-r-5"><b>Source : </b>' . $model->source . '</span>';
                    } else {
                        $html .= '<span data-rel="tooltip" title="None"><span class="m-r-5"><b>Source : </b> None</span>';
                    }

                    $html .= '<span class="text-trucate-ovf"> <span class="m-r-5 m-l-xs"><b>Category : </b>';
                    $html .= '<span style="word-break: break-all;display: inline;" class="showmore">';
                    if ($model->get_cate == "[]") {
                        $html .= 'None';
                    } else {
                        $catagory_name = "";
                        $num = count($model->get_cate);
                        $i = 0;
                        foreach ($model->get_cate as $record) {
                            if (++$i === $num) {
                                $catagory_name .= @$record->get_cate_name->name;
                            } else {
                                $catagory_name .= @$record->get_cate_name->name . ",";
                            }
                        }
                        $htmls = '';
                        $htmls .= $catagory_name;
                        if ($htmls == 'None-delete0') {
                            $html .= str_replace('-delete0', '', $htmls);
                        } else {
                            $html .= str_replace('None-delete0', '', $htmls);
                        }
                    }
                    $html .= '<button class="btn-showmore btn-link text-primary" style="padding:0;padding-left:1px;padding-right:1px;" onclick="showMore(this)">ดูเพิ่มเติม</button></span>';

                    $html .= '</span>';

                    $html .= '<div>';

                    $html .= ' <span class="m-r-5"><b>Public Date : </b>' . $model->public_date . '</span>';

                    if ($model->serverity == 'critical') {
                        $html .= ' <span class="m-r-5"><b>Serverity : </b> <span class="badge" style="background-color: #b93624;">Critical</span></span>';
                    } else if ($model->serverity == 'high') {
                        $html .= ' <span class="m-r-5"><b>Serverity : </b> <span class="badge" style="background-color: #fcc838;">High</span></span>';
                    } else if ($model->serverity == 'medium') {
                        $html .= ' <span class="m-r-5"><b>Serverity : </b> <span class="badge" style="background-color: #f2ff15;color:#333;">Medium</span></span>';
                    } else if ($model->serverity == 'low') {
                        $html .= ' <span class="m-r-5"><b>Serverity : </b> <span class="badge" style="background-color: #88ce4f;">Low</span></span>';
                    } else if ($model->serverity == 'information') {
                        $html .= ' <span class="m-r-5"><b>Serverity : </b> <span class="badge" style="background-color: #00dcff;">Information</span></span>';
                    } else {
                        $html .= ' <span class="m-r-5"><b>Serverity : </b> - </span>';
                    }

                    if ($model->save_draft == 1) {
                        $html .= ' <b class="m-r-5 m-l-xs">Data Status : </b> <span class="badge badge-danger" style="background-color: #ea2e49;">Darft</span>';
                    } else if ($model->save_draft == 0) {
                        $html .= ' <b class="m-r-5 m-l-xs">Data Status : </b> <span class="badge badge-success">Public</span>';
                    } else {
                        $html .= ' <b class="m-r-5 m-l-xs">Data Status : </b> <span class="badge badge-warning" style="background-color: #ffc107;">Not used</span>';
                    }
                    
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('link', function ($model) {
                    $html = '';
                    $html_th = '';
                    $html_en = '';
                    $html_line = '';
                    if ($model->title_th) {
                        $html_th = '<a href="' . route('news.public_detail_select', ['code' => $model->code, 'lang' => 'th']) . '" target="_blank">TH</a>';
                    }
                    if ($model->title_en) {
                        $html_en = '<a href="' . route('news.public_detail_select', ['code' => $model->code, 'lang' => 'en']) . '" target="_blank">EN</a>';
                        $html_line = ' | ';
                    }
    
                    $html .= $html_th . $html_line . $html_en;
    
                    return $html;
                })
                ->rawColumns(['content_detail', 'link'])
                ->toJson();

            $data_transcation = json_encode($result->getData());
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);

        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'message' => $e->getMessage()]);
        }
    }

    public function rss_data_table(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);

            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }

            $input = $data['data'] ?? [];
            $request->merge($input);
            $model = TransactionRssData::with('get_rss_news')->with('get_rss_source');

            if (($input['keywords'] ?? null || $input['startDate'] ?? null || $input['endDate'] ?? null || $input['status'] ?? null) && ($input['search_val'] ?? null) == true) {
                if ($input['keywords'] ?? null) {
                    $model->where('title', 'LIKE', '%' . $input['keywords'] . '%');
                }
                if ($input['isDateSearch'] ?? null) {
                    $date_start_f = Carbon::parse($input['startDate'])->format('Y-m-d');
                    $date_end_f = Carbon::parse($input['endDate'])->format('Y-m-d');
                    $model->whereBetween('transcation_date', [$date_start_f, $date_end_f]);
                }
                if ($input['status'] ?? null) {
                    if ($input['status'] == '1') {
                        $model = $model->whereHas('get_rss_news', fn($q) => $q->whereNotNull('transaction_rss_id'));
                    } else if ($input['status'] == '2') {
                        $model = $model->whereDoesntHave('get_rss_news', fn($q) => $q->whereNotNull('transaction_rss_id'));
                    }
                }
            }

            $result = DataTables::of($model)
                ->addColumn('chk', function ($data) {
                    return '<label class="checkbox"><input type="checkbox" name="id[]" value="' . $data->id . '" class="chk-rss"><span></span></label>';
                })
                ->editColumn('title', function ($data) {
                    return '<strong>' . $data->title . '</strong><br><small class="text-muted">' . $data->get_rss_source->name . '</small>';
                })
                ->editColumn('link', function ($data) {
                    return '<a href="' . $data->link . '" target="_blank" class="text-info"><i class="fas fa-external-link-alt"></i> Original Link</a>';
                })
                ->addColumn('status', function ($data) {
                    if ($data->get_rss_news) {
                        return '<span class="badge badge-success">Migrated</span>';
                    }
                    return '<span class="badge badge-warning">Pending</span>';
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group">
                        <button class="btn btn-xs btn-info create-news-from-rss" data-code="' . $data->code . '"><i class="fas fa-plus"></i> Create News</button>
                        <button class="btn btn-xs btn-danger delete-rss-data" data-code="' . $data->code . '"><i class="fas fa-trash"></i></button>
                    </div>';
                })
                ->rawColumns(['chk', 'title', 'link', 'status', 'action'])
                ->toJson();

            $data_transcation = json_encode($result->getData());
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);

        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'message' => $e->getMessage()]);
        }
    }

    public function rss_setting_table(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);

            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }

            $input = $data['data'] ?? [];
            $request->merge($input);
            $model = RSSData::whereNull('deleted_at');
            $result = DataTables::of($model)
                ->addColumn('chk', function ($data) {
                    return '<label class="checkbox"><input type="checkbox" name="id[]" value="' . $data->id . '" class="chk-setting"><span></span></label>';
                })
                ->editColumn('status', function ($data) {
                    $checked = $data->active == 1 ? 'checked' : '';
                    return '<label class="switch"><input type="checkbox" class="setting-toggle" data-id="' . $data->id . '" ' . $checked . '><span class="slider round"></span></label>';
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group">
                        <button class="btn btn-xs btn-default edit-setting" data-code="' . $data->code . '"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-xs btn-danger delete-setting" data-code="' . $data->code . '"><i class="fas fa-trash"></i></button>
                    </div>';
                })
                ->rawColumns(['chk', 'status', 'action'])
                ->toJson();

            $data_transcation = json_encode($result->getData());
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);

        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'message' => $e->getMessage()]);
        }
    }
}
