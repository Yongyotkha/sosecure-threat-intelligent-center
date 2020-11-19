<?php

namespace Modules\Scans\Http\Controllers;

use App\TransactionScans;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use phpseclib\Net\SSH1;
use phpseclib\Net\SSH2;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\TransactionTimeStampScans;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use DataTables;
use Modules\SiteSettings\Entities\SiteSettings;
use App\DataScans;
use App\DataTypes;
use Modules\Scans\Entities\Assets;
use Modules\Scans\Entities\AssetsData;
use Auth;

class ScansController extends Controller
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
       $data['page'] = langapp('scans');
       return view('scans::index')->with($data);
    }


    public function scan_domain($tab = 'overview', $site_code)
    {
        $allowed      = ['overview', 'datatype', 'asset', 'settings', 'logs'];
        $tab          = in_array($tab, $allowed) ? $tab : 'overview';
        $data['page'] = 'Domain Settings';
        $data['tab']  = $tab;
        $SiteSettings = TransactionTimeStampScans::where('code', $site_code)->first();
        $data['site']  = $SiteSettings;
        if($tab == 'overview'){
            $DataScans = DataScans::where('site_id', $SiteSettings -> site_id)->where('domain_id', $SiteSettings -> domain_id)->orderBy('total', 'desc')->take(5)->get();
            $data['DataScans'] = $DataScans;
        }

        return view('scans::scans_domain')->with($data);
    }

    public function get_referent(Request $request){
        $TransactionScans = [];
        foreach($request->values as $key => $data){
            $TransactionScans[$key]['raw_data'] = $data['raw_data'];
            $TransactionScans[$key]['data'] = TransactionScans::where('site_id', $data['site_id'])
            ->where('domain_id', $data['domain_id'])
            ->where('referent', $data['raw_data'])
            ->orwhere('raw_data', $data['raw_data'])
            ->get();
        }
        $DataTypes = DataTypes::all();
        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $TransactionScans, 'data_type' => $DataTypes]);   
    }

    public function get_data_type(){
        $DataTypes = DataTypes::all();
        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data_type' => $DataTypes]); 
    }

    public function save_assets(Request $request){
        foreach($request -> assets as $data){
            $Assets = Assets::where('raw_data', $data['raw_data'])->where('site_id', $data['site_id'])->where('domain_id', $data['domain_id'])->first();
            if(!$Assets){
                $Assets = new Assets;
                $Assets -> code = generator_uuid();
                $Assets -> created_by = Auth::user()->id;
                $Assets -> site_id = $data['site_id'];
                $Assets -> domain_id = $data['domain_id'];
                $Assets -> status = 1;
                $Assets -> raw_data = $data['raw_data'];
                $Assets -> save();
            }
            foreach($request -> assets_data as $item){
                $AssetsData = AssetsData::where('site_id', $data['site_id'])
                ->where('domain_id', $data['domain_id'])
                ->where('value', $item['raw_data'])
                ->where('data_type_id', $item['data_type'])
                ->first();
                if(!$AssetsData){
                    if($item['raw_data_base'] == $Assets -> raw_data){
                        $AssetsData = new AssetsData;
                        $AssetsData -> code = generator_uuid();
                        $AssetsData -> created_by = Auth::user()->id;
                        $AssetsData -> site_id = $data['site_id'];
                        $AssetsData -> domain_id = $data['domain_id'];
                        $AssetsData -> status = 1;
                        $AssetsData -> value = $item['raw_data'];
                        $AssetsData -> data_type_id = $item['data_type'];
                        $AssetsData -> asset_id = $Assets -> id;
                        $AssetsData -> save();

                        $TransactionScans = TransactionScans::where('site_id', $data['site_id'])
                        ->where('domain_id', $data['domain_id'])
                        ->where('raw_data', $item['raw_data'])
                        ->where('data_type', $AssetsData->get_data_type->value)
                        ->first();
                        if($TransactionScans){
                            $TransactionScans -> status_asset_use = 1;
                            $TransactionScans -> save();
                        }
                    }
                }
            }
        }
        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => '']); 
    }

    public function save_assets_new(Request $request){
        foreach($request -> assets as $data){
            $Assets = Assets::where('raw_data', $data['raw_data'])->where('site_id', $data['site_id'])->where('domain_id', $data['domain_id'])->first();
            if(!$Assets){
                $Assets = new Assets;
                $Assets -> code = generator_uuid();
                $Assets -> created_by = Auth::user()->id;
                $Assets -> site_id = $data['site_id'];
                $Assets -> domain_id = $data['domain_id'];
                $Assets -> status = 1;
                $Assets -> raw_data = $data['raw_data'];
                $Assets -> save();
            }
            foreach($request -> assets_data as $item){
                $AssetsData = AssetsData::where('site_id', $data['site_id'])
                ->where('domain_id', $data['domain_id'])
                ->where('value', $item['raw_data'])
                ->where('data_type_id', $item['data_type'])
                ->first();
                if(!$AssetsData){
                    if($item['raw_data_base'] == $data['raw_data_base']){
                        $AssetsData = new AssetsData;
                        $AssetsData -> code = generator_uuid();
                        $AssetsData -> created_by = Auth::user()->id;
                        $AssetsData -> site_id = $data['site_id'];
                        $AssetsData -> domain_id = $data['domain_id'];
                        $AssetsData -> status = 1;
                        $AssetsData -> value = $item['raw_data'];
                        $AssetsData -> data_type_id = $item['data_type'];
                        $AssetsData -> asset_id = $Assets -> id;
                        $AssetsData -> save();
                    }
                }
            }
        }
        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => '']); 
    }
    
    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('scans::create');
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
        return view('scans::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('scans::edit');
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

    public function scan_command(Request $request){
        $TransactionTimeStampScans = TransactionTimeStampScans::where('progress', 2)->where('status', 1)->get();
        foreach($TransactionTimeStampScans as $TransactionTimeStampScan){
            $path = public_path().'/files/scans/'.$TransactionTimeStampScan->get_site->code.'/'.$TransactionTimeStampScan->get_domain->code;
            $array = explode("\n", file_get_contents($path.'/looking_for_subdomain.txt'));
            $arrays = [];
            $arrays_final = [];
            $arrays_last_final = [];
            foreach ($array as $item) {
                $arrays[] = explode("\t", $item);
            }
            foreach($arrays as $data){
                $arrays_final[] = $data;
            }
            foreach($arrays_final as $item){
                $arrays = [];
                foreach($item as $data){
                    if(!empty($data)){
                        $arrays[] = trim($data);
                    }
                }
                $arrays_last_final[] = $arrays;
            }
            $arrays_last_final = array_filter($arrays_last_final);
            array_pop($arrays_last_final);
            foreach($arrays_last_final as $item){
                $TransactionScans = TransactionScans::where('site_id', $TransactionTimeStampScan->site_id)
                ->where('domain_id', $TransactionTimeStampScan->domain_id)
                ->where('module', $item[0])
                ->where('data_type', $item[1])
                ->where('raw_data', $item[2])
                ->first();
                if(!empty($TransactionScans)){
                    $TransactionScans -> updated_at = Carbon::now();
                }else{
                    $CreateTransactionScans = new TransactionScans();
                    $CreateTransactionScans -> code = Str::uuid()->toString();
                    $CreateTransactionScans -> created_by = $TransactionTimeStampScan -> created_by;
                    $CreateTransactionScans -> site_id = $TransactionTimeStampScan->site_id;
                    $CreateTransactionScans -> domain_id = $TransactionTimeStampScan->domain_id;
                    $CreateTransactionScans -> module = $item[0];
                    $CreateTransactionScans -> data_type = $item[1];
                    $CreateTransactionScans -> raw_data = $item[2];
                    $CreateTransactionScans -> status = 1;
                    $CreateTransactionScans -> save();
                }
            }

            $TransactionTimeStampScan->progress = 3;
            $TransactionTimeStampScan->save();
        }
    }

    public function save_scan(){
        $array = explode("\n", file_get_contents(public_path().'/files/scans/looking_for_subdomain.txt'));
        $arrays = [];
        $arrays_final = [];
        $arrays_last_final = [];
        foreach ($array as $item) {
            $arrays[] = explode("\t", $item);
        }
        foreach($arrays as $data){
            $arrays_final[] = $data;
        }
        foreach($arrays_final as $item){
            $arrays = [];
            foreach($item as $data){
                if(!empty($data)){
                    $arrays[] = trim($data);
                }
            }
            $arrays_last_final[] = $arrays;
        }
        $arrays_last_final = array_filter($arrays_last_final);
        array_pop($arrays_last_final);
        dd($arrays_last_final);
    }

    public function tableData()
    {
        $site_code = $this->request->site_code;
        // $site_code = 'a7b6ff37-30ec-4494-9527-93b0ccc51d56';
        $site_id_find = SiteSettings::where("code",$site_code)->first();
        $site_id = $site_id_find->id;

        $site_code = $this->request->site_code;
        $site_id = $this->request->site_id;
        $model = $this->applyFilter()->with(['profile:user_id,job_title,mobile,city,use_gravatar,avatar']);
        $model = $this->user->query();
        $model = TransactionTimeStampScans::query();
        $test = 1;
        if($site_id) {
            $model->when(
                $test == 1,
                function ($q) use ($site_id) {
                    return $q->where('site_id','=', $site_id);
                }
            );
        }


        $model = TransactionTimeStampScans::query();
        return DataTables::eloquent($model)
            ->editColumn('chk', function (TransactionTimeStampScans $model) {
                    return '<label><input type="checkbox" name="checked" value="' . $model->code . '"><span class="label-text"></span></label>';
            })
            ->addColumn('name', function (TransactionTimeStampScans $model) {
                return '<label>'.$model -> get_site -> name.'</label>';
            })
            ->addColumn('domain', function (TransactionTimeStampScans $model) {
                return '<label>'.$model -> get_domain -> name.'</label>';
            })
            ->addColumn('progress', function (TransactionTimeStampScans $model) {
                $html = '';
                $html = get_name_scan_status($model -> progress , 'badg');
                return $html;
            })
            ->addColumn('action', function (TransactionTimeStampScans $model) {
                $html = '';
                $html .= "<div style='display: flex;'><a href='". route('scans.index', ['tab' => 'overview', 'site_code' => $model -> code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs'>
                                <i class='far fa-eye'></i>
                            </a>
                            <a href='' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                                <i class='fas fa-redo'></i>
                            </a>
                            <a href='' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                            <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                            </a>
                            <a href='' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                            <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                            </a></div>";
                return $html;
            })
            ->rawColumns(['chk','name','domain','progress','action'])
            ->toJson();
    }

    public function tableDataScans(Request $request){
        $SiteSettings = TransactionTimeStampScans::where('code', $request->code)->first();
        $TransactionScans = TransactionScans::where('site_id', $SiteSettings -> site_id)->where('domain_id', $SiteSettings -> domain_id)->get();
        return DataTables::of($TransactionScans)
            ->editColumn('chk', function (TransactionScans $data) {
                $res = '';
                if($data -> status_asset_use == 1){
                    $res .= '<label>
                    <input type="checkbox" checked onclick="return false;"/>
                        <span class="label-text"></span>
                    </label>';
                }else{
                    $res .= '<label>
                        <input name="select[]" value="'.$data -> raw_data.'" data-domain="'.$data -> domain_id.'" data-site="'.$data -> site_id.'" class="select-chk" type="checkbox" />
                        <span class="label-text"></span>
                    </label>';
                }
                return $res;
            })
            ->addColumn('use', function (TransactionScans $data) {
                $res = '';
                if($data -> status_asset_use == 1){
                    $res .= '<span class="badge badge-success">นำไปใช่้งานแล้ว</span>';
                }else if($data -> status == 0){
                    $res .= '<span class="badge badge-danger">หาไม่เจอ</span>';
                }else if($data -> status == 1){
                    $res .= '<span class="badge badge-warning" style="background-color: #ffc107;">เจอค่า</span>';
                }else if($data -> status == 2){
                    $res .= '<span class="badge badge-primary" style="background-color: #3869d4;">เพิ่มมาใหม่</span>';
                }
                return $res;
            })
            ->rawColumns(['chk','use'])
            ->toJson();
    }

    public function tableDataScanAssets(Request $request){
        $SiteSettings = TransactionTimeStampScans::where('code', $request->code)->first();
        $Assets = Assets::where('site_id', $SiteSettings->site_id)->where('domain_id', $SiteSettings->domain_id)->get();
        return DataTables::of($Assets)
        ->addColumn('assets', function (Assets $data) {
            return $data -> raw_data;
        })
        ->addColumn('referent', function (Assets $data) {
            $res = '';
            $res .= '<ul class="asset-list-tb">';
                foreach($data->get_assets_data as $item){
                    $res .= '<li>' . $item -> value . '</li>';
                }
            $res .= '</ul>';
            return $res;
        })
        ->addColumn('status', function (Assets $data) {
            $res = '';
            if($data -> status == 1){
                $res .= '<span class="badge badge-success">Active</span>';
            }else{
                $res .= '<span class="badge badge-danger">Inactive</span>';
            }
            return $res;
        })
        ->addColumn('action', function (Assets $data) use($SiteSettings) {
            return '<button type="submit" class="btn btn-sm btn-info m-xs">
                    <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                </button>
                <a href="'. route("scans_assets.delete", ["id" => $data->code, "code" => $SiteSettings->code]) .'" class="btn btn-sm btn-danger m-xs" data-toggle="ajaxModal">
                    <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z"></path></svg>
                </a>';
        })
        ->rawColumns(['assets','referent','status','action'])
        ->toJson();
    }

    public function scans_assets_delete($id, $code)
    {
        $Assets = Assets::where('code',$id)->first();
        $data['scans'] = $Assets;
        $data['code'] = $code;
        return view('scans::modal.delete')->with($data);
    }

    public function f_scans_assets_delete($id = null, $code)
    {
        $Assets = Assets::find($id);
        $AssetsData = AssetsData::where('asset_id', $id)->get();
        foreach($AssetsData as $data){
            $TransactionScans = TransactionScans::where('site_id', $data -> site_id)->where('domain_id', $data -> domain_id)->where('raw_data', $data->value)
                                                 ->where('data_type', $data->get_data_type->value)->first();
            if($TransactionScans){
                $TransactionScans -> status_asset_use = 0;
                $TransactionScans -> save();
            }
            $data -> delete();
        }
        $Assets->delete();

        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('scans.index', ['tab' => 'asset', 'site_code' => $code]),
            ],
            true,
            Response::HTTP_OK
        );
    }
}
