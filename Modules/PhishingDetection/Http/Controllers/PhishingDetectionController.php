<?php

namespace Modules\PhishingDetection\Http\Controllers;

use App\Sites;
use App\LogPhishing;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
class PhishingDetectionController extends Controller
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
        $data['page'] = langapp('phishing_detection');
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

    public function datatable(Request $request){
        $logPhishing = LogPhishing::where('transaction_status', 3)->where('url_is_work', 1)->get();

        return DataTables::of($logPhishing)
        ->editColumn('site_name', function ($collection) {
            $html = '';
            // $query = Sites::where('id', $collection->site_id)
            $query = DB::table('site')->where('id', $collection->site_id)
                ->first();
                
            // dd($query);
            $html = $query->name;
            return $html;
        })
        ->editColumn('url_detection', function ($collection) {
            return 'https://demo02.mtsc.co.th/login';
        })
        ->editColumn('status', function ($collection) {
            $html = '';
            $html .= '<label class="switch">
                        <input type="checkbox" id="status_' . $collection->id . '" onchange="change_status(\'' . $collection->id . '\')" name="status" value="1" checked>
                        <span></span>
                    </label>';
            return $html;
        })
        ->addColumn('severity', function ( $collection) {
            if($collection -> is_found == 1){
                $html = '<span class="badge" style="background-color: #e64732;">High</span>';
            }else{
                $html = '<span class="badge" style="background-color: #88ce4f;">Low</span>';
            }
            return $html;
        })
        ->addColumn('c_status', function($query) {
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
        ->addColumn('action', function ( $collection) {
            return '<a target="_blank" href="'.$collection -> url.'" class="btn btn-info btn-xs">
                        <i class="fas fa-eye"></i>
                    </a>

                    <a href="#" class="btn btn-danger btn-xs">
                        <i class="fas fa-trash"></i>
                    </a>
                    ';
        })
        ->rawColumns(['severity','status','c_status','action'])
        ->toJson();
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
}
