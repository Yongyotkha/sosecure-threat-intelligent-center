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

use Modules\RSSFeedSettings\Entities\RSSData;
use Modules\RSSFeedSettings\Entities\RSSNews;
use Modules\RSSFeedSettings\Entities\RSSNewsCategory;
use Modules\SiteSettings\Entities\Tags;
use Modules\SiteSettings\Entities\site_config_email_alert;
use Modules\SiteSettings\Entities\SiteCategory;
use Modules\RSSFeedSettings\Entities\TransactionRssData;
use App\DataLeakSocial;


use MongoDB\BSON\Regex;
use MongoDB\Client;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\UTCDateTime;
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

    public function tableSocailMonitoring(){
        $role_custom = @check_role_custom();
    
        $model = DataLeakSocial::where('deleted_at',null)->get();
        return DataTables::of($model)
            ->editColumn('chk', function (DataLeakSocial $model) {
                    return '<label><input type="checkbox" name="rss_id" class="rss_id" value="' . $model->code . '"><span class="label-text"></span></label>';
            })
            ->addColumn('link', function (DataLeakSocial $model) {
                $html = '';
                $html .= "<a href='". route('rssfeedsettings.rss_data_create_news', ['code' => $model->code]) ."' data-toggle='ajaxModal'>
                    ".$model->link."
                </a>";
                return $html;
            })
            ->addColumn('transactionRssData_count', function (DataLeakSocial $model) {


                $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
                $client = new \MongoDB\Client($DB_MONGO_KEY);
                $db_name = 'social';
                $db = $client->$db_name;
                $collection = $db->DailyFeed;
                $where = array(
                    'sourceid' => $model->id,
                );
        
                $cursor = $collection->find($where);   //This is the main line
                $document_all = $cursor->toArray();
                $cursor_count = count($document_all);
             



                $html = '';
                $html .= $cursor_count;
                return $html;
            })
            ->addColumn('feed_last_mongodb', function (DataLeakSocial $model) {


                $DB_MONGO_KEY = env("DB_MONGO_DEV", "");
                $client = new \MongoDB\Client($DB_MONGO_KEY);
                $db_name = 'social';
                $db = $client->$db_name;
                $collection = $db->feed_source;
                $where = array(
                   'source_id' => $model->id,
                  
                );
        
                $cursor = $collection->find($where);   //This is the main line
                $document_all = $cursor->toArray();
                $cursor_count = count($document_all);
   
             



                $html = '';
                if($cursor_count > 0){
                    foreach ($document_all as  $value) {
                      
                       

                        try {
                            if (property_exists($value, 'last_feed_date')) {
                            $html .=  change_date_utc_to_thai($value->last_feed_date);
                            }
                        } catch (Exception $e) {
                          
                        }
                    }

                   
                }
             
                return $html;
            })
            ->addColumn('status', function (DataLeakSocial $model) {
                if($model->status == '1') {
                    $checked_val = 'checked';
                } else {
                    $checked_val = '';
                }
                $html = '';
                $html .= '<label class="switch">
                            <input type="checkbox" id="rss-active-'.$model->code.'" onchange="change_rss_active(\''.$model->code.'\')" '.$checked_val.' value="1">
                            <span></span>
                        </label>';
                return $html;
            })
            ->addColumn('action', function (DataLeakSocial $model) {
                $html = '';
                $html .= "<a href='". route('rssfeedsettings.edit', ['id' => $model->code]) ."' class='btn btn-". get_option('theme_color') ." btn-xs' data-toggle='ajaxModal'>
                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                </a>
                <a href='". route('rssfeedsettings.delete', ['id' => $model->code]) ."' class='btn btn-danger btn-xs' data-toggle='ajaxModal'>
                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'><path d='M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z'></path></svg>
                </a></div>";
                return $html;
            })
            ->rawColumns(['chk','link','status','action'])
            ->toJson();
    }


    public function social_feel()
    {
   
        $data['SiteSettings'] = "";
        $data['page'] = langapp('social_feel');
        return view('monitoring::social_feel')->with($data);
    }


    public function tablesocial_feel(Request $request)
{
    $role_custom = @check_role_custom();
    if(!$role_custom['indicators']) {
        check_permission403();
    }

        // $DB_MONGO_KEY = env("DB_MONGO_DEV", "mongodb://10.104.0.7:27017");

        // $client = new \MongoDB\Client($DB_MONGO_KEY);

    $f_search = $request->f_search;
    $start_date = $request->start_date;
    $end_date = $request->end_date;
    $keyword = $request->keyword;

    if($start_date) {
        $date_start_explode = explode(" ",$start_date);
        $date_start_date = @$date_start_explode[0];
        $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
            // dd($date_start_time);
        $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
            // dd($date_start_date_format);
        $date_start_time_time = date("H:i", strtotime($date_start_time));
        $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
            // dd($date_start_time_time);


        $date_end_explode = explode(" ",$end_date);
        $date_end_date = @$date_end_explode[0];
        $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
            // dd($date_end_time);
        $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
        $date_end_time_time = date("H:i", strtotime($date_end_time));
        $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
            // dd($date_end_time_time);



        $dateStart = new \MongoDB\BSON\UTCDateTime(strtotime($date_start_datetime_format)*1000);
        $dateEnd = new \MongoDB\BSON\UTCDateTime(strtotime($date_end_datetime_format)*1000);
            // dd($dateStart);
    }






    $perpage = 25;

    if (isset($_POST['page'])) {
        $page = $_POST['page'];
    } else {
        $page = 1;
    }
    $start = ($page - 1) * $perpage;

    $mongo_url = DB_MONGO_01;
    $client = new \MongoDB\Client($mongo_url);
    $db_name = 'social';
    $db = $client->$db_name;
    $collection = $db->Feed;
        // $where = array(
        //     'status' => 1,
        // );

    if($f_search == 'true') {
        $query = [
            '$and' => [
                [
                    '$or' => [
                        [
                            '$and' => [
                                [
                                    'name' => new Regex('^.*'.$keyword.'.*$', 'i')
                                ],
                                [
                                    'status' => 1
                                ]
                            ]
                        ],
                        [
                            '$and' => [
                                [
                                    'tags' => new Regex('^.*'.$keyword.'.*$', 'i')
                                ],
                                [
                                    'status' => 1
                                ]
                            ]
                        ],
                        [
                            '$and' => [
                                [
                                    'groups' => new Regex('^.*'.$keyword.'.*$', 'i')
                                ],
                                [
                                    'status' => 1
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'created_at' => [
                            '$gte' => $dateStart//new UTCDateTime(-15644559600000)
                        ]
                    ],
                    [
                        'created_at' => [
                            '$lte' => $dateEnd//new UTCDateTime(-15644559600000)
                        ]
                    ]
                ]
            ];


        } else {
            $query = [
                'status' => 1//,
                // 'tags' => new Regex('^.*webscanner.*$', 'i')//LIKE
                // 'tags' => [//IN
                //     '$in' => [
                //         'webscanner',
                //         'test'
                //     ]
                // ]
            ];
        }

        
        $options = [
            'sort' => [
                'feedtimepost' => -1
            ],
            'skip' => $start,//10
            'limit' => $perpage//5
        ];
        
        $cursor = $collection->find($query, $options);

        $query2 = [];
        $options2 = [
            'projection' => [
                '_id' => '$_id'
            ]
        ];

        $cursor_all = $collection->find($query, $options2);

        $total_record = count($cursor_all->toArray());
        // dd($total_record);
        // $total_record = mysqli_num_rows($query2);
        $total_page = ceil($total_record / $perpage);
        $second_last = $total_page - 1; // total pages minus 1

        $offset = ($page-1) * $perpage;
        $previous_page = $page - 1;
        $next_page = $page + 1;
        $adjacents = "2";


        $pagination = '<nav>
        <ul class="pagination">';

        if($page > 1) {
            $pagination .= '<li onclick="pagination_goto(1)" data-page="1"><a href="#">First Page</a></li>';
        }

        $pagination .= '<li onclick="pagination_goto('.$previous_page.')" data-page="'.@$previous_page.'"';
        if($page <= 1) {
            $pagination .= 'class="disabled"';
        }
        $pagination .= '>';

        $pagination .= '<a ';
        if($page > 1) {
            $pagination .= 'href="#"';
        }

        $pagination .= '>Previous</a></li>';

        if ($total_page <= 10){   
            for ($counter = 1; $counter <= $total_page; $counter++){
                if ($counter == $page) {
                    $pagination .= '<li class="active"><a>'.$counter.'</a></li>'; 
                }else{
                    $pagination .= '<li onclick="pagination_goto('.$counter.')" data-page="'.$counter.'"><a href="#">'.$counter.'</a></li>';
                }
            }
        } elseif ($total_page > 10){
            if($page <= 4) { 
                for ($counter = 1; $counter < 8; $counter++){ 
                    if ($counter == $page) {
                        $pagination .= '<li class="active"><a>'.$counter.'</a></li>'; 
                    }else{
                        $pagination .= '<li onclick="pagination_goto('.$counter.')" data-page="'.$counter.'"><a href="#">'.$counter.'</a></li>';
                    }
                }
                $pagination .= '<li><a>...</a></li>';
                $pagination .= '<li onclick="pagination_goto('.$second_last.')" data_page="'.$second_last.'"><a href="#">'.$second_last.'</a></li>';
                $pagination .= '<li onclick="pagination_goto('.$total_page.')" data-page="'.$total_page.'"><a href="#">'.$total_page.'</a></li>';
            } elseif ($page > 4 && $page < $total_page - 4) { 
                $pagination .= '<li onclick="pagination_goto(1)" data-page="1"><a href="#">1</a></li>';
                $pagination .= '<li onclick="pagination_goto(2)" data-page="2"><a href="#">2</a></li>';
                $pagination .= "<li><a>...</a></li>";
                for (
                   $counter = $page - $adjacents;
                   $counter <= $page + $adjacents;
                   $counter++
               ) { 
                    if ($counter == $page) {
                        $pagination .= '<li class="active"><a>'.$counter.'</a></li>'; 
                    }else{
                        $pagination .= '<li onclick="pagination_goto('.$counter.')" data-page="'.$counter.'"><a href="#">'.$counter.'</a></li>';
                    }                  
                }   
                $pagination .= "<li><a>...</a></li>";
                $pagination .= '<li onclick="pagination_goto('.$second_last.')" data-page="'.$second_last.'"><a href="#">'.$second_last.'</a></li>';
                $pagination .= '<li onclick="pagination_goto('.$total_page.')" data-page="'.$total_page.'"><a href="#">'.$total_page.'</a></li>';
            } else {
                $pagination .= '<li onclick="pagination_goto(1)" data-page="1"><a href="#">1</a></li>';
                $pagination .= '<li onclick="pagination_goto(2)" data-page="2"><a href="#">2</a></li>';
                $pagination .= '<li><a>...</a></li>';
                for (
                   $counter = $total_page - 6;
                   $counter <= $total_page;
                   $counter++
               ) {
                    if ($counter == $page) {
                        $pagination .= '<li class="active"><a>'.$counter.'</a></li>'; 
                    }else{
                        $pagination .= '<li onclick="pagination_goto('.$counter.')" data-page="'.$counter.'"><a href="#">'.$counter.'</a></li>';
                    }                   
                }
            }
        }

        $pagination .= '<li onclick="pagination_goto('.$next_page.')" data-page="'.@$next_page.'"';

        if($page >= $total_page){
            $pagination .= 'class="disabled"';
        } 
        $pagination .= ' >';

        $pagination .= '<a ';
        if($page < $total_page) {
            $pagination .= 'href="#"';
        }
        $pagination .= '>Next</a></li>';

        if($page < $total_page){
            $pagination .= '<li onclick="pagination_goto('.$total_page.')" data-page="'.$total_page.'"><a href="#">Last &rsaquo;&rsaquo;</a></li>';
        } 
        $pagination .= '</ul></nav>';






        $start_first_in_page = $start+1;
        $end_last_in_page = $start+$perpage;

        $showing_amount_text = '<div id="showing_amount_text" class="pull-left" style="margin-top: 5px; margin-left: 15px;">Showing '.$start_first_in_page.' to '.$end_last_in_page.' of '.$total_record.' entries</div>';



        $html = '';
        $head_table = '';
        $head_table .= '<table class="table table-striped" id="table_events">
        <thead>
        <tr>
        <!--<th>
        <label>
        <input name="select_all" value="1" id="select-all" type="checkbox" />
        <span class="label-text"></span>
        </label>
        </th>-->
        <th style="width: 50px;">No</th>
        <th style="width: 500px;">Event Name</th>
        <th style="width: 200px;">Group</th>
        <th style="width: 200px;">Tags</th>
        <!--<th>Attr</th>-->
        <th style="width: 100px;">Published</th>
        <th style="width: 120px;">Last Status</th>
        <th style="width: 120px;">DateTime</th>
        <th style="width: 100px;">View</th>
        <th style="width: 120px;">Action</th>
        </tr>
        </thead>
        <tbody>';

        $html .= $head_table;





        $i = $start;
        // dd($cursor->toArray());
        foreach ($cursor as $document) {
            $i++;
            $html .= '
            <tr>
            <!--<td>
            <label>
            <input value="'.$document['_id'].'" type="checkbox" />
            <span class="label-text"></span>
            </label>
            </td>-->
            <td>'.$i.'</td>
            <td style="width: 500px;">'.$document['feedcontent'].'</td>
            <td>'.explode_val($document['feedlink'],'groups').'</td>
            <td>'.explode_val($document['feedlink'],'tags').'</td>
            <!--<td>
            <a href="#"></a>
            </td>-->
            <td>'.check_publish($document['feedlink']).'</td>
            <td>'.check_last_status($document['feedlink']).'</td>
            <td>'.change_date_utc_to_thai($document['feedlink']).'</td>
            <td>'.$document['feedlink'].'</td>
            <td>
           
            </td>
            </tr>
            ';
            // dd($document['_id']);
        }

        $html .= '</tbody>
        </table>';

        // dd($cursor);
        // $cursor = $collection->find($where,['projection'=>['_id'=>0]]);
        // $cursor = $collection->find($where);

        // $model= $cursor->toArray();

        // dd($model);
        // $model = $model[0];
        // unset($model['_id']);


        // $collection = collect(['name', 'public']);
        // $collection = collect([
        //     ['product' => 'Desk', 'price' => 200],
        //     ['product' => 'Chair', 'price' => 100],
        // ]);

        // $collection->paginate(15);
        // dd($collection);

        // $combined = $collection->combine(['George', 1]);
        // $combined->all();
        // dd($combined);
        //  dd($model[0]['TLP']);
        // $model = collect($model);
        //dd($model[0]->TLP);

        // return DataTables::of($collection->toJson())
        // ->editColumn('chk', function ( $collection) {
        //     return '<label><input type="checkbox"  name="events_id" class="events_id" value=""><span class="label-text"></span></label>';
        // })
        // ->addColumn('no', function ( $collection) {
        //     return '-';
        // })
        // ->addColumn('even_name', function ( $collection) {

        //     return $collection->product;
        // })

        // ->addColumn('group', function ( $collection) {
        
        //     return "-";
        // })
        // ->addColumn('tags', function ( $collection) {
        //     return '-';
        // })
        // ->addColumn('attr', function ( $collection) {
        //     return '-';
        // })
        // ->addColumn('published', function ( $collection) {
        //     return '-';
        // })
        // ->addColumn('last_status', function ( $collection) {
        //     return '-';
        // })
        // ->addColumn('date_time', function ( $collection) {
        //     return '-';
        // })
        // ->addColumn('view', function ( $collection) {
        //     return '-';
        // })
        // ->addColumn('action', function ( $collection) {
        //     return '-';
        // })


        // ->rawColumns(['chk', 'no', 'even_name', 'group', 'tags', 'attr', 'published', 'last_status', 'date_time', 'view', 'action'])
        // ->toJson();
        // return $html;
        if ($request->ajax()) {
            $data = [
                "html" => $html,
                "pagination" => $pagination,
                "showing_amount_text" => $showing_amount_text,
            ];
            return response()->json($data);
        }

    }

}
