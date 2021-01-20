<?php

namespace App\Http\Controllers;

use App\Traits\Taggable;
use Illuminate\Http\Request;
use Modules\Invoices\Entities\Invoice;
use App\DataLeakFeed;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
use App\R_s_s_news;

use DB;

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
        $this->request->validate(['keyword' => 'required']);
        $keyword = '%'.$this->request->keyword.'%';

        $dataWait['queryData'] = R_s_s_news::select('id','title_th as name','detail_th as content')->where('title_th','LIKE',$keyword);
        $dataWait['count'] =  $dataWait['queryData']->count();
        $dataWait['queryData'] = $dataWait['queryData']->get()->toArray();
        $data['dataSearch']["News"] = $dataWait;

        $dataWait['queryData'] = CVEMapping::select('id','namecve as name','description as content')->where('namecve','LIKE',$keyword);
        $dataWait['count'] =  $dataWait['queryData']->count();
        $dataWait['queryData'] = $dataWait['queryData']->get()->toArray();
        $data['dataSearch']["Vulnerabilities"] = $dataWait;



        $dataWait["queryData"] = DataLeakFeed::select('id','feedcontent as content','sourceid','keyword as name')->where('feel_type','!=','social')->where(function ($query) use ($keyword){
            $query->where('keyword','LIKE', $keyword)
                ->orWhere('source_name', 'LIKE', $keyword);
        });
        $dataWait["count"] = $dataWait["queryData"]->count();
        $dataWait["queryData"] = $dataWait["queryData"]->get()->toArray();
        $data['dataSearch']["Compromised"] = $dataWait;



        $dataWait["queryData"] = DataLeakFeed::select('id','feedcontent as content','sourceid','keyword as name')->where('feel_type','social')->where(function ($query) use ($keyword){
            $query->where('keyword','LIKE', $keyword)
                ->orWhere('source_name', 'LIKE', $keyword);
        });
        $dataWait["count"] = $dataWait["queryData"]->count();
        $dataWait["queryData"] = $dataWait["queryData"]->get()->toArray();
        $data['dataSearch']["Data Leak"] = $dataWait;

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
