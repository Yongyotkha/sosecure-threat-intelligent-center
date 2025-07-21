<?php

namespace Modules\MonitoringVulnerabilitys\Http\Controllers;

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

class ApiActorController extends Controller
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
    
    public function ActorIndex()
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
                        'page' => $data['page']
                    ]
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
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function ActorDataTable(Request $request)
    {
        $input = $request->all();

        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $client = new MongoClient($DB_MONGO_KEY);
        if(app()->environment('local'))
        {
            $collection = $client->sosecure_threatintelligent->fx_otx_adversaries;
        }
        else
        {
            $collection = $client->sosecure_threatintelligent_test->fx_otx_adversaries;
        }
        // $db_name = 'sosecure_threatintelligent_test';
        // $db = $client->$db_name;
        // $collection = $db->fx_otx_adversaries;

        $query = [
            'delete_at' => null
        ];

        if($request -> filter_name != null)
        {
            $query['name'] = new \MongoDB\BSON\Regex($request->filter_name);          
        }

        if($request -> status_actor != null)
        {
            if($request -> status_actor == "0") 
            {
                $query['status'] = "0";
                
            } 
            else if ($request -> status_actor == "1") 
            {
                $query['status'] = "1";
            }
        }

        // dd($query);

        $option = [];

        $final = $collection->find($query,$option);

        $result = $final->toArray();

        return DataTables::of($result)
            ->make(true);
            // ->toJson();

    }

    public function CampDataTable(Request $request)
    {
        $input = $request->all();

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

        if($request -> filter_name != null)
        {
            $query['name'] = new \MongoDB\BSON\Regex($request->filter_name);          
        }

        if($request -> status_actor != null)
        {
            if($request -> status_actor == "0") 
            {
                $query['status'] = "0";
                
            } 
            else if ($request -> status_actor == "1") 
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

        return DataTables::of($result)
            ->make(true);
            // ->toJson();

    }

    public function TechDataTable(Request $request)
    {
        $input = $request->all();

        $query_tech = FXTechniques::where('id', '>', 1);

        if($request->filter_name != null)
        {
            $query_tech->where('tactics_id', 'like', '%'.$request->filter_name.'%')
                       ->orwhere('tactics_name', 'like', '%'.$request->filter_name.'%')
                       ->orwhere('code', 'like', '%'.$request->filter_name.'%')
                       ->orwhere('name', 'like', '%'.$request->filter_name.'%');
        }

        if($request->status_actor != null)
        {
            $query_tech->where('status', $request->status_actor);
        }

        return DataTables::of($query_tech)
            ->make(true);
            // ->toJson();

    }

    public function chart_actor(Request $request)
    {
        $input = $request->all();

        // dd($input);

        if($request->filter_name != null)
        {
            $filter_name = $request->filter_name;
        }
        else
        {
            $filter_name = '';
        }

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

        // if ($request->ajax()) {

            return response()->json($data_all_chart_actor);
        // }

        // dd($data_all_chart_actor);
    }

    public function chart_techniques(Request $request)
    {
        $input = $request->all();

        // dd($input);

        if($request->filter_name != null)
        {
            $filter_name = $request->filter_name;
        }
        else
        {
            $filter_name = '';
        }

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

        // dd($data_all_chart_techniques);
        // if ($request->ajax()) {

            return response()->json($data_all_chart_techniques);
        // }

    }

    public function ActorDetailRelatedNew(Request $request)
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
            'mode' => 'news',
            'adversary_uuid' => $request->hd_uuid,
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

        // $test = $result[0]['new_detail']['detail_th'];
        // dd($test);
        // $test = $result[0]['new_detail'];
        // $test2 = strip_tags(htmlspecialchars_decode($test));
        // $test2 = htmlspecialchars_decode($test);
        // $test2 = $test->detail_th;
        // $test2 = strip_tags($test, '<p><span>');
        // $test2 = strtolower($test);

        // foreach($test->detail_th as $fff)
        // {
        //     $test = $fff->removeChild($fff);
        // }


        // dd($test2);

        return DataTables::of($result)
            // ->addColumn('content', function(){
            //     $html = '';
            //     return $html;
            // })
            ->make(true);
            // ->toJson();
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
        $input = $request->all();

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
            'actor_id' => $request->hd_uuid,
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

        // dd($result_table);

        // $query = [
        //     'campainge_uuid' => $request->hd_uuid,
        //     'delete_at' => null
        // ];

        // $option = [];

        // $final = $collection->find($query,$option);
        // $result = $final->toArray();

        // for($i=0;$i<count($result);$i++)
        // {
        //     $data_related = [];

        //     $query_related = [
        //         'adversary_uuid' => $result[$i]->campainge_uuid,
        //         'mode' => 'campainge',
        //         'join' => 'techniques',
        //         'delete_at' => null
        //     ];
        //     $option_related = [];
    
        //     $final_related = $collection_related->find($query_related,$option_related);
        //     $result_related = $final_related->toArray();
        //     $result[$i]['related'] = $result_related;

        //     $query_related_actor = [
        //         'adversary_uuid' => $result[$i]->campainge_uuid,
        //         'mode' => 'campainge',
        //         'join' => 'actor',
        //         'delete_at' => null
        //     ];
        //     $option_related_actor = [];
    
        //     $final_related_actor = $collection_related->find($query_related_actor,$option_related_actor);
        //     $result_related_actor = $final_related_actor->toArray();
        //     $result[$i]['related_actor'] = $result_related_actor;
        // }

        // dd($result[0]['related'][0]->adversary_uuid);

        return DataTables::of($result_table)
            ->make(true);
            // ->toJson();

    }

    public function ActorDetail(Request $request)
    {
        $data['page'] = "Actor Detail";

        $input = $request->all();

        // dd($input);
        // if($request->mode == 'cve')
        // {
            $_id = $request->get('_id');

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

        return view('monitoringvulnerabilitys::actor_detail', compact('data_detail', 'result', 'count_new', 'count_cve', 'count_indi', 'count_camp'))->with($data);
    }

    public function actor_select_category(Request $request)
    {
        $input = $request->all();

        if($request->has('q'))
        {
            $search = $request->q;

            $query = FXCategories::
                where('name', 'like', '%'.$search.'%')
                ->select('name')
                ->get();
        }

        return response()->json($query);
    }

    public function actor_add()
    {
        $edit = false;

        $query_category = FXCategories::select('name')
                ->get();

        return view('monitoringvulnerabilitys::modal.add_actor')->with(compact('edit', 'query_category'));   
    }

    public function create_actor(Request $request)
    {
        $input = $request->all();

        // dd($input);

        $validator = Validator::make($request->all(), [
            'act_name' => 'required',
            'category_actor' => 'required'
        ]);

        if($validator->fails())
        {
            $validation = $validator->getMessageBag()->toArray();

            return response()->json([
                'status_code' => '422', 
                'errors' => $validation
            ]);
        }
        else
        {

            if(isset($input['status_actor']))
            {
                $status = "1";
            }
            else
            {
                $status = "0";
            }

            if ($request->hasFile('act_logo')) 
            {
                $image = $request->file('act_logo');
                $imagename = 'AT_'.time().'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('images/logo_actors');
                $image->move($destinationPath, $imagename);
                // $logo = asset('images/logo_actors/'.$imagename);
                $logo = '/images/logo_actors/'.$imagename;
            }
            else
            {
                $logo = '/asset_salepage/images/AgentBasedDetection.png';
            }

            // dd($logo);

            $categorys = $request->get('category_actor');
            $count_cate = count($categorys);
            $data_category = '';
            $array_row = 1;

            foreach($categorys as $category)
            {
                if($array_row == $count_cate)
                {
                    $data_category = $data_category.$category;
                }
                else
                {
                    $data_category = $data_category.$category.',';
                }
                $array_row++;
            }

            $data_tag = '';
            if(@$input['act_tags'])
            {
                $data_tag = implode(',',$input['act_tags']);
            }

            $date_now = date('Y-m-d H:i:s');

            $uuid = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));

            $data_all = array(
                "adversary_uuid" => $uuid,
                "logo" => $logo,
                "name" => $input['act_name'],
                "description" => $input['act_des'],
                "category" => $data_category,
                "tags" => $data_tag,
                "location" => $input['act_loca'],
                // "attack_techniques" => $input['act_attack_techniques'],
                "status" => $status,
                "create_at" => $date_now,
                "create_at" => $date_now,
                "create_by" => 'system',
                "update_at" => $date_now,
                "update_by" => 'system'
            );

            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $client = new MongoClient($DB_MONGO_KEY);
            if(app()->environment('local'))
            {
                $collection = $client->sosecure_threatintelligent->fx_otx_adversaries;
            }
            else
            {
                $collection = $client->sosecure_threatintelligent_test->fx_otx_adversaries;
            }
            // $db_name = 'sosecure_threatintelligent_test';
            // $db = $client->$db_name;
            // $collection = $db->fx_otx_adversaries;

            $collection->insertOne($data_all);

            return response()->json([
                'status_code' => '200',
                'redirect' => route('actor.index')
            ]);
            
        }

    }

    public function actor_edit(Request $request){
        $edit = true;

        $_id = $request->get('_id');
        
        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $client = new MongoClient($DB_MONGO_KEY);
        if(app()->environment('local'))
        {
            $db_name = 'sosecure_threatintelligent';
            $collection = $client->$db_name->fx_otx_adversaries;
        }
        else
        {
            $db_name = 'sosecure_threatintelligent_test';
            $collection = $client->$db_name->fx_otx_adversaries;
        }

        $query = [
            '_id' => new \MongoDB\BSON\ObjectID($_id)
        ];

        $option = [];

        $final = $collection->find($query,$option);
        $result = $final->toArray();

        $data_edit = array();

        foreach($result as $data)
        {
            $data_edit['_id'] = @$data->_id;
            $data_edit['uuid'] = @$data->uuid;
            $data_edit['name'] = @$data->name; //
            $data_edit['logo'] = @$data->logo;
            $data_edit['description'] = @$data->description;
            $data_edit['category'] = @$data->category;
            $data_edit['tags'] = @$data->tags ? explode(',', $data->tags) : false;
            $data_edit['location'] = @$data->location;
            // $data_edit['attack_techniques'] = @$data->attack_techniques;
            $data_edit['status'] = @$data->status;
            $data_edit['create_at'] = @$data->create_at;

            // $data_edit[] = $data_edit;
        }

        $list_category = explode(",", $data_edit['category']);
        $data_edit['category'] = $list_category;
        $data_edit['count'] = count($list_category);
        
        // dd($data_edit);

        $query_category = FXCategories::select('name')
            ->get();

        // dd($data_edit);

        return view('monitoringvulnerabilitys::modal.add_actor')->with(compact('edit', 'data_edit', 'query_category'));   
    }

    public function update_actor(Request $request)
    {
        $input = $request->all();
        $hd_edit_id = $input['hd_edit_id'];

        // dd($input);

        $validator = Validator::make($request->all(), [
            'act_name' => 'required',
            'category_actor' => 'required'
        ]);

        if($validator->fails())
        {
            $validation = $validator->getMessageBag()->toArray();

            return response()->json([
                'status_code' => '422', 
                'errors' => $validation
            ]);
        }
        else
        {
            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $client = new MongoClient($DB_MONGO_KEY);
            if(app()->environment('local'))
            {
                $db_name = 'sosecure_threatintelligent';
                $db = $client->$db_name;
                $collection = $db->fx_otx_adversaries;
            }
            else
            {
                $db_name = 'sosecure_threatintelligent_test';
                $db = $client->$db_name;
                $collection = $db->fx_otx_adversaries;
            }

            $querys = [
                '_id' => new \MongoDB\BSON\ObjectID($hd_edit_id)
            ];
            $options = [];

            $document = $collection->findOne($querys,$options);

            if(isset($input['status_actor']))
            {
                $status = "1";
            }
            else
            {
                $status = "0";
            }

            if ($request->hasFile('act_logo')) 
            {
                $image = $request->file('act_logo');
                $imagename = 'AT_'.time().'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('images/logo_actors');
                $image->move($destinationPath, $imagename);
                // $logo = asset('images/logo_actors/'.$imagename);
                $logo = '/images/logo_actors/'.$imagename;

            }
            else
            {
                $logo = $document['logo'];
            }

            $categorys = $request->get('category_actor');
            $count_cate = count($categorys);
            $data_category = '';
            $array_row = 1;

            foreach($categorys as $category)
            {
                if($array_row == $count_cate)
                {
                    $data_category = $data_category.$category;
                }
                else
                {
                    $data_category = $data_category.$category.',';
                }
                $array_row++;
            }

            $date_now = date('Y-m-d H:i:s');

            $data_tag = '';
            if(@$input['act_tags'])
            {
                $data_tag = implode(',',$input['act_tags']);
            }

            // $uuid = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));

            $update_result = $collection->updateOne(
                ['_id' => $document['_id']],
                ['$set' => 
                    [
                        'logo' => @$logo,
                        'name' => $input['act_name'],
                        'description' => $input['act_des'],
                        'category' => $data_category,
                        'tags' => $data_tag,
                        'location' => $input['act_loca'],
                        // 'attack_techniques' => $input['act_attack_techniques'],
                        'status' => $status,
                        'update_at' => $date_now,
                        'update_by' => 'system'
                    ]
                ]
            );

            $collection_related = $db->fx_otx_adversaries_related;
            
            $query_c_count = [
                'adversary_uuid' => $document['adversary_uuid']
            ];
            $option_c_count = [];
            
            $final_check_count = $collection_related->find($query_c_count, $option_c_count);
            $result_check_count = $final_check_count->toArray();

            $check_count = count($result_check_count);

            if($check_count > 0)
            {
                $update_result_relate = $collection_related->updateMany(
                    ['adversary_uuid' => $document['adversary_uuid']],
                    ['$set' => 
                        [
                            'adversary_name' => $input['act_name'],
                        ]
                    ]
                );
            }

            return response()->json([
                'status_code' => '200',
                'redirect' => route('actor.index')
            ]);
            
        }

    }
    
    public function update_status_actor(Request $request)
    {
        $input = $request->all();
        
        $uuid = $request->uuid;
        $status = $request->status;

        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $client = new MongoClient($DB_MONGO_KEY);
        if(app()->environment('local'))
        {
            $db_name = 'sosecure_threatintelligent';
            $db = $client->$db_name;
            $collection = $db->fx_otx_adversaries;
        }
        else
        {
            $db_name = 'sosecure_threatintelligent_test';
            $db = $client->$db_name;
            $collection = $db->fx_otx_adversaries;
        }

        $querys = [
            'adversary_uuid' => $uuid
        ];
        $options = [];

        $document = $collection->findOne($querys,$options);

        if($document)
        {
            $date_now = date('Y-m-d H:i:s');

            $update_result = $collection->updateOne(
                ['adversary_uuid' => $document['adversary_uuid']],
                ['$set' => 
                    [
                        'status' => $status,
                        'update_at' => $date_now,
                        'update_by' => 'system'
                    ]
                ]
            );

            return response()->json([
                'status_code' => '200',
                'redirect' => route('actor.index')
            ]);
        }

    }

    public function delete(Request $request)
    {
        // $data['rssfeedsettings'] = $id;
        // $input = $request->all();
        // dd($input);
        $_id = $request->get('_id');
        $mode = $request->get('mode');

        return view('monitoringvulnerabilitys::modal.delete')->with(compact('_id','mode'));
    }

    public function delete_actor(Request $request)
    {
        $input = $request->all();
        
        $_id = $request->get('hd_delete_id');
        
        // dd($_id);

        if(!empty($_id))
        {
            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $client = new MongoClient($DB_MONGO_KEY);
            if(app()->environment('local'))
            {
                $db_name = 'sosecure_threatintelligent';
                $db = $client->$db_name;
                if($request->hd_mode == 'actor')
                {
                    $collection = $db->fx_otx_adversaries;
                }
                else if($request->hd_mode == 'camp')
                {
                    $collection = $db->fx_otx_campaign;
                }
                $collection_delete_related = $client->sosecure_threatintelligent->fx_otx_adversaries_related;
            }
            else
            {
                $db_name = 'sosecure_threatintelligent_test';
                $db = $client->$db_name;
                if($request->hd_mode == 'actor')
                {
                    $collection = $db->fx_otx_adversaries;
                }
                else if($request->hd_mode == 'camp')
                {
                    $collection = $db->fx_otx_campaign;
                }
                $collection_delete_related = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
            }
    
            $querys = [
                '_id' => new \MongoDB\BSON\ObjectID($_id)
            ];
            $options = [];
    
            $document = $collection->findOne($querys,$options);

            $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);

            // dd($document['adversary_uuid']);

            if($request->hd_mode == 'actor')
            {
                $uuid = $document['adversary_uuid'];
            }
            else if($request->hd_mode == 'camp')
            {
                $uuid = $document['campainge_uuid'];
            }
            
            $querys_delete_related = [
                'adversary_uuid' => $uuid
            ];
            $options_delete_related = [];
            $document_delete_related = $collection_delete_related->find($querys_delete_related,$options_delete_related);
            $final = $document_delete_related->toArray();

            // dd(count($final));

            if(count($final) > 0)
            {
                $update_result_related = $collection_delete_related->updateMany(
                    ['adversary_uuid' => $uuid],
                    ['$set' => 
                        [
                            'delete_at' => $date_now
                        ]
                    ]
                );
            }

            $update_result = $collection->updateOne(
                ['_id' => $document['_id']],
                ['$set' => 
                    [
                        'delete_at' => $date_now
                    ]
                ]
            );

            return response()->json([
                'status_code' => '200',
                'redirect' => route('actor.index')
            ]);
        }
        else
        {
            return response()->json([
                'status_code' => '500'
            ]);
        }

    }

    public function CampaingeDetail(Request $request)
    {
        $data['page'] = "Campainge Detail";

        $input = $request->all();

        // dd($input);

        $_id = $request->get('_id');

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

        return view('monitoringvulnerabilitys::campainge_detail', compact('result', 'count_new', 'count_cve', 'count_indi'))->with($data);
    }

    public function campainge_add()
    {
        $edit = false;
        $query_techniques = FXTechniques::get();

        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $client = new MongoClient($DB_MONGO_KEY);
        if(app()->environment('local'))
        {
            $collection = $client->sosecure_threatintelligent->fx_otx_adversaries;
        }
        else
        {
            $collection = $client->sosecure_threatintelligent_test->fx_otx_adversaries;
        }

        $query = [
            'delete_at' => null
        ];

        $option = [];

        $final = $collection->find($query,$option);

        $result_actor = $final->toArray();

        // dd($result_actor);

        return view('monitoringvulnerabilitys::modal.add_campainge')->with(compact('edit','query_techniques','result_actor'));   
    }

    public function campainge_select_technigues(Request $request)
    {
        $input = $request->all();

        if($request->has('q'))
        {
            $search = $request->q;

            $query = FXTechniques::
                where('name', 'like', '%'.$search.'%')
                ->select('name')
                ->get();
        }

        return response()->json($query);
    }

    public function select_row_campain(Request $request){
        $input = $request->all();

        $id = $request->tech_campainge;
        $query_sel = FXTechniques::where('id', $request->tech_campainge)->first();

        $response = [
            'id' => $id,
            'query_sel' => $query_sel
        ];

        return response()->json($response);
    }

    public function create_campainge(Request $request)
    {
        $input = $request->all();

        // dd($input);

        $validator = Validator::make($request->all(), [
            'tech_name' => 'required'
        ]);

        if($validator->fails())
        {
            $validation = $validator->getMessageBag()->toArray();

            return response()->json([
                'status_code' => '422', 
                'errors' => $validation
            ]);
        }
        else
        {
            // dd($input);

            if(isset($input['status_campainge']))
            {
                $status = "1";
            }
            else
            {
                $status = "0";
            }

            $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);

            $uuid = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));

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

            $actor = '';
            if(count($request->actor_campainge) > 0)
            {
                $actor = implode(',', $request->actor_campainge);
            }

            $data_all = array(
                "campainge_uuid" => $uuid,
                "name" => $input['tech_name'],
                // "actor" => @$actor,
                "description" => @$input['description_campainge'],
                "status" => $status,
                "create_at" => $date_now,
                "create_by" => 'system',
                "update_at" => $date_now,
                "update_by" => 'system'
            );

            $collection->insertOne($data_all);
            
            if(@$request->tech)
            {
                foreach($request->tech as $data)
                {
                    $data_related = array(
                        'adversary_uuid' => $uuid,
                        'adversary_name' => $request->tech_name,
                        'pulse_id' => $data['id'],
                        'pulse_name' => '',
                        'tactics_id' => $data['tactics_id'],
                        'tactics_name' => $data['tactics_name'],
                        'techniques' => $data['techniques'],
                        'mode' => 'campainge',
                        'join' => 'techniques',
                        'modified' => $date_now,
                        'created_at' => $date_now,
                        'created_by' => 'system',
                        'updated_at' => $date_now,
                        'updated_by' => 'system'
                    );
                    $collection_related->insertOne($data_related);
                }
            }

            if(count($request->actor_campainge_selected) > 0)
            {
                foreach($request->actor_campainge_selected as $data)
                {
                    $data_actor_related = array(
                        'adversary_uuid' => $uuid,
                        'adversary_name' => $request->tech_name,
                        'pulse_id' => '',
                        'pulse_name' => '',
                        'tactics_id' => '',
                        'tactics_name' => '',
                        'techniques' => '',

                        'actor_id' => $data['id'],
                        'actor_name' => $data['name'],

                        'mode' => 'campainge',
                        'join' => 'actor',
                        'modified' => $date_now,
                        'created_at' => $date_now,
                        'created_by' => 'system',
                        'updated_at' => $date_now,
                        'updated_by' => 'system'
                    );
                    $collection_related->insertOne($data_actor_related);
                }
            }

            return response()->json([
                'status_code' => '200',
                'redirect' => route('actor.index')
            ]);
            
        }

    }

    public function campainge_edit(Request $request)
    {
        $edit = true;
        $query_techniques = FXTechniques::get();

        $_id = $request->get('_id');
        
        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $client = new MongoClient($DB_MONGO_KEY);
        if(app()->environment('local'))
        {
            $db_name = 'sosecure_threatintelligent';
            $collection = $client->$db_name->fx_otx_campaign;
            $collection_related = $client->$db_name->fx_otx_adversaries_related;
        }
        else
        {
            $db_name = 'sosecure_threatintelligent_test';
            $collection = $client->$db_name->fx_otx_campaign;
            $collection_related = $client->$db_name->fx_otx_adversaries_related;
        }

        $query = [
            '_id' => new \MongoDB\BSON\ObjectID($_id)
        ];

        $option = [];

        $final = $collection->find($query,$option);
        $result = $final->toArray();

        $data_edit = array();

        foreach($result as $data)
        {
            $data_edit['_id'] = @$data->_id;
            $data_edit['uuid'] = @$data->campainge_uuid;
            $data_edit['name'] = @$data->name;
            $data_edit['status'] = @$data->status;

            // $data_edit[] = $data_edit;
        }

        $query_related = [
            'adversary_uuid' => $data_edit['uuid']
        ];

        $option_related = [];

        $final_related = $collection_related->find($query_related,$option_related);
        $result_related = $final_related->toArray();

        $data_edit['techniques'] = $result_related;

        //----------------------------------------------------------------------------------------------

        // $query_actor_related = [
        //     'adversary_uuid' => $data_edit['uuid'],
        //     'mode' => 'campainge',
        //     'join' => 'actor',
        // ];

        // $option_actor_related = [];

        // $final_actor_related = $collection_related->find($query_actor_related,$option_actor_related);
        // $result_actor_related = $final_actor_related->toArray();

        // $data_edit['actor'] = $result_actor_related;

        // dd($data_edit);

        return view('monitoringvulnerabilitys::modal.add_campainge')->with(compact('edit', 'data_edit', 'query_techniques'));   
    }

    public function campainge_edit_detail(Request $request)
    {
        $edit = true;
        $query_techniques = FXTechniques::get();

        $_id = $request->get('_id');
        
        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $client = new MongoClient($DB_MONGO_KEY);
        if(app()->environment('local'))
        {
            $db_name = 'sosecure_threatintelligent';
            $collection = $client->$db_name->fx_otx_campaign;
            $collection_related = $client->$db_name->fx_otx_adversaries_related;
            $collection_adversaries = $client->$db_name->fx_otx_adversaries;
        }
        else
        {
            $db_name = 'sosecure_threatintelligent_test';
            $collection = $client->$db_name->fx_otx_campaign;
            $collection_related = $client->$db_name->fx_otx_adversaries_related;
            $collection_adversaries = $client->$db_name->fx_otx_adversaries;
        }

        $query = [
            '_id' => new \MongoDB\BSON\ObjectID($_id)
        ];

        $option = [];

        $final = $collection->find($query,$option);
        $result = $final->toArray();

        $data_edit = array();

        foreach($result as $data)
        {
            $data_edit['_id'] = @$data->_id;
            $data_edit['uuid'] = @$data->campainge_uuid;
            $data_edit['name'] = @$data->name;
            $data_edit['description'] = @$data->description;
            $data_edit['status'] = @$data->status;
        }

        $query_related = [
            'adversary_uuid' => $data_edit['uuid'],
            'mode' => 'campainge',
            'join' => 'techniques',
            'delete_at' => null
        ];

        $option_related = [];

        $final_related = $collection_related->find($query_related,$option_related);
        $result_related = $final_related->toArray();

        $data_edit['techniques'] = $result_related;

        // ------------------------------------------------------------------------

        $query_related_actor = [
            'adversary_uuid' => $data_edit['uuid'],
            'mode' => 'campainge',
            'join' => 'actor',
            'delete_at' => null
        ];

        $option_related_actor = [];

        $final_related_actor = $collection_related->find($query_related_actor,$option_related_actor);
        $result_related_actor = $final_related_actor->toArray();

        $arr_actor = [];
        if(count($result_related_actor) > 0)
        {
            foreach($result_related_actor as $actor)
            {
                $arr_actor[] = $actor->actor_id;
            }
        }

        $data_edit['actor'] = $result_related_actor;
        $data_edit['arr_actor'] = $arr_actor;

        // -------------------------------------------------------------------

        $query_master_actor = [
            'delete_at' => null
        ];

        $option_master_actor = [];

        $final_master_actor = $collection_adversaries->find($query_master_actor,$option_master_actor);

        $result_actor = $final_master_actor->toArray();

        // dd($data_edit['actor']);

        return view('monitoringvulnerabilitys::modal.edit_campainge')->with(compact('edit', 'data_edit', 'query_techniques', 'result_actor'));   
    }

    public function campainge_add_tech(Request $request)
    {
        $input = $request->all();

        // dd($input);

        $validator = Validator::make($request->all(), [
            'tech' => 'required'
        ]);

        if($validator->fails())
        {
            $validation = $validator->getMessageBag()->toArray();

            return response()->json([
                'status_code' => '422', 
                'errors' => $validation
            ]);
        }
        else
        {
            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $client = new MongoClient($DB_MONGO_KEY);
            if(app()->environment('local'))
            {
                $db_name = 'sosecure_threatintelligent';
                $db = $client->$db_name;
                $collection_related = $db->fx_otx_adversaries_related;
            }
            else
            {
                $db_name = 'sosecure_threatintelligent_test';
                $db = $client->$db_name;
                $collection_related = $db->fx_otx_adversaries_related;
            }

            $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);
            
            foreach($request->tech as $data)
            {
                $data_related = array(
                    'adversary_uuid' => $request->hd_uuid,
                    'adversary_name' => $request->hd_name,
                    'pulse_id' => $data['id'],
                    'pulse_name' => '',
                    'tactics_id' => $data['tactics_id'],
                    'tactics_name' => $data['tactics_name'],
                    'techniques' => $data['techniques'],
                    'mode' => 'campainge',
                    'join' => 'techniques',
                    'modified' => $date_now,
                    'created_at' => $date_now,
                    'created_by' => 'system',
                    'updated_at' => $date_now,
                    'updated_by' => 'system'
                );
                $collection_related->insertOne($data_related);
            }

            return response()->json([
                'status_code' => '200',
                'redirect' => route('actor.index')
            ]); 
        }
    }

    public function update_detail_campainge(Request $request)
    {
        $input = $request->all();
        $hd_edit_id = $input['hd_edit_id'];

        // dd($input);

        $validator = Validator::make($request->all(), [
            'tech_name' => 'required'
        ]);

        if($validator->fails())
        {
            $validation = $validator->getMessageBag()->toArray();

            return response()->json([
                'status_code' => '422', 
                'errors' => $validation
            ]);
        }
        else
        {
            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $client = new MongoClient($DB_MONGO_KEY);
            if(app()->environment('local'))
            {
                $db_name = 'sosecure_threatintelligent';
                $db = $client->$db_name;
                $collection = $db->fx_otx_campaign;
                $collection_related = $client->sosecure_threatintelligent->fx_otx_adversaries_related;
            }
            else
            {
                $db_name = 'sosecure_threatintelligent_test';
                $db = $client->$db_name;
                $collection = $db->fx_otx_campaign;
                $collection_related = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
            }

            $querys = [
                '_id' => new \MongoDB\BSON\ObjectID($hd_edit_id)
            ];
            $options = [];

            $document = $collection->findOne($querys,$options);
            
            // dd($document['campainge_uuid']);

            if(isset($input['status_campainge']))
            {
                $status = "1";
            }
            else
            {
                $status = "0";
            }

            $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);

            $update_result = $collection->updateOne(
                ['_id' => $document['_id']],
                ['$set' => 
                    [
                        'name' => $input['tech_name'],
                        'description' => $input['description_campainge'],
                        'status' => $status,
                        'update_at' => $date_now,
                        'update_by' => 'system'
                    ]
                ]
            );

            if(@$request->tech_delete)
            {
                $data_delete = explode(',', (string)$request->tech_delete);

                foreach($data_delete as $id)
                {
                    if($id != null)
                    {
                        $query_del_rel = [
                            '_id' => new \MongoDB\BSON\ObjectID($id)
                        ];
                        $option_del_rel = [];
            
                        $document_del_rel = $collection_related->findOne($query_del_rel,$option_del_rel);
        
                        $update_result = $collection_related->updateOne(
                            ['_id' => $document_del_rel['_id']],
                            // ['_id' => $del_id],
                            ['$set' => 
                                [
                                    'delete_at' => $date_now
                                ]
                            ]
                        );
                    }
                }
            }

            if(@$request->tech)
            {
                foreach($request->tech as $data)
                {
                    $data_related = array(
                        'adversary_uuid' => $document['campainge_uuid'],
                        'adversary_name' => $document['name'],
                        'pulse_id' => $data['id'],
                        'pulse_name' => '',
                        'tactics_id' => $data['tactics_id'],
                        'tactics_name' => $data['tactics_name'],
                        'techniques' => $data['techniques'],
                        'mode' => 'campainge',
                        'join' => 'techniques',
                        'modified' => $date_now,
                        'created_at' => $date_now,
                        'created_by' => 'system',
                        'updated_at' => $date_now,
                        'updated_by' => 'system'
                    );
                    $collection_related->insertOne($data_related);
                }
            }

            // $query_del_actor = [
            //     'adversary_uuid' => $document['campainge_uuid'],
            //     'mode' => 'campainge',
            //     'join' => 'actor'
            // ];
            // $option_del_actor = [];

            // $document_del_actor = $collection_related->findOne($query_del_actor,$option_del_actor);

            $update_del_actor = $collection_related->updateMany(
                [
                    'adversary_uuid' => $document['campainge_uuid'],
                    'mode' => 'campainge',
                    'join' => 'actor'
                ],
                [
                    '$set' => [
                        'delete_at' => $date_now
                    ]
                ]
            );

            if(count($request->actor_campainge_selected) > 0)
            {
                foreach($request->actor_campainge_selected as $data)
                {
                    $data_actor_related = array(
                        'adversary_uuid' => $document['campainge_uuid'],
                        'adversary_name' => $document['name'],
                        'pulse_id' => '',
                        'pulse_name' => '',
                        'tactics_id' => '',
                        'tactics_name' => '',
                        'techniques' => '',

                        'actor_id' => $data['id'],
                        'actor_name' => $data['name'],

                        'mode' => 'campainge',
                        'join' => 'actor',
                        'modified' => $date_now,
                        'created_at' => $date_now,
                        'created_by' => 'system',
                        'updated_at' => $date_now,
                        'updated_by' => 'system'
                    );
                    $collection_related->insertOne($data_actor_related);
                }
            }

            return response()->json([
                'status_code' => '200',
                'redirect' => route('actor.index')
            ]);
            
        }

    }

    public function update_status_camp(Request $request)
    {
        $input = $request->all();
        
        $uuid = $request->uuid;
        $status = $request->status;

        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
        $client = new MongoClient($DB_MONGO_KEY);
        if(app()->environment('local'))
        {
            $db_name = 'sosecure_threatintelligent';
            $db = $client->$db_name;
            $collection = $db->fx_otx_campaign;
        }
        else
        {
            $db_name = 'sosecure_threatintelligent_test';
            $db = $client->$db_name;
            $collection = $db->fx_otx_campaign;
        }

        $querys = [
            'campainge_uuid' => $uuid
        ];
        $options = [];

        $document = $collection->findOne($querys,$options);

        if($document)
        {
            $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);

            $update_result = $collection->updateOne(
                ['campainge_uuid' => $document['campainge_uuid']],
                ['$set' => 
                    [
                        'status' => $status,
                        'update_at' => $date_now,
                        'update_by' => 'system'
                    ]
                ]
            );

            return response()->json([
                'status_code' => '200',
                'redirect' => route('actor.index')
            ]);
        }

    }

    public function delete_tech(Request $request)
    {
        // $data['rssfeedsettings'] = $id;
        $_id = $request->get('_id');

        return view('monitoringvulnerabilitys::modal.delete_techniques')->with(compact('_id'));
    }

    public function delete_techniques(Request $request)
    {
        $input = $request->all();
        
        $_id = $request->get('hd_delete_id');
        
        // dd($_id);

        if(!empty($_id))
        {
            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $client = new MongoClient($DB_MONGO_KEY);
            if(app()->environment('local'))
            {
                $collection_delete_related = $client->sosecure_threatintelligent->fx_otx_adversaries_related;
            }
            else
            {
                $collection_delete_related = $client->sosecure_threatintelligent_test->fx_otx_adversaries_related;
            }
    
            $querys = [
                '_id' => new \MongoDB\BSON\ObjectID($_id)
            ];
            $options = [];
    
            $document = $collection_delete_related->findOne($querys,$options);

            $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);

            // dd($document['adversary_uuid']);

            $update_result = $collection_delete_related->updateOne(
                ['_id' => $document['_id']],
                ['$set' => 
                    [
                        'delete_at' => $date_now
                    ]
                ]
            );

            return response()->json([
                'status_code' => '200',
                'redirect' => route('actor.index')
            ]);
        }
        else
        {
            return response()->json([
                'status_code' => '500'
            ]);
        }

    }

    public function add_actor_vulnerabilities(Request $request)
    {
        // $data['rssfeedsettings'] = $id;
        $edit = false;
        $id = $request->get('id');
        $name = $request->get('name');

        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        if(app()->environment('local'))
        {
            $col_fx_otx_campaign = $clientMD->sosecure_threatintelligent->fx_otx_campaign;
            $col_fx_otx_adversaries = $clientMD->sosecure_threatintelligent->fx_otx_adversaries;
            $col_fx_otx_adversaries_related = $clientMD->sosecure_threatintelligent->fx_otx_adversaries_related;
        }
        else
        {
            $col_fx_otx_campaign = $clientMD->sosecure_threatintelligent_test->fx_otx_campaign;
            $col_fx_otx_adversaries = $clientMD->sosecure_threatintelligent_test->fx_otx_adversaries;
            $col_fx_otx_adversaries_related = $clientMD->sosecure_threatintelligent_test->fx_otx_adversaries_related;
        }

        $query_actor = [
            'pulse_id' => $id,
            'mode' => 'vulnerabilities',
            'join' => 'actor',
            'delete_at' => null
        ];
        $options_actor = [];
        $connection_actor = $col_fx_otx_adversaries_related->find($query_actor,$options_actor);
        if($connection_actor != null)
        {
            $actors = $connection_actor->toArray();
            $data['actors'] = $actors;
        } 
        else
        {
            $data['actors'] = null;
        }

        $query_campainge = [
            'pulse_id' => $id,
            'mode' => 'vulnerabilities',
            'join' => 'campainge',
            'delete_at' => null
        ];
        $option_campainge = [];

        $connection_campainge = $col_fx_otx_adversaries_related->find($query_campainge,$option_campainge);
        
        if($connection_actor != null)
        {
            $campainge = $connection_campainge->toArray();
            foreach($campainge as $data_campainge)
            {
                $data['campainge'][] = $data_campainge['adversary_uuid'];
            }
            // $data['campainge'] = $campainge;
        } 
        else
        {
            $data['campainge'] = null;
        }

        // $data_chk = $connection_campainge->toArray();

        // $arr_chk_campainge = [];
        // if(@$data_chk)
        // {
        //     foreach($data_chk as $data_campainge)
        //     {
        //         $arr_chk_campainge[] = $data_campainge['adversary_uuid'];
        //     }
        // }

        // $data['arr_chk_campainge'] = $arr_chk_campainge;

        $query_master_campainge = [
            'status' => '1'
        ];
        $option_master_campainge = [];

        $connection_master_campainge = $col_fx_otx_campaign->find($query_master_campainge,$option_master_campainge);
        
        if($connection_master_campainge != null)
        {
            $master_campainge = $connection_master_campainge->toArray();
            $data['master_campainge'] = $master_campainge;
        } 
        else
        {
            $data['master_campainge'] = null;
        }

        // dd($data['campainge']);

        return view('monitoringvulnerabilitys::modal.add_actor_vulnerabilities')->with(compact('id', 'name', 'edit', 'data'));
    }

    public function select_actor_vulnerabilities(Request $request)
    {
        if($request->has('q')){
            $search = $request->q;

            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $client = new MongoClient($DB_MONGO_KEY);
            if(app()->environment('local'))
            {
                $collection = $client->sosecure_threatintelligent->fx_otx_adversaries;
            }
            else
            {
                $collection = $client->sosecure_threatintelligent_test->fx_otx_adversaries;
            }
            // //-------------------------------------------
            // $db_name = 'sosecure_threatintelligent_test';
            // //-------------------------------------------
            // $db = $client->$db_name;
            // $collection = $db->fx_otx_adversaries;
            
            $query = [
                'name' => new \MongoDB\BSON\Regex($search),
                'delete_at' => null
            ];

            $option = [];

            $final = $collection->find($query,$option);
            $result = $final->toArray();
        }

        return response()->json($result);
    }

    public function create_actor_vulnerabilities(Request $request)
    {
        $input = $request->all();
        // dd($input);
        $vulnerabilities_actor = $request->vulnerabilities_actor;
        $vulnerabilities_id = $request->hd_id;
        $vulnerabilities_name = $request->hd_name;

        $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
        if(app()->environment('local'))
        {
            $collection_campaign = $clientMD->sosecure_threatintelligent->fx_otx_campaign;
            $col_fx_otx_adversaries = $clientMD->sosecure_threatintelligent->fx_otx_adversaries;
            $col_fx_otx_adversaries_related = $clientMD->sosecure_threatintelligent->fx_otx_adversaries_related;
        }
        else
        {
            $collection_campaign = $clientMD->sosecure_threatintelligent_test->fx_otx_campaign;
            $col_fx_otx_adversaries = $clientMD->sosecure_threatintelligent_test->fx_otx_adversaries;
            $col_fx_otx_adversaries_related = $clientMD->sosecure_threatintelligent_test->fx_otx_adversaries_related;
        }

        $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);

        $querys_delete_related = [
            'pulse_id' => $vulnerabilities_id,
            'pulse_name' => $vulnerabilities_name,
            'mode' => 'vulnerabilities',
            'join' => 'actor'
        ];
        $options_delete_related = [];
        $document_delete_related = $col_fx_otx_adversaries_related->find($querys_delete_related,$options_delete_related);
        $final_actor = $document_delete_related->toArray();

        // dd(count($final_actor));
        
        if(count($final_actor) > 0)
        {
            $update_result_related = $col_fx_otx_adversaries_related->updateMany(
                $querys_delete_related,
                ['$set' => 
                    [
                        'delete_at' => $date_now
                    ]
                ]
            );
        }

        if(@$request->vulnerabilities_actor)
        {

            foreach($request->vulnerabilities_actor as $data_vul)
            {
                // dd($data_vul);
                $query = [
                    'adversary_uuid' => $data_vul
                ];
                $option = [];
        
                $result = $col_fx_otx_adversaries->findOne($query,$option);
                // dd($result);
                $data_adv_related = array(
                    'adversary_uuid' => $result['adversary_uuid'],
                    'adversary_name' => $result['name'],
                    'pulse_id' => $vulnerabilities_id,
                    'pulse_name' => $vulnerabilities_name,
                    'mode' => 'vulnerabilities',
                    'join' => 'actor',
                    'modified' => $date_now,
                    'created_at' => $date_now,
                    'created_by' => 'system',
                    'updated_at' => $date_now,
                    'updated_by' => 'system'
                );
        
                $vul_related = $col_fx_otx_adversaries_related->insertOne($data_adv_related);
            }
        }

        $querys_delete_camp = [
            'pulse_id' => $vulnerabilities_id,
            'pulse_name' => $vulnerabilities_name,
            'mode' => 'vulnerabilities',
            'join' => 'campainge'
        ];
        $options_delete_camp = [];
        $document_delete_camp = $col_fx_otx_adversaries_related->find($querys_delete_camp,$options_delete_camp);
        $final_camp = $document_delete_camp->toArray();

        // dd(count($final));

        if(count($final_camp) > 0)
        {
            $update_result_related = $col_fx_otx_adversaries_related->updateMany(
                $querys_delete_camp,
                ['$set' => 
                    [
                        'delete_at' => $date_now
                    ]
                ]
            );
        }

        if(@$request->vulnerabilities_campainge)
        {

            foreach($request->vulnerabilities_campainge as $data_camp)
            {
                $query = [
                    'campainge_uuid' => $data_camp
                ];
                $option = [];
        
                $result = $collection_campaign->findOne($query,$option);
    
                $data_camp_related = array(
                    'adversary_uuid' => $result['campainge_uuid'],
                    'adversary_name' => $result['name'],
                    'pulse_id' => $vulnerabilities_id,
                    'pulse_name' => $vulnerabilities_name,
                    'mode' => 'vulnerabilities',
                    'join' => 'campainge',
                    'modified' => $date_now,
                    'created_at' => $date_now,
                    'created_by' => 'system',
                    'updated_at' => $date_now,
                    'updated_by' => 'system'
                );
        
                $camp_related = $col_fx_otx_adversaries_related->insertOne($data_camp_related);
            }
        }

        // if($update_fx_otx_adversaries_related)
        // {
            return response()->json([
                'status_code' => '200',
                'message' => 'Success!!',
                'redirect' => route('monitoringvulnerabilitys.index')
            ]);
        // }
        // else
        // {
        //     return response()->json([
        //         'status_code' => '404',
        //         'message' => 'Error 404!!'
        //         // 'redirect' => route('actor.index')
        //     ]);
        // }
    }

}
