<?php

namespace App\Http\Controllers;

use App\DataLeakFeed;
use App\Services\IndicatorCheckService;
use App\Services\PublishedFeedsService;
use App\LogSearch;
use App\R_s_s_news;
use App\SiteLimitApi;
use App\SiteRequestLimitApi;
use App\SystemLimitApi;
use App\Traits\Taggable;
use DB;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\Request;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
use Modules\MonitoringVulnerabilitys\Entities\CVEMappingAssets;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use MongoDB\Client as MongoClient;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Validation\Validator;
use Modules\SiteSettings\Entities\SiteSettings;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response as FacadeResponse;


class SearchController extends Controller
{
    use Taggable;
    protected $request;
    protected $search;
    protected $page;

    public function __construct(Request $request)
    {
      //  $this->middleware('auth');
        $this->request = $request;
    }

    public function searchPage(Request $request){
        $data['page'] = langapp('search');
        $data['keyword'] = $this->request->keyword;
        $data['mode'] = $this->request->mode;

        return view('new_searches')->with($data);
    }

    public function searchAPI(){
        set_time_limit(120);
     
        $type = $this->request->type;
        $keyword = $this->request->keyword;

        $data['dataSearch'] = array();
        $limit = 100;

        $role_custom = @check_role_custom();
        $site_id_arr = @get_role_custom()['site_id_arr'];
        if ($this->request->keyword && $this->request->mode !== 'lookup') {
            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);

            if(@$role_custom['news']) {
                if($type == 'news'){
                    $keyword = '%' . $this->request->keyword . '%';
                    $dataWait['queryData'] = R_s_s_news::select('id', 'title_th as name', 'detail_th as content', DB::raw('CONCAT("/public/news/detail/",code ,"/th") AS link'))->where('title_th', 'LIKE', $keyword);
                    $dataWait['count'] = $dataWait['queryData']->count();
                    $dataWait['queryData'] = $dataWait['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                    $dataWait2['queryData'] = R_s_s_news::select('id', 'title_en as name', 'detail_en as content', DB::raw('CONCAT("/public/news/detail/",code ,"/en" ) AS link'))->where('title_en', 'LIKE', $keyword);
                    $dataWait2['count'] = $dataWait2['queryData']->count() + $dataWait['count'];
                    if ($dataWait2['count'] > 0){
                        $dataWait2['queryData'] = $dataWait2['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                        $dataWait2['queryData'] = array_merge($dataWait['queryData'], $dataWait2['queryData']);
                        $dataWait2['moreDetail'] = $dataWait2['count']<101?"":$this->request->keyword;
                        $data['dataSearch']["News"] = $dataWait2;
    
                    }
                } 
            }

            if(@$role_custom['vulnerabilities']) {
                if($type == 'vulnerabilities'){
                    if(@get_role_custom()['superadmin'] == 1) {
                        $CVEMappingAssets_name = CVEMappingAssets::select('namecve')->get();

                        $dataWait['queryData'] = CVEMapping::select('id', 'namecve as name', 'description as content', DB::raw('CONCAT("/monitoringvulnerabilitys") AS link'))->where('namecve', 'LIKE', $keyword)->whereIn('namecve', $CVEMappingAssets_name);
                        $dataWait['count'] = $dataWait['queryData']->count();
                        if ($dataWait['count'] > 0) {
                            $dataWait['queryData'] = $dataWait['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                            $data['dataSearch']["Vulnerabilities"] = $dataWait;
                        }
                    } else {
                        $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();

                        $dataWait['queryData'] = CVEMapping::select('id', 'namecve as name', 'description as content', DB::raw('CONCAT("/monitoringvulnerabilitys") AS link'))->where('namecve', 'LIKE', $keyword)->whereIn('namecve', $CVEMappingAssets_name);
                        $dataWait['count'] = $dataWait['queryData']->count();
                        if ($dataWait['count'] > 0) {
                            $dataWait['queryData'] = $dataWait['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                            $data['dataSearch']["Vulnerabilities"] = $dataWait;
                        }
                    }
                }
            }

            if(@$role_custom['compromised']) {
                if($type == 'compromised'){
                    if(@get_role_custom()['superadmin'] == 1) {
                        $DataLeakFeed_compromised = DataLeakFeed::select('data_leak_feed.id', 'data_leak_feed.feedcontent as content', 'data_leak_feed.sourceid', 'data_leak_feed.keyword as name', DB::raw('CONCAT("/darkweb-datas") AS link'))->whereNull('data_leak_feed.deleted_at')->whereNull('data_leak_socail_ref.deleted_at')->where('data_leak_socail_ref.status',1)->whereIn('data_leak_feed.feel_type',['darkweb','webserver','compromise','compromised'])->where(function ($query) use ($keyword) {
                            $query->where('data_leak_feed.keyword', 'LIKE', $keyword)
                                ->orWhere('data_leak_feed.source_name', 'LIKE', $keyword) ->orWhere('data_leak_feed.feedcontent', 'LIKE', $keyword);
                        });
                        $DataLeakFeed_compromised = $DataLeakFeed_compromised->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');
                            // if(isset($SiteSettings->id)){
                        $dataWait["queryData"] = $DataLeakFeed_compromised;
                        $dataWait["count"] = $dataWait["queryData"]->count();
                        if ($dataWait['count'] > 0) {
                            $dataWait["queryData"] = $dataWait["queryData"]->orderBy('data_leak_feed.updated_at', 'desc')->get()->toArray();
                            $data['dataSearch']["Compromised"] = $dataWait;
                        }
                    } else {
                        $DataLeakFeed_compromised = DataLeakFeed::select('data_leak_feed.id', 'data_leak_feed.feedcontent as content', 'data_leak_feed.sourceid', 'data_leak_feed.keyword as name', DB::raw('CONCAT("/darkweb-datas") AS link'))->whereNull('data_leak_feed.deleted_at')->whereNull('data_leak_socail_ref.deleted_at')->where('data_leak_socail_ref.status',1)->whereIn('data_leak_feed.feel_type',['darkweb','webserver','compromise','compromised'])->where(function ($query) use ($keyword) {
                            $query->where('data_leak_feed.keyword', 'LIKE', $keyword)
                                ->orWhere('data_leak_feed.source_name', 'LIKE', $keyword) ->orWhere('data_leak_feed.feedcontent', 'LIKE', $keyword);
                        });
                        $DataLeakFeed_compromised = $DataLeakFeed_compromised->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');
                            // if(isset($SiteSettings->id)){
                        $dataWait["queryData"] = $DataLeakFeed_compromised->whereIn('site.id', $site_id_arr);
                        $dataWait["count"] = $dataWait["queryData"]->count();
                        if ($dataWait['count'] > 0) {
                            $dataWait["queryData"] = $dataWait["queryData"]->orderBy('data_leak_feed.updated_at', 'desc')->get()->toArray();
                            $data['dataSearch']["Compromised"] = $dataWait;
                        }
                    }
                }
            }

            if(@$role_custom['data_leak']) {
                if($type == 'data_leak'){
                    $keywords = $this->request->keyword;
                    $DataLeakFeed_data  = DataLeakFeed::where('deleted_at', null)->where('status','1')->whereIn('feel_type', ['social','darkweb_public'])->get();
                    foreach ($DataLeakFeed_data as $value_data) {
                          $value_data->feedcontent_decode = html_entity_decode($value_data->feedcontent);
                         
                    }
                    $DataLeakFeed_data_id = array();
                    array_push($DataLeakFeed_data_id, 0);
                    foreach($DataLeakFeed_data as $a) {
                        if(strpos($a->feedcontent_decode, $keywords) !== false) {
                            array_push($DataLeakFeed_data_id, $a->id);
                        } 
                    }
            
                    if(@get_role_custom()['superadmin'] == 1) {
                        $DataLeakFeed_social = DataLeakFeed::select('data_leak_feed.id', 'data_leak_feed.feedcontent as content', 'data_leak_feed.sourceid', 'data_leak_feed.keyword as name', DB::raw('CONCAT("/socialdatas") AS link'))->whereNull('data_leak_feed.deleted_at')->whereNull('data_leak_socail_ref.deleted_at')->whereIn('data_leak_feed.feel_type',['social', 'darkweb_public'])->where(function ($query) use ($keyword,$DataLeakFeed_data_id) {
                            $query->where('data_leak_feed.keyword', 'LIKE', $keyword)
                            ->orWhere('data_leak_feed.source_name', 'LIKE', $keyword) ->orWhere('data_leak_feed.feedcontent', 'LIKE', $keyword)
                            ->orWhereIn('data_leak_feed.id', $DataLeakFeed_data_id);
                        });
                     
                        $DataLeakFeed_social = $DataLeakFeed_social->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');
                        $dataWait["queryData"] = $DataLeakFeed_social;
    
                        $dataWait["count"] = $dataWait["queryData"]->count();
                        if ($dataWait['count'] > 0) {
    
                        
    
                            $dataWait["queryData"] = $dataWait["queryData"]->orderBy('data_leak_feed.updated_at', 'desc')->get()->toArray();
                            $data['dataSearch']["Data Leak"] = $dataWait;
                        }
                    } else {
                        $DataLeakFeed_social = DataLeakFeed::select('data_leak_feed.id', 'data_leak_feed.feedcontent as content', 'data_leak_feed.sourceid', 'data_leak_feed.keyword as name', DB::raw('CONCAT("/socialdatas") AS link'))->whereNull('data_leak_feed.deleted_at')->whereNull('data_leak_socail_ref.deleted_at')->whereIn('data_leak_feed.feel_type',['social', 'darkweb_public'])->where(function ($query) use ($keyword,$DataLeakFeed_data_id) {
                            $query->where('data_leak_feed.keyword', 'LIKE', $keyword)
                            ->orWhere('data_leak_feed.source_name', 'LIKE', $keyword) ->orWhere('data_leak_feed.feedcontent', 'LIKE', $keyword)
                            ->orWhereIn('data_leak_feed.id', $DataLeakFeed_data_id);
                        });
                        $DataLeakFeed_social = $DataLeakFeed_social->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');
                        $dataWait["queryData"] = $DataLeakFeed_social->whereIn('site.id', $site_id_arr);
                        
                        $dataWait["count"] = $dataWait["queryData"]->count();
                        if ($dataWait['count'] > 0) {
                            $dataWait["queryData"] = $dataWait["queryData"]->orderBy('data_leak_feed.updated_at', 'desc')->get()->toArray();
                            $data['dataSearch']["Data Leak"] = $dataWait;
                        }
                    }
                }
            }

            if(@$role_custom['web_defacement']) {
                if($type == 'web_defacement'){
                    if(@get_role_custom()['superadmin'] == 1) {
                        $dataWait['queryData'] = WebdefacmentSetting::select('id', 'name', 'url as content', DB::raw('CONCAT("/webdefacement/detail/",code) AS link'))->where('name', 'LIKE', $keyword);
                        $dataWait['count'] = $dataWait['queryData']->count();
                        if ($dataWait['count'] > 0) {
                            $dataWait['queryData'] = $dataWait['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                            $data['dataSearch']["Web Defacement"] = $dataWait;
                        }
                    } else {
                        $dataWait['queryData'] = WebdefacmentSetting::select('id', 'name', 'url as content', DB::raw('CONCAT("/webdefacement/detail/",code) AS link'))->where('name', 'LIKE', $keyword)->whereIn('site_id',$site_id_arr);
                        $dataWait['count'] = $dataWait['queryData']->count();
                        if ($dataWait['count'] > 0) {
                            $dataWait['queryData'] = $dataWait['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                            $data['dataSearch']["Web Defacement"] = $dataWait;
                        }
                    }
                }
            }
           
            if(@$role_custom['indicators']) {
                if($type == 'events'){
                    $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
                    $pipeLine = array('name' => ['$regex'=>$this->request->keyword, '$options' => 'i']);
                    $dataWait['count'] = $col_fx_otx_events->count($pipeLine);
            
         
    
                    if($dataWait['count']>0){
                        $options = [
                            'allowDiskUse' => TRUE
                        ];
                        $pipeline = [
                            [
                                '$match' => [
                                    'name'  => ['$regex'=>$this->request->keyword, '$options' => 'i']
                                ]
                            ],
                            [
                                '$project' => [
                                    '_id' => 0,
                                    'id' => '$pulse_id',
                                    'name' => '$name',
                                    'is_modified' => '$is_modified',
                                    'public' => '$public',
                                    'created_at' => '$created_at',
                                    'modified' => '$modified',
                                    'tags' => '$tags',
                                    'groups' => '$groups',
                                    'industries' => '$industries',
                                    'content' => [ '$concat' => ['source: ','$source']],
                                    'link' => [ '$concat' => ['/indicators/events/events_detail/','$pulse_id']],
                                ]
                            ],
                            [
                                '$sort' => [
                                    'modified'  => -1,
                                ]
                            ],
                            [
                                '$limit' => $limit
                            ]
                        ];
                        
                        $dataWait['queryData'] = $col_fx_otx_events->aggregate($pipeline,$options);
                        $dataWait['queryData'] = $dataWait['queryData']->toArray();
                        $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$this->request->keyword;
                        $data['dataSearch']["Events"] = $dataWait;
                    
                    }else{
    
                 
                        $pipeLine = array('tags' => ['$regex'=>$this->request->keyword, '$options' => 'i']);
                        $dataWait['count'] = $col_fx_otx_events->count($pipeLine);
        
                   
        
                        if($dataWait['count']>0){
                            $options = [
                                'allowDiskUse' => TRUE
                            ];
                            $pipeline = [
                                [
                                    '$match' => [
                                        'tags'  => ['$regex'=>$this->request->keyword, '$options' => 'i']
                                    ]
                                ],
                                [
                                    '$project' => [
                                        '_id' => 0,
                                        'id' => '$pulse_id',
                                        'name' => '$name',
                                        'is_modified' => '$is_modified',
                                        'public' => '$public',
                                        'created_at' => '$created_at',
                                        'modified' => '$modified',
                                        'tags' => '$tags',
                                        'groups' => '$groups',
                                        'industries' => '$industries',
                                        'content' => [ '$concat' => ['source: ','$source']],
                                        'link' => [ '$concat' => ['/indicators/events/events_detail/','$pulse_id']],
                                    ]
                                ],
                                [
                                    '$sort' => [
                                        'modified'  => -1,
                                    ]
                                ],
                                [
                                    '$limit' => $limit
                                ]
                            ];
                            $dataWait['queryData'] = $col_fx_otx_events->aggregate($pipeline,$options);
                            $dataWait['queryData'] = $dataWait['queryData']->toArray();
                            $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$this->request->keyword;
                            $data['dataSearch']["Events"] = $dataWait;
                        }else{
                            $data['dataSearch']["Events"]  = array();
                        }
    
                    }
                }
            }

            if(@$role_custom['indicators']) {
                if($type == 'events'){
                    
                    $dataWait = null;
                    $col_fx_transaction_otx_indicators_data = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;
                    $pipeLine = array('indicator_name' => ['$regex'=>$this->request->keyword, '$options' => 'i']);
                    $dataWait['count'] = $col_fx_transaction_otx_indicators_data->count($pipeLine);
                  
          
                    if($dataWait['count']>0){
                        $options = [
                            'allowDiskUse' => TRUE
                        ];
                        $pipeline = [
                            [
                                '$match' => [
                                    'indicator_name'  => ['$regex'=>$this->request->keyword, '$options' => 'i'],
                                ]
                            ],
                            [
                                '$project' => [
                                    '_id' => 0,
                                    'id' => '$indicator_id',
                                    'name' => '$indicator_name',
                                    'content' => [ '$concat' => ['type: ', '$type' ]],
                                    'link' => [ '$concat' => ['/indicators/detail?id=','$indicator_id','&type=','$type']],
                                ]
                            ],
                            [
                                '$sort' => [
                                    'modified'  => -1,
                                ]
                            ],
                            [
                                '$limit' => $limit
                            ]
                        ];
    
    
                     
    
                                            //ให้แสดง Event แทน=========================================================
                                            $dataWait['queryData'] = $col_fx_transaction_otx_indicators_data->aggregate($pipeline,$options);
                                            $dataWait['queryData'] = $dataWait['queryData']->toArray();
                                            $event_ids = [];
                                            $indicator_id = [];

                                            foreach($dataWait['queryData'] as $key => $value){
                                                         
                                                    if(!in_array($value['id'],$indicator_id ) )
                                                    {
                                                        array_push($indicator_id,$value['id']);
                                                        
                                                    }
                                            }
                                           
                                            if(!empty($indicator_id)){
                                                $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;
                                                $options = [
                                                    'allowDiskUse' => TRUE
                                                ];
                                                $pipeline = [
                                                    [
                                                        '$match' => [
                                                            'indicator_id'  => ['$in'=>$indicator_id],
                                                        ]
                                                    ],
                                                    [
                                                        '$project' => [
                                                            '_id' => 0,
                                                            'pulse_id' => '$pulse_id',
                                                        ]
                                                    ],
                                                    [
                                                        '$sort' => [
                                                            'modified'  => -1,
                                                        ]
                                                    ],
                                                    [
                                                        '$limit' => $limit
                                                    ]
                                                ];
                                                $document_all = $col_fx_otx_events_indicator_ref->aggregate($pipeline,$options)->toArray();
                                            }
                                            
                                            if(!empty($document_all)){
                                                foreach($document_all as $key_plus => $value_plus){
                                                        if(!in_array($value_plus['pulse_id'],$event_ids ) )
                                                        {
                                                            array_push($event_ids,$value_plus['pulse_id']);
                                                            
                                                        }
                                                }
                                            }
                                           
                                            // foreach($dataWait['queryData'] as $key => $value){
    
    
       
                                            //     $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;
                                         
                                        
                                            //     $query = [
                                            //         'indicator_id' => $value['id'],
                                                    
                                            //     ];
                                        
                                        
                                            //     $options = [
                                            //         'sort' => [
                                            //             // $order => $dir
                                            //         ],
                                            //         'skip' =>  0,
                                            //         'limit' => 20,
                                            //     ];
                                        
                                            //     $cursor = $col_fx_otx_events_indicator_ref->find($query,$options);    
                                            //     $document_all = $cursor->toArray();
    
                                               
                                            //             // foreach($document_all as $key_plus => $value_plus){
                                                         
                                            //             //        if(!in_array($value_plus['pulse_id'],$event_ids ) )
                                            //             //         {
                                            //             //             array_push($event_ids,$value_plus['pulse_id']);
                                                                 
                                            //             //         }
                                            //             // }
    
    
    
                                            // }
                                         
                                     
                                          
                                            if(!empty($event_ids)){
                                                $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
                                                $pipeLine = array('pulse_id' => ['$in'=>$event_ids]);
                                                $dataWait['count'] = $col_fx_otx_events->count($pipeLine);
                                                
                                                $options = [
                                                    'allowDiskUse' => TRUE
                                                ];
                                                $pipeline = [
                                                    [
                                                        '$match' => [
                                                            'pulse_id'  => ['$in'=>$event_ids],
                                                        ]
                                                    ],
                                                    [
                                                        '$project' => [
                                                            '_id' => 0,
                                                            'id' => '$pulse_id',
                                                            'name' => '$name',
                                                            'is_modified' => '$is_modified',
                                                            'public' => '$public',
                                                            'created_at' => '$created_at',
                                                            'modified' => '$modified',
                                                            'tags' => '$tags',
                                                            'groups' => '$groups',
                                                            'industries' => '$industries',
                                                            'content' => [ '$concat' => ['source: ','$source']],
                                                            'link' => [ '$concat' => ['/indicators/events/events_detail/','$pulse_id']],
                                                        ]
                                                    ],
                                                    [
                                                        '$sort' => [
                                                            'modified'  => -1,
                                                        ]
                                                    ],
                                                    [
                                                        '$limit' => $limit
                                                    ]
                                                ];
                                                 $dataWait['queryData'] = $col_fx_otx_events->aggregate($pipeline,$options);
                                                
                                               if(count($data['dataSearch']["Events"]) > 0){
                                                    foreach ($dataWait['queryData']->toArray() as $queryData_data) {
                                                          array_push($data['dataSearch']["Events"],$queryData_data);
                                                      }
                                                      $data['dataSearch']["Events"]["count"] = $dataWait['count'];
                                                }else{
                                                   
                                                    $dataWait['queryData'] = $dataWait['queryData']->toArray();
                                                    $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$this->request->keyword;
                                                    $data['dataSearch']["Events"]["count"] = $dataWait['count'];
                                                    $data['dataSearch']["Events"] = $dataWait;
                                                }
                                                
                                               
                                            }
                                            
                                           // ===============================================================
                    }
                }
                // return 
            }
           
            if(@$role_custom['indicators']) {
                if($type == 'malware'){
                //Malware
                $this->request->keyword = trim($this->request->keyword);
                $dataWait = null;
                $col_fx_otx_malware_related = $clientMD->sosecure_threatintelligent->fx_otx_malware_related;
                $pipeLine = array('malware_name' => ['$regex'=>$this->request->keyword, '$options' => 'i']);
                $dataWait['count'] = $col_fx_otx_malware_related->count($pipeLine);
                if($dataWait['count']>0){
                    $options = [
                        'allowDiskUse' => TRUE
                    ];
                    $pipeline = [
                        [
                            '$match' => [
                                'malware_name'  => ['$regex'=>$this->request->keyword, '$options' => 'i'],
                            ]
                        ],
                        [
                            '$project' => [
                                '_id' => 0,
                                'malware_uuid' => '$malware_uuid',
                                'pulse_id' => '$pulse_id',
                            ]
                        ],
                        [
                            '$sort' => [
                                'updated_at'  => -1,
                            ]
                        ],
                        [
                            '$limit' => $limit
                        ]
                    ];


                 

                                        //ให้แสดง Event แทน=========================================================
                                        $dataWait['queryData'] = $col_fx_otx_malware_related->aggregate($pipeline,$options);
                                        $dataWait['queryData'] = $dataWait['queryData']->toArray();
                                        $event_ids = [];
                                  
                                        foreach($dataWait['queryData'] as $key_plus => $value_plus){
                                                     
                                            if(!in_array($value_plus['pulse_id'],$event_ids ) )
                                             {
                                                 array_push($event_ids,$value_plus['pulse_id']);
                                              
                                             }
                                       }


                                     
                                        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
                                        $pipeLine = array('pulse_id' => ['$in'=>$event_ids]);
                                        $dataWait['count'] =count($event_ids);
                                       
                                        if($dataWait['count']>0){
                                            $options = [
                                                'allowDiskUse' => TRUE
                                            ];
                                            $pipeline = [
                                                [
                                                    '$match' => [
                                                        'pulse_id'  => ['$in'=>$event_ids],
                                                    ]
                                                ],
                                                [
                                                    '$project' => [
                                                        '_id' => 0,
                                                        'id' => '$pulse_id',
                                                        'name' => '$name',
                                                        'is_modified' => '$is_modified',
                                                        'public' => '$public',
                                                        'created_at' => '$created_at',
                                                        'modified' => '$modified',
                                                        'tags' => '$tags',
                                                        'groups' => '$groups',
                                                        'industries' => '$industries',
                                                        'content' => [ '$concat' => ['source: ','$source']],
                                                        'link' => [ '$concat' => ['/indicators/events/events_detail/','$pulse_id']],
                                                    ]
                                                ],
                                                [
                                                    '$sort' => [
                                                        'modified'  => -1,
                                                    ]
                                                ],
                                                [
                                                    '$limit' => $limit
                                                ]
                                            ];
                                             $dataWait['queryData'] = $col_fx_otx_events->aggregate($pipeline,$options);
                                         
                                             $dataWait['queryData'] = $dataWait['queryData']->toArray();
                                             $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$this->request->keyword;
                                             $data['dataSearch']["Events"] = $dataWait;
                                        }
                                        //===============================================================

                    // $dataWait['queryData'] = $col_fx_transaction_otx_indicators_data->aggregate($pipeline,$options);
                    // $dataWait['queryData'] = $dataWait['queryData']->toArray();
                    // $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$this->request->keyword;
                    // $data['dataSearch']["indicators"] = $dataWait;
                }
            }
            }

            if(@$role_custom['indicators']) {
                if($type == 'adversaries'){
                //Adversaries
              
                $this->request->keyword = trim($this->request->keyword);
                $dataWait = null;
                $col_fx_otx_malware_related = $clientMD->sosecure_threatintelligent->fx_otx_adversaries_related;
                $pipeLine = array('adversary_name' => ['$regex'=>$this->request->keyword, '$options' => 'i']);
                $dataWait['count'] = $col_fx_otx_malware_related->count($pipeLine);
             
                if($dataWait['count']>0){
                    $options = [
                        'allowDiskUse' => TRUE
                    ];
                    $pipeline = [
                        [
                            '$match' => [
                                'adversary_name'  => ['$regex'=>$this->request->keyword, '$options' => 'i'],
                            ]
                        ],
                        [
                            '$project' => [
                                '_id' => 0,
                                'adversary_uuid' => '$adversary_uuid',
                                'pulse_id' => '$pulse_id',
                            ]
                        ],
                        [
                            '$sort' => [
                                'updated_at'  => -1,
                            ]
                        ],
                        [
                            '$limit' => $limit
                        ]
                    ];


               

                                        //ให้แสดง Event แทน=========================================================
                                     $dataWait['queryData'] = $col_fx_otx_malware_related->aggregate($pipeline,$options);
                                     $dataWait['queryData'] = $dataWait['queryData']->toArray();
                                        $event_ids = [];
                                       
                                        foreach($dataWait['queryData'] as $key_plus => $value_plus){
                                                     
                                            if(!in_array($value_plus['pulse_id'],$event_ids ) )
                                             {
                                                 array_push($event_ids,$value_plus['pulse_id']);
                                              
                                             }
                                       }


                                     
                                        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
                                        $pipeLine = array('pulse_id' => ['$in'=>$event_ids]);
                                        $dataWait['count'] =count($event_ids);
                                       
                                        if($dataWait['count']>0){
                                            $options = [
                                                'allowDiskUse' => TRUE
                                            ];
                                            $pipeline = [
                                                [
                                                    '$match' => [
                                                        'pulse_id'  => ['$in'=>$event_ids],
                                                    ]
                                                ],
                                                [
                                                    '$project' => [
                                                        '_id' => 0,
                                                        'id' => '$pulse_id',
                                                        'name' => '$name',
                                                        'is_modified' => '$is_modified',
                                                        'public' => '$public',
                                                        'created_at' => '$created_at',
                                                        'modified' => '$modified',
                                                        'tags' => '$tags',
                                                        'groups' => '$groups',
                                                        'industries' => '$industries',
                                                        'content' => [ '$concat' => ['source: ','$source']],
                                                        'link' => [ '$concat' => ['/indicators/events/events_detail/','$pulse_id']],
                                                    ]
                                                ],
                                                [
                                                    '$sort' => [
                                                        'modified'  => -1,
                                                    ]
                                                ],
                                                [
                                                    '$limit' => $limit
                                                ]
                                            ];
                                             $dataWait['queryData'] = $col_fx_otx_events->aggregate($pipeline,$options);
                                          
                                             $dataWait['queryData'] = $dataWait['queryData']->toArray();
                                          
                                             $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$this->request->keyword;
                                             $data['dataSearch']["Events"] = $dataWait;
                                            
                                        }
                                        //===============================================================

                    // $dataWait['queryData'] = $col_fx_transaction_otx_indicators_data->aggregate($pipeline,$options);
                    // $dataWait['queryData'] = $dataWait['queryData']->toArray();
                    // $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$this->request->keyword;
                    // $data['dataSearch']["indicators"] = $dataWait;
                }
            }
            }

            if(@$role_custom['indicators']) {
                if($type == 'malware'){
                //Malware
                $this->request->keyword = trim($this->request->keyword);
                $dataWait = null;
                $col_fx_otx_malware_related = $clientMD->sosecure_threatintelligent->fx_otx_malware;
                $pipeLine = array('malware_uuid' => ['$regex'=>$this->request->keyword, '$options' => 'i']);
                $dataWait['count'] = $col_fx_otx_malware_related->count($pipeLine);
                if($dataWait['count']>0){
                    $options = [
                        'allowDiskUse' => TRUE
                    ];
                    $pipeline = [
                        [
                            '$match' => [
                                'malware_uuid'  => ['$regex'=>$this->request->keyword, '$options' => 'i'],
                            ]
                        ],
                        [
                            '$project' => [
                                '_id' => 0,
                                'id' => '$malware_uuid',
                                'name' => '$malware_uuid',
                                'content' => [ '$concat' => ['category: ', '$category' ]],
                                'link' => [ '$concat' => ['/indicators/detail_malware?malware_uuid=','$malware_uuid','&name=','$malware_uuid']],
                            ]
                        ],
                        [
                            '$sort' => [
                                'updated_at'  => -1,
                            ]
                        ],
                        [
                            '$limit' => $limit
                        ]
                    ];


                 

                    $dataWait['queryData'] = $col_fx_otx_malware_related->aggregate($pipeline,$options);
                    $dataWait['queryData'] = $dataWait['queryData']->toArray();
                    $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/malware?Search_Link_All=".$this->request->keyword;
                    $data['dataSearch']["malware"] = $dataWait;
                }
            }
            }

            if(@$role_custom['indicators']) {
                if($type == 'adversaries'){
                //Adversaries
              
                $this->request->keyword = trim($this->request->keyword);
                $dataWait = null;
                $col_fx_otx_malware_related = $clientMD->sosecure_threatintelligent->fx_otx_adversaries;
                $pipeLine = array('name' => ['$regex'=>$this->request->keyword, '$options' => 'i']);
                $dataWait['count'] = $col_fx_otx_malware_related->count($pipeLine);
                
                if($dataWait['count']>0){
                    $options = [
                        'allowDiskUse' => TRUE
                    ];
                    $pipeline = [
                        [
                            '$match' => [
                                'name'  => ['$regex'=>$this->request->keyword, '$options' => 'i'],
                            ]
                        ],
                        [
                            '$project' => [
                                '_id' => 0,
                                'id' => '$adversary_uuid',
                                'name' => '$name',
                                'content' => [ '$concat' => ['description: ', '$description' ]],
                                'link' => [ '$concat' => ['/indicators/detail_adversary/','$adversary_uuid','?name=','$name']],
                            ]
                        ],
                        [
                            '$sort' => [
                                'updated_at'  => -1,
                            ]
                        ],
                        [
                            '$limit' => $limit
                        ]
                    ];




                    $dataWait['queryData'] = $col_fx_otx_malware_related->aggregate($pipeline,$options);
                    $dataWait['queryData'] = $dataWait['queryData']->toArray();
                    $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/detail_adversary?Search_Link_All=".$this->request->keyword;
                    $data['dataSearch']["adversaries"] = $dataWait;
                }
            }
        }
        }
        return response()->json($data);
    }

    public function search(Request $mode)
    {
        set_time_limit(120);
        // $this->request->validate(['keyword' => 'required']);
        $data['dataSearch'] = array();
        $limit = 100;//->take($limit)

        $role_custom = @check_role_custom();
        $site_id_arr = @get_role_custom()['site_id_arr'];

        if ($this->request->keyword && $this->request->mode !== 'lookup') {

            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);
            
            if(@$role_custom['news']) {
                $keyword = '%' . $this->request->keyword . '%';
                $dataWait['queryData'] = R_s_s_news::select('id', 'title_th as name', 'detail_th as content', DB::raw('CONCAT("/public/news/detail/",code ,"/th") AS link'))->where('title_th', 'LIKE', $keyword);
                $dataWait['count'] = $dataWait['queryData']->count();
                $dataWait['queryData'] = $dataWait['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                $dataWait2['queryData'] = R_s_s_news::select('id', 'title_en as name', 'detail_en as content', DB::raw('CONCAT("/public/news/detail/",code ,"/en" ) AS link'))->where('title_en', 'LIKE', $keyword);
                $dataWait2['count'] = $dataWait2['queryData']->count() + $dataWait['count'];
                if ($dataWait2['count'] > 0){
                    $dataWait2['queryData'] = $dataWait2['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                    $dataWait2['queryData'] = array_merge($dataWait['queryData'], $dataWait2['queryData']);
                    $dataWait2['moreDetail'] = $dataWait2['count']<101?"":$this->request->keyword;
                    $data['dataSearch']["News"] = $dataWait2;

                }
            }

            if(@$role_custom['vulnerabilities']) {
                if(@get_role_custom()['superadmin'] == 1) {
                    $CVEMappingAssets_name = CVEMappingAssets::select('namecve')->get();

                    $dataWait['queryData'] = CVEMapping::select('id', 'namecve as name', 'description as content', DB::raw('CONCAT("/monitoringvulnerabilitys") AS link'))->where('namecve', 'LIKE', $keyword)->whereIn('namecve', $CVEMappingAssets_name);
                    $dataWait['count'] = $dataWait['queryData']->count();
                    if ($dataWait['count'] > 0) {
                        $dataWait['queryData'] = $dataWait['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                        $data['dataSearch']["Vulnerabilities"] = $dataWait;
                    }
                } else {
                    $CVEMappingAssets_name = CVEMappingAssets::whereIn('site_id', $site_id_arr)->select('namecve')->get();

                    $dataWait['queryData'] = CVEMapping::select('id', 'namecve as name', 'description as content', DB::raw('CONCAT("/monitoringvulnerabilitys") AS link'))->where('namecve', 'LIKE', $keyword)->whereIn('namecve', $CVEMappingAssets_name);
                    $dataWait['count'] = $dataWait['queryData']->count();
                    if ($dataWait['count'] > 0) {
                        $dataWait['queryData'] = $dataWait['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                        $data['dataSearch']["Vulnerabilities"] = $dataWait;
                    }
                }
            }


            if(@$role_custom['compromised']) {
                if(@get_role_custom()['superadmin'] == 1) {
                    $DataLeakFeed_compromised = DataLeakFeed::select('data_leak_feed.id', 'data_leak_feed.feedcontent as content', 'data_leak_feed.sourceid', 'data_leak_feed.keyword as name', DB::raw('CONCAT("/darkweb-datas") AS link'))->whereNull('data_leak_feed.deleted_at')->whereNull('data_leak_socail_ref.deleted_at')->where('data_leak_socail_ref.status',1)->whereIn('data_leak_feed.feel_type',['darkweb','webserver','compromise','compromised'])->where(function ($query) use ($keyword) {
                        $query->where('data_leak_feed.keyword', 'LIKE', $keyword)
                            ->orWhere('data_leak_feed.source_name', 'LIKE', $keyword) ->orWhere('data_leak_feed.feedcontent', 'LIKE', $keyword);
                    });
                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');
                        // if(isset($SiteSettings->id)){
                    $dataWait["queryData"] = $DataLeakFeed_compromised;
                    $dataWait["count"] = $dataWait["queryData"]->count();
                    if ($dataWait['count'] > 0) {
                        $dataWait["queryData"] = $dataWait["queryData"]->orderBy('data_leak_feed.updated_at', 'desc')->get()->toArray();
                        $data['dataSearch']["Compromised"] = $dataWait;
                    }
                } else {
                    $DataLeakFeed_compromised = DataLeakFeed::select('data_leak_feed.id', 'data_leak_feed.feedcontent as content', 'data_leak_feed.sourceid', 'data_leak_feed.keyword as name', DB::raw('CONCAT("/darkweb-datas") AS link'))->whereNull('data_leak_feed.deleted_at')->whereNull('data_leak_socail_ref.deleted_at')->where('data_leak_socail_ref.status',1)->whereIn('data_leak_feed.feel_type',['darkweb','webserver','compromise','compromised'])->where(function ($query) use ($keyword) {
                        $query->where('data_leak_feed.keyword', 'LIKE', $keyword)
                            ->orWhere('data_leak_feed.source_name', 'LIKE', $keyword) ->orWhere('data_leak_feed.feedcontent', 'LIKE', $keyword);
                    });
                    $DataLeakFeed_compromised = $DataLeakFeed_compromised->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');
                        // if(isset($SiteSettings->id)){
                    $dataWait["queryData"] = $DataLeakFeed_compromised->whereIn('site.id', $site_id_arr);
                    $dataWait["count"] = $dataWait["queryData"]->count();
                    if ($dataWait['count'] > 0) {
                        $dataWait["queryData"] = $dataWait["queryData"]->orderBy('data_leak_feed.updated_at', 'desc')->get()->toArray();
                        $data['dataSearch']["Compromised"] = $dataWait;
                    }
                }
            }

            if(@$role_custom['data_leak']) {

                $keywords = $this->request->keyword;
                $DataLeakFeed_data  = DataLeakFeed::where('deleted_at', null)->where('status','1')->whereIn('feel_type', ['social','darkweb_public'])->get();
                foreach ($DataLeakFeed_data as $value_data) {
                      $value_data->feedcontent_decode = html_entity_decode($value_data->feedcontent);
                     
                }
                $DataLeakFeed_data_id = array();
                array_push($DataLeakFeed_data_id, 0);
                foreach($DataLeakFeed_data as $a) {
                    if(strpos($a->feedcontent_decode, $keywords) !== false) {
                        array_push($DataLeakFeed_data_id, $a->id);
                    } 
                }
        
              
              



                if(@get_role_custom()['superadmin'] == 1) {
                    $DataLeakFeed_social = DataLeakFeed::select('data_leak_feed.id', 'data_leak_feed.feedcontent as content', 'data_leak_feed.sourceid', 'data_leak_feed.keyword as name', DB::raw('CONCAT("/socialdatas") AS link'))->whereNull('data_leak_feed.deleted_at')->whereNull('data_leak_socail_ref.deleted_at')->whereIn('data_leak_feed.feel_type',['social', 'darkweb_public'])->where(function ($query) use ($keyword,$DataLeakFeed_data_id) {
                        $query->where('data_leak_feed.keyword', 'LIKE', $keyword)
                        ->orWhere('data_leak_feed.source_name', 'LIKE', $keyword) ->orWhere('data_leak_feed.feedcontent', 'LIKE', $keyword)
                        ->orWhereIn('data_leak_feed.id', $DataLeakFeed_data_id);
                    });
                 
                    $DataLeakFeed_social = $DataLeakFeed_social->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');
                    $dataWait["queryData"] = $DataLeakFeed_social;

                    $dataWait["count"] = $dataWait["queryData"]->count();
                    if ($dataWait['count'] > 0) {

                    

                        $dataWait["queryData"] = $dataWait["queryData"]->orderBy('data_leak_feed.updated_at', 'desc')->get()->toArray();
                        $data['dataSearch']["Data Leak"] = $dataWait;
                    }
                } else {
                    $DataLeakFeed_social = DataLeakFeed::select('data_leak_feed.id', 'data_leak_feed.feedcontent as content', 'data_leak_feed.sourceid', 'data_leak_feed.keyword as name', DB::raw('CONCAT("/socialdatas") AS link'))->whereNull('data_leak_feed.deleted_at')->whereNull('data_leak_socail_ref.deleted_at')->whereIn('data_leak_feed.feel_type',['social', 'darkweb_public'])->where(function ($query) use ($keyword,$DataLeakFeed_data_id) {
                        $query->where('data_leak_feed.keyword', 'LIKE', $keyword)
                        ->orWhere('data_leak_feed.source_name', 'LIKE', $keyword) ->orWhere('data_leak_feed.feedcontent', 'LIKE', $keyword)
                        ->orWhereIn('data_leak_feed.id', $DataLeakFeed_data_id);
                    });
                    $DataLeakFeed_social = $DataLeakFeed_social->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');
                    $dataWait["queryData"] = $DataLeakFeed_social->whereIn('site.id', $site_id_arr);
                    
                    $dataWait["count"] = $dataWait["queryData"]->count();
                    if ($dataWait['count'] > 0) {
                        $dataWait["queryData"] = $dataWait["queryData"]->orderBy('data_leak_feed.updated_at', 'desc')->get()->toArray();
                        $data['dataSearch']["Data Leak"] = $dataWait;
                    }
                }
            }


            if(@$role_custom['web_defacement']) {
                if(@get_role_custom()['superadmin'] == 1) {
                    $dataWait['queryData'] = WebdefacmentSetting::select('id', 'name', 'url as content', DB::raw('CONCAT("/webdefacement/detail/",code) AS link'))->where('name', 'LIKE', $keyword);
                    $dataWait['count'] = $dataWait['queryData']->count();
                    if ($dataWait['count'] > 0) {
                        $dataWait['queryData'] = $dataWait['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                        $data['dataSearch']["Web Defacement"] = $dataWait;
                    }
                } else {
                    $dataWait['queryData'] = WebdefacmentSetting::select('id', 'name', 'url as content', DB::raw('CONCAT("/webdefacement/detail/",code) AS link'))->where('name', 'LIKE', $keyword)->whereIn('site_id',$site_id_arr);
                    $dataWait['count'] = $dataWait['queryData']->count();
                    if ($dataWait['count'] > 0) {
                        $dataWait['queryData'] = $dataWait['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                        $data['dataSearch']["Web Defacement"] = $dataWait;
                    }
                }
            }


            // $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;//indicator_name
            // $pipeLine = array('indicator_name' => ['$regex'=>$this->request->keyword, '$options' => 'i']);
            // $dataWait['count'] = $col_fx_otx_indicator_detail->count($pipeLine);
            // if($dataWait['count']>0){
            //     $options = [
            //         'allowDiskUse' => TRUE
            //     ];
            //     $pipeline = [
            //         [
            //             '$match' => [
            //                 'indicator_name'  => ['$regex'=>$this->request->keyword, '$options' => 'i'],
            //             ]
            //         ],
            //         [
            //             '$project' => [
            //                 '_id' => 0,
            //                 'id' => '$indicator_id',
            //                 'name' => '$indicator_name',
            //                 'content' => [ '$concat' => ['source: ','$source']],
            //                 'link' => [ '$concat' => ['/indicators/detail?id=','$indicator_id','&type=','$type','&indicator=','$indicator_name']],
            //             ]
            //         ],
            //         [
            //             '$sort' => [
            //                 'updated_at'  => -1,
            //             ]
            //         ],
            //         [
            //             '$limit' => $limit
            //         ]
            //     ];
            //     $dataWait['queryData'] = $col_fx_otx_indicator_detail->aggregate($pipeline,$options);
            //     $dataWait['queryData'] = $dataWait['queryData']->toArray();
            //     $dataWait['moreDetail'] = $dataWait['count']<101?"":$this->request->keyword;
            //     $data['dataSearch']["Attributes"] = $dataWait;
            // }

            if(@$role_custom['indicators']) {
                $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
                $pipeLine = array('name' => ['$regex'=>$this->request->keyword, '$options' => 'i']);
                $dataWait['count'] = $col_fx_otx_events->count($pipeLine);

            

                if($dataWait['count']>0){
                    $options = [
                        'allowDiskUse' => TRUE
                    ];
                    $pipeline = [
                        [
                            '$match' => [
                                'name'  => ['$regex'=>$this->request->keyword, '$options' => 'i']
                            ]
                        ],
                        [
                            '$project' => [
                                '_id' => 0,
                                'id' => '$pulse_id',
                                'name' => '$name',
                                'is_modified' => '$is_modified',
                                'public' => '$public',
                                'created_at' => '$created_at',
                                'modified' => '$modified',
                                'tags' => '$tags',
                                'groups' => '$groups',
                                'industries' => '$industries',
                                'content' => [ '$concat' => ['source: ','$source']],
                                'link' => [ '$concat' => ['/indicators/events/events_detail/','$pulse_id']],
                            ]
                        ],
                        [
                            '$sort' => [
                                'modified'  => -1,
                            ]
                        ],
                        [
                            '$limit' => $limit
                        ]
                    ];
                    
                    $dataWait['queryData'] = $col_fx_otx_events->aggregate($pipeline,$options);
                    $dataWait['queryData'] = $dataWait['queryData']->toArray();
                    $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$this->request->keyword;
                    $data['dataSearch']["Events"] = $dataWait;
                
                }else{

             
                    $pipeLine = array('tags' => ['$regex'=>$this->request->keyword, '$options' => 'i']);
                    $dataWait['count'] = $col_fx_otx_events->count($pipeLine);
    
               
    
                    if($dataWait['count']>0){
                        $options = [
                            'allowDiskUse' => TRUE
                        ];
                        $pipeline = [
                            [
                                '$match' => [
                                    'tags'  => ['$regex'=>$this->request->keyword, '$options' => 'i']
                                ]
                            ],
                            [
                                '$project' => [
                                    '_id' => 0,
                                    'id' => '$pulse_id',
                                    'name' => '$name',
                                    'is_modified' => '$is_modified',
                                    'public' => '$public',
                                    'created_at' => '$created_at',
                                    'modified' => '$modified',
                                    'tags' => '$tags',
                                    'groups' => '$groups',
                                    'industries' => '$industries',
                                    'content' => [ '$concat' => ['source: ','$source']],
                                    'link' => [ '$concat' => ['/indicators/events/events_detail/','$pulse_id']],
                                ]
                            ],
                            [
                                '$sort' => [
                                    'modified'  => -1,
                                ]
                            ],
                            [
                                '$limit' => $limit
                            ]
                        ];
                        $dataWait['queryData'] = $col_fx_otx_events->aggregate($pipeline,$options);
                        $dataWait['queryData'] = $dataWait['queryData']->toArray();
                        $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$this->request->keyword;
                        $data['dataSearch']["Events"] = $dataWait;
                    }else{
                        $data['dataSearch']["Events"]  = array();
                    }
                    
    

                }
            }

            if(@$role_custom['indicators']) {
                $dataWait = null;
                $col_fx_transaction_otx_indicators_data = $clientMD->sosecure_threatintelligent->fx_transaction_otx_indicators_data;
                $pipeLine = array('indicator' => ['$regex'=>$this->request->keyword, '$options' => 'i']);
                $dataWait['count'] = $col_fx_transaction_otx_indicators_data->count($pipeLine);
                if($dataWait['count']>0){
                    $options = [
                        'allowDiskUse' => TRUE
                    ];
                    $pipeline = [
                        [
                            '$match' => [
                                'indicator'  => ['$regex'=>$this->request->keyword, '$options' => 'i'],
                            ]
                        ],
                        [
                            '$project' => [
                                '_id' => 0,
                                'id' => '$indicator_id',
                                'name' => '$indicator',
                                'content' => [ '$concat' => ['type: ', '$type' ]],
                                'link' => [ '$concat' => ['/indicators/detail?id=','$indicator_id','&type=','$type']],
                            ]
                        ],
                        [
                            '$sort' => [
                                'modified'  => -1,
                            ]
                        ],
                        [
                            '$limit' => $limit
                        ]
                    ];


                 

                                        //ให้แสดง Event แทน=========================================================
                                        $dataWait['queryData'] = $col_fx_transaction_otx_indicators_data->aggregate($pipeline,$options);
                                        $dataWait['queryData'] = $dataWait['queryData']->toArray();
                                        $event_ids = [];
                                  
                                        foreach($dataWait['queryData'] as $key => $value){


   
                                            $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;
                                     
                                    
                                            $query = [
                                                'indicator_id' => $value['id'],
                                                
                                            ];
                                    
                                    
                                            $options = [
                                                'sort' => [
                                                    // $order => $dir
                                                ],
                                                'skip' =>  0,
                                                'limit' => 20,
                                            ];
                                    
                                            $cursor = $col_fx_otx_events_indicator_ref->find($query,$options);    
                                            $document_all = $cursor->toArray();

                                           
                                                    foreach($document_all as $key_plus => $value_plus){
                                                     
                                                           if(!in_array($value_plus['pulse_id'],$event_ids ) )
                                                            {
                                                                array_push($event_ids,$value_plus['pulse_id']);
                                                             
                                                            }
                                                    }



                                        }
                                     
                                        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
                                        $pipeLine = array('pulse_id' => ['$in'=>$event_ids]);
                                        $dataWait['count'] =count($event_ids);
                                       
                                        if($dataWait['count']>0){
                                            $options = [
                                                'allowDiskUse' => TRUE
                                            ];
                                            $pipeline = [
                                                [
                                                    '$match' => [
                                                        'pulse_id'  => ['$in'=>$event_ids],
                                                    ]
                                                ],
                                                [
                                                    '$project' => [
                                                        '_id' => 0,
                                                        'id' => '$pulse_id',
                                                        'name' => '$name',
                                                        'is_modified' => '$is_modified',
                                                        'public' => '$public',
                                                        'created_at' => '$created_at',
                                                        'modified' => '$modified',
                                                        'tags' => '$tags',
                                                        'groups' => '$groups',
                                                        'industries' => '$industries',
                                                        'content' => [ '$concat' => ['source: ','$source']],
                                                        'link' => [ '$concat' => ['/indicators/events/events_detail/','$pulse_id']],
                                                    ]
                                                ],
                                                [
                                                    '$sort' => [
                                                        'modified'  => -1,
                                                    ]
                                                ],
                                                [
                                                    '$limit' => $limit
                                                ]
                                            ];
                                             $dataWait['queryData'] = $col_fx_otx_events->aggregate($pipeline,$options);
                                         
                                           //  $dataWait['queryData'] = $dataWait['queryData']->toArray();
                                           //  $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$this->request->keyword;
                                          // dd($data['dataSearch']["Events"]);
                                           if(count($data['dataSearch']["Events"]) > 0){
                                                foreach ($dataWait['queryData']->toArray() as $queryData_data) {
                                                      array_push($data['dataSearch']["Events"],$queryData_data);
                                                  }

                                            }else{
                                                $dataWait['queryData'] = $dataWait['queryData']->toArray();
                                                $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$this->request->keyword;
                                                $data['dataSearch']["Events"] = $dataWait;
                                            }
                                            
                                           
                                        }
                                        //===============================================================

                    // $dataWait['queryData'] = $col_fx_transaction_otx_indicators_data->aggregate($pipeline,$options);
                    // $dataWait['queryData'] = $dataWait['queryData']->toArray();
                    // $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$this->request->keyword;
                    // $data['dataSearch']["indicators"] = $dataWait;
                }
            }
         
            if(@$role_custom['indicators']) {
                //Malware
                $this->request->keyword = trim($this->request->keyword);
                $dataWait = null;
                $col_fx_otx_malware_related = $clientMD->sosecure_threatintelligent->fx_otx_malware_related;
                $pipeLine = array('malware_name' => ['$regex'=>$this->request->keyword, '$options' => 'i']);
                $dataWait['count'] = $col_fx_otx_malware_related->count($pipeLine);
                if($dataWait['count']>0){
                    $options = [
                        'allowDiskUse' => TRUE
                    ];
                    $pipeline = [
                        [
                            '$match' => [
                                'malware_name'  => ['$regex'=>$this->request->keyword, '$options' => 'i'],
                            ]
                        ],
                        [
                            '$project' => [
                                '_id' => 0,
                                'malware_uuid' => '$malware_uuid',
                                'pulse_id' => '$pulse_id',
                            ]
                        ],
                        [
                            '$sort' => [
                                'updated_at'  => -1,
                            ]
                        ],
                        [
                            '$limit' => $limit
                        ]
                    ];


                 

                                        //ให้แสดง Event แทน=========================================================
                                        $dataWait['queryData'] = $col_fx_otx_malware_related->aggregate($pipeline,$options);
                                        $dataWait['queryData'] = $dataWait['queryData']->toArray();
                                        $event_ids = [];
                                  
                                        foreach($dataWait['queryData'] as $key_plus => $value_plus){
                                                     
                                            if(!in_array($value_plus['pulse_id'],$event_ids ) )
                                             {
                                                 array_push($event_ids,$value_plus['pulse_id']);
                                              
                                             }
                                       }


                                     
                                        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
                                        $pipeLine = array('pulse_id' => ['$in'=>$event_ids]);
                                        $dataWait['count'] =count($event_ids);
                                       
                                        if($dataWait['count']>0){
                                            $options = [
                                                'allowDiskUse' => TRUE
                                            ];
                                            $pipeline = [
                                                [
                                                    '$match' => [
                                                        'pulse_id'  => ['$in'=>$event_ids],
                                                    ]
                                                ],
                                                [
                                                    '$project' => [
                                                        '_id' => 0,
                                                        'id' => '$pulse_id',
                                                        'name' => '$name',
                                                        'is_modified' => '$is_modified',
                                                        'public' => '$public',
                                                        'created_at' => '$created_at',
                                                        'modified' => '$modified',
                                                        'tags' => '$tags',
                                                        'groups' => '$groups',
                                                        'industries' => '$industries',
                                                        'content' => [ '$concat' => ['source: ','$source']],
                                                        'link' => [ '$concat' => ['/indicators/events/events_detail/','$pulse_id']],
                                                    ]
                                                ],
                                                [
                                                    '$sort' => [
                                                        'modified'  => -1,
                                                    ]
                                                ],
                                                [
                                                    '$limit' => $limit
                                                ]
                                            ];
                                             $dataWait['queryData'] = $col_fx_otx_events->aggregate($pipeline,$options);
                                         
                                             $dataWait['queryData'] = $dataWait['queryData']->toArray();
                                             $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$this->request->keyword;
                                             $data['dataSearch']["Events"] = $dataWait;
                                        }
                                        //===============================================================

                    // $dataWait['queryData'] = $col_fx_transaction_otx_indicators_data->aggregate($pipeline,$options);
                    // $dataWait['queryData'] = $dataWait['queryData']->toArray();
                    // $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$this->request->keyword;
                    // $data['dataSearch']["indicators"] = $dataWait;
                }
            }

            if(@$role_custom['indicators']) {
                //Adversaries
              
                $this->request->keyword = trim($this->request->keyword);
                $dataWait = null;
                $col_fx_otx_malware_related = $clientMD->sosecure_threatintelligent->fx_otx_adversaries_related;
                $pipeLine = array('adversary_name' => ['$regex'=>$this->request->keyword, '$options' => 'i']);
                $dataWait['count'] = $col_fx_otx_malware_related->count($pipeLine);
             
                if($dataWait['count']>0){
                    $options = [
                        'allowDiskUse' => TRUE
                    ];
                    $pipeline = [
                        [
                            '$match' => [
                                'adversary_name'  => ['$regex'=>$this->request->keyword, '$options' => 'i'],
                            ]
                        ],
                        [
                            '$project' => [
                                '_id' => 0,
                                'adversary_uuid' => '$adversary_uuid',
                                'pulse_id' => '$pulse_id',
                            ]
                        ],
                        [
                            '$sort' => [
                                'updated_at'  => -1,
                            ]
                        ],
                        [
                            '$limit' => $limit
                        ]
                    ];


               

                                        //ให้แสดง Event แทน=========================================================
                                     $dataWait['queryData'] = $col_fx_otx_malware_related->aggregate($pipeline,$options);
                                     $dataWait['queryData'] = $dataWait['queryData']->toArray();
                                        $event_ids = [];
                                       
                                        foreach($dataWait['queryData'] as $key_plus => $value_plus){
                                                     
                                            if(!in_array($value_plus['pulse_id'],$event_ids ) )
                                             {
                                                 array_push($event_ids,$value_plus['pulse_id']);
                                              
                                             }
                                       }


                                     
                                        $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;
                                        $pipeLine = array('pulse_id' => ['$in'=>$event_ids]);
                                        $dataWait['count'] =count($event_ids);
                                       
                                        if($dataWait['count']>0){
                                            $options = [
                                                'allowDiskUse' => TRUE
                                            ];
                                            $pipeline = [
                                                [
                                                    '$match' => [
                                                        'pulse_id'  => ['$in'=>$event_ids],
                                                    ]
                                                ],
                                                [
                                                    '$project' => [
                                                        '_id' => 0,
                                                        'id' => '$pulse_id',
                                                        'name' => '$name',
                                                        'is_modified' => '$is_modified',
                                                        'public' => '$public',
                                                        'created_at' => '$created_at',
                                                        'modified' => '$modified',
                                                        'tags' => '$tags',
                                                        'groups' => '$groups',
                                                        'industries' => '$industries',
                                                        'content' => [ '$concat' => ['source: ','$source']],
                                                        'link' => [ '$concat' => ['/indicators/events/events_detail/','$pulse_id']],
                                                    ]
                                                ],
                                                [
                                                    '$sort' => [
                                                        'modified'  => -1,
                                                    ]
                                                ],
                                                [
                                                    '$limit' => $limit
                                                ]
                                            ];
                                             $dataWait['queryData'] = $col_fx_otx_events->aggregate($pipeline,$options);
                                          
                                             $dataWait['queryData'] = $dataWait['queryData']->toArray();
                                          
                                             $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$this->request->keyword;
                                             $data['dataSearch']["Events"] = $dataWait;
                                            
                                        }
                                      
                                        //===============================================================

                    // $dataWait['queryData'] = $col_fx_transaction_otx_indicators_data->aggregate($pipeline,$options);
                    // $dataWait['queryData'] = $dataWait['queryData']->toArray();
                    // $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$this->request->keyword;
                    // $data['dataSearch']["indicators"] = $dataWait;
                }
            }

            if(@$role_custom['indicators']) {
                //Malware
                $this->request->keyword = trim($this->request->keyword);
                $dataWait = null;
                $col_fx_otx_malware_related = $clientMD->sosecure_threatintelligent->fx_otx_malware;
                $pipeLine = array('malware_uuid' => ['$regex'=>$this->request->keyword, '$options' => 'i']);
                $dataWait['count'] = $col_fx_otx_malware_related->count($pipeLine);
                if($dataWait['count']>0){
                    $options = [
                        'allowDiskUse' => TRUE
                    ];
                    $pipeline = [
                        [
                            '$match' => [
                                'malware_uuid'  => ['$regex'=>$this->request->keyword, '$options' => 'i'],
                            ]
                        ],
                        [
                            '$project' => [
                                '_id' => 0,
                                'id' => '$malware_uuid',
                                'name' => '$malware_uuid',
                                'content' => [ '$concat' => ['category: ', '$category' ]],
                                'link' => [ '$concat' => ['/indicators/detail_malware?malware_uuid=','$malware_uuid','&name=','$malware_uuid']],
                            ]
                        ],
                        [
                            '$sort' => [
                                'updated_at'  => -1,
                            ]
                        ],
                        [
                            '$limit' => $limit
                        ]
                    ];


                 

                    $dataWait['queryData'] = $col_fx_otx_malware_related->aggregate($pipeline,$options);
                    $dataWait['queryData'] = $dataWait['queryData']->toArray();
                    $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/malware?Search_Link_All=".$this->request->keyword;
                    $data['dataSearch']["malware"] = $dataWait;
                }
            }

            if(@$role_custom['indicators']) {
                //Adversaries
              
                $this->request->keyword = trim($this->request->keyword);
                $dataWait = null;
                $col_fx_otx_malware_related = $clientMD->sosecure_threatintelligent->fx_otx_adversaries;
                $pipeLine = array('name' => ['$regex'=>$this->request->keyword, '$options' => 'i']);
                $dataWait['count'] = $col_fx_otx_malware_related->count($pipeLine);
                
                if($dataWait['count']>0){
                    $options = [
                        'allowDiskUse' => TRUE
                    ];
                    $pipeline = [
                        [
                            '$match' => [
                                'name'  => ['$regex'=>$this->request->keyword, '$options' => 'i'],
                            ]
                        ],
                        [
                            '$project' => [
                                '_id' => 0,
                                'id' => '$adversary_uuid',
                                'name' => '$name',
                                'content' => [ '$concat' => ['description: ', '$description' ]],
                                'link' => [ '$concat' => ['/indicators/detail_adversary/','$adversary_uuid','?name=','$name']],
                            ]
                        ],
                        [
                            '$sort' => [
                                'updated_at'  => -1,
                            ]
                        ],
                        [
                            '$limit' => $limit
                        ]
                    ];




                    $dataWait['queryData'] = $col_fx_otx_malware_related->aggregate($pipeline,$options);
                    $dataWait['queryData'] = $dataWait['queryData']->toArray();
                    $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/detail_adversary?Search_Link_All=".$this->request->keyword;
                    $data['dataSearch']["adversaries"] = $dataWait;
                }
            }

        } else {

        }

        // dd(json_encode($data['news']));
        // $data['invoices'] = \Modules\Invoices\Entities\Invoice::select('id', 'reference_no', 'title')->WithAnyTags($this->request->keyword)->get();
        // $data['estimates'] = \Modules\Estimates\Entities\Estimate::select('id', 'reference_no', 'title')->WithAnyTags($this->request->keyword)->get();
        // $data['projects'] = \Modules\Projects\Entities\Project::select('id', 'name')->WithAnyTags($this->request->keyword)->get();
        // $data['credits'] = \Modules\Creditnotes\Entities\CreditNote::select('id', 'reference_no')->WithAnyTags($this->request->keyword)->get();
        // $data['deals'] = \Modules\Deals\Entities\Deal::select('id', 'title')->WithAnyTags($this->request->keyword)->get();
        // $data['leads'] = \Modules\Leads\Entities\Lead::select('id', 'name')->WithAnyTags($this->request->keyword)->get();
        // $data['expenses'] = \Modules\Expenses\Entities\Expense::select('id', 'code')->WithAnyTags($this->request->keyword)->get();
        // $data['clients'] = \Modules\Clients\Entities\Client::select('id', 'name')->WithAnyTags($this->request->keyword)->get();
        // $data['issues'] = \Modules\Issues\Entities\Issue::WithAnyTags($this->request->keyword)->get();
        // $data['articles'] = \Modules\Knowledgebase\Entities\Knowledgebase::WithAnyTags($this->request->keyword)->get();
        // $data['payments'] = \Modules\Payments\Entities\Payment::select('id', 'code')->WithAnyTags($this->request->keyword)->get();
        // $data['tasks'] = \Modules\Tasks\Entities\Task::select('id', 'name', 'project_id')->WithAnyTags($this->request->keyword)->get();
        // $data['tickets'] = \Modules\Tickets\Entities\Ticket::select('id', 'subject')->WithAnyTags($this->request->keyword)->get();
        // $data['milestones'] = \Modules\Milestones\Entities\Milestone::select('id', 'milestone_name')->WithAnyTags($this->request->keyword)->get();


        $data['page'] = langapp('search');
        $data['keyword'] = $this->request->keyword;
        return view('searches')->with($data,compact('mode'));
    }

    public function loadSearchAPI(Request $request)
    {
        set_time_limit(120); // Increase max execution time for multiple API calls
        if ($request->hasSession()) {
            $request->session()->save(); // Release session lock to allow concurrent AJAX requests
        }

        $role_custom = @check_role_custom();
        $source = $request->source;
        $original_keyword = $request->keyword;
        $keyword = $request->keyword;
        
        // Strip port from IPv4 if present (e.g. 1.2.3.4:80 -> 1.2.3.4)
        if (preg_match('/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}):\d+$/', $keyword, $matches)) {
            if (filter_var($matches[1], FILTER_VALIDATE_IP)) {
                $keyword = $matches[1]; // Use IP without port for most feeds
            }
        }

        $site_code = $request->code;
        $site_id = 0;
        $site = null;
    
       //format
       $type = $this->check_keyword_type($keyword);

        if ($source === 'calculate_risk') {
            if ($type === '') {
                return response()->json([
                    'status_code' => 401,
                    'message' => 'allow only type ( IP,Domain,URL,MD5, SHA1 or SHA256 ) Please contact the system administrator.',
                    'type' => $type,
                ]);
            }

            try {
                $iocType = IndicatorCheckService::normalizeIocType($type);
                $service = new IndicatorCheckService();
                $client = new GuzzleClient(['verify' => false, 'timeout' => 30]);
                $result = $service->checkIocAsync($client, $keyword, $iocType, null)->wait();

                return response()->json([
                    'status_code' => 200,
                    'data' => [
                        'total_score' => $result['total_score'],
                        'risk_level' => $result['risk_level'],
                        'debug_scores' => $result['debug_scores'] ?? [],
                        'debug_weights' => $result['debug_weights'] ?? [],
                    ],
                    'type' => $type,
                ]);
            } catch (\Exception $e) {
                \Log::warning('Search calculate_risk failed: ' . $e->getMessage());

                return response()->json([
                    'status_code' => 500,
                    'error' => $e->getMessage(),
                    'type' => $type,
                ]);
            }
        }

        if($source == 'internal_events') {
            try {
                $clientMD = new \MongoDB\Client(env("DB_MONGO_STOREDATAB"));
                $db = PublishedFeedsService::database($clientMD);
                $queryOptions = [
                    'maxTimeMS' => 15000,
                    'typeMap' => ['root' => 'array', 'document' => 'array'],
                    'hint' => ['indicator' => 1],
                ];

                $indicatorVariants = array_values(array_unique(array_filter([
                    $keyword,
                    $original_keyword !== $keyword ? $original_keyword : null,
                    strtolower($keyword),
                    strtoupper($keyword),
                ], function ($value) {
                    return $value !== null && $value !== '';
                })));

                $eventsList = [];
                $refQuery = count($indicatorVariants) === 1
                    ? ['indicator' => $indicatorVariants[0]]
                    : ['indicator' => ['$in' => $indicatorVariants]];

                $otxRefs = $db->fx_otx_events_indicator_ref->find(
                    $refQuery,
                    array_merge($queryOptions, [
                        'limit' => 500,
                        'projection' => ['pulse_id' => 1],
                    ])
                )->toArray();
                
                $pulseIds = [];
                foreach ($otxRefs as $ref) {
                    if (!isset($ref['pulse_id'])) {
                        continue;
                    }

                    $rawPulseId = $ref['pulse_id'];
                    if (is_array($rawPulseId) || $rawPulseId instanceof \Traversable) {
                        foreach ($rawPulseId as $pid) {
                            if (is_scalar($pid) && (string) $pid !== '') {
                                $pulseIds[] = (string) $pid;
                            }
                        }
                        continue;
                    }

                    if (is_scalar($rawPulseId) && (string) $rawPulseId !== '') {
                        $pulseIds[] = (string) $rawPulseId;
                    }
                }

                $pulseIds = array_values(array_unique($pulseIds));

                if (count($pulseIds) > 100) {
                    $pulseIds = array_slice($pulseIds, 0, 100);
                }

                if (count($pulseIds) > 0) {
                    $otxEvents = $db->fx_otx_events->find(
                        ['pulse_id' => ['$in' => $pulseIds]],
                        array_merge($queryOptions, [
                            'limit' => 100,
                            'projection' => ['pulse_id' => 1, 'name' => 1, 'tags' => 1, 'created' => 1],
                        ])
                    )->toArray();
                    
                    foreach ($otxEvents as $event) {
                        $pulseId = $event['pulse_id'] ?? 'Unknown';
                        $sourceName = strpos($pulseId, 'misp.') === 0 ? 'MISP' : 'AlienVault OTX';
                        
                        $dateStr = 'N/A';
                        if (isset($event['created'])) {
                            if ($event['created'] instanceof \MongoDB\BSON\UTCDateTime) {
                                $dateStr = $event['created']->toDateTime()->setTimezone(new \DateTimeZone('Asia/Bangkok'))->format('Y-m-d H:i:s');
                            } else {
                                $dateStr = (string)$event['created'];
                            }
                        }
                        
                        $tags = '';
                        if (isset($event['tags']) && is_array($event['tags'])) {
                            $tags = implode(', ', $event['tags']);
                        }
                        
                        $eventsList[] = [
                            'source' => $sourceName,
                            'event_id' => $pulseId,
                            'event_name' => $event['name'] ?? 'Unknown',
                            'tags' => $tags,
                            'date' => $dateStr
                        ];
                    }
                }
                
                return response()->json([
                    'status_code' => 200,
                    'data' => $eventsList,
                    'type' => $type
                ]);
            } catch (\MongoDB\Driver\Exception\ExecutionTimeoutException $e) {
                \Log::warning('internal_events MongoDB timeout: ' . $e->getMessage(), [
                    'keyword' => $keyword,
                ]);

                return response()->json([
                    'status_code' => 200,
                    'data' => [],
                    'message' => 'Internal events search timed out. Use the exact IOC value from Indicators.',
                    'type' => $type,
                ]);
            } catch (\Exception $e) {
                if (stripos($e->getMessage(), 'exceeded time limit') !== false) {
                    \Log::warning('internal_events MongoDB timeout: ' . $e->getMessage(), [
                        'keyword' => $keyword,
                    ]);

                    return response()->json([
                        'status_code' => 200,
                        'data' => [],
                        'message' => 'Internal events search timed out. Use the exact IOC value from Indicators.',
                        'type' => $type,
                    ]);
                }

                return response()->json([
                    'status_code' => 500,
                    'error' => $e->getMessage(),
                    'type' => $type
                ]);
            }
        }

        if($site_code){
            $site = SiteSettings::where('code', $site_code)->first();
            $site_id = $site -> id;


            if(!$site->allow_api_api_loookup=="Y"){

                $response_data = array(
                    'status_code' => 400,
                    'search_api_loookup_allow' =>0,
                    'message' => 'Please contact the system administrator.',
                );
                return response()->json($response_data);

            }
        }else{
            $site = SiteSettings::first();

        }
        $site = SiteSettings::first();
       // return response()->json($site);
       if($type == ''){
        $response_data = array(
            'status_code' => 401,
            'search_api_loookup_allow' =>0,
            'message' => 'allow only type ( IP,Domain,URL,MD5, SHA1 or SHA256 ) Please contact the system administrator.',
        );
        return response()->json($response_data);
       }else{

         if($type == 'SHA256'){
             if(strlen($keyword) !=64){
                $response_data = array(
                    'status_code' => 400,
                    'search_api_loookup_allow' =>0,
                    'message' => 'Incorrect format',
                );
                return response()->json($response_data);
             }
         }
         if($type == 'SHA1'){
            if(strlen($keyword) !=40){
               $response_data = array(
                   'status_code' => 400,
                   'search_api_loookup_allow' =>0,
                   'message' => 'Incorrect format',
               );
               return response()->json($response_data);
            }
        }

       }
       

     
        $center_search_api_loookup_limit = 0;
        $center_search_api_loookup_allow = 0;
        $log_search = LogSearch::where('keyword', $keyword)->count();
        $site_request_limit_api_query = null;
        if($site_code){
               //$site
               $center_search_api_loookup_limit =$site->search_api_loookup_limit;
               $site_request_limit_api_count = $site->search_api_loookup_Use;
   
        }else{
            if(!empty(Auth::user()->site_id)){
               // $site = SiteSettings::where('id', Auth::user()->site_id)->first();
                $center_search_api_loookup_limit =$site->search_api_loookup_limit;
            }else{
                $center_search_api_loookup_limit = env('center_search_api_loookup_limit', 1000);
            }
              
               $site_request_limit_api_query = SiteRequestLimitApi::where('mode', 'api_limit')->where('site_id',0)->first();
               $site_request_limit_api_count =$site_request_limit_api_query->count;
        }
        // if($log_search> 0){
        //     $center_search_api_loookup_allow = 1;

        // }else{
                if(app()->environment('local') || (int)$center_search_api_loookup_limit > $site_request_limit_api_count){
                    $center_search_api_loookup_allow = 1;
                  
                    if($source == 'check_api_search_limit'){
                        if($site_code){
                            FacadesDB::table('site')
                            ->where('code', $site_code)
                            ->update(['search_api_loookup_Use' => $site->search_api_loookup_Use++]);
                            $site -> save();
                            $site_request_limit_api_count = $site->search_api_loookup_Use;
                        }else{
                            $site_request_limit_api_query->count = $site_request_limit_api_count+1;
                            $site_request_limit_api_query->save();
                            $site_request_limit_api_count = $site_request_limit_api_count+1;
    
                        }
                    }


                }else{
                    $response_data = array(
                        'status_code' => 400,
                        'search_api_loookup_allow' => 0,
                        'message' => 'Search API lookup limit exceeded. Please contact your system administrator.',
                    );
                    return response()->json($response_data);
                }

        // }

  
        if($source =="otx_puls_tag"){
            $type="tags";
        }

   
        $response = '{}';
        $response2 = "{}";
        $response3 = '{}';

        if($source =="ibmcloud"){
            return response()->json(['status_code' => 400, 'message' => 'IBM Cloud is disabled']);
            $log_search = LogSearch::select('path')->where('keyword', $keyword)->where('source', $source)->first();
         
            if($log_search){
                $url = storage_path() .'/app/public/'.$log_search -> path;
                if(!File::exists($url)){
                    $response = null;
                }else{
                    $response3 = file_get_contents($url); 
             
                    if($response3 == '[]' || $response3 == null || $response3 == '')
                    {
                        if(File::exists($url))
                        {File::delete($url);}

                      //  $ibmcloud_API_Key = "d4b45ba9-4a1f-4127-bb72-1a01ab26a4b9";
                      //  $ibmcloud_API_Key_Password = "95d8e0cd-0f34-45dc-9c6c-aa490fcb0415";
                
                      list($ibmcloud_API_Key, $ibmcloud_API_Key_Password) = IndicatorCheckService::getBasicAuthCredentials('IBMCLOUD');
                        if($type == 'IP'){
                            $ibmcloud_url = "https://exchange.xforce.ibmcloud.com/api/ipr/" . $keyword;
                        }else if($type == 'Domain' || $type == 'URL'){
                            $ibmcloud_url = "https://exchange.xforce.ibmcloud.com/api/url/" . $keyword;
                        }else if($type == 'SHA256' || $type == 'MD5' || $type == 'SHA1'){
                            $ibmcloud_url = "https://exchange.xforce.ibmcloud.com/api/malware/" . $keyword;
                        }
                        
                        $ch = curl_init();
                        header('Content-type: application/json');
                        curl_setopt($ch, CURLOPT_URL,$ibmcloud_url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                        curl_setopt($ch, CURLOPT_USERPWD, "$ibmcloud_API_Key:$ibmcloud_API_Key_Password");
                        $result = curl_exec($ch);
                        $response = $result;
                        curl_close($ch);  
                        $path = 'search_file/'.time().'.json';
                        if( Storage::disk('public')->put($path, $response)) {
                            $log_search = LogSearch::where('keyword', $keyword)->where('source', $source)->first();
                            $log_search -> path = $path;
                            $log_search -> save();
                        }
                    }
                    else
                    {
                        $response = file_get_contents($url); 
                    }
                }
            }else{
                $check_limit_search = $this->check_limit_search($site_id, $source);

                if(!$check_limit_search){
                    $response_data = array(
                        'status_code' => 400,
                        'message' => 'เกิน limit การค้นหา Please contact the system administrator.',
                    );
                    return response()->json($response_data);
                }
           
               // $ibmcloud_API_Key = "d4b45ba9-4a1f-4127-bb72-1a01ab26a4b9";
               // $ibmcloud_API_Key_Password = "95d8e0cd-0f34-45dc-9c6c-aa490fcb0415";
               list($ibmcloud_API_Key, $ibmcloud_API_Key_Password) = IndicatorCheckService::getBasicAuthCredentials('IBMCLOUD');
                if($type == 'IP'){
                    $ibmcloud_url = "https://exchange.xforce.ibmcloud.com/api/ipr/" . $keyword;
                }else if($type == 'Domain' || $type == 'URL'){
                    $ibmcloud_url = "https://exchange.xforce.ibmcloud.com/api/url/" . $keyword;
                }else if($type == 'SHA256' || $type == 'MD5' || $type == 'SHA1'){
                    $ibmcloud_url = "https://exchange.xforce.ibmcloud.com/api/malware/" . $keyword;
                }
           
                $ch = curl_init();
                header('Content-type: application/json');
                curl_setopt($ch, CURLOPT_URL,$ibmcloud_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, "$ibmcloud_API_Key:$ibmcloud_API_Key_Password");
                $result = curl_exec($ch);
                $response = $result;
                curl_close($ch);  
                $path = 'search_file/'.time().'.json';
                if( Storage::disk('public')->put($path, $response)) {
                    $log_search = new LogSearch();
                    $log_search -> path = $path;
                    $log_search -> keyword = $keyword;
                    $log_search -> type = $type;
                    $log_search -> source = $source;
                    $log_search -> save();
                }
            }
            
        }else if($source =="virustotal"){
            $log_search = LogSearch::where('keyword', $keyword)->where('source', $source)->first();
            if($log_search && !File::exists(storage_path() .'/app/public/'.$log_search -> path)){
                $log_search->delete();
                $log_search = null;
            }
            if($log_search){
                $url = storage_path() .'/app/public/'.$log_search -> path;
                $response3 = file_get_contents($url); 
                if($response3 == '[]' || $response3 == null || $response3 == '')
                {
                    if(File::exists($url))
                    {File::delete($url);}

                    $virustotal_API_Key = IndicatorCheckService::getRandomKey('VT');
                    if($type == 'IP'){
                        $virustotal_url = "https://www.virustotal.com/api/v3/ip_addresses/" . $keyword;
                    }else if($type == 'Domain'){
                        $virustotal_url='https://www.virustotal.com/api/v3/domains/' . $keyword;
                    }else if($type == 'URL'){
                        $virustotal_url='https://www.virustotal.com/api/v3/url/' . $keyword;
                    }else if($type == 'SHA256' || $type == 'MD5' || $type == 'SHA1'){
                        $virustotal_url='https://www.virustotal.com/api/v3/files/' . $keyword;
                    }

                    $headers = array(
                        'X-Apikey: '.$virustotal_API_Key
                    );
                    // Send request to Server
                    $ch = curl_init($virustotal_url);
                    // To save response in a variable from server, set headers;
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    // Get response
                    $response = curl_exec($ch);
                    curl_close($ch);  

                    $path = 'search_file/'.time().'.json';
                    if( Storage::disk('public')->put($path, $response)) {
                        $log_search = LogSearch::where('keyword', $keyword)->where('source', $source)->first();
                        $log_search -> path = $path;
                        $log_search -> save();
                    }
                }
                else
                {
                    $response = file_get_contents($url); 
                }
            }else{
                $check_limit_search = $this->check_limit_search($site_id, $source);

                if(!$check_limit_search){
                    $response_data = array(
                        'status_code' => 400,
                        'message' => 'เกิน limit การค้นหาPlease contact the system administrator.',
                    );
                    return response()->json($response_data);
                }
                $virustotal_API_Key = IndicatorCheckService::getRandomKey('VT');
                if($type == 'IP'){
                    $virustotal_url = "https://www.virustotal.com/api/v3/ip_addresses/" . $keyword;
                }else if($type == 'Domain'){
                    $virustotal_url='https://www.virustotal.com/api/v3/domains/' . $keyword;
                }else if($type == 'URL'){
                    $virustotal_url='https://www.virustotal.com/api/v3/url/' . $keyword;
                }else if($type == 'SHA256' || $type == 'MD5' || $type == 'SHA1'){
                    $virustotal_url='https://www.virustotal.com/api/v3/files/' . $keyword;
                }

                $headers = array(
                    'X-Apikey: '.$virustotal_API_Key
                );
                // Send request to Server
                $ch = curl_init($virustotal_url);
                // To save response in a variable from server, set headers;
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                // Get response
                $response = curl_exec($ch);
                curl_close($ch);  

                $path = 'search_file/'.time().'.json';
                if( Storage::disk('public')->put($path, $response)) {
                    $log_search = new LogSearch();
                    $log_search -> path = $path;
                    $log_search -> keyword = $keyword;
                    $log_search -> type = $type;
                    $log_search -> source = $source;
                    $log_search -> save();
                }


            

            }

        }else if($source =="hybrid"){
            $log_search = LogSearch::where('keyword', $keyword)->where('source', $source)->first();
            if($log_search && !File::exists(storage_path() .'/app/public/'.$log_search -> path)){
                $log_search->delete();
                $log_search = null;
            }
            if($log_search){
                $url = storage_path() .'/app/public/'.$log_search -> path;
                $response3 = file_get_contents($url); 
                if($response3 == '[]' || $response3 == null || $response3 == '')
                {
                    if(File::exists($url))
                    {File::delete($url);}
                    $hybrid_API_Key = IndicatorCheckService::getRandomKey('HYBRID');

                    //$virustotal_url='https://www.virustotal.com/api/v3/domains/xlus0222uj81bxyf.xyz';
                    $headers = array(
                        'api-key: '.$hybrid_API_Key,
                        'accept: '.'application/json',
                        'Content-Type: '.'application/x-www-form-urlencoded',
                        'user-agent: '.'Falcon Sandbox',
                    );
                    //'host'=>'151.101.2.110','domain'=>'151.101.2.110','url'=>'151.101.2.110','url'=>'151.101.2.110','similar_to'=>'151.101.2.110','context'=>'151.101.2.110'
                    
                    if($type == 'IP'){
                        $hybrid_url = "https://hybrid-analysis.com/api/v2/search/terms";
                        $fields = array('host'=>$keyword);
                        $postvars = '';
                        foreach($fields as $key=>$value) {
                            $postvars .= $key . "=" . $value . "&";
                        }
                    }else if($type == 'Domain'){
                        $hybrid_url = "https://hybrid-analysis.com/api/v2/search/terms";
                        $fields = array('domain'=>$keyword);
                        $postvars = '';
                        foreach($fields as $key=>$value) {
                            $postvars .= $key . "=" . $value . "&";
                        }
                    }else if($type == 'URL'){
                        $hybrid_url = "https://hybrid-analysis.com/api/v2/search/terms";
                        $fields = array('domain'=>$keyword);
                        $postvars = '';
                        foreach($fields as $key=>$value) {
                            $postvars .= $key . "=" . $value . "&";
                        }
                    }else if($type == 'SHA256' || $type == 'MD5' || $type == 'SHA1'){
                        $hybrid_url = "https://hybrid-analysis.com/api/v2/search/hash";
                        $fields = array('hash'=>$keyword);
                        $postvars = '';
                        foreach($fields as $key=>$value) {
                            $postvars .= $key . "=" . $value . "&";
                        }
                    }
                    
                    // Send request to Server
                    $ch = curl_init($hybrid_url);
                    // To save response in a variable from server, set headers;
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_POSTFIELDS,$postvars);
                    // Get response
                    $response = curl_exec($ch);
                    curl_close($ch);  

                    $path = 'search_file/'.time().'.json';
                    if( Storage::disk('public')->put($path, $response)) {
                        $log_search = LogSearch::where('keyword', $keyword)->where('source', $source)->first();
                        $log_search -> path = $path;
                        $log_search -> save();
                    }
                }
                else
                {
                    $response = file_get_contents($url); 
                }
            }else{
                $check_limit_search = $this->check_limit_search($site_id, $source);

                if(!$check_limit_search){
                    $response_data = array(
                        'status_code' => 400,
                        'message' => 'เกิน limit การค้นหาPlease contact the system administrator.',
                    );
                    return response()->json($response_data);
                }

                $hybrid_API_Key = IndicatorCheckService::getRandomKey('HYBRID');

                //$virustotal_url='https://www.virustotal.com/api/v3/domains/xlus0222uj81bxyf.xyz';
                $headers = array(
                    'api-key: '.$hybrid_API_Key,
                    'accept: '.'application/json',
                    'Content-Type: '.'application/x-www-form-urlencoded',
                    'user-agent: '.'Falcon Sandbox',
                );
                //'host'=>'151.101.2.110','domain'=>'151.101.2.110','url'=>'151.101.2.110','url'=>'151.101.2.110','similar_to'=>'151.101.2.110','context'=>'151.101.2.110'
                
                if($type == 'IP'){
                    $hybrid_url = "https://hybrid-analysis.com/api/v2/search/terms";
                    $fields = array('host'=>$keyword);
                    $postvars = '';
                    foreach($fields as $key=>$value) {
                        $postvars .= $key . "=" . $value . "&";
                    }
                }else if($type == 'Domain'){
                    $hybrid_url = "https://hybrid-analysis.com/api/v2/search/terms";
                    $fields = array('domain'=>$keyword);
                    $postvars = '';
                    foreach($fields as $key=>$value) {
                        $postvars .= $key . "=" . $value . "&";
                    }
                }else if($type == 'URL'){
                    $hybrid_url = "https://hybrid-analysis.com/api/v2/search/terms";
                    $fields = array('domain'=>$keyword);
                    $postvars = '';
                    foreach($fields as $key=>$value) {
                        $postvars .= $key . "=" . $value . "&";
                    }
                }else if($type == 'SHA256' || $type == 'MD5' || $type == 'SHA1'){
                    $hybrid_url = "https://hybrid-analysis.com/api/v2/search/hash";
                    $fields = array('hash'=>$keyword);
                    $postvars = '';
                    foreach($fields as $key=>$value) {
                        $postvars .= $key . "=" . $value . "&";
                    }
                }
                
                // Send request to Server
                $ch = curl_init($hybrid_url);
                // To save response in a variable from server, set headers;
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS,$postvars);
                // Get response
                $response = curl_exec($ch);
                curl_close($ch);  

                $path = 'search_file/'.time().'.json';
                if( Storage::disk('public')->put($path, $response)) {
                    $log_search = new LogSearch();
                    $log_search -> path = $path;
                    $log_search -> keyword = $keyword;
                    $log_search -> type = $type;
                    $log_search -> source = $source;
                    $log_search -> save();
                }
            }
        }else if($source =="otx_indicators"){


            $otx_API_Key = IndicatorCheckService::getRandomKey('OTX');
            $otx_general_url ="";
            $otx_analysis_url ="";
            if($type == 'IP'){
                if (!filter_var($keyword, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    $otx_analysis_url = "https://otx.alienvault.com/api/v1/indicators/IPv4/".$keyword."/general";
                    $otx_general_url = "https://otx.alienvault.com/otxapi/indicator/ip/analysis/".$keyword;
                } else {
                    $otx_analysis_url = "https://otx.alienvault.com/api/v1/indicators/IPv6/".$keyword."/general";
                    $otx_general_url = "https://otx.alienvault.com/otxapi/indicator/ip/analysis/".$keyword;
                }

            }else if($type == 'Domain'){
                $otx_analysis_url = "https://otx.alienvault.com/api/v1/indicators/domain/".$keyword."/general";
                $otx_general_url = "https://otx.alienvault.com/otxapi/indicator/url/analysis/".$keyword;
               
            }else if($type == 'URL'){
                $otx_analysis_url = "https://otx.alienvault.com/otxapi/indicator/url/general/".rawurlencode($keyword);
                $otx_general_url = "https://otx.alienvault.com/otxapi/indicator/url/analysis/".rawurlencode($keyword);
            }else if($type == 'SHA256' || $type == 'MD5' || $type == 'SHA1'){

                $otx_general_url = "https://otx.alienvault.com/api/v1/indicators/file/".$keyword."/general";
                $otx_analysis_url = "https://otx.alienvault.com/api/v1/indicators/file/".$keyword."/analysis";
            }else{


            }


            $ch = curl_init();
            $headers = array(
                 'X-OTX-API-KEY: '.$otx_API_Key,
                'accept: '.'application/json',
                 'Content-Type: '.'application/x-www-form-urlencoded',
            );
            // Send request to Server
            $ch = curl_init($otx_analysis_url);
            // To save response in a variable from server, set headers;
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            // Get response
            $response = curl_exec($ch);
            curl_close($ch);  

            if($otx_general_url){
                    $ch = curl_init();
                    $headers = array(
                        'X-OTX-API-KEY: '.$otx_API_Key,
                        'accept: '.'application/json',
                        'Content-Type: '.'application/x-www-form-urlencoded',
                    );
                    // Send request to Server
                    $ch = curl_init($otx_general_url);
                    // To save response in a variable from server, set headers;
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                    // Get response
                    $response2 = curl_exec($ch);
                    curl_close($ch);  
             }
  



        }else if($source =="otx_puls"){
        
            $otx_API_Key = IndicatorCheckService::getRandomKey('OTX');  
            $otx_url = "https://otx.alienvault.com/api/v1/pulses/".$keyword;
           
            $ch = curl_init();
            $headers = array(
                 'X-OTX-API-KEY: '.$otx_API_Key,
                'accept: '.'application/json',
                 'Content-Type: '.'application/x-www-form-urlencoded',
            );
            // Send request to Server
            $ch = curl_init($otx_url);
            // To save response in a variable from server, set headers;
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            // Get response
            $response = curl_exec($ch);
            curl_close($ch);  

        
        }else if($source =="otx_puls_indicator"){
        
            $otx_API_Key = IndicatorCheckService::getRandomKey('OTX');  
            $otx_url = "https://otx.alienvault.com/api/v1/pulses/".$keyword.'/indicators';
           
            $ch = curl_init();
            $headers = array(
                 'X-OTX-API-KEY: '.$otx_API_Key,
                'accept: '.'application/json',
                 'Content-Type: '.'application/x-www-form-urlencoded',
            );
            // Send request to Server
            $ch = curl_init($otx_url);
            // To save response in a variable from server, set headers;
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            // Get response
            $response = curl_exec($ch);
            curl_close($ch);  

        
        }else if($source =="otx_puls_tag"){
            $keyword =str_replace(' ', '%20', trim($keyword));

            $otx_API_Key = IndicatorCheckService::getRandomKey('OTX');  
            if(!$request->nextpage){
                $otx_url = "https://otx.alienvault.com/otxapi/pulses/?limit=20&page=1&sort=-modified&q=tag:".$keyword;
            }else{
                $otx_url =$request->nextpage;
            }
         
           
            $ch = curl_init();
            $headers = array(
                 'X-OTX-API-KEY: '.$otx_API_Key,
                'accept: '.'application/json',
                 'Content-Type: '.'application/x-www-form-urlencoded',
            );
            // Send request to Server
            $ch = curl_init($otx_url);
            // To save response in a variable from server, set headers;
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            // Get response
            $response = curl_exec($ch);
            curl_close($ch);  

        
        }else if($source =="abuseipdb"){
            $log_search = LogSearch::where('keyword', $keyword)->where('source', $source)->first();
            if($log_search && !File::exists(storage_path() .'/app/public/'.$log_search -> path)){
                $log_search->delete();
                $log_search = null;
            }
            if($log_search){
                $url = storage_path() .'/app/public/'.$log_search -> path;
                $response3 = file_get_contents($url);
                if(!$this->isValidAbuseIPDBResponse($response3)) {
                    if(File::exists($url)) { File::delete($url); }
                    $response = $this->fetchAbuseIPDB($keyword);
                    if($this->isValidAbuseIPDBResponse($response)) {
                        $path = 'search_file/'.time().'.json';
                        if( Storage::disk('public')->put($path, $response)) {
                            $log_search = LogSearch::where('keyword', $keyword)->where('source', $source)->first();
                            $log_search -> path = $path;
                            $log_search -> save();
                        }
                    }
                } else {
                    $response = $response3;
                }
            }else{
                $response = $this->fetchAbuseIPDB($keyword);
                if($this->isValidAbuseIPDBResponse($response)) {
                    $path = 'search_file/'.time().'.json';
                    if( Storage::disk('public')->put($path, $response)) {
                        $log_search = new LogSearch();
                        $log_search -> path = $path;
                        $log_search -> keyword = $keyword;
                        $log_search -> type = $type;
                        $log_search -> source = $source;
                        $log_search -> save();
                    }
                }
            }
        }else if($source =="threatfox"){
            \Log::info('[ThreatFox Debug] keyword='.$keyword.', original_keyword='.$original_keyword);
            $log_search = LogSearch::where('keyword', $keyword)->where('source', $source)->first();
            if($log_search && !File::exists(storage_path() .'/app/public/'.$log_search -> path)){
                $log_search->delete();
                $log_search = null;
            }
            // Also clear stale cache that contains 'no_result' from previous searches without port
            if($log_search){
                $url = storage_path() .'/app/public/'.$log_search -> path;
                $cachedContent = File::exists($url) ? file_get_contents($url) : '';
                $cachedData = json_decode($cachedContent, true);
                \Log::info('[ThreatFox Debug] Cache found, query_status=' . ($cachedData['query_status'] ?? 'N/A') . ', content_len=' . strlen($cachedContent));
                if(empty($cachedContent) || $cachedContent == '[]' || 
                   (is_array($cachedData) && isset($cachedData['query_status']) && $cachedData['query_status'] !== 'ok')){
                    // Stale or no_result cache — delete and re-fetch
                    \Log::info('[ThreatFox Debug] Clearing stale cache');
                    if(File::exists($url)) { File::delete($url); }
                    $log_search->delete();
                    $log_search = null;
                }
            }
            if($log_search){
                $url = storage_path() .'/app/public/'.$log_search -> path;
                $response = file_get_contents($url); 
                \Log::info('[ThreatFox Debug] Using cached response');
            }else{
                \Log::info('[ThreatFox Debug] Fetching fresh with keyword: ' . $original_keyword);
                $response = $this->fetchThreatFox($original_keyword);
                \Log::info('[ThreatFox Debug] API response: ' . substr($response, 0, 500));
                $path = 'search_file/'.time().'.json';
                if( Storage::disk('public')->put($path, $response)) {
                    $log_search = new LogSearch();
                    $log_search -> path = $path;
                    $log_search -> keyword = $keyword;
                    $log_search -> type = $type;
                    $log_search -> source = $source;
                    $log_search -> save();
                }
            }
        }else if($source =="rstcloud"){
            $log_search = LogSearch::where('keyword', $keyword)->where('source', $source)->first();
            if($log_search && !File::exists(storage_path() .'/app/public/'.$log_search -> path)){
                $log_search->delete();
                $log_search = null;
            }
            if($log_search){
                $url = storage_path() .'/app/public/'.$log_search -> path;
                $response3 = file_get_contents($url); 
                if($response3 == '[]' || $response3 == null || $response3 == '') {
                    if(File::exists($url)) { File::delete($url); }
                    $response = $this->fetchRSTCloud($keyword);
                    $path = 'search_file/'.time().'.json';
                    if( Storage::disk('public')->put($path, $response)) {
                        $log_search = LogSearch::where('keyword', $keyword)->where('source', $source)->first();
                        $log_search -> path = $path;
                        $log_search -> save();
                    }
                } else {
                    $response = file_get_contents($url); 
                }
            }else{
                $response = $this->fetchRSTCloud($keyword);
                $path = 'search_file/'.time().'.json';
                if( Storage::disk('public')->put($path, $response)) {
                    $log_search = new LogSearch();
                    $log_search -> path = $path;
                    $log_search -> keyword = $keyword;
                    $log_search -> type = $type;
                    $log_search -> source = $source;
                    $log_search -> save();
                }
            }
        }else if($source =="check_api_search_limit"){
            // if($site_code){
            //     $center_search_api_loookup_limit =$site->search_api_loookup_limit;
            //     $site_request_limit_api_count = SiteRequestLimitApi::orderBy('id', 'desc')->select('count')->where('mode', 'search')->where('site_id',$site_id)->first();
    
            // }else{
            //     $center_search_api_loookup_limit = env('center_search_api_loookup_limit', 1000);
            //     $site_request_limit_api_count = SiteRequestLimitApi::orderBy('id', 'desc')->select('count')->where('mode', 'search')->where('site_id',0)->first();
            // }


        }
   

    
        $decodedResponse = json_decode($response, true);
        $statusCode = Response::HTTP_OK;
        if ($source === 'abuseipdb' && is_array($decodedResponse)) {
            if (!empty($decodedResponse['errors'][0]['status'])) {
                $statusCode = (int) $decodedResponse['errors'][0]['status'];
            } elseif (!isset($decodedResponse['data'])) {
                $statusCode = Response::HTTP_BAD_GATEWAY;
            }
        }

        $response_data = array(
            'status_code' => $statusCode,
            'message' => '',
            'data' => $decodedResponse,
            'data2' => json_decode($response2, true),
            'type' => $type,
            'source' => $source,
            'site_code' =>$site_code,
            'center_search_api_loookup_limit' =>$center_search_api_loookup_limit,
            'site_request_limit_api_count' =>$site_request_limit_api_count,
            'search_api_loookup_allow' =>$center_search_api_loookup_allow,
        );
        return response()->json($response_data);
    }

    private function check_keyword_type($keyword){
        $type = '';
        if (preg_match("/^([a-f0-9]{64})$/", $keyword) == 1) {
            $type =  'SHA256';
        }else if(preg_match('/^[a-f0-9]{32}$/', $keyword)) {
            $type = 'MD5';
        }else if(preg_match('/^[0-9a-f]{40}$/i', $keyword)) {
            $type = 'SHA1';
        }else if(filter_var($keyword, FILTER_VALIDATE_IP)) {
            $type = 'IP';
        }else if(filter_var($keyword, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $type = 'IP';
        }else if(filter_var($keyword, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE)) {
            $type = 'IP';
        }else if(filter_var($keyword, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE)) {
            $type = 'IP';
        }else if($this->is_valid_domain($keyword)) {
            $type = 'Domain';
        }else if(preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$keyword)) {
            $type = 'URL';
        }else if(preg_match("/\b(?:(?:http?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i",$keyword)) {
            $type = 'URL';
        }
        return $type;
    }
    

    private function check_limit_search($site_id, $source){
        // $site_request_limit_api = SiteRequestLimitApi::where('site_id', $site_id)->where('mode', 'search')->where('source', $source)->first();
        // $system_limit_api = SystemLimitApi::select('limit')->where('mode', 'search')->where('source', $source)->first();
        // if($site_request_limit_api){
        //     $site_limit_api = SiteLimitApi::select('limit')->where('site_id', $site_id)->where('source', $source)->where('mode', 'search')->first();
        //     if($site_request_limit_api -> count < $site_limit_api -> limit){
        //         $site_request_limit_api_sum = SiteRequestLimitApi::select('count')->where('mode', 'search')->where('source', $source)->sum('count');
        //         if($site_request_limit_api_sum < $system_limit_api -> limit){
        //             $site_request_limit_api -> count = $site_request_limit_api -> count + 1;
        //             $site_request_limit_api -> save();
        //             $status = true;
        //         }else{
        //             $status = false;
        //         }
        //     }else{
        //         $status = false;
        //     }
        // }else{
        //     $site_request_limit_api_sum = SiteRequestLimitApi::select('count')->where('mode', 'search')->where('source', $source)->sum('count');
        //     if($site_request_limit_api_sum < $system_limit_api -> limit){
        //         $site_request_limit_api = new SiteRequestLimitApi();
        //         $site_request_limit_api -> site_id = $site_id;
        //         $site_request_limit_api -> source = $source;
        //         $site_request_limit_api -> count = 1;
        //         $site_request_limit_api -> mode = 'search';
        //         $site_request_limit_api -> save();

        //         $status = true;
        //     }else{
        //         $status = false;
        //     }
        // }
        $status = true;
        return $status;
    }   

    private function is_valid_domain($domain_name){

        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
            && preg_match("/^.{1,253}$/", $domain_name) //overall length check
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)   );
    }

    public function provideCSVFeed_bkk()
    {
        // ตัวอย่างข้อมูลที่ต้องการให้ MISP ดึง
        $feedData = [
            [
                "uuid" => Str::uuid(),
                "type" => "ip-src",
                "value" => "192.168.1.1",
                "category" => "Network activity"
            ],
            [
                "uuid" => Str::uuid(),
                "type" => "domain",
                "value" => "malicious-site.com",
                "category" => "Network activity"
            ],
        ];

        // สร้าง Streamed Response สำหรับ CSV
        $response = new StreamedResponse(function () use ($feedData) {
            $handle = fopen('php://output', 'w');

            // เพิ่ม Header CSV
            fputcsv($handle, ['uuid', 'type', 'value', 'category']);

            // เพิ่มข้อมูลลง CSV
            foreach ($feedData as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        });

        // กำหนด Header ให้เป็น CSV
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="misp-feed.csv"');

        return $response;
    }

    public function provideCSVFeed_bkkl()
    {
        $feedData = [
            ["2024-03-01", "2024-03-21", "192.168.1.1", "443", "US", "AS15169", "Google LLC"],
            ["2024-03-02", "2024-03-20", "185.220.101.45", "80", "DE", "AS24940", "Hetzner Online GmbH"],
            ["2024-03-05", "2024-03-19", "203.0.113.10", "8080", "JP", "AS9605", "NTT Communications Corporation"],
            ["2024-03-07", "2024-03-18", "45.33.32.156", "22", "FR", "AS12876", "Online SAS"],
            ["2024-03-10", "2024-03-17", "198.51.100.23", "3306", "UK", "AS16509", "Amazon.com Inc"],
            ["2024-03-11", "2024-03-16", "162.243.161.79", "8443", "CA", "AS14061", "DigitalOcean LLC"],
            ["2024-03-12", "2024-03-15", "167.99.27.239", "21", "SG", "AS14061", "DigitalOcean LLC"],
            ["2024-03-13", "2024-03-14", "176.31.45.3", "25", "RU", "AS12389", "Rostelecom"],
        ];

        $response = new StreamedResponse(function () use ($feedData) {
            $handle = fopen('php://output', 'w');

            // เพิ่ม Header CSV
            fputcsv($handle, ['first_seen', 'last_seen', 'ip', 'port', 'country', 'as_number', 'as_name']);

            // เพิ่มข้อมูลลง CSV
            foreach ($feedData as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        });

        // กำหนด Header ให้เป็น CSV
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="c2-intel.csv"');

        return $response;
    }
    public function ShopprovideCSVFeed()
    {
        // ตัวอย่างข้อมูล CSV ที่ต้องการแสดงผล
        $feedData = [
            ["ip" => "1.118.34.218", "ioc" => "Possible Cobaltstrike C2 IP"],
            ["ip" => "1.118.35.212", "ioc" => "Possible Cobaltstrike C2 IP"],
            ["ip" => "1.118.35.47", "ioc" => "Possible Cobaltstrike C2 IP"],
            ["ip" => "1.12.233.147", "ioc" => "Possible Cobaltstrike C2 IP"],
            ["ip" => "1.14.123.213", "ioc" => "Possible Cobaltstrike C2 IP"],
            ["ip" => "1.75.34.67", "ioc" => "Possible Cobaltstrike C2 IP"],
            ["ip" => "1.92.100.58", "ioc" => "Possible Cobaltstrike C2 IP"],
            ["ip" => "1.92.91.192", "ioc" => "Possible Cobaltstrike C2 IP"],
            ["ip" => "1.94.117.32", "ioc" => "Possible Cobaltstrike C2 IP"],
            ["ip" => "1.94.126.248", "ioc" => "Possible Cobaltstrike C2 IP"],
        ];

        return view('misp_feed', compact('feedData'));
    }
    public function provideCSVFeed()
    {
        $fileName = 'misp_feed.csv';

        $feedData = [
            ["550e8400-e29b-41d4-a716-446655440000", "ip-src", "192.168.1.1", "Network activity"],
            ["550e8400-e29b-41d4-a716-446655440001", "ip-src", "10.0.0.2", "Network activity"],
            ["550e8400-e29b-41d4-a716-446655440002", "domain", "malicious-site.com", "Network activity"],
            ["550e8400-e29b-41d4-a716-446655440003", "domain", "phishing-attack.net", "Network activity"],
            ["550e8400-e29b-41d4-a716-446655440004", "url", "http://bad-url.com/malware", "Payload delivery"],
            ["550e8400-e29b-41d4-a716-446655440005", "url", "http://dangerous-link.org/phish", "Phishing"],
            ["550e8400-e29b-41d4-a716-446655440006", "ip-src", "203.0.113.10", "Network activity"],
            ["550e8400-e29b-41d4-a716-446655440007", "ip-src", "185.220.101.45", "Network activity"],
            ["550e8400-e29b-41d4-a716-446655440008", "domain", "compromised-server.net", "Network activity"],
            ["550e8400-e29b-41d4-a716-446655440009", "hash", "5d41402abc4b2a76b9719d911017c592", "Malware sample"],
            ["550e8400-e29b-41d4-a716-446655440010", "hash", "ad0234829205b9033196ba818f7a872b", "Malware sample"],
            ["550e8400-e29b-41d4-a716-446655440011", "ip-src", "45.33.32.156", "Network activity"],
            ["550e8400-e29b-41d4-a716-446655440012", "ip-src", "198.51.100.23", "Network activity"],
            ["550e8400-e29b-41d4-a716-446655440013", "domain", "fakebank-login.com", "Credential phishing"],
            ["550e8400-e29b-41d4-a716-446655440014", "domain", "hacker-forum.net", "Network activity"],
            ["550e8400-e29b-41d4-a716-446655440015", "url", "http://darkweb-marketplace.com", "Dark web"],
            ["550e8400-e29b-41d4-a716-446655440016", "ip-src", "185.100.87.174", "Network activity"],
            ["550e8400-e29b-41d4-a716-446655440017", "ip-src", "103.194.169.1", "Network activity"],
            ["550e8400-e29b-41d4-a716-446655440018", "domain", "trojan-downloader.site", "Malware distribution"],
            ["550e8400-e29b-41d4-a716-446655440019", "url", "http://badware.com/ransomware.exe", "Ransomware"],
            ["550e8400-e29b-41d4-a716-446655440020", "hash", "e99a18c428cb38d5f260853678922e03", "Malware sample"],
            ["550e8400-e29b-41d4-a716-446655440021", "ip-src", "51.75.126.21", "Network activity"],
            ["550e8400-e29b-41d4-a716-446655440022", "ip-src", "8.8.8.8", "DNS traffic"],
            ["550e8400-e29b-41d4-a716-446655440023", "domain", "stealer-malware.com", "Information theft"],
            ["550e8400-e29b-41d4-a716-446655440024", "url", "http://infected-download.com/trojan.zip", "Malware distribution"],
            ["550e8400-e29b-41d4-a716-446655440025", "hash", "aab3238922bcc25a6f606eb525ffdc56", "Malware sample"]
        ];

        $response = new StreamedResponse(function () use ($feedData) {
            $handle = fopen('php://output', 'w');

            // เพิ่ม Header CSV
            fputcsv($handle, ['uuid', 'type', 'value', 'category']);

            // เพิ่มข้อมูลลง CSV
            foreach ($feedData as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        });

        // กำหนด Header ให้เป็น CSV
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
  

 

   
    
    public function sslBlacklist(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'inline; filename="sslblacklist.csv"',
        ];
    
        $records = [
            ['2025-04-09 06:31:31','60ece5998a5b54a5ffe75cb4','SSH Brute-Force Honeypot Live','SHA1','2e6f7b26dcf020e658c05ca310a898a1efef2fed','ConnectWise C&C'],
            ['2025-04-09 06:31:31','60ece5998a5b54a5ffe75cb4','SSH Brute-Force Honeypot Live','SHA1','9f9f074c7a082780693069651261a41f5d7ff0cd','AsyncRAT C&C'],
            ['2025-04-09 06:31:31','60ece5998a5b54a5ffe75cb4','SSH Brute-Force Honeypot Live','SHA1','ab181aaf043e7d35b85d86142988ca361a33aeb8','AsyncRAT C&C'],
            ['2025-04-09 06:31:31','60ece5998a5b54a5ffe75cb4','SSH Brute-Force Honeypot Live','SHA1','a975bb92402a83a2c8446082bd4847c2059d0602','LummaStealer C&C'],
            ['2025-04-09 06:31:31','60ece5998a5b54a5ffe75cb4','SSH Brute-Force Honeypot Live','SHA1','7e013496716615db3a73d287d897a052ad3a71f1','OffLoader C&C'],
            ['2025-04-09 06:31:31','60ece5998a5b54a5ffe75cb4','SSH Brute-Force Honeypot Live','SHA1','d6201bd843754f27041f47f6e95708318744c15b','OffLoader C&C'],
            ['2025-04-09 06:31:31','60ece5998a5b54a5ffe75cb4','SSH Brute-Force Honeypot Live','SHA1','e02ddb0e7267f8222cccda5fef72f87420f96e8f','OffLoader C&C'],
            ['2025-04-09 06:31:31','60ece5998a5b54a5ffe75cb4','SSH Brute-Force Honeypot Live','SHA1','70599f2772f70a3b618d9707b816ba280a458517','AsyncRAT C&C'],
            ['2025-04-09 06:31:31','60ece5998a5b54a5ffe75cb4','SSH Brute-Force Honeypot Live','SHA1','db995fa4fb9e53bc304da1ff32dc2314664b912b','Rhadamanthys C&C'],
            ['2025-04-09 06:31:31','60ece5998a5b54a5ffe75cb4','SSH Brute-Force Honeypot Live','SHA1','db0f7247b09b40f3b147c6eca880dd50c5c9e380','LummaStealer C&C'],
            ['2025-04-09 06:31:31','5a64f74f0e543738c12bc973','Webscanners with Bad Requests - HTTP Status 400 - 1/20/2018 thru current day','SHA1','9fb326529bb9dc40ab387999bbc7750595a89e9f','LummaStealer C&C'],
            ['2025-04-09 06:31:31','5a64f74f0e543738c12bc973','Webscanners with Bad Requests - HTTP Status 400 - 1/20/2018 thru current day','SHA1','c8fc1416a286522044cb8736f7f6df7aab6004d9','LummaStealer C&C'],
            ['2025-04-09 06:31:31','5a64f74f0e543738c12bc973','Webscanners with Bad Requests - HTTP Status 400 - 1/20/2018 thru current day','SHA1','8423a49e59d5732f529b45cedf69cad6a7752300','LummaStealer C&C'],
            ['2025-04-09 06:31:31','5a64f74f0e543738c12bc973','Webscanners with Bad Requests - HTTP Status 400 - 1/20/2018 thru current day','SHA1','d02f637b2f790f8b93e2fabf9b13a104fdb39f38','LummaStealer C&C'],
     
        ];
    
        return response()->streamDownload(function () use ($records) {
            echo "################################################################\n";
            echo "# Sosecure SSL Certificate\n";
            echo "# Last updated: " . Carbon::now('UTC')->format('Y-m-d H:i:s') . " UTC\n";
            echo "# Terms Of Use: https://insights.sosecure.co.th/feed/terms\n";
            echo "# Contact: csoc@sosecure.co.th\n";
            echo "################################################################\n";
            echo "\n"; // เว้นบรรทัดตรงนี้เพื่อให้ Excel แสดงหัวตารางได้ถูกต้อง
            echo "Listingdate,event_id,event_name,type,Listingreason\n";
    
            foreach ($records as $row) {
                echo implode(",", $row) . "\n";
            }
        }, 'sslblacklist.csv', $headers);
    }
    
    
  
    public function sha256MalwareList(): StreamedResponse
    {
        $lines = [];
    
        // Header block
        $lines[] = str_repeat('#', 64) . ' MalwareBazaar recent malware samples (SHA256 hashes)         #';
        $lines[] = '# Last updated: ' . now()->format('Y-m-d H:i:s') . ' UTC';
        $lines[] = '#';
        $lines[] = '# Terms Of Use: https://bazaar.abuse.ch/faq/#tos';
        $lines[] = '# For questions please contact bazaar [at] abuse.ch';
        $lines[] = str_repeat('#', 66);
        $lines[] = '#';
        $lines[] = '# sha256_hash';
    
        $hashes = [
            'b1e2b8b0806852aa586a0491fc91a832babfff3882eaed12a6b7d2e7b776d4c4',
            '6ac910e3dfd27cd006972437b090fb3b1e7843e763534db05efe721f1828eca2',
            'a6f6c881665fd25ab6640b1c922d188d8e93ddb13336c5ba51231c8ff4cfde2e',
            'ed9eaead2f8c731f8ab49ee52bab2057ae526c1029316cb2b20a5f01eb0697c6',
            '94723e64a4fe32436b43aef84d14a0cd912cf3b17c881572c2e9d92e9349adc7',
            'd530c63416f12df760514d0e7f0acfbabe74e66b4dc923d6d8ce060d62aa7a03',
            'f8bbdb08e1552909a7d505ee85065b1c02a0eca98d2f111b6e18c935ec2524ec',
            '761766237d7a8d58a5cdf2aa5ccace247b724d033d3196bc441c6a9c31717561',
            '6bc1aeec3046446ad8f32a9956fd4841799abc5efaa6ee43bdb0021813cc4605',
            '6603338f6a709aa136eef198311467a868faff29c644bbc33a62dd1ba8eaf640',
            'e594f00895dd29d763379ee4aa87c6004e811385f71077959674c4ef636f5a2e',
            '2807f5e1177d1c0ca031fe3dde968177008aa592ba78f23fa43d615367e51aac',
            'f8984264632a0aba48bcd90967988aa1d2c10f9381d00abc08456431ea46d208',
            '0cdf00254f1b15e4a8e626995683c60be55ac9231c4fe7c65e4b36356422938b',
            '3bc5f7b8bb948953daf91d46fbc831ce62dd8f122551372ac7d4dd58b28b4688',
            'f27ac5e33f10a62651ae955bd3ce123daa0a94e1658f371b75654faf92eab776',
            'df668ebc65fd0035faed898755e4dd1ee61f76f58b6a49448f6489765e1fbc2a',
            'b4e3200beb7da880299270c487bcb75e72705cb1c10a65a251f8ccd4579326fe',
            'a2f41135a41217c45ae6ddad5db193b5454245d08063df1a0393772271639c1c',
            '91b0b1f842b5380d81ecf3f023a2b8a2a7abb86dc9ef4de58f569752dbe15f52',
            // ... ใส่ทั้งหมดต่อจากนี้ ...
        ];

    
        foreach ($hashes as $hash) {
            $lines[] = $hash;
        }
    
        $csv = implode("\n", $lines);
    
        return response()->stream(function () use ($csv) {
            echo $csv;
        }, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'inline; filename="malware_sha256.csv"',
        ]);
    }
    
    public function threatFoxMd5List(): StreamedResponse
    {
        $lines = [];
    
        // Header block
        $lines[] = str_repeat('#', 64);
        $lines[] = '# ThreatFox IOCs: recent MD5 hashes - CSV format               #';
        $lines[] = '# Last updated: ' . now()->format('Y-m-d H:i:s') . ' UTC';
        $lines[] = '#';
        $lines[] = '# Terms Of Use: https://threatfox.abuse.ch/faq/#tos';
        $lines[] = '# For questions please contact threatfox [at] abuse.ch';
        $lines[] = str_repeat('#', 64);
        $lines[] = '#';
        $lines[] = '# "first_seen_utc","ioc_id","ioc_value","ioc_type","threat_type","fk_malware","malware_alias","malware_printable","last_seen_utc","confidence_level","reference","tags","anonymous","reporter"';
    
        $data = json_decode(file_get_contents(storage_path('hashes/threatfox_md5_list.json')));
        foreach ($data as $row) {
            $lines[] = '"' . implode('","', $row) . '"';
        }
    
        $csv = implode("\n", $lines);
        return response()->stream(function () use ($csv) {
            echo $csv;
        }, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'inline; filename="Md5.csv"',
        ]);
    }

    private function isValidAbuseIPDBResponse($response)
    {
        if ($response === null || $response === '' || $response === '[]') {
            return false;
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded) || isset($decoded['errors']) || !isset($decoded['data'])) {
            return false;
        }

        return true;
    }

    private function fetchAbuseIPDB($ip)
    {
        $keys = IndicatorCheckService::getProviderKeys('ABUSE');
        if (empty($keys)) {
            \Log::warning('AbuseIPDB: no system API keys configured');
            return json_encode([
                'errors' => [[
                    'detail' => 'No AbuseIPDB API key configured. Add an APIv2 key in System API Keys.',
                    'status' => 401,
                ]],
            ]);
        }

        $url = "https://api.abuseipdb.com/api/v2/check?ipAddress=" . urlencode($ip) . "&maxAgeInDays=120";
        $lastResponse = null;

        foreach ($keys as $key) {
            $key = trim((string) $key);
            if ($key === '') {
                continue;
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Key: ' . $key,
                'Accept: application/json',
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $response = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $lastResponse = $response;
            if ($this->isValidAbuseIPDBResponse($response)) {
                return $response;
            }

            if ($httpCode === 429) {
                \Log::warning("AbuseIPDB rate limited for IP {$ip}");
                return $response;
            }
        }

        \Log::warning("AbuseIPDB: all configured API keys failed for IP {$ip}");
        return $lastResponse ?: json_encode([
            'errors' => [[
                'detail' => 'Authentication failed. Your API key is either missing, incorrect, or revoked. Note: The APIv2 key differs from the APIv1 key.',
                'status' => 401,
            ]],
        ]);
    }

    private function fetchThreatFox($keyword)
    {
        $postData = json_encode([
            'query' => 'search_ioc',
            'search_term' => $keyword
        ]);

        // Try without Auth-Key first (anonymous — avoids key-level blacklist)
        $url = "https://threatfox-api.abuse.ch/api/v1/";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        curl_close($ch);

        $decoded = json_decode($response, true);
        if (isset($decoded['query_status']) && $decoded['query_status'] !== 'user_blacklisted') {
            return $response;
        }

        // Fallback: try with Auth-Key
        \Log::info('[ThreatFox Debug] Anonymous request blacklisted, trying with Auth-Key');
        $key = IndicatorCheckService::getRandomKey('TF');
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Auth-Key: ' . $key,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    private function fetchRSTCloud($keyword)
    {
        $key = IndicatorCheckService::getRandomKey('RST');
        
        $url = "https://api.rstcloud.net/v1/ioc?value=" . urlencode($keyword);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'x-api-key: ' . $key,
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}    