<?php

namespace App\Http\Controllers\Api;

use App\Bookmark;
use App\DataLeakFeed;
use App\DataLeakFeedTemp;
use App\DataLeakSocialRef;
use App\Entities\IndicatorSummaryYear;
use App\leak_socail_ref_temp;
use App\Log;
use App\R_s_s_news;
use App\ReadCategories;
use App\ReadNews;
use App\TransactionTimeStampScans;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

class ApiGetMongoDB extends ApiController
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
            


                $start =  $row;

                $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                $clientMD = new MongoClient($DB_MONGO_KEY);
                $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
                
                
                $options = [
                    'projection' => [
                        '_id' => 0,
                        
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
                        $order => $dir
                    ],
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

                    
                if($data['data']['keywords']||$data['data']['isDateSearch']||$data['data']['start_date']||$data['data']['end_date']||$data['data']['check_published']){
                
                    if ($data['data']['keywords']) {
                        $query['name'] = ['$regex'=>$data['data']['keywords'], '$options' => 'i'];
                        // $_search =  array_merge($_search, array('indicator' => ['$regex'=>$request->keywords, '$options' => 'i']));
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
                            $nestedData['groups'] = $this->explode_val($document["groups"],'groups',$url);
                            $nestedData['tags'] = $this->explode_val($document["tags"],'tags',$url);
                            $nestedData['attr'] = '';
                            $nestedData['attrCount'] = $document["indicator_count"];
                            $nestedData['public'] = ($document["public"]);
                            $nestedData['is_modified'] = ($document["is_modified"]);
                            $nestedData['modified'] = change_date_utc_to_thai($document['modified']);
                            $nestedData['count_view'] = $document["count_view"];
                            $nestedData['pulse_id'] = $document["pulse_id"];
                        
                            // <a href="'.rou   te('indicators.events_detail_select',['id' => $document['pulse_id']]).'" 
                            // class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>
                            
                        
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
                        $nestedData['count_view'] = $cursor_2["count_view"];
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
                    
                ];
        
                $options = [
                    'skip' => $start,
                    'limit' => $rowperpage,
                    'sort' => [
                        'created' => -1,
                    ]
                ];
                
            if($count_page==-1){
                    $cursor_count = $col_fx_otx_events_indicator_ref->count($query);
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
                $data_result = array();
                foreach ($document_all as  $value) {
                    $query = [
                        'indicator_id' => $value->indicator_id
                        
                    ];
                    $cursor_2 = $col_fx_otx_indicator_detail->findOne($query,$options);
                
                    //$join_fx_otx_indicator_detail[]=  array("a"=>$value,"b"=>$cursor_2);
                    // $view = '<a href="'.route('indicators.detail_indicator').
                    //         '?id='.$document['b']['indicator_id'].'&type='.$document['b']['type'].'&indicator='.$document['b']['indicator_name'].'" 
                    //         class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>'; 
                    $data_result[] = array( 
                        "TYPE"=>@$cursor_2['type'],
                        "AttributeName"=>@$cursor_2['indicator_name'],
                        "ROLE"=>@$value['role'],
                        "Date"=>(isset($value['created'])?change_date_utc_to_thai($value['created']):""),
                        "Action"=>$url."?id=".@$cursor_2['indicator_id'].
                                '&type='.@$cursor_2['type'].'&indicator='.@$cursor_2['indicator_name']
                        
                    );
        
                }
                
                $total_record = $cursor_count;
                $total_count_filter = $count_filter;
            
        
                $dataOut["draw"] = $draw;
                $dataOut["recordsTotal"] = $cursor_count;
                $dataOut["recordsFiltered"] = $total_count_filter;
                $dataOut["data"] = $data_result;

                $data_transcation = json_encode($dataOut);
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
                        $nestedData['count_view'] = $document["count_view"];
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
            return response()->json($response);
        }
    } 

    public function table_asset(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
                $dataArr =[
                    'file'           => 'Api/asset',
                    'error_summary'  => 'The request parameters are invalid 400 Site : ' . @$data['site']['data']['name'],
                ];
                Log::create($dataArr);
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
            
            $dataArr =[
                'file'           => 'Api/asset',
                'error_summary'  => $e -> getMessage() .' Site : '. @$data['site']['data']['name'],
            ];
            Log::create($dataArr);
            return response()->json($response);
        }
    }

    public function index_client(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                if($auth_site['status_code'] !== '200'){
                    return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                }

                $RSSNews_count = RSSNews::where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now())->count();
                // dd($news_all);
                // $RSSNews_count = RSSNews::count("id");
                $RSSNews_all = RSSNews::all();
        
                //<><><div>
                // if(Auth::check()) {
        
                //     $site_id_arr = UserSite::select('site_id')->where('user_id', @$user_id)->get();
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
                
        
        
                // dd($RSSNews_all[0]->get_cate);
                // dd($RSSNews_all[0]->get_cate[0]->get_cate_name->name);
                // dd($RSSNews_count);
            $Category = CategorySettings::where('active', 1)->get();
            $NewsCategory = [];
            $ReadCategories = [];
        
            if(!empty($Category)){
                foreach($Category as $item){
                    $NewsCategory[] = RSSNewsCategory::where('news_category_id', @$item->id)->wherehas('news', function($q){
                        $q->where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now());
                    })->first();
                }
            } 
            if(!empty($NewsCategory)){
                    foreach($NewsCategory as $item){
                        $ReadCategories[] = ReadCategories::where('categories_id', '=',@$item['news_category_id'])->where('news_id', '=',@$item['rss_news_id'])->first();
                    }
            }
        
            $RSSNews_all = $RSSNews_all;
            $RSSNews_count = $RSSNews_count;
            $page = langapp('news');
            $Category = $Category;
            $NewsCategory = $NewsCategory;
            $ReadCategories = $ReadCategories;

            $dataOut = [
                'RSSNews_all' => $RSSNews_all,
                'RSSNews_count' => $RSSNews_count,
                'page' => $page,
                'Category' => $Category,
                'NewsCategory' => $NewsCategory,
                'ReadCategories' => $ReadCategories,
            ];

                $data_transcation = json_encode($dataOut);
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

    public function jqueryLoadMoreNews(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'news'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $title = $data['data']['title'];
                    $cate = $data['data']['cate'];
                    $related_news = $data['data']['related_news'];
                    $lang_th = $data['data']['lang_th'];
                    $lang_en = $data['data']['lang_en'];
                    $date_start = $data['data']['date_start'];
                    $date_end = $data['data']['date_end'];
                    $f_search = $data['data']['f_search'];
                    $site_code = $data['data']['site_code'];
                    $user_id = $data['data']['user_id'];
                    $page = $data['data']['page'];
                    $url = $data['data']['url'];

                    $site_id = '';
        
                    if($site_code) {
                        $site_id_m = SiteSettings::where('code',$site_code)->first();
                        $site_id = @$site_id_m->id;
                    }
        
                    $date_start_explode = explode(" ",$date_start);
                    $date_start_date = @$date_start_explode[0];
                    $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                    // dd($date_start_time);
                    $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                    // dd($date_start_date_format);
                    $date_start_time_time = date("H:i", strtotime($date_start_time));
                    $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                    // dd($date_start_time_time);
        
                    $date_end_explode = explode(" ",$date_end);
                    $date_end_date = @$date_end_explode[0];
                    $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                    // dd($date_end_time);
                    $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                    $date_end_time_time = date("H:i", strtotime($date_end_time));
                    $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
                    // dd($date_end_time_time);
        
                    // date("H:i", strtotime("04:25 PM"))
                    $html = '';
        
                    $news_all = RSSNews::where('save_draft',0)->orwhere('save_draft',null)->where('status', 1)->where('public_date', '<=', Carbon::now());
                    $news_all = $news_all->get()->count();
                    // $news_all = $news_all->where(function($q) ) {
                    //     $q->where('save_draft',1);
                    // }
        
                    // $news_all = $news_all->where(function ($query) {
                    //     $query->orwhere('save_draft', 0);
                    // });
        
                    // $news_all->get()->count();
                    // dd($news_all->get()->count());
        
                    if(($title || $cate || $related_news || $lang_th || $lang_en || $date_start || $date_end) && $f_search == 1){
        
                        $news = RSSNews::where(function ($query) {
                            $query->where('save_draft',  0)
                                ->orWhere('save_draft',  null);
                        })->where('status', 1)->where('public_date', '<=', Carbon::now());//->get() ->orderBy('created_at','desc')->paginate(10)  // selectRaw('*, count(id) as rss_new_count')
                        if($title){
                            $news = $news -> where('title_th', 'LIKE' ,'%'.$title.'%');
                        }
        
                        if($lang_th=='true' && $lang_en=='true') {
                            $news = $news -> where('title_th', 'LIKE' ,'%'.$title.'%');
                        } else if($lang_th || $lang_en) {
                            if($lang_th=='true') {
                                $news = $news -> where('title_th', 'LIKE' ,'%'.$title.'%');
                            } else if ($lang_en=='true') {
                                $news = $news -> where('title_en', 'LIKE' ,'%'.$title.'%');
                            }
                        } else {
                            if($title) {
                                $news = $news -> where('title_th', 'LIKE' ,'%'.$title.'%');
                            } else {
        
                            }
                            
                        }
                    
                        if($request ->site_id) {
        
                            $site_id = @$request ->site_id;
                            $news = $news->wherehas('get_site_news_related', function($q) use ($site_id) {
                                $q->where('site_id', $site_id)->where('deleted_at', null);
                            });
                        }
        
        
        
                        if($related_news == 'true') {
                            $site_id = @$request ->site_id;
                            $news = $news->wherehas('get_site_news_related', function($q) use ($site_id) {
                                $q->where('site_id', $site_id)->where('deleted_at', null);
                            });
        
                        }
        
                        if($cate) {
                            // dd($cate);
                            $cate_id_m = CategorySettings::where('code',$cate)->first();
                            $cate_id = @$cate_id_m->id;
                            // dd($cate_id);
                            $news = $news->whereHas('get_cate', function ($query) use ($cate_id) {
                                $query->where('news_category_id', '=', $cate_id);
                            });
        
                        }
        
                        if($date_start) {
                            // $news = $news -> whereDate('created_at','>', $date_start_datetime_format);
                            $news = $news -> whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format));
                        //     ->where(function($query) use ($date_start_datetime_format,$date_end_datetime_format){
                        //         $query->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))
                        //               ->whereBetween('time',array($timfrom,$timto));
                        //    })
        
                        }
        
                        if($date_end) {
        
                        }
        
                        // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
        
                        //  $news = $news->get();
                        // $news->orderBy('created_at','desc')->paginate(10);
                        // $news = RSSNews::where('save_draft', 0);//->get()
                        // $news -> paginate(10);//->get()
                        // $news = RSSNews::where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now())->orderBy('created_at','desc')->paginate(10);//->get()
                        // $news = $news->get();
                        // dd($news->get());
                        // dd($news);
                        // dd($news->total);
                        $news_all = $news->count();
                        // $news = $news->orderBy('created_at','desc')->paginate(PAGINATE_NUM);
                        
                        $news = $news->orderBy('created_at','desc')->skip($page == 2 ? $page * 10 : 0)->take(PAGINATE_NUM)->get();
                    }else{
                        $news = RSSNews::where(function ($query) {
                            $query->where('save_draft',  0)
                                ->orWhere('save_draft',  null);
                        })->where('status', 1)->where('public_date', '<=', Carbon::now());//->get()
        
                        if($request ->site_id) {
        
                            $site_id = @$request ->site_id;
                            $news = $news->wherehas('get_site_news_related', function($q) use ($site_id) {
                                $q->where('site_id', $site_id)->where('deleted_at', null);
                            });
                        }
                        $news_all = $news->count();
                        // $news = $news->orderBy('created_at','desc')->paginate(PAGINATE_NUM);
                        $news = $news->orderBy('created_at','desc')->skip($page == 2 ? $page * 10 : 0)->take(PAGINATE_NUM)->get();
                    }
        
                    // dd($news);
                    $content = [];
                    foreach($news as $item){
                        $related_news_site = '';
                        $icon_related= '';
                        $n_title = @$item -> title_th;
                        $n_detail = @$item -> detail_th;
                        if($site_id) {
                            $related_news_site = SiteNewsRelated::where("news_id",$item -> id)->where("site_id",$site_id)->first();
                        }
                        
                        if($related_news_site) {
                            $icon_related = '<i class="fas fa-newspaper"></i>';
                        } else {
                            $icon_related = '';
                        }
        
                        if($lang_th=='true' && $lang_en=='true') {
                            $n_title = $item -> title_th;
                            $n_detail = $item -> detail_th;
        
                            // $n_title = $data -> title_en;
                        } else if($lang_th=='true') {
                            if($lang_th=='true') {
                                $n_title = $item -> title_th;
                                $n_detail = $item -> detail_th;
                            } else if ($lang_en=='true') {
                                $n_title = $item -> title_en;
                                $n_detail = $item -> detail_en;
                            }
        
                            // $n_title = $data -> title_en;
                        } else if ($lang_en=='true') {
                            if ($lang_en=='true') {
                                $n_title = $item -> title_en;
                                $n_detail = $item -> detail_en;
                            } else if ($lang_th=='true') {
                                $n_title = $item -> title_th;
                                $n_detail = $item -> detail_th;
                            }
        
                            // $n_title = $item -> title_en;
                        } else {
                            if(@$item -> title_th) {
                                $n_title = $item -> title_th;
                                $n_detail = $item -> detail_th;
                            } else {
                                $n_title = $item -> title_en;
                                $n_detail = $item -> detail_en;
                            }
        
                            // $n_title = $item -> title_en;
            
                        }
        
                        // $n_detail = strip_tags($n_detail);
                        // dd($n_detail);
        
                        $content[] = strip_tags($n_detail);
                        // $content[] = $item -> detail_en;
                        // dd($content);
        
                        
        
        
                        $check_read_news = ReadNews::where('user_id', $user_id)->where('news_id', $item -> id)->first();
                        $checkBookmark = Bookmark::where('user_id', $user_id)->where('news_id', $item -> id)->first();
                        if($check_read_news){
                            $html .= '<div class="list-news">';
                            $font_weight = '';
                        }else{
                            $html .= '<div class="list-news" style="background-color:#ececec">';
                            $font_weight = 'font-weight: bold !important;';
                        }
        
                        if(@$item->transaction_rss_id) {
                            if(@$item->logo) {
                                $logo_url = config('app.URL_CENTER_PUBLISH').@$item->logo;
                            } else {
                                $logo_url = @$item->logo_rss;
                            }
                        } else {
                            $logo_url = config('app.URL_CENTER_PUBLISH').@$item->logo;
                        }
                        $html .= '
                            <!--<div class="checkbox-news-select">
                                <label class="mr-3">
                                    <input type="checkbox" name="" class="chk-bookmark">
                                    <span class="label-text checkbox-news-input"></span>
                                </label>
                            </div>-->
                            <div class="content-news-text">
                                <a href="'.$url.'/news/detail/'.$item -> code.'">
                                    <span class="head-news-text text-elip-ovf" style="'.@$font_weight.'">'.$icon_related.' '.$n_title.'</span>
                                </a>
                                <div class="entry-meta">
                                <span class="entry-view"> <i class="fas fa-eye"></i> '.$item -> view.'</span>
                                <span class="entry-date"> <i class="fas fa-calendar-alt"></i> '.$item -> public_date.'</span>
                                <span><p class="details-news-elip">&nbsp;'.strip_tags($n_detail).'</p></span>
                                </div>
                            </div>
                            <div class="content-news-image">
                                <a href="'.$url.'/news/detail/'.$item -> code.'">
                                    <img src="'.$logo_url.'" alt="" onerror="setDefaultPic(this)">
                                </a>
                            </div>
                            <div class="action-bookmark">';
                            if(!empty($checkBookmark)){
                                $html .= '<i class="fas fa-bookmark bookmark-active" id="mark'.$item -> id.'" onclick="Bookmarks(this, '.$item -> id.')"></i>';
                            }else{
                                $html .= '<i class="fas fa-bookmark" id="mark'.$item -> id.'" onclick="Bookmarks(this, '.$item -> id.')"></i>';
                            }
                                $html .= '</div>
                        </div>
                        ';
                    }
                    $dataOut = [
                        "html" => $html,
                        "count" => $news_all
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
            return response()->json($response);
        }
    }

    public function url_bookmark(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'news'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $user_id = $data['data']['user_id'];
                    $news_id = $data['data']['news_id'];

                    $checkBookmark = Bookmark::where('user_id', $user_id)->where('news_id', $news_id)->first();
                    if($checkBookmark){
                        $checkBookmark -> delete();
                    }else{
                        $Bookmark = new Bookmark();
                        $Bookmark -> code = generator_uuid();
                        $Bookmark -> user_id = $user_id;
                        $Bookmark -> news_id = $news_id;
                        $Bookmark -> save();
                    }

                    $dataOut = [
                        "data" => '',
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
            return response()->json($response);
        }
    }

    public function url_news_detail_code(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'news'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $code = $data['data']['code'];
                    $user_id = $data['data']['user_id'];
                    $RSSNews_prev = '';
                    $RSSNews_next = '';
                    $RSSNews_last10 = '';
                    $lang = 'th';
                    $RSSNews = RSSNews::where("code",$code)->with('get_cate')->first();

                    $cate_id_all = [];
                    if($RSSNews->get_cate) {
                        foreach($RSSNews->get_cate as $cate) {
                            $cate->get_cate_name->id;
                            $cate_id_all[] = intval($cate->get_cate_name->id);
                            // dd($cate->get_cate_name->id);
                        }
                    }
                    // dd($cate_id_all);


                    if($cate_id_all) {
                        $NewsCategory = RSSNewsCategory::whereIn('news_category_id', $cate_id_all)->where('status',1)->get();
                        // dd($NewsCategory);
                        
                        
                        $rss_news_id_array = [];
                        if($NewsCategory) {
                            foreach($NewsCategory as $NewsCategory_val) {
                                if($NewsCategory_val->rss_news_id == $RSSNews->id) {

                                } else {
                                    $rss_news_id_array[] = intval($NewsCategory_val->rss_news_id);
                                }
                                // dd($topic->topic->id);
                            }
                        }
                        // dd($rss_news_id_array);
                        if($rss_news_id_array) {
                            $RSSNews_last10 = RSSNews::whereIn('id', $rss_news_id_array)->where('status',1)->where('public_date', '<=', Carbon::now())->orderBy('created_at','DESC')->limit(10)->get();
                            // dd($RSSNews_last10);
                        }


                        $rss_news_id_all_array = [];
                        if($NewsCategory) {
                            foreach($NewsCategory as $NewsCategory_val) {
                                
                                    $rss_news_id_all_array[] = intval($NewsCategory_val->rss_news_id);
                                
                                // dd($topic->topic->id);
                            }
                        }
                        // dd($rss_news_id_all_array);
                        if($rss_news_id_all_array) {
                            $RSSNews_prev = RSSNews::whereIn('id', $rss_news_id_all_array)->where('status',1)->where('id','<',$RSSNews->id)->orderBy('created_at','DESC')->limit(1)->first();
                            $RSSNews_next = RSSNews::whereIn('id', $rss_news_id_all_array)->where('status',1)->where('id','>',$RSSNews->id)->orderBy('created_at','DESC')->limit(1)->first();
                            // dd($RSSNews_last10);
                        }
                        
                    }

                    // dd($RSSNews_prev);
                    // dd($RSSNews_next);
                    if($lang == 'th') {
                        $RSSNews_name = $RSSNews->title_th;
                        $RSSNews_detail = $RSSNews->detail_th;
                    } else {
                        $RSSNews_name = $RSSNews->title_en;
                        $RSSNews_detail = $RSSNews->detail_en;
                    }


                    $dataOut['RSSNews_prev'] = $RSSNews_prev;
                    $dataOut['RSSNews_next'] = $RSSNews_next;
                    $dataOut['RSSNews_last10'] = $RSSNews_last10;
                    $dataOut['RSSNews_name'] = $RSSNews_name;
                    $dataOut['RSSNews_detail'] = $RSSNews_detail;
                    $dataOut['lang'] = $lang;
                    $dataOut['RSSNews'] = $RSSNews;
                    $dataOut['page'] = langapp('news_detail');
                    // $RSSNews;
                    $ReadNews_data = ReadNews::where('user_id',$user_id)->where('news_id',$RSSNews->id)->where('status',1)->first();
                    if($ReadNews_data) {

                    } else {
                        $ReadNews = new ReadNews;
                        $ReadNews->code = generator_uuid();
                        $ReadNews->site_id = null;
                        $ReadNews->user_id = $user_id;
                        $ReadNews->news_id = $RSSNews->id;
                        $ReadNews->save();
                    }


                    $RSSNews->view = $RSSNews->view+1;
                    $RSSNews->save();

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
            return response()->json($response);
        }
    }

    public function jqueryLoadMoreNewsBookmark(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'news'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $html = '';
                    $user_id = $data['data']['user_id'];
                    $url = $data['data']['url'];
                    $Bookmark = Bookmark::where('user_id',@$user_id)->orderBy('created_at','desc')->get();
                    if($Bookmark) {
                        foreach($Bookmark as $item){
                            $check_read_news = ReadNews::where('user_id', $user_id)->where('news_id', $item -> rss_news_id)->first();
                            if($check_read_news){
                                $html .= '<div class="list-news">';
                                $font_weight = 'font-weight: bold !important;';
                            }else{
                                $html .= '<div class="list-news" style="background-color:#ececec">';
                                $font_weight = '';
                            }
        
                            if(@$item -> news -> transaction_rss_id) {
                                if(@$item -> news -> logo) {
                                    $url_logo = config('app.URL_CENTER_PUBLISH').@$item -> news -> logo;
                                } else {
                                    $url_logo = @$item -> news -> logo_rss;
                                }
                            } else {
                                $url_logo = config('app.URL_CENTER_PUBLISH').@$item -> news -> logo;
                            }
        
        
                            $html .= '
                            <!--<div class="checkbox-news-select">
                                    <label class="mr-3">
                                        <input type="checkbox" name="" class="chk-bookmark">
                                        <span class="label-text checkbox-news-input"></span>
                                    </label>
                                </div>-->
                                <div class="content-news-text">
                                    <a href="'.$url.'/news/detail/'.@$item -> news -> code.'">
                                        <span class="head-news-text" style="'.@$font_weight.'">'.@$item -> news -> title_th.'</span>
                                    </a>
                                    <div class="entry-meta">
                                        <span class="entry-date"> <i class="fas fa-calendar-alt"></i> '.@$item -> news -> public_date.'</span>
                                        <span class="entry-view"> <i class="fas fa-eye"></i> '.@$item -> news -> view.'</span>
                                        <span><p></p>&nbsp;'.strip_tags(@$item -> news -> detail_th).'</p></span>
                                    </div>
                                </div>
                                <div class="content-news-image">
                                    <a href="'.$url.'/news/detail/'.@$item -> news -> code.'">
                                        <img src="'.$url_logo.'" alt="" onerror="setDefaultPic(this)">
                                    </a>
                                </div>
                                <div class="action-bookmark">';
                                    $html .= '<i class="fas fa-bookmark bookmark-active" id="mark'.@$item -> news -> id.'" onclick="Bookmarks(this, '.@$item -> news -> id.')"></i>';
                                    $html .= '</div>
                            </div>
                            ';
                        }
                    }
                    $dataOut = [
                        "html" => $html,
                        "count" => count($Bookmark)
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
            return response()->json($response);
        }
    }

    public function count_asset(Request $request){
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
                    $get_role_custom = $data['data']['get_role_custom'];
                    $site = $data['data']['site'];
                    if($get_role_custom == 1) {
                        if(!$site){
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
                        }else{
                            $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $site)->first();
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
                        }
                    } else {
                        $site_id_arr = $data['data']['site_id_arr'];
                        if(!$site){
                            $datacountAssets = @Assets::select('assets.id','assets_datas.data_type_id','assets_datas.value')->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')->whereIn('assets.site_id',$site_id_arr)->whereIn('assets_datas.data_type_id',[5,6])->where('assets.status', 1)->get();
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
                        }else{
                            $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $site)->first();
                            $datacountAssets = @Assets::select('assets.id','assets_datas.data_type_id','assets_datas.value')->leftJoin('assets_datas', 'assets.id', '=', 'assets_datas.asset_id')->whereIn('assets.site_id',$site_id_arr)->where('assets.site_id',$SiteSettingsfor->id)->whereIn('assets_datas.data_type_id',[5,6])->where('assets.status', 1)->get();
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
                        }
                    }
                    $assets = $dataOut["countAssets"];

                    $data_transcation = json_encode($assets);
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

    public function count_vulnerability(Request $request){
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
                    $get_role_custom = $data['data']['get_role_custom'];
                    $site = $data['data']['site'];
                    $user_id = $data['data']['user_id'];
                    $site_id_arr = UserSite::select('site_id')->where('user_id', $user_id)->get();
                    if($get_role_custom == 1) {
                        if(!$site){
                            $CVEMapping = CVEMapping::select('id')->count();
                        }else{
                            $site_id_m = SiteSettings::select('id')->where('code',$site)->first();
                            $CVEMapping = CVEMapping::select('id')->where('site_id', $site_id_m->id)->count();
                        }
                    } else {
                        if(!$site){
                            $CVEMapping = CVEMapping::select('id')->whereIn('site_id', $site_id_arr)->count();
                        }else{
                            $site_id_m = SiteSettings::select('id')->where('code',$site)->first();
                            $CVEMapping = CVEMapping::select('id')->where('site_id', $site_id_m->id)->whereIn('site_id', $site_id_arr)->count();
                        }
                    }

                    $data_transcation = json_encode($CVEMapping);
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

    public function count_compromised(Request $request){
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
                    $get_role_custom = $data['data']['get_role_custom'];
                    $site = $data['data']['site'];
                    $user_id = $data['data']['user_id'];
                    
                    $site_id_arr = UserSite::select('site_id')->where('user_id', $user_id)->get();
                    if($get_role_custom == 1) {
                        if(!$site){
                            $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('status', 1)->whereIn('feel_type', ['darkweb','webserver','server','compromise','compromised'])->count();
                        }else{
                            $site_id_m = SiteSettings::select('id')->where('code',$site)->first();
                            $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('site_id',$site_id_m->id)->where('status', 1)->whereIn('feel_type', ['darkweb','webserver','compromise','compromised'])->count();
                        }
                    } else {
                        if(!$site){
                            $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('status', 1)->whereIn('site_id', $site_id_arr)->whereIn('feel_type', ['darkweb','webserver','server','compromise','compromised'])->count();
                        }else{
                            $site_id_m = SiteSettings::select('id')->where('code',$site)->first();
                            $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('site_id', $site_id_m->id)->where('status', 1)->whereIn('site_id', $site_id_arr)->whereIn('feel_type', ['darkweb','webserver','compromise','compromised'])->count();
                        }
                    }

                    $data_transcation = json_encode($DataLeakSocialRef);
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

    public function count_data_leak(Request $request){
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
                    $get_role_custom = $data['data']['get_role_custom'];
                    $site = $data['data']['site'];
                    $site_id_arr = $data['data']['site_id_arr'];

                    if($get_role_custom == 1) {
                        if(!$site){
                            $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('status', 1)->where('feel_type', 'social')->count();
                        }else{
                            $site_id_m = SiteSettings::select('id')->where('code',$site)->first();
                            $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('site_id', $site_id_m->id)->where('status', 1)->where('feel_type', 'social')->count();
                        }
                    } else {
                        if(!$site){
                            $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('status', 1)->whereIn('site_id', $site_id_arr)->where('feel_type', 'social')->count();
                        }else{
                            $site_id_m = SiteSettings::select('id')->where('code',$site)->first();
                            $DataLeakSocialRef = DataLeakSocialRef::select('id')->where('site_id', $site_id_m->id)->where('status', 1)->whereIn('site_id', $site_id_arr)->where('feel_type', 'social')->count();
                        }
                    }

                    $data_transcation = json_encode($DataLeakSocialRef);
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

    public function count_vulnerability_host(Request $request){
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
                    $get_role_custom = $data['data']['get_role_custom'];
                    $site = $data['data']['site'];
                    $user_id = $data['data']['user_id'];

                    $site_id_arr = UserSite::select('site_id')->where('user_id', $user_id)->get();
                    if($get_role_custom == 1) {
                        if(!$site){
                            $CVEAssets = CVEAssets::select('vendor', 'title')->where("active", '=', 1)->groupBy('vendor', 'title')->get();
                        }else{
                            $site_id_m = SiteSettings::select('id')->where('code',$site)->first();
                            $CVEAssets = CVEAssets::select('vendor', 'title')->where('site_id', $site_id_m->id)->where("active", '=', 1)->groupBy('vendor', 'title')->get();
                        }
                    } else {
                        if(!$site){
                            $CVEAssets = CVEAssets::select('vendor', 'title')->where("active", '=', 1)->whereIn('site_id', $site_id_arr)->groupBy('vendor', 'title')->get();
                        }else{
                            $site_id_m = SiteSettings::select('id')->where('code',$site)->first();
                            $CVEAssets = CVEAssets::select('vendor', 'title')->where('site_id', $site_id_m->id)->where("active", '=', 1)->whereIn('site_id', $site_id_arr)->groupBy('vendor', 'title')->get();
                        }
                    }
            
            
                    
                    $vendor = [];
                    $title = [];
                    foreach($CVEAssets as $item){
                        $vendor[] = $item -> vendor;
                        $title[] = $item -> title;
                    }
                    $DataCveven = DataCveven::select('namecve', 'title', DB::raw('count(*) as total'))->whereIn('vendor', $vendor)->whereIn('title', $title)->groupBy('namecve')->get();
                    $namecve = [];
                    $check_total_namecve = array();
                    $host_name = [];
                    foreach($DataCveven as $item){
                        $namecve[] = $item -> namecve;
                        $check_total_namecve[] = collect([
                            'total' => $item -> total,
                            'namecve' => $item -> namecve,
                            'title' => $item -> title
                        ]);
                    }
            
                    $site_id_arr = UserSite::select('site_id')->where('user_id', $user_id)->get();
                    if($get_role_custom == 1) {
                        $CVEMapping = CVEMapping::select('namecve', 'severity')->whereIn('namecve', $namecve)->groupBy('severity','namecve')->get();
                    } else {
                        $CVEMapping = CVEMapping::select('namecve', 'severity')->whereIn('site_id', $site_id_arr)->whereIn('namecve', $namecve)->groupBy('severity','namecve')->get();
                    }
            
            
                    foreach($CVEMapping as $value){
                        foreach($check_total_namecve as $item){
                            if($value -> namecve == $item['namecve']){
                                $value['total'] = $item['total'];
                                $value['title'] = $item['title'];
                                $host_name[] = $item['title'];
                            }
                        }
                    }
                    $result = array();
                    foreach ($host_name as $element) {
                        $result[$element] = $element;
                    }
                    
                    $response = array(
                        'data' => $CVEMapping,
                        'host_name' => $result
                    );

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

    public function chart_indicators(Request $request){
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
                    $displayType = $data['data']['displayType'];
                    if($displayType == 'mon'){
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
                        foreach($IndicatorSummaryYear as $item){
                            if($item -> month == 1){
                                $events[0] = $item -> event_count;
                                $attribute[0] = $item -> attribute_count;
                            }else if($item -> month == 2){
                                $events[1] = $item -> event_count;
                                $attribute[1] = $item -> attribute_count;
                            }else if($item -> month == 3){
                                $events[2] = $item -> event_count;
                                $attribute[2] = $item -> attribute_count;
                            }else if($item -> month == 4){
                                $events[3] = $item -> event_count;
                                $attribute[3] = $item -> attribute_count;
                            }else if($item -> month == 5){
                                $events[4] = $item -> event_count;
                                $attribute[4] = $item -> attribute_count;
                            }else if($item -> month == 6){
                                $events[5] = $item -> event_count;
                                $attribute[5] = $item -> attribute_count;
                            }else if($item -> month == 7){
                                $events[6] = $item -> event_count;
                                $attribute[6] = $item -> attribute_count;
                            }else if($item -> month == 8){
                                $events[7] = $item -> event_count;
                                $attribute[7] = $item -> attribute_count;
                            }else if($item -> month == 9){
                                $events[8] = $item -> event_count;
                                $attribute[8] = $item -> attribute_count;
                            }else if($item -> month == 10){
                                $events[9] = $item -> event_count;
                                $attribute[9] = $item -> attribute_count;
                            }else if($item -> month == 11){
                                $events[10] = $item -> event_count;
                                $attribute[10] = $item -> attribute_count;
                            }else if($item -> month == 12){
                                $events[11] = $item -> event_count;
                                $attribute[11] = $item -> attribute_count;
                            }
                        }
                    }
                    
                    $response = [
                        'events' => $events,
                        'attribute' => $attribute,
                        'nameXAxis' => $nameXAxis,
                        'nameYAxis' => $nameYAxis,
                        'nameSeriesAttribute' => $nameSeriesAttribute,
                        'nameSeriesEvent' => $nameSeriesEvent,
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

    public function load_chart(Request $request){
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
                    $user_id = $data['data']['user_id'];
                    $get_role_custom = $data['data']['get_role_custom'];
                    $model = new CVEMapping;

                    $site_id_arr = UserSite::select('site_id')->where('user_id', $user_id)->get();
                    if($get_role_custom == 1) {
                        if(!$site){
                            $model->get();
                            $high = $model->where('severity', '=', 'HIGH')->count();
                            $medium = $model->where('severity', '=', 'MEDIUM')->count();
                            $critical = $model->where('severity', '=', 'CRITICAL')->count();
                            $low = $model->where('severity', '=', 'LOW')->count();
                            $none = $model->where('severity', '=', 'NONE')->count();
                        }else{
                            $model->get();
                            $site_id_m = SiteSettings::select('id')->where('code',$site)->first();
                            $high = $model->where('site_id', $site_id_m->id)->where('severity', '=', 'HIGH')->count();
                            $medium = $model->where('site_id', $site_id_m->id)->where('severity', '=', 'MEDIUM')->count();
                            $critical = $model->where('site_id', $site_id_m->id)->where('severity', '=', 'CRITICAL')->count();
                            $low = $model->where('site_id', $site_id_m->id)->where('severity', '=', 'LOW')->count();
                            $none = $model->where('site_id', $site_id_m->id)->where('severity', '=', 'NONE')->count();
                        }
                    } else {
                        if(!$site){
                            $model->get();
                            $high = $model->where('severity', '=', 'HIGH')->whereIn('site_id', $site_id_arr)->count();
                            $medium = $model->where('severity', '=', 'MEDIUM')->whereIn('site_id', $site_id_arr)->count();
                            $critical = $model->where('severity', '=', 'CRITICAL')->whereIn('site_id', $site_id_arr)->count();
                            $low = $model->where('severity', '=', 'LOW')->whereIn('site_id', $site_id_arr)->count();
                            $none = $model->where('severity', '=', 'NONE')->whereIn('site_id', $site_id_arr)->count();
                        }else{
                            $model->get();
                            $site_id_m = SiteSettings::select('id')->where('code',$site)->first();
                            $high = $model->where('site_id', $site_id_m->id)->where('severity', '=', 'HIGH')->whereIn('site_id', $site_id_arr)->count();
                            $medium = $model->where('site_id', $site_id_m->id)->where('severity', '=', 'MEDIUM')->whereIn('site_id', $site_id_arr)->count();
                            $critical = $model->where('site_id', $site_id_m->id)->where('severity', '=', 'CRITICAL')->whereIn('site_id', $site_id_arr)->count();
                            $low = $model->where('site_id', $site_id_m->id)->where('severity', '=', 'LOW')->whereIn('site_id', $site_id_arr)->count();
                            $none = $model->where('site_id', $site_id_m->id)->where('severity', '=', 'NONE')->whereIn('site_id', $site_id_arr)->count();
                        }
                    }
            
            
                    $response = [
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
            return response()->json($response);
        }
    }

    public function table_dashboard(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'dashboard'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $startDate = $data['data']['startDate'];
                    $endDate = $data['data']['endDate'];
                    $sitecode = $data['data']['sitecode'];
                    $pagename = $data['data']['pagename'];
                    $get_role_custom = $data['data']['get_role_custom'];
    
                    $model = '';
                    $html = '';

                    $date_start_explode = explode(" ",$startDate);
                    $date_start_date = @$date_start_explode[0];
                    $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                    // dd($date_start_time);
                    $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                    // dd($date_start_date_format);
                    $date_start_time_time = date("H:i", strtotime($date_start_time));
                    $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                    // dd($date_start_time_time);

                    $date_end_explode = explode(" ",$endDate);
                    $date_end_date = @$date_end_explode[0];
                    $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                    // dd($date_end_time);
                    $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                    $date_end_time_time = date("H:i", strtotime($date_end_time));
                    $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';

                    $SiteSettings = null;

                    if($sitecode){
                        $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->where("code",$sitecode)->first();
                    }

                    
                    $dataCVEMapping = array();
                    $dataR_s_s_news = array();
                    $DataLeakFeed_social = array();
                    $DataLeakFeed_compromised = array();
                    $data_fx_otx_events = array();
                    if (!$pagename||$pagename=='Vulnerabilities') {
                        $dataCVEMapping = CVEMapping::select('namecve as content', 'data_datacve_mapping.created_at as datetime', 'site.name as sitename', DB::raw('CONCAT("/monitoringvulnerabilitys") AS link , "Vulnerabilities" AS pagename'))->whereBetween('data_datacve_mapping.created_at',array($date_start_datetime_format,$date_end_datetime_format));
                        if(isset($SiteSettings->id)){
                            $dataCVEMapping = $dataCVEMapping->where("data_datacve_mapping.site_id",$SiteSettings->id);
                        }
                        $dataCVEMapping = $dataCVEMapping->leftjoin('site', 'data_datacve_mapping.site_id', '=', 'site.id')->get()->toArray();
                    }

                    if (!$pagename||$pagename=='News') {
                        $dataR_s_s_news_th = R_s_s_news::select('title_th as content', 'created_at as datetime', DB::raw(' "All Site" as sitename,CONCAT("/public/news/detail/",code ,"/th") AS link , "News" AS pagename'))->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))
                        ->where(function ($query) {
                            $query->whereNotNull('title_th')->where('title_th', '!=', '');//detail_th   
                        })->get()->toArray();
                        $dataR_s_s_news_en = R_s_s_news::select('title_en as content', 'created_at as datetime', DB::raw(' "All Site" as sitename,CONCAT("/public/news/detail/",code ,"/en") AS link , "News" AS pagename'))->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))
                        ->where(function ($query) {
                            $query->whereNotNull('title_en')->where('title_en', '!=', '');//detail_en
                        })->get()->toArray();
                        $dataR_s_s_news = array_merge($dataR_s_s_news_th,$dataR_s_s_news_en);
                    }

                    if (!$pagename||$pagename=='Data Leak') {
                        if($get_role_custom == 1) {//|| @get_role_custom()['site_admin'] == 1
                            $DataLeakFeed_social = DataLeakFeedTemp::select('id','feedcontent as content', 'created_at as datetime', DB::raw(' "" as sitename,CONCAT("/socialdatas") AS link , "Data Leak" AS pagename'))->whereNull('deleted_at')->where('keyword', '!=', null)->where('keyword', '!=', '')->where('feed_type', 'social')->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))->get()->toArray();
                            foreach ($DataLeakFeed_social as $key => $value) {
                                $leak_socail_ref_temps = leak_socail_ref_temp::select('site_id')->whereNull('deleted_at')->where('data_leak_feed_id', $value["id"])->first();
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
                        }else{
                            $DataLeakFeed_social = DataLeakFeed::select('id','feedcontent as content', 'created_at as datetime', DB::raw(' "" as sitename,CONCAT("/socialdatas") AS link , "Data Leak" AS pagename'))->whereNull('deleted_at')->where('keyword', '!=', null)->where('keyword', '!=', '')->where('feel_type', 'social')->whereBetween('created_at',array($date_start_datetime_format,$date_end_datetime_format))->get()->toArray();
                            foreach ($DataLeakFeed_social as $key => $value) {
                                $leak_socail_ref_temps = DataLeakSocialRef::select('site_id')->whereNull('deleted_at')->where('data_leak_feed_id', $value["id"])->first();
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
                    
                    
                    // if (!$pagename||$pagename=='Compromised') {
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

                    if (!$pagename||$pagename=='Compromised') {
                        if($get_role_custom == 1) {//|| @get_role_custom()['site_admin'] == 1
                            $DataLeakFeed_compromised = DataLeakFeed::select('data_leak_feed.source_name as content', 'data_leak_feed.created_at as datetime', 'site.name as sitename',DB::raw('CONCAT("/darkweb-datas") AS link , "Compromised" AS pagename'))->whereNull('data_leak_feed.deleted_at')->where('data_leak_feed.feel_type','!=', 'social')->whereBetween('data_leak_feed.created_at',array($date_start_datetime_format,$date_end_datetime_format));
                            $DataLeakFeed_compromised = $DataLeakFeed_compromised->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');
                            if(isset($SiteSettings->id)){
                                $DataLeakFeed_compromised = $DataLeakFeed_compromised->where('site.id', $SiteSettings->id);
                            }
                            $DataLeakFeed_compromised = $DataLeakFeed_compromised->get()->toArray();
                        }else{
                            $DataLeakFeed_compromised = DataLeakFeed::select('data_leak_feed.source_name as content', 'data_leak_feed.created_at as datetime', 'site.name as sitename',DB::raw('CONCAT("/darkweb-datas") AS link , "Compromised" AS pagename'))->whereNull('data_leak_feed.deleted_at')->where('data_leak_feed.feel_type','!=', 'social')->whereBetween('data_leak_feed.created_at',array($date_start_datetime_format,$date_end_datetime_format));
                            $DataLeakFeed_compromised = $DataLeakFeed_compromised->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id')->get()->toArray();
                        }
                    }

                    // if (!$pagename||$pagename=='Indicators') {
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


                        $model = array_merge($dataCVEMapping,$dataR_s_s_news,$DataLeakFeed_social,$DataLeakFeed_compromised,$data_fx_otx_events);
                        $dataOut = array();
                        usort($model, function($a, $b) {
                            $t1 = strtotime($a['datetime']);
                            $t2 = strtotime($b['datetime']);
                            return $t2 - $t1;
                        });

                        $dataOut["data"] =  $model;

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
            return response()->json($response);
        }
    }

    public function cve_assets(Request $request){
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
                    $get_role_custom = $data['data']['get_role_custom'];
                    $site = $data['data']['site'];
                    $user_id = $data['data']['user_id'];
                    
                    $site_id_arr = UserSite::select('site_id')->where('user_id', $user_id)->get();
                    if($get_role_custom == 1) {
                        if(!$site){
                            $assets = [];
                            $Assets_data = Assets::where('status',1)->get();
                            foreach ($Assets_data as $key => $value) {
                                $AssetsData_data = AssetsData::where('site_id',$value->site_id)->where('asset_id',$value->id)->where('status',1)->get();
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
                                        array_push($assets, $Assets_data_list);
                                    }else{
                                        foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                                            $Assets_data_list = array();
                                            $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first(); 
                                            $Assets_data_list['site'] = $site->name;
                                            $Assets_data_list['host'] = $Domain_listvalue->value;
                                            $Assets_data_list['value'] = $IP_Listvalue->value;
                                            array_push($assets, $Assets_data_list);
                                        }
                                    }
                                }
                            }
                        }else{
                            $site_id_m = SiteSettings::select('id')->where('code',$site)->first();
                            $assets = [];
                            $Assets_data = Assets::where('status',1)->get();
                            foreach ($Assets_data as $key => $value) {
                                $AssetsData_data = AssetsData::where('site_id',$site_id_m->id)->where('asset_id',$value->id)->where('status',1)->get();
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
                                        array_push($assets, $Assets_data_list);
                                    }else{
                                        foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                                            $Assets_data_list = array();
                                            $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first(); 
                                            $Assets_data_list['site'] = $site->name;
                                            $Assets_data_list['host'] = $Domain_listvalue->value;
                                            $Assets_data_list['value'] = $IP_Listvalue->value;
                                            array_push($assets, $Assets_data_list);
                                        }
                                    }
                                }
                            }
                        }
                        
                    } else {
                        if(!$site){
                            $assets = [];
                            $Assets_data = Assets::where('status',1)->get();
                            foreach ($Assets_data as $key => $value) {
                                $AssetsData_data = AssetsData::where('site_id',$value->site_id)->where('asset_id',$value->id)->where('status',1)->get();
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
                                        array_push($assets, $Assets_data_list);
                                    }else{
                                        foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                                            $Assets_data_list = array();
                                            $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first(); 
                                            $Assets_data_list['site'] = $site->name;
                                            $Assets_data_list['host'] = $Domain_listvalue->value;
                                            $Assets_data_list['value'] = $IP_Listvalue->value;
                                            array_push($assets, $Assets_data_list);
                                        }
                                    }
                                }
                            }
                        }else{
                            $site_id_m = SiteSettings::select('id')->where('code',$site)->first();
                            $assets = [];
                            $Assets_data = Assets::where('status',1)->get();
                            foreach ($Assets_data as $key => $value) {
                                $AssetsData_data = AssetsData::where('site_id',$site_id_m->id)->where('asset_id',$value->id)->where('status',1)->get();
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
                                        array_push($assets, $Assets_data_list);
                                    }else{
                                        foreach ($Domain_list as $Domain_listkey => $Domain_listvalue) {
                                            $Assets_data_list = array();
                                            $site = SiteSettings::select('name')->where('id', $IP_Listvalue->site_id)->withTrashed()->first(); 
                                            $Assets_data_list['site'] = $site->name;
                                            $Assets_data_list['host'] = $Domain_listvalue->value;
                                            $Assets_data_list['value'] = $IP_Listvalue->value;
                                            array_push($assets, $Assets_data_list);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $data_transcation = json_encode($assets);
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
                    } else if($check==2) {

                    }else{
                        $model = $model->where('is_fix', '=', 0);
                    }


                    if ($datatype) {
        
                        $model = $model->whereIn('severity', $datatype);
                    }   
                } else {
                    $model = CVEMapping::where('is_fix', 0);
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
                        return $model -> site;
                    })
                    ->addColumn('hostname', function (CVEMapping $model) {
                        return $model -> hostname;
                    })
                    ->addColumn('ip', function (CVEMapping $model) {
                        return $model -> ip;
                    })
                    ->addColumn('vendor', function (CVEMapping $model) {
                        return $model -> vendor;
                    })
                    ->addColumn('title', function (CVEMapping $model) {
                        return $model -> title;
                    })
                    ->addColumn('version', function (CVEMapping $model) {
                        return $model -> version;
                    })
                    ->addColumn('edition', function (CVEMapping $model) {
                        return $model -> edition;
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
                    $response['page'] = langapp('monitoring_vulnerabilitys');
                    $response['cve_assets'] = CVEAssets::where("active", '=', 1)->get();
                    $response['count_CVEAssets'] = CVEAssets::where("active", '=', 1)->count();
                    $response['count_CVEMapping'] = CVEMapping::count();
                    $response['count_isFix'] = CVEMapping::where("is_fix", '=', 1)->count();

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
                            $model = $model->whereBetween('modified', array($date_start_date_format, $date_end_date_format));
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
                        $get_month = $model->select(DB::raw('count(modified)  as count_mon'),DB::raw('DAY(modified) as mon'))
                        ->whereRaw('MONTH(modified) = MONTH(CURDATE())')
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
                        $get_month = $model->select(DB::raw('count(modified)  as count_mon'),DB::raw('MONTH(modified) as mon'))
                        ->whereRaw('YEAR(modified) = YEAR(CURDATE())')
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
            return response()->json($response);
        }
    }

    public function compromised_count_val(Request $request){
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

                    $date_start = $data['data']['date_start'];
                    $date_end = $data['data']['date_end'];
                    $site_code = $data['data']['site_code'];
                    $title = $data['data']['title'];
                    $social = $data['data']['social'];
                    $keywords = $data['data']['keywords'];
                    $f_search = $data['data']['f_search'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $check_type = $data['data']['check_type'];

                    $user = User::where('id', $data['data']['user_id'])->first();

                    $site_id = '';
            
                    if($site_code) {
                        $site_id_m = SiteSettings::where('code',$site_code)->first();
                        $site_id = @$site_id_m->id;
                    }
            
                    $title = $request ->title;
                    $social = $request ->social;
                   
            
                    $date_start_explode = explode(" ",$date_start);
                    $date_start_date = @$date_start_explode[0];
                    $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                    // dd($date_start_time);
                    $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                    // dd($date_start_date_format);
                    $date_start_time_time = date("H:i", strtotime($date_start_time));
                    $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                    // dd($date_start_time_time);
            
                    $date_end_explode = explode(" ",$date_end);
                    $date_end_date = @$date_end_explode[0];
                    $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                    // dd($date_end_time);
                    $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                    $date_end_time_time = date("H:i", strtotime($date_end_time));
                    $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
                    // dd($date_end_time_time);
            
                    // date("H:i", strtotime("04:25 PM"))
                    $html = '';
            
            
                    if(  $f_search == 1 && ($keywords || $social || $date_start || $date_end || $site_id || $check_type) ){
            
                        $model = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->with('get_site')->with('get_data_leak_feed_one');
                        $countGroupBy = DataLeakSocialRef::where('deleted_at', null)->where('status', 1);
            
            
                        if($social) {
                            $model = $model-> where('feel_type', '=' ,$social);
                            $countGroupBy = $countGroupBy -> where('feel_type', '=' ,$social);
                        }else{
                            $model = $model->whereIn('feel_type', ['darkweb', 'compromise','webserver','server']);
                            $countGroupBy = $countGroupBy->whereIn('feel_type', ['darkweb', 'compromise','webserver','server']);
                        }
            
                        if($keywords){
                            $model = $model->where('keyword', 'LIKE', '%' . $keywords . '%');
                            $countGroupBy = $countGroupBy -> where('keyword', 'LIKE' ,'%'.$keywords.'%');
                        }
            
                        
            
                        if($date_start) {
                          
                            if($isDateSearch=="true"){
                            
                                $countGroupBy = $countGroupBy->whereHas('get_data_leak_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
                                    $qq->whereBetween('feedtimepost', array($date_start_datetime_format, $date_end_datetime_format));
                                });
            
                                $model = $model->whereHas('get_data_leak_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
                                    $qq->whereBetween('feedtimepost', array($date_start_datetime_format, $date_end_datetime_format));
                                });
                            
                            }
            
                        }
            
                        if($request ->check_type) {
            
                            $model = $model-> where('feel_type', '=' ,$check_type);
                            $countGroupBy = $countGroupBy -> where('feel_type', '=' ,$check_type);
            
                        }
            
            
                        if($site_id) {
                            $model = $model->where('site_id', $site_id);
                            $countGroupBy = $countGroupBy->where('site_id', $site_id);
                        }

                        $Data_leak_feed_all = $model->count();
                        $countGroupBy = $countGroupBy->select( 'feel_type',DB::raw('count(*) as total'))->groupBy('feel_type')->get();
                        $model = $model->with('get_data_leak_feed_one')->orderBy('id','desc')->paginate(PAGINATE_NUM);
                    }else{
                        $Data_leak_feed_all = DataLeakSocialRef::where('deleted_at', null)->where('status', 1)->where('feel_type', 'darkweb')->orWhere('feel_type', 'compromise')->orWhere('feel_type', 'webserver')->orWhere('feel_type', 'server')->count();
                        $news = DataLeakSocialRef::where('deleted_at', null)->where('status', 1)->whereIn('feel_type', ['darkweb', 'compromise','webserver','server']);//->get()
                        $countGroupBy = DataLeakSocialRef::select( 'feel_type',DB::raw('count(*) as total'))->where('deleted_at', null)->where('status', 1)->whereIn('feel_type', ['darkweb', 'compromise','webserver','server'])->groupBy('feel_type');
                       
            
                            $site_id_arr = UserSite::select('site_id')->where('user_id', $user -> id)->get();
                            if(@$user->site_role_id && @$user->site_id) {
                                if(@$user->site_role_id == 99 || @$user->site_role_id == 4) {//support and admin
                                    $news = $news->whereIn('site_id', $site_id_arr);
                                    $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            
                                } else {//not support and admin
                                    $news = $news->whereIn('site_id', $site_id_arr);
                                    $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                                }
                            }           
                        
            
                        if($site_id) {
                            $news = $news->where('site_id', $site_id);
                            $countGroupBy = $countGroupBy->where('site_id', $site_id);
                        }
                        $news = $news->with('get_data_leak_feed_one')->orderBy('id','desc')->paginate(PAGINATE_NUM);
                        $countGroupBy = $countGroupBy->get();
            
            
                    }
                    $content = [];
                    $count_sub_type["webserver"] = 0;
                    $count_sub_type["darkweb"] = 0;
                    $count_sub_type["compromise"] = 0;
                    
                    foreach ($countGroupBy as $countGroup) {
                        $count_sub_type[$countGroup->feel_type] = $countGroup->total;
                    }   
                    
                    $response = [
                        "html" => $html,
                        "count" => $Data_leak_feed_all,
                        "webserver" => $count_sub_type["webserver"],
                        "darkweb" => $count_sub_type["darkweb"],
                        "compromise" => $count_sub_type["compromise"],
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

    public function data_leak_view(Request $request){
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
                    
                    $model1 = DataLeakSocialRef::select('data_leak_feed.feedcontent')
                    ->join('data_leak_feed', 'data_leak_feed.id', '=','data_leak_socail_ref.data_leak_feed_id')
                    ->where('data_leak_socail_ref.code',$code)->first();

                    $response = [
                        "code" => $code,
                        "feedcontent" => $model1->feedcontent,
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

    public function compromised_view(Request $request){
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

                    $code = $data['data']['code'];
                    
                    $model1 = DataLeakSocialRef::select('data_leak_feed.feedcontent')
                    ->join('data_leak_feed', 'data_leak_feed.id', '=','data_leak_socail_ref.data_leak_feed_id')
                    ->where('data_leak_socail_ref.code',$code)->first();

                    $response = [
                        "code" => $code,
                        "feedcontent" => $model1->feedcontent,
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

    public function compromised_table(Request $request){
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

                    $startDate = $data['data']['startDate'];
                    $endDate = $data['data']['endDate'];
                    $search_val = $data['data']['search_val'];
                    $site = $data['data']['site'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $keywords = $data['data']['keywords'];
                    $assets = $data['data']['assets'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $check = $data['data']['check'];
                    $datatype = $data['data']['datatype'];
                    $level = $data['data']['level'];
                    $source = $data['data']['source'];
                    
                    $where1 = ['deleted_at' => null, 'feel_type' => 'darkweb'];
                    $where = ['deleted_at' => null];
                    $orwhere = ['deleted_at' => null, 'feel_type' => 'compromise'];
                    $orwhere2 = ['deleted_at' => null, 'feel_type' => 'webserver'];
                    $orwhere3 = ['deleted_at' => null, 'feel_type' => 'server'];

                    if ($search_val == 'true') {
                        $model = DataLeakSocialRef::where('deleted_at', null)->with('get_site')->with('get_data_leak_feed_one');
                        $countGroupBy = DataLeakSocialRef::where('deleted_at', null)->where('status', 1);

                        if ($keywords) {

                            $model = $model->where('keyword', 'LIKE', '%' . $keywords . '%')
                            ->orWhereHas('get_data_leak_feed_one', function($q) use ($request) { 
                                $q->where('feedcontent', 'like', '%'.$keywords.'%');
                            });

                            $countGroupBy = $countGroupBy->where('keyword', 'LIKE', '%' . $keywords . '%')
                            ->orWhereHas('get_data_leak_feed_one', function($q) use ($request) { 
                                $q->where('feedcontent', 'like', '%'.$keywords.'%');
                            });
                        }

                        if ($source) {
                            $model = $model->where('feel_type', '=', $source);
                            $countGroupBy = $countGroupBy->where('feel_type', '=', $source);
                        } else {
                            $model = $model->whereIn('feel_type', ['darkweb', 'compromise', 'webserver', 'server']);
                            $countGroupBy = $countGroupBy->whereIn('feel_type', ['darkweb', 'compromise', 'webserver', 'server']);
                        }

                        if($request ->check_type) {

                            $model = $model-> where('feel_type', '=' ,$request -> check_type);
                            $countGroupBy = $countGroupBy -> where('feel_type', '=' ,$request -> check_type);

                        }

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


                        if ($site) {
                            
                            $SiteSettings = SiteSettings::where('code', @$site)->first();
                            $model = $model->where('site_id', $SiteSettings->id);
                        }

                        if ($startDate) {
                            $date_start = $startDate;
                            $date_end = $endDate;

                            $date_start_explode = explode(" ", $date_start);
                            $date_start_date = @$date_start_explode[0];
                            $date_start_time = @$date_start_explode[1] . ' ' . @$date_start_explode[2];
                            // dd($date_start_time);
                            $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                            // dd($date_start_date_format);
                            $date_start_time_time = date("H:i", strtotime($date_start_time));
                            $date_start_datetime_format = $date_start_date_format . ' ' . $date_start_time_time . ':00';
                            // dd($date_start);

                            $date_end_explode = explode(" ", $date_end);
                            $date_end_date = @$date_end_explode[0];
                            $date_end_time = @$date_end_explode[1] . ' ' . @$date_end_explode[2];
                            // dd($date_end_time);
                            $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                            $date_end_time_time = date("H:i", strtotime($date_end_time));
                            $date_end_datetime_format = $date_end_date_format . ' ' . $date_end_time_time . ':00';
                            // dd($date_end_time_time);

                            // $model -> whereDate('transcation_date', Carbon::parse($request -> public_date)->format('Y-m-d'));
                            $model = $model->whereHas('get_data_leak_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
                                $qq->whereBetween('feedtimepost', array($date_start_datetime_format, $date_end_datetime_format));
                            });
                        }


                        $model->orderBy('id', 'desc');
                    } else {

                        $model = DataLeakSocialRef::where('deleted_at', null)->whereIn('feel_type', ['darkweb', 'compromise', 'webserver', 'server'])
                            ->whereHas('get_data_leak_feed_one', function ($q) use ($where1, $orwhere, $orwhere2, $orwhere3) {
                                // $q->where($where1);
                                // $q->orwhere($orwhere);
                                // $q->orwhere($orwhere2);
                                // $q->orwhere($orwhere3);
                            })
                            ->with('get_site')->with('get_data_leak_feed_one');

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

                        if ($site) {
                            
                            $SiteSettings = SiteSettings::where('code', @$site)->first();
                            // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
                            $model = $model->where('site_id', $SiteSettings->id);
                            // });
                    
                        }

                        $model->orderBy('id', 'desc')->get();
                    }

                    $res = DataTables::of($model)->toJson();

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
                if($data['data']['menu'] !== 'data_leak'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $startDate = $data['data']['startDate'];
                    $endDate = $data['data']['endDate'];
                    $search_val = $data['data']['search_val'];
                    $site = $data['data']['site'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];
                    $keywords = $data['data']['keywords'];
                    $type = $data['data']['type'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $check_type = $data['data']['check_type'];
                    $source = $data['data']['source'];
                    
                    $model = DataLeakSocialRef::where('deleted_at', null)
                    ->whereHas('get_data_leak_feed_one', function ($query) {
                        $query->where('feel_type', '=', 'social');
                    })
                    ->with('get_site')
                    ->with('get_data_leak_feed_one');
        
                    if ($search_val == 1) {
            
                        if ($keywords) {
                            $keywords = $keywords;
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($keywords) {
                                $query->where('keyword', 'LIKE', '%' . $keywords . '%')
                                    ->orWhere('feedcontent', 'LIKE', '%' . $keywords . '%');
                            });
                        }
            
            
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
            
            
                        if ($site) {
                            $SiteSettings = SiteSettings::where('code', @$site)->first();
                            // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
                            $model = $model->where('site_id', $SiteSettings->id);
                            // });
                        }
            
                        if ($type) {
            
                            $type = $type;
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($type) {
                                $query->where('feel_type', 'LIKE', '%' . $type . '%');
                            });
            
                        }
            
                        if ($check_type) {
                        
                            $type = $check_type;
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($type) {
                                $query->where('feel_type', 'LIKE', '%' . $type . '%');
                            });
            
                        }
            
                        if ($source) {
            
                            $source = $source;
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($source) {
                                $query->where('sourceid', 'LIKE', '%' . $source . '%');
                            });
            
                        }
            
                        if ($isDateSearch == 1) {
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
            
                            $source = $source;
                            $model->whereHas('get_data_leak_feed_one', function ($query) use ($date_start_date_format, $date_end_date_format) {
                                $query->whereBetween('feedtimepost', array($date_start_date_format, $date_end_date_format));
                            });
            
                        }
            
                        $model->get();
                    } else {
            
                        if ($site) {
                            $SiteSettings = SiteSettings::where('code', @$site)->first();
                            // $model = $model->whereHas('get_social_ref', function($qq) use ($request) {
                            $model = $model->where('site_id', $SiteSettings->id);
                            // });
                        }

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
            
                        $model->get();
                    }
        
                    $res = DataTables::of($model)->toJson(); 

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
            return response()->json($response);
        }
    }

    public function data_leak_count_val(Request $request){
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

                    $date_start = $data['data']['date_start'];
                    $date_end = $data['data']['date_end'];
                    $site_code = $data['data']['site_code'];
                    $title = $data['data']['title'];
                    $social = $data['data']['social'];
                    $f_search = $data['data']['f_search'];
                    $type = $data['data']['type'];
                    $isDateSearch = $data['data']['isDateSearch'];
                    $check_type = $data['data']['check_type'];

                    $user = User::where('id', $data['data']['user_id'])->first();

                    $site_id = '';
            
                    if($site_code) {
                        $site_id_m = SiteSettings::where('code',$site_code)->first();
                        $site_id = @$site_id_m->id;
                    }
               
            
                    $date_start_explode = explode(" ",$date_start);
                    $date_start_date = @$date_start_explode[0];
                    $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                    // dd($date_start_time);
                    $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                    // dd($date_start_date_format);
                    $date_start_time_time = date("H:i", strtotime($date_start_time));
                    $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                    // dd($date_start_time_time);
            
                    $date_end_explode = explode(" ",$date_end);
                    $date_end_date = @$date_end_explode[0];
                    $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                    // dd($date_end_time);
                    $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                    $date_end_time_time = date("H:i", strtotime($date_end_time));
                    $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
                    // dd($date_end_time_time);
            
                    // date("H:i", strtotime("04:25 PM"))
                    $html = '';
            
                    
            
                    if(  $f_search == 1 && ($title || $social || $date_start || $date_end || $site_id || $check_type) ){
            
                        $model = DataLeakSocialRef::where('deleted_at', null)->where('status',1)->with('get_site')->with('get_data_leak_feed_one');
                        $countGroupBy = DataLeakSocialRef::where('deleted_at', null)->where('status', 1);
            
            
                        if($type) {
                            $model = $model-> where('feel_type', '=' ,$type);
                            $countGroupBy = $countGroupBy -> where('feel_type', '=' ,$type);
                        }else{
                            $model = $model->whereIn('feel_type', ['social', 'darkweb_public']);
                            $countGroupBy = $countGroupBy->whereIn('feel_type', ['social', 'darkweb_public']);
                        }
            
                        if($social) {
                            $model = $model-> where('sourceid', '=' ,$social);
                            $countGroupBy = $countGroupBy -> where('sourceid', '=' ,$social);
                        }
            
                        if($title){
                            $model = $model->where('keyword', 'LIKE', '%' . $request->title . '%');
                            // $news = $news -> where('feedcontent', 'LIKE' ,'%'.$title.'%');
                            // $countGroupBy = $countGroupBy -> where('feedcontent', 'LIKE' ,'%'.$title.'%');
                            $countGroupBy = $countGroupBy -> where('keyword', 'LIKE' ,'%'.$title.'%');
                        }
            
                        if($check_type) {
                            $model = $model-> where('feel_type', '=' ,$check_type);
                            $countGroupBy = $countGroupBy -> where('feel_type', '=' ,$check_type);
                        }
            
                        
            
                        if($date_start) {
                          
                            if($isDateSearch=="true"){
                                // $news = $news -> whereBetween('feedtimepost',array($date_start_datetime_format,$date_end_datetime_format));
                                // $countGroupBy = $countGroupBy -> whereBetween('feedtimepost',array($date_start_datetime_format,$date_end_datetime_format));
                            
                                $countGroupBy = $countGroupBy->whereHas('get_data_leak_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
                                    $qq->whereBetween('feedtimepost', array($date_start_datetime_format, $date_end_datetime_format));
                                });
            
                                $model = $model->whereHas('get_data_leak_feed_one', function ($qq) use ($request, $date_start_datetime_format, $date_end_datetime_format) {
                                    $qq->whereBetween('feedtimepost', array($date_start_datetime_format, $date_end_datetime_format));
                                });
                            
                            }
            
                        }
            
                        if($date_end) {
            
                        }
            
                        $site_id_arr = UserSite::select('site_id')->where('user_id', @$user->id)->get();
                        if(@$user->site_role_id && @$user->site_id) {
                            if(@$user->site_role_id == 99 || @$user->site_role_id == 4) {//support and admin
                                // dd(99);
        
                                $model = $model->whereIn('site_id', $site_id_arr);
                
                                $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                        
        
                            } else {//not support and admin
                                $model = $model->whereIn('site_id', $site_id_arr);
                
                                $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            }
                        }
            
            
            
                        if($site_id) {
                            $model = $model->where('site_id', $site_id);
            
                            $countGroupBy = $countGroupBy->where('site_id', $site_id);
                        }

                        $Data_leak_feed_all = $model->count();
                        $countGroupBy = $countGroupBy->select( 'feel_type',DB::raw('count(*) as total'))->groupBy('feel_type')->get();
                        $model = $model->with('get_data_leak_feed_one')->orderBy('id','desc')->paginate(PAGINATE_NUM);
                    }else{
                        $Data_leak_feed_all = DataLeakSocialRef::where('deleted_at', null)->where('status', 1)->where('feel_type', 'social')->orWhere('feel_type', 'darkweb_public')->count();
                        $news = DataLeakSocialRef::where('deleted_at', null)->where('status', 1)->whereIn('feel_type', ['social', 'darkweb_public']);//->get()
                        $countGroupBy = DataLeakSocialRef::select( 'feel_type',DB::raw('count(*) as total'))->where('deleted_at', null)->where('status', 1)->whereIn('feel_type', ['social', 'darkweb_public'])->groupBy('feel_type');
                       
            
                        $site_id_arr = UserSite::select('site_id')->where('user_id', @$user->id)->get();

                            if(@$user->site_role_id && @$user->site_id) {
                                if(@$user->site_role_id == 99 || @$user->site_role_id == 4) {//support and admin
                                    // dd(99);
            
                                    $news = $news->whereIn('site_id', $site_id_arr);
                                    $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                            
                                } else {//not support and admin
                                    $news = $news->whereIn('site_id', $site_id_arr);
                                    $countGroupBy = $countGroupBy->whereIn('site_id', $site_id_arr);
                                }
                            }
            
                        if($site_id) {
                            $news = $news->where('site_id', $site_id);
                            $countGroupBy = $countGroupBy->where('site_id', $site_id);
                        }
            
                        $news = $news->with('get_data_leak_feed_one')->orderBy('id','desc')->paginate(PAGINATE_NUM);
                        $countGroupBy = $countGroupBy->get();
            
            
                    }

                    $content = [];

                    $count_sub_type["darkweb"] = 0;
                    $count_sub_type["social"] = 0;
                    
                    foreach ($countGroupBy as $countGroup) {
                        $count_sub_type[$countGroup->feel_type] = $countGroup->total;
                    }

                    $response = [
                        "html" => $html,
                        "count" => $Data_leak_feed_all,
                        "darkweb" => $count_sub_type["darkweb"],
                        "social" => $count_sub_type["social"],
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

    public function web_defacement_load_card(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'web_defacement'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    $keywords = $data['data']['keywords'];
                    $datatype = $data['data']['datatype'];
                    $site = $data['data']['site'];
                    $level = $data['data']['level'];
                    $search = $data['data']['search'];
                    $url = $data['data']['url'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];

                    $html = '';
     

                    $modal = WebdefacmentSetting::where("active", '=', 1)->where("deleted_at",null);

                    $site_id_arr = @$get_role_custom_first['site_id_arr'];
                    if(@$get_role_custom_first['superadmin'] == 1) {
                        
                    }else if(@$get_role_custom_first['client'] == 1) {
                        $modal = $modal->whereIn('site_id', $site_id_arr);
                    }else if(@$get_role_custom_first['site_support'] == 1) {
                        $modal = $modal->whereIn('site_id', $site_id_arr);
                    }else if(@$get_role_custom_first['site_admin'] == 1) {
                        $modal = $modal->whereIn('site_id', $site_id_arr);
                    }else if(@$get_role_custom_first['site_client'] == 1) {
                        $modal = $modal->whereIn('site_id', $site_id_arr);
                    }


                    if ($search == 1) {
                

                        if ($keywords) {
                            $modal = $modal->where('name', 'LIKE', '%' . $keywords . '%')
                            ->orWhere('url', 'LIKE', '%' . $keywords . '%');
                        }

                        if ($datatype) {
                            // dd($datatype);
                            
                            $modal = $modal->whereIn('status_val', $datatype);
                            // dd($modal);
                        }
                        
                        
                    }

                    if ($site) {
                        $modal = $modal->where('site_id', '=', $site);
                        
                    }

                    if($level){
                        if($level =='High'){
                            $modal = $modal->where('status_val', 'High');
                        }
                        else if($level =='Normal'){
                            $modal = $modal->where('status_val', 'Normal');
                        }
                        else if($level =='Medium'){
                            $modal = $modal->where('status_val', 'Medium');
                        }
                    }
                        
                    $modal = $modal->get();


                    foreach ($modal as $key) {
                        $html .= 
                        '<div class="item-wdfm wdfm-inner-4">
                            <div class="wdfm-card">
                                <div class="wdfm-header">
                                    <div class="wdfm-img">
                                        <a href="'.env('URL_CENTER_PUBLISH').@$key->image_last.'" data-lightbox="name-img-2" >
                                            <img src="'.env('URL_CENTER_PUBLISH').@$key->image_last.'" onerror="setDefaultPic(this)"/>
                                        </a>
                                    </div>
                                </div>
                                <div class="wdfm-body">
                                    <div class="wdfm-btn">
                                        <a href="'.$url.'/webdefacement/detail/'.@$key->code.'" class="btn btn-icon btn-default btn-sm" data-rel="tooltip" title="View" data-placement="bottom">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                    <h4 class="wdfm-elip">'.@$key->name.'</h4>
                                    <p class="mdfm-text-muted">'.@$key->url.'</p>
                                </div>
                                <div class="wdfm-footer">
                                    <div class="wdfm-ft-left flex">
                                        <div><strong>Site </strong>: '.@$key->get_site->name.'</div>
                                        <div class="status-flex mr-2"><strong>Status</strong> : &nbsp; '.@get_webdefacment_status($key->status_val,'color').'</div>
                                        <div class="text-sm-date">Last Online: '.@$key->last_online.'</div>
                                        <div class="text-sm-date">Last Check: '.@$key->last_check.'</div>
                                    </div>
                                </div>
                                <div class="wdfm-footer-action">
                                    <div style="display: flex;justify-content:center;">';
                            
                            
                        
                            $html .= '<a href="'.$url.'/webdefacement/detail/'.@$key->code.'" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a>';
                            if(@$get_role_custom_first['superadmin'] == 1 || @$get_role_custom_first['client'] == 1) {
                                $html .= '<a href="#" onclick="btn_click_edit_webdefacement(\''.$key->code.'\')" class="btn btn-info btn-sm"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.94 74.17l-60.11-60.11c-18.75-18.75-49.16-18.75-67.91 0l-56.55 56.55 128.02 128.02 56.55-56.55c18.75-18.75 18.75-49.15 0-67.91zm-246.8-20.53c-15.62-15.62-40.94-15.62-56.56 0L75.8 172.43c-6.25 6.25-6.25 16.38 0 22.62l22.63 22.63c6.25 6.25 16.38 6.25 22.63 0l101.82-101.82 22.63 22.62L93.95 290.03A327.038 327.038 0 0 0 .17 485.11l-.03.23c-1.7 15.28 11.21 28.2 26.49 26.51a327.02 327.02 0 0 0 195.34-93.8l196.79-196.79-82.77-82.77-84.85-84.85z"></path></svg> Edit</a>
                                    <a href="#" onclick="btn_click_del_webdefacement('.$key->id.')" class="btn btn-danger btn-sm btn_del_webdefacment"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z"></path></svg> Delete</a>
                                ';
                            }
                    

                            $html .= '
                                    </div>
                                </div>
                            </div>
                        </div>';                    
                    }


                    $response = [
                        "html" => $html,
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

    public function web_defacement_detail(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'web_defacement'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $site_code = $data['data']['site_code'];
                    $code = $data['data']['code'];
                    $get_role_custom_first = $data['data']['get_role_custom_first'];

                    $WebdefacmentSetting = WebdefacmentSetting::where("code", $code)->where('deleted_at', null)->where('active', 1)->with('get_site')->with('get_webdefacment_data_original_detail')->with('get_webdefacment_data_check_detail')->with('get_webdefacment_data_log_detail');
        
                    $site_id_arr = @$get_role_custom_first['site_id_arr'];
                    if(@$get_role_custom_first['superadmin'] == 1) {
                        
                    }else if(@$get_role_custom_first['client'] == 1) {
                        $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
                        ->whereIn('id', $site_id_arr)//['49', '56']
                        ->get();
                    }else if(@$get_role_custom_first['site_support'] == 1) {
                        $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
                        ->whereIn('id', $site_id_arr)//['49', '56']
                        ->get();
                    }else if(@$get_role_custom_first['site_admin'] == 1) {
                        $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
                        ->whereIn('id', $site_id_arr)//['49', '56']
                        ->get();
                    }else if(@$get_role_custom_first['site_client'] == 1) {
                        $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
                        ->whereIn('id', $site_id_arr)//['49', '56']
                        ->get();
                    }

                    if(count($site_id_arr) > 0) {
                        $WebdefacmentSetting = $WebdefacmentSetting->whereIn('site_id' , $site_id_arr);
                    }
                    $WebdefacmentSetting = $WebdefacmentSetting->first();

                    $response = [
                        "code" => @$code,
                        "webdefacement" => $WebdefacmentSetting,
                        "webdefacment_data_original" => @$data['webdefacement']->get_webdefacment_data_original_detail[0],
                        "webdefacment_data_check" => @$data['webdefacement']->get_webdefacment_data_check_detail[0],
                        "webdefacment_data_log" => @$data['webdefacement']->get_webdefacment_data_log_detail,
                        "site_code" => @$site_code,
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

    public function web_defacement_update_original(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'web_defacement'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    ini_set('max_execution_time', '1000');
                    $webdefacment_id = $data['data']['webdefacment_id'];
                    $command = 'app:WebDefacementUpdateOriginal';
                    $params = [
                            'webdefacment_id' => $webdefacment_id,
                    ];
                    Artisan::call($command, $params);
                    $result = Artisan::output();
                    
                    $response = [
                        "data" => $result,
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

    public function web_defacement_update_original_detail(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'web_defacement'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $id = $data['data']['id'];

                    $webdefacement = WebdefacmentSetting::where('id', $id)->first();
                    $webdefacement_original = WebdefacmentDataOriginal::where('webdefacment_setting_id', $id)->orderBy('created_at', 'desc')->first();
                    $html_h = $webdefacement_original->hash;
                    $html_f = $webdefacement_original->filesize;
                    $html_e = $webdefacement_original->element;
                    $html_b = @$webdefacement->blacklist_keyword_content;
                    $html_l = $webdefacement_original->last_update;

                    $response = [
                        "html_h" => @$html_h,
                        "html_f" => $html_f,
                        "html_e" => @$html_e,
                        "html_b" => @$html_b,
                        "html_l" => @$html_l,
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

    public function web_defacement_deface_now(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'web_defacement'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    ini_set('max_execution_time', '1000');
                    $webdefacment_id = $data['data']['webdefacment_id'];
                    $command = 'app:WebDefacementProccessbyWebdefacment_id';
                    $params = [
                        'webdefacment_id' => $webdefacment_id,
                    ];

                    Artisan::call($command, $params);
                    
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
            return response()->json($response);
        }
    }

    public function web_defacement_deface_now_detail(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'web_defacement'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $id = $data['data']['id'];

                    $webdefacement = WebdefacmentSetting::where('id', $id)->first();
                    $webdefacement_check = WebdefacmentDataCheck::where('webdefacment_setting_id', $id)->first();
                    $html_h = @$webdefacement_check->hash_new;
                    $html_f = @formatSizeUnits(@$webdefacement_check->filesize_new) . ' (Difference ' . @$webdefacement_check->filesize_percent . '%)';
                    $html_e = @$webdefacement_check->element_new;
                    $html_b = @$webdefacement->blacklist_keyword_current;
                    $html_l = @$webdefacement_check->last_update;

                    $response = [
                        "html_h" => @$html_h,
                        "html_f" => @$html_f,
                        "html_e" => @$html_e,
                        "html_b" => @$html_b,
                        "html_l" => @$html_l,
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

    public function web_defacement_update_image(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                if($data['data']['menu'] !== 'web_defacement'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }
                    ini_set('max_execution_time', '1000');
                    $webdefacment_id = $data['data']['id'];
                    $webdefacement = WebdefacmentSetting::where('id', $webdefacment_id)->first();

                    if($webdefacement->get_webdefacment_data_original_detail[0]->url_id){
                        $url_id = $webdefacement->get_webdefacment_data_original_detail[0]->url_id;
                    }else{
                        $url_id = 0;
                    }

                    $site_id = $webdefacement->site_id;
                    $url_web = $webdefacement->url;
                    $port_web = $webdefacement->port;
                    $delay_screenshot_val = $webdefacement->delay_screen_shot_val;
               
                    $command = 'app:WebDefacementsCreenshotCheck';
            
                    $params = [
                            'url' => $url_web,
                            'port' => $port_web,
                            'site_id' => $site_id,
                            'url_id' => $url_id,
                            'delay' => $delay_screenshot_val,
                    ];
            
                        Artisan::call($command, $params);
                        $result = Artisan::output();
                    
                    $response = [
                        "data" => $result,
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
                        $result .=  '<a href="'.$url.'/indicators/tags'.$tag.'">'.$tag.'</a> ,';
                    } else if ($type == 'groups') {
                        $result .=  '<a href="'.$url.'/indicators/groups'.$tag.'">'.$tag.'</a> ,';
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
