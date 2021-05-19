<?php

namespace App\Http\Controllers;

use App\DataLeakFeed;
use App\R_s_s_news;
use App\Traits\Taggable;
use DB;
use Illuminate\Http\Request;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
use Modules\MonitoringVulnerabilitys\Entities\CVEMappingAssets;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use MongoDB\Client as MongoClient;

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

    public function search()
    {

        // dd(json_encode($this->request->keyword));
        // $this->request->validate(['keyword' => 'required']);
        $data['dataSearch'] = array();
        $limit = 100;//->take($limit)

        $role_custom = @check_role_custom();
        $site_id_arr = @get_role_custom()['site_id_arr'];

        if ($this->request->keyword) {

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
                    $dataWait['queryData'] = $col_fx_transaction_otx_indicators_data->aggregate($pipeline,$options);
                    $dataWait['queryData'] = $dataWait['queryData']->toArray();
                    $dataWait['moreDetail'] = $dataWait['count']<101?"":"/indicators/events?Search_Link_All=".$this->request->keyword;
                    $data['dataSearch']["indicators"] = $dataWait;
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
        return view('searches')->with($data);
    }

    public function loadSearchAPI(Request $request)
    {
        $role_custom = @check_role_custom();
        $sourse =$request->sourse;
        $keyword =$request->keyword;
        $response = array();
        if($sourse =="ibmcloud"){
            $ibmcloud_API_Key = "d4b45ba9-4a1f-4127-bb72-1a01ab26a4b9";
            $ibmcloud_API_Key_Password = "95d8e0cd-0f34-45dc-9c6c-aa490fcb0415";
            $ibmcloud_url = "https://exchange.xforce.ibmcloud.com/api/ipr/190.187.248.117";
            $ch = curl_init();
            header('Content-type: application/json');
            curl_setopt($ch, CURLOPT_URL,$ibmcloud_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "$ibmcloud_API_Key:$ibmcloud_API_Key_Password");
            $result = curl_exec($ch);
            $response = $result;
            curl_close($ch);  
        }else if($sourse =="virustotal"){
            $virustotal_API_Key = "8ed71053d254aa99c9a79b73c6f3223cac762c2c77628d075e62ec506a538267";
            $virustotal_url = "https://www.virustotal.com/api/v3/ip_addresses/190.187.248.117";
            $virustotal_url='https://www.virustotal.com/api/v3/domains/xlus0222uj81bxyf.xyz';
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
        }else if($sourse =="hybrid"){



        }else{

        }
          

       

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'data' => $response,
                'data' => $response,
            ],
            true,
            Response::HTTP_OK
        );
    }
}
