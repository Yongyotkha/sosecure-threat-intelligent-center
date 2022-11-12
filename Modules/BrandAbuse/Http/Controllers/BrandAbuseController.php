<?php

namespace Modules\BrandAbuse\Http\Controllers;

use App\Credentials;
use App\BrandAbuseFeed;
use App\BrandAbuseFeedTemp;
use App\BrandAbuseSocial;
use App\BrandAbuseSocialRef;
use App\BrandAbuseSocialRefTemp;
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

class BrandAbuseController extends Controller
{
    protected $item;
    protected $siteSettings;

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
    public function index()
    {
        return view('brandabuse::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('brandabuse::create');
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
        return view('brandabuse::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('brandabuse::edit');
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

    public function count_val(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['data_leak']) {
            check_permission403();
        }

        // dd($request->check_type);

        // $where1 = ['deleted_at' => null, 'feel_type' => 'darkweb'];
        // $where = ['deleted_at' => null];
        // $orwhere = ['deleted_at' => null, 'feel_type' => 'compromise'];
        // $orwhere2 = ['deleted_at' => null, 'feel_type' => 'webserver'];
        // $orwhere3 = ['deleted_at' => null, 'feel_type' => 'server'];
        

        $date_start = $request->startDate;
        $date_end = $request->startDate;
        $site_id = '';
        $site_code = $request ->site_id;

        if($site_code) {
            $site_id_m = SiteSettings::where('code',$site_code)->first();
            $site_id = @$site_id_m->id;
        }

        $title = $request ->title;
        $social = $request ->social;
       

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

        $html = '';

        

        if(  $request -> search_val == 1){

            $model = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->with('get_site')->with('get_data_leak_feed_one');
            $countGroupBy = BrandAbuseSocialRef::where('deleted_at', null)->where('status',1)->whereIn('feel_type', ['social', 'darkweb_public'])->with('get_site')->with('get_data_leak_feed_one');

            if ($request->keywords) {
                $keywords = $request->keywords;
                $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($keywords) {
                    $query->where('keyword', 'LIKE', '%' . $keywords . '%')
                        ->orWhere('feedcontent', 'LIKE', '%' . $keywords . '%');
                });

                $countGroupBy->whereHas('get_brand_abuse_feed_one', function ($query) use ($keywords) {
                    $query->where('keyword', 'LIKE', '%' . $keywords . '%')
                        ->orWhere('feedcontent', 'LIKE', '%' . $keywords . '%');
                });
            }

            if($request -> check_type) {
                $model = $model-> where('feel_type', '=' ,$request -> check_type);
                $countGroupBy = $countGroupBy -> where('feel_type', '=' ,$request -> check_type);
            }

                if($request -> isDateSearch==1){

                    $countGroupBy = $countGroupBy->whereHas('get_brand_abuse_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
                        $qq->whereBetween('feedtimepost', array($date_start_datetime_format, $date_end_datetime_format));
                    });

                    $model = $model->whereHas('get_brand_abuse_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
                        $qq->whereBetween('feedtimepost', array($date_start_datetime_format, $date_end_datetime_format));
                    });
                
                }

            if(Auth::check()) {

                $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
                if(Auth::user()->hasRole('admin')) {//if admin
                    // dd(777);
                    
    
                } else { //if notAdmin
                    // dd(888);
                    if(@Auth::user()->site_role_id && @Auth::user()->site_id) {
                        if(@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) {//support and admin
                            // dd(99);
    
                            $model = $model->whereIn('site_id', $site_id_arr);
            
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                 
    
                        } else {//not support and admin
                            $model = $model->whereIn('site_id', $site_id_arr);
            
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                        }
                    }
                }
            }



            if($site_id) {
                $model = $model->where('site_id', $site_id);

                $countGroupBy = $countGroupBy->where('site_id', $site_id);
            }

            $Data_leak_feed_all = $model->count();
            $countGroupBy = $countGroupBy->select( 'feel_type',DB::raw('count(*) as total'))->groupBy('feel_type')->get();
            $model = $model->with('get_brand_abuse_feed_one')->orderBy('id','desc')->paginate(PAGINATE_NUM);
        }else{
            $Data_leak_feed_all = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('feel_type', ['social','darkweb_public'])->count();
            $news = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('feel_type', ['social', 'darkweb_public']);//->get()
            $countGroupBy = BrandAbuseSocialRef::select( 'feel_type',DB::raw('count(*) as total'))->where('deleted_at', null)->whereIn('feel_type', ['social', 'darkweb_public'])->groupBy('feel_type');
            //remove all ->where('status',1)

            if(Auth::check()) {

                $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
                if(Auth::user()->hasRole('admin')) {//if admin
                    // dd(777);
                    
                } else { //if notAdmin
                    // dd(888);
                    if(@Auth::user()->site_role_id && @Auth::user()->site_id) {
                        if(@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) {//support and admin
                            // dd(99);
    
                            $news = $news->whereIn('site_id', $site_id_arr);
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                 
                        } else {//not support and admin
                            $news = $news->whereIn('site_id', $site_id_arr);
                            $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                        }
                    }
                }
            }

            if($site_id) {
                $news = $news->where('site_id', $site_id);
                $countGroupBy = $countGroupBy->where('site_id', $site_id);
            }

            $news = $news->with('get_brand_abuse_feed_one')->orderBy('id','desc')->paginate(PAGINATE_NUM);
            $countGroupBy = $countGroupBy->get();


        }

        // dd($news);
        // $content = [];

        // dd($content);
        $count_sub_type["darkweb"] = 0;
        $count_sub_type["social"] = 0;
        
        foreach ($countGroupBy as $countGroup) {
            $count_sub_type[$countGroup->feel_type] = $countGroup->total;
        }
        if ($request->ajax()) {
            $data = [
                "html" => $html,
                "count" => $Data_leak_feed_all,
                "darkweb" => @$count_sub_type["darkweb_public"],
                "social" => @$count_sub_type["social"],
            ];
            return response()->json($data); 
        }
    }

    public function count_icon(Request $request){
        $site_code = $request->site_id;
        if($site_code) {
            $SiteSettings = SiteSettings::where('code',$site_code)->first();
        }
        
        $get_role_custom_first = @get_role_custom();
        $site_id_arr = @$get_role_custom_first['site_id_arr'];
        if(@$get_role_custom_first['superadmin'] == 1) {
            if(!$site_code) {
                $icon_mobile = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('mobile')).'%'])->count();//->get()
                $icon_facebook = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('facebook')).'%'])->count();//->get()
                $icon_line = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('line')).'%'])->count();//->get()
                $icon_twitter = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('twitter')).'%'])->count();//->get()
                $icon_website = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('website')).'%'])->count();//->get()
                $icon_other = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('mobile'))])
                                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('facebook'))])
                                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('line'))])
                                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('twitter'))])
                                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                $number_in_progress = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('in_progress')).'%'])->count();//->get()
                $number_reported = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('reported')).'%'])->count();//->get()
                $number_close = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('close')).'%'])->count();//->get()
                //remove all ->where('status',1)
            } else {
                $icon_mobile = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('mobile')).'%'])->count();//->get()
                $icon_facebook = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('facebook')).'%'])->count();//->get()
                $icon_line = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('line')).'%'])->count();//->get()
                $icon_twitter = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('twitter')).'%'])->count();//->get()
                $icon_website = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('website')).'%'])->count();//->get()
                $icon_other = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('mobile'))])
                                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('facebook'))])
                                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('line'))])
                                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('twitter'))])
                                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                $number_in_progress = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('in_progress')).'%'])->count();//->get()
                $number_reported = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('reported')).'%'])->count();//->get()
                $number_close = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('close')).'%'])->count();//->get()
                //remove all ->where('status',1)
            }
        } else {
            if(!$site_code) {
                $icon_mobile = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('mobile')).'%'])->count();//->get()
                $icon_facebook = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('facebook')).'%'])->count();//->get()
                $icon_line = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('line')).'%'])->count();//->get()
                $icon_twitter = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('twitter')).'%'])->count();//->get()
                $icon_website = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('website')).'%'])->count();//->get()
                $icon_other = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('mobile'))])
                                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('facebook'))])
                                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('line'))])
                                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('twitter'))])
                                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                $number_in_progress = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('in_progress')).'%'])->count();//->get()
                $number_reported = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('reported')).'%'])->count();//->get()
                $number_close = BrandAbuseSocialRef::where('deleted_at', null)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('close')).'%'])->count();//->get()
                //remove all ->where('status',1)
            } else {
                $icon_mobile = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('mobile')).'%'])->count();//->get()
                $icon_facebook = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('facebook')).'%'])->count();//->get()
                $icon_line = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('line')).'%'])->count();//->get()
                $icon_twitter = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('twitter')).'%'])->count();//->get()
                $icon_website = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) LIKE ? ',[trim(strtolower('website')).'%'])->count();//->get()
                $icon_other = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('mobile'))])
                                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('facebook'))])
                                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('line'))])
                                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('twitter'))])
                                                                                                                                                  ->whereRaw('LOWER(`keyword`) != ? ',[trim(strtolower('website'))])->count();
                $number_in_progress = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('in_progress')).'%'])->count();//->get()
                $number_reported = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('reported')).'%'])->count();//->get()
                $number_close = BrandAbuseSocialRef::where('deleted_at', null)->where('site_id',$SiteSettings->id)->whereIn('site_id',$site_id_arr)->whereIn('feel_type', ['social', 'darkweb_public'])->whereRaw('LOWER(`status_monitoring`) LIKE ? ',[trim(strtolower('close')).'%'])->count();//->get()
                //remove all ->where('status',1)
            }
        }
        
    
        // $news = $news->with('get_brand_abuse_feed_one')->orderBy('id','desc')->paginate(PAGINATE_NUM);
        

        if ($request->ajax()) {
            $data = [
                "icon_mobile" => @$icon_mobile,
                "icon_facebook" => @$icon_facebook,
                "icon_line" => @$icon_line,
                "icon_twitter" => @$icon_twitter,
                "icon_website" => @$icon_website,
                "icon_other" => @$icon_other,
                "number_in_progress" => @$number_in_progress,
                "number_reported" => @$number_reported,
                "number_close" => @$number_close,
            ];
            return response()->json($data); 
        }

    }

    // brandabuse ------------------------------------------------------------------------------------------------------------------------------------

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
        $data['source'] = BrandAbuseSocial::where("status", '=', 1)->get();

        $data['page'] = langapp('brand_abuse');
        return view('brandabuse::social-datas_all_site')->with($data);

    }

    public function socialdatas_all_site_tb(Request $request) //
    {
    
        $model = BrandAbuseSocialRef::where('deleted_at', null)
            ->whereHas('get_brand_abuse_feed_one', function ($query) {
                $query->whereIn('feel_type', ['social','darkweb_public']);
            })
            ->with('get_site')
            ->with('get_brand_abuse_feed_one');

        $BrandAbuseSocialRef_data = BrandAbuseSocialRef::join('brand_abuse_feed', 'brand_abuse_socail_ref.brand_abuse_feed_id', '=', 'brand_abuse_feed.id')
            ->whereIn('brand_abuse_feed.feel_type', ['social','darkweb_public'])->join('site','site.id','brand_abuse_socail_ref.site_id')
            ->select('brand_abuse_socail_ref.*','brand_abuse_feed.*','site.name as site_name','brand_abuse_socail_ref.code as code_data','brand_abuse_socail_ref.id as id_data','brand_abuse_socail_ref.status as status_data');

        if ($request->search_val == 1) {

            $BrandAbuseFeed_Data =   BrandAbuseFeed::where('deleted_at', null)->where('status','1')->whereIn('feel_type', ['social','darkweb_public']);
            $get_role_custom_first = @get_role_custom();
            $site_id_arr = @$get_role_custom_first['site_id_arr'];
            if(@$get_role_custom_first['superadmin'] == 1) {

            }else if(@$get_role_custom_first['client'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr);
                $BrandAbuseSocialRef_data->whereIn('brand_abuse_socail_ref.site_id', $site_id_arr)->where('brand_abuse_socail_ref.status', 1);
                $BrandAbuseFeed_Data->whereIn('site_id', $site_id_arr);

            }else if(@$get_role_custom_first['site_support'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr);
                $BrandAbuseSocialRef_data->whereIn('brand_abuse_socail_ref.site_id', $site_id_arr);
                $BrandAbuseFeed_Data->whereIn('site_id', $site_id_arr);

            }else if(@$get_role_custom_first['site_admin'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr);
                $BrandAbuseSocialRef_data->whereIn('brand_abuse_socail_ref.site_id', $site_id_arr);
                $BrandAbuseFeed_Data->whereIn('site_id', $site_id_arr);


            }else if(@$get_role_custom_first['site_client'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr)->where('brand_abuse_feed.status', 1);
                $BrandAbuseSocialRef_data->whereIn('brand_abuse_socail_ref.site_id', $site_id_arr)->where('fx_brand_abuse_feed.status', 1);
                $BrandAbuseFeed_Data->whereIn('site_id', $site_id_arr);

            }

            
        
           
            if ($request->keywords) {
                $keywords = $request->keywords;

                $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($keywords) {
                    $query->where('keyword', 'LIKE', '%' . $keywords . '%');
                });

                $BrandAbuseSocialRef_data->whereRaw('(LOWER(fx_brand_abuse_feed.keyword) LIKE ? or LOWER(fnStripTags(entity_decode(fx_brand_abuse_feed.feedcontent))) LIKE ? )', array([trim(strtolower('%' .$request->keywords.'%'))],[trim(strtolower('%' .$request->keywords.'%'))]));
            }

            if ($request->site) {
                $SiteSettings = SiteSettings::where('code', @$request->site)->first();

                $model = $model->where('site_id', $SiteSettings->id);
                $BrandAbuseSocialRef_data->where('brand_abuse_socail_ref.site_id',$SiteSettings->id);
            }

            if ($request->type) {

                $type = $request->type;
                $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($type) {
                    $query->where('feel_type', 'LIKE', '%' . $type . '%');
                });
                $BrandAbuseSocialRef_data->where('brand_abuse_feed.feel_type', 'LIKE', '%' . $type . '%');
            }

            if ($request->check_type) {
              
                $type = $request->check_type;
                $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($type) {
                    $query->where('feel_type', 'LIKE', '%' . $type . '%');
                });
                $BrandAbuseSocialRef_data->where('brand_abuse_feed.feel_type', 'LIKE', '%' . $type . '%');
            }

            if ($request->check_social && $request->check_type =="social") {
              
                $check_social = $request->check_social;
                $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($check_social) {
                    if($check_social =="other"){
                        $query->whereNotIn('keyword', ['Mobile','Facebook','Line','Twitter','Website']);
                    }else{
                        $query->where('keyword', $check_social);
                    }
                  
                });
                if($check_social =="other"){
                         $BrandAbuseSocialRef_data->whereNotIn('brand_abuse_feed.keyword', ['Mobile','Facebook','Line','Twitter','Website']);
                }else{
                       $BrandAbuseSocialRef_data->where('brand_abuse_feed.keyword', $check_social);

                }
            }



            if ($request->source) {

                $source = $request->source;
                $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($source) {
                    $query->where('sourceid', 'LIKE', '%' . $source . '%');
                });

                $BrandAbuseSocialRef_data->where('brand_abuse_feed.sourceid', 'LIKE', '%' . $source . '%');

            }

            if ($request->check_serverity) {
                $model = $model->where('serverity', $request->check_serverity);
                $BrandAbuseSocialRef_data->where('brand_abuse_socail_ref.serverity', 'LIKE', '%' . $request->check_serverity . '%');
            }

            if ($request->check_monitoring) {
                $model = $model->where('status_monitoring', $request->check_monitoring);
                $BrandAbuseSocialRef_data->where('brand_abuse_socail_ref.status_monitoring', 'LIKE', '%' . $request->check_monitoring . '%');
            }

            if ($request->isDateSearch == 1) {
                $date_start = $request->startDate;
                $date_end = $request->endDate;

                $date_start_explode = explode(" ", $date_start);
                $date_start_date = @$date_start_explode[0];
                $date_start_time = @$date_start_explode[1] . ' ' . @$date_start_explode[2];

                $date_start_date_format = date("Y-m-d", strtotime($date_start_date));

                $date_start_time_time = date("H:i", strtotime($date_start_time));
                $date_start_datetime_format = $date_start_date_format . ' '  . '00:00:01';

                $date_end_explode = explode(" ", $date_end);
                $date_end_date = @$date_end_explode[0];
                $date_end_time = @$date_end_explode[1] . ' ' . @$date_end_explode[2];
                $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                $date_end_time_time = date("H:i", strtotime($date_end_time));
                $date_end_datetime_format = $date_end_date_format . ' '  . '23:59:59';

                $source = $request->source;
                $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($date_start_date_format, $date_end_date_format) {
                    $query->whereBetween('feedtimepost', array($date_start_date_format, $date_end_date_format));
                });
               
                $BrandAbuseSocialRef_data->whereBetween('brand_abuse_feed.feedtimepost',array($date_start_datetime_format, $date_end_datetime_format));
            }
         

        } else {
         
            if ($request->site) {
                $SiteSettings = SiteSettings::where('code', @$request->site)->first();

                $model = $model->where('brand_abuse_socail_ref.site_id', $SiteSettings->id);
                //remove ->where('brand_abuse_socail_ref.status', 1)
                $BrandAbuseSocialRef_data->where('brand_abuse_socail_ref.site_id', $SiteSettings->id);
                //remove ->where('brand_abuse_socail_ref.status', 1)
            }

            if ($request->click_key) {
                $model = $model->where('keyword', $request->click_key);
                $BrandAbuseSocialRef_data->where('LOWER(`brand_abuse_feed.keyword`)','LIKE',[trim(strtolower($request->click_key))]);
            }


            if($request ->click_type) {

                $model = $model-> where('feel_type', '=' ,$request -> click_type);
                $BrandAbuseSocialRef_data->where('brand_abuse_socail_ref.feel_type',$request -> click_type);

            }
    
            if ($request->click_type2) {
                $keywords = $request->click_type2;
                $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($keywords) {
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

                if($keywords == 'other') {
                    $BrandAbuseSocialRef_data->whereRaw('LOWER(fx_brand_abuse_feed.keyword) != ? ',[trim(strtolower('mobile'))])
                                            ->whereRaw('LOWER(fx_brand_abuse_feed.keyword) != ? ',[trim(strtolower('facebook'))])
                                            ->whereRaw('LOWER(fx_brand_abuse_feed.keyword) != ? ',[trim(strtolower('line'))])
                                            ->whereRaw('LOWER(fx_brand_abuse_feed.keyword) != ? ',[trim(strtolower('twitter'))])
                                            ->whereRaw('LOWER(fx_brand_abuse_feed.keyword) != ? ',[trim(strtolower('website'))]);
                } else {
                    if($keywords == 'in_progress') {
                        $BrandAbuseSocialRef_data->where('brand_abuse_socail_ref.status_monitoring', 'LIKE', '%' . $keywords . '%');
                    } else if($keywords == 'reported') {
                        $BrandAbuseSocialRef_data->where('brand_abuse_socail_ref.status_monitoring', 'LIKE', '%' . $keywords . '%');
                    } else if($keywords == 'close') {
                        $BrandAbuseSocialRef_data->where('brand_abuse_socail_ref.status_monitoring', 'LIKE', '%' . $keywords . '%');
                    } else {
                        $BrandAbuseSocialRef_data->where('brand_abuse_feed.keyword', 'LIKE', '%' . $keywords . '%');
                    }
                }
        
            }

            $get_role_custom_first = @get_role_custom();
            $site_id_arr = @$get_role_custom_first['site_id_arr'];
            if(@$get_role_custom_first['superadmin'] == 1) {

            }else if(@$get_role_custom_first['client'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr)->where('status', 1);
                $BrandAbuseSocialRef_data->whereIn('brand_abuse_socail_ref.site_id', $site_id_arr)->where('brand_abuse_feed.status', 1);

            }else if(@$get_role_custom_first['site_support'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr);
                $BrandAbuseSocialRef_data->whereIn('brand_abuse_socail_ref.site_id', $site_id_arr);

            }else if(@$get_role_custom_first['site_admin'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr);
                $BrandAbuseSocialRef_data->whereIn('brand_abuse_socail_ref.site_id', $site_id_arr);

            }else if(@$get_role_custom_first['site_client'] == 1) {
                $model = $model->whereIn('site_id', $site_id_arr)->where('brand_abuse_feed.status', 1);
                $BrandAbuseSocialRef_data->whereIn('brand_abuse_socail_ref.site_id', $site_id_arr)->where('fx_brand_abuse_feed.status', 1);
            }

        }

        if(@$request->order){
            $column_order = @$request->order[0]['column'];
            $column_dir = @$request->order[0]['dir'];
            if($column_order == "9"){
                $model->orderBy('status',$column_dir);
                $BrandAbuseSocialRef_data->orderBy('brand_abuse_socail_ref.site_id', $column_dir);
            }
            else if($column_order == "8"){
                $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($column_dir) {
                          $query->orderBy('feedtimepost',$column_dir);
                });
                $BrandAbuseSocialRef_data->orderBy('brand_abuse_feed.feedtimepost', $column_dir);
            }
            else if($column_order == "7"){

                $model->orderBy('status_monitoring',$column_dir);
                $BrandAbuseSocialRef_data->orderBy('brand_abuse_socail_ref.status_monitoring', $column_dir);
            }
            else if($column_order == "6"){

                $model->orderBy('serverity',$column_dir);
                $BrandAbuseSocialRef_data->orderBy('brand_abuse_socail_ref.serverity', $column_dir);
            }
            else if($column_order == "5"){

                $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($column_dir) {
                    $query->orderBy('feedcontent',$column_dir);
                });
                $BrandAbuseSocialRef_data->orderBy('brand_abuse_feed.feedcontent', $column_dir);
            }
            else if($column_order == "4"){

                $model->orderBy('keyword',$column_dir);
                $BrandAbuseSocialRef_data->orderBy('brand_abuse_feed.keyword', $column_dir);
            }
            else if($column_order == "3"){

                $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($column_dir) {
                    $query->orderBy('source_name',$column_dir);
                });
                $BrandAbuseSocialRef_data->orderBy('brand_abuse_feed.source_name', $column_dir);
            }
            else if($column_order == "2"){

                $model->whereHas('get_brand_abuse_feed_one', function ($query) use ($column_dir) {
                    $query->orderBy('feel_type',$column_dir);
                });
                $BrandAbuseSocialRef_data->orderBy('brand_abuse_socail_ref.feel_type', $column_dir);
            }
            else if($column_order == "1"){

                $model->whereHas('get_site', function ($query) use ($column_dir) {
                    $query->orderBy('name',$column_dir);
                });
                $BrandAbuseSocialRef_data->orderBy('site.name', $column_dir);
           
            }else{
                $model->orderBy('created_at', 'desc');
                $BrandAbuseSocialRef_data->orderBy('brand_abuse_socail_ref.created_at', $column_dir);
            }
        }else{
            $model->orderBy('created_at', 'desc');
            $BrandAbuseSocialRef_data->orderBy('brand_abuse_socail_ref.created_at', $column_dir);
        }

        $response = [
            "recordsFiltered"=> $BrandAbuseSocialRef_data->count(),
            "draw"=>$request->draw,
            "recordsTotal" => $BrandAbuseSocialRef_data->count(),
            "start"=>$request->start,
            "length"=>$request->length,
            "data" => $BrandAbuseSocialRef_data->skip($request->start)->take($request->length)->get(),
        ];

        return response()->json( $response); 
    }

    public function create_brandabuse(Request $request)
    {
        $data['site'] = SiteSettings::where("active", '=', 1)->where('deleted_at', null)->get();
        $data['site_code'] = @$request->site;
        return view('brandabuse::modal.create_brandabuse')->with($data);
    }

    public function add_brandabuse(Request $request)
    {

        $keyword = @$request->keyword;
        $other = @$request->other;
        if($keyword == 'Other') {
            $keyword_i = @$other;
        } else {
            $keyword_i = @$keyword;
        }
   
        $BrandAbuseFeed = new BrandAbuseFeed();
        $BrandAbuseFeed->code = generator_uuid();
        $BrandAbuseFeed->feel_type = @$request->type;
        // $BrandAbuseFeed->feedcontent = @$request->content;

        $BrandAbuseFeed->keyword = @$keyword_i;

        $BrandAbuseFeed->source_name = @$request->source;
        $BrandAbuseFeed->feedtimepost = Carbon::now();
        $BrandAbuseFeed->status = 1;
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

                    //Link url
                    $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                    if(preg_match($reg_exUrl, $data, $url_image)) {
                        $url = $url_image[0];
                        $image = file_get_contents($url);
                        if ($image !== false){
                            $data = 'data:image/jpg;base64,'.base64_encode($image);
                        }
                    }

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
        $BrandAbuseFeed->feedcontent = $content;
        $BrandAbuseFeed->save();
        $BrandAbuseFeed_send_mail[] = $BrandAbuseFeed;

            if($request->site){
                foreach($request->site as $site){
                    $SiteSettings = SiteSettings::where('code', $site)->first();
                    $BrandAbuseSocialRefs = new BrandAbuseSocialRef;
                    $BrandAbuseSocialRefs->code = generator_uuid();
                    $BrandAbuseSocialRefs->site_id = $SiteSettings->id;
                    $BrandAbuseSocialRefs->brand_abuse_feed_id = $BrandAbuseFeed->id;
                    $BrandAbuseSocialRefs->keyword = $BrandAbuseFeed->keyword;
                    $BrandAbuseSocialRefs->feel_type = $BrandAbuseFeed->feel_type;
                    $BrandAbuseSocialRefs->status_monitoring = @$request->monitoring;
                    $BrandAbuseSocialRefs->serverity = @$request->serverity;
                    $BrandAbuseSocialRefs->status = 1;
                    $BrandAbuseSocialRefs->save();
                    if ($request->sent_mail == true) {
                        $site_email_alert = site_config_email_alert::where("site_id", $SiteSettings->id)->get();
                        if ($site_email_alert) {
                            $email_site_a = [];
                            foreach ($site_email_alert as $site_email_alert_val) {
                                $email_site_a[] = $site_email_alert_val->email;
                            }
                            $email_site_alert = array_unique($email_site_a);
                            foreach($email_site_alert as $email){
                                Mail::to($email)->send(new CompromisedMail($BrandAbuseFeed_send_mail, 'brand_abuse'));
                                if( count(Mail::failures()) == 0 ) {
                                    LogEmail::Create([
                                        'to' => $email,
                                        'status' => 'Success',
                                        'subject' => 'brand_abuse'
                                    ]);
                                }
                            }
                            if( count(Mail::failures()) > 0 ) {
                                foreach(Mail::failures() as $email_address) {
                                    LogEmail::Create([
                                        'to' => $email_address,
                                        'status' => 'Fail',
                                        'subject' => 'brand_abuse'
                                    ]);
                                }
                            }
                        }
                    }
                }
                $site = route('brandabuse.index_all_site');
            }else{
                $SiteSettings = SiteSettings::where('code', @$request->site_code)->first();
                $BrandAbuseSocialRefs = new BrandAbuseSocialRef;
                $BrandAbuseSocialRefs->code = generator_uuid();
                $BrandAbuseSocialRefs->site_id = $SiteSettings->id;
                $BrandAbuseSocialRefs->brand_abuse_feed_id = $BrandAbuseFeed->id;
                $BrandAbuseSocialRefs->keyword = $BrandAbuseFeed->keyword;
                $BrandAbuseSocialRefs->feel_type = $BrandAbuseFeed->feel_type;
                $BrandAbuseSocialRefs->status_monitoring = @$request->monitoring;
                $BrandAbuseSocialRefs->serverity = @$request->serverity;
                $BrandAbuseSocialRefs->status = 1;
                $BrandAbuseSocialRefs->save();
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
                            Mail::to($email)->send(new CompromisedMail($BrandAbuseFeed_send_mail, 'brand_abuse'));
                            if( count(Mail::failures()) == 0 ) {
                                LogEmail::Create([
                                    'to' => $email,
                                    'status' => 'Success',
                                    'subject' => 'brand_abuse'
                                ]);
                            }
                        }
                        if( count(Mail::failures()) > 0 ) {
                            foreach(Mail::failures() as $email_address) {
                                LogEmail::Create([
                                    'to' => $email_address,
                                    'status' => 'Fail',
                                    'subject' => 'brand_abuse'
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

    public function edit_brandabuse_modal($code,Request $request){
        $BrandAbuseSocialRefs = BrandAbuseSocialRef::where('code',$code)->first();
        $BrandAbuseFeed = BrandAbuseFeed::where('id',$BrandAbuseSocialRefs->brand_abuse_feed_id)->first();;
        $data['BrandAbuseFeed'] = $BrandAbuseFeed;
        $data['BrandAbuseSocialRefs'] = $BrandAbuseSocialRefs;

        $data['site'] = @$request->site;

        return view('brandabuse::modal.edit_brandabuse')->with($data);
    }

    public function edit_brandabuse(Request $request){
        $BrandAbuseFeed = BrandAbuseFeed::where('id',@$request->id_BrandAbuseFeed)->first();
        $BrandAbuseFeed->feel_type = @$request->type;
        // $BrandAbuseFeed->feedcontent = @$request->content;
        if(@$request->other){
            $BrandAbuseFeed->keyword = @$request->other;
        }else{
            $BrandAbuseFeed->keyword = @$request->keyword;
        }
        
        $BrandAbuseFeed->source_name = @$request->source;
        if ($request->sent_mail == true) {
            $BrandAbuseFeed->feedtimepost = Carbon::now();
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

                    //Link url
                    $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                    if(preg_match($reg_exUrl, $data, $url_image)) {
                        $url = $url_image[0];
                        $image = file_get_contents($url);
                        if ($image !== false){
                            $data = 'data:image/jpg;base64,'.base64_encode($image);
                        }
                    }

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
        $BrandAbuseFeed->feedcontent = $content;        
        $BrandAbuseFeed->save();
        $BrandAbuseFeed_send_mail[] = $BrandAbuseFeed;

        $BrandAbuseSocialRefs = BrandAbuseSocialRef::where('brand_abuse_feed_id',$BrandAbuseFeed->id)->get();
            if($BrandAbuseSocialRefs){
                foreach($BrandAbuseSocialRefs as $BrandAbuseSocialRefs){
                    $BrandAbuseSocialRefs->keyword = $BrandAbuseFeed->keyword;
                    $BrandAbuseSocialRefs->feel_type = $BrandAbuseFeed->feel_type;
                    $BrandAbuseSocialRefs->status_monitoring = @$request->monitoring;
                    $BrandAbuseSocialRefs->serverity = @$request->serverity;
                    $BrandAbuseSocialRefs->save();
                    if ($request->sent_mail == true) {
                        $site_email_alert = site_config_email_alert::where("site_id", $BrandAbuseSocialRefs->site_id)->get();
                        if ($site_email_alert) {
                            $email_site_a = [];
                            foreach ($site_email_alert as $site_email_alert_val) {
                                $email_site_a[] = $site_email_alert_val->email;
                            }
                            $email_site_alert = array_unique($email_site_a);
                            foreach($email_site_alert as $email){
                                Mail::to($email)->send(new CompromisedMail($BrandAbuseFeed_send_mail, 'brand_abuse'));
                                if( count(Mail::failures()) == 0 ) {
                                    LogEmail::Create([
                                        'to' => $email,
                                        'status' => 'Success',
                                        'subject' => 'brand_abuse'
                                    ]);
                                }
                            }
                            if( count(Mail::failures()) > 0 ) {
                                foreach(Mail::failures() as $email_address) {
                                    LogEmail::Create([
                                        'to' => $email_address,
                                        'status' => 'Fail',
                                        'subject' => 'brand_abuse'
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
                $site = route('brandabuse.index_all_site');
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

    public function change_status_brandabusedata(Request $request)
    {
        $BrandAbuseSocialRef = BrandAbuseSocialRef::where('id', $request->code)->first();
        $BrandAbuseSocialRef->status = $request->status;
        $BrandAbuseSocialRef->save();

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('brandabuse.index_all_site'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function delete_brandabusedata_modal($code)
    {
        $data["code"] = $code;
        return view('brandabuse::modal.delete_brandabusedata')->with($data);
    }

    public function delete_brandabusedata($code)
    {
        // dd($code);
        $BrandAbuseSocialRef = BrandAbuseSocialRef::where('code', $code)->first();
        $BrandAbuseFeedTemp = BrandAbuseFeedTemp::where('id', $BrandAbuseSocialRef->temp_id)->first();
        if($BrandAbuseFeedTemp){
            $BrandAbuseFeeds = BrandAbuseFeed::where('temp_id', $BrandAbuseFeedTemp->id)->get();
            if($BrandAbuseFeeds){
                foreach($BrandAbuseFeeds as $BrandAbuseFeed){
                    $leak_socail_ref_temp = leak_socail_ref_temp::where('brand_abuse_feed_id', $BrandAbuseFeedTemp->id)->first();
                    $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $BrandAbuseFeed->id)->first();
                    if($transaction_client_leak_feed){
                        $transaction_client_leak_feed -> transaction_mode = 'delete';
                        $transaction_client_leak_feed -> transaction_data_status = 1;
                        $transaction_client_leak_feed -> status = 1;
                        $transaction_client_leak_feed -> save();
                    }else{
                        $transaction_client_leak_feed = new transaction_client_leak_feed();
                        $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                        $transaction_client_leak_feed -> transaction_id = $BrandAbuseFeed->id;
                        $transaction_client_leak_feed -> transaction_mode = 'delete';
                        $transaction_client_leak_feed -> transaction_data_status = 1;
                        $transaction_client_leak_feed -> status = 1;
                        $transaction_client_leak_feed -> save();
                    }

                    $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $BrandAbuseSocialRef->id)->first();
                    if($transaction_client_leak_social_ref){
                        $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                        $transaction_client_leak_social_ref -> transaction_data_status = 1;
                        $transaction_client_leak_social_ref -> status = 1;
                        $transaction_client_leak_social_ref -> save();
                    }else{
                        $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                        $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                        $transaction_client_leak_social_ref -> transaction_id = $BrandAbuseSocialRef->id;
                        $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                        $transaction_client_leak_social_ref -> transaction_data_status = 1;
                        $transaction_client_leak_social_ref -> status = 1;
                        $transaction_client_leak_social_ref -> save();
                    }
        
                }
            }
            BrandAbuseFeed::where('temp_id', $BrandAbuseFeedTemp->id)->delete();
            $BrandAbuseSocialRef->delete();
            $BrandAbuseFeedTemp->approve = 0;
            $BrandAbuseFeedTemp->save();
        }else{
            $BrandAbuseSocialRef->delete();
        }

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('brandabuse.index_all_site'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function change_delete_brandabusedata(Request $request)
    {
        // dd($request->all());

        foreach ($request->id as $social_id) {
            // $BrandAbuseSocialRef = BrandAbuseSocialRef::where('id', (int)$social_id)->first();
            $BrandAbuseSocialRef = DB::table('brand_abuse_socail_ref')->where('id', (int)$social_id)->first();
            $BrandAbuseFeedTemp = BrandAbuseFeedTemp::where('id', @$BrandAbuseSocialRef->temp_id)->first();
            if($BrandAbuseFeedTemp){
                $BrandAbuseFeeds = BrandAbuseFeed::where('temp_id', $BrandAbuseFeedTemp->id)->get();
                if($BrandAbuseFeeds){
                    foreach($BrandAbuseFeeds as $BrandAbuseFeed){
                        $leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $BrandAbuseFeedTemp->id)->first();
                        $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $BrandAbuseFeed->id)->first();
                        if($transaction_client_leak_feed){
                            $transaction_client_leak_feed -> transaction_mode = 'delete';
                            $transaction_client_leak_feed -> transaction_data_status = 1;
                            $transaction_client_leak_feed -> status = 1;
                            $transaction_client_leak_feed -> save();
                        }else{
                            $transaction_client_leak_feed = new transaction_client_leak_feed();
                            $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                            $transaction_client_leak_feed -> transaction_id = $BrandAbuseFeed->id;
                            $transaction_client_leak_feed -> transaction_mode = 'delete';
                            $transaction_client_leak_feed -> transaction_data_status = 1;
                            $transaction_client_leak_feed -> status = 1;
                            $transaction_client_leak_feed -> save();
                        }

                        $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $BrandAbuseSocialRef->id)->first();
                        if($transaction_client_leak_social_ref){
                            $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                            $transaction_client_leak_social_ref -> transaction_data_status = 1;
                            $transaction_client_leak_social_ref -> status = 1;
                            $transaction_client_leak_social_ref -> save();
                        }else{
                            $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                            $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                            $transaction_client_leak_social_ref -> transaction_id = $BrandAbuseSocialRef->id;
                            $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                            $transaction_client_leak_social_ref -> transaction_data_status = 1;
                            $transaction_client_leak_social_ref -> status = 1;
                            $transaction_client_leak_social_ref -> save();
                        }
            
                    }
                }
                BrandAbuseFeed::where('temp_id', $BrandAbuseFeedTemp->id)->delete();
                // $data = BrandAbuseSocialRef::where('id', $social_id)->delete();
                $data = DB::table('brand_abuse_socail_ref')->where('id', $social_id)->delete();
                $BrandAbuseFeedTemp->approve = 0;
                $BrandAbuseFeedTemp->save();
            }else{
                // $data = BrandAbuseSocialRef::where('id', $social_id)->delete();
                $data = DB::table('brand_abuse_socail_ref')->where('id', $social_id)->delete();
            }
        }
        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('brandabuse.index_all_site'),
            ],
            true,
            Response::HTTP_OK
        );
    }


    // brandabuse -> activity ------------------------------------------------------------------------------------------------------------------------

    public function activity_brandabuse_modal($code,Request $request){
        $BrandAbuseSocialRefs = BrandAbuseSocialRef::where('code',$code)->first();
        $ActivityHistory = Activity::select('activity.*','users.name as users_name')->where('activity.data_leak_socail_ref_id',$BrandAbuseSocialRefs->id)->whereNull('activity.deleted_at')->leftJoin('users', 'activity.user_id', '=', 'users.id')->orderBy('created_at','desc')->get();
        // $BrandAbuseFeed = BrandAbuseFeed::where('id',$BrandAbuseSocialRefs->data_leak_feed_id)->first();
        $data['BrandAbuseSocialRefs'] = $BrandAbuseSocialRefs;
        $data['site'] = @$request->site;
        $data['ActivityHistory'] = $ActivityHistory;
        
        return view('brandabuse::modal.activity_brandabuse_modal')->with($data);
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

                    //Link url
                    $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                    if(preg_match($reg_exUrl, $data, $url_image)) {
                        $url = $url_image[0];
                        $image = file_get_contents($url);
                        if ($image !== false){
                            $data = 'data:image/jpg;base64,'.base64_encode($image);
                        }
                    }

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
            $Activity->data_leak_socail_ref_id = $request->id_BrandAbuseSocialRefs;
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
        $Activity_check = Activity::where('data_leak_socail_ref_id',$request->id_BrandAbuseSocialRefs)->where('status_activity','close')->first();

        $BrandAbuseSocialRef = BrandAbuseSocialRef::where('id',$request->id_BrandAbuseSocialRefs)->first();

        if($status_activity) {
            $BrandAbuseSocialRef->status_monitoring = $status_activity;
            $BrandAbuseSocialRef->save();
        }
        

        if($request->site_code){
            $site = route('socialdatas.index', ['id' => @$request->site_code]);
        }else{
            $site = route('brandabuse.index_all_site');
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

    public function activity_history_reload(Request $request){
        $BrandAbuseSocialRefs = BrandAbuseSocialRef::where('id',$request->code)->first();
        $ActivityHistory = Activity::select('activity.*','users.name as users_name')->where('activity.data_leak_socail_ref_id',$BrandAbuseSocialRefs->id)->whereNull('activity.deleted_at')->leftJoin('users', 'activity.user_id', '=', 'users.id')->orderBy('created_at','desc')->get();
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

    public function activity_get_edit_data(Request $request){
        $Activity = Activity::where('id',$request->code_activity)->first()->toArray();
        if ($request->ajax()) {
            $data = [
                "ActivityHistory" => $Activity,
            ];
            return response()->json($data);
        }
    }

    public function activity_delete(Request $request){
        if($request->site_code){
            $site = route('socialdatas.index', ['id' => @$request->site_code]);
        }else{
            $site = route('brandabuse.index_all_site');
        }
        $Activity = Activity::where('id',$request->code_activity)->first();
        $Activity->delete();

        $Activity_check = Activity::where('data_leak_socail_ref_id',$Activity->data_leak_socail_ref_id)->where('status_activity','close')->first();
        $Activity_check_last = Activity::where('data_leak_socail_ref_id',$Activity->data_leak_socail_ref_id)->where('deleted_at',null)->orderBy('id','desc')->first();
        $status_activity = $Activity_check_last->status_activity;

        $BrandAbuseSocialRef = BrandAbuseSocialRef::where('id',$Activity->data_leak_socail_ref_id)->first();
        // if($BrandAbuseSocialRef->status_monitoring != 'close') {
            if($Activity_check_last) {
                if($status_activity) {
                    $BrandAbuseSocialRef->status_monitoring = $status_activity;
                    $BrandAbuseSocialRef->save();
                } else {
                    $BrandAbuseSocialRef->status_monitoring = 'in_progress';
                    $BrandAbuseSocialRef->save();
                }
            } else {
                $BrandAbuseSocialRef->status_monitoring = 'in_progress';
                $BrandAbuseSocialRef->save();
            } 
        // }


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

    // brandabuse feed ---------------------------------------------------------------------------------------------------------------------------

    public function brandabusefeed()
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

        $data['SiteSettings'] = $SiteSettings;

        $BrandAbuseSocial = BrandAbuseSocial::where('deleted_at', null)->where('status', 1)->get();
        $data['site_settings'] = SiteSettings::where('deleted_at', null)->where('active', 1)->get();
        $data['BrandAbuseSocial'] = $BrandAbuseSocial;
        $data['page'] = langapp('brand_abuse_feed');

        return view('brandabuse::brandabusefeed')->with($data);
    }

    public function brandabusefeedsocial_datatables(Request $request)
    {
        ini_set('max_execution_time', 180);
        set_time_limit(200);

        if ($request->search_val == 1) {

            $model = BrandAbuseFeedTemp::where('keyword', '!=', null)->where('keyword', '!=', '')->whereIn('feed_type', ['social','darkweb_public']);

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
                $date_start_date_format = date("Y-m-d", strtotime($date_start_date));

                $date_end_explode = explode(" ", $date_end);
                $date_end_date = @$date_end_explode[0];
                $date_end_date_format = date("Y-m-d", strtotime($date_end_date));

                $model = $model->whereBetween('feedtimepost', array($date_start_date_format, $date_end_date_format));
            }

            $model = $model->get();
        } else {

            $model = BrandAbuseFeedTemp::where('keyword', '!=', null)->where('keyword', '!=', '')->whereIn('feed_type', ['social','darkweb_public']);
            if ($request->site) {
                $site = SiteSettings::select('id')->where('code', $request->site)->first();

                $model->whereHas('get_socail_ref_temp', function ($query) use ($site) {

                    $query->where('site_id', 'LIKE', '%' . $site->id . '%');
                });
            }
            // $model = $model->get();
            $model = $model->limit(100)->get();
            
        }
        

        return DataTables::of($model)
            ->editColumn(
                'chk',
                function (BrandAbuseFeedTemp $model) {
                    return '<label><input type="checkbox" name="data_feed_id" class="data_feed_id" value="' . $model->id . '"><span class="label-text"></span></label>';
                }
            )
            ->editColumn(
                'site',
                function (BrandAbuseFeedTemp $model) {
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
                function (BrandAbuseFeedTemp $model) {
                    if ($model->feed_type) {
                        return get_word_leak_compromise($model->feed_type,'data_leak');
                    } else {
                        return '-';
                    }

                }
            )
            ->editColumn(
                'keyword',
                function (BrandAbuseFeedTemp $model) {
                    if ($model->keyword) {
                        return $model->keyword;
                    } else {
                        return '-';
                    }
                }
            )
            ->editColumn(
                'content',
                function (BrandAbuseFeedTemp $model) {
                    return '<div>' . $model->feedcontent . '</div>';
                }
            )
            ->editColumn(
                'data_feed',
                function (BrandAbuseFeedTemp $model) {
                    return $model->feedtimestamp;
                }
            )
            ->editColumn(
                'url',
                function (BrandAbuseFeedTemp $model) {
                    return '<a href="' . $model->feedlink . '" target="_blank"><i class="fas fa-link"></i></a>';
                }
            )
            ->editColumn(
                'action',
                function (BrandAbuseFeedTemp $model) {
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

    public function approve_data_feed(Request $request)
    {

        // dd($request->sent_mail);
        $sent_mail = $request->sent_mail;
        $site_id = 0;
        $BrandAbuseFeed_send_mail = [];
        $BrandAbuseFeedTemps = BrandAbuseFeedTemp::whereIn('id', $request->id)->get();
        $type = @$BrandAbuseFeedTemps[0]->feed_type;
        foreach ($BrandAbuseFeedTemps as $BrandAbuseFeedTemp) {
            $check_BrandAbuseFeed = BrandAbuseFeed::where('temp_id', $BrandAbuseFeedTemp->id)->first();
            if (empty($check_BrandAbuseFeed)) {
                $BrandAbuseFeed = new BrandAbuseFeed();
                $BrandAbuseFeed->code = generator_uuid();
                $BrandAbuseFeed->temp_id = $BrandAbuseFeedTemp->id;
                $BrandAbuseFeed->data_id = $BrandAbuseFeedTemp->data_id;
                $BrandAbuseFeed->sourceid = $BrandAbuseFeedTemp->sourceid;
                $BrandAbuseFeed->keyword = $BrandAbuseFeedTemp->keyword;
                $BrandAbuseFeed->source_name = $BrandAbuseFeedTemp->source_name;
                $BrandAbuseFeed->feedcontent = $BrandAbuseFeedTemp->feedcontent;
                $BrandAbuseFeed->feedlink = $BrandAbuseFeedTemp->feedlink;
                $BrandAbuseFeed->feedtimepost = $BrandAbuseFeedTemp->feedtimepost;
                $BrandAbuseFeed->feedtimestamp = $BrandAbuseFeedTemp->feedtimestamp;
                $BrandAbuseFeed->feeduser = $BrandAbuseFeedTemp->feeduser;
                $BrandAbuseFeed->tag = $BrandAbuseFeedTemp->tag;
                $BrandAbuseFeed->feel_type = $BrandAbuseFeedTemp->feed_type;
                $BrandAbuseFeed->status = 1;
                $BrandAbuseFeed->save();

                // $BrandAbuseFeed_send_mail[] = $BrandAbuseFeed;

                $leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $BrandAbuseFeedTemp->id)->first();
                if (!empty($leak_socail_ref_temp)) {
                    $BrandAbuseSocialRef = new BrandAbuseSocialRef;
                    $BrandAbuseSocialRef->code = generator_uuid();
                    $BrandAbuseSocialRef->temp_id = $BrandAbuseFeedTemp->id;
                    $BrandAbuseSocialRef->brand_abuse_feed_id = $BrandAbuseFeed->id;
                    $BrandAbuseSocialRef->site_id = $leak_socail_ref_temp->site_id;
                    $BrandAbuseSocialRef->keyword = $leak_socail_ref_temp->keyword;
                    $BrandAbuseSocialRef->feel_type = $BrandAbuseFeedTemp->feed_type;
                    $BrandAbuseSocialRef->status = 1;
                    $BrandAbuseSocialRef->view = 0;
                    $BrandAbuseSocialRef->save();

                    if ($site_id == 0) {
                        $site_id = $leak_socail_ref_temp->site_id;
                    }
                    
                    if(!isset($BrandAbuseFeed_send_mail[(string)$leak_socail_ref_temp->site_id])){
                        $BrandAbuseFeed_send_mail[(string)$leak_socail_ref_temp->site_id] = [];
                    }
                    array_push($BrandAbuseFeed_send_mail[(string)$leak_socail_ref_temp->site_id], $BrandAbuseFeed);


                    $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $BrandAbuseFeed->id)->first();
                    if($transaction_client_leak_feed){
                        $transaction_client_leak_feed -> transaction_mode = 'insert';
                        $transaction_client_leak_feed -> transaction_data_status = 1;
                        $transaction_client_leak_feed -> status = 1;
                        $transaction_client_leak_feed -> save();
                    }else{
                        $transaction_client_leak_feed = new transaction_client_leak_feed();
                        $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                        $transaction_client_leak_feed -> transaction_id = $BrandAbuseFeed->id;
                        $transaction_client_leak_feed -> transaction_mode = 'insert';
                        $transaction_client_leak_feed -> transaction_data_status = 1;
                        $transaction_client_leak_feed -> status = 1;
                        $transaction_client_leak_feed -> save();
                    }

                    
                    $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $BrandAbuseSocialRef->id)->first();
                    if($transaction_client_leak_social_ref){
                        $transaction_client_leak_social_ref -> transaction_mode = 'insert';
                        $transaction_client_leak_social_ref -> transaction_data_status = 1;
                        $transaction_client_leak_social_ref -> status = 1;
                        $transaction_client_leak_social_ref -> save();
                    }else{
                        $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                        $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                        $transaction_client_leak_social_ref -> transaction_id = $BrandAbuseSocialRef->id;
                        $transaction_client_leak_social_ref -> transaction_mode = 'insert';
                        $transaction_client_leak_social_ref -> transaction_data_status = 1;
                        $transaction_client_leak_social_ref -> status = 1;
                        $transaction_client_leak_social_ref -> save();
                    }
                }

                $BrandAbuseFeedTemp->approve = 1;
                $BrandAbuseFeedTemp->save();
            }
        }
        
        if ($this->request->sent_mail == 1) {
            foreach ($BrandAbuseFeed_send_mail as $key => $value) {
                $site_email_alert = site_config_email_alert::where("site_id", $key)->get();
                if ($site_email_alert) {
                    $email_site_a = [];
                    foreach ($site_email_alert as $site_email_alert_val) {
                        $email_site_a[] = $site_email_alert_val->email;
                    }
                    $email_site_alert = array_unique($email_site_a);
                    foreach($email_site_alert as $email){
                        Mail::to($email)->send(new CompromisedMail($value, 'brand_abuse'));
                        if( count(Mail::failures()) == 0 ) {
                            LogEmail::Create([
                                'to' => $email,
                                'status' => 'Success',
                                'subject' => 'brand_abuse'
                            ]);
                        }
                    }
                    if( count(Mail::failures()) > 0 ) {
                        foreach(Mail::failures() as $email_address) {
                            LogEmail::Create([
                                'to' => $email_address,
                                'status' => 'Fail',
                                'subject' => 'brand_abuse'
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
                $site = route('brandabusefeed.index');
            }
        } else {
            if($request->site){
                $site = route('compromised_feed.index',['id'=>$request->site]);
            }else{
                $site = route('datafeed.darkweb_index');
            }
        }

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
        $BrandAbuseFeedTemps = BrandAbuseFeedTemp::whereIn('id', $request->id)->get();
        $type = @$BrandAbuseFeedTemps[0]->feed_type;
        foreach ($BrandAbuseFeedTemps as $BrandAbuseFeedTemp) {
            $BrandAbuseFeeds = BrandAbuseFeed::where('temp_id', $BrandAbuseFeedTemp->id)->get();
            foreach($BrandAbuseFeeds as $BrandAbuseFeed){
                $leak_socail_ref_temp = leak_socail_ref_temp::where('data_leak_feed_id', $BrandAbuseFeedTemp->id)->first();
                $transaction_client_leak_feed = transaction_client_leak_feed::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $BrandAbuseFeed->id)->first();
                if($transaction_client_leak_feed){
                    $transaction_client_leak_feed -> transaction_mode = 'delete';
                    $transaction_client_leak_feed -> transaction_data_status = 1;
                    $transaction_client_leak_feed -> status = 1;
                    $transaction_client_leak_feed -> save();
                }else{
                    $transaction_client_leak_feed = new transaction_client_leak_feed();
                    $transaction_client_leak_feed -> site_id = $leak_socail_ref_temp->site_id;
                    $transaction_client_leak_feed -> transaction_id = $BrandAbuseFeed->id;
                    $transaction_client_leak_feed -> transaction_mode = 'delete';
                    $transaction_client_leak_feed -> transaction_data_status = 1;
                    $transaction_client_leak_feed -> status = 1;
                    $transaction_client_leak_feed -> save();
                } 
            }
            BrandAbuseFeed::where('temp_id', $BrandAbuseFeedTemp->id)->delete();
            $BrandAbuseSocialRefs = BrandAbuseSocialRef::where('temp_id', $BrandAbuseFeedTemp->id)->get();
            foreach($BrandAbuseSocialRefs as $BrandAbuseSocialRef){
                $transaction_client_leak_social_ref = transaction_client_leak_social_ref::where('site_id', $leak_socail_ref_temp->site_id)->where('transaction_id', $BrandAbuseSocialRef->id)->first();
                if($transaction_client_leak_social_ref){
                    $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                    $transaction_client_leak_social_ref -> transaction_data_status = 1;
                    $transaction_client_leak_social_ref -> status = 1;
                    $transaction_client_leak_social_ref -> save();
                }else{
                    $transaction_client_leak_social_ref = new transaction_client_leak_social_ref();
                    $transaction_client_leak_social_ref -> site_id = $leak_socail_ref_temp->site_id;
                    $transaction_client_leak_social_ref -> transaction_id = $BrandAbuseSocialRef->id;
                    $transaction_client_leak_social_ref -> transaction_mode = 'delete';
                    $transaction_client_leak_social_ref -> transaction_data_status = 1;
                    $transaction_client_leak_social_ref -> status = 1;
                    $transaction_client_leak_social_ref -> save();
                }
            }
            BrandAbuseSocialRef::where('temp_id', $BrandAbuseFeedTemp->id)->delete();
            $BrandAbuseFeedTemp->approve = 0;
            $BrandAbuseFeedTemp->save();
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
        
        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => @$site,
            ],
            true,
            Response::HTTP_OK
        );
    }
}
