<?php

namespace Modules\SiteSettings\Http\Controllers;

use Auth;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\LogsSetting;
use Modules\SiteSettings\Entities\LogsSent;
use Modules\SiteSettings\Http\Requests\VlogsNoSysFormatRequest;
use Modules\SiteSettings\Http\Requests\VlogsSysFormatRequest;

class IndicatorsSettingController extends Controller
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
    public function index($id)
    {
       $get_data = $this->siteSettings->get_data($id);
       
       $get_Logdata = new LogsSetting;
       
       $get_Logdata = $get_Logdata->get_data( $get_data->id,"INDICATOR");

       if($get_Logdata){
           $content=$get_Logdata->content;
       }else{
        $content=null;
       }
       
       $data['content'] = $content;
       $data['siteSettings'] = $get_data;
       $data['LogsSetting'] = $get_Logdata;
       $data['page'] = 'Indicators Logs';
       return view('sitesettings::indicator_logs')->with($data);
    }

    public function upsert_IndicatorsLogs(VlogsNoSysFormatRequest $request, $id = null)
    {

        $get_data = $this->siteSettings->get_data($id);

        $LogsSetting = LogsSetting::where('site_id', $get_data->id)->where('type','INDICATOR')->first();
        if($LogsSetting){
            $LogsSetting->site_id = $get_data->id;
            $LogsSetting->type = "INDICATOR";
            $LogsSetting->ip = $request->ip_address;
            $LogsSetting->link = $request->syslog;
            $LogsSetting->protocal = $request->protocal;
            $LogsSetting->port = $request->port;
            $LogsSetting->save();
        }else{
            $LogsSetting = new LogsSetting;
            $LogsSetting->site_id = $get_data->id;
            $LogsSetting->type = "INDICATOR";
            $LogsSetting->ip = $request->ip_address;
            $LogsSetting->link = $request->syslog;
            $LogsSetting->protocal = $request->protocal;
            $LogsSetting->port = $request->port;
            $LogsSetting->protocal_format = 1;
            $LogsSetting->save();
            
        }
        return ajaxResponse(
            [
                'id'       => $id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('indisetting.indi_logs',['id' => $id]),
            ],
            true,
            Response::HTTP_OK
        );

    }

    public function upsert_IndicatorsSysFormat(VlogsSysFormatRequest $request, $id = null)
    {

        // dd($request->text_protocal_format);
        $get_data = $this->siteSettings->get_data($id);
        $LogsSetting = LogsSetting::where('site_id', $get_data->id)->where('type','INDICATOR')->first();
        if($LogsSetting){
            $LogsSetting->site_id = $get_data->id;
            $LogsSetting->type = "INDICATOR";
            $LogsSetting->content = $request->text_protocal_format;
            $LogsSetting->save();
        }else{
            $LogsSetting = new LogsSetting;
            $LogsSetting->site_id = $get_data->id;
            $LogsSetting->type = "INDICATOR";
            $LogsSetting->content = $request->text_protocal_format;
            $LogsSetting->save();
            
        }
        return ajaxResponse(
            [
                'id'       => $id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('indisetting.indi_logs',['id' => $id]),
            ],
            true,
            Response::HTTP_OK
        );

    }

    public function indicator_log(Request $request, $id = null)
    {

        // dd($request->start_date);
        
        if ($request->start_date) {
            $date_start = $request->start_date;
            $date_end = $request->end_date;

            $date_start_explode = explode(" ", $date_start);
            $date_start_date = @$date_start_explode[0];
            $date_start_time = @$date_start_explode[1].' '.@$date_start_explode[2];
            // dd($date_start_time);
            $date_start_date_format = date("Y-m-d", strtotime($date_start_date));
            // dd($date_start_date_format);
            $date_start_time_time = date("H:i", strtotime($date_start_time));
            $date_start_datetime_format = $date_start_date_format.' '.$date_start_time_time.':00';
            // dd($date_start);

            $date_end_explode = explode(" ", $date_end);
            $date_end_date = @$date_end_explode[0];
            $date_end_time = @$date_end_explode[1].' '.@$date_end_explode[2];
            // dd($date_end_time);
            $date_end_date_format = date("Y-m-d", strtotime($date_end_date));
            $date_end_time_time = date("H:i", strtotime($date_end_time));
            $date_end_datetime_format = $date_end_date_format.' '.$date_end_time_time.':00';
            // dd($date_end_time_time);

            
            // $model = $model->whereBetween('feedtimepost', array($date_start_date_format, $date_end_date_format));
        }

        // dd($request->text_protocal_format);
        $get_data = $this->siteSettings->get_data($id);
        $LogsSetting = LogsSent::where('mode', 'indicator')->first();

        if($LogsSetting->status_progrss != 3) {
            return response()->json(['message' => 'Failed, Send log waiting for operation.!', 'errors' => ['missing' => ["Failed, Send log waiting for operation.! "]]], 500);
        }
        


        if($LogsSetting){
            // $LogsSetting->mode = 'indicator';
            $LogsSetting->start = $date_start_datetime_format;
            $LogsSetting->end = $date_end_datetime_format;
            $LogsSetting->status_progrss = 1;
            $LogsSetting->save();
        }else{
            $LogsSetting = new LogsSent;
            $LogsSetting->mode = 'indicator';
            $LogsSetting->start = $date_start_datetime_format;
            $LogsSetting->end = $date_end_datetime_format;
            $LogsSetting->status_progrss = 1;
            $LogsSetting->save();
            
        }
        return ajaxResponse(
            [
                'id'       => $id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('indisetting.indi_logs',['id' => $id]),
            ],
            true,
            Response::HTTP_OK
        );

    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        
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
        
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        
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

}
