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
use App\Entities\TransactionBatchjob;

use App\FXTechniques;
use App\Entities\OtxIndicatiorData;
use Illuminate\Http\Response;
use Modules\indicators\Entities\OTXtypeData;
use MongoDB\Client;

class ApiIndicatorController extends ApiController
{
    public function events_table(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                $draw = $data['data']['draw'];
                $row = (int)$data['data']['start'];
                $rowperpage = (int)$data['data']['length'];

                $orderRaw = isset($data['data']['order']) ? $data['data']['order'] : null;
                $dirRaw = isset($data['data']['dir']) ? $data['data']['dir'] : null;
                $url = isset($data['data']['url']) ? $data['data']['url'] : null;
                $industries = isset($data['data']['industries']) ? $data['data']['industries'] : null;
                $groups = isset($data['data']['groups']) ? $data['data']['groups'] : null;
                $keywords = isset($data['data']['keywords']) ? $data['data']['keywords'] : null;
                $keyword_search = isset($data['data']['keyword_search']) ? $data['data']['keyword_search'] : null;

                $start =  $row;

                $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                $clientMD = new MongoClient($DB_MONGO_KEY);
                $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;

                $dirStr = strtolower($dirRaw ?? 'desc');
                $dir    = ($dirStr === 'asc' || $dirStr === '1') ? 1 : -1;

                $order = $orderRaw;
                $sortable = ['modified', 'name', 'creator_org', 'groups', 'tags', 'attrCount', 'pulse_id'];

                if (!$order || !in_array($order, $sortable, true)) {
                    $order = 'modified';
                    $dir   = -1;
                }

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
                        'indicator_count' => 1,
                        'pulse_id' => 1,
                        'creator_org' => 1,
                    ],
                    'sort'  => [$order => $dir, '_id' => -1],
                    'skip'  => $start,
                    'limit' => $rowperpage,
                ];

                $query = [
                    '$or' => [
                        ['deleted_at' => null],
                        ['deleted_at' => ['$exists' => false]],
                    ],
                ];

                if (isset($data['data']['count_page']) && $data['data']['count_page'] == -1) {
                    $cursor_count = $col_fx_otx_events->count($query);
                    $count_filter = $cursor_count;
                } else {
                    $cursor_count = isset($data['data']['count_page']) ? $data['data']['count_page'] : 0;
                    $count_filter = $cursor_count;
                }


                if ($keywords || isset($data['data']['isDateSearch']) || isset($data['data']['start_date']) || isset($data['data']['end_date']) || isset($data['data']['check_published']) || $industries || $groups || $keyword_search) {

                    if ($keywords) {
                        $query['name'] = ['$regex' => $keywords, '$options' => 'i'];
                    }
                    if ($industries) {
                        $query['industries'] = ['$regex' => $industries, '$options' => 'i'];
                    }
                    if ($groups) {
                        $query['groups'] = ['$regex' => $groups, '$options' => 'i'];
                    }
                    if ($keyword_search) {
                        $query['$or'] = [
                            ['name' => ['$regex' => $keyword_search, '$options' => 'i']],
                            ['groups' => ['$regex' => $keyword_search, '$options' => 'i']],
                            ['source' => ['$regex' => $keyword_search, '$options' => 'i']],
                            ['creator_org' => ['$regex' => $keyword_search, '$options' => 'i']],
                            ['tags' => ['$regex' => $keyword_search, '$options' => 'i']],
                        ];
                    }

                    $isDateSearch = filter_var($data['data']['isDateSearch'] ?? false, FILTER_VALIDATE_BOOLEAN);

                    if ($isDateSearch) {
                        if (isset($data['data']['startDate']) && isset($data['data']['endDate']) && $data['data']['startDate'] && $data['data']['endDate']) {
                            $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($data['data']['startDate']) * 1000), '$lte' => new UTCDateTime(strtotime($data['data']['endDate']) * 1000)];
                        } else if (isset($data['data']['startDate']) && $data['data']['startDate']) {
                            $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($data['data']['startDate']) * 1000)];
                        } else if (isset($data['data']['endDate']) && $data['data']['endDate']) {
                            $query['modified'] = ['$lte' => new UTCDateTime(strtotime($data['data']['endDate']) * 1000)];
                        }
                    }

                    if (isset($data['data']['check_published']) && $data['data']['check_published']) {
                        if ($data['data']['check_published'] == 1) {
                            $query['public'] = ['$in' => [1, "1"]];
                        } else if ($data['data']['check_published'] == 2) {
                            $query['public'] = ['$in' => [0, "0"]];
                        }
                    }
                    
                    $cursor = $col_fx_otx_events->find($query, $options);
                    $count_filter = $col_fx_otx_events->count($query);
                } else {
                    $cursor = $col_fx_otx_events->find($query, $options);
                }

                $cursor = $cursor->toArray();

                $data_nestedData = array();
                $order_number = $start;
                if (!empty($cursor)) {
                    foreach ($cursor as $document_2) {
                        $order_number++;
                        $nestedData['No'] = $order_number;
                        $nestedData['name'] = $document_2["name"] ?? '';
                        $nestedData['groups'] = isset($document_2["groups"]) ? $this->explode_val($document_2["groups"], 'groups', $url) : '';
                        $nestedData['tags'] = isset($document_2["tags"]) ? $this->explode_val($document_2["tags"], 'tags', $url) : '';
                        $nestedData['tags_list'] = $document_2["tags"] ?? '';
                        $nestedData['creator_org'] = $document_2["creator_org"] ?? null;
                        $nestedData['industries'] = isset($document_2["industries"]) ? $this->explode_val($document_2["industries"], 'industries', $url) : '';
                        $nestedData['attr'] = '';
                        
                        if (isset($data['data']['startDate']) && isset($data['data']['endDate']) && $data['data']['startDate'] && $data['data']['endDate']) {
                            try {
                                $indicatorRefCol = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;
                                $attrQuery = [
                                    'pulse_id' => $document_2['pulse_id'],
                                    'updated_at' => [
                                        '$gte' => new UTCDateTime(strtotime($data['data']['startDate']) * 1000),
                                        '$lte' => new UTCDateTime(strtotime($data['data']['endDate']) * 1000)
                                    ]
                                ];
                                $nestedData['attrCount'] = $indicatorRefCol->countDocuments($attrQuery, ['maxTimeMS' => 3000]);
                            } catch (\Exception $e) {
                                $nestedData['attrCount'] = $document_2["indicator_count"] ?? 0;
                            }
                        } else {
                            $nestedData['attrCount'] = $document_2["indicator_count"] ?? 0;
                        }

                        $nestedData['public'] = ($document_2["public"] ?? 0);
                        $nestedData['is_modified'] = ($document_2["is_modified"] ?? 0);
                        
                        if (function_exists('change_date_thai_tummai') && isset($document_2['modified'])) {
                            $nestedData['modified'] = change_date_thai_tummai($document_2['modified']);
                        } else if (function_exists('change_date_utc_to_thai') && isset($document_2['modified'])) {
                            $nestedData['modified'] = change_date_utc_to_thai($document_2['modified']);
                        } else {
                            $nestedData['modified'] = '';
                        }
                        
                        $nestedData['count_view'] = @$document_2["count_view"] ?? 0;
                        $nestedData['pulse_id'] = $document_2["pulse_id"] ?? '';
                        $nestedData['creator_org'] = isset($document_2["creator_org"]) ? $document_2["creator_org"] : null;

                        //------------------------------------------------------
                        $DB_MONGO_KEY = env("DB_MONGO_DEV");
                        $clientMD2 = new \MongoDB\Client($DB_MONGO_KEY);
                        if (app()->environment('local')) {
                            $collection = $clientMD2->sosecure_threatintelligent->fx_otx_adversaries;
                            $collection_related = $clientMD2->sosecure_threatintelligent->fx_otx_adversaries_related;
                        } else {
                            $collection = $clientMD2->sosecure_threatintelligent_test->fx_otx_adversaries;
                            $collection_related = $clientMD2->sosecure_threatintelligent_test->fx_otx_adversaries_related;
                        }

                        $query_actor = [
                            'pulse_id' => $document_2['pulse_id'] ?? '',
                            'mode' => 'indicator',
                            'join' => 'actor',
                            'delete_at' => null
                        ];
                        $option_actor = [];

                        $result_actor = $collection_related->find($query_actor, $option_actor);
                        $final_actor = $result_actor->toArray();
                        $count_actor = count($final_actor);

                        $nestedData['actor'] = $final_actor;
                        $nestedData['count_actor'] = $count_actor;
                        $nestedData['logo'] = [];

                        foreach (@$final_actor as $sel_data_act) {
                            $query_sel_act = [
                                'adversary_uuid' => $sel_data_act['adversary_uuid']
                            ];
                            $option_sel_act = [];
                            $result_sel_act = $collection->findOne($query_sel_act, $option_sel_act);
                            if (@$result_sel_act['logo']) {
                                $nestedData['logo'][] = $result_sel_act['logo'];
                            } else {
                                $nestedData['logo'][] = '/asset_salepage/images/AgentBasedDetection.png';
                            }
                        }

                        $query_camp = [
                            'pulse_id' => $document_2['pulse_id'] ?? '',
                            'mode' => 'indicator',
                            'join' => 'campainge',
                            'delete_at' => null
                        ];
                        $option_camp = [];

                        $result_camp = $collection_related->find($query_camp, $option_camp);
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
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            if ($header) {
                $data = $this->dataFalse($header, $mode, $data_request);
                if(isset($data['site']['data']['id'])) {
                    $this->saveLog($data['site']['data']['id'], json_encode($response));
                }
            }

            return response()->json($response);
        }
    }

    public function events_table_test(Request $request)
    {
        try {
            $header = $request->bearerToken();
            // $mode = $request->mode;
            // $data_request = $request -> data;
            // $data = $this -> dataFalse($header, $mode, $data_request);
            if ($header !== 'VzR6S25ldm5kdmtlMEdTYS9nUmhZTDlHNzNKOEFNTkN2WW5UTzFQd2lxcE92WjN3VTIwd3FFZVMrR0VDazY4c1ZSSGJoUUVRbFBGNXg2SHZYNnQwNkQ0TUtHY0VSSVpMYUs4RTFjZnhiTEE9') {
                return response()->json(['error' => ' Authentication failed', 'status_code' => '401']);
            } else {
                $draw = $request->draw;
                $row = (int)$request->start;
                $rowperpage = (int)$request->length;

                $order = $request->order;
                $dir = $request->dir;
                $url = $request->url;
                $industries = $request->industries;
                $groups = $request->groups;

                $start =  $row;

                $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                $clientMD = new MongoClient($DB_MONGO_KEY);
                $col_fx_otx_events = $clientMD->sosecure_threatintelligent->fx_otx_events;


                $options = [
                    'projection' => [
                        '_id' => 0,
                        'industries' => 1,
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

                if ($request->count_page == -1) {
                    $cursor_count = $col_fx_otx_events->count($query);
                    $count_filter = $cursor_count;
                } else {
                    $cursor_count = $request->count_page;
                    $count_filter = $cursor_count;
                }


                if ($request->keywords || $request->isDateSearch || $request->start_date || $request->end_date || $request->check_published || $industries || $groups) {

                    if ($request->keywords) {
                        $query['name'] = ['$regex' => $request->keywords, '$options' => 'i'];
                        // $_search =  array_merge($_search, array('indicator' => ['$regex'=>$request->keywords, '$options' => 'i']));
                    }
                    if ($industries) {
                        $query['industries'] = ['$regex' => $industries, '$options' => 'i'];
                    }
                    if ($groups) {
                        $query['groups'] = ['$regex' => $groups, '$options' => 'i'];
                    }

                    $isDateSearch = filter_var($request->isDateSearch, FILTER_VALIDATE_BOOLEAN);

                    if ($isDateSearch) {
                        if ($request->startDate && $request->endDate) {
                            $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($request->startDate) * 1000), '$lte' => new UTCDateTime(strtotime($request->endDate) * 1000)];
                            // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                        } else if ($request->startDate) {
                            $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($request->startDate) * 1000)];
                            // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000)] ) );
                        } else if ($request->endDate) {
                            $query['modified'] = ['$lte' => new UTCDateTime(strtotime($request->endDate) * 1000)];
                            // $_search =  array_merge( $_search, array('updated_at' => ['$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                        }
                    }

                    if ($request->check_published) {
                        if ($request->check_published == 1) {
                            $query['public'] = 1;
                        } else if ($request->check_published == 2) {
                            $query['public'] = 0;
                        }
                    }
                    $cursor = $col_fx_otx_events->find($query, $options);
                    $count_filter = $col_fx_otx_events->count($query);
                } else {
                    $cursor = $col_fx_otx_events->find($query, $options);
                }

                $query['indicator_count'] = ['$ne' => 0];
                $cursor = $col_fx_otx_events->find($query, $options);
                $cursor = $cursor->toArray();

                $data_nestedData = array();
                $order_number = $start;
                if (!empty($cursor)) {
                    foreach ($cursor as $document) {
                        $order_number++;
                        $nestedData['No'] = $order_number;
                        $nestedData['name'] = $document["name"];
                        $nestedData['groups'] = $document["groups"] ? $this->explode_val($document["groups"], 'groups', $url) : '';
                        $nestedData['tags'] = $this->explode_val($document["tags"], 'tags', $url);
                        $nestedData['industries'] = $document["industries"] ? $this->explode_val($document["industries"], 'industries', $url) : '';
                        $nestedData['attr'] = '';
                        $nestedData['attrCount'] = $document["indicator_count"];
                        $nestedData['public'] = ($document["public"]);
                        $nestedData['is_modified'] = ($document["is_modified"]);
                        // $nestedData['modified'] = change_date_utc_to_thai($document['modified']);
                        $nestedData['modified'] = change_date_thai_tummai($document['modified']);
                        $nestedData['count_view'] = @$document["count_view"];
                        $nestedData['pulse_id'] = $document["pulse_id"];

                        // <a href="'.rou   te('indicators.events_detail_select',['id' => $document['pulse_id']]).'" 
                        // class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>

                        //------------------------------------------------------
                        $DB_MONGO_KEY = env("DB_MONGO_DEV");
                        $clientMD = new \MongoDB\Client($DB_MONGO_KEY);
                        if (app()->environment('local')) {
                            $collection = $clientMD->sosecure_threatintelligent->fx_otx_adversaries;
                            $collection_related = $clientMD->sosecure_threatintelligent->fx_otx_adversaries_related;
                        } else {
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

                        $result_actor = $collection_related->find($query_actor, $option_actor);
                        $final_actor = $result_actor->toArray();
                        $count_actor = count($final_actor);

                        $nestedData['actor'] = $final_actor;
                        $nestedData['count_actor'] = $count_actor;

                        foreach (@$final_actor as $sel_data_act) {
                            $query_sel_act = [
                                'adversary_uuid' => $sel_data_act['adversary_uuid']
                            ];
                            $option_sel_act = [];
                            $result_sel_act = $collection->findOne($query_sel_act, $option_sel_act);
                            if (@$result_sel_act['logo']) {
                                $nestedData['logo'][] = $result_sel_act['logo'];
                            } else {
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

                        $result_camp = $collection_related->find($query_camp, $option_camp);
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

                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $dataOut]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            return response()->json($response);
        }
    }

    public function table_groups(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'indicators') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
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
                        'groups' => new Regex('^.*' . $tags . '.*$', 'i'),
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



                    if ($count_page == -1) {
                        $cursor_count = $col_fx_otx_events->count($query);
                        $count_filter = $cursor_count;
                    } else {
                        $cursor_count = $count_page;
                        $count_filter = $cursor_count;
                    }

                    if ($keywords || $isDateSearch || $industries) {


                        if ($keywords) {
                            $query['name'] = ['$regex' => $keywords, '$options' => 'i'];
                            // $_search =  array_merge($_search, array('indicator' => ['$regex'=>$keywords, '$options' => 'i']));
                        }
                        if ($industries) {
                            $query['industries'] = ['$regex' => $industries, '$options' => 'i'];
                        }

                        $isDateSearch = filter_var($isDateSearch, FILTER_VALIDATE_BOOLEAN);

                        if ($isDateSearch) {
                            // dd($startDate);
                            if ($startDate && $endDate) {

                                $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($startDate) * 1000), '$lte' => new UTCDateTime(strtotime($endDate) * 1000)];
                                // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                            } else if ($startDate) {
                                $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($startDate) * 1000)];
                                // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000)] ) );
                            } else if ($endDate) {
                                $query['modified'] = ['$lte' => new UTCDateTime(strtotime($endDate) * 1000)];
                                // $_search =  array_merge( $_search, array('updated_at' => ['$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                            }
                        }
                        $cursor = $col_fx_otx_events->find($query, $options);
                        $count_filter = $col_fx_otx_events->count($query);
                    } else {
                        $cursor = $col_fx_otx_events->find($query, $options);
                    }



                    $cursor = $cursor->toArray();

                    $data_res = array();
                    $order_number = $start;
                    if (!empty($cursor)) {
                        foreach ($cursor as $document) {


                            $order_number++;
                            $nestedData['No'] = $order_number;
                            $nestedData['name'] = $document["name"];
                            $nestedData['groups'] = $this->explode_val($document["groups"], 'groups', $url);
                            $nestedData['tags'] = $this->explode_val($document["tags"], 'tags', $url);
                            $nestedData['industries'] = $this->explode_val($document["industries"], null, $url);
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
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function table_tags(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'indicators') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
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
                        'tags' => new Regex('^.*' . $tags . '.*$', 'i'),
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



                    if ($count_page == -1) {
                        $cursor_count = $col_fx_otx_events->count($query);
                        $count_filter = $cursor_count;
                    } else {
                        $cursor_count = $count_page;
                        $count_filter = $cursor_count;
                    }

                    if ($keywords || $isDateSearch || $industries) {


                        if ($keywords) {
                            $query['name'] = ['$regex' => $keywords, '$options' => 'i'];
                            // $_search =  array_merge($_search, array('indicator' => ['$regex'=>$keywords, '$options' => 'i']));
                        }
                        if ($industries) {
                            $query['industries'] = ['$regex' => $industries, '$options' => 'i'];
                        }
                        $isDateSearch = filter_var($isDateSearch, FILTER_VALIDATE_BOOLEAN);

                        if ($isDateSearch) {
                            // dd($startDate);
                            if ($startDate && $endDate) {

                                $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($startDate) * 1000), '$lte' => new UTCDateTime(strtotime($endDate) * 1000)];
                                // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000), '$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                            } else if ($startDate) {
                                $query['modified'] = ['$gt' =>  new UTCDateTime(strtotime($startDate) * 1000)];
                                // $_search =  array_merge( $_search, array('updated_at' => ['$gt' =>  new UTCDateTime(strtotime($date_start_datetime_format)*1000)] ) );
                            } else if ($endDate) {
                                $query['modified'] = ['$lte' => new UTCDateTime(strtotime($endDate) * 1000)];
                                // $_search =  array_merge( $_search, array('updated_at' => ['$lte' => new UTCDateTime(strtotime($date_end_datetime_format)*1000)] ) );
                            }
                        }
                        $cursor = $col_fx_otx_events->find($query, $options);
                        $count_filter = $col_fx_otx_events->count($query);
                    } else {
                        $cursor = $col_fx_otx_events->find($query, $options);
                    }



                    $cursor = $cursor->toArray();

                    $data_res = array();
                    $order_number = $start;
                    if (!empty($cursor)) {
                        foreach ($cursor as $document) {


                            $order_number++;
                            $nestedData['No'] = $order_number;
                            $nestedData['name'] = $document["name"];
                            $nestedData['groups'] = $this->explode_val($document["groups"], 'groups', $url);
                            $nestedData['tags'] = $this->explode_val($document["tags"], 'tags', $url);
                            $nestedData['industries'] = $this->explode_val($document["industries"], null, $url);
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
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function industries(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'indicators') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
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
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function group(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'indicators') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
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
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function adversaries(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'indicators') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
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
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function malware(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'indicators') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
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
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function show_detail_malware(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'indicators') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
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
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }



    public function show_detail_adversary(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'indicators') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
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
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }


    public function load_relatedPulse(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
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

                if ($data['data']['count_page'] == -1) {
                    $cursor_count = $col_fx_otx_events_indicator_ref->count($query);
                    $count_filter = $cursor_count;
                    // dd($cursor_count);
                } else {
                    $cursor_count = $data['data']['count_page'];
                    $count_filter = $cursor_count;
                }
                $cursor = $col_fx_otx_events_indicator_ref->find($query, $options);
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

                if ($document_all) {
                    foreach ($document_all as  $value) {
                        $query = [
                            'pulse_id' => $value->pulse_id

                        ];
                        $cursor_2 = $col_fx_otx_events->findOne($query, $options);

                        //  dd($value);
                        $order_number++;
                        $nestedData['No'] = $order_number;
                        $nestedData['name'] = $cursor_2["name"];
                        $nestedData['groups'] = $this->explode_val($cursor_2["groups"], 'groups', $data['data']['url']);
                        $nestedData['tags'] = $this->explode_val($cursor_2["tags"], 'tags', $data['data']['url']);
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
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function events(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                $get_role_custom_first = @get_role_custom();
                $SiteSettings = '';
                $SiteSettings = @$get_role_custom_first['SiteSettings'];
                $site_id_arr = @$get_role_custom_first['site_id_arr'];
                if (@$get_role_custom_first['superadmin'] == 1) {
                    $SiteSettings = @$get_role_custom_first['SiteSettings'];
                } else if (@$get_role_custom_first['client'] == 1) {
                    $SiteSettings = @$get_role_custom_first['SiteSettings'];
                } else if (@$get_role_custom_first['site_support'] == 1) {
                    $SiteSettings = @$get_role_custom_first['SiteSettings'];
                } else if (@$get_role_custom_first['site_admin'] == 1) {
                    $SiteSettings = @$get_role_custom_first['SiteSettings'];
                } else if (@$get_role_custom_first['site_client'] == 1) {
                    $SiteSettings = @$get_role_custom_first['SiteSettings'];
                }

                $data_send["attr_all"] = IndicatorSummaryYear::where("type", 'summary_all')->first();
                $data_send["attr_current"] = IndicatorSummaryYear::where("type", 'summary_current')->first();
                // DB::raw('CONCAT("[",attribute_count, "]") as data2')
                $dataForloop = IndicatorSummaryYear::select('type_name AS name', 'attribute_count AS data')->where("type", 'summary_attr_type')->orderBy('attribute_count', 'desc')->take(10)->get();
                $data_send["attr_type"] = array();
                foreach ($dataForloop as $document) {
                    array_push($data_send["attr_type"], array('name' => ucwords($document->name), 'data' => [$document->data]));
                }
                $data_send['SiteSettings'] = $SiteSettings;
                $data_send['page'] = langapp('indicators');
                if (isset($data_send['data']['Search_Link_All'])) {
                    $data_send['Search_Link_All'] = $data['data']['Search_Link_All'];
                } else {
                    $data_send['Search_Link_All'] = "";
                }

                $data_transcation = json_encode($data_send);
                $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function events_detail_select(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
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

                // --- Process indicator_type_counts (เหมือน Center) ---
                if (!empty($cursor)) {
                    // แปลง cursor เป็น array ธรรมดาเพื่อให้แก้ไขค่าได้
                    $cursorData = json_decode(json_encode($cursor), true);

                    $typeCounts = $cursorData[0]['indicator_type_counts'] ?? null;
                    $hasValidKeys = false;

                    if (!empty($typeCounts) && is_array($typeCounts)) {
                        $firstKey = array_key_first($typeCounts);
                        $hasValidKeys = !is_numeric($firstKey);
                    }

                    // รับค่า date filter จาก request (ถ้ามี)
                    $startDate = $data['data']['startDate'] ?? null;
                    $endDate = $data['data']['endDate'] ?? null;
                    $hasDateFilter = $startDate && $endDate;

                    // Fallback: Aggregate จาก fx_otx_events_indicator_ref ถ้า key เป็นตัวเลข หรือมี date filter
                    if (!$hasValidKeys || $hasDateFilter) {
                        $indicatorRefCol = $client->sosecure_threatintelligent->fx_otx_events_indicator_ref;

                        $matchQuery = ['pulse_id' => $id, 'status' => 1];

                        if ($hasDateFilter) {
                            $matchQuery['updated_at'] = [
                                '$gte' => new \MongoDB\BSON\UTCDateTime(strtotime($startDate) * 1000),
                                '$lte' => new \MongoDB\BSON\UTCDateTime(strtotime($endDate) * 1000)
                            ];
                        }

                        $pipeline = [
                            ['$match' => $matchQuery],
                            ['$group' => ['_id' => '$type', 'count' => ['$sum' => 1]]],
                            ['$sort' => ['count' => -1]]
                        ];
                        $aggregateResult = $indicatorRefCol->aggregate($pipeline)->toArray();

                        // สร้าง indicator_type_counts ใหม่เป็น associative array (type name => count)
                        $processedTypeCounts = [];
                        foreach ($aggregateResult as $item) {
                            if (!empty($item['_id'])) {
                                $processedTypeCounts[$item['_id']] = $item['count'];
                            }
                        }

                        // อัปเดตค่า indicator_type_counts กลับเข้า cursor data
                        $cursorData[0]['indicator_type_counts'] = $processedTypeCounts;

                        // เพิ่ม actual_indicator_count (ผลรวมจริงจาก aggregation)
                        if (!empty($processedTypeCounts)) {
                            $cursorData[0]['actual_indicator_count'] = array_sum($processedTypeCounts);
                        }
                    }

                    $data_transcation = json_encode($cursorData);
                } else {
                    $data_transcation = json_encode($cursor);
                }

                $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if (isset($data['site']['data']['id'])) {
                $this->saveLog($data['site']['data']['id'], json_encode($response));
            }

            return response()->json($response);
        }
    }

    public function events_load_attributes_tb(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                $draw = $data['data']['draw'];
                $row = (int)$data['data']['row'];
                $rowperpage = (int)$data['data']['rowperpage'];
                $reqId = $data['data']['reqId'];
                $count_page = $data['data']['count_page'];
                $url = $data['data']['url'] ?? null;
                $start = $row;

                // จำกัด rowperpage สูงสุดไว้ที่ 100 เพื่อป้องกัน timeout
                $rowperpage = min($rowperpage, 100);

                $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                $clientMD = new MongoClient($DB_MONGO_KEY);
                $col_fx_otx_events_indicator_ref = $clientMD->sosecure_threatintelligent->fx_otx_events_indicator_ref;

                // Query แบบ optimized
                $query = [
                    'pulse_id' => $reqId,
                ];

                // Date filter support
                $startDateStr = $data['data']['startDate'] ?? null;
                $endDateStr = $data['data']['endDate'] ?? null;

                if ($startDateStr && $endDateStr) {
                    try {
                        $query['updated_at'] = [
                            '$gte' => new \MongoDB\BSON\UTCDateTime(strtotime($startDateStr) * 1000),
                            '$lte' => new \MongoDB\BSON\UTCDateTime(strtotime($endDateStr) * 1000)
                        ];
                    } catch (\Exception $e) {
                        // Invalid date format — skip filter
                    }
                }

                // Options พร้อม projection และ maxTimeMS
                $options = [
                    'projection' => [
                        '_id' => 0,
                        'indicator_id' => 1,
                        'indicator_name' => 1,
                        'type' => 1,
                        'pulse_id' => 1,
                        'updated_at' => 1,
                        'tags' => 1,
                        'name' => 1,
                        'attribute_serverity' => 1,
                        'attribute_score' => 1,
                        'indicator' => 1,
                        'is_active' => 1,
                        'role' => 1,
                        'title' => 1,
                        'created_at' => 1
                    ],
                    'skip' => $start,
                    'limit' => $rowperpage,
                    'sort' => [
                        'updated_at' => -1,
                    ],
                    'maxTimeMS' => 25000,
                    'typeMap' => [
                        'root' => 'array',
                        'document' => 'array',
                        'array' => 'array'
                    ]
                ];

                // Count
                if ($count_page == -1) {
                    try {
                        $cursor_count = $col_fx_otx_events_indicator_ref->countDocuments($query, ['maxTimeMS' => 10000]);
                    } catch (\Exception $e) {
                        $cursor_count = $data['data']['total_record'] ?? 0;
                    }
                    $count_filter = $cursor_count;
                } else {
                    $cursor_count = $count_page;
                    $count_filter = $cursor_count;
                }

                $cursor = $col_fx_otx_events_indicator_ref->find($query, $options);
                $document_all = $cursor->toArray();

                // ตรวจสอบ tags — ให้เป็น string ว่างแทน null
                foreach ($document_all as &$item) {
                    $item['tags'] = $item['tags'] ?? '';
                }
                unset($item);

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
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if (isset($data['site']['data']['id'])) {
                $this->saveLog($data['site']['data']['id'], json_encode($response));
            }

            return response()->json($response);
        }
    }


    public function events_load_pulse_tb(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
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
                    'skip' => $start, //10
                    'limit' => $rowperpage //5
                ];

                if ($count_page == -1) {
                    $cursor_count = $fx_otx_events_event_ref->count($query);
                    $count_filter = $cursor_count;
                } else {
                    $cursor_count = $count_page;
                    $count_filter = $cursor_count;
                }



                $cursor = $fx_otx_events_event_ref->find($query, $options);
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

                if ($document_all) {
                    foreach ($document_all as  $value) {
                        $query = [
                            'pulse_id' => $value->pulse_id
                        ];
                        $document = $col_fx_otx_events->findOne($query, $options);

                        $order_number++;
                        $nestedData['No'] = $order_number;
                        $nestedData['name'] = $document["name"];
                        $nestedData['groups'] = $this->explode_val($document["groups"], 'groups', $url);
                        $nestedData['tags'] = $this->explode_val($document["tags"], 'tags', $url);
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
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function events_count_view(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                $clientMD = new MongoClient($DB_MONGO_KEY);
                $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_events;
                $options = array(
                    'typeMap' => array(
                        'root' => 'array',
                        'document' => 'array',
                    ),
                );
                $document = $col_fx_otx_indicator_detail->findOne(array('pulse_id' => $data['data']['pulse_id']), $options);
                if ($document) {
                    $update_fx_otx_events_indicator_ref = $col_fx_otx_indicator_detail->updateOne(
                        ['_id' => $document['_id']],
                        [
                            '$set' => [
                                'count_view' => $document['count_view'] + 1
                            ]
                        ]
                    );
                }

                $data_res = [
                    "count" => $document['count_view'] + 1,
                ];

                $data_transcation = json_encode($data_res);
                $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function load_adversary_tb(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'indicators') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
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
                        'skip' => $start, //10
                        'limit' => $rowperpage //5
                    ];

                    if ($request->count_page == -1) {
                        $cursor_count = $fx_otx_events_event_ref->count($query);
                        $count_filter = $cursor_count;
                    } else {
                        $cursor_count = $request->count_page;
                        $count_filter = $cursor_count;
                    }



                    $cursor = $fx_otx_events_event_ref->find($query, $options);
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

                    if ($document_all) {
                        foreach ($document_all as  $value) {
                            $query = [
                                'pulse_id' => $value->pulse_id
                            ];
                            $document = $col_fx_otx_events->findOne($query, $options);

                            $order_number++;
                            $nestedData['No'] = $order_number;
                            $nestedData['name'] = $document["name"];
                            $nestedData['groups'] = explode_val($document["groups"], 'groups');
                            $nestedData['tags'] = explode_val($document["tags"], 'tags');
                            $nestedData['attr'] = '';
                            $nestedData['attrCount'] = $document["indicator_count"];
                            $nestedData['public'] = ($document["public"]);
                            $nestedData['is_modified'] = ($document["is_modified"]);

                            try {
                                $nestedData['modified'] = @$document['modified'];
                            } catch (Exception $e) {
                                $nestedData['modified'] = $document['modified'];
                            } finally {
                                $nestedData['modified'] = $document['modified'];
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
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function load_malware_tb(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                if ($data['data']['menu'] !== 'indicators') {
                    return response()->json(['error' => "You don't have permission to access", 'status_code' => '403']);
                } else {
                    $auth_site = $this->AuthorizationSite($header, $request->mode, $data['data']['user_id'], $data['data']['menu']);
                    if ($auth_site['status_code'] !== '200') {
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
                        'skip' => $start, //10
                        'limit' => $rowperpage //5
                    ];

                    if ($request->count_page == -1) {
                        $cursor_count = $fx_otx_events_event_ref->count($query);
                        $count_filter = $cursor_count;
                    } else {
                        $cursor_count = $request->count_page;
                        $count_filter = $cursor_count;
                    }



                    $cursor = $fx_otx_events_event_ref->find($query, $options);
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

                    if ($document_all) {
                        foreach ($document_all as  $value) {
                            $query = [
                                'pulse_id' => $value->pulse_id
                            ];
                            $document = $col_fx_otx_events->findOne($query, $options);

                            $order_number++;
                            $nestedData['No'] = $order_number;
                            $nestedData['name'] = $document["name"];
                            $nestedData['groups'] = explode_val($document["groups"], 'groups');
                            $nestedData['tags'] = explode_val($document["tags"], 'tags');
                            $nestedData['attr'] = '';
                            $nestedData['attrCount'] = $document["indicator_count"];
                            $nestedData['public'] = ($document["public"]);
                            $nestedData['is_modified'] = ($document["is_modified"]);

                            try {
                                $nestedData['modified'] = @$document['modified'];
                            } catch (Exception $e) {
                                $nestedData['modified'] = $document['modified'];
                            } finally {
                                $nestedData['modified'] = $document['modified'];
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
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function table_summary(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $query_summary = "select t1.year,t1.month,group_industries_name,group_sumc from 
              (
            (
            SELECT year,month,group_concat(industries_name order by sumc DESC SEPARATOR '\n') as group_industries_name
            FROM 
            (
                SELECT year,month,industries_name, sum(attribute_count) as sumc
                FROM 
                (
                    SELECT year,month,industries_name,attribute_count 
                    FROM fx_indicator_summary_year
                    where type='attribute_type' and status=1
                    order by attribute_count DESC
                ) as a1
                group by year,month,industries_name
                order by sumc desc
            ) as tab
            group by year,month
            
            ) as t1
        inner join 
            (
            SELECT year,month,group_concat(sumc order by sumc DESC SEPARATOR '\n') as group_sumc 
            FROM 
            (
                SELECT year,month,industries_name,sum(attribute_count) as sumc
                FROM 
                (
                    SELECT year,month,industries_name,attribute_count 
                    FROM fx_indicator_summary_year
                    where type='attribute_type' and status=1
                    order by attribute_count DESC
                ) as a2
                group by year,month,industries_name
                order by sumc desc
            ) as tab
            group by year,month
            ) as t2
        ON (t1.year = t2.year and t1.month = t2.month)
        )
        order by year Desc,month Desc";
            $summary = DB::select($query_summary);
            if (!empty($summary)) {
                foreach ($summary as $records) {
                    // $records->month = $arr_months[$records->month];
                    $records->group_sumc = preg_replace_callback("/[0-9]+/", function ($matches) {
                        return number_format($matches[0], 0, ',', ',');
                    }, $records->group_sumc);
                }
            }
            $Transaction = TransactionBatchjob::where('mode', 'indicator_summary_type')->first();
            if (empty($Transaction)) {
                $Transaction = date("Y-m-d H:i:s");
            }
            else{
                $Transaction = $Transaction->transcation_date;
            }
            $data_transcation = json_encode(['summary'=>$summary,'dateday'=>$Transaction]);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);

            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    public function table_summary_export(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);

            $summary = DB::table('indicator_summary_year')->select('year', 'month', 'industries_name', DB::raw("SUM(attribute_count) as sumc"))
                ->where("status", "1")
                ->where("type", "attribute_type")
                ->where("year", ">=", $data['data']['minyear'])
                ->where("year", "<=", $data['data']['maxyear'])
                ->where("month", ">=", $data['data']['minmonth'])
                ->where("month", "<=", $data['data']['maxmonth'])
                ->groupBy("year", "month", "industries_name")
                ->orderBy("year", "desc")
                ->orderBy("month", "desc")
                ->orderBy("sumc", "desc")
                ->get();

            $data_transcation = json_encode($summary->toArray());
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'],  $data['site']['data']['mac_address_key']);

            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            } else {
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'message' => $e->getMessage(),
            );

            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            $this->saveLog($data['site']['data']['id'], json_encode($response));

            return response()->json($response);
        }
    }

    private function dataFalse($bearerToken, $mode, $data)
    {
        try {
            $header = $bearerToken;
            $site = $this->AuthorizationRegister($header, $mode);
            if ($site['status_code'] !== '200') {
                return $this->AuthorizationRegister($header, $mode);
            }
            $value = $data;
            $data = encrypt_decrypt('decrypt', $value, $header, $site['data']['ip_key'],  $site['data']['mac_address_key']);

            if ($data === false) {
                return $data;
            } else {
                $data_return = [
                    'site' => $site,
                    'data' => json_decode($data, true),
                ];
                return $data_return;
            }
        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e->getMessage(),
            );
            return response()->json($response);
        }
    }

    private function explode_val($val, $type = null, $url)
    {
        $result = '';
        if ($val) {
            $val_arr = explode(",", $val);
            if ($val_arr) {
                foreach ($val_arr as $tag) {
                    if ($type == 'tags') {
                        $result .=  '<a href="' . $url . '/indicators/tags/' . $tag . '">' . $tag . '</a> ,';
                    } else if ($type == 'groups') {
                        $result .=  '<a href="' . $url . '/indicators/groups/' . $tag . '">' . $tag . '</a> ,';
                    } else {
                        $result .=  '<a href="#">' . $tag . '</a> ,';
                    }
                }
                $result = rtrim($result, ',');
            }
        } else {
            $result = '';
        }
        return $result;
    }

    /**
     * Export events + indicators เป็น CSV (หลาย events ตาม date range)
     * type=1: export events only
     * type=2: export events + attributes
     */
    public function export_events_indicators(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }

            $params = $data['data'];

            // Validate required params
            $startDateInput = $params['start_date'] ?? null;
            $endDateInput = $params['end_date'] ?? null;
            $formType = (int)($params['type'] ?? 1);

            if (!$startDateInput || !$endDateInput || !in_array($formType, [1, 2])) {
                return response()->json(['error' => 'start_date, end_date, and type (1 or 2) are required', 'status_code' => '400']);
            }

            // Parse dates
            try {
                $tz = new \DateTimeZone('Asia/Bangkok');
                $start = new \DateTime($startDateInput . ' 00:00:00', $tz);
                $end = new \DateTime($endDateInput . ' 23:59:59', $tz);
                $from = new \MongoDB\BSON\UTCDateTime($start->getTimestamp() * 1000);
                $to = new \MongoDB\BSON\UTCDateTime($end->getTimestamp() * 1000);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format.', 'status_code' => '422']);
            }

            // Parse pulse_id (optional)
            $pulseRaw = $params['pulse_id'] ?? [];
            if (is_string($pulseRaw) && str_contains($pulseRaw, ',')) {
                $pulseIds = array_map('trim', explode(',', $pulseRaw));
            } else {
                $pulseIds = is_array($pulseRaw) ? $pulseRaw : [$pulseRaw];
            }
            $pulseIds = array_filter($pulseIds, function($id) { return is_string($id) && !empty($id); });

            // Connect Mongo
            $mongoUri = config("app.DB_MONGO_DEV");
            $client = new MongoClient($mongoUri);
            $db = $client->sosecure_threatintelligent;
            $eventsCollection = $db->fx_otx_events;
            $attributesCollection = $db->fx_otx_events_indicator_ref;
            $mongoOptions = ['typeMap' => ['root' => 'array', 'document' => 'array']];

            // Build event query
            $usePulseOnly = !empty($pulseIds) && isset($params['pulse_id_only']);
            $ispublished = $params['published'] ?? null;

            if ($usePulseOnly) {
                $eventQuery = ['pulse_id' => ['$in' => $pulseIds]];
            } else {
                $eventQuery = [
                    'modified' => ['$gte' => $from, '$lte' => $to],
                    'status' => 1,
                    'deleted_at' => null,
                    'indicator_count' => ['$gt' => 0],
                ];
                if ($ispublished !== null && $ispublished !== '') {
                    $ispublished_ = (int)$ispublished;
                    $eventQuery['public'] = ['$in' => [$ispublished_, (string)$ispublished]];
                }
                if (!empty($pulseIds)) {
                    $eventQuery['pulse_id'] = ['$in' => $pulseIds];
                }
            }

            // Event name filter (optional)
            $eventName = trim($params['event_name'] ?? '');
            if (!empty($eventName)) {
                $eventQuery['name'] = new \MongoDB\BSON\Regex($eventName, 'i');
            }

            $events = $eventsCollection->find($eventQuery, $mongoOptions)->toArray();

            if (empty($events)) {
                return response()->json([
                    'message' => !empty($pulseIds)
                        ? 'Not found event : ' . implode(', ', $pulseIds) . ' in date range'
                        : 'Not found event in date range',
                    'status_code' => '200'
                ]);
            }

            // สร้าง CSV
            $timestamp = date("Y-m-d_H.i.s");
            $fileName = $formType === 1
                ? "Sosecure-Threat-Insight-Events-{$timestamp}.csv"
                : "Sosecure-Threat-Insight-Events-Indicators-{$timestamp}.csv";
            $filePath = storage_path("app/exportindicator/{$fileName}");

            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0777, true);
            }

            $file = fopen($filePath, 'w');

            if ($formType === 1) {
                // Events only
                fputcsv($file, ['event_id', 'event_name', 'public', 'event_tags', 'modified_datetime']);

                foreach ($events as $doc) {
                    $eventModified = isset($doc['modified']) && $doc['modified'] instanceof UTCDateTime
                        ? $doc['modified']->toDateTime()->settimezone(new \DateTimeZone('Asia/Bangkok'))->format('Y-m-d')
                        : '';
                    fputcsv($file, [
                        $doc['pulse_id'] ?? '',
                        $doc['name'] ?? '',
                        $doc['public'] ?? '',
                        isset($doc['tags']) ? (is_array($doc['tags']) ? implode(',', $doc['tags']) : $doc['tags']) : '',
                        $eventModified,
                    ]);
                }
            } elseif ($formType === 2) {
                // Events + Attributes
                $eventPulseIds = array_column($events, 'pulse_id');
                $attrQuery = [
                    'pulse_id' => ['$in' => $eventPulseIds],
                    'updated_at' => ['$gte' => $from, '$lte' => $to]
                ];
                $attributes = $attributesCollection->find($attrQuery, $mongoOptions)->toArray();

                $attributeMap = [];
                foreach ($attributes as $attr) {
                    $pid = $attr['pulse_id'] ?? '';
                    $attributeMap[$pid][] = $attr;
                }

                fputcsv($file, [
                    'event_id', 'event_name', 'public', 'event_tags', 'modified_datetime',
                    'attribute_id', 'attribute_type', 'attribute_name', 'attribute_tags',
                    'attribute_score', 'attribute_serverity', 'attribute_datetime'
                ]);

                foreach ($events as $event) {
                    $pulseId = $event['pulse_id'] ?? '';
                    $eName = $event['name'] ?? '';
                    $ePublic = $event['public'] ?? '';
                    $eTags = isset($event['tags']) ? (is_array($event['tags']) ? implode(',', $event['tags']) : $event['tags']) : '';
                    $eModified = isset($event['modified']) && $event['modified'] instanceof UTCDateTime
                        ? $event['modified']->toDateTime()->settimezone(new \DateTimeZone('Asia/Bangkok'))->format('Y-m-d')
                        : '';

                    if (!empty($attributeMap[$pulseId])) {
                        foreach ($attributeMap[$pulseId] as $attr) {
                            $attrDatetime = isset($attr['updated_at']) && $attr['updated_at'] instanceof UTCDateTime
                                ? $attr['updated_at']->toDateTime()->settimezone(new \DateTimeZone('Asia/Bangkok'))->format('Y-m-d H:i:s')
                                : '';
                            fputcsv($file, [
                                $pulseId, $eName, $ePublic, $eTags, $eModified,
                                $attr['indicator_id'] ?? '',
                                $attr['type'] ?? '',
                                $attr['indicator'] ?? '',
                                isset($attr['tags']) ? (is_array($attr['tags']) ? implode(',', $attr['tags']) : $attr['tags']) : '',
                                $attr['attribute_score'] ?? '',
                                $attr['attribute_serverity'] ?? '',
                                $attrDatetime
                            ]);
                        }
                    } else {
                        fputcsv($file, [$pulseId, $eName, $ePublic, $eTags, $eModified, '', '', '', '', '', '', '']);
                    }
                }
            }

            fclose($file);

            return response()->download($filePath, $fileName, ['Content-Type' => 'text/csv']);

        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Export single event + indicators ทั้งหมดเป็น CSV
     */
    public function export_event_indicators(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }

            $pulseId = trim($data['data']['pulse_id'] ?? '');
            if (empty($pulseId)) {
                return response()->json(['error' => 'pulse_id is required', 'status_code' => '400']);
            }

            $mongoUri = config("app.DB_MONGO_DEV");
            $client = new MongoClient($mongoUri);
            $db = $client->sosecure_threatintelligent;
            $eventsCollection = $db->fx_otx_events;
            $attributesCollection = $db->fx_otx_events_indicator_ref;
            $mongoOptions = ['typeMap' => ['root' => 'array', 'document' => 'array']];

            $event = $eventsCollection->findOne([
                'pulse_id' => $pulseId,
                'status' => 1,
                'deleted_at' => null
            ], $mongoOptions);

            if (!$event) {
                return response()->json(['message' => "Not found event : {$pulseId}", 'status_code' => '200']);
            }

            $attributes = $attributesCollection->find(['pulse_id' => $pulseId], $mongoOptions)->toArray();

            // สร้าง CSV
            $timestamp = date("Y-m-d_H.i.s");
            $fileName = "Sosecure-Threat-Insight-Indicators-{$timestamp}-{$pulseId}.csv";
            $filePath = storage_path("app/exportindicator/{$fileName}");

            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0777, true);
            }

            $file = fopen($filePath, 'w');
            fputcsv($file, [
                'event_id', 'event_name', 'public', 'event_tags', 'modified_datetime',
                'attribute_id', 'attribute_type', 'attribute_name', 'attribute_tags',
                'attribute_score', 'attribute_serverity', 'attribute_datetime'
            ]);

            $eventName = $event['name'] ?? '';
            $eventPublic = $event['public'] ?? '';
            $eventTags = isset($event['tags']) ? (is_array($event['tags']) ? implode(',', $event['tags']) : $event['tags']) : '';
            $eventModified = isset($event['modified']) && $event['modified'] instanceof UTCDateTime
                ? $event['modified']->toDateTime()->settimezone(new \DateTimeZone('Asia/Bangkok'))->format('Y-m-d')
                : '';

            if (!empty($attributes)) {
                foreach ($attributes as $attr) {
                    $attrDatetime = isset($attr['updated_at']) && $attr['updated_at'] instanceof UTCDateTime
                        ? $attr['updated_at']->toDateTime()->settimezone(new \DateTimeZone('Asia/Bangkok'))->format('Y-m-d H:i:s')
                        : '';
                    fputcsv($file, [
                        $pulseId, $eventName, $eventPublic, $eventTags, $eventModified,
                        $attr['indicator_id'] ?? '',
                        $attr['type'] ?? '',
                        $attr['indicator'] ?? '',
                        isset($attr['tags']) ? (is_array($attr['tags']) ? implode(',', $attr['tags']) : $attr['tags']) : '',
                        $attr['attribute_score'] ?? '',
                        $attr['attribute_serverity'] ?? '',
                        $attrDatetime
                    ]);
                }
            } else {
                fputcsv($file, [$pulseId, $eventName, $eventPublic, $eventTags, $eventModified, '', '', '', '', '', '', '']);
            }

            fclose($file);

            return response()->download($filePath, $fileName, ['Content-Type' => 'text/csv']);

        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'message' => $e->getMessage()]);
        }
    }
}
