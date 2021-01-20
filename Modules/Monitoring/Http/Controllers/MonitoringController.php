<?php

namespace Modules\Monitoring\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Yajra\DataTables\DataTables;
use App\Entities\TransactionBatchjob;
use DB;
class MonitoringController extends Controller
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
        $data['page'] = langapp('monitoring');
        return view('monitoring::index')->with($data);
    }

    public function batchjob()
    {
        $data['page'] = langapp('batchjob');
        return view('monitoring::index')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('monitoring::create');
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
        return view('monitoring::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('monitoring::edit');
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


    public function tableMonitor(Request $request)
    {
        
        $model = '';
        $html = '';
        if ($request->search_ == 1) {   
            
        } else {
            //DB::raw('site_id as dd'),
            $model = TransactionBatchjob::where('status', 1)->leftjoin('site', 'transaction_batchjob.site_id', '=', 'site.id')
            ->select('site.name as site_id', 'transaction_batchjob.transcation_date_end', 'transaction_batchjob.transcation_date_start', 'transaction_batchjob.progress', 'transaction_batchjob.mode', 'transaction_batchjob.name');
        }
        
            $model = $model;

        return DataTables::of($model)
            ->toJson();
    }
}
