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
use Modules\Social\Entities\Data_leak_social;
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
use Modules\SiteSettings\Entities\Activity;
use App\CredentialLeakRef;
use Illuminate\Support\Facades\Log;


class ApiDataLeakController extends ApiController
{
    protected function resolveTable(array $candidates): string
    {
        foreach ($candidates as $t) {
            if (\Illuminate\Support\Facades\Schema::hasTable($t)) return $t;
        }
        throw new \RuntimeException('Table not found. Tried: ' . implode(', ', $candidates));
    }

    public function data_leak_view(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $code = $data['data']['code'];
                
                $model1 = DataLeakSocialRef::select('data_leak_feed.feedcontent')
                ->join('data_leak_feed', 'data_leak_feed.id', '=','data_leak_socail_ref.data_leak_feed_id')
                ->where('data_leak_socail_ref.code',$code)->first();

                $response = [
                    "code" => $code,
                    "feedcontent" => $model1->feedcontent ?? '',
                ];

                $data_transcation = json_encode($response);
                $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
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

    
    public function data_leak_table(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $authenticatedSiteId = (int)($data['site']['data']['id'] ?? 0);
                $get_role = $data['data']['get_role_custom_first'] ?? [];
                $isSuperAdmin = (isset($get_role['superadmin']) && (int)$get_role['superadmin'] == 1);
                
                $site_ids = [];
                if ($isSuperAdmin && isset($get_role['site_id_arr'])) {
                    $site_ids = collect($get_role['site_id_arr'])
                        ->pluck('site_id')
                        ->filter()
                        ->values()
                        ->toArray();
                }

                    $startDate = $data['data']['startDate'] ?? null;
                    $endDate = $data['data']['endDate'] ?? null;
                    $search_val = $data['data']['search_val'] ?? null;
                    $site = $data['data']['site'] ?? null;
                    $get_role_custom_first = $data['data']['get_role_custom_first'] ?? null;
                    $keywords = $data['data']['keywords'] ?? null;
                    $type = $data['data']['type'] ?? null;
                    $isDateSearch = $data['data']['isDateSearch'] ?? null;
                    $check_type = $data['data']['check_type'] ?? null;
                    $source = $data['data']['source'] ?? null;
                    $click_type = $data['data']['click_type'] ?? null;
                    $click_type2 = $data['data']['click_type2'] ?? null;
                    $click_key = $data['data']['click_key'] ?? null;


                    $check_serverity = $data['data']['check_serverity'] ?? null;
                    $check_monitoring = $data['data']['check_monitoring'] ?? null;
                    $check_social = $data['data']['check_social'] ?? null;

                    $prefix = DB::getTablePrefix();
                    $type = $data['data']['type'] ?? null;
                    $modelClass = ($type === 'credential') ? CredentialLeakRef::class : DataLeakSocialRef::class;
                    
                    $refTable = ($type === 'credential') ? 'credential_leak_ref' : 'data_leak_socail_ref';
                    $feedTable = 'data_leak_feed';
                    $siteTable = 'site';

                    $refTableRaw = $prefix . $refTable;
                    $feedTableRaw = $prefix . $feedTable;
                    $siteTableRaw = $prefix . $siteTable;


                    if ($type === 'credential') {
                        $model = CredentialLeakRef::where('deleted_at', null)
                        ->with('get_site')
                        ->with('get_data_leak_feed_one');
                    } else {
                        $model = DataLeakSocialRef::where('deleted_at', null)
                        ->where('status',1)
                        ->whereHas('get_data_leak_feed_one', function ($query) {
                            $query->whereIn('feel_type', ['social', 'darkweb_public', 'surface_web', 'darkweb']);
                        })
                        ->with('get_site')
                        ->with('get_data_leak_feed_one');
                    }
        

                    if ($type === 'credential') {
                        $DataLeakSocialRef_data = CredentialLeakRef::whereNull("{$refTable}.deleted_at")
                        ->leftJoin($feedTable, "{$refTable}.data_leak_feed_id", '=', "{$feedTable}.id")
                        ->leftJoin($siteTable, "{$siteTable}.id", "{$refTable}.site_id")
                        ->select(
                            "{$refTable}.id as ref_id",
                            "{$refTable}.site_id as site_id",
                            "{$refTable}.serverity as ref_serverity",
                            "{$refTable}.status_monitoring as ref_status_monitoring",
                            "{$refTable}.status as ref_status",
                            "{$feedTable}.*",
                            "{$siteTable}.name as site_name",
                            "{$refTable}.code as code_data",
                            DB::raw("COALESCE({$feedTableRaw}.feedtimepost, {$feedTableRaw}.created_at) as ref_feedtimepost")
                        );
                    } else {
                        $DataLeakSocialRef_data = DataLeakSocialRef::whereNull("{$refTable}.deleted_at")
                        ->leftJoin($feedTable, "{$refTable}.data_leak_feed_id", '=', "{$feedTable}.id")
                        ->whereIn("{$feedTable}.feel_type", ['social', 'darkweb_public', 'surface_web', 'darkweb'])->leftJoin($siteTable, "{$siteTable}.id", "{$refTable}.site_id")
                        ->select(
                            "{$refTable}.id as ref_id",
                            "{$refTable}.site_id as site_id",
                            "{$refTable}.serverity as ref_serverity",
                            "{$refTable}.status_monitoring as ref_status_monitoring",
                            "{$refTable}.status as ref_status",
                            "{$feedTable}.*",
                            "{$siteTable}.name as site_name",
                            "{$refTable}.code as code_data",
                            DB::raw("COALESCE({$feedTableRaw}.feedtimepost, {$feedTableRaw}.created_at) as ref_feedtimepost")
                        );
                    }


                    if ($search_val == 1) {
            
                    

                        $DataLeakFeed_Data =   DataLeakFeed::where('deleted_at', null)->where('status','1')->whereIn('feel_type', ['social', 'darkweb_public', 'surface_web', 'darkweb']);
            
            
                        $isClientOrSiteClient = (
                            (isset($get_role['client']) && (int)$get_role['client'] == 1) ||
                            (isset($get_role['site_client']) && (int)$get_role['site_client'] == 1)
                        );

                        $selectedSiteId = null;
                        $siteInput = $data['data']['site_code'] ?? ($data['data']['site'] ?? null);
                        if (!empty($siteInput)) {
                            if (is_numeric($siteInput)) {
                                $selectedSiteId = (int) $siteInput;
                            } else {
                                if ($s = SiteSettings::where('code', $siteInput)->first()) {
                                    $selectedSiteId = (int) $s->id;
                                }
                            }
                        }

                        if ($selectedSiteId) {
                            $model->where('site_id', $selectedSiteId);
                            $DataLeakSocialRef_data->where("{$refTable}.site_id", $selectedSiteId);
                        } else if ($isSuperAdmin && !empty($site_ids)) {
                            $model->whereIn('site_id', $site_ids);
                            $DataLeakSocialRef_data->whereIn("{$refTable}.site_id", $site_ids);
                        } else if ($authenticatedSiteId > 0) {
                            $model->where('site_id', $authenticatedSiteId);
                            $DataLeakSocialRef_data->whereIn("{$refTable}.site_id", [$authenticatedSiteId]);
                        }

                        if ($isClientOrSiteClient) {
                            $model->where('status', 1);
                            $DataLeakSocialRef_data->where("{$refTable}.status", 1);
                        }

                        if ($keywords) {
                        
                         /*   $DataLeakFeed_data  = $DataLeakFeed_Data->get();
                            foreach ($DataLeakFeed_data as $value_data) {
                                  $value_data->feedcontent_decode = html_entity_decode($value_data->feedcontent);
                            }
                            $DataLeakFeed_data_id = array();
                            array_push($DataLeakFeed_data_id, 0);
                            foreach($DataLeakFeed_data as $a) {
                                if(strpos(strtoupper($a->feedcontent_decode), strtoupper($keywords)) !== false) {
                                    array_push($DataLeakFeed_data_id, $a->id);
                                } 
                            }
                            */

                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($keywords) {
                                $query->where('keyword', 'LIKE', '%' . $keywords . '%');
                                    //->orWhere('feedcontent', 'LIKE', '%' . $keywords . '%');
                                   
                            });
                            $DataLeakSocialRef_data->whereRaw("(LOWER({$feedTableRaw}.keyword) LIKE ? or LOWER(fnStripTags(entity_decode({$feedTableRaw}.feedcontent))) LIKE ? )", array([trim(strtolower('%' .$keywords.'%'))],[trim(strtolower('%' .$keywords.'%'))]));
                         //   $DataLeakSocialRef_data->orWhereIn('data_leak_feed.id', $DataLeakFeed_data_id);
                        }
            
                
            
                        if ($check_type) {
                        
                            $type = $check_type;
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($type) {
                                $query->where('feel_type', 'LIKE', '%' . $type . '%');
                            });
                            $DataLeakSocialRef_data->where("{$feedTable}.feel_type", 'LIKE', '%' . $type . '%');
                        }
                  
                        if ($check_social && $check_type =="social") {
              
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($check_social) {
                                if($check_social =="other"){
                                    $query->whereNotIn('keyword', ['Mobile','Facebook','Line','Twitter','Website']);
                                }else{
                                    $query->where('keyword', $check_social);
                                }
                              
                            });
                            if($check_social =="other"){
                                $DataLeakSocialRef_data->whereNotIn("{$feedTable}.keyword", ['Mobile','Facebook','Line','Twitter','Website']);
                            }else{
                                    $DataLeakSocialRef_data->where("{$feedTable}.keyword", $check_social);
            
                            }
            
                        }
                        if ($check_serverity) {
                            $model = $model->whereRaw("LOWER(serverity) = ?", [strtolower(trim($check_serverity))]);
                            $DataLeakSocialRef_data->whereRaw("LOWER({$refTableRaw}.serverity) = ?", [strtolower(trim($check_serverity))]);
                        }
            
                        if ($check_monitoring) {
                            if($check_monitoring == 'in_progress') {
                                $model->where(function($q) {
                                    $q->whereRaw("LOWER(status_monitoring) = 'in_progress'")
                                      ->orWhereRaw("status_monitoring IS NULL")
                                      ->orWhereRaw("status_monitoring = ''");
                                });
                                $DataLeakSocialRef_data->where(function($q) use ($refTable) {
                                    $q->whereRaw("LOWER({$refTable}.status_monitoring) = 'in_progress'")
                                      ->orWhereRaw("{$refTable}.status_monitoring IS NULL")
                                      ->orWhereRaw("{$refTable}.status_monitoring = ''");
                                });
                            } else {
                                $model->whereRaw("LOWER(status_monitoring) = ?", [strtolower(trim($check_monitoring))]);
                                $DataLeakSocialRef_data->whereRaw("LOWER({$refTableRaw}.status_monitoring) = ?", [strtolower(trim($check_monitoring))]);
                            }
                        }
                        // if ($source) {
            
                        //     $source = $source;
                        //     $model->whereHas('get_data_leak_feed_one', function ($query) use ($source) {
                        //         $query->where('sourceid', 'LIKE', '%' . $source . '%');
                        //     });
            
                        // }
            
                        if ($isDateSearch == 1) {
                            $date_start = $startDate;
                            $date_end = $endDate;
            
                            $date_start_explode = explode(" ", $date_start);
                            $date_start_date = @$date_start_explode[0];
                            $date_start_time = @$date_start_explode[1] . ' ' . @$date_start_explode[2];
            
                            $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
            
                            $date_start_time_time = date("H:i", strtotime($date_start_time));
                           // $date_start_datetime_format = $date_start_date_format . ' ' . $date_start_time_time . ':00';
                           $date_start_datetime_format = $date_start_date_format . ' '  . '00:00:01';
            
                            $date_end_explode = explode(" ", $date_end);
                            $date_end_date = @$date_end_explode[0];
                            $date_end_time = @$date_end_explode[1] . ' ' . @$date_end_explode[2];
                            // dd($date_end_time);
                            $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                            $date_end_time_time = date("H:i", strtotime($date_end_time));
                           // $date_end_datetime_format = $date_end_date_format . ' ' . $date_end_time_time . ':00';
                            $date_end_datetime_format = $date_end_date_format . ' '  . '23:59:59';
            
                            $source = $source;
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($date_start_date_format, $date_end_date_format) {
                                $query->whereBetween('feedtimepost', array($date_start_date_format, $date_end_date_format));
                            });
                            $DataLeakSocialRef_data->whereBetween("{$feedTable}.feedtimepost", array($date_start_datetime_format, $date_end_datetime_format));
                        }

                       // $model->orderBy('created_at','desc')->get();
                    } else {

                        // ---------- CLICK_TYPE / CLICK_TYPE2 ----------
                        $clickTypeVal = !empty($click_type2) ? $click_type2 : $click_type;

                        if (!empty($clickTypeVal)) {
                            $ct = strtolower(trim($clickTypeVal));

                            if (in_array($ct, ['in_progress', 'reported', 'close'])) {
                                $model->where('status_monitoring', 'LIKE', "%{$ct}%");
                                $DataLeakSocialRef_data->where("{$refTable}.status_monitoring", 'LIKE', "%{$ct}%");
                            } elseif ($ct === 'social' || $ct === 'surface_web') {
                                $model->whereHas('get_data_leak_feed_one', function($q) use ($prefix, $feedTable) {
                                    $q->whereRaw("LOWER(`{$prefix}{$feedTable}`.`feel_type`) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')");
                                });
                                $DataLeakSocialRef_data->whereRaw("LOWER(`{$prefix}{$feedTable}`.`feel_type`) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')");
                            } elseif ($ct === 'darkweb_public' || $ct === 'darkweb') { 
                                $model->whereHas('get_data_leak_feed_one', function($q) {
                                    $q->whereIn('feel_type', ['darkweb', 'darkweb_public', 'darkweb_private']);
                                });
                                $DataLeakSocialRef_data->whereIn("{$feedTable}.feel_type", ['darkweb', 'darkweb_public', 'darkweb_private']);
                            } elseif (in_array($ct, ['mobile', 'facebook', 'line', 'twitter', 'website'])) {
                                $model->whereHas('get_data_leak_feed_one', function($q) use ($ct) {
                                    $q->where('source_name', 'LIKE', "%{$ct}%");
                                });
                                $DataLeakSocialRef_data->where("{$feedTable}.source_name", 'LIKE', "%{$ct}%");
                            } elseif ($ct === 'other') {
                                $model->whereHas('get_data_leak_feed_one', function($q) {
                                    $q->where('source_name', 'NOT LIKE', '%mobile%')
                                      ->where('source_name', 'NOT LIKE', '%facebook%')
                                      ->where('source_name', 'NOT LIKE', '%line%')
                                      ->where('source_name', 'NOT LIKE', '%twitter%');
                                });
                                $DataLeakSocialRef_data->where("{$feedTable}.source_name", 'NOT LIKE', '%mobile%')
                                                        ->where("{$feedTable}.source_name", 'NOT LIKE', '%facebook%')
                                                        ->where("{$feedTable}.source_name", 'NOT LIKE', '%line%')
                                                        ->where("{$feedTable}.source_name", 'NOT LIKE', '%twitter%');
                            } elseif (in_array($ct, ['website_s', 'social_s', 'community_s'])) {
                                // Surface Web sub-categories using source_name
                                $model->whereHas('get_data_leak_feed_one', function($q) use ($ct, $prefix, $feedTable) {
                                    $q->whereRaw("LOWER(`{$prefix}{$feedTable}`.`feel_type`) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')");
                                    if ($ct === 'website_s') {
                                        $q->where(function($sq) use ($prefix, $feedTable) {
                                            $sq->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.com%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.net%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.org%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.co.th%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.io%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.th%']);
                                        })
                                        ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                        ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                                        ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%line%'])
                                        ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%telegram%'])
                                        ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%instagram%'])
                                        ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%tiktok%'])
                                        ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%youtube%']);
                                    } elseif ($ct === 'social_s') {
                                        $q->where(function($sq) use ($prefix, $feedTable) {
                                            $sq->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%twitter%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%facebook%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%line%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%telegram%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%instagram%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%tiktok%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%youtube%']);
                                        });
                                    } elseif ($ct === 'community_s') {
                                        $q->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%.com%'])
                                          ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%.net%'])
                                          ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%.org%'])
                                          ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%.th%'])
                                          ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                          ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                                          ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%line%'])
                                          ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                                    }
                                });

                                $DataLeakSocialRef_data->whereRaw("LOWER(`{$prefix}{$feedTable}`.`feel_type`) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')");
                                if ($ct === 'website_s') {
                                    $DataLeakSocialRef_data->where(function($q) use ($prefix, $feedTable) {
                                        $q->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.com%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.net%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.org%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.co.th%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.io%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.th%']);
                                    })
                                    ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                    ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                                    ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%line%'])
                                    ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%telegram%'])
                                    ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%instagram%'])
                                    ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%tiktok%'])
                                    ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%youtube%']);
                                } elseif ($ct === 'social_s') {
                                    $DataLeakSocialRef_data->where(function($q) use ($prefix, $feedTable) {
                                        $q->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%twitter%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%facebook%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%line%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%telegram%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%instagram%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%tiktok%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%youtube%']);
                                    });
                                } elseif ($ct === 'community_s') {
                                    $DataLeakSocialRef_data->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%.com%'])
                                      ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%.net%'])
                                      ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%.org%'])
                                      ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%.th%'])
                                      ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                      ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                                      ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%line%'])
                                      ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                                }
                            } elseif (in_array($ct, ['website_d', 'social_d', 'community_d'])) {
                                // Dark Web sub-categories using source_name
                                $model->whereHas('get_data_leak_feed_one', function($q) use ($ct, $prefix, $feedTable) {
                                    $q->whereIn('feel_type', ['darkweb', 'darkweb_public', 'darkweb_private']);
                                    if ($ct === 'website_d') {
                                        $q->where(function($sq) use ($prefix, $feedTable) {
                                            $sq->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.com%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.net%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.org%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.onion%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.io%']);
                                        })
                                        ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                        ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                                        ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%line%'])
                                        ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                                    } elseif ($ct === 'social_d') {
                                        $q->where(function($sq) use ($prefix, $feedTable) {
                                            $sq->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%twitter%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%facebook%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%line%'])
                                              ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%telegram%']);
                                        });
                                    } elseif ($ct === 'community_d') {
                                        $q->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%.com%'])
                                          ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%.net%'])
                                          ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%.org%'])
                                          ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%.onion%'])
                                          ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                          ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                                          ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%line%'])
                                          ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                                    }
                                });

                                $DataLeakSocialRef_data->whereIn("{$feedTable}.feel_type", ['darkweb', 'darkweb_public', 'darkweb_private']);
                                if ($ct === 'website_d') {
                                    $DataLeakSocialRef_data->where(function($q) use ($prefix, $feedTable) {
                                        $q->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.com%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.net%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.org%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.onion%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%.io%']);
                                    })
                                    ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                    ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                                    ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%line%'])
                                    ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                                } elseif ($ct === 'social_d') {
                                    $DataLeakSocialRef_data->where(function($q) use ($prefix, $feedTable) {
                                        $q->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%twitter%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%facebook%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%line%'])
                                          ->orWhereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) LIKE ?", ['%telegram%']);
                                    });
                                } elseif ($ct === 'community_d') {
                                    $DataLeakSocialRef_data->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%.com%'])
                                      ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%.net%'])
                                      ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%.org%'])
                                      ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%.onion%'])
                                      ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                      ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                                      ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%line%'])
                                      ->whereRaw("LOWER(`{$prefix}{$feedTable}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                                }
                            }
                        }

                        // click_type and click_key are now handled by the consolidated clickTypeVal logic above.

                        $selectedSiteId = null;
                        $siteInput = $data['data']['site_code'] ?? ($data['data']['site'] ?? null);
                        if (!empty($siteInput)) {
                            if (is_numeric($siteInput)) {
                                $selectedSiteId = (int) $siteInput;
                            } else {
                                if ($s = SiteSettings::where('code', $siteInput)->first()) {
                                    $selectedSiteId = (int) $s->id;
                                }
                            }
                        }

                        if ($selectedSiteId) {
                            $model->where('site_id', $selectedSiteId);
                            $DataLeakSocialRef_data->where("{$refTable}.site_id", $selectedSiteId);
                        } else {
                            $site_id_arr = @$get_role['site_id_arr'] ?? [];
                            $isSuperAdmin = (@$get_role['superadmin'] == 1);

                            if ($isSuperAdmin) {
                                // Superadmin sees everything if no site selected
                            } elseif (!empty($site_id_arr)) {
                                $site_ids = collect($site_id_arr)->pluck('site_id')->filter()->toArray();
                                if (!empty($site_ids)) {
                                    $model->whereIn('site_id', $site_ids);
                                    $DataLeakSocialRef_data->whereIn("{$refTable}.site_id", $site_ids);
                                }
                            } elseif ($authenticatedSiteId > 0) {
                                $model->where('site_id', $authenticatedSiteId);
                                $DataLeakSocialRef_data->where("{$refTable}.site_id", $authenticatedSiteId);
                            }
                        }

                       // $model->orderBy('created_at','desc')->get();
                    }


                    $order_column = $data['data']['order_column'] ?? ($data['data']['order'][0]['column'] ?? null);
                    $order_dir = $data['data']['order_dir'] ?? ($data['data']['order'][0]['dir'] ?? 'desc');
                 //   $res =  "";
                    if($order_column !== null){
                        $column_order =$order_column;
                        $column_dir =  $order_dir;
                        if($column_order == "9"){
                            $model->orderBy('status',$column_dir);
                            $DataLeakSocialRef_data->orderBy("{$refTable}.site_id", $column_dir)->orderBy("{$refTable}.id", $column_dir);
                        }
                        else if($column_order == "8"){
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($column_dir) {
                                      $query->orderBy('feedtimepost',$column_dir);
                            });
                            $DataLeakSocialRef_data->orderBy(DB::raw("COALESCE({$feedTableRaw}.feedtimepost, {$feedTableRaw}.created_at)"), $column_dir)->orderBy("{$refTable}.id", $column_dir);
                        }
                        else if($column_order == "7"){
            
                            $model->orderBy('status_monitoring',$column_dir);
                            $DataLeakSocialRef_data->orderBy("{$refTable}.status_monitoring", $column_dir)->orderBy("{$refTable}.id", $column_dir);
                        }
                        else if($column_order == "6"){
            
                            $model->orderBy('serverity',$column_dir);
                            $DataLeakSocialRef_data->orderBy("{$refTable}.serverity", $column_dir)->orderBy("{$refTable}.id", $column_dir);
                        }
                        else if($column_order == "5"){
            
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($column_dir) {
                                $query->orderBy('feedcontent',$column_dir);
                            });
                            $DataLeakSocialRef_data->orderBy("{$feedTable}.feedcontent", $column_dir)->orderBy("{$refTable}.id", $column_dir);
                        }
                        else if($column_order == "4"){
            
                            $model->orderBy('keyword',$column_dir);
                            $DataLeakSocialRef_data->orderBy("{$feedTable}.keyword", $column_dir)->orderBy("{$refTable}.id", $column_dir);
                        }
                        else if($column_order == "3"){
            
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($column_dir) {
                                $query->orderBy('source_name',$column_dir);
                            });
                            $DataLeakSocialRef_data->orderBy("{$feedTable}.source_name", $column_dir)->orderBy("{$refTable}.id", $column_dir);
                        }
                        else if($column_order == "2"){
            
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($column_dir) {
                                $query->orderBy('feel_type',$column_dir);
                            });
                            $DataLeakSocialRef_data->orderBy("{$refTable}.feel_type", $column_dir)->orderBy("{$refTable}.id", $column_dir);
                        }
                        else if($column_order == "1"){
            
                            $model->whereHas('get_site', function ($query) use ($column_dir) {
                                $query->orderBy('name',$column_dir);
                            });
                            $DataLeakSocialRef_data->orderBy('site.name', $column_dir)->orderBy("{$refTable}.id", $column_dir);
                       
                        }else{
                            $model->orderBy('created_at', 'desc');
                            $DataLeakSocialRef_data->orderBy("{$refTable}.created_at", $column_dir)->orderBy("{$refTable}.id", $column_dir);
                        }
                    }else{
                        $model->orderBy('created_at', 'desc');
                        $DataLeakSocialRef_data->orderBy("{$refTable}.created_at", $column_dir)->orderBy("{$refTable}.id", $column_dir);
                    }
                  //  $model->where('status_monitoring', 'in_progress');
                 //   $res = DataTables::of($model)->toJson(); 
                 //  $res =    $data['data']['order_column'];
                    $data_count = $DataLeakSocialRef_data->count();
                    \Log::info("DEBUG data_leak_table SQL", [
                        'sql' => $DataLeakSocialRef_data->toSql(),
                        'bindings' => $DataLeakSocialRef_data->getBindings(),
                        'count' => $data_count
                    ]);

                    $response = [
                        "draw" => (int)($data['data']['draw'] ?? 0),
                        "recordsFiltered" => $data_count,
                        "recordsTotal" => $data_count,
                        "data" => $DataLeakSocialRef_data->skip($data['data']['start'] ?? 0)->take($data['data']['length'] ?? 10)->get()
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
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

    public function data_leak_count_val(Request $request){
        \Log::info("DEBUG count_val reached", ['data' => $request->all()]);
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $authenticatedSiteId = (int)($data['site']['data']['id'] ?? 0);
                $get_role = $data['data']['get_role_custom_first'] ?? [];
                $isSuperAdmin = (isset($get_role['superadmin']) && (int)$get_role['superadmin'] == 1);
                $isClientOrSiteClient = (
                    (isset($get_role['client']) && (int)$get_role['client'] == 1) ||
                    (isset($get_role['site_client']) && (int)$get_role['site_client'] == 1)
                );
                
                $site_ids = [];
                if ($isSuperAdmin && isset($get_role['site_id_arr'])) {
                    $site_ids = collect($get_role['site_id_arr'])
                        ->pluck('site_id')
                        ->filter()
                        ->values()
                        ->toArray();
                }

                    $type = $data['data']['type'] ?? null;
                    $refTable = ($type === 'credential') ? 'credential_leak_ref' : 'data_leak_socail_ref';
                    $feedTable = 'data_leak_feed';
                    $siteTable = 'site';

                    // ---------- SELECTED SITE ----------
                    $selectedSiteId = null;
                    if (!empty($data['data']['site_code'])) {
                        if ($s = SiteSettings::where('code', $data['data']['site_code'])->first()) {
                            $selectedSiteId = (int) $s->id;
                        }
                    }

                    // ---------- QUERY BASE ----------
                    $qCredential = DB::table($refTable)
                        ->join($feedTable, "$refTable.data_leak_feed_id", '=', "$feedTable.id")
                        ->join($siteTable, "$siteTable.id", '=', "$refTable.site_id")
                        ->whereNull("$refTable.deleted_at")
                        ->when($type !== 'credential', fn($q) => $q->whereIn("$feedTable.feel_type", ['surface_web', 'darkweb', 'darkweb_public', 'social']))
                        ->when($selectedSiteId, fn($q) => $q->where("$refTable.site_id", $selectedSiteId)) // Specific site filter
                        ->when(!$selectedSiteId && $isSuperAdmin && !empty($site_ids), fn($q) => $q->whereIn("$refTable.site_id", $site_ids))
                        ->when(!$selectedSiteId && !$isSuperAdmin && $authenticatedSiteId > 0, fn($q) => $q->where("$refTable.site_id", $authenticatedSiteId))
                        ->when($isClientOrSiteClient, fn($q) => $q->where("$feedTable.status", 1))
                        ->select(
                            "$refTable.id as ref_id",
                            "$refTable.site_id",
                            "$refTable.status_monitoring",
                            "$feedTable.keyword",
                            "$feedTable.source_name",
                            "$feedTable.feel_type",
                            "$feedTable.feedtimepost"
                        );


                    // ---------- DATE FILTER ----------
                    if (isset($data['data']['isDateSearch']) && (int)$data['data']['isDateSearch'] === 1 && @$data['data']['startDate'] && @$data['data']['endDate']) {
                        $start = Carbon::parse($data['data']['startDate'])->startOfDay()->toDateTimeString();
                        $end   = Carbon::parse($data['data']['endDate'])->endOfDay()->toDateTimeString();
                        $feedAlias = DB::getTablePrefix() . $feedTable;

                        $qCredential->whereRaw("
                            `$feedAlias`.`feedtimepost` IS NOT NULL
                            AND CAST(`$feedAlias`.`feedtimepost` AS DATETIME) BETWEEN ? AND ?
                        ", [$start, $end]);
                    }

                    // ---------- CLICK_TYPE / CLICK_TYPE2 ----------
                    $click_type  = $data['data']['click_type'] ?? null;
                    $click_type2 = $data['data']['click_type2'] ?? null;
                    $clickTypeVal = !empty($click_type2) ? $click_type2 : $click_type;

                    if (!empty($clickTypeVal)) {
                        $ct = strtolower(trim($clickTypeVal));
                        $f_raw = DB::getTablePrefix() . $feedTable;
                        $r_raw = DB::getTablePrefix() . $refTable;

                        if (in_array($ct, ['in_progress', 'reported', 'close'])) {
                            if ($ct === 'in_progress') {
                                $qCredential->where(function($q) use ($r_raw) {
                                    $q->whereRaw("LOWER($r_raw.status_monitoring) = 'in_progress'")
                                      ->orWhereRaw("$r_raw.status_monitoring IS NULL")
                                      ->orWhereRaw("$r_raw.status_monitoring = ''");
                                });
                            } else {
                                $qCredential->whereRaw("LOWER($r_raw.status_monitoring) = ?", [$ct]);
                            }
                        } elseif ($ct === 'social' || $ct === 'surface_web') {
                            $qCredential->whereRaw("LOWER(`{$f_raw}`.`feel_type`) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')");
                        } elseif ($ct === 'darkweb_public' || $ct === 'darkweb') {
                            $qCredential->whereIn("$feedTable.feel_type", ['darkweb', 'darkweb_public', 'darkweb_private']);
                        } elseif (in_array($ct, ['website_s', 'social_s', 'community_s'])) {
                            $qCredential->whereRaw("LOWER(`{$f_raw}`.`feel_type`) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')");
                            if ($ct === 'website_s') {
                                $qCredential->where(function($sq) use ($f_raw) {
                                    $sq->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.com%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.net%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.org%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.co.th%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.io%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.th%']);
                                })
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%line%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                            } elseif ($ct === 'social_s') {
                                $qCredential->where(function($sq) use ($f_raw) {
                                    $sq->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%twitter%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%facebook%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%line%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%telegram%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%instagram%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%tiktok%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%youtube%']);
                                });
                            } elseif ($ct === 'community_s') {
                                $qCredential->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.com%'])
                                  ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.net%'])
                                  ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.org%'])
                                  ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.th%'])
                                  ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                  ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                                  ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%line%'])
                                  ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                            }
                        } elseif (in_array($ct, ['website_d', 'social_d', 'community_d'])) {
                            $qCredential->whereIn("$feedTable.feel_type", ['darkweb', 'darkweb_public', 'darkweb_private']);
                            if ($ct === 'website_d') {
                                $qCredential->where(function($sq) use ($f_raw) {
                                    $sq->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.com%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.net%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.org%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.onion%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.io%']);
                                })
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%line%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                            } elseif ($ct === 'social_d') {
                                $qCredential->where(function($sq) use ($f_raw) {
                                    $sq->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%twitter%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%facebook%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%line%'])
                                      ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%telegram%']);
                                });
                            } elseif ($ct === 'community_d') {
                                $qCredential->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.com%'])
                                  ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.net%'])
                                  ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.org%'])
                                  ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.onion%'])
                                  ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                  ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                                  ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%line%'])
                                  ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                            }
                        }
                    }

                    // ---------- ADDITIONAL FILTERS (Matching Original SocialController) ----------
                    if (!empty($data['data']['check_type'])) {
                        $checkType = strtolower(trim($data['data']['check_type']));
                        $f_raw = DB::getTablePrefix() . $feedTable;
                        if ($checkType === 'surface_web') {
                            $qCredential->whereRaw("LOWER(`{$f_raw}`.feel_type) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')");
                        } elseif ($checkType === 'darkweb') {
                            $qCredential->whereRaw("LOWER(`{$f_raw}`.feel_type) IN ('darkweb', 'darkweb_public', 'darkweb_private')");
                        }
                    }

                    if (!empty($data['data']['check_social'])) {
                        $checkSocial = strtolower(trim($data['data']['check_social']));
                        $f_raw = DB::getTablePrefix() . $feedTable;
                        if ($checkSocial === 'website') {
                            $qCredential->whereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) LIKE ?", ["website%"]);
                        } elseif ($checkSocial === 'social') {
                            $qCredential->where(function($query) use ($f_raw) {
                                $query->whereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) LIKE ?", ["facebook%"])
                                      ->orWhereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) LIKE ?", ["line%"])
                                      ->orWhereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) LIKE ?", ["twitter%"])
                                      ->orWhereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) LIKE ?", ["fanpage%"]);
                            });
                        } elseif ($checkSocial === 'community') {
                            $qCredential->whereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) NOT LIKE ?", ["website%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) NOT LIKE ?", ["facebook%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) NOT LIKE ?", ["line%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) NOT LIKE ?", ["twitter%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) NOT LIKE ?", ["fanpage%"]);
                        }
                    }

                    if (!empty($data['data']['check_darkweb'])) {
                        $checkDarkweb = strtolower(trim($data['data']['check_darkweb']));
                        $f_raw = DB::getTablePrefix() . $feedTable;
                        if ($checkDarkweb === 'website') {
                            $qCredential->whereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) LIKE ?", ["website%"]);
                        } elseif ($checkDarkweb === 'social') {
                            $qCredential->where(function($query) use ($f_raw) {
                                $query->whereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) LIKE ?", ["facebook%"])
                                      ->orWhereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) LIKE ?", ["line%"])
                                      ->orWhereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) LIKE ?", ["twitter%"])
                                      ->orWhereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) LIKE ?", ["telegram%"])
                                      ->orWhereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) LIKE ?", ["social%"]);
                            });
                        } elseif ($checkDarkweb === 'community') {
                            $qCredential->whereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) NOT LIKE ?", ["website%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) NOT LIKE ?", ["facebook%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) NOT LIKE ?", ["line%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) NOT LIKE ?", ["twitter%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) NOT LIKE ?", ["telegram%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.keyword)) NOT LIKE ?", ["social%"]);
                        }
                    }

                    if (!empty($data['data']['check_serverity'])) {
                        $r_raw = DB::getTablePrefix() . $refTable;
                        $qCredential->whereRaw("LOWER($r_raw.serverity) = ?", [strtolower(trim($data['data']['check_serverity']))]);
                    }

                    if (!empty($data['data']['check_monitoring'])) {
                        $checkMonitoring = strtolower(trim($data['data']['check_monitoring']));
                        $r_raw = DB::getTablePrefix() . $refTable;
                        if ($checkMonitoring === 'in_progress') {
                            $qCredential->where(function($query) use ($r_raw) {
                                $query->whereRaw("LOWER($r_raw.status_monitoring) = 'in_progress'")
                                      ->orWhereRaw("$r_raw.status_monitoring IS NULL")
                                      ->orWhereRaw("$r_raw.status_monitoring = ''");
                            });
                        } else {
                            $qCredential->whereRaw("LOWER($r_raw.status_monitoring) = ?", [$checkMonitoring]);
                        }
                    }

                    // ---------- KEYWORDS FILTER ----------
                    if (!empty($data['data']['keywords'])) {
                        $keywords = $data['data']['keywords'];
                        $feedAlias = DB::getTablePrefix() . $feedTable;
                        $qCredential->where(function($q) use ($feedAlias, $keywords) {
                            $q->whereRaw("LOWER(`{$feedAlias}`.`keyword`) LIKE ?", ['%' . strtolower($keywords) . '%'])
                            ->orWhereRaw("LOWER(`{$feedAlias}`.`feedcontent`) LIKE ?", ['%' . strtolower($keywords) . '%']);
                        });
                    }

                    $qCredential->where("$refTable.status", 1);

                    // ---------- QUERY WRAPPER ----------
                    $base = DB::query()->fromSub($qCredential, 'u');

                    // ---------- SURFACE WEB COUNTS ----------
                    $baseSurface = (clone $base)->whereIn('feel_type', ['surface_web', 'social']);
                    
                    $icon_website_s = (clone $baseSurface)->where(function($q) {
                        $q->whereRaw("LOWER(source_name) LIKE ?", ['%.com%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.net%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.org%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.co.th%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.io%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.th%']);
                    })
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%twitter%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%facebook%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%line%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%telegram%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%instagram%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%tiktok%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%youtube%'])
                    ->count();
                    
                    $icon_social_s = (clone $baseSurface)->where(function($q) {
                        $q->whereRaw("LOWER(source_name) LIKE ?", ['%twitter%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%facebook%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%line%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%telegram%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%instagram%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%tiktok%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%youtube%']);
                    })->count();
                    
                    $icon_community_s = (clone $baseSurface)
                        ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.com%'])
                        ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.net%'])
                        ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.org%'])
                        ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.th%'])
                        ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%twitter%'])
                        ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%facebook%'])
                        ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%line%'])
                        ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%telegram%'])
                        ->count();
                    $surface_web_total = (clone $base)->whereIn('feel_type', ['surface_web', 'social'])->count();

                    // ---------- DARK WEB COUNTS ----------
                    $baseDarkweb = (clone $base)->whereIn('feel_type', ['darkweb', 'darkweb_public']);
                    
                    $icon_website_d = (clone $baseDarkweb)->where(function($q) {
                        $q->whereRaw("LOWER(source_name) LIKE ?", ['%.com%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.net%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.org%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.onion%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.io%']);
                    })
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%twitter%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%facebook%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%line%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%telegram%'])
                    ->count();
                    
                    $icon_social_d = (clone $baseDarkweb)->where(function($q) {
                        $q->whereRaw("LOWER(source_name) LIKE ?", ['%twitter%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%facebook%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%line%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%telegram%']);
                    })->count();
                    
                    $icon_community_d = (clone $baseDarkweb)
                        ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.com%'])
                        ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.net%'])
                        ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.onion%'])
                        ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%twitter%'])
                        ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%facebook%'])
                        ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%telegram%'])
                        ->count();
                    $darkweb_total = (clone $base)->whereIn('feel_type', ['darkweb', 'darkweb_public'])->count();

                    // ---------- STATUS COUNTS ----------
                    $number_in_progress = (clone $base)->where(function($q) {
                        $q->where('status_monitoring', 'LIKE', '%in_progress%')
                        ->orWhereNull('status_monitoring')
                        ->orWhere('status_monitoring', '');
                    })->count();
                    $number_reported    = (clone $base)->where('status_monitoring', 'LIKE', '%reported%')->count();
                    $number_close       = (clone $base)->where('status_monitoring', 'LIKE', '%close%')->count();
                    $response = [
                        "credential" => (clone $base)->count(),
                        "social"     => (clone $base)->whereIn('feel_type', ['surface_web', 'social'])->count(),
                        "darkweb"    => (clone $base)->whereIn('feel_type', ['darkweb', 'darkweb_public'])->count(),
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
    } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e -> getMessage(),
            );
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


    public function data_leak_delete(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                     $code = $data['data']['code'];
                    
                    // $model1 = DataLeakSocialRef::select('data_leak_feed.feedcontent')
                    // ->join('data_leak_feed', 'data_leak_feed.id', '=','data_leak_socail_ref.data_leak_feed_id')
                    // ->where('data_leak_socail_ref.code',$code)->first();

                    $response = [
                        "code" => $code,
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
            return response()->json($response);
        }
    }

    public function data_leak_delete_select(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code = $data['data']['code'];
                    
                    $DataLeakSocialRef = DataLeakSocialRef::where('code', $code)->first();
                    if($DataLeakSocialRef){
                        $DataLeakSocialRef->delete();
                    }

                    $response = [
                        "code" => $code,
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
            return response()->json($response);
        }
    }

    public function data_leak_delete_change(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code = $data['data']['code'];
                    foreach($code as $id){
                        $DataLeakSocialRef = DataLeakSocialRef::where('id', $id)->delete();
                    }


                    $response = [
                        "code" => $code,

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
            return response()->json($response);
        }
    }

    public function count_keyword(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $authenticatedSiteId = (int)($data['site']['data']['id'] ?? 0);
                $get_role = $data['data']['get_role_custom_first'] ?? [];
                $isSuperAdmin = (isset($get_role['superadmin']) && (int)$get_role['superadmin'] == 1);
                $isClientOrSiteClient = (
                    (isset($get_role['client']) && (int)$get_role['client'] == 1) ||
                    (isset($get_role['site_client']) && (int)$get_role['site_client'] == 1)
                );

                $site_ids = [];
                if ($isSuperAdmin && isset($get_role['site_id_arr'])) {
                    $site_ids = collect($get_role['site_id_arr'])
                        ->pluck('site_id')
                        ->filter()
                        ->values()
                        ->toArray();
                }

                $type = $data['data']['type'] ?? null;
                $modelClass = ($type === 'credential') ? CredentialLeakRef::class : DataLeakSocialRef::class;

                $model = $modelClass::select('keyword', DB::raw('count(*) as count_keyword'))
                    ->whereNull('deleted_at');

                if ($type !== 'credential') {
                    $model->whereIn('feel_type', ['social', 'darkweb_public', 'surface_web', 'darkweb']);
                }

                // Filter by site: if superadmin has list, use it. Otherwise use authenticated site.
                if ($isSuperAdmin && !empty($site_ids)) {
                    $model->whereIn('site_id', $site_ids);
                } else if ($authenticatedSiteId > 0) {
                    $model->where('site_id', $authenticatedSiteId);
                }

                if ($isClientOrSiteClient) {
                    $model->where('status', 1);
                }

                $model = $model->groupBy('keyword')->get()->toArray();
                    

                    $response = [
                        "model" => $model,

                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
    } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }

    public function count_icon(Request $request){
        \Log::info("DEBUG count_icon reached", ['data' => $request->all()]);
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $authenticatedSiteId = (int)($data['site']['data']['id'] ?? 0);
                $get_role = $data['data']['get_role_custom_first'] ?? [];
                $isSuperAdmin = (isset($get_role['superadmin']) && (int)$get_role['superadmin'] == 1);
                $isClientOrSiteClient = (
                    (isset($get_role['client']) && (int)$get_role['client'] == 1) ||
                    (isset($get_role['site_client']) && (int)$get_role['site_client'] == 1)
                );
                
                $site_ids = [];
                if ($isSuperAdmin && isset($get_role['site_id_arr'])) {
                    $site_ids = collect($get_role['site_id_arr'])
                        ->pluck('site_id')
                        ->filter()
                        ->values()
                        ->toArray();
                }

                $type = $data['data']['type'] ?? null;
                $refTable = ($type === 'credential') ? 'credential_leak_ref' : 'data_leak_socail_ref';
                $feedTable = 'data_leak_feed';
                $siteTable = 'site';

                // ---------- SELECTED SITE ----------
                $selectedSiteId = null;
                if (!empty($data['data']['site_code'])) {
                    if ($s = SiteSettings::where('code', $data['data']['site_code'])->first()) {
                        $selectedSiteId = (int) $s->id;
                    }
                }

                // ---------- QUERY BASE ----------
                $qCredential = DB::table($refTable)
                    ->join($feedTable, "$refTable.data_leak_feed_id", '=', "$feedTable.id")
                    ->join($siteTable, "$siteTable.id", '=', "$refTable.site_id")
                    ->whereNull("$refTable.deleted_at")
                    ->when($type !== 'credential', fn($q) => $q->whereIn("$feedTable.feel_type", ['surface_web', 'darkweb', 'darkweb_public', 'social']))
                    ->when($selectedSiteId, fn($q) => $q->where("$refTable.site_id", $selectedSiteId)) // Specific site filter
                    ->when(!$selectedSiteId && $isSuperAdmin && !empty($site_ids), fn($q) => $q->whereIn("$refTable.site_id", $site_ids))
                    ->when(!$selectedSiteId && !$isSuperAdmin && $authenticatedSiteId > 0, fn($q) => $q->where("$refTable.site_id", $authenticatedSiteId))
                    ->when($isClientOrSiteClient, fn($q) => $q->where("$feedTable.status", 1))
                    ->select(
                        "$refTable.id as ref_id",
                        "$refTable.site_id",
                        "$refTable.status_monitoring",
                        "$feedTable.keyword",
                        "$feedTable.source_name",
                        "$feedTable.feel_type",
                        "$feedTable.feedtimepost"
                    );


                    // ---------- DATE FILTER ----------
                    if (isset($data['data']['isDateSearch']) && (int)$data['data']['isDateSearch'] === 1 && @$data['data']['startDate'] && @$data['data']['endDate']) {
                        $start = Carbon::parse($data['data']['startDate'])->startOfDay()->toDateTimeString();
                        $end   = Carbon::parse($data['data']['endDate'])->endOfDay()->toDateTimeString();
                        $feedAlias = DB::getTablePrefix() . $feedTable;

                        $qCredential->whereRaw("
                            `$feedAlias`.`feedtimepost` IS NOT NULL
                            AND CAST(`$feedAlias`.`feedtimepost` AS DATETIME) BETWEEN ? AND ?
                        ", [$start, $end]);
                    }

                    // ---------- ADDITIONAL FILTERS ----------
                    if (!empty($data['data']['check_type'])) {
                        $qCredential->where("$feedTable.feel_type", $data['data']['check_type']);
                    }
                    if (!empty($data['data']['check_social'])) {
                        $checkSocial = strtolower(trim($data['data']['check_social']));
                        $f_raw = DB::getTablePrefix() . $feedTable;
                        if ($checkSocial === 'website') {
                            $qCredential->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["website%"]);
                        } elseif ($checkSocial === 'social') {
                            $qCredential->where(function($query) use ($f_raw) {
                                $query->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["facebook%"])
                                      ->orWhereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["line%"])
                                      ->orWhereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["twitter%"])
                                      ->orWhereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["fanpage%"]);
                            });
                        } elseif ($checkSocial === 'community') {
                            $qCredential->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["website%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["facebook%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["line%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["twitter%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["fanpage%"]);
                        } else {
                            $qCredential->where("$f_raw.keyword", 'LIKE', $data['data']['check_social'] . '%');
                        }
                    }
                    if (!empty($data['data']['check_darkweb'])) {
                        $checkDarkweb = strtolower(trim($data['data']['check_darkweb']));
                        $f_raw = DB::getTablePrefix() . $feedTable;
                        if ($checkDarkweb === 'website') {
                            $qCredential->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["website%"]);
                        } elseif ($checkDarkweb === 'social') {
                            $qCredential->where(function($query) use ($f_raw) {
                                $query->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["facebook%"])
                                      ->orWhereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["line%"])
                                      ->orWhereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["twitter%"])
                                      ->orWhereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["telegram%"])
                                      ->orWhereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["social%"]);
                            });
                        } elseif ($checkDarkweb === 'community') {
                            $qCredential->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["website%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["facebook%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["line%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["twitter%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["telegram%"])
                              ->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["social%"]);
                        } else {
                            $qCredential->where("{$f_raw}.keyword", 'LIKE', $data['data']['check_darkweb'] . '%');
                        }
                    }

                    if (!empty($data['data']['check_serverity'])) {
                        $qCredential->whereRaw("LOWER($refTable.serverity) = ?", [strtolower(trim($data['data']['check_serverity']))]);
                    }

                    if (!empty($data['data']['check_monitoring'])) {
                        $checkMonitoring = strtolower(trim($data['data']['check_monitoring']));
                        if ($checkMonitoring === 'in_progress') {
                            $qCredential->where(function($query) use ($refTable) {
                                $query->whereRaw("LOWER($refTable.status_monitoring) = 'in_progress'")
                                      ->orWhereRaw("$refTable.status_monitoring IS NULL")
                                      ->orWhereRaw("$refTable.status_monitoring = ''");
                            });
                        } else {
                            $qCredential->whereRaw("LOWER($refTable.status_monitoring) = ?", [$checkMonitoring]);
                        }
                    }

                    // ---------- KEYWORDS FILTER ----------
                    if (!empty($data['data']['keywords'])) {
                        $keywords = $data['data']['keywords'];
                        $feedAlias = DB::getTablePrefix() . $feedTable;
                        $qCredential->where(function($q) use ($feedAlias, $keywords) {
                            $q->whereRaw("LOWER(`{$feedAlias}`.`keyword`) LIKE ?", ['%' . strtolower($keywords) . '%'])
                            ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%' . strtolower($keywords) . '%'])
                            ->orWhereRaw("LOWER(`{$feedAlias}`.`feedcontent`) LIKE ?", ['%' . strtolower($keywords) . '%']);
                        });
                    }

                    // ---------- ADDITIONAL FILTERS (Matching Original SocialController) ----------
                    if (!empty($data['data']['check_type'])) {
                        $checkType = strtolower(trim($data['data']['check_type']));
                        if ($checkType === 'surface_web') {
                            $qCredential->whereRaw("LOWER($feedTable.feel_type) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')");
                        } elseif ($checkType === 'darkweb') {
                            $qCredential->whereRaw("LOWER($feedTable.feel_type) IN ('darkweb', 'darkweb_public', 'darkweb_private')");
                        }
                    }

                    if (!empty($data['data']['check_social'])) {
                        $checkSocial = strtolower(trim($data['data']['check_social']));
                        if ($checkSocial === 'website') {
                            $qCredential->whereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["website%"]);
                        } elseif ($checkSocial === 'social') {
                            $qCredential->where(function($query) use ($feedTable) {
                                $query->whereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["facebook%"])
                                      ->orWhereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["line%"])
                                      ->orWhereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["twitter%"])
                                      ->orWhereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["fanpage%"]);
                            });
                        } elseif ($checkSocial === 'community') {
                            $qCredential->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["website%"])
                              ->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["facebook%"])
                              ->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["line%"])
                              ->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["twitter%"])
                              ->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["fanpage%"]);
                        }
                    }

                    if (!empty($data['data']['check_darkweb'])) {
                        $checkDarkweb = strtolower(trim($data['data']['check_darkweb']));
                        if ($checkDarkweb === 'website') {
                            $qCredential->whereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["website%"]);
                        } elseif ($checkDarkweb === 'social') {
                            $qCredential->where(function($query) use ($feedTable) {
                                $query->whereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["facebook%"])
                                      ->orWhereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["line%"])
                                      ->orWhereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["twitter%"])
                                      ->orWhereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["telegram%"])
                                      ->orWhereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["social%"]);
                            });
                        } elseif ($checkDarkweb === 'community') {
                            $qCredential->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["website%"])
                              ->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["facebook%"])
                              ->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["line%"])
                              ->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["twitter%"])
                              ->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["telegram%"])
                              ->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["social%"]);
                        }
                    }

                    if (!empty($data['data']['check_serverity'])) {
                        $qCredential->whereRaw("LOWER($refTable.serverity) = ?", [strtolower(trim($data['data']['check_serverity']))]);
                    }

                    if (!empty($data['data']['check_monitoring'])) {
                        $checkMonitoring = strtolower(trim($data['data']['check_monitoring']));
                        if ($checkMonitoring === 'in_progress') {
                            $qCredential->where(function($query) use ($refTable) {
                                $query->whereRaw("LOWER($refTable.status_monitoring) = 'in_progress'")
                                      ->orWhereRaw("$refTable.status_monitoring IS NULL")
                                      ->orWhereRaw("$refTable.status_monitoring = ''");
                            });
                        } else {
                            $qCredential->whereRaw("LOWER($refTable.status_monitoring) = ?", [$checkMonitoring]);
                        }
                    }

                    $qCredential->where("$refTable.status", 1); 
                    
                    // ---------- QUERY WRAPPER ----------
                    $base = DB::query()->fromSub($qCredential, 'u');

                    \Log::info("DEBUG count_icon SQL FINAL", ["sql" => $qCredential->toSql(), "bindings" => $qCredential->getBindings()]);

                    // ---------- ICON COUNTS (Matching Original Logic) ----------
                    $icon_mobile   = (clone $base)->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['mobile%'])->count();
                    $icon_facebook = (clone $base)->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['facebook%'])->count();
                    $icon_line     = (clone $base)->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['line%'])->count();
                    $icon_twitter  = (clone $base)->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['twitter%'])->count();
                    $icon_website  = (clone $base)->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['website%'])->count();
                    $icon_other    = (clone $base)->whereRaw('TRIM(LOWER(keyword)) NOT IN (?, ?, ?, ?, ?)', ['mobile','facebook','line','twitter','website'])->count();

                    // ---------- SURFACE WEB COUNTS ----------
                    $baseSurface = (clone $base)->whereRaw("LOWER(feel_type) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')");
                    
                    $icon_website_s   = (clone $baseSurface)->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['website%'])->count();
                    $icon_social_s    = (clone $baseSurface)->where(function($q) {
                        $q->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['facebook%'])
                          ->orWhereRaw('TRIM(LOWER(keyword)) LIKE ?', ['line%'])
                          ->orWhereRaw('TRIM(LOWER(keyword)) LIKE ?', ['twitter%']);
                    })->count();
                    $icon_community_s = (clone $baseSurface)
                        ->whereRaw('TRIM(LOWER(keyword)) NOT LIKE ?', ['website%'])
                        ->whereRaw('TRIM(LOWER(keyword)) NOT LIKE ?', ['facebook%'])
                        ->whereRaw('TRIM(LOWER(keyword)) NOT LIKE ?', ['line%'])
                        ->whereRaw('TRIM(LOWER(keyword)) NOT LIKE ?', ['twitter%'])
                        ->count();
                    $surface_web_total = (clone $baseSurface)->count();

                    // ---------- DARK WEB COUNTS ----------
                    $baseDarkweb = (clone $base)->whereRaw("LOWER(feel_type) IN ('darkweb', 'darkweb_public', 'darkweb_private')");
                    
                    $icon_website_d   = (clone $baseDarkweb)->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['website%'])->count();
                    $icon_social_d    = (clone $baseDarkweb)->where(function($q) {
                        $q->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['facebook%'])
                          ->orWhereRaw('TRIM(LOWER(keyword)) LIKE ?', ['line%'])
                          ->orWhereRaw('TRIM(LOWER(keyword)) LIKE ?', ['twitter%']);
                    })->count();
                    $icon_community_d = (clone $baseDarkweb)
                        ->whereRaw('TRIM(LOWER(keyword)) NOT LIKE ?', ['website%'])
                        ->whereRaw('TRIM(LOWER(keyword)) NOT LIKE ?', ['facebook%'])
                        ->whereRaw('TRIM(LOWER(keyword)) NOT LIKE ?', ['line%'])
                        ->whereRaw('TRIM(LOWER(keyword)) NOT LIKE ?', ['twitter%'])
                        ->count();
                    $darkweb_total = (clone $baseDarkweb)->count();

                    // ---------- STATUS COUNTS ----------
                    $number_in_progress = (clone $base)->where(function($q) {
                        $q->whereRaw("LOWER(status_monitoring) = 'in_progress'")
                          ->orWhereRaw("status_monitoring IS NULL")
                          ->orWhereRaw("status_monitoring = ''");
                    })->count();
                    $number_reported    = (clone $base)->whereRaw('LOWER(status_monitoring) = ?', ['reported'])->count();
                    $number_close       = (clone $base)->whereRaw('LOWER(status_monitoring) = ?', ['close'])->count();

                    $response = [
                        "icon_mobile"        => $icon_mobile,
                        "icon_facebook"      => $icon_facebook,
                        "icon_line"          => $icon_line,
                        "icon_twitter"       => $icon_twitter,
                        "icon_website"       => $icon_website,
                        "icon_other"         => $icon_other,

                        "icon_website_s"     => $icon_website_s,
                        "icon_social_s"      => $icon_social_s,
                        "icon_community_s"   => $icon_community_s,
                        "surface_web_total"  => $surface_web_total,
                        "icon_website_d"     => $icon_website_d,
                        "icon_social_d"      => $icon_social_d,
                        "icon_community_d"   => $icon_community_d,
                        "darkweb_total"      => $darkweb_total,
                        "number_in_progress" => $number_in_progress,
                        "number_reported"    => $number_reported,
                        "number_close"       => $number_close,
                    ];

                    $data_transcation = json_encode($response);
                    $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
                }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }

    public function getDataLeakSocial(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $site_id_arr = @$get_role_custom_first['site_id_arr'];

                    // $site_code = $data['data']['site_code'];
                    // if($site_code) {
                    //     $SiteSettings = SiteSettings::where('code',$site_code)->first();
                    // }

                    if(@$get_role_custom_first['superadmin'] == 1) {
                    } else {
                            $Data_leak_social = Data_leak_social::where('deleted_at', null)->where('status',1)->get();
                           
                    }

                    $response = [
                        "Data_leak_social" => $Data_leak_social,
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
            return response()->json($response);
        }
    }


    public function activity_dataleak_modal(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $DataLeakSocialRef_code = $data['data']['DataLeakSocialRef_code'];
                    $site = $data['data']['site'];
                  

                    $user = User::where('id', $data['data']['user_id'])->first();

                    // $site_id = '';
            
                    // if($site_code) {
                    //     $site_id_m = SiteSettings::where('code',$site_code)->first();
                    //     $site_id = @$site_id_m->id;
                    // }


                    $DataLeakSocialRefs = DataLeakSocialRef::where('code',$DataLeakSocialRef_code)->first();
                    $ActivityHistory = Activity::select('activity.*','users.name as users_name')->where('activity.data_leak_socail_ref_id',$DataLeakSocialRefs->id)->whereNull('activity.deleted_at')->leftJoin('users', 'activity.user_id', '=', 'users.id')->orderBy('created_at','desc')->get();
                    // $DataLeakFeed = DataLeakFeed::where('id',$DataLeakSocialRefs->data_leak_feed_id)->first();
                    $response['DataLeakSocialRefs'] = $DataLeakSocialRefs;
                    $response['site'] = @$site;
                    $response['ActivityHistory'] = $ActivityHistory;
               
            

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

    public function activity_history_reload(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $DataLeakSocialRef_code = $data['data']['DataLeakSocialRef_code'];
                    // $site = $data['data']['site'];
                  

                    $user = User::where('id', $data['data']['user_id'])->first();

                    // $site_id = '';
            
                    // if($site_code) {
                    //     $site_id_m = SiteSettings::where('code',$site_code)->first();
                    //     $site_id = @$site_id_m->id;
                    // }


                    $DataLeakSocialRefs = DataLeakSocialRef::where('id',$DataLeakSocialRef_code)->first();
                    $ActivityHistory = Activity::select('activity.*','users.name as users_name')->where('activity.data_leak_socail_ref_id',$DataLeakSocialRefs->id)->whereNull('activity.deleted_at')->leftJoin('users', 'activity.user_id', '=', 'users.id')->orderBy('created_at','desc')->get();
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
                                    if($value->user_id == $user->id){
                                        $html .= '
                                        <span class="float-right">
                                            <a href="#gototop" class="btn btn-info btn-xs disable_atag" onclick="edit_activity( \''. $value->id .'\');">
                                                <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                                            </a>
                                            <a href="javascript:void(0)" class="btn btn-danger btn-xs disable_atag" onclick="delete_activity(\''.$value->id.'\',\''.$value->code.'\');"><i class="fas fa-trash-alt"></i></a>
                                        </span>';
                                    }
                                
                            $html .= '
                                </div>
                            </li>';
                        }
                    }else{
                        $html .= '<li class="list-group-item"> </li>';
                    }
               
                    $response = [
                        "html" => $html,
                        "ActivityHistory" => $ActivityHistory,
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

    public function activity_get_edit_data(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code_activity = $data['data']['code_activity'];
                    // $site = $data['data']['site'];
                  

                    $user = User::where('id', $data['data']['user_id'])->first();

                    // $site_id = '';
            
                    // if($site_code) {
                    //     $site_id_m = SiteSettings::where('code',$site_code)->first();
                    //     $site_id = @$site_id_m->id;
                    // }


                    $Activity = Activity::where('id',$code_activity)->first()->toArray();
   
               
                    $response = [
                        "ActivityHistory" => $Activity,
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

    public function activity_save(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    // $code_activity = $data['data']['code_activity'];
                    $code_edited_activity = $data['data']['code_edited_activity'];
                    $status_activity = $data['data']['status_activity'];
                    $content = $data['data']['content'];
                    $check_active = $data['data']['check_active'];
                    $id_DataLeakSocialRefs = $data['data']['id_DataLeakSocialRefs'];
                    $title = $data['data']['title'];
                    $site_code = $data['data']['site_code'];
                    // $site = $data['data']['site'];
                  
                    //--------------------------------//
                    $status_activity = $status_activity;
                    $content = @$content; //รับค่าจาก messageInput
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
                                $data_content = $img->getattribute('src');

                                //Link url
                                $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                                if(preg_match($reg_exUrl, $data_content, $url_image)) {
                                    $url = $url_image[0];
                                    $image = file_get_contents($url);
                                    if ($image !== false){
                                        $data_content = 'data:image/jpg;base64,'.base64_encode($image);
                                    }
                                }

                                $img_check_src = explode(";",$data_content);
                                if(@$img_check_src[1]) {
                                    list($type, $data_content) = explode(';', $data_content);
                                    list(, $data_content)= explode(',', $data_content);
                                    $data_content = base64_decode($data_content);
                                //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                                    $image_name= time().$k.'.png';
                                //อัพโหลดภาพไปยัง public
                                    $path = public_path('images/file_editor') .'/'. $image_name;
                                //ทำการอัพโหลดภาพ
                                    file_put_contents($path, $data_content);
                                    $img->removeattribute('src');
                                    $img->setattribute('src', config('app.URL_CENTER_PUBLISH').'/images/file_editor/'.$image_name);
                                } else {
            
                                }
                            }
                            $content = $dom->savehtml();
            
                        }
                    }
            
                    if($check_active=="1"){
                        $Activity = new Activity;
                        $Activity->code = generator_uuid();
                        $Activity->data_leak_socail_ref_id = $id_DataLeakSocialRefs;
                        $Activity->title = $title;
                        $Activity->content = $content;
                        $Activity->user_id = $data['data']['user_id'];
                        if($status_activity) {
                            $Activity->status_activity = $status_activity;
                        }
                        $Activity->save();
                    }else if($check_active=="2"){
                        $Activity = Activity::where('id',$code_edited_activity)->first();
                        $Activity->title = $title;
                        $Activity->content = $content;
                        if($status_activity) {
                            $Activity->status_activity = $status_activity;
                        }
                        $Activity->save();
                    }
                    $Activity_check = Activity::where('data_leak_socail_ref_id',$id_DataLeakSocialRefs)->where('status_activity','close')->first();

                    $DataLeakSocialRef = DataLeakSocialRef::where('id',$id_DataLeakSocialRefs)->first();
                    // if($DataLeakSocialRef->status_monitoring != 'close') {
                        if($Activity->status_activity == 'close') {
                            $DataLeakSocialRef->status_monitoring = 'close';
                            $DataLeakSocialRef->save();
                        } else if ($Activity->status_activity == 'in_progress') {//reported
                            if($Activity_check) {
                                $DataLeakSocialRef->status_monitoring = 'close';
                                $DataLeakSocialRef->save();
                            } else {
                                $DataLeakSocialRef->status_monitoring = 'in_progress';//reported
                                $DataLeakSocialRef->save();
                            }
                        } else if ($Activity->status_activity == 'in_progress') {
                            if($Activity_check) {
                                $DataLeakSocialRef->status_monitoring = 'close';
                                $DataLeakSocialRef->save();
                            } else {
                                $DataLeakSocialRef->status_monitoring = 'in_progress';
                                $DataLeakSocialRef->save();
                            }
                        }   
                    // }
            

                    //--------------------------------//
   
               
                    $response = [
                        "socail_ref_id" => $Activity->data_leak_socail_ref_id,
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

    public function activity_delete(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    // $code_activity = $data['data']['code_activity'];
                    $code_activity = $data['data']['code_activity'];
                    // $site = $data['data']['site'];
                  
                    //--------------------------------//
                    $Activity = Activity::where('id',$code_activity)->first();
                    $Activity->delete();

                    $Activity_check = Activity::where('data_leak_socail_ref_id',$Activity->data_leak_socail_ref_id)->where('status_activity','close')->first();
                    $Activity_check_last = Activity::where('data_leak_socail_ref_id',$Activity->data_leak_socail_ref_id)->where('deleted_at',null)->orderBy('id','desc')->first();
                    $status_activity = $Activity_check_last->status_activity;

                    $DataLeakSocialRef = DataLeakSocialRef::where('id',$Activity->data_leak_socail_ref_id)->first();
                    // if($DataLeakSocialRef->status_monitoring != 'close') {
                        if($Activity_check_last) {
                            if($status_activity) {
                                $DataLeakSocialRef->status_monitoring = $status_activity;
                                $DataLeakSocialRef->save();
                            } else {
                                $DataLeakSocialRef->status_monitoring = 'in_progress';
                                $DataLeakSocialRef->save();
                            }
                        } else {
                            $DataLeakSocialRef->status_monitoring = 'in_progress';
                            $DataLeakSocialRef->save();
                        } 
                    // }
                    //--------------------------------//
   
               
                    $response = [
                        "Activity" => $Activity,
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

    public function compromise_activity_dataleak_modal(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'compromised'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $DataLeakSocialRef_code = $data['data']['DataLeakSocialRef_code'];
                    $site = $data['data']['site'];
                  

                    $user = User::where('id', $data['data']['user_id'])->first();

                    // $site_id = '';
            
                    // if($site_code) {
                    //     $site_id_m = SiteSettings::where('code',$site_code)->first();
                    //     $site_id = @$site_id_m->id;
                    // }


                    $DataLeakSocialRefs = DataLeakSocialRef::where('code',$DataLeakSocialRef_code)->first();
                    $ActivityHistory = Activity::select('activity.*','users.name as users_name')->where('activity.data_leak_socail_ref_id',$DataLeakSocialRefs->id)->whereNull('activity.deleted_at')->leftJoin('users', 'activity.user_id', '=', 'users.id')->orderBy('created_at','desc')->get();
                    // $DataLeakFeed = DataLeakFeed::where('id',$DataLeakSocialRefs->data_leak_feed_id)->first();
                    $response['DataLeakSocialRefs'] = $DataLeakSocialRefs;
                    $response['site'] = @$site;
                    $response['ActivityHistory'] = $ActivityHistory;
               
            

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

    public function compromise_activity_history_reload(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'compromised'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $DataLeakSocialRef_code = $data['data']['DataLeakSocialRef_code'];
                    // $site = $data['data']['site'];
                  

                    $user = User::where('id', $data['data']['user_id'])->first();

                    // $site_id = '';
            
                    // if($site_code) {
                    //     $site_id_m = SiteSettings::where('code',$site_code)->first();
                    //     $site_id = @$site_id_m->id;
                    // }


                    $DataLeakSocialRefs = DataLeakSocialRef::where('id',$DataLeakSocialRef_code)->first();
                    $ActivityHistory = Activity::select('activity.*','users.name as users_name')->where('activity.data_leak_socail_ref_id',$DataLeakSocialRefs->id)->whereNull('activity.deleted_at')->leftJoin('users', 'activity.user_id', '=', 'users.id')->orderBy('created_at','desc')->get();
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
                                    if($value->user_id == $user->id){
                                        $html .= '
                                        <span class="float-right">
                                            <a href="#gototop" class="btn btn-info btn-xs disable_atag" onclick="edit_activity( \''. $value->id .'\');">
                                                <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                                            </a>
                                            <a href="javascript:void(0)" class="btn btn-danger btn-xs disable_atag" onclick="delete_activity(\''.$value->id.'\',\''.$value->code.'\');"><i class="fas fa-trash-alt"></i></a>
                                        </span>';
                                    }
                                
                            $html .= '
                                </div>
                            </li>';
                        }
                    }else{
                        $html .= '<li class="list-group-item"> </li>';
                    }
               
                    $response = [
                        "html" => $html,
                        "ActivityHistory" => $ActivityHistory,
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

    public function compromise_activity_get_edit_data(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'compromised'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $code_activity = $data['data']['code_activity'];
                    // $site = $data['data']['site'];
                  

                    $user = User::where('id', $data['data']['user_id'])->first();

                    // $site_id = '';
            
                    // if($site_code) {
                    //     $site_id_m = SiteSettings::where('code',$site_code)->first();
                    //     $site_id = @$site_id_m->id;
                    // }


                    $Activity = Activity::where('id',$code_activity)->first()->toArray();
   
               
                    $response = [
                        "ActivityHistory" => $Activity,
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

    public function compromise_activity_save(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                if($data['data']['menu'] !== 'compromised'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    // $code_activity = $data['data']['code_activity'];
                    $code_edited_activity = $data['data']['code_edited_activity'];
                    $status_activity = $data['data']['status_activity'];
                    $content = $data['data']['content'];
                    $check_active = $data['data']['check_active'];
                    $id_DataLeakSocialRefs = $data['data']['id_DataLeakSocialRefs'];
                    $title = $data['data']['title'];
                    $site_code = $data['data']['site_code'];
                    // $site = $data['data']['site'];
                  
                    //--------------------------------//
                    $status_activity = $status_activity;
                    $content = @$content; //รับค่าจาก messageInput
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
                                $data_content = $img->getattribute('src');

                                //Link url
                                $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
                                if(preg_match($reg_exUrl, $data_content, $url_image)) {
                                    $url = $url_image[0];
                                    $image = file_get_contents($url);
                                    if ($image !== false){
                                        $data_content = 'data:image/jpg;base64,'.base64_encode($image);
                                    }
                                }

                                $img_check_src = explode(";",$data_content);
                                if(@$img_check_src[1]) {
                                    list($type, $data_content) = explode(';', $data_content);
                                    list(, $data_content)= explode(',', $data_content);
                                    $data_content = base64_decode($data_content);
                                //ตั้งชื่อรูปภาพใหม่โดยอ้างอิงจากเวลา
                                    $image_name= time().$k.'.png';
                                //อัพโหลดภาพไปยัง public
                                    $path = public_path('images/file_editor') .'/'. $image_name;
                                //ทำการอัพโหลดภาพ
                                    file_put_contents($path, $data_content);
                                    $img->removeattribute('src');
                                    $img->setattribute('src', config('app.URL_CENTER_PUBLISH').'/images/file_editor/'.$image_name);
                                } else {
            
                                }
                            }
                            $content = $dom->savehtml();
            
                        }
                    }
            
                    if($check_active=="1"){
                        $Activity = new Activity;
                        $Activity->code = generator_uuid();
                        $Activity->data_leak_socail_ref_id = $id_DataLeakSocialRefs;
                        $Activity->title = $title;
                        $Activity->content = $content;
                        $Activity->user_id = $data['data']['user_id'];
                        if($status_activity) {
                            $Activity->status_activity = $status_activity;
                        }
                        $Activity->save();
                    }else if($check_active=="2"){
                        $Activity = Activity::where('id',$code_edited_activity)->first();
                        $Activity->title = $title;
                        $Activity->content = $content;
                        if($status_activity) {
                            $Activity->status_activity = $status_activity;
                        }
                        $Activity->save();
                    }
            

                    //--------------------------------//
   
               
                    $response = [
                        "socail_ref_id" => $Activity->data_leak_socail_ref_id,
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

    public function compromise_activity_delete(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                if($data['data']['menu'] !== 'compromised'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    // $code_activity = $data['data']['code_activity'];
                    $code_activity = $data['data']['code_activity'];
                    // $site = $data['data']['site'];
                  
                    //--------------------------------//
                    $Activity = Activity::where('id',$code_activity)->first();
                    $Activity->delete();
                    //--------------------------------//
   
               
                    $response = [
                        "Activity" => $Activity,
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

    public function count_icon_dataleak(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $authenticatedSiteId = (int)($data['site']['data']['id'] ?? 0);
                $get_role = $data['data']['get_role_custom_first'] ?? [];
                $isSuperAdmin = (isset($get_role['superadmin']) && (int)$get_role['superadmin'] == 1);
                $isClientOrSiteClient = (
                    (isset($get_role['client']) && (int)$get_role['client'] == 1) ||
                    (isset($get_role['site_client']) && (int)$get_role['site_client'] == 1)
                );
                
                $site_ids = [];
                if ($isSuperAdmin && isset($get_role['site_id_arr'])) {
                    $site_ids = collect($get_role['site_id_arr'])->pluck('site_id')->filter()->values()->toArray();
                }

                $type = $data['data']['type'] ?? null;
                $refTable = 'data_leak_socail_ref';
                $feedTable = 'data_leak_feed';
                $siteTable = 'site';

                $selectedSiteId = null;
                if (!empty($data['data']['site_code'])) {
                    if ($s = SiteSettings::where('code', $data['data']['site_code'])->first()) {
                        $selectedSiteId = (int) $s->id;
                    }
                }

                $qCredential = DB::table($refTable)
                    ->join($feedTable, "$refTable.data_leak_feed_id", '=', "$feedTable.id")
                    ->join($siteTable, "$siteTable.id", '=', "$refTable.site_id")
                    ->whereNull("$refTable.deleted_at")
                    ->whereIn("$feedTable.feel_type", ['surface_web', 'darkweb', 'darkweb_public', 'social'])
                    ->when($selectedSiteId, fn($q) => $q->where("$refTable.site_id", $selectedSiteId))
                    ->when(!$selectedSiteId && $isSuperAdmin && !empty($site_ids), fn($q) => $q->whereIn("$refTable.site_id", $site_ids))
                    ->when(!$selectedSiteId && !$isSuperAdmin && $authenticatedSiteId > 0, fn($q) => $q->where("$refTable.site_id", $authenticatedSiteId))
                    ->when($isClientOrSiteClient, fn($q) => $q->where("$feedTable.status", 1))
                    ->select("$refTable.*", "$feedTable.keyword", "$feedTable.feel_type", "$feedTable.feedtimepost");

                if (isset($data['data']['isDateSearch']) && (int)$data['data']['isDateSearch'] === 1 && @$data['data']['startDate'] && @$data['data']['endDate']) {
                    $start = Carbon::parse($data['data']['startDate'])->startOfDay()->toDateTimeString();
                    $end   = Carbon::parse($data['data']['endDate'])->endOfDay()->toDateTimeString();
                    $f_raw = DB::getTablePrefix() . $feedTable;
                    $qCredential->whereRaw("CAST(`$f_raw`.`feedtimepost` AS DATETIME) BETWEEN ? AND ?", [$start, $end]);
                }

                if (!empty($data['data']['check_type'])) {
                    $checkType = strtolower(trim($data['data']['check_type']));
                    $f_raw = DB::getTablePrefix() . $feedTable;
                    if ($checkType === 'surface_web') {
                        $qCredential->whereRaw("LOWER(`{$f_raw}`.`feel_type`) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')");
                    } elseif ($checkType === 'darkweb') {
                        $qCredential->whereRaw("LOWER(`{$f_raw}`.`feel_type`) IN ('darkweb', 'darkweb_public', 'darkweb_private')");
                    }
                }

                if (!empty($data['data']['check_social'])) {
                    $checkSocial = strtolower(trim($data['data']['check_social']));
                    $f_raw = DB::getTablePrefix() . $feedTable;
                    if ($checkSocial === 'website') {
                        $qCredential->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["website%"]);
                    } elseif ($checkSocial === 'social') {
                        $qCredential->where(function($query) use ($f_raw) {
                            $query->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["facebook%"])->orWhereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["line%"])->orWhereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["twitter%"])->orWhereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["fanpage%"]);
                        });
                    } elseif ($checkSocial === 'community') {
                        $qCredential->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["website%"])->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["facebook%"])->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["line%"])->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["twitter%"])->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["fanpage%"]);
                    } else {
                        $qCredential->where("{$f_raw}.keyword", 'LIKE', $data['data']['check_social'] . '%');
                    }
                }

                if (!empty($data['data']['check_darkweb'])) {
                    $checkDarkweb = strtolower(trim($data['data']['check_darkweb']));
                    $f_raw = DB::getTablePrefix() . $feedTable;
                    if ($checkDarkweb === 'website') {
                        $qCredential->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["website%"]);
                    } elseif ($checkDarkweb === 'social') {
                        $qCredential->where(function($query) use ($f_raw) {
                            $query->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["facebook%"])->orWhereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["line%"])->orWhereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["twitter%"])->orWhereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["telegram%"])->orWhereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) LIKE ?", ["social%"]);
                        });
                    } elseif ($checkDarkweb === 'community') {
                        $qCredential->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["website%"])->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["facebook%"])->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["line%"])->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["twitter%"])->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["telegram%"])->whereRaw("TRIM(LOWER(`{$f_raw}`.`keyword`)) NOT LIKE ?", ["social%"]);
                    } else {
                        $qCredential->where("{$f_raw}.keyword", 'LIKE', $data['data']['check_darkweb'] . '%');
                    }
                }

                if (!empty($data['data']['check_serverity'])) {
                    $r_raw = DB::getTablePrefix() . $refTable;
                    $qCredential->whereRaw("LOWER($r_raw.serverity) = ?", [strtolower(trim($data['data']['check_serverity']))]);
                }

                if (!empty($data['data']['check_monitoring'])) {
                    $checkMonitoring = strtolower(trim($data['data']['check_monitoring']));
                    $r_raw = DB::getTablePrefix() . $refTable;
                    if ($checkMonitoring === 'in_progress') {
                        $qCredential->where(function($query) use ($r_raw) {
                            $query->whereRaw("LOWER($r_raw.status_monitoring) = 'in_progress'")->orWhereRaw("$r_raw.status_monitoring IS NULL")->orWhereRaw("$r_raw.status_monitoring = ''");
                        });
                    } else {
                        $qCredential->whereRaw("LOWER($r_raw.status_monitoring) = ?", [$checkMonitoring]);
                    }
                }

                if (!empty($data['data']['keywords'])) {
                    $kw = $data['data']['keywords'];
                    $f_raw = DB::getTablePrefix() . $feedTable;
                    $qCredential->where(function($q) use ($f_raw, $kw) {
                        $q->whereRaw("LOWER($f_raw.keyword) LIKE ?", ['%'.strtolower($kw).'%'])->orWhereRaw("LOWER($f_raw.feedcontent) LIKE ?", ['%'.strtolower($kw).'%']);
                    });
                }

                $qCredential->where("$refTable.status", 1);
                $base = DB::query()->fromSub($qCredential, 'u');

                $icon_mobile   = (clone $base)->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['mobile%'])->count();
                $icon_facebook = (clone $base)->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['facebook%'])->count();
                $icon_line     = (clone $base)->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['line%'])->count();
                $icon_twitter  = (clone $base)->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['twitter%'])->count();
                $icon_website  = (clone $base)->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['website%'])->count();
                $icon_other    = (clone $base)->whereRaw('TRIM(LOWER(keyword)) NOT IN (?, ?, ?, ?, ?)', ['mobile','facebook','line','twitter','website'])->count();

                $baseSurface = (clone $base)->whereRaw("LOWER(feel_type) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')");
                $icon_website_s   = (clone $baseSurface)->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['website%'])->count();
                $icon_social_s    = (clone $baseSurface)->where(function($q) { $q->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['facebook%'])->orWhereRaw('TRIM(LOWER(keyword)) LIKE ?', ['line%'])->orWhereRaw('TRIM(LOWER(keyword)) LIKE ?', ['twitter%']); })->count();
                $icon_community_s = (clone $baseSurface)->whereRaw('TRIM(LOWER(keyword)) NOT LIKE ?', ['website%'])->whereRaw('TRIM(LOWER(keyword)) NOT LIKE ?', ['facebook%'])->whereRaw('TRIM(LOWER(keyword)) NOT LIKE ?', ['line%'])->whereRaw('TRIM(LOWER(keyword)) NOT LIKE ?', ['twitter%'])->count();
                $surface_web_total = (clone $baseSurface)->count();

                $baseDarkweb = (clone $base)->whereRaw("LOWER(feel_type) IN ('darkweb', 'darkweb_public', 'darkweb_private')");
                $icon_website_d   = (clone $baseDarkweb)->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['website%'])->count();
                $icon_social_d    = (clone $baseDarkweb)->where(function($q) { $q->whereRaw('TRIM(LOWER(keyword)) LIKE ?', ['facebook%'])->orWhereRaw('TRIM(LOWER(keyword)) LIKE ?', ['line%'])->orWhereRaw('TRIM(LOWER(keyword)) LIKE ?', ['twitter%']); })->count();
                $icon_community_d = (clone $baseDarkweb)->whereRaw('TRIM(LOWER(keyword)) NOT LIKE ?', ['website%'])->whereRaw('TRIM(LOWER(keyword)) NOT LIKE ?', ['facebook%'])->whereRaw('TRIM(LOWER(keyword)) NOT LIKE ?', ['line%'])->whereRaw('TRIM(LOWER(keyword)) NOT LIKE ?', ['twitter%'])->count();
                $darkweb_total = (clone $baseDarkweb)->count();

                $number_in_progress = (clone $base)->where(function($q) { $q->whereRaw("LOWER(status_monitoring) = 'in_progress'")->orWhereRaw("status_monitoring IS NULL")->orWhereRaw("status_monitoring = ''"); })->count();
                $number_reported    = (clone $base)->whereRaw('LOWER(status_monitoring) = ?', ['reported'])->count();
                $number_close       = (clone $base)->whereRaw('LOWER(status_monitoring) = ?', ['close'])->count();

                $response = [
                    "icon_mobile" => $icon_mobile, "icon_facebook" => $icon_facebook, "icon_line" => $icon_line, "icon_twitter" => $icon_twitter, "icon_website" => $icon_website, "icon_other" => $icon_other,
                    "icon_website_s" => $icon_website_s, "icon_social_s" => $icon_social_s, "icon_community_s" => $icon_community_s, "surface_web_total" => $surface_web_total,
                    "icon_website_d" => $icon_website_d, "icon_social_d" => $icon_social_d, "icon_community_d" => $icon_community_d, "darkweb_total" => $darkweb_total,
                    "number_in_progress" => $number_in_progress, "number_reported" => $number_reported, "number_close" => $number_close,
                ];

                $data_transcation = json_encode($response);
                $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'message' => $e->getMessage()]);
        }
    }

    public function data_leak_count_val_dataleak(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $authenticatedSiteId = (int)($data['site']['data']['id'] ?? 0);
                $get_role = $data['data']['get_role_custom_first'] ?? [];
                $isSuperAdmin = (isset($get_role['superadmin']) && (int)$get_role['superadmin'] == 1);
                $isClientOrSiteClient = (
                    (isset($get_role['client']) && (int)$get_role['client'] == 1) ||
                    (isset($get_role['site_client']) && (int)$get_role['site_client'] == 1)
                );
                
                $site_ids = [];
                if ($isSuperAdmin && isset($get_role['site_id_arr'])) {
                    $site_ids = collect($get_role['site_id_arr'])->pluck('site_id')->filter()->values()->toArray();
                }

                $refTable = 'data_leak_socail_ref';
                $feedTable = 'data_leak_feed';
                $siteTable = 'site';

                $selectedSiteId = null;
                if (!empty($data['data']['site_code'])) {
                    if ($s = SiteSettings::where('code', $data['data']['site_code'])->first()) {
                        $selectedSiteId = (int) $s->id;
                    }
                }

                $qCredential = DB::table($refTable)
                    ->join($feedTable, "$refTable.data_leak_feed_id", '=', "$feedTable.id")
                    ->join($siteTable, "$siteTable.id", '=', "$refTable.site_id")
                    ->whereNull("$refTable.deleted_at")
                    ->whereIn("$feedTable.feel_type", ['surface_web', 'darkweb', 'darkweb_public', 'social'])
                    ->when($selectedSiteId, fn($q) => $q->where("$refTable.site_id", $selectedSiteId))
                    ->when(!$selectedSiteId && $isSuperAdmin && !empty($site_ids), fn($q) => $q->whereIn("$refTable.site_id", $site_ids))
                    ->when(!$selectedSiteId && !$isSuperAdmin && $authenticatedSiteId > 0, fn($q) => $q->where("$refTable.site_id", $authenticatedSiteId))
                    ->when($isClientOrSiteClient, fn($q) => $q->where("$feedTable.status", 1))
                    ->select("$refTable.*", "$feedTable.keyword", "$feedTable.feel_type", "$feedTable.feedtimepost");

                if (isset($data['data']['isDateSearch']) && (int)$data['data']['isDateSearch'] === 1 && @$data['data']['startDate'] && @$data['data']['endDate']) {
                    $start = Carbon::parse($data['data']['startDate'])->startOfDay()->toDateTimeString();
                    $end   = Carbon::parse($data['data']['endDate'])->endOfDay()->toDateTimeString();
                    $qCredential->whereRaw("CAST(`$feedTable`.`feedtimepost` AS DATETIME) BETWEEN ? AND ?", [$start, $end]);
                }

                if (!empty($data['data']['check_type'])) {
                    $checkType = strtolower(trim($data['data']['check_type']));
                    if ($checkType === 'surface_web') {
                        $qCredential->whereRaw("LOWER($feedTable.feel_type) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')");
                    } elseif ($checkType === 'darkweb') {
                        $qCredential->whereRaw("LOWER($feedTable.feel_type) IN ('darkweb', 'darkweb_public', 'darkweb_private')");
                    }
                }

                if (!empty($data['data']['check_social'])) {
                    $checkSocial = strtolower(trim($data['data']['check_social']));
                    if ($checkSocial === 'website') {
                        $qCredential->whereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["website%"]);
                    } elseif ($checkSocial === 'social') {
                        $qCredential->where(function($query) use ($feedTable) {
                            $query->whereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["facebook%"])->orWhereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["line%"])->orWhereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["twitter%"])->orWhereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["fanpage%"]);
                        });
                    } elseif ($checkSocial === 'community') {
                        $qCredential->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["website%"])->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["facebook%"])->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["line%"])->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["twitter%"])->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["fanpage%"]);
                    } else {
                        $qCredential->where("$feedTable.keyword", 'LIKE', $data['data']['check_social'] . '%');
                    }
                }

                if (!empty($data['data']['check_darkweb'])) {
                    $checkDarkweb = strtolower(trim($data['data']['check_darkweb']));
                    if ($checkDarkweb === 'website') {
                        $qCredential->whereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["website%"]);
                    } elseif ($checkDarkweb === 'social') {
                        $qCredential->where(function($query) use ($feedTable) {
                            $query->whereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["facebook%"])->orWhereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["line%"])->orWhereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["twitter%"])->orWhereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["telegram%"])->orWhereRaw("TRIM(LOWER($feedTable.keyword)) LIKE ?", ["social%"]);
                        });
                    } elseif ($checkDarkweb === 'community') {
                        $qCredential->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["website%"])->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["facebook%"])->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["line%"])->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["twitter%"])->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["telegram%"])->whereRaw("TRIM(LOWER($feedTable.keyword)) NOT LIKE ?", ["social%"]);
                    } else {
                        $qCredential->where("$feedTable.keyword", 'LIKE', $data['data']['check_darkweb'] . '%');
                    }
                }

                if (!empty($data['data']['check_serverity'])) {
                    $qCredential->whereRaw("LOWER($refTable.serverity) = ?", [strtolower(trim($data['data']['check_serverity']))]);
                }

                if (!empty($data['data']['check_monitoring'])) {
                    $checkMonitoring = strtolower(trim($data['data']['check_monitoring']));
                    if ($checkMonitoring === 'in_progress') {
                        $qCredential->where(function($query) use ($refTable) {
                            $query->whereRaw("LOWER($refTable.status_monitoring) = 'in_progress'")->orWhereRaw("$refTable.status_monitoring IS NULL")->orWhereRaw("$refTable.status_monitoring = ''");
                        });
                    } else {
                        $qCredential->whereRaw("LOWER($refTable.status_monitoring) = ?", [$checkMonitoring]);
                    }
                }

                if (!empty($data['data']['keywords'])) {
                    $kw = $data['data']['keywords'];
                    $qCredential->where(function($q) use ($feedTable, $kw) {
                        $q->whereRaw("LOWER($feedTable.keyword) LIKE ?", ['%'.strtolower($kw).'%'])->orWhereRaw("LOWER($feedTable.feedcontent) LIKE ?", ['%'.strtolower($kw).'%']);
                    });
                }

                $qCredential->where("$refTable.status", 1);
                $base = DB::query()->fromSub($qCredential, 'u');

                $response = [
                    "html" => "",
                    "count" => (clone $base)->count(),
                    "darkweb" => (clone $base)->whereIn('feel_type', ['darkweb', 'darkweb_public', 'darkweb_private'])->count(),
                    "social" => (clone $base)->whereRaw("LOWER(feel_type) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')")->count(),
                ];

                $data_transcation = json_encode($response);
                $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'message' => $e->getMessage()]);
        }
    }
    public function credentialdatas_count_icon(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                $feedTable          = $this->resolveTable(['data_leak_feed']);
                $siteTable          = $this->resolveTable(['site']);
                $credentialRefTable = $this->resolveTable(['credential_leak_ref']);

                // ---------- ROLE ----------
                $authenticatedSiteId = (int)($data['site']['data']['id'] ?? 0);
                $get_role = $data['data']['get_role_custom_first'] ?? [];
                $isSuperAdmin = (isset($get_role['superadmin']) && (int)$get_role['superadmin'] == 1);
                $site_ids = collect(@$get_role['site_id_arr'])
                    ->pluck('site_id')
                    ->filter()
                    ->values()
                    ->toArray();

                $isClientOrSiteClient = (
                    (isset($get_role['client']) && (int)$get_role['client'] == 1) ||
                    (isset($get_role['site_client']) && (int)$get_role['site_client'] == 1)
                );

                $hasRoleSiteLimit = !empty($site_ids);

                // ---------- SELECTED SITE ----------
                $selectedSite = null;
                if (!empty($data['data']['site_id'])) {
                    if ($s = SiteSettings::where('code', $data['data']['site_id'])->first()) {
                        $selectedSite = (int) $s->id;
                    }
                }

                // ---------- CREDENTIAL REF ----------
                $qCredential = DB::table($credentialRefTable)
                    ->join($feedTable, "$credentialRefTable.data_leak_feed_id", '=', "$feedTable.id")
                    ->join($siteTable, "$siteTable.id", '=', "$credentialRefTable.site_id")
                    ->whereIn("$feedTable.feel_type", ['surface_web', 'darkweb', 'darkweb_public', 'darkweb_private', 'social'])
                    ->whereNull("$credentialRefTable.deleted_at")
                    ->when($isClientOrSiteClient, fn($q) => $q->where("$feedTable.status", 1));

                if (!empty($selectedSite)) {
                    $qCredential->where("$credentialRefTable.site_id", $selectedSite);
                } elseif ($hasRoleSiteLimit) {
                    $qCredential->whereIn("$credentialRefTable.site_id", $site_ids);
                } elseif (!empty($get_role['site_id'])) {
                    $qCredential->where("$credentialRefTable.site_id", (int)$get_role['site_id']);
                } elseif (!$isSuperAdmin && $authenticatedSiteId > 0) {
                    $qCredential->where("$credentialRefTable.site_id", $authenticatedSiteId);
                }

                $qCredential->select(
                    "$credentialRefTable.id as ref_id",
                    "$credentialRefTable.site_id",
                    "$credentialRefTable.status_monitoring",
                    "$feedTable.keyword",
                    "$feedTable.source_name",
                    "$feedTable.feel_type",
                    "$feedTable.feedtimepost"
                );

                // ---------- SINGLE SITE_ID FILTER ----------


                // ---------- DATE FILTER ----------
                if (isset($data['data']['isDateSearch']) && (int) $data['data']['isDateSearch'] === 1 && @$data['data']['startDate'] && @$data['data']['endDate']) {
                    $start = Carbon::parse($data['data']['startDate'])->startOfDay()->toDateTimeString();
                    $end   = Carbon::parse($data['data']['endDate'])->endOfDay()->toDateTimeString();
                    $feedAlias = DB::getTablePrefix() . $feedTable;

                    $qCredential->whereRaw("
                        `$feedAlias`.`feedtimepost` IS NOT NULL
                        AND CAST(`$feedAlias`.`feedtimepost` AS DATETIME) BETWEEN ? AND ?
                    ", [$start, $end]);
                }

                // ---------- KEYWORDS FILTER ----------
                if (!empty($data['data']['keywords'])) {
                    $keywords = $data['data']['keywords'];
                    $feedAlias = DB::getTablePrefix() . $feedTable;
                    $qCredential->where(function ($q) use ($feedAlias, $keywords) {
                        $q->whereRaw("LOWER(`{$feedAlias}`.`keyword`) LIKE ?", ['%' . strtolower($keywords) . '%'])
                            ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%' . strtolower($keywords) . '%'])
                            ->orWhereRaw("LOWER(`{$feedAlias}`.`feedcontent`) LIKE ?", ['%' . strtolower($keywords) . '%']);
                    });
                }

                // ---------- CHECK_TYPE FILTER ----------
                if (!empty($data['data']['check_type'])) {
                    $checkType = strtolower(trim($data['data']['check_type']));
                    if ($checkType === 'surface_web' || $checkType === 'social') {
                        $qCredential->whereIn("$feedTable.feel_type", ['surface_web', 'social']);
                    } elseif ($checkType === 'darkweb' || $checkType === 'darkweb_public' || $checkType === 'darkweb_private') {
                        $qCredential->whereIn("$feedTable.feel_type", ['darkweb', 'darkweb_public', 'darkweb_private']);
                    }
                }

                // ---------- CHECK_SOCIAL FILTER ----------
                if (!empty($data['data']['check_social'])) {
                    $cs = strtolower(trim($data['data']['check_social']));
                    $feedAlias = DB::getTablePrefix() . $feedTable;
                    $qCredential->whereIn("$feedTable.feel_type", ['surface_web', 'social']);
                    if ($cs === 'website') {
                        $qCredential->where(function ($q) use ($feedAlias) {
                            $q->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.com%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.net%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.org%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.co.th%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.io%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.th%']);
                        })
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%line%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%telegram%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%instagram%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%tiktok%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%youtube%']);
                    } elseif ($cs === 'social') {
                        $qCredential->where(function ($q) use ($feedAlias) {
                            $q->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%twitter%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%facebook%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%line%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%telegram%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%instagram%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%tiktok%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%youtube%']);
                        });
                    } elseif ($cs === 'community') {
                        $qCredential->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.com%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.net%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.org%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.th%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%line%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                    }
                }

                // ---------- CHECK_DARKWEB FILTER ----------
                if (!empty($data['data']['check_darkweb'])) {
                    $cd = strtolower(trim($data['data']['check_darkweb']));
                    $feedAlias = DB::getTablePrefix() . $feedTable;
                    $qCredential->whereIn("$feedTable.feel_type", ['darkweb', 'darkweb_public', 'darkweb_private']);
                    if ($cd === 'website') {
                        $qCredential->where(function ($q) use ($feedAlias) {
                            $q->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.com%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.net%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.org%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.onion%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.io%']);
                        })
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%line%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                    } elseif ($cd === 'social') {
                        $qCredential->where(function ($q) use ($feedAlias) {
                            $q->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%twitter%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%facebook%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%line%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%telegram%']);
                        });
                    } elseif ($cd === 'community') {
                        $qCredential->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.com%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.net%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.onion%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                    }
                }

                // ---------- CHECK_SERVERITY FILTER ----------
                if (!empty($data['data']['check_serverity'])) {
                    $qCredential->whereRaw("LOWER(" . DB::getTablePrefix() . "$credentialRefTable.serverity) = ?", [strtolower(trim($data['data']['check_serverity']))]);
                }

                // ---------- CHECK_MONITORING FILTER ----------
                if (!empty($data['data']['check_monitoring'])) {
                    $checkMonitoring = strtolower(trim($data['data']['check_monitoring']));
                    if ($checkMonitoring === 'in_progress') {
                        $qCredential->where(function ($q) use ($credentialRefTable) {
                            $q->where("$credentialRefTable.status_monitoring", 'in_progress')
                                ->orWhereNull("$credentialRefTable.status_monitoring")
                                ->orWhere("$credentialRefTable.status_monitoring", '');
                        });
                    } else {
                        $qCredential->where("$credentialRefTable.status_monitoring", $data['data']['check_monitoring']);
                    }
                }

                // ---------- CLICK_TYPE / CLICK_TYPE2 FILTER ----------
                $clickType = !empty($data['data']['click_type2']) ? $data['data']['click_type2'] : ($data['data']['click_type'] ?? null);

                if (!empty($clickType)) {
                    $ct = strtolower(trim($clickType));
                    $feedAlias = DB::getTablePrefix() . $feedTable;

                    if (in_array($ct, ['in_progress', 'reported', 'close'])) {
                        $qCredential->where("$credentialRefTable.status_monitoring", 'LIKE', "%{$ct}%");
                    } elseif ($ct === 'social' || $ct === 'surface_web') {
                        $qCredential->whereIn("$feedTable.feel_type", ['surface_web', 'social']);
                    } elseif ($ct === 'darkweb_public' || $ct === 'darkweb') {
                        $qCredential->whereIn("$feedTable.feel_type", ['darkweb', 'darkweb_public', 'darkweb_private']);
                    } elseif (in_array($ct, ['mobile', 'facebook', 'line', 'twitter', 'website'])) {
                        $qCredential->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ["%{$ct}%"]);
                    } elseif ($ct === 'other') {
                        $qCredential->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%mobile%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%twitter%']);
                    } elseif (in_array($ct, ['website_s', 'social_s', 'community_s'])) {
                        $qCredential->whereIn("$feedTable.feel_type", ['surface_web', 'social']);
                        if ($ct === 'website_s') {
                            $qCredential->where(function ($q) use ($feedAlias) {
                                $q->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.com%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.net%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.org%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.th%']);
                            });
                        } elseif ($ct === 'social_s') {
                            $qCredential->where(function ($q) use ($feedAlias) {
                                $q->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%twitter%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%facebook%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%telegram%']);
                            });
                        } elseif ($ct === 'community_s') {
                            $qCredential->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.com%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.th%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%facebook%']);
                        }
                    } elseif (in_array($ct, ['website_d', 'social_d', 'community_d'])) {
                        $qCredential->whereIn("$feedTable.feel_type", ['darkweb', 'darkweb_public', 'darkweb_private']);
                        if ($ct === 'website_d') {
                            $qCredential->where(function ($q) use ($feedAlias) {
                                $q->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.com%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.onion%']);
                            });
                        } elseif ($ct === 'social_d') {
                            $qCredential->where(function ($q) use ($feedAlias) {
                                $q->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%twitter%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%facebook%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%telegram%']);
                            });
                        } elseif ($ct === 'community_d') {
                            $qCredential->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.com%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.onion%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%facebook%']);
                        }
                    }
                }

                // ---------- QUERY WRAPPER ----------
                $base = DB::query()->fromSub($qCredential, 'u');

                // ---------- SURFACE WEB COUNTS ----------
                $baseSurface = (clone $base)->whereIn('feel_type', ['surface_web', 'social']);
                $icon_website_s = (clone $baseSurface)->where(function ($q) {
                    $q->whereRaw("LOWER(source_name) LIKE ?", ['%.com%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.net%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.org%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.co.th%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.io%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.th%']);
                })
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%twitter%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%facebook%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%line%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%telegram%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%instagram%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%tiktok%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%youtube%'])
                    ->count();

                $icon_social_s = (clone $baseSurface)->where(function ($q) {
                    $q->whereRaw("LOWER(source_name) LIKE ?", ['%twitter%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%facebook%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%line%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%telegram%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%instagram%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%tiktok%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%youtube%']);
                })->count();

                $icon_community_s = (clone $baseSurface)
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.com%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.net%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.org%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.co.th%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.io%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.th%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%twitter%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%facebook%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%line%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%telegram%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%instagram%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%tiktok%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%youtube%'])
                    ->count();
                $surface_web_total = (clone $base)->whereIn('feel_type', ['surface_web', 'social'])->count();

                // ---------- DARK WEB COUNTS ----------
                $baseDarkweb = (clone $base)->whereIn('feel_type', ['darkweb', 'darkweb_public', 'darkweb_private']);
                $icon_website_d = (clone $baseDarkweb)->where(function ($q) {
                    $q->whereRaw("LOWER(source_name) LIKE ?", ['%.com%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.net%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.org%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.onion%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%.io%']);
                })
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%twitter%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%facebook%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%line%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%telegram%'])
                    ->count();

                $icon_social_d = (clone $baseDarkweb)->where(function ($q) {
                    $q->whereRaw("LOWER(source_name) LIKE ?", ['%twitter%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%facebook%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%line%'])
                        ->orWhereRaw("LOWER(source_name) LIKE ?", ['%telegram%']);
                })->count();

                $icon_community_d = (clone $baseDarkweb)
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.com%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.net%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.org%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.onion%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%.io%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%twitter%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%facebook%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%line%'])
                    ->whereRaw("LOWER(source_name) NOT LIKE ?", ['%telegram%'])
                    ->count();
                $darkweb_total = (clone $base)->whereIn('feel_type', ['darkweb', 'darkweb_public', 'darkweb_private'])->count();

                // ---------- STATUS COUNTS ----------
                $number_in_progress = (clone $base)->where(function ($q) {
                    $q->where('status_monitoring', 'LIKE', '%in_progress%')
                        ->orWhereNull('status_monitoring')
                        ->orWhere('status_monitoring', '');
                })->count();
                $number_reported    = (clone $base)->where('status_monitoring', 'LIKE', '%reported%')->count();
                $number_close       = (clone $base)->where('status_monitoring', 'LIKE', '%close%')->count();

                $response = [
                    "icon_website_s"     => $icon_website_s,
                    "icon_social_s"      => $icon_social_s,
                    "icon_community_s"   => $icon_community_s,
                    "surface_web_total"  => $surface_web_total,
                    "icon_website_d"     => $icon_website_d,
                    "icon_social_d"      => $icon_social_d,
                    "icon_community_d"   => $icon_community_d,
                    "darkweb_total"      => $darkweb_total,
                    "number_in_progress" => $number_in_progress,
                    "number_reported"    => $number_reported,
                    "number_close"       => $number_close,
                ];

                $data_transcation = json_encode($response);
                $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );
            return response()->json($response);
        }
    }

    public function credentialdatas_count_val(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                $feedTable          = $this->resolveTable(['data_leak_feed']);
                $siteTable          = $this->resolveTable(['site']);
                $credentialRefTable = $this->resolveTable(['credential_leak_ref']);

                // ---------- ROLE ----------
                $authenticatedSiteId = (int)($data['site']['data']['id'] ?? 0);
                $get_role = $data['data']['get_role_custom_first'] ?? [];
                $isSuperAdmin = (isset($get_role['superadmin']) && (int)$get_role['superadmin'] == 1);
                $site_ids = collect(@$get_role['site_id_arr'])
                    ->pluck('site_id')
                    ->filter()
                    ->values()
                    ->toArray();

                $isClientOrSiteClient = (
                    (isset($get_role['client']) && (int)$get_role['client'] == 1) ||
                    (isset($get_role['site_client']) && (int)$get_role['site_client'] == 1)
                );

                $hasRoleSiteLimit = !empty($site_ids);

                // ---------- SELECTED SITE ----------
                $selectedSite = null;
                if (!empty($data['data']['site_id'])) {
                    if ($s = SiteSettings::where('code', $data['data']['site_id'])->first()) {
                        $selectedSite = (int) $s->id;
                    }
                }

                // ---------- QUERY ----------
                $q = DB::table($credentialRefTable)
                    ->join($feedTable, "$credentialRefTable.data_leak_feed_id", '=', "$feedTable.id")
                    ->whereIn("$feedTable.feel_type", ['surface_web', 'darkweb', 'darkweb_public', 'darkweb_private', 'social'])
                    ->whereNull("$credentialRefTable.deleted_at")
                    ->when($isClientOrSiteClient, fn($q) => $q->where("$feedTable.status", 1));
                if (!empty($selectedSite)) {
                    $q->where("$credentialRefTable.site_id", $selectedSite);
                } elseif ($hasRoleSiteLimit) {
                    $q->whereIn("$credentialRefTable.site_id", $site_ids);
                } elseif (!empty($get_role['site_id'])) {
                    $q->where("$credentialRefTable.site_id", (int)$get_role['site_id']);
                } elseif (!$isSuperAdmin && $authenticatedSiteId > 0) {
                    $q->where("$credentialRefTable.site_id", $authenticatedSiteId);
                }

                // ---------- KEYWORDS ----------
                if (!empty($data['data']['keywords'])) {
                    $kw = strtolower(trim($data['data']['keywords']));
                    $feedAlias = DB::getTablePrefix() . $feedTable;
                    $q->where(function ($query) use ($feedAlias, $kw) {
                        $query->whereRaw("LOWER(`{$feedAlias}`.`keyword`) LIKE ?", ["%{$kw}%"])
                            ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ["%{$kw}%"])
                            ->orWhereRaw("LOWER(`{$feedAlias}`.`feedcontent`) LIKE ?", ["%{$kw}%"]);
                    });
                }

                // ---------- DATE FILTER ----------
                if (isset($data['data']['isDateSearch']) && (int) $data['data']['isDateSearch'] === 1 && @$data['data']['startDate'] && @$data['data']['endDate']) {
                    $start = Carbon::parse($data['data']['startDate'])->startOfDay()->toDateTimeString();
                    $end   = Carbon::parse($data['data']['endDate'])->endOfDay()->toDateTimeString();
                    $feedAlias = DB::getTablePrefix() . $feedTable;

                    $q->whereRaw("
                        `$feedAlias`.`feedtimepost` IS NOT NULL
                        AND CAST(`$feedAlias`.`feedtimepost` AS DATETIME) BETWEEN ? AND ?
                    ", [$start, $end]);
                }

                // Clone query for dashboard counts before category/click filters
                $qCounts = clone $q;

                // ---------- CHECK_TYPE FILTER ----------
                if (!empty($data['data']['check_type'])) {
                    $checkType = strtolower(trim($data['data']['check_type']));
                    if ($checkType === 'surface_web' || $checkType === 'social') {
                        $q->whereIn("$feedTable.feel_type", ['surface_web', 'social']);
                    } elseif ($checkType === 'darkweb' || $checkType === 'darkweb_public' || $checkType === 'darkweb_private') {
                        $q->whereIn("$feedTable.feel_type", ['darkweb', 'darkweb_public', 'darkweb_private']);
                    }
                }

                // ---------- CHECK_SOCIAL FILTER ----------
                if (!empty($data['data']['check_social'])) {
                    $cs = strtolower(trim($data['data']['check_social']));
                    $feedAlias = DB::getTablePrefix() . $feedTable;
                    $q->whereIn("$feedTable.feel_type", ['surface_web', 'social']);
                    if ($cs === 'website') {
                        $q->where(function ($query) use ($feedAlias) {
                            $query->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.com%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.net%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.org%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.co.th%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.io%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.th%']);
                        })
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%line%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%telegram%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%instagram%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%tiktok%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%youtube%']);
                    } elseif ($cs === 'social') {
                        $q->where(function ($query) use ($feedAlias) {
                            $query->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%twitter%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%facebook%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%line%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%telegram%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%instagram%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%tiktok%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%youtube%']);
                        });
                    } elseif ($cs === 'community') {
                        $q->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.com%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.net%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.org%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.th%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%line%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                    }
                }

                // ---------- CHECK_DARKWEB FILTER ----------
                if (!empty($data['data']['check_darkweb'])) {
                    $cd = strtolower(trim($data['data']['check_darkweb']));
                    $feedAlias = DB::getTablePrefix() . $feedTable;
                    $q->whereIn("$feedTable.feel_type", ['darkweb', 'darkweb_public', 'darkweb_private']);
                    if ($cd === 'website') {
                        $q->where(function ($query) use ($feedAlias) {
                            $query->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.com%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.net%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.org%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.onion%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.io%']);
                        })
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%line%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                    } elseif ($cd === 'social') {
                        $q->where(function ($query) use ($feedAlias) {
                            $query->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%twitter%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%facebook%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%line%'])
                                ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%telegram%']);
                        });
                    } elseif ($cd === 'community') {
                        $q->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.com%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.net%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.onion%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                    }
                }

                // ---------- CHECK_SERVERITY FILTER ----------
                if (!empty($data['data']['check_serverity'])) {
                    $q->whereRaw("LOWER(" . DB::getTablePrefix() . "$credentialRefTable.serverity) = ?", [strtolower(trim($data['data']['check_serverity']))]);
                }

                // ---------- CHECK_MONITORING FILTER ----------
                if (!empty($data['data']['check_monitoring'])) {
                    $checkMonitoring = strtolower(trim($data['data']['check_monitoring']));
                    if ($checkMonitoring === 'in_progress') {
                        $q->where(function ($query) use ($credentialRefTable) {
                            $query->where("$credentialRefTable.status_monitoring", 'in_progress')
                                ->orWhereNull("$credentialRefTable.status_monitoring")
                                ->orWhere("$credentialRefTable.status_monitoring", '');
                        });
                    } else {
                        $q->where("$credentialRefTable.status_monitoring", $data['data']['check_monitoring']);
                    }
                }

                // ---------- CLICK_TYPE / CLICK_TYPE2 FILTER ----------
                $clickType = !empty($data['data']['click_type2']) ? $data['data']['click_type2'] : ($data['data']['click_type'] ?? null);

                if (!empty($clickType)) {
                    $ct = strtolower(trim($clickType));
                    $feedAlias = DB::getTablePrefix() . $feedTable;

                    if (in_array($ct, ['in_progress', 'reported', 'close'])) {
                        $q->where("$credentialRefTable.status_monitoring", 'LIKE', "%{$ct}%");
                    } elseif ($ct === 'social' || $ct === 'surface_web') {
                        $q->whereIn("$feedTable.feel_type", ['surface_web', 'social']);
                    } elseif ($ct === 'darkweb_public' || $ct === 'darkweb') {
                        $q->whereIn("$feedTable.feel_type", ['darkweb', 'darkweb_public', 'darkweb_private']);
                    } elseif (in_array($ct, ['mobile', 'facebook', 'line', 'twitter', 'website'])) {
                        $q->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ["%{$ct}%"]);
                    } elseif ($ct === 'other') {
                        $q->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%mobile%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%line%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%twitter%']);
                    } elseif (in_array($ct, ['website_s', 'social_s', 'community_s'])) {
                        $q->whereIn("$feedTable.feel_type", ['surface_web', 'social']);
                        if ($ct === 'website_s') {
                            $q->where(function ($query) use ($feedAlias) {
                                $query->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.com%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.net%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.org%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.co.th%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.io%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.th%']);
                            })
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%line%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%telegram%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%instagram%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%tiktok%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%youtube%']);
                        } elseif ($ct === 'social_s') {
                            $q->where(function ($query) use ($feedAlias) {
                                $query->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%twitter%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%facebook%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%line%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%telegram%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%instagram%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%tiktok%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%youtube%']);
                            });
                        } elseif ($ct === 'community_s') {
                            $q->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.com%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.net%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.org%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.th%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%line%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                        }
                    } elseif (in_array($ct, ['website_d', 'social_d', 'community_d'])) {
                        $q->whereIn("$feedTable.feel_type", ['darkweb', 'darkweb_public', 'darkweb_private']);
                        if ($ct === 'website_d') {
                            $q->where(function ($query) use ($feedAlias) {
                                $query->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.com%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.net%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.org%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.onion%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%.io%']);
                            })
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%line%'])
                            ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                        } elseif ($ct === 'social_d') {
                            $q->where(function ($query) use ($feedAlias) {
                                $query->whereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%twitter%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%facebook%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%line%'])
                                    ->orWhereRaw("LOWER(`{$feedAlias}`.`source_name`) LIKE ?", ['%telegram%']);
                            });
                        } elseif ($ct === 'community_d') {
                            $q->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.com%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.net%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.org%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%.onion%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                                ->whereRaw("LOWER(`{$feedAlias}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                        }
                    }
                }

                // Clone query for counting types
                $countCredential = (clone $qCounts)->count(); // Total
                $countSocial     = (clone $qCounts)->whereIn("$feedTable.feel_type", ['surface_web', 'social'])->count();
                $countDarkweb    = (clone $qCounts)->whereIn("$feedTable.feel_type", ['darkweb', 'darkweb_public', 'darkweb_private'])->count();

                $response = [
                    "credential" => $countCredential,
                    "social"     => $countSocial,
                    "darkweb"    => $countDarkweb,
                ];

                $data_transcation = json_encode($response);
                $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );
            return response()->json($response);
        }
    }

    public function dataleak_count_icon(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                $authenticatedSiteId = (int)($data['site']['data']['id'] ?? 0);
                $get_role = $data['data']['get_role_custom_first'] ?? [];
                $isSuperAdmin = (isset($get_role['superadmin']) && (int)$get_role['superadmin'] == 1);
                $isClientOrSiteClient = (
                    (isset($get_role['client']) && (int)$get_role['client'] == 1) ||
                    (isset($get_role['site_client']) && (int)$get_role['site_client'] == 1)
                );

                $site_ids = [];
                if ($isSuperAdmin && isset($get_role['site_id_arr'])) {
                    $site_ids = collect($get_role['site_id_arr'])
                        ->pluck('site_id')
                        ->filter()
                        ->values()
                        ->toArray();
                }

                $feedTable = 'data_leak_feed';
                $socialRefTable = 'data_leak_socail_ref';
                $siteTable = 'site';
                $f = $feedTable;
                $r = $socialRefTable;
                $prefix = DB::getTablePrefix();
                $f_raw = $prefix . $feedTable;
                $r_raw = $prefix . $socialRefTable;

                // ---------- SELECTED SITE ----------
                $selectedSiteId = null;
                if (!empty($data['data']['site_id'])) {
                    if ($s = SiteSettings::where('code', $data['data']['site_id'])->first()) {
                        $selectedSiteId = (int) $s->id;
                    }
                }

                // ---------- QUERY BASE ----------
                $q = DB::table($socialRefTable)
                    ->join($feedTable, "{$r}.data_leak_feed_id", '=', "{$f}.id")
                    ->whereIn("{$f}.feel_type", ['social', 'darkweb_public', 'surface_web', 'darkweb'])
                    ->whereNull("{$r}.deleted_at")
                    ->when($isClientOrSiteClient, fn($query) => $query->where("{$f}.status", 1));

                if (!empty($selectedSiteId)) {
                    $q->where("{$r}.site_id", $selectedSiteId);
                } elseif (!empty($site_ids)) {
                    $q->whereIn("{$r}.site_id", $site_ids);
                } elseif (!empty($get_role['site_id'])) {
                    $q->where("{$r}.site_id", (int)$get_role['site_id']);
                } elseif (!$isSuperAdmin && $authenticatedSiteId > 0) {
                    $q->where("{$r}.site_id", $authenticatedSiteId);
                }

                // ---------- DATE FILTER ----------
                if (isset($data['data']['isDateSearch']) && (int)$data['data']['isDateSearch'] === 1 && @$data['data']['startDate'] && @$data['data']['endDate']) {
                    $start = Carbon::parse($data['data']['startDate'])->startOfDay()->toDateTimeString();
                    $end   = Carbon::parse($data['data']['endDate'])->endOfDay()->toDateTimeString();
                    $q->whereRaw("`{$f_raw}`.`feedtimepost` IS NOT NULL AND CAST(`{$f_raw}`.`feedtimepost` AS DATETIME) BETWEEN ? AND ?", [$start, $end]);
                }

                // ---------- KEYWORDS FILTER ----------
                if (!empty($data['data']['keywords'])) {
                    $keywords = $data['data']['keywords'];
                    $q->where(function ($query) use ($keywords, $f_raw) {
                        $query->whereRaw("LOWER({$f_raw}.keyword) LIKE ?", ['%' . strtolower($keywords) . '%'])
                            ->orWhereRaw("LOWER({$f_raw}.feedcontent) LIKE ?", ['%' . strtolower($keywords) . '%']);
                    });
                }

                // ---------- FILTERS (Matching SocialController) ----------
                if (!empty($data['data']['check_type'])) {
                    $checkType = strtolower(trim($data['data']['check_type']));
                    if ($checkType === 'surface_web') {
                        $q->whereRaw("LOWER({$f_raw}.feel_type) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')");
                    } elseif ($checkType === 'darkweb') {
                        $q->whereRaw("LOWER({$f_raw}.feel_type) IN ('darkweb', 'darkweb_public', 'darkweb_private')");
                    }
                }

                if (!empty($data['data']['check_social'])) {
                    $checkSocial = strtolower(trim($data['data']['check_social']));
                    if ($checkSocial === 'website') {
                        $q->where(function ($query) use ($f_raw) {
                            $query->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.com%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.net%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.org%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.co.th%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.io%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.th%']);
                        })
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%line%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%instagram%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%tiktok%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%youtube%']);
                    } elseif ($checkSocial === 'social') {
                        $q->where(function ($query) use ($f_raw) {
                            $query->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%twitter%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%facebook%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%line%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%telegram%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%instagram%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%tiktok%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%youtube%']);
                        });
                    } elseif ($checkSocial === 'community') {
                        $q->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.com%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.net%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.org%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.th%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%line%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                    }
                }

                if (!empty($data['data']['check_darkweb'])) {
                    $checkDarkweb = strtolower(trim($data['data']['check_darkweb']));
                    if ($checkDarkweb === 'website') {
                        $q->where(function ($query) use ($f_raw) {
                            $query->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.com%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.net%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.org%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.onion%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.io%']);
                        })
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%line%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                    } elseif ($checkDarkweb === 'social') {
                        $q->where(function ($query) use ($f_raw) {
                            $query->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%twitter%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%facebook%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%line%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%telegram%']);
                        });
                    } elseif ($checkDarkweb === 'community') {
                        $q->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.com%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.net%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.org%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.onion%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                    }
                }

                if (!empty($data['data']['check_serverity'])) {
                    $q->whereRaw("LOWER({$r_raw}.serverity) = ?", [strtolower(trim($data['data']['check_serverity']))]);
                }

                if (!empty($data['data']['check_monitoring'])) {
                    $checkMonitoring = strtolower(trim($data['data']['check_monitoring']));
                    if ($checkMonitoring === 'in_progress') {
                        $q->where(function ($query) use ($r_raw) {
                            $query->whereRaw("LOWER({$r_raw}.status_monitoring) = 'in_progress'")
                                ->orWhereRaw("{$r_raw}.status_monitoring IS NULL")
                                ->orWhereRaw("{$r_raw}.status_monitoring = ''");
                        });
                    } else {
                        $q->whereRaw("LOWER({$r_raw}.status_monitoring) = ?", [$checkMonitoring]);
                    }
                }

                // ---------- COUNTS ----------
                $base = clone $q;

                $icon_mobile = (clone $base)->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%mobile%'])->count();
                $icon_facebook = (clone $base)->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%facebook%'])->count();
                $icon_line = (clone $base)->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%line%'])->count();
                $icon_twitter = (clone $base)->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%twitter%'])->count();
                $icon_website = (clone $base)->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.com%'])
                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.net%'])
                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.org%'])->count();
                
                $icon_other = (clone $base)->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%mobile%'])
                    ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                    ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%line%'])
                    ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                    ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.com%'])
                    ->count();

                $baseSurface = (clone $base)->whereRaw("LOWER(`{$f_raw}`.`feel_type`) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')");
                
                $icon_website_s = (clone $baseSurface)->where(function ($query) use ($f_raw) {
                    $query->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.com%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.net%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.org%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.co.th%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.io%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.th%']);
                })
                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%line%'])
                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%'])
                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%instagram%'])
                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%tiktok%'])
                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%youtube%'])->count();

                $icon_social_s = (clone $baseSurface)->where(function ($query) use ($f_raw) {
                    $query->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%twitter%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%facebook%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%line%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%telegram%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%instagram%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%tiktok%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%youtube%']);
                })->count();

                $icon_community_s = (clone $baseSurface)->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.com%'])
                    ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.net%'])
                    ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.org%'])
                    ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.th%'])
                    ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                    ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                    ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%line%'])
                    ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%'])->count();

                $baseDarkweb = (clone $base)->whereRaw("LOWER(`{$f_raw}`.`feel_type`) IN ('darkweb', 'darkweb_public', 'darkweb_private')");
                
                $icon_website_d = (clone $baseDarkweb)->where(function ($query) use ($f_raw) {
                    $query->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.com%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.net%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.org%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.onion%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.io%']);
                })
                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%line%'])
                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%'])->count();

                $icon_social_d = (clone $baseDarkweb)->where(function ($query) use ($f_raw) {
                    $query->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%twitter%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%facebook%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%line%'])
                        ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%telegram%']);
                })->count();

                $icon_community_d = (clone $baseDarkweb)->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.com%'])
                    ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.net%'])
                    ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.org%'])
                    ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.onion%'])
                    ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                    ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                    ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%'])->count();

                $number_in_progress = (clone $base)->where(function ($q) use ($r_raw) {
                    $q->whereRaw("LOWER({$r_raw}.status_monitoring) = 'in_progress'")
                        ->orWhereRaw("{$r_raw}.status_monitoring IS NULL")
                        ->orWhereRaw("{$r_raw}.status_monitoring = ''");
                })->count();
                $number_reported = (clone $base)->whereRaw("LOWER({$r_raw}.status_monitoring) = ?", ['reported'])->count();
                $number_close = (clone $base)->whereRaw("LOWER({$r_raw}.status_monitoring) = ?", ['close'])->count();

                $response = [
                    "icon_mobile"        => $icon_mobile,
                    "icon_facebook"      => $icon_facebook,
                    "icon_line"          => $icon_line,
                    "icon_twitter"       => $icon_twitter,
                    "icon_website"       => $icon_website,
                    "icon_other"         => $icon_other,
                    "icon_website_s"     => $icon_website_s,
                    "icon_social_s"      => $icon_social_s,
                    "icon_community_s"   => $icon_community_s,
                    "icon_website_d"     => $icon_website_d,
                    "icon_social_d"      => $icon_social_d,
                    "icon_community_d"   => $icon_community_d,
                    "number_in_progress" => $number_in_progress,
                    "number_reported"    => $number_reported,
                    "number_close"       => $number_close,
                ];
                $data_transcation = json_encode($response);
                $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'message' => $e->getMessage()]);
        }
    }

    public function dataleak_count_val(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                $authenticatedSiteId = (int)($data['site']['data']['id'] ?? 0);
                $get_role = $data['data']['get_role_custom_first'] ?? [];
                $isSuperAdmin = (isset($get_role['superadmin']) && (int)$get_role['superadmin'] == 1);
                $isClientOrSiteClient = (
                    (isset($get_role['client']) && (int)$get_role['client'] == 1) ||
                    (isset($get_role['site_client']) && (int)$get_role['site_client'] == 1)
                );

                $site_ids = [];
                if ($isSuperAdmin && isset($get_role['site_id_arr'])) {
                    $site_ids = collect($get_role['site_id_arr'])
                        ->pluck('site_id')
                        ->filter()
                        ->values()
                        ->toArray();
                }

                $feedTable = 'data_leak_feed';
                $socialRefTable = 'data_leak_socail_ref';
                $f = $feedTable;
                $r = $socialRefTable;
                $prefix = DB::getTablePrefix();
                $f_raw = $prefix . $feedTable;
                $r_raw = $prefix . $socialRefTable;

                // ---------- SELECTED SITE ----------
                $selectedSiteId = null;
                if (!empty($data['data']['site_id'])) {
                    if ($s = SiteSettings::where('code', $data['data']['site_id'])->first()) {
                        $selectedSiteId = (int) $s->id;
                    }
                }

                // ---------- QUERY BASE ----------
                $q = DB::table($socialRefTable)
                    ->join($feedTable, "{$r}.data_leak_feed_id", '=', "{$f}.id")
                    ->whereIn("{$f}.feel_type", ['social', 'darkweb_public', 'surface_web', 'darkweb'])
                    ->whereNull("{$r}.deleted_at")
                    ->when($isClientOrSiteClient, fn($query) => $query->where("{$f}.status", 1))
                    ->when($selectedSiteId, fn($query) => $query->where("{$r}.site_id", $selectedSiteId))
                    ->when(!$selectedSiteId && $isSuperAdmin && !empty($site_ids), fn($query) => $query->whereIn("{$r}.site_id", $site_ids))
                    ->when(!$selectedSiteId && !$isSuperAdmin && $authenticatedSiteId > 0, fn($query) => $query->where("{$r}.site_id", $authenticatedSiteId));

                // ---------- FILTERS ----------
                if (isset($data['data']['isDateSearch']) && (int)$data['data']['isDateSearch'] === 1 && @$data['data']['startDate'] && @$data['data']['endDate']) {
                    $start = Carbon::parse($data['data']['startDate'])->startOfDay()->toDateTimeString();
                    $end   = Carbon::parse($data['data']['endDate'])->endOfDay()->toDateTimeString();
                    $q->whereRaw("`{$f_raw}`.`feedtimepost` IS NOT NULL AND CAST(`{$f_raw}`.`feedtimepost` AS DATETIME) BETWEEN ? AND ?", [$start, $end]);
                }

                if (!empty($data['data']['keywords'])) {
                    $keywords = $data['data']['keywords'];
                    $q->where(function ($query) use ($keywords, $f_raw) {
                        $query->whereRaw("LOWER({$f_raw}.keyword) LIKE ?", ['%' . strtolower($keywords) . '%'])
                            ->orWhereRaw("LOWER({$f_raw}.feedcontent) LIKE ?", ['%' . strtolower($keywords) . '%']);
                    });
                }


                if (!empty($data['data']['check_serverity'])) {
                    $q->whereRaw("LOWER({$r_raw}.serverity) = ?", [strtolower(trim($data['data']['check_serverity']))]);
                }

                if (!empty($data['data']['check_monitoring'])) {
                    $checkMonitoring = strtolower(trim($data['data']['check_monitoring']));
                    if ($checkMonitoring === 'in_progress') {
                        $q->where(function ($query) use ($r_raw) {
                            $query->whereRaw("LOWER({$r_raw}.status_monitoring) = 'in_progress'")
                                ->orWhereRaw("{$r_raw}.status_monitoring IS NULL")
                                ->orWhereRaw("{$r_raw}.status_monitoring = ''");
                        });
                    } else {
                        $q->whereRaw("LOWER({$r_raw}.status_monitoring) = ?", [$checkMonitoring]);
                    }
                }

                // Clone query for dashboard counts before category/click filters
                $qCounts = clone $q;

                // ---------- CATEGORY FILTERS ----------
                if (!empty($data['data']['check_type'])) {
                    $checkType = strtolower(trim($data['data']['check_type']));
                    if ($checkType === 'surface_web') {
                        $q->whereRaw("LOWER({$f_raw}.feel_type) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')");
                    } elseif ($checkType === 'darkweb') {
                        $q->whereRaw("LOWER({$f_raw}.feel_type) IN ('darkweb', 'darkweb_public', 'darkweb_private')");
                    }
                }

                if (!empty($data['data']['check_social'])) {
                    $checkSocial = strtolower(trim($data['data']['check_social']));
                    if ($checkSocial === 'website') {
                        $q->where(function ($query) use ($f_raw) {
                            $query->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.com%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.net%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.org%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.co.th%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.io%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.th%']);
                        })
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%line%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%instagram%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%tiktok%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%youtube%']);
                    } elseif ($checkSocial === 'social') {
                        $q->where(function ($query) use ($f_raw) {
                            $query->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%twitter%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%facebook%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%line%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%telegram%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%instagram%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%tiktok%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%youtube%']);
                        });
                    } elseif ($checkSocial === 'community') {
                        $q->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.com%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.net%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.org%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.th%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%line%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                    }
                }

                if (!empty($data['data']['check_darkweb'])) {
                    $checkDarkweb = strtolower(trim($data['data']['check_darkweb']));
                    if ($checkDarkweb === 'website') {
                        $q->where(function ($query) use ($f_raw) {
                            $query->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.com%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.net%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.org%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.onion%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.io%']);
                        })
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%line%'])
                        ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                    } elseif ($checkDarkweb === 'social') {
                        $q->where(function ($query) use ($f_raw) {
                            $query->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%twitter%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%facebook%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%line%'])
                                ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%telegram%']);
                        });
                    } elseif ($checkDarkweb === 'community') {
                        $q->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.com%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.net%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.org%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.onion%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                    }
                }

                // ---------- DATA TYPE FILTER (click_type / click_type2) ----------
                $clickTypeVal = !empty($data['data']['click_type2']) ? $data['data']['click_type2'] : ($data['data']['click_type'] ?? null);

                if (!empty($clickTypeVal)) {
                    $ct = strtolower(trim($clickTypeVal));

                    if (in_array($ct, ['reported', 'in_progress', 'close'])) {
                        if ($ct === 'in_progress') {
                            $q->where(function ($query) use ($r_raw) {
                                $query->whereRaw("LOWER({$r_raw}.status_monitoring) = 'in_progress'")
                                    ->orWhereRaw("{$r_raw}.status_monitoring IS NULL")
                                    ->orWhereRaw("{$r_raw}.status_monitoring = ''");
                            });
                        } else {
                            $q->whereRaw("LOWER({$r_raw}.status_monitoring) = ?", [$ct]);
                        }
                    } elseif ($ct === 'social' || $ct === 'surface_web') {
                        $q->whereRaw("LOWER({$f_raw}.feel_type) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')");
                    } elseif ($ct === 'darkweb_public' || $ct === 'darkweb') {
                        $q->whereRaw("LOWER({$f_raw}.feel_type) IN ('darkweb', 'darkweb_public', 'darkweb_private')");
                    } elseif (in_array($ct, ['website_s', 'social_s', 'community_s'])) {
                        $q->whereRaw("LOWER({$f_raw}.feel_type) NOT IN ('darkweb', 'darkweb_public', 'darkweb_private', 'compromise')");
                        if ($ct === 'website_s') {
                            $q->where(function ($query) use ($f_raw) {
                                $query->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.com%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.net%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.org%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.co.th%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.io%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.th%']);
                            })
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%line%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%instagram%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%tiktok%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%youtube%']);
                        } elseif ($ct === 'social_s') {
                            $q->where(function ($query) use ($f_raw) {
                                $query->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%twitter%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%facebook%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%line%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%telegram%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%instagram%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%tiktok%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%youtube%']);
                            });
                        } elseif ($ct === 'community_s') {
                            $q->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.com%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.net%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.org%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.th%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%line%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                        }
                    } elseif (in_array($ct, ['website_d', 'social_d', 'community_d'])) {
                        $q->whereRaw("LOWER({$f_raw}.feel_type) IN ('darkweb', 'darkweb_public', 'darkweb_private')");
                        if ($ct === 'website_d') {
                            $q->where(function ($query) use ($f_raw) {
                                $query->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.com%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.net%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.org%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.onion%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%.io%']);
                            })
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%line%'])
                            ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                        } elseif ($ct === 'social_d') {
                            $q->where(function ($query) use ($f_raw) {
                                $query->whereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%twitter%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%facebook%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%line%'])
                                    ->orWhereRaw("LOWER(`{$f_raw}`.`source_name`) LIKE ?", ['%telegram%']);
                            });
                        } elseif ($ct === 'community_d') {
                            $q->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.com%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.net%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.org%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%.onion%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%twitter%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%facebook%'])
                                ->whereRaw("LOWER(`{$f_raw}`.`source_name`) NOT LIKE ?", ['%telegram%']);
                        }
                    }
                }


                $baseQuery = clone $q;
                
                $countDarkweb = (clone $baseQuery)->whereIn("{$f}.feel_type", ['darkweb', 'darkweb_public'])->count();
                $countSocial  = (clone $baseQuery)->whereIn("{$f}.feel_type", ['social', 'surface_web'])->count();
                $countTotal   = (clone $baseQuery)->count();

                $response = [
                    'count'   => $countTotal,
                    'darkweb' => $countDarkweb,
                    'social'  => $countSocial,
                ];

                $data_transcation = json_encode($response);
                $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'message' => $e->getMessage()]);
        }
    }
}
