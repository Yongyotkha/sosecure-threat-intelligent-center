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
use App\FXAgentSchedule;
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
        $query = FXAgentLogs::
                    join('site', 'agent_logs.site_id', 'site.id')
                    // ->where('site_id', $request->site_id)
                    ->where(function ($query_site) use ($site_log_id) {
                        if($site_log_id != null)
                        {
                            $query_site->where('agent_logs.site_id', $site_log_id);
                        }
                        else
                        {
                            $query_site->where('agent_logs.site_id', '!=', null);
                        }
                    })
                    ->select(
                        'site.name as site_name',
                        'site.logo as site_logo',
                        'site.ip_key as site_ip_key',
                        'agent_logs.id as agent_logs_id',
                        'agent_logs.agent_id as agent_logs_agent_id',
                        'agent_logs.title as agent_logs_title',
                        'agent_logs.ip_address as agent_logs_ip_address',
                        'agent_logs.description as agent_logs_description',
                        'agent_logs.rules as agent_logs_rules',
                        'agent_logs.created as agent_logs_created'
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

        // dd($input);

        $query = FXAgentAlerts::
                    join('site', 'agent_alerts.site_id', 'site.id')
                    ->select(
                        'site.name as site_name',
                        'site.logo as site_logo',
                        'agent_alerts.id as agent_alerts_id',
                        'agent_alerts.rule as agent_alerts_rule',
                        'agent_alerts.description as agent_alerts_description',
                        'agent_alerts.incident as agent_alerts_incident',
                        'agent_alerts.status as agent_alerts_status',
                        'agent_alerts.created as agent_alerts_created'
                    );

        if($request->site_id != null)
        {
            $query->where('site_id', $request->site_id);
        }

        if($request->keyword_search != null)
        {
            $query->where('site.name', 'like', '%'.$request->keyword_search.'%')
                  ->orwhere('agent_alerts.rule', 'like', '%'.$request->keyword_search.'%')
                  ->orwhere('agent_alerts.description', 'like', '%'.$request->keyword_search.'%')
                  ->orwhere('agent_alerts.incident', 'like', '%'.$request->keyword_search.'%')
                  ->orwhere('agent_alerts.log_file', 'like', '%'.$request->keyword_search.'%');
        }

        if($request->filter_alert_site_name != null)
        {
            $query->where('site.name', 'like', '%'.$request->filter_alert_site_name.'%');
        }

        if($request->filter_alert_des != null)
        {
            $query->where('agent_alerts.description', 'like', '%'.$request->filter_alert_des.'%');
        }

        if($request->check_alert != null)
        {
            $query->where('agent_alerts.incident', $request->check_alert);
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
                    <a href="'.route('agentmanagement.view_txt').'" data-toggle="ajaxModal"  class="btn btn-info btn-xs">
                        <i class="fas fa-eye"></i>
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
        // dd($input);

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
                        'site_agents.domain as site_agents_domain',
                        'site_agents.ip_private as site_agents_ip_private',
                        'site_agents.last_online as site_agents_last_online',
                        'site_agents.status as site_agents_status',
                        'site_agents.created as site_agents_created'
                    );

        if($request->site_id != null)
        {
            $query->where('site_id', $request->site_id);
        }

        if($request->keyword_search != null)
        {
            $query->where('site.name', 'like', '%'.$request->keyword_search.'%')
                  ->orwhere('site_agents.device_name', 'like', '%'.$request->keyword_search.'%')
                  ->orwhere('os_type.name', 'like', '%'.$request->keyword_search.'%')
                  ->orwhere('site_agents.os_description', 'like', '%'.$request->keyword_search.'%')
                  ->orwhere('site_agents.system_info', 'like', '%'.$request->keyword_search.'%')
                  ->orwhere('site_agents.domain', 'like', '%'.$request->keyword_search.'%')
                  ->orwhere('site_agents.ip_private', 'like', '%'.$request->keyword_search.'%');
        }

        if($request->filter_agent_site_name != null)
        {
            $query->where('site.name', 'like', '%'.$request->filter_agent_site_name.'%');
        }

        if($request->filter_agent_device != null)
        {
            $query->where('site_agents.device_name', 'like', '%'.$request->filter_agent_device.'%');
        }

        if($request->filter_agent_ip != null)
        {
            $query->where('site_agents.ip_private', 'like', '%'.$request->filter_agent_ip.'%');
        }

        if($request->check_os_type != null)
        {
            $query->where('site_agents.os_type', $request->check_os_type);
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
        $query_schedule = FXAgentSchedule::
                        join('site', 'agent_schedule.site_id', 'site.id')
                        ->select(
                            'site.name as site_name',
                            'site.logo as site_logo',
                            'agent_schedule.name as agent_schedule_name',
                            'agent_schedule.start_date as agent_schedule_start_date',
                            'agent_schedule.end_date as agent_schedule_end_date',
                            'agent_schedule.username as agent_schedule_username',
                            'agent_schedule.status as agent_schedule_status',
                            'agent_schedule.source as agent_schedule_source',
                            'agent_schedule.duration as agent_schedule_duration'
                        );

        if($request->site_id != null)
        {
            $query_schedule->where('site_id', $request->site_id);
        }

        return DataTables::of($query_schedule)
        ->addColumn('chk', function($query_schedule) {
            $html = '';
            $html .= '
                <label>
                    <input name="select_all" value="'.$query_schedule->id.'" id="select-all" type="checkbox" class="select-chk">
                    <span class="label-text"></span>
                </label>
            ';
            return $html;
        })
        // ->addColumn('chk_status', function($query_schedule) {
        //     $html = '';
        //     $html .= '
        //         <label class="switch">
        //             <input type="checkbox" id="" onchange="" name="active" value="1"
        //     ';
        //             if($query_schedule->status == 1)
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
        // ->addColumn('action', function($query_schedule) {
        //     $html = '';
        //     $html .= '
        //         <a href="#?id='.$query_schedule->id.'" class="btn btn-danger btn-xs">
        //             <i class="fas fa-trash"></i>
        //         </a>
        //     ';
        //     return $html;
        // })
        ->rawColumns(['chk'])
        ->make(true);
    }
}
