<?php

namespace Modules\DashboardNew\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;



use App\IndicatorSummaryYear;
use Illuminate\Support\Facades\DB;
use Modules\Scans\Entities\Assets;
use Modules\SiteSettings\Entities\DataCveven;
use Modules\SiteSettings\Entities\SiteSettings;
use Illuminate\Support\Facades\Auth;
use Modules\Users\Entities\User;
use Modules\Users\Entities\UserSite;
use Yajra\DataTables\DataTables;
use App\Entities\TransactionBatchjob;

use MongoDB\BSON\UTCDateTime;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use Modules\MonitoringVulnerabilitys\Entities\CVEAssets;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
use MongoDB\Client as MongoClient;
use App\FXAssetsPort;
use App\DataLeakFeed;
use App\DataLeakSocialRef;
use App\R_s_s_news;
use App\DataLeakSocialRefTemp;
use App\DataLeakFeedTemp;
use App\Entities\CPE;
use App\leak_socail_ref_temp;
use Modules\Scans\Entities\AssetsData;
use App\TransactionScans;
use App\TransactionTimeStampScans;
use Modules\MonitoringVulnerabilitys\Entities\CVEMappingAssets;
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
       
        $role_custom = @check_role_custom();
        if(!$role_custom['dashboard']) {
            check_permission403();
        }
        // $menu = array();
        // if(isset($_SESSION["menu"])){
        //     // unset($_SESSION["lastname"]);
        //     $menu = $_SESSION["menu"];
        // }
        
        // dd($menu[4]->get_menu_sub);

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


        $data['site_settings'] = @$SiteSettings;

        

        $data['page'] = 'dashboard_home';
        // $data['page'] = langapp('dashboard');

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
            $role_custom = @check_role_custom();
            if($role_custom['assets']) {
                $data['count_CVEAssets'] = CVEAssets::where("active", '=', 1)->whereIn('site_id', $site_id_arr)->count(); 
            }
            if($role_custom['vulnerabilities']) {
                $data['count_CVEMapping'] = CVEMapping::whereIn('site_id', $site_id_arr)->count();
            }
            if($role_custom['assets']) {
                $data['get_CVEAssets'] = CVEAssets::where("active", '=', 1)->whereIn('site_id', $site_id_arr)->get();
            }
            if($role_custom['compromised']) {
                $data['count_compromised'] = DataLeakSocialRef::where("status", '=', 1)
                ->where("deleted_at", '=', null)
                ->where("feel_type", '!=', 'social')
                ->whereIn('site_id', $site_id_arr)
                ->count();
            }
            if($role_custom['data_leak']) {
                $data['count_dataLeak'] = DataLeakSocialRef::where("status", '=', 1)
                ->where("deleted_at", '=', null)
                ->where("feel_type", '=', 'social')
                ->whereIn('site_id', $site_id_arr)
                ->count();
            }
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

        $input = $request->all();
        // dd($input);

        $site_id_active = SiteSettings::select('id')->where('active', 1)->whereNull('deleted_at')->get()->pluck('id')->toArray(); 
        // dd(get_role_custom());
        if(Auth::check()) {
            $role_custom = @check_role_custom();
            if($role_custom['assets']) {
                $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
                if(@get_role_custom()['superadmin'] == 1) {
                    if(!$request -> site){
                        $assets = [];
                        $Assets_data = Assets::where('status',1)->get();
                        foreach ($Assets_data as $key => $value) {
                            $AssetsData_data = AssetsData::where('site_id',$value->site_id)->whereIn('site_id',$site_id_active)->where('asset_id',$value->id)->where('status',1)->get();
                            $Domain_list = [];
                            $IP_List =[];
                            foreach ($AssetsData_data as $AssetsData_datakey => $AssetsData_datavalue) {
                                if ($AssetsData_datavalue->data_type_id == 1 || $AssetsData_datavalue->data_type_id == 4) {
                                    //Domain
                                    array_push($Domain_list, $AssetsData_datavalue);
                                }elseif ($AssetsData_datavalue->data_type_id == 5 || $AssetsData_datavalue->data_type_id == 6) {
                                    //IP Asset
                                    array_push($IP_List, $AssetsData_datavalue);
                                }else{

                                }
                            }

                            foreach ($IP_List as $IP_Listkey => $IP_Listvalue) {
                                $CPE_Data = CPE::where('asset_id',$IP_Listvalue->id)->get();
                                $CPE_List = array();
                                foreach ($CPE_Data as $CPE_Datakey => $CPE_Datavalue) {
                                    array_push($CPE_List, $CPE_Datavalue->result .' : '.$CPE_Datavalue->os_type);
                                    
                                }
                                if (count($Domain_list) == 0) {
                                    $Assets_data_list = array();
                                    $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first(); 
                                    $Assets_data_list['site'] = $site->name;
                                    $Assets_data_list['host'] = "None";
                                    $Assets_data_list['value'] = $IP_Listvalue->value;
                                    $Assets_data_list['port'] = "None";
                                    array_push($assets, $Assets_data_list);
                                }else{
                                    foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                                        $Assets_data_list = array();
                                        $port = '';
                                        $num_main = 1;
                                        $port_all = FXAssetsPort::
                                                        select('port')
                                                        ->where('site_id', $IP_Listvalue->site_id)
                                                        ->where('asset_name', $IP_Listvalue->value)
                                                        ->get(); 
                                        $count_port = count($port_all);
                                        
                                        if($count_port > 0)
                                        {
                                            foreach($port_all as $data_port)
                                            {
                                                if($count_port == $num_main)
                                                {
                                                    $port = $port.$data_port->port;
                                                }
                                                else
                                                {
                                                    $port = $port.$data_port->port.',';
                                                }
                                                $num_main++;
                                            }
                                        }
                                        else
                                        {
                                            $port = " - ";
                                        }

                                        $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first(); 
                                        $Assets_data_list['site'] = $site->name;
                                        $Assets_data_list['host'] = $Domain_listvalue->value;
                                        $Assets_data_list['value'] = $IP_Listvalue->value;
                                        $Assets_data_list['port'] = $port;
                                        array_push($assets, $Assets_data_list);
                                    }
                                }
                            }
                        }
                    }else{
                        $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->whereNull('deleted_at')->where('active',1)->first();
                        // dd($site_id_m);

                        $assets = [];
                        $Assets_data = Assets::where('status',1)->get();
                        foreach ($Assets_data as $key => $value) {
                            $AssetsData_data = AssetsData::where('site_id',$site_id_m->id)->whereIn('site_id',$site_id_active)->where('asset_id',$value->id)->where('status',1)->get();
                            // dd($AssetsData_data);
                            
                            $Domain_list = [];
                            $IP_List =[];
                            foreach ($AssetsData_data as $AssetsData_datakey => $AssetsData_datavalue) {
                                // dd($AssetsData_datavalue);
                                if ($AssetsData_datavalue->data_type_id == 1 || $AssetsData_datavalue->data_type_id == 4) {
                                    //Domain
                                    array_push($Domain_list, $AssetsData_datavalue);
                                }elseif ($AssetsData_datavalue->data_type_id == 5 || $AssetsData_datavalue->data_type_id == 6) {
                                    //IP Asset
                                    array_push($IP_List, $AssetsData_datavalue);
                                }else{

                                }
                            }
                            // dd($Domain_list);
                            // dd($IP_List);

                            foreach ($IP_List as $IP_Listkey => $IP_Listvalue) {
                                $CPE_Data = CPE::where('asset_id',$IP_Listvalue->id)->get();
                                // dd($IP_Listvalue);
                                $CPE_List = array();
                                foreach ($CPE_Data as $CPE_Datakey => $CPE_Datavalue) {
                                    array_push($CPE_List, $CPE_Datavalue->result .' : '.$CPE_Datavalue->os_type);
                                    
                                }
                                if (count($Domain_list) == 0) {
                                    $Assets_data_list = array();
                                    $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first(); 
                                    $Assets_data_list['site'] = $site->name;
                                    $Assets_data_list['host'] = "None";
                                    $Assets_data_list['value'] = $IP_Listvalue->value;
                                    $Assets_data_list['port'] = "None";
                                    array_push($assets, $Assets_data_list);

                                }else{
                                    foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                                        $Assets_data_list = array();
                                        $port = '';
                                        $num_main = 1;
                                        $port_all = FXAssetsPort::
                                                        select('port')
                                                        ->where('site_id', $IP_Listvalue->site_id)
                                                        ->where('asset_name', $IP_Listvalue->value)
                                                        ->get(); 
                                        $count_port = count($port_all);
                                        
                                        if($count_port > 0)
                                        {
                                            foreach($port_all as $data_port)
                                            {
                                                if($count_port == $num_main)
                                                {
                                                    $port = $port.$data_port->port;
                                                }
                                                else
                                                {
                                                    $port = $port.$data_port->port.',';
                                                }
                                                $num_main++;
                                            }
                                        }
                                        else
                                        {
                                            $port = " - ";
                                        }

                                        $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first(); 
                                        $Assets_data_list['site'] = $site->name;
                                        $Assets_data_list['host'] = $Domain_listvalue->value;
                                        $Assets_data_list['value'] = $IP_Listvalue->value;
                                        $Assets_data_list['port'] = $port;
                                        array_push($assets, $Assets_data_list);
                                    }
                                }
                            }
                        }
                    }
                    
                } else {
                    if(!$request -> site){
                        $assets = [];
                        $Assets_data = Assets::where('status',1)->get();
                        foreach ($Assets_data as $key => $value) {
                            $AssetsData_data = AssetsData::where('site_id',$value->site_id)->whereIn('site_id',$site_id_active)->where('asset_id',$value->id)->where('status',1)->get();
                            $Domain_list = [];
                            $IP_List =[];
                            foreach ($AssetsData_data as $AssetsData_datakey => $AssetsData_datavalue) {
                                if ($AssetsData_datavalue->data_type_id == 1 || $AssetsData_datavalue->data_type_id == 4) {
                                    //Domain
                                    array_push($Domain_list, $AssetsData_datavalue);
                                }elseif ($AssetsData_datavalue->data_type_id == 5 || $AssetsData_datavalue->data_type_id == 6) {
                                    //IP Asset
                                    array_push($IP_List, $AssetsData_datavalue);
                                }else{

                                }
                            }

                            foreach ($IP_List as $IP_Listkey => $IP_Listvalue) {
                                $CPE_Data = CPE::where('asset_id',$IP_Listvalue->id)->get();
                                $CPE_List = array();
                                foreach ($CPE_Data as $CPE_Datakey => $CPE_Datavalue) {
                                    array_push($CPE_List, $CPE_Datavalue->result .' : '.$CPE_Datavalue->os_type);
                                    
                                }
                                if (count($Domain_list) == 0) {
                                    $Assets_data_list = array();
                                    $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first(); 
                                    $Assets_data_list['site'] = $site->name;
                                    $Assets_data_list['host'] = "None";
                                    $Assets_data_list['value'] = $IP_Listvalue->value;
                                    $Assets_data_list['port'] = "None";
                                    array_push($assets, $Assets_data_list);
                                }else{
                                    foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                                        $Assets_data_list = array();
                                        $port = '';
                                        $num_main = 1;
                                        $port_all = FXAssetsPort::
                                                        select('port')
                                                        ->where('site_id', $IP_Listvalue->site_id)
                                                        ->where('asset_name', $IP_Listvalue->value)
                                                        ->get(); 
                                        $count_port = count($port_all);
                                        
                                        if($count_port > 0)
                                        {
                                            foreach($port_all as $data_port)
                                            {
                                                if($count_port == $num_main)
                                                {
                                                    $port = $port.$data_port->port;
                                                }
                                                else
                                                {
                                                    $port = $port.$data_port->port.',';
                                                }
                                                $num_main++;
                                            }
                                        }
                                        else
                                        {
                                            $port = " - ";
                                        }

                                        $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first(); 
                                        $Assets_data_list['site'] = $site->name;
                                        $Assets_data_list['host'] = $Domain_listvalue->value;
                                        $Assets_data_list['value'] = $IP_Listvalue->value;
                                        $Assets_data_list['port'] = $port;
                                        array_push($assets, $Assets_data_list);
                                    }
                                }
                            }
                        }
                    }else{
                        $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->whereNull('deleted_at')->where('active',1)->first();
                        $assets = [];
                        $Assets_data = Assets::where('status',1)->get();
                        foreach ($Assets_data as $key => $value) {
                            $AssetsData_data = AssetsData::where('site_id',$site_id_m->id)->whereIn('site_id',$site_id_active)->where('asset_id',$value->id)->where('status',1)->get();
                            $Domain_list = [];
                            $IP_List =[];
                            foreach ($AssetsData_data as $AssetsData_datakey => $AssetsData_datavalue) {
                                if ($AssetsData_datavalue->data_type_id == 1 || $AssetsData_datavalue->data_type_id == 4) {
                                    //Domain
                                    array_push($Domain_list, $AssetsData_datavalue);
                                }elseif ($AssetsData_datavalue->data_type_id == 5 || $AssetsData_datavalue->data_type_id == 6) {
                                    //IP Asset
                                    array_push($IP_List, $AssetsData_datavalue);
                                }else{

                                }
                            }

                            foreach ($IP_List as $IP_Listkey => $IP_Listvalue) {
                                $CPE_Data = CPE::where('asset_id',$IP_Listvalue->id)->get();
                                $CPE_List = array();
                                foreach ($CPE_Data as $CPE_Datakey => $CPE_Datavalue) {
                                    array_push($CPE_List, $CPE_Datavalue->result .' : '.$CPE_Datavalue->os_type);
                                    
                                }
                                if (count($Domain_list) == 0) {
                                    $Assets_data_list = array();
                                    $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first(); 
                                    $Assets_data_list['site'] = $site->name;
                                    $Assets_data_list['host'] = "None";
                                    $Assets_data_list['value'] = $IP_Listvalue->value;
                                    $Assets_data_list['port'] = "None";
                                    array_push($assets, $Assets_data_list);
                                }else{
                                    foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                                        $Assets_data_list = array();
                                        $port = '';
                                        $num_main = 1;
                                        $port_all = FXAssetsPort::
                                                        select('port')
                                                        ->where('site_id', $IP_Listvalue->site_id)
                                                        ->where('asset_name', $IP_Listvalue->value)
                                                        ->get(); 
                                        $count_port = count($port_all);
                                        
                                        if($count_port > 0)
                                        {
                                            foreach($port_all as $data_port)
                                            {
                                                if($count_port == $num_main)
                                                {
                                                    $port = $port.$data_port->port;
                                                }
                                                else
                                                {
                                                    $port = $port.$data_port->port.',';
                                                }
                                                $num_main++;
                                            }
                                        }
                                        else
                                        {
                                            $port = " - ";
                                        }

                                        $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first(); 
                                        $Assets_data_list['site'] = $site->name;
                                        $Assets_data_list['host'] = $Domain_listvalue->value;
                                        $Assets_data_list['value'] = $IP_Listvalue->value;
                                        $Assets_data_list['port'] = $port;
                                        array_push($assets, $Assets_data_list);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
        // dd($assets);
    //$assets = Assets::select('raw_data','referent',DB::raw('CONCAT("/asset?Search_Link_All=",id) AS link'))->where('status', 1)->get();
        $response = array(
            'error' => '', 
            'status_code' => '200',
            'data' => @$assets
        );
        return response()->json($response);
    }

    public function assets_port(Request $request)
    {
        
    }

    public function count_asset(Request $request){
        // if(Auth::check()) {
        //     $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
        //     if(@get_role_custom()['superadmin'] == 1) {
        //         if($request -> site == 0){
        //             $assets = Assets::select('id')->where('status', 1)->count();
        //         }else{
        //             $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->first();
        //             $assets = Assets::select('id')->where('site_id', $site_id_m->id)->where('status', 1)->count();
        //         }
        //     } else {
        //         if($request -> site == 0){
        //             $assets = Assets::select('id')->where('status', 1)->whereIn('site_id', $site_id_arr)->count();
        //         }else{
        //             $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->first();
        //             $assets = Assets::select('id')->where('site_id', $site_id_m->id)->whereIn('site_id', $site_id_arr)->where('status', 1)->count();
        //         }
        //     }
        // }
        $site_id_active = SiteSettings::select('id')->where('active', 1)->whereNull('deleted_at')->get()->pluck('id')->toArray(); 
        if(Auth::check()) {
            if(@get_role_custom()['superadmin'] == 1) {
                if(!$request -> site){
                    $datacountAssets = @Assets::select('assets.id','assets_datas.data_type_id','assets_datas.value')->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')->whereIn('assets_datas.data_type_id',[5,6])->where('assets.status', 1)->get();
                    $dataOut["countAssets"] = 0;
                    foreach ($datacountAssets as $key => $value) {
                        $AssetsData_data = AssetsData::where('asset_id', $value->id)->whereIn('site_id',$site_id_active)->whereIn('assets_datas.data_type_id',[1,4])->get()->toArray();
                        $countfn = count($AssetsData_data);
                        if($countfn==0){
                            $dataOut["countAssets"]++;
                        }else{
                            $dataOut["countAssets"] = $dataOut["countAssets"]+$countfn;
                        }
                    }
                }else{
                    $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $request -> site)->first();
                    $datacountAssets = @Assets::select('assets.id','assets_datas.data_type_id','assets_datas.value')->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')->where('assets.site_id',$SiteSettingsfor->id)->whereIn('assets_datas.data_type_id',[5,6])->where('assets.status', 1)->get();
                    $dataOut["countAssets"] = 0;
                    $dataOut["assetLimit"] = $SiteSettingsfor -> asset_limit;
                    foreach ($datacountAssets as $key => $value) {
                        $AssetsData_data = AssetsData::where('asset_id', $value->id)->whereIn('site_id',$site_id_active)->whereIn('assets_datas.data_type_id',[1,4])->get()->toArray();
                        $countfn = count($AssetsData_data);
                        if($countfn==0){
                            $dataOut["countAssets"]++;
                        }else{
                            $dataOut["countAssets"] = $dataOut["countAssets"]+$countfn;
                        }
                    }
                }
            } else {
                $role_custom = @check_role_custom();
                if($role_custom['assets']) {
                    $site_id_arr = @get_role_custom()['site_id_arr'];
                    if(!$request -> site){
                        $datacountAssets = @Assets::select('assets.id','assets_datas.data_type_id','assets_datas.value')->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')->whereIn('assets.site_id',$site_id_arr)->whereIn('assets_datas.data_type_id',[5,6])->where('assets.status', 1)->get();
                        $dataOut["countAssets"] = 0;
                        foreach ($datacountAssets as $key => $value) {
                            $AssetsData_data = AssetsData::where('asset_id', $value->id)->whereIn('site_id',$site_id_active)->whereIn('assets_datas.data_type_id',[1,4])->get()->toArray();
                            $countfn = count($AssetsData_data);
                            if($countfn==0){
                                $dataOut["countAssets"]++;
                            }else{
                                $dataOut["countAssets"] = $dataOut["countAssets"]+$countfn;
                            }
                        }
                    }else{
                        $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $request -> site)->first();
                        $datacountAssets = @Assets::select('assets.id','assets_datas.data_type_id','assets_datas.value')->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')->whereIn('assets.site_id',$site_id_arr)->where('assets.site_id',$SiteSettingsfor->id)->whereIn('assets_datas.data_type_id',[5,6])->where('assets.status', 1)->get();
                        $dataOut["countAssets"] = 0;
                        $dataOut["assetLimit"] = $SiteSettingsfor -> asset_limit;
                        foreach ($datacountAssets as $key => $value) {
                            $AssetsData_data = AssetsData::where('asset_id', $value->id)->whereIn('site_id',$site_id_active)->whereIn('assets_datas.data_type_id',[1,4])->get()->toArray();
                            $countfn = count($AssetsData_data);
                            if($countfn==0){
                                $dataOut["countAssets"]++;
                            }else{
                                $dataOut["countAssets"] = $dataOut["countAssets"]+$countfn;
                            }
                        }
                    }
                }
            }
        }
        $assets = @$dataOut;
        $response = array(
            'error' => '', 
            'status_code' => '200',
            'data' => $assets
        );
        return response()->json($response);
    }

    public function count_vulnerability(Request $request){
        if(Auth::check()) {
            $role_custom = @check_role_custom();
            if($role_custom['vulnerabilities']) {
                $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
                if(@get_role_custom()['superadmin'] == 1) {
                    if(!$request -> site){
                        $CVEMapping = CVEMapping::select('id')->count();
                    }else{
                        $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->first();

                        $CVEMappingAssets_name = CVEMappingAssets::where('site_id',$site_id_m->id)->select('namecve')->get();
                        $CVEMapping = CVEMapping::select('id')->whereIn('namecve', $CVEMappingAssets_name)->count();
                    }
                } else {
                    if(!$request -> site){
                     $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id',$site_id_arr)->select('namecve')->get();
                     $CVEMapping = CVEMapping::select('id')->whereIn('namecve', $CVEMappingAssets_name)->count();
                 }else{
                    $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->first();
                    $CVEMappingAssets_name = CVEMappingAssets::where('site_id',$site_id_m->id)->select('namecve')->get();
                    $CVEMapping = CVEMapping::select('id')->whereIn('namecve', $CVEMappingAssets_name)->count();
                }
            }
        }
    }

    $response = array(
        'error' => '', 
        'status_code' => '200',
        'data' => @$CVEMapping
    );
    return response()->json($response);
}

public function count_compromised(Request $request){
    if(Auth::check()) {
        $role_custom = @check_role_custom();
        if($role_custom['compromised']) {
            $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            if(@get_role_custom()['superadmin'] == 1) {
                if(!$request -> site){
                    $DataLeakSocialRef = DataLeakSocialRef::select('id')->whereNull('deleted_at')->where('status', 1)->whereIn('feel_type', ['darkweb','webserver','server','compromise','compromised'])->count();
                }else{
                    $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->first();
                    $DataLeakSocialRef = DataLeakSocialRef::select('id')->whereNull('deleted_at')->where('site_id',$site_id_m->id)->where('status', 1)->whereIn('feel_type', ['darkweb','webserver','compromise','compromised'])->count();
                }
            } else {
                if(!$request -> site){
                    $DataLeakSocialRef = DataLeakSocialRef::select('id')->whereNull('deleted_at')->where('status', 1)->whereIn('site_id', $site_id_arr)->whereIn('feel_type', ['darkweb','webserver','server','compromise','compromised'])->count();
                }else{
                    $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->first();
                    $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('site_id', $site_id_m->id)->where('status', 1)->whereIn('site_id', $site_id_arr)->whereIn('feel_type', ['darkweb','webserver','compromise','compromised'])->count();
                }
            }
        }
    }


    $response = array(
        'error' => '', 
        'status_code' => '200',
        'data' => @$DataLeakSocialRef
    );
    return response()->json($response);
}

public function count_data_leak(Request $request){
    if(Auth::check()) {
        $role_custom = @check_role_custom();
        if($role_custom['data_leak']) {
            $site_id_arr = @get_role_custom()['site_id_arr'];
                // $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            if(@get_role_custom()['superadmin'] == 1) {
                if(!$request -> site){
                    $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('status',1)->whereNull('deleted_at')->whereIn('feel_type', ['social','darkweb_public'])->count();
                }else{
                    $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->first();
                    $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('status',1)->whereNull('deleted_at')->where('site_id', $site_id_m->id)->whereIn('feel_type', ['social','darkweb_public'])->count();
                }
            } else {
                if(!$request -> site){
                    $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('status',1)->whereNull('deleted_at')->whereIn('site_id', $site_id_arr)->whereIn('feel_type', ['social','darkweb_public'])->count();
                }else{
                    $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->first();
                    $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('status',1)->whereNull('deleted_at')->where('site_id', $site_id_m->id)->whereIn('site_id', $site_id_arr)->whereIn('feel_type', ['social','darkweb_public'])->count();
                }
            }
        }
    }

    $response = array(
        'error' => '', 
        'status_code' => '200',
        'data' => @$DataLeakSocialRef
    );
    return response()->json($response);
}

public function count_vulnerability_host(Request $request){
/*
    if(Auth::check()) {
        $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
        if(@get_role_custom()['superadmin'] == 1) {
            if(!$request -> site){
                $CVEAssets = CVEAssets::select('vendor', 'title')->where("active", '=', 1)->groupBy('vendor', 'title')->get();
            }else{
                $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->first();
                $CVEAssets = CVEAssets::select('vendor', 'title')->where('site_id', $site_id_m->id)->where("active", '=', 1)->groupBy('vendor', 'title')->get();
            }
        } else {
            if(!$request -> site){
                $CVEAssets = CVEAssets::select('vendor', 'title')->where("active", '=', 1)->whereIn('site_id', $site_id_arr)->groupBy('vendor', 'title')->get();
            }else{
                $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->first();
                $CVEAssets = CVEAssets::select('vendor', 'title')->where('site_id', $site_id_m->id)->where("active", '=', 1)->whereIn('site_id', $site_id_arr)->groupBy('vendor', 'title')->get();
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
        $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->first();
        if(@get_role_custom()['superadmin'] == 1) {
            if(!$request -> site) {
                $CVEMapping = CVEMapping::select('namecve', 'severity')->whereIn('namecve', $namecve)->groupBy('severity','namecve')->get();
            } else {

              $CVEMappingAssets_name = CVEMappingAssets::where('site_id',$site_id_m->id)->select('namecve')->get();

              $CVEMapping = CVEMapping::select('namecve', 'severity')->whereIn('namecve', $CVEMappingAssets_name)->groupBy('severity','namecve')->get();
          } 
      } else {
        if(!$request -> site) {
          $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id',$site_id_arr)->select('namecve')->get();
          $CVEMapping = CVEMapping::select('namecve', 'severity')->whereIn('namecve', $CVEMappingAssets_name)->groupBy('severity','namecve')->get();
      } else {
       $CVEMappingAssets_name = CVEMappingAssets::where('site_id',$site_id_m->id)->select('namecve')->get();
       $CVEMapping = CVEMapping::select('namecve', 'severity')->whereIn('namecve', $CVEMappingAssets_name)->groupBy('severity','namecve')->get();
   }

}
}
        // dd($check_total_namecve);
       // $CVEMapping = CVEMapping::select('namecve', 'severity')->groupBy('severity','namecve')->get();
foreach($CVEMapping as $data){
    foreach($check_total_namecve as $item){
        if($data -> namecve == $item['namecve']){
            $data['total'] = $item['total'];
            $data['title'] = $item['title'];
            $host_name[] = $item['title'];
        }
    }
}

*/
$result = array();
$CVEMapping= array();
//foreach ($host_name as $element) {
  //  $result[$element] = $element;
//}

$role_custom = @check_role_custom();
if(!$role_custom['vulnerabilities']) {
    $CVEMapping = [];
    $result = [];
}

//============ปรับรูปแบบใหม่=====================
$total_critical = array();
$total_high = array();
$total_medium = array();
$total_low = array();
$total_infomation  = array();

if(Auth::check()) {
    $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
    if(@get_role_custom()['superadmin'] == 1) {
        if(!$request -> site){
            $cve_host_name_summarys_data =  DB::select('SELECT title,sum(status_critical) as status_critical,sum(status_high) as status_high,sum(status_medium) as status_medium,sum(status_low) as status_low,sum(status_infomation) as status_infomation FROM sosecure_insight.fx_cve_host_name_summarys where site_id = 0 group by title');
        }else{
            $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->first();
            $cve_host_name_summarys_data =  DB::select('SELECT title,sum(status_critical) as status_critical,sum(status_high) as status_high,sum(status_medium) as status_medium,sum(status_low) as status_low,sum(status_infomation) as status_infomation FROM sosecure_insight.fx_cve_host_name_summarys where site_id = '.$site_id_m->id.' group by title');
        }
    } else {
        if(!$request -> site){
          //  $CVEAssets = CVEAssets::select('vendor', 'title')->where("active", '=', 1)->whereIn('site_id', $site_id_arr)->groupBy('vendor', 'title')->get();
            $site_id_List = '('.implode(',',$site_id_arr).')';
            $cve_host_name_summarys_data =  DB::select('SELECT title,sum(status_critical) as status_critical,sum(status_high) as status_high,sum(status_medium) as status_medium,sum(status_low) as status_low,sum(status_infomation) as status_infomation FROM sosecure_insight.fx_cve_host_name_summarys where site_id in '.$site_id_List.' group by title');
        }else{
            $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->first();
          //  $CVEAssets = CVEAssets::select('vendor', 'title')->where('site_id', $site_id_m->id)->where("active", '=', 1)->whereIn('site_id', $site_id_arr)->groupBy('vendor', 'title')->get();
           $cve_host_name_summarys_data =  DB::select('SELECT title,sum(status_critical) as status_critical,sum(status_high) as status_high,sum(status_medium) as status_medium,sum(status_low) as status_low,sum(status_infomation) as status_infomation FROM sosecure_insight.fx_cve_host_name_summarys where site_id = '.$site_id_m->id.' group by title');
        }
    }
}

foreach($cve_host_name_summarys_data as $host_name){
  
    array_push($result,$host_name->title);
    if($host_name->status_high > 0){
        array_push($total_high,(int)$host_name->status_high);
    }else{
        array_push($total_high,0);
    }
    if($host_name->status_critical > 0){
        array_push($total_critical,(int)$host_name->status_critical);
    }else{
        array_push($total_critical,0);
    }
    if($host_name->status_medium > 0){
        array_push($total_medium,(int)$host_name->status_medium);
    }else{
        array_push($total_medium,0);
    }
    if($host_name->status_low > 0){
        array_push($total_low,(int)$host_name->status_low);
    }else{
        array_push($total_low,0);
    }
    if($host_name->status_infomation > 0){
        array_push($total_infomation,(int)$host_name->status_infomation);
    }else{
        array_push($total_infomation,0);
    } 


}

$response = array(
    'error' => '', 
    'status_code' => '200',
    'data' => [
        'data' => $CVEMapping,
        'host_name' => $result,
        'total_critical' => $total_critical,
        'total_high' => $total_high,
        'total_medium' => $total_medium,
        'total_low' => $total_low,
        'total_infomation' => $total_infomation,
        'user_id' =>@Auth::user()->id,
        'side_code' =>$request -> site

    ]
);
return response()->json($response);
}

public function chart_indicators(Request $request){

    $role_custom = @check_role_custom();
    if($role_custom['indicators']) {
        if($request->displayType == 'mon'){
                $currentMonth = 2;//year - current is 2 old is 1
                $IndicatorSummaryYear = IndicatorSummaryYear::where("status", '=', 1)->where('year', $currentMonth)->where('type','summary_month')->get();
                $events = array_fill(0, (int)date('t'), 0);
                $attribute = array_fill(0, (int)date('t'), 0);
                foreach($IndicatorSummaryYear  as $value){
                    $events[$value->month-1] = $value->event_count;
                    $attribute[$value->month-1] = $value->attribute_count;
                }
                $nameXAxis = array();
                foreach ($events as $key => $value) {
                    $nameXAxis[$key] = (string)($key+1);
                }
                $nameYAxis = 'Number (Days)';
                $nameSeriesEvent = 'Number of Event';
                $nameSeriesAttribute = 'Number of Attribute';

            }else{
                $nameXAxis = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $nameYAxis = 'Number (Months)';
                $nameSeriesEvent = 'Number of Event';
                $nameSeriesAttribute = 'Number of Attribute';
                $IndicatorSummaryYear = IndicatorSummaryYear::where("status", '=', 1)->where('year', now()->year)->where('type','summary_year')->get();
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
            }
        } else {
            $events = [];
            $attribute = [];
            $nameXAxis = [];
            $nameYAxis = '';
            $nameSeriesAttribute = '';
            $nameSeriesEvent = '';
        }


        $response = array(
            'error' => '', 
            'status_code' => '200',
            'data' => [
                'events' => $events,
                'attribute' => $attribute,
                'nameXAxis' => $nameXAxis,
                'nameYAxis' => $nameYAxis,
                'nameSeriesAttribute' => $nameSeriesAttribute,
                'nameSeriesEvent' => $nameSeriesEvent,
                
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

    public function table_dashboard(Request $request)
    {



        $model = '';
        $html = '';

        $date_start_explode = explode(" ",$request->startDate);
        $date_start_date = @$date_start_explode[0];
        $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
        // dd($date_start_time);
        $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
        // dd($date_start_date_format);
        $date_start_time_time = date("H:i", strtotime($date_start_time));
        $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
        // dd($date_start_time_time);

        $date_end_explode = explode(" ",$request->endDate);
        $date_end_date = @$date_end_explode[0];
        $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
        // dd($date_end_time);
        $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
        $date_end_time_time = date("H:i", strtotime($date_end_time));
        $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';

        $SiteSettings = null;

        if($request->sitecode){
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->where("code",$request->sitecode)->first();
        }
        $site_id_arr = @get_role_custom()['site_id_arr'];

        
        $dataCVEMapping = array();
        $dataR_s_s_news = array();
        $DataLeakFeed_social = array();
        $DataLeakFeed_compromised = array();
        $data_fx_otx_events = array();
        $WebdefacmentSetting = array();
        $TransactionScans = array();
        if (!$request->pagename||$request->pagename=='Vulnerability') {
            $role_custom = @check_role_custom();
            if($role_custom['vulnerabilities']) {
                if(@get_role_custom()['superadmin'] == 1) {
                    if(!$request -> sitecode){
                        $CVEMappingAssets_name = CVEMappingAssets::select('namecve')->get();
                        $dataCVEMapping = CVEMapping::select('data_datacve_mapping.namecve as content', 'data_datacve_mapping.created_at as datetime', 'site.name as sitename', DB::raw('CONCAT("/monitoringvulnerabilitys") AS link , "Vulnerabilities" AS pagename'))->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name)->whereBetween('data_datacve_mapping.created_at',array($date_start_datetime_format,$date_end_datetime_format));
                        // if(isset($SiteSettings->id)){
                            // $dataCVEMapping = $dataCVEMapping->whereIn("data_datacve_mapping.site_id",$site_id_arr);
                        // }
                        $dataCVEMapping = $dataCVEMapping->leftjoin('data_datacve_mapping_assets as cve_asset', 'data_datacve_mapping.namecve', '=', 'cve_asset.namecve');
                        $dataCVEMapping = $dataCVEMapping->leftjoin('site', 'cve_asset.site_id', '=', 'site.id')->groupby(['cve_asset.namecve','cve_asset.site_id'])->get()->toArray();
                    } else {
                        $CVEMappingAssets_name = CVEMappingAssets::where('site_id', $SiteSettings->id)->select('namecve')->get();
                        $dataCVEMapping = CVEMapping::select('data_datacve_mapping.namecve as content', 'data_datacve_mapping.created_at as datetime', 'site.name as sitename', DB::raw('CONCAT("/monitoringvulnerabilitys") AS link , "Vulnerabilities" AS pagename'))->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name)->whereBetween('data_datacve_mapping.created_at',array($date_start_datetime_format,$date_end_datetime_format));
                        // if(isset($SiteSettings->id)){
                        $dataCVEMapping = $dataCVEMapping->where("cve_asset.site_id",$SiteSettings->id);
                        // }
                        $dataCVEMapping = $dataCVEMapping->leftjoin('data_datacve_mapping_assets as cve_asset', 'data_datacve_mapping.namecve', '=', 'cve_asset.namecve');
                        $dataCVEMapping = $dataCVEMapping->leftjoin('site', 'cve_asset.site_id', '=', 'site.id')->groupby(['cve_asset.namecve','cve_asset.site_id'])->get()->toArray();
                    }
                } else {
                    if(!$request -> sitecode){
                        $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                        $dataCVEMapping = CVEMapping::select('data_datacve_mapping.namecve as content', 'data_datacve_mapping.created_at as datetime', 'site.name as sitename', DB::raw('CONCAT("/monitoringvulnerabilitys") AS link , "Vulnerabilities" AS pagename'))->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name)->whereBetween('data_datacve_mapping.created_at',array($date_start_datetime_format,$date_end_datetime_format));
                        // if(isset($SiteSettings->id)){
                        $dataCVEMapping = $dataCVEMapping->whereIn("cve_asset.site_id",$site_id_arr);
                        // }
                        $dataCVEMapping = $dataCVEMapping->leftjoin('data_datacve_mapping_assets as cve_asset', 'data_datacve_mapping.namecve', '=', 'cve_asset.namecve');
                        $dataCVEMapping = $dataCVEMapping->leftjoin('site', 'cve_asset.site_id', '=', 'site.id')->groupby(['cve_asset.namecve','cve_asset.site_id'])->get()->toArray();
                    } else {
                        $CVEMappingAssets_name = CVEMappingAssets::where('site_id',$SiteSettings->id)->whereIn('site_id', $site_id_arr)->select('namecve')->get();
                        $dataCVEMapping = CVEMapping::select('data_datacve_mapping.namecve as content', 'data_datacve_mapping.created_at as datetime', 'site.name as sitename', DB::raw('CONCAT("/monitoringvulnerabilitys") AS link , "Vulnerabilities" AS pagename'))->whereIn('data_datacve_mapping.namecve', $CVEMappingAssets_name)->whereBetween('data_datacve_mapping.created_at',array($date_start_datetime_format,$date_end_datetime_format));
                        // if(isset($SiteSettings->id)){
                        $dataCVEMapping = $dataCVEMapping->where("cve_asset.site_id",$SiteSettings->id);
                        // }
                        $dataCVEMapping = $dataCVEMapping->leftjoin('data_datacve_mapping_assets as cve_asset', 'data_datacve_mapping.namecve', '=', 'cve_asset.namecve');
                        $dataCVEMapping = $dataCVEMapping->leftjoin('site', 'cve_asset.site_id', '=', 'site.id')->groupby(['cve_asset.namecve','cve_asset.site_id'])->get()->toArray();
                    }
                }
            }
        }

        if (!$request->pagename||$request->pagename=='News') {
            $role_custom = @check_role_custom();
            if($role_custom['news']) {
                $dataR_s_s_news_th = R_s_s_news::select('title_th as content', 'created_at as datetime', DB::raw(' "All Site" as sitename,CONCAT("/news/public/news/detail/",code ,"/th") AS link , "News" AS pagename'))->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))
                ->where(function ($query) {
                    $query->whereNotNull('title_th')->where('title_th', '!=', '');//detail_th   
                })->get()->toArray();
                $dataR_s_s_news_en = R_s_s_news::select('title_en as content', 'created_at as datetime', DB::raw(' "All Site" as sitename,CONCAT("/news/public/news/detail/",code ,"/en") AS link , "News" AS pagename'))->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))
                ->where(function ($query) {
                    $query->whereNotNull('title_en')->where('title_en', '!=', '');//detail_en
                })->get()->toArray();
                $dataR_s_s_news = array_merge($dataR_s_s_news_th,$dataR_s_s_news_en);
            }
        }

        if (!$request->pagename||$request->pagename=='Data Leak') {
            $role_custom = @check_role_custom();
            if($role_custom['data_leak']) {
                if(@get_role_custom()['superadmin'] == 1) {//|| @get_role_custom()['site_admin'] == 1
                if(!$request -> sitecode){
                    $DataLeakFeed_social = DataLeakFeedTemp::select('id','feedcontent as content', 'created_at as datetime', DB::raw(' "" as sitename,CONCAT("/datafeedsocial") AS link , "Data Leak" AS pagename'))->whereNull('deleted_at')->where('status',1)->whereIn('feed_type', ['social','darkweb_public'])->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))->get()->toArray();
                    foreach ($DataLeakFeed_social as $key => $value) {
                        $leak_socail_ref_temps = leak_socail_ref_temp::select('site_id')->where('status',1)->whereNull('deleted_at')->where('status',1)->where('data_leak_feed_id', $value["id"])->first();
                        if($leak_socail_ref_temps){
                            if(isset($SiteSettings->id)){
                                $pos = strpos($leak_socail_ref_temps->site_id, $SiteSettings->id."");
                                if ($pos === false) {
                                    unset($DataLeakFeed_social[$key]);
                                    continue;
                                }
                            }
                            $site = SiteSettings::select('name')->whereIn('id', explode("," , $leak_socail_ref_temps->site_id))->get();
                            $name_site = '';
                            foreach ($site as $data) {
                                $name_site .= $data->name . ' ,';
                            }
                            $name_site = rtrim($name_site, " ,");
                            $DataLeakFeed_social[$key]["sitename"] = $name_site;
                        }else{
                            unset($DataLeakFeed_social[$key]);
                        }

                    }
                } else {
                    $DataLeakFeed_social = DataLeakFeedTemp::select('id','feedcontent as content', 'created_at as datetime', DB::raw(' "" as sitename,CONCAT("/datafeedsocial") AS link , "Data Leak" AS pagename'))->whereNull('deleted_at')->where('status',1)->whereIn('feed_type', ['social','darkweb_public'])->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))->get()->toArray();
                    foreach ($DataLeakFeed_social as $key => $value) {
                        $leak_socail_ref_temps = leak_socail_ref_temp::select('site_id')->where('status',1)->whereNull('deleted_at')->where('status',1)->where('data_leak_feed_id', $value["id"])->where('site_id',$SiteSettings->id)->first();
                        if($leak_socail_ref_temps){
                            if(isset($SiteSettings->id)){
                                $pos = strpos($leak_socail_ref_temps->site_id, $SiteSettings->id."");
                                if ($pos === false) {
                                    unset($DataLeakFeed_social[$key]);
                                    continue;
                                }
                            }
                            $site = SiteSettings::select('name')->whereIn('id', explode("," , $leak_socail_ref_temps->site_id))->get();
                            $name_site = '';
                            foreach ($site as $data) {
                                $name_site .= $data->name . ' ,';
                            }
                            $name_site = rtrim($name_site, " ,");
                            $DataLeakFeed_social[$key]["sitename"] = $name_site;
                        }else{
                            unset($DataLeakFeed_social[$key]);
                        }

                    }
                }
            }else{
                if(!$request -> sitecode){
                    $DataLeakFeed_social = DataLeakFeed::select('id','feedcontent as content', 'created_at as datetime', DB::raw(' "" as sitename,CONCAT("/socialdatas") AS link , "Data Leak" AS pagename'))->whereNull('deleted_at')->whereIn('feel_type', ['social','darkweb_public'])->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))->get()->toArray();
                    foreach ($DataLeakFeed_social as $key => $value) {
                        $leak_socail_ref_temps = DataLeakSocialRef::select('site_id')->where('status',1)->whereNull('deleted_at')->where('data_leak_feed_id', $value["id"])->whereIn('site_id',$site_id_arr)->first();
                        if($leak_socail_ref_temps){
                            $site = SiteSettings::select('name')->whereIn('id', explode("," , $leak_socail_ref_temps->site_id))->get();
                            $name_site = '';
                            foreach ($site as $data) {
                                $name_site .= $data->name . ' ,';
                            }
                            $name_site = rtrim($name_site, " ,");
                            $DataLeakFeed_social[$key]["sitename"] = $name_site;
                        }else{
                            unset($DataLeakFeed_social[$key]);
                        }
                    }
                } else {
                    $DataLeakFeed_social = DataLeakFeed::select('id','feedcontent as content', 'created_at as datetime', DB::raw(' "" as sitename,CONCAT("/socialdatas") AS link , "Data Leak" AS pagename'))->whereNull('deleted_at')->where('status',1)->whereIn('feel_type', ['social','darkweb_public'])->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))->get()->toArray();
                    foreach ($DataLeakFeed_social as $key => $value) {
                        $leak_socail_ref_temps = DataLeakSocialRef::select('site_id')->where('status',1)->whereNull('deleted_at')->where('data_leak_feed_id', $value["id"])->where('site_id',$SiteSettings->id)->first();
                        if($leak_socail_ref_temps){
                            $site = SiteSettings::select('name')->whereIn('id', explode("," , $leak_socail_ref_temps->site_id))->get();
                            $name_site = '';
                            foreach ($site as $data) {
                                $name_site .= $data->name . ' ,';
                            }
                            $name_site = rtrim($name_site, " ,");
                            $DataLeakFeed_social[$key]["sitename"] = $name_site;
                        }else{
                            unset($DataLeakFeed_social[$key]);
                        }
                    }
                }
            }
        }
    }


        // if (!$request->pagename||$request->pagename=='Compromised') {
        //     if(@get_role_custom()['superadmin'] == 1) {//|| @get_role_custom()['site_admin'] == 1
        //         $DataLeakFeed_compromised = DataLeakFeed::select('id','source_name as content', 'created_at as datetime', DB::raw(' "" as sitename,CONCAT("/socialdatas") AS link , "Compromised" AS pagename'))->whereNull('deleted_at')->where('feel_type','!=', 'social')->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))->get()->toArray();
        //         foreach ($DataLeakFeed_compromised as $key => $value) {
        //             $leak_socail_ref = DataLeakSocialRef::select('site_id')->whereNull('deleted_at')->where('data_leak_feed_id', $value["id"])->first();
        //             if($leak_socail_ref){
        //                 if(isset($SiteSettings->id)){
        //                     $pos = strpos($leak_socail_ref->site_id, $SiteSettings->id."");
        //                     if ($pos === false) {
        //                         unset($DataLeakFeed_compromised[$key]);
        //                         continue;
        //                     }
        //                 }
        //                 $site = SiteSettings::select('name')->where('id', $leak_socail_ref->site_id)->first();
        //                 $name_site='';
        //                 if($site){
        //                     $name_site = $site->id;
        //                 }
        //                 $DataLeakFeed_compromised[$key]["sitename"] = $name_site;
        //             }else{
        //                 unset($DataLeakFeed_compromised[$key]);
        //             }
        //         }
        //     }else{
        //         $DataLeakFeed_compromised = DataLeakFeed::select('id','source_name as content', 'created_at as datetime', DB::raw(' "" as sitename,CONCAT("/socialdatas") AS link , "Compromised" AS pagename'))->whereNull('deleted_at')->where('feel_type', '!=' , 'social')->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))->get()->toArray();
        //         foreach ($DataLeakFeed_compromised as $key => $value) {
        //             $leak_socail_ref = DataLeakSocialRef::select('site_id')->whereNull('deleted_at')->where('data_leak_feed_id', $value["id"])->first();
        //             if($leak_socail_ref){
        //                 $site = SiteSettings::select('name')->where('id', $leak_socail_ref->site_id)->first();
        //                 $name_site = '';
        //                 if($site){
        //                     $name_site = $site->id;
        //                 }
        //                 $DataLeakFeed_compromised[$key]["sitename"] = $name_site;
        //             }else{
        //                 unset($DataLeakFeed_compromised[$key]);
        //             }
        //         }
        //     }
        // }

    if (!$request->pagename||$request->pagename=='Compromised') {
        $role_custom = @check_role_custom();
        if($role_custom['compromised']) {
                if(@get_role_custom()['superadmin'] == 1) {//|| @get_role_custom()['site_admin'] == 1
                if(!$request -> sitecode){
                    $DataLeakFeed_compromised = DataLeakFeedTemp::select('data_leak_feed_temp.feedlink as content', 'data_leak_feed_temp.created_at as datetime', 'site.name as sitename',DB::raw('CONCAT("/datafeed_darkweb") AS link , "Compromised" AS pagename'))->whereNull('data_leak_feed_temp.deleted_at')->where('data_leak_socail_ref_temp.status',1)->whereIn('data_leak_feed_temp.feed_type', ['darkweb','webserver','compromise','compromised'])->whereBetween('data_leak_feed_temp.created_at',array($date_start_datetime_format,$date_end_datetime_format));
                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->leftjoin('data_leak_socail_ref_temp', 'data_leak_feed_temp.id', '=', 'data_leak_socail_ref_temp.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref_temp.site_id', '=', 'site.id');
                        // if(isset($SiteSettings->id)){
                            // $DataLeakFeed_compromised = $DataLeakFeed_compromised->where('site.id', $SiteSettings->id);
                        // }
                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->get()->toArray();
                } else {
                    $DataLeakFeed_compromised = DataLeakFeedTemp::select('data_leak_feed_temp.feedlink as content', 'data_leak_feed_temp.created_at as datetime', 'site.name as sitename',DB::raw('CONCAT("/datafeed_darkweb") AS link , "Compromised" AS pagename'))->whereNull('data_leak_feed_temp.deleted_at')->where('data_leak_socail_ref_temp.status',1)->whereIn('data_leak_feed_temp.feed_type',['darkweb','webserver','compromise','compromised'])->whereBetween('data_leak_feed_temp.created_at',array($date_start_datetime_format,$date_end_datetime_format));
                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->leftjoin('data_leak_socail_ref_temp', 'data_leak_feed_temp.id', '=', 'data_leak_socail_ref_temp.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref_temp.site_id', '=', 'site.id');
                        // if(isset($SiteSettings->id)){
                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->where('site.id', $SiteSettings->id);
                        // }
                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->get()->toArray();
                }
            }else{
                if(!$request -> sitecode){
                    $DataLeakFeed_compromised = DataLeakFeed::select('data_leak_feed.source_name as content', 'data_leak_feed.created_at as datetime', 'site.name as sitename',DB::raw('CONCAT("/darkweb-datas") AS link , "Compromised" AS pagename'))->whereNull('data_leak_feed.deleted_at')->where('data_leak_socail_ref.status',1)->whereIn('data_leak_feed.feel_type',['darkweb','webserver','compromise','compromised'])->whereBetween('data_leak_feed.created_at',array($date_start_datetime_format,$date_end_datetime_format));
                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');
                        // if(isset($SiteSettings->id)){
                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->whereIn('site.id', $site_id_arr);
                        // }
                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->get()->toArray();
                } else {
                    $DataLeakFeed_compromised = DataLeakFeed::select('data_leak_feed.source_name as content', 'data_leak_feed.created_at as datetime', 'site.name as sitename',DB::raw('CONCAT("/darkweb-datas") AS link , "Compromised" AS pagename'))->whereNull('data_leak_feed.deleted_at')->where('data_leak_socail_ref.status',1)->whereIn('data_leak_feed.feel_type',['darkweb','webserver','compromise','compromised'])->whereBetween('data_leak_feed.created_at',array($date_start_datetime_format,$date_end_datetime_format));
                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');
                        // if(isset($SiteSettings->id)){
                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->where('site.id', $SiteSettings->id);
                        // }
                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->get()->toArray();
                }
            }
        }
    }

    if (!$request->pagename||$request->pagename=='Web Defacement') {
        $role_custom = @check_role_custom();
        if($role_custom['web_defacement']) {
                if(@get_role_custom()['superadmin'] == 1) {//|| @get_role_custom()['site_admin'] == 1
                if(!$request -> sitecode){
                    $WebdefacmentSetting = WebdefacmentSetting::select('webdefacment_setting.last_check as datetime', 'site.name as sitename','site.id as site_id',DB::raw('CONCAT("/webdefacement/detail/",fx_webdefacment_setting.code) AS link , "Web Defacement" AS pagename , CONCAT(fx_webdefacment_setting.name,"||",fx_webdefacment_setting.url,"||",fx_webdefacment_setting.status_val) AS content'))->whereNull('webdefacment_setting.deleted_at')->where('webdefacment_setting.active',1)->where('webdefacment_setting.status_add',1)->whereBetween('webdefacment_setting.created_at',array($date_start_datetime_format,$date_end_datetime_format));
                        // $WebdefacmentSetting = $WebdefacmentSetting->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id');
                    $WebdefacmentSetting = $WebdefacmentSetting->leftjoin('site', 'webdefacment_setting.site_id', '=', 'site.id');

                            //$WebdefacmentSetting = $WebdefacmentSetting->whereIn('site_id', $site_id_arr);
                    $WebdefacmentSetting = $WebdefacmentSetting->get()->toArray();
                        // }
                } else {
                    $WebdefacmentSetting = WebdefacmentSetting::select('webdefacment_setting.name as content', 'webdefacment_setting.last_check as datetime', 'site.name as sitename','site.id as site_id',DB::raw('CONCAT("/webdefacement") AS link , "Web Defacement" AS pagename, CONCAT(fx_webdefacment_setting.name,"||",fx_webdefacment_setting.url,"||",fx_webdefacment_setting.status_val) AS content'))->whereNull('webdefacment_setting.deleted_at')->where('webdefacment_setting.active',1)->where('webdefacment_setting.status_add',1)->whereBetween('webdefacment_setting.created_at',array($date_start_datetime_format,$date_end_datetime_format));
                    $WebdefacmentSetting = $WebdefacmentSetting->leftjoin('site', 'webdefacment_setting.site_id', '=', 'site.id');

                    $WebdefacmentSetting = $WebdefacmentSetting->where('site.id', $SiteSettings->id);
                    $WebdefacmentSetting = $WebdefacmentSetting->get()->toArray();
                }
            }else{
                if(!$request -> sitecode){
                    $WebdefacmentSetting = WebdefacmentSetting::select('webdefacment_setting.last_check as datetime', 'site.name as sitename','site.id as site_id',DB::raw('CONCAT("/webdefacement/detail/",fx_webdefacment_setting.code) AS link , "Web Defacement" AS pagename , CONCAT(fx_webdefacment_setting.name,"||",fx_webdefacment_setting.url,"||",fx_webdefacment_setting.status_val) AS content'))->whereNull('webdefacment_setting.deleted_at')->where('webdefacment_setting.active',1)->where('webdefacment_setting.status_add',1)->whereBetween('webdefacment_setting.created_at',array($date_start_datetime_format,$date_end_datetime_format));
                    $WebdefacmentSetting = $WebdefacmentSetting->leftjoin('site', 'webdefacment_setting.site_id', '=', 'site.id');

                    $WebdefacmentSetting = $WebdefacmentSetting->whereIn('site_id', $site_id_arr);
                    $WebdefacmentSetting = $WebdefacmentSetting->get()->toArray();
                } else {
                    $WebdefacmentSetting = WebdefacmentSetting::select('webdefacment_setting.name as content', 'webdefacment_setting.last_check as datetime', 'site.name as sitename','site.id as site_id',DB::raw('CONCAT("/webdefacement") AS link , "Web Defacement" AS pagename, CONCAT(fx_webdefacment_setting.name,"||",fx_webdefacment_setting.url,"||",fx_webdefacment_setting.status_val) AS content'))->whereNull('webdefacment_setting.deleted_at')->where('webdefacment_setting.active',1)->where('webdefacment_setting.status_add',1)->whereBetween('webdefacment_setting.created_at',array($date_start_datetime_format,$date_end_datetime_format));
                    $WebdefacmentSetting = $WebdefacmentSetting->leftjoin('site', 'webdefacment_setting.site_id', '=', 'site.id');

                        // if(isset($SiteSettings->id)){
                        //     $DataLeakFeed_compromised = $DataLeakFeed_compromised->where('site.id', $SiteSettings->id);
                        // } else {
                    $WebdefacmentSetting = $WebdefacmentSetting->where('site.id', $SiteSettings->id);
                    $WebdefacmentSetting = $WebdefacmentSetting->get()->toArray();
                        // }
                }
            }
        }
    }

    if (!$request->pagename||$request->pagename=='assets') {
        $role_custom = @check_role_custom();
        if($role_custom['assets']) {
                if(@get_role_custom()['superadmin'] == 1) {//|| @get_role_custom()['site_admin'] == 1
                if(!$request -> sitecode){
                        // $TransactionTimeStampScans = TransactionTimeStampScans::where('code', $SiteSettings->code)->first();

                    $TransactionScans = TransactionScans::select('transaction_time_stamp_scans.code as t_code','transaction_scans.updated_at as datetime', 'site.name as sitename','site.id as site_id',DB::raw('CONCAT("/scans/scans-domain/datatype/",fx_transaction_time_stamp_scans.code) AS link , "Assets" AS pagename , CONCAT(IFNULL(fx_transaction_scans.data_type,""),"||",IFNULL(fx_transaction_scans.raw_data,""),"||",IFNULL(fx_transaction_scans.referent,""),"||",IFNULL(fx_transaction_scans.status,"")) AS content'))->where('transaction_scans.status',1)->where('transaction_scans.module','!=','sfp_citadel')->orderBy('transaction_scans.status', 'desc');
                    $TransactionScans = $TransactionScans->leftjoin('site', 'transaction_scans.site_id', '=', 'site.id');
                    $TransactionScans = $TransactionScans->leftjoin('transaction_time_stamp_scans', 'site.id', '=', 'transaction_time_stamp_scans.site_id');

                    $TransactionScans = $TransactionScans->whereBetween('transaction_scans.updated_at',array($date_start_datetime_format,$date_end_datetime_format))->get()->toArray();
                } else {
                        // $TransactionTimeStampScans = TransactionTimeStampScans::where('site_id', $SiteSettings->id)->first();

                    $TransactionScans = TransactionScans::select('transaction_time_stamp_scans.code as t_code','transaction_scans.updated_at as datetime', 'site.name as sitename','site.id as site_id',DB::raw('CONCAT("/scans/scans-domain/datatype/",fx_transaction_time_stamp_scans.code) AS link , "Assets" AS pagename , CONCAT(IFNULL(fx_transaction_scans.data_type,""),"||",IFNULL(fx_transaction_scans.raw_data,""),"||",IFNULL(fx_transaction_scans.referent,""),"||",IFNULL(fx_transaction_scans.status,"")) AS content'))->where('transaction_scans.site_id', $SiteSettings->id)->where('transaction_scans.status',1)->where('transaction_scans.module','!=','sfp_citadel')->orderBy('transaction_scans.status', 'desc');
                    $TransactionScans = $TransactionScans->leftjoin('site', 'transaction_scans.site_id', '=', 'site.id');
                    $TransactionScans = $TransactionScans->leftjoin('transaction_time_stamp_scans', 'site.id', '=', 'transaction_time_stamp_scans.site_id');

                    $TransactionScans = $TransactionScans->whereBetween('transaction_scans.updated_at',array($date_start_datetime_format,$date_end_datetime_format))->get()->toArray();
                }
            }else{
                if(!$request -> sitecode){
                        // $TransactionTimeStampScans = TransactionTimeStampScans::where('site_id', $SiteSettings->id)->first();

                    $TransactionScans = TransactionScans::select('transaction_time_stamp_scans.code as t_code','transaction_scans.updated_at as datetime', 'site.name as sitename','site.id as site_id',DB::raw('CONCAT("/scans/scans-domain/datatype/",fx_transaction_time_stamp_scans.code) AS link , "Assets" AS pagename , CONCAT(IFNULL(fx_transaction_scans.data_type,""),"||",IFNULL(fx_transaction_scans.raw_data,""),"||",IFNULL(fx_transaction_scans.referent,""),"||",IFNULL(fx_transaction_scans.status,"")) AS content'))->where('transaction_scans.site_id', $SiteSettings->id)->where('transaction_scans.status',1)->where('transaction_scans.module','!=','sfp_citadel')->orderBy('transaction_scans.status', 'desc');
                    $TransactionScans = $TransactionScans->leftjoin('site', 'transaction_scans.site_id', '=', 'site.id');
                    $TransactionScans = $TransactionScans->leftjoin('transaction_time_stamp_scans', 'site.id', '=', 'transaction_time_stamp_scans.site_id');
                    $TransactionScans = $TransactionScans->whereIn('site_id', $site_id_arr);
                    $TransactionScans = $TransactionScans->whereBetween('transaction_scans.updated_at',array($date_start_datetime_format,$date_end_datetime_format))->get()->toArray();
                } else {
                        // $TransactionTimeStampScans = TransactionTimeStampScans::where('site_id', $SiteSettings->id)->first();

                    $TransactionScans = TransactionScans::select('transaction_time_stamp_scans.code as t_code','transaction_scans.updated_at as datetime', 'site.name as sitename','site.id as site_id',DB::raw('CONCAT("/scans/scans-domain/datatype/",fx_transaction_time_stamp_scans.code) AS link , "Assets" AS pagename , CONCAT(IFNULL(fx_transaction_scans.data_type,""),"||",IFNULL(fx_transaction_scans.raw_data,""),"||",IFNULL(fx_transaction_scans.referent,""),"||",IFNULL(fx_transaction_scans.status,"")) AS content'))->where('transaction_scans.site_id', $SiteSettings->id)->where('transaction_scans.status',1)->where('transaction_scans.module','!=','sfp_citadel')->orderBy('transaction_scans.status', 'desc');
                    $TransactionScans = $TransactionScans->leftjoin('site', 'transaction_scans.site_id', '=', 'site.id');
                    $TransactionScans = $TransactionScans->leftjoin('transaction_time_stamp_scans', 'site.id', '=', 'transaction_time_stamp_scans.site_id');
                    $TransactionScans = $TransactionScans->where('site.id', $SiteSettings->id)->whereIn('transaction_scans.site_id', $site_id_arr);
                    $TransactionScans = $TransactionScans->whereBetween('transaction_scans.updated_at',array($date_start_datetime_format,$date_end_datetime_format))->get()->toArray();
                }
            }
        }
    }

        // if (!$request->pagename||$request->pagename=='Indicators') {
        //     $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        //     $clientMD = new MongoClient($DB_MONGO_KEY);
        //     $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;

        //     $options = [
        //         'allowDiskUse' => TRUE
        //     ];

        //     $pipeline = [
        //         [
        //             '$match' => [
        //                 'created_at'  => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)],
        //             ]
        //         ],
        //         [
        //             '$project' => [
        //                 '_id' => 0,
        //                 'sitename' => 'All Site',
        //                 'content' => '$name',
        //                 'datetime' => ['$dateToString'=>['format'=>'%Y-%m-%d %H:%M:%S','date'=>'$created_at','timezone'=>'Asia/Bangkok']],
        //                 'pagename' => 'Indicators',
        //                 'link' => [ '$concat' => ['/indicators/events/events_detail/','$pulse_id']],
        //             ]
        //         ]
        //     ];

        //     // dd($pipeline);
        //     $data_fx_otx_events = $col_fx_otx_events->aggregate($pipeline,$options);

        //     $data_fx_otx_events = $data_fx_otx_events->toArray();

        // }


    $model = array_merge(@$dataCVEMapping,@$dataR_s_s_news,@$DataLeakFeed_social,@$DataLeakFeed_compromised,@$WebdefacmentSetting,@$TransactionScans);
    $dataOut = array();
    usort($model, function($a, $b) {
        $t1 = strtotime($a['datetime']);
        $t2 = strtotime($b['datetime']);
        return $t2 - $t1;
    });

    $dataOut["data"] =  $model;
    return response()->json($dataOut);
}

public function load_chart(Request $request)
{
    //   $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id',$site_id_arr)->select('namecve')->get();


    if(Auth::check()) 
    {
        
        $role_custom = @check_role_custom();
        if($role_custom['vulnerabilities']) 
        {
            $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
            if(@get_role_custom()['superadmin'] == 1) 
            {
                
                if(!$request -> site)
                {
                    // $model = new CVEMapping;
                    // $model->get();
                    // $high = $model->where('severity', '=', 'HIGH')->count();
                    // $medium = $model->where('severity', '=', 'MEDIUM')->count();
                    // $critical = $model->where('severity', '=', 'CRITICAL')->count();
                    // $low = $model->where('severity', '=', 'LOW')->count();
                    // $none = $model->where('severity', '=', 'NONE')->count();

                    $none = DB::table('summary')
                        ->where('status', 'Y')
                        ->where('data_text', 'Information')
                        ->where('data_key_2', 'Vulnerability')
                        ->select( DB::raw('sum(data_value) as sum'))
                        ->first()
                        ->sum;
                    $none = intval($none);

                    $low = DB::table('summary')
                        ->where('status', 'Y')
                        ->where('data_text', 'Low')
                        ->where('data_key_2', 'Vulnerability')
                        ->select( DB::raw('sum(data_value) as sum'))
                        ->first()
                        ->sum;
                    $low = intval($low);

                    $medium = DB::table('summary')
                        ->where('status', 'Y')
                        ->where('data_text', 'Medium')
                        ->where('data_key_2', 'Vulnerability')
                        ->select( DB::raw('sum(data_value) as sum'))
                        ->first()
                        ->sum;
                    $medium = intval($medium);

                    $high = DB::table('summary')
                        ->where('status', 'Y')
                        ->where('data_text', 'High')
                        ->where('data_key_2', 'Vulnerability')
                        ->select( DB::raw('sum(data_value) as sum'))
                        ->first()
                        ->sum;
                    $high = intval($high);

                    $critical = DB::table('summary')
                        ->where('status', 'Y')
                        ->where('data_text', 'Critical')
                        ->where('data_key_2', 'Vulnerability')
                        ->select( DB::raw('sum(data_value) as sum'))
                        ->first()
                        ->sum;
                    $critical = intval($critical);
                    

                }
                else
                {
                    // $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->first();
                    // $CVEMappingAssets_name = CVEMappingAssets::where('site_id', $site_id_m->id)->select('namecve')->get();
                    // $model = new CVEMapping;
                    // $model->get();
                    // $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->first();

                    // $high = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'HIGH')->count();
                    // $medium = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'MEDIUM')->count();
                    // $critical = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'CRITICAL')->count();
                    // $low = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'LOW')->count();
                    // $none = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'NONE')->count();

                    $none = DB::table('summary')
                        ->where('site', $request->site)
                        ->where('status', 'Y')
                        ->where('data_text', 'Information')
                        ->where('data_key_2', 'Vulnerability')
                        ->select('data_value')
                        ->first()
                        ->data_value;
                    $none = intval($none);
                  
                    $low = DB::table('summary')
                        ->where('site', $request->site)
                        ->where('status', 'Y')
                        ->where('data_text', 'Low')
                        ->where('data_key_2', 'Vulnerability')
                        ->select('data_value')
                        ->first()
                        ->data_value;
                    $low = intval($low);

                    $medium = DB::table('summary')
                        ->where('site', $request->site)
                        ->where('status', 'Y')
                        ->where('data_text', 'Medium')
                        ->where('data_key_2', 'Vulnerability')
                        ->select('data_value')
                        ->first()
                        ->data_value;
                    $medium = intval($medium);   

                    $high = DB::table('summary')
                        ->where('site', $request->site)
                        ->where('status', 'Y')
                        ->where('data_text', 'High')
                        ->where('data_key_2', 'Vulnerability')
                        ->select('data_value')
                        ->first()
                        ->data_value;
                    $high = intval($high);
                    
                    $critical = DB::table('summary')
                        ->where('site', $request->site)
                        ->where('status', 'Y')
                        ->where('data_text', 'Critical')
                        ->where('data_key_2', 'Vulnerability')
                        ->select('data_value')
                        ->first()
                        ->data_value;
                    $critical = intval($critical);

                }
            } 
            else 
            {
                if(!$request -> site)
                {
                    $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();
                    $model = new CVEMapping;
                    $model->get();
                    $high = $model->where('severity', '=', 'HIGH')->whereIn('namecve', $CVEMappingAssets_name)->count();
                    $medium = $model->where('severity', '=', 'MEDIUM')->whereIn('namecve', $CVEMappingAssets_name)->count();
                    $critical = $model->where('severity', '=', 'CRITICAL')->whereIn('namecve', $CVEMappingAssets_name)->count();
                    $low = $model->where('severity', '=', 'LOW')->whereIn('namecve', $CVEMappingAssets_name)->count();
                    $none = $model->where('severity', '=', 'NONE')->whereIn('namecve', $CVEMappingAssets_name)->count();
                }
                else
                {
                    $site_id_m = SiteSettings::select('id')->where('code',$request -> site)->first();
                    $CVEMappingAssets_name = CVEMappingAssets::where('site_id', $site_id_m->id)->select('namecve')->get();
                    $model = new CVEMapping;
                    $model->get();
                    $high = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'HIGH')->count();
                    $medium = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'MEDIUM')->count();
                    $critical = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'CRITICAL')->count();
                    $low = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'LOW')->count();
                    $none = $model->whereIn('namecve', $CVEMappingAssets_name)->where('severity', '=', 'NONE')->count();
                }
            }
        }
    }

    if($request->ajax()) 
    {
        $data = [
            "count_high" => @$high,
            "count_medium" => @$medium,
            "count_critical" => @$critical,
            "count_low" => @$low,
            "count_none" => @$none,
        ];

        return response()->json($data);
    }
}
}
