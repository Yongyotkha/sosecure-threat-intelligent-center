<?php

namespace Modules\AgentManagement\Http\Controllers;

use App\AgentScanLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;
use DB;
use File;
use Validator;
use ZipArchive;
use Auth;
use App\FXOSType;
use App\FXSeverityType;
use App\FXSiteAgents;
use App\FXAgentAlerts;
use App\FXAgentLogs;
use App\FXAgentRules;
use App\FXAgentSchedule;
use App\YaraLog;
use App\TBLRuleCategory;
use App\TBLRuleFiles;
use App\TBLRuleName;
use App\RuleNameSite;
use App\RuleFileSiteDownload;
use App\RuleFileSiteAgentDownload;
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
                                ->where('status', '1')
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

        $site_id = $request->site_id;
        $keyword_search = $request->keyword_search;
        $query = YaraLog::
                    join('site', 'yara_log.site_id', 'site.id')
                    ->join('site_agents', 'yara_log.agent_id', 'site_agents.id')
                    ->leftjoin('rule_name', 'yara_log.rule', 'rule_name.rule_name')
                    ->select(
                        'site.name as site_name',
                        'site.logo as site_logo',
                        'site_agents.ip_private as site_agents_ip_private',
                        'yara_log.id as agent_alerts_id',
                        'yara_log.rule as agent_alerts_rule',
                        'yara_log.description as agent_alerts_description',
                        // 'yara_log.severity as agent_alerts_severity',
                        'yara_log.status as agent_alerts_status',
                        'yara_log.created_at as agent_alerts_created',
                        'yara_log.device_name',
                        'yara_log.first_scan',
                        'yara_log.last_scan',
                        'rule_name.severity as severity_status'
                    )
                    
                    ->where('yara_log.status', 1)
                    ->where(function($query) use ($site_id ){
                        if($site_id  != null){
                            $query->where('site_id', $site_id );
                        }
                    })
                    ->where(function($query) use ($keyword_search){
                        if($keyword_search != null) {
                            $query->where('site.name', 'like', '%'.$keyword_search.'%')
                                ->orwhere('yara_log.description', 'like', '%'.$keyword_search.'%');     
                        }
                    })
                    ->where('yara_log.ignore_flag', 'Y')
                    ->orderBy('agent_alerts_created', 'desc')               
                    ->get();
          

        // if($request->site_id != null)
        // {
        //     $query->where('site_id', $request->site_id);
        // }

        // if($request->keyword_search != null)
        // {
        //     $query->where('site.name', 'like', '%'.$request->keyword_search.'%')
        //         //   ->orwhere('agent_alerts.rule', 'like', '%'.$request->keyword_search.'%')
        //           ->orwhere('yara_log.description', 'like', '%'.$request->keyword_search.'%');
        //         //   ->orwhere('yara_log.incident', 'like', '%'.$request->keyword_search.'%')
        //         //   ->orwhere('yara_log.log_file', 'like', '%'.$request->keyword_search.'%');
        // }

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
        ->addColumn('severity_status', function($query) {
            $html = '';
                    if($query->severity_status == 'Critical')
                    {
            $html .= '<span class="badge" style="background-color: #b93624;">Critical</span>';
                    }
                    else if($query->severity_status == 'High')
                    {
            $html .= '<span class="badge" style="background-color: #fcc838;">High</span>';
                    }
                    else if($query->severity_status == 'Medium')
                    {
            $html .= '<span class="badge" style="background-color: #f2ff15;color: #333;">Medium</span>';
                    }
                    else if($query->severity_status == 'Low')
                    {
            $html .= '<span class="badge" style="background-color: #409967;">Low</span>';
                    }
                    else if($query->severity_status == 'Information')
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
            $html .= ' 
                
                <a href="'.route('agentmanagement.modal_agent_alert_delete', ['id' => $query->agent_alerts_id]).'"  class="btn btn-danger btn-xs" data-toggle="ajaxModal">
                    <i class="fas fa-ban"></i>
                </a>
            ';
            return $html;
        })
        ->rawColumns(['chk', 'severity_status', 'chk_status', 'device_name', 'action'])
        ->make(true);
    }


    public function modal_agent_alert_delete(Request $request)
    {
        $query = YaraLog::where(['id' => $request->id])->first();
        return view('agentmanagement::modal.delete_alert')->with(compact('query'));   
    }

    public function alert_delete_id(Request $request){
        $id = $request->hd_delete_id;

        $query = YaraLog::where('id', $id)
            ->update([
                'ignore_flag' => 'N'
            ]);

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
                        <li class="d-none"><a href="#"><i class="fas fa-power-off"></i> Restrat Service</a></li>
                        <li class="d-none"><a href="#"><i class="fas fa-search"></i> Quick Scan</a></li>
                        <li class="d-none"><a href="#"><i class="fas fa-stop-circle"></i> Stop Service</a></li>
                        <li class="d-none"><a href="#"><i class="fas fa-search"></i> Scan Yara</a></li>
                        <li><a href="#"><i class="fas fa-eye"></i> View Log Data</a></li>
                        <li class="d-none"><a href="#"><i class="fas fa-eye"></i> View Log Error</a></li>
                        <li><a href="'.route('agentmanagement.agent_modal_control_agent').'" data-toggle="ajaxModal"><i class="fas fa-eye"></i> Control Agent</a></li>
                        <li><a href="'.route('agentmanagement.agent_modal_manage_rule', $query->site_id).'" data-toggle="ajaxModal"><i class="fas fa-eye"></i> Manage Rule</a></li>
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

    public function agent_modal_control_agent(Request $request)
    {
        // $_id = $request->get('_id');
        return view('agentmanagement::modal.agent_modal_control_agent');
    }

    public function agent_modal_manage_rule(Request $request)
    {
        $id = $request->get('id');
        $ruleNameSite = RuleNameSite::select('rule_category.name as category_name','rule_name_site.id','rule_name.file_name', 'rule_name.rule_name', 'rule_name.description', 'rule_name.severity', 'rule_name.status', 'rule_name_site.create_by', 'rule_name_site.update_by', 'rule_name_site.created_at')->where('site_id', $request -> site_id)
        ->join('rule_name', 'rule_name.id', '=', 'rule_name_site.rule_id')
        ->join('rule_category', 'rule_category.id', '=', 'rule_name.rule_category_id')
        ->get();
        return view('agentmanagement::modal.agent_modal_manage_rule', compact('ruleNameSite'));
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

    public function get_rule_site(Request $request)
    {
        // dd($request->all());

        $site_id = $request->search_site;
        $keyword_search = $request->keyword_search;

        $data_site = DB::table('site')->where(['id' => $site_id])->first();

        $site_rule = RuleNameSite::where(['site_id' => $site_id, 'deleted_at' => null])->pluck('rule_id')->toArray();

        $site_name_rule = TBLRuleName::
            leftjoin('rule_category', 'rule_name.rule_category_id', 'rule_category.id')
            ->select(
                'rule_name.*',
                'rule_category.id as category_id',
                'rule_category.name as category_name',
            )
            ->where(function ($master_rule) use ($keyword_search) {

                if($keyword_search)
                {
                    // $master_rule->where(TBLRuleName::raw("(CONCAT(category_name,' - ',rule_name.rule_name))"), 'like','%'.$keyword_search.'%');
                    $master_rule->where('rule_name.rule_name', 'like','%'.$keyword_search.'%')
                        ->orwhere('rule_category.name', 'like','%'.$keyword_search.'%');
                }

            })
            ->whereIn('rule_name.id', $site_rule)
            ->where(['rule_name.status' => 'Y', 'rule_name.deleted_at' => null])
            ->get();
        
        $master_rule = TBLRuleName::
            leftjoin('rule_category', 'rule_name.rule_category_id', 'rule_category.id')
            ->select(
                'rule_name.*',
                'rule_category.id as category_id',
                'rule_category.name as category_name',
            )
            ->where(function ($master_rule) use ($keyword_search) {

                if($keyword_search)
                {
                    // $master_rule->where(TBLRuleName::raw("(CONCAT(category_name,' - ',rule_name.rule_name))"), 'like','%'.$keyword_search.'%');
                    $master_rule->where('rule_name.rule_name', 'like','%'.$keyword_search.'%')
                        ->orwhere('rule_category.name', 'like','%'.$keyword_search.'%');
                }

            })
            ->whereNotIn('rule_name.id', $site_rule)
            ->where(['rule_name.status' => 'Y', 'rule_name.deleted_at' => null])
            ->get();

        $html = '';
        
        if(count($site_name_rule) > 0)
        {
            foreach($site_name_rule as $name_rule)
            {
                $html .= '
                    <li class="item-list item--keyword" data-id="'. $name_rule->id .'">
                        <div class="left-side-item">
                            <span class="drag-handle m-r-xs"><i class="fa fa-arrows-alt"></i></span>
                            <span class="text-keyword">'. $name_rule->get_name_category->name .' - '. $name_rule->rule_name .'</span>
                        </div>
                        <div class="action-keyword">
                            <a href="#" class="text-white delete_rule_site_master" data-delete_rule_site_master="'. $name_rule->id .'" data-mode_delete="site"><i class="fas fa-trash-alt"></i></a>
                        </div>
                    </li>
                ';
                // <a href="#" class="text-white m-r-xs edit-keyword" data-target="#edit_keyword" data-toggle="modal"><i class="fas fa-ellipsis-v"></i></a>
            }
        }

        $html_master_rule = '';

        if(count($master_rule) > 0)
        {
            foreach($master_rule as $mas_rule)
            {
                $html_master_rule .= '
                    <li class="item-list item--keyword" data-id="'. $mas_rule->id .'">
                        <div class="left-side-item">
                            <span class="drag-handle m-r-xs"><i class="fa fa-arrows-alt"></i></span>
                            <span class="text-keyword">'. $mas_rule->get_name_category->name .' - '. $mas_rule->rule_name .'</span>
                        </div>
                        <div class="action-keyword">
                            <a href="#" class="text-white delete_rule_site_master" data-delete_rule_site_master="'. $mas_rule->id .'" data-mode_delete="master"><i class="fas fa-trash-alt"></i></a>
                        </div>
                    </li>
                ';
                // <a href="#" class="text-white m-r-xs edit-keyword" data-target="#edit_keyword" data-toggle="modal"><i class="fas fa-ellipsis-v"></i></a>
            }
        }

        $response = [
            'data_site' => $data_site,
            'html' => $html,
            'html_master_rule' => $html_master_rule,
            'site_name_rule' => $site_name_rule
        ];

        return response()->json($response);
    }

    public function get_extension_rule_site(Request $request)
    {
        $select_extension = TBLRuleCategory::where([
                'mode' => 'extention', 
                'status' => 'Y',
                'deleted_at' => null
            ])
            ->select('id', 'name')
            ->get()
            ->toArray();

        $response = [
            'select_extension' => $select_extension
        ];

        return response()->json($response);
    }

    public function check_insert_rule_process(Request $request)
    {

        // dd($request->all());

        $message = '';
        $status = 0;

        $from_id = $request->from_id;
        $to_id = $request->to_id;
        $attributes_id = $request->attributes_id;
        $code_site = $request->code_site;

        $main_data = [];
        $main_data['site_id'] = $code_site;
        $main_data['rule_id'] = $attributes_id;
        $main_data['create_by'] = Auth::user()->id;
        $main_data['update_by'] = Auth::user()->id;

        $site_rule = RuleNameSite::create($main_data);

        $get_data_category = TBLRuleName::where(['id' => $attributes_id])->first();

        $chk_file_site = RuleFileSiteDownload::where(['site_id' => $code_site, 'rule_files_id' => $get_data_category->rule_file_id])
            ->where('transaction_download_client', '!=', 3)
            ->get();

        if(count(@$chk_file_site) == 0)
        {
            $main_data_rule_file_site = [];
            $main_data_rule_file_site['site_id'] = $code_site;
            $main_data_rule_file_site['rule_files_id'] = $get_data_category->rule_file_id;
            $main_data_rule_file_site['status'] = 'Y';
            $main_data_rule_file_site['transaction_download_client'] = 1;
            $main_data_rule_file_site['created_by'] = Auth::user()->id;
            $main_data_rule_file_site['updated_by'] = Auth::user()->id;
    
            RuleFileSiteDownload::create($main_data_rule_file_site);
        }

        // $site = SiteSettings::select('id')->where('code', $code_site)->first();

        // if(($from_id) && ($to_id || $attributes_id)) 
        // {
        //     $select_order = Site_keywords::select('order')->orderBy('order','desc')->first();

        //     if($from_id == 'keyword_main') 
        //     {
        //         $site_keywords_main = site_keywords_main::where('id',$attributes_id)->first();

        //         $Site_keywords_check = Site_keywords::where('keywords_main_id',$attributes_id)->where('site_id',$site->id)->where('type','social')->first();
        //         if($Site_keywords_check) 
        //         {
        //             $message = langapp('changes_saved_successful');
        //             $status = 1;
        //         } 
        //         else 
        //         {
        //             $Site_keywords_insert = new Site_keywords;
        //             $Site_keywords_insert->code = generator_uuid();
        //             $Site_keywords_insert->keywords_main_id = $attributes_id;
        //             $Site_keywords_insert->site_id = $site->id;
        //             $Site_keywords_insert->name = $site_keywords_main->name;
        //             $Site_keywords_insert->type = 'social';
        //             $Site_keywords_insert->status = 1;
        //             $Site_keywords_insert->created_by = Auth::user()->id;
        //             $Site_keywords_insert->order = $select_order->order+1;
        //             $Site_keywords_insert->save();

        //             $message = langapp('changes_saved_successful');
        //             $status = 1;
        //         }
        //     }
        // }

        // dd($message);

        return ajaxResponse(
            [
                'data' => '',
                'message' => 'Change save success',
                'status' => 'success'
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function select_rule_all_master(Request $request)
    {
        // dd($request->all());

        $code_site = $request->site_id;

        $date_now = date('Y-m-d H:i:s');

        $site_rule = RuleNameSite::where(['site_id' => $code_site])->update(['deleted_at' => $date_now, 'deleted_by' => Auth::user()->id]);
        
        $get_data_category = TBLRuleName::where(['status' => 'Y', 'deleted_at' => null])->get();

        foreach($get_data_category as $data_category)
        {
            $main_data = [];
            $main_data['site_id'] = $code_site;
            $main_data['rule_id'] = $data_category->id;
            $main_data['create_by'] = Auth::user()->id;
            $main_data['update_by'] = Auth::user()->id;

            RuleNameSite::create($main_data);

            $chk_file_site = RuleFileSiteDownload::where(['site_id' => $code_site, 'rule_files_id' => $data_category->rule_file_id])
                ->where('transaction_download_client', '!=', 3)
                ->get();
    
            if(count(@$chk_file_site) == 0)
            {
                $main_data_rule_file_site = [];
                $main_data_rule_file_site['site_id'] = $code_site;
                $main_data_rule_file_site['rule_files_id'] = $data_category->rule_file_id;
                $main_data_rule_file_site['status'] = 'Y';
                $main_data_rule_file_site['transaction_download_client'] = 1;
                $main_data_rule_file_site['created_by'] = Auth::user()->id;
                $main_data_rule_file_site['updated_by'] = Auth::user()->id;
        
                RuleFileSiteDownload::create($main_data_rule_file_site);
            }
        }

        return ajaxResponse(
            [
                'data' => '',
                'message' => 'Change save success',
                'status' => 'success'
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function delete_rule_all_site(Request $request)
    {
        // dd($request->all());

        $code_site = $request->site_id;

        $date_now = date('Y-m-d H:i:s');

        $site_rule = RuleNameSite::where(['site_id' => $code_site])->update(['deleted_at' => $date_now, 'deleted_by' => Auth::user()->id]);

        return ajaxResponse(
            [
                'data' => '',
                'message' => 'Change save success',
                'status' => 'success'
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function delete_rule_site(Request $request)
    {
        // dd($request->all());

        $date_now = date('Y-m-d H:i:s');

        if($request->delete_mode == 'master')
        {
            TBLRuleName::where(['id' => $request->delete_rule_id])->update(['deleted_at' => $date_now, 'deleted_by' => Auth::user()->id]);
            RuleNameSite::where(['rule_id' => $request->delete_rule_id])->update(['deleted_at' => $date_now, 'deleted_by' => Auth::user()->id]);
        }
        else
        {
            RuleNameSite::where(['rule_id' => $request->delete_rule_id, 'site_id' => $request->delete_site])->update(['deleted_at' => $date_now, 'deleted_by' => Auth::user()->id]);
        }

        $response = [
            'status' => 'success',
            'message' => 'Delete rule success.'
        ];

        return response()->json($response);
    }

    public function agent_rule(Request $request)
    {
        $data['page'] = langapp('agent_rule');
        $get_role_custom_first = @get_role_custom();
        $SiteSettings = @$get_role_custom_first['SiteSettings'];
        $data['site_settings'] = $SiteSettings;

        $select_category = TBLRuleCategory::where(['status' => 'Y'])->select('id', 'name')->get()->toArray();
        $data['select_category'] = $select_category;

        // dd($data['site_settings']);

        $select_site = DB::table('site')->select('code', 'name')->pluck('name', 'code')->toArray();
        $data['select_site'] = $select_site;

        $master_rule = TBLRuleName::where(['deleted_at' => null])->get();
        $data['master_rule'] = $master_rule;

        return view('agentmanagement::rule')->with($data);
    }

    public function agent_rule_chart_top(Request $request)
    {
        $querys = TBLRuleCategory::
            // where(['status' => 'Y'])
            get();

        foreach($querys as $query)
        {
            $query_rule_name = TBLRuleName::
                where([
                    'rule_category_id' => $query->id,
                    'status' => 'Y'
                ])
                ->select(
                    'file_name',
                    'rule_name',
                    'description',
                    'severity'
                )
                ->get();

            $query['arr_rule_name'] = $query_rule_name;
        }

        // dd($querys);

        return DataTables::of($querys)
            ->addIndexColumn()
            ->editColumn('test', function($querys){
                return '';
            })
            ->editColumn('c_checkbox', function($querys){
                $html = '';

                $html .= '
                    <label>
                        <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                        <span class="label-text"></span>
                    </label>
                '; 

                return $html;
            })
            ->editColumn('c_file_name', function($querys){
                $html = '';

                $arr = [];
                foreach ($querys->arr_rule_name as $arr_rule_name)
                {
                    $arr[] = $arr_rule_name->file_name;
                }

                return $arr ? implode('<br>', $arr) : '-';
            })
            ->editColumn('c_rule_name', function($querys){
                $html = '';

                $arr = [];
                foreach ($querys->arr_rule_name as $arr_rule_name)
                {
                    $arr[] = $arr_rule_name->rule_name;
                }

                return $arr ? implode('<br>', $arr) : '-';
            })
            ->editColumn('c_severity', function($querys){
                $html = '';

                if(@$querys->severity == 'Critical')
                {
                    $html .= '<span class="badge" style="background-color: #b93624;">Critical</span>';
                }
                else if(@$querys->severity == 'High')
                {
                    $html .= '<span class="badge" style="background-color: #fcc838;">High</span>';
                }
                else if(@$querys->severity == 'Medium')
                {
                    $html .= '<span class="badge" style="background-color: #f2ff15;color: #333;">Medium</span>';
                }
                else if(@$querys->severity == 'Low')
                {
                    $html .= '<span class="badge" style="background-color: #409967;">Low</span>';
                }
                else if(@$querys->severity == 'Information')
                {
                    $html .= '<span class="badge" style="background-color: #00dcff;">Information</span>';
                }
                else
                {
                    $html .= '<span class="badge"> No Severity </span>';
                }

                return $html;
            })
            ->editColumn('c_status', function($querys){
                $html = '';

                $html .= '
                    <label class="switch">
                        <input type="checkbox" id="status" name="status" 
                ';

                if($querys->status == 'Y')
                {
                    $html .= 'checked';
                }

                $html .=  ' value="1">
                        <span></span>
                    </label>          
                '; 

                return $html;
            })
            ->editColumn('c_action', function($querys){
                $html = '';

                $html .= '
                    <button type="button" class="btn btn-info btn-xs"><i class="fas fa-edit"></i></button>
                    <button type="button" class="btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></button>                
                '; 

                return $html;
            })
            ->rawColumns(['c_checkbox', 'c_file_name', 'c_rule_name', 'c_severity', 'c_status', 'c_action'])
            ->make(true);
    }

    public function agent_rule_tbl_all_rule(Request $request)
    {
        $querys = TBLRuleCategory::
            where(['mode' => 'category', 'deleted_at' => null])
            ->orderBy('id', 'desc')
            ->get();

        foreach($querys as $query)
        {
            $query_rule_name = TBLRuleName::
                where([
                    'rule_category_id' => $query->id,
                    'deleted_at' => null
                    // 'status' => 'Y'
                ])
                ->select(
                    'id',
                    'file_name',
                    'rule_name',
                    'description',
                    'severity',
                    'status'
                )
                ->get();

            $query['arr_rule_name'] = $query_rule_name;
        }

        // dd($querys);

        return DataTables::of($querys)
            ->addIndexColumn()
            ->editColumn('test', function($querys){
                return '';
            })
            ->editColumn('c_checkbox', function($querys){
                $html = '';

                $html .= '
                    <label>
                        <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                        <span class="label-text"></span>
                    </label>
                '; 

                return $html;
            })
            ->editColumn('c_file_name', function($querys){
                $html = '';

                $arr = [];
                foreach ($querys->arr_rule_name as $arr_rule_name)
                {
                    $arr[] = '<div style="margin: 8px;">'. $arr_rule_name->file_name .'</div>';
                }

                return $arr ? implode('', $arr) : '-';
            })
            ->editColumn('c_rule_name', function($querys){
                $html = '';

                $arr = [];
                foreach ($querys->arr_rule_name as $arr_rule_name)
                {
                    $arr[] = '<div style="margin: 8px;">'.$arr_rule_name->rule_name.'</div>';
                }

                return $arr ? implode('', $arr) : '-';
            })
            ->editColumn('c_description', function($querys){
                $html = '';

                $arr = [];
                foreach ($querys->arr_rule_name as $arr_rule_name)
                {
                    $arr[] = '<div style="margin: 8px;">'. ( $arr_rule_name->description ? $arr_rule_name->description : ' - ' ) .'</div>';
                }

                return $arr ? implode('', $arr) : '-';
            })
            ->editColumn('c_severity', function($querys){
                $html = '';

                if(count(@$querys->arr_rule_name) > 0)
                {
                    foreach ($querys->arr_rule_name as $arr_rule_name)
                    {
                        // $arr[] = $arr_rule_name->severity;
                        $html .= '<div style="margin: 8px;">';
                        
                        if(@$arr_rule_name->severity == 'Critical')
                        {
                            $html .= '<span class="badge" style="background-color: #b93624;">Critical</span>';
                        }
                        else if(@$arr_rule_name->severity == 'High')
                        {
                            $html .= '<span class="badge" style="background-color: #fcc838;">High</span>';
                        }
                        else if(@$arr_rule_name->severity == 'Medium')
                        {
                            $html .= '<span class="badge" style="background-color: #f2ff15;color: #333;">Medium</span>';
                        }
                        else if(@$arr_rule_name->severity == 'Low')
                        {
                            $html .= '<span class="badge" style="background-color: #409967;">Low</span>';
                        }
                        else if(@$arr_rule_name->severity == 'Information')
                        {
                            $html .= '<span class="badge" style="background-color: #00dcff;">Information</span>';
                        }
                        else
                        {
                            $html .= '<span class="badge"> No Severity </span>';
                        }

                        $html .= '</div>';
    
                        // $html .= '<br>';
                    }
                }
                else
                {
                    $html = ' - ';
                }

                return $html;
            })
            ->editColumn('c_status', function($querys){
                $html = '';

                if(count(@$querys->arr_rule_name) > 0)
                {
                    foreach ($querys->arr_rule_name as $arr_rule_name)
                    {
                        // $arr[] = $arr_rule_name->severity;
                        
                        $html .= '
                            <label class="switch">
                                <input type="checkbox" id="status_rule_'.$arr_rule_name->id.'" name="status_rule_'.$arr_rule_name->id.'" 
                        ';

                        if(@$arr_rule_name->status == 'Y')
                        {
                            $html .= 'checked';
                        }

                        $html .=  ' value="1" onchange="update_status_rule('.$arr_rule_name->id.')">
                                <span></span>
                            </label>          
                        '; 
    
                        $html .= '<br>';
                    }
                }
                else
                {
                    $html = ' - ';
                }
                
                // $html .= '
                //     <label class="switch">
                //         <input type="checkbox" id="status" name="status" 
                // ';

                // if($querys->status == 'Y')
                // {
                //     $html .= 'checked';
                // }

                // $html .=  ' value="1">
                //         <span></span>
                //     </label>          
                // '; 

                return $html;
            })
            ->editColumn('c_action', function($querys){
                $html = '';

                // <button type="button" class="btn btn-info btn-xs"><i class="fas fa-edit"></i></button>
                $html .= '
                    <a href="'.route('agentmanagement.agent_rule_edit', ['id' => $querys->id]).'" class="btn btn-info btn-xs" data-toggle="ajaxModal">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="'.route('agentmanagement.modal_category_delete', ['id' => $querys->id]).'" class="btn btn-danger btn-xs" data-toggle="ajaxModal">
                        <i class="fas fa-trash-alt"></i>
                    </a>               
                '; 

                return $html;
            })
            ->rawColumns(['c_checkbox', 'c_file_name', 'c_rule_name', 'c_description', 'c_severity', 'c_status', 'c_action'])
            ->make(true);
    }

    public function tbl_category_rule(Request $request)
    {
        $querys = TBLRuleCategory::
            where(['mode' => 'category', 'deleted_at' => null])
            ->orderBy('id', 'desc')
            ->get();

        return DataTables::of($querys)
            ->addIndexColumn()
            ->editColumn('test', function($querys){
                return '';
            })
            ->editColumn('c_status', function($querys){
                $html = '';

                $html .= '
                    <label class="switch">
                        <input type="checkbox" id="status_'.$querys->id.'" name="status_'.$querys->id.'" 
                ';

                if($querys->status == 'Y')
                {
                    $html .= 'checked';
                }

                $html .=  ' value="1" onchange="update_status_category('.$querys->id.')">
                        <span></span>
                    </label>          
                '; 

                return $html;
            })
            ->editColumn('c_action', function($querys){
                $html = '';

                // <button type="button" class="btn btn-info btn-xs" onclick="edit_category('.$querys->id.')"><i class="fas fa-edit"></i></button>
                $html .= '
                    <a href="'.route('agentmanagement.category_edit', ['id' => $querys->id]).'" class="btn btn-info btn-xs" data-toggle="ajaxModal">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="'.route('agentmanagement.modal_category_delete', ['id' => $querys->id]).'" class="btn btn-danger btn-xs" data-toggle="ajaxModal">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                '; 
                    // <button type="button" class="btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></button>                

                return $html;
            })
            ->rawColumns(['c_status', 'c_action'])
            ->make(true);
    }

    public function tbl_extention_rule(Request $request)
    {
        $querys = TBLRuleCategory::
            where(['mode' => 'extention', 'deleted_at' => null])
            ->orderBy('id', 'desc')
            ->get();

        return DataTables::of($querys)
            ->addIndexColumn()
            ->editColumn('test', function($querys){
                return '';
            })
            ->editColumn('c_status', function($querys){
                $html = '';

                $html .= '
                    <label class="switch">
                        <input type="checkbox" id="extension_status_'.$querys->id.'" name="extension_status_'.$querys->id.'" 
                ';

                if($querys->status == 'Y')
                {
                    $html .= 'checked';
                }

                $html .=  ' value="1" onchange="update_status_category('.$querys->id.')">
                        <span></span>
                    </label>          
                '; 

                return $html;
            })
            ->editColumn('c_action', function($querys){
                $html = '';

                // <button type="button" class="btn btn-info btn-xs"><i class="fas fa-edit"></i></button>
                // <button type="button" class="btn btn-danger btn-xs"><i class="fas fa-trash-alt"></i></button> 

                $html .= '
                    <a href="'.route('agentmanagement.category_edit', ['id' => $querys->id]).'" class="btn btn-info btn-xs" data-toggle="ajaxModal">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="'.route('agentmanagement.modal_category_delete', ['id' => $querys->id]).'" class="btn btn-danger btn-xs" data-toggle="ajaxModal">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                '; 
                                   

                return $html;
            })
            ->rawColumns(['c_status', 'c_action'])
            ->make(true);
    }

    public function extension_insert(Request $request)
    {

        $check_extension = TBLRuleCategory::where(['name' => $request->extension_name, 'mode' => 'extention', 'deleted_at' => null])->first();

        if(!$check_extension)
        {
            $main_data = [];
            $main_data['name'] = $request->extension_name;
            $main_data['mode'] = 'extention';
            $main_data['status'] = @$request->status ? 'Y' : 'N';
    
            TBLRuleCategory::create($main_data);
    
            $response = [
                'status' => 'success',
                'message' => 'Add extension success.'
            ];
        }
        else
        {
            $response = [
                'status' => 'error',
                'message' => 'This extension already exists.'
            ];
        }

        return response()->json($response);
    }

    public function category_insert(Request $request)
    {

        $check_category = TBLRuleCategory::where(['name' => $request->category_name, 'mode' => 'category', 'deleted_at' => null])->first();

        if(!$check_category)
        {
            $main_data = [];
            $main_data['name'] = $request->category_name;
            $main_data['mode'] = 'category';
            $main_data['status'] = @$request->status ? 'Y' : 'N';
    
            TBLRuleCategory::create($main_data);
    
            $response = [
                'status' => 'success',
                'message' => 'Add category success.'
            ];
        }
        else
        {
            $response = [
                'status' => 'error',
                'message' => 'This category already exists.'
            ];
        }

        return response()->json($response);
    }

    public function category_edit(Request $request)
    {
        $edit = true;

        $query = TBLRuleCategory::where(['id' => $request->id])->first();

        return view('agentmanagement::modal.edit_category')->with(compact('edit', 'query'));   
    }

    public function category_update(Request $request)
    {
        $main_data = [];
        $main_data['name'] = $request->edit_category_name;
        $main_data['status'] = @$request->status ? 'Y' : 'N';

        TBLRuleCategory::where(['id' => $request->hd_id])->update($main_data);

        $response = [
            'status' => 'success',
            'message' => 'Update category success.'
        ];

        return response()->json($response);
    }

    public function modal_category_delete(Request $request)
    {
        $query = TBLRuleCategory::where(['id' => $request->id])->first();

        return view('agentmanagement::modal.delete_category')->with(compact('query'));   
    }

    public function category_delete(Request $request)
    {
        $id = $request->hd_delete_id;

        $date_now = date('Y-m-d H:i:s');

        TBLRuleCategory::where(['id' => $id])->update(['deleted_at' => $date_now, 'deleted_by' => Auth::user()->id]);

        $arr_rule_id = TBLRuleName::where(['rule_category_id' => $id])->pluck('id')->toArray();

        // dd($arr_rule_id);

        TBLRuleName::where(['rule_category_id' => $id])->update(['deleted_at' => $date_now, 'deleted_by' => Auth::user()->id]);
        TBLRuleFiles::where(['rule_category_id' => $id])->update(['deleted_at' => $date_now, 'deleted_by' => Auth::user()->id]);
        RuleNameSite::whereIn('rule_id', $arr_rule_id)->update(['deleted_at' => $date_now, 'deleted_by' => Auth::user()->id]);

        $response = [
            'status' => 'success',
            'message' => 'Delete category success.'
        ];

        return response()->json($response);
    }

    public function status_category_update(Request $request)
    {
        TBLRuleCategory::where(['id' => $request->id])->update(['status' => $request->chk_status == 1 ? 'Y' : 'N']);

        $response = [
            'status' => 'success',
            'message' => 'Update status success.'
        ];

        return response()->json($response);
    }

    public function status_rule_update(Request $request)
    {
        TBLRuleName::where(['id' => $request->id])->update(['status' => $request->chk_status == 1 ? 'Y' : 'N']);

        $response = [
            'status' => 'success',
            'message' => 'Update status success.'
        ];

        return response()->json($response);
    }

    public function get_select_category_rule(Request $request)
    {
        $select_category = TBLRuleCategory::where([
                'mode' => 'category', 
                'status' => 'Y', 
                'deleted_at' => null
            ])
            ->select('id', 'name')
            ->get()
            ->toArray();

        $response = [
            'select_category' => $select_category
        ];

        return response()->json($response);
    }

    public function agent_rule_insert(Request $request)
    {
        // dd($request->all());

        try
        {
            $message = [
                'name.required' => 'Catagory name is required.',
                'file_rule_name.required' => 'File rule is required.',
                'detail.required' => 'File is not data'
            ];

            $validate = Validator::make($request->all(), [
                'name' => 'required',
                'file_rule_name' => 'required',
                'detail' => 'required'
            ], $message);

            if($validate->fails())
            {
                $validate = $validate->getMessageBag()->toArray();

                return response()->json([
                    'status' => '422',
                    'errors' => $validate,
                    'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน.'
                ]);
            }
            else
            {
            
                // $category_data = [];
                // $category_data['name'] = @$request->name;
                // $category_data['status'] = @$request->status ? ( $request->status == 1 ? 'Y' : 'N' ) : '';

                // $id_catagory = TBLRuleCategory::create($category_data)->id;

                if(@$request->file_rule_name)
                {
                    $path = public_path('rule_files/');
        
                    if(!File::isDirectory($path)){
                        File::makeDirectory($path, 0777, true, true);
                    }
        
                    $fileFinalName = $request->file_rule_name->getClientOriginalName();
                    $fileFinalName_explode = explode('.', $fileFinalName);
                    $path_save = 'rule_files/';
                    $request->file_rule_name->move($path_save, $fileFinalName);

                    $rule_file_data = [];
        
                    $rule_file_data['rule_category_id'] = @$request->name;
                    $rule_file_data['path'] = $path_save.$fileFinalName;
                    $rule_file_data['rule_name'] = $fileFinalName_explode[0];
                    $rule_file_data['version'] = '0';
                    $rule_file_data['transaction_download_client'] = 1;
        
                    $id_file = TBLRuleFiles::create($rule_file_data)->id;

                    // if(@$request->detail)
                    // {
                    //     foreach($request->detail as $detail)
                    //     {
                    //         $detail_rule_name = $detail;
                    //         // $detail_rule_name['rule_category_id'] = @$id_catagory;
                    //         $detail_rule_name['rule_category_id'] = @$request->name;
                    //         $detail_rule_name['rule_file_id'] = @$id_file;
                    //         $detail_rule_name['status'] = @$request->status ? ( $request->status == 1 ? 'Y' : 'N' ) : '';
        
                    //         $id_rule = TBLRuleName::create($detail_rule_name)->id;

                    //         $detail_rule_site = [];
                    //         $detail_rule_site['site_id'] = Auth::user()->site_id;
                    //         $detail_rule_site['rule_id'] = $id_rule;
                    //         $detail_rule_site['create_by'] = Auth::user()->id;
                    //         $detail_rule_site['update_by'] = Auth::user()->id;

                    //         RuleNameSite::create($detail_rule_site);
                    //     }
                    // }

                    if(@$request->site)
                    {
                        if(in_array('all', $request->site))
                        {
                            $arr_site = DB::table('site')->select('id')->get();

                            if(@$request->detail)
                            {
                                foreach($request->detail as $detail)
                                {
                                    $detail_rule_name = $detail;
                                    // $detail_rule_name['rule_category_id'] = @$id_catagory;
                                    $detail_rule_name['rule_category_id'] = @$request->name;
                                    $detail_rule_name['rule_file_id'] = @$id_file;
                                    $detail_rule_name['status'] = @$request->status ? ( $request->status == 1 ? 'Y' : 'N' ) : '';
                                    $detail_rule_name['created_by'] = Auth::user()->id;
                                    $detail_rule_name['updated_by'] = Auth::user()->id;
                
                                    $id_rule = TBLRuleName::create($detail_rule_name)->id;
        
                                    foreach($arr_site as $site)
                                    {
                                        $detail_rule_site = [];
                                        $detail_rule_site['site_id'] = $site->id;
                                        $detail_rule_site['rule_id'] = $id_rule;
                                        $detail_rule_site['create_by'] = Auth::user()->id;
                                        $detail_rule_site['update_by'] = Auth::user()->id;
            
                                        RuleNameSite::create($detail_rule_site);
                                    }
                                }
                            }
                        }
                        else if(!in_array('all', $request->site) && count($request->site) > 0)
                        {
                            if(@$request->detail)
                            {
                                foreach($request->detail as $detail)
                                {
                                    $detail_rule_name = $detail;
                                    // $detail_rule_name['rule_category_id'] = @$id_catagory;
                                    $detail_rule_name['rule_category_id'] = @$request->name;
                                    $detail_rule_name['rule_file_id'] = @$id_file;
                                    $detail_rule_name['status'] = @$request->status ? ( $request->status == 1 ? 'Y' : 'N' ) : '';
                                    $detail_rule_name['created_by'] = Auth::user()->id;
                                    $detail_rule_name['updated_by'] = Auth::user()->id;
                
                                    $id_rule = TBLRuleName::create($detail_rule_name)->id;
        
                                    foreach($request->site as $site)
                                    {
                                        $id_site = DB::table('site')->where(['code' => $site])->first()->id;

                                        $detail_rule_site = [];
                                        $detail_rule_site['site_id'] = $id_site;
                                        $detail_rule_site['rule_id'] = $id_rule;
                                        $detail_rule_site['create_by'] = Auth::user()->id;
                                        $detail_rule_site['update_by'] = Auth::user()->id;
            
                                        RuleNameSite::create($detail_rule_site);
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        if(@$request->detail)
                        {
                            foreach($request->detail as $detail)
                            {
                                $detail_rule_name = $detail;
                                // $detail_rule_name['rule_category_id'] = @$id_catagory;
                                $detail_rule_name['rule_category_id'] = @$request->name;
                                $detail_rule_name['rule_file_id'] = @$id_file;
                                $detail_rule_name['status'] = @$request->status ? ( $request->status == 1 ? 'Y' : 'N' ) : '';
                                $detail_rule_name['created_by'] = Auth::user()->id;
                                $detail_rule_name['updated_by'] = Auth::user()->id;
            
                                $id_rule = TBLRuleName::create($detail_rule_name)->id;
    
                                $detail_rule_site = [];
                                $detail_rule_site['site_id'] = '';
                                $detail_rule_site['rule_id'] = $id_rule;
                                $detail_rule_site['create_by'] = Auth::user()->id;
                                $detail_rule_site['update_by'] = Auth::user()->id;
    
                                RuleNameSite::create($detail_rule_site);
                            }
                        }
                    }

                }

                $response = [
                    'status' => 'success',
                    'message' => 'Success!! | '
                ];
            }
        }
        catch (Exception $e)
        {
            $response = [
                'status' => 'error',
                'message' => 'ไม่สำเร็จ!!! | มีบางอย่างผิดพลาด กรุณาแจ้งเจ้าหน้าที่.',
                'ms' => $e->getMessage()
            ];
        }

        return response()->json($response);
    }

    public function agent_rule_edit(Request $request)
    {
        $edit = true;

        $query = TBLRuleCategory::where(['id' => $request->id])->first();

        $query_rule = TBLRuleName::where(['rule_category_id' => $query->id, 'deleted_at' => null])->get();

        $query_site = DB::table('site')->select('code', 'name')->pluck('name', 'code')->toArray();

        return view('agentmanagement::modal.edit_rule')->with(compact('edit', 'query', 'query_rule', 'query_site'));   
    }

    public function agent_rule_update(Request $request)
    {
        foreach($request->detail as $detail)
        {

            // dd($detail);

            $main_data = [];
            $main_data['description'] = $detail['description'];
            $main_data['severity'] = $detail['severity'];
            $main_data['updated_by'] = Auth::user()->id;

            TBLRuleName::where(['id' => $detail['id']])->update($main_data);
        }

        TBLRuleName::where(['id' => $request->hd_id])->update(['status' => $request->status == 1 ? 'Y' : 'N']);

        $response = [
            'status' => 'success',
            'message' => 'Update rule success.'
        ];

        return response()->json($response);
    }

    public function modal_agent_rule_delete(Request $request)
    {
        $query = TBLRuleCategory::where(['id' => $request->id])->first();

        return view('agentmanagement::modal.delete_rule')->with(compact('query'));   
    }

    public function agent_rule_delete(Request $request)
    {
        $id = $request->hd_delete_id;

        $date_now = date('Y-m-d H:i:s');

        TBLRuleCategory::where(['id' => $id])->update(['deleted_at' => $date_now, 'deleted_by' => Auth::user()->id]);

        $arr_rule_id = TBLRuleName::where(['rule_category_id' => $id])->pluck('id')->toArray();

        // dd($arr_rule_id);

        TBLRuleName::where(['rule_category_id' => $id])->update(['deleted_at' => $date_now, 'deleted_by' => Auth::user()->id]);
        TBLRuleFiles::where(['rule_category_id' => $id])->update(['deleted_at' => $date_now, 'deleted_by' => Auth::user()->id]);
        RuleNameSite::whereIn('rule_id', $arr_rule_id)->update(['deleted_at' => $date_now, 'deleted_by' => Auth::user()->id]);

        $response = [
            'status' => 'success',
            'message' => 'Delete category success.'
        ];

        return response()->json($response);
    }

    public function agent_rule_get_zip(Request $request)
    {
        $filezip = zip_open($request->file_rule_name);

        // dd($filezip);

        $name_file = [];
        if ($filezip)
        {
            while ($zip_entry = zip_read($filezip))
            {
                // Name: zip_entry_name($zip_entry)

                // dd($zip_entry);

                $chk_ext = explode('.', zip_entry_name($zip_entry));

                if(@$chk_ext[1] == 'yar')
                {
                    $data = [];
                    $data['file_name'] = zip_entry_name($zip_entry);
                    $data['rule_name'] = $chk_ext[0];

                    // $chk_rule = TBLRuleName::where(['rule_category_id' => $request->name, 'rule_name' => $chk_ext[0]])->get();
                    $chk_rule = TBLRuleName::where(['rule_name' => $chk_ext[0], 'deleted_at' => null])->get();

                    if(count($chk_rule) > 0)
                    {
                        $data['status'] = 1;
                    }
                    else
                    {
                        $data['status'] = 0;
                    }

                    $name_file[] = $data;
                }

                // if (zip_entry_open($zip, $zip_entry))
                // {
                //     // echo "File Contents:<br/>";
                //     // $contents = zip_entry_read($zip_entry);
                //     // echo "$contents<br />";
                //     zip_entry_close($zip_entry);
                // }
            }
            
            // zip_close($filezip);
        }

        $response = [
            'name_file' => $name_file
        ];

        return response()->json($response);
    }

    public function add_new_category(Request $request)
    {
        // dd($request->all());

        $check_category = TBLRuleCategory::where(['name' => $request->new_category_name, 'mode' => 'category', 'deleted_at' => null])->first();

        if(!$check_category)
        {
            $main_data = [];
            $main_data['name'] = $request->new_category_name;
            $main_data['mode'] = 'category';
            $main_data['status'] = 'Y';
    
            TBLRuleCategory::create($main_data);
    
            $select_category = TBLRuleCategory::where([
                    'mode' => 'category', 
                    'status' => 'Y', 
                    'deleted_at' => null
                ])
                ->select('id', 'name')
                ->get()
                ->toArray();
    
            $response = [
                'status' => 'success',
                'message' => 'Add category success.',
                'select_category' => $select_category
            ];
        }
        else
        {
            $response = [
                'status' => 'error',
                'message' => 'This category already exists.'
            ];
        }


        return response()->json($response);

    }

}
