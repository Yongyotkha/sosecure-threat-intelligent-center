<?php

namespace App\Http\Controllers\Api;

use App\Entities\IndicatorSummaryYear;
use Illuminate\Http\Request;
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
                

                            $order_number++;
                            $nestedData['No'] = $order_number;
                            $nestedData['name'] = $document["name"];
                            $nestedData['groups'] = explode_val($document["groups"],'groups');
                            $nestedData['tags'] = explode_val($document["tags"],'tags');
                            $nestedData['attr'] = '';
                            $nestedData['attrCount'] = $document["indicator_count"];
                            $nestedData['public'] = ($document["public"]);
                            $nestedData['is_modified'] = ($document["is_modified"]);
                            $nestedData['modified'] = change_date_utc_to_thai($document['modified']);
                            $nestedData['count_view'] = $document["count_view"];
                            $nestedData['pulse_id'] = $document["pulse_id"];
                        
                            // <a href="'.route('indicators.events_detail_select',['id' => $document['pulse_id']]).'" 
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
                        "Action"=>route('indicators.detail_indicator')."?id=".@$cursor_2['indicator_id'].
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
