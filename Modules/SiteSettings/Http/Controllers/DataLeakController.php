<?php

namespace Modules\SiteSettings\Http\Controllers;

use App\Credentials;
use App\DataLeakFeed;
use App\DataLeakFeedTemp;
use App\DataLeakSocial;
use App\DataLeakSocialRef;
use App\DataLeakSocialRefTemp;
use App\Entities\CompromisedServer;
use App\leak_socail_ref_temp;
use App\LogEmail;
use App\Mail\CompromisedMail;
use App\transaction_client_asset;
use App\transaction_client_asset_data;
use App\transaction_client_compromised_server;
use App\transaction_client_leak_feed;
use App\transaction_client_leak_social_ref;
use App\transcation_jobs_clients;
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
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\SiteSettings\Entities\Activity;
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
        // if (Auth::check()) {
        //     $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
        //     if (Auth::user()->hasRole('admin')) { //if admin
        //         // dd(777);
        //         $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)->get();

        //     } else { //if notAdmin
        //         // dd(888);
        //         if (@Auth::user()->site_role_id && @Auth::user()->site_id) {
        //             if (@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) { //support and admin
        //                 // dd(99);

        //                 $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)
        //                     ->whereIn('id', $site_id_arr) //['49', '56']
        //                     ->get();

        //             } else { //not support and admin
        //                 $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)
        //                     ->whereIn('id', $site_id_arr) //['49', '56']
        //                     ->get();
        //             }
        //         }
        //     }
        // }

        $SiteSettings = @get_role_custom()['SiteSettings'];
        $site_id_arr = @get_role_custom()['site_id_arr'];
        if(@get_role_custom()['superadmin'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }else if(@get_role_custom()['client'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }else if(@get_role_custom()['site_support'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }else if(@get_role_custom()['site_admin'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }else if(@get_role_custom()['site_client'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }
        // $data['SiteSettings'] = SiteSettings::where("active", '=', 1)->where('deleted_at', null)->get();
        $data['SiteSettings'] = $SiteSettings;

        $DataLeakSocial = DataLeakSocial::where('deleted_at', null)->where('status', 1)->get();
        $data['site_settings'] = SiteSettings::where('deleted_at', null)->where('active', 1)->get();
        $data['DataLeakSocial'] = $DataLeakSocial;
        $data['page'] = langapp('data_leak_feed');
        return view('sitesettings::datafeed')->with($data);
    }

    public function socialdatas($id)
    {
        $get_data = $this->siteSettings->get_data($id);
      
        $data['siteSettings'] = $get_data;
        $data['page'] = 'Data Leak Data';
        $data['source'] = DataLeakSocial::where("status", '=', 1)->where('deleted_at', null)->get();
        return view('sitesettings::social-datas')->with($data);
    }

    public function datafeed_darkweb()
    {
        // if (Auth::check()) {
        //     $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
        //     if (Auth::user()->hasRole('admin')) { //if admin
        //         // dd(777);
        //         $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)->get();

        //     } else { //if notAdmin
        //         // dd(888);
        //         if (@Auth::user()->site_role_id && @Auth::user()->site_id) {
        //             if (@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) { //support and admin
        //                 // dd(99);

        //                 $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)
        //                     ->whereIn('id', $site_id_arr) //['49', '56']
        //                     ->get();

        //             } else { //not support and admin
        //                 $SiteSettings = SiteSettings::where("active", 1)->where("deleted_at", null)
        //                     ->whereIn('id', $site_id_arr) //['49', '56']
        //                     ->get();
        //             }
        //         }
        //     }
        // }

        $SiteSettings = @get_role_custom()['SiteSettings'];
        $site_id_arr = @get_role_custom()['site_id_arr'];
        if(@get_role_custom()['superadmin'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }else if(@get_role_custom()['client'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }else if(@get_role_custom()['site_support'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }else if(@get_role_custom()['site_admin'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }else if(@get_role_custom()['site_client'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }
        
        // $data['SiteSettings'] = SiteSettings::where("active", '=', 1)->where('deleted_at', null)->get();
        $data['SiteSettings'] = $SiteSettings;

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

                        // $DataLeakFeed_send_mail[] = $DataLeakFeed;

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

                            if(!isset($DataLeakFeed_send_mail[(string)$leak_socail_ref_temp->site_id])){
                                $DataLeakFeed_send_mail[(string)$leak_socail_ref_temp->site_id] = [];
                            }
                            array_push($DataLeakFeed_send_mail[(string)$leak_socail_ref_temp->site_id], $DataLeakFeed);

                            $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakFeed->id)->first();
                            if($transaction_client_leak_feed){
                                $transaction_client_leak_feed -> transaction_mode = 'insert';
                                $transaction_client_leak_feed -> transaction_data_status = 1;
                                $transaction_client_leak_feed -> status = 1;
                                $transaction_client_leak_feed -> save();
                            }else{
                                $transaction_client_leak_feed = new transaction_client_leak_feed();
                                $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                                $transaction_client_leak_feed -> transaction_id = $DataLeakFeed->id;
                                $transaction_client_leak_feed -> transaction_mode = 'insert';
                                $transaction_client_leak_feed -> transaction_data_status = 1;
                                $transaction_client_leak_feed -> status = 1;
                                $transaction_client_leak_feed -> save();
                            }
            
                            
                            $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakSocialRef->id)->first();
                            if($transaction_client_leak_social_ref){
                                $transaction_client_leak_social_ref -> transaction_mode = 'insert';
                                $transaction_client_leak_social_ref -> transaction_data_status = 1;
                                $transaction_client_leak_social_ref -> status = 1;
                                $transaction_client_leak_social_ref -> save();
                            }else{
                                $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                                $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                                $transaction_client_leak_social_ref -> transaction_id = $DataLeakSocialRef->id;
                                $transaction_client_leak_social_ref -> transaction_mode = 'insert';
                                $transaction_client_leak_social_ref -> transaction_data_status = 1;
                                $transaction_client_leak_social_ref -> status = 1;
                                $transaction_client_leak_social_ref -> save();
                            }
                        }

                        $DataLeakFeedTemp->approve = 1;
                        $DataLeakFeedTemp->save();
                    }
                }
            }
            // $DataLeakFeed_send_mail[] = $DataLeakFeedTemp;
            if ($this->request->sent_mail == 1) {
                foreach ($DataLeakFeed_send_mail as $key => $value) {
                    $site_email_alert = site_config_email_alert::where("site_id", $key)->get();
                    if ($site_email_alert) {
                        $email_site_a = [];
                        foreach ($site_email_alert as $site_email_alert_val) {
                            $email_site_a[] = $site_email_alert_val->email;
                        }
                        $email_site_alert = array_unique($email_site_a);
                        foreach($email_site_alert as $email){
                            Mail::to($email)->send(new CompromisedMail($value, 'compromised'));
                            if( count(Mail::failures()) == 0 ) {
                                LogEmail::Create([
                                    'to' => $email,
                                    'status' => 'Success',
                                    'subject' => 'compromised'
                                ]);
                            }
                        }
                        if( count(Mail::failures()) > 0 ) {
                            foreach(Mail::failures() as $email_address) {
                                LogEmail::Create([
                                    'to' => $email_address,
                                    'status' => 'Fail',
                                    'subject' => 'compromised'
                                ]);
                            }
                        }
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
                    if($DataLeakFeedTemp){
                        $DataLeakFeeds = DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->get();
                        if($DataLeakFeeds){
                            foreach($DataLeakFeeds as $DataLeakFeed){
                                $leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $DataLeakFeedTemp->id)->first();
                                $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakFeed->id)->first();
                                if($transaction_client_leak_feed){
                                    $transaction_client_leak_feed -> transaction_mode = 'delete';
                                    $transaction_client_leak_feed -> transaction_data_status = 1;
                                    $transaction_client_leak_feed -> status = 1;
                                    $transaction_client_leak_feed -> save();
                                }else{
                                    $transaction_client_leak_feed = new transaction_client_leak_feed();
                                    $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                                    $transaction_client_leak_feed -> transaction_id = $DataLeakFeed->id;
                                    $transaction_client_leak_feed -> transaction_mode = 'delete';
                                    $transaction_client_leak_feed -> transaction_data_status = 1;
                                    $transaction_client_leak_feed -> status = 1;
                                    $transaction_client_leak_feed -> save();
                                }
                            }
                            $DataLeakSocialRefs = DataLeakSocialRef::where('temp_id', $DataLeakFeedTemp->id)->get();
                            foreach($DataLeakSocialRefs as $DataLeakSocialRef){
                                $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakSocialRef->id)->first();
                                if($transaction_client_leak_social_ref){
                                    $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                                    $transaction_client_leak_social_ref -> transaction_data_status = 1;
                                    $transaction_client_leak_social_ref -> status = 1;
                                    $transaction_client_leak_social_ref -> save();
                                }else{
                                    $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                                    $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                                    $transaction_client_leak_social_ref -> transaction_id = $DataLeakSocialRef->id;
                                    $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                                    $transaction_client_leak_social_ref -> transaction_data_status = 1;
                                    $transaction_client_leak_social_ref -> status = 1;
                                    $transaction_client_leak_social_ref -> save();
                                }
                            }
                        }
                        DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->delete();
                        DataLeakSocialRef::where('temp_id', $DataLeakFeedTemp->id)->delete();
                        $DataLeakFeedTemp->approve = 0;
                        $DataLeakFeedTemp->save();
                    }
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
        $DataLeakSocial = DataLeakSocial::where('deleted_at', null)->where('status', 1)->get();
        $data['DataLeakSocial'] = $DataLeakSocial;
        $data['siteSettings'] = $get_data;
        $data['page'] = 'Data Leack Data';
        $data['id']  = ($get_data->code);

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
        $data['page'] = 'Compromised_Feed_in_site';
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
        $data['siteCode'] = $siteID->code;
        $data['page'] = 'Compromised_Data_in_site';
        $data['siteSettings'] = $get_data;
        return view('sitesettings::compromised_data')->with($data);
    }

    public function socialdatas_all_site()
    {

        $SiteSettings = @get_role_custom()['SiteSettings'];
        $site_id_arr = @get_role_custom()['site_id_arr'];
        if(@get_role_custom()['superadmin'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }else if(@get_role_custom()['client'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }else if(@get_role_custom()['site_support'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }else if(@get_role_custom()['site_admin'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }else if(@get_role_custom()['site_client'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }

        // $data['SiteSettings'] = SiteSettings::where("active", '=', 1)->where('deleted_at', null)->get();
        $data['SiteSettings'] = $SiteSettings;
        $data['source'] = DataLeakSocial::where("status", '=', 1)->get();

        $data['page'] = langapp('data_leak');
        return view('sitesettings::social-datas_all_site')->with($data);
    }

    public function darkweb_datas_all_site()
    {
        //    $get_data = $this->siteSettings->get_data($id);
        //    $data['siteSettings'] = $get_data;

        $SiteSettings = @get_role_custom()['SiteSettings'];
        $site_id_arr = @get_role_custom()['site_id_arr'];
        if(@get_role_custom()['superadmin'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }else if(@get_role_custom()['client'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }else if(@get_role_custom()['site_support'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }else if(@get_role_custom()['site_admin'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
        }else if(@get_role_custom()['site_client'] == 1) {
            $SiteSettings = @get_role_custom()['SiteSettings'];
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

    public function create_compromise(Request $request)
    {
        $data['site'] = SiteSettings::where("active", '=', 1)->where('deleted_at', null)->get();
        $data['site_code'] = @$request->site;

        
        return view('sitesettings::modal.create_compromise')->with($data);
    }

    public function create_dataleak(Request $request)
    {
        $data['site'] = SiteSettings::where("active", '=', 1)->where('deleted_at', null)->get();
        $data['site_code'] = @$request->site;
        return view('sitesettings::modal.create_dataleak')->with($data);
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
        //why use %...%
        $model = DataLeakSocialRef::where('site_id', 'LIKE', '' . $site->id . '')->where('deleted_at', null)
        ->whereHas('get_data_leak_feed_one', function ($query) {
            $query->whereIn('feel_type',['social','darkweb_public']);
        })
        ->with('get_site')
        ->with('get_data_leak_feed_one');
        if($request->search_val == 1){
            if ($request->search) {
                $search = $request->search;
                $model = $model->whereHas('get_data_leak_feed_one', function ($query) use ($search) {
                    $query->where('feedcontent', 'LIKE', '%' . $search . '%');
                    $query->orwhere('keyword', 'LIKE', '%' . $search . '%');
                });
            }

            //<><><>
            // if (Auth::check()) {

            //     $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            //     if (Auth::user()->hasRole('admin')) { //if admin
            //         // dd(777);

            //     } else { //if notAdmin
            //         // dd(888);
            //         if (@Auth::user()->site_role_id && @Auth::user()->site_id) {
            //             if (@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) { //support and admin
            //                 // dd(99);

            //                 $model = $model->whereIn('site_id', $site_id_arr);

            //                 // $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);

            //             } else { //not support and admin
            //                 $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

            //                 // $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
            //             }
            //         }
            //     }
            // }
            $get_role_custom_first = @get_role_custom();
            $site_id_arr = @$get_role_custom_first['site_id_arr'];
            if(@$get_role_custom_first['superadmin'] == 1) {

            }else if(@$get_role_custom_first['client'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

            }else if(@$get_role_custom_first['site_support'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr);

            }else if(@$get_role_custom_first['site_admin'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr);

            }else if(@$get_role_custom_first['site_client'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

            }

            if ($request->check_type) {
                
                $type = $request->check_type;
                $model->whereHas('get_data_leak_feed_one', function ($query) use ($type) {
                    $query->where('feel_type', 'LIKE', '%' . $type . '%');
                });

            }
            
            if ($request->start_date) {
                $date_start = $request->start_date;
                $date_end = $request->end_date;

                $date_start_explode = explode(" ", $date_start);
                $date_start_date = @$date_start_explode[0];
                $date_start_date_format = date("Y-m-d", strtotime($date_start_date));

                $date_end_explode = explode(" ", $date_end);
                $date_end_date = @$date_end_explode[0];
                $date_end_date_format = date("Y-m-d", strtotime($date_end_date));

                $model = $model->whereBetween('created_at', array($date_start_date_format, $date_end_date_format));
            }

            $source = $request->source;
            $model->whereHas('get_data_leak_feed_one', function ($query) use ($source) {
                $query->where('sourceid', 'LIKE', '%' . $source . '%');
            });

           
        }
        $model->orderBy('created_at', 'desc');
        
        return DataTables::of($model)
            ->editColumn(
                'chk',
                function (DataLeakSocialRef $model) {
                    return '<label><input type="checkbox" name="data_feed_id" class="data_feed_id" value="' . $model->id . '"><span class="label-text"></span></label>';
                }
            )
            ->editColumn(
                'site',
                function (DataLeakSocialRef $model) {
                    if (@$model->get_site) {
                        return @$model->get_site->name;
                    } else {
                        return '-';
                    }
                }
            )
            ->editColumn(
                'type',
                function (DataLeakSocialRef $model) {
                    if (@$model->get_data_leak_feed_one) {
                        return get_word_leak_compromise($model->get_data_leak_feed_one->feel_type,'data_leak');
                    } else {
                        return '-';
                    }
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
                    return @$model->get_data_leak_feed->feedtimepost;
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
                //     return "
                //     <a href='" . route('socialdatas.view_content_dataleak', ['code' => $model->code]) . "' class='btn btn-info btn-xs' data-toggle='ajaxModal'><i class='fas fa-eye'></i></a>
                //     <a href='" . route('socialdatas.delete', ['code' => $model->code]) . "' class='btn btn-danger btn-xs' data-toggle='ajaxModal'>
                // <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                // </a>";
                return '';
                }
            )
            ->rawColumns(['chk','type','site', 'source', 'keyword', 'content', 'data_feed', 'view_count', 'status', 'action'])
            ->make(true);
    }

    public function socialdatas_change_delete(Request $request)
    {

        $site_code = DataLeakSocialRef::where("id", $request->id_change)->first();
        $site_code = SiteSettings::where('id', $site_code->site_id)->first();

        foreach ($request->id_change as $id_change) {

            $DataLeakSocialRef = DataLeakSocialRef::where('id', $id_change)->first();
            $DataLeakFeedTemps = DataLeakFeedTemp::whereIn('id', [$DataLeakSocialRef->temp_id])->get();
            DataLeakFeed::where('id', $DataLeakSocialRef->data_leak_feed_id)->delete();
            DataLeakSocialRef::where('temp_id', $DataLeakSocialRef->temp_id)->delete();
    
            if($DataLeakFeedTemps){
                foreach($DataLeakFeedTemps as $DataLeakFeedTemps){
                    $DataLeakFeedTemps->approve = 0;
                    $DataLeakFeedTemps->save();
                }
            } 

        }

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('socialdatas.index', ['id' => $site_code->code]),

            ],
            true,
            Response::HTTP_OK
        );
    }

    public function socialdatas_all_site_tb(Request $request)
    {
        $model = DataLeakSocialRef::where('deleted_at', null)
            ->whereHas('get_data_leak_feed_one', function ($query) {
                $query->whereIn('feel_type', ['social','darkweb_public']);
            })
            ->with('get_site')
            ->with('get_data_leak_feed_one');

        if ($request->search_val == 1) {



            //<><><>
            // if (Auth::check()) {

            //     $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            //     if (Auth::user()->hasRole('admin')) { //if admin
            //         // dd(777);

            //     } else { //if notAdmin
            //         // dd(888);
            //         if (@Auth::user()->site_role_id && @Auth::user()->site_id) {
            //             if (@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) { //support and admin
            //                 // dd(99);

            //                 $model = $model->whereIn('site_id', $site_id_arr);

            //                 // $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);

            //             } else { //not support and admin
            //                 $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

            //                 // $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
            //             }
            //         }
            //     }
            // }

            $get_role_custom_first = @get_role_custom();
            $site_id_arr = @$get_role_custom_first['site_id_arr'];
            if(@$get_role_custom_first['superadmin'] == 1) {
                

            }else if(@$get_role_custom_first['client'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

            }else if(@$get_role_custom_first['site_support'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr);

            }else if(@$get_role_custom_first['site_admin'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr);

            }else if(@$get_role_custom_first['site_client'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

            }

            if ($request->keywords) {
                $keywords = $request->keywords;
                $model->whereHas('get_data_leak_feed_one', function ($query) use ($keywords) {
                    $query->where('keyword', 'LIKE', '%' . $keywords . '%');
                });
            }



            if ($request->site) {
                $SiteSettings = SiteSettings::where('code', @$request->site)->first();
                // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
                $model = $model->where('site_id', $SiteSettings->id);
                // });
            }

            if ($request->type) {

                $type = $request->type;
                $model->whereHas('get_data_leak_feed_one', function ($query) use ($type) {
                    $query->where('feel_type', 'LIKE', '%' . $type . '%');
                });

            }

            if ($request->check_type) {
              
                $type = $request->check_type;
                $model->whereHas('get_data_leak_feed_one', function ($query) use ($type) {
                    $query->where('feel_type', 'LIKE', '%' . $type . '%');
                });

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

            // $model->get();
        } else {
         
            if ($request->site) {
                $SiteSettings = SiteSettings::where('code', @$request->site)->first();
                // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
                $model = $model->where('site_id', $SiteSettings->id);
                // });
            }

            if ($request->click_key) {
                $model = $model->where('keyword', $request->click_key);
                // });
            }


            if($request ->click_type) {

                $model = $model-> where('feel_type', '=' ,$request -> click_type);


            }
    
            if ($request->click_type2) {
                $keywords = $request->click_type2;
                $model->whereHas('get_data_leak_feed_one', function ($query) use ($keywords) {
                    if($keywords == 'other') {
                        $query->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('mobile'))])
                                            ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('facebook'))])
                                            ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('line'))])
                                            ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('twitter'))])
                                            ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))]);
    
                    } else {
                        if($keywords == 'in_progress') {
                            $query->where('status_monitoring', 'LIKE', '%' . $keywords . '%');
                        } else if($keywords == 'reported') {
                            $query->where('status_monitoring', 'LIKE', '%' . $keywords . '%');
                        } else if($keywords == 'close') {
                            $query->where('status_monitoring', 'LIKE', '%' . $keywords . '%');
                        } else {
                            $query->where('keyword', 'LIKE', '%' . $keywords . '%');
                        }
                    }
                });
            }

            //<><><>
            // if (Auth::check()) {

            //     $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            //     if (Auth::user()->hasRole('admin')) { //if admin
            //         // dd(777);

            //     } else { //if notAdmin
            //         // dd(888);
            //         if (@Auth::user()->site_role_id && @Auth::user()->site_id) {
            //             if (@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) { //support and admin
            //                 // dd(99);

            //                 $model = $model->whereIn('site_id', $site_id_arr);

            //                 // $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);

            //             } else { //not support and admin
            //                 $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

            //                 // $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
            //             }
            //         }
            //     }
            // }

            $get_role_custom_first = @get_role_custom();
            $site_id_arr = @$get_role_custom_first['site_id_arr'];
            if(@$get_role_custom_first['superadmin'] == 1) {

            }else if(@$get_role_custom_first['client'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

            }else if(@$get_role_custom_first['site_support'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr);

            }else if(@$get_role_custom_first['site_admin'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr);

            }else if(@$get_role_custom_first['site_client'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

            }

            
        }
        


        $model->orderBy('created_at', 'desc');

        return DataTables::of($model)->toJson();

        // $model = DataLeakSocialRef::where('deleted_at', null)->orderBy('id', 'desc');
        // $model->whereHas('get_data_leak_feed', function ($query){
        //     $query->where('site_id', );

    }

    // View Content DataLeak
    public function view_dataleak_modal($code)
    {
        $data["code"] = $code;
        $model = DataLeakSocialRef::select('data_leak_feed.feedcontent')->join('data_leak_feed', 'data_leak_feed.id', '=',
        'data_leak_socail_ref.data_leak_feed_id')->where('data_leak_socail_ref.code',$code)->first();
        $data["feedcontent"] = htmlspecialchars_decode($model->feedcontent);
        return view('sitesettings::modal.view_content_dataleak')->with($data);
    }
    
    // View Content Compromise
    public function view_compromise_modal($code)
    {
        $data["code"] = $code;
        $model1 = DataLeakSocialRef::select('data_leak_feed.feedcontent')->join('data_leak_feed', 'data_leak_feed.id', '=',
        'data_leak_socail_ref.data_leak_feed_id')->where('data_leak_socail_ref.code',$code)->first();
        $data["feedcontent"] = $model1->feedcontent;
        return view('sitesettings::modal.view_content_compromise')->with($data);
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
        $DataLeakSocialRef = DataLeakSocialRef::where('code', $code)->first();
        $DataLeakFeedTemp = DataLeakFeedTemp::where('id', $DataLeakSocialRef->temp_id)->first();
        if($DataLeakFeedTemp){
            $DataLeakFeeds = DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->get();
            if($DataLeakFeeds){
                foreach($DataLeakFeeds as $DataLeakFeed){
                    $leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $DataLeakFeedTemp->id)->first();
                    $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakFeed->id)->first();
                    if($transaction_client_leak_feed){
                        $transaction_client_leak_feed -> transaction_mode = 'delete';
                        $transaction_client_leak_feed -> transaction_data_status = 1;
                        $transaction_client_leak_feed -> status = 1;
                        $transaction_client_leak_feed -> save();
                    }else{
                        $transaction_client_leak_feed = new transaction_client_leak_feed();
                        $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                        $transaction_client_leak_feed -> transaction_id = $DataLeakFeed->id;
                        $transaction_client_leak_feed -> transaction_mode = 'delete';
                        $transaction_client_leak_feed -> transaction_data_status = 1;
                        $transaction_client_leak_feed -> status = 1;
                        $transaction_client_leak_feed -> save();
                    }

                    $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakSocialRef->id)->first();
                    if($transaction_client_leak_social_ref){
                        $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                        $transaction_client_leak_social_ref -> transaction_data_status = 1;
                        $transaction_client_leak_social_ref -> status = 1;
                        $transaction_client_leak_social_ref -> save();
                    }else{
                        $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                        $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                        $transaction_client_leak_social_ref -> transaction_id = $DataLeakSocialRef->id;
                        $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                        $transaction_client_leak_social_ref -> transaction_data_status = 1;
                        $transaction_client_leak_social_ref -> status = 1;
                        $transaction_client_leak_social_ref -> save();
                    }
        
                }
            }
            DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->delete();
            $DataLeakSocialRef->delete();
            $DataLeakFeedTemp->approve = 0;
            $DataLeakFeedTemp->save();
        }else{
            $DataLeakSocialRef->delete();
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

    public function delete_dataleakdata($code)
    {
        // dd($code);
        $DataLeakSocialRef = DataLeakSocialRef::where('code', $code)->first();
        $DataLeakFeedTemp = DataLeakFeedTemp::where('id', $DataLeakSocialRef->temp_id)->first();
        if($DataLeakFeedTemp){
            $DataLeakFeeds = DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->get();
            if($DataLeakFeeds){
                foreach($DataLeakFeeds as $DataLeakFeed){
                    $leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $DataLeakFeedTemp->id)->first();
                    $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakFeed->id)->first();
                    if($transaction_client_leak_feed){
                        $transaction_client_leak_feed -> transaction_mode = 'delete';
                        $transaction_client_leak_feed -> transaction_data_status = 1;
                        $transaction_client_leak_feed -> status = 1;
                        $transaction_client_leak_feed -> save();
                    }else{
                        $transaction_client_leak_feed = new transaction_client_leak_feed();
                        $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                        $transaction_client_leak_feed -> transaction_id = $DataLeakFeed->id;
                        $transaction_client_leak_feed -> transaction_mode = 'delete';
                        $transaction_client_leak_feed -> transaction_data_status = 1;
                        $transaction_client_leak_feed -> status = 1;
                        $transaction_client_leak_feed -> save();
                    }

                    $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakSocialRef->id)->first();
                    if($transaction_client_leak_social_ref){
                        $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                        $transaction_client_leak_social_ref -> transaction_data_status = 1;
                        $transaction_client_leak_social_ref -> status = 1;
                        $transaction_client_leak_social_ref -> save();
                    }else{
                        $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                        $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                        $transaction_client_leak_social_ref -> transaction_id = $DataLeakSocialRef->id;
                        $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                        $transaction_client_leak_social_ref -> transaction_data_status = 1;
                        $transaction_client_leak_social_ref -> status = 1;
                        $transaction_client_leak_social_ref -> save();
                    }
        
                }
            }
            DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->delete();
            $DataLeakSocialRef->delete();
            $DataLeakFeedTemp->approve = 0;
            $DataLeakFeedTemp->save();
        }else{
            $DataLeakSocialRef->delete();
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
            $DataLeakSocialRef = DataLeakSocialRef::where('id', $social_id)->first();
            $DataLeakFeedTemp = DataLeakFeedTemp::where('id', $DataLeakSocialRef->temp_id)->first();
            if($DataLeakFeedTemp){
                $DataLeakFeeds = DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->get();
                if($DataLeakFeeds){
                    foreach($DataLeakFeeds as $DataLeakFeed){
                        $leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $DataLeakFeedTemp->id)->first();
                        $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakFeed->id)->first();
                        if($transaction_client_leak_feed){
                            $transaction_client_leak_feed -> transaction_mode = 'delete';
                            $transaction_client_leak_feed -> transaction_data_status = 1;
                            $transaction_client_leak_feed -> status = 1;
                            $transaction_client_leak_feed -> save();
                        }else{
                            $transaction_client_leak_feed = new transaction_client_leak_feed();
                            $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                            $transaction_client_leak_feed -> transaction_id = $DataLeakFeed->id;
                            $transaction_client_leak_feed -> transaction_mode = 'delete';
                            $transaction_client_leak_feed -> transaction_data_status = 1;
                            $transaction_client_leak_feed -> status = 1;
                            $transaction_client_leak_feed -> save();
                        }

                        $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakSocialRef->id)->first();
                        if($transaction_client_leak_social_ref){
                            $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                            $transaction_client_leak_social_ref -> transaction_data_status = 1;
                            $transaction_client_leak_social_ref -> status = 1;
                            $transaction_client_leak_social_ref -> save();
                        }else{
                            $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                            $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                            $transaction_client_leak_social_ref -> transaction_id = $DataLeakSocialRef->id;
                            $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                            $transaction_client_leak_social_ref -> transaction_data_status = 1;
                            $transaction_client_leak_social_ref -> status = 1;
                            $transaction_client_leak_social_ref -> save();
                        }
            
                    }
                }
                DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->delete();
                $data = DataLeakSocialRef::where('id', $social_id)->delete();
                $DataLeakFeedTemp->approve = 0;
                $DataLeakFeedTemp->save();
            }else{
                $data = DataLeakSocialRef::where('id', $social_id)->delete();
            }
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
            $DataLeakSocialRef = DataLeakSocialRef::where('id', $val_id)->first();
            $DataLeakFeedTemp = DataLeakFeedTemp::where('id', $DataLeakSocialRef->temp_id)->first();
            if($DataLeakFeedTemp){
                $DataLeakFeeds = DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->get();
                if($DataLeakFeeds){
                    foreach($DataLeakFeeds as $DataLeakFeed){
                        $leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $DataLeakFeedTemp->id)->first();
                        $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakFeed->id)->first();
                        if($transaction_client_leak_feed){
                            $transaction_client_leak_feed -> transaction_mode = 'delete';
                            $transaction_client_leak_feed -> transaction_data_status = 1;
                            $transaction_client_leak_feed -> status = 1;
                            $transaction_client_leak_feed -> save();
                        }else{
                            $transaction_client_leak_feed = new transaction_client_leak_feed();
                            $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                            $transaction_client_leak_feed -> transaction_id = $DataLeakFeed->id;
                            $transaction_client_leak_feed -> transaction_mode = 'delete';
                            $transaction_client_leak_feed -> transaction_data_status = 1;
                            $transaction_client_leak_feed -> status = 1;
                            $transaction_client_leak_feed -> save();
                        }

                        $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakSocialRef->id)->first();
                        if($transaction_client_leak_social_ref){
                            $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                            $transaction_client_leak_social_ref -> transaction_data_status = 1;
                            $transaction_client_leak_social_ref -> status = 1;
                            $transaction_client_leak_social_ref -> save();
                        }else{
                            $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                            $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                            $transaction_client_leak_social_ref -> transaction_id = $DataLeakSocialRef->id;
                            $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                            $transaction_client_leak_social_ref -> transaction_data_status = 1;
                            $transaction_client_leak_social_ref -> status = 1;
                            $transaction_client_leak_social_ref -> save();
                        }
            
                    }
                }
                DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->delete();
                $data = DataLeakSocialRef::where('id', $val_id)->delete();
                $DataLeakFeedTemp->approve = 0;
                $DataLeakFeedTemp->save();
            }
            else{
                $data = DataLeakSocialRef::where('id', $val_id)->delete();
            }
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
            $model = DataLeakSocialRef::where('deleted_at', null)
            ->whereHas('get_data_leak_feed_one', function ($query) {
                $query->whereIn('feel_type', ['darkweb', 'compromise', 'webserver', 'server']);
            })
            ->with('get_site')
            ->with('get_data_leak_feed_one');
            $countGroupBy = DataLeakSocialRef::where('deleted_at', null)->where('status', 1);

            // if($request -> keywords){
            //     $model = $model->where('source_name', 'LIKE', '%'.$request -> keywords.'%');
            // }

            if ($request->keywords) {

                // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
                    if ($request->keywords) {
                        $keywords = $request->keywords;
                        $model->whereHas('get_data_leak_feed_one', function ($query) use ($keywords) {
                            $query->where('keyword', 'LIKE', '%' . $keywords . '%');
                        });
                    }
                // });
                // dd($model->get()->toArray());
            }

            // if ($request->source) {
            //     $model = $model->where('feel_type', '=', $request->source);
            //     $countGroupBy = $countGroupBy->where('feel_type', '=', $request->source);
            // } else {
            //     $model = $model->whereIn('feel_type', ['darkweb', 'compromise', 'webserver', 'server']);
            //     $countGroupBy = $countGroupBy->whereIn('feel_type', ['darkweb', 'compromise', 'webserver', 'server']);
            // }

            if($request ->check_type) {

                $model = $model-> where('feel_type', '=' ,$request -> check_type);
                $countGroupBy = $countGroupBy -> where('feel_type', '=' ,$request -> check_type);

            }




            //<><><>
            // if (Auth::check()) {

            //     $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            //     if (Auth::user()->hasRole('admin')) { //if admin
            //         // dd(777);

            //     } else { //if notAdmin
            //         // dd(888);
            //         if (@Auth::user()->site_role_id && @Auth::user()->site_id) {
            //             if (@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) { //support and admin
            //                 // dd(99);

            //                 $model = $model->whereIn('site_id', $site_id_arr);

            //                 // $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);

            //             } else { //not support and admin
            //                 $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

            //                 // $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
            //             }
            //         }
            //     }
            // }
            $get_role_custom_first = @get_role_custom();
            $site_id_arr = @$get_role_custom_first['site_id_arr'];
            if(@$get_role_custom_first['superadmin'] == 1) {

            }else if(@$get_role_custom_first['client'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

            }else if(@$get_role_custom_first['site_support'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr);

            }else if(@$get_role_custom_first['site_admin'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr);

            }else if(@$get_role_custom_first['site_client'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

            }


            if ($request->site) {
                
                $SiteSettings = SiteSettings::where('code', @$request->site)->first();
                // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
                $model = $model->where('site_id', $SiteSettings->id);
                // });
           
            }

            if ($request->isDateSearch == 1) {
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

                if ($request->click_key) {
                    $model = $model->where('keyword', $request->click_key);
                    // });
                }

            //<><><>
            // if (Auth::check()) {

            //     $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            //     if (Auth::user()->hasRole('admin')) { //if admin
            //         // dd(777);

            //     } else { //if notAdmin
            //         // dd(888);
            //         if (@Auth::user()->site_role_id && @Auth::user()->site_id) {
            //             if (@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) { //support and admin
            //                 // dd(99);

            //                 $model = $model->whereIn('site_id', $site_id_arr);

            //                 // $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);

            //             } else { //not support and admin
            //                 $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

            //                 // $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
            //             }
            //         }
            //     }
            // }
            $get_role_custom_first = @get_role_custom();
            $site_id_arr = @$get_role_custom_first['site_id_arr'];
            if(@$get_role_custom_first['superadmin'] == 1) {

            }else if(@$get_role_custom_first['client'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

            }else if(@$get_role_custom_first['site_support'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr);

            }else if(@$get_role_custom_first['site_admin'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr);

            }else if(@$get_role_custom_first['site_client'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);

            }

            if ($request->site) {
                
                $SiteSettings = SiteSettings::where('code', @$request->site)->first();
                // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
                $model = $model->where('site_id', $SiteSettings->id);
                // });
           
            }

            if($request ->click_type) {

                $model = $model-> where('feel_type', '=' ,$request -> click_type);


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

            $model = DataLeakFeedTemp::where('keyword', '!=', null)->where('keyword', '!=', '')->whereIn('feed_type', ['social','darkweb_public']);

            if ($request->site) {
                $site = SiteSettings::select('id')->where('code', $request->site)->first();

                $model->whereHas('get_socail_ref_temp', function ($query) use ($site) {

                    $query->where('site_id', 'LIKE', '%' . $site->id . '%');
                });
            }
            if ($request->search) {
                $model = $model->where('keyword', 'LIKE', '%' . $request->search . '%');
            }

            if ($request->type) {

                $model = $model->where('feed_type', $request->type);
            }
            // if ($request->source_select) {
            //     $model = $model->where('sourceid', $request->source_select);
            // }
            // if ($request->check_all == 'true') {

            // } else {
            //     if ($request->check_pending == 'true') {
            //         $model = $model->where('approve', '0');
            //     }
            //     if ($request->check_approved == 'true') {
            //         $model = $model->where('approve', '1');
            //     }
            // }
            if ($request->check_type) {
                if($request->check_type==1){
                    $model = $model->where('approve', '0');
                }else if($request->check_type==2){
                    $model = $model->where('approve', '1');
                }
            }


            if ($request->isDateSearch == 1) {
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

            $model = DataLeakFeedTemp::where('keyword', '!=', null)->where('keyword', '!=', '')->whereIn('feed_type', ['social','darkweb_public']);
            if ($request->site) {
                $site = SiteSettings::select('id')->where('code', $request->site)->first();

                $model->whereHas('get_socail_ref_temp', function ($query) use ($site) {

                    $query->where('site_id', 'LIKE', '%' . $site->id . '%');
                });
            }
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
                'site',
                function (DataLeakFeedTemp $model) {
                    $name_site = '';
                    $leak_socail_ref_temps = leak_socail_ref_temp::select('site_id')->where('data_leak_feed_id', $model->id)->where('keyword', '!=', 'scanner')->first();
                    if($leak_socail_ref_temps){
                        $site = SiteSettings::select('name')->whereIn('id', [$leak_socail_ref_temps->site_id])->get();
                        if($site){
                            foreach ($site as $data) {
                                $name_site .= $data->name . ' ,';
                            }
                            return rtrim($name_site, ", ");
                        }else{
                            return '-';
                        }
                    }else{
                        return '-';
                    }


                }
            )
            ->editColumn(
                'source',
                function (DataLeakFeedTemp $model) {
                    if ($model->feed_type) {
                        return get_word_leak_compromise($model->feed_type,'data_leak');
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
                    return '<div>' . $model->feedcontent . '</div>';
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
            ->rawColumns(['chk', 'site', 'source', 'keyword', 'content', 'data_feed', 'url', 'action'])
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
            
            $model = DataLeakSocialRefTemp::where(function ($q) use ($request) {
                $q->where('keyword', '!=', null);
                $q->where('keyword', '!=', '');
                if($request->site){
                    
                    $q->where('site_id',$request->site);
                }
            });

            $model->whereHas('get_data_leak_feed_temp_one', function ($qq) use ($request) {

                $qq->whereIn('feed_type', ['darkweb', 'compromise', 'webserver', 'server']);

                if ($request->search) {
                    $qq->where('keyword', 'LIKE', '%' . $request->search . '%');
                }

                if ($request->source_select) {
                    $qq->where('sourceid', $request->source_select);
                }


                if ($request->check_type) {
                    if($request->check_type==1){
                        $qq->where('approve', '0');
                    }else if($request->check_type==2){
                        $qq->where('approve', '1');
                    }
                }
                // if ($request->check_all == 'true') {

                // } else {
                //     if ($request->check_pending == 'true' && $request->check_approved == 'true') {

                //     } else if ($request->check_pending == 'true') {
                //         $qq->where('approve', '0');
                //     } else if ($request->check_approved == 'true') {
                //         $qq->where('approve', '1');
                //     }
                // }

                if ($request->isDateSearch==1) {
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
                    $qq->whereBetween('feedtimepost', array($date_start_date_format, $date_end_date_format));
                }
            });

            $model = $model->get();
        } else {
            
            $model = DataLeakSocialRefTemp::where(function ($q) use ($request) {
                $q->where('keyword', '!=', null);
                $q->where('keyword', '!=', '');
                if(@$request->site){  
                    $q->where('site_id',$request->site);
                }
            })->with('get_site');

            $model = $model->whereHas('get_data_leak_feed_temp_one', function ($qq) use ($request) {
                $qq->where('keyword', 'LIKE', '%' . $request->search . '%');
                $qq->whereIn('feed_type', ['darkweb', 'compromise', 'webserver', 'server']);
            });

            $model = $model->get();

            // dd($model);

        }

        return DataTables::of($model)
            ->editColumn(
                'chk',
                function ($model) {
                    return '<label><input type="checkbox" name="data_feed_id" class="data_feed_id val_id" value="' . $model->get_data_leak_feed_temp_one->id . '"><span class="label-text"></span></label>';
                }
            )
            ->editColumn(
                'site',
                function ($model) {
                    // $data_leak_socail_ref_temp = DataLeakSocialRefTemp::select('site_id')->where('data_leak_feed_id' , $model->get_data_leak_feed_temp_one->id)->first();
                    // $site = SiteSettings::select('name')->whereIn('id', [$data_leak_socail_ref_temp -> site_id])->get();
                    // $name_site = '';
                    // foreach($site as $data){
                    //     $name_site .= $data -> name . ' ,';
                    // }
                    // return rtrim($name_site, ", ") . ' '.$model->get_data_leak_feed_temp_one->id;

                    $site = SiteSettings::select('name')->whereIn('id', [$model->site_id])->first();
                    return @$site->name;

                }
            )

            ->editColumn(
                'type',
                function ($model) {
                    if ($model->get_data_leak_feed_temp_one) {
                        return get_word_leak_compromise($model->get_data_leak_feed_temp_one->feed_type,'compromise');
                    } else {
                        return '-';
                    }

                }
            )
            ->editColumn(
                'source',
                function ($model) {
                    if ($model->get_data_leak_feed_temp_one) {
                        return $model->get_data_leak_feed_temp_one->source_name;
                    } else {
                        return '-';
                    }

                }
            )
            ->editColumn(
                'keyword',
                function ($model) {
                    if ($model->keyword) {
                        return $model->keyword;
                    } else {
                        return '-';
                    }
                }
            )
            ->editColumn(
                'content',
                function ($model) {
                    if ($model->get_data_leak_feed_temp_one) {
                        return $model->get_data_leak_feed_temp_one->feedcontent;
                    } else {
                        return '-';
                    }

                }
            )
            ->editColumn(
                'data_feed',
                function ($model) {
                    if ($model->get_data_leak_feed_temp_one) {
                        return $model->get_data_leak_feed_temp_one->feedtimestamp;
                    } else {
                        return '-';
                    }
                }
            )
            ->editColumn(
                'url',
                function ($model) {
                    return '<a href="' . @$model->get_data_leak_feed_temp_one->feedlink . '" target="_blank"><i class="fas fa-link"></i></a>';
                }
            )
            ->editColumn(
                'action',
                function ($model) {
                    $html = '';
                    if (@$model->get_data_leak_feed_temp_one->approve == 0) {
                        $html .= '<button class="btn btn-success btn-xs" data-toggle="modal" data-target="#confirm-change-status" onclick="approve_dataFeed(' . @$model->get_data_leak_feed_temp_one->id . ')">
                        Approve
                    </button>';

                        // $html .= '<a href="'.route('').'" class="btn btn-{{get_option("theme_color")}} btn-xs" data-toggle="ajaxModal"><i class="fas fa-share-square"></i></a>';

                    } else {
                        $html .= '<button class="btn btn-danger btn-xs" data-toggle="modal" data-target="#confirm-change-status-cancle" onclick="cancle_dataFeed(' . @$model->get_data_leak_feed_temp_one->id . ')">
                        Cancel
                    </button>';
                    }
                    return $html;
                }
            )
            ->rawColumns(['chk', 'site', 'type', 'source', 'keyword', 'content', 'data_feed', 'url', 'action'])
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
        $DataLeakFeedTemps = DataLeakFeedTemp::whereIn('id', [$DataLeakSocialRef->temp_id])->get();
        DataLeakFeed::where('id', $DataLeakSocialRef->data_leak_feed_id)->delete();
        DataLeakSocialRef::where('temp_id', $DataLeakSocialRef->temp_id)->delete();

        if($DataLeakFeedTemps){
            foreach($DataLeakFeedTemps as $DataLeakFeedTemps){
                $DataLeakFeedTemps->approve = 0;
                $DataLeakFeedTemps->save();
            }
        }

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
        $type = @$DataLeakFeedTemps[0]->feed_type;
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

                // $DataLeakFeed_send_mail[] = $DataLeakFeed;

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
                    
                    if(!isset($DataLeakFeed_send_mail[(string)$leak_socail_ref_temp->site_id])){
                        $DataLeakFeed_send_mail[(string)$leak_socail_ref_temp->site_id] = [];
                    }
                    array_push($DataLeakFeed_send_mail[(string)$leak_socail_ref_temp->site_id], $DataLeakFeed);


                    $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakFeed->id)->first();
                    if($transaction_client_leak_feed){
                        $transaction_client_leak_feed -> transaction_mode = 'insert';
                        $transaction_client_leak_feed -> transaction_data_status = 1;
                        $transaction_client_leak_feed -> status = 1;
                        $transaction_client_leak_feed -> save();
                    }else{
                        $transaction_client_leak_feed = new transaction_client_leak_feed();
                        $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                        $transaction_client_leak_feed -> transaction_id = $DataLeakFeed->id;
                        $transaction_client_leak_feed -> transaction_mode = 'insert';
                        $transaction_client_leak_feed -> transaction_data_status = 1;
                        $transaction_client_leak_feed -> status = 1;
                        $transaction_client_leak_feed -> save();
                    }

                    
                    $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakSocialRef->id)->first();
                    if($transaction_client_leak_social_ref){
                        $transaction_client_leak_social_ref -> transaction_mode = 'insert';
                        $transaction_client_leak_social_ref -> transaction_data_status = 1;
                        $transaction_client_leak_social_ref -> status = 1;
                        $transaction_client_leak_social_ref -> save();
                    }else{
                        $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                        $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                        $transaction_client_leak_social_ref -> transaction_id = $DataLeakSocialRef->id;
                        $transaction_client_leak_social_ref -> transaction_mode = 'insert';
                        $transaction_client_leak_social_ref -> transaction_data_status = 1;
                        $transaction_client_leak_social_ref -> status = 1;
                        $transaction_client_leak_social_ref -> save();
                    }
                }

                $DataLeakFeedTemp->approve = 1;
                $DataLeakFeedTemp->save();
            }
        }
        
        if ($this->request->sent_mail == 1) {
            foreach ($DataLeakFeed_send_mail as $key => $value) {
                $site_email_alert = site_config_email_alert::where("site_id", $key)->get();
                if ($site_email_alert) {
                    $email_site_a = [];
                    foreach ($site_email_alert as $site_email_alert_val) {
                        $email_site_a[] = $site_email_alert_val->email;
                    }
                    $email_site_alert = array_unique($email_site_a);
                    foreach($email_site_alert as $email){
                        Mail::to($email)->send(new CompromisedMail($value, 'data_leak'));
                        if( count(Mail::failures()) == 0 ) {
                            LogEmail::Create([
                                'to' => $email,
                                'status' => 'Success',
                                'subject' => 'data_leak'
                            ]);
                        }
                    }
                    if( count(Mail::failures()) > 0 ) {
                        foreach(Mail::failures() as $email_address) {
                            LogEmail::Create([
                                'to' => $email_address,
                                'status' => 'Fail',
                                'subject' => 'data_leak'
                            ]);
                        }
                    }
                }
            }

            



        }

        if(@$type == 'social' || @$type == 'darkweb_public') {
            if($request->site){
                $site = route('darkweb_datas.index',['id'=>$request->site]);
            }else{
                $site = route('datafeed.index');
            }
        } else {
            if($request->site){
                $site = route('compromised_feed.index',['id'=>$request->site]);
            }else{
                $site = route('datafeed.darkweb_index');
            }
        }


        // if($request->site){
        //     $site = route('darkweb_datas.index',['id'=>$request->site]);
        // }else{
        //     $site = route('datafeed.index');
        // }
        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => @$site,
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function cancle_data_feed(Request $request)
    {
        $DataLeakFeedTemps = DataLeakFeedTemp::whereIn('id', $request->id)->get();
        $type = @$DataLeakFeedTemps[0]->feed_type;
        foreach ($DataLeakFeedTemps as $DataLeakFeedTemp) {
            $DataLeakFeeds = DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->get();
            foreach($DataLeakFeeds as $DataLeakFeed){
                $leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $DataLeakFeedTemp->id)->first();
                $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakFeed->id)->first();
                if($transaction_client_leak_feed){
                    $transaction_client_leak_feed -> transaction_mode = 'delete';
                    $transaction_client_leak_feed -> transaction_data_status = 1;
                    $transaction_client_leak_feed -> status = 1;
                    $transaction_client_leak_feed -> save();
                }else{
                    $transaction_client_leak_feed = new transaction_client_leak_feed();
                    $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                    $transaction_client_leak_feed -> transaction_id = $DataLeakFeed->id;
                    $transaction_client_leak_feed -> transaction_mode = 'delete';
                    $transaction_client_leak_feed -> transaction_data_status = 1;
                    $transaction_client_leak_feed -> status = 1;
                    $transaction_client_leak_feed -> save();
                } 
            }
            DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->delete();
            $DataLeakSocialRefs = DataLeakSocialRef::where('temp_id', $DataLeakFeedTemp->id)->get();
            foreach($DataLeakSocialRefs as $DataLeakSocialRef){
                $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakSocialRef->id)->first();
                if($transaction_client_leak_social_ref){
                    $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                    $transaction_client_leak_social_ref -> transaction_data_status = 1;
                    $transaction_client_leak_social_ref -> status = 1;
                    $transaction_client_leak_social_ref -> save();
                }else{
                    $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                    $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                    $transaction_client_leak_social_ref -> transaction_id = $DataLeakSocialRef->id;
                    $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                    $transaction_client_leak_social_ref -> transaction_data_status = 1;
                    $transaction_client_leak_social_ref -> status = 1;
                    $transaction_client_leak_social_ref -> save();
                }
            }
            DataLeakSocialRef::where('temp_id', $DataLeakFeedTemp->id)->delete();
            $DataLeakFeedTemp->approve = 0;
            $DataLeakFeedTemp->save();
        }

        if(@$type == 'social' || @$type == 'darkweb_public') {
            if($request->site){
                $site = route('darkweb_datas.index',['id'=>$request->site]);
            }else{
                $site = route('datafeed.index');
            }
        } else {
            if($request->site){
                $site = route('compromised_feed.index',['id'=>$request->site]);
            }else{
                $site = route('datafeed.darkweb_index');
            }
        }

        // if($request->site){
        //     $site = route('darkweb_datas.index',['id'=>$request->site]);
        // }else{
        //     $site = route('datafeed.index');
        // }
        
        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => @$site,
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

                if ($request->check_type) {
                    if($request->check_type==1){
                        $q->where('approve', '0');
                    }else if($request->check_type==2){
                        $q->where('approve', '1');
                    }
                }

                // if ($request->check_all == 'true') {

                // } else {
                //     if ($request->check_pending == 'true' && $request->check_approved == 'true') {

                //     } else if ($request->check_pending == 'true') {
                //         $q->where('approve', '0');
                //     } else if ($request->check_approved == 'true') {
                //         $q->where('approve', '1');
                //     }
                // }

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

                if ($request->check_type) {
                    if($request->check_type==1){
                        $q->where('approve', '0');
                    }else if($request->check_type==2){
                        $q->where('approve', '1');
                    }
                }
                // if ($request->check_all == 'true') {

                // } else {
                //     if ($request->check_pending == 'true' && $request->check_approved == 'true') {

                //     } else if ($request->check_pending == 'true') {
                //         $q->where('approve', '0');
                //     } else if ($request->check_approved == 'true') {
                //         $q->where('approve', '1');
                //     }
                // }

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
                    return '<div>' . $model->feedcontent . '</div>';
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

                    $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakFeed->id)->first();
                    if($transaction_client_leak_feed){
                        $transaction_client_leak_feed -> transaction_mode = 'insert';
                        $transaction_client_leak_feed -> transaction_data_status = 1;
                        $transaction_client_leak_feed -> status = 1;
                        $transaction_client_leak_feed -> save();
                    }else{
                        $transaction_client_leak_feed = new transaction_client_leak_feed();
                        $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                        $transaction_client_leak_feed -> transaction_id = $DataLeakFeed->id;
                        $transaction_client_leak_feed -> transaction_mode = 'insert';
                        $transaction_client_leak_feed -> transaction_data_status = 1;
                        $transaction_client_leak_feed -> status = 1;
                        $transaction_client_leak_feed -> save();
                    }
    
                    
                    $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakSocialRef->id)->first();
                    if($transaction_client_leak_social_ref){
                        $transaction_client_leak_social_ref -> transaction_mode = 'insert';
                        $transaction_client_leak_social_ref -> transaction_data_status = 1;
                        $transaction_client_leak_social_ref -> status = 1;
                        $transaction_client_leak_social_ref -> save();
                    }else{
                        $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                        $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                        $transaction_client_leak_social_ref -> transaction_id = $DataLeakSocialRef->id;
                        $transaction_client_leak_social_ref -> transaction_mode = 'insert';
                        $transaction_client_leak_social_ref -> transaction_data_status = 1;
                        $transaction_client_leak_social_ref -> status = 1;
                        $transaction_client_leak_social_ref -> save();
                    }
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
            $DataLeakFeeds = DataLeakFeed::where('temp_id', $DataLeakFeedTemp->id)->get();
            if($DataLeakFeeds){
                foreach($DataLeakFeeds as $DataLeakFeed){
                    $leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $DataLeakFeedTemp->id)->first();
                    $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakFeed->id)->first();
                    if($transaction_client_leak_feed){
                        $transaction_client_leak_feed -> transaction_mode = 'delete';
                        $transaction_client_leak_feed -> transaction_data_status = 1;
                        $transaction_client_leak_feed -> status = 1;
                        $transaction_client_leak_feed -> save();
                    }else{
                        $transaction_client_leak_feed = new transaction_client_leak_feed();
                        $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                        $transaction_client_leak_feed -> transaction_id = $DataLeakFeed->id;
                        $transaction_client_leak_feed -> transaction_mode = 'delete';
                        $transaction_client_leak_feed -> transaction_data_status = 1;
                        $transaction_client_leak_feed -> status = 1;
                        $transaction_client_leak_feed -> save();
                    }  
                }
            }
            $DataLeakSocialRefs = DataLeakSocialRef::where('temp_id', $DataLeakFeedTemp->id)->get();
            if($DataLeakSocialRefs){
                foreach($DataLeakSocialRefs as $DataLeakSocialRef){
                    $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $DataLeakSocialRef->id)->first();
                    if($transaction_client_leak_social_ref){
                        $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                        $transaction_client_leak_social_ref -> transaction_data_status = 1;
                        $transaction_client_leak_social_ref -> status = 1;
                        $transaction_client_leak_social_ref -> save();
                    }else{
                        $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                        $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                        $transaction_client_leak_social_ref -> transaction_id = $DataLeakSocialRef->id;
                        $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                        $transaction_client_leak_social_ref -> transaction_data_status = 1;
                        $transaction_client_leak_social_ref -> status = 1;
                        $transaction_client_leak_social_ref -> save();
                    }
                }
            }

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
            $model = DataLeakSocialRef::where('deleted_at', null)->where('site_id', $request->site_id)->whereIn('feel_type', ['darkweb', 'compromise', 'webserver', 'server'])->with('get_site')->with('get_data_leak_feed_one');

            // if($request -> keywords){
            //     $model = $model->where('source_name', 'LIKE', '%'.$request -> keywords.'%');
            // }

            if ($request->keywords) {

                // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
                $model = $model->where('keyword', 'LIKE', '%' . $request->keywords . '%');
                // });

            }

            if($request ->check_type) {

                $model = $model-> where('feel_type', '=' ,$request -> check_type);

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
            ->whereIn('feel_type', ['darkweb', 'compromise', 'webserver', 'server'])
            ->with('get_site')->with('get_data_leak_feed_one');

                $model->orderBy('id', 'desc');
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

        $data['Credentials'] = Credentials::where('site_id',$get_data->id)->get();

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

        
        if ($request->id_chang) {

            foreach ($request->id_chang as $id_chang) {
                $transaction_client_compromised_server = transaction_client_compromised_server::where('site_id', $request->site)->where('transaction_id', $id_chang)->first();
                if($transaction_client_compromised_server){
                    $transaction_client_compromised_server -> transaction_mode = 'delete';
                    $transaction_client_compromised_server -> transaction_data_status = 1;
                    $transaction_client_compromised_server -> status = 1;
                    $transaction_client_compromised_server -> save();
                }else{
                    $transaction_client_compromised_server = new transaction_client_compromised_server();
                    $transaction_client_compromised_server -> site_id = $request->site;
                    $transaction_client_compromised_server -> transaction_id = $id_chang;
                    $transaction_client_compromised_server -> transaction_mode = 'delete';
                    $transaction_client_compromised_server -> transaction_data_status = 1;
                    $transaction_client_compromised_server -> status = 1;
                    $transaction_client_compromised_server -> save();
                }
                $data = CompromisedServer::where("id", $id_chang)->delete();

            }
        } else {
            $transaction_client_compromised_server = transaction_client_compromised_server::where('site_id', $request->site)->where('transaction_id', $request->id)->first();
            if($transaction_client_compromised_server){
                $transaction_client_compromised_server -> transaction_mode = 'delete';
                $transaction_client_compromised_server -> transaction_data_status = 1;
                $transaction_client_compromised_server -> status = 1;
                $transaction_client_compromised_server -> save();
            }else{
                $transaction_client_compromised_server = new transaction_client_compromised_server();
                $transaction_client_compromised_server -> site_id = $request->site;
                $transaction_client_compromised_server -> transaction_id = $request->id;
                $transaction_client_compromised_server -> transaction_mode = 'delete';
                $transaction_client_compromised_server -> transaction_data_status = 1;
                $transaction_client_compromised_server -> status = 1;
                $transaction_client_compromised_server -> save();
            }
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
        $transaction_client_compromised_server = transaction_client_compromised_server::where('site_id', $request->site)->where('transaction_id', $request->id)->first();
        if($transaction_client_compromised_server){
            $transaction_client_compromised_server -> transaction_mode = 'update';
            $transaction_client_compromised_server -> transaction_data_status = 1;
            $transaction_client_compromised_server -> status = 1;
            $transaction_client_compromised_server -> save();
        }else{
            $transaction_client_compromised_server = new transaction_client_compromised_server();
            $transaction_client_compromised_server -> site_id = $request->site;
            $transaction_client_compromised_server -> transaction_id = $request->id;
            $transaction_client_compromised_server -> transaction_mode = 'update';
            $transaction_client_compromised_server -> transaction_data_status = 1;
            $transaction_client_compromised_server -> status = 1;
            $transaction_client_compromised_server -> save();
        }

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
            $data->credentials_id = $Credentials->id;

            $data->save();

            $transaction_client_compromised_server = new transaction_client_compromised_server();
            $transaction_client_compromised_server -> site_id = $request->site;
            $transaction_client_compromised_server -> transaction_id = $data->id;
            $transaction_client_compromised_server -> transaction_mode = 'insert';
            $transaction_client_compromised_server -> transaction_data_status = 1;
            $transaction_client_compromised_server -> status = 1;
            $transaction_client_compromised_server -> save();

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

                $transaction_client_asset = transaction_client_asset::where('site_id', $request->site)->where('transaction_id', $Assets->id)->first();
                if($transaction_client_asset){
                    $transaction_client_asset -> transaction_mode = 'insert';
                    $transaction_client_asset -> transaction_data_status = 1;
                    $transaction_client_asset -> status = 1;
                    $transaction_client_asset -> save();
                }

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

                $transaction_client_asset = new transaction_client_asset();
                $transaction_client_asset -> site_id = $request->site;
                $transaction_client_asset -> transaction_id = $Assets_new->id;
                $transaction_client_asset -> transaction_mode = 'insert';
                $transaction_client_asset -> transaction_data_status = 1;
                $transaction_client_asset -> status = 1;
                $transaction_client_asset -> save();

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

                $transaction_client_asset_data = new transaction_client_asset_data();
                $transaction_client_asset_data -> site_id = $request->site;
                $transaction_client_asset_data -> transaction_id = $AssetsData->id;
                $transaction_client_asset_data -> transaction_mode = 'insert';
                $transaction_client_asset_data -> transaction_data_status = 1;
                $transaction_client_asset_data -> status = 1;
                $transaction_client_asset_data -> save();

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
        $dataCredentials = [
            'ip' => $ip,
            'port' => $port,
            'user' => $user,
            'pass' => $pass,
            'os' => $os,
        ];
        $encodedDataCredentials = json_encode($dataCredentials);
        try {
            $transcation_jobs_clients_check = transcation_jobs_clients::where('site_id', $data->site_id)->where('mode', 'compromised_webserver_test_connection')->orderBy('created_at', 'desc')->first();
            if(empty($transcation_jobs_clients_check)){
                $transcation_jobs_clients_check = new transcation_jobs_clients();
                $transcation_jobs_clients_check  -> site_id = $data->site_id;
                $transcation_jobs_clients_check  -> job_key = str_random(24);
                $transcation_jobs_clients_check  -> mode = 'compromised_webserver_test_connection';
                $transcation_jobs_clients_check  -> status = 1;
                $transcation_jobs_clients_check  -> transaction_data_status = 1;
                $transcation_jobs_clients_check  -> data = $encodedDataCredentials;
                $transcation_jobs_clients_check  -> save();

                $status = true;
                $job_key = $transcation_jobs_clients_check -> job_key;
                $message = '';
            }else if($transcation_jobs_clients_check -> transaction_data_status === 3){
                $transcation_jobs_clients_check = new transcation_jobs_clients();
                $transcation_jobs_clients_check  -> site_id = $data->site_id;
                $transcation_jobs_clients_check  -> job_key = str_random(24);
                $transcation_jobs_clients_check  -> mode = 'compromised_webserver_test_connection';
                $transcation_jobs_clients_check  -> status = 1;
                $transcation_jobs_clients_check  -> transaction_data_status = 1;
                $transcation_jobs_clients_check  -> data = $encodedDataCredentials;
                $transcation_jobs_clients_check  -> save();

                $status = true;
                $job_key = $transcation_jobs_clients_check -> job_key;
                $message = '';
            }else{
                $status = false;
                $message = 'There is transaction information in the system, please wait a moment.';
                $job_key = null;
            }
            // if ($os == "Linux") {
            //     $ssh = new SSH2($ip, $port);
            //     $ssh->setTimeout(60);

            //     if (!$ssh->login($user, $pass)) {
            //         $checkConnect = false;
            //         $message = 'Connect Error';
            //     } else {
            //         $checkConnect = true;
            //         $message = 'Connect Success';
            //     }
            // } else if ($os == "Windows") {
            //     $checkConnect = false;
            //     $message = 'Connect Error';
            // } else {
            //     $checkConnect = false;
            //     $message = 'Connect Error';
            // }
        } catch (Exception $e) {
            $status = false;
            $message = $e->getMessage();
        }

        $dataout = [
            'status' => $status,
            'data' => $job_key,
            'message' => $message,
        ];
        return response()->json($dataout);
    }

    public function load_data_connection(Request $request){
        $transcation_jobs_clients_check = transcation_jobs_clients::where('transaction_data_status', 3)->where('job_key', $request->key)->first();
        if($transcation_jobs_clients_check){
            $encodedDataCredentials = json_decode($transcation_jobs_clients_check -> return_data, true);
            if($encodedDataCredentials['checkConnect'] === true){
                $dataout = [
                    'status' => 'complete',
                    'webserverConnect' => true,
                    'message' => $encodedDataCredentials['message'],
                    'data' => $transcation_jobs_clients_check -> job_key,
                ];
            }else{
                $dataout = [
                    'status' => 'complete',
                    'webserverConnect' => false,
                    'message' => $encodedDataCredentials['message'],
                    'data' => $transcation_jobs_clients_check -> job_key,
                ];
            }
        }else{
            $dataout = [
                'status' => 'waiting',
                'message' => '',
            ];
        }
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

    public function dark_web_datatables(Request $request)
    {
        $model = DataLeakFeedTemp::whereIn('feed_type', ['social','darkweb_public'])->with('get_socail_ref_temp');

        $site = SiteSettings::select('id')->where('code', $request->site)->first();

        $model->whereHas('get_socail_ref_temp', function ($query) use ($site) {

            $query->where('site_id', 'LIKE', $site->id);
        });
        
        // $model->whereHas('get_socail_ref_temp', function ($query) use ($site) {
        //     $query->where('site_id', $site->id);
        // })->get();
        // dd($model);
        if ($request->search_val == 1) {

            // $model = DataLeakFeedTemp::where('keyword', '!=', null)->where('keyword', '!=', '')->where('feed_type','social');
            // $model->whereHas('get_socail_ref_temp', function ($query) use ($request) {

            //     $query->where('site_id', 'LIKE', '%' . $request->site . '%');
            // });
 
            if ($request->search) {
                $model = $model->where('keyword', 'LIKE', '%' . $request->search . '%');
            }

            if ($request->source_select) {
                $model = $model->where('feed_type', $request->source_select);
            }

            if ($request->check_type) {
                if($request->check_type==1){
                    $model = $model->where('approve', '0');
                }else if($request->check_type==2){
                    $model = $model->where('approve', '1');
                }
            }
            // if ($request->check_all == 'true') {

            // } else {
            //     if ($request->check_pending == 'true') {
            //         $model = $model->where('approve', '0');
            //     }
            //     if ($request->check_approved == 'true') {
            //         $model = $model->where('approve', '1');
            //     }
            // }

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
                    return '<div>'. $model->feedcontent . '</div>';
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
            ->rawColumns(['chk', 'site', 'source', 'keyword', 'content', 'data_feed', 'url', 'action'])
            ->make(true);
    }

    public function add_compromise(Request $request)
    {
   
        $DataLeakFeed = new DataLeakFeed();
        $DataLeakFeed->code = generator_uuid();
        $DataLeakFeed->feel_type = @$request->type;
        // $DataLeakFeed->feedcontent = @$request->content;
        $DataLeakFeed->keyword = @$request->keyword;
        $DataLeakFeed->source_name = @$request->remark;
        $DataLeakFeed->feedtimepost = Carbon::now();
        $DataLeakFeed->status = 1;


        $content = @$_POST['content']; //รับค่าจาก messageInput
        if($content) {
            $dom = new \domdocument();
            if($dom->getelementsbytagname('img')){
                $dom->loadHtml('<?xml encoding="UTF-8">'.$content,
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
                        $img->setattribute('src', config('app.URL_CENTER_PUBLISH').'/images/file_editor/'.$image_name);
                    } else {

                    }
                }
                $content = $dom->savehtml();

            }
        }
        $DataLeakFeed->feedcontent = $content;







        $DataLeakFeed->save();
        $DataLeakFeed_send_mail[] = $DataLeakFeed;

            if($request->site){
                foreach($request->site as $site){
                    $SiteSettings = SiteSettings::where('code', $site)->first();
                    $DataLeakSocialRefs = new DataLeakSocialRef;
                    $DataLeakSocialRefs->code = generator_uuid();
                    $DataLeakSocialRefs->site_id = $SiteSettings->id;
                    $DataLeakSocialRefs->data_leak_feed_id = $DataLeakFeed->id;
                    $DataLeakSocialRefs->keyword = $DataLeakFeed->keyword;
                    $DataLeakSocialRefs->feel_type = $DataLeakFeed->feel_type;
                    $DataLeakSocialRefs->serverity = @$request->serverity;
                    $DataLeakSocialRefs->status = 1;
                    $DataLeakSocialRefs->save();
                    if ($request->sent_mail == true) {
                        $site_email_alert = site_config_email_alert::where("site_id", $SiteSettings->id)->get();
                        if ($site_email_alert) {
                            $email_site_a = [];
                            foreach ($site_email_alert as $site_email_alert_val) {
                                $email_site_a[] = $site_email_alert_val->email;
                            }
                            $email_site_alert = array_unique($email_site_a);
                            foreach($email_site_alert as $email){
                                Mail::to($email)->send(new CompromisedMail($DataLeakFeed_send_mail, 'compomise'));
                                if( count(Mail::failures()) == 0 ) {
                                    LogEmail::Create([
                                        'to' => $email,
                                        'status' => 'Success',
                                        'subject' => 'compomise'
                                    ]);
                                }
                            }
                            if( count(Mail::failures()) > 0 ) {
                                foreach(Mail::failures() as $email_address) {
                                    LogEmail::Create([
                                        'to' => $email_address,
                                        'status' => 'Fail',
                                        'subject' => 'compomise'
                                    ]);
                                }
                            }
                        }
                    }
                }
                $site = route('darkweb.index_all_site');
            }else{
                $SiteSettings = SiteSettings::where('code', @$request->site_code)->first();
                $DataLeakSocialRefs = new DataLeakSocialRef;
                $DataLeakSocialRefs->code = generator_uuid();
                $DataLeakSocialRefs->site_id = $SiteSettings->id;
                $DataLeakSocialRefs->data_leak_feed_id = $DataLeakFeed->id;
                $DataLeakSocialRefs->keyword = $DataLeakFeed->keyword;
                $DataLeakSocialRefs->feel_type = $DataLeakFeed->feel_type;
                $DataLeakSocialRefs->status_monitoring = @$request->monitoring;
                $DataLeakSocialRefs->serverity = @$request->serverity;
                $DataLeakSocialRefs->status = 1;
                $DataLeakSocialRefs->save();
                $site = route('compromised_data.index', ['code' => @$request->site_code]);
                if ($request->sent_mail == true) {
                    $site_email_alert = site_config_email_alert::where("site_id", $SiteSettings->id)->get();
                    if ($site_email_alert) {
                        $email_site_a = [];
                        foreach ($site_email_alert as $site_email_alert_val) {
                            $email_site_a[] = $site_email_alert_val->email;
                        }
                        $email_site_alert = array_unique($email_site_a);
                        foreach($email_site_alert as $email){
                            Mail::to($email)->send(new CompromisedMail($DataLeakFeed_send_mail, 'compomise'));
                            if( count(Mail::failures()) == 0 ) {
                                LogEmail::Create([
                                    'to' => $email,
                                    'status' => 'Success',
                                    'subject' => 'compomise'
                                ]);
                            }
                        }
                        if( count(Mail::failures()) > 0 ) {
                            foreach(Mail::failures() as $email_address) {
                                LogEmail::Create([
                                    'to' => $email_address,
                                    'status' => 'Fail',
                                    'subject' => 'compomise'
                                ]);
                            }
                        }
                    }
                }
            }


        

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => $site,
            ],
            true,
            Response::HTTP_OK
        );
    }
    public function edit_darkwebdata_modal($code,Request $request){

        
        $DataLeakSocialRefs = DataLeakSocialRef::where('code',$code)->first();
        $DataLeakFeed = DataLeakFeed::where('id',$DataLeakSocialRefs->data_leak_feed_id)->first();;
        $data['DataLeakFeed'] = $DataLeakFeed;
        $data['DataLeakSocialRefs'] = $DataLeakSocialRefs;


        $data['site'] = @$request->site;

        
        return view('sitesettings::modal.edit_compromise')->with($data);
    }

    public function edit_compromise(Request $request){
        $DataLeakFeed = DataLeakFeed::where('id',@$request->id_DataLeakFeed)->first();
        $DataLeakFeed->feel_type = @$request->type;
        // $DataLeakFeed->feedcontent = @$request->content;
        $DataLeakFeed->keyword = @$request->keyword;
        $DataLeakFeed->source_name = @$request->remark;
        if ($request->sent_mail == true) {
            $DataLeakFeed->feedtimepost = Carbon::now();
        }
        
        $content = @$_POST['content']; //รับค่าจาก messageInput
        if($content) {
            $dom = new \domdocument();
            if($dom->getelementsbytagname('img')){
                $dom->loadHtml('<?xml encoding="UTF-8">'.$content,
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
                        $img->setattribute('src', config('app.URL_CENTER_PUBLISH').'/images/file_editor/'.$image_name);
                    } else {

                    }
                }
                $content = $dom->savehtml();

            }
        }
        $DataLeakFeed->feedcontent = $content;
        $DataLeakFeed->save();
        $DataLeakFeed_send_mail[] = $DataLeakFeed;

        $DataLeakSocialRefs = DataLeakSocialRef::where('data_leak_feed_id',$DataLeakFeed->id)->get();
            if($DataLeakSocialRefs){
                foreach($DataLeakSocialRefs as $DataLeakSocialRefs){
                    $DataLeakSocialRefs->keyword = $DataLeakFeed->keyword;
                    $DataLeakSocialRefs->feel_type = $DataLeakFeed->feel_type;
                    $DataLeakSocialRefs->serverity = @$request->serverity;
                    $DataLeakSocialRefs->save();
                    if ($request->sent_mail == true) {
                        $site_email_alert = site_config_email_alert::where("site_id", $DataLeakSocialRefs->site_id)->get();
                        if ($site_email_alert) {
                            $email_site_a = [];
                            foreach ($site_email_alert as $site_email_alert_val) {
                                $email_site_a[] = $site_email_alert_val->email;
                            }
                            $email_site_alert = array_unique($email_site_a);
                            foreach($email_site_alert as $email){
                                Mail::to($email)->send(new CompromisedMail($DataLeakFeed_send_mail, 'compomise'));
                                if( count(Mail::failures()) == 0 ) {
                                    LogEmail::Create([
                                        'to' => $email,
                                        'status' => 'Success',
                                        'subject' => 'compomise'
                                    ]);
                                }
                            }
                            if( count(Mail::failures()) > 0 ) {
                                foreach(Mail::failures() as $email_address) {
                                    LogEmail::Create([
                                        'to' => $email_address,
                                        'status' => 'Fail',
                                        'subject' => 'compomise'
                                    ]);
                                }
                            }
                        }
                    }
                }
                
            }
            if($request->site_code){
                $site = route('compromised_data.index', ['code' => @$request->site_code]);
            }else{
                $site = route('darkweb.index_all_site');
            }
             
        

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => $site,
            ],
            true,
            Response::HTTP_OK
        );

    }

    public function add_dataleak(Request $request)
    {

        $keyword = @$request->keyword;
        $other = @$request->other;
        if($keyword == 'Other') {
            $keyword_i = @$other;
        } else {
            $keyword_i = @$keyword;
        }
   
        $DataLeakFeed = new DataLeakFeed();
        $DataLeakFeed->code = generator_uuid();
        $DataLeakFeed->feel_type = @$request->type;
        // $DataLeakFeed->feedcontent = @$request->content;

        $DataLeakFeed->keyword = @$keyword_i;

        $DataLeakFeed->source_name = @$request->source;
        $DataLeakFeed->feedtimepost = Carbon::now();
        $DataLeakFeed->status = 1;
        $content = @$_POST['content']; //รับค่าจาก messageInput
        if($content) {
            $dom = new \domdocument();
            if($dom->getelementsbytagname('img')){
                $dom->loadHtml('<?xml encoding="UTF-8">'.$content,
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
                        $img->setattribute('src', config('app.URL_CENTER_PUBLISH').'/images/file_editor/'.$image_name);
                    } else {

                    }
                }
                $content = $dom->savehtml();

            }
        }
        $DataLeakFeed->feedcontent = $content;
        $DataLeakFeed->save();
        $DataLeakFeed_send_mail[] = $DataLeakFeed;

            if($request->site){
                foreach($request->site as $site){
                    $SiteSettings = SiteSettings::where('code', $site)->first();
                    $DataLeakSocialRefs = new DataLeakSocialRef;
                    $DataLeakSocialRefs->code = generator_uuid();
                    $DataLeakSocialRefs->site_id = $SiteSettings->id;
                    $DataLeakSocialRefs->data_leak_feed_id = $DataLeakFeed->id;
                    $DataLeakSocialRefs->keyword = $DataLeakFeed->keyword;
                    $DataLeakSocialRefs->feel_type = $DataLeakFeed->feel_type;
                    $DataLeakSocialRefs->status_monitoring = @$request->monitoring;
                    $DataLeakSocialRefs->serverity = @$request->serverity;
                    $DataLeakSocialRefs->status = 1;
                    $DataLeakSocialRefs->save();
                    if ($request->sent_mail == true) {
                        $site_email_alert = site_config_email_alert::where("site_id", $SiteSettings->id)->get();
                        if ($site_email_alert) {
                            $email_site_a = [];
                            foreach ($site_email_alert as $site_email_alert_val) {
                                $email_site_a[] = $site_email_alert_val->email;
                            }
                            $email_site_alert = array_unique($email_site_a);
                            foreach($email_site_alert as $email){
                                Mail::to($email)->send(new CompromisedMail($DataLeakFeed_send_mail, 'data_leak'));
                                if( count(Mail::failures()) == 0 ) {
                                    LogEmail::Create([
                                        'to' => $email,
                                        'status' => 'Success',
                                        'subject' => 'data_leak'
                                    ]);
                                }
                            }
                            if( count(Mail::failures()) > 0 ) {
                                foreach(Mail::failures() as $email_address) {
                                    LogEmail::Create([
                                        'to' => $email_address,
                                        'status' => 'Fail',
                                        'subject' => 'data_leak'
                                    ]);
                                }
                            }
                        }
                    }
                }
                $site = route('socialdatas.index_all_site');
            }else{
                $SiteSettings = SiteSettings::where('code', @$request->site_code)->first();
                $DataLeakSocialRefs = new DataLeakSocialRef;
                $DataLeakSocialRefs->code = generator_uuid();
                $DataLeakSocialRefs->site_id = $SiteSettings->id;
                $DataLeakSocialRefs->data_leak_feed_id = $DataLeakFeed->id;
                $DataLeakSocialRefs->keyword = $DataLeakFeed->keyword;
                $DataLeakSocialRefs->feel_type = $DataLeakFeed->feel_type;
                $DataLeakSocialRefs->status_monitoring = @$request->monitoring;
                $DataLeakSocialRefs->serverity = @$request->serverity;
                $DataLeakSocialRefs->status = 1;
                $DataLeakSocialRefs->save();
                $site = route('socialdatas.index', ['id' => @$request->site_code]);
                if ($request->sent_mail == true) {
                    $site_email_alert = site_config_email_alert::where("site_id", $SiteSettings->id)->get();
                    if ($site_email_alert) {
                        $email_site_a = [];
                        foreach ($site_email_alert as $site_email_alert_val) {
                            $email_site_a[] = $site_email_alert_val->email;
                        }
                        $email_site_alert = array_unique($email_site_a);
                        foreach($email_site_alert as $email){
                            Mail::to($email)->send(new CompromisedMail($DataLeakFeed_send_mail, 'data_leak'));
                            if( count(Mail::failures()) == 0 ) {
                                LogEmail::Create([
                                    'to' => $email,
                                    'status' => 'Success',
                                    'subject' => 'data_leak'
                                ]);
                            }
                        }
                        if( count(Mail::failures()) > 0 ) {
                            foreach(Mail::failures() as $email_address) {
                                LogEmail::Create([
                                    'to' => $email_address,
                                    'status' => 'Fail',
                                    'subject' => 'data_leak'
                                ]);
                            }
                        }
                    }
                }
            }


        

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => $site,
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function edit_dataleak_modal($code,Request $request){
        $DataLeakSocialRefs = DataLeakSocialRef::where('code',$code)->first();
        $DataLeakFeed = DataLeakFeed::where('id',$DataLeakSocialRefs->data_leak_feed_id)->first();;
        $data['DataLeakFeed'] = $DataLeakFeed;
        $data['DataLeakSocialRefs'] = $DataLeakSocialRefs;

        $data['site'] = @$request->site;

        
        return view('sitesettings::modal.edit_dataleak')->with($data);
    }

    public function activity_dataleak_modal($code,Request $request){
        $DataLeakSocialRefs = DataLeakSocialRef::where('code',$code)->first();
        $ActivityHistory = Activity::select('activity.*','users.name as users_name')->where('activity.data_leak_socail_ref_id',$DataLeakSocialRefs->id)->whereNull('activity.deleted_at')->leftJoin('users', 'activity.user_id', '=', 'users.id')->orderBy('created_at','desc')->get();
        // $DataLeakFeed = DataLeakFeed::where('id',$DataLeakSocialRefs->data_leak_feed_id)->first();
        $data['DataLeakSocialRefs'] = $DataLeakSocialRefs;
        $data['site'] = @$request->site;
        $data['ActivityHistory'] = $ActivityHistory;
        
        return view('sitesettings::modal.activity_dataleak_modal')->with($data);
    }

    public function activity_get_edit_data(Request $request){
        $Activity = Activity::where('id',$request->code_activity)->first()->toArray();
        if ($request->ajax()) {
            $data = [
                "ActivityHistory" => $Activity,
            ];
            return response()->json($data);
        }
    }

    public function activity_history_reload(Request $request){
        $DataLeakSocialRefs = DataLeakSocialRef::where('id',$request->code)->first();
        $ActivityHistory = Activity::select('activity.*','users.name as users_name')->where('activity.data_leak_socail_ref_id',$DataLeakSocialRefs->id)->whereNull('activity.deleted_at')->leftJoin('users', 'activity.user_id', '=', 'users.id')->orderBy('created_at','desc')->get();
        $html = '';
        if(!empty($ActivityHistory)){
            foreach ($ActivityHistory as $key => $value) {
                    $html_status_activity = '';
                    $activity_color = '';
                    $activity_name = '';
                    if($value->status_activity) {
                        if($value->status_activity == 'in_progress') {
                            $activity_color = '#FFC107';
                            $activity_name = 'Progress';
                        } else if ($value->status_activity == 'reported') {
                            $activity_color = '#28A745';
                            $activity_name = 'Reported';
                        } else if ($value->status_activity == 'close') {
                            $activity_color = '#DC3545';
                            $activity_name = 'Close';
                        }
                        $html_status_activity = '<span class="badge" style="background-color: '.$activity_color.'; display: block;">'.$activity_name.'</span>';
                    }
                $html .= '
                <li class="work" id="list_activity_'.$value->code.'">
                    <input class="radio" id="work_'.$key.'" name="works" type="radio">
                    <div class="relative">
                        <label for="work_'.$key.'" class="label_custom" style="font-weight: 900;">'.$value->title.'</label>
                        <span class="date_custom" style="text-align:center;">'.$value->updated_at.$html_status_activity.'</span>
                        <span class="circle_custom"></span>
                    </div>
                    <div class="content_custom">
                        <p>
                            '.$value->content.'
                        </p>
                    </div>
                    <div>
                        <strong>Post By</strong> '.$value->users_name;
                    if(TYPE_WEB == 'center'){
                        $html .= '
                        <span class="float-right">
                            <a href="#gototop" class="btn btn-info btn-xs disable_atag" onclick="edit_activity( \''. $value->id .'\');">
                                <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                            </a>
                            <a href="javascript:void(0)" class="btn btn-danger btn-xs disable_atag" onclick="delete_activity(\''.$value->id.'\',\''.$value->code.'\');"><i class="fas fa-trash-alt"></i></a>
                        </span>';
                        
                    }else{
                        if($value->user_id==Auth::user()->id){
                            $html .= '
                            <span class="float-right">
                                <a href="#gototop" class="btn btn-info btn-xs disable_atag" onclick="edit_activity( \''. $value->id .'\');">
                                    <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                                </a>
                                <a href="javascript:void(0)" class="btn btn-danger btn-xs disable_atag" onclick="delete_activity(\''.$value->id.'\',\''.$value->code.'\');"><i class="fas fa-trash-alt"></i></a>
                            </span>';
                        }
                    }
                $html .= '
                    </div>
                </li>';
            }
        }else{
            $html .= '<li class="list-group-item"> </li>';
        }

        
        if ($request->ajax()) {
            $data = [
                "html" => $html,
                "ActivityHistory" => $ActivityHistory,
            ];
            return response()->json($data);
        }
    }  

    public function activity_save(Request $request){
        if(!$request->title&&!@$_POST['content']){
            return response()->json(['message' => 'You have to fill Title', 'errors' => ['missing' => ["You have to fill Title"],'missing2' => ["You have to fill content"]]], 500);
        }else if(!$request->title){
            return response()->json(['message' => 'You have to fill Title', 'errors' => ['missing' => ["You have to fill Title"]]], 500);
        }else if(!@$_POST['content']){
            return response()->json(['message' => 'You have to fill content', 'errors' => ['missing' => ["You have to fill content"]]], 500);
        }

        $status_activity = $request->status_activity;
        $content = @$_POST['content']; //รับค่าจาก messageInput
        if($content) {
            $dom = new \domdocument();
            if($dom->getelementsbytagname('img')){
                $dom->loadHtml('<?xml encoding="UTF-8">'.$content,
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
                        $img->setattribute('src', config('app.URL_CENTER_PUBLISH').'/images/file_editor/'.$image_name);
                    } else {

                    }
                }
                $content = $dom->savehtml();

            }
        }

        if($request->check_active=="1"){
            $Activity = new Activity;
            $Activity->code = generator_uuid();
            $Activity->data_leak_socail_ref_id = $request->id_DataLeakSocialRefs;
            $Activity->title = $request->title;
            $Activity->content = $content;
            $Activity->user_id = Auth::user()->id;
            if($status_activity) {
                $Activity->status_activity = $status_activity;
            }
            $Activity->save();
        }else if($request->check_active=="2"){
            $Activity = Activity::where('id',$request->code_edited_activity)->first();
            $Activity->title = $request->title;
            $Activity->content = $content;
            if($status_activity) {
                $Activity->status_activity = $status_activity;
            }
            $Activity->save();
        }

        if($request->site_code){
            $site = route('socialdatas.index', ['id' => @$request->site_code]);
        }else{
            $site = route('socialdatas.index_all_site');
        }

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => $site,
                'socail_ref_id' => $Activity->data_leak_socail_ref_id,
            ],
            true,
            Response::HTTP_OK
        );

    }

    public function activity_delete(Request $request){
        if($request->site_code){
            $site = route('socialdatas.index', ['id' => @$request->site_code]);
        }else{
            $site = route('socialdatas.index_all_site');
        }
        $Activity = Activity::where('id',$request->code_activity)->first();
        $Activity->delete();
        if($Activity){
            return ajaxResponse(
                [
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => $site,
                ],
                true,
                Response::HTTP_OK
            );
        }else{
            return response()->json(['message' => 'Error Delete Activity Please Contact Admin', 'errors' => ['missing' => ["Error Delete Activity Please Contact Admin"]]], 500);
        }  
    }
    
    public function edit_dataleak(Request $request){
        $DataLeakFeed = DataLeakFeed::where('id',@$request->id_DataLeakFeed)->first();
        $DataLeakFeed->feel_type = @$request->type;
        // $DataLeakFeed->feedcontent = @$request->content;
        if(@$request->other){
            $DataLeakFeed->keyword = @$request->other;
        }else{
            $DataLeakFeed->keyword = @$request->keyword;
        }
        
        $DataLeakFeed->source_name = @$request->source;
        if ($request->sent_mail == true) {
            $DataLeakFeed->feedtimepost = Carbon::now();
        }
        $content = @$_POST['content']; //รับค่าจาก messageInput
        if($content) {
            $dom = new \domdocument();
            if($dom->getelementsbytagname('img')){
                $dom->loadHtml('<?xml encoding="UTF-8">'.$content,
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
                        $img->setattribute('src', config('app.URL_CENTER_PUBLISH').'/images/file_editor/'.$image_name);
                    } else {

                    }
                }
                $content = $dom->savehtml();

            }
        }
        $DataLeakFeed->feedcontent = $content;        
        $DataLeakFeed->save();
        $DataLeakFeed_send_mail[] = $DataLeakFeed;

        $DataLeakSocialRefs = DataLeakSocialRef::where('data_leak_feed_id',$DataLeakFeed->id)->get();
            if($DataLeakSocialRefs){
                foreach($DataLeakSocialRefs as $DataLeakSocialRefs){
                    $DataLeakSocialRefs->keyword = $DataLeakFeed->keyword;
                    $DataLeakSocialRefs->feel_type = $DataLeakFeed->feel_type;
                    $DataLeakSocialRefs->status_monitoring = @$request->monitoring;
                    $DataLeakSocialRefs->serverity = @$request->serverity;
                    $DataLeakSocialRefs->save();
                    if ($request->sent_mail == true) {
                        $site_email_alert = site_config_email_alert::where("site_id", $DataLeakSocialRefs->site_id)->get();
                        if ($site_email_alert) {
                            $email_site_a = [];
                            foreach ($site_email_alert as $site_email_alert_val) {
                                $email_site_a[] = $site_email_alert_val->email;
                            }
                            $email_site_alert = array_unique($email_site_a);
                            foreach($email_site_alert as $email){
                                Mail::to($email)->send(new CompromisedMail($DataLeakFeed_send_mail, 'data_leak'));
                                if( count(Mail::failures()) == 0 ) {
                                    LogEmail::Create([
                                        'to' => $email,
                                        'status' => 'Success',
                                        'subject' => 'data_leak'
                                    ]);
                                }
                            }
                            if( count(Mail::failures()) > 0 ) {
                                foreach(Mail::failures() as $email_address) {
                                    LogEmail::Create([
                                        'to' => $email_address,
                                        'status' => 'Fail',
                                        'subject' => 'data_leak'
                                    ]);
                                }
                            }
                        }
                    }
                }
                
            }

            if($request->site_code){
                $site = route('socialdatas.index', ['id' => @$request->site_code]);
            }else{
                $site = route('socialdatas.index_all_site');
            }

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => $site,
            ],
            true,
            Response::HTTP_OK
        );

    }

    public function count_keyword(Request $request){
        $model = DataLeakSocialRef::select('keyword',DB::raw('count(*)  as count_keyword'))
        ->where('status',1)->where('deleted_at',null)
        ->whereIn('feel_type', ['social', 'darkweb_public'])
        ->groupBy('keyword');
        
        if ($request->site) {
                
            $SiteSettings = SiteSettings::where('code', @$request->site)->first();
            // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
            $model = $model->where('site_id', $SiteSettings->id);
            // });
       
        }


        $model = $model->get()->toArray();

        return ajaxResponse(
            [
                'model' => $model,
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function count_keyword_darkweb(Request $request){
        $model = DataLeakSocialRef::select('keyword',DB::raw('count(*)  as count_keyword'))
        ->where('status',1)->where('deleted_at',null)
        ->whereIn('feel_type', ['darkweb', 'compromise', 'webserver', 'server'])
        ->groupBy('keyword');

        if ($request->site) {
                
            $SiteSettings = SiteSettings::where('code', @$request->site)->first();
            // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
            $model = $model->where('site_id', $SiteSettings->id);
            // });
       
        }


        $model = $model->get()->toArray();

        return ajaxResponse(
            [
                'model' => $model,
            ],
            true,
            Response::HTTP_OK
        );
    }

    

}
