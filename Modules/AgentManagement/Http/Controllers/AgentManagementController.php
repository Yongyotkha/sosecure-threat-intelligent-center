<?php

namespace Modules\AgentManagement\Http\Controllers;

use App\AgentScanLog;
use App\AgentScanFile;
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
use App\SiteAgentExtention;
use App\SiteAgentIgnore;
use App\SsdeepFile;
use App\SsdeepFileSite;
use App\SsdeepFileSiteAgentDownload;
use App\SsdeepCandidate;
use App\Services\SsdeepAutoPackService;
use App\Support\AgentScheduleInterval;
use App\AgentReleasePackage;
use App\AgentReleaseTarget;
use App\AgentReleaseEvent;
use Illuminate\Support\Facades\Schema;
use Modules\SiteSettings\Entities\Menu;
use Modules\SiteSettings\Entities\Menu_sub;
use Modules\SiteSettings\Entities\site_config_email_alert;
use Modules\SiteSettings\Entities\site_menu_permission;
use Modules\SiteSettings\Entities\site_menu_sub_permission;

use MongoDB\BSON\Regex;
use MongoDB\Client;
use MongoDB\Client as MongoClient;
use Yajra\DataTables\DataTables;

use Excel;
use App\Exports\AgentTBAlert;
use App\Exports\AgentTBAgent;

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
        $site_id = $request->site_id;

        $count = YaraLog::
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
                    ->where('status', 1)
                    ->where('ignore_flag', 'Y')
                    ->distinct()
                    ->count('agent_id');

        $data = [];
        $data['chart'][] = ['Agent', $count];
        $data['count_all'] = $count;
        
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
            $count = FXSiteAgents::
                select(
                    'id'
                )
                ->where(function ($query_site) use ($site_id) {
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
                ->count();
                    
            // $count = count($query);

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
            $sevName = $data_severity->name;
            $count = YaraLog::
                join('site', 'yara_log.site_id', 'site.id')
                ->join('site_agents', 'yara_log.agent_id', 'site_agents.id')
                ->leftjoin('rule_name', 'yara_log.rule', 'rule_name.rule_name')
                ->where('yara_log.status', 1)
                ->where(function($query) use ($site_id){
                    if($site_id != null)
                    {
                        $query->where('yara_log.site_id', $site_id);
                    }
                })
                ->where('yara_log.ignore_flag', 'Y')
                ->where(function($query) use ($sevName) {
                    if ($sevName === 'No Severity') {
                        // Neither YARA rule catalog nor ssdeep mapped severity
                        $query->where(function($q) {
                            $q->whereNull('rule_name.severity')->orWhere('rule_name.severity', '');
                        })->where(function($q) {
                            $q->whereNull('yara_log.severity')->orWhere('yara_log.severity', '');
                        });
                    } else {
                        // Prefer rule_name.severity (YARA); fall back to yara_log.severity (ssdeep score map)
                        $query->where('rule_name.severity', $sevName)
                            ->orWhere(function($q) use ($sevName) {
                                $q->where(function($q2) {
                                    $q2->whereNull('rule_name.severity')->orWhere('rule_name.severity', '');
                                })->where('yara_log.severity', $sevName);
                            });
                    }
                })
                ->count();

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
        $query_rule = YaraLog::select(
                'rule',
                DB::raw('count(*) as total')
            )
            ->orderBy('total', 'desc')
            ->groupBy('rule')
            ->limit(10)
            ->get();

        $data = [];
        foreach($query_rule as $data_rule)
        {
            $count = YaraLog::select(
                    'id'
                )
                ->where(function ($query_site) use ($site_id) {
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
                ->count();

            // $count = count($query_rule);

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
            $count = YaraLog::select(
                    'id'
                )
                ->where(function ($query_site) use ($site_id) {
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
                ->count();

            // $count = count($query_timeline);

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

        $count_agent = FXSiteAgents::select(
                'id'
            )
            ->where(function ($query_site) use ($site_log_id) {
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
            ->count();
        // $count_agent = count($query_agent);

        // ------------------------- old --------------------------
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

        // ------------------------- base -------------------------
        // $query_alert = YaraLog::
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
        //         ->where(['deleted_at' => null])
        //         ->where('ignore_flag', 'Y')
        //         ->get();
        // $count_alert = count($query_alert);

        // ---------------------------- new --------------------------------
        $count_alert = YaraLog::
                    join('site', 'yara_log.site_id', 'site.id')
                    ->join('site_agents', 'yara_log.agent_id', 'site_agents.id')
                    ->leftjoin('rule_name', 'yara_log.rule', 'rule_name.rule_name')
                    ->select(
                        'site.name as site_name',
                        'site.logo as site_logo',
                        'site_agents.ip_private as site_agents_ip_private',
                        'yara_log.id as agent_alerts_id',
                        'yara_log.rule as agent_alerts_rule',
                        'yara_log.status as agent_alerts_status',
                        'yara_log.created_at as agent_alerts_created',
                        'yara_log.device_name',
                        'yara_log.first_scan',
                        'yara_log.last_scan',
                        'rule_name.description as agent_alerts_description',
                        'rule_name.severity as severity_status'
                    )
                    ->where('yara_log.status', 1)
                    ->where(function($query) use ($site_log_id ){
                        if($site_log_id  != null)
                        {
                            $query->where('site_id', $site_log_id );
                        }
                    })
                    ->where('yara_log.ignore_flag', 'Y')
                    ->orderBy('yara_log.last_scan', 'desc')              
                    ->count();
        // $count_alert = count($query_alert);

        $count_log = FXAgentLogs::select(
                'id'
            )
            ->where(function ($query_site) use ($site_log_id) {
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
            ->count();
        // $count_log = count($query_log);

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
            'count_agent' => number_format($count_agent),
            'count_alert' => number_format($count_alert),
            'count_log' => number_format($count_log),
            'count_rule' => number_format($count_rule)
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
            leftjoin('site', 'agent_scan_log.site_id', 'site.id')
            ->leftjoin('site_agents', 'agent_scan_log.agent_id', 'site_agents.id')
            ->orderBy('agent_scan_log.first_scan', 'desc')
            ->select(
                'site.name as site_name',
                'site.logo as site_logo',
                'site.ip_key as site_ip_key',
                'site_agents.ip_private as site_agents_ip_private',
                'agent_scan_log.mode',
                'agent_scan_log.first_scan',
                'agent_scan_log.last_scan',
                'agent_scan_log.description',
                'agent_scan_log.created_at',
                'agent_scan_log.updated_at'
            )
            ->whereNotNull('site_agents.ip_private')
            ->limit(5)
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
        $query = YaraLog::join('site', 'yara_log.site_id', 'site.id')
            ->join('site_agents', 'yara_log.agent_id', 'site_agents.id')
            ->leftjoin('os_type', 'site_agents.os_type', 'os_type.id')
            ->leftjoin('rule_name', 'yara_log.rule', 'rule_name.rule_name')
            ->select(
                'site.name as site_name',
                'site.logo as site_logo',
                'site_agents.ip_private as site_agents_ip_private',
                'site_agents.os_description as site_agents_os_description',
                'yara_log.id as agent_alerts_id',
                'yara_log.rule as agent_alerts_rule',
                'yara_log.status as agent_alerts_status',
                'yara_log.created_at as agent_alerts_created',
                'yara_log.updated_at as agent_alerts_updated',
                'yara_log.device_name',
                'yara_log.first_scan',
                'yara_log.last_scan',
                'yara_log.channel',
                'yara_log.ignore_flag',
                'yara_log.description as yara_description',
                'yara_log.severity as log_severity',
                'rule_name.description as agent_alerts_description',
                'rule_name.severity as severity_status',
                'os_type.name as os_type_name',
                'yara_log.path'
            )
            ->where('yara_log.status', 1)
            ->whereNull('site_agents.deleted_at')
            ->where(function($query) use ($site_id ){
                if($site_id  != null && $site_id != ''){
                    $query->where('yara_log.site_id', $site_id );
                }
            })
            ->where(function($query) use ($keyword_search){
                if($keyword_search != null) {
                    $query->where('site.name', 'like', '%'.$keyword_search.'%')
                        ->orwhere('yara_log.description', 'like', '%'.$keyword_search.'%')
                        ->orwhere('yara_log.device_name', 'like', '%'.$keyword_search.'%')
                        ->orwhere('yara_log.path', 'like', '%'.$keyword_search.'%')
                        ->orwhere('site_agents.ip_private', 'like', '%'.$keyword_search.'%');     
                }
            })
            // ->where('yara_log.ignore_flag', 'Y')
            ;
            // ->get();
          
        // dd($query);

        if($start_date_input != null && $end_date_input != null)
        {
            $start_date = date('Y-m-d', strtotime($start_date_input));
            $end_date = date('Y-m-d', strtotime($end_date_input));
            
            $query->whereDate('yara_log.last_scan', '>=', $start_date)
                  ->whereDate('yara_log.last_scan', '<=', $end_date);
        }

        if($request->filter_alert_rule != null)
        {
            $query->where('yara_log.rule', 'like', '%'.$request->filter_alert_rule.'%');
        }

        if($request->filter_alert_des != null)
        {
            $query->where('rule_name.description', 'like', '%'.$request->filter_alert_des.'%');
        }

        // if($request->check_alert != null)
        // {
        //     $query->where('agent_alerts.incident', $request->check_alert);
        // }

        if($request->check_alert_severity != null)
        {
            $sev = $request->check_alert_severity;
            $query->where(function($q) use ($sev) {
                $q->where('rule_name.severity', $sev)
                  ->orWhere(function($q2) use ($sev) {
                      $q2->where(function($q3) {
                          $q3->whereNull('rule_name.severity')->orWhere('rule_name.severity', '');
                      })->where('yara_log.severity', $sev);
                  });
            });
        }

        if(@$request->check_alert_ignore == 'all' || @$request->check_alert_ignore == '1')
        {
            if($request->check_alert_ignore == 'all')
            {
                $query->whereIn('yara_log.ignore_flag', ['Y','N']);
            }
            else if($request->check_alert_ignore == '1')
            {
                $query->where('yara_log.ignore_flag', 'N');
            }
        }
        else
        {
            $query->where('yara_log.ignore_flag', 'Y');
        }
        
        // return response()->json(['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

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
        ->editColumn('channel', function($query) {
            return (!empty($query->channel)) ? $query->channel : 'yara';
        })
        ->addColumn('detail_all', function($query) {
            $desc = $query->agent_alerts_description;
            if ($desc === null || $desc === '') {
                $desc = $query->yara_description;
            }
            $html = '';
            $html .= '
                <div>
                    <strong>Rule</strong> : '.$query->agent_alerts_rule.'
                </div>

                <div>
                     <strong>Description</strong> : '.$desc.'
                </div>

                <div>
                    <strong>Path</strong> :'.$query -> path.'
                    <button type="button" onclick="copyToClipboard(\'#valuecopy_'.$query->agent_alerts_id.'\')" id="btn_copy_link_'.$query->agent_alerts_id.'" data-text="'.$query -> path.'" class="btn btn-xs btn-secondary">
                        <i class="fas fa-copy"></i>
                    </button>
                    <input type="hidden" id="valuecopy_'.$query->agent_alerts_id.'" value="'.$query -> path.'">
                </div>  

                <div>
                    <strong>Last Scan</strong> : '.$query->last_scan.'
                </div>

                <div>
                    <strong>IP</strong> : '.$query->site_agents_ip_private.'
                </div>

                <div>
                    <strong>OS Type</strong> : '.$query->os_type_name.'
                </div>

                <div>
                    <strong>OS Description</strong> : '.$query->site_agents_os_description.'
                </div>
            ';
            // <div>
            //     <strong>Start Date</strong> : '.$query->first_scan.'
            // </div>
            return $html;
        })
        ->addColumn('severity_status', function($query) {
            $sev = $query->severity_status;
            if ($sev === null || $sev === '') {
                $sev = $query->log_severity;
            }
            $html = '';
                    if($sev == 'Critical')
                    {
            $html .= '<span class="badge" style="background-color: #b93624;">Critical</span>';
                    }
                    else if($sev == 'High')
                    {
            $html .= '<span class="badge" style="background-color: #fcc838;">High</span>';
                    }
                    else if($sev == 'Medium')
                    {
            $html .= '<span class="badge" style="background-color: #f2ff15;color: #333;">Medium</span>';
                    }
                    else if($sev == 'Low')
                    {
            $html .= '<span class="badge" style="background-color: #409967;">Low</span>';
                    }
                    else if($sev == 'Information')
                    {
            $html .= '<span class="badge" style="background-color: #00dcff;">Information</span>';
                    }
                    else
                    {
            $html .= '<span class="badge">No Severity</span>';
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
        // ->addColumn('device_name', function($query) {
        //     $html = '';
        //     $html .= '  
            
            
        //     <div class="box-tooltip">
        //         <span data-toggle="tooltip" data-placement="right" data-html="true" title="" data-original-title="'.$query -> device_name.'">
        //             <button class="btn btn-secondary"><i class="fas fa-file"></i></button>
        //         </span>
        //     </div>  
        //     ';
        //     return $html;
        // })
        
        ->addColumn('action', function($query) {
            $html = '';

            $class_btn_append = '';
            $class_icon_append = '';
            if($query->ignore_flag == 'Y')
            {
                $class_btn_append = 'btn-danger';
                $class_icon_append = 'fas fa-ban';
            }
            else
            {
                $class_btn_append = 'btn-success';
                $class_icon_append = 'fas fa-solid fa-check';
            }

            $html .= ' 
                <a href="'.route('agentmanagement.modal_agent_alert_delete', ['id' => $query->agent_alerts_id]).'"  class="btn '.$class_btn_append.' btn-xs" data-toggle="ajaxModal">
                    <i class="'.$class_icon_append.'"></i>
                </a>
            ';

            return $html;
        })
        ->rawColumns(['chk','detail_all', 'severity_status', 'chk_status', 'action'])
        ->make(true);
    }


    public function modal_agent_alert_delete(Request $request)
    {
        $query = YaraLog::where(['id' => $request->id])->first();

        if($query->ignore_flag == 'Y')
        {
            return view('agentmanagement::modal.delete_alert')->with(compact('query'));   
        }
        else
        {
            return view('agentmanagement::modal.change_delete_alert')->with(compact('query'));   
        }
    }

    public function alert_delete_id(Request $request){
        $id = $request->hd_delete_id;

        $query = YaraLog::where('id', $id)
            ->update([
                'ignore_flag' => ($request->hd_ignore_flag == 'Y' ? 'N' : 'Y')
            ]);

    }

    public function tb_agent(Request $request)
    {
        $input = $request->all();
        $start_date_input = $request->start_date;
        $end_date_input = $request->end_date;

        // dd($request->site_id);
        // dd($input);

        $agentCols = [
                        'site.name as site_name',
                        'site.logo as site_logo',
                        'os_type.name as os_type_name',
                        'site_agents.id as site_agents_id',
                        'site_agents.site_id as site_agents_site_id',
                        'site_agents.device_name as site_agents_device_name',
                        'site_agents.os_description as site_agents_os_description',
                        'site_agents.system_info as site_agents_system_info',
                        'site_agents.domain as site_agents_domain',
                        'site_agents.ip_private as site_agents_ip_private',
                        'site_agents.last_online as site_agents_last_online',
                        'site_agents.status as site_agents_status',
                        'site_agents.created_at as site_agents_created',
                        'site_agents.batchjob_everydate',
                        'site_agents.real_time_protection',
                        'site_agents.usb_protection',
                        'site_agents.login_last_online',
        ];
        foreach (['agent_version_current', 'agent_version_target', 'agent_update_status'] as $col) {
            if (Schema::hasColumn('site_agents', $col)) {
                $agentCols[] = 'site_agents.'.$col;
            }
        }

        $query = FXSiteAgents::
                    join('site', 'site_agents.site_id', 'site.id')
                    ->join('os_type', 'site_agents.os_type', 'os_type.id')
                    ->select($agentCols)
                    ->where('site_agents.deleted_at', null);

        if($request->site_id != null)
        {
            $query->where('site_agents.site_id', $request->site_id);
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

        $dupQuery = FXSiteAgents::select('site_id', 'ip_private', DB::raw('COUNT(*) as c'))
            ->whereNull('deleted_at')
            ->whereNotNull('ip_private')
            ->where('ip_private', '!=', '')
            ->groupBy('site_id', 'ip_private')
            ->having('c', '>', 1);
        if ($request->site_id != null) {
            $dupQuery->where('site_id', $request->site_id);
        }
        $dupIpSet = [];
        foreach ($dupQuery->get() as $dupRow) {
            $dupIpSet[$dupRow->site_id.'|'.$dupRow->ip_private] = (int) $dupRow->c;
        }

        // dd($query->get());
        return DataTables::of($query)
        ->editColumn('site_agents_ip_private', function ($row) use ($dupIpSet) {
            $ip = $row->site_agents_ip_private ?: '-';
            $html = e($ip);
            $key = $row->site_agents_site_id.'|'.$row->site_agents_ip_private;
            if (!empty($row->site_agents_ip_private) && isset($dupIpSet[$key])) {
                $html .= ' <span class="label label-danger" title="Duplicate IP within this site ('.$dupIpSet[$key].' agents)">duplicate</span>';
            }
            return $html;
        })
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
                        <li><a href="'.route('agentmanagement.agent_modal_view_log_data', ['id' => $query->site_agents_id]).'" data-toggle="ajaxModal"><i class="fas fa-eye"></i> View Log Data</a></li>
                        <li class="d-none"><a href="#"><i class="fas fa-eye"></i> View Log Error</a></li>
                        <li><a href="'.route('agentmanagement.agent_modal_manage_rule', ['id' => $query->site_agents_id, 'agent_id' => $query->site_agents_id, 'site_id' => $query->site_agents_site_id]).'" data-toggle="ajaxModal"><i class="fas fa-eye"></i> Manage Rule</a></li>
                        <li><a href="'.route('agentmanagement.agent_modal_control_agent', ['id' => $query->site_agents_id, 'batchjob' => $query->batchjob_everydate, 'real_time' => $query->real_time_protection, 'usb' => $query->usb_protection] ).'" data-toggle="ajaxModal"><i class="fas fa-eye"></i> Control Agent</a></li>
                    
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
        ->addColumn('custom_last_online', function($query) {
            $html = '';
            
            $html .= '<div class="status-flex mr-2">App :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . ($query['site_agents_last_online'] ? $query['site_agents_last_online'] : '-') . '</div>';
            $html .= '<div class="status-flex mr-2">Login :&nbsp;&nbsp;' . ($query['login_last_online'] ? $query['login_last_online'] : '-') . '</div>';
        
            return $html;
        })
        ->addColumn('custom_status', function($query) {
            $html = '';
            $currentTime = time();

            // Check App status
            $lastOnlineApp = strtotime($query['site_agents_last_online']);
            $onlineTimeApp = ($currentTime - $lastOnlineApp) / 60; // Difference in minutes

            if (date('Y-m-d', $lastOnlineApp) === date('Y-m-d', $currentTime)) {
                if ($onlineTimeApp <= 2) {
                    $html .= '<div class="status-flex mr-2">App :&nbsp&nbsp&nbsp&nbsp<span class="dot low"></span> Online</div>';
                } else {
                    $html .= '<div class="status-flex mr-2">App :&nbsp&nbsp&nbsp&nbsp<span class="dot critical"></span> Offline</div>';
                }
            } else {
                $html .= '<div class="status-flex mr-2">App :&nbsp&nbsp&nbsp&nbsp<span class="dot critical"></span> Offline</div>';
            }

            // Check Login status
            $login_last_online = strtotime($query['login_last_online']);
            $login_onlineTime = ($currentTime - $login_last_online) / 60; // Difference in minutes
          
            if (date('Y-m-d', $login_last_online) === date('Y-m-d', $currentTime)) {
                if ($login_onlineTime <= 2) {
                    $html .= '<div class="status-flex mr-2">Login :&nbsp&nbsp<span class="dot low"></span> Online</div>';
                } else {
                    $html .= '<div class="status-flex mr-2">Login :&nbsp&nbsp<span class="dot critical"></span> Offline</div>';
                }
            } else {
                $html .= '<div class="status-flex mr-2">Login :&nbsp&nbsp<span class="dot critical"></span> Offline</div>';
            }

            return $html;
        })
        ->addColumn('c_agent_version', function ($query) {
            $cur = isset($query->agent_version_current) ? $query->agent_version_current : '';
            $tgt = isset($query->agent_version_target) ? $query->agent_version_target : '';
            $st = isset($query->agent_update_status) ? $query->agent_update_status : '';
            $html = '<div>Cur: '.e($cur ?: '-').'</div>';
            $html .= '<div>Tgt: '.e($tgt ?: '-').'</div>';
            if ($st) {
                $html .= '<div><small>'.e($st).'</small></div>';
            }
            return $html;
        })
        ->rawColumns(['chk', 'chk_status', 'action', 'custom_status', 'custom_last_online', 'c_agent_version', 'site_agents_ip_private'])
        ->make(true);
    }

    public function tb_schedule(Request $request)
    {
        // $start_date_input = $request->start_date;
        // $end_date_input = $request->end_date;
        
        $query_schedule = AgentScanLog::
            join('site', 'agent_scan_log.site_id', 'site.id')
            ->leftjoin('site_agents', function ($join) {
                $join->on('agent_scan_log.agent_id', '=', 'site_agents.id')
                    ->whereNull('site_agents.deleted_at');
            })
            ->where('agent_scan_log.deleted_at', null)
            ->select(
                'agent_scan_log.id as scan_id',
                'agent_scan_log.agent_id',
                'agent_scan_log.site_id',
                'site.name as site_name',
                'site.logo as site_logo',
                'site.ip_key as site_ip_key',
                'site_agents.ip_private as site_agents_ip_private',
                'agent_scan_log.mode',
                'agent_scan_log.first_scan',
                'agent_scan_log.last_scan',
                'agent_scan_log.description'
            );

        if (Schema::hasColumn('agent_scan_log', 'run_id')) {
            $query_schedule->addSelect('agent_scan_log.run_id');
        }

        if($request->site_id != null)
        {
            $query_schedule->where('agent_scan_log.site_id', $request->site_id);
        }
        
        // Newest detections/scans first (last_scan, then id for same-second ties).
        $query_schedule->orderBy('agent_scan_log.last_scan', 'DESC')
            ->orderBy('agent_scan_log.id', 'DESC');
            
        return DataTables::of($query_schedule)
        ->addColumn('chk', function($query_schedule) {
            $html = '';
            $html .= '
                <label>
                    <input name="select_all" value="'.$query_schedule->scan_id.'" id="select-all" type="checkbox" class="select-chk">
                    <span class="label-text"></span>
                </label>
            ';
            return $html;
        })
        ->addColumn('c_expand', function ($row) {
            $scanId = (int) $row->scan_id;
            return '<button type="button" class="btn btn-xs btn-default btn-scan-expand" data-scan-id="'.$scanId.'" title="Show detections">'
                .'<i class="fas fa-plus"></i></button>';
        })
        ->editColumn('description', function ($row) {
            $desc = trim((string) $row->description);
            $parsed = $this->parseScanLogDescription($desc);
            if ($parsed['scanned'] === null && $parsed['skipped'] === null && $parsed['threats'] === null) {
                return e($desc !== '' ? $desc : '-');
            }

            $status = 'Scan';
            if (preg_match('/^Scan\s+(\w+)/i', $desc, $m)) {
                $status = 'Scan '.$m[1];
            }

            $html = '<span class="text-muted" style="margin-right:6px;">'.e($status).'</span>';
            if ($parsed['scanned'] !== null) {
                $html .= ' <span class="label label-info">scanned '.$parsed['scanned'].'</span>';
            }
            if ($parsed['skipped'] !== null) {
                $html .= ' <span class="label label-default">skipped '.$parsed['skipped'].'</span>';
            }
            if ($parsed['threats'] !== null) {
                $cls = ((int) $parsed['threats'] > 0) ? 'label-danger' : 'label-success';
                $html .= ' <span class="label '.$cls.'">threats '.$parsed['threats'].'</span>';
            }
            if ($parsed['yara'] !== null) {
                $html .= ' <span class="label label-primary">yara '.$parsed['yara'].'</span>';
            }
            if ($parsed['ssdeep'] !== null) {
                $html .= ' <span class="label label-warning">ssdeep '.$parsed['ssdeep'].'</span>';
            }
            return $html;
        })
        ->rawColumns(['chk', 'c_expand', 'description'])
        ->make(true);
    }

    /**
     * Parse "Scan done: scanned=N skipped=N total=N threats=N (yara=N ssdeep=N)" from agent_scan_log.description.
     *
     * @return array{scanned:?int,skipped:?int,threats:?int,yara:?int,ssdeep:?int}
     */
    protected function parseScanLogDescription($desc)
    {
        $out = ['scanned' => null, 'skipped' => null, 'threats' => null, 'yara' => null, 'ssdeep' => null];
        if (preg_match('/scanned\s*=\s*(\d+)/i', $desc, $m)) {
            $out['scanned'] = (int) $m[1];
        }
        if (preg_match('/skipped\s*=\s*(\d+)/i', $desc, $m)) {
            $out['skipped'] = (int) $m[1];
        }
        if (preg_match('/threats\s*=\s*(\d+)/i', $desc, $m)) {
            $out['threats'] = (int) $m[1];
        }
        if (preg_match('/yara\s*=\s*(\d+)/i', $desc, $m)) {
            $out['yara'] = (int) $m[1];
        }
        if (preg_match('/ssdeep\s*=\s*(\d+)/i', $desc, $m)) {
            $out['ssdeep'] = (int) $m[1];
        }
        return $out;
    }

    /**
     * Engine-scanned paths for one Scan History row (clean + infected; not skipped).
     * Paginated — Full scans can be very large.
     */
    public function scan_history_files(Request $request)
    {
        $scanId = (int) $request->input('scan_id', 0);
        $page = max(1, (int) $request->input('page', 1));
        $limit = (int) $request->input('limit', 100);
        if ($limit < 1) {
            $limit = 100;
        }
        if ($limit > 500) {
            $limit = 500;
        }
        if ($scanId <= 0) {
            return response()->json(['status' => 'error', 'message' => 'scan_id required', 'rows' => [], 'total' => 0]);
        }

        $scan = AgentScanLog::where('id', $scanId)->whereNull('deleted_at')->first();
        if (!$scan) {
            return response()->json(['status' => 'error', 'message' => 'Scan not found', 'rows' => [], 'total' => 0]);
        }

        $this->ensureAgentScanFileTable();
        if (!\Schema::hasTable('agent_scan_file')) {
            // Fallback for older agents that only reported threats.
            return $this->scan_history_threats_as_files($request, $scan, $page, $limit);
        }

        $q = AgentScanFile::query()
            ->where('agent_id', $scan->agent_id)
            ->where('site_id', $scan->site_id);

        $runId = '';
        if (\Schema::hasColumn('agent_scan_log', 'run_id')) {
            $runId = trim((string) $scan->run_id);
        }
        $matchedBy = 'time_window';
        if ($runId !== '') {
            $q->where('run_id', $runId);
            $matchedBy = 'run_id';
        } else {
            $from = $scan->first_scan ?: $scan->created_at;
            $to = $scan->last_scan ?: $scan->updated_at;
            if ($from) {
                $q->where('scanned_at', '>=', $from);
            }
            if ($to) {
                $q->where('scanned_at', '<=', \Carbon\Carbon::parse($to)->addMinutes(5));
            }
        }

        $total = (clone $q)->count();
        // Infected first, then newest.
        $rows = $q->orderByRaw("CASE WHEN result = 'infected' THEN 0 ELSE 1 END")
            ->orderBy('scanned_at', 'desc')
            ->orderBy('id', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'path' => $row->path,
                'result' => $row->result ?: 'clean',
                'rule' => $row->rule ?: '-',
                'engine' => $row->engine ?: '-',
                'score' => $row->score !== null ? $row->score : '-',
                'scanned_at' => $row->scanned_at ? \Carbon\Carbon::parse($row->scanned_at)->format('Y-m-d H:i:s') : '-',
            ];
        }

        return response()->json([
            'status' => 'success',
            'scan_id' => $scanId,
            'run_id' => $runId,
            'matched_by' => $matchedBy,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'count' => count($data),
            'rows' => $data,
        ]);
    }

    /**
     * Legacy fallback: map yara_log detections into the files response shape.
     */
    protected function scan_history_threats_as_files(Request $request, $scan, $page, $limit)
    {
        $this->ensureYaraLogRunIdColumn();
        $q = YaraLog::query()
            ->where('agent_id', $scan->agent_id)
            ->where('site_id', $scan->site_id);

        $runId = '';
        if (\Schema::hasColumn('agent_scan_log', 'run_id')) {
            $runId = trim((string) $scan->run_id);
        }
        $matchedBy = 'time_window';
        if ($runId !== '' && \Schema::hasColumn('yara_log', 'run_id')) {
            $q->where('run_id', $runId);
            $matchedBy = 'run_id';
        } else {
            $from = $scan->first_scan ?: $scan->created_at;
            $to = $scan->last_scan ?: $scan->updated_at;
            if ($from) {
                $q->where('last_scan', '>=', $from);
            }
            if ($to) {
                $q->where('last_scan', '<=', \Carbon\Carbon::parse($to)->addMinutes(5));
            }
        }

        $total = (clone $q)->count();
        $rows = $q->orderBy('last_scan', 'desc')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();
        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'path' => $row->path,
                'result' => 'infected',
                'rule' => $row->rule ?: '-',
                'engine' => $row->channel ?: 'yara',
                'score' => $row->severity ?: '-',
                'scanned_at' => $row->last_scan ? \Carbon\Carbon::parse($row->last_scan)->format('Y-m-d H:i:s') : '-',
            ];
        }

        return response()->json([
            'status' => 'success',
            'scan_id' => (int) $scan->id,
            'run_id' => $runId,
            'matched_by' => $matchedBy.'+yara_log',
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'count' => count($data),
            'rows' => $data,
        ]);
    }

    /**
     * Detections for one Scan History row (expandable child table).
     * @deprecated Prefer scan_history_files
     */
    public function scan_history_threats(Request $request)
    {
        return $this->scan_history_files($request);
    }

    /**
     * Lazy-add yara_log.run_id when migration not applied yet.
     */
    protected function ensureYaraLogRunIdColumn()
    {
        try {
            if (\Schema::hasTable('yara_log') && !\Schema::hasColumn('yara_log', 'run_id')) {
                \Schema::table('yara_log', function ($table) {
                    $table->string('run_id', 64)->nullable()->after('agent_id');
                    $table->index(['run_id', 'agent_id'], 'yara_log_run_agent');
                });
            }
        } catch (\Exception $e) {
            // ignore — endpoint still works via time fallback
        }
    }

    protected function ensureAgentScanFileTable()
    {
        try {
            if (\Schema::hasTable('agent_scan_file')) {
                return;
            }
            \Schema::create('agent_scan_file', function ($table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('site_id')->nullable()->index();
                $table->unsignedBigInteger('agent_id')->nullable()->index();
                $table->string('run_id', 64)->nullable();
                $table->text('path');
                $table->string('result', 16)->default('clean');
                $table->string('rule', 255)->nullable();
                $table->string('engine', 32)->nullable();
                $table->decimal('score', 10, 2)->nullable();
                $table->dateTime('scanned_at')->nullable();
                $table->timestamps();
                $table->index(['run_id', 'agent_id'], 'agent_scan_file_run_agent');
                $table->index(['agent_id', 'scanned_at'], 'agent_scan_file_agent_time');
            });
        } catch (\Exception $e) {
            // ignore
        }
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

    public function agent_modal_view_log_data(Request $request)
    {
        $id = (int) $request->get('id');
        $agent = FXSiteAgents::find($id);

        $agent_label = $agent && !empty($agent->device_name) ? $agent->device_name : ('Agent #'.$id);
        $agent_ip = $agent && !empty($agent->ip_private) ? $agent->ip_private : '';

        $scan_logs = collect();
        if ($id > 0) {
            $scanQuery = AgentScanLog::where('agent_id', $id)
                ->whereNull('deleted_at')
                ->orderBy('updated_at', 'desc')
                ->limit(50);
            $scan_logs = $scanQuery->get(['mode', 'first_scan', 'last_scan', 'description', 'created_at', 'updated_at']);
        }

        $alert_logs = collect();
        if ($id > 0) {
            $alert_logs = YaraLog::where('agent_id', $id)
                ->where('status', 1)
                ->orderBy('last_scan', 'desc')
                ->limit(50)
                ->get(['rule', 'path', 'last_scan', 'created_at', 'channel']);
        }

        return view('agentmanagement::modal.agent_modal_view_log_data', compact(
            'id', 'agent_label', 'agent_ip', 'scan_logs', 'alert_logs'
        ));
    }

    public function agent_modal_control_agent(Request $request)
    {
        $id = $request->id;
        $agent = FXSiteAgents::find($id);

        $batchjob = AgentScheduleInterval::normalizeDailyTime(
            $agent && $agent->batchjob_everydate ? $agent->batchjob_everydate : ($request->batchjob ?: AgentScheduleInterval::DEFAULT_BATCH_TIME),
            AgentScheduleInterval::DEFAULT_BATCH_TIME
        );
        $real_time = $agent ? $agent->real_time_protection : $request->real_time;
        $usb = $agent ? $agent->usb_protection : $request->usb;

        $attr = function ($key, $default) use ($agent) {
            if (!$agent) {
                return $default;
            }
            $attrs = $agent->getAttributes();
            if (!array_key_exists($key, $attrs) || $attrs[$key] === null || $attrs[$key] === '') {
                return $default;
            }
            return $attrs[$key];
        };

        $ti_sync = AgentScheduleInterval::normalize(
            $attr('ti_sync_everydate', AgentScheduleInterval::DEFAULT_TI_SYNC),
            AgentScheduleInterval::DEFAULT_TI_SYNC
        );
        $agent_update_schedule = AgentScheduleInterval::normalizeAgentUpdate(
            $attr('agent_update_schedule', AgentScheduleInterval::DEFAULT_AGENT_UPDATE),
            AgentScheduleInterval::DEFAULT_AGENT_UPDATE
        );
        $interval_options = AgentScheduleInterval::options();
        $agent_update_options = AgentScheduleInterval::agentUpdateOptions();
        $batch_time_options = AgentScheduleInterval::dailyTimeOptions();
        $ssdeep_enabled = $attr('ssdeep_enabled', 'Y');
        $ssdeep_threshold = (int) $attr('ssdeep_threshold', 85);
        $ssdeep_report_api = $attr('ssdeep_report_api', 'Y');
        $quarantine_on_detect = $attr('quarantine_on_detect', 'Y');
        $send_ssdeep_candidate = $attr('send_ssdeep_candidate', 'N');
        $auto_scan_on_login = $attr('auto_scan_on_login', 'N');

        $exclusion_path_list = $this->controlPathsToList($attr('exclusion_paths', ''));
        $quick_scan_path_list = $this->controlPathsToList($attr(
            'quick_scan_paths',
            '%USERPROFILE%\Downloads;%USERPROFILE%\Desktop;%TEMP%;%APPDATA%'
        ));

        $scan_extension_options = $this->controlDefaultScanExtensionOptions();
        $scan_extensions_selected = $this->controlExtensionsToList($attr(
            'scan_extensions',
            implode(',', $scan_extension_options)
        ));
        $scan_extensions_extra = implode(',', array_values(array_diff(
            $scan_extensions_selected,
            $scan_extension_options
        )));

        $config_updated_at = $attr('config_updated_at', null);
        $config_updated_at_label = '';
        if (!empty($config_updated_at)) {
            try {
                $dt = new \DateTime((string) $config_updated_at, new \DateTimeZone('UTC'));
                $dt->setTimezone(new \DateTimeZone(date_default_timezone_get() ?: 'Asia/Bangkok'));
                $config_updated_at_label = $dt->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                $config_updated_at_label = (string) $config_updated_at;
            }
        }

        return view('agentmanagement::modal.agent_modal_control_agent', compact(
            'batchjob', 'ti_sync', 'agent_update_schedule', 'interval_options', 'agent_update_options', 'batch_time_options', 'real_time', 'usb', 'id',
            'ssdeep_enabled', 'ssdeep_threshold', 'ssdeep_report_api',
            'quarantine_on_detect', 'send_ssdeep_candidate',
            'auto_scan_on_login',
            'exclusion_path_list', 'quick_scan_path_list',
            'scan_extension_options', 'scan_extensions_selected', 'scan_extensions_extra',
            'config_updated_at_label'
        ));
    }

    public function update_control_agent(Request $request){
       
        $id = $request->hd_id;
        $batch_start = AgentScheduleInterval::normalizeDailyTime(
            $request->batch_start,
            AgentScheduleInterval::DEFAULT_BATCH_TIME
        );
        $real_time = $this->controlAgentFlagToYn($request->input('real_time'));
        $usb = $this->controlAgentFlagToYn($request->input('usb'));

        $payload = [
            'batchjob_everydate' => $batch_start,
            'real_time_protection' => $real_time,
            'usb_protection' => $usb,
        ];

        if (\Schema::hasColumn('site_agents', 'ti_sync_everydate')) {
            $payload['ti_sync_everydate'] = AgentScheduleInterval::normalize(
                $request->input('ti_sync_start', AgentScheduleInterval::DEFAULT_TI_SYNC),
                AgentScheduleInterval::DEFAULT_TI_SYNC
            );
        }
        if (\Schema::hasColumn('site_agents', 'agent_update_schedule')) {
            $payload['agent_update_schedule'] = AgentScheduleInterval::normalizeAgentUpdate(
                $request->input('agent_update_schedule', AgentScheduleInterval::DEFAULT_AGENT_UPDATE),
                AgentScheduleInterval::DEFAULT_AGENT_UPDATE
            );
        }

        $putYn = function ($col, $input) use (&$payload, $request) {
            if (\Schema::hasColumn('site_agents', $col)) {
                $payload[$col] = $this->controlAgentFlagToYn($request->input($input));
            }
        };
        // Locked product policy (UI hidden): ssdeep/report/candidate On, quarantine Off.
        if (\Schema::hasColumn('site_agents', 'ssdeep_enabled')) {
            $payload['ssdeep_enabled'] = 'Y';
        }
        if (\Schema::hasColumn('site_agents', 'ssdeep_report_api')) {
            $payload['ssdeep_report_api'] = 'Y';
        }
        if (\Schema::hasColumn('site_agents', 'quarantine_on_detect')) {
            $payload['quarantine_on_detect'] = 'N';
        }
        if (\Schema::hasColumn('site_agents', 'send_ssdeep_candidate')) {
            $payload['send_ssdeep_candidate'] = 'Y';
        }
        $putYn('auto_scan_on_login', 'auto_scan_on_login');

        if (\Schema::hasColumn('site_agents', 'ssdeep_threshold')) {
            $thr = (int) $request->input('ssdeep_threshold', 85);
            if ($thr < 0) {
                $thr = 0;
            }
            if ($thr > 100) {
                $thr = 100;
            }
            $payload['ssdeep_threshold'] = $thr;
        }
        if (\Schema::hasColumn('site_agents', 'exclusion_paths')) {
            $payload['exclusion_paths'] = $this->controlNormalizePathInput($request->input('exclusion_paths'));
        }
        if (\Schema::hasColumn('site_agents', 'scan_extensions')) {
            $checked = $request->input('scan_extensions', []);
            if (!is_array($checked)) {
                $checked = [];
            }
            $extra = (string) $request->input('scan_extensions_extra', '');
            $payload['scan_extensions'] = $this->controlNormalizeExtensions(
                implode(',', array_merge($checked, [$extra]))
            );
        }
        // quick_scan_paths: UI hidden; do not overwrite from Control Agent form.
        // Control Agent save always wins over older agent local settings.
        if (\Schema::hasColumn('site_agents', 'config_updated_at')) {
            $payload['config_updated_at'] = gmdate('Y-m-d H:i:s');
        }

        FXSiteAgents::where('id', $id)->update($payload);

        return response()->json([
            'status_code' => '200'
        ]);
    }

    private function controlDefaultScanExtensionOptions()
    {
        return [
            '.exe', '.dll', '.sys', '.scr', '.com', '.pif', '.msi', '.cpl',
            '.bat', '.cmd', '.ps1', '.psm1', '.vbs', '.vbe', '.js', '.jse', '.wsf', '.wsh', '.hta',
            '.lnk', '.url', '.scf', '.reg', '.chm',
            '.php', '.phtml', '.php3', '.php4', '.php5', '.php7', '.phps', '.phar',
            '.asp', '.aspx', '.ashx', '.asmx', '.jsp', '.jspx',
            '.html', '.htm', '.shtml', '.cfm', '.cgi', '.pl', '.py', '.rb', '.sh',
            '.inc', '.tpl',
            '.docm', '.xlsm', '.pptm', '.jar',
            '.txt', '.log', '.bak', '.old', '.dat',
            '.img', '.iso',
        ];
    }

    private function controlPathsToList($value)
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return [];
        }
        $raw = str_replace(["\r\n", "\r", "\n"], ';', $raw);
        $parts = array_values(array_filter(array_map('trim', explode(';', $raw)), function ($p) {
            return $p !== '';
        }));
        // Repair values that lost ";" (legacy display/save bug).
        if (count($parts) === 1) {
            $repaired = $this->controlRepairJoinedPaths($parts[0]);
            if (!empty($repaired)) {
                $parts = $repaired;
            }
        }
        return $parts;
    }

    private function controlRepairJoinedPaths($blob)
    {
        $known = [
            '\windows',
            '\$recycle.bin',
            '\system volume information',
            '\program files (x86)',
            '\program files',
            '\programdata',
            '%USERPROFILE%\Downloads',
            '%USERPROFILE%\Desktop',
            '%TEMP%',
            '%APPDATA%',
        ];
        // Longer tokens first so "program files (x86)" wins over "program files".
        usort($known, function ($a, $b) {
            return strlen($b) - strlen($a);
        });
        $lower = strtolower($blob);
        $found = [];
        foreach ($known as $token) {
            $pos = strpos($lower, strtolower($token));
            if ($pos === false) {
                continue;
            }
            $found[] = ['pos' => $pos, 'token' => substr($blob, $pos, strlen($token))];
            // Remove match so overlapping shorter tokens are not re-found.
            $lower = substr_replace($lower, str_repeat("\0", strlen($token)), $pos, strlen($token));
        }
        if (count($found) < 2) {
            return [];
        }
        usort($found, function ($a, $b) {
            return $a['pos'] - $b['pos'];
        });
        return array_map(function ($f) {
            return $f['token'];
        }, $found);
    }

    private function controlExtensionsToList($value)
    {
        $raw = str_replace(["\r\n", "\r", "\n", ';'], ',', (string) $value);
        $out = [];
        foreach (explode(',', $raw) as $p) {
            $p = strtolower(trim($p));
            if ($p === '') {
                continue;
            }
            if ($p[0] !== '.') {
                $p = '.' . $p;
            }
            $out[] = $p;
        }
        return array_values(array_unique($out));
    }

    private function controlNormalizePathInput($value)
    {
        if (is_array($value)) {
            $parts = array_filter(array_map('trim', $value), function ($p) {
                return $p !== '';
            });
            return implode(';', $parts);
        }
        return $this->controlNormalizeList($value, ';');
    }

    private function controlNormalizeList($value, $sep = ';')
    {
        $s = str_replace(["\r\n", "\r", "\n"], $sep, (string) $value);
        $parts = array_filter(array_map('trim', explode($sep, $s)), function ($p) {
            return $p !== '';
        });
        return implode($sep, $parts);
    }

    private function controlNormalizeExtensions($value)
    {
        $s = str_replace(["\r\n", "\r", "\n", ';'], ',', (string) $value);
        $parts = [];
        foreach (explode(',', $s) as $p) {
            $p = strtolower(trim($p));
            if ($p === '') {
                continue;
            }
            if ($p[0] !== '.') {
                $p = '.' . $p;
            }
            $parts[] = $p;
        }
        return implode(',', array_values(array_unique($parts)));
    }

    /**
     * Control Agent form / API: Y|1|true → Y, else N (missing = off).
     */
    private function controlAgentFlagToYn($value)
    {
        if ($value === null || $value === '' || $value === false || $value === 0 || $value === '0' || $value === 'N') {
            return 'N';
        }
        if ($value === true || $value === 1 || $value === '1' || $value === 'Y' || $value === 'on' || $value === 'true') {
            return 'Y';
        }
        $s = strtoupper(trim((string) $value));
        return ($s === 'Y' || $s === 'TRUE' || $s === 'ON') ? 'Y' : 'N';
    }

    public function agent_modal_manage_rule(Request $request)
    {
        $agent_id = (int) ($request->get('agent_id') ?: $request->get('id'));
        $agent = FXSiteAgents::find($agent_id);
        if (!$agent) {
            return response('Agent not found', 404);
        }

        $site_id = $agent->site_id;
        $id = $agent_id;

        $ignoredIds = [];
        $ignoredSsdeepIds = [];
        if (\Schema::hasTable('site_agent_ignore')) {
            $ignoredIds = SiteAgentIgnore::where('site_id', $site_id)
                ->where('agent_id', $agent_id)
                ->where('type', 'rule')
                ->where('status', 'Y')
                ->pluck('ref_id')
                ->map(function ($v) {
                    return (int) $v;
                })
                ->toArray();
            $ignoredSsdeepIds = SiteAgentIgnore::where('site_id', $site_id)
                ->where('agent_id', $agent_id)
                ->where('type', 'ssdeep')
                ->where('status', 'Y')
                ->pluck('ref_id')
                ->map(function ($v) {
                    return (int) $v;
                })
                ->toArray();
        }

        $ruleNameSite = RuleNameSite::select(
            'rule_category.name as category_name',
            'rule_name_site.id',
            'rule_name_site.rule_id',
            'rule_name.file_name',
            'rule_name.rule_name',
            'rule_name.description',
            'rule_name.severity',
            'rule_name.status',
            'rule_name_site.create_by',
            'rule_name_site.update_by',
            'rule_name_site.created_at'
        )
            ->where('rule_name_site.site_id', $site_id)
            ->whereNull('rule_name_site.deleted_at')
            ->join('rule_name', 'rule_name.id', '=', 'rule_name_site.rule_id')
            ->join('rule_category', 'rule_category.id', '=', 'rule_name.rule_category_id')
            ->orderBy('rule_category.name', 'asc')
            ->orderBy('rule_name.rule_name', 'asc')
            ->get();

        $ssdeep_site_packs = collect();
        $ssdeep_master_packs = collect();
        $ssdeep_ready = method_exists($this, 'ssdeepTablesReady') && $this->ssdeepTablesReady();
        if ($ssdeep_ready) {
            $site_pack_ids = SsdeepFileSite::where('status', 'Y')
                ->where('site_id', $site_id)
                ->pluck('ssdeep_file_id')
                ->toArray();
            $ssdeep_site_packs = SsdeepFile::whereIn('status', ['Y', '1'])
                ->whereIn('id', !empty($site_pack_ids) ? $site_pack_ids : [0])
                ->orderBy('id', 'desc')
                ->get();
            // Master packs are site-assignment only; per-agent ignore applies to site packs.
            $ssdeep_master_packs = collect();
        }

        $agent_label = trim((string) ($agent->device_name ?: $agent->ip_private ?: ('#'.$agent_id)));

        return view('agentmanagement::modal.agent_modal_manage_rule', compact(
            'ruleNameSite',
            'ignoredIds',
            'ignoredSsdeepIds',
            'id',
            'agent_id',
            'site_id',
            'agent_label',
            'ssdeep_ready',
            'ssdeep_site_packs',
            'ssdeep_master_packs'
        ));
    }

    public function updateManageRule(Request $request)
    {
        $agent_id = (int) $request->input('agent_id');
        $site_id = $request->input('site_id');
        $rule_ids = $request->input('rule_ids', []);
        $ignore = $request->input('ignore', []);
        $ssdeepPackIds = $request->input('ssdeep_pack_ids', []);
        $ssdeepIgnore = $request->input('ssdeep_ignore', []);

        if (!$agent_id || $site_id === null || $site_id === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'agent_id and site_id required',
            ], 422);
        }

        if (!is_array($rule_ids)) {
            $rule_ids = [];
        }
        if (!is_array($ignore)) {
            $ignore = [];
        }
        if (!is_array($ssdeepPackIds)) {
            $ssdeepPackIds = [];
        }
        if (!is_array($ssdeepIgnore)) {
            $ssdeepIgnore = [];
        }

        $userId = Auth::check() ? Auth::user()->id : null;

        foreach ($rule_ids as $rawId) {
            $rule_id = (int) $rawId;
            if ($rule_id <= 0) {
                continue;
            }
            $wantIgnore = !empty($ignore[$rule_id]) || !empty($ignore[(string) $rule_id]);

            $row = SiteAgentIgnore::where('site_id', $site_id)
                ->where('agent_id', $agent_id)
                ->where('type', 'rule')
                ->where('ref_id', $rule_id)
                ->first();

            if ($wantIgnore) {
                if (empty($row)) {
                    $row = new SiteAgentIgnore();
                    $row->site_id = $site_id;
                    $row->agent_id = $agent_id;
                    $row->ref_id = $rule_id;
                    $row->type = 'rule';
                    $row->create_by = $userId;
                }
                $row->status = 'Y';
                $row->update_by = $userId;
                $row->save();
            } elseif (!empty($row)) {
                $row->status = 'N';
                $row->update_by = $userId;
                $row->save();
            }
        }

        // Ssdeep: Ignore = per-agent only (site_agent_ignore type=ssdeep). Site assignment unchanged.
        if (\Schema::hasTable('site_agent_ignore')) {
            foreach ($ssdeepPackIds as $rawPackId) {
                $packId = (int) $rawPackId;
                if ($packId <= 0) {
                    continue;
                }
                $wantIgnore = !empty($ssdeepIgnore[$packId]) || !empty($ssdeepIgnore[(string) $packId]);

                $row = SiteAgentIgnore::where('site_id', $site_id)
                    ->where('agent_id', $agent_id)
                    ->where('type', 'ssdeep')
                    ->where('ref_id', $packId)
                    ->first();

                $changed = false;
                if ($wantIgnore) {
                    if (empty($row) || $row->status !== 'Y') {
                        $changed = true;
                    }
                    if (empty($row)) {
                        $row = new SiteAgentIgnore();
                        $row->site_id = $site_id;
                        $row->agent_id = $agent_id;
                        $row->ref_id = $packId;
                        $row->type = 'ssdeep';
                        $row->create_by = $userId;
                    }
                    $row->status = 'Y';
                    $row->update_by = $userId;
                    $row->save();
                } elseif (!empty($row) && $row->status === 'Y') {
                    $row->status = 'N';
                    $row->update_by = $userId;
                    $row->save();
                    $changed = true;
                }

                // Re-queue this agent's downloads so TI sync rebuilds local store
                // without the ignored pack (site assignment stays unchanged).
                if ($changed && \Schema::hasTable('ssdeep_file_site_agent_downloads')) {
                    SsdeepFileSiteAgentDownload::where('site_id', $site_id)
                        ->where('agent_id', $agent_id)
                        ->update(['transaction_download_client' => 0]);
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Manage Rules settings saved.',
        ]);
    }


    public function agent_delete(Request $request)
    {
        $input = $request->all();
        
        $_id = $request->get('hd_delete_id');

        if(!empty($_id))
        {
            // $date_now = new UTCDateTime(strtotime(date("Y-m-d H:i:s"))*1000);

            $date_now = date('Y-m-d H:i:s');

            $payload = ['deleted_at' => $date_now];
            if (Schema::hasColumn('site_agents', 'ip_unique_key')) {
                $payload['ip_unique_key'] = null;
            }
            $update_status = FXSiteAgents::where('id', $_id)->update($payload);

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
                $label = e(($name_rule->category_name ?: '-').' - '.$name_rule->rule_name);
                $html .= '
                    <li class="item-list item--keyword" data-id="'. $name_rule->id .'">
                        <div class="left-side-item">
                            <span class="drag-handle m-r-xs"><i class="fa fa-arrows-alt"></i></span>
                            <span class="text-keyword">'. $label .'</span>
                        </div>
                        <div class="action-keyword">
                            <a href="#" class="text-white delete_rule_site_master" data-delete_rule_site_master="'. $name_rule->id .'" data-mode_delete="site"><i class="fas fa-trash-alt"></i></a>
                        </div>
                    </li>
                ';
            }
        }

        $html_master_rule = '';

        if(count($master_rule) > 0)
        {
            foreach($master_rule as $mas_rule)
            {
                $label = e(($mas_rule->category_name ?: '-').' - '.$mas_rule->rule_name);
                $html_master_rule .= '
                    <li class="item-list item--keyword" data-id="'. $mas_rule->id .'">
                        <div class="left-side-item">
                            <span class="drag-handle m-r-xs"><i class="fa fa-arrows-alt"></i></span>
                            <span class="text-keyword">'. $label .'</span>
                        </div>
                        <div class="action-keyword">
                            <a href="#" class="text-white delete_rule_site_master" data-delete_rule_site_master="'. $mas_rule->id .'" data-mode_delete="master"><i class="fas fa-trash-alt"></i></a>
                        </div>
                    </li>
                ';
            }
        }

        $select_type_extension = SiteAgentExtention::where('site_id', $site_id)->get();

        $extension_all = 1;

        if(count($select_type_extension) > 0)
        {
            $extension_all = 0;
        }

        $response = [
            'data_site' => $data_site,
            'html' => $html,
            'html_master_rule' => $html_master_rule,
            'site_name_rule' => $site_name_rule,
            'extension_all' => $extension_all
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

        $select_type_extension = SiteAgentExtention::where('site_id', $request->value_site)->pluck('extention_id')->toArray();

        if($request->value_type == 'all')
        {
            SiteAgentExtention::where('site_id', $request->value_site)->delete();
        }

        $response = [
            'select_extension' => $select_extension,
            'select_type_extension' => $select_type_extension
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

        $select_category = TBLRuleCategory::where(['status' => 'Y', 'mode' => 'category', 'deleted_at' => null])
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->toArray();
        $data['select_category'] = $select_category;

        // dd($data['site_settings']);

        // One site query for both Add Rule (code=>name) and Ssdeep (id/name/code).
        $sites = DB::table('site')->select('id', 'code', 'name')->orderBy('name')->get();
        $data['select_site'] = $sites->pluck('name', 'code')->toArray();
        $data['sites_list'] = $sites;

        $master_rule = TBLRuleName::with('get_name_category')->where(['deleted_at' => null])->get();
        $data['master_rule'] = $master_rule;

        $data['master_ssdeep'] = SsdeepFile::whereIn('status', ['Y', '1'])->orderBy('id', 'desc')->get();
        $data['ssdeep_auto_distribute'] = app(SsdeepAutoPackService::class)->isGlobalAutoDistributeEnabled();

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
                    $html .= '<span class="badge">No Severity</span>';
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
                            $html .= '<span class="badge">No Severity</span>';
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

        $ssdeepReady = method_exists($this, 'ssdeepTablesReady') && $this->ssdeepTablesReady()
            && Schema::hasTable('ssdeep_file')
            && Schema::hasColumn('ssdeep_file', 'category');

        return DataTables::of($querys)
            ->addIndexColumn()
            ->editColumn('test', function($querys){
                return '';
            })
            ->editColumn('c_yara_count', function ($row) {
                $n = TBLRuleName::where([
                    'rule_category_id' => $row->id,
                    'deleted_at' => null,
                ])->count();
                return (int) $n;
            })
            ->editColumn('c_ssdeep_count', function ($row) use ($ssdeepReady) {
                if (!$ssdeepReady) {
                    return 0;
                }
                $name = strtolower(trim((string) $row->name));
                if ($name === '') {
                    return 0;
                }
                return (int) SsdeepFile::whereRaw('LOWER(category) = ?', [$name])->count();
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

    public function update_site_extension(Request $request)
    {

        SiteAgentExtention::where('site_id', $request->value_site)->delete();

        foreach($request->arr_extension as $arr_extension)
        {
            $main_data = [];
            $main_data['site_id'] = $request->value_site;
            $main_data['agent_id'] = '';
            $main_data['extention_id'] = $arr_extension;
            $main_data['status'] = 'Y';
            $main_data['create_by'] = Auth::user()->id;
            $main_data['update_by'] = Auth::user()->id;

            SiteAgentExtention::create($main_data);
        }

        $response = [
            'status' => 'success',
            'message' => ''
        ];

        return response()->json($response);
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
                    // Always store under public/rule_files so downloadProtectedFile can serve them.
                    $path_save = 'rule_files/';
                    $request->file_rule_name->move($path, $fileFinalName);

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

    protected function ssdeepTablesReady()
    {
        return Schema::hasTable('ssdeep_file') && Schema::hasTable('ssdeep_file_site');
    }

    protected function ssdeepCandidateTableReady()
    {
        return Schema::hasTable('ssdeep_candidate');
    }

    /**
     * DataTables: auto-promote / queued ssdeep fuzzy candidates from agents.
     */
    public function ssdeep_candidate_tbl(Request $request)
    {
        if (!$this->ssdeepCandidateTableReady()) {
            return DataTables::of(collect([]))->addIndexColumn()->make(true);
        }

        // Legacy "duplicate" rows are no longer kept — purge on list load.
        try {
            app(SsdeepAutoPackService::class)->purgeLegacyDuplicateCandidates();
        } catch (\Exception $e) {
            // ignore
        }

        $siteId = (int) $request->input('site_id', 0);
        $status = trim((string) $request->input('status', ''));
        $keyword = trim((string) $request->input('keyword', ''));

        $query = SsdeepCandidate::query()->orderBy('id', 'desc');
        if ($siteId > 0) {
            $query->where('site_id', $siteId);
        }
        if ($status !== '') {
            // duplicate status retired — treat as empty result
            if (in_array($status, ['duplicate', 'skipped_duplicate', 'skipped_empty'], true)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('status', $status);
            }
        }
        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('file_name', 'like', '%'.$keyword.'%')
                    ->orWhere('path', 'like', '%'.$keyword.'%')
                    ->orWhere('rule', 'like', '%'.$keyword.'%')
                    ->orWhere('ssdeep', 'like', '%'.$keyword.'%')
                    ->orWhere('hash_sha256', 'like', '%'.$keyword.'%')
                    ->orWhere('hash_md5', 'like', '%'.$keyword.'%')
                    ->orWhere('source', 'like', '%'.$keyword.'%');
                if (Schema::hasColumn('ssdeep_candidate', 'note')) {
                    $q->orWhere('note', 'like', '%'.$keyword.'%');
                }
            });
        }

        $siteNames = DB::table('site')->pluck('name', 'id');

        return DataTables::of($query->limit(2000)->get())
            ->addIndexColumn()
            ->editColumn('c_site', function ($row) use ($siteNames) {
                $name = $siteNames[$row->site_id] ?? null;
                return e($name ?: ('#'.$row->site_id));
            })
            ->editColumn('c_agent', function ($row) {
                return $row->agent_id !== null ? e((string) $row->agent_id) : '-';
            })
            ->editColumn('c_file', function ($row) {
                $name = $row->file_name ?: basename((string) $row->path);
                $path = (string) $row->path;
                if ($path === '') {
                    return e($name ?: '-');
                }
                return '<span title="'.e($path).'">'.e($name ?: '-').'</span>';
            })
            ->editColumn('c_rule', function ($row) {
                return e($row->rule ?: '-');
            })
            ->editColumn('c_engine', function ($row) {
                return e($row->engine ?: '-');
            })
            ->editColumn('c_score', function ($row) {
                return (int) $row->score;
            })
            ->editColumn('c_ssdeep', function ($row) {
                $h = trim((string) $row->ssdeep);
                if ($h === '') {
                    return '-';
                }
                $short = strlen($h) > 28 ? substr($h, 0, 28).'…' : $h;
                return '<code title="'.e($h).'" style="font-size:11px;">'.e($short).'</code>';
            })
            ->editColumn('c_source', function ($row) {
                return e($row->source ?: '-');
            })
            ->editColumn('c_status', function ($row) {
                $s = strtolower(trim((string) $row->status));
                $map = [
                    'promoted' => 'label-success',
                    'queued' => 'label-warning',
                    'failed' => 'label-danger',
                    'duplicate' => 'label-warning',
                    'skipped_duplicate' => 'label-warning',
                    'skipped_empty' => 'label-default',
                    'skipped_disabled' => 'label-default',
                ];
                $cls = $map[$s] ?? 'label-info';
                $label = $row->status ?: '-';
                if ($s === 'skipped_duplicate') {
                    $label = 'duplicate';
                }
                return '<span class="label '.$cls.'">'.e($label).'</span>';
            })
            ->editColumn('c_note', function ($row) {
                $note = '';
                if (Schema::hasColumn('ssdeep_candidate', 'note')) {
                    $note = trim((string) $row->note);
                }
                if ($note === '') {
                    return '-';
                }
                $short = mb_strlen($note) > 60 ? mb_substr($note, 0, 60).'…' : $note;
                return '<span title="'.e($note).'">'.e($short).'</span>';
            })
            ->editColumn('c_detected', function ($row) {
                if ($row->detected_at) {
                    try {
                        return \Carbon\Carbon::parse($row->detected_at)->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        return e((string) $row->detected_at);
                    }
                }
                return $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '-';
            })
            ->rawColumns(['c_file', 'c_ssdeep', 'c_status', 'c_note'])
            ->make(true);
    }

    public function get_ssdeep_site(Request $request)
    {
        if (!$this->ssdeepTablesReady()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ssdeep tables are missing. Run ssdeep_agent_tables migration/SQL first.',
                'data_site' => null,
                'html' => '',
                'html_master' => '',
            ], 503);
        }

        $site_id = $request->search_site;
        $keyword_search = trim((string) $request->keyword_search);

        if (!$site_id) {
            $master_packs = SsdeepFile::whereIn('status', ['Y', '1'])
                ->when($keyword_search !== '', function ($q) use ($keyword_search) {
                    $q->where(function ($inner) use ($keyword_search) {
                        $inner->where('version', 'like', '%'.$keyword_search.'%')
                            ->orWhere('file_name', 'like', '%'.$keyword_search.'%')
                            ->orWhere('title', 'like', '%'.$keyword_search.'%')
                            ->orWhere('category', 'like', '%'.$keyword_search.'%')
                            ->orWhere('description', 'like', '%'.$keyword_search.'%');
                    });
                })
                ->orderBy('id', 'desc')
                ->get();

            $html_master = '';
            foreach ($master_packs as $pack) {
                $html_master .= $this->ssdeepPackListItemHtml($pack, 'master');
            }

            return response()->json([
                'status' => 'success',
                'data_site' => null,
                'html' => '',
                'html_master' => $html_master,
            ]);
        }

        $data_site = DB::table('site')->where('id', $site_id)->orWhere('code', $site_id)->first();
        if (!$data_site) {
            return response()->json([
                'status' => 'error',
                'message' => 'Site not found.',
            ], 404);
        }

        $site_pack_ids = SsdeepFileSite::where('status', 'Y')
            ->where(function($q) use ($data_site) {
                $q->where('site_id', $data_site->id)->orWhere('site_id', $data_site->code);
            })
            ->pluck('ssdeep_file_id')
            ->toArray();

        $site_packs = SsdeepFile::whereIn('status', ['Y', '1'])
            ->whereIn('id', !empty($site_pack_ids) ? $site_pack_ids : [0])
            ->when($keyword_search !== '', function ($q) use ($keyword_search) {
                $q->where(function ($inner) use ($keyword_search) {
                    $inner->where('version', 'like', '%'.$keyword_search.'%')
                        ->orWhere('file_name', 'like', '%'.$keyword_search.'%')
                        ->orWhere('title', 'like', '%'.$keyword_search.'%')
                        ->orWhere('category', 'like', '%'.$keyword_search.'%')
                        ->orWhere('description', 'like', '%'.$keyword_search.'%');
                });
            })
            ->orderBy('id', 'desc')
            ->get();

        $master_packs = SsdeepFile::whereIn('status', ['Y', '1'])
            ->whereNotIn('id', !empty($site_pack_ids) ? $site_pack_ids : [0])
            ->when($keyword_search !== '', function ($q) use ($keyword_search) {
                $q->where(function ($inner) use ($keyword_search) {
                    $inner->where('version', 'like', '%'.$keyword_search.'%')
                        ->orWhere('file_name', 'like', '%'.$keyword_search.'%')
                        ->orWhere('title', 'like', '%'.$keyword_search.'%')
                        ->orWhere('category', 'like', '%'.$keyword_search.'%')
                        ->orWhere('description', 'like', '%'.$keyword_search.'%');
                });
            })
            ->orderBy('id', 'desc')
            ->get();

        $html = '';
        foreach ($site_packs as $pack) {
            $html .= $this->ssdeepPackListItemHtml($pack, 'site');
        }

        $html_master = '';
        foreach ($master_packs as $pack) {
            $html_master .= $this->ssdeepPackListItemHtml($pack, 'master');
        }

        return response()->json([
            'status' => 'success',
            'data_site' => $data_site,
            'html' => $html,
            'html_master' => $html_master,
        ]);
    }

    protected function ssdeepPackListItemHtml($pack, $mode)
    {
        $label = e(method_exists($pack, 'displayLabel') ? $pack->displayLabel() : ($pack->version.' — '.($pack->file_name ?: '')));
        $metaBits = [];
        if (!empty($pack->category)) {
            $metaBits[] = (string) $pack->category;
        }
        $metaBits[] = ($pack->format ?: 'sqlite_zip');
        $metaBits[] = ((int) $pack->signature_count).' sigs';
        $meta = e(implode(' · ', $metaBits));

        return '
            <li class="item-list item--keyword" data-id="'.(int) $pack->id.'">
                <div class="left-side-item">
                    <span class="drag-handle m-r-xs"><i class="fa fa-arrows-alt"></i></span>
                    <span class="text-keyword">'.$label.' <small class="text-muted">('.$meta.')</small></span>
                </div>
                <div class="action-keyword">
                    <a href="#" class="text-white delete_ssdeep_site_master" data-delete_ssdeep_id="'.(int) $pack->id.'" data-mode_delete="'.e($mode).'"><i class="fas fa-trash-alt"></i></a>
                </div>
            </li>
        ';
    }

    public function ssdeep_assign_site(Request $request)
    {
        if (!$this->ssdeepTablesReady()) {
            return ajaxResponse(
                ['data' => '', 'message' => 'Ssdeep tables are missing. Run migration/SQL first.', 'status' => 'error'],
                true,
                Response::HTTP_OK
            );
        }

        $site_id = $request->site_id;
        $pack_id = $request->ssdeep_file_id;

        $pack = SsdeepFile::where(['id' => $pack_id, 'status' => 'Y'])->first();
        if (!$pack || !$site_id) {
            return ajaxResponse(
                ['data' => '', 'message' => 'Invalid pack or site.', 'status' => 'error'],
                true,
                Response::HTTP_OK
            );
        }

        $row = SsdeepFileSite::where(['site_id' => $site_id, 'ssdeep_file_id' => $pack_id])->first();
        if (empty($row)) {
            $row = new SsdeepFileSite();
            $row->site_id = $site_id;
            $row->ssdeep_file_id = $pack_id;
        }
        $row->status = 'Y';
        $row->transaction_download_client = 1;
        $row->save();

        // Allow agents to re-download after re-assign.
        SsdeepFileSiteAgentDownload::where('site_id', $site_id)
            ->where('ssdeep_file_id', $pack_id)
            ->update(['transaction_download_client' => 0]);

        return ajaxResponse(
            ['data' => '', 'message' => 'Ssdeep pack assigned to site.', 'status' => 'success'],
            true,
            Response::HTTP_OK
        );
    }

    /**
     * Re-queue all YARA rule packs for a site so every agent downloads them again.
     * Mirrors ssdeep_assign_site reset behavior.
     */
    public function rule_packs_requeue_site(Request $request)
    {
        $site_id = $request->site_id;
        $agent_id = $request->agent_id; // optional: one agent only
        if (!$site_id) {
            return ajaxResponse(
                ['data' => '', 'message' => 'site_id required', 'status' => 'error'],
                true,
                Response::HTTP_OK
            );
        }

        $packs = RuleFileSiteDownload::where('site_id', $site_id)
            ->where(function ($q) {
                $q->where('status', 'Y')->orWhereNull('status');
            })
            ->get();

        $reset = 0;
        $created = 0;
        foreach ($packs as $pack) {
            $q = RuleFileSiteAgentDownload::where('site_id', $site_id)
                ->where('rule_files_id', $pack->rule_files_id);
            if ($agent_id) {
                $q->where('agent_id', $agent_id);
            }
            $updated = $q->update(['transaction_download_client' => 0, 'status' => 'Y']);
            $reset += (int) $updated;

            // Ensure at least one row exists per active agent when targeting whole site.
            if (!$agent_id) {
                $agents = FXSiteAgents::where('site_id', $site_id)
                    ->where('status', 1)
                    ->whereNull('deleted_at')
                    ->pluck('id');
                foreach ($agents as $aid) {
                    $row = RuleFileSiteAgentDownload::where('site_id', $site_id)
                        ->where('agent_id', $aid)
                        ->where('rule_files_id', $pack->rule_files_id)
                        ->first();
                    if (empty($row)) {
                        $row = new RuleFileSiteAgentDownload();
                        $row->site_id = $site_id;
                        $row->agent_id = $aid;
                        $row->rule_files_id = $pack->rule_files_id;
                        $row->status = 'Y';
                        $row->transaction_download_client = 0;
                        $row->save();
                        $created++;
                    }
                }
            } elseif ($agent_id) {
                $row = RuleFileSiteAgentDownload::where('site_id', $site_id)
                    ->where('agent_id', $agent_id)
                    ->where('rule_files_id', $pack->rule_files_id)
                    ->first();
                if (empty($row)) {
                    $row = new RuleFileSiteAgentDownload();
                    $row->site_id = $site_id;
                    $row->agent_id = $agent_id;
                    $row->rule_files_id = $pack->rule_files_id;
                    $row->status = 'Y';
                    $row->transaction_download_client = 0;
                    $row->save();
                    $created++;
                }
            }
        }

        return ajaxResponse(
            [
                'data' => ['reset' => $reset, 'created' => $created, 'packs' => $packs->count()],
                'message' => "YARA packs re-queued (reset={$reset}, created={$created}).",
                'status' => 'success',
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function ssdeep_unassign_site(Request $request)
    {
        if (!$this->ssdeepTablesReady()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ssdeep tables are missing. Run migration/SQL first.',
            ]);
        }

        $site_id = $request->site_id;
        $pack_id = $request->ssdeep_file_id;
        $mode = $request->delete_mode;

        if ($mode === 'master') {
            SsdeepFile::where(['id' => $pack_id])->update(['status' => 'N']);
            SsdeepFileSite::where(['ssdeep_file_id' => $pack_id])->update(['status' => 'N']);
        } else {
            SsdeepFileSite::where(['site_id' => $site_id, 'ssdeep_file_id' => $pack_id])
                ->update(['status' => 'N']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Ssdeep pack removed.',
        ]);
    }

    public function ssdeep_assign_all_master(Request $request)
    {
        if (!$this->ssdeepTablesReady()) {
            return ajaxResponse(
                ['data' => '', 'message' => 'Ssdeep tables are missing. Run migration/SQL first.', 'status' => 'error'],
                true,
                Response::HTTP_OK
            );
        }

        $site_id = $request->site_id;
        if (!$site_id) {
            return ajaxResponse(
                ['data' => '', 'message' => 'Please select site.', 'status' => 'error'],
                true,
                Response::HTTP_OK
            );
        }

        $packs = SsdeepFile::where('status', 'Y')->get();
        foreach ($packs as $pack) {
            $row = SsdeepFileSite::where(['site_id' => $site_id, 'ssdeep_file_id' => $pack->id])->first();
            if (empty($row)) {
                $row = new SsdeepFileSite();
                $row->site_id = $site_id;
                $row->ssdeep_file_id = $pack->id;
            }
            $row->status = 'Y';
            $row->transaction_download_client = 1;
            $row->save();

            SsdeepFileSiteAgentDownload::where('site_id', $site_id)
                ->where('ssdeep_file_id', $pack->id)
                ->update(['transaction_download_client' => 0]);
        }

        return ajaxResponse(
            ['data' => '', 'message' => 'All ssdeep packs assigned.', 'status' => 'success'],
            true,
            Response::HTTP_OK
        );
    }

    public function ssdeep_delete_all_site(Request $request)
    {
        if (!$this->ssdeepTablesReady()) {
            return ajaxResponse(
                ['data' => '', 'message' => 'Ssdeep tables are missing. Run migration/SQL first.', 'status' => 'error'],
                true,
                Response::HTTP_OK
            );
        }

        $site_id = $request->site_id;
        SsdeepFileSite::where(['site_id' => $site_id])->update(['status' => 'N']);

        return ajaxResponse(
            ['data' => '', 'message' => 'All ssdeep packs removed from site.', 'status' => 'success'],
            true,
            Response::HTTP_OK
        );
    }

    public function ssdeep_pack_insert(Request $request)
    {
        try {
            @set_time_limit(120);
            @ini_set('max_execution_time', '120');

            if (!$this->ssdeepTablesReady()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ssdeep tables are missing. Run migration/SQL first.',
                ]);
            }

            $validate = Validator::make($request->all(), [
                'version' => 'required',
                'category' => 'required|string|max:64',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:2000',
                'file_ssdeep' => 'required|file',
            ], [
                'version.required' => 'Version is required.',
                'category.required' => 'Category is required.',
                'title.required' => 'Title is required.',
                'file_ssdeep.required' => 'Ssdeep pack file is required.',
            ]);

            if ($validate->fails()) {
                return response()->json([
                    'status' => '422',
                    'errors' => $validate->getMessageBag()->toArray(),
                    'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน.',
                ]);
            }

            $version = trim($request->version);
            $exists = SsdeepFile::where('version', $version)->where('status', 'Y')->first();
            if ($exists) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This version already exists.',
                ]);
            }

            $uploaded = $request->file('file_ssdeep');
            $originalName = $uploaded->getClientOriginalName();
            $ext = strtolower($uploaded->getClientOriginalExtension());

            $format = $request->format;
            if (!$format) {
                if ($ext === 'json') {
                    $format = 'json';
                } elseif ($ext === 'db') {
                    $format = 'sqlite';
                } else {
                    $format = 'sqlite_zip';
                }
            }

            $dir = public_path('ssdeep_files/');
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0777, true, true);
            }

            $safeBase = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
            $fileFinalName = $safeBase.'_'.time().'.'.$ext;
            $uploaded->move($dir, $fileFinalName);

            $fullPath = $dir.$fileFinalName;
            $sizeBytes = @filesize($fullPath) ?: 0;
            // Skip expensive SQLite full-table count on large packs (was hanging uploads).
            $sha256 = hash_file('sha256', $fullPath);
            $signatureCount = 0;
            if ($sizeBytes > 0 && $sizeBytes <= (2 * 1024 * 1024)) {
                $signatureCount = $this->countSsdeepSignatures($fullPath, $format);
            }

            // Absolute URL so agents can download without Client static mirror.
            $publicPath = url('/ssdeep_files/'.$fileFinalName);

            $pack = new SsdeepFile();
            $pack->version = $version;
            $pack->path = $publicPath;
            $pack->file_name = $originalName;
            $pack->title = trim((string) $request->input('title', '')) ?: null;
            $pack->category = strtolower(trim((string) $request->input('category', ''))) ?: null;
            $pack->description = trim((string) $request->input('description', '')) ?: null;
            $pack->format = $format;
            $pack->sha256 = $sha256;
            $pack->size_bytes = $sizeBytes;
            $pack->signature_count = $signatureCount;
            $pack->status = @$request->status ? ($request->status == 1 ? 'Y' : 'N') : 'Y';
            if (Schema::hasColumn('ssdeep_file', 'source')) {
                $pack->source = 'master';
            }
            $pack->save();

            $siteIds = [];
            if (@$request->site) {
                if (in_array('all', (array) $request->site)) {
                    $siteIds = DB::table('site')->pluck('id')->toArray();
                } else {
                    foreach ((array) $request->site as $siteVal) {
                        if (is_numeric($siteVal)) {
                            $siteIds[] = (int) $siteVal;
                        } else {
                            $siteRow = DB::table('site')->where('code', $siteVal)->first();
                            if ($siteRow) {
                                $siteIds[] = $siteRow->id;
                            }
                        }
                    }
                }
            }

            foreach (array_unique($siteIds) as $siteId) {
                $row = new SsdeepFileSite();
                $row->site_id = $siteId;
                $row->ssdeep_file_id = $pack->id;
                $row->status = 'Y';
                $row->transaction_download_client = 1;
                $row->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Ssdeep pack uploaded successfully.',
                'id' => $pack->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Upload failed: '.$e->getMessage(),
                'ms' => $e->getMessage(),
            ]);
        }
    }

    protected function countSsdeepSignatures($fullPath, $format)
    {
        try {
            $lower = strtolower((string) $format);
            if ($lower === 'json' || substr($fullPath, -5) === '.json') {
                $raw = @file_get_contents($fullPath);
                $data = json_decode($raw, true);
                if (is_array($data)) {
                    if (isset($data['signatures']) && is_array($data['signatures'])) {
                        return count($data['signatures']);
                    }
                    return count($data);
                }
                return 0;
            }

            $dbPath = $fullPath;
            $tmpUnzip = null;
            if ($lower === 'sqlite_zip' || substr($fullPath, -4) === '.zip') {
                $zip = new ZipArchive();
                if ($zip->open($fullPath) === true) {
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $name = $zip->getNameIndex($i);
                        $base = strtolower(basename($name));
                        if (substr($base, -3) === '.db' || substr($base, -8) === '.sqlite') {
                            $tmpUnzip = $fullPath.'.unzipped.db';
                            $stream = $zip->getStream($name);
                            if ($stream) {
                                $out = fopen($tmpUnzip, 'wb');
                                if ($out) {
                                    stream_copy_to_stream($stream, $out);
                                    fclose($out);
                                }
                                fclose($stream);
                                $dbPath = $tmpUnzip;
                            }
                            break;
                        }
                    }
                    $zip->close();
                }
            }

            if (!file_exists($dbPath)) {
                return 0;
            }

            $pdo = new \PDO('sqlite:'.$dbPath);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(\PDO::FETCH_COLUMN);
            $count = 0;
            foreach ($tables as $table) {
                if (in_array(strtolower($table), ['signatures', 'ssdeep', 'hashes', 'items'], true)) {
                    $count = (int) $pdo->query('SELECT COUNT(*) FROM "'.$table.'"')->fetchColumn();
                    break;
                }
            }
            if ($count === 0 && !empty($tables)) {
                $count = (int) $pdo->query('SELECT COUNT(*) FROM "'.$tables[0].'"')->fetchColumn();
            }
            if ($tmpUnzip && file_exists($tmpUnzip)) {
                @unlink($tmpUnzip);
            }
            return $count;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function ssdeep_pack_tbl(Request $request)
    {
        if (!$this->ssdeepTablesReady()) {
            return DataTables::of(collect([]))->addIndexColumn()->make(true);
        }

        $querys = SsdeepFile::orderBy('id', 'desc')->get();

        return DataTables::of($querys)
            ->addIndexColumn()
            ->editColumn('c_category', function ($row) {
                return e($row->category ?: '-');
            })
            ->editColumn('c_title', function ($row) {
                return e($row->title ?: '-');
            })
            ->editColumn('c_version', function ($row) {
                return e($row->version);
            })
            ->editColumn('c_file_name', function ($row) {
                return e($row->file_name ?: '-');
            })
            ->editColumn('c_format', function ($row) {
                return e($row->format ?: '-');
            })
            ->editColumn('c_sha256', function ($row) {
                $sha = (string) $row->sha256;
                if ($sha === '') {
                    return '-';
                }
                return '<span title="'.e($sha).'">'.e(substr($sha, 0, 12)).'…</span>';
            })
            ->editColumn('c_signature_count', function ($row) {
                return (int) $row->signature_count;
            })
            ->editColumn('c_source', function ($row) {
                $src = method_exists($row, 'packSource') ? $row->packSource() : 'master';
                if ($src === 'auto') {
                    return '<span class="label label-info" title="Promoted from Ssdeep Candidates">Auto</span>';
                }
                return '<span class="label label-primary" title="Uploaded as master data">Master</span>';
            })
            ->editColumn('c_status', function ($row) {
                $checked = $row->status == 'Y' ? 'checked' : '';
                return '
                    <label class="switch">
                        <input type="checkbox" id="ssdeep_status_'.$row->id.'" '.$checked.' value="1" onchange="update_ssdeep_pack_status('.$row->id.')">
                        <span></span>
                    </label>
                ';
            })
            ->editColumn('c_action', function ($row) {
                return '
                    <button type="button" class="btn btn-warning btn-xs btn-edit-ssdeep-pack" data-id="'.$row->id.'" onclick="edit_ssdeep_pack('.$row->id.')" title="Edit Site assignments">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-xs btn-delete-ssdeep-pack" data-id="'.$row->id.'" onclick="delete_ssdeep_pack('.$row->id.')" title="Delete pack">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                ';
            })
            ->editColumn('updated_at', function ($row) {
                return $row->updated_at ? $row->updated_at->format('Y-m-d H:i:s') : '-';
            })
            ->rawColumns(['c_sha256', 'c_source', 'c_status', 'c_action'])
            ->make(true);
    }

    public function ssdeep_pack_get(Request $request)
    {
        if (!$this->ssdeepTablesReady()) {
            return response()->json(['status' => 'error', 'message' => 'Ssdeep tables are missing.'], 404);
        }
        $pack = SsdeepFile::find((int) $request->id);
        if (!$pack) {
            return response()->json(['status' => 'error', 'message' => 'Ssdeep pack not found.'], 404);
        }
        $site_ids = SsdeepFileSite::where(['ssdeep_file_id' => $pack->id, 'status' => 'Y'])
            ->pluck('site_id')
            ->toArray();

        return response()->json([
            'status' => 'success',
            'pack' => $pack,
            'site_ids' => $site_ids,
        ]);
    }

    public function ssdeep_pack_update(Request $request)
    {
        if (!$this->ssdeepTablesReady()) {
            return response()->json(['status' => 'error', 'message' => 'Ssdeep tables missing.']);
        }
        $pack = SsdeepFile::find((int) $request->id);
        if (!$pack) {
            return response()->json(['status' => 'error', 'message' => 'Ssdeep pack not found.']);
        }

        if ($request->has('status')) {
            $pack->status = $request->status == '1' || $request->status == 'Y' ? 'Y' : 'N';
        }
        if ($request->has('title')) {
            $pack->title = trim((string) $request->input('title', '')) ?: null;
        }
        if ($request->has('category')) {
            $pack->category = strtolower(trim((string) $request->input('category', ''))) ?: null;
        }
        if ($request->has('description')) {
            $pack->description = trim((string) $request->input('description', '')) ?: null;
        }
        $pack->save();

        $sites = $request->input('site', []);
        if (!is_array($sites)) {
            $sites = [];
        }

        $allSites = DB::table('site')->pluck('id')->toArray();
        $targetSiteIds = [];

        if (in_array('all', $sites)) {
            $targetSiteIds = $allSites;
        } else {
            foreach ($sites as $siteVal) {
                if (is_numeric($siteVal)) {
                    $targetSiteIds[] = (int) $siteVal;
                } else {
                    $siteRow = DB::table('site')->where('code', $siteVal)->first();
                    if ($siteRow) {
                        $targetSiteIds[] = $siteRow->id;
                    }
                }
            }
        }
        $targetSiteIds = array_unique($targetSiteIds);

        // Deactivate unselected sites
        SsdeepFileSite::where('ssdeep_file_id', $pack->id)
            ->whereNotIn('site_id', !empty($targetSiteIds) ? $targetSiteIds : [0])
            ->update(['status' => 'N']);

        // Activate or create selected sites
        foreach ($targetSiteIds as $siteId) {
            $row = SsdeepFileSite::where(['ssdeep_file_id' => $pack->id, 'site_id' => $siteId])->first();
            if (!$row) {
                $row = new SsdeepFileSite();
                $row->ssdeep_file_id = $pack->id;
                $row->site_id = $siteId;
                $row->transaction_download_client = 1;
            }
            $row->status = 'Y';
            $row->save();

            SsdeepFileSiteAgentDownload::where('site_id', $siteId)
                ->where('ssdeep_file_id', $pack->id)
                ->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Ssdeep pack updated successfully.',
        ]);
    }

    public function ssdeep_pack_bulk_update_sites(Request $request)
    {
        if (!$this->ssdeepTablesReady()) {
            return response()->json(['status' => 'error', 'message' => 'Ssdeep tables missing.']);
        }

        $sites = $request->input('site', []);
        if (!is_array($sites)) {
            $sites = [];
        }

        $allSites = DB::table('site')->pluck('id')->toArray();
        $targetSiteIds = [];

        if (in_array('all', $sites)) {
            $targetSiteIds = $allSites;
        } else {
            foreach ($sites as $siteVal) {
                if (is_numeric($siteVal)) {
                    $targetSiteIds[] = (int) $siteVal;
                } else {
                    $siteRow = DB::table('site')->where('code', $siteVal)->first();
                    if ($siteRow) {
                        $targetSiteIds[] = $siteRow->id;
                    }
                }
            }
        }
        $targetSiteIds = array_unique($targetSiteIds);

        $activePacks = SsdeepFile::where('status', 'Y')->get();
        foreach ($activePacks as $pack) {
            SsdeepFileSite::where('ssdeep_file_id', $pack->id)
                ->whereNotIn('site_id', !empty($targetSiteIds) ? $targetSiteIds : [0])
                ->update(['status' => 'N']);

            foreach ($targetSiteIds as $siteId) {
                $row = SsdeepFileSite::where(['ssdeep_file_id' => $pack->id, 'site_id' => $siteId])->first();
                if (!$row) {
                    $row = new SsdeepFileSite();
                    $row->ssdeep_file_id = $pack->id;
                    $row->site_id = $siteId;
                    $row->transaction_download_client = 1;
                }
                $row->status = 'Y';
                $row->save();

                SsdeepFileSiteAgentDownload::where('site_id', $siteId)
                    ->where('ssdeep_file_id', $pack->id)
                    ->delete();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Bulk updated site assignments for all Ssdeep packs.',
        ]);
    }

    public function ssdeep_pack_status(Request $request)
    {
        if (!$this->ssdeepTablesReady()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ssdeep tables are missing.',
            ]);
        }

        SsdeepFile::where(['id' => $request->id])
            ->update(['status' => $request->chk_status == 1 ? 'Y' : 'N']);

        return response()->json([
            'status' => 'success',
            'message' => 'Update status success.',
        ]);
    }

    public function ssdeep_pack_auto_distribute(Request $request)
    {
        $svc = app(SsdeepAutoPackService::class);
        $on = $request->chk_status == 1
            || $request->chk_status === '1'
            || $request->chk_status === 'Y'
            || $request->enabled == 1
            || $request->enabled === '1'
            || $request->enabled === true;

        if (!$svc->setGlobalAutoDistribute($on)) {
            return response()->json([
                'status' => 'error',
                'message' => 'ไม่สามารถบันทึกการตั้งค่า Auto distribute ได้ (ตรวจสิทธิ์ DB / สร้างตาราง ssdeep_settings ไม่สำเร็จ)',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'enabled' => $on,
            'message' => $on
                ? 'Auto distribute ON — pack ใหม่จาก Candidates จะถูกแจกเข้าทุก Site อัตโนมัติ'
                : 'Auto distribute OFF — pack ใหม่จะถูกผูกเฉพาะ Site ที่ detect',
        ]);
    }

    public function ssdeep_pack_delete(Request $request)
    {
        if (!$this->ssdeepTablesReady()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ssdeep tables are missing.',
            ]);
        }

        $id = (int) $request->id;
        $pack = SsdeepFile::find($id);
        if (!$pack) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pack not found.',
            ]);
        }

        // Hard delete: remove DB row + related links + file on disk.
        $relative = '';
        if (preg_match('#ssdeep_files/(.+)$#i', (string) $pack->path, $m)) {
            $relative = 'ssdeep_files/'.$m[1];
        }
        $baseName = $pack->file_name ? basename($pack->file_name) : ($relative ? basename($relative) : '');

        SsdeepFileSiteAgentDownload::where('ssdeep_file_id', $id)->delete();
        SsdeepFileSite::where('ssdeep_file_id', $id)->delete();
        SsdeepFile::where('id', $id)->delete();

        if ($relative !== '') {
            $full = public_path($relative);
            if (is_file($full)) {
                @unlink($full);
            }
            $jsonSide = preg_replace('/\.zip$/i', '.json', $full);
            if ($jsonSide && is_file($jsonSide)) {
                @unlink($jsonSide);
            }
        } elseif ($baseName !== '') {
            $full = public_path('ssdeep_files/'.$baseName);
            if (is_file($full)) {
                @unlink($full);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Ssdeep pack deleted permanently.',
        ]);
    }

    public function ssdeep_pack_cleanup_duplicates(Request $request)
    {
        if (!$this->ssdeepTablesReady()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ssdeep tables are missing.',
            ]);
        }

        try {
            $stats = app(SsdeepAutoPackService::class)->cleanupDuplicatePacks();
            return response()->json([
                'status' => 'success',
                'message' => isset($stats['message']) ? $stats['message'] : 'Cleanup done.',
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cleanup failed: '.$e->getMessage(),
            ]);
        }
    }

    public function ssdeep_candidate_cleanup_duplicates(Request $request)
    {
        if (!$this->ssdeepCandidateTableReady()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ssdeep candidate table is missing.',
            ]);
        }

        try {
            $stats = app(SsdeepAutoPackService::class)->cleanupDuplicateCandidatesDetailed();
            return response()->json([
                'status' => 'success',
                'message' => isset($stats['message']) ? $stats['message'] : 'Candidate cleanup done.',
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cleanup failed: '.$e->getMessage(),
            ]);
        }
    }

    public function export_excel_tb_alert(Request $request){
        ini_set('memory_limit', '1024M');
        set_time_limit(0);
        $data = $request->all();
        return Excel::download(new AgentTBAlert(@$data), 'tb_alert.xlsx');
    }

    public function export_excel_tb_agent(Request $request){
        ini_set('memory_limit', '1024M');
        set_time_limit(0);
        $data = $request->all();
        return Excel::download(new AgentTBAgent(@$data), 'tb_agent.xlsx');
    }

    public function agent_releases(Request $request)
    {
        $page = "Agent Releases";
        $this->ensureAgentReleasePackageKindSchema();
        $site_settings = SiteSettings::whereNull('deleted_at')->orderBy('name')->get();
        $packages = [];
        if (Schema::hasTable('agent_release_packages')) {
            $packages = AgentReleasePackage::orderBy('id', 'desc')->get();
        }
        $targets = [];
        if (Schema::hasTable('agent_release_targets')) {
            $targets = AgentReleaseTarget::where('status', 'Y')->orderBy('id', 'desc')->get();
        }
        $globalTarget = null;
        foreach ($targets as $t) {
            if ($t->site_id === null) {
                $globalTarget = $t;
                break;
            }
        }
        return view('agentmanagement::agent_releases', compact('page','site_settings', 'packages', 'targets', 'globalTarget'));
    }

    public function agent_release_upload(Request $request)
    {
        if (!Schema::hasTable('agent_release_packages')) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTA tables are missing. Run migration 2026_07_29_143000_create_agent_release_ota_tables (or database/sql/agent_release_ota.sql).',
            ], 500);
        }

        $this->ensureAgentReleasePackageKindSchema();

        $kind = strtolower(trim((string) $request->input('kind', 'agent_binary')));
        if (!in_array($kind, ['agent_binary', 'installer'], true)) {
            $kind = 'agent_binary';
        }

        $os = strtolower(trim((string) $request->input('os', 'windows')));
        $allowedOs = AgentReleasePackage::allowedOs();
        if (!in_array($os, $allowedOs, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OS. Choose one of: '.implode(', ', $allowedOs).'.',
            ], 422);
        }
        // OTA binary path is Windows-only for now.
        if ($kind === 'agent_binary') {
            $os = 'windows';
        }

        $validate = Validator::make($request->all(), [
            'version' => 'required',
            'os' => 'required',
            // Setup ~90MB; agent binary ~30MB — allow up to 200MB after nginx/php limits.
            'file_agent' => 'required|file|max:204800',
        ], [
            'version.required' => 'Version is required.',
            'os.required' => 'OS is required.',
            'file_agent.required' => 'Package file is required.',
            'file_agent.max' => 'File is too large (max 200MB). Check PHP upload_max_filesize / post_max_size if this persists.',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status' => '422',
                'errors' => $validate->getMessageBag()->toArray(),
                'message' => 'Please complete all required fields.',
            ], 422);
        }

        if (!$request->hasFile('file_agent') || !$request->file('file_agent')->isValid()) {
            $err = $request->file('file_agent') ? $request->file('file_agent')->getErrorMessage() : 'No file received';
            return response()->json([
                'status' => 'error',
                'message' => 'Upload rejected by PHP: '.$err.'. Check upload_max_filesize and post_max_size.',
            ], 422);
        }

        $version = trim($request->version);
        $dupQuery = AgentReleasePackage::where('version', $version);
        if (Schema::hasColumn('agent_release_packages', 'kind')) {
            $dupQuery->where('kind', $kind);
        }
        if (Schema::hasColumn('agent_release_packages', 'os')) {
            $dupQuery->where('os', $os);
        }
        if ($dupQuery->first()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This version already exists for kind "'.$kind.'" / OS "'.$os.'". Use a new version, another kind, or another OS.',
            ]);
        }

        $uploaded = $request->file('file_agent');
        $originalName = $uploaded->getClientOriginalName();
        $ext = strtolower($uploaded->getClientOriginalExtension());
        $baseLower = strtolower($originalName);
        // Handle double extensions like .tar.gz
        if (substr($baseLower, -7) === '.tar.gz') {
            $ext = 'tar.gz';
        }

        $windowsExts = ['exe'];
        $linuxExts = ['zip', 'deb', 'rpm', 'whl', 'tar.gz', 'tgz', 'gz', 'bin'];
        if ($os === 'windows') {
            if (!in_array($ext, $windowsExts, true)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Windows packages must be .exe.',
                ], 422);
            }
        } else {
            if (!in_array($ext, $linuxExts, true)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Linux packages must be one of: .zip .deb .rpm .whl .tar.gz .tgz .bin',
                ], 422);
            }
        }

        $looksLikeSetup = (strpos($baseLower, 'setup') !== false
            || strpos($baseLower, 'installer') !== false);
        $uploadBytes = (int) $uploaded->getSize();

        if ($kind === 'agent_binary') {
            if ($looksLikeSetup || $uploadBytes > 60 * 1024 * 1024) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'OTA binary must be dist/insite-agent.exe (~30MB), not the Inno Setup installer. Choose package type "First-install Setup" for Setup files.',
                ]);
            }
        } elseif ($os === 'windows') {
            // Windows first-install should be Inno Setup.
            if (!$looksLikeSetup && $uploadBytes < 50 * 1024 * 1024) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Windows first-install package should be the Inno Setup exe (SOSECURE_Threat_inSight_Go_Setup_*.exe). For OTA use type "OTA agent binary".',
                ]);
            }
        }

        try {
            $dir = public_path('agent_releases/');
            if (!File::isDirectory($dir)) {
                File::makeDirectory($dir, 0777, true, true);
            }
            $safeBase = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
            // Keep .tar.gz as compound extension.
            $storeExt = $ext === 'tar.gz' ? 'tar.gz' : $ext;
            $fileFinalName = $safeBase.'_'.$os.'_'.preg_replace('/[^A-Za-z0-9._-]/', '_', $version).'_'.time().'.'.$storeExt;
            $uploaded->move($dir, $fileFinalName);
            $fullPath = $dir.$fileFinalName;
            $sizeBytes = @filesize($fullPath) ?: 0;
            $sha256 = hash_file('sha256', $fullPath);
            $publicPath = url('/agent_releases/'.$fileFinalName);

            $pack = new AgentReleasePackage();
            $pack->version = $version;
            if (Schema::hasColumn('agent_release_packages', 'kind')) {
                $pack->kind = $kind;
            }
            if (Schema::hasColumn('agent_release_packages', 'os')) {
                $pack->os = $os;
            }
            $pack->path = $publicPath;
            $pack->file_name = $originalName;
            $pack->sha256 = $sha256;
            $pack->size_bytes = $sizeBytes;
            $pack->status = 'Y';
            $pack->notes = $request->input('notes');
            $pack->save();

            // OTA target only applies to agent_binary packages.
            if ($kind === 'agent_binary' && $request->input('set_as_target') == '1') {
                $siteId = $request->input('site_id');
                if ($siteId === '' || $siteId === 'global') {
                    $siteId = null;
                }
                $this->upsertReleaseTarget($siteId, $pack);
            }

            $osLabel = ucfirst($os);
            $msg = $kind === 'installer'
                ? 'First-install package uploaded for '.$osLabel.': '.$version
                : 'OTA agent binary uploaded (Windows): '.$version;

            return response()->json([
                'status' => 'success',
                'message' => $msg,
                'package_id' => $pack->id,
                'kind' => $kind,
                'os' => $os,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Upload failed: '.$e->getMessage(),
            ], 500);
        }
    }

    public function agent_release_set_target(Request $request)
    {
        $packageId = (int) $request->input('package_id');
        $pack = AgentReleasePackage::where('id', $packageId)->where('status', 'Y')->first();
        if (!$pack) {
            return response()->json(['status' => 'error', 'message' => 'Package not found or inactive.']);
        }
        $kind = Schema::hasColumn('agent_release_packages', 'kind')
            ? strtolower(trim((string) ($pack->kind ?: 'agent_binary')))
            : 'agent_binary';
        if ($kind === 'installer') {
            return response()->json([
                'status' => 'error',
                'message' => 'Setup/installer packages are for first-install download only. Set OTA target from an agent_binary package (insite-agent.exe).',
            ]);
        }
        $siteId = $request->input('site_id');
        if ($siteId === '' || $siteId === 'global' || $siteId === null) {
            $siteId = null;
        } else {
            $siteId = (int) $siteId;
        }
        $this->upsertReleaseTarget($siteId, $pack);
        return response()->json([
            'status' => 'success',
            'message' => 'Target version set to '.$pack->version.($siteId ? ' for site #'.$siteId : ' (global)'),
        ]);
    }

    public function agent_release_toggle(Request $request)
    {
        $pack = AgentReleasePackage::find((int) $request->input('package_id'));
        if (!$pack) {
            return response()->json(['status' => 'error', 'message' => 'Package not found.']);
        }
        $pack->status = $pack->status === 'Y' ? 'N' : 'Y';
        $pack->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Package '.$pack->version.' is now '.($pack->status === 'Y' ? 'active' : 'inactive'),
            'new_status' => $pack->status,
        ]);
    }

    public function agent_release_events(Request $request)
    {
        if (!Schema::hasTable('agent_release_events')) {
            return response()->json(['data' => []]);
        }
        $q = AgentReleaseEvent::orderBy('id', 'desc')->limit(200);
        if ($request->site_id) {
            $q->where('site_id', $request->site_id);
        }
        return response()->json(['data' => $q->get()]);
    }

    private function upsertReleaseTarget($siteId, AgentReleasePackage $pack)
    {
        if (!Schema::hasTable('agent_release_targets')) {
            return;
        }
        // Deactivate previous targets for same scope.
        $q = AgentReleaseTarget::where('status', 'Y');
        if ($siteId === null) {
            $q->whereNull('site_id');
        } else {
            $q->where('site_id', $siteId);
        }
        $q->update(['status' => 'N']);

        $row = new AgentReleaseTarget();
        $row->site_id = $siteId;
        $row->package_id = $pack->id;
        $row->target_version = $pack->version;
        $row->status = 'Y';
        $row->save();

        // Mirror onto agents for visibility.
        if (Schema::hasColumn('site_agents', 'agent_version_target')) {
            $agents = FXSiteAgents::whereNull('deleted_at');
            if ($siteId !== null) {
                $agents->where('site_id', $siteId);
            }
            $agents->update(['agent_version_target' => $pack->version]);
        }
    }

    /**
     * Ensure agent_release_packages.kind + os exist and unique(version,kind,os).
     * Safe to call repeatedly (used before upload/list).
     */
    private function ensureAgentReleasePackageKindSchema()
    {
        if (!Schema::hasTable('agent_release_packages')) {
            return;
        }
        if (!Schema::hasColumn('agent_release_packages', 'kind')) {
            try {
                Schema::table('agent_release_packages', function ($table) {
                    $table->string('kind', 32)->default('agent_binary')->after('version');
                });
            } catch (\Exception $e) {
                return;
            }
        }
        if (!Schema::hasColumn('agent_release_packages', 'os')) {
            try {
                Schema::table('agent_release_packages', function ($table) {
                    $table->string('os', 32)->default('windows')->after('kind');
                });
            } catch (\Exception $e) {
            }
        }
        try {
            \DB::table('agent_release_packages')
                ->where(function ($q) {
                    $q->where('file_name', 'like', '%setup%')
                        ->orWhere('file_name', 'like', '%Setup%')
                        ->orWhere('file_name', 'like', '%installer%');
                })
                ->where(function ($q) {
                    $q->whereNull('kind')->orWhere('kind', '')->orWhere('kind', 'agent_binary');
                })
                ->update(['kind' => 'installer']);
            \DB::table('agent_release_packages')
                ->where(function ($q) {
                    $q->whereNull('kind')->orWhere('kind', '');
                })
                ->update(['kind' => 'agent_binary']);
            \DB::table('agent_release_packages')
                ->where(function ($q) {
                    $q->whereNull('os')->orWhere('os', '');
                })
                ->update(['os' => 'windows']);
        } catch (\Exception $e) {
        }
        try {
            Schema::table('agent_release_packages', function ($table) {
                $table->dropUnique('agent_release_packages_version_uq');
            });
        } catch (\Exception $e) {
        }
        try {
            Schema::table('agent_release_packages', function ($table) {
                $table->dropUnique('agent_release_packages_version_kind_uq');
            });
        } catch (\Exception $e) {
        }
        try {
            Schema::table('agent_release_packages', function ($table) {
                $table->unique(['version', 'kind', 'os'], 'agent_release_packages_version_kind_os_uq');
            });
        } catch (\Exception $e) {
        }
    }

}
