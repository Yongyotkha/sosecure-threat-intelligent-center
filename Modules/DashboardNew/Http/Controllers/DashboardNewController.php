<?php

namespace Modules\DashboardNew\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\MonitoringVulnerabilitys\Entities\CVEAssets;
use Modules\MonitoringVulnerabilitys\Entities\CVEMapping;
use App\DataLeakFeed;

class DashboardNewController extends Controller
{
    /**
     * Item Model
     *
     * @var \Modules\Items\Entities\Item
     */
    protected $item;
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->request = $request;
    }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        // session_start();
        // $menu = session('menu');
        $menu = array();
        if(isset($_SESSION["menu"])){
            // unset($_SESSION["lastname"]);
            $menu = $_SESSION["menu"];
        }
        
        // dd($menu[4]->get_menu_sub);
        $data['page'] = langapp('dashboard');
        $data['count_CVEAssets'] = CVEAssets::where("active", '=', 1)->count();
        $data['count_CVEMapping'] = CVEMapping::count();
        $data['count_compromised'] = DataLeakFeed::where("status", '=', 1)
                                    ->where("deleted_at", '=', null)
                                    ->where("feel_type", '!=', 'social')
                                    ->count();
        $data['count_dataLeak'] = DataLeakFeed::where("status", '=', 1)
                                    ->where("deleted_at", '=', null)
                                    ->where("feel_type", '!=', 'social')
                                    ->count();
        $data['get_CVEAssets'] = CVEAssets::where("active", '=', 1)->get();                            


        return view('dashboardnew::index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('dashboardnew::create');
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
        return view('dashboardnew::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('dashboardnew::edit');
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

    public function load_chart(Request $request)
    {
        $model = new CVEMapping;
        $model->get();
    
        $high = $model->where('severity', '=', 'HIGH')->count();
        $medium = $model->where('severity', '=', 'MEDIUM')->count();
        $critical = $model->where('severity', '=', 'CRITICAL')->count();
        $low = $model->where('severity', '=', 'LOW')->count();
        $none = $model->where('severity', '=', 'NONE')->count();

        if ($request->ajax()) {
            $data = [
                "count_high" => $high,
                "count_medium" => $medium,
                "count_critical" => $critical,
                "count_low" => $low,
                "count_none" => $none,
            ];
            return response()->json($data);
        }
    }
}
