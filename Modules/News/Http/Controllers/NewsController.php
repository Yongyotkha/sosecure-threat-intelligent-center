<?php

namespace Modules\News\Http\Controllers;

use App\Bookmark;
use App\ReadNews;
use App\ReadTopic;
use App\ReadCategories;
use App\Topic;
use Carbon\Carbon;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\SiteCategory;
use Modules\SiteSettings\Entities\SiteNewsRelated;
use Modules\RSSFeedSettings\Entities\NewsTopics;
use Modules\RSSFeedSettings\Entities\RSSNews;
use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Modules\CategorySettings\Entities\CategorySettings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
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
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function test()
    {
        $SiteSettings = SiteSettings::all();
        $RSSNews_all = RSSNews::all();
        $SiteNewsRelated = SiteNewsRelated::all();

        $news_array = array();
        // $new_array_sub = [];
        if($RSSNews_all) {
            foreach($RSSNews_all as $key => $val) {
                $val_news_id = $val->id;
                // $val->get_cate[0]->get_cate_name->id;
                $new_array_sub = [];
                if($val->get_cate) {
                    foreach($val->get_cate as $key2 => $val2) {
               
                        $new_array_sub['news_id'] = $val_news_id;
                        $new_array_sub['cate_id'] = $val2->news_category_id;
       
                    }
                    array_push($news_array, $new_array_sub);
                }

            }
        }

        $site_array = [];
        if($SiteSettings) {
            foreach($SiteSettings as $key => $val) {
                $val_news_id = $val->id;
                // $val->get_cate[0]->get_cate_name->id;
                $site_array_sub = [];
                if($val->get_categorys) {
                    foreach($val->get_categorys as $key2 => $val2) {

               
                        $site_array_sub['site_id'] = $val_news_id;
                        $site_array_sub['cate_id'] = $val2->category_id;

                    }
                    array_push($site_array, $site_array_sub);
                }

            }
        }

        $site_news_related = [];
        if($SiteNewsRelated) {
            foreach($SiteNewsRelated as $key => $val) {
                $val_news_related_id = $val->site_id;
                // $val->get_cate[0]->get_cate_name->id;
                $news_related_array_sub = [];
                if($val->get_news) {
                    foreach($val->get_news[0]->get_cate as $key2 => $val2) {

                        $news_related_array_sub['site_id'] = $val_news_related_id;
                        $news_related_array_sub['cate_id'] = $val2->news_category_id;

                    }
                    array_push($site_news_related, $news_related_array_sub);
                }

            }
        }

        // dd($news_array);
        // dd($site_array);
        dd($site_news_related);
        // dd($SiteNewsRelated[0]->get_news[0]->get_cate);
        // dd($SiteNewsRelated[0]->get_news->get_cate);


        
       return view('news::index')->with($data);
    }

    public function index()
    {
        $RSSNews_count = RSSNews::where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now())->count();
        // dd($news_all);
        // $RSSNews_count = RSSNews::count("id");
        $RSSNews_all = RSSNews::all();
        // dd($RSSNews_all[0]->get_cate);
        // dd($RSSNews_all[0]->get_cate[0]->get_cate_name->name);
        // dd($RSSNews_count);
       $Category = CategorySettings::where('active', 1)->get();
       $NewsCategory = [];
       $ReadCategories = [];
  
       if(!empty($Category)){
        foreach($Category as $data){
            $NewsCategory[] = RSSNewsCategory::where('news_category_id', @$data->id)->wherehas('news', function($q){
                $q->where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now());
            })->first();
           }
       } 
       if(!empty($NewsCategory)){
            foreach($NewsCategory as $data){
                $ReadCategories[] = ReadCategories::where('categories_id', '=',@$data['news_category_id'])->where('news_id', '=',@$data['rss_news_id'])->first();
           }
       }

       $RSSNews_all = $RSSNews_all;
       $RSSNews_count = $RSSNews_count;
       $page = langapp('news');
       $Category = $Category;
       $NewsCategory = $NewsCategory;
       $ReadCategories = $ReadCategories;
       return view('news::index',compact('RSSNews_all','RSSNews_count','page','Category','NewsCategory','ReadCategories'));
    }

    public function news_detail()
    {
        $data['page'] = langapp('news_detail');
       return view('news::news_detail')->with($data);
    }

    public function news_detail_code($code)
    {
        $RSSNews_prev = '';
        $RSSNews_next = '';
        $RSSNews_last10 = '';
        $lang = 'th';
        $RSSNews = RSSNews::where("code",$code)->first();

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


        $data['RSSNews_prev'] = $RSSNews_prev;
        $data['RSSNews_next'] = $RSSNews_next;
        $data['RSSNews_last10'] = $RSSNews_last10;
        $data['RSSNews_name'] = $RSSNews_name;
        $data['RSSNews_detail'] = $RSSNews_detail;
        $data['lang'] = $lang;
        $data['RSSNews'] = $RSSNews;
        $data['page'] = langapp('news_detail');
        // $RSSNews;
        $ReadNews_data = ReadNews::where('user_id',@Auth::user()->id)->where('news_id',$RSSNews->id)->where('status',1)->first();
        if($ReadNews_data) {

        } else {
            $ReadNews = new ReadNews;
            $ReadNews->code = generator_uuid();
            $ReadNews->site_id = null;
            $ReadNews->user_id = @Auth::user()->id;
            $ReadNews->news_id = $RSSNews->id;
            $ReadNews->save();
        }


        $RSSNews->view = $RSSNews->view+1;
        $RSSNews->save();
       return view('news::news_detail')->with($data);
    }
    
    public function public_detail()
    {
       $data['page'] = langapp('news_detail');
       return view('news::public_detail')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('news::create');
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
        return view('news::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('news::edit');
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

        $date_start = $request->date_start;
        $date_end = $request->date_end;

        $title = $request ->title;
        $cate = $request ->cate;
        $related_news = $request ->related_news;
        $lang_th = $request ->lang_th;
        $lang_en = $request ->lang_en;

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

        $news_all = RSSNews::where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now())->count();

        if(($request -> title || $request -> cate || $request -> related_news || $request -> lang_th || $request -> lang_en || $request -> date_start || $request -> date_end) && $request -> f_search == 1){

            $news = RSSNews::where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now());//->get() ->orderBy('created_at','desc')->paginate(10)  // selectRaw('*, count(id) as rss_new_count')
            if($request -> title){
                $news = $news -> where('title_th', 'LIKE' ,'%'.$request -> title.'%');
            }

            if($lang_th=='true' && $lang_en=='true') {
                $news = $news -> where('title_th', 'LIKE' ,'%'.$title.'%')->orwhere('title_en', 'LIKE' ,'%'.$title.'%');
            } else if($lang_th || $lang_en) {
                if($lang_th=='true') {
                    $news = $news -> where('title_th', 'LIKE' ,'%'.$title.'%');
                } else if ($lang_en=='true') {
                    $news = $news -> where('title_en', 'LIKE' ,'%'.$title.'%');
                }
            } else {
                if($title) {
                    $news = $news -> where('title_th', 'LIKE' ,'%'.$title.'%')->orwhere('title_en', 'LIKE' ,'%'.$title.'%');
                } else {

                }
                
            }

            if($related_news) {

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
            $news = $news->orderBy('created_at','desc')->paginate(PAGINATE_NUM);
        }else{
            $news = RSSNews::where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now())->orderBy('created_at','desc')->paginate(PAGINATE_NUM);//->get()
        }

        // dd($news);

        foreach($news as $data){
            $check_read_news = ReadNews::where('user_id', Auth::user()->id)->where('news_id', $data -> id)->first();
            $checkBookmark = Bookmark::where('user_id', Auth::user()->id)->where('news_id', $data -> id)->first();
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
                    <a href="'.route('news.news_detail_code',['code' => $data -> code]).'">
                        <span class="head-news-text">'.$data -> title_th.'</span>
                    </a>
                    <div class="entry-meta">
                        <span class="entry-date"> <i class="fas fa-calendar-alt"></i> '.$data -> public_date.'</span>
                        <span class="entry-view"> <i class="fas fa-eye"></i> '.$data -> view.'</span>
                        <span>&nbsp;'.$data -> detail_th.'</span>
                    </div>
                </div>
                <div class="content-news-image">
                    <a href="'.route('news.news_detail_code',['code' => $data -> code]).'">
                        <img src="'.$data -> logo.'" alt="">
                    </a>
                </div>
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
        if ($request->ajax()) {
            $data = [
                "html" => $html,
                "count" => $news_all
            ];
            return response()->json($data); 
        }
    }

    public function jqueryLoadMoreNewsTopic(Request $request){
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
                        <span>&nbsp;'.$data -> news -> detail_th.'</span>
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
        $html = '';
        $Bookmark = Bookmark::orderBy('created_at','desc')->get();
        foreach($Bookmark as $data){
            $check_read_news = ReadNews::where('user_id', Auth::user()->id)->where('news_id', $data -> rss_news_id)->first();
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
                        <span>&nbsp;'.$data -> news -> detail_th.'</span>
                    </div>
                </div>
                <div class="content-news-image">
                    <a href="'.route('news.news_detail_code',['code' => $data -> news -> code]).'">
                        <img src="'.$data -> news -> logo.'" alt="">
                    </a>
                </div>
                <div class="action-bookmark">';
                    $html .= '<i class="fas fa-bookmark bookmark-active" id="mark'.$data -> news -> id.'" onclick="Bookmarks(this, '.$data -> news -> id.')"></i>';
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
        $checkBookmark = Bookmark::where('user_id', Auth::user()->id)->where('news_id', $request -> news_id)->first();
        if($checkBookmark){
            $checkBookmark -> delete();
        }else{
            $Bookmark = new Bookmark();
            $Bookmark -> code = generator_uuid();
            $Bookmark -> user_id = Auth::user()->id;
            $Bookmark -> news_id = $request -> news_id;
            $Bookmark -> save();
        }
        return response()->json(); 
    }
}
