<?php

namespace Modules\Social\Http\Controllers;

use Carbon\Carbon;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\Social\Entities\Data_leak_social;
use Modules\Social\Entities\Data_leak_feed;
use Modules\Social\Entities\Bookmarks_social;
use Modules\Social\Entities\Read_social;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Users\Entities\User;
use Modules\Users\Entities\UserSite;
use App\DataLeakSocialRef;
use DB;

class SocialController extends Controller
{
    /**
     * Item Model
     *
     * @var \Modules\Items\Entities\Item
     */
    protected $item;
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['data_leak']) {
            check_permission403();
        }

        if(Auth::check()) {

            $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            if(Auth::user()->hasRole('admin')) {//if admin
                // dd(777);
                $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->get();

            } else { //if notAdmin
                // dd(888);
                if(@Auth::user()->site_role_id && @Auth::user()->site_id) {
                    if(@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) {//support and admin
                        // dd(99);

                        $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
                        ->whereIn('id', $site_id_arr)//['49', '56']
                        ->get();
             

                    } else {//not support and admin
                        $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
                        ->whereIn('id', $site_id_arr)//['49', '56']
                        ->get();
                    }
                }
            }
        }


        // $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->get();
        $Data_leak_social = Data_leak_social::where("deleted_at",null)->where("status",1)->get();
        $data['Data_leak_social'] = $Data_leak_social;
        $data['SiteSettings'] = $SiteSettings;
        $data['page'] = langapp('social');
       return view('social::index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('social::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('social::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('social::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }


    public function jqueryLoadMoreNews(Request $request){
        $role_custom = @check_role_custom();
        if(!$role_custom['data_leak']) {
            check_permission403();
        }

        $date_start = $request->date_start;
        $date_end = $request->date_end;
        $site_id = '';

        $site_code = $request ->site_id;
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

        $Data_leak_feed_all = Data_leak_feed::where('deleted_at', null)->where('status', 1)->where('feel_type', 'social')->count();

        if(($request -> title || $request -> social || $request -> date_start || $request -> date_end || $site_id) && $request -> f_search == 1){

            $news = Data_leak_feed::where('deleted_at', null)->where('status', 1)->where('feel_type', 'social');//->get() ->orderBy('created_at','desc')->paginate(10)  // selectRaw('*, count(id) as rss_new_count')
            
            if(Auth::check()) {

                $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
                if(Auth::user()->hasRole('admin')) {//if admin
                    // dd(777);
                    
    
                } else { //if notAdmin
                    // dd(888);
                    if(@Auth::user()->site_role_id && @Auth::user()->site_id) {
                        if(@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) {//support and admin
                            // dd(99);
    
                            $news = $news->whereHas('get_social', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                 
    
                        } else {//not support and admin
                            $news = $news->whereHas('get_social', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }
                    }
                }
            }
            
            if($request -> title){
                $news = $news -> where('feedcontent', 'LIKE' ,'%'.$request -> title.'%');
            }

 
            if($social) {
                // dd($social);
                // $cate_id_m = CategorySettings::where('code',$cate)->first();
                // $cate_id = @$cate_id_m->id;
                // dd($cate_id);

                // $news = $news->whereHas('get_cate', function ($query) use ($cate_id) {
                //     $query->where('news_category_id', '=', $cate_id);
                // });

            }

            if($date_start) {
                // $news = $news -> whereDate('created_at','>', $date_start_datetime_format);
                $news = $news -> whereBetween('feedtimepost',array($date_start_datetime_format,$date_end_datetime_format));
            //     ->where(function($query) use ($date_start_datetime_format,$date_end_datetime_format){
            //         $query->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))
            //               ->whereBetween('time',array($timfrom,$timto));
            //    })

            }

            if($date_end) {

            }

            if($site_id) {
                $news = $news->whereHas('get_social', function ($query) use ($site_id) {
                            $query->where('site_id', '=', $site_id);
                        });
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
            $Data_leak_feed_all = $news->count();
            $news = $news->with('get_ref')->orderBy('feedtimepost','desc')->paginate(PAGINATE_NUM);
        }else{
            $news = Data_leak_feed::where('deleted_at', null)->where('status', 1)->where('feel_type', 'social');//->get()

            if(Auth::check()) {

                $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
                if(Auth::user()->hasRole('admin')) {//if admin
                    // dd(777);
                    
    
                } else { //if notAdmin
                    // dd(888);
                    if(@Auth::user()->site_role_id && @Auth::user()->site_id) {
                        if(@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) {//support and admin
                            // dd(99);
    
                            $news = $news->whereHas('get_social', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                 
    
                        } else {//not support and admin
                            $news = $news->whereHas('get_social', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }
                    }
                }
            }

            $news = $news->with('get_ref')->orderBy('feedtimepost','desc')->paginate(PAGINATE_NUM);//->get()


        }

        // dd($news);
        $content = [];
        foreach($news as $data){
    
            $n_title = @$data -> feedcontent;
            if($site_id) {
                // $related_news_site = SiteNewsRelated::where("news_id",$data -> id)->where("site_id",$site_id)->first();
                // $news = $news->get_social;
            }
            
 
    

            // $n_detail = strip_tags($n_detail);
            // dd($n_detail);

            // $content[] = strip_tags($n_detail);
            // $content[] = $data -> detail_en;
            // dd($content);

            
            $count_view = 0;
            // if($data -> get_social) {
            //     foreach($data -> get_social as $view_val) {
            //         $count_view += $view_val->view;
            //     }
            // }

            $check_read_news = Read_social::where('user_id', Auth::user()->id)->where('data_leak_feed_id', $data -> id)->first();
            $checkBookmark = Bookmarks_social::where('user_id', Auth::user()->id)->where('data_leak_feed_id', $data -> id)->first();
            if($check_read_news){
                $html .= '<div class="list-news" style="background-color:#ececec">';
            }else{
                $html .= '<div class="list-news">';
            }

            $get_ref_name = '';
            $get_ref_name .= 'Site: ';
            if(!empty($data -> get_ref)){
                foreach ($data -> get_ref as $get_ref) {

                    if(isset($get_ref->get_site_name->name))
                    $get_ref_name = $get_ref_name.$get_ref->get_site_name->name.", ";
                }
            }

            $get_ref_name = rtrim($get_ref_name,", ");

            $html .= '
                <!--<div class="checkbox-news-select">
                    <label class="mr-3">
                        <input type="checkbox" name="" class="chk-bookmark">
                        <span class="label-text checkbox-news-input"></span>
                    </label>
                </div>-->
                
                    
                        <article class="def-rlt">
                            <div class="entry">
                                <span class="entry-category">
                                    <a href="#">'.$data -> source_name.'</a>
                                </span>
                                <h3 style="font-size: 16px;">
                                    <a href="'.$data -> feedlink.'" target="_blank" onclick="add_read('.$data -> id.')">
                                    '.$n_title.'
                                    </a>
                                </h3>
                                <div class="entry-meta">
                                    <span class="entry-view"><b>'.@$data -> feel_type.'</b></span>
                                    <span class="entry-date"> <i class="fas fa-calendar-alt"></i> '.$data -> feedtimepost.'</span>
                                    <span class="entry-view"> <i class="fas fa-eye"></i> '.@$data -> view.'</span>
                                    <span class="entry-view"><b>'.$get_ref_name.'</b></span>
                                </div>
                                <!--<div class="description-text hidden-xs">
                                <span><p>&nbsp;'.strip_tags($n_title).'</p></span>
                                </div>-->
                            </div>
                        </article>
                    
                
                <!--<div class="content-news-image">
                    <a href="'.route('news.news_detail_code',['code' => $data -> code]).'">
                        <img src="'.$data -> logo.'" alt="">
                    </a>
                </div>-->
                <div class="action-bookmark">';
                if(!empty($checkBookmark)){
                    $html .= '<i class="fas fa-bookmark bookmark-active" id="mark'.$data -> id.'" onclick="Bookmarks(this, '.$data -> id.')"></i>';
                }else{
                    $html .= '<i class="fas fa-bookmark" id="mark'.$data -> id.'" onclick="Bookmarks(this, '.$data -> id.')"></i>';
                }
                    $html .= '</div>
            </div>
            ';
        }
        // dd($content);
        if ($request->ajax()) {
            $data = [
                "html" => $html,
                "count" => $Data_leak_feed_all
            ];
            return response()->json($data); 
        }
    }

    public function jqueryLoadMoreNewsTopic(Request $request){
        $role_custom = @check_role_custom();
        if(!$role_custom['data_leak']) {
            check_permission403();
        }
        $html = '';
        $NewsTopic = NewsTopics::where('topic_id', $request->topic_id)->wherehas('news', function($q){
            $q->where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now())->orderBy('created_at','desc');
        })->get();
        foreach($NewsTopic as $data){
            $checkReadTopic = ReadTopic::where('user_id', Auth::user()->id)->where('topic_id', $data -> topic_id)->where('news_id', $data -> rss_news_id)->first();
            if(empty($checkReadTopic)){
                $ReadTopic = new ReadTopic;
                $ReadTopic -> code = generator_uuid();
                $ReadTopic -> user_id = Auth::user()->id;
                $ReadTopic -> topic_id = $data -> topic_id;
                $ReadTopic -> news_id = $data -> rss_news_id;
                $ReadTopic -> save();
            }
            
            $check_read_news = ReadNews::where('user_id', Auth::user()->id)->where('news_id', $data -> rss_news_id)->first();
            $checkBookmark = Bookmark::where('user_id', Auth::user()->id)->where('news_id', $data -> rss_news_id)->first();
            if($check_read_news){
                $html .= '<div class="list-news" style="background-color:#ececec">';
            }else{
                $html .= '<div class="list-news">';
            }
            $html .= '
                <div class="checkbox-news-select">
                    <label class="mr-3">
                        <input type="checkbox" name="" class="chk-bookmark">
                        <span class="label-text checkbox-news-input"></span>
                    </label>
                </div>
                <div class="content-news-text">
                    <a href="'.route('news.news_detail_code',['code' => $data -> news -> code]).'">
                        <span class="head-news-text">'.$data -> news -> title_th.'</span>
                    </a>
                    <div class="entry-meta">
                        <span class="entry-date"> <i class="fas fa-calendar-alt"></i> '.$data -> news -> public_date.'</span>
                        <span class="entry-view"> <i class="fas fa-eye"></i> '.$data -> news -> view.'</span>
                        <span>&nbsp;'.strip_tags($data -> news -> detail_th).'</span>
                    </div>
                </div>
                <div class="content-news-image">
                    <a href="'.route('news.news_detail_code',['code' => $data -> news -> code]).'">
                        <img src="'.$data -> news -> logo.'" alt="">
                    </a>
                </div>
                <div class="action-bookmark">';
                if(!empty($checkBookmark)){
                    $html .= '<i class="fas fa-bookmark bookmark-active" id="mark'.$data -> news -> id.'" onclick="Bookmarks(this, '.$data -> news -> id.')"></i>';
                }else{
                    $html .= '<i class="fas fa-bookmark" id="mark'.$data -> news -> id.'" onclick="Bookmarks(this, '.$data -> news -> id.')"></i>';
                }
                    $html .= '</div>
            </div>
            ';
        }
        if ($request->ajax()) {
            $data = [
                "html" => $html,
                "count" => count($NewsTopic)
            ];
            return response()->json($data); 
        }
    }

    public function jqueryLoadMoreNewsBookmark(Request $request){
        $role_custom = @check_role_custom();
        if(!$role_custom['data_leak']) {
            check_permission403();
        }
        $html = '';
        $Bookmark = Bookmarks_social::where('user_id',@Auth::user()->id)->orderBy('created_at','desc')->get();//->paginate(PAGINATE_NUM);//->get()
        // $Bookmark = Bookmarks_social::orderBy('created_at','desc')->get();
        foreach($Bookmark as $data){
            $count_view = 0;
            if($data -> data_leak_feed) {
                if($data -> data_leak_feed -> get_social) {
                    foreach($data -> data_leak_feed -> get_social as $view_val) {
                        if($view_val) {
                            $count_view += $view_val -> view;
                        }
                    }
                }
            }

            $get_ref_name = '';
            $get_ref_name .= 'Site: ';
           
            if(!empty($data -> data_leak_feed -> get_ref)){
                foreach ($data -> data_leak_feed -> get_ref as $get_ref) {
                   
                    if(isset($get_ref->get_site_name->name)){
                    $get_ref_name = $get_ref_name.$get_ref->get_site_name->name.", ";
                    
                    }
                }
            }

            $get_ref_name = rtrim($get_ref_name,", ");


            $check_read_news = Read_social::where('user_id', Auth::user()->id)->where('data_leak_feed_id', $data -> data_leak_feed_id)->first();
            if($check_read_news){
                $html .= '<div class="list-news" style="background-color:#ececec">';
            }else{
                $html .= '<div class="list-news">';
            }
            $html .= '
            <!--<div class="checkbox-news-select">
                    <label class="mr-3">
                        <input type="checkbox" name="" class="chk-bookmark">
                        <span class="label-text checkbox-news-input"></span>
                    </label>
                </div>-->

                <article class="def-rlt">
                    <div class="entry">
                        <span class="entry-category">
                            <a href="#">'.@$data -> data_leak_feed -> source_name.'</a>
                        </span>
                        <h3 style="font-size: 16px;">
                            <a href="'.@$data -> data_leak_feed -> feedlink.'" target="_blank">
                            '.@$data -> data_leak_feed -> feedcontent.'
                            </a>
                        </h3>
                        <div class="entry-meta">
                            <span class="entry-date"> <b>'.@$data -> data_leak_feed -> feel_type.'</b></span>
                            <span class="entry-date"> <i class="fas fa-calendar-alt"></i> '.@$data -> data_leak_feed -> feedtimepost.'</span>
                            <span class="entry-view"> <i class="fas fa-eye"></i> '.$count_view.'</span>
                            <span class="entry-date"> <b>'.$get_ref_name.'</b></span>
                        </div>
                        <!--<div class="description-text hidden-xs">
                        <span><p>&nbsp;'.strip_tags(@$data -> data_leak_feed -> feedcontent).'</p></span>
                        </div>-->
                    </div>
                </article>

                <!--<div class="content-news-image">
                    <a href="'.route('news.news_detail_code',['code' => @$data -> data_leak_feed -> code]).'">
                        <img src="'.@$data -> data_leak_feed -> logo.'" alt="">
                    </a>
                </div>-->
                <div class="action-bookmark">';
                    $html .= '<i class="fas fa-bookmark bookmark-active" id="mark'.@$data -> data_leak_feed -> id.'" onclick="Bookmarks(this, '.@$data -> data_leak_feed -> id.')"></i>';
                    $html .= '</div>
            </div>
            ';
        }
        if ($request->ajax()) {
            $data = [
                "html" => $html,
                "count" => count($Bookmark)
            ];
            return response()->json($data); 
        }
    }

    public function bookmark(Request $request){
        $role_custom = @check_role_custom();
        if(!$role_custom['data_leak']) {
            check_permission403();
        }
        $checkBookmark = Bookmarks_social::where('user_id', Auth::user()->id)->where('data_leak_feed_id', $request -> news_id)->first();
        if($checkBookmark){
            $checkBookmark -> delete();
        }else{
            $Bookmark = new Bookmarks_social();
            $Bookmark -> code = generator_uuid();
            $Bookmark -> user_id = Auth::user()->id;
            $Bookmark -> data_leak_feed_id = $request -> news_id;
            $Bookmark -> save();
        }
        return response()->json(); 
    }

    public function add_read(Request $request) {
       
        $addread = $request->addread;

        try {
            //  Block of code to try
        

            if($addread) {
                $RSSNews = Data_leak_feed::where("id",$addread)->first();

                $RSSNews->view = $RSSNews->view+1;
                $RSSNews->save();

                $Read_social_check = Read_social::where("id",$addread)->where('user_id',@Auth::user()->id)->first();
                if($Read_social_check) {

                } else {
                    $Read_social = new Read_social;
                    $Read_social->code = generator_uuid();
                    $Read_social->site_id = @Auth::user()->site_id;
                    $Read_social->user_id = @Auth::user()->id;
                    $Read_social->data_leak_feed_id = $addread;
                    $Read_social->status = 1;
                    $Read_social->save();
                }
    
            }


            if ($request->ajax()) {
                $data = [
                    "message" => '',
                    "success" => true
                ];
            }

        }
        catch(\Exception $e) {
        //  Block of code to handle errors
            $data = [
                "message" => $e->getMessage(),
                "success" => false
            ];
        }

        if ($request->ajax()) {
        
            return response()->json($data); 
        }

        // dd($addread);
    }


    public function count_val(Request $request){
        $role_custom = @check_role_custom();
        if(!$role_custom['data_leak']) {
            check_permission403();
        }

        // dd($request->check_type);

        $where1 = ['deleted_at' => null, 'feel_type' => 'darkweb'];
        $where = ['deleted_at' => null];
        $orwhere = ['deleted_at' => null, 'feel_type' => 'compromise'];
        $orwhere2 = ['deleted_at' => null, 'feel_type' => 'webserver'];
        $orwhere3 = ['deleted_at' => null, 'feel_type' => 'server'];
        

        $date_start = $request->startDate;
        $date_end = $request->startDate;
        $site_id = '';
        $site_code = $request ->site_id;

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

        

        if(  $request -> search_val == 1){

            $model = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->with('get_site')->with('get_data_leak_feed_one');
            $countGroupBy = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->with('get_site')->with('get_data_leak_feed_one');


            // if($request -> type) {
            //     $model = $model-> where('feel_type', '=' ,$request -> type);
            //     $countGroupBy = $countGroupBy -> where('feel_type', '=' ,$request -> type);
            // }

            // if($request -> social) {
            //     $model = $model-> where('sourceid', '=' ,$request -> social);
            //     $countGroupBy = $countGroupBy -> where('sourceid', '=' ,$request -> social);
            // }

            if ($request->keywords) {
                $keywords = $request->keywords;
                $model->whereHas('get_data_leak_feed_one', function ($query) use ($keywords) {
                    $query->where('keyword', 'LIKE', '%' . $keywords . '%')
                        ->orWhere('feedcontent', 'LIKE', '%' . $keywords . '%');
                });

                $countGroupBy->whereHas('get_data_leak_feed_one', function ($query) use ($keywords) {
                    $query->where('keyword', 'LIKE', '%' . $keywords . '%')
                        ->orWhere('feedcontent', 'LIKE', '%' . $keywords . '%');
                });
            }

            // if($request -> keywords){
            //     $model = $model->where('keyword', 'LIKE', '%' . $request->title . '%');
            //     // $news = $news -> where('feedcontent', 'LIKE' ,'%'.$request -> title.'%');
            //     // $countGroupBy = $countGroupBy -> where('feedcontent', 'LIKE' ,'%'.$request -> title.'%');
            //     $countGroupBy = $countGroupBy -> where('keyword', 'LIKE' ,'%'.$request -> title.'%');
            // }

            if($request -> check_type) {
                $model = $model-> where('feel_type', '=' ,$request -> check_type);
                $countGroupBy = $countGroupBy -> where('feel_type', '=' ,$request -> check_type);
            }

            // if($request ->click_type) {

            //     $model = $model-> where('feel_type', '=' ,$request -> click_type);
            //     $countGroupBy = $countGroupBy -> where('feel_type', '=' ,$request -> click_type);

            // }

            

  
              
                if($request -> isDateSearch==1){
                    // $news = $news -> whereBetween('feedtimepost',array($date_start_datetime_format,$date_end_datetime_format));
                    // $countGroupBy = $countGroupBy -> whereBetween('feedtimepost',array($date_start_datetime_format,$date_end_datetime_format));
                
                    $countGroupBy = $countGroupBy->whereHas('get_data_leak_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
                        $qq->whereBetween('feedtimepost', array($date_start_datetime_format, $date_end_datetime_format));
                    });

                    $model = $model->whereHas('get_data_leak_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
                        $qq->whereBetween('feedtimepost', array($date_start_datetime_format, $date_end_datetime_format));
                    });
                
                }

      



            if(Auth::check()) {

                $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
                if(Auth::user()->hasRole('admin')) {//if admin
                    // dd(777);
                    
    
                } else { //if notAdmin
                    // dd(888);
                    if(@Auth::user()->site_role_id && @Auth::user()->site_id) {
                        if(@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) {//support and admin
                            // dd(99);
    
                            $model = $model->whereIn('site_id', $site_id_arr);
            
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                 
    
                        } else {//not support and admin
                            $model = $model->whereIn('site_id', $site_id_arr);
            
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                        }
                    }
                }
            }



            if($site_id) {
                $model = $model->where('site_id', $site_id);

                $countGroupBy = $countGroupBy->where('site_id', $site_id);
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
            $Data_leak_feed_all = $model->count();
            $countGroupBy = $countGroupBy->select( 'feel_type',DB::raw('count(*) as total'))->groupBy('feel_type')->get();
            $model = $model->with('get_data_leak_feed_one')->orderBy('id','desc')->paginate(PAGINATE_NUM);
        }else{
            $Data_leak_feed_all = DataLeakSocialRef::where('deleted_at', null)->where('status', 1)->whereIn('feel_type', ['social','darkweb_public'])->count();
            $news = DataLeakSocialRef::where('deleted_at', null)->where('status', 1)->whereIn('feel_type', ['social', 'darkweb_public']);//->get()
            $countGroupBy = DataLeakSocialRef::select( 'feel_type',DB::raw('count(*) as total'))->where('deleted_at', null)->where('status', 1)->whereIn('feel_type', ['social', 'darkweb_public'])->groupBy('feel_type');
  

            if(Auth::check()) {

                $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
                if(Auth::user()->hasRole('admin')) {//if admin
                    // dd(777);
                    
                } else { //if notAdmin
                    // dd(888);
                    if(@Auth::user()->site_role_id && @Auth::user()->site_id) {
                        if(@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) {//support and admin
                            // dd(99);
    
                            $news = $news->whereIn('site_id', $site_id_arr);
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                 
                        } else {//not support and admin
                            $news = $news->whereIn('site_id', $site_id_arr);
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                        }
                    }
                }
            }

            if($site_id) {
                $news = $news->where('site_id', $site_id);
                $countGroupBy = $countGroupBy->where('site_id', $site_id);
            }

            $news = $news->with('get_data_leak_feed_one')->orderBy('id','desc')->paginate(PAGINATE_NUM);
            $countGroupBy = $countGroupBy->get();


        }

        // dd($news);
        $content = [];

        // dd($content);
        $count_sub_type["darkweb"] = 0;
        $count_sub_type["social"] = 0;
        
        foreach ($countGroupBy as $countGroup) {
            $count_sub_type[$countGroup->feel_type] = $countGroup->total;
        }
        if ($request->ajax()) {
            $data = [
                "html" => $html,
                "count" => $Data_leak_feed_all,
                "darkweb" => @$count_sub_type["darkweb_public"],
                "social" => @$count_sub_type["social"],
            ];
            return response()->json($data); 
        }
    }
}
