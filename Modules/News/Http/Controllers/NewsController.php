<?php

namespace Modules\News\Http\Controllers;

use App\Bookmark;
use App\Log;
use App\ReadNews;
use App\ReadTopic;
use App\ReadCategories;
use App\Topic;
use Carbon\Carbon;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\SiteCategory;
use Modules\SiteSettings\Entities\SiteNewsRelated;
use Modules\SiteSettings\Entities\SiteNewsRelatedTemp;
use Modules\RSSFeedSettings\Entities\NewsTopics;
use Modules\RSSFeedSettings\Entities\RSSNews;
use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Modules\CategorySettings\Entities\CategorySettings;
use Modules\Users\Entities\User;
use Modules\Users\Entities\UserSite;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PgSql\Lob;

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
        $SiteCategory = SiteCategory::all();

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
                    $new_array_sub_cal = array_filter($new_array_sub);
                    array_push($news_array, $new_array_sub_cal);
                }

            }
        }

        $site_array = [];
        // if($SiteSettings) {
        //     foreach($SiteSettings as $key => $val) {
                
        //         // $val->get_cate[0]->get_cate_name->id;
        //         // $site_array_sub = [];
        //         if($val) {
        //             $val_news_id = $val->id;
        //             if($val->get_categorys) {
        //                 foreach($val->get_categorys as $key2 => $val2) {
        //                     if($val2) {
        //                         $site_array_sub['site_id'] = $val_news_id;
        //                         $site_array_sub['cate_id'] = $val2->category_id;
        //                     }
        //                 }
        //                 $site_array_sub_cal = array_filter($site_array_sub);
        //                 array_push($site_array, $site_array_sub_cal);
        //             }
        //         }
        //     }
        // }

        if($SiteCategory) {
            foreach($SiteCategory as $key => $val) {
                
                // $val->get_cate[0]->get_cate_name->id;
                // $site_array_sub = [];
                if($val) {
                    $val_site_id = $val->site_id;
                    if($val->site_id) {
                        
                                $site_array_sub['site_id'] = $val_site_id;
                                $site_array_sub['cate_id'] = $val->category_id;
                            
                        $site_array_sub_cal = array_filter($site_array_sub);
                        array_push($site_array, $site_array_sub_cal);
                    }
                }
            }
        }
        

        //site_news_related จาก db
        $site_news_related = [];
        if($SiteNewsRelated) {
            foreach($SiteNewsRelated as $key => $val) {
                if($val) {
                    $val_news_related_id = $val->site_id;
                    $val_news_id = $val->news_id;
                    // $val->get_cate[0]->get_cate_name->id;
                    
                    if($val->get_news) {
                        $news_related_array_sub = [];
                        foreach(@$val->get_news as $key2 => $val2) {

                            if($val2) {
                                $news_related_array_sub['site_id'] = $val_news_related_id;
                                // $news_related_array_sub['cate_id'] = @$val2->news_category_id;
                                $news_related_array_sub['cate_id'] = @$val2->get_cate[0]->news_category_id;
                                $news_related_array_sub['news_id'] = $val_news_id;
                            }
    
                 
    
                        }
                        $news_related_array_sub_cal = array_filter($news_related_array_sub);
                        array_push($site_news_related, $news_related_array_sub_cal);
                    }
                }
            }
        }




        //site news
        $site_and_news_array_all = [];
        if($news_array) {
            foreach($news_array as $news_key => $news_val) {
                // dd($news_val['news_id']);
                if($news_val) {
                    if($site_array) {
                        $site_and_news_array = [];
                        foreach($site_array as $site_key => $site_val) {
                            if($site_val) {
                                if(@$news_val['cate_id'] == @$site_val['cate_id']) {
                                    if(@$site_val['cate_id'] && @$site_val['site_id'] && @$news_val['news_id'] && @$news_val['cate_id']) {

                                        
                                                $site_and_news_array['site_id'] = $site_val['site_id'];
                                                $site_and_news_array['cate_id'] = $site_val['cate_id'];
                                                $site_and_news_array['news_id'] = $news_val['news_id'];
                                        
                                        
                                    }
                  
    
                                }
                                
                            }
                        }
                        array_push($site_and_news_array_all, $site_and_news_array);
                    }
                }  
            }
        }




        // $site_news_related_insert_all = [];
        // $site_news_related_insert = [];
        // $site_news_related_insert_sub = [];
        // if($site_news_related) {
        //     foreach($site_news_related as $key => $val) {
        //         if($val) {

        //             if($site_and_news_array_all) {
        //                 foreach($site_and_news_array_all as $key2 => $val2) {
        //                     if(@$val['site_id'] == @$val2['site_id'] && @$val['cate_id'] == @$val2['cate_id'] && @$val['news_id'] == @$val2['news_id']) {
                                

        //                     } else {
        //                         $site_news_related_insert_sub['site_id'] = @$val2['site_id'];
        //                         $site_news_related_insert_sub['cate_id'] = @$val2['cate_id'];
        //                         $site_news_related_insert_sub['news_id'] = @$val2['news_id'];

        //                         // dd($site_news_related_insert_sub);

        //                         $site_news_related_insert_sub_cal = array_filter($site_news_related_insert_sub);
        //                         array_push($site_news_related_insert, $site_news_related_insert_sub_cal);
        //                     }

        //                 }
        //             }


        //         }
        //     }

        //     $site_news_related_insert_cal = array_filter($site_news_related_insert);
        //     array_push($site_news_related_insert_all, $site_news_related_insert_cal);


        // }

        SiteNewsRelatedTemp::truncate();
        if($site_and_news_array_all) {
            // dd($site_and_news_array_all);
            foreach($site_and_news_array_all as $key => $val) {
                // dd($val);
                if($val) {
                    $SiteNewsRelatedTemp = new SiteNewsRelatedTemp;
                    $SiteNewsRelatedTemp->site_id = @$val['site_id'];
                    // $SiteNewsRelatedTemp->cate_id = $val['cate_id'];
                    $SiteNewsRelatedTemp->news_id = @$val['news_id'];
                    $SiteNewsRelatedTemp->save();
                }
            }
        }

        $SiteNewsRelatedTemp = SiteNewsRelatedTemp::all();
        if($SiteNewsRelatedTemp) {
            if($site_and_news_array_all) {
                SiteNewsRelated::truncate();
                foreach($site_and_news_array_all as $key => $val) {
                    if($val) {
                        $SiteNewsRelatedTemp = new SiteNewsRelated;
                        $SiteNewsRelatedTemp->site_id = @$val['site_id'];
                        // $SiteNewsRelatedTemp->cate_id = $val['cate_id'];
                        $SiteNewsRelatedTemp->news_id = @$val['news_id'];
                        $SiteNewsRelatedTemp->save();
                    }
                }
            }
        }





        // $site_array = array_filter($site_array);
        // $result=array_diff($site_news_related[0],$site_and_news_array_all[0]);

        // $result=array_diff($news_related_array_sub_cal,$site_and_news_array);


        



        // dd($news_array);
        // dd($site_array);
        // dd($site_news_related);
        // print_r($result);
        // dd($site_news_related_insert_all);
        // dd(array_unique($site_and_news_array_all));
        // dd($SiteNewsRelated[0]->get_news[0]->get_cate);
        // dd($SiteNewsRelated[0]->get_news->get_cate);


        
    //    return view('news::index')->with($data);
    }

    public function index()
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['news']) {
            check_permission403();
        }
        $data['page'] = langapp('news');
        // $data['Category'] = CategorySettings::where('active',1)->get();
        $data['category'] = CategorySettings::where('active',1)->get();
        // abort(403, 'Unauthorized action.');
        return view('rssfeedsettings::rss_news')->with($data);

    }

    public function index_client()
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['news']) {
            check_permission403();
        }
        $RSSNews_count = RSSNews::where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now())->count();
        // dd($news_all);
        // $RSSNews_count = RSSNews::count("id");
        $RSSNews_all = RSSNews::all();

        //<><><>
        // if(Auth::check()) {

        //     $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
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
        $get_role_custom_first = @get_role_custom();
        $SiteSettings = '';
        $SiteSettings = @$get_role_custom_first['SiteSettings'];
        $site_id_arr = @$get_role_custom_first['site_id_arr'];
        if(@$get_role_custom_first['superadmin'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }else if(@$get_role_custom_first['client'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }else if(@$get_role_custom_first['site_support'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }else if(@$get_role_custom_first['site_admin'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }else if(@$get_role_custom_first['site_client'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }


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
       return view('news::index',compact('RSSNews_all','RSSNews_count','page','Category','NewsCategory','ReadCategories','SiteSettings'));
    }

    public function news_detail()
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['news']) {
            check_permission403();
        }
        $data['page'] = langapp('news_detail');
       return view('news::news_detail')->with($data);
    }

    public function news_detail_code($code)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['news']) {
            check_permission403();
        }
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
                $RSSNews_last10 = RSSNews::whereIn('id', $rss_news_id_array)->where('status',1)->where('save_draft',0)->where('public_date', '<=', Carbon::now())->orderBy('created_at','DESC')->limit(10)->get();
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

    public function public_detail_select($code , $lang)
    {


        $RSSNews_prev = '';
        $RSSNews_next = '';
        $RSSNews_last10 = '';
        // $lang = 'th';
        $RSSNews = RSSNews::where("code",$code)->first();

        $cate_id_all = [];
        if(@$RSSNews->get_cate) {
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
                $RSSNews_last10 = RSSNews::whereIn('id', $rss_news_id_array)->where('status',1)->where('save_draft',0)->where('public_date', '<=', Carbon::now())->orderBy('created_at','DESC')->limit(10)->get();
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

    //    $data['page'] = langapp('news_detail');
       return view('news::public_detail_select')->with($data);
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
        $role_custom = @check_role_custom();
        if(!$role_custom['news']) {
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

        if(($request -> title || $request -> cate || $request -> related_news || $request -> lang_th || $request -> lang_en || $request -> date_start || $request -> date_end || $request -> status_serverity) && $request -> f_search == 1){

            $news = RSSNews::where(function ($query) {
                $query->where('save_draft',  0)
                    ->orWhere('save_draft',  null);
            })->where('status', 1)->where('public_date', '<=', Carbon::now());//->get() ->orderBy('created_at','desc')->paginate(10)  // selectRaw('*, count(id) as rss_new_count')
            if($request -> title){
                $news = $news -> where('title_th', 'LIKE' ,'%'.$request -> title.'%');
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
          
            if($request ->site_id) {

                $site_id = @$request ->site_id;
                $news = $news->wherehas('get_site_news_related', function($q) use ($site_id) {
                    $q->where('site_id', $site_id)->where('deleted_at', null);
                });
            }



            if($related_news == 'true') {
                $site_id = @$request ->site_id;
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

            if($request -> status_serverity){

                $news = $news -> where('serverity', $request -> status_serverity);
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
            $news = RSSNews::where(function ($query) {
                $query->where('save_draft',  0)
                    ->orWhere('save_draft',  null);
            })->where('status', 1)->where('public_date', '<=', Carbon::now());//->get()

            if($request ->site_id) {
                $site_id = SiteSettings::select('id')->where('code', $request ->site_id)->where('active','1')->first()->id;
                $news = $news->wherehas('get_site_news_related', function($q) use ($site_id) {
                    $q->where('site_id', $site_id)->where('deleted_at', null);
                });
            }
            $news_all = $news->count();
            $news = $news->orderBy('created_at','desc')->paginate(PAGINATE_NUM);
        }

        // dd($news);
        $content = [];
        foreach($news as $data){
            $related_news_site = '';
            $icon_related= '';
            $n_title = @$data -> title_th;
            $n_detail = @$data -> detail_th;
            if($site_id) {
                $related_news_site = SiteNewsRelated::where("news_id",$data -> id)->where("site_id",$site_id)->first();
            }
            
            if($related_news_site) {
                $icon_related = '<i class="fas fa-newspaper"></i>';
            } else {
                $icon_related = '';
            }

            if($lang_th=='true' && $lang_en=='true') {
                $n_title = $data -> title_th;
                $n_detail = $data -> detail_th;

                // $n_title = $data -> title_en;
            } else if($lang_th=='true') {
                if($lang_th=='true') {
                    $n_title = $data -> title_th;
                    $n_detail = $data -> detail_th;
                } else if ($lang_en=='true') {
                    $n_title = $data -> title_en;
                    $n_detail = $data -> detail_en;
                }

                // $n_title = $data -> title_en;
            } else if ($lang_en=='true') {
                if ($lang_en=='true') {
                    $n_title = $data -> title_en;
                    $n_detail = $data -> detail_en;
                } else if ($lang_th=='true') {
                    $n_title = $data -> title_th;
                    $n_detail = $data -> detail_th;
                }

                // $n_title = $data -> title_en;
            } else {
                if(@$data -> title_th) {
                    $n_title = $data -> title_th;
                    $n_detail = $data -> detail_th;
                } else {
                    $n_title = $data -> title_en;
                    $n_detail = $data -> detail_en;
                }

                // $n_title = $data -> title_en;
 
            }

            // $n_detail = strip_tags($n_detail);
            // dd($n_detail);

            $content[] = strip_tags($n_detail);
            // $content[] = $data -> detail_en;
            // dd($content);

            


            $check_read_news = ReadNews::where('user_id', Auth::user()->id)->where('news_id', $data -> id)->first();
            $checkBookmark = Bookmark::where('user_id', Auth::user()->id)->where('news_id', $data -> id)->first();
            if($check_read_news){
                $html .= '<div class="list-news">';
                $font_weight = '';
            }else{
                $html .= '<div class="list-news" style="background-color:#ececec">';
                $font_weight = 'font-weight: bold !important;';
            }

            if(@$data->transaction_rss_id) {
                if(@$data->logo) {
                    $logo_url = config('app.URL_CENTER_PUBLISH').@$data->logo;
                } else {
                    $logo_url = @$data->logo_rss;
                }
            } else {
                $logo_url = config('app.URL_CENTER_PUBLISH').@$data->logo;
            }


            $html_cate_all = '';
            if(!empty($data->get_cate)) {
                foreach($data->get_cate as $cate_id_val) {
                    if(!empty($cate_id_val)) {
                        $html_cate_all .= '<span class="badge badge-primary" style="background-color:#007bff;">'.@$cate_id_val->get_cate_name->name.'</span> &nbsp;';
                    }
                }
            }

            $html_source_all_full = '';
            $html_source_all = '';
            if(!empty($data->source)) {
                $html_source_all .= '<span class="badge badge-info" style="background-color:#17a2b8;">'.@$data->source.'</span> &nbsp;';  
            }
            if(!empty($html_source_all)) {
                $html_source_all_full = '<b>Source: </b>'.$html_source_all;
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
                    <a href="'.route('news.news_detail_code',['code' => $data -> code]).'">
                        <span class="head-news-text text-elip-ovf" style="'.@$font_weight.'">'.$icon_related.' '.$n_title.'</span>
                    </a>
                    <div class="entry-meta">
                    <span class="entry-view"> <i class="fas fa-eye"></i> '.$data -> view.'</span>
                    <span class="entry-date"> <i class="fas fa-calendar-alt"></i> '.$data -> public_date.'</span>
                     <span> <b>Serverity: </b> ';
                    if($data->serverity=='critical'){
                        $html .=  '<span class="badge" style="background-color: #e64732;">Critical</span>';
                    }else if($data->serverity=='high'){
                        $html .= '<span class="badge" style="background-color: #fcc838;">High</span>';
                    }else if($data->serverity=='medium'){
                        $html .= '<span class="badge" style="background-color: #00dcff;">Medium</span>';
                    }else if($data->serverity=='low'){
                        $html .= '<span class="badge" style="background-color: #88ce4f;">Low</span>';
                    }else if($data->serverity=='information'){
                        $html .= '<span class="badge" style="background-color: #d3d3d3;">Information</span>';
                    }else{
                        $html .= '-';
                    }

                    $html .= '</span> &nbsp; <span class="entry-cate"> <b>Categories: </b>'.$html_cate_all.'</span>'
                    .@$html_source_all_full.
                    '<span><p class="details-news-elip">&nbsp;'.strip_tags($n_detail).'</p></span>
                    </div>
                </div>
                <div class="content-news-image">
                    <a href="'.route('news.news_detail_code',['code' => $data -> code]).'">
                        <img src="'.$logo_url.'" alt="" onerror="setDefaultPic(this)">
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
        // dd($content);
        if ($request->ajax()) {
            $data = [
                "html" => $html,
                "count" => $news_all
            ];
            return response()->json($data); 
        }
    }

    public function load_top_source(Request $request){
        $role_custom = @check_role_custom();
        if(!$role_custom['news']) {
            check_permission403();
        }
        $model = New RSSNews();
        if($request -> search_val == 1){
            
            if($request -> keywords){
                $model_where = RSSNews::where('title_en', 'LIKE' ,'%'.$request -> keywords.'%')->first();
                
                if($model_where) {
                    $model = $model -> where('title_en', 'LIKE' ,'%'.$request -> keywords.'%');
                } else {
                    $model = $model -> where('title_th', 'LIKE' ,'%'.$request -> keywords.'%');
                }
                
            }

            if($request -> isDateSearch == 1){
                $date_start = $request->startDate;
                $date_end = $request->endDate;

                $date_start_explode = explode(" ",$date_start);
                $date_start_date = @$date_start_explode[0];
                $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                // dd($date_start_time);
                $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                // dd($date_start_date_format);
                $date_start_time_time = date("H:i", strtotime($date_start_time));
                $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                // dd($date_start);

                $date_end_explode = explode(" ",$date_end);
                $date_end_date = @$date_end_explode[0];
                $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                // dd($date_end_time);
                $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                $date_end_time_time = date("H:i", strtotime($date_end_time));
                $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
                // dd($date_end_time_time);

                // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
                $model = $model -> whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format));
            }

            if($request -> status_news){
                if($request -> status_news == 1 || $request -> status_news == 2){
                    if($request -> status_news == 1) {
                        $model =  $model -> where('save_draft','=',0);
                        
                    } else if ($request -> status_news == 2) {
                        $model =  $model -> where('save_draft',1);
                        // dd($model);
                    }
                    
                }  
            }
            if($request -> news_source){

                // $model -> where('source', 'LIKE' ,'%'.$request -> news_source.'%');
                $model = $model -> whereIn('source', $request -> news_source);
            }

            if($request -> news_category){
                $news_cate_id = $request -> news_category;
                $model =  $model -> whereHas('get_cate', function ($query) use ($news_cate_id) {
                    $query->whereIn('news_category_id', $news_cate_id);
                });
            }

            if($request -> status_serverity){

                $model = $model -> where('serverity', $request -> status_serverity);
            }
            
        }

        $model = $model         
        ->select(DB::raw('count(*) as source_count , source as source'))
        ->groupBy('source')
        ->orderBy('source_count', 'desc')
        ->limit(11)
        ->get();

        $host2 = array();
        foreach($model as $value){
            if(empty($value->source)||$value->source=='None'){
                if(!isset($host2['None'])){
                    $host2['None'] = 0;
                }
                $host2['None'] = $host2['None']+(int)$value->source_count;
            }else{
                $host2[$value->source] = (int)$value->source_count;
            }
        }
        arsort($host2);
        $countLimit = 0;
        $host = array();
        foreach ($host2 as $key => $value) {
            $countLimit++;
            if($countLimit<11){
                $host[] = [$key,$value];
            }
        }

        // $host = array();
        // foreach($model as $value){

        //     $host[] = [empty($value->source)?'None':$value->source,(int)$value->source_count];
        // }
    
        if ($request->ajax()) {

            return response()->json($host);
        }

    }

    public function load_top_category(Request $request){
        
        $role_custom = @check_role_custom();
        if(!$role_custom['news']) {
            check_permission403();
        }
        $model = New RSSNews();

        if($request -> search_val == 1){
            
            if($request -> keywords){
                $model_where = RSSNews::where('title_en', 'LIKE' ,'%'.$request -> keywords.'%')->first();
                
                if($model_where) {
                    $model = $model -> where('title_en', 'LIKE' ,'%'.$request -> keywords.'%');
                } else {
                    $model = $model -> where('title_th', 'LIKE' ,'%'.$request -> keywords.'%');
                }
                
            }

            if($request -> isDateSearch == 1){
                $date_start = $request->startDate;
                $date_end = $request->endDate;

                $date_start_explode = explode(" ",$date_start);
                $date_start_date = @$date_start_explode[0];
                $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                // dd($date_start_time);
                $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                // dd($date_start_date_format);
                $date_start_time_time = date("H:i", strtotime($date_start_time));
                $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                // dd($date_start);

                $date_end_explode = explode(" ",$date_end);
                $date_end_date = @$date_end_explode[0];
                $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                // dd($date_end_time);
                $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                $date_end_time_time = date("H:i", strtotime($date_end_time));
                $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
                // dd($date_end_time_time);

                // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
                $model = $model -> whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format));
            }

            if($request -> status_news){
                if($request -> status_news == 1 || $request -> status_news == 2){
                    if($request -> status_news == 1) {
                        $model =  $model -> where('save_draft','=',0);
                        
                    } else if ($request -> status_news == 2) {
                        $model =  $model -> where('save_draft',1);
                        // dd($model);
                    }
                    
                }  
            }
            if($request -> news_source){

                // $model -> where('source', 'LIKE' ,'%'.$request -> news_source.'%');
                $model = $model -> whereIn('source', $request -> news_source);
            }

            if($request -> news_category){
                $news_cate_id = $request -> news_category;
                $model =  $model -> whereHas('get_cate', function ($query) use ($news_cate_id) {
                    $query->whereIn('news_category_id', $news_cate_id);
                });
            }

            if($request -> status_serverity){
                    
                $model = $model -> where('serverity', $request -> status_serverity);
            }
            
        }
       
        // $RSSNewsCategory = RSSNewsCategory::select('rss_news_id','categories.name')->join('categories','categories.id','=','r_s_s_news_categories.news_category_id')->where('categories.active',1);
        // dd($RSSNewsCategory->get());
        // $model = $model         
        // ->select('categories.name as categories_name_',DB::raw('count(*) as categories_count'))
        // ->leftjoin('r_s_s_news_categories','r_s_s_news.id','=','r_s_s_news_categories.rss_news_id')
        // ->leftjoin('categories','categories.id','=','r_s_s_news_categories.news_category_id')
        // ->where('categories.active',1)
        // ->where('categories.deleted_at',null)
        // ->groupBy('categories.name')
        // ->orderBy('categories_count', 'desc')
        // ->limit(10)
        // ->get();

        $model = $model
        ->select('name_cat as categories_name_',DB::raw('count(*) as categories_count'))
        ->leftjoin(DB::raw('(SELECT fx_r_s_s_news_categories.rss_news_id as rssid ,fx_r_s_s_news_categories.news_category_id as category_id, fx_categories.name as name_cat FROM fx_r_s_s_news_categories,fx_categories
        where fx_r_s_s_news_categories.news_category_id = fx_categories.id 
        and fx_categories.active=1 and fx_categories.deleted_at is null) as fx_TotalCatches'), 
        function($join)
        {
           $join->on('r_s_s_news.id', '=', 'TotalCatches.rssid');
        })
        ->addSelect('category_id')
        ->groupBy('name_cat')
        ->orderBy('categories_count', 'desc')
        ->limit(10)
        ->get();


        // $sql = "SELECT count(*) as categories_count ,name_cat FROM fx_r_s_s_news LEFT JOIN
        // (SELECT fx_r_s_s_news_categories.rss_news_id as rssid,fx_categories.name as name_cat FROM fx_r_s_s_news_categories,fx_categories
        // where fx_r_s_s_news_categories.news_category_id = fx_categories.id 
        // and fx_categories.active=1) as test
        // on fx_r_s_s_news.id = test.rssid
        // group by name_cat
        // order by categories_count desc
        // limit 10" ;
        // $model = DB::select( DB::raw($sql));


        $host = array();
        $color=['#3B3D50','#ECC44D','#DA4C62','#E95C83','#6F57E9','#7698A0','#02CCCD','#A8C5CC','#A0D0C8','#E7DED4'];
        foreach($model as $key => $value){

            $host[] = array(
                'name' => empty($value->categories_name_)?'None':$value->categories_name_,
                'y' => (int)$value->categories_count,
                'color' => $color[$key] ,
                'data' => $value->category_id
            );

            
        }
    
        if ($request->ajax()) {

            return response()->json($host);
        }

    }

    public function jqueryLoadMoreNewsTopic(Request $request){
        $role_custom = @check_role_custom();
        if(!$role_custom['news']) {
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
                $font_weight = '';
            }else{
                $html .= '<div class="list-news">';
                $font_weight = 'font-weight: bold !important;';
            }

            if(@$data -> news -> transaction_rss_id) {
                if(@$data -> news -> logo) {
                    $url_logo = config('app.URL_CENTER_PUBLISH').@$data -> news -> logo;
                } else {
                    $url_logo = @$data -> news -> logo_rss;
                }
            } else {
                $url_logo = config('app.URL_CENTER_PUBLISH').@$data -> news -> logo;
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
                        <span class="head-news-text" style="'.@$font_weight.'">'.$data -> news -> title_th.'</span>
                    </a>
                    <div class="entry-meta">
                        <span class="entry-view"> <i class="fas fa-eye"></i> '.$data -> news -> view.'</span>
                        <span class="entry-date"> <i class="fas fa-calendar-alt"></i> '.$data -> news -> public_date.'</span>
                        <span>&nbsp;'.strip_tags($data -> news -> detail_th).'</span>
                    </div>
                </div>
                <div class="content-news-image">
                    <a href="'.route('news.news_detail_code',['code' => $data -> news -> code]).'">
                        <img src="'.$url_logo.'" alt="" onerror="setDefaultPic(this)">
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
        if(!$role_custom['news']) {
            check_permission403();
        }
        $html = '';
        $Bookmark = Bookmark::where('user_id',@Auth::user()->id)->orderBy('created_at','desc')->with('news')->get();
        if($Bookmark) {
            foreach($Bookmark as $data){
                $check_read_news = ReadNews::where('user_id', Auth::user()->id)->where('news_id', $data -> rss_news_id)->first();
                if($check_read_news){
                    $html .= '<div class="list-news">';
                    $font_weight = '';
                }else{
                    $html .= '<div class="list-news" style="background-color:#ececec">';
                    $font_weight = 'font-weight: bold !important;';
                }

                if(@$data -> news -> transaction_rss_id) {
                    if(@$data -> news -> logo) {
                        $url_logo = config('app.URL_CENTER_PUBLISH').@$data -> news -> logo;
                    } else {
                        $url_logo = @$data -> news -> logo_rss;
                    }
                } else {
                    $url_logo = config('app.URL_CENTER_PUBLISH').@$data -> news -> logo;
                }

                $html_cate_all = '';
                if(!empty($data->news->get_cate)) {
                    foreach(@$data->news->get_cate as $cate_id_val) {
                        if(!empty($cate_id_val)) {
                            $html_cate_all .= '<span class="badge badge-primary" style="background-color:#007bff;">'.@$cate_id_val->get_cate_name->name.'</span> &nbsp;';
                        }
                    }
                }

                $html_source_all_full = '';
                $html_source_all = '';
                if(!empty($data->news->source)) {
                    $html_source_all .= '<span class="badge badge-info" style="background-color:#17a2b8;">'.@$data->news->source.'</span> &nbsp;';  
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
                        <a href="'.route('news.news_detail_code',['code' => @$data -> news -> code]).'">
                            <span class="head-news-text" style="'.@$font_weight.'">'.@$data -> news -> title_th.'</span>
                        </a>
                        <div class="entry-meta">
                            <span class="entry-date"> <i class="fas fa-calendar-alt"></i> '.@$data -> news -> public_date.'</span>
                            <span class="entry-view"> <i class="fas fa-eye"></i> '.@$data -> news -> view.'</span>
                            <span> <b>Serverity: </b> ';
                            if($data->news->serverity=='critical'){
                                $html .=  '<span class="badge" style="background-color: #fcc838;">Critical</span>';
                            }else if($data->news->serverity=='high'){
                                $html .= '<span class="badge" style="background-color: #fcc838;">High</span>';
                            }else if($data->news->serverity=='medium'){
                                $html .= '<span class="badge" style="background-color: #00dcff;">Medium</span>';
                            }else if($data->news->serverity=='low'){
                                $html .= '<span class="badge" style="background-color: #88ce4f;">Low</span>';
                            }else if($data->news->serverity=='information'){
                                $html .= '<span class="badge" style="background-color: #d3d3d3;">Information</span>';
                            }else{
                                $html .= '-';
                            }
                            $html .='</span> &nbsp; <span class="entry-cate"> <b>Categories: </b>'.$html_cate_all.'</span>'
                            .@$html_source_all_full.
                            '<span><p></p>&nbsp;'.strip_tags(@$data -> news -> detail_th).'</p></span>
                        </div>
                    </div>
                    <div class="content-news-image">
                        <a href="'.route('news.news_detail_code',['code' => @$data -> news -> code]).'">
                            <img src="'.$url_logo.'" alt="" onerror="setDefaultPic(this)">
                        </a>
                    </div>
                    <div class="action-bookmark">';
                        $html .= '<i class="fas fa-bookmark bookmark-active" id="mark'.@$data -> news -> id.'" onclick="Bookmarks(this, '.@$data -> news -> id.')"></i>';
                        $html .= '</div>
                </div>
                ';
            }
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
