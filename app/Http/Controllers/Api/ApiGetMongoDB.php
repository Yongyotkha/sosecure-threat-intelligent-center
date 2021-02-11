<?php

namespace App\Http\Controllers\Api;

use App\Bookmark;
use App\DataLeakSocialRef;
use App\Entities\IndicatorSummaryYear;
use App\ReadCategories;
use App\ReadNews;
use App\TransactionTimeStampScans;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

                
                
                $cursor = $cursor->toArray();

                $data_nestedData = array();
                $order_number = $start;
                if(!empty($cursor))
                {
                    foreach ($cursor as $document)
                    {
                
                        if($document["indicator_count"] > 0){
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
                            $nestedData['puls   e_id'] = $document["pulse_id"];
                        
                            // <a href="'.rou   te('indicators.events_detail_select',['id' => $document['pulse_id']]).'" 
                            // class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>
                            
                        
                        $data_nestedData[] = $nestedData;

                        }
                            
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

    public function table_asset(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $Assets_list = [];
                $menu = $data['data']['menu'];
                $site = $data['data']['site'];
                $domaincode = $data['data']['domaincode'];
                $url = $data['data']['url'];

                if($menu=='site'){
                    $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $site)->first();
                    $Assets_data = Assets::where('status', 1)->where('site_id', $SiteSettingsfor->id)->get();
                }else if($menu=='scan'){
                    $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $site)->first();
                    $DomainFor = Domain::withTrashed()->where('code',$domaincode)->first();
                    if($SiteSettingsfor&&$DomainFor){
                        $Assets_data = Assets::where('status', 1)->where('site_id', $SiteSettingsfor->id)->where('domain_id', $DomainFor->id)->get();
                    }
                }else{
                    $SiteSettingsfor = SiteSettings::withTrashed()->where('code', $site)->first();
                    $Assets_data = Assets::where('status', 1)->where('site_id', $SiteSettingsfor->id)->get();
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
                            array_push($CPE_Del, '<span class="il-block" style="box-sizing:border-box; -moz-box-sizing:border-box;">&nbsp;'.'<a href="'.$url.'/asset/assets_delete_cpe/'.$CPE_Datavalue->code.'/'.$menu.'" class="btn btn-xs btn-danger" style="display:inline; font-size: 11px;" data-toggle="ajaxModal"><i class="fas fa-trash"></i></a>'.'</span>');
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
                            $Assets_data_list['cpe'] = '<a href="'.$url.'/asset/assets_add_cpe/'.$value->code.'/'.$menu.'/'.$IP_Listvalue->code.'" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal"><i class="fas fa-plus"></i> Add CPE </a>';
                            if(isset($TTSS->code)){
                                $Assets_data_list['action'] = '<a href="'.$url.'/scans/scans_assets_edit_modal/'.$value->code.'/'.@$TTSS->code.'/'.$menu. '" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">
                                <svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg>
                                </a>';
                            }else{
                                $Assets_data_list['action'] = '<a href="'.$url.'/scans/scans_assets_edit_modal/'.$value->code.'/'.@$value->site_id.'/'.$menu . '" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">
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
                                $Assets_data_list['cpe'] = '<a href="'.$url.'/asset/assets_add_cpe/'.$value->code.'/'.$menu.'/'.$IP_Listvalue->code.'" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal"><i class="fas fa-plus"></i> Add CPE </a>';
                                $Assets_data_list['action'] = '<a href="'.$url.'/scans/scans_assets_edit_modal/'.$value->code.'/'.@$value->site_id.'/'.$menu . '" class="btn btn-xs btn-' . get_option("theme_color") . ' m-xs" data-toggle="ajaxModal">
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
                $dataOut["data"] =  $Assets_list;

                $SiteSettingsfor = SiteSettings::select('id')->where('code', $site)->first();
                // $dataOut["countAssets"] = @AssetsData::whereIn('data_type_id', [5,6])->where('status', 1)->count();
                $dataOut["countAssets"] = @Assets::select('id')->where('site_id', @$SiteSettingsfor->id)->whereHas('get_assets_data', function($q){
                                            $q->whereIn('data_type_id', [5,6]);
                })->count();
                // $dataOut["countWindows"] = @CPEData::whereRaw('LOWER(os_type) = ?', strtolower('WINDOWS'))->count();
                $dataOut["countWindows"] = @CPE::select('id')->whereHas('get_assets', function($q) use ($SiteSettingsfor){
                                            $q->where('os_type', 1)->whereIn('data_type_id', [5,6])->where('site_id', @$SiteSettingsfor->id);
                                        })->count();
                // $dataOut["countLinux"] = @CPEData::whereRaw('LOWER(os_type) = ?', strtolower('LINUX'))->count();
                $dataOut["countLinux"] = @CPE::select('id')->whereHas('get_assets', function($q) use ($SiteSettingsfor){
                    $q->where('os_type', 2)->whereIn('data_type_id', [5,6])->where('site_id', @$SiteSettingsfor->id);
                })->count();

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

    public function index_client(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{ 
                $RSSNews_count = RSSNews::where('save_draft', 0)->where('status', 1)->where('public_date', '<=', Carbon::now())->count();
                // dd($news_all);
                // $RSSNews_count = RSSNews::count("id");
                $RSSNews_all = RSSNews::all();
        
                //<><><>
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
                'SiteSettings' => $SiteSettings,
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
