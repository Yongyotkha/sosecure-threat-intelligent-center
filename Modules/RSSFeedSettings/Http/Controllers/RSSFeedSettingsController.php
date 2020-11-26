<?php

namespace Modules\RSSFeedSettings\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\RSSFeedSettings\Entities\NewsCategory;
use Modules\CategorySettings\Entities\CategorySettings;
use Modules\RSSFeedSettings\Entities\NewsTag;
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

    public function tableRssData(){
        $model = TransactionRssData::all();
        return DataTables::of($model)
            ->editColumn('chk', function (TransactionRssData $model) {
                    return '<label><input type="checkbox" name="checked" value="' . $model->code . '"><span class="label-text"></span></label>';
            })
            ->addColumn('link', function (TransactionRssData $model) {
                $html = '';
                $html .= "<a href='". route('rssfeedsettings.rss_data_create_news', ['code' => $model->code]) ."' data-toggle='ajaxModal'>
                    ".$model->link."
                </a>";
                return $html;
            })
            ->addColumn('status', function (TransactionRssData $model) {
                $html = '';
                return $html;
            })
            ->addColumn('action', function (TransactionRssData $model) {
                $html = '';
                $html .= "<a href='' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                </a>
                <a href='' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
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

    public function rss_data_tags(Request $request){
        $search = $request->searchTerm;
        $get_tags_query = "SELECT * FROM fx_tags ";
        if(!empty($search)){
            $get_tags_query = $get_tags_query . "AND name LIKE %"."'".$search."'"."%";
        }
        $get_tags = DB::select($get_tags_query);
        $get_tags = collect($get_tags);
        return response()->json($get_tags);
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
            $RSSNews = new RSSNews();
            $RSSNews -> code = generator_uuid();
            $RSSNews -> logo = $TransactionRssData -> enclosure;
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

            $RSSNews -> transaction_rss_id = $TransactionRssData -> id;
            $RSSNews -> status = $request -> status ? 1 : 0;
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
        //
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
        return view('rssfeedsettings::edit');
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
