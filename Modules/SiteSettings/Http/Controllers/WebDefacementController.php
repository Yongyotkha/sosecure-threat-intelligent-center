<?php

namespace Modules\SiteSettings\Http\Controllers;

use Artisan;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;

class WebDefacementController extends Controller
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
    public function webdefacement_website($id)
    {
       $get_data = $this->siteSettings->get_data($id);
    //    dd($get_data);
       $data['siteSettings'] = $get_data;
       $data['page'] = 'Webdefacement';
       return view('sitesettings::webdefacement_website')->with($data);
    }

    public function load_card_by_site(Request $request)
    {
        $html = ''; 
        $site_id = $request->site_id;
        $modal = WebdefacmentSetting::where("active", '=', 1)->where('site_id', $site_id)->where("deleted_at",null)->get();
        foreach ($modal as $key) {
            $html .= '<div class="item-wdfm wdfm-inner">
                        <div class="wdfm-card">
                            <div class="wdfm-header">
                                <div class="wdfm-img">
                                    <a href="#">
                                        <div class="wdfm-logo" style="background-image:url('.asset($key->image_last).')"></div>
                                    </a>
                                </div>
                            </div>
                            <div class="wdfm-body">
                                <div class="wdfm-btn">
                                    <a href="'.route('webdefacement.detail',['code' => $key->code]).'" class="btn btn-icon btn-default btn-sm" data-rel="tooltip" title="View" data-placement="bottom">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                                <h4>'.@$key->name.'</h4>
                                <p class="mdfm-text-muted">'.@$key->url.'</p>
                            </div>
                            <div class="wdfm-footer start-top">
                                <div class="wdfm-ft-left flex">
                                    <div>Site : '.@$key->get_site->name.'</div>
                                    <div class="status-flex">Status : &nbsp; '.get_webdefacment_status($key->status_val,'color').'</div>
                                    <div>Hash '.@$key->webdefacment_data_original_last($key->id)->hash.'</div>
                                    <div>Filesize '.formatSizeUnits(@$key->webdefacment_data_original_last($key->id)->filesize).'</div>
                                    <div>Element '.@$key->webdefacment_data_original_last($key->id)->element.'</div>
                                    <div>Image Screen 
                                        <a href="'.asset($key->image_last).'" data-lightbox="name-img-2" class="btn btn-info btn-xs"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M569.354 231.631C512.969 135.949 407.81 72 288 72 168.14 72 63.004 135.994 6.646 231.631a47.999 47.999 0 0 0 0 48.739C63.031 376.051 168.19 440 288 440c119.86 0 224.996-63.994 281.354-159.631a47.997 47.997 0 0 0 0-48.738zM288 392c-75.162 0-136-60.827-136-136 0-75.162 60.826-136 136-136 75.162 0 136 60.826 136 136 0 75.162-60.826 136-136 136zm104-136c0 57.438-46.562 104-104 104s-104-46.562-104-104c0-17.708 4.431-34.379 12.236-48.973l-.001.032c0 23.651 19.173 42.823 42.824 42.823s42.824-19.173 42.824-42.823c0-23.651-19.173-42.824-42.824-42.824l-.032.001C253.621 156.431 270.292 152 288 152c57.438 0 104 46.562 104 104z"></path></svg></a>
                                    </div>
                                </div>
                                <div class="wdfm-ft-right flex">
                                    <div>Last Online: '.@$key->last_online.'</div><!-- 10 second ago -->
                                    <div>Last Check: '.@$key->last_check.'</div>
                                    <div>&nbsp;</div>
                                    <div>Last Update: '.@$key->updated_at.'</div>
                                </div>
                            </div>
                            <div class="wdfm-footer-action">
                                <div>
                                    <strong>Update Original</strong>
                                </div>
                                <div class="flex-end">
                                    <a href="" class="btn btn-info btn-sm"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.94 74.17l-60.11-60.11c-18.75-18.75-49.16-18.75-67.91 0l-56.55 56.55 128.02 128.02 56.55-56.55c18.75-18.75 18.75-49.15 0-67.91zm-246.8-20.53c-15.62-15.62-40.94-15.62-56.56 0L75.8 172.43c-6.25 6.25-6.25 16.38 0 22.62l22.63 22.63c6.25 6.25 16.38 6.25 22.63 0l101.82-101.82 22.63 22.62L93.95 290.03A327.038 327.038 0 0 0 .17 485.11l-.03.23c-1.7 15.28 11.21 28.2 26.49 26.51a327.02 327.02 0 0 0 195.34-93.8l196.79-196.79-82.77-82.77-84.85-84.85z"></path></svg> Edit</a>
                                    <a href="" class="btn btn-danger btn-sm"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z"></path></svg> Delete</a>
                                </div>
                            </div>
                        </div>
                    </div>';
                     
  
        }

        if ($request->ajax()) {
            $data = [
                "html" => $html,
            ];
            return response()->json($data);
        }
       
    }

    public function get_check_site(Request $request)
    {
        $html = ''; 
        $site_id = $request->site_id;
        $url_web = $request->url_web;
        $port_web = $request->port_web;
        // dd($port_web);
        $command = 'app:WebDefacementDataCheck';

        $params = [
                'url' => $url_web,
                'port' => $port_web,
                'site_id' => $site_id,
        ];


            Artisan::call($command, $params);
            $result = Artisan::output();
            // dd($result);
        
       
    }



    public function webdefacement_server($id)
    {
       $get_data = $this->siteSettings->get_data($id);
       $data['siteSettings'] = $get_data;
       $data['page'] = 'Webdefacement';
       return view('sitesettings::webdefacement_server')->with($data);
    }

    public function edit_image()
    {
       $data['page'] = 'Webdefacement';
       return view('sitesettings::edit_image')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('sitesettings::create');
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
        return view('sitesettings::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('sitesettings::edit');
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
