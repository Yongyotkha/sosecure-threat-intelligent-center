<?php

namespace App\Http\Controllers;

use App\DataLeakFeed;
use App\LogSearch;
use App\R_s_s_news;
use App\SiteLimitApi;
use App\SiteRequestLimitApi;
use App\SystemLimitApi;
use App\Traits\Taggable;
use DB;
use Illuminate\Http\Request;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
use Modules\MonitoringVulnerabilitys\Entities\CVEMappingAssets;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use MongoDB\Client as MongoClient;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Validation\Validator;
use Modules\SiteSettings\Entities\SiteSettings;

class SearchController extends Controller
{
    use Taggable;
    protected $request;
    protected $search;
    protected $page;

    public function __construct(Request $request)
    {
        $this->middleware('auth');
        $this->request = $request;
    }

    public function search(Request $mode)
    {
        // dd(json_encode($this->request->keyword));
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
                if(@get_role_custom()['superadmin'] == 1) {
                    $DataLeakFeed_social = DataLeakFeed::select('data_leak_feed.id', 'data_leak_feed.feedcontent as content', 'data_leak_feed.sourceid', 'data_leak_feed.keyword as name', DB::raw('CONCAT("/socialdatas") AS link'))->whereNull('data_leak_feed.deleted_at')->whereNull('data_leak_socail_ref.deleted_at')->whereIn('data_leak_feed.feel_type',['social', 'darkweb_public'])->where(function ($query) use ($keyword) {
                        $query->where('data_leak_feed.keyword', 'LIKE', $keyword)
                        ->orWhere('data_leak_feed.source_name', 'LIKE', $keyword) ->orWhere('data_leak_feed.feedcontent', 'LIKE', $keyword);
                    });
                    $DataLeakFeed_social = $DataLeakFeed_social->leftjoin('data_leak_socail_ref', 'data_leak_feed.id', '=', 'data_leak_socail_ref.data_leak_feed_id')->leftjoin('site', 'data_leak_socail_ref.site_id', '=', 'site.id');
                    $dataWait["queryData"] = $DataLeakFeed_social;

                    $dataWait["count"] = $dataWait["queryData"]->count();
                    if ($dataWait['count'] > 0) {
                        $dataWait["queryData"] = $dataWait["queryData"]->orderBy('data_leak_feed.updated_at', 'desc')->get()->toArray();
                        $data['dataSearch']["Data Leak"] = $dataWait;
                    }
                } else {
                    $DataLeakFeed_social = DataLeakFeed::select('data_leak_feed.id', 'data_leak_feed.feedcontent as content', 'data_leak_feed.sourceid', 'data_leak_feed.keyword as name', DB::raw('CONCAT("/socialdatas") AS link'))->whereNull('data_leak_feed.deleted_at')->whereNull('data_leak_socail_ref.deleted_at')->whereIn('data_leak_feed.feel_type',['social', 'darkweb_public'])->where(function ($query) use ($keyword) {
                        $query->where('data_leak_feed.keyword', 'LIKE', $keyword)
                        ->orWhere('data_leak_feed.source_name', 'LIKE', $keyword) ->orWhere('data_leak_feed.feedcontent', 'LIKE', $keyword);
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
                                'name'  => ['$regex'=>$this->request->keyword, '$options' => 'i'],
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
                                'link' => [ '$concat' => ['/indicators/malware/?id=','$adversary_uuid','&name=','$name']],
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
        $role_custom = @check_role_custom();
        $source = $request->source;
        $keyword = $request->keyword;
        $site_code = $request->code;
        $site_id = 0;
        $site = null;
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
        }
    

       //format
       $type = $this->check_keyword_type($keyword);
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
               $center_search_api_loookup_limit = env('center_search_api_loookup_limit', 1000);
               $site_request_limit_api_query = SiteRequestLimitApi::where('mode', 'api_limit')->where('site_id',0)->first();
               $site_request_limit_api_count =$site_request_limit_api_query->count;
        }
        if($log_search> 0){
            $center_search_api_loookup_allow = 1;

        }else{
                if((int)$center_search_api_loookup_limit > $site_request_limit_api_count){
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

                
                }

        }

  
        if($source =="otx_puls_tag"){
            $type="tags";
        }

   
        $response = '{}';
        $response2 = "{}";
        if($source =="ibmcloud"){
            $log_search = LogSearch::select('path')->where('keyword', $keyword)->where('source', $source)->first();
            if($log_search){
                $url = storage_path() .'/app/public/'.$log_search -> path;
                if(!File::exists($url)){
                    $response = null;
                }else{
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

                $ibmcloud_API_Key = "d4b45ba9-4a1f-4127-bb72-1a01ab26a4b9";
                $ibmcloud_API_Key_Password = "95d8e0cd-0f34-45dc-9c6c-aa490fcb0415";
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
            $log_search = LogSearch::select('path')->where('keyword', $keyword)->where('source', $source)->first();
            if($log_search){
                $url = storage_path() .'/app/public/'.$log_search -> path;
                if(!File::exists($url)){
                    $response = null;
                }else{
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
                $virustotal_API_Key = "8ed71053d254aa99c9a79b73c6f3223cac762c2c77628d075e62ec506a538267";
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
            $log_search = LogSearch::select('path')->where('keyword', $keyword)->where('source', $source)->first();
            if($log_search){
                $url = storage_path() .'/app/public/'.$log_search -> path;
                if(!File::exists($url)){
                    $response = null;
                }else{
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

                $hybrid_API_Key = "kpy0ibau846587b1lnemkw4k082be03bncw1bkz140a16b6cs64sk6uzf0498e3f";

                //$virustotal_url='https://www.virustotal.com/api/v3/domains/xlus0222uj81bxyf.xyz';
                $headers = array(
                    'api-key: '.$hybrid_API_Key,
                    'accept: '.'application/json',
                    'Content-Type: '.'application/x-www-form-urlencoded',
                    'user-agent: '.'Falcon Sandbox',
                );
                //'host'=>'151.101.2.110','domain'=>'151.101.2.110','url'=>'151.101.2.110','url'=>'151.101.2.110','similar_to'=>'151.101.2.110','context'=>'151.101.2.110'
                
                if($type == 'IP'){
                    $hybrid_url = "https://www.hybrid-analysis.com/api/v2/search/terms";
                    $fields = array('host'=>$keyword);
                    $postvars = '';
                    foreach($fields as $key=>$value) {
                        $postvars .= $key . "=" . $value . "&";
                    }
                }else if($type == 'Domain'){
                    $hybrid_url = "https://www.hybrid-analysis.com/api/v2/search/terms";
                    $fields = array('domain'=>$keyword);
                    $postvars = '';
                    foreach($fields as $key=>$value) {
                        $postvars .= $key . "=" . $value . "&";
                    }
                }else if($type == 'URL'){
                    $hybrid_url = "https://www.hybrid-analysis.com/api/v2/search/terms";
                    $fields = array('domain'=>$keyword);
                    $postvars = '';
                    foreach($fields as $key=>$value) {
                        $postvars .= $key . "=" . $value . "&";
                    }
                }else if($type == 'SHA256' || $type == 'MD5' || $type == 'SHA1'){
                    $hybrid_url = "https://www.hybrid-analysis.com/api/v2/search/hash";
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


            $otx_API_Key = "c69611682f6e13bfe36a9b3740dac840ce279d6d52b1b8c7c78eb097bee53688";
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
                    // Get response
                    $response2 = curl_exec($ch);
                    curl_close($ch);  
             }
  



        }else if($source =="otx_puls"){
        
            $otx_API_Key = "c69611682f6e13bfe36a9b3740dac840ce279d6d52b1b8c7c78eb097bee53688";  
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
            // Get response
            $response = curl_exec($ch);
            curl_close($ch);  

        
        }else if($source =="otx_puls_indicator"){
        
            $otx_API_Key = "c69611682f6e13bfe36a9b3740dac840ce279d6d52b1b8c7c78eb097bee53688";  
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
            // Get response
            $response = curl_exec($ch);
            curl_close($ch);  

        
        }else if($source =="otx_puls_tag"){
            $keyword =str_replace(' ', '%20', trim($keyword));

            $otx_API_Key = "c69611682f6e13bfe36a9b3740dac840ce279d6d52b1b8c7c78eb097bee53688";  
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
            // Get response
            $response = curl_exec($ch);
            curl_close($ch);  

        
        }else if($source =="check_api_search_limit"){
            // if($site_code){
            //     $center_search_api_loookup_limit =$site->search_api_loookup_limit;
            //     $site_request_limit_api_count = SiteRequestLimitApi::orderBy('id', 'desc')->select('count')->where('mode', 'search')->where('site_id',$site_id)->first();
    
            // }else{
            //     $center_search_api_loookup_limit = env('center_search_api_loookup_limit', 1000);
            //     $site_request_limit_api_count = SiteRequestLimitApi::orderBy('id', 'desc')->select('count')->where('mode', 'search')->where('site_id',0)->first();
            // }


        }
   

    
        $response_data = array(
            'status_code' => Response::HTTP_OK,
            'message' => '',
            'data' => json_decode($response, true),
            'data2' => json_decode($response2, true),
            'type' => $type,
            'source' => $source,
            'site_code' =>$site_code,
            'center_search_api_loookup_limit' =>$center_search_api_loookup_limit,
            'site_request_limit_api_count' =>$site_request_limit_api_count,
            'search_api_loookup_allow' =>$center_search_api_loookup_allow
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

    private function is_valid_domain($url){

        $validation = FALSE;
        /*Parse URL*/    $urlparts = parse_url(filter_var($url, FILTER_SANITIZE_URL));
        /*Check host exist else path assign to host*/    if(!isset($urlparts['host'])){
            $urlparts['host'] = $urlparts['path'];
        }
    
        if($urlparts['host']!=''){
           /*Add scheme if not found*/        if (!isset($urlparts['scheme'])){
                $urlparts['scheme'] = 'http';
            }
            /*Validation*/        if(checkdnsrr($urlparts['host'], 'A') && in_array($urlparts['scheme'],array('http','https')) && ip2long($urlparts['host']) === FALSE){ 
                $urlparts['host'] = preg_replace('/^www\./', '', $urlparts['host']);
                $url = $urlparts['scheme'].'://'.$urlparts['host']. "/";            
                
                if (filter_var($url, FILTER_VALIDATE_URL) !== false && @get_headers($url)) {
                    $validation = TRUE;
                }
            }
        }
        if(!$validation){
           return false;
        }else{
            return true;
        }
    }
}
