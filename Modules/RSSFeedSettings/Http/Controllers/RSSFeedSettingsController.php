<?php

namespace Modules\RSSFeedSettings\Http\Controllers;

use App\siteNewsRelated;
use App\Topic;
use Modules\RSSFeedSettings\Http\Requests\CreateRssRequest;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\RSSFeedSettings\Entities\NewsCategory;
use Modules\CategorySettings\Entities\CategorySettings;
use Modules\RSSFeedSettings\Entities\NewsTag;
use Modules\RSSFeedSettings\Entities\NewsTopics;
use Modules\RSSFeedSettings\Entities\RSSData;
use Modules\RSSFeedSettings\Entities\RSSNews;
use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Modules\SiteSettings\Entities\Tags;
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
        $data['page'] = "rss data";
        return view('rssfeedsettings::rss_data')->with($data);
    }

    public function tableRssData(Request $request){
        if($request -> keywords || $request -> public_date || $request -> status !== "null" || $request -> source){
            $model = TransactionRssData::where('status', 1);
            if($request -> keywords){
                $model -> where('title', 'LIKE' ,'%'.$request -> keywords.'%');
            }
            if($request -> public_date){
                $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
            }
            if($request -> status){
                if($request -> status == '2' || $request -> status == '3'){
                    if($request -> status == '2'){
                        $RSSNews = RSSNews::where('transaction_rss_id','!=' ,null)->get();
                        foreach($RSSNews as $data){
                            $model -> where('id', $data -> transaction_rss_id);
                        }
                    }else if($request -> status == '3'){
                        $RSSNews = RSSNews::where('transaction_rss_id','!=' ,null)->get();
                        foreach($RSSNews as $data){
                            $model -> where('id', '!=' ,$data -> transaction_rss_id);
                        }
                    }
                    
                }  
            }
            if($request -> source){
                $model -> where('link', 'LIKE' ,'%'.$request -> source.'%');
            }
            $model -> get();
        }else{
            $model = TransactionRssData::all();
        }

        return DataTables::of($model)
            ->editColumn('chk', function (TransactionRssData $model) {
                    return '<label><input type="checkbox" name="checked" value="' . $model->code . '"><span class="label-text"></span></label>';
            })
            ->addColumn('link', function (TransactionRssData $model) {
                $html = '';
                $RSSNews = RSSNews::where('transaction_rss_id', $model -> id)->first();
                if(!empty($RSSNews)){
                    $html .= $model->link;
                }else{
                    $html .= "<a href='". route('rssfeedsettings.rss_data_create_news', ['code' => $model->code]) ."' data-toggle='ajaxModal'>
                        ".$model->link."
                    </a>";
                }
               
                return $html;
            })
            ->addColumn('status', function (TransactionRssData $model) {
                $RSSNews = RSSNews::where('transaction_rss_id', $model -> id)->first();
                $html = '';
                if(!empty($RSSNews) && $RSSNews -> save_draft == 1){
                    $html .= '<span class="badge badge-danger" style="background-color: #ea2e49;">Darft</span>';
                }else if(!empty($RSSNews) && $RSSNews -> save_draft == 0){
                    $html .= '<span class="badge badge-success">Used</span>';
                }else{
                    $html .= '<span class="badge badge-warning" style="background-color: #ffc107;">Not used</span>';
                }  
                return $html;
            })
            ->addColumn('action', function (TransactionRssData $model) {
                $html = '';
                $html .= "
                <a href='". route('rssfeedsettings.rss_data_delete', ['id' => $model->code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                </a></div>";
                return $html;
                // <a href='". route('rssfeedsettings.edit', ['id' => $model->code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                // <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                // </a>
            })
            ->rawColumns(['chk','link','status','action'])
            ->toJson();
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
        $model = RSSNews::all();
        return DataTables::of($model)
            ->editColumn('chk', function (RSSNews $model) {
                    return '<label><input type="checkbox" name="checked" value="' . $model->code . '"><span class="label-text"></span></label>';
            })
            ->addColumn('site_name', function (RSSNews $model) {
                $data = siteNewsRelated::where('news_id', $model -> id)->get();
                $html = '';
                foreach($data as $item){
                    $html .= $item -> get_site -> name .' , ';
                }
                return rtrim($html, ' , ');
            })
            ->addColumn('source', function (RSSNews $model) {
                return $model -> source;
            })
            ->addColumn('title', function (RSSNews $model) {
                if($model -> title_th){
                    return $model -> title_th;
                }else if($model -> title_en){
                    return $model -> title_en;
                }else{
                    return '-';
                }
            })
            ->addColumn('topic', function (RSSNews $model) {
                if(empty($model->get_topic->topic)){
                    return '-';
                }else{
                    return $model->get_topic->topic->name;
                }
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
                return '<a href="#">TH</a> | <a href="#">EN</a>';
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
                <a href='' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                </a>
                <a href='' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                </a></div>";
                return $html;
               
            })
            ->rawColumns(['chk','site_name','source','title','topic','data_status','link','status','action'])
            ->toJson();
    }

    public function tableRssSetting(){
        $model = RSSData::all();
        return DataTables::of($model)
            ->editColumn('chk', function (RSSData $model) {
                    return '<label><input type="checkbox" name="checked" value="' . $model->code . '"><span class="label-text"></span></label>';
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
                <a href='". route('rssfeedsettings.delete', ['id' => $model->code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
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
        $data['category'] = CategorySettings::where('active',1)->get();
        return view('rssfeedsettings::modal.create_news')->with($data);
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


    public function rss_data_delete(Request $id)
    {
        $data['rssfeedsettings'] = $id;
        return view('rssfeedsettings::modal.rss_data_delete')->with($data);
    }

    public function rss_data_delete_process($id = null)
    {
        $model = TransactionRssData::where("code",$id);
        $model->delete();
        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('rssfeedsettings.rss_data'),
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
        $RSSNews = new RSSNews();
        $RSSNews -> code = generator_uuid();
        $RSSNews -> logo = $logo;
        $RSSNews -> title_th = $request -> title_th;
        $RSSNews -> title_en = $request -> title_en;
        $RSSNews -> source = $request -> source;
        $RSSNews -> public_date = Carbon::parse($request -> public_date);
        $detail_th = $request -> detail_th; //รับค่าจาก messageInput
        $dom = new \domdocument();
        if ($dom->getelementsbytagname('img')) {
            $dom->loadHtml('<?xml encoding="UTF-8">' . $detail_th,
                LIBXML_HTML_NOIMPLIED |
                LIBXML_HTML_NODEFDTD |
                LIBXML_NOERROR |
                LIBXML_NOWARNING 
            );
            //ดึงเอาส่วนที่เป็นรูปภาพมาจาก summernote
            $images = $dom->getelementsbytagname('img');
            //ลูปรูปภาพและทำการเข้ารหัสรูปภาพ
            foreach ($images as $k => $img) {
                $data = $img->getattribute('src');
                $blacklistArray = array('data:image');
                $string = $data;
                $matches = array();
                $matchFound = preg_match_all(
                    "/\b(" . implode($blacklistArray, "|") . ")\b/i",
                    $string,
                    $matches
                );
                // if it find matches bad words
                if ($matchFound) {
                    $words = array_unique($matches[0]);
                    foreach ($words as $word) {
                        list($type, $data) = explode(';', $data);
                        list(, $data) = explode(',', $data);

                        $data = base64_decode($data);
                        //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                        $image_name = time() . $k . '.png';
                        //อัพโหลดภาพไปยัง public
                        $path = public_path('images/file_editor') . '/' . $image_name;
                        //ทำการอัพโหลดภาพ
                        file_put_contents($path, $data);
                        $img->removeattribute('src');
                        $img->setattribute('src', url('/images/file_editor/' . $image_name));
                    }
                }
            }
            $detail_th = $dom->savehtml();
        }
        $RSSNews -> detail_th = $detail_th;

        $detail_en = $request -> detail_en; //รับค่าจาก messageInput
        $dom = new \domdocument();
        if ($dom->getelementsbytagname('img')) {
            $dom->loadHtml('<?xml encoding="UTF-8">' . $detail_en,
                LIBXML_HTML_NOIMPLIED |
                LIBXML_HTML_NODEFDTD |
                LIBXML_NOERROR |
                LIBXML_NOWARNING 
            );
            //ดึงเอาส่วนที่เป็นรูปภาพมาจาก summernote
            $images = $dom->getelementsbytagname('img');
            //ลูปรูปภาพและทำการเข้ารหัสรูปภาพ
            foreach ($images as $k => $img) {
                $data = $img->getattribute('src');
                $blacklistArray = array('data:image');
                $string = $data;
                $matches = array();
                $matchFound = preg_match_all(
                    "/\b(" . implode($blacklistArray, "|") . ")\b/i",
                    $string,
                    $matches
                );
                // if it find matches bad words
                if ($matchFound) {
                    $words = array_unique($matches[0]);
                    foreach ($words as $word) {
                        list($type, $data) = explode(';', $data);
                        list(, $data) = explode(',', $data);

                        $data = base64_decode($data);
                        //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                        $image_name = time() . $k . '.png';
                        //อัพโหลดภาพไปยัง public
                        $path = public_path('images/file_editor') . '/' . $image_name;
                        //ทำการอัพโหลดภาพ
                        file_put_contents($path, $data);
                        $img->removeattribute('src');
                        $img->setattribute('src', url('/images/file_editor/' . $image_name));
                    }
                }
            }
            $detail_en = $dom->savehtml();
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
        
        if(!empty($request -> tags)){
            foreach($request -> tags as $item){
                $tags = Tags::where('name', $item)->first();
                if($tags){
                    $NewsTag = new NewsTag();
                    $NewsTag -> code = generator_uuid();
                    $NewsTag -> tag_id = $tags -> id;
                    $NewsTag -> rss_news_id = $RSSNews -> id;
                    $NewsTag -> status = 1;
                    $NewsTag -> save();
                }else{
                    $tags = new Tags;
                    $tags -> name = $item;
                    $tags -> save();

                    $NewsTag = new NewsTag();
                    $NewsTag -> code = generator_uuid();
                    $NewsTag -> tag_id = $tags -> id;
                    $NewsTag -> rss_news_id = $RSSNews -> id;
                    $NewsTag -> status = 1;
                    $NewsTag -> save();
                } 
            }
        }

        if(!empty($request -> topic)){
            foreach($request -> topic as $item){
                $Topic = Topic::where('name', $item)->first();
                if($Topic){
                    $NewsTopics = new NewsTopics();
                    $NewsTopics -> code = generator_uuid();
                    $NewsTopics -> topic_id = $Topic -> id;
                    $NewsTopics -> rss_news_id = $RSSNews -> id;
                    $NewsTopics -> status = 1;
                    $NewsTopics -> save();
                }else{
                    $Topic = new Topic();
                    $Topic -> name = $item;
                    $Topic -> status = 1;
                    $Topic -> save();

                    $NewsTopics = new NewsTopics();
                    $NewsTopics -> code = generator_uuid();
                    $NewsTopics -> topic_id = $Topic -> id;
                    $NewsTopics -> rss_news_id = $RSSNews -> id;
                    $NewsTopics -> status = 1;
                    $NewsTopics -> save();
                } 
            }
        }

        return ajaxResponse(
            [
                'message'  => "Successfully",
                'redirect' => route('rssfeedsettings.news'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function rss_data_store_news(Request $request){
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
            if($TransactionRssData -> enclosure){
                $logo = $TransactionRssData -> enclosure;
            }
            $RSSNews = new RSSNews();
            $RSSNews -> code = generator_uuid();
            $RSSNews -> logo = $logo;
            $RSSNews -> title_th = $request -> title_th;
            $RSSNews -> title_en = $request -> title_en;
            $RSSNews -> source = $request -> source;
            $RSSNews -> link = $TransactionRssData -> link;
            $RSSNews -> public_date = Carbon::parse($request -> public_date);
            $detail_th = $request -> detail_th; //รับค่าจาก messageInput
            $dom = new \domdocument();
            if ($dom->getelementsbytagname('img')) {
                $dom->loadHtml('<?xml encoding="UTF-8">' . $detail_th,
                    LIBXML_HTML_NOIMPLIED |
                    LIBXML_HTML_NODEFDTD |
                    LIBXML_NOERROR |
                    LIBXML_NOWARNING 
                );
                //ดึงเอาส่วนที่เป็นรูปภาพมาจาก summernote
                $images = $dom->getelementsbytagname('img');
                //ลูปรูปภาพและทำการเข้ารหัสรูปภาพ
                foreach ($images as $k => $img) {
                    $data = $img->getattribute('src');
                    $blacklistArray = array('data:image');
                    $string = $data;
                    $matches = array();
                    $matchFound = preg_match_all(
                        "/\b(" . implode($blacklistArray, "|") . ")\b/i",
                        $string,
                        $matches
                    );
                    // if it find matches bad words
                    if ($matchFound) {
                        $words = array_unique($matches[0]);
                        foreach ($words as $word) {
                            list($type, $data) = explode(';', $data);
                            list(, $data) = explode(',', $data);

                            $data = base64_decode($data);
                            //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                            $image_name = time() . $k . '.png';
                            //อัพโหลดภาพไปยัง public
                            $path = public_path('images/file_editor') . '/' . $image_name;
                            //ทำการอัพโหลดภาพ
                            file_put_contents($path, $data);
                            $img->removeattribute('src');
                            $img->setattribute('src', url('/images/file_editor/' . $image_name));
                        }
                    }
                }
                $detail_th = $dom->savehtml();
            }
            $RSSNews -> detail_th = $detail_th;

            $detail_en = $request -> detail_en; //รับค่าจาก messageInput
            $dom = new \domdocument();
            if ($dom->getelementsbytagname('img')) {
                $dom->loadHtml('<?xml encoding="UTF-8">' . $detail_en,
                    LIBXML_HTML_NOIMPLIED |
                    LIBXML_HTML_NODEFDTD |
                    LIBXML_NOERROR |
                    LIBXML_NOWARNING 
                );
                //ดึงเอาส่วนที่เป็นรูปภาพมาจาก summernote
                $images = $dom->getelementsbytagname('img');
                //ลูปรูปภาพและทำการเข้ารหัสรูปภาพ
                foreach ($images as $k => $img) {
                    $data = $img->getattribute('src');
                    $blacklistArray = array('data:image');
                    $string = $data;
                    $matches = array();
                    $matchFound = preg_match_all(
                        "/\b(" . implode($blacklistArray, "|") . ")\b/i",
                        $string,
                        $matches
                    );
                    // if it find matches bad words
                    if ($matchFound) {
                        $words = array_unique($matches[0]);
                        foreach ($words as $word) {
                            list($type, $data) = explode(';', $data);
                            list(, $data) = explode(',', $data);

                            $data = base64_decode($data);
                            //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                            $image_name = time() . $k . '.png';
                            //อัพโหลดภาพไปยัง public
                            $path = public_path('images/file_editor') . '/' . $image_name;
                            //ทำการอัพโหลดภาพ
                            file_put_contents($path, $data);
                            $img->removeattribute('src');
                            $img->setattribute('src', url('/images/file_editor/' . $image_name));
                        }
                    }
                }
                $detail_en = $dom->savehtml();
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
                foreach($request -> tags as $item){
                    $tags = Tags::where('name', $item)->first();
                    if($tags){
                        $NewsTag = new NewsTag();
                        $NewsTag -> code = generator_uuid();
                        $NewsTag -> tag_id = $tags -> id;
                        $NewsTag -> rss_news_id = $RSSNews -> id;
                        $NewsTag -> status = 1;
                        $NewsTag -> save();
                    }else{
                        $tags = new Tags;
                        $tags -> name = $item;
                        $tags -> save();

                        $NewsTag = new NewsTag();
                        $NewsTag -> code = generator_uuid();
                        $NewsTag -> tag_id = $tags -> id;
                        $NewsTag -> rss_news_id = $RSSNews -> id;
                        $NewsTag -> status = 1;
                        $NewsTag -> save();
                    } 
                }
            }

            if(!empty($request -> topic)){
                foreach($request -> topic as $item){
                    $Topic = Topic::where('name', $item)->first();
                    if($Topic){
                        $NewsTopics = new NewsTopics();
                        $NewsTopics -> code = generator_uuid();
                        $NewsTopics -> topic_id = $Topic -> id;
                        $NewsTopics -> rss_news_id = $RSSNews -> id;
                        $NewsTopics -> status = 1;
                        $NewsTopics -> save();
                    }else{
                        $Topic = new Topic();
                        $Topic -> name = $item;
                        $Topic -> status = 1;
                        $Topic -> save();

                        $NewsTopics = new NewsTopics();
                        $NewsTopics -> code = generator_uuid();
                        $NewsTopics -> topic_id = $Topic -> id;
                        $NewsTopics -> rss_news_id = $RSSNews -> id;
                        $NewsTopics -> status = 1;
                        $NewsTopics -> save();
                    } 
                }
            }

            return ajaxResponse(
                [
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
        $data['page'] = langapp('rss_logs');
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
