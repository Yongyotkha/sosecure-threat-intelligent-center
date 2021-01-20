<?php

namespace Modules\Monitoring\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Yajra\DataTables\DataTables;
use App\Entities\TransactionBatchjob;
use DB;
use Modules\SiteSettings\Entities\SiteSettings;
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
        $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->get();
        $data['SiteSettings'] = $SiteSettings;
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
        if ($request->isSearch == 1) {
            
            $model = TransactionBatchjob::select('site.name as site_id', 'transaction_batchjob.transcation_date_end', 'transaction_batchjob.transcation_date_start', 'transaction_batchjob.progress', 'transaction_batchjob.mode', 'transaction_batchjob.name')->where('status', 1);

            if($request->isDateSearch==1){
                $date_start_explode = explode(" ",$request->startDate);
                $date_start_date = @$date_start_explode[0];
                $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
                // dd($date_start_time);
                $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
                // dd($date_start_date_format);
                $date_start_time_time = date("H:i", strtotime($date_start_time));
                $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
                // dd($date_start_time_time);

                $date_end_explode = explode(" ",$request->endDate);
                $date_end_date = @$date_end_explode[0];
                $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
                // dd($date_end_time);
                $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
                $date_end_time_time = date("H:i", strtotime($date_end_time));
                $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
                $model = $model -> whereBetween('transcation_date',array($date_start_datetime_format,$date_end_datetime_format));
                
            }

            if($request->Keywords){
                $keywords = "%".$request->Keywords."%";
                $model = $model->where(function ($query) use ($keywords){
                    $query->where('transaction_batchjob.name','LIKE', $keywords)
                    ->orWhere('mode', 'LIKE', $keywords);
                });
            }

            if($request->select||$request->select==="0"){
                if($request->select==="0"){
                    $model = $model->where('progress', $request->select);
                }else if($request->select=='1'){
                    $model = $model->whereIn('progress', [1, 2]);
                }else{

                }
            }

            if($request->sitecode){
                $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->where("code",$request->sitecode)->first();
                $model = $model->where('site_id', $SiteSettings->id);
            }

            $model = $model->leftjoin('site', 'transaction_batchjob.site_id', '=', 'site.id');
            
        } else {

            $model = TransactionBatchjob::select('site.name as site_id', 'transaction_batchjob.transcation_date_end', 'transaction_batchjob.transcation_date_start', 'transaction_batchjob.progress', 'transaction_batchjob.mode', 'transaction_batchjob.name')->where('status', 1)->leftjoin('site', 'transaction_batchjob.site_id', '=', 'site.id');
        
        }
        
            $model = $model;

        return DataTables::of($model)
            ->toJson();
    }
}
