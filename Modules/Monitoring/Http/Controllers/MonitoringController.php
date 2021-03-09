<?php

namespace Modules\Monitoring\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Yajra\DataTables\DataTables;
use App\Entities\TransactionBatchjob;
use DB;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\Monitoring\Entities\MonitorLogs;
use Modules\Monitoring\Entities\SentLogs;
use Carbon\Carbon;
use App\Entities\Categories;
use Modules\Monitoring\Entities\MonitoringSystem;
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
        $role_custom = @check_role_custom();
        if(!$role_custom['monitoring']) {
            check_permission403();
        }
        
        $data['page'] = langapp('monitoring');
        return view('monitoring::index')->with($data);
    }

    public function dashboard()
    {
        
        // dd($dateNow->diffInMinutes('2021-03-05 14:00:09'));

        $role_custom = @check_role_custom();
        if(!$role_custom['monitoring']) {
            check_permission403();
        }
        $tz = new \DateTimeZone('Asia/Bangkok');
        $dateNow = Carbon::now();
        $dateNow->setTimezone($tz);
        $htmlCard = '';
        $SiteSettings = SiteSettings::select('id','logo','name','code')->where('active', '1')->whereNull('deleted_at')->with('get_categorys')->get()->toArray();
        
        if(!empty($SiteSettings)){
           
            foreach ($SiteSettings as $key => $value) {
                $TransactionBatchjob = TransactionBatchjob::select('transcation_date_start')->where('site_id', $value['id'])->where('status', 1)->orderBy('transcation_date_start','desc')->first()->toArray();
                $SiteSettings[$key] = array_merge($SiteSettings[$key], $TransactionBatchjob);
            }
    
            usort($SiteSettings, function($a, $b) {
                $t1 = strtotime($a['transcation_date_start']);
                $t2 = strtotime($b['transcation_date_start']);
                return $t2 - $t1;
            });
         
            foreach ($SiteSettings as $key => $value) {
                
                if(!empty($value['transcation_date_start'])){
                    $dateDiffMin = $dateNow->diffInMinutes($value['transcation_date_start']);
                    if($dateDiffMin<3){
                        $statusDotClass = 'dot low';
                        $statusDotName = 'Online';
                    }else{
                        $statusDotClass = 'dot critical';
                        $statusDotName = 'Offline';
                    }
        
                    $get_categorys = '';
                    if(!empty($value['get_categorys'])){
                        foreach ($value['get_categorys'] as $Categorie_s) {
                            $Categorie = Categories::select('name')->where('id',$Categorie_s['category_id'])->first();
                            $get_categorys = $get_categorys.$Categorie->name.',';
                        }
                        $get_categorys = rtrim($get_categorys,",");
                    }
                    $MonitoringSystem_data= MonitoringSystem::where('site_id',$value['id'])->select('content','updated_at')->first();
                    
                    $htmlCard .= '
                    <div class="item-wdfm mdasbord-inner" id="data_main_'.$value['code'].'">
                        <div class="wdfm-card">
                            <center > 
                                <div style="width:200px; height:200px;"><img id="preview-image_logo" src="https://insight.sosecure.co.th/'.$value['logo'].'" onerror="setDefaultPic(this)" style="width:100%;height:100%; object-fit:contain;" alt="..."></div>
                            </center>
        
                            <div class="wdfm-footer start-top" >
                                <div class="wdfm-ft-left flex">
                                    <div><strong>Site:</strong> '.$value['name'].'</div>
                                    <div class="status-flex" id="data_status_'.$value['code'].'"><strong>Status:</strong> &nbsp; <span class="dot '.$statusDotClass.'"></span> '.$statusDotName.'
                                    </div>
                                    <div><strong>Catagory:</strong> '.$get_categorys.'</div>';
                                    $htmlCard .= '   <div id="data_lastcheck_'.$value['code'].'"><strong>Last Online:</strong> '.$value['transcation_date_start'].'</div>';
                                    if($MonitoringSystem_data){
                                        $htmlCard .= '  <div id="data_monitoring_server_'.$value['code'].'"><strong>Monitoring:</strong> '.'-'.'</div>
                                        <div id="data_monitoring_server_date_'.$value['code'].'"><strong>Monitoring Last:</strong> '.'-'.'</div>';
                                    }else{
                                        $htmlCard .= '   <div id="data_monitoring_server_'.$value['code'].'"><strong>Monitoring:</strong> '.'-'.'</div>
                                        <div id="data_monitoring_server_date_'.$value['code'].'"><strong>Monitoring Last:</strong> '.'-'.'</div>';

                                    }
                                
                                    $htmlCard .= '     </div>
                            </div>
                        </div>
                    </div>';
                }

            }
        }
        


        $data['page'] = langapp('monitoring_dashboard');
        $data['htmlCard'] = $htmlCard;
        return view('monitoring::dashboard')->with($data);
    }
    

    public function batchjob()
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['monitoring']) {
            check_permission403();
        }
        // <><><>
        // $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->get();
        $get_role_custom_first = @get_role_custom();
        $SiteSettings = '';
        $SiteSettings = @$get_role_custom_first['SiteSettings'];
        $site_id_arr = @$get_role_custom_first['site_id_arr'];
        if(@$get_role_custom_first['superadmin'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }else if(@$get_role_custom_first['client'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }else if(@$get_role_custom_first['site_support'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }else if(@$get_role_custom_first['site_admin'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }else if(@$get_role_custom_first['site_client'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }
        $data['SiteSettings'] = $SiteSettings;
        $data['page'] = langapp('schedule_task');
        return view('monitoring::index')->with($data);
    }

    public function monitor_logs()
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['monitoring']) {
            check_permission403();
        }
        // <><><>
        // $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->get();
        $get_role_custom_first = @get_role_custom();
        $SiteSettings = '';
        $SiteSettings = @$get_role_custom_first['SiteSettings'];
        $site_id_arr = @$get_role_custom_first['site_id_arr'];
        if(@$get_role_custom_first['superadmin'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }else if(@$get_role_custom_first['client'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }else if(@$get_role_custom_first['site_support'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }else if(@$get_role_custom_first['site_admin'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }else if(@$get_role_custom_first['site_client'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }
        $data['SiteSettings'] = $SiteSettings;
        $data['page'] = langapp('logs');
        return view('monitoring::monitor_logs')->with($data);
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
        $role_custom = @check_role_custom();
        if(!$role_custom['monitoring']) {
            check_permission403();
        }
        
        
        $model = '';
        $html = '';
        if ($request->isSearch == 1) {
            $model = TransactionBatchjob::select('site.name as site_id', 'transaction_batchjob.id as transaction_batchjob_id', 'transaction_batchjob.transcation_date_end', 'transaction_batchjob.transcation_date_start', 'transaction_batchjob.progress', 'transaction_batchjob.mode', 'transaction_batchjob.name', 'transaction_batchjob.message')->where('status', 1);
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
                $model = $model->where('progress', $request->select);
            }

            if($request->sitecode){
                $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->where("code",$request->sitecode)->first();
                $model = $model->where('site_id', $SiteSettings->id);
            }

            $model = $model->leftjoin('site', 'transaction_batchjob.site_id', '=', 'site.id');
          
            
        } else {

            $model = TransactionBatchjob::select('site.name as site_id', 'transaction_batchjob.id as transaction_batchjob_id', 'transaction_batchjob.transcation_date_end', 'transaction_batchjob.transcation_date_start', 'transaction_batchjob.progress', 'transaction_batchjob.mode', 'transaction_batchjob.name', 'transaction_batchjob.message')->where('status', 1)->leftjoin('site', 'transaction_batchjob.site_id', '=', 'site.id');
        
        }
        
            $model = $model;

        return DataTables::of($model)
        ->editColumn('message', function (TransactionBatchjob $model) {
            $html = '';
            if($model->message) {
                $html .= ' <a href="'.route('monitoring.view_message_modal',['id' => $model->transaction_batchjob_id]).'" class="btn btn-info btn-xs" data-toggle="ajaxModal"><i class="fas fa-eye"></i></a>';
            } else {
                $html = '';
            }
            // $html .= '<div class="text-elip-message" data-title='.$model->message.'>'.$model->message.'</div>';
            
            return  $html;
        })
        ->rawColumns(['name','mode','progress','transcation_date_start','transcation_date_end','site_id','message',])
        ->toJson();
    }

    public function table_monitor_logs(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['monitoring']) {
            check_permission403();
        }
        
        $model = '';
        $html = '';

        if ($request->isSearch == 1) {
            $model = MonitorLogs::select('site.name as site_name', 'logs.id as logs','logs.site_id','logs.file',
            'logs.error_summary','logs.log_trace', 'logs.created_at', 'logs.updated_at');
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
                $model = $model -> whereBetween('logs.updated_at',array($date_start_datetime_format,$date_end_datetime_format));
            }

            if($request->Keywords){
                
                $keywords = "%". str_replace(array('\\', '"','\''), '\\\\', $request->Keywords)."%";
                $model = $model->where(function ($query) use ($keywords){
                    $query->where('logs.file','LIKE', $keywords)
                    ->orWhere('logs.error_summary', 'LIKE', $keywords)
                    ->orWhere('logs.log_trace', 'LIKE', $keywords);
                });
            }

            if($request->sitecode){
                $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->where("code",$request->sitecode)->first();
                $model = $model->where('site_id', $SiteSettings->id);
            }

            $model = $model->leftjoin('site', 'logs.site_id', '=', 'site.id');
          
            
        }else{
            $model = MonitorLogs::select('site.name as site_id','logs.id as logs','logs.file',
            'logs.error_summary','logs.log_trace', 'logs.created_at', 'logs.updated_at')
            ->leftjoin('site', 'logs.site_id', '=', 'site.id');
        }
        
        return DataTables::of($model)->toJson();
    }


    // View Content DataLeak
    public function view_message_modal(Request $request , $id)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['monitoring']) {
            check_permission403();
        }
        if($id) {
            $TransactionBatchjob = TransactionBatchjob::where('id',$id)->first();
        }
        $data['message'] = @$TransactionBatchjob->message;
        return view('monitoring::modal.view_message')->with($data);
    }

    public function delete_logs(Request $request){
        $role_custom = @check_role_custom();
        if(!$role_custom['monitoring']) {
            check_permission403();
        }
        if($request->id_change){
            foreach($request->id_change as $id_change){
                MonitorLogs::where('id',$id_change)->delete();
            }
        }else{
            MonitorLogs::where('id',$request->id)->delete();
        }

        return ajaxResponse(
            [
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('monitoring.monitor_logs'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function send_logs(){
        $role_custom = @check_role_custom();
        // dd($role_custom);
        if(!$role_custom['monitoring']) {
            check_permission403();
        }
        $get_role_custom_first = @get_role_custom();
        $SiteSettings = '';
        $SiteSettings = @$get_role_custom_first['SiteSettings'];
        $site_id_arr = @$get_role_custom_first['site_id_arr'];
        if(@$get_role_custom_first['superadmin'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }else if(@$get_role_custom_first['client'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }else if(@$get_role_custom_first['site_support'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }else if(@$get_role_custom_first['site_admin'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }else if(@$get_role_custom_first['site_client'] == 1) {
            $SiteSettings = @$get_role_custom_first['SiteSettings'];
        }
        $data['SiteSettings'] = $SiteSettings;
        $data['page'] = langapp('send_logs');
        $data['type'] = SentLogs::select('type')->distinct()->get();

        return view('monitoring::send_logs')->with($data);
    }

    public function table_send_logs(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['monitoring']) {
            check_permission403();
        }
        
        $model = '';
        $html = '';

        if ($request->isSearch == 1) {
     
            $model = SentLogs::select('site.name as site_name', 'logs_sent_transaction.id as logs','logs_sent_transaction.mode',
            'logs_sent_transaction.content','logs_sent_transaction.type','logs_sent_transaction.transaction_status',
            'logs_sent_transaction.created_at', 'logs_sent_transaction.updated_at','logs_sent_transaction.site_id')
            ->leftjoin('site', 'logs_sent_transaction.site_id', '=', 'site.id');
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
                $model = $model -> whereBetween('logs_sent_transaction.updated_at',array($date_start_datetime_format,$date_end_datetime_format));
            }

            if($request->Keywords){
                
                $keywords = "%". str_replace(array('\\', '"','\''), '\\\\', $request->Keywords)."%";
                $model = $model->where('content','LIKE', $keywords);
            }

            if($request->select_val){
                
                $model = $model->where('transaction_status', $request->select_val);
                
            }

            if($request->type){
   
                $model = $model->where('type', $request->type);
                
            }

            if($request->sitecode){

                $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->where("code",$request->sitecode)->first();
                $model = $model->where('site_id', $SiteSettings->id);
                
            }

           
          
            
        }else{
            $model = SentLogs::select('site.name as site_name', 'logs_sent_transaction.id as logs','logs_sent_transaction.mode',
            'logs_sent_transaction.content','logs_sent_transaction.type',
            'logs_sent_transaction.transaction_status', 'logs_sent_transaction.created_at', 'logs_sent_transaction.updated_at')
            ->leftjoin('site', 'logs_sent_transaction.site_id', '=', 'site.id');
        }
        
        return DataTables::of($model)->toJson();
    }

    public function delete_send_logs(Request $request){
        $role_custom = @check_role_custom();
        if(!$role_custom['monitoring']) {
            check_permission403();
        }
        if($request->id_change){

            foreach($request->id_change as $id_change){
                SentLogs::where('id',$id_change)->delete();
            }
        }else{
           
            SentLogs::where('id',$request->id)->delete();
        }

        return ajaxResponse(
            [
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('monitoring.send_logs'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function load_card(Request $request){

        $tz = new \DateTimeZone('Asia/Bangkok');
        $dateNow = Carbon::now();
        $dateNow->setTimezone($tz);

       
        $SiteSettings = SiteSettings::select('id','logo','name','code')->where('active', '1')->whereNull('deleted_at')->get()->toArray();
        
        foreach ($SiteSettings as $key => $value) {
            $TransactionBatchjob = TransactionBatchjob::select('transcation_date_start')->where('site_id', $value['id'])->where('status', 1)->orderBy('transcation_date_start','desc')->first()->toArray();
            $dateDiffMin = $dateNow->diffInMinutes($TransactionBatchjob['transcation_date_start']);
            if($dateDiffMin<3){
                $dataStatus['statusDotClass'] = 'dot low';
                $dataStatus['statusDotName'] = 'Online';
            }else{
                $dataStatus['statusDotClass'] = 'dot critical';
                $dataStatus['statusDotName'] = 'Offline';
            }
            $MonitoringSystem_data= MonitoringSystem::where('site_id',$value['id'])->select('content','updated_at')->first();
            $dataStatus['MonitoringSystem'] = $MonitoringSystem_data;
            unset($SiteSettings[$key]['id']);
            $SiteSettings[$key] = array_merge($SiteSettings[$key], $TransactionBatchjob, $dataStatus);

        

        }

        if ($request->ajax()) {
            $data = [
                "card_data" => @$SiteSettings,
            ];
            return response()->json($data);
        }
    }

    public function load_status(Request $request){

    }
    public function load_category(Request $request){

    }

}
