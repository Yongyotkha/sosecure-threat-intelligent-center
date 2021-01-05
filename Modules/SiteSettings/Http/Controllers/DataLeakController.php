<?php

namespace Modules\SiteSettings\Http\Controllers;

use App\DataLeakFeed;
use App\DataLeakFeedTemp;
use App\DataLeakSocial;
use App\DataLeakSocialRef;
use App\leak_socail_ref_temp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;
use Yajra\DataTables\Facades\DataTables;


class DataLeakController extends Controller
{
        /**
     * Item Model
     *
     * @var \Modules\Items\Entities\Item
     */
    protected $item;
    protected $siteSettings;
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request, SiteSettings $siteSettings)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
        $this->siteSettings = $siteSettings;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    
    public function keyword($id)
    {
       $get_data = $this->siteSettings->get_data($id);
       $data['siteSettings'] = $get_data;
       $data['page'] = 'Keyword Setting';
       return view('sitesettings::keyword_setting')->with($data);
    }

    public function datafeed()
    {
       $DataLeakSocial = DataLeakSocial::where('deleted_at',null)->where('status',1)->get();
       $data['DataLeakSocial'] = $DataLeakSocial;
       $data['page'] = 'Data Leak Feed';
       return view('sitesettings::datafeed')->with($data);
    }

    public function socialdatas($id)
    {
       $get_data = $this->siteSettings->get_data($id);
       $data['siteSettings'] = $get_data;
       $data['page'] = 'Data Leak Datas';
       return view('sitesettings::social-datas')->with($data);
    }

    public function datafeed_darkweb()
    {
       $DataLeakSocial = DataLeakSocial::where('deleted_at',null)->where('status',1)->get();
       $data['DataLeakSocial'] = $DataLeakSocial;
       $data['page'] = 'Compromised Feed';
       return view('sitesettings::datafeed_darkweb')->with($data);
    }
    
    public function darkweb_datas($id)
    {
       $get_data = $this->siteSettings->get_data($id);
       $data['siteSettings'] = $get_data;
       $data['page'] = 'Compromised Feed';
       return view('sitesettings::darkweb-datas')->with($data);
    }

    // Compromised Feed
    public function compromised_feed()
    {
        $data['page'] = 'Compromised Feed';
        return view('sitesettings::compromised_feed')->with($data);
    }
     // Compromised Data

    public function compromised_data()
    {
        $data['page'] = 'Compromised Data';
        return view('sitesettings::compromised_data')->with($data);
    }

    public function socialdatas_all_site()
    {
    //    $get_data = $this->siteSettings->get_data($id);
    //    $data['siteSettings'] = $get_data;
        $data['site'] = SiteSettings::where("active", '=', 1)->get();
        $data['source'] = DataLeakSocial::where("status", '=', 1)->get();

        $data['page'] = 'DataLeakDatas';
        return view('sitesettings::social-datas_all_site')->with($data);
    }

    public function darkweb_datas_all_site()
    {
    //    $get_data = $this->siteSettings->get_data($id);
    //    $data['siteSettings'] = $get_data;
        $data['site'] = SiteSettings::where("active", '=', 1)->where('deleted_at',null)->get();
        $data['source'] = DataLeakSocial::where("status", '=', 1)->where('deleted_at',null)->get();

       $data['page'] = 'Dark Web Datas';
       return view('sitesettings::darkweb-datas_all_site')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('sitesettings::create');
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
        return view('sitesettings::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('sitesettings::edit');
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

    public function socialdatas_datatables(Request $request){
        $site =  $this->siteSettings->get_data($request->site_code);
        $model = DataLeakSocialRef::where('site_id', 'LIKE' , '%'.$site->id.'%')->where('deleted_at', null)->orderBy('id', 'desc');
        if($request -> search){
            if($request -> search){
                $search = $request -> search;
                $model = $model -> whereHas('get_data_leak_feed', function($query) use ($search){
                    $query -> where('feedcontent', 'LIKE' , '%'.$search.'%');
                    $query -> orwhere('tag', 'LIKE' , '%'.$search.'%');
                });
            }
            $model = $model -> get();
        }else{
            $model = $model -> get();
        }
        return DataTables::of($model)
        ->editColumn(
            'chk',
            function (DataLeakSocialRef $model) {
                return '<label><input type="checkbox" name="data_feed_id" class="data_feed_id" value="' . $model->id . '"><span class="label-text"></span></label>';
            }
        )
        ->editColumn(
            'source',
            function (DataLeakSocialRef $model) {
                if(@$model -> get_data_leak_feed -> source_name){
                    return @$model -> get_data_leak_feed -> source_name;
                }else{
                    return '-';
                }
            }
        )
        ->editColumn(
            'keyword',
            function (DataLeakSocialRef $model) {
                if($model -> keyword){
                    return $model -> keyword;
                }else{
                    return '-';
                }
            }
        )
        ->editColumn(
            'content',
            function (DataLeakSocialRef $model) {
                return @$model -> get_data_leak_feed -> feedcontent;
            }
        )
        ->editColumn(
            'data_feed',
            function (DataLeakSocialRef $model) {
                return @$model -> get_data_leak_feed -> feedtimestamp;
            }
        )
        ->editColumn(
            'view_count',
            function (DataLeakSocialRef $model) {
                return $model -> view;
            }
        )
        ->editColumn(
            'status',
            function (DataLeakSocialRef $model) {
                if($model->status == '1') {
                    $checked_val = 'checked';
                } else {
                    $checked_val = '';
                }
                $html = '';
                $html .= '<label class="switch">
                            <input type="checkbox" id="status_'.$model->code.'" onchange="change_status(\''. $model->code .'\')" '.$checked_val.' name="status" value="1">
                            <span></span>
                            </label>';

                return $html;
            }
        )
        ->editColumn(
            'action',
            function (DataLeakSocialRef $model) {
                return "<a href='". route('socialdatas.delete', ['code' => $model->code]) ."' class='btn btn-danger btn-xs' data-toggle='ajaxModal'>
                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                </a>";
            }
        )
        ->rawColumns(['chk','source','keyword','content','data_feed','view_count','status','action'])
        ->make(true);
    }

    public function socialdatas_all_site_tb(Request $request){

        $model = DataLeakSocialRef::where('deleted_at',null)->where('status',1)->with('get_site')->with('get_data_leak_feed_one');

        if($request -> search_val==1){
           
            if($request ->keywords){
                $model->where('keyword', 'LIKE', '%' . $request->keywords . '%')
                ->orWhere('feedcontent', 'LIKE', '%' . $request->keywords . '%');               
            }

            if($request ->site){

                // $model = $model ->where('site_id', $request ->site);
                $site=$request ->site;
                    $model -> whereHas('get_social_ref', function($query) use ($site){
                    $query -> where('site_id', $site);
                });               
            }

            if($request ->source){

                $model-> where('sourceid', $request ->source);
                   
            }

            if($request ->isDateSearch==1){
                $date_start = $request->startDate;
                $date_end = $request->endDate;
        
                $date_start_explode = explode(" ", $date_start);
                $date_start_date = @$date_start_explode[0];
                $date_start_time = @$date_start_explode[1] . ' ' . @$date_start_explode[2];
        
                $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
        
                $date_start_time_time = date("H:i", strtotime($date_start_time));
                $date_start_datetime_format = $date_start_date_format . ' ' . $date_start_time_time . ':00';
        
        
                $date_end_explode = explode(" ", $date_end);
                $date_end_date = @$date_end_explode[0];
                $date_end_time = @$date_end_explode[1] . ' ' . @$date_end_explode[2];
                // dd($date_end_time);
                $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                $date_end_time_time = date("H:i", strtotime($date_end_time));
                $date_end_datetime_format = $date_end_date_format . ' ' . $date_end_time_time . ':00';
                $model->whereBetween('feedtimepost', array($date_start_date_format, $date_end_date_format));

            }

            $model->get();
        }else{

            $model->get();
        }

        


        
        return DataTables::of($model)->toJson();


         // $model = DataLeakSocialRef::where('deleted_at', null)->orderBy('id', 'desc');
        // $model->whereHas('get_data_leak_feed', function ($query){
        //     $query->where('site_id', );

        
       
    }

    public function delete_dataleakdata_modal($code){

        $data["code"] = $code;
        return view('sitesettings::modal.delete_dataleakdata')->with($data);
    }

    public function delete_dataleakdata($code){
        dd($code);
        $model = DataLeakSocialRef::where('code', $code);
        $model->softDeletes();

        // return ajaxResponse(
        //     [
        //         'message'  => langapp('changes_saved_successful'),
        //         'redirect' => route('socialdatas.index',['id' => $site_code->code]),
        //     ],
        //     true,
        //     Response::HTTP_OK
        // );
    }

    
    public function darkweb_all_site_tb(Request $request){

        $where1 = ['deleted_at' => null, 'feel_type' => 'darkweb'];
        $where = ['deleted_at' => null];
        $orwhere = ['deleted_at' => null, 'feel_type' => 'compromise'];


        if($request -> search_val == 'true') {

            // $model = DataLeakFeed::where($where);
            $model = DataLeakSocialRef::where('deleted_at',null)->with('get_site')->with('get_data_leak_feed_one');


            // if($request -> keywords){
            //     $model = $model->where('source_name', 'LIKE', '%'.$request -> keywords.'%');
            // }

            if($request -> keywords) {

                // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
                    $model = $model->where('keyword', 'LIKE', '%'.$request -> keywords.'%');
                // });

            }

            if($request -> source) {
                $model = $model->whereHas('get_data_leak_feed_one', function($qq) use ($request) {
                    $qq->where('sourceid', $request -> source);
                });
            }

            if($request -> site) {
                // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
                    $model = $model->where('site_id', $request -> site);
                // });
            }

            if($request -> startDate) {
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
                $model = $model->whereHas('get_data_leak_feed_one', function($qq) use ($request,$date_start_datetime_format,$date_end_datetime_format) {
                    $qq -> whereBetween('feedtimepost',array($date_start_datetime_format,$date_end_datetime_format));
                });
            }



            $model->whereHas('get_data_leak_feed_one', function($q) use ($where1,$orwhere) {
                $q->where($where1);
                $q->orwhere($orwhere);
            });
           
            $model->orderBy('id', 'desc');
        } else {
            // $model = DataLeakFeed::where($where)
            //                     ->orwhere(function ($q) use ($orwhere) {
            //                         $q->where($orwhere);
            //                     })->orderBy('id', 'desc')->with('get_social_ref');

            $model = DataLeakSocialRef::where('deleted_at',null)
            ->whereHas('get_data_leak_feed_one', function($q) use ($where1,$orwhere) {
                $q->where($where1);
                $q->orwhere($orwhere);
            })
            ->with('get_site')->with('get_data_leak_feed_one');

            $model->get();
        }

        return DataTables::of($model)->toJson();

         // $model = DataLeakSocialRef::where('deleted_at', null)->orderBy('id', 'desc');
        // $model->whereHas('get_data_leak_feed', function ($query){
        //     $query->where('site_id', );

    }

    public function datafeedsocial_datatables(Request $request){
        if($request -> search_val == 1) {
            $model = DataLeakFeedTemp::where('keyword', '!=' , null)->where('keyword', '!=' , '')->where('feed_type','social');

            if($request -> search){
                $model = $model->where('keyword', 'LIKE', '%'.$request -> search.'%');
            }
       
            if($request -> source_select) {
                $model = $model->where('sourceid', $request -> source_select);
            }
            if($request -> check_all == 'true') {

            } else {
                if($request -> check_pending == 'true') {
                    $model = $model->where('approve', '0');
                }
                if($request -> check_approved == 'true') {
                    $model = $model->where('approve', '1');
                }
            }

            if($request -> start_date) {
                $date_start = $request->start_date;
                $date_end = $request->end_date;

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
                $model = $model -> whereBetween('feedtimepost',array($date_start_date_format,$date_end_date_format));
            }
            
            $model = $model->get();
        } else {
            $model = DataLeakFeedTemp::where('keyword', '!=' , null)->where('keyword', '!=' , '')->where('feed_type','social')->get();
        }

        
        return DataTables::of($model)
        ->editColumn(
            'chk',
            function (DataLeakFeedTemp $model) {
                return '<label><input type="checkbox" name="data_feed_id" class="data_feed_id" value="' . $model->id . '"><span class="label-text"></span></label>';
            }
        )
        ->editColumn(
            'source',
            function (DataLeakFeedTemp $model) {
                if($model -> source_name){
                    return $model -> source_name;
                }else{
                    return '-';
                }
                
            }
        )
        ->editColumn(
            'keyword',
            function (DataLeakFeedTemp $model) {
                if($model -> keyword){
                    return $model -> keyword;
                }else{
                    return '-';
                }
            }
        )
        ->editColumn(
            'content',
            function (DataLeakFeedTemp $model) {
                return '<div class="text-elip">'.$model -> feedcontent.'</div>';
            }
        )
        ->editColumn(
            'data_feed',
            function (DataLeakFeedTemp $model) {
                return $model -> feedtimestamp;
            }
        )
        ->editColumn(
            'url',
            function (DataLeakFeedTemp $model) {
                return '<a href="'.$model -> feedlink.'" target="_blank"><i class="fas fa-link"></i></a>';
            }
        )
        ->editColumn(
            'action',
            function (DataLeakFeedTemp $model) {
                $html = '';
                if($model -> approve == 0){
                    $html .= '<button class="btn btn-success btn-xs" data-toggle="modal" data-target="#confirm-change-status" onclick="approve_dataFeed('.$model -> id.')">
                        Approve
                    </button>';
                }else{
                    $html .= '<button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#confirm-change-status-cancle" onclick="cancle_dataFeed('.$model -> id.')">
                        Cancel
                    </button>';
                }
                return $html;
            }
        )
        ->rawColumns(['chk','source','keyword','content','data_feed','url','action'])
        ->make(true);
    }

    public function datafeed_darkweb_datatables(Request $request){
        if($request -> search_val == 1) {
            $model = DataLeakFeedTemp::where('keyword', '!=' , null)->where('keyword', '!=' , '')->where('feed_type','social');
            // $model = DataLeakFeedTemp::where('keyword', '!=' , null)->where('keyword', '!=' , '')->where('feed_type','darkweb');

            if($request -> search){
                $model = $model->where('keyword', 'LIKE', '%'.$request -> search.'%');
            }
       
            if($request -> source_select) {
                $model = $model->where('sourceid', $request -> source_select);
            }
            if($request -> check_all == 'true') {

            } else {
                if($request -> check_pending == 'true') {
                    $model = $model->where('approve', '0');
                }
                if($request -> check_approved == 'true') {
                    $model = $model->where('approve', '1');
                }
            }

            if($request -> start_date) {
                $date_start = $request->start_date;
                $date_end = $request->end_date;

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
                $model = $model -> whereBetween('feedtimepost',array($date_start_date_format,$date_end_date_format));
            }
            
            $model = $model->get();
        } else {
            $model = DataLeakFeedTemp::where('keyword', '!=' , null)->where('keyword', '!=' , '')->where('feed_type','social')->get();
        }

        
        return DataTables::of($model)
        ->editColumn(
            'chk',
            function (DataLeakFeedTemp $model) {
                return '<label><input type="checkbox" name="data_feed_id" class="data_feed_id" value="' . $model->id . '"><span class="label-text"></span></label>';
            }
        )
        ->editColumn(
            'source',
            function (DataLeakFeedTemp $model) {
                if($model -> source_name){
                    return $model -> source_name;
                }else{
                    return '-';
                }
                
            }
        )
        ->editColumn(
            'keyword',
            function (DataLeakFeedTemp $model) {
                if($model -> keyword){
                    return $model -> keyword;
                }else{
                    return '-';
                }
            }
        )
        ->editColumn(
            'content',
            function (DataLeakFeedTemp $model) {
                return '<div class="text-elip">'.$model -> feedcontent.'</div>';
            }
        )
        ->editColumn(
            'data_feed',
            function (DataLeakFeedTemp $model) {
                return $model -> feedtimestamp;
            }
        )
        ->editColumn(
            'url',
            function (DataLeakFeedTemp $model) {
                return '<a href="'.$model -> feedlink.'" target="_blank"><i class="fas fa-link"></i></a>';
            }
        )
        ->editColumn(
            'action',
            function (DataLeakFeedTemp $model) {
                $html = '';
                if($model -> approve == 0){
                    $html .= '<button class="btn btn-success btn-xs" data-toggle="modal" data-target="#confirm-change-status" onclick="approve_dataFeed('.$model -> id.')">
                        Approve
                    </button>';
                }else{
                    $html .= '<button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#confirm-change-status-cancle" onclick="cancle_dataFeed('.$model -> id.')">
                        Cancel
                    </button>';
                }
                return $html;
            }
        )
        ->rawColumns(['chk','source','keyword','content','data_feed','url','action'])
        ->make(true);
    }

    public function change_status(Request $request){
        
        $DataLeakSocialRef = DataLeakSocialRef::where('code', $request -> code)->first();
        $DataLeakSocialRef->status = $request->status;
        $DataLeakSocialRef->save();

        $site_code = $this->siteSettings->find_code($DataLeakSocialRef->site_id);
        return ajaxResponse(
            [
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('socialdatas.index',['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function darkweb_data_change_status(Request $request){

        // $DataLeakSocialRef = DataLeakSocialRef::where('code', $request -> code)->first();
        // $DataLeakSocialRef->status = $request->status;
        // $DataLeakSocialRef->save();

        $DataLeakFeed = DataLeakSocialRef::where('id', $request -> id)->first();
        $DataLeakFeed->status = $request->active;
        $DataLeakFeed->save();

        // $site_code = $this->siteSettings->find_code($DataLeakSocialRef->site_id);
        return ajaxResponse(
            [
                'message'  => langapp('changes_saved_successful'),
                // 'redirect' => route('socialdatas.index',['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function delete_socialdatas(Request $request){
        $DataLeakSocialRef = DataLeakSocialRef::where('code', $request -> code)->first();
        $data['DataLeakSocialRef'] = $DataLeakSocialRef;
        return view('sitesettings::modal.delete_socialdatas')->with($data);
    }

    public function delete_socialdata(Request $request){
        $DataLeakSocialRef = DataLeakSocialRef::where('code', $request -> code)->first();
        $DataLeakFeedTemps = DataLeakFeedTemp::whereIn('id', $DataLeakSocialRef -> temp_id)->get();
        DataLeakFeed::where('id', $DataLeakSocialRef -> data_leak_feed_id)->delete();
        DataLeakSocialRef::where('temp_id', $DataLeakSocialRef -> temp_id)->delete();
        $DataLeakFeedTemps -> approve = 0;
        $DataLeakFeedTemps -> save();
        $site_code = $this->siteSettings->find_code($DataLeakSocialRef->site_id);
        return ajaxResponse(
            [
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('socialdatas.index',['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function approve_data_feed(Request $request){
        $DataLeakFeedTemps = DataLeakFeedTemp::whereIn('id', $request -> id)->get();
        foreach($DataLeakFeedTemps as $DataLeakFeedTemp){
            $check_DataLeakFeed = DataLeakFeed::where('temp_id', $DataLeakFeedTemp -> id)->first();
            if(empty($check_DataLeakFeed)){
                $DataLeakFeed = new DataLeakFeed();
                $DataLeakFeed -> code = generator_uuid();
                $DataLeakFeed -> temp_id = $DataLeakFeedTemp -> id;
                $DataLeakFeed -> data_id = $DataLeakFeedTemp -> data_id;
                $DataLeakFeed -> sourceid = $DataLeakFeedTemp -> sourceid;
                $DataLeakFeed -> keyword = $DataLeakFeedTemp -> keyword;
                $DataLeakFeed -> source_name = $DataLeakFeedTemp -> source_name;
                $DataLeakFeed -> feedcontent = $DataLeakFeedTemp -> feedcontent;
                $DataLeakFeed -> feedlink = $DataLeakFeedTemp -> feedlink;
                $DataLeakFeed -> feedtimepost = $DataLeakFeedTemp -> feedtimepost;
                $DataLeakFeed -> feedtimestamp = $DataLeakFeedTemp -> feedtimestamp;
                $DataLeakFeed -> feeduser = $DataLeakFeedTemp -> feeduser;
                $DataLeakFeed -> tag = $DataLeakFeedTemp -> tag;
                $DataLeakFeed -> status = 1;
                $DataLeakFeed->save();

                $leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $DataLeakFeedTemp -> id)->first();
                if(!empty($leak_socail_ref_temp)){
                    $DataLeakSocialRef = new DataLeakSocialRef;
                    $DataLeakSocialRef -> code = generator_uuid();
                    $DataLeakSocialRef -> temp_id = $DataLeakFeedTemp -> id;
                    $DataLeakSocialRef -> data_leak_feed_id = $DataLeakFeed -> id;
                    $DataLeakSocialRef -> site_id = $leak_socail_ref_temp -> site_id;
                    $DataLeakSocialRef -> keyword = $leak_socail_ref_temp -> keyword;
                    $DataLeakSocialRef -> status = 1;
                    $DataLeakSocialRef -> view = 0;
                    $DataLeakSocialRef -> save();
                }

                $DataLeakFeedTemp -> approve = 1;
                $DataLeakFeedTemp -> save();
            }
        }
        
        return ajaxResponse(
            [
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('datafeed.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function cancle_data_feed(Request $request){
        $DataLeakFeedTemps = DataLeakFeedTemp::whereIn('id', $request -> id)->get();
        foreach($DataLeakFeedTemps as $DataLeakFeedTemp){
            DataLeakFeed::where('temp_id', $DataLeakFeedTemp -> id)->delete();
            DataLeakSocialRef::where('temp_id', $DataLeakFeedTemp -> id)->delete();
            $DataLeakFeedTemp -> approve = 0;
            $DataLeakFeedTemp -> save();
        }
        return ajaxResponse(
            [
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('datafeed.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }
}
