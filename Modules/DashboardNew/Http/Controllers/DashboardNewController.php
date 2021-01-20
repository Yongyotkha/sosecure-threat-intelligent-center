<?php

namespace Modules\DashboardNew\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\MonitoringVulnerabilitys\Entities\CVEAssets;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
use App\DataLeakFeed;
use App\DataLeakSocialRef;
use App\IndicatorSummaryYear;
use Illuminate\Support\Facades\DB;
use Modules\Scans\Entities\Assets;
use Modules\SiteSettings\Entities\DataCveven;
use Modules\SiteSettings\Entities\SiteSettings;
use Illuminate\Support\Facades\Auth;
use Modules\Users\Entities\User;
use Modules\Users\Entities\UserSite;

class DashboardNewController extends Controller
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
        
        // $menu = array();
        // if(isset($_SESSION["menu"])){
        //     // unset($_SESSION["lastname"]);
        //     $menu = $_SESSION["menu"];
        // }
        
        // dd($menu[4]->get_menu_sub);

        if(@get_role_custom()['superadmin'] == 1 || @get_role_custom()['site_admin'] == 1) {

        }

        if(Auth::check()) {

            $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            if(Auth::user()->hasRole('admin')) {//if admin
                // dd(777);
                $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->get();

            } else { //if notAdmin
                // dd(888);
                if(@Auth::user()->site_role_id && @Auth::user()->site_id) {
                    if(@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) {//support and admin
                        // dd(99);

                        $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
                        ->whereIn('id', $site_id_arr)//['49', '56']
                        ->get();
             

                    } else {//not support and admin
                        $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
                        ->whereIn('id', $site_id_arr)//['49', '56']
                        ->get();
                    }
                }
            }
        }

        $data['site_settings'] = $SiteSettings;
            
        

        $data['page'] = langapp('dashboard');
        if(@get_role_custom()['superadmin'] == 1) {
            $data['count_CVEAssets'] = CVEAssets::where("active", '=', 1)->count();
            $data['count_CVEMapping'] = CVEMapping::count();
            $data['get_CVEAssets'] = CVEAssets::where("active", '=', 1)->get();
            $data['count_compromised'] = DataLeakSocialRef::where("status", '=', 1)
                                    ->where("deleted_at", '=', null)
                                    ->where("feel_type", '!=', 'social')
                                    ->count();
            $data['count_dataLeak'] = DataLeakSocialRef::where("status", '=', 1)
                                    ->where("deleted_at", '=', null)
                                    ->where("feel_type", '=', 'social')
                                    ->count();
        } else {
            $data['count_CVEAssets'] = CVEAssets::where("active", '=', 1)->whereIn('site_id', $site_id_arr)->count();
            $data['count_CVEMapping'] = CVEMapping::whereIn('site_id', $site_id_arr)->count();
            $data['get_CVEAssets'] = CVEAssets::where("active", '=', 1)->whereIn('site_id', $site_id_arr)->get();
            $data['count_compromised'] = DataLeakSocialRef::where("status", '=', 1)
                                    ->where("deleted_at", '=', null)
                                    ->where("feel_type", '!=', 'social')
                                    ->whereIn('site_id', $site_id_arr)
                                    ->count();
            $data['count_dataLeak'] = DataLeakSocialRef::where("status", '=', 1)
                                    ->where("deleted_at", '=', null)
                                    ->where("feel_type", '=', 'social')
                                    ->whereIn('site_id', $site_id_arr)
                                    ->count();
        }

        
        // $data['get_CVEAssets'] = CVEAssets::where("active", '=', 1)->get();                            
        // $data['site_settings'] = SiteSettings::where("active", 1)->where("deleted_at", null)->get();

        return view('dashboardnew::index')->with($data);
    }
    public function detail_asset()
    {
        $data['page'] = langapp('dashboard');
        return view('dashboardnew::view_detail_asset')->with($data);
    }

    public function cve_assets(Request $request){
        if(Auth::check()) {
            $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            if(@get_role_custom()['superadmin'] == 1) {
                if($request -> site == 0){
                    $assets = Assets::where('status', 1)->get();
                }else{
                    $assets = Assets::where('site_id', $request -> site)->where('status', 1)->get();
                }
            } else {
                if($request -> site == 0){
                    $assets = Assets::where('status', 1)->whereIn('site_id', $site_id_arr)->get();
                }else{
                    $assets = Assets::where('site_id', $request -> site)->whereIn('site_id', $site_id_arr)->where('status', 1)->get();
                }
            }
        }
        

        $response = array(
            'error' => '', 
            'status_code' => '200',
            'data' => $assets
        );
        return response()->json($response);
    }

    public function count_asset(Request $request){
        if(Auth::check()) {
            $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            if(@get_role_custom()['superadmin'] == 1) {
                if($request -> site == 0){
                    $assets = Assets::select('id')->where('status', 1)->count();
                }else{
                    $assets = Assets::select('id')->where('site_id', $request -> site)->where('status', 1)->count();
                }
            } else {
                if($request -> site == 0){
                    $assets = Assets::select('id')->where('status', 1)->whereIn('site_id', $site_id_arr)->count();
                }else{
                    $assets = Assets::select('id')->where('site_id', $request -> site)->whereIn('site_id', $site_id_arr)->where('status', 1)->count();
                }
            }
        }

        $response = array(
            'error' => '', 
            'status_code' => '200',
            'data' => $assets
        );
        return response()->json($response);
    }

    public function count_vulnerability(Request $request){
        if(Auth::check()) {
            $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            if(@get_role_custom()['superadmin'] == 1) {
                if($request -> site == 0){
                    $CVEMapping = CVEMapping::select('id')->count();
                }else{
                    $CVEMapping = CVEMapping::select('id')->where('site_id', $request -> site)->count();
                }
            } else {
                if($request -> site == 0){
                    $CVEMapping = CVEMapping::select('id')->whereIn('site_id', $site_id_arr)->count();
                }else{
                    $CVEMapping = CVEMapping::select('id')->where('site_id', $request -> site)->whereIn('site_id', $site_id_arr)->count();
                }
            }
        }

        $response = array(
            'error' => '', 
            'status_code' => '200',
            'data' => $CVEMapping
        );
        return response()->json($response);
    }

    public function count_compromised(Request $request){
        if(Auth::check()) {
            $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            if(@get_role_custom()['superadmin'] == 1) {
                if($request -> site == 0){
                    $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('status', 1)->whereIn('feel_type', ['darkweb','webserver','server','compromise','compromised'])->count();
                }else{
                    $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('site_id', $request -> site)->where('status', 1)->whereIn('feel_type', ['darkweb','webserver','compromise','compromised'])->count();
                }
            } else {
                if($request -> site == 0){
                    $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('status', 1)->whereIn('site_id', $site_id_arr)->whereIn('feel_type', ['darkweb','webserver','server','compromise','compromised'])->count();
                }else{
                    $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('site_id', $request -> site)->where('status', 1)->whereIn('site_id', $site_id_arr)->whereIn('feel_type', ['darkweb','webserver','compromise','compromised'])->count();
                }
            }
        }


        $response = array(
            'error' => '', 
            'status_code' => '200',
            'data' => $DataLeakSocialRef
        );
        return response()->json($response);
    }

    public function count_data_leak(Request $request){
        if(Auth::check()) {
            $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            if(@get_role_custom()['superadmin'] == 1) {
                if($request -> site == 0){
                    $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('status', 1)->where('feel_type', 'social')->count();
                }else{
                    $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('site_id', $request -> site)->where('status', 1)->where('feel_type', 'social')->count();
                }
            } else {
                if($request -> site == 0){
                    $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('status', 1)->whereIn('site_id', $site_id_arr)->where('feel_type', 'social')->count();
                }else{
                    $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('site_id', $request -> site)->where('status', 1)->whereIn('site_id', $site_id_arr)->where('feel_type', 'social')->count();
                }
            }
        }

        $response = array(
            'error' => '', 
            'status_code' => '200',
            'data' => $DataLeakSocialRef
        );
        return response()->json($response);
    }

    public function count_vulnerability_host(Request $request){
        if(Auth::check()) {
            $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            if(@get_role_custom()['superadmin'] == 1) {
                if($request -> site == 0){
                    $CVEAssets = CVEAssets::select('vendor', 'title')->where("active", '=', 1)->groupBy('vendor', 'title')->get();
                }else{
                    $CVEAssets = CVEAssets::select('vendor', 'title')->where('site_id', $request -> site)->where("active", '=', 1)->groupBy('vendor', 'title')->get();
                }
            } else {
                if($request -> site == 0){
                    $CVEAssets = CVEAssets::select('vendor', 'title')->where("active", '=', 1)->whereIn('site_id', $site_id_arr)->groupBy('vendor', 'title')->get();
                }else{
                    $CVEAssets = CVEAssets::select('vendor', 'title')->where('site_id', $request -> site)->where("active", '=', 1)->whereIn('site_id', $site_id_arr)->groupBy('vendor', 'title')->get();
                }
            }
        }


        
        $vendor = [];
        $title = [];
        foreach($CVEAssets as $data){
            $vendor[] = $data -> vendor;
            $title[] = $data -> title;
        }
        $DataCveven = DataCveven::select('namecve', 'title', DB::raw('count(*) as total'))->whereIn('vendor', $vendor)->whereIn('title', $title)->groupBy('namecve')->get();
        $namecve = [];
        $check_total_namecve = array();
        $host_name = [];
        foreach($DataCveven as $data){
            $namecve[] = $data -> namecve;
            $check_total_namecve[] = collect([
                'total' => $data -> total,
                'namecve' => $data -> namecve,
                'title' => $data -> title
            ]);
        }

        if(Auth::check()) {
            $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            if(@get_role_custom()['superadmin'] == 1) {
                $CVEMapping = CVEMapping::select('namecve', 'severity')->whereIn('namecve', $namecve)->groupBy('severity','namecve')->get();
            } else {
                $CVEMapping = CVEMapping::select('namecve', 'severity')->whereIn('site_id', $site_id_arr)->whereIn('namecve', $namecve)->groupBy('severity','namecve')->get();
            }
        }


        foreach($CVEMapping as $data){
            foreach($check_total_namecve as $item){
                if($data -> namecve == $item['namecve']){
                    $data['total'] = $item['total'];
                    $data['title'] = $item['title'];
                    $host_name[] = $item['title'];
                }
            }
        }
        $result = array();
        foreach ($host_name as $element) {
            $result[$element] = $element;
        }
        
        $response = array(
            'error' => '', 
            'status_code' => '200',
            'data' => [
                'data' => $CVEMapping,
                'host_name' => $result
            ]
        );
        return response()->json($response);
    }

    public function chart_indicators(Request $request){
        $IndicatorSummaryYear = IndicatorSummaryYear::where("status", '=', 1)->where('year', now()->year)->get();
        $events = [0,0,0,0,0,0,0,0,0,0,0,0];
        $attribute = [0,0,0,0,0,0,0,0,0,0,0,0];
        foreach($IndicatorSummaryYear as $data){
            if($data -> month == 1){
                $events[0] = $data -> event_count;
                $attribute[0] = $data -> attribute_count;
            }else if($data -> month == 2){
                $events[1] = $data -> event_count;
                $attribute[1] = $data -> attribute_count;
            }else if($data -> month == 3){
                $events[2] = $data -> event_count;
                $attribute[2] = $data -> attribute_count;
            }else if($data -> month == 4){
                $events[3] = $data -> event_count;
                $attribute[3] = $data -> attribute_count;
            }else if($data -> month == 5){
                $events[4] = $data -> event_count;
                $attribute[4] = $data -> attribute_count;
            }else if($data -> month == 6){
                $events[5] = $data -> event_count;
                $attribute[5] = $data -> attribute_count;
            }else if($data -> month == 7){
                $events[6] = $data -> event_count;
                $attribute[6] = $data -> attribute_count;
            }else if($data -> month == 8){
                $events[7] = $data -> event_count;
                $attribute[7] = $data -> attribute_count;
            }else if($data -> month == 9){
                $events[8] = $data -> event_count;
                $attribute[8] = $data -> attribute_count;
            }else if($data -> month == 10){
                $events[9] = $data -> event_count;
                $attribute[9] = $data -> attribute_count;
            }else if($data -> month == 11){
                $events[10] = $data -> event_count;
                $attribute[10] = $data -> attribute_count;
            }else if($data -> month == 12){
                $events[11] = $data -> event_count;
                $attribute[11] = $data -> attribute_count;
            }
        }
        $response = array(
            'error' => '', 
            'status_code' => '200',
            'data' => [
                'events' => $events,
                'attribute' => $attribute,
            ]
        );
        return response()->json($response);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('dashboardnew::create');
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
        return view('dashboardnew::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('dashboardnew::edit');
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

    public function load_chart(Request $request)
    {
        $model = new CVEMapping;

        if(Auth::check()) {
            $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            if(@get_role_custom()['superadmin'] == 1) {
                if($request -> site == 0){
                    $model->get();
                    $high = $model->where('severity', '=', 'HIGH')->count();
                    $medium = $model->where('severity', '=', 'MEDIUM')->count();
                    $critical = $model->where('severity', '=', 'CRITICAL')->count();
                    $low = $model->where('severity', '=', 'LOW')->count();
                    $none = $model->where('severity', '=', 'NONE')->count();
                }else{
                    $model->get();
                    $high = $model->where('site_id', $request -> site)->where('severity', '=', 'HIGH')->count();
                    $medium = $model->where('site_id', $request -> site)->where('severity', '=', 'MEDIUM')->count();
                    $critical = $model->where('site_id', $request -> site)->where('severity', '=', 'CRITICAL')->count();
                    $low = $model->where('site_id', $request -> site)->where('severity', '=', 'LOW')->count();
                    $none = $model->where('site_id', $request -> site)->where('severity', '=', 'NONE')->count();
                }
            } else {
                if($request -> site == 0){
                    $model->get();
                    $high = $model->where('severity', '=', 'HIGH')->whereIn('site_id', $site_id_arr)->count();
                    $medium = $model->where('severity', '=', 'MEDIUM')->whereIn('site_id', $site_id_arr)->count();
                    $critical = $model->where('severity', '=', 'CRITICAL')->whereIn('site_id', $site_id_arr)->count();
                    $low = $model->where('severity', '=', 'LOW')->whereIn('site_id', $site_id_arr)->count();
                    $none = $model->where('severity', '=', 'NONE')->whereIn('site_id', $site_id_arr)->count();
                }else{
                    $model->get();
                    $high = $model->where('site_id', $request -> site)->where('severity', '=', 'HIGH')->whereIn('site_id', $site_id_arr)->count();
                    $medium = $model->where('site_id', $request -> site)->where('severity', '=', 'MEDIUM')->whereIn('site_id', $site_id_arr)->count();
                    $critical = $model->where('site_id', $request -> site)->where('severity', '=', 'CRITICAL')->whereIn('site_id', $site_id_arr)->count();
                    $low = $model->where('site_id', $request -> site)->where('severity', '=', 'LOW')->whereIn('site_id', $site_id_arr)->count();
                    $none = $model->where('site_id', $request -> site)->where('severity', '=', 'NONE')->whereIn('site_id', $site_id_arr)->count();
                }
            }
        }




        // $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        // $clientMD = new MongoClient($DB_MONGO_KEY);
        // $html = '';
        // $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;

        if ($request->ajax()) {
            $data = [
                "count_high" => $high,
                "count_medium" => $medium,
                "count_critical" => $critical,
                "count_low" => $low,
                "count_none" => $none,
            ];
            return response()->json($data);
        }
    }
}
