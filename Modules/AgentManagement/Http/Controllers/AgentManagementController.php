<?php

namespace Modules\AgentManagement\Http\Controllers;

use App\AgentScanLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;
use DB;
use App\FXOSType;
use App\FXSeverityType;
use App\FXSiteAgents;
use App\FXAgentAlerts;
use App\FXAgentLogs;
use App\FXAgentRules;
use App\FXAgentSchedule;
use App\YaraLog;
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

    public function data_chart_incident(Request $request)
    {
        // $site_id = $request->site_id;
        // $query_incident = FXAgentAlerts::
        //                     select('incident')
        //                     ->groupBy('incident')
        //                     ->get();

        // $data = [];
        // $count_all = 0;
        // foreach($query_incident as $data_incident)
        // {
        //     $query_count = FXAgentAlerts::
        //                     where(function ($query_site) use ($site_id) {
        //                         if($site_id != null)
        //                         {
        //                             $query_site->where('site_id', $site_id);
        //                         }
        //                         else
        //                         {
        //                             $query_site->where('site_id', '!=', null);
        //                         }
        //                     })
        //                     ->where('status', 'Y')
        //                     ->where('incident',$data_incident->incident)
        //                     ->get();

        //     $count = count($query_count);

        //     $count_all = $count_all + $count;

        //     $data['chart'][] = [$data_incident->incident, $count];
        // }
        
        // $data['count_all'] = $count_all;

        $data['chart'][] = [0, 0];
        $data['count_all'] = 0;
        
        return response()->json($data);
    }

    public function data_chart_platform(Request $request)
    {
        $site_id = $request->site_id;
        $query_type = FXOSType::
                        select('id','name')
                        ->get();

        $data = [];
        $count_all = 0;
        foreach($query_type as $data_type)
        {
            $query = FXSiteAgents::
                        where(function ($query_site) use ($site_id) {
                            if($site_id != null)
                            {
                                $query_site->where('site_id', $site_id);
                            }
                            else
                            {
                                $query_site->where('site_id', '!=', null);
                            }
                        })
                        ->where(['deleted_at' => null])
                        ->where('os_type', $data_type->id)
                        ->get();
                    
            $count = count($query);

            $count_all = $count_all + $count;

            $data['chart'][] = [$data_type->name, $count];
        }
        $data['count_all'] = $count_all;
        // dd($data);

        return response()->json($data);
    }

    public function data_chart_severity(Request $request)
    {
        $site_id = $request->site_id;
        $query_severity = FXSeverityType::select('id', 'name')->get();

        $data = [];
        $count_all = 0;
        foreach($query_severity as $data_severity)
        {
            // FXAgentAlerts
            $query_count = YaraLog::
                                where(function ($query_site) use ($site_id) {
                                    if($site_id != null)
                                    {
                                        $query_site->where('site_id', $site_id);
                                    }
                                    else
                                    {
                                        $query_site->where('site_id', '!=', null);
                                    }
                                })
                                ->where('status', 'Y')
                                ->where('severity', $data_severity->name)
                                ->get();

            $count = count($query_count);

            $count_all = $count_all + $count;

            $data['chart'][] = [$data_severity->name, $count];
        }
        $data['count_all'] = $count_all;

        return response()->json($data);
    }
    
    public function data_chart_rule(Request $request)
    {
        $site_id = $request->site_id;

        // FXAgentAlerts
        $query_rule = YaraLog::
                        select('rule',DB::raw('count(*) as total'))
                        ->orderBy('total', 'desc')
                        ->groupBy('rule')
                        ->limit(10)
                        ->get();

        $data = [];
        foreach($query_rule as $data_rule)
        {
            $query_rule = YaraLog::
                            where(function ($query_site) use ($site_id) {
                                if($site_id != null)
                                {
                                    $query_site->where('site_id', $site_id);
                                }
                                else
                                {
                                    $query_site->where('site_id', '!=', null);
                                }
                            })
                            ->where(['status' => '1', 'deleted_at' => null])
                            ->where('rule', $data_rule->rule)
                            ->get();

            $count = count($query_rule);

            $data[] = [$data_rule->rule, $count];
        }

        return response()->json($data);
    }

    public function data_chart_timeline(Request $request)
    {
        $input = $request->all();
        $site_id = $request->site_id;
        $start_date_input = $request->start_date;
        $end_date_input = $request->end_date;
        $data = [];

        // dd($input);

        // // $start_date_input = date("Y-m-d", strtotime("+1 day", strtotime($start_date_input)));
        if($start_date_input == null && $end_date_input == null)
        {
            $end_date_input = date('Y-m-d');
            $start_date_input = date("Y-m-d", strtotime("-30 day", strtotime($end_date_input)));
        }

        $start_date = date('Y-m-d', strtotime($start_date_input));
        $end_date = date('Y-m-d', strtotime($end_date_input));
        
        $Variable1 = strtotime($start_date);
        $Variable2 = strtotime($end_date);
        
        for ($currentDate = $Variable1; $currentDate <= $Variable2; $currentDate += (86400)) {
                                            
            $Store = date('Y-m-d', $currentDate);
            $Store2 = date("Y-m-d", strtotime("+1 day", strtotime($Store)));
            
            $day = explode('-', $Store);

            //FXAgentAlerts
            $query_timeline = YaraLog::
                                where(function ($query_site) use ($site_id) {
                                    if(@$site_id != null)
                                    {
                                        $query_site->where('site_id', $site_id);
                                    }
                                    else
                                    {
                                        $query_site->where('site_id', '!=', null);
                                    }
                                })
                                ->where('status', '1')
                                ->whereBetween('created_at', [$Store, $Store2.' 23:59:59'])
                                ->get();

            $count = count($query_timeline);

            $data['day'][] = $day[2];
            $data['date'][] = $Store;
            $data['count'][] = $count;
        }

        return response()->json($data);
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
                ->where(['deleted_at' => null])
                ->get();
        $count_agent = count($query_agent);

        // $query_alert = FXAgentAlerts::
        //         where(function ($query_site) use ($site_log_id) {
        //             if($site_log_id != null)
        //             {
        //                 $query_site->where('site_id', $site_log_id);
        //             }
        //             else
        //             {
        //                 $query_site->where('site_id', '!=', null);
        //             }
        //         })
        //         ->where('status', 'Y')
        //         ->get();
        // $count_alert = count($query_alert);

        $query_alert = YaraLog::
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
                ->where(['deleted_at' => null])
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
                ->where('status', 'Y')
                ->get();
        $count_log = count($query_log);

        // FXAgentRules
        $count_rule = YaraLog::
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
                ->where('status', '1')
                ->distinct('rule')
                ->count('rule');

        // $count_rule = count($query_rule);

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
        // $query = FXAgentLogs::
        //             join('site', 'agent_logs.site_id', 'site.id')
        //             // ->where('site_id', $request->site_id)
        //             ->where(function ($query_site) use ($site_log_id) {
        //                 if($site_log_id != null)
        //                 {
        //                     $query_site->where('agent_logs.site_id', $site_log_id);
        //                 }
        //                 else
        //                 {
        //                     $query_site->where('agent_logs.site_id', '!=', null);
        //                 }
        //             })
        //             ->where('status', 'Y')
        //             ->select(
        //                 'site.name as site_name',
        //                 'site.logo as site_logo',
        //                 'site.ip_key as site_ip_key',
        //                 'agent_logs.id as agent_logs_id',
        //                 'agent_logs.agent_id as agent_logs_agent_id',
        //                 'agent_logs.title as agent_logs_title',
        //                 'agent_logs.ip_address as agent_logs_ip_address',
        //                 'agent_logs.description as agent_logs_description',
        //                 'agent_logs.rules as agent_logs_rules',
        //                 'agent_logs.created as agent_logs_created'
        //             )
        //             ->orderBy('created', 'desc')
        //             ->get();

        $query = AgentScanLog::
                    join('site', 'agent_scan_log.site_id', 'site.id')
                    ->join('site_agents', 'agent_scan_log.agent_id', 'site_agents.id')
                    ->orderBy('agent_scan_log.created_at', 'desc')
                    ->select(
                        'site.name as site_name',
                        'site.logo as site_logo',
                        'site.ip_key as site_ip_key',
                        'site_agents.ip_private as site_agents_ip_private',
                        'agent_scan_log.mode',
                        'agent_scan_log.first_scan',
                        'agent_scan_log.last_scan',
                        'agent_scan_log.description',
                        'agent_scan_log.created_at'
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
        $start_date_input = $request->start_date;
        $end_date_input = $request->end_date;

        // dd($input);

        $query = YaraLog::
                    join('site', 'yara_log.site_id', 'site.id')
                    ->join('site_agents', 'yara_log.agent_id', 'site_agents.id')
                    ->select(
                        'site.name as site_name',
                        'site.logo as site_logo',
                        'site_agents.ip_private as site_agents_ip_private',
                        'yara_log.id as agent_alerts_id',
                        'yara_log.rule as agent_alerts_rule',
                        'yara_log.description as agent_alerts_description',
                        'yara_log.severity as agent_alerts_severity',
                        'yara_log.status as agent_alerts_status',
                        'yara_log.created_at as agent_alerts_created',
                        'yara_log.device_name',
                        'yara_log.first_scan',
                        'yara_log.last_scan'
                    )
                    ->where('yara_log.status', 1)
                    ->orderBy('agent_alerts_created', 'desc');

        if($request->site_id != null)
        {
            $query->where('site_id', $request->site_id);
        }

        if($request->keyword_search != null)
        {
            $query->where('site.name', 'like', '%'.$request->keyword_search.'%')
                //   ->orwhere('agent_alerts.rule', 'like', '%'.$request->keyword_search.'%')
                  ->orwhere('yara_log.description', 'like', '%'.$request->keyword_search.'%');
                //   ->orwhere('yara_log.incident', 'like', '%'.$request->keyword_search.'%')
                //   ->orwhere('yara_log.log_file', 'like', '%'.$request->keyword_search.'%');
        }

        // if($start_date_input != null && $end_date_input != null)
        // {
        //     $query->whereBetween('agent_alerts.created', [$start_date_input, $end_date_input]);
        // }

        // if($request->filter_alert_rule != null)
        // {
        //     $query->where('agent_alerts.rule', 'like', '%'.$request->filter_alert_rule.'%');
        // }

        // if($request->filter_alert_des != null)
        // {
        //     $query->where('agent_alerts.description', 'like', '%'.$request->filter_alert_des.'%');
        // }

        // if($request->check_alert != null)
        // {
        //     $query->where('agent_alerts.incident', $request->check_alert);
        // }

        // if($request->check_alert_severity != null)
        // {
        //     $query->where('agent_alerts.severity', $request->check_alert_severity);
        // }

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
        ->addColumn('sev_status', function($query) {
            $html = '';
                    if($query->agent_alerts_severity == 'Critical')
                    {
            $html .= '<span class="badge" style="background-color: #b93624;">Critical</span>';
                    }
                    else if($query->agent_alerts_severity == 'High')
                    {
            $html .= '<span class="badge" style="background-color: #fcc838;">High</span>';
                    }
                    else if($query->agent_alerts_severity == 'Medium')
                    {
            $html .= '<span class="badge" style="background-color: #f2ff15;color: #333;">Medium</span>';
                    }
                    else if($query->agent_alerts_severity == 'Low')
                    {
            $html .= '<span class="badge" style="background-color: #409967;">Low</span>';
                    }
                    else if($query->agent_alerts_severity == 'Information')
                    {
            $html .= '<span class="badge" style="background-color: #00dcff;">Information</span>';
                    }
                    else
                    {
            $html .= '<span class="badge"> No Severity </span>';
                    }
            $html .= '';
            return $html;
        })
        ->addColumn('chk_status', function($query) {
            $html = '';
            $html .= '
                <label class="switch">
                    <input type="checkbox" id="" onchange="" name="active" value="1"
            ';
                    if($query->agent_alerts_status == 1)
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
        ->addColumn('device_name', function($query) {
            $html = '';
            $html .= '     
            <div class="wrapper-new">
                <span class="tooltip-new">'.$query -> device_name.'</span>
                <button class="btn btn-secondary"><i class="fas fa-file"></i></button>
            </div>  
            ';
            return $html;
        })
        
        ->addColumn('action', function($query) {
            $html = '';
            // <a href="'.route('agentmanagement.view_txt').'" data-toggle="ajaxModal"  class="btn btn-info btn-xs">
            //             <i class="fas fa-eye"></i>
            //         </a>
            $html .= '
                    
                    <a href="#" class="btn btn-danger btn-xs">
                        <i class="fas fa-ban"></i>
                    </a>
            ';
            return $html;
        })
        ->rawColumns(['chk', 'sev_status', 'chk_status', 'device_name', 'action'])
        ->make(true);
    }

    public function tb_agent(Request $request)
    {
        $input = $request->all();
        $start_date_input = $request->start_date;
        $end_date_input = $request->end_date;

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
                        'site_agents.created_at as site_agents_created'
                    )
                    ->where('site_agents.deleted_at', null);

        if($request->site_id != null)
        {
            $query->where('site_id', $request->site_id);
        }

        if($request->keyword_search != null)
        {
            $query->where('site.name', 'like', '%'.$request->keyword_search.'%')
                  ->orwhere('site_agents.device_name', 'like', '%'.$request->keyword_search.'%')
                  ->orwhere('os_type.name', 'like', '%'.$request->keyword_search.'%')
                //   ->orwhere('site_agents.os_description', 'like', '%'.$request->keyword_search.'%')
                  ->orwhere('site_agents.system_info', 'like', '%'.$request->keyword_search.'%')
                  ->orwhere('site_agents.domain', 'like', '%'.$request->keyword_search.'%')
                  ->orwhere('site_agents.ip_private', 'like', '%'.$request->keyword_search.'%');
        }

        if($start_date_input != null && $end_date_input != null)
        {
            $query->whereBetween('site_agents.last_online', [$start_date_input, $end_date_input]);
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

        if($request->filter_agent_os_des != null)
        {
            $query->where('site_agents.os_description', 'like', '%'.$request->filter_agent_os_des.'%');
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
                <a href="'.route('agentmanagement.agent_delete_modal', ['_id' => $query->site_agents_id]).'" class="btn btn-danger btn-xs" data-toggle="ajaxModal"><i class="fas fa-trash-alt"></i></a>
            ';
            return $html;
        })
        ->addColumn('chk_status', function($query) {
            $html = '';
            $html .= '
                <div class="text-center">
                    <label class="switch">
                        <input type="checkbox" id="agent-status-'.$query->site_agents_id.'" onchange="change_status_agent('.$query->site_agents_id.')" 
                ';
                if($query->site_agents_status == 1)
                {
                    $html .= 'checked';
                }
                $html .= '            
                        value="1">
                        <span></span>
                    </label>
                </div>
            ';
            return $html;
        })
        ->rawColumns(['chk', 'chk_status', 'action'])
        ->make(true);
    }

    public function tb_schedule(Request $request)
    {
        // $start_date_input = $request->start_date;
        // $end_date_input = $request->end_date;
        
        $query_schedule = AgentScanLog::
                        join('site', 'agent_scan_log.site_id', 'site.id')
                        // ->join('site_agents', 'yara_log.agent_id', 'site_agents.id')
                        ->select(
                            'site.name as site_name',
                            'site.logo as site_logo',
                            'site.ip_key as site_ip_key',
                            // 'site_agents.ip_private as site_agents_ip_private',
                            'agent_scan_log.mode',
                            'agent_scan_log.first_scan',
                            'agent_scan_log.last_scan',
                            'agent_scan_log.description'
                        );

        if($request->site_id != null)
        {
            $query_schedule->where('site_id', $request->site_id);
        }

        // if($start_date_input != null && $end_date_input != null)
        // {
        //     $query->whereBetween('agent_schedule.created_at', [$start_date_input, $end_date_input]);
        // }

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

    public function update_status_agent(Request $request)
    {
        $input = $request->all();
        
        $id = $request->id;
        $status = $request->status;

        $update_status = FXSiteAgents::where('id', $id)->update(['status' => $status]);

        return response()->json([
            'status_code' => '200'
        ]);

    }

    public function agent_delete_modal(Request $request)
    {
        $_id = $request->get('_id');

        return view('agentmanagement::modal.agent_delete')->with(compact('_id'));
    }

    public function agent_delete(Request $request)
    {
        $input = $request->all();
        
        $_id = $request->get('hd_delete_id');

        if(!empty($_id))
        {
            // $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);

            $date_now = date('Y-m-d H:i:s');

            $update_status = FXSiteAgents::where('id', $_id)->update(['deleted_at' => $date_now]);

            return response()->json([
                'status_code' => '200',
                'redirect' => route('agentmanagement.index')
            ]);
        }
        else
        {
            return response()->json([
                'status_code' => '500'
            ]);
        }

    }
}
