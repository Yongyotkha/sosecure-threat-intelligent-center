<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Log;
use App\LogPhishing;
use Yajra\DataTables\DataTables;
use App\Sites;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\SiteSettings\Entities\SiteSettings;
use Illuminate\Support\Str;

class ApiPhishingController extends ApiController
{
    public function table(Request $request)
    {
        try
        {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false)
            {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }
            else
            { 
                if($data['data']['menu'] !== 'phishing_detection')
                {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }
                else
                {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200')
                    {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $filter_site = @$data['data']['filter_site'];
                    $filter_url = @$data['data']['filter_url'];
                    $filter_ip = @$data['data']['filter_ip'];
                    $filter_serverity = @$data['data']['filter_serverity'];
                    $filter_type = @$data['data']['filter_type'];

                    $site_id = DB::table('site')->where(['code' => $filter_site])->first()->id;

                    $logPhishing = LogPhishing::
                        join('site', 'log_phishing.site_id', 'site.id')
                        ->select(
                            'log_phishing.*',
                            'site.name as site_name'
                        )
                        ->where('log_phishing.transaction_status', 3)
                        ->where('log_phishing.url_is_work', 1)
                        ->whereNull('log_phishing.deleted_at');

                    if(@$site_id && @$site_id != null)
                    {
                        $logPhishing->where('log_phishing.site_id', $site_id);
                    }

                    if(@$filter_url && @$filter_url != null)
                    {
                        $logPhishing->where('log_phishing.url', 'like', '%'.$filter_url.'%');
                    }

                    if(@$filter_ip && @$filter_ip != null)
                    {
                        $logPhishing->where('log_phishing.ip', 'like', '%'.$filter_ip.'%');
                    }

                    if(@$filter_serverity && @$filter_serverity != null)
                    {
                        $logPhishing->where('log_phishing.serverity', $filter_serverity);
                    }

                    if(@$filter_type && @$filter_type != null)
                    {
                        $logPhishing->where('log_phishing.type', $filter_type);
                    }

                    $logPhishing->orderBy('log_phishing.created_at', 'DESC')->get();

                    $res = DataTables::of($logPhishing)
                        ->editColumn('status', function ($collection) {
                            $html = '';
                            $html .= '<label class="switch">
                                        <input type="checkbox" id="status_' . $collection->id . '" onchange="change_status(\'' . $collection->id . '\')" name="status" value="1" checked>
                                        <span></span>
                                    </label>';
                            return $html;
                        })
                        ->editColumn('c_serverity', function ( $collection) {
                            
                            $html = '';
                            if($collection->serverity == 'Critical')
                            {
                                $html .=  '<span class="badge" style="background-color: #b93624;">Critical</span>';
                            }
                            else if($collection->serverity == 'High')
                            {
                                $html .= '<span class="badge" style="background-color: #fcc838;">High</span>';
                            }
                            else if($collection->serverity == 'Medium')
                            {
                                $html .= '<span class="badge" style="background-color: #f2ff15;color:#333;">Medium</span>';
                            }
                            else if($collection->serverity == 'Low')
                            {
                                $html .= '<span class="badge" style="background-color: #88ce4f;">Low</span>';
                            }
                            else if($collection->serverity == 'Information')
                            {
                                $html .= '<span class="badge" style="background-color: #00dcff;">Information</span>';
                            }
                            else
                            {
                                $html .= '-';
                            }

                            return $html;
                        })
                        ->editColumn('c_status', function($query) {
                            $html = '';
                            // $html .= '
                            //     <div class="text-center">
                            //         <label class="switch">
                            //             <input type="checkbox" id="phishing-status-'.$query->id.'" onchange="change_status_phishing('.$query->id.')" 
                            //     ';
                            //     if($query->status == 1)
                            //     {
                            //         $html .= 'checked';
                            //     }
                            //     $html .= '            
                            //             value="1">
                            //             <span></span>
                            //         </label>
                            //     </div>
                            // ';

                            if($query->status == 1)
                            {
                                $html.= '
                                    <span class="badge" style="background-color: #88ce4f;">Active</span>
                                ';
                            }
                            else
                            {
                                $html.= '
                                    <span class="badge" style="background-color: #b93624;">Inactive</span>
                                ';
                            }

                            return $html;
                        })
                        ->editColumn('action', function ( $collection) {
                            return '<a href="'.route('phishing_detection.view', ['code' => $collection->code]).'" class="btn btn-info btn-xs" data-toggle="ajaxModal">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="'.route('phishing_detection.edit', ['code' => $collection->code]).'" class="btn btn-warning btn-xs" data-toggle="ajaxModal">
                                        <i class="fas fa-solid fa-pen"></i>
                                    </a>

                                    <a href="'.route('phishing_detection.delete', ['code' => $collection->code]).'" class="btn btn-danger btn-xs" data-toggle="ajaxModal">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                    ';
                                    // <a href="#" class="btn btn-danger btn-xs">
                                    //     <i class="fas fa-trash"></i>
                                    // </a>
                        })
                        ->rawColumns(['c_serverity','status','c_status','action'])
                        // ->toJson();
                        ->make(true);

                    $data_count = $logPhishing->count();

                    $response = [
                        "data" => $res,
                        "recordsFiltered_count"=> $data_count,
                        "recordsTotal_count" => $data_count,
                        // "data" => DataTables::of($logPhishing->skip(@$data['data']['start'])->take(@$data['data']['length'])->get())->rawColumns(['feedcontent','get_brand_abuse_feed_one.feedcontent'])->toJson(),
                        "page" => langapp('phishing_detection'),
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                    
                }
            }
        } 
        catch (\Exception $e) 
        {
            $response = array(
                'status_code' => 500,
                'message' => $e -> getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function create_phishing_detection(Request $request)
    {
        try
        {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false)
            {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }
            else
            { 
                if($data['data']['menu'] !== 'phishing_detection')
                {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }
                else
                {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200')
                    {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $edit = false;
                    $view = false;
                    $page = langapp('phishing_detection');

                    // $get_role_custom_first = @get_role_custom();
                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $SiteSettings = @$get_role_custom_first['SiteSettings'];
                    $site_settings = $SiteSettings;

                    // return view('phishingdetection::modal.add')->with($data);

                    $response = [
                        'edit' => $edit,
                        'view' => $view,
                        'page' => $page,
                        'site_settings' => $site_settings
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } 
        catch (\Exception $e) 
        {
            $response = array(
                'status_code' => 500,
                'message' => $e -> getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }

    }

    public function save_phishing(Request $request)
    {
        try
        {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false)
            {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }
            else
            { 
                if($data['data']['menu'] !== 'phishing_detection')
                {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }
                else
                {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200')
                    {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $phishing_id = @$data['data']['phishing_id'];
                    $phishing_code = @$data['data']['phishing_code'];
                    $site_id = @$data['data']['site_id'];
                    $url_detection = @$data['data']['url_detection'];
                    $url = @$data['data']['url'];
                    $ip = @$data['data']['ip'];
                    $score = @$data['data']['score'];
                    $type = @$data['data']['type'];
                    $serverity = @$data['data']['serverity'];
                    $status = @$data['data']['status'];

                    $response = [];

                    if(!@$phishing_id)
                    {
                        $main_data = [];

                        $main_data['site_id'] = $site_id;
                        $main_data['url_detection'] = $url_detection;
                        $main_data['url'] = $url;
                        $main_data['ip'] = $ip;
                        $main_data['score'] = $score;
                        $main_data['type'] = $type;
                        $main_data['serverity'] = $serverity;
                        $main_data['code'] = (string)Str::uuid();
                        $main_data['transaction_status'] = 3;
                        $main_data['url_is_work'] = 1;
                        $main_data['status'] = @$status ? 1 : 0;
            
                        LogPhishing::create($main_data);
            
                        $response = [
                            'status' => 'success',
                            'message' => langapp('save_successful'),
                            'main_data' => $main_data
                        ];
                    }
                    else
                    {
                        $main_data = [];
            
                        $main_data['site_id'] = $site_id;
                        $main_data['url_detection'] = $url_detection;
                        $main_data['url'] = $url;
                        $main_data['ip'] = $ip;
                        $main_data['score'] = $score;
                        $main_data['type'] = $type;
                        $main_data['serverity'] = $serverity;

                        LogPhishing::where(['id' => $phishing_id])->update($main_data);
            
                        $response = [
                            'status' => 'success',
                            'message' => langapp('changes_saved_successful'),
                            'main_data' => $main_data
                        ];
                    }  

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } 
        catch (\Exception $e) 
        {
            $response = array(
                'status_code' => 500,
                'message' => $e -> getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function edit_phishing_detection(Request $request)
    {
        try
        {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false)
            {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }
            else
            { 
                if($data['data']['menu'] !== 'phishing_detection')
                {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }
                else
                {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200')
                    {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code = @$data['data']['code'];

                    $query_edit = LogPhishing::where(['code' => $code])->first();

                    $edit = true;
                    $view = false;
                    $page = langapp('phishing_detection');

                    // $get_role_custom_first = @get_role_custom();
                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $SiteSettings = @$get_role_custom_first['SiteSettings'];
                    $site_settings = $SiteSettings;

                    // return view('phishingdetection::modal.add')->with($data);

                    $response = [
                        'edit' => $edit,
                        'view' => $view,
                        'page' => $page,
                        'site_settings' => $site_settings,
                        'query_edit' => $query_edit
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } 
        catch (\Exception $e) 
        {
            $response = array(
                'status_code' => 500,
                'message' => $e -> getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function view_phishing_detection(Request $request)
    {
        try
        {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false)
            {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }
            else
            { 
                if($data['data']['menu'] !== 'phishing_detection')
                {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }
                else
                {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200')
                    {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code = @$data['data']['code'];

                    $query_edit = LogPhishing::where(['code' => $code])->first();

                    $edit = true;
                    $view = true;
                    $page = langapp('phishing_detection');

                    // $get_role_custom_first = @get_role_custom();
                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $SiteSettings = @$get_role_custom_first['SiteSettings'];
                    $site_settings = $SiteSettings;

                    // return view('phishingdetection::modal.add')->with($data);

                    $response = [
                        'edit' => $edit,
                        'view' => $view,
                        'page' => $page,
                        'site_settings' => $site_settings,
                        'query_edit' => $query_edit
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } 
        catch (\Exception $e) 
        {
            $response = array(
                'status_code' => 500,
                'message' => $e -> getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function update_status_phishing(Request $request)
    {
        try
        {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false)
            {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }
            else
            { 
                if($data['data']['menu'] !== 'phishing_detection')
                {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }
                else
                {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200')
                    {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    
                    $id = $data['data']['id'];
                    $status = $data['data']['status'];

                    $update_status = LogPhishing::where('id', $id)->update(['status' => $status]);

                    $response = [
                        'status_code' => '200'
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } 
        catch (\Exception $e) 
        {
            $response = array(
                'status_code' => 500,
                'message' => $e -> getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }


    }

    // public function delete_phishing_detection(Request $request)
    // {
    //     $data["code"] = $request->code;
    //     return view('phishingdetection::modal.delete')->with($data);
    // }

    public function delete_phishing(Request $request)
    {
        try
        {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false)
            {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }
            else
            { 
                if($data['data']['menu'] !== 'phishing_detection')
                {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }
                else
                {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200')
                    {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code = @$data['data']['code'];

                    LogPhishing::where('code', $code)->update(['deleted_at' => date('Y-m-d H:i:s')]);

                    $response = [
                        'status_code' => '200'
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } 
        catch (\Exception $e) 
        {
            $response = array(
                'status_code' => 500,
                'message' => $e -> getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function data_chart_timeline(Request $request)
    {
        try
        {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false)
            {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }
            else
            { 
                if($data['data']['menu'] !== 'phishing_detection')
                {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }
                else
                {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200')
                    {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    // $input = $request->all();
                    // $site_id = $request->site_id;
                    // $start_date_input = $request->start_date;
                    // $end_date_input = $request->end_date;

                    $site_code = @$data['data']['filter_site'];
                    $filter_url = @$data['data']['filter_url'];
                    $filter_ip = @$data['data']['filter_ip'];
                    $filter_serverity = @$data['data']['filter_serverity'];
                    $filter_type = @$data['data']['filter_type'];

                    $site_id = DB::table('site')->where(['code' => $site_code])->first()->id;

                    $response = [];

                    // dd($input);

                    // // $start_date_input = date("Y-m-d", strtotime("+1 day", strtotime($start_date_input)));
                    // if($start_date_input == null && $end_date_input == null)
                    // {
                        $end_date_input = date('Y-m-d');
                        $start_date_input = date("Y-m-d", strtotime("-30 day", strtotime($end_date_input)));
                    // }

                    $start_date = date('Y-m-d', strtotime($start_date_input));
                    $end_date = date('Y-m-d', strtotime($end_date_input));
                    
                    $Variable1 = strtotime($start_date);
                    $Variable2 = strtotime($end_date);
                    
                    for ($currentDate = $Variable1; $currentDate <= $Variable2; $currentDate += (86400)) {
                                                        
                        $Store = date('Y-m-d', $currentDate);
                        $Store2 = date("Y-m-d", strtotime("+1 day", strtotime($Store)));
                        
                        $day = explode('-', $Store);

                        $query_timeline = LogPhishing::
                            where(function ($query_site) use ($site_id) {
                                if(@$site_id)
                                {
                                    $query_site->where('site_id', $site_id);
                                }
                            })
                            ->where(function ($query) use ($filter_url){
                                if($filter_url)
                                {
                                    $query->where('url', 'like', '%'.$filter_url.'%');
                                }
                            })
                            ->where(function ($query) use ($filter_ip){
                                if($filter_ip)
                                {
                                    $query->where('ip', 'like', '%'.$filter_ip.'%');
                                }
                            })
                            ->where(function ($query) use ($filter_serverity){
                                if($filter_serverity)
                                {
                                    $query->where('serverity', $filter_serverity);
                                }
                            })
                            ->where(function ($query) use ($filter_type){
                                if($filter_type)
                                {
                                    $query->where('type', $filter_type);
                                }
                            })
                            // where('status', '1')
                            ->whereNull('deleted_at')
                            ->whereBetween('created_at', [$Store.' 00:00:00', $Store.' 23:59:59'])
                            ->get();

                        $count = count($query_timeline);

                        $response['day'][] = $day[2];
                        $response['date'][] = $Store;
                        $response['count'][] = $count;
                    }

                    // return response()->json($data);
                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } 
        catch (\Exception $e) 
        {
            $response = array(
                'status_code' => 500,
                'message' => $e -> getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function data_chart_circle(Request $request)
    {
        try
        {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false)
            {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }
            else
            { 
                if($data['data']['menu'] !== 'phishing_detection')
                {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }
                else
                {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200')
                    {
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    // $input = $request->all();
                    // $site_id = $request->site_id;
                    // $start_date_input = $request->start_date;
                    // $end_date_input = $request->end_date;

                    $site_code = @$data['data']['filter_site'];
                    $filter_url = @$data['data']['filter_url'];
                    $filter_ip = @$data['data']['filter_ip'];
                    $filter_serverity = @$data['data']['filter_serverity'];
                    $filter_type = @$data['data']['filter_type'];

                    $site_id = DB::table('site')->where(['code' => $site_code])->first()->id;

                    $count_referrer = LogPhishing::
                        // where('status', '1')
                        where('type', 'Referrer')
                        ->where(function ($query) use ($site_id){
                            if($site_id)
                            {
                                $query->where('site_id', $site_id);
                            }
                        })
                        ->where(function ($query) use ($filter_url){
                            if($filter_url)
                            {
                                $query->where('url', 'like', '%'.$filter_url.'%');
                            }
                        })
                        ->where(function ($query) use ($filter_ip){
                            if($filter_ip)
                            {
                                $query->where('ip', 'like', '%'.$filter_ip.'%');
                            }
                        })
                        ->where(function ($query) use ($filter_serverity){
                            if($filter_serverity)
                            {
                                $query->where('serverity', $filter_serverity);
                            }
                        })
                        ->where(function ($query) use ($filter_type){
                            if($filter_type)
                            {
                                $query->where('type', $filter_type);
                            }
                        })
                        ->whereNull('deleted_at')
                        ->count();

                    $count_threat_feed = LogPhishing::
                        // where('status', '1')
                        where('type', 'Threat Feed')
                        ->where(function ($query) use ($site_id){
                            if($site_id)
                            {
                                $query->where('site_id', $site_id);
                            }
                        })
                        ->where(function ($query) use ($filter_url){
                            if($filter_url)
                            {
                                $query->where('url', 'like', '%'.$filter_url.'%');
                            }
                        })
                        ->where(function ($query) use ($filter_ip){
                            if($filter_ip)
                            {
                                $query->where('ip', 'like', '%'.$filter_ip.'%');
                            }
                        })
                        ->where(function ($query) use ($filter_serverity){
                            if($filter_serverity)
                            {
                                $query->where('serverity', $filter_serverity);
                            }
                        })
                        ->where(function ($query) use ($filter_type){
                            if($filter_type)
                            {
                                $query->where('type', $filter_type);
                            }
                        })
                        ->whereNull('deleted_at')
                        ->count();

                    $count_domain_name = LogPhishing::
                        // where('status', '1')
                        where('type', 'Domain name')
                        ->where(function ($query) use ($site_id){
                            if($site_id)
                            {
                                $query->where('site_id', $site_id);
                            }
                        })
                        ->where(function ($query) use ($filter_url){
                            if($filter_url)
                            {
                                $query->where('url', 'like', '%'.$filter_url.'%');
                            }
                        })
                        ->where(function ($query) use ($filter_ip){
                            if($filter_ip)
                            {
                                $query->where('ip', 'like', '%'.$filter_ip.'%');
                            }
                        })
                        ->where(function ($query) use ($filter_serverity){
                            if($filter_serverity)
                            {
                                $query->where('serverity', $filter_serverity);
                            }
                        })
                        ->where(function ($query) use ($filter_type){
                            if($filter_type)
                            {
                                $query->where('type', $filter_type);
                            }
                        })
                        ->whereNull('deleted_at')
                        ->count();

                    $count_other = LogPhishing::
                        // where('status', '1')
                        where('type', 'Other')
                        ->where(function ($query) use ($site_id){
                            if($site_id)
                            {
                                $query->where('site_id', $site_id);
                            }
                        })
                        ->where(function ($query) use ($filter_url){
                            if($filter_url)
                            {
                                $query->where('url', 'like', '%'.$filter_url.'%');
                            }
                        })
                        ->where(function ($query) use ($filter_ip){
                            if($filter_ip)
                            {
                                $query->where('ip', 'like', '%'.$filter_ip.'%');
                            }
                        })
                        ->where(function ($query) use ($filter_serverity){
                            if($filter_serverity)
                            {
                                $query->where('serverity', $filter_serverity);
                            }
                        })
                        ->where(function ($query) use ($filter_type){
                            if($filter_type)
                            {
                                $query->where('type', $filter_type);
                            }
                        })
                        ->whereNull('deleted_at')
                        ->count();

                    $response = [
                        'count_referrer' => $count_referrer,
                        'count_threat_feed' => $count_threat_feed,
                        'count_domain_name' => $count_domain_name,
                        'count_other' => $count_other,
                    ];

                    // return response()->json($response);
                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } 
        catch (\Exception $e) 
        {
            $response = array(
                'status_code' => 500,
                'message' => $e -> getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    private function dataFalse($bearerToken, $mode, $data){
        try {
            $header = $bearerToken;
            $site = $this->AuthorizationRegister($header, $mode);
            if($site['status_code'] !== '200'){
                return $this->AuthorizationRegister($header, $mode);
            }
            $value = $data;
            $data = encrypt_decrypt('decrypt', $value, $header, $site['data']['ip_key'],  $site['data']['mac_address_key']);

            if($data === false){
                return $data;
            }else{
                $data_return = [
                    'site' => $site,
                    'data' => json_decode($data, true),
                ];
                return $data_return;
            }

        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }
}
