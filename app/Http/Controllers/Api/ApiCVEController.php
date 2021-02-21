<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Bookmark;
use App\DataLeakFeed;
use App\DataLeakFeedTemp;
use App\DataLeakSocialRef;
use App\Entities\IndicatorSummaryYear;
use App\leak_socail_ref_temp;
use App\R_s_s_news;
use App\ReadCategories;
use App\ReadNews;
use App\TransactionTimeStampScans;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Assets\Entities\OSType;
use Modules\CategorySettings\Entities\CategorySettings;
use Modules\MonitoringVulnerabilitys\Entities\CVEAssets;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
use Modules\RSSFeedSettings\Entities\RSSNews;
use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Modules\Scans\Entities\Assets;
use Modules\Scans\Entities\AssetsData;
use Modules\Scans\Entities\CPE;
use Modules\SiteSettings\Entities\DataCveven;
use Modules\SiteSettings\Entities\Domain;
use Modules\SiteSettings\Entities\SiteNewsRelated;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\Users\Entities\UserSite;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\UTCDateTime;
use Yajra\DataTables\DataTables;
use Modules\Users\Entities\User;
use Modules\WebDefacement\Entities\WebdefacmentDataCheck;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use Symfony\Polyfill\Intl\Idn\Resources\unidata\Regex;

class ApiCVEController extends ApiController
{
    public function vulnerabilitys_table(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'vulnerabilities'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $date_start = $data['data']['date_start'];
                    $date_end = $data['data']['date_end'];
                    $search = $data['data']['search'];
                    $site = $data['data']['site'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $keywords = $data['data']['keywords'];
                    $assets = $data['data']['assets'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $check = $data['data']['check'];
                    $datatype = $data['data']['datatype'];
                    $level = $data['data']['level'];
                    $column = $data['data']['column'];
                    $dir = $data['data']['dir'];
                    

                    $date_start_explode = explode(" ", $date_start);
                $date_start_date = @$date_start_explode[0];
                $date_start_time = @$date_start_explode[1] . ' ' . @$date_start_explode[2];

                $date_start_date_format = date("Y-m-d", strtotime($date_start_date));

                $date_start_time_time = date("H:i", strtotime($date_start_time));
                $date_start_datetime_format = $date_start_date_format . ' ' . $date_start_time_time . ':00';


                $date_end_explode = explode(" ", $date_end);
                $date_end_date = @$date_end_explode[0];
                $date_end_time = @$date_end_explode[1] . ' ' . @$date_end_explode[2];

                $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                $date_end_time_time = date("H:i", strtotime($date_end_time));
                $date_end_datetime_format = $date_end_date_format . ' ' . $date_end_time_time . ':00';


                $model = '';
                $html = '';

                $site_id = null;
                if ($search == 1) {
                    $model = CVEMapping::with('get_site')->with('get_cve_asset');;

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

                    // if ($site) {
                    //     $site_id = $site;
                    //     $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id) {
                    //         $query->where('site_id', $site_id);
                    //     });
                    // }


                    if ($assets) {
                        $model = $model->where('cveven_id', '=', $assets);
                    } 
                    if ($keywords) {
                        $model = $model->where('namecve', 'LIKE', '%' . $keywords . '%');
                    }

                    if ($isDateSearch) {
                        $model = $model->whereBetween('modified', array($date_start_date_format, $date_end_date_format));
                    }
                    

                    if($check==1){
                        $model = $model->where('is_fix', '=', $check);
                    } else if($check==2) {

                    }else{
                        $model = $model->where('is_fix', '=', 0);
                    }


                    if ($datatype) {
        
                        $model = $model->whereIn('severity', $datatype);
                    }   
                } else {
                    $model = CVEMapping::where('is_fix', 0)->with('get_site')->with('get_cve_asset');
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


                    // if ($site) {
                        
                    //     $model = $model->where('site_id', $site);


                    // }

                    
                }

                if($level){
                        if($level =='critical'){
                            $model = $model->where('severity', 'CRITICAL');
                        }
                        else if($level =='high'){
                            $model = $model->where('severity', 'HIGH');
                        }
                        else if($level =='medium'){
                            $model = $model->where('severity', 'MEDIUM');
                        }
                        else if($level =='low'){
                            $model = $model->where('severity', 'LOW');
                        }
                        else if($level =='none'){
                            $model = $model->where(function ($query) {
                                $query->where('severity', 'NONE')
                                    ->orWhere('severity', '');
                            });
                            
                        }
                    
                }

                if(!empty($column)){
                    if($column==3){
                        if($dir=='desc'){
                            $model = $model->orderByRaw("CASE
                            WHEN severity = 'CRITICAL' THEN 0
                            WHEN severity = 'HIGH' THEN 1
                            WHEN severity = 'MEDIUM' THEN 2
                            WHEN severity = 'LOW' THEN 3
                            WHEN severity = 'NONE' THEN 4
                            WHEN severity = '' THEN 4
                            ELSE 5
                            END")->orderBy( 'created_at','desc' )->get();
                        }else{
                            $model = $model->orderByRaw("CASE
                            WHEN severity = 'CRITICAL' THEN 4
                            WHEN severity = 'HIGH' THEN 3
                            WHEN severity = 'MEDIUM' THEN 2
                            WHEN severity = 'LOW' THEN 1
                            WHEN severity = 'NONE' THEN 0
                            WHEN severity = '' THEN 0
                            ELSE 5
                            END")->orderBy( 'created_at','desc' )->get();
                        }
                    }else{
                        
                    }
                }else{
                    $model = $model->orderByRaw("CASE
                    WHEN severity = 'CRITICAL' THEN 0
                    WHEN severity = 'HIGH' THEN 1
                    WHEN severity = 'MEDIUM' THEN 2
                    WHEN severity = 'LOW' THEN 3
                    WHEN severity = 'NONE' THEN 4
                    WHEN severity = '' THEN 4
                    ELSE 5
                END")->orderBy( 'created_at','desc' )->get();
                }
                
                foreach($model as $model_data){
                    $vendor = [];
                    $title = [];
                    $version = [];
                    $edition = [];

                    $hostname_asset = [];
                    $ip_asset = [];
                    $vendor_asset = [];
                    $title_asset = [];
                    $version_asset = [];
                    $edition_asset = [];
                    $site_asset = [];
                    if($site_id){
                        $DataCvevens = DataCveven::select('vendor','title','version','edition')->where('namecve', $model_data -> namecve)->get();
                        foreach($DataCvevens as $DataCveven){
                            $vendor[] = $DataCveven -> vendor;
                            $title[] = $DataCveven -> title;
                            $version[] = $DataCveven -> version;
                            $edition[] = !empty($DataCveven -> edition) ? $DataCveven -> edition : '-';
                        }
                        $CVEAssets = CVEAssets::whereIn('vendor', $vendor)->whereIn('title', $title)->whereIn('version', $version)->whereIn('edition', $edition)->where('site_id', $site_id)->get();
                    }else{
                        $DataCvevens = DataCveven::select('vendor','title','version','edition')->where('namecve', $model_data -> namecve)->get();
                        foreach($DataCvevens as $DataCveven){
                            $vendor[] = $DataCveven -> vendor;
                            $title[] = $DataCveven -> title;
                            $version[] = $DataCveven -> version;
                            $edition[] = !empty($DataCveven -> edition) ? $DataCveven -> edition : '-';
                        }
                        $CVEAssets = CVEAssets::whereIn('vendor', $vendor)->whereIn('title', $title)->whereIn('version', $version)->whereIn('edition', $edition)->get();
                    }

                    if(!empty($CVEAssets)){
                        foreach($CVEAssets as $item){
                            $site_setting = SiteSettings::select('name')->where('id', $item -> site_id)->first();
                            array_push($site_asset, '<span class="il-block">&nbsp;'.$site_setting->name.'</span>');
                            array_push($ip_asset, '<span class="il-block">&nbsp;'.$item->IP.'</span>');
                            array_push($hostname_asset, '<span class="il-block">&nbsp;'.$item->Hostname.'</span>');
                            array_push($vendor_asset, '<span class="il-block">&nbsp;'.$item->vendor.'</span>');
                            array_push($title_asset, '<span class="il-block">&nbsp;'.$item->title.'</span>');
                            array_push($version_asset, '<span class="il-block">&nbsp;'.$item->version.'</span>');
                            array_push($edition_asset, '<span class="il-block">&nbsp;'.$item->edition.'</span>');
                        }

                        $site_asset = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $site_asset);
                        $ip_asset = implode('<hr cdlass="m-0" style="border: 1px solid #efefef;">', (array) $ip_asset);
                        $hostname_asset = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $hostname_asset);
                        $vendor_asset = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $vendor_asset);
                        $title_asset = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $title_asset);
                        $version_asset = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $version_asset);
                        $edition_asset = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $edition_asset);
                    }else{
                        array_push($site_asset, '<span class="il-block">&nbsp; - </span>');
                        array_push($ip_asset, '<span class="il-block">&nbsp; - </span>');
                        array_push($hostname_asset, '<span class="il-block">&nbsp; - </span>');
                        array_push($vendor, '<span class="il-block">&nbsp; - </span>');
                        array_push($title_asset, '<span class="il-block">&nbsp; - </span>');
                        array_push($version_asset, '<span class="il-block">&nbsp; - </span>');
                        array_push($edition_asset, '<span class="il-block">&nbsp; - </span>');

                        $site_asset = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $ip_asset);
                        $ip_asset = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $ip_asset);
                        $hostname_asset = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $hostname_asset);
                        $vendor_asset = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $vendor_asset);
                        $title_asset = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $title_asset);
                        $version_asset = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $version_asset);
                        $edition_asset = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $edition_asset);
                    }

                    $model_data['site'] = $site_asset;
                    $model_data['ip'] = $ip_asset;
                    $model_data['hostname'] = $hostname_asset;
                    $model_data['vendor'] = $vendor_asset;
                    $model_data['title'] = $title_asset;
                    $model_data['version'] = $version_asset;
                    $model_data['edition'] = $edition_asset;
                }

                $res = DataTables::of($model)
                    ->editColumn('chk', function (CVEMapping $model) {
                        return '<label><input type="checkbox"  name="cve_id" class="cve_id" value="' . $model->id . '"><span class="label-text"></span></label>';
                    })
                    ->addColumn('name_cve', function (CVEMapping $model) {
                        return '<span class="d-inline">'.$model->namecve.'</span>';
                    })
                    ->addColumn('description', function (CVEMapping $model) {
                        $html = '';
                        $html .= '<div class="text-trucate-ovf">'.$model->description.'</div>';
                        $html .= '<strong>Published:</strong> '.@$model->published.'&nbsp; &nbsp; <strong>Modified:</strong> '.@$model->modified.'';
                        return $html;
                    })
                    ->addColumn('site', function (CVEMapping $model) {
                        return @$model->get_site->name;
                    })
                    ->addColumn('hostname', function (CVEMapping $model) {
                        return @$model->get_cve_asset -> Hostname?$model->get_cve_asset -> Hostname:'-';
                    })
                    ->addColumn('ip', function (CVEMapping $model) {
                        return @$model->get_cve_asset -> IP?$model->get_cve_asset -> IP:'-';;
                    })
                    ->addColumn('vendor', function (CVEMapping $model) {
                        return @$model->get_cve_asset -> vendor?$model->get_cve_asset -> vendor:'-';;
                    })
                    ->addColumn('title', function (CVEMapping $model) {
                        return @$model->get_cve_asset -> title?$model->get_cve_asset -> title:'-';;
                    })
                    ->addColumn('version', function (CVEMapping $model) {
                        return @$model->get_cve_asset -> version?$model->get_cve_asset -> version:'-';;
                    })
                    ->addColumn('edition', function (CVEMapping $model) {
                        return @$model->get_cve_asset -> edition?$model->get_cve_asset -> edition:'-';;
                    })
                    ->addColumn('cvss_severity', function (CVEMapping $model) {
                        $html = '';
                        if($model->severity===""){
                            $dummyServerity = 'NONE';
                        }else{
                            $dummyServerity = $model->severity;
                        }
                        $html .= get_CVSS_Severity_status($model->cvss_score,$dummyServerity,'badg');
                        return $html;
                    })
                    ->addColumn('transaction', function (CVEMapping $model) {
                        return $model->created_at;
                    })
                    ->editColumn(
                        'fixed',
                        function ($model) {
                            if ($model->is_fix == 1) {
                                $checked_val = 'checked';
                            } else {
                                $checked_val = '';
                            }
                            $html = '';

                            $html .= '<label class="switch">
                                            <input type="checkbox" id="cve_active_' . $model->id . '" onchange="monitoringvulnerabilitys_active(\'' . $model->id . '\')" ' . $checked_val . ' name="active" value="1">
                                            <span></span>
                                        </label>';

                            return $html;
                        }
                    )


                    ->rawColumns(['chk', 'name_cve', 'description', 'cvss_severity', 'transaction', 'fixed', 'site', 'hostname', 'ip', 'vendor', 'title', 'version', 'edition'])
                    ->toJson();

                    $response = [
                        "data" => $res,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
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

    public function vulnerabilitys_index(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'vulnerabilities'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $site_id_arr = @$get_role_custom_first['site_id_arr'];
                    $response['page'] = langapp('monitoring_vulnerabilitys');

                    $cve_assets = CVEAssets::where("active", '=', 1);
                    if(@$get_role_custom_first['superadmin'] == 1) {
                        
        
                    }else if(@$get_role_custom_first['client'] == 1) {
                        $cve_assets = $cve_assets->whereIn('site_id', $site_id_arr)->where('status', 1);
        
                    }else if(@$get_role_custom_first['site_support'] == 1) {
                        $cve_assets = $cve_assets->whereIn('site_id', $site_id_arr);
        
                    }else if(@$get_role_custom_first['site_admin'] == 1) {
                        $cve_assets = $cve_assets->whereIn('site_id', $site_id_arr);
        
                    }else if(@$get_role_custom_first['site_client'] == 1) {
                        $cve_assets = $cve_assets->whereIn('site_id', $site_id_arr)->where('status', 1);
        
                    }

                    $response['count_CVEAssets'] = $cve_assets->count();
                    $response['cve_assets'] = $cve_assets->get();
                    


                    $count_CVEMapping = new CVEMapping;                   
                    if(@$get_role_custom_first['superadmin'] == 1) {
                        
        
                    }else if(@$get_role_custom_first['client'] == 1) {
                        $count_CVEMapping = $count_CVEMapping->whereIn('site_id', $site_id_arr)->where('status', 1);
        
                    }else if(@$get_role_custom_first['site_support'] == 1) {
                        $count_CVEMapping = $count_CVEMapping->whereIn('site_id', $site_id_arr);
        
                    }else if(@$get_role_custom_first['site_admin'] == 1) {
                        $count_CVEMapping = $count_CVEMapping->whereIn('site_id', $site_id_arr);
        
                    }else if(@$get_role_custom_first['site_client'] == 1) {
                        $count_CVEMapping = $count_CVEMapping->whereIn('site_id', $site_id_arr)->where('status', 1);
        
                    }
                    $response['count_CVEMapping'] = $count_CVEMapping->count();

                    $count_isFix= CVEMapping::where("is_fix", '=', 1);
                        
                        if(@$get_role_custom_first['superadmin'] == 1) {
                        
        
                        }else if(@$get_role_custom_first['client'] == 1) {
                            $count_isFix = $count_isFix->whereIn('site_id', $site_id_arr)->where('status', 1);
            
                        }else if(@$get_role_custom_first['site_support'] == 1) {
                            $count_isFix = $count_isFix->whereIn('site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_admin'] == 1) {
                            $count_isFix = $count_isFix->whereIn('site_id', $site_id_arr);
            
                        }else if(@$get_role_custom_first['site_client'] == 1) {
                            $count_isFix = $count_isFix->whereIn('site_id', $site_id_arr)->where('status', 1);
            
                        }
                    $response['count_isFix'] = $count_isFix->count();

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
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

    public function vulnerabilitys_load_cve(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'vulnerabilities'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $site = $data['data']['site'];
                    if(empty($site)){
                        $response['page'] = langapp('monitoring_vulnerabilitys');
                        $response['count_CVEAssets'] = CVEAssets::where("active", '=', 1)->count();
                        $response['count_CVEMapping'] = CVEMapping::count();
                        $response['count_isFix'] = CVEMapping::where("is_fix", '=', 1)->count();
                    }else{
                        $response['page'] = langapp('monitoring_vulnerabilitys');
                        $response['count_CVEAssets'] = CVEAssets::where("active", '=', 1)->where('site_id', $site)->count();
                        $response['count_CVEMapping'] = CVEMapping::where('site_id', $site)->count();
                        $response['count_isFix'] = CVEMapping::where("is_fix", '=', 1)->where('site_id', $site)->count();
                    }

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
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

    public function vulnerabilitys_count(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'vulnerabilities'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $startDate = $data['data']['startDate'];
                    $endDate = $data['data']['endDate'];
                    $count = $data['data']['count'];
                    $site = $data['data']['site'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $keywords = $data['data']['keywords'];
                    $assets = $data['data']['assets'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $check = $data['data']['check'];
                    $datatype = $data['data']['datatype'];
                    $level = $data['data']['level'];
                    
                    $model = '';
                    $html = '';

                    if ($count == 1) {
                        $model = new CVEMapping;
                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if(@$get_role_custom_first['superadmin'] == 1) {
                            
                        }else if(@$get_role_custom_first['client'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_support'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_admin'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_client'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }
            
            
                        if ($site) {
                            $site_id = $site;
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id) {
                                $query->where('site_id', $site_id);
                            });
                        }
                        if ($assets) {
                            $model = $model->where('cveven_id', '=', $assets);
                        } 
                        if ($keywords) {
                            $model = $model->where('namecve', 'LIKE', '%' . $keywords . '%');
                        }
            
                        if ($isDateSearch) {
                            $date_start = $startDate;
                            $date_end = $endDate;
                    
                            $date_start_explode = explode(" ", $date_start);
                            $date_start_date = @$date_start_explode[0];
                            $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                    
                    
                            $date_end_explode = explode(" ", $date_end);
                            $date_end_date = @$date_end_explode[0];
                            $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
            
                            $model = $model->whereBetween('modified', array($date_start_date_format, $date_end_date_format));
                        }
                        
                        if($check==1){
                            $model = $model->where('is_fix', '=', $check);
                        }
            
            
                        if ($datatype) {
                            $model = $model->whereIn('severity', $datatype);
                        }           

                    } else {
                        $model = new CVEMapping;
                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if(@$get_role_custom_first['superadmin'] == 1) {
                            
                        }else if(@$get_role_custom_first['client'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_support'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_admin'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_client'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }
            
                        if ($site) {
                            
                            $site_id = $site;
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id) {
                                $query->where('site_id', $site_id);
                            });
                        }
                    }
            
                    if($level){
                        if($level =='critical'){
                            $model=$model->where('severity', 'CRITICAL');
                        }
                        else if($level =='high'){
                            $model=$model->where('severity', 'HIGH');
                        }
                        else if($level =='medium'){
                            $model=$model->where('severity', 'MEDIUM');
                        }
                        else if($level =='low'){
                            $model=$model->where('severity', 'LOW');
                        }
                        else if($level =='none'){
                            $model = $model->where(function ($query) use ($request) {
                                $query->where('severity', 'NONE')
                                    ->orWhere('severity', '');
                            });
                        }
                    }
            
                    $model = $model->get();
                  
                    $count_assets = $model->groupBy('cveven_id')->count();
                    $count = $model->count();
                    $high = $model->where('severity', '=', 'HIGH')->count();
                    $medium = $model->where('severity', '=', 'MEDIUM')->count();
                    $critical = $model->where('severity', '=', 'CRITICAL')->count();
                    $low = $model->where('severity', '=', 'LOW')->count();
                    $none = $model->where('severity', '=', 'NONE')->count();
                    $none = $none+$model->where('severity', '=', '')->count();
            
                    $html .= '<ul class="total-count">
                        <li>
                            <h1>Total Asset</h1>
                            <span class="color-purple">' . $count_assets . '</span>
                        </li>
                        <li>
                            <h1>Total CVE</h1>
                            <span class="color-red">' . $count . '</span>
                        </li>
                    </ul>';
            
                    $response = [
                        "html" => $html,
                        "count" => $count,
                        "count_high" => $high,
                        "count_medium" => $medium,
                        "count_critical" => $critical,
                        "count_low" => $low,
                        "count_none" => $none,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
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

    public function vulnerabilitys_top_host(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'vulnerabilities'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

    
                    $date_start = $data['data']['date_start'];
                    $date_end = $data['data']['date_end'];
                    $count = $data['data']['count'];
                    $site = $data['data']['site'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $keywords = $data['data']['keywords'];
                    $assets = $data['data']['assets'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $check = $data['data']['check'];
                    $datatype = $data['data']['datatype'];
                    $level = $data['data']['level'];

                    $date_start_explode = explode(" ", $date_start);
                    $date_start_date = @$date_start_explode[0];
                    $date_start_time = @$date_start_explode[1] . ' ' . @$date_start_explode[2];

                    $date_start_date_format = date("Y-m-d", strtotime($date_start_date));

                    $date_start_time_time = date("H:i", strtotime($date_start_time));
                    $date_start_datetime_format = $date_start_date_format . ' ' . $date_start_time_time . ':00';


                    $date_end_explode = explode(" ", $date_end);
                    $date_end_date = @$date_end_explode[0];
                    $date_end_time = @$date_end_explode[1] . ' ' . @$date_end_explode[2];

                    $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                    $date_end_time_time = date("H:i", strtotime($date_end_time));
                    $date_end_datetime_format = $date_end_date_format . ' ' . $date_end_time_time . ':00';

                    if ($count == 1) {
                        $model = new CVEMapping;
                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if(@$get_role_custom_first['superadmin'] == 1) {
                            
                        }else if(@$get_role_custom_first['client'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_support'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_admin'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_client'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }

                        if ($site) {
                            $site_id = $site;
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id) {
                                $query->where('site_id', $site_id);
                            });
                        }
                        if ($assets) {
                            $model = $model->where('cveven_id', '=', $assets);
                        } 
                        if ($keywords) {
                            $model = $model->where('namecve', 'LIKE', '%' . $keywords . '%');
                        }

                        if ($isDateSearch) {
                            $model = $model->whereBetween('modified', array($date_start_date_format, $date_end_date_format));
                        }
                        
                        if($check==1){
                            $model = $model->where('is_fix', '=', $check);
                        }


                        if ($datatype) {
                            $model = $model->whereIn('severity', $datatype);
                        }
                        
                    }else{

                        $model = new CVEMapping;

                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if(@$get_role_custom_first['superadmin'] == 1) {
                            
                        }else if(@$get_role_custom_first['client'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_support'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_admin'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_client'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }

                        if ($site) {
                            
                            $site_id = $site;
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id) {
                                $query->where('site_id', $site_id);
                            });


                        }

                        

                    }

                    if($level){
                        if($level =='critical'){
                            $model=$model->where('severity', 'CRITICAL');
                        }
                        else if($level =='high'){
                            $model=$model->where('severity', 'HIGH');
                        }
                        else if($level =='medium'){
                            $model=$model->where('severity', 'MEDIUM');
                        }
                        else if($level =='low'){
                            $model=$model->where('severity', 'LOW');
                        }
                        else if($level =='none'){
                            $model = $model->where(function ($query) use ($request) {
                                $query->where('severity', 'NONE')
                                    ->orWhere('severity', '');
                            });
                        }
                    }

                    $get_ip = $model         
                        ->select(DB::raw('count(*) as cveven_count, cveven_id,CONCAT(vendor," ",IP) as vendor_ip
                        ,sum(severity = "HIGH") as HIGH
                        ,sum(severity = "MEDIUM") as MEDIUM
                        ,sum(severity = "LOW") as LOW
                        ,(sum(severity = "NONE") + sum(severity = "")) as NONE
                        ,sum(severity = "CRITICAL") as CRITICAL
                        '
                        ))
                        ->groupBy('cveven_id')
                        ->orderBy('cveven_count', 'desc')
                        ->limit(5)
                        ->join('cve_assets', 'data_datacve_mapping.cveven_id', '=', 'cve_assets.id')            
                        ->get();

                        $_array = array();
                        $severity_high = array();
                        $severity_critical = array();
                        $severity_medium = array();
                        $severity_low = array();
                        $severity_none = array();
                
                        if($get_ip) {
                        foreach($get_ip as $key ) {
                            $vendor_ip = @$key->vendor_ip;
                            $HIGH = @$key->HIGH;
                            $MEDIUM = @$key->MEDIUM;
                            $LOW = @$key->LOW;
                            $NONE = @$key->NONE;
                            $CRITICAL = @$key->CRITICAL;
                
                            array_push($_array, $vendor_ip);
                            array_push($severity_high, $HIGH);
                            array_push($severity_medium, $MEDIUM);
                            array_push($severity_low, $LOW);
                            array_push($severity_none, $NONE);
                            array_push($severity_critical, $CRITICAL);
                        }
                        } 

                    $severity_high = array_map(function($value) {
                        return intval($value);
                    }, $severity_high);
                    $severity_medium = array_map(function($value) {
                        return intval($value);
                    }, $severity_medium);
                    $severity_low = array_map(function($value) {
                        return intval($value);
                    }, $severity_low);
                    $severity_none = array_map(function($value) {
                        return intval($value);
                    }, $severity_none);
                    $severity_critical = array_map(function($value) {
                        return intval($value);
                    }, $severity_critical);
        
                    
            
                    $response = [
                        "ip" => $_array,
                        "severity_high" => $severity_high,
                        "severity_critical" => $severity_critical,
                        "severity_low" => $severity_low,
                        "severity_medium" => $severity_medium,
                        "severity_none" => $severity_none,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
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

    public function vulnerabilitys__fixed(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'vulnerabilities'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

    
                    $date_start = $data['data']['date_start'];
                    $date_end = $data['data']['date_end'];
                    $count = $data['data']['count'];
                    $site = $data['data']['site'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $keywords = $data['data']['keywords'];
                    $assets = $data['data']['assets'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $check = $data['data']['check'];
                    $datatype = $data['data']['datatype'];
                    $level = $data['data']['level'];

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

                    if ($count == 1) {
                        $model = CVEMapping::where('is_fix', 1);
                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if(@$get_role_custom_first['superadmin'] == 1) {
                            
                        }else if(@$get_role_custom_first['client'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_support'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_admin'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_client'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }


                        if ($site) {
                            $site_id = $site;
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id) {
                                $query->where('site_id', $site_id);
                            });
                        }
                        if ($assets) {
                            $model = $model->where('cveven_id', '=', $assets);
                        } 
                        if ($keywords) {
                            $model = $model->where('namecve', 'LIKE', '%' . $keywords . '%');
                        }

                        if ($isDateSearch) {
                            $model = $model->whereBetween('modified', array($date_start_date_format, $date_end_date_format));
                        }
                        
                        if($check==1){
                            $model = $model->where('is_fix', '=', $check);
                        }
                        if ($datatype) {
                            $model = $model->whereIn('severity', $datatype);
                        }

                        if($level){
                            if($level =='critical'){
                                $model=$model->where('severity', 'CRITICAL');
                            }
                            else if($level =='high'){
                                $model=$model->where('severity', 'HIGH');
                            }
                            else if($level =='medium'){
                                $model=$model->where('severity', 'MEDIUM');
                            }
                            else if($level =='low'){
                                $model=$model->where('severity', 'LOW');
                            }
                            else if($level =='none'){
                                $model = $model->where(function ($query) use ($request) {
                                    $query->where('severity', 'NONE')
                                        ->orWhere('severity', '');
                                });
                            }
                        }
                        
                        $model->get();
                        $get_ip = $model         
                        ->select(DB::raw('sum(severity = "HIGH") as HIGH
                        ,sum(severity = "MEDIUM") as MEDIUM
                        ,sum(severity = "LOW") as LOW
                        ,(sum(severity = "NONE") + sum(severity = "")) as NONE
                        ,sum(severity = "CRITICAL") as CRITICAL
                        '
                        ))      
                        ->get();
                
                        // if($get_ip->)
                        $isFix_high = intval($get_ip[0]->HIGH);
                        $isFix_medium = intval($get_ip[0]->MEDIUM);
                        $isFix_critical = intval($get_ip[0]->CRITICAL);
                        $isFix_low = intval($get_ip[0]->LOW);
                        $isFix_none = intval($get_ip[0]->NONE);
                    }else{
            
                        $isFix = CVEMapping::where('is_fix', 1);

                        if ($site) {
                            
                            $site_id = $site;
                            $isFix = $isFix->whereHas('get_cve_asset', function ($query) use ($site_id) {
                                $query->where('site_id', $site_id);
                            });


                        }

                        if($level){
                            if($level =='critical'){
                                $isFix=$isFix->where('severity', 'CRITICAL');
                            }
                            else if($level =='high'){
                                $isFix=$isFix->where('severity', 'HIGH');
                            }
                            else if($level =='medium'){
                                $isFix=$isFix->where('severity', 'MEDIUM');
                            }
                            else if($level =='low'){
                                $isFix=$isFix->where('severity', 'LOW');
                            }
                            else if($level =='none'){
                                // $isFix=$isFix->where('severity', 'NONE');
                                $isFix = $isFix->where(function ($query) use ($request) {
                                    $query->where('severity', 'NONE')
                                        ->orWhere('severity', '');
                                });

                                
                            }
                        }
                        

                        $isFix=$isFix->get();

                        $isFix_high = $isFix->where('severity', '=', 'HIGH')->count();
                        $isFix_medium = $isFix->where('severity', '=', 'MEDIUM')->count();
                        $isFix_critical = $isFix->where('severity', '=', 'CRITICAL')->count();
                        $isFix_low = $isFix->where('severity', '=', 'LOW')->count();
                        $isFix_none = $isFix->where('severity', '=', 'NONE')->count();
                        $isFix_none =  $isFix_none+$isFix->where('severity', '=', '')->count();
                    }       
                    $response = [
                        "isFix_critical" => $isFix_critical,
                        "isFix_medium" => $isFix_medium,
                        "isFix_high" => $isFix_high,
                        "isFix_low" => $isFix_low,
                        "isFix_none" => $isFix_none,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
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

    public function vulnerabilitys_change_status(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'vulnerabilities'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $id = $data['data']['id'];
                    $active = $data['data']['active'];

                    $CVEMapping = CVEMapping::where("id", $id)->first();
                    $CVEMapping->is_fix = $active;
                    $CVEMapping->save();

                    $response = [
                        "data" => 'success',
                    ];
                
                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
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

    public function vulnerabilitys_change_status_detail(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'vulnerabilities'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

    
                    $id = $data['data']['id'];
                    $id_asset = $data['data']['id_asset'];
                    $active = $data['data']['active'];

                    $CVEMapping = CVEMapping::where("id", $id)->first();
                    $CVEMapping->is_fix = $active;
                    $CVEMapping->save();
            
                    $CVEAssets = CVEAssets::where("id", $id_asset)->first();

                    $response = [
                        "code" => $CVEAssets -> code,
                    ];
                
                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
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

    public function vulnerabilitys_asset_data_detail(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'vulnerabilities'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

    
                    $code = $data['data']['code'];
                    $cve_asset = CVEAssets::where('active',1)->where('code',$code)->with('get_site')->first();
                    $page = langapp('monitoring_vulnerabilitys');
                    $response = [
                        "cve_asset" => $cve_asset,
                        "page" => $page,
                    ];
                
                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
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

    public function vulnerabilitys_cve_table(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'vulnerabilities'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $isDateSearch = $data['data']['isDateSearch'];
                    $fix = $data['data']['fix'];
                    $id = $data['data']['id'];
                    $date_start = $data['data']['date_start'];
                    $date_end = $data['data']['date_end'];

                    if($isDateSearch||$fix){
                        $model = CVEMapping::where('cveven_id',$id)->orderBy('modified', 'desc');
            
                        if ($isDateSearch) {
                       
                            $date_start = $startDate;
                            $date_end = $endDate;
                    
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
            
                            $model = $model->whereBetween('modified', array($date_start_date_format, $date_end_date_format));
                        }
            
                        if($fix){
                            if($fix==1){
                                $model = $model->where('is_fix',0);
                            }else if($fix==2){
                                $model = $model->where('is_fix',1);
                            }
            
                        }
                    }else{
                        $model = CVEMapping::where('cveven_id',$id)->where('is_fix',0)->orderBy('modified', 'desc');
                    }
                    
                    $model -> get();
             
                    $response = DataTables::of($model)->toJson();
                
                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
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

    public function vulnerabilitys_all_asset_data(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'vulnerabilities'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

    
                    $site = $data['data']['site'];
                    $model = CVEAssets::where('active',1)->with('get_site');
        
                    if($site){ 
                        $model = $model->where('site_id',$site);
                    }

                    $get_role_custom_first = $data['data']['get_role_custom_first'];
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
                    
                        

                    $model -> get();

                    $response = DataTables::of($model)->toJson();
                
                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
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

    public function vulnerabilitys_all(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'vulnerabilities'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

    
                    $date_start = $data['data']['date_start'];
                    $date_end = $data['data']['date_end'];
                    $count = $data['data']['count'];
                    $site = $data['data']['site'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $keywords = $data['data']['keywords'];
                    $assets = $data['data']['assets'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $check = $data['data']['check'];
                    $datatype = $data['data']['datatype'];
                    $level = $data['data']['level'];
                    $displayType = $data['data']['displayType'];


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

                    if ($count == 1) {
                        $model = new CVEMapping;
                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if(@$get_role_custom_first['superadmin'] == 1) {
                            
                        }else if(@$get_role_custom_first['client'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_support'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_admin'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_client'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }

                        if ($site) {
                            $site_id = $site;
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id) {
                                $query->where('site_id', $site_id);
                            });
                        }
                        
                        if ($assets) {
                            $model = $model->where('cveven_id', '=', $assets);
                        } 
                        if ($keywords) {
                            $model = $model->where('namecve', 'LIKE', '%' . $keywords . '%');
                        }

                        if ($isDateSearch) {
                            $model = $model->whereBetween('published', array($date_start_date_format, $date_end_date_format));
                        }
                        
                        if($check==2){
                            
                        }else if($check==1){
                            $model = $model->where('is_fix', '=', $check);
                        }

                        if ($datatype) {
                            $model = $model->whereIn('severity', $datatype);
                        }

                        

                    }else{

                        $model = new CVEMapping;

                        $site_id_arr = @$get_role_custom_first['site_id_arr'];
                        if(@$get_role_custom_first['superadmin'] == 1) {
                            
                        }else if(@$get_role_custom_first['client'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_support'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_admin'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }else if(@$get_role_custom_first['site_client'] == 1) {
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id_arr) {
                                $query->whereIn('site_id', $site_id_arr);
                            });
                        }



                        if ($site) {
                            $site_id = $site;
                            $model = $model->whereHas('get_cve_asset', function ($query) use ($site_id) {
                                $query->where('site_id', $site_id);
                            });
                        }
                    }

                    if($level){
                        if($level =='critical'){
                            $model=$model->where('severity', 'CRITICAL');
                        }
                        else if($level =='high'){
                            $model=$model->where('severity', 'HIGH');
                        }
                        else if($level =='medium'){
                            $model=$model->where('severity', 'MEDIUM');
                        }
                        else if($level =='low'){
                            $model=$model->where('severity', 'LOW');
                        }
                        else if($level =='none'){
                            $model = $model->where(function ($query) use ($request) {
                                $query->where('severity', 'NONE')
                                    ->orWhere('severity', '');
                            });
                        }
                    }
                    
                    if($displayType == 'mon'){
                        $get_month = $model->select(DB::raw('count(published)  as count_mon'),DB::raw('DAY(published) as mon'))
                        ->whereRaw('MONTH(published) = MONTH(CURDATE())')
                        ->groupBy('mon')
                        ->get();
                        $count_month = array_fill(0, (int)date('t'), 0);
                        foreach($get_month  as $key){
                            $count_month[$key->mon-1] = $key->count_mon;//update each month with the total value
                        }
                        $namexAxis = array();
                        foreach ($count_month as $key => $value) {
                            $namexAxis[$key] = (string)($key+1);
                        }
                        $nameyAxis = 'Number (Days)';
                        $nameSeries = 'Number of Days';
                    }else{
                        $get_month = $model->select(DB::raw('count(published)  as count_mon'),DB::raw('MONTH(published) as mon'))
                        ->whereRaw('YEAR(published) = YEAR(CURDATE())')
                        ->groupBy('mon')
                        ->get();
                        $count_month = [0,0,0,0,0,0,0,0,0,0,0,0];//initialize all months to 0
                        foreach($get_month  as $key){
                            $count_month[$key->mon-1] = $key->count_mon;//update each month with the total value
                        }
                        $namexAxis = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        $nameyAxis = 'Number (Months)';
                        $nameSeries = 'Number of Months';
                        
                    }
                    
                    $response = [
                        'sddad' => $get_month->toArray(),
                        "nameXAxis" => $namexAxis ,
                        "nameYAxis" => $nameyAxis ,
                        "nameSeries" => $nameSeries ,
                        "count_month" =>$count_month 
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
            }
        } catch (\Exception $e) {
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

    private function explode_val($val,$type=null,$url) {
        $result = '';
        if($val) {
            $val_arr = explode(",",$val);
            if($val_arr) {
                foreach($val_arr as $tag) {
                    if($type == 'tags') {
                        $result .=  '<a href="'.$url.'/indicators/tags/'.$tag.'">'.$tag.'</a> ,';
                    } else if ($type == 'groups') {
                        $result .=  '<a href="'.$url.'/indicators/groups/'.$tag.'">'.$tag.'</a> ,';
                    } else {
                        $result .=  '<a href="#">'.$tag.'</a> ,';
                    }
    
                }
                $result = rtrim($result,',');
            }
        } else {
            $result = '';
        }
        return $result;
    }
}
