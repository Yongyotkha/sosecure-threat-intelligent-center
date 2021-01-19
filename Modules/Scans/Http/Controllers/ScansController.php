<?php

namespace Modules\Scans\Http\Controllers;

use App\DataScans;
use App\DataTypes;
use App\TransactionScans;
use App\TransactionTimeStampScans;
use Auth;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Scans\Entities\Assets;
use Modules\Scans\Entities\AssetsData;
use Modules\SiteSettings\Entities\SiteSettings;

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
        $allowed = ['overview', 'datatype', 'asset', 'settings', 'logs'];
        $tab = in_array($tab, $allowed) ? $tab : 'overview';
        $data['page'] = 'Domain Settings';
        $data['tab'] = $tab;
        $SiteSettings = TransactionTimeStampScans::where('code', $site_code)->first();
        $data['site'] = $SiteSettings;
        if ($tab == 'overview') {
            $DataScans = DataScans::where('site_id', $SiteSettings->site_id)->where('domain_id', $SiteSettings->domain_id)->orderBy('total', 'desc')->take(5)->get();
            $data['DataScans'] = $DataScans;
        }

        return view('scans::scans_domain')->with($data);
    }

    public function get_referent(Request $request)
    {
        $TransactionScans = [];
        foreach ($request->values as $key => $data) {
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

    public function get_data_type()
    {
        $DataTypes = DataTypes::all();
        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data_type' => $DataTypes]);
    }

    public function save_assets(Request $request)
    {
        foreach ($request->assets as $data) {
            $Assets = Assets::where('raw_data', $data['raw_data'])->where('site_id', $data['site_id'])->where('domain_id', $data['domain_id'])->first();
            if (!$Assets) {
                $Assets = new Assets;
                $Assets->code = generator_uuid();
                $Assets->created_by = Auth::user()->id;
                $Assets->site_id = $data['site_id'];
                $Assets->domain_id = $data['domain_id'];
                $Assets->status = 1;
                $Assets->raw_data = $data['raw_data'];
                $Assets->save();
            }
            foreach ($request->assets_data as $item) {
                $AssetsData = AssetsData::where('site_id', $data['site_id'])
                    ->where('domain_id', $data['domain_id'])
                    ->where('value', $item['raw_data'])
                    ->where('data_type_id', $item['data_type'])
                    ->first();
                if (!$AssetsData) {
                    if ($item['raw_data_base'] == $Assets->raw_data) {
                        $AssetsData = new AssetsData;
                        $AssetsData->code = generator_uuid();
                        $AssetsData->created_by = Auth::user()->id;
                        $AssetsData->site_id = $data['site_id'];
                        $AssetsData->domain_id = $data['domain_id'];
                        $AssetsData->status = 1;
                        $AssetsData->value = $item['raw_data'];
                        $AssetsData->data_type_id = $item['data_type'];
                        $AssetsData->asset_id = $Assets->id;
                        $AssetsData->save();

                        $TransactionScans = TransactionScans::where('site_id', $data['site_id'])
                            ->where('domain_id', $data['domain_id'])
                            ->where('raw_data', $item['raw_data'])
                            ->where('data_type', $AssetsData->get_data_type->value)
                            ->first();
                        if ($TransactionScans) {
                            $TransactionScans->status_asset_use = 1;
                            $TransactionScans->save();
                        }
                    }
                }
            }
        }
        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => '']);
    }

    public function save_assets_new(Request $request)
    {
        foreach ($request->assets as $data) {
            $Assets = Assets::where('raw_data', $data['raw_data'])->where('site_id', $data['site_id'])->where('domain_id', $data['domain_id'])->first();
            if (!$Assets) {
                $Assets = new Assets;
                $Assets->code = generator_uuid();
                $Assets->created_by = Auth::user()->id;
                $Assets->site_id = $data['site_id'];
                $Assets->domain_id = $data['domain_id'];
                $Assets->status = 1;
                $Assets->raw_data = $data['raw_data'];
                $Assets->save();
            }
            foreach ($request->assets_data as $item) {
                $AssetsData = AssetsData::where('site_id', $data['site_id'])
                    ->where('domain_id', $data['domain_id'])
                    ->where('value', $item['raw_data'])
                    ->where('data_type_id', $item['data_type'])
                    ->first();
                if (!$AssetsData) {
                    if ($item['raw_data_base'] == $data['raw_data_base']) {
                        $AssetsData = new AssetsData;
                        $AssetsData->code = generator_uuid();
                        $AssetsData->created_by = Auth::user()->id;
                        $AssetsData->site_id = $data['site_id'];
                        $AssetsData->domain_id = $data['domain_id'];
                        $AssetsData->status = 1;
                        $AssetsData->value = $item['raw_data'];
                        $AssetsData->data_type_id = $item['data_type'];
                        $AssetsData->asset_id = $Assets->id;
                        $AssetsData->save();
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

    public function scan_command(Request $request)
    {
        $TransactionTimeStampScans = TransactionTimeStampScans::where('progress', 2)->where('status', 1)->get();
        foreach ($TransactionTimeStampScans as $TransactionTimeStampScan) {
            $path = public_path() . '/files/scans/' . $TransactionTimeStampScan->get_site->code . '/' . $TransactionTimeStampScan->get_domain->code;
            $array = explode("\n", file_get_contents($path . '/looking_for_subdomain.txt'));
            $arrays = [];
            $arrays_final = [];
            $arrays_last_final = [];
            foreach ($array as $item) {
                $arrays[] = explode("\t", $item);
            }
            foreach ($arrays as $data) {
                $arrays_final[] = $data;
            }
            foreach ($arrays_final as $item) {
                $arrays = [];
                foreach ($item as $data) {
                    if (!empty($data)) {
                        $arrays[] = trim($data);
                    }
                }
                $arrays_last_final[] = $arrays;
            }
            $arrays_last_final = array_filter($arrays_last_final);
            array_pop($arrays_last_final);
            foreach ($arrays_last_final as $item) {
                $TransactionScans = TransactionScans::where('site_id', $TransactionTimeStampScan->site_id)
                    ->where('domain_id', $TransactionTimeStampScan->domain_id)
                    ->where('module', $item[0])
                    ->where('data_type', $item[1])
                    ->where('raw_data', $item[2])
                    ->first();
                if (!empty($TransactionScans)) {
                    $TransactionScans->updated_at = Carbon::now();
                } else {
                    $CreateTransactionScans = new TransactionScans();
                    $CreateTransactionScans->code = Str::uuid()->toString();
                    $CreateTransactionScans->created_by = $TransactionTimeStampScan->created_by;
                    $CreateTransactionScans->site_id = $TransactionTimeStampScan->site_id;
                    $CreateTransactionScans->domain_id = $TransactionTimeStampScan->domain_id;
                    $CreateTransactionScans->module = $item[0];
                    $CreateTransactionScans->data_type = $item[1];
                    $CreateTransactionScans->raw_data = $item[2];
                    $CreateTransactionScans->status = 1;
                    $CreateTransactionScans->save();
                }
            }

            $TransactionTimeStampScan->progress = 3;
            $TransactionTimeStampScan->save();
        }
    }

    public function save_scan()
    {
        $array = explode("\n", file_get_contents(public_path() . '/files/scans/looking_for_subdomain.txt'));
        $arrays = [];
        $arrays_final = [];
        $arrays_last_final = [];
        foreach ($array as $item) {
            $arrays[] = explode("\t", $item);
        }
        foreach ($arrays as $data) {
            $arrays_final[] = $data;
        }
        foreach ($arrays_final as $item) {
            $arrays = [];
            foreach ($item as $data) {
                if (!empty($data)) {
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
        $site_id = '';
        $site_code = $this->request->site_code;
        // $site_code = 'a7b6ff37-30ec-4494-9527-93b0ccc51d56';
        $site_id_find = SiteSettings::where("code", $site_code)->first();
        if ($site_code) {
            $site_id = $site_id_find->id;
        }

        // $site_code = $this->request->site_code;
        // $site_id = $this->request->site_id;
        // $model = $this->applyFilter()->with(['profile:user_id,job_title,mobile,city,use_gravatar,avatar']);
        // $model = $this->user->query();
        $model = TransactionTimeStampScans::query();
        $test = 1;
        if ($site_id) {
            $model->when(
                $test == 1,
                function ($q) use ($site_id) {
                    return $q->where('site_id', '=', $site_id);
                }
            );
        }

        // $model = TransactionTimeStampScans::query();
        return DataTables::eloquent($model)
            ->editColumn('chk', function (TransactionTimeStampScans $model) {
                return '<label><input type="checkbox" name="checked" value="' . $model->code . '"><span class="label-text"></span></label>';
            })
            ->addColumn('name', function (TransactionTimeStampScans $model) {
                return '<label>' . @$model->get_site->name . '</label>';
            })
            ->addColumn('domain', function (TransactionTimeStampScans $model) {
                return '<label>' . @$model->get_domain->name . '</label>';
            })
            ->addColumn('progress', function (TransactionTimeStampScans $model) {
                $html = '';
                $html = get_name_scan_status($model->progress, 'badg');
                return $html;
            })
            ->addColumn('action', function (TransactionTimeStampScans $model) {
                $html = '';
                $html .= "<div style='display: flex;'><a href='" . route('scans.index', ['tab' => 'overview', 'site_code' => $model->code]) . "' class='btn btn-" . get_option('theme_color') . " btn-xs'>
                                <i class='far fa-eye'></i>
                            </a>";
                if (@$model->progress !== 3) {
                    $html .= "<a href='#' class='btn btn-" . get_option('theme_color') . " btn-xs' disabled>
                                    <i class='fas fa-redo'></i>
                                </a>";
                } else {
                    $html .= "<a href='" . route('get.scans.redo_process', ['id' => $model->code]) . "' class='btn btn-" . get_option('theme_color') . " btn-xs' data-toggle='ajaxModal'>
                                    <i class='fas fa-redo'></i>
                                </a>";
                }

                if (@$model->progress !== 3) {
                    $html .= "<a href='' class='disabled btn btn-" . get_option('theme_color') . " btn-xs' data-toggle='ajaxModal'>
                                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                                    </a>
                                    <a href='' class='disabled btn btn-" . get_option('theme_color') . " btn-xs' data-toggle='ajaxModal'>
                                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                                    </a>";
                } else {
                    $html .= "<a href='' class='btn btn-" . get_option('theme_color') . " btn-xs' data-toggle='ajaxModal'>
                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                    </a>
                    <a href='' class='btn btn-" . get_option('theme_color') . " btn-xs' data-toggle='ajaxModal'>
                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                    </a>";
                }

                $html .= "</div>";
                return $html;
            })
            ->rawColumns(['chk', 'name', 'domain', 'progress', 'action'])
            ->toJson();
    }

    public function tableDataScans(Request $request)
    {
        $SiteSettings = TransactionTimeStampScans::where('code', $request->code)->first();
        $TransactionScans = TransactionScans::where('site_id', $SiteSettings->site_id)->where('domain_id', $SiteSettings->domain_id)->orderBy('status', 'desc')->get();
        return DataTables::of($TransactionScans)
            ->editColumn('chk', function (TransactionScans $data) {
                $res = '';
                if ($data->status_asset_use == 1) {
                    $res .= '<label>
                    <input type="checkbox" checked onclick="return false;"/>
                        <span class="label-text"></span>
                    </label>';
                } else {
                    $res .= '<label>
                        <input name="select[]" value="' . $data->raw_data . '" data-domain="' . $data->domain_id . '" data-site="' . $data->site_id . '" class="select-chk" type="checkbox" />
                        <span class="label-text"></span>
                    </label>';
                }
                return $res;
            })
            ->addColumn('use', function (TransactionScans $data) {
                $res = '';
                if ($data->status_asset_use == 1) {
                    $res .= '<span class="badge badge-success">Used</span>';
                } else if ($data->status == 0) {
                    $res .= '<span class="badge badge-danger">Not Found</span>';
                } else if ($data->status == 1) {
                    $res .= '<span class="badge badge-warning" style="background-color: #ffc107;">Found</span>';
                } else if ($data->status == 2) {
                    $res .= '<span class="badge badge-primary" style="background-color: #3869d4;">New</span>';
                }
                return $res;
            })
            ->rawColumns(['chk', 'use'])
            ->toJson();
    }

    public function tableDataScanAssets(Request $request)
    {
        if($request -> menu == 'scan'){
            $SiteSettings = TransactionTimeStampScans::where('code', $request->code)->first();
            if($SiteSettings){
                $Assets = Assets::where('site_id', $SiteSettings->site_id)->where('domain_id', $SiteSettings->domain_id)->get();
            }else{
                $Assets = [];
            }
        }else if($request -> menu == 'site'){
            $site = SiteSettings::select('id')->where('code', $request->code)->first();
            $SiteSettings = TransactionTimeStampScans::select('domain_id')->where('site_id', $site->id)->get();
            if($SiteSettings){
                $domain_id = [];
                foreach($SiteSettings as $data){
                    $domain_id[] = $data -> domain_id;
                }
                $Assets = Assets::where('site_id', $site -> id)->whereIn('domain_id', $domain_id)->get();
            }else{
                $Assets = [];
            }
        }else if($request -> menu == 'system'){
            if($request -> site_id == 0){
                $Assets = Assets::all();
            }else{
                $Assets = Assets::where('site_id', $request -> site_id)->get();
            }
            
        }
        $menu = $request -> menu;
        
        return DataTables::of($Assets)
        ->addColumn('chk', function (Assets $data) {
            return '<label><input type="checkbox" name="checked" class="select-chk asset_id" value="' . $data->code . '"><span class="label-text"></span></label>';
        })
        ->addColumn('site', function (Assets $data) {
            if($data->get_site){
                return $data->get_site->name;
            }else{
                return 'No Data';
            }
            
        })
        ->addColumn('assets', function (Assets $data) {
            return $data->raw_data;
        })
        ->addColumn('referent', function (Assets $data) {
            $res = '';
            $res .= '<ul class="asset-list-tb">';
            foreach ($data->get_assets_data as $item) {
                $res .= '<li>' . $item->value . '</li>';
            }
            $res .= '</ul>';
            return $res;
        })
        ->addColumn('status', function (Assets $data) {
            $res = '';
            if ($data->status == 1) {
                $res .= '<span class="badge badge-success">Active</span>';
            } else {
                $res .= '<span class="badge badge-danger">Inactive</span>';
            }
            return $res;
        })
        ->addColumn('action', function (Assets $data) use ($menu) {
            $SiteSettings = TransactionTimeStampScans::select('code')->where('site_id', $data->site_id)->where('domain_id', $data->domain_id)->first();
            return '<a href="' . route("scans_assets.scans_assets_edit_modal", ["id" => $data->code, "code" => @$SiteSettings->code, "page" => $menu]) . '" class="btn btn-sm btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">
            <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                </a>
                <a href="' . route("scans_assets.delete", ["id" => $data->code, "code" => @$SiteSettings->code, "page" => $menu]) . '" class="btn btn-sm btn-danger m-xs" data-toggle="ajaxModal">
                    <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z"></path></svg>
                </a>';
            })
        ->rawColumns(['chk', 'site', 'assets', 'referent', 'status', 'action'])
        ->toJson();
    }

    public function scans_assets_delete($id, $code, $page)
    {
        $Assets = Assets::where('code', $id)->first();
        $data['scans'] = $Assets;
        $data['code'] = $code;
        $data['page'] = $page;
        return view('scans::modal.delete')->with($data);
    }

    public function f_scans_assets_delete($id = null, $code, $page)
    {
        $Assets = Assets::find($id);
        $site = SiteSettings::select('code')->where('id', $Assets -> site_id)->first()->code;
        $AssetsData = AssetsData::where('asset_id', $id)->get();
        foreach ($AssetsData as $data) {
            $TransactionScans = TransactionScans::where('site_id', $data->site_id)->where('domain_id', $data->domain_id)->where('raw_data', $data->value)
                ->where('data_type', $data->get_data_type->value)->first();
            if ($TransactionScans) {
                $TransactionScans->status_asset_use = 0;
                $TransactionScans->save();
            }
            $data->delete();
        }
        $Assets->delete();

        if($page == 'site'){
            return ajaxResponse(
                [
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => route('assetssite.index', ['id' => $site]),
                ],
                true,
                Response::HTTP_OK
            );
        }else if($page == 'scan'){
            return ajaxResponse(
                [
                    'message' => langapp('deleted_successfully'),
                    'redirect' => route('scans.index', ['tab' => 'asset', 'site_code' => $code]),
                ],
                true,
                Response::HTTP_OK
            );
        }
    }

    public function delete_assets_select(Request $request){
        $site = null;
        $SiteSettings = null;
        foreach($request -> asset_id as $key => $id){
            $Assets = Assets::where('code', $id)->first();
            if($key == 0){
                $site = SiteSettings::select('code')->where('id', $Assets -> site_id)->first();
            }
            $AssetsData = AssetsData::where('asset_id', $Assets -> id)->get();
            foreach ($AssetsData as $key_2 => $data) {
                if($key_2 == 0){
                    $SiteSettings = TransactionTimeStampScans::select('code')->where('site_id', $data->site_id)->where('domain_id', $data->domain_id)->first();
                }
                $TransactionScans = TransactionScans::where('site_id', $data->site_id)->where('domain_id', $data->domain_id)->where('raw_data', $data->value)
                    ->where('data_type', $data->get_data_type->value)->first();
                if ($TransactionScans) {
                    $TransactionScans->status_asset_use = 0;
                    $TransactionScans->save();
                }
                $data->delete();
            }
            $Assets->delete();
        }
        if($request -> page == 'site'){
            return ajaxResponse(
                [
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => route('assetssite.index', ['id' => $site->code]),
                ],
                true,
                Response::HTTP_OK
            );
        }else if($request -> page == 'scan'){
            return ajaxResponse(
                [
                    'message' => langapp('deleted_successfully'),
                    'redirect' => route('scans.index', ['tab' => 'asset', 'site_code' => $SiteSettings -> code]),
                ],
                true,
                Response::HTTP_OK
            );
        }
    }

    public function scans_redo($code)
    {
        $TransactionTimeStampScans = TransactionTimeStampScans::where('code', $code)->first();
        $data['domain'] = $TransactionTimeStampScans;
        return view('scans::modal.redo_domain_transaction')->with($data);
    }

    public function redo_process($code)
    {
        $TransactionTimeStampScans = TransactionTimeStampScans::where('code', $code)->first();
        $TransactionTimeStampScans->progress = 0;
        $TransactionTimeStampScans->save();

        return ajaxResponse(
            [
                'message' => "Successfully",
                'redirect' => '/scans',
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function scans_assets_edit_modal($id, $code, $page)
    {
        $Assets = Assets::where('code', $id)->first();
        $data['AssetsData'] = AssetsData::where('asset_id', $Assets->id)->get();
        $data['scans'] = $Assets;
        $data['code'] = $code;
        $data['code_asset'] = $id;
        $SiteSettings = TransactionTimeStampScans::where('code', $code)->first();
        $data['site'] = $SiteSettings;
        $data['page'] = $page;
        $data['DataTypes'] = DataTypes::all();
        return view('scans::modal.update_asset')->with($data);
    }

    public function scans_assets_edit(Request $request)
    {
        

        $Assets = Assets::where('code', $request->code_assets)->first();
        $Assets_id = $Assets->id;
        $arr= [];
        $site = SiteSettings::select('code')->where('id', $Assets -> site_id)->first()->code;
        // $Assets->raw_data = $request->assets[0]['raw_data'];
        // $Assets->save();

        foreach ($request->assets_data as $data) {
            $myArray = explode(',', $data['data_type']);
            if ($myArray[1]) {
                $AssetsData = AssetsData::where('id', $myArray[1])->first();
                $AssetsData->value = $data['raw_data'];
                $AssetsData->data_type_id = $myArray[0];

            } else {
                $AssetsData = new AssetsData;
                $AssetsData->code = generator_uuid();
                $AssetsData -> created_by = Auth::user()->id;
                $AssetsData->site_id = $request->assets[0]['site_id'];
                $AssetsData->domain_id = $request->assets[0]['domain_id'];
                $AssetsData->status = 1;
                $AssetsData->value = $data['raw_data'];
                $AssetsData->data_type_id = $myArray[0];
                $AssetsData->asset_id = $Assets_id;
                

            }
            

            $AssetsData->save();

            array_push($arr, $AssetsData->id);
        }

        
        
        AssetsData::where('asset_id', $Assets_id)->whereNotIn('id',$arr)->delete();
        
        if($request -> page == 'site'){
            return ajaxResponse(
                [
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => route('assetssite.index', ['id' => $site]),
                ],
                true,
                Response::HTTP_OK
            );
        }else if($request -> page == 'scan'){
            return ajaxResponse(
                [
                    'message' => langapp('changes_saved_successful'),
                    'redirect' => route('scans.index', ['tab' => 'asset', 'site_code' => $request->code]),
                ],
                true,
                Response::HTTP_OK
            );
        }
    }

}
