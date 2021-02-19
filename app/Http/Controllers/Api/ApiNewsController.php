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
                $RSSNews_all = RSSNews::all();
        
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
                    $NewsCategory[] = RSSNewsCategory::where('news_category_id', @$item->id)->wherehas('news', function($q){
                        $q->where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now());
                    })->first();
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
                    $site_code = $data['data']['site_code'];
                    $user_id = $data['data']['user_id'];
                    $page = $data['data']['page'];
                    $url = $data['data']['url'];

                    $site_id = '';
        
                    if($site_code) {
                        $site_id_m = SiteSettings::where('id',$site_code)->first();
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
                    
                        if($site_id) {
                            $news = $news->wherehas('get_site_news_related', function($q) use ($site_id) {
                                $q->where('site_id', $site_id)->where('deleted_at', null);
                            });
                        }
        
        
        
                        if($related_news == 'true') {
                            $news = $news->wherehas('get_site_news_related', function($q) use ($site_id) {
                                $q->where('site_id', $site_id)->where('deleted_at', null);
                            });
        
                        }
        
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
                            // $news = $news -> whereDate('created_at','>', $date_start_datetime_format);
                            $news = $news -> whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format));
                        //     ->where(function($query) use ($date_start_datetime_format,$date_end_datetime_format){
                        //         $query->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))
                        //               ->whereBetween('time',array($timfrom,$timto));
                        //    })
        
                        }
        
                        if($date_end) {
        
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
                        // $news = $news->orderBy('created_at','desc')->paginate(PAGINATE_NUM);
                        
                        $news = $news->orderBy('created_at','desc')->take(PAGINATE_NUM)->offset($page >= 2 ? $page * 10 : 0)->get();
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
                        // $news = $news->orderBy('created_at','desc')->paginate(PAGINATE_NUM);
                        $news = $news->orderBy('created_at','desc')->take(PAGINATE_NUM)->offset($page >= 2 ? $page * 10 : 0)->get();
                    }
        
                    // dd($news);
                    $content = [];
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
                        $html .= '
                            <!--<div class="checkbox-news-select">
                                <label class="mr-3">
                                    <input type="checkbox" name="" class="chk-bookmark">
                                    <span class="label-text checkbox-news-input"></span>
                                </label>
                            </div>-->
                            <div class="content-news-text">
                                <a href="'.$url.'/news/detail/'.$item -> code.'">
                                    <span class="head-news-text text-elip-ovf" style="'.@$font_weight.'">'.$icon_related.' '.$n_title.'</span>
                                </a>
                                <div class="entry-meta">
                                <span class="entry-view"> <i class="fas fa-eye"></i> '.$item -> view.'</span>
                                <span class="entry-date"> <i class="fas fa-calendar-alt"></i> '.$item -> public_date.'</span>
                                <span><p class="details-news-elip">&nbsp;'.strip_tags($n_detail).'</p></span>
                                </div>
                            </div>
                            <div class="content-news-image">
                                <a href="'.$url.'/news/detail/'.$item -> code.'">
                                    <img src="'.$logo_url.'" alt="" onerror="setDefaultPic(this)">
                                </a>
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
                        "count" => $news_all
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
                    $Bookmark = Bookmark::where('user_id',@$user_id)->orderBy('created_at','desc')->get();
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
                                } else {
                                    $url_logo = @$item -> news -> logo_rss;
                                }
                            } else {
                                $url_logo = config('app.URL_CENTER_PUBLISH').@$item -> news -> logo;
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
                                        <span><p></p>&nbsp;'.strip_tags(@$item -> news -> detail_th).'</p></span>
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
