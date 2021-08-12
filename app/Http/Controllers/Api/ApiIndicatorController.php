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

use App\FXTechniques;
use App\Entities\OtxIndicatiorData;
use Illuminate\Http\Response;
use Modules\indicators\Entities\OTXtypeData;
use MongoDB\Client;

class ApiIndicatorController extends ApiController
{
    public function events_table(Request $request){      
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $draw = $data['data']['draw'];
                $row = (int)$data['data']['start'];
                $rowperpage = (int)$data['data']['length'];
                
                $order = $data['data']['order'];
                $dir = $data['data']['dir'];
                $url = $data['data']['url'];
                $industries = $data['data']['industries'];
                $groups = $data['data']['groups'];

                $start =  $row;

                $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                $clientMD = new MongoClient($DB_MONGO_KEY);
                $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
                
                
                $options = [
                    'projection' => [
                        '_id' => 0,
                        'industries' =>1,
                        'name' => 1,
                        'groups' => 1,
                        'tags' => 1,
                        'public' => 1,
                        'is_modified' => 1,
                        'modified' => 1,
                        'count_view' => 1,
                        'indicator_count' => 1,
                        'pulse_id' => 1,
                        
                    ],
                    'sort' => [
                        'modified' => -1
                    ],
                    // 'sort' => [
                    //     $order => $dir
                    // ],
                    'skip' => $start,
                    'limit' => $rowperpage,
                ];

                $query = array( 
                    'status' => 1,
                    'deleted_at' => null,
                );
                
                if($data['data']['count_page']==-1){
                    $cursor_count = $col_fx_otx_events->count($query);
                    $count_filter = $cursor_count;
                }else{
                    $cursor_count = $data['data']['count_page'];
                    $count_filter = $cursor_count;
                }

                    
                if($data['data']['keywords']||$data['data']['isDateSearch']||$data['data']['start_date']||$data['data']['end_date']||$data['data']['check_published']||$industries||$groups){
                
                    if ($data['data']['keywords']) {
                        $query['name'] = ['$regex'=>$data['data']['keywords'], '$options' => 'i'];
                        // $_search =  array_merge($_search, array('indicator' => ['$regex'=>$request->keywords, '$options' => 'i']));
                    } 
                    if ($industries) {
                        $query['industries'] = ['$regex'=>$industries, '$options' => 'i'];
                    }
                    if ($groups) {
                        $query['groups'] = ['$regex'=>$groups, '$options' => 'i'];
                    }

                    $isDateSearch = filter_var($data['data']['isDateSearch'], FILTER_VALIDATE_BOOLEAN);

                    if($isDateSearch){
                        if ($data['data']['startDate']&&$data['data']['endDate']) {
                            $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($data['data']['startDate'])*1000), '$lte' => new UTCDateTime(strtotime($data['data']['endDate'])*1000)];
                            // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                        }else if($data['data']['startDate']){
                            $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($data['data']['startDate'])*1000)];
                            // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000)] ) );
                        }else if($data['data']['endDate']){
                            $query['modified'] = ['$lte' => new UTCDateTime(strtotime($data['data']['endDate'])*1000)];
                            // $_search =  array_merge( $_search, array('updated_at' => ['$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                        }
                    }

                    if ($data['data']['check_published']) {
                        if($data['data']['check_published']==1){
                            $query['public'] = 1;
                        }else if($data['data']['check_published']==2){
                            $query['public'] = 0;
                        }
                    }
                    $cursor = $col_fx_otx_events->find($query,$options);
                    $count_filter = $col_fx_otx_events->count($query);
                } else {
                        $cursor = $col_fx_otx_events->find($query,$options);
                }

                $query['indicator_count'] = ['$ne' => 0];
                $cursor = $col_fx_otx_events->find($query,$options);
                $cursor = $cursor->toArray();

                $data_nestedData = array();
                $order_number = $start;
                if(!empty($cursor))
                {
                    foreach ($cursor as $document)
                    {
                            $order_number++;
                            $nestedData['No'] = $order_number;
                            $nestedData['name'] = $document["name"];
                            $nestedData['groups'] = $document["groups"] ? $this->explode_val($document["groups"],'groups',$url) : '';
                            $nestedData['tags'] = $this->explode_val($document["tags"],'tags',$url);
                            $nestedData['industries'] = $document["industries"] ? $this->explode_val($document["industries"],'industries',$url) : '';
                            $nestedData['attr'] = '';
                            $nestedData['attrCount'] = $document["indicator_count"];
                            $nestedData['public'] = ($document["public"]);
                            $nestedData['is_modified'] = ($document["is_modified"]);
                            $nestedData['modified'] = change_date_utc_to_thai($document['modified']);
                            $nestedData['count_view'] = @$document["count_view"];
                            $nestedData['pulse_id'] = $document["pulse_id"];
                        
                            // <a href="'.rou   te('indicators.events_detail_select',['id' => $document['pulse_id']]).'" 
                            // class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>
                            
                            //------------------------------------------------------
                            $DB_MONGO_KEY = env("DB_MONGO_DEV");
                            $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
                            if(app()->environment('local'))
                            {
                                $collection = $clientMD->sosecure_threatintelligent->fx_otx_adversaries;
                                $collection_related = $clientMD->sosecure_threatintelligent->fx_otx_adversaries_related;
                            }
                            else
                            {
                                $collection = $clientMD->sosecure_threatintelligent_test->fx_otx_adversaries;
                                $collection_related = $clientMD->sosecure_threatintelligent_test->fx_otx_adversaries_related;
                            }

                            $query_actor = [
                                'pulse_id' => $document['pulse_id'],
                                'mode' => 'indicator',
                                'join' => 'actor',
                                'delete_at' => null
                            ];
                            $option_actor = [];

                            $result_actor = $collection_related->find($query_actor,$option_actor);
                            $final_actor = $result_actor->toArray();
                            $count_actor = count($final_actor);

                            $nestedData['actor'] = $final_actor;
                            $nestedData['count_actor'] = $count_actor;

                            foreach(@$final_actor as $sel_data_act)
                            {
                                $query_sel_act = [
                                    'adversary_uuid' => $sel_data_act['adversary_uuid']
                                ];
                                $option_sel_act = [];
                                $result_sel_act = $collection->findOne($query_sel_act,$option_sel_act);
                                if(@$result_sel_act['logo'])
                                {
                                    $nestedData['logo'][] = $result_sel_act['logo'];
                                }
                                else
                                {
                                    $nestedData['logo'][] = '/asset_salepage/images/AgentBasedDetection.png';
                                }
                            }

                            $query_camp = [
                                'pulse_id' => $document['pulse_id'],
                                'mode' => 'indicator',
                                'join' => 'campainge',
                                'delete_at' => null
                            ];
                            $option_camp = [];

                            $result_camp = $collection_related->find($query_camp,$option_camp);
                            $final_camp = $result_camp->toArray();
                            $count_camp = count($final_camp);

                            $nestedData['camp'] = $final_camp;
                            $nestedData['count_camp'] = $count_camp;
                        
                        $data_nestedData[] = $nestedData;
                            
                    }
                }
                $dataOut["draw"] = $draw;
                $dataOut["recordsTotal"] = $cursor_count;
                $dataOut["recordsFiltered"] = $count_filter;
                $dataOut["data"] = $data_nestedData;
                $dataOut["cursor"] = $cursor;

                $data_transcation_jobs_clients = json_encode($dataOut);
                $datas = encrypt_decrypt('encrypt', $data_transcation_jobs_clients, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
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

    public function table_groups(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'indicators'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }


                    $draw = $data['data']['draw'];
                    $row = $data['data']['row'];
                    $rowperpage = $data['data']['rowperpage'];
                    $order = $data['data']['order'];
                    $dir = $data['data']['dir'];
                    $reqId = $data['data']['reqId'];
                    $count_page = $data['data']['count_page'];
                    $keywords = $data['data']['keywords'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $startDate = $data['data']['startDate'];
                    $endDate = $data['data']['endDate'];
                    $tags = $data['data']['tags'];
                    $industries = $data['data']['industries'];
                    $url = $data['data']['url'];
                    $start =  $row;

                    $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                    $clientMD = new MongoClient($DB_MONGO_KEY);
                    $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;

                    $query = array(
                        'groups' => new Regex('^.*'.$tags.'.*$', 'i'),
                        'status' => 1,
                        'deleted_at' => null,
                    );

                    $options = [
                        'projection' => [
                            '_id' => 0,
                            'name' => 1,
                            'groups' => 1,
                            'tags' => 1,
                            'industries' =>1,
                            'public' => 1,
                            'is_modified' => 1,
                            'modified' => 1,
                            'count_view' => 1,
                            'pulse_id' => 1,
                            'indicator_count' => 1,
                        ],
                        'sort' => [
                            $order => $dir
                        ],
                        'skip' => $start,
                        'limit' => $rowperpage,
                    ];



                    if($count_page==-1){
                        $cursor_count = $col_fx_otx_events->count($query);
                        $count_filter = $cursor_count;
                    }else{
                        $cursor_count = $count_page;
                        $count_filter = $cursor_count;
                    }

                    if($keywords||$isDateSearch || $industries)
                    {


                        if ($keywords) {
                            $query['name'] = ['$regex'=>$keywords, '$options' => 'i'];
                                // $_search =  array_merge($_search, array('indicator' => ['$regex'=>$keywords, '$options' => 'i']));
                        } 
                        if ($industries) {
                            $query['industries'] = ['$regex'=>$industries, '$options' => 'i'];
                        }

                        $isDateSearch = filter_var($isDateSearch, FILTER_VALIDATE_BOOLEAN);

                        if($isDateSearch){
                                    // dd($startDate);
                            if ($startDate&&$endDate) {

                                $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($startDate)*1000), '$lte' => new UTCDateTime(strtotime($endDate)*1000)];
                                        // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                            }else if($startDate){
                                $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($startDate)*1000)];
                                        // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000)] ) );
                            }else if($endDate){
                                $query['modified'] = ['$lte' => new UTCDateTime(strtotime($endDate)*1000)];
                                        // $_search =  array_merge( $_search, array('updated_at' => ['$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                            }
                        }
                        $cursor = $col_fx_otx_events->find($query,$options);
                        $count_filter = $col_fx_otx_events->count($query);

                    } else {
                        $cursor = $col_fx_otx_events->find($query,$options);
                    }



                    $cursor = $cursor->toArray();

                    $data_res = array();
                    $order_number = $start;
                    if(!empty($cursor))
                    {
                    foreach ($cursor as $document)
                    {


                        $order_number++;
                        $nestedData['No'] = $order_number;
                        $nestedData['name'] = $document["name"];
                        $nestedData['groups'] = $this->explode_val($document["groups"],'groups', $url);
                        $nestedData['tags'] = $this->explode_val($document["tags"],'tags', $url);
                        $nestedData['industries'] = $this->explode_val($document["industries"], null,$url);
                        $nestedData['attr'] = '';
                        $nestedData['attrCount'] = $document["indicator_count"];
                        $nestedData['public'] = ($document["public"]);
                        $nestedData['is_modified'] = ($document["is_modified"]);
                        $nestedData['modified'] = change_date_utc_to_thai($document['modified']);
                        $nestedData['count_view'] = @$document["count_view"];
                        $nestedData['pulse_id'] = $document["pulse_id"];

                                    // <a href="'.route('indicators.events_detail_select',['id' => $document['pulse_id']]).'" 
                                    // class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>


                        $data_res[] = $nestedData;

                    }
                    }
                    $dataOut["draw"] = $draw;
                    $dataOut["recordsTotal"] = $cursor_count;
                    $dataOut["recordsFiltered"] = $count_filter;
                    $dataOut["data"] = $data;
                    $dataOut["cursor"] = $cursor;
            

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

    public function table_tags(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'indicators'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }


                    $draw = $data['data']['draw'];
                    $row = $data['data']['row'];
                    $rowperpage = $data['data']['rowperpage'];
                    $order = $data['data']['order'];
                    $dir = $data['data']['dir'];
                    $reqId = $data['data']['reqId'];
                    $count_page = $data['data']['count_page'];
                    $keywords = $data['data']['keywords'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $startDate = $data['data']['startDate'];
                    $endDate = $data['data']['endDate'];
                    $tags = $data['data']['tags'];
                    $industries = $data['data']['industries'];
                    $url = $data['data']['url'];
                    $start =  $row;
            

                    $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                    $clientMD = new MongoClient($DB_MONGO_KEY);
                    $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
            
                    $query = array(
                        'tags' => new Regex('^.*'.$tags.'.*$', 'i'),
                        'status' => 1,
                        'deleted_at' => null,
                    );
            
                    $options = [
                        'projection' => [
                            '_id' => 0,
                            'name' => 1,
                            'groups' => 1,
                            'tags' => 1,
                            'industries' => 1,
                            'public' => 1,
                            'is_modified' => 1,
                            'modified' => 1,
                            'count_view' => 1,
                            'pulse_id' => 1,
                            'indicator_count' => 1,
                        ],
                        'sort' => [
                            $order => $dir
                        ],
                        'skip' => $start,
                        'limit' => $rowperpage,
                    ];
            
            
            
                    if($count_page==-1){
                        $cursor_count = $col_fx_otx_events->count($query);
                        $count_filter = $cursor_count;
                    }else{
                        $cursor_count = $count_page;
                        $count_filter = $cursor_count;
                    }
            
                    if($keywords||$isDateSearch || $industries)
                    {
            
            
                            if ($keywords) {
                                $query['name'] = ['$regex'=>$keywords, '$options' => 'i'];
                                    // $_search =  array_merge($_search, array('indicator' => ['$regex'=>$keywords, '$options' => 'i']));
                            } 
                            if ($industries) {
                                $query['industries'] = ['$regex'=>$industries, '$options' => 'i'];
                            }
                            $isDateSearch = filter_var($isDateSearch, FILTER_VALIDATE_BOOLEAN);
            
                        if($isDateSearch){
                                    // dd($startDate);
                            if ($startDate&&$endDate) {
            
                                $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($startDate)*1000), '$lte' => new UTCDateTime(strtotime($endDate)*1000)];
                                        // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                            }else if($startDate){
                                $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($startDate)*1000)];
                                        // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000)] ) );
                            }else if($endDate){
                                $query['modified'] = ['$lte' => new UTCDateTime(strtotime($endDate)*1000)];
                                        // $_search =  array_merge( $_search, array('updated_at' => ['$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                            }
                        }
                        $cursor = $col_fx_otx_events->find($query,$options);
                        $count_filter = $col_fx_otx_events->count($query);
            
                    } else {
                        $cursor = $col_fx_otx_events->find($query,$options);
                    }
            
            
            
                    $cursor = $cursor->toArray();
            
                    $data_res = array();
                    $order_number = $start;
                    if(!empty($cursor))
                    {
                        foreach ($cursor as $document)
                        {
            
            
                            $order_number++;
                            $nestedData['No'] = $order_number;
                            $nestedData['name'] = $document["name"];
                            $nestedData['groups'] = $this->explode_val($document["groups"],'groups', $url);
                            $nestedData['tags'] = $this->explode_val($document["tags"],'tags', $url);
                            $nestedData['industries'] = $this->explode_val($document["industries"], null,$url);
                            $nestedData['attr'] = '';
                            $nestedData['attrCount'] = $document["indicator_count"];
                            $nestedData['public'] = ($document["public"]);
                            $nestedData['is_modified'] = ($document["is_modified"]);
                            $nestedData['modified'] = change_date_utc_to_thai($document['modified']);
                            $nestedData['count_view'] = @$document["count_view"];
                            $nestedData['pulse_id'] = $document["pulse_id"];
            
                                        // <a href="'.route('indicators.events_detail_select',['id' => $document['pulse_id']]).'" 
                                        // class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>
            
            
                            $data_res[] = $nestedData;
            
                        }
                    }
                    $dataOut["draw"] = $draw;
                    $dataOut["recordsTotal"] = $cursor_count;
                    $dataOut["recordsFiltered"] = $count_filter;
                    $dataOut["data"] = $data_res;
                    $dataOut["cursor"] = $cursor;

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

    public function industries(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'indicators'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    
                    $Indicatorindustries = IndicatorSummaryYear::where('type', 'industries')->where('status', 1)->where('industries_name', '!=', null)->where('industries_name', '!=', '')->orderBy('order')->select('industries_name')->get();

                    $data_transcation = json_encode($Indicatorindustries);
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

    public function group(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'indicators'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    
                    $Indicatorgroups = IndicatorSummaryYear::where('type', 'groups')->where('status', 1)->where('industries_name', '!=', null)->where('industries_name', '!=', '')->orderBy('order')->select('industries_name')->get();

                    $data_transcation = json_encode($Indicatorgroups);
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

    public function adversaries(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'indicators'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    
                    $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
                    $client = new \MongoDB\Client($DB_MONGO_KEY);
                    $db_name = 'sosecure_threatintelligent';
                    $db = $client->$db_name;
                    $collection = $db->fx_otx_adversaries_related;
                    $where = array(
                        'pulse_id' => $data['data']['pulse_id'],
                    );
                    $options = [];
                    $cursor = $collection->find($where, $options); 

                    $data_transcation = json_encode($cursor->toArray());
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

    public function malware(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'indicators'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    
                    $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
                    $client = new \MongoDB\Client($DB_MONGO_KEY);
                    $db_name = 'sosecure_threatintelligent';
                    $db = $client->$db_name;
                    $collection = $db->fx_otx_malware_related;
                    $where = array(
                        'pulse_id' => $data['data']['pulse_id'],
                    );
                    $options = [];
                    $cursor = $collection->find($where, $options); 

                    $data_transcation = json_encode($cursor->toArray());
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

    public function show_detail_malware(Request $request)
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'indicators'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
                    $client = new \MongoDB\Client($DB_MONGO_KEY);
                    $db_name = 'sosecure_threatintelligent';
                    $db = $client->$db_name;
                    $collection = $db->fx_otx_malware;
                    $where = array(
                        'malware_uuid' => urldecode($data['data']['malware_uuid']),
                    );
                    $options = [];
                    $cursor = $collection->find($where, $options); 
                    $dataOut['detail_malware'] = $cursor->toArray();

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



    public function show_detail_adversary(Request $request)
    {
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'indicators'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
                    $client = new \MongoDB\Client($DB_MONGO_KEY);
                    $db_name = 'sosecure_threatintelligent';
                    $db = $client->$db_name;
                    $collection = $db->fx_otx_adversaries;
                    $where = array(
                        'adversary_uuid' => $data['data']['adversary_uuid'],
                    );
                    $options = [];
                    $cursor = $collection->find($where, $options); 
                    $dataOut['adversary'] = $cursor->toArray();

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


    public function load_relatedPulse(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $draw = $data['data']['draw'];
                $start = $data['data']['start'];
                $rowperpage = $data['data']['rowperpage'];
                $order = 'modified';
                $dir = -1;
                
                $reqId = $data['data']['reqId'];
                $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                $clientMD = new MongoClient($DB_MONGO_KEY);
                $html = '';
                $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;
        
        
                $query = [
                    'indicator_id' => $reqId,
                    
                ];
        
        
                $options = [
                    'sort' => [
                        // $order => $dir
                    ],
                    'skip' => $start,
                    'limit' => $rowperpage,
                ];
        
                if($data['data']['count_page']==-1){
                    $cursor_count = $col_fx_otx_events_indicator_ref->count($query);
                    $count_filter = $cursor_count;
                    // dd($cursor_count);
                }else{
                    $cursor_count = $data['data']['count_page'];
                    $count_filter = $cursor_count;
                }
                $cursor = $col_fx_otx_events_indicator_ref->find($query,$options);    
                $document_all = $cursor->toArray();
            
        
        
                $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
                $options = array(
                    'typeMap' => array(
                    'root' => 'array',
                    'document' => 'array',
                    ),
                );
        
                $data_send = array();
                $order_number = $start;
                
                if($document_all){
                    foreach ($document_all as  $value) {
                        $query = [
                            'pulse_id' => $value->pulse_id
                            
                        ];
                        $cursor_2 = $col_fx_otx_events->findOne($query,$options);
                    
                        //  dd($value);
                        $order_number++;
                        $nestedData['No'] = $order_number;
                        $nestedData['name'] = $cursor_2["name"];
                        $nestedData['groups'] = $this->explode_val($cursor_2["groups"],'groups',$data['data']['url']);
                        $nestedData['tags'] = $this->explode_val($cursor_2["tags"],'tags',$data['data']['url']);
                        $nestedData['public'] = ($cursor_2["public"]);
                        $nestedData['is_modified'] = ($cursor_2["is_modified"]);
                        $nestedData['attrCount'] = $cursor_2["indicator_count"];
                        $nestedData['modified'] = change_date_utc_to_thai($cursor_2['modified']);
                        $nestedData['count_view'] = @$cursor_2["count_view"];
                        $nestedData['pulse_id'] = $cursor_2["pulse_id"];
                        $data_send[] = $nestedData;
                    }
                }
        
                $keysort = array_column($data_send, $order);
                array_multisort($keysort, SORT_DESC, $data_send);
                
                $dataOut["draw"] = $draw;
                $dataOut["recordsTotal"] = $cursor_count;
                $dataOut["recordsFiltered"] = $count_filter;
                $dataOut["data"] = $data_send;
                $dataOut["cursor"] = $cursor;

                $data_transcation = json_encode($dataOut);
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

    public function events(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
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

                $data_send["attr_all"] = IndicatorSummaryYear::where("type",'summary_all')->first();
                $data_send["attr_current"] = IndicatorSummaryYear::where("type",'summary_current')->first();
                // DB::raw('CONCAT("[",attribute_count, "]") as data2')
                $dataForloop = IndicatorSummaryYear::select('type_name AS name','attribute_count AS data')->where("type",'summary_attr_type')->orderBy('attribute_count','desc')->take(10)->get();
                $data_send["attr_type"] = array();
                foreach ($dataForloop as $document) {
                    array_push($data_send["attr_type"], array('name'=>ucwords($document->name),'data'=>[$document->data]));
                }
                $data_send['SiteSettings'] = $SiteSettings;
                $data_send['page'] = langapp('indicators');
                if(isset($data_send['data']['Search_Link_All'])){
                    $data_send['Search_Link_All'] = $data['data']['Search_Link_All'];
                }else{
                    $data_send['Search_Link_All'] = "";
                }

                $data_transcation = json_encode($data_send);
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

    public function events_detail_select(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $client = new MongoClient(DB_MONGO_01);
                $collection = $client->sosecure_threatintelligent->fx_otx_events;
                $id = $data['data']['id'];
                $query = [
                    'pulse_id' => $id
                ];

                $options = [
                    'limit' => 1
                ];

                $cursor = $collection->find($query, $options)->toArray();
                // $cursor[0]->created_at = change_date_utc_to_thai(@$cursor[0]->created_at);
                // $cursor[0]->modified = change_date_utc_to_thai(@$cursor[0]->modified);
                $data_transcation = json_encode($cursor);
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

    public function events_load_attributes_tb(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $draw = $data['data']['draw'];
                $row = (int)$data['data']['row'];
                $rowperpage = (int)$data['data']['rowperpage'];
                $reqId = $data['data']['reqId'];
                $count_page = $data['data']['count_page'];
                $url = $data['data']['url'];
                $start =  $row;

                $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                $clientMD = new MongoClient($DB_MONGO_KEY);
                $html = '';
                $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;
                
                $query = [
                    'pulse_id' => $reqId,
                    'type' => [
                        '$exists' => true,
                        '$ne' => null
                    ],
                ];
        
                $options = [
                    'skip' => $start,
                    'limit' => $rowperpage,
                    'sort' => [
                        'updated_at' => -1,
                    ]
                ];
                
            if($count_page==-1){
                    $cursor_count =$data['data']['total_record'];;
                    $count_filter = $cursor_count;
            }else{
                    $cursor_count = $count_page;
                    $count_filter = $cursor_count;
            }
            
        
            
            $cursor = $col_fx_otx_events_indicator_ref->find($query,$options);    
            $document_all = $cursor->toArray();

            $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;
            $options = array(
                'typeMap' => array(
                    'root' => 'array',
                    'document' => 'array',
                ),
            );
             //   $data_result = array();
              //  foreach ($document_all as  $value) {
                //    $query = [
                //        'indicator_id' => $value->indicator_id
                        
                //    ];
                 //   $cursor_2 = $col_fx_otx_indicator_detail->findOne($query,$options);
                
                    //$join_fx_otx_indicator_detail[]=  array("a"=>$value,"b"=>$cursor_2);
                    // $view = '<a href="'.route('indicators.detail_indicator').
                    //         '?id='.$document['b']['indicator_id'].'&type='.$document['b']['type'].'&indicator='.$document['b']['indicator_name'].'" 
                    //         class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>'; 
                  //  $data_result[] = array( 
                   //     "TYPE"=>@$cursor_2['type'],
                  //      "AttributeName"=>@$cursor_2['indicator_name'],
                   //     "ROLE"=>@$value['role'],
                  //      "Date"=>(isset($value['created'])?change_date_utc_to_thai($value['created']):""),
                  //      "Action"=>$url."?id=".@$cursor_2['indicator_id'].
                    //            '&type='.@$cursor_2['type'].'&indicator='.@$cursor_2['indicator_name']
                        
                  //  );
        
              //  }
                
                $total_record = $cursor_count;
                $total_count_filter = $count_filter;
            
        
                $dataOut["draw"] = $draw;
                $dataOut["recordsTotal"] = $cursor_count;
                $dataOut["recordsFiltered"] = $total_count_filter;
                $dataOut["data"] = $document_all;

                $data_transcation = json_encode($dataOut);
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

    
    public function events_load_pulse_tb(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $draw = $data['data']['draw'];
                $row = (int)$data['data']['row'];
                $rowperpage = (int)$data['data']['rowperpage'];
                $reqId = $data['data']['reqId'];
                $count_page = $data['data']['count_page'];
                $start =  $row;
                $url = $data['data']['url'];
                $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                $clientMD = new MongoClient($DB_MONGO_KEY);
                $html = '';
                $fx_otx_events_event_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_event_ref;
                
                $query = [
                    'main_pulse_id' => $reqId,
                    
                ];

                $options = [
                    'skip' => $start,//10
                    'limit' => $rowperpage//5
                ];

                if($count_page==-1){
                    $cursor_count = $fx_otx_events_event_ref->count($query); 
                    $count_filter = $cursor_count;
                }else{
                    $cursor_count = $count_page;
                    $count_filter = $cursor_count;
                }
                
            
                
                $cursor = $fx_otx_events_event_ref->find($query,$options);       
                $document_all = $cursor->toArray();
                    
                    // set_time_limit(500); 
                
                    // $count_doc = count($document_all);
                    
                $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
                $options = array(
                    'typeMap' => array(
                        'root' => 'array',
                        'document' => 'array',
                    ),
                );
                $data_res = array();
                $order_number = $start;

                if($document_all){
                    foreach ($document_all as  $value) {
                        $query = [
                            'pulse_id' => $value->pulse_id     
                        ];
                        $document = $col_fx_otx_events->findOne($query,$options);
                        
                        $order_number++;
                        $nestedData['No'] = $order_number;
                        $nestedData['name'] = $document["name"];
                        $nestedData['groups'] = $this->explode_val($document["groups"],'groups',$url);
                        $nestedData['tags'] = $this->explode_val($document["tags"],'tags',$url);
                        $nestedData['attr'] = '';
                        $nestedData['attrCount'] = $document["indicator_count"];
                        $nestedData['public'] = ($document["public"]);
                        $nestedData['is_modified'] = ($document["is_modified"]);
                        $nestedData['modified'] = change_date_utc_to_thai($document['modified']);
                        $nestedData['count_view'] = @$document["count_view"];
                        $nestedData['pulse_id'] = $document["pulse_id"];

                        $data_res[] = $nestedData;
                            
                    }
                }
                
                $dataOut["draw"] = $draw;
                $dataOut["recordsTotal"] = $cursor_count;
                $dataOut["recordsFiltered"] = $count_filter;
                $dataOut["data"] = $data_res;
                $dataOut["cursor"] = $cursor;

                $data_transcation = json_encode($dataOut);
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

    public function events_count_view(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                $clientMD = new MongoClient($DB_MONGO_KEY);
                $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_events;
                $options = array(
                    'typeMap' => array(
                        'root' => 'array',
                        'document' => 'array',
                    ),
                );
                $document = $col_fx_otx_indicator_detail->findOne(array('pulse_id' => $data['data']['pulse_id']),$options);
                if($document){
                    $update_fx_otx_events_indicator_ref = $col_fx_otx_indicator_detail->updateOne(
                        ['_id' => $document['_id']],
                        ['$set' => [
                            'count_view' => $document['count_view']+1
                            ]
                        ]
                    );
                }
                
                $data_res = [
                    "count" => $document['count_view']+1,
                ];

                $data_transcation = json_encode($data_res);
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

    public function load_adversary_tb(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'indicators'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }


                    $draw = $data['data']['draw'];
                    $row = $data['data']['row'];
                    $rowperpage = $data['data']['rowperpage'];
                    $start =  $row;
                    $reqId = $data['data']['reqId'];
        
                    $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                    $clientMD = new MongoClient($DB_MONGO_KEY);
                    $html = '';
                    $fx_otx_events_event_ref = $clientMD->sosecure_threatintelligent->fx_otx_adversaries_related;
        
                    $query = [
                        'adversary_uuid' => $reqId,
        
                    ];
        
                    $options = [
                    'skip' => $start,//10
                    'limit' => $rowperpage//5
                    ];
            
                    if($request->count_page==-1){
                        $cursor_count = $fx_otx_events_event_ref->count($query); 
                        $count_filter = $cursor_count;
                    }else{
                        $cursor_count = $request->count_page;
                        $count_filter = $cursor_count;
                    }
                    
            
                    
                    $cursor = $fx_otx_events_event_ref->find($query,$options);       
                    $document_all = $cursor->toArray();
            
                        // set_time_limit(500); 
                    
                        // $count_doc = count($document_all);
            
                    $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
                    $options = array(
                        'typeMap' => array(
                            'root' => 'array',
                            'document' => 'array',
                        ),
                    );
                    $data_s = array();
                    $order_number = $start;
            
                    if($document_all){
                        foreach ($document_all as  $value) {
                            $query = [
                                'pulse_id' => $value->pulse_id     
                            ];
                            $document = $col_fx_otx_events->findOne($query,$options);
                            
                            $order_number++;
                            $nestedData['No'] = $order_number;
                            $nestedData['name'] = $document["name"];
                            $nestedData['groups'] = explode_val($document["groups"],'groups');
                            $nestedData['tags'] = explode_val($document["tags"],'tags');
                            $nestedData['attr'] = '';
                            $nestedData['attrCount'] = $document["indicator_count"];
                            $nestedData['public'] = ($document["public"]);
                            $nestedData['is_modified'] = ($document["is_modified"]);
            
                            try {
                                $nestedData['modified'] =@$document['modified'];
                            } catch (Exception $e) {
                                $nestedData['modified'] =$document['modified'];
                            } finally {
                                $nestedData['modified'] =$document['modified'];
                            }
            
            
                            $nestedData['count_view'] = @$document["count_view"];
                            $nestedData['pulse_id'] = @$document["pulse_id"];
            
                            $data_s[] = $nestedData;
            
                        }
                    }
                    
                    $dataOut["draw"] = $draw;
                    $dataOut["recordsTotal"] = $cursor_count;
                    $dataOut["recordsFiltered"] = $count_filter;
                    $dataOut["data"] = $data_s;
                    $dataOut["cursor"] = $cursor;

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

    public function load_malware_tb(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'indicators'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }


                    $draw = $data['data']['draw'];
                    $row = $data['data']['row'];
                    $rowperpage = $data['data']['rowperpage'];
                    $start =  $row;
                    $reqId = $data['data']['reqId'];
        
                    $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                    $clientMD = new MongoClient($DB_MONGO_KEY);
                    $html = '';
                    $fx_otx_events_event_ref = $clientMD->sosecure_threatintelligent->fx_otx_malware_related;
        
                    $query = [
                        'malware_uuid' => $reqId,
        
                    ];
        
                    $options = [
                    'skip' => $start,//10
                    'limit' => $rowperpage//5
                    ];
            
                    if($request->count_page==-1){
                        $cursor_count = $fx_otx_events_event_ref->count($query); 
                        $count_filter = $cursor_count;
                    }else{
                        $cursor_count = $request->count_page;
                        $count_filter = $cursor_count;
                    }
                    
            
                    
                    $cursor = $fx_otx_events_event_ref->find($query,$options);       
                    $document_all = $cursor->toArray();
            
                        // set_time_limit(500); 
                    
                        // $count_doc = count($document_all);
            
                    $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
                    $options = array(
                        'typeMap' => array(
                            'root' => 'array',
                            'document' => 'array',
                        ),
                    );
                    $data_s = array();
                    $order_number = $start;
            
                    if($document_all){
                        foreach ($document_all as  $value) {
                            $query = [
                                'pulse_id' => $value->pulse_id     
                            ];
                            $document = $col_fx_otx_events->findOne($query,$options);
                            
                            $order_number++;
                            $nestedData['No'] = $order_number;
                            $nestedData['name'] = $document["name"];
                            $nestedData['groups'] = explode_val($document["groups"],'groups');
                            $nestedData['tags'] = explode_val($document["tags"],'tags');
                            $nestedData['attr'] = '';
                            $nestedData['attrCount'] = $document["indicator_count"];
                            $nestedData['public'] = ($document["public"]);
                            $nestedData['is_modified'] = ($document["is_modified"]);
            
                            try {
                                $nestedData['modified'] =@$document['modified'];
                            } catch (Exception $e) {
                                $nestedData['modified'] =$document['modified'];
                            } finally {
                                $nestedData['modified'] =$document['modified'];
                            }
            
            
                            $nestedData['count_view'] = @$document["count_view"];
                            $nestedData['pulse_id'] = @$document["pulse_id"];
            
                            $data_s[] = $nestedData;
            
                        }
                    }
                    
                    $dataOut["draw"] = $draw;
                    $dataOut["recordsTotal"] = $cursor_count;
                    $dataOut["recordsFiltered"] = $count_filter;
                    $dataOut["data"] = $data_s;
                    $dataOut["cursor"] = $cursor;

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
