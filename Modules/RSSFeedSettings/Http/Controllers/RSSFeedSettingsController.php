<?php

namespace Modules\RSSFeedSettings\Http\Controllers;

use App\Entities\fx_transaction_client_news_categories;
use App\Mail\NewsMail;
use App\siteNewsRelated;
use App\Topic;
use App\TransactionClientNews;
use Modules\RSSFeedSettings\Http\Requests\CreateRssRequest;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\RSSFeedSettings\Entities\NewsCategory;
use Modules\CategorySettings\Entities\CategorySettings;
use Modules\RSSFeedSettings\Entities\NewsTag;
use Modules\RSSFeedSettings\Entities\NewsTopics;
use Modules\RSSFeedSettings\Entities\RSSData;
use Modules\RSSFeedSettings\Entities\RSSNews;
use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Modules\SiteSettings\Entities\Tags;
use Modules\SiteSettings\Entities\site_config_email_alert;
use Modules\SiteSettings\Entities\SiteCategory;
use Modules\RSSFeedSettings\Entities\TransactionRssData;
use Yajra\DataTables\DataTables;

class RSSFeedSettingsController extends Controller
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

    public function __construct(Request $request , RSSData $RSSData)
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
       $data['page'] = langapp('rss_feed_settings');
       return view('rssfeedsettings::index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('rssfeedsettings::create');
    }

    public function rss_data()
    {
        $data['page'] = langapp('rss_data');
        return view('rssfeedsettings::rss_data')->with($data);
    }

    public function tableRssData(Request $request){

        
        if(($request -> keywords || $request -> isDateSearch || $request -> status) && $request -> search_val == true){
            $model = TransactionRssData::with('get_rss_news')->with('get_rss_source');

            if($request -> keywords){
                $model -> where('title', 'LIKE' ,'%'.$request -> keywords.'%');
            }
            if($request -> isDateSearch){
                $date_start = $request->startDate;
                $date_end = $request->endDate;

                $date_start_explode = explode(" ",$date_start);
                $date_start_date = @$date_start_explode[0];
                // $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                // dd($date_start_time);
                $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                // dd($date_start_date_format);
                // $date_start_time_time = date("H:i", strtotime($date_start_time));
                // $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                // dd($date_start);

                $date_end_explode = explode(" ",$date_end);
                $date_end_date = @$date_end_explode[0];
                // $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                // dd($date_end_time);
                $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                // $date_end_time_time = date("H:i", strtotime($date_end_time));
                // $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
                // dd($date_end_time_time);

                // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
                $model -> whereBetween('transcation_date',array($date_start_date_format,$date_end_date_format));
            }
            if($request -> status){
            
                    if($request -> status == '1'){

                        $model = $model->whereHas('get_rss_news', function ($query) {
                            $query->where('transaction_rss_id', '!=', null);
                        });



                        
                    }else if($request -> status == '2'){
     
                        $model = $model->whereDoesntHave('get_rss_news', function ($query) {
                            $query->where('transaction_rss_id', '!=', null);
                        });

                        
                    }
                    
            }
            
            $model -> get();
        }else{
            $model = TransactionRssData::with('get_rss_news')->with('get_rss_source')->get();
        }

        return DataTables::of($model)->toJson();
            // ->editColumn('chk', function (TransactionRssData $model) {
            //         return '<label><input type="checkbox" name="checked" value="' . $model->code . '"><span class="label-text"></span></label>';
            // })
            // ->addColumn('title', function (TransactionRssData $model) {

                
            //     if(@$model ->get_rss_news-> title_th){
            //         return '<div class="text-elip" data-rel="tooltip" title="'.@$model ->get_rss_news-> title_th.'">'.@$model ->get_rss_news -> title_th.'</div>';
            //     }else if(@$model ->get_rss_news-> title_en){
            //         return '<div class="text-elip" data-rel="tooltip" title="'.@$model ->get_rss_news-> title_en.'">'.@$model ->get_rss_news -> title_en.'</div>';
            //     }else if($model -> title){
            //         return '<div class="text-elip" data-rel="tooltip" title="'.$model -> title.'">'.$model -> title.'</div>';
            //     }else{
            //         return '-';
            //     }
            // })
            // ->addColumn('link', function (TransactionRssData $model) {
            //     $html = '';
            //     // $RSSNews = RSSNews::where('transaction_rss_id', $model -> id)->first();
            //     $word_leng = utf8_strlen($model->link);

            //     // if(!empty($RSSNews)){
            //     //     // if($word_leng > 30) {
            //     //     //     $html .= iconv_substr($model->link, 0, 30, "UTF-8");
            //     //     //     $html .= '...'; 
            //     //     // } else {
            //     //     //     $html .= $model->link; 
            //     //     // }
            //     //     $html .= '<a href="'.$model -> link .'" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-link"></i> Open</a>';
            //     // }else{
            //     //     // if($word_leng > 30) {
            //     //     //     $html .= iconv_substr($model->link, 0, 30, "UTF-8");
            //     //     //     $html .= '...'; 
            //     //     // } else {
            //     //     //     $html .= $model->link; 
            //     //     // }
            //     //     $html .= '<a href="'.$model -> link .'" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-link"></i> Open</a>';
            //     //     // $html .= iconv_substr($model->link, 0, 30, "UTF-8"); 
            //     //     // $html .= $word_leng; 
                 

            //     if(!empty($model->get_rss_news)){
            //         if($word_leng > 30) {
            //             $html .= iconv_substr($model->link, 0, 30, "UTF-8");
            //             $html .= '...'; 
            //         } else {
            //             $html .= $model->link; 
            //         }
            //         $html .= '<a href="'.$model -> link .'" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-link"></i> Open</a>';
            //     }else{
            //         if($word_leng > 30) {
            //             $html .= iconv_substr($model->link, 0, 30, "UTF-8");
            //             $html .= '...'; 
            //         } else {
            //             $html .= $model->link; 
            //         }
            //         $html .= '<a href="'.$model -> link .'" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-link"></i> Open</a>';
            //         // $html .= iconv_substr($model->link, 0, 30, "UTF-8"); 
            //         // $html .= $word_leng; 
            //     }


               
            //     return $html;
            // })
            // ->addColumn('status', function (TransactionRssData $model) {
            //     $html = '';



            //     if(!empty($model->get_rss_news) && $model->get_rss_news -> save_draft == 1){
            //         $html .= '<span class="badge badge-danger" style="background-color: #ea2e49;">Darft</span>';
            //     }else if(!empty($model->get_rss_news) && $model->get_rss_news -> save_draft == 0){
            //         $html .= '<span class="badge badge-success">Used</span>';
            //     }else{
            //         $html .= '<span class="badge badge-warning" style="background-color: #ffc107;">Not used</span>';
            //     }  
            //     return $html;
            // })
            // ->addColumn('action', function (TransactionRssData $model) {
            //     $html = '';
            //     $html_cr_news = '';

            //     if(!empty($model->get_rss_news) && $model->get_rss_news -> save_draft == 1){
            //         $html_cr_news .= "<a href='". route('rssfeedsettings.rss_data_create_news', ['code' => $model->code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
            //                                 <!--<svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>-->
            //                                 <i class='fas fa-share-square'></i>
            //                             </a>";
            //     }else if(!empty($model->get_rss_news) && $model->get_rss_news -> save_draft == 0){
            //         $html_cr_news .= "";
            //     }else{
            //         $html_cr_news .= "<a href='". route('rssfeedsettings.rss_data_create_news', ['code' => $model->code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
            //                             <!--<svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>-->
            //                             <i class='fas fa-share-square'></i>
            //                         </a>";
            //     }

             
            //     return $html;

            // })
            // ->rawColumns(['chk','title','link','status','action'])
             
    }

    public function deleteChecked(Request $request){

        foreach($request->id as $rss_id){

     
            $data = TransactionRssData::where("id",$rss_id);
            $data->delete();

        }


        return ajaxResponse(
            [
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('rssfeedsettings.rss_data'),
            ],
            true,
            Response::HTTP_OK
        );
    }  



    public function tableNews(Request $request){
        // if($request -> keywords || $request -> public_date || $request -> status !== "null" || $request -> source){
        //     $model = TransactionRssData::where('status', 1);
        //     if($request -> keywords){
        //         $model -> where('title', 'LIKE' ,'%'.$request -> keywords.'%');
        //     }
        //     if($request -> public_date){
        //         $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
        //     }
        //     if($request -> status){
        //         if($request -> status == '2' || $request -> status == '3'){
        //             if($request -> status == '2'){
        //                 $RSSNews = RSSNews::where('transaction_rss_id','!=' ,null)->get();
        //                 foreach($RSSNews as $data){
        //                     $model -> where('id', $data -> transaction_rss_id);
        //                 }
        //             }else if($request -> status == '3'){
        //                 $RSSNews = RSSNews::where('transaction_rss_id','!=' ,null)->get();
        //                 foreach($RSSNews as $data){
        //                     $model -> where('id', '!=' ,$data -> transaction_rss_id);
        //                 }
        //             }
                    
        //         }  
        //     }
        //     if($request -> source){
        //         $model -> where('link', 'LIKE' ,'%'.$request -> source.'%');
        //     }
        //     $model -> get();
        // }else{
        //     $model = TransactionRssData::all();
        // }

        if(($request -> keywords || $request -> startDate || $request -> endDate || $request -> status_news || $request -> news_source || $request -> news_category) && $request -> search_val == true){
            $model = RSSNews::where('status', 1);
            if($request -> keywords){
                $model_where = RSSNews::where('status', 1)->where('title_en', 'LIKE' ,'%'.$request -> keywords.'%')->first();
                
                if($model_where) {
                    $model -> where('title_en', 'LIKE' ,'%'.$request -> keywords.'%');
                } else {
                    $model -> where('title_th', 'LIKE' ,'%'.$request -> keywords.'%');
                }
                
            }
            // if($request -> start_date){
            //     $start_date = date("Y-m-d H:i:s",strtotime($request -> start_date));
            //     $end_date = date("Y-m-d H:i:s",strtotime($request -> end_date));
            //     // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
            //     $model -> whereBetween('created_at',array($start_date,$end_date));
            // }

            if($request -> startDate){
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
                $model -> whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format));
            }


            // dd($request -> status_news);
            if($request -> status_news){
                if($request -> status_news == 1 || $request -> status_news == 2){
                    if($request -> status_news == 1) {
                        $model -> where('save_draft','=',0);
                        
                    } else if ($request -> status_news == 2) {
                        $model -> where('save_draft',1);
                        // dd($model);
                    }
                    
                }  
            }
            if($request -> news_source){

                // $model -> where('source', 'LIKE' ,'%'.$request -> news_source.'%');
                $model -> whereIn('source', $request -> news_source);
            }
            if($request -> news_category){
                $news_cate_id = $request -> news_category;
                // CategorySettings::where("code",)->first();
                // dd($news_cate_id);

                // $model -> where('source', 'LIKE' ,'%'.$request -> news_source.'%');
                // $model -> whereIn('source', $request -> news_source);
                foreach($news_cate_id as $news_cate_id_val) {
                    // dd($news_cate_id_val);
                    $model -> whereHas('get_cate', function ($query) use ($news_cate_id_val) {
                        $query->where('news_category_id', $news_cate_id_val);
                    });
                }



            }
            $model -> get();
        }else{
            $model = RSSNews::all();
        }


        // $model = RSSNews::all();
        return DataTables::of($model)
            ->editColumn('chk', function (RSSNews $model) {
                    return '<label><input type="checkbox" name="checked" class="rss_new_id" value="' . $model->code . '"><span class="label-text"></span></label>';
            })
            // ->addColumn('site_name', function (RSSNews $model) {
            //     $data = siteNewsRelated::where('news_id', $model -> id)->get();
            //     $html = '';
            //     if(empty($data)){
            //         $html .= '-';
            //     }else{
            //         foreach($data as $item){
            //             $html .= $item -> get_site -> name .' , ';
            //         }
            //     }
            //     return rtrim($html, ' , ');
            // })
            ->addColumn('source', function (RSSNews $model) {
                return '<div class="text-elip max-w-fit" data-rel="tooltip" title="'.$model -> source.'">'.$model -> source.'</div>';
            })
            ->addColumn('title', function (RSSNews $model) {
                if($model -> title_th){
                    return '<div class="text-elip max-w-fit" data-rel="tooltip" title="'.$model -> title_th.'">'.$model -> title_th.'</div>';
                }else if($model -> title_en){
                    return '<div class="text-elip max-w-fit" data-rel="tooltip" title="'.$model -> title_en.'">'.$model -> title_en.'</div>';
                }else{
                    return '-';
                }
            })
            ->addColumn('cate', function (RSSNews $model) {
                $html = '';
                if(empty($model->get_cate)){
                    $html = '-';
                }else{
                    foreach($model->get_cate as $cate_val) {
                        $html .= $cate_val->get_cate_name->name.', ';
                    }
                    $html = rtrim($html,", ");
                }
                return $html;
            })
            ->addColumn('data_status', function (RSSNews $model) {
                $html = '';
                if($model -> save_draft == 1){
                    $html .= '<span class="badge badge-danger" style="background-color: #ea2e49;">Darft</span>';
                }else if($model -> save_draft == 0){
                    $html .= '<span class="badge badge-success">Public</span>';
                }else{
                    $html .= '<span class="badge badge-warning" style="background-color: #ffc107;">Not used</span>';
                }  
                return $html;
            })
            ->addColumn('link', function (RSSNews $model) {
                $html = '';
                $html_th = '';
                $html_en = '';
                $html_line = '';
                if($model->title_th) {
                    $html_th = '<a href="'.route('news.public_detail_select', ['code' => $model->code , 'lang' => 'th']).'" target="_blank">TH</a>';
                    
                }
                if($model->title_en) {
                    $html_en = '<a href="'.route('news.public_detail_select', ['code' => $model->code , 'lang' => 'en']).'" target="_blank">EN</a>';
                    $html_line = ' | ';
                }
                
                

                $html .= $html_th . $html_line . $html_en;

                return $html;
            })
            ->addColumn('status', function (RSSNews $model) {
                if($model->status == '1') {
                    $checked_val = 'checked';
                } else {
                    $checked_val = '';
                }
                $html = '';
                $html .= '<label class="switch">
                            <input type="checkbox" id="news-active-'.$model->code.'" onchange="change_news_active(\''.$model->code.'\')" '.$checked_val.' value="1">
                            <span></span>
                        </label>';
                return $html;
            })
            ->addColumn('action', function (RSSNews $model) {
                $html = '';
                $html .= "
                <a href='". route('rssfeedsettings.rss_news_edit_news', ['code' => $model->code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                </a>
                <a href='". route('rssfeedsettings.rss_news_delete', ['id' => $model->code]) ."' class='btn btn-danger btn-xs' data-toggle='ajaxModal'>
                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                </a></div>";
                return $html;
               
            })
            ->rawColumns(['chk','site_name','source','title','cate','data_status','link','status','action'])
            ->toJson();
    }

    public function tableRssSetting(){
        $model = RSSData::all();
        return DataTables::of($model)
            ->editColumn('chk', function (RSSData $model) {
                    return '<label><input type="checkbox" name="rss_id" class="rss_id" value="' . $model->code . '"><span class="label-text"></span></label>';
            })
            ->addColumn('link', function (RSSData $model) {
                $html = '';
                $html .= "<a href='". route('rssfeedsettings.rss_data_create_news', ['code' => $model->code]) ."' data-toggle='ajaxModal'>
                    ".$model->link."
                </a>";
                return $html;
            })
            ->addColumn('status', function (RSSData $model) {
                if($model->status == '1') {
                    $checked_val = 'checked';
                } else {
                    $checked_val = '';
                }
                $html = '';
                $html .= '<label class="switch">
                            <input type="checkbox" id="rss-active-'.$model->code.'" onchange="change_rss_active(\''.$model->code.'\')" '.$checked_val.' value="1">
                            <span></span>
                        </label>';
                return $html;
            })
            ->addColumn('action', function (RSSData $model) {
                $html = '';
                $html .= "<a href='". route('rssfeedsettings.edit', ['id' => $model->code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                </a>
                <a href='". route('rssfeedsettings.delete', ['id' => $model->code]) ."' class='btn btn-danger btn-xs' data-toggle='ajaxModal'>
                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                </a></div>";
                return $html;
            })
            ->rawColumns(['chk','link','status','action'])
            ->toJson();
    }

    public function rss_data_create_news($code)
    {
        $data['rss'] = TransactionRssData::where('code', $code)->first();
        $data['RSSNews'] = '';
        if($data['rss']) {
            // $data['RSSNews'] = RSSNews::where("transaction_rss_id",$data['rss']->id)->first();
        }
        
        $data['category'] = CategorySettings::where('active',1)->get();
        return view('rssfeedsettings::modal.create_news')->with($data);
    }

    public function rss_news_edit_news($code)
    {
        $data['RSSNews'] = RSSNews::where('code', $code)->first();
        // dd($data['RSSNews']->source);
        // $data['RSSNews'] = '';
        // if($data['rss']) {
        //     $data['RSSNews'] = RSSNews::where("transaction_rss_id",$data['rss']->id)->first();
        // }

        $get_source_query = "SELECT DISTINCT name FROM fx_rss UNION SELECT DISTINCT source FROM fx_r_s_s_news";
        $get_source = DB::select($get_source_query);
        $get_source = collect($get_source);

        $data['get_source'] = @$get_source;
        $data['action'] = 'edit';
        $data['category'] = CategorySettings::where('active',1)->get();
        return view('rssfeedsettings::modal.edit_news')->with($data);
    }

    public function rss_news_create_news()
    {
        $data['RSSNews'] = array();
        
        $get_source_query = "SELECT DISTINCT name FROM fx_rss UNION SELECT DISTINCT source FROM fx_r_s_s_news";
        $get_source = DB::select($get_source_query);
        $get_source = collect($get_source);

        $data['get_source'] = @$get_source;
        $data['action'] = 'create';
        // dd($data['get_source']);
        $data['category'] = CategorySettings::where('active',1)->get();
        return view('rssfeedsettings::modal.edit_news')->with($data);
    }

    public function rss_data_preview_news(Request $request){
        $data['page'] = "Preview News";
        return view('rssfeedsettings::preview_rss_news')->with($data);
    }

    public function rss_data_tags(Request $request){
        $search = $request->searchTerm;
        $get_tags_query = "SELECT * FROM fx_tags ";
        if(!empty($search)){
            $get_tags_query = $get_tags_query . "WHERE name LIKE '%$search%'";
        }
        $get_tags = DB::select($get_tags_query);
        $get_tags = collect($get_tags);
        return response()->json($get_tags);
    }

    public function rss_data_topics(Request $request){
        $search = $request->searchTerm;
        $get_topic_query = "SELECT * FROM fx_topics ";
        if(!empty($search)){
            $get_tags_query = $get_topic_query . "WHERE name LIKE '%$search%'";
        }
        $get_topic = DB::select($get_topic_query);
        $get_topic = collect($get_topic);
        return response()->json($get_topic);
    }

    public function rss_data_source(Request $request){
        $search = $request->searchTerm;
        $get_source_query = "SELECT DISTINCT name FROM fx_rss UNION SELECT DISTINCT source FROM fx_r_s_s_news";
        $get_source = DB::select($get_source_query);
        $get_source = collect($get_source);
        if(!empty($search)){
            $get_source = $get_source->where('name', 'like', '%'.$search.'%');
        }
        return response()->json($get_source);
    }


    public function rss_data_delete(Request $request, $id)
    {
        $model = TransactionRssData::where("code",$id)->first();
        $data['rssfeedsettings'] = $model;
        // dd($model);
        return view('rssfeedsettings::modal.rss_data_delete')->with($data);
    }

    public function rss_data_delete_process($id = null)
    {
        // dd($id);
        TransactionRssData::where("code",$id)->delete();
        

        // $RSSNews = RSSNews::where("transaction_rss_id",)->
        // RSSNewsCategory

        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('rssfeedsettings.rss_data'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function rss_news_delete_change(Request $request)
    {
   
        foreach($request->id_chang as $id ){

            TransactionRssData::where("code",$id)->delete();

        }

        // $RSSNews = RSSNews::where("transaction_rss_id",)->
        // RSSNewsCategory

        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('rssfeedsettings.rss_data'),
            ],
            true,
            Response::HTTP_OK
        );
    }


    public function rss_news_delete(Request $request, $id)
    {
        $model = RSSNews::where("code",$id)->first();
        $data['rssfeedsettings'] = $model;
        // dd($model);
        return view('rssfeedsettings::modal.rss_news_delete')->with($data);
    }

    public function rss_news_delete_process($id = null)
    {
  
        $RSS_news = RSSNews::where("code",$id)->first();
        RSSNews::where("code",$id)->delete();


       
        // $RSSNews = RSSNews::where('transaction_rss_id',$check_TransactionRssData->id)->first();
        if($RSS_news) {
            // $RSSNews_del = RSSNews::where('transaction_rss_id',$check_TransactionRssData);
            // $RSSNews_del->delete();
            $RSSNewsCategorycheck = RSSNewsCategory::where('rss_news_id',$RSS_news->id)->get();
            $category_id = [];
            foreach($RSSNewsCategorycheck as $item){
                $category_id[] = $item -> news_category_id;
            }
            $SiteCategory = SiteCategory::whereIn("category_id", $category_id)->get();
                // dd($SiteCategory[0]->site_email_alert);
        
                $site_news = [];
                if($SiteCategory) {
                    foreach($SiteCategory as $SiteCategory_val) {
                        if($SiteCategory_val) {
                            $site_email_alert = site_config_email_alert::where("site_id",$SiteCategory_val->site_id)->get();
                            if($site_email_alert) {
                                foreach($site_email_alert as $site_email_alert_val) {
                                    $site_news[] = @$SiteCategory_val->site_email_alert->site_id;
                                }
                            }
                        }
                    }
                }
                foreach($site_news as $data){
                    $TransactionClientNews = TransactionClientNews::where('site_id', $data)->where('transaction_id', $RSS_news -> id)->first();
                    if($TransactionClientNews){
                        $TransactionClientNews -> transaction_mode = 'delete';
                        $TransactionClientNews -> transaction_data_status = 1;
                        $TransactionClientNews -> status = 1;
                        $TransactionClientNews -> save();
                    }else{
                        $TransactionClientNews = new TransactionClientNews();
                        $TransactionClientNews -> site_id = $data;
                        $TransactionClientNews -> transaction_id = $RSS_news -> id;
                        $TransactionClientNews -> transaction_mode = 'delete';
                        $TransactionClientNews -> transaction_data_status = 1;
                        $TransactionClientNews -> status = 1;
                        $TransactionClientNews -> save();
                    }
                    if($SiteCategory){
                        foreach($SiteCategory as $SiteCategories){
                            $fx_transaction_client_news_categories = fx_transaction_client_news_categories::where('site_id', $data)->where('transaction_id', $SiteCategories -> category_id)->first();
                            if($fx_transaction_client_news_categories){
                                $fx_transaction_client_news_categories -> transaction_mode = 'delete';
                                $fx_transaction_client_news_categories -> transaction_data_status = 1;
                                $fx_transaction_client_news_categories -> status = 1;
                                $fx_transaction_client_news_categories -> save();
                            }else{
                                $fx_transaction_client_news_categories = new fx_transaction_client_news_categories();
                                $fx_transaction_client_news_categories -> site_id = $data;
                                $fx_transaction_client_news_categories -> transaction_id = $SiteCategories -> category_id;
                                $fx_transaction_client_news_categories -> transaction_mode = 'delete';
                                $fx_transaction_client_news_categories -> transaction_data_status = 1;
                                $fx_transaction_client_news_categories -> status = 1;
                                $fx_transaction_client_news_categories -> save();
                            }
                        }
                    }
                   
                    
                }
                
            $RSSNewsCategory = RSSNewsCategory::where('rss_news_id',$RSS_news->id);
            $RSSNewsCategory->delete();
        }
        

        // $RSSNews = RSSNews::where("transaction_rss_id",)->
        // RSSNewsCategory

        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('rssfeedsettings.news'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function rss_news_delete_select(Request $request)
    {
        foreach ($request->id as $id) {
            $RSS_news = RSSNews::where("code",$id)->first();
            RSSNews::where("code",$id)->delete();
    
    
           
            // $RSSNews = RSSNews::where('transaction_rss_id',$check_TransactionRssData->id)->first();
            if($RSS_news) {
                // $RSSNews_del = RSSNews::where('transaction_rss_id',$check_TransactionRssData);
                // $RSSNews_del->delete();
    
                $RSSNewsCategory = RSSNewsCategory::where('rss_news_id',$RSS_news->id);
                $RSSNewsCategory->delete();
            }
        }

        

        // $RSSNews = RSSNews::where("transaction_rss_id",)->
        // RSSNewsCategory

        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('rssfeedsettings.news'),
            ],
            true,
            Response::HTTP_OK
        );
    }



    public function rss_data_store_news_create(Request $request){
        $logo = asset('images/image-not-found.jpg');
        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $imagename = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('images/logo_news');
            $image->move($destinationPath, $imagename);
            $logo = asset('images/logo_news/'.$imagename);
        }


        $SiteCategory = SiteCategory::whereIn("category_id",$request -> category_news)->get();
        // dd($SiteCategory[0]->site_email_alert);

        $email_site_alert = [];
        $site_news = [];
        if($SiteCategory) {
            foreach($SiteCategory as $SiteCategory_val) {
                if($SiteCategory_val) {

                    $site_email_alert = site_config_email_alert::where("site_id",$SiteCategory_val->site_id)->get();

                    if($site_email_alert) {
                        foreach($site_email_alert as $site_email_alert_val) {
                            $email_site_alert[] = $site_email_alert_val->email;
                            $site_news[] = @$SiteCategory_val->site_email_alert->site_id;
                        }
                    }
                    // if(@$SiteCategory_val->site_email_alert->email) {
                    //     $email_site_alert[] = @$SiteCategory_val->site_email_alert->email;
                    // }
                }
            }


            // $email_site_alert_implode = implode(",",$email_site_alert);
            // dd($email_site_alert);
        }



        // $site_config_email_alert = site_config_email_alert::where()

        $RSSNews_check = RSSNews::where("code",$request->rss_code)->first();

        if(@$RSSNews_check) {

            $RSSNews_check -> code = generator_uuid();
            $RSSNews_check -> logo = $logo;
            $RSSNews_check -> title_th = $request -> title_th;
            $RSSNews_check -> title_en = $request -> title_en;
            $RSSNews_check -> source = $request -> source;
            $RSSNews_check -> public_date = Carbon::parse($request -> public_date);
            $detail_th = @$_POST['detail_th']; //รับค่าจาก messageInput
            if($detail_th) {
                $dom = new \domdocument();
                if($dom->getelementsbytagname('img')){
                    $dom->loadHtml('<?xml encoding="UTF-8">'.$detail_th,
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
                            $img->setattribute('src', url('/images/file_editor/'.$image_name));
                        } else {

                        }
                    }
                    $detail_th = $dom->savehtml();

                }
                //Summernote substr code ส่วนแรกกับท้ายออก
                // $substr_before = substr($detail, 142);
                // $substr_last = substr($substr_before, 0 , -15);
                // $after_substr_content = $substr_last;
            }
            $RSSNews_check -> detail_th = $detail_th;
    
            $detail_en = @$_POST['detail_en']; //รับค่าจาก messageInput
            if($detail_en) {
                $dom = new \domdocument();
                if($dom->getelementsbytagname('img')){
                    $dom->loadHtml('<?xml encoding="UTF-8">'.$detail_en,
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
                            $img->setattribute('src', url('/images/file_editor/'.$image_name));
                        } else {

                        }
                    }
                    $detail_en = $dom->savehtml();

                }
                //Summernote substr code ส่วนแรกกับท้ายออก
                // $substr_before = substr($detail, 142);
                // $substr_last = substr($substr_before, 0 , -15);
                // $after_substr_content = $substr_last;
            }
            $RSSNews_check -> detail_en = $detail_en;
    
            $RSSNews_check -> status = $request -> status ? 1 : 0;
            if($request->formsubmit == 'formDraft'){
                $RSSNews_check -> save_draft = 1;
            } else {
                $RSSNews_check -> save_draft = 0;
            }
            $RSSNews_check -> save();

            $RSSNewsCategory_del = RSSNewsCategory::where("rss_news_id",$RSSNews_check->id);
            $RSSNewsCategory_del->delete();

            if(!empty($request -> category_news)){
                foreach($request -> category_news as $item){
                    $RSSNewsCategory = new RSSNewsCategory();
                    $RSSNewsCategory -> code = generator_uuid();
                    $RSSNewsCategory -> rss_news_id = $RSSNews_check -> id;
                    $RSSNewsCategory -> news_category_id = $item;
                    $RSSNewsCategory -> status = 1;
                    $RSSNewsCategory -> save();
                }
            }
            
            // if(!empty($request -> tags)){
            //     foreach($request -> tags as $item){
            //         $tags = Tags::where('name', $item)->first();
            //         if($tags){
            //             $NewsTag = new NewsTag();
            //             $NewsTag -> code = generator_uuid();
            //             $NewsTag -> tag_id = $tags -> id;
            //             $NewsTag -> rss_news_id = $RSSNews -> id;
            //             $NewsTag -> status = 1;
            //             $NewsTag -> save();
            //         }else{
            //             $tags = new Tags;
            //             $tags -> name = $item;
            //             $tags -> save();
    
            //             $NewsTag = new NewsTag();
            //             $NewsTag -> code = generator_uuid();
            //             $NewsTag -> tag_id = $tags -> id;
            //             $NewsTag -> rss_news_id = $RSSNews -> id;
            //             $NewsTag -> status = 1;
            //             $NewsTag -> save();
            //         } 
            //     }
            // }
    
            // if(!empty($request -> topic)){
            //     foreach($request -> topic as $item){
            //         $Topic = Topic::where('name', $item)->first();
            //         if($Topic){
            //             $NewsTopics = new NewsTopics();
            //             $NewsTopics -> code = generator_uuid();
            //             $NewsTopics -> topic_id = $Topic -> id;
            //             $NewsTopics -> rss_news_id = $RSSNews -> id;
            //             $NewsTopics -> status = 1;
            //             $NewsTopics -> save();
            //         }else{
            //             $Topic = new Topic();
            //             $Topic -> name = $item;
            //             $Topic -> status = 1;
            //             $Topic -> save();
    
            //             $NewsTopics = new NewsTopics();
            //             $NewsTopics -> code = generator_uuid();
            //             $NewsTopics -> topic_id = $Topic -> id;
            //             $NewsTopics -> rss_news_id = $RSSNews -> id;
            //             $NewsTopics -> status = 1;
            //             $NewsTopics -> save();
            //         } 
            //     }
            // }
            if($request->formsubmit !== 'formDraft'){
                if($email_site_alert) {
                    foreach($email_site_alert as $data){
                        // var_dump($data);
                        $this->news = [
                            'news' => $RSSNews_check,
                        ];

                        // dd($this->news);
                        Mail::to($data)->send(new NewsMail($this->news));
                    }
                }
                // $mail = ['master_msn@msn.com', 'a.bestpad@gmail.com'];
                // foreach($mail as $data){
                //     $this->news = [
                //         'news' => $RSSNews_check,
                //     ];
                //     Mail::to($data)->send(new NewsMail($this->news));
                // }
                foreach($site_news as $data){
                    $TransactionClientNews = TransactionClientNews::where('site_id', $data)->where('transaction_id', $RSSNews_check -> id)->first();
                    if($TransactionClientNews){
                        $TransactionClientNews -> transaction_mode = 'update';
                        $TransactionClientNews -> transaction_data_status = 1;
                        $TransactionClientNews -> status = 1;
                        $TransactionClientNews -> save();
                    }else{
                        $TransactionClientNews = new TransactionClientNews();
                        $TransactionClientNews -> site_id = $data;
                        $TransactionClientNews -> transaction_id = $RSSNews_check -> id;
                        $TransactionClientNews -> transaction_mode = 'update';
                        $TransactionClientNews -> transaction_data_status = 1;
                        $TransactionClientNews -> status = 1;
                        $TransactionClientNews -> save();
                    }
                    if($SiteCategory){
                        foreach($SiteCategory as $SiteCategories){
                            $fx_transaction_client_news_categories = fx_transaction_client_news_categories::where('site_id', $data)->where('transaction_id', $SiteCategories -> category_id)->first();
                            if($fx_transaction_client_news_categories){
                                $fx_transaction_client_news_categories -> transaction_mode = 'update';
                                $fx_transaction_client_news_categories -> transaction_data_status = 1;
                                $fx_transaction_client_news_categories -> status = 1;
                                $fx_transaction_client_news_categories -> save();
                            }else{
                                $fx_transaction_client_news_categories = new fx_transaction_client_news_categories();
                                $fx_transaction_client_news_categories -> site_id = $data;
                                $fx_transaction_client_news_categories -> transaction_id = $SiteCategories -> category_id;
                                $fx_transaction_client_news_categories -> transaction_mode = 'update';
                                $fx_transaction_client_news_categories -> transaction_data_status = 1;
                                $fx_transaction_client_news_categories -> status = 1;
                                $fx_transaction_client_news_categories -> save();
                            }
                        }
                    }
                }
            }

        } else {

            $RSSNews = new RSSNews();
            $RSSNews -> code = generator_uuid();
            $RSSNews -> logo = $logo;
            $RSSNews -> title_th = $request -> title_th;
            $RSSNews -> title_en = $request -> title_en;
            $RSSNews -> source = $request -> source;
            $RSSNews -> public_date = Carbon::parse($request -> public_date);
            $detail_th = @$_POST['detail_th']; //รับค่าจาก messageInput
            if($detail_th) {
                $dom = new \domdocument();
                if($dom->getelementsbytagname('img')){
                    $dom->loadHtml('<?xml encoding="UTF-8">'.$detail_th,
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
                            $img->setattribute('src', url('/images/file_editor/'.$image_name));
                        } else {

                        }
                    }
                    $detail_th = $dom->savehtml();

                }
                //Summernote substr code ส่วนแรกกับท้ายออก
                // $substr_before = substr($detail, 142);
                // $substr_last = substr($substr_before, 0 , -15);
                // $after_substr_content = $substr_last;
            }
            $RSSNews -> detail_th = $detail_th;
    
            $detail_en = @$_POST['detail_en']; //รับค่าจาก messageInput
            if($detail_en) {
                $dom = new \domdocument();
                if($dom->getelementsbytagname('img')){
                    $dom->loadHtml('<?xml encoding="UTF-8">'.$detail_en,
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
                            $img->setattribute('src', url('/images/file_editor/'.$image_name));
                        } else {

                        }
                    }
                    $detail_en = $dom->savehtml();

                }
                //Summernote substr code ส่วนแรกกับท้ายออก
                // $substr_before = substr($detail, 142);
                // $substr_last = substr($substr_before, 0 , -15);
                // $after_substr_content = $substr_last;
            }
            $RSSNews -> detail_en = $detail_en;
    
            $RSSNews -> status = $request -> status ? 1 : 0;
            if($request->formsubmit == 'formDraft'){
                $RSSNews -> save_draft = 1;
            }
            $RSSNews -> save();
            if(!empty($request -> category_news)){
                foreach($request -> category_news as $item){
                    $RSSNewsCategory = new RSSNewsCategory();
                    $RSSNewsCategory -> code = generator_uuid();
                    $RSSNewsCategory -> rss_news_id = $RSSNews -> id;
                    $RSSNewsCategory -> news_category_id = $item;
                    $RSSNewsCategory -> status = 1;
                    $RSSNewsCategory -> save();
                }
            }
            
            // if(!empty($request -> tags)){
            //     foreach($request -> tags as $item){
            //         $tags = Tags::where('name', $item)->first();
            //         if($tags){
            //             $NewsTag = new NewsTag();
            //             $NewsTag -> code = generator_uuid();
            //             $NewsTag -> tag_id = $tags -> id;
            //             $NewsTag -> rss_news_id = $RSSNews -> id;
            //             $NewsTag -> status = 1;
            //             $NewsTag -> save();
            //         }else{
            //             $tags = new Tags;
            //             $tags -> name = $item;
            //             $tags -> save();
    
            //             $NewsTag = new NewsTag();
            //             $NewsTag -> code = generator_uuid();
            //             $NewsTag -> tag_id = $tags -> id;
            //             $NewsTag -> rss_news_id = $RSSNews -> id;
            //             $NewsTag -> status = 1;
            //             $NewsTag -> save();
            //         } 
            //     }
            // }
    
            // if(!empty($request -> topic)){
            //     foreach($request -> topic as $item){
            //         $Topic = Topic::where('name', $item)->first();
            //         if($Topic){
            //             $NewsTopics = new NewsTopics();
            //             $NewsTopics -> code = generator_uuid();
            //             $NewsTopics -> topic_id = $Topic -> id;
            //             $NewsTopics -> rss_news_id = $RSSNews -> id;
            //             $NewsTopics -> status = 1;
            //             $NewsTopics -> save();
            //         }else{
            //             $Topic = new Topic();
            //             $Topic -> name = $item;
            //             $Topic -> status = 1;
            //             $Topic -> save();
    
            //             $NewsTopics = new NewsTopics();
            //             $NewsTopics -> code = generator_uuid();
            //             $NewsTopics -> topic_id = $Topic -> id;
            //             $NewsTopics -> rss_news_id = $RSSNews -> id;
            //             $NewsTopics -> status = 1;
            //             $NewsTopics -> save();
            //         } 
            //     }
            // }
            if($request->formsubmit !== 'formDraft'){
                if($email_site_alert) {
                    foreach($email_site_alert as $data){
                        $this->news = [
                            'news' => $RSSNews,
                        ];
                        Mail::to($data)->send(new NewsMail($this->news));
                    }
                }
                // $mail = ['master_msn@msn.com', 'a.bestpad@gmail.com'];
                // foreach($mail as $data){
                //     $this->news = [
                //         'news' => $RSSNews,
                //     ];
                //     Mail::to($data)->send(new NewsMail($this->news));
                // }
                foreach($site_news as $data){
                    $TransactionClientNews = TransactionClientNews::where('site_id', $data)->where('transaction_id', $RSSNews -> id)->first();
                    if($TransactionClientNews){
                        $TransactionClientNews -> transaction_mode = 'insert';
                        $TransactionClientNews -> transaction_data_status = 1;
                        $TransactionClientNews -> status = 1;
                        $TransactionClientNews -> save();
                    }else{
                        $TransactionClientNews = new TransactionClientNews();
                        $TransactionClientNews -> site_id = $data;
                        $TransactionClientNews -> transaction_id = $RSSNews -> id;
                        $TransactionClientNews -> transaction_mode = 'insert';
                        $TransactionClientNews -> transaction_data_status = 1;
                        $TransactionClientNews -> status = 1;
                        $TransactionClientNews -> save();
                    }
                    if($SiteCategory){
                        foreach($SiteCategory as $SiteCategories){
                            $fx_transaction_client_news_categories = fx_transaction_client_news_categories::where('site_id', $data)->where('transaction_id', $SiteCategories -> category_id)->first();
                            if($fx_transaction_client_news_categories){
                                $fx_transaction_client_news_categories -> transaction_mode = 'insert';
                                $fx_transaction_client_news_categories -> transaction_data_status = 1;
                                $fx_transaction_client_news_categories -> status = 1;
                                $fx_transaction_client_news_categories -> save();
                            }else{
                                $fx_transaction_client_news_categories = new fx_transaction_client_news_categories();
                                $fx_transaction_client_news_categories -> site_id = $data;
                                $fx_transaction_client_news_categories -> transaction_id = $SiteCategories -> category_id;
                                $fx_transaction_client_news_categories -> transaction_mode = 'insert';
                                $fx_transaction_client_news_categories -> transaction_data_status = 1;
                                $fx_transaction_client_news_categories -> status = 1;
                                $fx_transaction_client_news_categories -> save();
                            }
                        }
                    }
                }
            }
        }

        
        return ajaxResponse(
            [
                // 'cate' => $SiteCategory,
                // 'test' => $email_site_alert,
                'message'  => "Successfully",
                'redirect' => route('rssfeedsettings.news'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function rss_data_store_news(Request $request){
        // dd($request);
        // return $_POST['detail_th'];
        // return '4444 '.$request -> detail_th;
        $SiteCategory = SiteCategory::whereIn("category_id",$request -> category_news)->get();
        // dd($SiteCategory[0]->site_email_alert);

        $email_site_alert = [];
        if($SiteCategory) {
            foreach($SiteCategory as $SiteCategory_val) {
                if($SiteCategory_val) {
                    if(@$SiteCategory_val->site_email_alert->email) {
                        $email_site_alert[] = @$SiteCategory_val->site_email_alert->email;
                    }
                }
            }
            // $email_site_alert_implode = implode(",",$email_site_alert);
            // dd($email_site_alert);
        }
        
        if($request->formsubmit == 'formSavingAndRun'){
            return ajaxResponse(
                [
                    'message'  => "Successfully",
                    'redirect' => route('rssfeedsettings.rss_data'),
                ],
                true,
                Response::HTTP_OK
            );
        }else{
            $TransactionRssData = TransactionRssData::where('code', $request->rss_code)->first();
            $logo = asset('images/image-not-found.jpg');
            
            if($TransactionRssData) {
                if($TransactionRssData -> enclosure){
                    $logo = $TransactionRssData -> enclosure;
                }
                $RSSNews_check = RSSNews::where("transaction_rss_id",$TransactionRssData->id)->first();
                if($RSSNews_check) {
                    $RSSNews_check-> code = generator_uuid();
                    $RSSNews_check -> logo = $logo;
                    $RSSNews_check -> title_th = $request -> title_th;
                    $RSSNews_check -> title_en = $request -> title_en;
                    $RSSNews_check -> source = $request -> source;
                    $RSSNews_check -> link = $TransactionRssData -> link;
                    $RSSNews_check -> public_date = Carbon::parse($request -> public_date);
                    $detail_th = @$_POST['detail_th']; //รับค่าจาก messageInput
                    // dd($detail_th);
                    if($detail_th) {
                        $dom = new \domdocument();
                        if($dom->getelementsbytagname('img')){
                            $dom->loadHtml('<?xml encoding="UTF-8">'.$detail_th,
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
                                    $img->setattribute('src', url('/images/file_editor/'.$image_name));
                                } else {

                                }
                            }
                            $detail_th = $dom->savehtml();
        
                        }
                        //Summernote substr code ส่วนแรกกับท้ายออก
                        // $substr_before = substr($detail, 142);
                        // $substr_last = substr($substr_before, 0 , -15);
                        // $after_substr_content = $substr_last;
                    }
                    

                    $RSSNews_check -> detail_th = $detail_th;

                    $detail_en = @$_POST['detail_en']; //รับค่าจาก messageInput
                    if($detail_en) {
                        $dom = new \domdocument();
                        if($dom->getelementsbytagname('img')){
                            $dom->loadHtml('<?xml encoding="UTF-8">'.$detail_en,
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
                                    $img->setattribute('src', url('/images/file_editor/'.$image_name));
                                } else {

                                }
                            }
                            $detail_en = $dom->savehtml();
        
                        }
                        //Summernote substr code ส่วนแรกกับท้ายออก
                        // $substr_before = substr($detail, 142);
                        // $substr_last = substr($substr_before, 0 , -15);
                        // $after_substr_content = $substr_last;
                    }
                    $RSSNews_check -> detail_en = $detail_en;
        
                    $RSSNews_check -> transaction_rss_id = $TransactionRssData -> id;
                    $RSSNews_check -> status = $request -> status ? 1 : 0;
                    if($request->formsubmit == 'formDraft'){
                        $RSSNews_check -> save_draft = 1;
                    } else {
                        $RSSNews_check -> save_draft = 0;
                    }
                    $RSSNews_check -> save();


                    $RSSNewsCategory_del = RSSNewsCategory::where("rss_news_id",$RSSNews_check->id);
                    $RSSNewsCategory_del->delete();

                    if(!empty($request -> category_news)){
                        foreach($request -> category_news as $item){
                            $RSSNewsCategory = new RSSNewsCategory();
                            $RSSNewsCategory -> code = generator_uuid();
                            $RSSNewsCategory -> rss_news_id = $RSSNews_check -> id;
                            $RSSNewsCategory -> news_category_id = $item;
                            $RSSNewsCategory -> status = 1;
                            $RSSNewsCategory -> save();
                        }
                    }



                    if($request->formsubmit !== 'formDraft'){
                        if($email_site_alert) {
                            foreach($email_site_alert as $data){
                                $this->news = [
                                    'news' => $RSSNews_check,
                                ];
                                // dd($this->news);
                                Mail::to($data)->send(new NewsMail($this->news));
                            }
                        }
                        // $mail = ['master_msn@msn.com', 'a.bestpad@gmail.com'];
                        // // $mail = ['todeooooo@gmail.com', 'yongyot.kamma@gmail.com'];
                        // foreach($mail as $data){
                        //     $this->news = [
                        //         'news' => $RSSNews_check,
                        //     ];
                        //     Mail::to($data)->send(new NewsMail($this->news));
                        // }
                    } 
                } else {

                    $RSSNews = new RSSNews();
                    $RSSNews -> code = generator_uuid();
                    $RSSNews -> logo = $logo;
                    $RSSNews -> title_th = $request -> title_th;
                    $RSSNews -> title_en = $request -> title_en;
                    $RSSNews -> source = $request -> source;
                    $RSSNews -> link = $TransactionRssData -> link;
                    $RSSNews -> public_date = Carbon::parse($request -> public_date);
                    $detail_th = @$_POST['detail_th']; //รับค่าจาก messageInput
                    if($detail_th) {
                        $dom = new \domdocument();
                        if($dom->getelementsbytagname('img')){
                            $dom->loadHtml('<?xml encoding="UTF-8">'.$detail_th,
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
                                    $img->setattribute('src', url('/images/file_editor/'.$image_name));
                                } else {

                                }
                            }
                            $detail_th = $dom->savehtml();
        
                        }
                        //Summernote substr code ส่วนแรกกับท้ายออก
                        // $substr_before = substr($detail, 142);
                        // $substr_last = substr($substr_before, 0 , -15);
                        // $after_substr_content = $substr_last;
                    }
                    $RSSNews -> detail_th = $detail_th;
        
                    $detail_en = @$_POST['detail_en']; //รับค่าจาก messageInput
                    if($detail_en) {
                        $dom = new \domdocument();
                        if($dom->getelementsbytagname('img')){
                            $dom->loadHtml('<?xml encoding="UTF-8">'.$detail_en,
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
                                    $img->setattribute('src', url('/images/file_editor/'.$image_name));
                                } else {

                                }
                            }
                            $detail_en = $dom->savehtml();
        
                        }
                        //Summernote substr code ส่วนแรกกับท้ายออก
                        // $substr_before = substr($detail, 142);
                        // $substr_last = substr($substr_before, 0 , -15);
                        // $after_substr_content = $substr_last;
                    }
                    $RSSNews -> detail_en = $detail_en;
        
                    $RSSNews -> transaction_rss_id = $TransactionRssData -> id;
                    $RSSNews -> status = $request -> status ? 1 : 0;
                    if($request->formsubmit == 'formDraft'){
                        $RSSNews -> save_draft = 1;
                    }
                    $RSSNews -> save();

                    if(!empty($request -> category_news)){
                        foreach($request -> category_news as $item){
                            $RSSNewsCategory = new RSSNewsCategory();
                            $RSSNewsCategory -> code = generator_uuid();
                            $RSSNewsCategory -> rss_news_id = $RSSNews -> id;
                            $RSSNewsCategory -> news_category_id = $item;
                            $RSSNewsCategory -> status = 1;
                            $RSSNewsCategory -> save();
                        }
                    }
                    
                    if(!empty($request -> tags)){
                        // foreach($request -> tags as $item){
                        //     $tags = Tags::where('name', $item)->first();
                        //     if($tags){
                        //         $NewsTag = new NewsTag();
                        //         $NewsTag -> code = generator_uuid();
                        //         $NewsTag -> tag_id = $tags -> id;
                        //         $NewsTag -> rss_news_id = $RSSNews -> id;
                        //         $NewsTag -> status = 1;
                        //         $NewsTag -> save();
                        //     }else{
                        //         $tags = new Tags;
                        //         $tags -> name = $item;
                        //         $tags -> save();
        
                        //         $NewsTag = new NewsTag();
                        //         $NewsTag -> code = generator_uuid();
                        //         $NewsTag -> tag_id = $tags -> id;
                        //         $NewsTag -> rss_news_id = $RSSNews -> id;
                        //         $NewsTag -> status = 1;
                        //         $NewsTag -> save();
                        //     } 
                        // }
                    }
        
                    if(!empty($request -> topic)){
                        // foreach($request -> topic as $item){
                        //     $Topic = Topic::where('name', $item)->first();
                        //     if($Topic){
                        //         $NewsTopics = new NewsTopics();
                        //         $NewsTopics -> code = generator_uuid();
                        //         $NewsTopics -> topic_id = $Topic -> id;
                        //         $NewsTopics -> rss_news_id = $RSSNews -> id;
                        //         $NewsTopics -> status = 1;
                        //         $NewsTopics -> save();
                        //     }else{
                        //         $Topic = new Topic();
                        //         $Topic -> name = $item;
                        //         $Topic -> status = 1;
                        //         $Topic -> save();
        
                        //         $NewsTopics = new NewsTopics();
                        //         $NewsTopics -> code = generator_uuid();
                        //         $NewsTopics -> topic_id = $Topic -> id;
                        //         $NewsTopics -> rss_news_id = $RSSNews -> id;
                        //         $NewsTopics -> status = 1;
                        //         $NewsTopics -> save();
                        //     } 
                        // }
                    }
                    if($request->formsubmit !== 'formDraft'){
                        if($email_site_alert) {
                            foreach($email_site_alert as $data){
                                $this->news = [
                                    'news' => $RSSNews,
                                ];
                                // dd($this->news);
                                Mail::to($data)->send(new NewsMail($this->news));
                            }
                        }
                        // $mail = ['master_msn@msn.com', 'a.bestpad@gmail.com'];
                        // foreach($mail as $data){
                        //     $this->news = [
                        //         'news' => $RSSNews,
                        //     ];
                        //     Mail::to($data)->send(new NewsMail($this->news));
                        // }
                    }

                }
            }
            return ajaxResponse(
                [
                    // 'test' => $_POST['detail_th'],
                    'message'  => "Successfully",
                    'redirect' => route('rssfeedsettings.rss_data'),
                ],
                true,
                Response::HTTP_OK
            );
        }
        
    }
    
    public function rss_setting()
    {
        $data['page'] = langapp('rss_logs');
        return view('rssfeedsettings::rss_setting')->with($data);
    }

    public function rss_logs()
    {
        $data['page'] = langapp('rss_logs');
        return view('rssfeedsettings::rss_logs')->with($data);
    }

    public function rss_news()
    {
        // $data['page'] = langapp('rss_logs');
        $data['page'] = langapp('news');
        // $data['Category'] = CategorySettings::where('active',1)->get();
        $data['category'] = CategorySettings::where('active',1)->get();
        return view('rssfeedsettings::rss_news')->with($data);
    }

    public function rss_feed_all()
    {
        $data['page'] = langapp('rss_logs');
        return view('rssfeedsettings::rss_feed_all')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        

        $RSSData = new RSSData;
        $RSSData->code = generator_uuid();
        $RSSData->name = $request->name_rss;
        $RSSData->url = $request->url_rss;
        $RSSData->status = $request->status_rss ? 1 : 0;
        $RSSData->created_by = @Auth::user()->id;
        $RSSData->save();


        return ajaxResponse(
            [
                'id'       => $RSSData->id,
                'message'  => langapp('saved_successfully'),
                'redirect' => route('rssfeedsettings.index'),
            ],
            true,
            Response::HTTP_CREATED
        );
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('rssfeedsettings::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $get_data = RSSData::where("code",$id)->first();

        $data['rssfeedsettings'] = $get_data;
        // $data['page'] = $this->getPage();
        return view('rssfeedsettings::modal.update')->with($data);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(CreateRssRequest $request, $id = null)
    {
       //  dd($request);
       //  exit();
        $RSSData = RSSData::where("code",$id)->first();
        // $CategorySettings->update($request->all());
        $RSSData->name = $request->name_rss;
        $RSSData->url = $request->url_rss;
        $RSSData->status = $request->active ? 1 : 0;
        $RSSData->save();

        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $client);
        // }
        return ajaxResponse(
            [
                'id'       => $RSSData->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('rssfeedsettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
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

    public function change_status(Request $request)
    {
        // dd($request);
        // exit();
        $rss_code = $this->request->code;
        // $data['category_id'] = $this->request->category_id;
        // $CategorySettings = $this->categorySettings->findOrFail($data['category_id']);
        $rss = RSSData::where("code",$rss_code)->first();
        // $CategorySettings->update($request->all());
        // $CategorySettings->name = $request->name;
        $rss->status = $rss->status == 1 ? 0 : 1;
        $rss->save();

        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $client);
        // }
        return ajaxResponse(
            [
                'id'       => $rss->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('rssfeedsettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function change_status_news(Request $request){
        $RSSNews = RSSNews::where('code', $request -> code)->first();
        $RSSNews->status = $request->active;
        $RSSNews->save();
        return ajaxResponse(
            [
                'id'       => $RSSNews->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('rssfeedsettings.news'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function delete(Request $id)
    {
        $data['rssfeedsettings'] = $id;
        return view('rssfeedsettings::modal.delete')->with($data);
    }



    public function delete_process($id = null)
    {
        $model = RSSData::where("code",$id);
        // dd($model);
        $model->delete();
        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('rssfeedsettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

}
