<?php

namespace Modules\News\Http\Controllers;

use App\Bookmark;
use App\ReadNews;
use App\ReadTopic;
use App\Topic;
use Carbon\Carbon;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\SiteCategory;
use Modules\SiteSettings\Entities\SiteNewsRelated;

use Modules\RSSFeedSettings\Entities\RSSNews;
use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\RSSFeedSettings\Entities\NewsTopics;

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
        $RSSNews_count = RSSNews::count("id");
        $RSSNews_all = RSSNews::all();
        // dd($RSSNews_all[0]->get_cate);
        // dd($RSSNews_all[0]->get_cate[0]->get_cate_name->name);
        // dd($RSSNews_count);
       $topic = Topic::where('status', 1)->get();
       $NewsTopic = [];
       $ReadTopic = [];
       foreach($topic as $data){
        $NewsTopic[] = NewsTopics::where('topic_id', $data->id)->wherehas('news', function($q){
            $q->where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now());
        })->first();
       }
       foreach($NewsTopic as $data){
        $ReadTopic[] = ReadTopic::where('topic_id', '=',$data['topic_id'])->where('news_id', '=',$data['rss_news_id'])->first();
       }
       $data['RSSNews_all'] = $RSSNews_all;
       $data['RSSNews_count'] = $RSSNews_count;
       $data['page'] = langapp('news');
       $data['topic'] = $topic;
       $data['NewsTopic'] = $NewsTopic;
       $data['ReadTopic'] = $ReadTopic;
       return view('news::index')->with($data);
    }

    public function news_detail()
    {
        $data['page'] = langapp('news_detail');
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
        $html = '';
        $news = RSSNews::where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now())->get();
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
                    <a href="#">
                        <span class="head-news-text">'.$data -> title_th.'</span>
                    </a>
                    <div class="entry-meta">
                        <span class="entry-date"> <i class="fas fa-calendar-alt"></i> '.$data -> public_date.'</span>
                        <span class="entry-view"> <i class="fas fa-eye"></i> '.$data -> view.'</span>
                        <span>&nbsp;'.$data -> detail_th.'</span>
                    </div>
                </div>
                <div class="content-news-image">
                    <a href="#">
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
                "count" => count($news)
            ];
            return response()->json($data); 
        }
    }

    public function jqueryLoadMoreNewsTopic(Request $request){
        $html = '';
        $NewsTopic = NewsTopics::where('topic_id', $request->topic_id)->wherehas('news', function($q){
            $q->where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now());
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
                    <a href="#">
                        <span class="head-news-text">'.$data -> news -> title_th.'</span>
                    </a>
                    <div class="entry-meta">
                        <span class="entry-date"> <i class="fas fa-calendar-alt"></i> '.$data -> news -> public_date.'</span>
                        <span class="entry-view"> <i class="fas fa-eye"></i> '.$data -> news -> view.'</span>
                        <span>&nbsp;'.$data -> news -> detail_th.'</span>
                    </div>
                </div>
                <div class="content-news-image">
                    <a href="#">
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
