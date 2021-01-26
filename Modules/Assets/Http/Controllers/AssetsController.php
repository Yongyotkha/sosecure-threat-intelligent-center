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
class AssetsController extends Controller
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

        if(isset($this->request->Search_Link_All)){
            $data['Search_Link_All'] = $this->request->Search_Link_All;
        }else{
            $data['Search_Link_All'] = "";
        }
        
        $data['SiteSettings'] = $SiteSettings;
        $data['page'] = langapp('assets');
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

    public function assets_add_cpe($id)
    {
        
        $data['cpe'] = CPEData::where('status', 1)->get();
        $data['Credentials'] = Credentials::where('status', 1)->get();
        $data['SiteSettings'] = SiteSettings::where("active", 1)->where("deleted_at", null)->get();
        $data['os'] = OSType::get();
        $data['assets'] = Assets::where('code', $id)->first();


        return view('assets::modal.add_cpe')->with($data);
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
                $returnData = AssetsData::select('value AS val_select')->whereIn('data_type_id', [1,4])->where('status', 1)->where('site_id', $site->id)->distinct()->get();
            }else{
                $returnData = AssetsData::select('value AS val_select')->whereIn('data_type_id', [1,4])->where('status', 1)->distinct()->get();
            }
        }else if($selectedGroup=='ip'){
            if($site){
                $returnData = AssetsData::select('value AS val_select')->whereIn('data_type_id', [5,6])->where('status', 1)->where('site_id', $site->id)->distinct()->get();
            }else{
                $returnData = AssetsData::select('value AS val_select')->whereIn('data_type_id', [5,6])->where('status', 1)->distinct()->get();
            }
        }else if($selectedGroup=='cpe'){
            $returnData = CPE::select('result AS val_select')->where('result','!=', null)->distinct()->get();
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
    
    public function table_asset()
    {
        $Assets_list = [];
        $Assets_data = Assets::where('status', 1)->get();
        $OsType = OSType::get()->keyBy('id')->toArray();
        $SiteSettings = SiteSettings::withTrashed()->get()->keyBy('id')->toArray();
        $menu = 'system';
        foreach ($Assets_data as $key => $value) {
            $AssetsData_data = AssetsData::where('site_id', $value->site_id)->where('asset_id', $value->id)->where('status', 1)->get();
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
                foreach ($CPE_Data as $CPE_Datakey => $CPE_Datavalue) {
                    array_push($CPE_List, $CPE_Datavalue->result." - OSType: ".(isset($OsType[$CPE_Datavalue->os_type]["name"])?$OsType[$CPE_Datavalue->os_type]["name"]:""));
                }
                if (count($CPE_List) > 0) {
                    $CPR_string = implode(' <br> ', (array) $CPE_List);
                }

                $menu = 'system';
                $TTSS = TransactionTimeStampScans::select('code')->where('site_id', $value->site_id)->where('domain_id', $value->domain_id)->first();
                if (count($Domain_list) == 0) {
                    $Assets_data_list = array();
                    $Assets_data_list['chk'] = "";
                    $Assets_data_list['cpe'] = '<a href="'.route("assets.assets_add_cpe", ["id" => $value->code]).'" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">Add </a>';
                    $Assets_data_list['action'] = '<a href="' . route("scans_assets.scans_assets_edit_modal", ["id" => $value->code, "code" => @$TTSS->code, "page" => $menu]) . '" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">
                    <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                    </a>';
                    //<a href="' . route("scans_assets.delete", ["id" => $value->code, "code" => @$TTSS->code, "page" => $menu]) . '" class="btn btn-xs btn-danger m-xs" data-toggle="ajaxModal">
                    // <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z"></path></svg>
                    // </a>
                    $Assets_data_list['id'] = $IP_Listvalue->id;
                    $Assets_data_list['code'] = $IP_Listvalue->code;
                    $Assets_data_list['site_code'] = @$SiteSettings[$IP_Listvalue->site_id]["code"];
                    $Assets_data_list['site_name'] = @$SiteSettings[$IP_Listvalue->site_id]["name"];
                    $Assets_data_list['status'] = $IP_Listvalue->status;
                    $Assets_data_list['created_at'] = $IP_Listvalue->created_at;
                    $Assets_data_list['updated_at'] = $IP_Listvalue->updated_at;
                    $Assets_data_list['domain'] = "";
                    $Assets_data_list['ip'] = $IP_Listvalue->value;
                    $Assets_data_list['CPE'] = $CPR_string;

                    array_push($Assets_list, $Assets_data_list);

                } else {

                    foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                        $Assets_data_list = array();
                        $Assets_data_list['chk'] = "";
                        $Assets_data_list['cpe'] = '<a href="'.route("assets.assets_add_cpe", ["id" => $value->code]).'" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">Add </a>';
                        $Assets_data_list['action'] = '<a href="' . route("scans_assets.scans_assets_edit_modal", ["id" => $value->code, "code" => @$value->site_id, "page" => $menu]) . '" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">
                        <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                        </a>';
                        //<a href="' . route("scans_assets.delete", ["id" => $value->code, "code" => @$TTSS->code, "page" => $menu]) . '" class="btn btn-xs btn-danger m-xs" data-toggle="ajaxModal">
                        // <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z"></path></svg>
                        // </a>
                        $Assets_data_list['id'] = $IP_Listvalue->id;
                        $Assets_data_list['code'] = $IP_Listvalue->code;
                        $Assets_data_list['site_code'] = @$SiteSettings[$IP_Listvalue->site_id]["code"];
                        $Assets_data_list['site_name'] = @$SiteSettings[$IP_Listvalue->site_id]["name"];
                        $Assets_data_list['status'] = $IP_Listvalue->status;
                        $Assets_data_list['created_at'] = $IP_Listvalue->created_at;
                        $Assets_data_list['updated_at'] = $IP_Listvalue->updated_at;
                        $Assets_data_list['domain'] = $Domain_listvalue->value;
                        $Assets_data_list['ip'] = $IP_Listvalue->value;
                        $Assets_data_list['CPE'] = $CPR_string;
                        array_push($Assets_list, $Assets_data_list);

                    }
                }

            }

        }

        $dataOut["data"] =  $Assets_list;
        return response()->json($dataOut);
    }
    public function assets_add_data(Request $request)
    {
        $assets = $request->assets;

        // if($request->data[0][4]=='Delete'){
        //     dd($request->data[0][0]);
        // }else{
        //     dd(55);
        // }
        

        foreach($request->data as $data){
            $model = new CPE();
            if($data[4]=='Delete'){
                $model->code = generator_uuid();
                $model->os_type = $data[2];
                $model->select = 'add';
                $model->cpe_data_id = $data[3];
                $model->remark = $data[1];
                $model->asset_id = $assets['id'];
                

            }else if($data[3]=='Delete'){
                
                $model->code = generator_uuid();
                $model->os_type = $data[2];
                $model->select = 'command';
                // $model->cpe_data_id = $data[3];
                $model->remark = $data[1];
                $model->asset_id = $assets['id'];
                $model->credentials_id = $data[4];
                $model->result = $data[0];

            }

            $model->save();
        }

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('assets.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }
}
