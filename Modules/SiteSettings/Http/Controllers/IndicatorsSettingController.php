<?php

namespace Modules\SiteSettings\Http\Controllers;

use Auth;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\LogsSetting;
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
        $get_data = $this->siteSettings->get_data($id);
        $LogsSetting = LogsSetting::where('site_id', $get_data->id)->where('type','INDICATOR')->first();
        if($LogsSetting){
            $LogsSetting->site_id = $get_data->id;
            $LogsSetting->type = "INDICATOR";
            $LogsSetting->protocal_format = $request->protocal_format;
            $LogsSetting->save();
        }else{
            $LogsSetting = new LogsSetting;
            $LogsSetting->site_id = $get_data->id;
            $LogsSetting->type = "INDICATOR";
            $LogsSetting->protocal_format = $request->protocal_format;
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
