<?php

namespace Modules\News\Http\Controllers;

use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\SiteCategory;
use Modules\SiteSettings\Entities\SiteNewsRelated;
use Modules\RSSFeedSettings\Entities\NewsTopics;

use Modules\RSSFeedSettings\Entities\RSSNews;
use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

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
       $data['RSSNews_all'] = $RSSNews_all;
       $data['RSSNews_count'] = $RSSNews_count;
       $data['page'] = langapp('news');
       return view('news::index')->with($data);
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

        $topic_id_all = [];
        if($RSSNews->get_topic_multi) {
            foreach($RSSNews->get_topic_multi as $topic) {
                $topic->topic->id;
                $topic_id_all[] = intval($topic->topic->id);
                // dd($topic->topic->id);
            }
        }
        // dd($topic_id_all);


        if($topic_id_all) {
            $NewsTopics = NewsTopics::whereIn('topic_id', $topic_id_all)->where('status',1)->get();
            // dd($NewsTopics);
            
            
            $rss_news_id_array = [];
            if($NewsTopics) {
                foreach($NewsTopics as $NewsTopics_val) {
                    if($NewsTopics_val->rss_news_id == $RSSNews->id) {

                    } else {
                        $rss_news_id_array[] = intval($NewsTopics_val->rss_news_id);
                    }
                    // dd($topic->topic->id);
                }
            }
            // dd($rss_news_id_array);
            if($rss_news_id_array) {
                $RSSNews_last10 = RSSNews::whereIn('id', $rss_news_id_array)->where('status',1)->orderBy('public_date','DESC')->limit(10)->get();
                // dd($RSSNews_last10);
            }


            $rss_news_id_all_array = [];
            if($NewsTopics) {
                foreach($NewsTopics as $NewsTopics_val) {
                    
                        $rss_news_id_all_array[] = intval($NewsTopics_val->rss_news_id);
                    
                    // dd($topic->topic->id);
                }
            }
            // dd($rss_news_id_all_array);
            if($rss_news_id_all_array) {
                $RSSNews_prev = RSSNews::whereIn('id', $rss_news_id_all_array)->where('status',1)->where('id','<',$RSSNews->id)->orderBy('public_date','DESC')->limit(1)->first();
                $RSSNews_next = RSSNews::whereIn('id', $rss_news_id_all_array)->where('status',1)->where('id','>',$RSSNews->id)->orderBy('public_date','DESC')->limit(1)->first();
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
}
