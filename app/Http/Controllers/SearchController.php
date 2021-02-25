<?php

namespace App\Http\Controllers;

use App\DataLeakFeed;
use App\R_s_s_news;
use App\Traits\Taggable;
use DB;
use Illuminate\Http\Request;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
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

        if ($this->request->keyword) {

            $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
            $clientMD = new MongoClient($DB_MONGO_KEY);
            

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


            $dataWait['queryData'] = CVEMapping::select('id', 'namecve as name', 'description as content', DB::raw('CONCAT("/monitoringvulnerabilitys") AS link'))->where('namecve', 'LIKE', $keyword);
            $dataWait['count'] = $dataWait['queryData']->count();
            if ($dataWait['count'] > 0) {
                $dataWait['queryData'] = $dataWait['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                $data['dataSearch']["Vulnerabilities"] = $dataWait;
            }


            $dataWait["queryData"] = DataLeakFeed::select('id', 'feedcontent as content', 'sourceid', 'keyword as name', DB::raw('CONCAT("/darkweb-datas") AS link'))->where('feel_type', '!=', 'social')->where(function ($query) use ($keyword) {
                $query->where('keyword', 'LIKE', $keyword)
                ->orWhere('source_name', 'LIKE', $keyword);
            });
            $dataWait["count"] = $dataWait["queryData"]->count();
            if ($dataWait['count'] > 0) {
                $dataWait["queryData"] = $dataWait["queryData"]->orderBy('updated_at', 'desc')->get()->toArray();
                $data['dataSearch']["Compromised"] = $dataWait;
            }


            $dataWait["queryData"] = DataLeakFeed::select('id', 'feedcontent as content', 'sourceid', 'keyword as name', DB::raw('CONCAT("/socialdatas") AS link'))->where('feel_type', 'social')->where(function ($query) use ($keyword) {
                $query->where('keyword', 'LIKE', $keyword)
                ->orWhere('source_name', 'LIKE', $keyword);
            });
            $dataWait["count"] = $dataWait["queryData"]->count();
            if ($dataWait['count'] > 0) {
                $dataWait["queryData"] = $dataWait["queryData"]->orderBy('updated_at', 'desc')->get()->toArray();
                $data['dataSearch']["Data Leak"] = $dataWait;
            }


            $dataWait['queryData'] = WebdefacmentSetting::select('id', 'name', 'url as content', DB::raw('CONCAT("/webdefacement/detail/",code) AS link'))->where('name', 'LIKE', $keyword);
            $dataWait['count'] = $dataWait['queryData']->count();
            if ($dataWait['count'] > 0) {
                $dataWait['queryData'] = $dataWait['queryData']->orderBy('updated_at', 'desc')->get()->toArray();
                $data['dataSearch']["Web Defacement"] = $dataWait;
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
                            'content' => [ '$concat' => ['type: ','$type']],
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
}
