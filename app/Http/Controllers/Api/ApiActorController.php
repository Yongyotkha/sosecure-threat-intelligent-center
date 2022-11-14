<?php

namespace App\Http\Controllers\Api;

use App\FXCategories;
use App\FXDataDatacveMapping;
use App\FXTechniques;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\MonitoringVulnerabilitys\Entities\CVEAssets;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
use Modules\MonitoringVulnerabilitys\Entities\CVEMappingAssets;
use Modules\RSSFeedSettings\Entities\RSSData;
use Modules\RSSFeedSettings\Entities\RSSNews;
use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Modules\SiteSettings\Entities\DataCveven;
use Yajra\DataTables\DataTables;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\Regex;
use MongoDB\Client;
use MongoDB\Client as MongoClient;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Modules\SiteSettings\Entities\SiteSettings;

class ApiActorController extends ApiController
{
    public function ActorIndex(Request $request)
    {
        try 
        {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) 
            {
                $response =[
                    'error' => 'The request parameters are invalid',
                    'status_code' => 400,
                    'data' => []
                ];
            } 
            else 
            {
                // $data_key = $data['data'];

                $data['page'] = "Actor";
                $data['page_chk'] = @$data['data']['page_chk'] ? $data['data']['page_chk'] : 'actor';

                $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                $client = new MongoClient($DB_MONGO_KEY);
                if(app()->environment('local'))
                {
                    $conllection_count_actor = $client->sosecure_threatintelligent->fx_otx_adversaries;
                    $conllection_adversaries_related = $client->sosecure_threatintelligent->fx_otx_adversaries_related;
                    $conllection_count_campaigne = $client->sosecure_threatintelligent->fx_otx_campaign;

                    $conllection = $client->sosecure_threatintelligent->fx_otx_adversaries_related;
                }
                else
                {
                    $conllection_count_actor = $client->sosecure_threatintelligent_test->fx_otx_adversaries;
                    $conllection_adversaries_related = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
                    $conllection_count_campaigne = $client->sosecure_threatintelligent_test->fx_otx_campaign;

                    $conllection = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
                }

                $query = [
                    'delete_at' => null
                ];
                $option = [];

                $query_campaign = [
                    'delete_at' => null
                ];
                $option_campaign = [];

                $query_actor = $conllection_count_actor->find($query,$option);
                $final_actor = $query_actor->toArray();
                $count_actor = count($final_actor);

                $final_techniques = FXTechniques::get();
                $count_techniques = count($final_techniques);

                $select_campaign = $conllection_count_campaigne->find($query_campaign,$option_campaign);
                $final_campaign = $select_campaign->toArray();
                $count_campaign = count($final_campaign);

                // ---------------------- recent activity actor ------------------------------

                $option_activity = 
                [ // base array
                    [
                        '$match' =>
                        [
                            'join' => 'actor',
                            'delete_at' => null,
                        ],
                    ],
                    [
                        '$group' => 
                        [
                            "_id" =>
                            [
                                'adversary_uuid' => '$adversary_uuid',
                                'adversary_name' => '$adversary_name',
                            ],
                            // "count" => [ '$sum' => 1 ],
                            'count' => [ '$max' => '$modified' ],
                        ],
                    ],
                    [
                        '$project' => 
                        [
                            'adversary_uuid' => '$_id.adversary_uuid',
                            'adversary_name' => '$_id.adversary_name',
                            'count' => '$count',
                            // 'modified' => [ '$max' => '$modified' ],
                        ]
                    ],
                    [
                        '$sort' => 
                        [
                            'count' => -1
                        ]
                    ],
                    [
                        '$limit' => 4,
                    ],
                ];
                
                $test_related = $conllection->aggregate($option_activity);
                $test_related = $test_related->toArray();

                $data_recent_activity = [];
                foreach($test_related as $data_related)
                {
                    if(app()->environment('local'))
                    {
                        $select_img = $client->sosecure_threatintelligent->fx_otx_adversaries;
                    }
                    else
                    {
                        $select_img = $client->sosecure_threatintelligent_test->fx_otx_adversaries;
                    }
                    $query_img = ['adversary_uuid' => $data_related->_id['adversary_uuid']];
                    $option_img = [];
                    $query_img = $select_img->findOne($query_img,$option_img);

                    $data_all = [];
                    $data_all['id'] = $data_related->_id['adversary_uuid'];
                    $data_all['name'] = $data_related->_id['adversary_name'];
                    $data_all['count'] = $data_related->count;

                    if(@$query_img['logo'])
                    {
                        $data_all['logo'] = @$query_img['logo'];
                    }
                    else
                    {
                        $data_all['logo'] = '/asset_salepage/images/AgentBasedDetection.png';
                    }

                    $data_recent_activity[] = $data_all;
                }

                // -------------------- end recent activity actor ----------------------------

                // ---------------------- recent activity event ------------------------------

                $option_activity_technique = 
                [ // base array
                    [
                        '$match' =>
                        [
                            'join' => 'actor',
                            'delete_at' => null
                        ],
                    ],
                    [
                        '$group' => 
                        [
                            "_id" =>
                            [
                                'pulse_id' => '$pulse_id',
                                'mode' => '$mode',
                            ],
                            'count' => [ '$max' => '$modified' ],
                        ],
                    ],
                    [
                        '$project' => 
                        [
                            'pulse_id' => '$_id.pulse_id',
                            'mode' => '$_id.mode',
                            'count' => '$count',
                        ]
                    ],
                    [
                        '$sort' => 
                        [
                            'count' => -1
                        ]
                    ],
                    [
                        '$limit' => 2,
                    ],
                ];

                $query_activity = $conllection->aggregate($option_activity_technique);
                $final_activity = $query_activity->toArray();
                
                $data_activity = [];
                foreach($final_activity as $dataa)
                {
                    if($dataa->mode == 'news')
                    {
                        $data_news = [];
                        $query_new = RSSNews::where('id', $dataa->pulse_id)->first();
                        $data_news['detail'] = $query_new;
                        $data_news['mode'] = 'news';

                        $data_activity[] = $data_news;
                    }
                    else if($dataa->mode == 'indicator')
                    {
                        $data_indi = [];
                        $conllection_events = $client->sosecure_threatintelligent->fx_otx_events;
                        $query_events = [
                            'pulse_id' => $dataa['pulse_id']
                        ];
                        $option_events = [];
                
                        $query_events = $conllection_events->findOne($query_events,$option_events);
                        $data_indi['detail'] = $query_events;
                        $data_indi['mode'] = 'indicator';

                        $data_activity[] = $data_indi;
                    }
                    else if($dataa->mode == 'vulnerabilities')
                    {
                        $data_cve = [];
                        $query_cve =  CVEMapping::where('id', $dataa['pulse_id'])->first();
                        $data_cve['detail'] = $query_cve;
                        $data_cve['mode'] = 'vulnerabilities';

                        $data_activity[] = $data_cve;
                    }

                    
                }

                // -------------------- end recent activity event ----------------------------
                
                // return view('monitoringvulnerabilitys::actor', compact('count_actor','count_campaign','count_techniques','data_recent_activity','data_activity'))->with($data);
                // return view('monitoringvulnerabilitys::actor')->with($data);

                $response = [
                    'count_actor' => $count_actor,
                    'count_campaign' => $count_campaign,
                    'count_techniques' => $count_techniques,
                    'data_recent_activity' => $data_recent_activity,
                    'data_activity' => $data_activity,
                    'data' => [
                        'page' => $data['page'],
                        'page_chk' => $data['page_chk']
                    ],
                    'page' => $data['page']
                ];

            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } 
        catch (\Exception $e) 
        {
            $response = array(
                'status_code' => 500,
                'error' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas,]);
        }
    }

    public function ActorDataTable(Request $request)
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
                if($data['data']['menu'] !== 'actor'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $filter_name = @$data['data']['filter_name'];
                    $status_actor = @$data['data']['status_actor'];

                    // $input = $request->all();

                    // $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                    // $client = new MongoClient($DB_MONGO_KEY);
                    // if(app()->environment('local'))
                    // {
                    //     $collection = $client->sosecure_threatintelligent->fx_otx_adversaries;
                    // }
                    // else
                    // {
                    //     $collection = $client->sosecure_threatintelligent_test->fx_otx_adversaries;
                    // }
                    // // $db_name = 'sosecure_threatintelligent_test';
                    // // $db = $client->$db_name;
                    // // $collection = $db->fx_otx_adversaries;

                    // $query = [
                    //     'delete_at' => null
                    // ];

                    // if($filter_name != null)
                    // {
                    //     $query['name'] = new \MongoDB\BSON\Regex($filter_name);          
                    // }

                    // if($status_actor != null)
                    // {
                    //     if($status_actor == "0") 
                    //     {
                    //         $query['status'] = "0";
                            
                    //     } 
                    //     else if ($status_actor == "1") 
                    //     {
                    //         $query['status'] = "1";
                    //     }
                    // }

                    // // dd($query);

                    // $option = [];

                    // $final = $collection->find($query,$option);

                    // $result = $final->toArray();

                    // // return DataTables::of($result)
                    // //     ->make(true);
                    //     // ->toJson();

                    // $data_count = count($result);

                    // $response = [
                    //     "recordsFiltered_count"=> $data_count,
                    //     "recordsTotal_count" => $data_count,
                    //     // "data" => DataTables::of($result->skip(@$data['data']['start'])->take(@$data['data']['length'])->get())->toJson(),
                    //     // ->rawColumns(['feedcontent','get_brand_abuse_feed_one.feedcontent'])
                    //     "data" => DataTables::of($result)->toJson(),
                    // ];

                    $response = [
                        "filter_name"=> @$filter_name,
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

    public function CampDataTable(Request $request)
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
                if($data['data']['menu'] !== 'actor'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $filter_name = @$data['data']['filter_name'];
                    $status_actor = @$data['data']['status_actor'];

                    // $input = $request->all();

                    $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                    $client = new MongoClient($DB_MONGO_KEY);
                    if(app()->environment('local'))
                    {
                        $collection = $client->sosecure_threatintelligent->fx_otx_campaign;
                        $collection_related = $client->sosecure_threatintelligent->fx_otx_adversaries_related;
                    }
                    else
                    {
                        $collection = $client->sosecure_threatintelligent_test->fx_otx_campaign;
                        $collection_related = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
                    }

                    $query = [
                        'delete_at' => null
                    ];

                    if($filter_name != null)
                    {
                        $query['name'] = new \MongoDB\BSON\Regex($filter_name);          
                    }

                    if($status_actor != null)
                    {
                        if($status_actor == "0") 
                        {
                            $query['status'] = "0";
                            
                        } 
                        else if ($status_actor == "1") 
                        {
                            $query['status'] = "1";
                        }
                    }

                    $option = [];

                    $final = $collection->find($query,$option);
                    $result = $final->toArray();

                    for($i=0;$i<count($result);$i++)
                    {
                        $data_related = [];

                        $query_related = [
                            'adversary_uuid' => $result[$i]->campainge_uuid,
                            'mode' => 'campainge',
                            'join' => 'techniques',
                            'delete_at' => null
                        ];
                        $option_related = [];
                
                        $final_related = $collection_related->find($query_related,$option_related);
                        $result_related = $final_related->toArray();
                        $result[$i]['related'] = $result_related;

                        $query_related_actor = [
                            'adversary_uuid' => $result[$i]->campainge_uuid,
                            'mode' => 'campainge',
                            'join' => 'actor',
                            'delete_at' => null
                        ];
                        $option_related_actor = [];
                
                        $final_related_actor = $collection_related->find($query_related_actor,$option_related_actor);
                        $result_related_actor = $final_related_actor->toArray();
                        $result[$i]['related_actor'] = $result_related_actor;
                    }

                    // dd($result[0]['related'][0]->adversary_uuid);

                    // return DataTables::of($result)
                    //     ->make(true);
                    //     // ->toJson();

                    $data_count = count($result);

                    $response = [
                        "recordsFiltered_count"=> $data_count,
                        "recordsTotal_count" => $data_count,
                        // "data" => DataTables::of($result->skip(@$data['data']['start'])->take(@$data['data']['length'])->get())->toJson(),
                        // ->rawColumns(['feedcontent','get_brand_abuse_feed_one.feedcontent'])
                        "data" => DataTables::of($result)->toJson(),
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

    public function TechDataTable(Request $request)
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
                if($data['data']['menu'] !== 'actor'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $filter_name = @$data['data']['filter_name'];
                    $status_actor = @$data['data']['status_actor'];

                    // $input = $request->all();

                    $query_tech = FXTechniques::where('id', '>', 1);

                    if($filter_name != null)
                    {
                        $query_tech->where('tactics_id', 'like', '%'.$filter_name.'%')
                                ->orwhere('tactics_name', 'like', '%'.$filter_name.'%')
                                ->orwhere('code', 'like', '%'.$filter_name.'%')
                                ->orwhere('name', 'like', '%'.$filter_name.'%');
                    }

                    if($status_actor != null)
                    {
                        $query_tech->where('status', $status_actor);
                    }

                    // return DataTables::of($query_tech)
                    //     ->make(true);
                    //     // ->toJson();

                    $data_count = count($query_tech);

                    $response = [
                        "recordsFiltered_count"=> $data_count,
                        "recordsTotal_count" => $data_count,
                        // "data" => DataTables::of($query_tech->skip(@$data['data']['start'])->take(@$data['data']['length'])->get())->toJson(),
                        // ->rawColumns(['feedcontent','get_brand_abuse_feed_one.feedcontent'])
                        "data" => DataTables::of($query_tech)->toJson(),
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

    public function chart_actor(Request $request)
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
                if($data['data']['menu'] !== 'actor'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $filter_name = @$data['data']['filter_name'] ? $data['data']['filter_name'] : '';
                    // $status_actor = @$data['data']['status_actor'];

                    // $input = $request->all();

                    // dd($input);

                    // if($request->filter_name != null)
                    // {
                    //     $filter_name = $request->filter_name;
                    // }
                    // else
                    // {
                    //     $filter_name = '';
                    // }

                    $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                    $client = new MongoClient($DB_MONGO_KEY);
                    if(app()->environment('local'))
                    {
                        $conllection = $client->sosecure_threatintelligent->fx_otx_adversaries_related;
                    }
                    else
                    {
                        $conllection = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
                    }
                    // $conllection = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;

                    $option_chart_actor = 
                    [ // base array
                        [
                            '$match' =>
                            [
                                'adversary_name' => new \MongoDB\BSON\Regex($filter_name),
                                'join' => 'actor',
                                'delete_at' => null
                            ],
                        ],
                        [
                            '$group' => 
                            [
                                "_id" =>
                                [
                                    // 'adversary_uuid' => '$adversary_uuid',
                                    'adversary_name' => '$adversary_name',
                                ],
                                "count" => [ '$sum' => 1 ],
                            ],
                        ],
                        [
                            '$project' => 
                            [
                                // 'adversary_uuid' => '$_id.adversary_uuid',
                                'adversary_name' => '$_id.adversary_name',
                                'count' => '$count',
                                // 'modified' => [ '$max' => '$modified' ],
                            ]
                        ],
                        [
                            '$sort' => 
                            [
                                'count' => -1
                            ]
                        ],
                        [
                            '$limit' => 10,
                        ],
                    ];

                    $select_chart_actor = $conllection->aggregate($option_chart_actor);
                    $select_chart_actor = $select_chart_actor->toArray();

                    $data_all_chart_actor = [];
                    foreach($select_chart_actor as $data_chart_actor)
                    {
                        $data_all = [];
                        // $data_all['id'] = $data_chart_actor->_id['adversary_uuid'];
                        // $data_all['name'] = $data_chart_actor->_id['adversary_name'];
                        // $data_all['count'] = $data_chart_actor->count;
                        // $data_all_chart_actor[] = $data_all;
                        $data_all_chart_actor[] = [$data_chart_actor->_id['adversary_name'],$data_chart_actor->count];
                    }

                    $data_transcation = json_encode($data_all_chart_actor);
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

    public function chart_techniques(Request $request)
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
                if($data['data']['menu'] !== 'actor'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $filter_name = @$data['data']['filter_name'] ? $data['data']['filter_name'] : '';
                    // $status_actor = @$data['data']['status_actor'];

                    // $input = $request->all();

                    // dd($input);

                    // if($request->filter_name != null)
                    // {
                    //     $filter_name = $request->filter_name;
                    // }
                    // else
                    // {
                    //     $filter_name = '';
                    // }

                    $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                    $client = new MongoClient($DB_MONGO_KEY);
                    if(app()->environment('local'))
                    {
                        // dd('local');
                        $conllection = $client->sosecure_threatintelligent->fx_otx_adversaries_related;
                    }
                    else
                    {
                        // dd('like');
                        $conllection = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
                    }

                    $option_chart_techniques = 
                    [ // base array
                        [
                            '$match' =>
                            [
                                'techniques' => new \MongoDB\BSON\Regex($filter_name),
                                'join' => 'techniques',
                                'delete_at' => null,
                            ],
                        ],
                        [
                            '$group' => 
                            [
                                "_id" =>
                                [
                                    // 'adversary_uuid' => '$adversary_uuid',
                                    // 'adversary_name' => '$adversary_name',
                                    'techniques' => '$techniques',
                                ],
                                "count" => [ '$sum' => 1 ],
                            ],
                        ],
                        [
                            '$project' => 
                            [
                                // 'adversary_uuid' => '$_id.adversary_uuid',
                                // 'adversary_name' => '$_id.adversary_name',
                                'techniques' => '$_id.techniques',
                                'count' => '$count',
                                // 'modified' => [ '$max' => '$modified' ],
                            ]
                        ],
                        [
                            '$sort' => 
                            [
                                'count' => -1
                            ]
                        ],
                        [
                            '$limit' => 10,
                        ],
                    ];

                    $select_chart_techniques = $conllection->aggregate($option_chart_techniques);
                    $select_chart_techniques = $select_chart_techniques->toArray();

                    $data_all_chart_techniques = [];
                    foreach($select_chart_techniques as $data_chart_techniques)
                    {
                        $data_all = [];
                        // $data_all['id'] = $data_chart_techniques->_id['adversary_uuid'];
                        // $data_all['name'] = $data_chart_techniques->_id['adversary_name'];
                        // $data_all['count'] = $data_chart_techniques->count;
                        // $data_all_chart_techniques[] = $data_all;
                        $data_all_chart_techniques[] = [$data_chart_techniques->_id['techniques'],$data_chart_techniques->count];
                    }

                    $data_transcation = json_encode($data_all_chart_techniques);
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

    public function ActorDetailRelatedNew(Request $request)
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
                if($data['data']['menu'] !== 'actor'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $hd_uuid = @$data['data']['hd_uuid'];

                    $input = $request->all();

                    $DB_MONGO_KEY = config('app.DB_MONGO_DEV');
                    $client = new MongoClient($DB_MONGO_KEY);
                    if(app()->environment('local'))
                    {
                        $conn = $client->sosecure_threatintelligent->fx_otx_adversaries_related;
                    }
                    else
                    {
                        $conn = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
                    }
                    // $conn = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;

                    $query = [
                        'mode' => 'news',
                        'adversary_uuid' => $hd_uuid,
                        'delete_at' => null
                    ];

                    $option = [];

                    $final = $conn->find($query, $option);
                    $result = $final->toArray();

                    // dd( $result );

                    $id_new = array();
                    foreach($result as $data)
                    {
                        $id_new[] = $data['pulse_id'];
                    }

                    $count_news = count($id_new);

                    $query_news = array();
                    for($i = 0 ; $i < $count_news ; $i++)
                    {
                        $detail_new = RSSNews::where('id', $id_new[$i])->first();
                        // dd($id_new[$i]);
                        $result[$i]['new_detail'] = $detail_new;
                        // $test = $result[$i]['new_detail'];
                        // $result[$i]['new_detail']['detail_th'] = strip_tags($test->detail_th);
                    }

                    $data_count = count($result);

                    $response = [
                        "recordsFiltered_count"=> $data_count,
                        "recordsTotal_count" => $data_count,
                        // "data" => DataTables::of($result->skip(@$data['data']['start'])->take(@$data['data']['length'])->get())->toJson(),
                        // ->rawColumns(['feedcontent','get_brand_abuse_feed_one.feedcontent'])
                        "data" => DataTables::of($result)->toJson(),
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

    public function ActorDetailRelatedCVE(Request $request)
    {
        $input = $request->all();

        $DB_MONGO_KEY = config('app.DB_MONGO_DEV');
        $client = new MongoClient($DB_MONGO_KEY);
        if(app()->environment('local'))
        {
            $conn = $client->sosecure_threatintelligent->fx_otx_adversaries_related;
        }
        else
        {
            $conn = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
        }
        // $conn = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;

        $query = [
            'mode' => 'vulnerabilities',
            'adversary_uuid' => $request->hd_uuid,
            'delete_at' => null
        ];

        $option = [];

        $final = $conn->find($query,$option);
        $result = $final->toArray();

        $id_new = array();
        foreach($result as $data)
        {
            $id_new[] = $data['pulse_id'];
        }

        $count_news = count($id_new);

        $FXDataDatacveMapping = new FXDataDatacveMapping();

        $query_news = array();
        for($i = 0 ; $i < $count_news ; $i++)
        {
            $result[$i]['cve_detail'] = $FXDataDatacveMapping->where('id', $id_new[$i])->first();
        }

        return DataTables::of($result)
            ->make(true);
    }

    public function ActorDetailRelatedIndi(Request $request)
    {
        $input = $request->all();

        $DB_MONGO_KEY = config('app.DB_MONGO_DEV');
        $client = new MongoClient($DB_MONGO_KEY);
        if(app()->environment('local'))
        {
            $conn = $client->sosecure_threatintelligent->fx_otx_adversaries_related;
            $collection_event = $client->sosecure_threatintelligent->fx_otx_events;
        }
        else
        {
            $conn = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
            $collection_event = $client->sosecure_threatintelligent->fx_otx_events;
        }

        $query = [
            'mode' => 'indicator',
            'adversary_uuid' => $request->hd_uuid,
            'delete_at' => null
        ];

        $option = [];

        $final = $conn->find($query,$option);
        $result = $final->toArray();

        $id_indi = array();
        foreach($result as $data)
        {
            $id_indi[] = $data['pulse_id'];
        }

        $count_indi = count($id_indi);

        // dd($id_indi);

        $data = [];
        if($count_indi > 0)
        {
            foreach($id_indi as $data_uuid)
            {
                $query_indi = [
                    'pulse_id' => $data_uuid,
                    'delete_at' => null
                ];
        
                $option_indi = [];
        
                $final_indi = $collection_event->findOne($query_indi,$option_indi);
                // $result_indi = $final_indi->toArray();

                $data[] = $final_indi;
                // dd($final_indi['pulse_id']);
            }
        }
        else
        {

        }

        return DataTables::of($data)
            ->addColumn('contant', function($data) {
                $html = '';
                $html .= '
                    <div class="d-flex-actor">
                        <div class="details-actor">
                            <h3 class="text-dark"> 
                                '.$data->name.'
                            </h3>
                            <p>
                                '.$data->description.'
                            </p>
                            <div class="btw-text">
                                <p>
                                    <strong>Attribute </strong> : '.$data->indicator_count.'
                                </p>
                            </div>
                            <div class="btw-text">
                                <p>
                                    <strong>Tags </strong> : '.$data->tags.'
                                </p>
                                &emsp;
                                <p>
                                    <strong>Groups </strong> : '.$data->groups.'
                                </p>
                            </div>
                        </div>
                    </div>
                ';
                return $html;
            })
            ->rawColumns(['contant'])
            ->make(true);
    }

    public function ActorDetailRelatedCampainge(Request $request)
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
                if($data['data']['menu'] !== 'actor'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $hd_uuid = @$data['data']['hd_uuid'];

                    // $input = $request->all();

                    $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                    $client = new MongoClient($DB_MONGO_KEY);
                    if(app()->environment('local'))
                    {
                        $collection = $client->sosecure_threatintelligent->fx_otx_campaign;
                        $collection_related = $client->sosecure_threatintelligent->fx_otx_adversaries_related;
                    }
                    else
                    {
                        $collection = $client->sosecure_threatintelligent_test->fx_otx_campaign;
                        $collection_related = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
                    }


                    $query_related_camp = [
                        'actor_id' => $hd_uuid,
                        'mode' => 'campainge',
                        'join' => 'actor'
                    ];
                    $option_related_camp = [];

                    $option_group = [
                        [
                            '$group' => [
                                '_id' => '$adversary_uuid'
                            ]
                        ]
                    ];

                    $final_related_camp = $collection_related->find($query_related_camp,$option_related_camp);
                    $result_related_camp = $final_related_camp->toArray();
                    
                    $arr_camp = [];
                    if(count($result_related_camp) > 0)
                    {
                        foreach($result_related_camp as $data_camp)
                        {
                            if(!in_array($data_camp['adversary_uuid'], $arr_camp))
                            {
                                $arr_camp[] = $data_camp['adversary_uuid'];
                            }
                            // $arr_camp[] = $data_camp['adversary_uuid'];
                        }
                    }

                    // dd($arr_camp);

                    $result_table = [];

                    foreach($arr_camp as $data_camp)
                    {
                        $query = [
                            'campainge_uuid' => $data_camp,
                            'delete_at' => null
                        ];
                
                        $option = [];
                
                        $result = $collection->findOne($query,$option);
                        // $result = $final->toArray();

                        // dd($result['name']);
                
                        // for($i=0;$i<count($result);$i++)
                        // {
                            $data_related = [];
                
                            $query_related = [
                                // 'adversary_uuid' => $result[$i]->campainge_uuid,
                                'adversary_uuid' => $result['campainge_uuid'],
                                'mode' => 'campainge',
                                'join' => 'techniques',
                                'delete_at' => null
                            ];
                            $option_related = [];
                    
                            $final_related = $collection_related->find($query_related,$option_related);
                            $result_related = $final_related->toArray();
                            // $result[$i]['related'] = $result_related;
                            $result['related'] = $result_related;
                
                            $query_related_actor = [
                                // 'adversary_uuid' => $result[$i]->campainge_uuid,
                                'adversary_uuid' => $result['campainge_uuid'],
                                'mode' => 'campainge',
                                'join' => 'actor',
                                'delete_at' => null
                            ];
                            $option_related_actor = [];
                    
                            $final_related_actor = $collection_related->find($query_related_actor,$option_related_actor);
                            $result_related_actor = $final_related_actor->toArray();
                            // $result[$i]['related_actor'] = $result_related_actor;
                            $result['related_actor'] = $result_related_actor;
                        // }

                        $result_table[] = $result;
                    }

                    $data_count = count($result_table);

                    $response = [
                        "recordsFiltered_count"=> $data_count,
                        "recordsTotal_count" => $data_count,
                        // "data" => DataTables::of($result_table->skip(@$data['data']['start'])->take(@$data['data']['length'])->get())->toJson(),
                        // ->rawColumns(['feedcontent','get_brand_abuse_feed_one.feedcontent'])
                        "data" => DataTables::of($result_table)->toJson(),
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

    public function ActorDetail(Request $request)
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
                if($data['data']['menu'] !== 'actor'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $_id = @$data['data']['_id'];

                    $data['page'] = "Actor Detail";

                    // $input = $request->all();

                    // dd($input);
                    // if($request->mode == 'cve')
                    // {
                        // $_id = $request->get('_id');

                        $query = [
                            'adversary_uuid' => $_id
                        ];

                    // }
                    // else
                    // {
                    //     $_id = $request->get('_id');

                    //     $query = [
                    //         '_id' => new \MongoDB\BSON\ObjectID($_id)
                    //     ];
                    // }

                    $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                    $client = new MongoClient($DB_MONGO_KEY);
                    if(app()->environment('local'))
                    {
                        $collection = $client->sosecure_threatintelligent->fx_otx_adversaries;
                        $conn = $client->sosecure_threatintelligent->fx_otx_adversaries_related;
                    }
                    else
                    {
                        $collection = $client->sosecure_threatintelligent_test->fx_otx_adversaries;
                        $conn = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
                    }
                    // $db_name = 'sosecure_threatintelligent_test';
                    // $collection = $client->$db_name->fx_otx_adversaries;

                    $option = [];

                    $final = $collection->find($query,$option);
                    // $final = $collection->find(array('_id' => new \MongoDB\BSON\ObjectID($_id)));

                    $result = $final->toArray();

                    // dd($result);

                    $data_detail = array();
                    foreach($result as $detail)
                    {
                        if(@$detail->logo)
                        {
                            $data_detail['logo'] = $detail->logo;
                        }
                        else
                        {
                            $data_detail['logo'] = '/asset_salepage/images/AgentBasedDetection.png';
                        }

                        $data_detail['uuid'] = $detail->adversary_uuid;
                        $data_detail['name'] = $detail->name;
                        $data_detail['description'] = $detail->description;
                        $data_detail['category'] = $detail->category;
                        $data_detail['tags'] = @$detail->tags;
                        $data_detail['location'] = @$detail->location;
                        // $data_detail['attack_techniques'] = @$detail->attack_techniques;
                    }

                    // ------------------------ count new --------------------------
                    // $conn = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;

                    $query_c_new = [
                        'mode' => 'news',
                        'adversary_uuid' => $data_detail['uuid'],
                        'delete_at' => null
                    ];

                    $option_c_new = [];

                    $final_c_new = $conn->find($query_c_new,$option_c_new);
                    $result_c_new = $final_c_new->toArray();
                    $count_new = count($result_c_new);
                    // ------------------------ count new --------------------------

                    // ------------------------ count cve --------------------------
                    // $conn = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;

                    $query_c_cve = [
                        'mode' => 'vulnerabilities',
                        'adversary_uuid' => $data_detail['uuid'],
                        'delete_at' => null
                    ];

                    $option_c_cve = [];

                    $final_c_cve = $conn->find($query_c_cve,$option_c_cve);
                    $result_c_cve = $final_c_cve->toArray();
                    $count_cve = count($result_c_cve);

                    // dd($data_detail);
                    // ------------------------ count cve --------------------------

                    // ------------------------ count indi --------------------------
                    // $conn = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;

                    $query_c_indi = [
                        'mode' => 'indicator',
                        'adversary_uuid' => $data_detail['uuid'],
                        'delete_at' => null
                    ];

                    $option_c_indi = [];

                    $final_c_indi = $conn->find($query_c_indi,$option_c_indi);
                    $result_c_indi = $final_c_indi->toArray();
                    $count_indi = count($result_c_indi);

                    // dd($data_detail);
                    // ------------------------ count indi --------------------------

                    // ------------------------ count indi --------------------------

                    $query_related_camp = [
                        'actor_id' => $data_detail['uuid'],
                        'mode' => 'campainge',
                        'join' => 'actor'
                    ];
                    $option_related_camp = [];

                    $final_related_camp = $conn->find($query_related_camp,$option_related_camp);
                    $result_related_camp = $final_related_camp->toArray();
                    
                    $arr_camp = [];
                    if(count($result_related_camp) > 0)
                    {
                        foreach($result_related_camp as $data_camp)
                        {
                            if(!in_array($data_camp['adversary_uuid'], $arr_camp))
                            {
                                $arr_camp[] = $data_camp['adversary_uuid'];
                            }
                        }
                    }

                    $count_camp = count($arr_camp);

                    // dd($data_detail);
                    // ------------------------ count indi --------------------------

                    $response = [
                        'data_detail' => $data_detail, 
                        'result' => $result, 
                        'count_new' => $count_new, 
                        'count_cve' => $count_cve, 
                        'count_indi' => $count_indi, 
                        'count_camp' => $count_camp,
                        'data' => [
                            'page' => $data['page']
                        ],
                        'page' => $data['page']
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

    public function CampaingeDetail(Request $request)
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
                if($data['data']['menu'] !== 'actor'){
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                }else{
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if($auth_site['status_code'] !== '200'){
                        return $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    }

                    $_id = @$data['data']['_id'];
                    $mode = @$data['data']['mode'];

                    $data['page'] = "Campainge Detail";

                    // $input = $request->all();
            
                    // // dd($input);
            
                    // $_id = $request->get('_id');
                    // $mode = @$request->mode;

                    $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                    $client = new MongoClient($DB_MONGO_KEY);
                    if(app()->environment('local'))
                    {
                        $collection = $client->sosecure_threatintelligent->fx_otx_campaign;
                        $collection_related = $client->sosecure_threatintelligent->fx_otx_adversaries_related;
                    }
                    else
                    {
                        $collection = $client->sosecure_threatintelligent_test->fx_otx_campaign;
                        $collection_related = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
                    }

                    $query = [
                        '_id' => new \MongoDB\BSON\ObjectID($_id)
                    ];
                    $option = [];

                    $result = $collection->findOne($query,$option);

                    // dd($result['campainge_uuid']);

                    $data_related = [];

                    $query_related = [
                        'adversary_uuid' => $result['campainge_uuid'],
                        'mode' => 'campainge',
                        'join' => 'techniques',
                        'delete_at' => null
                    ];
                    $option_related = [];

                    $final_related = $collection_related->find($query_related,$option_related);
                    $result_related = $final_related->toArray();
                    $result['related'] = $result_related;

                    $arr_tactic = [];

                    foreach($result_related as $related)
                    {
                        $arr_tactic[] = $related->tactics_id;
                    }

                    $result['arr_tactic'] = @$arr_tactic ? implode(', ',$arr_tactic) : ' - ';

                    $query_related_actor = [
                        'adversary_uuid' => $result['campainge_uuid'],
                        'mode' => 'campainge',
                        'join' => 'actor',
                        'delete_at' => null
                    ];
                    $option_related_actor = [];

                    $final_related_actor = $collection_related->find($query_related_actor,$option_related_actor);
                    $result_related_actor = $final_related_actor->toArray();

                    $arr_actor_id = [];
                    $arr_actor_name = [];

                    foreach($result_related_actor as $related_actor)
                    {
                        $arr_actor_id[] = $related_actor->actor_id;
                        $arr_actor_name[] = $related_actor->actor_name;
                    }

                    // $result['related_actor'] = @$result_related_actor;
                    $result['arr_actor_id'] = @$arr_actor_id ? implode(', ',$arr_actor_id) : ' - ';
                    $result['arr_actor_name'] = @$arr_actor_name ? implode(', ',$arr_actor_name) : ' - ';

                    // ------------------------ count new --------------------------

                    $query_c_new = [
                        'mode' => 'news',
                        'adversary_uuid' => $result['campainge_uuid'],
                        'delete_at' => null
                    ];

                    $option_c_new = [];

                    $final_c_new = $collection_related->find($query_c_new,$option_c_new);
                    $result_c_new = $final_c_new->toArray();
                    $count_new = count($result_c_new);

                    // ------------------------ count new --------------------------

                    // ------------------------ count cve --------------------------

                    $query_c_cve = [
                        'mode' => 'vulnerabilities',
                        'adversary_uuid' => $result['campainge_uuid'],
                        'delete_at' => null
                    ];

                    $option_c_cve = [];

                    $final_c_cve = $collection_related->find($query_c_cve,$option_c_cve);
                    $result_c_cve = $final_c_cve->toArray();
                    $count_cve = count($result_c_cve);

                    // ------------------------ count cve --------------------------

                    // ------------------------ count indi --------------------------

                    $query_c_indi = [
                        'mode' => 'indicator',
                        'adversary_uuid' => $result['campainge_uuid'],
                        'delete_at' => null
                    ];

                    $option_c_indi = [];

                    $final_c_indi = $collection_related->find($query_c_indi,$option_c_indi);
                    $result_c_indi = $final_c_indi->toArray();
                    $count_indi = count($result_c_indi);

                    // ------------------------ count indi --------------------------
               
                    $response = [
                        'result' => $result, 
                        'count_new' => $count_new, 
                        'count_cve' => $count_cve, 
                        'count_indi' => $count_indi, 
                        'mode' => $mode, 
                        'data' => [
                            'page' => $data['page']
                        ],
                        'page' => $data['page']
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

}
