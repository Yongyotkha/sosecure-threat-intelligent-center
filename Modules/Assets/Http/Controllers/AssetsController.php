<?php

namespace Modules\Assets\Http\Controllers;

use Modules\Scans\Entities\Assets;
use Modules\Scans\Entities\AssetsData;
use Modules\Scans\Entities\CPE;

use App\Credentials;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Assets\Entities\CPEData;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\Assets\Entities\OSType;
use Auth;
use Modules\Users\Entities\UserSite;
use App\TransactionTimeStampScans;
use Modules\SiteSettings\Entities\Domain;
use App\transaction_client_cpe;
use Artisan;

class AssetsController extends Controller
{
    /**
     * Item Model
     *
     * @var \Modules\Items\Entities\Item
     */
    protected $item;
    protected $ip;
    protected $mac;
    protected $header;
    protected $client;
    protected $urlLimit = 3;
    protected $base_url;
    protected $url_indicator_events_table;
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
        if(TYPE_WEB !== 'center'){
            $this->ip = config('app.ip_ad');
            $this->mac = config('app.mac_ad');
            $this->header = config('app.site_key');
            $this->client = new \GuzzleHttp\Client();
            $this->base_url = config('app.url_center').'/api/v1/'.config('app.mode').'/'.config('app.site_code');
            $this->url_table_asset = $this->base_url.'/asset/table_asset';
        }
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['assets']) {
            check_permission403();
        }
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


        if(isset($this->request->Search_Link_All)){
            $data['Search_Link_All'] = $this->request->Search_Link_All;
        }else{
            $data['Search_Link_All'] = "";
        }
        


        $data['SiteSettings'] = $SiteSettings;
        $data['page'] = langapp('assets');
        $data['menu'] = 'system';
        return view('assets::index')->with($data);
    }

    public function index_all_asset()
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['assets']) {
            check_permission403();
        }
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


        if(isset($this->request->Search_Link_All)){
            $data['Search_Link_All'] = $this->request->Search_Link_All;
        }else{
            $data['Search_Link_All'] = "";
        }
        


        $data['SiteSettings'] = $SiteSettings;
        $data['page'] = langapp('assets_setting');
        $data['menu'] = 'setting';
        return view('assets::index')->with($data);
    }
    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('assets::create');
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
        return view('assets::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('assets::edit');
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

    public function assets_add_cpe(Request $request)
    {
        // $data['cpe'] = CPEData::where('status', 1)->get();
        // $data['SiteSettings'] = SiteSettings::where("active", 1)->where("deleted_at", null)->get();
        $data['os'] = OSType::get();
        $data['assets'] = Assets::where('code',$request -> id)->first();
        $data['Credentials'] = Credentials::select('code','name')->where('status', 1)->where('site_id', @$data['assets']->site_id)->get();
        $data['menu'] = $request->menu;
        $data['idip'] = $request->idip;
        return view('assets::modal.add_cpe')->with($data);
    }


    public function assets_delete_cpe(Request $request)
    {
        $data['cpe'] = CPE::select('id','result')->where('code', $request->cpecode)->first();
        $data['menu'] = $request->menu;
        return view('assets::modal.delete_cpe')->with($data);
    }

    public function methot_delete_cpe(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['assets']) {
            check_permission403();
        }
        $CPE = CPE::where('id', $request->cpecode)->first();
        if($CPE){
            $CPE_Asset_id = $CPE->asset_id;
            $CPE_id = $CPE->id;
            $Assetsfor = AssetsData::where('id',$CPE_Asset_id)->first();
            $transaction_client_cpe = transaction_client_cpe::where('site_id', $Assetsfor->site_id)->where('transaction_id', $CPE_id)->first();
            if($transaction_client_cpe){
                $transaction_client_cpe -> transaction_mode = 'delete';
                $transaction_client_cpe -> transaction_data_status = 1;
                $transaction_client_cpe -> status = 1;
                $transaction_client_cpe -> save();
            }else{
                $transaction_client_cpe = new transaction_client_cpe();
                $transaction_client_cpe -> site_id = $Assetsfor->site_id;
                $transaction_client_cpe -> transaction_id = $CPE_id;
                $transaction_client_cpe -> transaction_mode = 'delete';
                $transaction_client_cpe -> transaction_data_status = 1;
                $transaction_client_cpe -> status = 1;
                $transaction_client_cpe -> save();
            }


            $CPE->delete();
            if($request -> page == 'site'){

                $SiteSettingsfor = SiteSettings::withTrashed()->where('id', $Assetsfor->site_id)->first();
                return ajaxResponse(
                    [
                        'message' => langapp('changes_saved_successful'),
                        'redirect' => route('assetssite.index', ['id' => $SiteSettingsfor->code]),
                    ],
                    true,
                    Response::HTTP_OK
                );
            }else if($request -> page == 'scan'){

                $TransactionTimeStampScansfor = TransactionTimeStampScans::where('domain_id', $Assetsfor->domain_id)->first();
                return ajaxResponse(
                    [
                        'message' => langapp('changes_saved_successful'),
                        'redirect' => route('scans.index', ['tab' => 'asset', 'site_code' => $TransactionTimeStampScansfor->code]),
                    ],
                    true,
                    Response::HTTP_OK
                );
            }else if($request -> page == 'system'){
                return ajaxResponse(
                    [
                        'message' => langapp('changes_saved_successful'),
                        'redirect' => route('assets.index'),
                    ],
                    true,
                    Response::HTTP_OK
                );
            }else if($request -> page == 'setting'){
                return ajaxResponse(
                    [
                        'message' => langapp('changes_saved_successful'),
                        'redirect' => route('assets.index_setting'),
                    ],
                    true,
                    Response::HTTP_OK
                );
            }
        }

        
    }
    public function assets_redirect_add(Request $request)
    {
        //<><><>
        // if(Auth::check()) {
        //     $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
        //     if(Auth::user()->hasRole('admin')) {//if admin
        //         // dd(777);
        //         $SiteSettings = SiteSettings::select('code','name')->where("active",1)->where("deleted_at",null)->get();

        //     } else { //if notAdmin
        //         // dd(888);
        //         if(@Auth::user()->site_role_id && @Auth::user()->site_id) {
        //             if(@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) {//support and admin
        //                 // dd(99);

        //                 $SiteSettings = SiteSettings::select('code','name')->where("active",1)->where("deleted_at",null)
        //                 ->whereIn('id', $site_id_arr)//['49', '56']
        //                 ->get();
             

        //             } else {//not support and admin
        //                 $SiteSettings = SiteSettings::select('code','name')->where("active",1)->where("deleted_at",null)
        //                 ->whereIn('id', $site_id_arr)//['49', '56']
        //                 ->get();
        //             }
        //         }
        //     }
        // }
        $TTSS = TransactionTimeStampScans::select('transaction_time_stamp_scans.code','site.name','site.id')->leftjoin('domain', 'transaction_time_stamp_scans.domain_id', '=', 'domain.id')->where('domain.domain_default','1')->leftjoin('site', 'transaction_time_stamp_scans.site_id', '=', 'site.id')->whereNull('site.deleted_at')->get();
        $get_role_custom_first = @get_role_custom();
        $SiteSettings = array();
        $site_id_arr = @$get_role_custom_first['site_id_arr']->toArray();
        if(@$get_role_custom_first['superadmin'] == 1) {
            $SiteSettings = $TTSS;
            // foreach ($TTSS as $key => $value) {
            //     if (in_array($value->id, array_column($site_id_arr, 'site_id'))) {
            //         $SiteSettings [] = $value;
            //     }
            // }
        }else if(@$get_role_custom_first['client'] == 1) {
            foreach ($TTSS as $key => $value) {
                if (in_array($value->id, array_column($site_id_arr, 'site_id'))) {
                    $SiteSettings [] = $value;
                }
            }
        }else if(@$get_role_custom_first['site_support'] == 1) {
            foreach ($TTSS as $key => $value) {
                if (in_array($value->id, array_column($site_id_arr, 'site_id'))) {
                    $SiteSettings [] = $value;
                }
            }
        }else if(@$get_role_custom_first['site_admin'] == 1) {
            foreach ($TTSS as $key => $value) {
                if (in_array($value->id, array_column($site_id_arr, 'site_id'))) {
                    $SiteSettings [] = $value;
                }
            }
        }else if(@$get_role_custom_first['site_client'] == 1) {
            foreach ($TTSS as $key => $value) {
                if (in_array($value->id, array_column($site_id_arr, 'site_id'))) {
                    $SiteSettings [] = $value;
                }
            }
        }
        $data['SiteSettings'] = $SiteSettings;

        return view('assets::modal.redirect_add')->with($data);
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

    }

    public function get_selected_filter(Request $request)
    {
        $selectedGroup = $request->selectedGroup;
        $site = null;
        if($request->sitecode){
            $site = SiteSettings::select('id')->where("code",$request->sitecode)->first();
        }
        $returnData = null;
        if($selectedGroup=='domain'){
            if($site){
                if(isset($request->domaincode)){
                    $DomainFor = Domain::withTrashed()->where('code',$request->domaincode)->first();
                    $returnData = AssetsData::select('value AS val_select')->whereIn('data_type_id', [1,4])->where('status', 1)->where('site_id', $site->id)->where('domain_id', $DomainFor->id)->distinct()->get();
                }else{
                    $returnData = AssetsData::select('value AS val_select')->whereIn('data_type_id', [1,4])->where('status', 1)->where('site_id', $site->id)->distinct()->get();
                }
            }else{
                $returnData = AssetsData::select('value AS val_select')->whereIn('data_type_id', [1,4])->where('status', 1)->distinct()->get();
            }
        }else if($selectedGroup=='ip'){
            if($site){
                if(isset($request->domaincode)){
                    $DomainFor = Domain::withTrashed()->where('code',$request->domaincode)->first();
                    $returnData = AssetsData::select('value AS val_select')->whereIn('data_type_id', [5,6])->where('status', 1)->where('site_id', $site->id)->where('domain_id', $DomainFor->id)->distinct()->get();
                }else{
                    $returnData = AssetsData::select('value AS val_select')->whereIn('data_type_id', [5,6])->where('status', 1)->where('site_id', $site->id)->distinct()->get();
                }
            }else{
                $returnData = AssetsData::select('value AS val_select')->whereIn('data_type_id', [5,6])->where('status', 1)->distinct()->get();
            }
        }else if($selectedGroup=='cpe'){
            if($site){
                $returnData = CPE::select('cpe.result AS val_select')->leftjoin('assets_datas', 'cpe.asset_id', '=', 'assets_datas.id')->where('result','!=', null)->where('site_id', $site->id)->distinct()->get();
            }else{
                $returnData = CPE::select('result AS val_select')->where('result','!=', null)->distinct()->get();
            }
            
        }else if($selectedGroup=='os_type'){
            $returnData = OSType::select('name AS val_select')->distinct()->get();
        }
        if ($request->ajax()) {
            $data = [
                "selected" => $returnData,
            ];
            return response()->json($data);
        }

    }
    
    public function table_asset(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['assets']) {
            check_permission403();
        }
        if(TYPE_WEB == 'center'){
            $menu = $request->menu;
            $Assets_list = [];
            if($menu=='site'){
                $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $request->site)->first();
                $Assets_data = Assets::where('status', 1)->where('site_id', $SiteSettingsfor->id)->get();
            }else if($menu=='scan'){
                $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $request->site)->first();
                $DomainFor = Domain::withTrashed()->where('code',$request->domaincode)->first();
                if($SiteSettingsfor&&$DomainFor){
                    $Assets_data = Assets::where('status', 1)->where('site_id', $SiteSettingsfor->id)->where('domain_id', $DomainFor->id)->get();
                }
    
            }else{
                $Assets_data = Assets::where('status', 1)->get();
            }
            $OsType = OSType::get()->keyBy('id')->toArray();
            $SiteSettings = SiteSettings::withTrashed()->get()->keyBy('id')->toArray();
            foreach ($Assets_data as $key => $value) {
                $AssetsData_data = AssetsData::where('site_id', $value->site_id)->where('asset_id', $value->id)->get();
                $Domain_list = [];
                $IP_List = [];
                foreach ($AssetsData_data as $AssetsData_datakey => $AssetsData_datavalue) {
                    if ($AssetsData_datavalue->data_type_id == 1 || $AssetsData_datavalue->data_type_id == 4) {
                        //Domain
                        array_push($Domain_list, $AssetsData_datavalue);
    
                    } elseif ($AssetsData_datavalue->data_type_id == 5 || $AssetsData_datavalue->data_type_id == 6) {
                        //IP Asset
                        array_push($IP_List, $AssetsData_datavalue);
    
                    } else {
    
                    }
                }
    
                foreach ($IP_List as $IP_Listkey => $IP_Listvalue) {
                    $CPR_string = "";
                    $CPE_Data = CPE::where('asset_id', $IP_Listvalue->id)->get();
                    $CPE_List = array();
                    $CPE_Vendor = array();
                    $CPE_Title = array();
                    $CPE_Version = array();
                    $CPE_Edition = array();
                    $CPE_Remark = array();
                    $CPE_Ostype = array();
                    $CPE_Del = array();
                    foreach ($CPE_Data as $CPE_Datakey => $CPE_Datavalue) {
                        array_push($CPE_List, $CPE_Datavalue->result." - OSType: ".(isset($OsType[$CPE_Datavalue->os_type]["name"])?$OsType[$CPE_Datavalue->os_type]["name"]:""));
                        array_push($CPE_Vendor, '<span class="il-block">&nbsp;'.$CPE_Datavalue->vendor.'</span>');
                        array_push($CPE_Title, '<span class="il-block">&nbsp;'.$CPE_Datavalue->title.'</span>');
                        array_push($CPE_Version, '<span class="il-block">&nbsp;'.$CPE_Datavalue->version.'</span>');
                        array_push($CPE_Edition, '<span class="il-block">&nbsp;'.$CPE_Datavalue->edition.'</span>');
                        array_push($CPE_Remark, '<span class="il-block">&nbsp;'.$CPE_Datavalue->remark.'</span>');
                        array_push($CPE_Del, '<span class="il-block" style="box-sizing:border-box; -moz-box-sizing:border-box;">&nbsp;'.'<a href="'.route("assets.assets_delete_cpe", ["cpecode" => $CPE_Datavalue->code,"menu" => $menu]).'" class="btn btn-xs btn-danger" style="display:inline; font-size: 11px;" data-toggle="ajaxModal"><i class="fas fa-trash"></i></a>'.'</span>');
                        array_push($CPE_Ostype, '<span class="il-block">&nbsp;'.(isset($OsType[$CPE_Datavalue->os_type]["name"])?$OsType[$CPE_Datavalue->os_type]["name"]:"").'</span>');
                    }
    
                    if (count($CPE_List) > 0) {
                        $CPR_string = implode(' <br> ', (array) $CPE_List);
                        $CPE_Vendor = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $CPE_Vendor);
                        $CPE_Title = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $CPE_Title);
                        $CPE_Version = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $CPE_Version);
                        $CPE_Edition = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $CPE_Edition);
                        $CPE_Remark = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $CPE_Remark);
                        $CPE_Ostype = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $CPE_Ostype);
                        $CPE_Del = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $CPE_Del);
                    }
    
                   
                    $TTSS = TransactionTimeStampScans::select('code')->where('site_id', $value->site_id)->where('domain_id', $value->domain_id)->first();
                    if (count($Domain_list) == 0) {
                        $Assets_data_list = array();
                        $Assets_data_list['chk'] = "";
                        $Assets_data_list['cpe'] = '<a href="'.route("assets.assets_add_cpe", ["id" => $value->code,"page" => $menu, "idip" => $IP_Listvalue->code]).'" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal"><i class="fas fa-plus"></i> Add CPE </a>';
                        if(isset($TTSS->code)){
                            $Assets_data_list['action'] = '<a href="' . route("scans_assets.scans_assets_edit_modal", ["id" => $value->code, "code" => @$TTSS->code, "page" => $menu]) . '" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">
                            <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                            </a>';
                        }else{
                            $Assets_data_list['action'] = '<a href="' . route("scans_assets.scans_assets_edit_modal", ["id" => $value->code, "code" => @$value->site_id, "page" => $menu]) . '" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">
                            <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                            </a>';
                        }
                        
                        //<a href="' . route("scans_assets.delete", ["id" => $value->code, "code" => @$TTSS->code, "page" => $menu]) . '" class="btn btn-xs btn-danger m-xs" data-toggle="ajaxModal">
                        // <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z"></path></svg>
                        // </a>
                        $Assets_data_list['id'] = $IP_Listvalue->id;
                        $Assets_data_list['code'] = $IP_Listvalue->code;
                        $Assets_data_list['ip_asset_id'] = $IP_Listvalue->asset_id;
                        $Assets_data_list['site_code'] = @$SiteSettings[$IP_Listvalue->site_id]["code"];
                        $Assets_data_list['site_name'] = @$SiteSettings[$IP_Listvalue->site_id]["name"];
                        $Assets_data_list['status'] = $IP_Listvalue->status;
                        $Assets_data_list['created_at'] = $IP_Listvalue->created_at;
                        $Assets_data_list['updated_at'] = $IP_Listvalue->updated_at;
                        $Assets_data_list['domain'] = "";
                        $Assets_data_list['ip'] = $IP_Listvalue->value;
                        $Assets_data_list['CPE'] = $CPR_string;
    
                        $Assets_data_list['CPE_Vendor'] = $CPE_Vendor;
                        $Assets_data_list['CPE_Title'] = $CPE_Title;
                        $Assets_data_list['CPE_Version'] = $CPE_Version;
                        $Assets_data_list['CPE_Edition'] = $CPE_Edition;
                        $Assets_data_list['CPE_Remark'] = $CPE_Remark;
                        $Assets_data_list['CPE_Ostype'] = $CPE_Ostype;
                        $Assets_data_list['CPE_Del'] = $CPE_Del;
                        
                        array_push($Assets_list, $Assets_data_list);
    
                    } else {
                        foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                            $Assets_data_list = array();
                            $Assets_data_list['chk'] = "";
                            $Assets_data_list['cpe'] = '<a href="'.route("assets.assets_add_cpe", ["id" => $value->code,"page" => $menu, "idip" => $IP_Listvalue->code]).'" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal"><i class="fas fa-plus"></i> Add CPE </a>';
                            $Assets_data_list['action'] = '<a href="' . route("scans_assets.scans_assets_edit_modal", ["id" => $value->code, "code" => @$value->site_id, "page" => $menu]) . '" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">
                            <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                            </a>';
                            //<a href="' . route("scans_assets.delete", ["id" => $value->code, "code" => @$TTSS->code, "page" => $menu]) . '" class="btn btn-xs btn-danger m-xs" data-toggle="ajaxModal">
                            // <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z"></path></svg>
                            // </a>
                            $Assets_data_list['id'] = $IP_Listvalue->id;
                            $Assets_data_list['code'] = $IP_Listvalue->code;
                            $Assets_data_list['ip_asset_id'] = $IP_Listvalue->asset_id;
                            $Assets_data_list['site_code'] = @$SiteSettings[$IP_Listvalue->site_id]["code"];
                            $Assets_data_list['site_name'] = @$SiteSettings[$IP_Listvalue->site_id]["name"];
                            $Assets_data_list['status'] = $IP_Listvalue->status;
                            $Assets_data_list['created_at'] = $IP_Listvalue->created_at;
                            $Assets_data_list['updated_at'] = $IP_Listvalue->updated_at;
                            $Assets_data_list['domain'] = $Domain_listvalue->value;
                            $Assets_data_list['ip'] = $IP_Listvalue->value;
                            $Assets_data_list['CPE'] = $CPR_string;
    
                            $Assets_data_list['CPE_Vendor'] = $CPE_Vendor;
                            $Assets_data_list['CPE_Title'] = $CPE_Title;
                            $Assets_data_list['CPE_Version'] = $CPE_Version;
                            $Assets_data_list['CPE_Edition'] = $CPE_Edition;
                            $Assets_data_list['CPE_Remark'] = $CPE_Remark;
                            $Assets_data_list['CPE_Ostype'] = $CPE_Ostype;
                            $Assets_data_list['CPE_Del'] = $CPE_Del;
                            array_push($Assets_list, $Assets_data_list);
    
                        }
                    }
    
                }
    
            }
            $datacountAssets = @Assets::select('assets.id','assets_datas.data_type_id','assets_datas.value')->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')->whereIn('assets_datas.data_type_id',[5,6])->where('assets.status', 1)->get();
            $dataOut["countAssets"] = 0;
            foreach ($datacountAssets as $key => $value) {
                $AssetsData_data = AssetsData::where('asset_id', $value->id)->whereIn('assets_datas.data_type_id',[1,4])->get()->toArray();
                $countfn = count($AssetsData_data);
                if($countfn==0){
                    $dataOut["countAssets"]++;
                }else{
                    $dataOut["countAssets"] = $dataOut["countAssets"]+$countfn;
                }
            }

            // $dataOut["countWindows"] = @CPEData::whereRaw('LOWER(os_type) = ?', strtolower('WINDOWS'))->count();
            $dataOut["countWindows"] = @CPE::select('id')->whereHas('get_assets', function($q){
                $q->where('os_type', 1)->whereIn('data_type_id', [5,6]);
            })->count();
            // $dataOut["countLinux"] = @CPEData::whereRaw('LOWER(os_type) = ?', strtolower('LINUX'))->count();
            $dataOut["countLinux"] = @CPE::select('id')->whereHas('get_assets', function($q){
                $q->where('os_type', 2)->whereIn('data_type_id', [5,6]);
            })->count();

            $dataOut["data"] =  $Assets_list;
            return response()->json($dataOut);
        }else{
            $menu = $request->menu;
            $site = $request->site;
            $domaincode = $request->domaincode;

            $ip = $this->ip;
            $mac = $this->mac;
            $authorization_key = $this->header;
            $url_table_asset = $this->url_table_asset;

            $request_body_complete = [
                'menu' => $menu,
                'site' => $site,
                'domaincode' => $domaincode,
            ];

            $body_complete = json_encode($request_body_complete);
            $form_body_complete = encrypt_decrypt('encrypt', $body_complete, $authorization_key, $ip, $mac);
            $response_complete = $this -> reconnnect($url_table_asset, $form_body_complete, $authorization_key);
            if($response_complete['status_code'] == "200"){
                $dataOut = $response_complete['data'];
                return response()->json($dataOut);
            }else{
                return response()->json($response_complete);
            }
           
        }
    }

    public function selectCPE_by(Request $request)
    {
        $OSType = OSType::select('name')->where('id', $request->os_id)->first();
        $CPEData = CPEData::whereRaw('LOWER(os_type) = ?', strtolower($OSType->name))->get();
        return response()->json($CPEData);
    }

    public function countAssets(Request $request)
    {
        if($request->sitecode){
            $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $request->sitecode)->first();
            $dataOut["SiteSettingsfor"] = $SiteSettingsfor;
            // $dataOut["countAssets"] = @Assets::select('id')->where('site_id',$SiteSettingsfor->id)->whereHas('get_assets_data', function($q) use ($SiteSettingsfor) {
            //     $q->whereIn('data_type_id', [5,6]);
            // })->count();
            $datacountAssets = @Assets::select('assets.id','assets_datas.data_type_id','assets_datas.value')->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')->where('assets.site_id',$SiteSettingsfor->id)->whereIn('assets_datas.data_type_id',[5,6])->where('assets.status', 1)->get();
            $dataOut["countAssets"] = 0;
            foreach ($datacountAssets as $key => $value) {
                $AssetsData_data = AssetsData::where('asset_id', $value->id)->whereIn('assets_datas.data_type_id',[1,4])->get()->toArray();
                $countfn = count($AssetsData_data);
                if($countfn==0){
                    $dataOut["countAssets"]++;
                }else{
                    $dataOut["countAssets"] = $dataOut["countAssets"]+$countfn;
                }
            }

            // $dataOut["countWindows"] = @CPEData::whereRaw('LOWER(os_type) = ?', strtolower('WINDOWS'))->count();
            $dataOut["countWindows"] = @CPE::select('id')->whereHas('get_assets', function($q) use ($SiteSettingsfor) {
                $q->where('os_type', 1)->where('site_id',$SiteSettingsfor->id)->whereIn('data_type_id', [5,6]);
            })->count();
            // $dataOut["countLinux"] = @CPEData::whereRaw('LOWER(os_type) = ?', strtolower('LINUX'))->count();
            $dataOut["countLinux"] = @CPE::select('id')->whereHas('get_assets', function($q) use ($SiteSettingsfor) {
                $q->where('os_type', 2)->where('site_id',$SiteSettingsfor->id)->whereIn('data_type_id', [5,6]);
            })->count();
        }else{
            // $dataOut["countAssets"] = @Assets::select('id')->whereHas('get_assets_data', function($q){
            //     $q->whereIn('data_type_id', [5,6]);
            // })->count();
            $datacountAssets = @Assets::select('assets.id','assets_datas.data_type_id','assets_datas.value')->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')->whereIn('assets_datas.data_type_id',[5,6])->where('assets.status', 1)->get();
            $dataOut["countAssets"] = 0;
            foreach ($datacountAssets as $key => $value) {
                $AssetsData_data = AssetsData::where('asset_id', $value->id)->whereIn('assets_datas.data_type_id',[1,4])->get()->toArray();
                $countfn = count($AssetsData_data);
                if($countfn==0){
                    $dataOut["countAssets"]++;
                }else{
                    $dataOut["countAssets"] = $dataOut["countAssets"]+$countfn;
                }
            }

            // $dataOut["countWindows"] = @CPEData::whereRaw('LOWER(os_type) = ?', strtolower('WINDOWS'))->count();
            $dataOut["countWindows"] = @CPE::select('id')->whereHas('get_assets', function($q){
                $q->where('os_type', 1)->whereIn('data_type_id', [5,6]);
            })->count();
            // $dataOut["countLinux"] = @CPEData::whereRaw('LOWER(os_type) = ?', strtolower('LINUX'))->count();
            $dataOut["countLinux"] = @CPE::select('id')->whereHas('get_assets', function($q){
                $q->where('os_type', 2)->whereIn('data_type_id', [5,6]);
            })->count();
        }
        return response()->json($dataOut);
    }

    public function run_artisan_cpe(Request $request)
    {


        $command = 'app:TransactionCheckAssetMappingCPE';
        $Credentials = Credentials::where('code', $request->u_p)->first();
        $OSType = OSType::select('name')->where('id', $request->os_id)->first();
        $params = [
                'site_id' => $Credentials->site_id,
                'OS_Type' => strtolower($OSType->name),
                'IP' => $request->ip,
                'UserName' => $Credentials->user,
                'Password' => $Credentials->password,
        ];
        Artisan::call($command, $params);
        $resultArtisan = Artisan::output();
        // return response()->json($resultArtisan);

    }
    


    public function assets_add_data(Request $request)
    {
        $assets = $request->assets;
        $idip = AssetsData::where('code', $request->idip)->first();
        // if($request->data[0][4]=='Delete'){
        //     dd($request->data[0][0]);
        // }else{
        //     dd(55);
        // }
        
            if($request->data){
                foreach($request->data as $data){
                    $model = new CPE();

                    if($data[4]==''){
                        $vendor_text = @$data[0];
                        $vender_split = explode(":", $vendor_text);
                        $product_name = @$vender_split[4];
                        $vendor_name = @$vender_split[3];
                        $product_version = @$vender_split[5];
                        $product_edition = @$vender_split[6];
        
                        $model->code = generator_uuid();
                        $model->os_type = $data[2];
                        $model->select = 'add';
                        $model->cpe_data_id = $data[3];
                        $model->remark = $data[1];
                        $model->asset_id = $idip->id;
                        $model->result = $data[0];
                        $model->vendor = $vendor_name;
                        $model->title = $product_name;
                        $model->version = $product_version;
                        $model->edition = $product_edition;
        
                    }else if($data[3]==''){
                        $OSType = OSType::select('name')->where('id', $data[2])->first();
                        $CPEData = CPEData::where('cpe',$data[0])->where('os_name',$data[5])->where('os_type',strtolower($OSType->name))->first();
                        $Credentials = Credentials::where('code', $data[4])->first();
                        if(!$CPEData){
                            $CPEData = new CPEData;
                            $CPEData->cpe = $data[0];
                            $CPEData->os_name = $data[5];
                            $CPEData->os_type = strtolower($OSType->name);
                            $CPEData->status = 1;
                            $CPEData->save();
                        }
                        $vendor_text = @$data[0];
                        $vender_split = explode(":", $vendor_text);
                        $product_name = @$vender_split[4];
                        $vendor_name = @$vender_split[3];
                        $product_version = @$vender_split[5];
                        $product_edition = @$vender_split[6];
                        
                        $model->code = generator_uuid();
                        $model->os_type = $data[2];
                        $model->select = 'command';
                        $model->cpe_data_id = $CPEData->id;
                        $model->remark = $data[1];
                        $model->asset_id = $idip->id;
                        $model->credentials_id = @$Credentials->id;
                        $model->result = $data[0];
                        $model->vendor = $vendor_name;
                        $model->title = $product_name;
                        $model->version = $product_version;
                        $model->edition = $product_edition;
        
                    }
        
                    $model->save();
                    $transaction_client_cpe = transaction_client_cpe::where('site_id', $idip->site_id)->where('transaction_id', $model->id)->first();
                    if($transaction_client_cpe){
                        $transaction_client_cpe -> transaction_mode = 'insert';
                        $transaction_client_cpe -> transaction_data_status = 1;
                        $transaction_client_cpe -> status = 1;
                        $transaction_client_cpe -> save();
                    }else{
                        $transaction_client_cpe = new transaction_client_cpe();
                        $transaction_client_cpe -> site_id = $idip->site_id;
                        $transaction_client_cpe -> transaction_id = $model->id;
                        $transaction_client_cpe -> transaction_mode = 'insert';
                        $transaction_client_cpe -> transaction_data_status = 1;
                        $transaction_client_cpe -> status = 1;
                        $transaction_client_cpe -> save();
                    }
                }
            }
       

        if($request -> page == 'site'){
            $SiteSettingsfor = SiteSettings::withTrashed()->where('id', $idip->site_id)->first();
            return ajaxResponse(
                [
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => route('assetssite.index', ['id' => $SiteSettingsfor->code]),
                ],
                true,
                Response::HTTP_OK
            );
        }else if($request -> page == 'scan'){

            $TransactionTimeStampScansfor = TransactionTimeStampScans::where('domain_id', $idip->domain_id)->first();
            return ajaxResponse(
                [
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => route('scans.index', ['tab' => 'asset', 'site_code' => $TransactionTimeStampScansfor->code]),
                ],
                true,
                Response::HTTP_OK
            );
        }else if($request -> page == 'system'){
            return ajaxResponse(
                [
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => route('assets.index'),
                ],
                true,
                Response::HTTP_OK
            );
        }else if($request -> page == 'setting'){
            return ajaxResponse(
                [
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => route('assets.index_setting'),
                ],
                true,
                Response::HTTP_OK
            );
        }
        
        
    }

    private function reconnnect($url, $form_body, $authorization_key){
        if(TYPE_WEB !== 'center'){
            try{
                $headers = ['Authorization' => 'Bearer ' . $authorization_key];
                $res = $this->client->request('POST', $url,  [
                    'headers' => $headers, 
                    'form_params' => [
                        'data' => $form_body
                    ]
                ]);
                $response = json_decode($res->getBody()->getContents(), true);
                if($response['status_code'] == 200){
                    $decrypt = encrypt_decrypt('decrypt', $response['data'], $authorization_key, $this->ip, $this->mac);
                    $response_data = ['message' => '', 'error' => '', 'status_code' => '200', 'data' => json_decode($decrypt, true)];
                }else{
                    $response_data = ['message' => '', 'error' => 'Not Found', 'status_code' => '404', 'data' => $response];
                }
                return $response_data;
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        }
    }
}
