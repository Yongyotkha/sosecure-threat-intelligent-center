<?php

namespace Modules\AgentManagement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;
use DB;
use App\FXSiteAgents;
use App\FXAgentAlerts;
use App\FXAgentLogs;
use App\FXAgentRules;
use Modules\SiteSettings\Entities\Menu;
use Modules\SiteSettings\Entities\Menu_sub;
use Modules\SiteSettings\Entities\site_config_email_alert;
use Modules\SiteSettings\Entities\site_menu_permission;
use Modules\SiteSettings\Entities\site_menu_sub_permission;

use MongoDB\BSON\Regex;
use MongoDB\Client;
use MongoDB\Client as MongoClient;
use Yajra\DataTables\DataTables;

class AgentManagementController extends Controller
{
    /**
     * Item Model
     *
     * @var \Modules\Items\Entities\Item
     */
    protected $item;
    protected $siteSettings;
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request, SiteSettings $siteSettings)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
        $this->siteSettings = $siteSettings;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        $data['page'] = langapp('agent_management');
        $get_role_custom_first = @get_role_custom();
        $SiteSettings = @$get_role_custom_first['SiteSettings'];
        $data['site_settings'] = $SiteSettings;
        // $get_data = $this->siteSettings->get_data($id);
        // $data['siteSettings'] = $get_data;
        // $data['page'] = 'Agent';

        // $query_agent = FXSiteAgents::where('site_id', $get_data->id)->get();
        // $count_agent = count($query_agent);

        // $data['count_agent'] = $count_agent;

        // dd($data['site_settings']);

        return view('agentmanagement::index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('agentmanagement::create');
    }

    public function view_txt()
    {
        return view('agentmanagement::modal.view_txt_modal');
    }
    
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('agentmanagement::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('agentmanagement::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }


    public function count_head(Request $request)
    {
        $input = $request->all();
        // dd($input);
        $site_log_id = $request->site_log_id;

        $query_agent = FXSiteAgents::
                where(function ($query_site) use ($site_log_id) {
                    if($site_log_id != null)
                    {
                        $query_site->where('site_id', $site_log_id);
                    }
                    else
                    {
                        $query_site->where('site_id', '!=', null);
                    }
                })
                ->get();
        $count_agent = count($query_agent);

        $query_alert = FXAgentAlerts::
                where(function ($query_site) use ($site_log_id) {
                    if($site_log_id != null)
                    {
                        $query_site->where('site_id', $site_log_id);
                    }
                    else
                    {
                        $query_site->where('site_id', '!=', null);
                    }
                })
                ->get();
        $count_alert = count($query_alert);

        $query_log = FXAgentLogs::
                where(function ($query_site) use ($site_log_id) {
                    if($site_log_id != null)
                    {
                        $query_site->where('site_id', $site_log_id);
                    }
                    else
                    {
                        $query_site->where('site_id', '!=', null);
                    }
                })
                ->get();
        $count_log = count($query_log);

        $query_rule = FXAgentRules::
                where(function ($query_site) use ($site_log_id) {
                    if($site_log_id != null)
                    {
                        $query_site->where('site_id', $site_log_id);
                    }
                    else
                    {
                        $query_site->where('site_id', '!=', null);
                    }
                })
                ->get();
        $count_rule = count($query_rule);

        $response = [
            'count_agent' => $count_agent,
            'count_alert' => $count_alert,
            'count_log' => $count_log,
            'count_rule' => $count_rule
        ];

        return response()->json($response);

    }

    public function dudit_log_feed(Request $request)
    {
        $input = $request->all();
        // dd($input);

        $site_log_id = $request->site_log_id;
        $query = FXAgentAlerts::
                    join('site', 'agent_alerts.site_id', 'site.id')
                    // ->where('site_id', $request->site_id)
                    ->where(function ($query_site) use ($site_log_id) {
                        if($site_log_id != null)
                        {
                            $query_site->where('agent_alerts.site_id', $site_log_id);
                        }
                        else
                        {
                            $query_site->where('agent_alerts.site_id', '!=', null);
                        }
                    })
                    ->select(
                        'site.name as site_name',
                        'site.logo as site_logo',
                        'site.ip_key as site_ip_key',
                        'agent_alerts.id as agent_alerts_id',
                        'agent_alerts.rule as agent_alerts_rule',
                        'agent_alerts.description as agent_alerts_description',
                        'agent_alerts.incident as agent_alerts_incident',
                        'agent_alerts.status as agent_alerts_status',
                        'agent_alerts.created as agent_alerts_created'
                    )
                    ->get();
                    
        // dd($query);

        $response = [
            'query' => $query
        ];

        return response()->json($response);
    }

    public function tb_alert(Request $request)
    {
        $input = $request->all();

        // dd($request->site_id);

        $query = FXAgentAlerts::
                    join('site', 'agent_alerts.site_id', 'site.id')
                    ->select(
                        'site.name as site_name',
                        'site.logo as site_logo',
                        'agent_alerts.id as agent_alerts_id',
                        'agent_alerts.description as agent_alerts_description',
                        'agent_alerts.status as agent_alerts_status',
                        'agent_alerts.created as agent_alerts_created'
                    );

        if($request->site_id != null)
        {
            $query->where('site_id', $request->site_id);
        }

        return DataTables::of($query)
        ->addColumn('chk', function($query) {
            $html = '';
            $html .= '
                <label>
                    <input name="select_all" value="'.$query->agent_alerts_id.'" id="select-all" type="checkbox" class="select-chk">
                    <span class="label-text"></span>
                </label>
            ';
            return $html;
        })
        ->addColumn('chk_status', function($query) {
            $html = '';
            $html .= '
                <label class="switch">
                    <input type="checkbox" id="" onchange="" name="active" value="1"
            ';
                    if($query->agent_alerts_status == 'Y')
                    {
            $html .= 'checked';
                    }
            $html .= '        
                    >
                    <span></span>
                </label>
            ';
            return $html;
        })
        ->addColumn('action', function($query) {
            $html = '';
            $html .= '
                <a href="#?id='.$query->agent_alerts_id.'" class="btn btn-danger btn-xs">
                    <i class="fas fa-trash"></i>
                </a>
            ';
            return $html;
        })
        ->rawColumns(['chk','chk_status','action'])
        ->make(true);
    }

    public function tb_agent(Request $request)
    {
        $input = $request->all();

        // dd($request->site_id);

        $query = FXSiteAgents::
                    join('site', 'site_agents.site_id', 'site.id')
                    ->join('os_type', 'site_agents.os_type', 'os_type.id')
                    ->select(
                        'site.name as site_name',
                        'site.logo as site_logo',
                        'os_type.name as os_type_name',
                        'site_agents.id as site_agents_id',
                        'site_agents.device_name as site_agents_device_name',
                        'site_agents.os_description as site_agents_os_description',
                        'site_agents.system_info as site_agents_system_info',
                        'site_agents.ip_private as site_agents_ip_private',
                        'site_agents.last_online as site_agents_last_online',
                        'site_agents.status as site_agents_status',
                        'site_agents.created as site_agents_created'
                    );

        if($request->site_id != null)
        {
            $query->where('site_id', $request->site_id);
        }

        return DataTables::of($query)
        ->addColumn('chk', function($query) {
            $html = '';
            $html .= '
                <label>
                    <input name="select_all" value="'.$query->site_agents_id.'" id="select-all" type="checkbox" class="select-chk">
                    <span class="label-text"></span>
                </label>
            ';
            return $html;
        })
        ->addColumn('action', function($query) {
            $html = '';
            $html .= '
                <div class="btn-group">
                    <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-ellipsis-h"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="#"><i class="fas fa-power-off"></i> Restrat Service</a></li>
                        <li><a href="#"><i class="fas fa-search"></i> Quick Scan</a></li>
                        <li><a href="#"><i class="fas fa-stop-circle"></i> Stop Service</a></li>
                        <li><a href="#"><i class="fas fa-search"></i> Scan Yara</a></li>
                        <li><a href="#"><i class="fas fa-eye"></i> View Log Data</a></li>
                        <li><a href="#"><i class="fas fa-eye"></i> View Log Error</a></li>
                    </ul>
                </div>
            ';
            return $html;
        })
        ->rawColumns(['chk','action'])
        ->make(true);
    }

    

    public function tb_schedule(Request $request)
    {
        $query_agent = FXSiteAgents::where('site_id', $request->hd_site_id)->get();

        return DataTables::of($query_agent)
        // ->addColumn('chk', function($query_agent) {
        //     $html = '';
        //     $html .= '
        //         <label>
        //             <input name="select_all" value="'.$query_agent->id.'" id="select-all" type="checkbox" class="select-chk">
        //             <span class="label-text"></span>
        //         </label>
        //     ';
        //     return $html;
        // })
        // ->addColumn('chk_status', function($query_agent) {
        //     $html = '';
        //     $html .= '
        //         <label class="switch">
        //             <input type="checkbox" id="" onchange="" name="active" value="1"
        //     ';
        //             if($query_agent->status == 1)
        //             {
        //     $html .= 'checked';
        //             }
        //     $html .= '        
        //             >
        //             <span></span>
        //         </label>
        //     ';
        //     return $html;
        // })
        // ->addColumn('action', function($query_agent) {
        //     $html = '';
        //     $html .= '
        //         <a href="#?id='.$query_agent->id.'" class="btn btn-danger btn-xs">
        //             <i class="fas fa-trash"></i>
        //         </a>
        //     ';
        //     return $html;
        // })
        // ->rawColumns(['chk','chk_status','action'])
        ->make(true);
    }
}
