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


class ApiAssetController extends ApiController
{
    public function table_asset(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'assets'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $Assets_list = [];
                    $menu = $data['data']['menu'];
                    $site = $data['data']['site'];
                    $domaincode = $data['data']['domaincode'];
                    $url = $data['data']['url'];

                    $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $site)->first();
                    $Assets_data = Assets::where('status', 1)->where('site_id', $SiteSettingsfor->id)->get();

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
                            $CPE_OtherCheck = 0;
                            if(!empty($CPE_Data)){
                                foreach ($CPE_Data as $CPE_Datakey => $CPE_Datavalue) {
                                    array_push($CPE_List, $CPE_Datavalue->result." - OSType: ".(isset($OsType[$CPE_Datavalue->os_type]["name"])?$OsType[$CPE_Datavalue->os_type]["name"]:""));
                                    array_push($CPE_Vendor, '<span class="il-block">&nbsp;'.$CPE_Datavalue->vendor.'</span>');
                                    array_push($CPE_Title, '<span class="il-block">&nbsp;'.$CPE_Datavalue->title.'</span>');
                                    array_push($CPE_Version, '<span class="il-block">&nbsp;'.$CPE_Datavalue->version.'</span>');
                                    array_push($CPE_Edition, '<span class="il-block">&nbsp;'.$CPE_Datavalue->edition.'</span>');
                                    array_push($CPE_Remark, '<span class="il-block">&nbsp;'.$CPE_Datavalue->remark.'</span>');
                                    array_push($CPE_Del, '<span class="il-block" style="box-sizing:border-box; -moz-box-sizing:border-box;">&nbsp;'.'<a href="'.route("assets.assets_delete_cpe", ["cpecode" => $CPE_Datavalue->code,"menu" => $menu]).'" class="btn btn-xs btn-danger" style="display:inline; font-size: 11px;" data-toggle="ajaxModal"><i class="fas fa-trash"></i></a>'.'</span>');
                                    array_push($CPE_Ostype, '<span class="il-block">&nbsp;'.(isset($OsType[$CPE_Datavalue->os_type]["name"])?$OsType[$CPE_Datavalue->os_type]["name"]:"").'</span>');
                                    if(($CPE_Datavalue->os_type!=1)&&($CPE_Datavalue->os_type!=2)){
                                        $CPE_OtherCheck = 1;
                                    }
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
                                }else{
                                    array_push($CPE_List, '<span class="il-block text-elipse-vul">&nbsp; - </span>');
                                    array_push($CPE_Vendor, '<span class="il-block text-elipse-vul">&nbsp; - </span>');
                                    array_push($CPE_Title, '<span class="il-block text-elipse-vul">&nbsp; - </span>');
                                    array_push($CPE_Version, '<span class="il-block text-elipse-vul">&nbsp; - </span>');
                                    array_push($CPE_Edition, '<span class="il-block text-elipse-vul">&nbsp; - </span>');
                                    array_push($CPE_Remark, '<span class="il-block text-elipse-vul">&nbsp; - </span>');
                                    array_push($CPE_Ostype, '<span class="il-block text-elipse-vul">&nbsp; - </span>');
                                    array_push($CPE_Del, '<span class="il-block text-elipse-vul">&nbsp; - </span>');
                    
                                    $CPE_List = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $CPE_List);
                                    $CPE_Vendor = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $CPE_Vendor);
                                    $CPE_Title = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $CPE_Title);
                                    $CPE_Version = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $CPE_Version);
                                    $CPE_Edition = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $CPE_Edition);
                                    $CPE_Remark = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $CPE_Remark);
                                    $CPE_Ostype = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $CPE_Ostype);
                                    $CPE_Del = implode('<hr class="m-0" style="border: 1px solid #efefef;">', (array) $CPE_Del);
                                }
                            }
                            
            
                        
                            $TTSS = TransactionTimeStampScans::select('code')->where('site_id', $value->site_id)->where('domain_id', $value->domain_id)->first();
                            if (count($Domain_list) == 0) {
                                $Assets_data_list = array();
                                $Assets_data_list['chk'] = "";
                                // $Assets_data_list['cpe'] = '<a href="'.route("assets.assets_add_cpe", ["id" => $value->code,"page" => $menu, "idip" => $IP_Listvalue->code]).'" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal"><i class="fas fa-plus"></i> Add CPE </a>';
                                // if(isset($TTSS->code)){
                                //     $Assets_data_list['action'] = '<a href="' . route("scans_assets.scans_assets_edit_modal", ["id" => $value->code, "code" => @$TTSS->code, "page" => $menu]) . '" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">
                                //     <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                                //     </a>';
                                // }else{
                                //     $Assets_data_list['action'] = '<a href="' . route("scans_assets.scans_assets_edit_modal", ["id" => $value->code, "code" => @$value->site_id, "page" => $menu]) . '" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">
                                //     <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                                //     </a>';
                                // }
                                
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
                                $Assets_data_list['CPE_OtherCheck'] = $CPE_OtherCheck;
                                array_push($Assets_list, $Assets_data_list);
            
                            } else {
                                foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                                    $Assets_data_list = array();
                                    $Assets_data_list['chk'] = "";
                                    // $Assets_data_list['cpe'] = '<a href="'.route("assets.assets_add_cpe", ["id" => $value->code,"page" => $menu, "idip" => $IP_Listvalue->code]).'" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal"><i class="fas fa-plus"></i> Add CPE </a>';
                                    // $Assets_data_list['action'] = '<a href="' . route("scans_assets.scans_assets_edit_modal", ["id" => $value->code, "code" => @$value->site_id, "page" => $menu]) . '" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">
                                    // <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                                    // </a>';
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
                                    $Assets_data_list['CPE_OtherCheck'] = $CPE_OtherCheck;
                                    array_push($Assets_list, $Assets_data_list);
            
                                }
                            }
            
                        }
            
                    }
                    $datacountAssets = @Assets::select('assets.id','assets_datas.data_type_id','assets_datas.value')
                    ->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')
                    ->whereIn('assets_datas.data_type_id',[5,6])
                    ->where('assets.status', 1)
                    ->where('assets.site_id', '=', $SiteSettingsfor -> id)
                    ->get();
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
                    $dataOut["countWindows"] = @CPE::select('id')->whereHas('get_assets', function($q) use ($SiteSettingsfor){
                        $q->where('os_type', 1)->whereIn('data_type_id', [5,6])->where('site_id', '=',$SiteSettingsfor -> id);
                    })->count();
                    // $dataOut["countLinux"] = @CPEData::whereRaw('LOWER(os_type) = ?', strtolower('LINUX'))->count();
                    $dataOut["countLinux"] = @CPE::select('id')->whereHas('get_assets', function($q) use ($SiteSettingsfor){
                        $q->where('os_type', 2)->whereIn('data_type_id', [5,6])->where('site_id', '=',$SiteSettingsfor -> id);
                    })->count();

                    $dataOut["countOther"] = @CPE::select('id')->whereHas('get_assets', function($q) use ($SiteSettingsfor) {
                        $q->where('site_id',$SiteSettingsfor->id)->whereIn('data_type_id', [5,6])->where(function ($query) {
                            $query->whereNotIn('os_type', [1,2])
                                ->orWhereNull('os_type')->orWhere('os_type','');
                        });
                    })->count();

                    $dataOut["data"] =  $Assets_list;

                    $data_transcation = json_encode($dataOut);
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

    public function asset_count_asset(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'assets'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $sitecode = $data['data']['sitecode'];

                    $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $sitecode)->first();
                    $dataOut["SiteSettingsfor"] = $SiteSettingsfor;
                    $dataOut["countAssets"] = @Assets::select('id')->where('site_id',$SiteSettingsfor->id)->whereHas('get_assets_data', function($q) use ($SiteSettingsfor) {
                        $q->whereIn('data_type_id', [5,6]);
                    })->count();
                    // $dataOut["countWindows"] = @CPEData::whereRaw('LOWER(os_type) = ?', strtolower('WINDOWS'))->count();
                    $dataOut["countWindows"] = @CPE::select('id')->whereHas('get_assets', function($q) use ($SiteSettingsfor) {
                        $q->where('os_type', 1)->where('site_id',$SiteSettingsfor->id)->whereIn('data_type_id', [5,6]);
                    })->count();
                    // $dataOut["countLinux"] = @CPEData::whereRaw('LOWER(os_type) = ?', strtolower('LINUX'))->count();
                    $dataOut["countLinux"] = @CPE::select('id')->whereHas('get_assets', function($q) use ($SiteSettingsfor) {
                        $q->where('os_type', 2)->where('site_id',$SiteSettingsfor->id)->whereIn('data_type_id', [5,6]);
                    })->count();

                    $dataOut["countOther"] = @CPE::select('id')->whereHas('get_assets', function($q) use ($SiteSettingsfor) {
                        $q->where('site_id',$SiteSettingsfor->id)->whereIn('data_type_id', [5,6])->where(function ($query) {
                            $query->whereNotIn('os_type', [1,2])
                                ->orWhereNull('os_type')->orWhere('os_type','');
                        });
                    })->count();

                    $data_transcation = json_encode($dataOut);
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

    public function get_selected_filter(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'assets'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $domaincode = $data['data']['domaincode'];
                    $sitecode = $data['data']['sitecode'];
                    $selectedGroup = $data['data']['selectedGroup'];

                    $site = null;
                    if($sitecode){
                        $site = SiteSettings::select('id')->where("code",$sitecode)->first();
                    }
                    $returnData = null;
                    if($selectedGroup=='domain'){
                        if($site){
                            if(isset($domaincode)){
                                $DomainFor = Domain::withTrashed()->where('code',$domaincode)->first();
                                $returnData = AssetsData::select('value AS val_select')->whereIn('data_type_id', [1,4])->where('status', 1)->where('site_id', $site->id)->where('domain_id', $DomainFor->id)->distinct()->get();
                            }else{
                                $returnData = AssetsData::select('value AS val_select')->whereIn('data_type_id', [1,4])->where('status', 1)->where('site_id', $site->id)->distinct()->get();
                            }
                        }else{
                            $returnData = AssetsData::select('value AS val_select')->whereIn('data_type_id', [1,4])->where('status', 1)->distinct()->get();
                        }
                    }else if($selectedGroup=='ip'){
                        if($site){
                            if(isset($domaincode)){
                                $DomainFor = Domain::withTrashed()->where('code',$domaincode)->first();
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

                    $dataOut = [
                        "selected" => $returnData,
                    ];

                    $data_transcation = json_encode($dataOut);
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
