<?php

namespace Modules\PhishingDetection\Http\Controllers;

use App\Sites;
use App\LogPhishing;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Modules\SiteSettings\Entities\SiteSettings;
use Illuminate\Support\Str;
class PhishingDetectionController extends Controller
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
        $data['page'] = langapp('phishing_detection');
        $get_role_custom_first = @get_role_custom();
        $SiteSettings = @$get_role_custom_first['SiteSettings'];
        $data['site_settings'] = $SiteSettings;
        return view('phishingdetection::index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('phishingdetection::create');
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
        return view('phishingdetection::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('phishingdetection::edit');
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

    public function datatable(Request $request)
    {

        $filter_site = @$request->filter_site;
        $filter_url = @$request->filter_url;
        $filter_ip = @$request->filter_ip;
        $filter_serverity = @$request->filter_serverity;
        $filter_type = @$request->filter_type;

        $logPhishing = LogPhishing::
            join('site', 'log_phishing.site_id', 'site.id')
            ->select(
                'log_phishing.*',
                'site.name as site_name'
            )
            ->where('log_phishing.transaction_status', 3)
            ->where('log_phishing.url_is_work', 1)
            ->whereNull('log_phishing.deleted_at');

        if($filter_site)
        {
            $logPhishing->where('log_phishing.site_id', $filter_site);
        }

        if($filter_url)
        {
            $logPhishing->where('log_phishing.url', 'like', '%'.$filter_url.'%');
        }

        if($filter_ip)
        {
            $logPhishing->where('log_phishing.ip', 'like', '%'.$filter_ip.'%');
        }

        if($filter_serverity)
        {
            $logPhishing->where('log_phishing.serverity', $filter_serverity);
        }

        if($filter_type)
        {
            $logPhishing->where('log_phishing.type', $filter_type);
        }

        $logPhishing->orderBy('log_phishing.updated_at', 'DESC')->get();

        return DataTables::of($logPhishing)
            ->editColumn('status', function ($collection) {
                $html = '';
                $html .= '<label class="switch">
                            <input type="checkbox" id="status_' . $collection->id . '" onchange="change_status(\'' . $collection->id . '\')" name="status" value="1" checked>
                            <span></span>
                        </label>';
                return $html;
            })
            ->editColumn('c_serverity', function ( $collection) {
                
                $html = '';
                if($collection->serverity == 'Critical')
                {
                    $html .=  '<span class="badge" style="background-color: #b93624;">Critical</span>';
                }
                else if($collection->serverity == 'High')
                {
                    $html .= '<span class="badge" style="background-color: #fcc838;">High</span>';
                }
                else if($collection->serverity == 'Medium')
                {
                    $html .= '<span class="badge" style="background-color: #f2ff15;color:#333;">Medium</span>';
                }
                else if($collection->serverity == 'Low')
                {
                    $html .= '<span class="badge" style="background-color: #88ce4f;">Low</span>';
                }
                else if($collection->serverity == 'Information')
                {
                    $html .= '<span class="badge" style="background-color: #00dcff;">Information</span>';
                }
                else
                {
                    $html .= '-';
                }

                return $html;
            })
            ->editColumn('c_status', function($query) {
                $html = '';
                $html .= '
                    <div class="text-center">
                        <label class="switch">
                            <input type="checkbox" id="phishing-status-'.$query->id.'" onchange="change_status_phishing('.$query->id.')" 
                    ';
                    if($query->status == 1)
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
            ->editColumn('action', function ( $collection) {
                return '<a href="'.route('phishing_detection.view', ['code' => $collection->code]).'" class="btn btn-info btn-xs" data-toggle="ajaxModal">
                            <i class="fas fa-eye"></i>
                        </a>

                        <a href="'.route('phishing_detection.edit', ['code' => $collection->code]).'" class="btn btn-warning btn-xs" data-toggle="ajaxModal">
                            <i class="fas fa-solid fa-pen"></i>
                        </a>

                        <a href="'.route('phishing_detection.delete', ['code' => $collection->code]).'" class="btn btn-danger btn-xs" data-toggle="ajaxModal">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                        ';
                        // <a href="#" class="btn btn-danger btn-xs">
                        //     <i class="fas fa-trash"></i>
                        // </a>
            })
            ->rawColumns(['c_serverity','status','c_status','action'])
            ->make(true);
    }


    public function create_phishing_detection(Request $request)
    {
        $data['edit'] = false;
        $data['view'] = false;
        $data['page'] = langapp('phishing_detection');
        $get_role_custom_first = @get_role_custom();
        $SiteSettings = @$get_role_custom_first['SiteSettings'];
        $data['site_settings'] = $SiteSettings;
        return view('phishingdetection::modal.add')->with($data);
    }

    public function save_phishing(Request $request)
    {
        try
        {
            if(!@$request->phishing_id)
            {
                $main_data = $request->except(['_token']);
                $main_data['code'] = (string)Str::uuid();
                $main_data['transaction_status'] = 3;
                $main_data['url_is_work'] = 1;
                $main_data['status'] = @$request->status ? 1 : 0;
    
                LogPhishing::create($main_data);
    
                $response = [
                    'status' => 'success',
                    'message' => langapp('save_successful')
                ];
            }
            else
            {
                $main_data = $request->except(['_token', 'phishing_id', 'phishing_code']);
    
                LogPhishing::where(['id' => $request->phishing_id])->update($main_data);
    
                $response = [
                    'status' => 'success',
                    'message' => langapp('changes_saved_successful')
                ];
            }    
        }
        catch (\Exception $e)
        {
            $response = [
                'status' => 'error',
                'message' => langapp('error_something_went_wrong'),
                'error' => $e->getMessage()
            ];
        }

        return response()->json($response);
    }

    public function edit_phishing_detection(Request $request)
    {
        $query_edit = LogPhishing::where(['code' => $request->code])->first();

        $data['edit'] = true;
        $data['view'] = false;
        $data['page'] = langapp('phishing_detection');
        $get_role_custom_first = @get_role_custom();
        $SiteSettings = @$get_role_custom_first['SiteSettings'];
        $data['site_settings'] = $SiteSettings;
        $data['query_edit'] = $query_edit;
        return view('phishingdetection::modal.add')->with($data);
    }

    public function view_phishing_detection(Request $request)
    {
        $query_edit = LogPhishing::where(['code' => $request->code])->first();

        $data['edit'] = true;
        $data['view'] = true;
        $data['page'] = langapp('phishing_detection');
        $get_role_custom_first = @get_role_custom();
        $SiteSettings = @$get_role_custom_first['SiteSettings'];
        $data['site_settings'] = $SiteSettings;
        $data['query_edit'] = $query_edit;
        return view('phishingdetection::modal.add')->with($data);
    }

    public function update_status_phishing(Request $request)
    {
        $input = $request->all();
        
        $id = $request->id;
        $status = $request->status;

        $update_status = LogPhishing::where('id', $id)->update(['status' => $status]);

        return response()->json([
            'status_code' => '200'
        ]);

    }

    public function delete_phishing_detection(Request $request)
    {
        $data["code"] = $request->code;
        return view('phishingdetection::modal.delete')->with($data);
    }

    public function delete_phishing(Request $request)
    {
        LogPhishing::where('code', $request->code)->update(['deleted_at' => date('Y-m-d H:i:s')]);

        return response()->json([
            'status_code' => '200'
        ]);
    }

    public function data_chart_timeline(Request $request)
    {
        $input = $request->all();
        // $site_id = $request->site_id;
        // $start_date_input = $request->start_date;
        // $end_date_input = $request->end_date;
        $data = [];

        // dd($input);

        // // $start_date_input = date("Y-m-d", strtotime("+1 day", strtotime($start_date_input)));
        // if($start_date_input == null && $end_date_input == null)
        // {
            $end_date_input = date('Y-m-d');
            $start_date_input = date("Y-m-d", strtotime("-30 day", strtotime($end_date_input)));
        // }

        $start_date = date('Y-m-d', strtotime($start_date_input));
        $end_date = date('Y-m-d', strtotime($end_date_input));
        
        $Variable1 = strtotime($start_date);
        $Variable2 = strtotime($end_date);
        
        for ($currentDate = $Variable1; $currentDate <= $Variable2; $currentDate += (86400)) {
                                            
            $Store = date('Y-m-d', $currentDate);
            $Store2 = date("Y-m-d", strtotime("+1 day", strtotime($Store)));
            
            $day = explode('-', $Store);

            $query_timeline = LogPhishing::
                                // where(function ($query_site) use ($site_id) {
                                //     if(@$site_id != null)
                                //     {
                                //         $query_site->where('site_id', $site_id);
                                //     }
                                //     else
                                //     {
                                //         $query_site->where('site_id', '!=', null);
                                //     }
                                // })
                                // where('status', '1')
                                whereNull('deleted_at')
                                ->whereBetween('created_at', [$Store.' 00:00:00', $Store.' 23:59:59'])
                                ->get();

            $count = count($query_timeline);

            $data['day'][] = $day[2];
            $data['date'][] = $Store;
            $data['count'][] = $count;
        }

        return response()->json($data);
    }

    public function data_chart_circle(Request $request)
    {
        $input = $request->all();
        // $site_id = $request->site_id;
        // $start_date_input = $request->start_date;
        // $end_date_input = $request->end_date;

        $count_referrer = LogPhishing::
            // where('status', '1')
            where('type', 'Referrer')
            ->whereNull('deleted_at')
            ->count();

        $count_threat_feed = LogPhishing::
            // where('status', '1')
            where('type', 'Threat Feed')
            ->whereNull('deleted_at')
            ->count();

        $count_domain_name = LogPhishing::
            // where('status', '1')
            where('type', 'Domain name')
            ->whereNull('deleted_at')
            ->count();

        $count_other = LogPhishing::
            // where('status', '1')
            where('type', 'Other')
            ->whereNull('deleted_at')
            ->count();

        $response = [
            'count_referrer' => $count_referrer,
            'count_threat_feed' => $count_threat_feed,
            'count_domain_name' => $count_domain_name,
            'count_other' => $count_other,
        ];

        return response()->json($response);
    }
}
