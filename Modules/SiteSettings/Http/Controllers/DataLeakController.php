<?php

namespace Modules\SiteSettings\Http\Controllers;

use App\Credentials;
use App\DataLeakFeed;
use App\DataLeakFeedTemp;
use App\DataLeakSocial;
use App\DataLeakSocialRef;
use App\Entities\CompromisedServer;
use App\leak_socail_ref_temp;
use App\Mail\CompromisedMail;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Mail;
use Modules\Scans\Entities\Assets;
use Modules\Scans\Entities\AssetsData;
use Modules\SiteSettings\Entities\Domain;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\site_config_email_alert;
use Modules\Users\Entities\User;
use Modules\Users\Entities\UserSite;
use phpseclib\Net\SSH2;
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
        $DataLeakSocial = DataLeakSocial::where('deleted_at', null)->where('status', 1)->get();
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
        $DataLeakSocial = DataLeakSocial::where('deleted_at', null)->where('status', 1)->get();
        $data['DataLeakSocial'] = $DataLeakSocial;
        // $data['page'] = langapp('compromised_feed');
        $data['page'] = langapp('compromised_feed');
        return view('sitesettings::datafeed_darkweb')->with($data);
    }

    public function get_data_feed()
    {
        $id = $this->request->id;
        if (!empty($id)) {
            $DataLeakFeedTemp = DataLeakFeedTemp::whereIn('id', $id)->where('deleted_at', null)->get();
            $response = [
                'message' => 'Successful',
                'error' => '',
                'status_code' => '200',
                'data' => $DataLeakFeedTemp,
            ];
        } else {
            $response = [
                'error' => 'Not Found',
                'status_code' => '404',
            ];
        }
        return response()->json($response);
    }

    public function approve_compromised_feed()
    {
        if ($this->request->status_action == 1) {
            //Approve
            $site_id = 0;
            $DataLeakFeed_send_mail = [];
            foreach ($this->request->id as $key => $id) {
                $DataLeakFeedTemp = DataLeakFeedTemp::where('id', $id)->where('approve', '!=', 1)->first();
                if (!empty($DataLeakFeedTemp)) {
                    $check_DataLeakFeed = DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->first();
                    if (empty($check_DataLeakFeed)) {
                        $DataLeakFeed = new DataLeakFeed();
                        $DataLeakFeed->code = generator_uuid();
                        $DataLeakFeed->temp_id = $DataLeakFeedTemp->id;
                        $DataLeakFeed->data_id = $DataLeakFeedTemp->data_id;
                        $DataLeakFeed->sourceid = $DataLeakFeedTemp->sourceid;
                        $DataLeakFeed->keyword = $DataLeakFeedTemp->keyword;
                        $DataLeakFeed->source_name = $DataLeakFeedTemp->source_name;
                        $DataLeakFeed->feedcontent = $this->request->content[$key];
                        $DataLeakFeed->feedlink = $DataLeakFeedTemp->feedlink;
                        $DataLeakFeed->feedtimepost = $DataLeakFeedTemp->feedtimepost;
                        $DataLeakFeed->feedtimestamp = $DataLeakFeedTemp->feedtimestamp;
                        $DataLeakFeed->feeduser = $DataLeakFeedTemp->feeduser;
                        $DataLeakFeed->tag = $DataLeakFeedTemp->tag;
                        $DataLeakFeed->status = 1;
                        $DataLeakFeed->view = 0;
                        $DataLeakFeed->save();

                        $DataLeakFeed_send_mail[] = $DataLeakFeed;

                        $leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $DataLeakFeedTemp->id)->first();
                        if (!empty($leak_socail_ref_temp)) {
                            $DataLeakSocialRef = new DataLeakSocialRef;
                            $DataLeakSocialRef->code = generator_uuid();
                            $DataLeakSocialRef->temp_id = $DataLeakFeedTemp->id;
                            $DataLeakSocialRef->data_leak_feed_id = $DataLeakFeed->id;
                            $DataLeakSocialRef->site_id = $leak_socail_ref_temp->site_id;
                            $DataLeakSocialRef->keyword = $leak_socail_ref_temp->keyword;
                            $DataLeakSocialRef->feel_type = $DataLeakFeedTemp->feed_type;
                            $DataLeakSocialRef->status = 1;
                            $DataLeakSocialRef->view = 0;
                            $DataLeakSocialRef->save();

                            if ($site_id == 0) {
                                $site_id = $leak_socail_ref_temp->site_id;
                            }
                        }

                        $DataLeakFeedTemp->approve = 1;
                        $DataLeakFeedTemp->save();
                    }
                }
            }

            if ($this->request->sent_mail == 1) {
                $site_email_alert = site_config_email_alert::where("site_id", $site_id)->get();
                if ($site_email_alert) {
                    foreach ($site_email_alert as $site_email_alert_val) {
                        Mail::to($site_email_alert_val->email)->send(new CompromisedMail($DataLeakFeed_send_mail, 'compromised'));
                    }
                }
            }
            $response = [
                'message' => 'Successful',
                'error' => '',
                'status_code' => '200',
                'data' => '',
            ];
        } else {
            //Cancle
            if (!empty($this->request->id)) {
                $DataLeakFeedTemps = DataLeakFeedTemp::whereIn('id', $this->request->id)->where('approve', '=', 1)->get();
                foreach ($DataLeakFeedTemps as $DataLeakFeedTemp) {
                    DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->delete();
                    DataLeakSocialRef::where('temp_id', $DataLeakFeedTemp->id)->delete();
                    $DataLeakFeedTemp->approve = 0;
                    $DataLeakFeedTemp->save();
                }
            }
            $response = [
                'message' => 'Successful',
                'error' => '',
                'status_code' => '200',
                'data' => '',
            ];
        }
        return response()->json($response);
    }

    public function darkweb_datas($id)
    {
        $get_data = $this->siteSettings->get_data($id);
        $data['siteSettings'] = $get_data;
        $data['page'] = 'Compromised Feed';
        return view('sitesettings::darkweb-datas')->with($data);
    }

    // Compromised Feed
    public function compromised_feed($code)
    {
        // dd($code);
        $get_data = $this->siteSettings->get_data($code);
        $data['siteSettings'] = $get_data;
        $siteID = siteSettings::where('code', $code)->first();

        $DataLeakSocial = DataLeakSocial::where('deleted_at', null)->where('status', 1)->get();
        $data['DataLeakSocial'] = $DataLeakSocial;
        $data['siteID'] = $siteID->id;
        $data['page'] = 'Compromised Feed';
        // dd($DataLeakSocial);
        return view('sitesettings::compromised_feed')->with($data);
    }
    // Compromised Data

    public function compromised_data($code)
    {
        $get_data = $this->siteSettings->get_data($code);
        $siteID = siteSettings::where('code', $code)->first();

        $data['site'] = SiteSettings::where("active", '=', 1)->where('deleted_at', null)->get();
        $data['source'] = DataLeakSocial::where("status", '=', 1)->where('deleted_at', null)->get();

        $data['siteID'] = $siteID->id;
        $data['page'] = 'Compromised Data';
        $data['siteSettings'] = $get_data;
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

        if (Auth::check()) {
            $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            if (Auth::user()->hasRole('admin')) { //if admin
                // dd(777);
                $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)->get();

            } else { //if notAdmin
                // dd(888);
                if (@Auth::user()->site_role_id && @Auth::user()->site_id) {
                    if (@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) { //support and admin
                        // dd(99);

                        $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)
                            ->whereIn('id', $site_id_arr) //['49', '56']
                            ->get();

                    } else { //not support and admin
                        $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)
                            ->whereIn('id', $site_id_arr) //['49', '56']
                            ->get();
                    }
                }
            }
        }

        // $data['SiteSettings'] = SiteSettings::where("active", '=', 1)->where('deleted_at', null)->get();
        $data['SiteSettings'] = $SiteSettings;

        $data['source'] = DataLeakSocial::where("status", '=', 1)->where('deleted_at', null)->get();

        $data["webserver"] = 0;
        $data["darkweb"] = 0;
        $data["compromise"] = 0;

        $data['page'] = langapp('compromised_data');
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

    public function socialdatas_datatables(Request $request)
    {
        $site = $this->siteSettings->get_data($request->site_code);
        $model = DataLeakSocialRef::where('site_id', 'LIKE', '%' . $site->id . '%')->where('deleted_at', null)->orderBy('id', 'desc');
        if ($request->search) {
            if ($request->search) {
                $search = $request->search;
                $model = $model->whereHas('get_data_leak_feed', function ($query) use ($search) {
                    $query->where('feedcontent', 'LIKE', '%' . $search . '%');
                    $query->orwhere('tag', 'LIKE', '%' . $search . '%');
                });
            }
            $model = $model->get();
        } else {
            $model = $model->get();
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
                    if (@$model->get_data_leak_feed->source_name) {
                        return @$model->get_data_leak_feed->source_name;
                    } else {
                        return '-';
                    }
                }
            )
            ->editColumn(
                'keyword',
                function (DataLeakSocialRef $model) {
                    if ($model->keyword) {
                        return $model->keyword;
                    } else {
                        return '-';
                    }
                }
            )
            ->editColumn(
                'content',
                function (DataLeakSocialRef $model) {
                    return @$model->get_data_leak_feed->feedcontent;
                }
            )
            ->editColumn(
                'data_feed',
                function (DataLeakSocialRef $model) {
                    return @$model->get_data_leak_feed->feedtimestamp;
                }
            )
            ->editColumn(
                'view_count',
                function (DataLeakSocialRef $model) {
                    return $model->view;
                }
            )
            ->editColumn(
                'status',
                function (DataLeakSocialRef $model) {
                    if ($model->status == '1') {
                        $checked_val = 'checked';
                    } else {
                        $checked_val = '';
                    }
                    $html = '';
                    $html .= '<label class="switch">
                            <input type="checkbox" id="status_' . $model->code . '" onchange="change_status(\'' . $model->code . '\')" ' . $checked_val . ' name="status" value="1">
                            <span></span>
                            </label>';

                    return $html;
                }
            )
            ->editColumn(
                'action',
                function (DataLeakSocialRef $model) {
                    return "<a href='" . route('socialdatas.delete', ['code' => $model->code]) . "' class='btn btn-danger btn-xs' data-toggle='ajaxModal'>
                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                </a>";
                }
            )
            ->rawColumns(['chk', 'source', 'keyword', 'content', 'data_feed', 'view_count', 'status', 'action'])
            ->make(true);
    }

    public function socialdatas_all_site_tb(Request $request)
    {

        $model = DataLeakSocialRef::where('deleted_at', null)
            ->whereHas('get_data_leak_feed_one', function ($query) {
                $query->where('feel_type', '=', 'social');
            })
            ->with('get_site')
            ->with('get_data_leak_feed_one');

        if ($request->search_val == 1) {

            if ($request->keywords) {
                $keywords = $request->keywords;
                $model->whereHas('get_data_leak_feed_one', function ($query) use ($keywords) {
                    $query->where('keyword', 'LIKE', '%' . $keywords . '%')
                        ->orWhere('feedcontent', 'LIKE', '%' . $keywords . '%');
                });
            }

            if ($request->site) {

                $model->where('site_id', $request->site);

            }

            if ($request->source) {

                $source = $request->source;
                $model->whereHas('get_data_leak_feed_one', function ($query) use ($source) {
                    $query->where('sourceid', 'LIKE', '%' . $source . '%');
                });

            }

            if ($request->isDateSearch == 1) {
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

                $source = $request->source;
                $model->whereHas('get_data_leak_feed_one', function ($query) use ($date_start_date_format, $date_end_date_format) {
                    $query->whereBetween('feedtimepost', array($date_start_date_format, $date_end_date_format));
                });

            }

            $model->get();
        } else {

            $model->get();
        }

        return DataTables::of($model)->toJson();

        // $model = DataLeakSocialRef::where('deleted_at', null)->orderBy('id', 'desc');
        // $model->whereHas('get_data_leak_feed', function ($query){
        //     $query->where('site_id', );

    }

    public function delete_dataleakdata_modal($code)
    {

        $data["code"] = $code;
        return view('sitesettings::modal.delete_dataleakdata')->with($data);
    }

    public function delete_darkwebdata_modal($code)
    {

        $DataLeakSocialRef = DataLeakSocialRef::where('code', $code)->first();
        $data["DataLeakSocialRef"] = $DataLeakSocialRef;
        return view('sitesettings::modal.delete_darkwebdata')->with($data);
    }

    public function delete_darkwebdata_process($code)
    {
        // dd($code);
        DataLeakSocialRef::where('code', $code)->delete();

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('darkweb.index_all_site'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function delete_dataleakdata($code)
    {
        // dd($code);
        $model = DataLeakSocialRef::where('code', $code)->delete();
        // $model->softDeletes();

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('socialdatas.index_all_site'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function change_status_dataleakdata(Request $request)
    {

        $DataLeakSocialRef = DataLeakSocialRef::where('id', $request->code)->first();
        $DataLeakSocialRef->status = $request->status;
        $DataLeakSocialRef->save();

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('socialdatas.index_all_site'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function change_delete_dataleakdata(Request $request)
    {

        // dd($request->id);

        foreach ($request->id as $social_id) {
            $data = DataLeakSocialRef::where('id', $social_id)->delete();

            // $data = CVEMapping::where("id", $request->id)->first();
            // if($data->is_fix == 1){
            //     $data->is_fix = 0;
            // }else{
            //     $data->is_fix = 1;
            // }

            // $data->save();
        }

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('socialdatas.index_all_site'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function delete_darkweb_select_process(Request $request)
    {
        // dd($request->id);
        foreach ($request->id as $val_id) {
            $data = DataLeakSocialRef::where('id', $val_id)->delete();
        }
        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('darkweb.index_all_site'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function darkweb_all_site_tb(Request $request)
    {

        $where1 = ['deleted_at' => null, 'feel_type' => 'darkweb'];
        $where = ['deleted_at' => null];
        $orwhere = ['deleted_at' => null, 'feel_type' => 'compromise'];
        $orwhere2 = ['deleted_at' => null, 'feel_type' => 'webserver'];
        $orwhere3 = ['deleted_at' => null, 'feel_type' => 'server'];

        if ($request->search_val == 'true') {

            // $model = DataLeakFeed::where($where);
            $model = DataLeakSocialRef::where('deleted_at', null)->with('get_site')->with('get_data_leak_feed_one');
            $countGroupBy = DataLeakSocialRef::where('deleted_at', null)->where('status', 1);

            // if($request -> keywords){
            //     $model = $model->where('source_name', 'LIKE', '%'.$request -> keywords.'%');
            // }

            if ($request->keywords) {

                // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
                $model = $model->where('keyword', 'LIKE', '%' . $request->keywords . '%');
                // });
                $countGroupBy = $countGroupBy->where('keyword', 'LIKE', '%' . $request->keywords . '%');

            }

            if ($request->source) {
                $model = $model->where('feel_type', '=', $request->source);
                $countGroupBy = $countGroupBy->where('feel_type', '=', $request->source);
            } else {
                $model = $model->whereIn('feel_type', ['darkweb', 'compromise', 'webserver', 'server']);
                $countGroupBy = $countGroupBy->whereIn('feel_type', ['darkweb', 'compromise', 'webserver', 'server']);
            }

            if (Auth::check()) {

                $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
                if (Auth::user()->hasRole('admin')) { //if admin
                    // dd(777);

                } else { //if notAdmin
                    // dd(888);
                    if (@Auth::user()->site_role_id && @Auth::user()->site_id) {
                        if (@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) { //support and admin
                            // dd(99);

                            $model = $model->whereIn('site_id', $site_id_arr);

                            // $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);

                        } else { //not support and admin
                            $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

                            // $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                        }
                    }
                }
            }

            if ($request->site) {
                $SiteSettings = SiteSettings::where('code', @$request->site)->first();
                // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
                $model = $model->where('site_id', $SiteSettings->id);
                // });
            }

            if ($request->startDate) {
                $date_start = $request->startDate;
                $date_end = $request->endDate;

                $date_start_explode = explode(" ", $date_start);
                $date_start_date = @$date_start_explode[0];
                $date_start_time = @$date_start_explode[1] . ' ' . @$date_start_explode[2];
                // dd($date_start_time);
                $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                // dd($date_start_date_format);
                $date_start_time_time = date("H:i", strtotime($date_start_time));
                $date_start_datetime_format = $date_start_date_format . ' ' . $date_start_time_time . ':00';
                // dd($date_start);

                $date_end_explode = explode(" ", $date_end);
                $date_end_date = @$date_end_explode[0];
                $date_end_time = @$date_end_explode[1] . ' ' . @$date_end_explode[2];
                // dd($date_end_time);
                $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                $date_end_time_time = date("H:i", strtotime($date_end_time));
                $date_end_datetime_format = $date_end_date_format . ' ' . $date_end_time_time . ':00';
                // dd($date_end_time_time);

                // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
                $model = $model->whereHas('get_data_leak_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
                    $qq->whereBetween('feedtimepost', array($date_start_datetime_format, $date_end_datetime_format));
                });
            }

            // $model->whereHas('get_data_leak_feed_one', function ($q) use ($where1, $orwhere, $orwhere2, $orwhere3) {
            //     $q->where($where1);
            //     $q->orwhere($orwhere);
            //     $q->orwhere($orwhere2);
            //     $q->orwhere($orwhere3);
            // });

            $model->orderBy('id', 'desc');
        } else {
            // $model = DataLeakFeed::where($where)
            //                     ->orwhere(function ($q) use ($orwhere) {
            //                         $q->where($orwhere);
            //                     })->orderBy('id', 'desc')->with('get_social_ref');

            $model = DataLeakSocialRef::where('deleted_at', null)->whereIn('feel_type', ['darkweb', 'compromise', 'webserver', 'server'])
                ->whereHas('get_data_leak_feed_one', function ($q) use ($where1, $orwhere, $orwhere2, $orwhere3) {
                    // $q->where($where1);
                    // $q->orwhere($orwhere);
                    // $q->orwhere($orwhere2);
                    // $q->orwhere($orwhere3);
                })
                ->with('get_site')->with('get_data_leak_feed_one');

            if (Auth::check()) {

                $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
                if (Auth::user()->hasRole('admin')) { //if admin
                    // dd(777);

                } else { //if notAdmin
                    // dd(888);
                    if (@Auth::user()->site_role_id && @Auth::user()->site_id) {
                        if (@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) { //support and admin
                            // dd(99);

                            $model = $model->whereIn('site_id', $site_id_arr);

                            // $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);

                        } else { //not support and admin
                            $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

                            // $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                        }
                    }
                }
            }

            $model->orderBy('id', 'desc')->get();
        }

        return DataTables::of($model)->toJson();

        // $model = DataLeakSocialRef::where('deleted_at', null)->orderBy('id', 'desc');
        // $model->whereHas('get_data_leak_feed', function ($query){
        //     $query->where('site_id', );

    }

    public function datafeedsocial_datatables(Request $request)
    {
        if ($request->search_val == 1) {
            $model = DataLeakFeedTemp::where('keyword', '!=', null)->where('keyword', '!=', '')->where('feed_type', 'social');

            if ($request->search) {
                $model = $model->where('keyword', 'LIKE', '%' . $request->search . '%');
            }

            if ($request->source_select) {
                $model = $model->where('sourceid', $request->source_select);
            }
            if ($request->check_all == 'true') {

            } else {
                if ($request->check_pending == 'true') {
                    $model = $model->where('approve', '0');
                }
                if ($request->check_approved == 'true') {
                    $model = $model->where('approve', '1');
                }
            }

            if ($request->start_date) {
                $date_start = $request->start_date;
                $date_end = $request->end_date;

                $date_start_explode = explode(" ", $date_start);
                $date_start_date = @$date_start_explode[0];
                // $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                // dd($date_start_time);
                $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                // dd($date_start_date_format);
                // $date_start_time_time = date("H:i", strtotime($date_start_time));
                // $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                // dd($date_start);

                $date_end_explode = explode(" ", $date_end);
                $date_end_date = @$date_end_explode[0];
                // $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                // dd($date_end_time);
                $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                // $date_end_time_time = date("H:i", strtotime($date_end_time));
                // $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
                // dd($date_end_time_time);

                // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
                $model = $model->whereBetween('feedtimepost', array($date_start_date_format, $date_end_date_format));
            }

            $model = $model->get();
        } else {
            $model = DataLeakFeedTemp::where('keyword', '!=', null)->where('keyword', '!=', '')->where('feed_type', 'social')->get();
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
                    if ($model->source_name) {
                        return $model->source_name;
                    } else {
                        return '-';
                    }

                }
            )
            ->editColumn(
                'keyword',
                function (DataLeakFeedTemp $model) {
                    if ($model->keyword) {
                        return $model->keyword;
                    } else {
                        return '-';
                    }
                }
            )
            ->editColumn(
                'content',
                function (DataLeakFeedTemp $model) {
                    return '<div class="text-elip">' . $model->feedcontent . '</div>';
                }
            )
            ->editColumn(
                'data_feed',
                function (DataLeakFeedTemp $model) {
                    return $model->feedtimestamp;
                }
            )
            ->editColumn(
                'url',
                function (DataLeakFeedTemp $model) {
                    return '<a href="' . $model->feedlink . '" target="_blank"><i class="fas fa-link"></i></a>';
                }
            )
            ->editColumn(
                'action',
                function (DataLeakFeedTemp $model) {
                    $html = '';
                    if ($model->approve == 0) {
                        $html .= '<button class="btn btn-success btn-xs" data-toggle="modal" data-target="#confirm-change-status" onclick="approve_dataFeed(' . $model->id . ')">
                        Approve
                    </button>';
                    } else {
                        $html .= '<button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#confirm-change-status-cancle" onclick="cancle_dataFeed(' . $model->id . ')">
                        Cancel
                    </button>';
                    }
                    return $html;
                }
            )
            ->rawColumns(['chk', 'source', 'keyword', 'content', 'data_feed', 'url', 'action'])
            ->make(true);
    }

    public function datafeed_darkweb_datatables(Request $request)
    {

        $where1 = ['deleted_at' => null, 'feel_type' => 'darkweb'];
        $where = ['deleted_at' => null];
        $orwhere = ['deleted_at' => null, 'feel_type' => 'compromise'];

        if ($request->search_val == 1) {
            // $model = DataLeakFeedTemp::where('keyword', '!=' , null)->where('keyword', '!=' , '')->where('feed_type','social');

            // $model = DataLeakFeedTemp::
            //     where(function($q) /*use ($where1,$orwhere)*/ {
            //         $q->where('keyword', '!=' , null);
            //         $q->where('keyword', '!=' , '');
            //         $q->where('feed_type','darkweb');
            //         // $q->orwhere($orwhere);
            //     });

            // $model = $model->orwhere(function($q) /*use ($where1,$orwhere)*/ {
            //     $q->where('keyword', '!=' , null);
            //     $q->where('keyword', '!=' , '');
            //     $q->where('feed_type','compromise');
            //     // $q->orwhere($orwhere);
            // });

            $model = DataLeakFeedTemp::
                where(function ($q) use ($request) {
                $q->where('keyword', '!=', null);
                $q->where('keyword', '!=', '');
                $q->where('feed_type', 'darkweb');
                // $q->orwhere($orwhere);

                if ($request->search) {
                    $q->where('keyword', 'LIKE', '%' . $request->search . '%');
                }

                if ($request->source_select) {
                    $q->where('sourceid', $request->source_select);
                }
                if ($request->check_all == 'true') {

                } else {
                    if ($request->check_pending == 'true' && $request->check_approved == 'true') {

                    } else if ($request->check_pending == 'true') {
                        $q->where('approve', '0');
                    } else if ($request->check_approved == 'true') {
                        $q->where('approve', '1');
                    }
                }

                if ($request->start_date) {
                    $date_start = $request->start_date;
                    $date_end = $request->end_date;

                    $date_start_explode = explode(" ", $date_start);
                    $date_start_date = @$date_start_explode[0];
                    // $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                    // dd($date_start_time);
                    $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                    // dd($date_start_date_format);
                    // $date_start_time_time = date("H:i", strtotime($date_start_time));
                    // $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                    // dd($date_start);

                    $date_end_explode = explode(" ", $date_end);
                    $date_end_date = @$date_end_explode[0];
                    // $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                    // dd($date_end_time);
                    $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                    // $date_end_time_time = date("H:i", strtotime($date_end_time));
                    // $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
                    // dd($date_end_time_time);

                    // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
                    $q->whereBetween('feedtimepost', array($date_start_date_format, $date_end_date_format));
                }

            });

            $model = $model->orwhere(function ($q) use ($request) {
                $q->where('keyword', '!=', null);
                $q->where('keyword', '!=', '');
                $q->where('feed_type', 'compromise');
                // $q->orwhere($orwhere);

                if ($request->search) {
                    $q->where('keyword', 'LIKE', '%' . $request->search . '%');
                }

                if ($request->source_select) {
                    $q->where('sourceid', $request->source_select);
                }
                if ($request->check_all == 'true') {

                } else {
                    if ($request->check_pending == 'true' && $request->check_approved == 'true') {

                    } else if ($request->check_pending == 'true') {
                        $q->where('approve', '0');
                    } else if ($request->check_approved == 'true') {
                        $q->where('approve', '1');
                    }
                }

                if ($request->start_date) {
                    $date_start = $request->start_date;
                    $date_end = $request->end_date;

                    $date_start_explode = explode(" ", $date_start);
                    $date_start_date = @$date_start_explode[0];
                    // $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                    // dd($date_start_time);
                    $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                    // dd($date_start_date_format);
                    // $date_start_time_time = date("H:i", strtotime($date_start_time));
                    // $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                    // dd($date_start);

                    $date_end_explode = explode(" ", $date_end);
                    $date_end_date = @$date_end_explode[0];
                    // $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                    // dd($date_end_time);
                    $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                    // $date_end_time_time = date("H:i", strtotime($date_end_time));
                    // $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
                    // dd($date_end_time_time);

                    // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
                    $q->whereBetween('feedtimepost', array($date_start_date_format, $date_end_date_format));
                }
            });
            $model = $model->get();
        } else {
            $model = DataLeakFeedTemp::
                where(function ($q) /*use ($where1,$orwhere)*/ {
                $q->where('keyword', '!=', null);
                $q->where('keyword', '!=', '');
                $q->where('feed_type', 'darkweb');
                // $q->orwhere($orwhere);
            });

            $model = $model->orwhere(function ($q) /*use ($where1,$orwhere)*/ {
                $q->where('keyword', '!=', null);
                $q->where('keyword', '!=', '');
                $q->where('feed_type', 'compromise');
                // $q->orwhere($orwhere);
            });
            $model = $model->get();

        }

        return DataTables::of($model)
            ->editColumn(
                'chk',
                function (DataLeakFeedTemp $model) {
                    return '<label><input type="checkbox" name="data_feed_id" class="data_feed_id val_id" value="' . $model->id . '"><span class="label-text"></span></label>';
                }
            )
            ->editColumn(
                'source',
                function (DataLeakFeedTemp $model) {
                    if ($model->source_name) {
                        return $model->source_name;
                    } else {
                        return '-';
                    }

                }
            )
            ->editColumn(
                'keyword',
                function (DataLeakFeedTemp $model) {
                    if ($model->keyword) {
                        return $model->keyword;
                    } else {
                        return '-';
                    }
                }
            )
            ->editColumn(
                'content',
                function (DataLeakFeedTemp $model) {
                    return '<div class="text-elip">' . $model->feedcontent . '</div>';
                }
            )
            ->editColumn(
                'data_feed',
                function (DataLeakFeedTemp $model) {
                    return $model->feedtimestamp;
                }
            )
            ->editColumn(
                'url',
                function (DataLeakFeedTemp $model) {
                    return '<a href="' . $model->feedlink . '" target="_blank"><i class="fas fa-link"></i></a>';
                }
            )
            ->editColumn(
                'action',
                function (DataLeakFeedTemp $model) {
                    $html = '';
                    if ($model->approve == 0) {
                        $html .= '<button class="btn btn-success btn-xs" data-toggle="modal" data-target="#confirm-change-status" onclick="approve_dataFeed(' . $model->id . ')">
                        Approve
                    </button>';

                        // $html .= '<a href="'.route('').'" class="btn btn-{{get_option("theme_color")}} btn-xs" data-toggle="ajaxModal"><i class="fas fa-share-square"></i></a>';

                    } else {
                        $html .= '<button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#confirm-change-status-cancle" onclick="cancle_dataFeed(' . $model->id . ')">
                        Cancel
                    </button>';
                    }
                    return $html;
                }
            )
            ->rawColumns(['chk', 'source', 'keyword', 'content', 'data_feed', 'url', 'action'])
            ->make(true);
    }

    public function change_status(Request $request)
    {

        $DataLeakSocialRef = DataLeakSocialRef::where('code', $request->code)->first();
        $DataLeakSocialRef->status = $request->status;
        $DataLeakSocialRef->save();

        $site_code = $this->siteSettings->find_code($DataLeakSocialRef->site_id);
        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('socialdatas.index', ['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function darkweb_data_change_status(Request $request)
    {

        // $DataLeakSocialRef = DataLeakSocialRef::where('code', $request -> code)->first();
        // $DataLeakSocialRef->status = $request->status;
        // $DataLeakSocialRef->save();

        $DataLeakFeed = DataLeakSocialRef::where('id', $request->id)->first();
        $DataLeakFeed->status = $request->active;
        $DataLeakFeed->save();

        // $site_code = $this->siteSettings->find_code($DataLeakSocialRef->site_id);
        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                // 'redirect' => route('socialdatas.index',['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function delete_socialdatas(Request $request)
    {
        $DataLeakSocialRef = DataLeakSocialRef::where('code', $request->code)->first();
        $data['DataLeakSocialRef'] = $DataLeakSocialRef;
        return view('sitesettings::modal.delete_socialdatas')->with($data);
    }

    public function delete_socialdata(Request $request)
    {
        $DataLeakSocialRef = DataLeakSocialRef::where('code', $request->code)->first();
        $DataLeakFeedTemps = DataLeakFeedTemp::whereIn('id', $DataLeakSocialRef->temp_id)->get();
        DataLeakFeed::where('id', $DataLeakSocialRef->data_leak_feed_id)->delete();
        DataLeakSocialRef::where('temp_id', $DataLeakSocialRef->temp_id)->delete();
        $DataLeakFeedTemps->approve = 0;
        $DataLeakFeedTemps->save();
        $site_code = $this->siteSettings->find_code($DataLeakSocialRef->site_id);
        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('socialdatas.index', ['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function approve_data_feed(Request $request)
    {

        // dd($request->sent_mail);
        $sent_mail = $request->sent_mail;
        $site_id = 0;
        $DataLeakFeed_send_mail = [];
        $DataLeakFeedTemps = DataLeakFeedTemp::whereIn('id', $request->id)->get();
        foreach ($DataLeakFeedTemps as $DataLeakFeedTemp) {
            $check_DataLeakFeed = DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->first();
            if (empty($check_DataLeakFeed)) {
                $DataLeakFeed = new DataLeakFeed();
                $DataLeakFeed->code = generator_uuid();
                $DataLeakFeed->temp_id = $DataLeakFeedTemp->id;
                $DataLeakFeed->data_id = $DataLeakFeedTemp->data_id;
                $DataLeakFeed->sourceid = $DataLeakFeedTemp->sourceid;
                $DataLeakFeed->keyword = $DataLeakFeedTemp->keyword;
                $DataLeakFeed->source_name = $DataLeakFeedTemp->source_name;
                $DataLeakFeed->feedcontent = $DataLeakFeedTemp->feedcontent;
                $DataLeakFeed->feedlink = $DataLeakFeedTemp->feedlink;
                $DataLeakFeed->feedtimepost = $DataLeakFeedTemp->feedtimepost;
                $DataLeakFeed->feedtimestamp = $DataLeakFeedTemp->feedtimestamp;
                $DataLeakFeed->feeduser = $DataLeakFeedTemp->feeduser;
                $DataLeakFeed->tag = $DataLeakFeedTemp->tag;
                $DataLeakFeed->feel_type = $DataLeakFeedTemp->feed_type;
                $DataLeakFeed->status = 1;
                $DataLeakFeed->save();

                $DataLeakFeed_send_mail[] = $DataLeakFeed;

                $leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $DataLeakFeedTemp->id)->first();
                if (!empty($leak_socail_ref_temp)) {
                    $DataLeakSocialRef = new DataLeakSocialRef;
                    $DataLeakSocialRef->code = generator_uuid();
                    $DataLeakSocialRef->temp_id = $DataLeakFeedTemp->id;
                    $DataLeakSocialRef->data_leak_feed_id = $DataLeakFeed->id;
                    $DataLeakSocialRef->site_id = $leak_socail_ref_temp->site_id;
                    $DataLeakSocialRef->keyword = $leak_socail_ref_temp->keyword;
                    $DataLeakSocialRef->feel_type = $DataLeakFeedTemp->feed_type;
                    $DataLeakSocialRef->status = 1;
                    $DataLeakSocialRef->view = 0;
                    $DataLeakSocialRef->save();

                    if ($site_id == 0) {
                        $site_id = $leak_socail_ref_temp->site_id;
                    }
                }

                $DataLeakFeedTemp->approve = 1;
                $DataLeakFeedTemp->save();
            }
        }

        if ($this->request->sent_mail == 1) {
            $site_email_alert = site_config_email_alert::where("site_id", $site_id)->get();
            if ($site_email_alert) {
                foreach ($site_email_alert as $site_email_alert_val) {
                    Mail::to($site_email_alert_val->email)->send(new CompromisedMail($DataLeakFeed_send_mail, 'data_leak'));
                }
            }
        }

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('datafeed.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function cancle_data_feed(Request $request)
    {
        $DataLeakFeedTemps = DataLeakFeedTemp::whereIn('id', $request->id)->get();
        foreach ($DataLeakFeedTemps as $DataLeakFeedTemp) {
            DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->delete();
            DataLeakSocialRef::where('temp_id', $DataLeakFeedTemp->id)->delete();
            $DataLeakFeedTemp->approve = 0;
            $DataLeakFeedTemp->save();
        }
        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('datafeed.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function compromised_feed_datatables(Request $request)
    {

        $where1 = ['deleted_at' => null, 'feel_type' => 'darkweb'];
        $where = ['deleted_at' => null];
        $orwhere = ['deleted_at' => null, 'feel_type' => 'compromise'];

        if ($request->search_val == 1) {
            // $model = DataLeakFeedTemp::where('keyword', '!=' , null)->where('keyword', '!=' , '')->where('feed_type','social');

            // $model = DataLeakFeedTemp::
            //     where(function($q) /*use ($where1,$orwhere)*/ {
            //         $q->where('keyword', '!=' , null);
            //         $q->where('keyword', '!=' , '');
            //         $q->where('feed_type','darkweb');
            //         // $q->orwhere($orwhere);
            //     });

            // $model = $model->orwhere(function($q) /*use ($where1,$orwhere)*/ {
            //     $q->where('keyword', '!=' , null);
            //     $q->where('keyword', '!=' , '');
            //     $q->where('feed_type','compromise');
            //     // $q->orwhere($orwhere);
            // });

            // dd($request -> check_pending);

            $site_id = $request->site_id;
            $model = DataLeakFeedTemp::
                where(function ($q) use ($site_id, $request) {

                $q->where('keyword', '!=', null);
                $q->where('keyword', '!=', '');
                $q->where('feed_type', 'darkweb');
                // $q->orwhere($orwhere);
                $q->wherehas('get_socail_ref_temp', function ($a) use ($site_id) {
                    $a->where('site_id', $site_id)->where('deleted_at', null);
                });

                // $q->orwhere($orwhere);

                if ($request->search) {
                    $q->where('keyword', 'LIKE', '%' . $request->search . '%');
                }

                if ($request->source_select) {
                    $q->where('sourceid', $request->source_select);
                }
                if ($request->check_all == 'true') {

                } else {
                    if ($request->check_pending == 'true' && $request->check_approved == 'true') {

                    } else if ($request->check_pending == 'true') {
                        $q->where('approve', '0');
                    } else if ($request->check_approved == 'true') {
                        $q->where('approve', '1');
                    }
                }

                if ($request->start_date) {
                    $date_start = $request->start_date;
                    $date_end = $request->end_date;

                    $date_start_explode = explode(" ", $date_start);
                    $date_start_date = @$date_start_explode[0];
                    // $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                    // dd($date_start_time);
                    $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                    // dd($date_start_date_format);
                    // $date_start_time_time = date("H:i", strtotime($date_start_time));
                    // $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                    // dd($date_start);

                    $date_end_explode = explode(" ", $date_end);
                    $date_end_date = @$date_end_explode[0];
                    // $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                    // dd($date_end_time);
                    $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                    // $date_end_time_time = date("H:i", strtotime($date_end_time));
                    // $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
                    // dd($date_end_time_time);

                    // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
                    $q->whereBetween('feedtimepost', array($date_start_date_format, $date_end_date_format));
                }

            });

            $model = $model->orwhere(function ($q) use ($site_id, $request) /*use ($where1,$orwhere)*/ {
                $q->where('keyword', '!=', null);
                $q->where('keyword', '!=', '');
                $q->where('feed_type', 'compromise');
                // $q->orwhere($orwhere);
                $q->wherehas('get_socail_ref_temp', function ($q) use ($site_id) {
                    $q->where('site_id', $site_id)->where('deleted_at', null);
                });
                // $q->orwhere($orwhere);

                if ($request->search) {
                    $q->where('keyword', 'LIKE', '%' . $request->search . '%');
                }

                if ($request->source_select) {
                    $q->where('sourceid', $request->source_select);
                }
                if ($request->check_all == 'true') {

                } else {
                    if ($request->check_pending == 'true' && $request->check_approved == 'true') {

                    } else if ($request->check_pending == 'true') {
                        $q->where('approve', '0');
                    } else if ($request->check_approved == 'true') {
                        $q->where('approve', '1');
                    }
                }

                if ($request->start_date) {
                    $date_start = $request->start_date;
                    $date_end = $request->end_date;

                    $date_start_explode = explode(" ", $date_start);
                    $date_start_date = @$date_start_explode[0];
                    // $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                    // dd($date_start_time);
                    $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                    // dd($date_start_date_format);
                    // $date_start_time_time = date("H:i", strtotime($date_start_time));
                    // $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                    // dd($date_start);

                    $date_end_explode = explode(" ", $date_end);
                    $date_end_date = @$date_end_explode[0];
                    // $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                    // dd($date_end_time);
                    $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                    // $date_end_time_time = date("H:i", strtotime($date_end_time));
                    // $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
                    // dd($date_end_time_time);

                    // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
                    $q->whereBetween('feedtimepost', array($date_start_date_format, $date_end_date_format));
                }
            });

            $model = $model->get();
        } else {

            $site_id = $request->site_id;
            $model = DataLeakFeedTemp::
                where(function ($q) use ($site_id) {

                $q->where('keyword', '!=', null);
                $q->where('keyword', '!=', '');
                $q->where('feed_type', 'darkweb');
                // $q->orwhere($orwhere);
                $q->wherehas('get_socail_ref_temp', function ($a) use ($site_id) {
                    $a->where('site_id', $site_id)->where('deleted_at', null);
                });
            });

            $model = $model->orwhere(function ($q) use ($site_id) /*use ($where1,$orwhere)*/ {
                $q->where('keyword', '!=', null);
                $q->where('keyword', '!=', '');
                $q->where('feed_type', 'compromise');
                // $q->orwhere($orwhere);
                $q->wherehas('get_socail_ref_temp', function ($q) use ($site_id) {
                    $q->where('site_id', $site_id)->where('deleted_at', null);
                });
            });
            $model = $model->get();

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
                    if ($model->source_name) {
                        return $model->source_name;
                    } else {
                        return '-';
                    }

                }
            )
            ->editColumn(
                'keyword',
                function (DataLeakFeedTemp $model) {
                    if ($model->keyword) {
                        return $model->keyword;
                    } else {
                        return '-';
                    }
                }
            )
            ->editColumn(
                'content',
                function (DataLeakFeedTemp $model) {
                    return '<div class="text-elip">' . $model->feedcontent . '</div>';
                }
            )
            ->editColumn(
                'data_feed',
                function (DataLeakFeedTemp $model) {
                    return $model->feedtimestamp;
                }
            )
            ->editColumn(
                'url',
                function (DataLeakFeedTemp $model) {
                    return '<a href="' . $model->feedlink . '" target="_blank"><i class="fas fa-link"></i></a>';
                }
            )
            ->editColumn(
                'action',
                function (DataLeakFeedTemp $model) {
                    $html = '';
                    if ($model->approve == 0) {
                        $html .= '<button class="btn btn-success btn-xs" data-toggle="modal" data-target="#confirm-change-status" onclick="approve_dataFeed(' . $model->id . ')">
                        Approve
                    </button>';

                        // $html .= '<a href="'.route('').'" class="btn btn-{{get_option("theme_color")}} btn-xs" data-toggle="ajaxModal"><i class="fas fa-share-square"></i></a>';

                    } else {
                        $html .= '<button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#confirm-change-status-cancle" onclick="cancle_dataFeed(' . $model->id . ')">
                        Cancel
                    </button>';
                    }
                    return $html;
                }
            )
            ->rawColumns(['chk', 'source', 'keyword', 'content', 'data_feed', 'url', 'action'])
            ->make(true);
    }

    public function compromised_feed_approve_data_feed(Request $request)
    {

        // dd($request->sent_mail);
        $sent_mail = $request->sent_mail;

        $DataLeakFeedTemps = DataLeakFeedTemp::whereIn('id', $request->id)->get();
        foreach ($DataLeakFeedTemps as $DataLeakFeedTemp) {
            $check_DataLeakFeed = DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->first();
            if (empty($check_DataLeakFeed)) {
                $DataLeakFeed = new DataLeakFeed();
                $DataLeakFeed->code = generator_uuid();
                $DataLeakFeed->temp_id = $DataLeakFeedTemp->id;
                $DataLeakFeed->data_id = $DataLeakFeedTemp->data_id;
                $DataLeakFeed->sourceid = $DataLeakFeedTemp->sourceid;
                $DataLeakFeed->keyword = $DataLeakFeedTemp->keyword;
                $DataLeakFeed->source_name = $DataLeakFeedTemp->source_name;
                $DataLeakFeed->feedcontent = $DataLeakFeedTemp->feedcontent;
                $DataLeakFeed->feedlink = $DataLeakFeedTemp->feedlink;
                $DataLeakFeed->feedtimepost = $DataLeakFeedTemp->feedtimepost;
                $DataLeakFeed->feedtimestamp = $DataLeakFeedTemp->feedtimestamp;
                $DataLeakFeed->feeduser = $DataLeakFeedTemp->feeduser;
                $DataLeakFeed->tag = $DataLeakFeedTemp->tag;
                $DataLeakFeed->status = 1;
                $DataLeakFeed->save();

                $leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $DataLeakFeedTemp->id)->first();
                if (!empty($leak_socail_ref_temp)) {
                    $DataLeakSocialRef = new DataLeakSocialRef;
                    $DataLeakSocialRef->code = generator_uuid();
                    $DataLeakSocialRef->temp_id = $DataLeakFeedTemp->id;
                    $DataLeakSocialRef->data_leak_feed_id = $DataLeakFeed->id;
                    $DataLeakSocialRef->site_id = $leak_socail_ref_temp->site_id;
                    $DataLeakSocialRef->keyword = $leak_socail_ref_temp->keyword;
                    $DataLeakSocialRef->status = 1;
                    $DataLeakSocialRef->view = 0;
                    $DataLeakSocialRef->save();
                }

                $DataLeakFeedTemp->approve = 1;
                $DataLeakFeedTemp->save();
            }
        }

        $code_site = SiteSettings::where("id", '=', $request->site_id)->first();

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('compromised_feed.index', ['code' => $code_site->code]),
            ],
            true,
            Response::HTTP_OK
        );

    }

    public function compromised_feed_cancle_data_feed(Request $request)
    {
        $DataLeakFeedTemps = DataLeakFeedTemp::whereIn('id', $request->id)->get();
        foreach ($DataLeakFeedTemps as $DataLeakFeedTemp) {
            DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->delete();
            DataLeakSocialRef::where('temp_id', $DataLeakFeedTemp->id)->delete();
            $DataLeakFeedTemp->approve = 0;
            $DataLeakFeedTemp->save();
        }

        $code_site = SiteSettings::where("id", '=', $request->site_id)->first();
        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('compromised_feed.index', ['code' => $code_site->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function compromised_feed_darkweb_all_site_tb(Request $request)
    {

        $where1 = ['deleted_at' => null, 'feel_type' => 'darkweb'];
        $where = ['deleted_at' => null];
        $orwhere = ['deleted_at' => null, 'feel_type' => 'compromise'];

        if ($request->search_val == 'true') {

            // $model = DataLeakFeed::where($where);
            $model = DataLeakSocialRef::where('deleted_at', null)->where('site_id', $request->site_id)->with('get_site')->with('get_data_leak_feed_one');

            // if($request -> keywords){
            //     $model = $model->where('source_name', 'LIKE', '%'.$request -> keywords.'%');
            // }

            if ($request->keywords) {

                // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
                $model = $model->where('keyword', 'LIKE', '%' . $request->keywords . '%');
                // });

            }

            if ($request->source) {
                $model = $model->whereHas('get_data_leak_feed_one', function ($qq) use ($request) {
                    $qq->where('sourceid', $request->source);
                });
            }

            if ($request->site) {
                // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
                $model = $model->where('site_id', $request->site);
                // });
            }

            if ($request->startDate) {
                $date_start = $request->startDate;
                $date_end = $request->endDate;

                $date_start_explode = explode(" ", $date_start);
                $date_start_date = @$date_start_explode[0];
                $date_start_time = @$date_start_explode[1] . ' ' . @$date_start_explode[2];
                // dd($date_start_time);
                $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                // dd($date_start_date_format);
                $date_start_time_time = date("H:i", strtotime($date_start_time));
                $date_start_datetime_format = $date_start_date_format . ' ' . $date_start_time_time . ':00';
                // dd($date_start);

                $date_end_explode = explode(" ", $date_end);
                $date_end_date = @$date_end_explode[0];
                $date_end_time = @$date_end_explode[1] . ' ' . @$date_end_explode[2];
                // dd($date_end_time);
                $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                $date_end_time_time = date("H:i", strtotime($date_end_time));
                $date_end_datetime_format = $date_end_date_format . ' ' . $date_end_time_time . ':00';
                // dd($date_end_time_time);

                // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
                $model = $model->whereHas('get_data_leak_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
                    $qq->whereBetween('feedtimepost', array($date_start_datetime_format, $date_end_datetime_format));
                });
            }

            $model->whereHas('get_data_leak_feed_one', function ($q) use ($where1, $orwhere) {
                $q->where($where1);
                $q->orwhere($orwhere);
            });

            $model->orderBy('id', 'desc');
        } else {
            // $model = DataLeakFeed::where($where)
            //                     ->orwhere(function ($q) use ($orwhere) {
            //                         $q->where($orwhere);
            //                     })->orderBy('id', 'desc')->with('get_social_ref');

            $model = DataLeakSocialRef::where('deleted_at', null)->where('site_id', $request->site_id)
                ->whereHas('get_data_leak_feed_one', function ($q) use ($where1, $orwhere) {
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

    public function compromised_feed_change_status(Request $request)
    {
        // dd($request->id);

        // $DataLeakSocialRef = DataLeakSocialRef::where('code', $request -> code)->first();
        // $DataLeakSocialRef->status = $request->status;
        // $DataLeakSocialRef->save();

        $DataLeakFeed = DataLeakSocialRef::where('id', $request->id)->first();
        $DataLeakFeed->status = $request->active;
        $DataLeakFeed->save();

        // $site_code = $this->siteSettings->find_code($DataLeakSocialRef->site_id);
        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                // 'redirect' => route('socialdatas.index',['id' => $site_code->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function compromised_feed_delete_select(Request $request)
    {
        $code_site = SiteSettings::where("id", '=', $request->site_id)->first();

        foreach ($request->id as $val_id) {
            $data = DataLeakSocialRef::where('id', $val_id)->delete();
        }

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('compromised_data.index', ['code' => $code_site->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function delete_compromised_feed_modal($code)
    {

        $DataLeakSocialRef = DataLeakSocialRef::where('code', $code)->first();
        $data["DataLeakSocialRef"] = $DataLeakSocialRef;
        return view('sitesettings::modal.delete_compromised_feed_data')->with($data);
    }

    public function delete_compromised_feed_process($code)
    {

        $data = DataLeakSocialRef::where('code', $code)->first();
        $data = $data->site_id;
        $code_site = SiteSettings::where("id", '=', $data)->first();

        // dd($code);
        DataLeakSocialRef::where('code', $code)->delete();

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('compromised_data.index', ['code' => $code_site->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function compromised_web_server($code)
    {
        $get_data = $this->siteSettings->get_data($code);
        $data['siteSettings'] = $get_data;
        $siteID = siteSettings::where('code', $code)->first();

        $DataLeakSocial = DataLeakSocial::where('deleted_at', null)->where('status', 1)->get();
        $data['DataLeakSocial'] = $DataLeakSocial;
        $data['siteID'] = $siteID->id;
        $data['page'] = 'Web Server';

        $data['Credentials'] = Credentials::all();

        return view('sitesettings::compromised_web_server')->with($data);
    }

    public function table_web_server(Request $request)
    {

        $model = CompromisedServer::where('site_id', $request->site)->where('deleted_at', null)->with('get_site');
        // if ($request->search_val == 1) {

        //     if ($request->keywords) {
        //         $keywords = $request->keywords;
        //         $model->whereHas('get_data_leak_feed_one', function ($query) use ($keywords) {
        //             $query->where('keyword', 'LIKE', '%' . $keywords . '%')
        //                 ->orWhere('feedcontent', 'LIKE', '%' . $keywords . '%');
        //         });
        //     }

        //     if ($request->site) {

        //         $model->where('site_id', $request->site);

        //     }

        //     if ($request->source) {

        //         $source = $request->source;
        //         $model->whereHas('get_data_leak_feed_one', function ($query) use ($source) {
        //             $query->where('sourceid', 'LIKE', '%' . $source . '%');
        //         });

        //     }

        //     if ($request->isDateSearch == 1) {
        //         $date_start = $request->startDate;
        //         $date_end = $request->endDate;

        //         $date_start_explode = explode(" ", $date_start);
        //         $date_start_date = @$date_start_explode[0];
        //         $date_start_time = @$date_start_explode[1] . ' ' . @$date_start_explode[2];

        //         $date_start_date_format = date("Y-m-d", strtotime($date_start_date));

        //         $date_start_time_time = date("H:i", strtotime($date_start_time));
        //         $date_start_datetime_format = $date_start_date_format . ' ' . $date_start_time_time . ':00';

        //         $date_end_explode = explode(" ", $date_end);
        //         $date_end_date = @$date_end_explode[0];
        //         $date_end_time = @$date_end_explode[1] . ' ' . @$date_end_explode[2];
        //         // dd($date_end_time);
        //         $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
        //         $date_end_time_time = date("H:i", strtotime($date_end_time));
        //         $date_end_datetime_format = $date_end_date_format . ' ' . $date_end_time_time . ':00';

        //         $source = $request->source;
        //         $model->whereHas('get_data_leak_feed_one', function ($query) use ($date_start_date_format, $date_end_date_format) {
        //             $query->whereBetween('feedtimepost', array($date_start_date_format, $date_end_date_format));
        //         });

        //     }

        // }
        $model->get();

        return DataTables::of($model)->toJson();

        // $model = DataLeakSocialRef::where('deleted_at', null)->orderBy('id', 'desc');
        // $model->whereHas('get_data_leak_feed', function ($query){
        //     $query->where('site_id', );

    }

    public function web_server_delete(Request $request)
    {

        // dd($request->id);
        if ($request->id_chang) {

            foreach ($request->id_chang as $id_chang) {

                $data = CompromisedServer::where("id", $id_chang)->delete();

            }
        } else {

            CompromisedServer::where("id", $request->id)->delete();

        }

        $code_site = SiteSettings::where("id", '=', $request->site)->first();

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('compromised_web_server.index', ['code' => $code_site->code]),
            ],
            true,
            Response::HTTP_OK
        );

    }

    public function web_server_change_status(Request $request)
    {
        // dd($request->id);
        if ($request->id_chang) {
            foreach ($request->id_chang as $id_chang) {
                $data = CompromisedServer::where("id", $id_chang)->first();
                if ($data->active == 1) {
                    $data->active = 0;
                } else {
                    $data->active = 1;
                }
                $data->save();
            }
        } else {
            $data = CompromisedServer::where("id", $request->id)->first();
            if ($data->active == 1) {
                $data->active = 0;
            } else {
                $data->active = 1;
            }
            $data->save();
        }

        $code_site = SiteSettings::where("id", '=', $request->site)->first();

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('compromised_web_server.index', ['code' => $code_site->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function web_server_create(Request $request)
    {

        // $myArray = explode(',', $request->type);
        // $myArray1 = implode(',', $myArray);
        // dd($request->user);
        $Credentials = Credentials::where('id', $request->user)->first();
        $checkCompromisedServer = CompromisedServer::where('site_id', $request->site)
            ->where('ip', $request->ip)
            ->where('port', $request->port)
            ->where('user', $Credentials->user)
            ->where('path', $request->root_path)
            ->first();

        if (!$checkCompromisedServer) {

            $data = new CompromisedServer;
            $data->code = generator_uuid();
            $data->site_id = $request->site;
            $data->ip = $request->ip;
            $data->port = $request->port;
            $data->user = $Credentials->user;
            $data->password = $Credentials->password;
            $data->path = $request->root_path;
            $data->os = $request->os;
            $data->active = $request->check;
            $data->file_extension = $request->type;
            $data->credentials_id = $Credentials->id;;

            $data->save();

            $Assets = Assets::where('raw_data', $request->ip)->first();
            $Domain = Domain::where('site_id', $request->site)->where('domain_default', 1)->first();

            if ($Assets) {
                // dd($Assets);
                $Assets->site_id = $request->site;
                $Assets->port = $request->port;
                $Assets->user = $Credentials->user;
                $Assets->password = $Credentials->password;
                $Assets->os = $request->os;
                $Assets->status = $request->check;
                $Assets->created_by = @Auth::user()->id;
                $Assets->domain_id = $Domain->id;

                $Assets->save();

            } else {
                $Assets_new = new Assets;
                $Assets_new->code = generator_uuid();
                $Assets_new->site_id = $request->site;
                $Assets_new->raw_data = $request->ip;
                $Assets_new->port = $request->port;
                $Assets_new->user = $Credentials->user;
                $Assets_new->password = $Credentials->password;
                $Assets_new->os = $request->os;
                $Assets_new->status = $request->check;
                $Assets_new->created_by = @Auth::user()->id;
                $Assets_new->domain_id = $Domain->id;
                $Assets_new->domain_id = $Domain->id;

                $Assets_new->save();

                $AssetsData = new AssetsData;
                $AssetsData->code = generator_uuid();
                $AssetsData->site_id = $request->site;
                $AssetsData->value = $request->ip;
                $AssetsData->status = $request->check;
                $AssetsData->created_by = @Auth::user()->id;
                $AssetsData->domain_id = $Domain->id;
                $AssetsData->data_type_id = 5;
                $AssetsData->asset_id = $Assets_new->id;

                $AssetsData->save();

            }
            $message = langapp('changes_saved_successful');

        } else {

            $message = '';

        }

        $code_site = SiteSettings::where("id", '=', $request->site)->first();

        return ajaxResponse(
            [
                'message' => $message,
                'redirect' => route('compromised_web_server.index', ['code' => $code_site->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function web_server_edit_modal($code)
    {


        $data["CompromisedServer"] = CompromisedServer::where('code', $code)->first();
        $data["type"] = explode(',', $data["CompromisedServer"]->file_extension);

        return view('sitesettings::modal.update_compromised_web_server')->with($data);
    }

    public function web_server_edit(Request $request, $id)
    {

        $data = CompromisedServer::where('id', $id)->first();

        $data->ip = $request->ip;
        $data->port = $request->port;
        $data->user = $request->user;
        $data->password = $request->password;
        $data->path = $request->root_path;
        $data->os = $request->os;
        $data->active = $request->status;
        $data->file_extension = $request->type;

        $data->save();

        $code_site = SiteSettings::where("id", '=', $request->site)->first();

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('compromised_web_server.index', ['code' => $code_site->code]),
            ],
            true,
            Response::HTTP_OK
        );

    }

    protected function checkWebserverIP(Request $request)
    {
      
        $data = Credentials::where('id', $request->user)->first();
        $ip = @$request->ip;
        $port = @$request->port;
        $user = $data->user;
        $pass = $data->password;
        $os = @$request->os;
        $checkConnect = null;
        $message = '';
        try {
            if ($os == "Linux") {
                $ssh = new SSH2($ip, $port);
                $ssh->setTimeout(60);

                if (!$ssh->login($user, $pass)) {
                    $checkConnect = false;
                    $message = 'Connect Error';
                } else {
                    $checkConnect = true;
                    $message = 'Connect Success';
                }
            } else if ($os == "Windows") {
                $checkConnect = false;
                $message = 'Connect Error';
            } else {
                $checkConnect = false;
                $message = 'Connect Error';
            }
        } catch (Exception $e) {
            $checkConnect = false;
            $message = $e->getMessage();
        }

        $dataout = [
            'webserverConnect' => $checkConnect,
            'message' => $message,
        ];
        return response()->json($dataout);
    }

    public function web_server_add_user(Request $request)
    {

        $data_search = Credentials::where("name", $request->name)->first();
        if (!$data_search) {
            $data = new Credentials;
            $data->code = generator_uuid();
            $data->site_id = $request->site;
            $data->name = $request->name;
            $data->user = $request->user;
            $data->password = $request->password;
            $data->status = 1;
            $data->save();
            $message = langapp('changes_saved_successful');
            return ajaxResponse(
                [
                    'message' => $message,
                    'id' => $data->id,
                    'name' => $data->name,

                ],
                true,
                Response::HTTP_OK
            );
        } else {

            $message = '';
            return ajaxResponse(
                [
                    'message' => $message,
                ],
                true,
                Response::HTTP_OK
            );
        }

        $code_site = SiteSettings::where("id", '=', $request->site)->first();

    }

}
