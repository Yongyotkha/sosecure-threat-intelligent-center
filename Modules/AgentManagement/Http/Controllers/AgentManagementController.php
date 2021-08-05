<?php

namespace Modules\AgentManagement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;
use DB;
use App\FXSiteAgents;
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
