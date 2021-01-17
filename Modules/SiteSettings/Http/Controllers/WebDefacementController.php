<?php

namespace Modules\SiteSettings\Http\Controllers;

use Auth;
use Artisan;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\WebDefacement\Entities\WebdefacmentImageMark;

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
                                        <img src="'.asset($key->image_last).'" onerror="setDefaultPic(this)"/>
                                    </a>
                                </div>
                            </div>
                            <div class="wdfm-body">
                                <div class="wdfm-btn">
                                    <a href="'.route('webdefacement.detail',['code' => $key->code]).'" class="btn btn-icon btn-default btn-sm" data-rel="tooltip" title="View" data-placement="bottom">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                                <h4 class="wdfm-elip">'.@$key->name.'</h4>
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
                                    <div class="text-sm-date" style="margin-top:5px;">Last Online: '.@$key->last_online.'</div><!-- 10 second ago -->
                                    <div class="text-sm-date">Last Check: '.@$key->last_check.'</div>
                                    <div class="text-sm-date">Last Update: '.@$key->updated_at.'</div>
                                </div>
                           
                            </div>
                            <div class="wdfm-footer-action">
                                <div>
                                    <strong>Update Original</strong>
                                </div>
                                <div class="flex-end">
                                    <a href="'.route('webdefacement.detail',['code' => $key->code]).'" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a>
                                    <a href="#" onclick="btn_click_edit_webdefacement(\''.$key->code.'\')" class="btn btn-info btn-sm"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.94 74.17l-60.11-60.11c-18.75-18.75-49.16-18.75-67.91 0l-56.55 56.55 128.02 128.02 56.55-56.55c18.75-18.75 18.75-49.15 0-67.91zm-246.8-20.53c-15.62-15.62-40.94-15.62-56.56 0L75.8 172.43c-6.25 6.25-6.25 16.38 0 22.62l22.63 22.63c6.25 6.25 16.38 6.25 22.63 0l101.82-101.82 22.63 22.62L93.95 290.03A327.038 327.038 0 0 0 .17 485.11l-.03.23c-1.7 15.28 11.21 28.2 26.49 26.51a327.02 327.02 0 0 0 195.34-93.8l196.79-196.79-82.77-82.77-84.85-84.85z"></path></svg> Edit</a>
                                    <a href="#" onclick="btn_click_del_webdefacement('.$key->id.')" class="btn btn-danger btn-sm btn_del_webdefacment"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z"></path></svg> Delete</a>
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

    public function WebDefacement_edit_data(Request $request){
        $WebdefacmentSetting = WebdefacmentSetting::select('site_id','id','name','url','port','hash','filesize','element','blacklist_keyword','image_check','blacklist_keyword_content','delay_screen_shot_val')->where('code', $request -> id)->first();
        $site = SiteSettings::find($WebdefacmentSetting -> site_id);
        $webdefacment_data_original = WebdefacmentDataOriginal::where('webdefacment_setting_id',$WebdefacmentSetting -> id)->orderBy('id','desc')->first();
        $WebdefacmentSetting -> site_code = $site -> code;
        $WebdefacmentSetting -> image = $webdefacment_data_original -> image;
        $WebdefacmentSetting -> part_image = $webdefacment_data_original -> part_image;
        if($WebdefacmentSetting){
            $response = [
                'message' => 'Successful', 
                'error' => '', 
                'status_code' => '200', 
                'data' => $WebdefacmentSetting
            ];
        }else{
            $response = [
                'message' => 'Not Found', 
                'error' => '', 
                'status_code' => '404', 
                'data' => ''
            ];
        }
        
        return response()->json($response);
    }

    public function delete_websefacement_process(Request $request)
    {
        $id = $request->id;
        // dd($code);

        $WebdefacmentSetting = WebdefacmentSetting::where('id', $id)->first();
        $model = WebdefacmentSetting::where('id', $id)->delete();
        // $model->softDeletes();

        if($WebdefacmentSetting) {
            $site_id = $WebdefacmentSetting->site_id;
            $SiteSettings = SiteSettings::where('id',$site_id)->first();
            if($SiteSettings) {
                $site_code = $SiteSettings->code;
            }
        }

        return ajaxResponse(
            [
                'message' => langapp('changes_saved_successful'),
                'redirect' => route('webdefacement_website.index',['id' => $site_code]),
            ],
            true,
            Response::HTTP_OK
        );
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

    public function get_check_image_screenshot(Request $request)
    {
        $html = ''; 
        $site_id = $request->site_id;
        $url_web = $request->url_web;
        $port_web = $request->port_web;
        $url_id = $request->url_id;
        $delay_screenshot_val = $request->delay_screenshot_val;
        $webdefacment_setting_id = $request->webdefacment_setting_id;


        // dd($port_web);
        $command = 'app:WebDefacementsCreenshotCheck';

        $params = [
                'url' => $url_web,
                'port' => $port_web,
                'site_id' => $site_id,
                'url_id' => $url_id,
                'delay' => $delay_screenshot_val,
        ];

            Artisan::call($command, $params);
            $result = Artisan::output();

            // $data_arr = json_decode($result);
            // dd($result);

    }


    public function get_update_image_screenshot(Request $request)
    {
        $html = ''; 
        $webdefacment_setting_id = $request->webdefacment_setting_id;
        $image = $request->image;
        $part_image = $request->part_image;


        // dd($port_web);



            if($webdefacment_setting_id) {
                $webdefacment_data_original = WebdefacmentDataOriginal::where('deleted_at',null)->where('webdefacment_setting_id',$webdefacment_setting_id)->orderBy('id','desc')->first();

                if($webdefacment_data_original) {
                    $webdefacment_data_original->webdefacment_setting_id = $webdefacment_setting_id;
                    $webdefacment_data_original->image = $image;
                    $webdefacment_data_original->part_image = $part_image;
                    $webdefacment_data_original->save();
        
                } else {
                    $webdefacment_data_original = new WebdefacmentDataOriginal;
                    $webdefacment_data_original->webdefacment_setting_id = $webdefacment_setting_id;
                    $webdefacment_data_original->image = $image;
                    $webdefacment_data_original->part_image = $part_image;
                    $webdefacment_data_original->save();
                }
                if($webdefacment_data_original) {

                    $response = array(
                        'message' => '', 
                        'status_code' => '200',
                        'data' => $webdefacment_data_original
                    );
        
        
                    return response()->json($response);
                }
            }else{
                return response()->json(['error' => 'Not Found', 'status_code' => '404']);
            }


    }


    public function WebDefacement_create_data(Request $request) {
        $name_web = $request->name_web;
        $url_web = $request->url_web;
        $port_web = $request->port_web;
        $hash = ($request->hash == 'true') ? 1 : 0;
        $file_size = ($request->file_size == 'true') ? 1 : 0;
        $element = ($request->element == 'true') ? 1 : 0;
        $blacklist = ($request->blacklist == 'true') ? 1 : 0;
        $blacklist_text = $request->blacklist_text;
        $delay_screen_shot = ($request->delay_screen_shot == 'true') ? 1 : 0;
        $delay_screenshot_val = $request->delay_screenshot_val;
        $site_id = $request->site_id;
        $webdefacment_setting_id = $request->webdefacment_setting_id;


        // dd($file_size);
        if($webdefacment_setting_id) {
            $WebdefacmentSetting = WebdefacmentSetting::where('id',$webdefacment_setting_id)->first();
            $WebdefacmentSetting->name = $name_web;
            if($request->mode == 'create'){
                $WebdefacmentSetting->url = $url_web;
                $WebdefacmentSetting->port = $port_web;
            }
           
            $WebdefacmentSetting->hash = $hash;
            $WebdefacmentSetting->filesize = $file_size;
            $WebdefacmentSetting->element = $element;
            $WebdefacmentSetting->DomainHeaders = '';
            $WebdefacmentSetting->blacklist_keyword = $blacklist;
            $WebdefacmentSetting->image_check = $delay_screen_shot;
            if($delay_screen_shot == 1){
                $WebdefacmentSetting->delay_screen_shot_val = $delay_screenshot_val;
            }else{
                $WebdefacmentSetting->delay_screen_shot_val = null;
            }
            if($blacklist == 1){
                $WebdefacmentSetting->blacklist_keyword_content = $blacklist_text;
            }else{
                $WebdefacmentSetting->blacklist_keyword_content = null;
            }
            
            if($request->mode == 'create'){
                $WebdefacmentSetting->site_id = $site_id;
                $WebdefacmentSetting->active = 1;
                $WebdefacmentSetting->domain = '';
                $WebdefacmentSetting->user_agent = '';
                $WebdefacmentSetting->webdeflacement_progress = 1;
                $WebdefacmentSetting->image_last = '';
                $WebdefacmentSetting->status_add = 1;
            }
            $WebdefacmentSetting->save();
        } else {
            // $WebdefacmentSetting = new WebdefacmentSetting;
            // $WebdefacmentSetting->code = generator_uuid();
            // $WebdefacmentSetting->name = $name_web;
            // $WebdefacmentSetting->url = $url_web;
            // $WebdefacmentSetting->port = $port_web;
            // $WebdefacmentSetting->hash = $hash;
            // $WebdefacmentSetting->filesize = $file_size;
            // $WebdefacmentSetting->element = $element;
            // $WebdefacmentSetting->DomainHeaders = '';
            // $WebdefacmentSetting->blacklist_keyword = $blacklist;
            // $WebdefacmentSetting->image_check = $delay_screen_shot;
            // $WebdefacmentSetting->delay_screen_shot_val = $delay_screenshot_val;
            // $WebdefacmentSetting->blacklist_keyword_content = $blacklist_text;
            // $WebdefacmentSetting->site_id = $site_id;
            // $WebdefacmentSetting->active = 1;
            // $WebdefacmentSetting->domain = '';
            // $WebdefacmentSetting->user_agent = '';
            // $WebdefacmentSetting->webdeflacement_progress = 3;
            // $WebdefacmentSetting->image_last = '';
            // $WebdefacmentSetting->user_id = @Auth::user()->id;
            // $WebdefacmentSetting->status_add = 1;
            // $WebdefacmentSetting->save();
        }
       
        if($request->channel == 'main_webdefacement'){
            return ajaxResponse(
                [
                    'id'       => $WebdefacmentSetting->id,
                    'message'  => langapp('saved_successfully'),
                    'redirect' =>route('webdefacement.index'),
                ],
                true,
                Response::HTTP_CREATED
            );
        }else{
            $SiteSettings = SiteSettings::select('code')->where('id',$site_id)->first();
            return ajaxResponse(
                [
                    'id'       => $WebdefacmentSetting->id,
                    'message'  => langapp('saved_successfully'),
                    'redirect' =>route('webdefacement_website.index', ['id' => $SiteSettings->code]),
                ],
                true,
                Response::HTTP_CREATED
            );
        }
       
    }

    private function webDefacementUpdateOriginal($webdefacment_setting_id){
        $command = 'app:WebDefacementUpdateOriginal';
        $params = [
                'webdefacment_id' => $webdefacment_setting_id,
        ];
        Artisan::call($command, $params);
    }

    private function webDefacementsCreenshotCheck($url_web, $port_web, $site_id, $url_id, $delay_screenshot_val){
        $command_2 = 'app:WebDefacementsCreenshotCheck';
        $params_2 = [
                'url' => $url_web,
                'port' => $port_web,
                'site_id' => $site_id,
                'url_id' => $url_id,
                'delay' => $delay_screenshot_val,
        ];

        Artisan::call($command_2, $params_2);
    }

    public function get_create_open_md_site_url(Request $request) {

        $site_id = $request->site_id;
        $WebdefacmentSetting = WebdefacmentSetting::where('site_id',$site_id)->where('status_add',0)->where('user_id',@Auth::user()->id)->orderBy('id','desc')->first();

        if($WebdefacmentSetting) {
            

        } else {
            $WebdefacmentSetting = new WebdefacmentSetting;
            $WebdefacmentSetting->code = generator_uuid();
            $WebdefacmentSetting->site_id = $site_id;
            $WebdefacmentSetting->user_id = @Auth::user()->id;
            $WebdefacmentSetting->status_add = 0;
            $WebdefacmentSetting->active = 0;
            $WebdefacmentSetting->save();
        }


        $webdefacment_data_original = WebdefacmentDataOriginal::where('deleted_at',null)->where('webdefacment_setting_id',$WebdefacmentSetting->id)->orderBy('id','desc')->first();

        if($webdefacment_data_original) {
            

        } else {
            $webdefacment_data_original = new WebdefacmentDataOriginal;
            $webdefacment_data_original->webdefacment_setting_id = $WebdefacmentSetting->id;
            $webdefacment_data_original->save();
        }



        if($WebdefacmentSetting) {

            $response = array(
                'error' => '', 
                'status_code' => '200',
                'data' => $WebdefacmentSetting
            );


            return response()->json($response);
        }else{
            return response()->json(['error' => 'Not Found', 'status_code' => '404']);
        }

        
    }



    public function webdefacement_server($id)
    {
       $get_data = $this->siteSettings->get_data($id);
       $data['siteSettings'] = $get_data;
       $data['page'] = 'Webdefacement';
       return view('sitesettings::webdefacement_server')->with($data);
    }

    public function edit_image($site_id, $id)
    {
        $data['page'] = 'Webdefacement';
        $site_code = $this->siteSettings->find_id($site_id);
        if($site_code){
            $WebdefacmentDataOriginal = WebdefacmentDataOriginal::select('id', 'webdefacment_setting_id', 'image')
            ->where('webdefacment_setting_id', $id)
            ->whereHas('get_webdefacment_setting', function($query) use($site_code){
                $query->where('site_id', $site_code -> id);
            })
            ->first();
            if($WebdefacmentDataOriginal){
                $data['web_defacment_original'] = $WebdefacmentDataOriginal;
                return view('sitesettings::edit_image')->with($data);
            }else{
                abort(404);
            }
        }else{
            abort(404);
        }
    }   

    public function save_item(Request $request){
        $WebdefacmentImageMark = new WebdefacmentImageMark();
        $WebdefacmentImageMark -> webdefacment_data_original_id = $request -> webdefacment_data_original_id;
        $WebdefacmentImageMark -> top = $request -> top;
        $WebdefacmentImageMark -> left = $request -> left;
        $WebdefacmentImageMark -> hight = $request -> height;
        $WebdefacmentImageMark -> width = $request -> width;
        $WebdefacmentImageMark -> save();

        $res = [
            'data' => $WebdefacmentImageMark -> id
        ];
        return response()->json($res);
    }

    public function update_item_top_left(Request $request){
        $WebdefacmentImageMark = WebdefacmentImageMark::find($request->web_defacment_image_mark_id);
        if($WebdefacmentImageMark){
            $WebdefacmentImageMark -> top = $request -> top;
            $WebdefacmentImageMark -> left = $request -> left;
            $WebdefacmentImageMark -> save();
            $response = array(
                'error' => '', 
                'status_code' => '200',
                'data' => ''
            );
            return response()->json($response);
        }else{
            return response()->json(['error' => 'Not Found', 'status_code' => '404']);
        }
    }

    public function update_item_width_height(Request $request){
        $WebdefacmentImageMark = WebdefacmentImageMark::find($request->web_defacment_image_mark_id);
        if($WebdefacmentImageMark){
            $WebdefacmentImageMark -> hight = $request -> height;
            $WebdefacmentImageMark -> width = $request -> width;
            $WebdefacmentImageMark -> save();
            $response = array(
                'error' => '', 
                'status_code' => '200',
                'data' => ''
            );
            return response()->json($response);
        }else{
            return response()->json(['error' => 'Not Found', 'status_code' => '404']);
        }
    }

    public function get_image_data(Request $request){
        $WebdefacmentImageMark = WebdefacmentImageMark::where('webdefacment_data_original_id', $request->webdefacment_data_original_id)->get();
        if($WebdefacmentImageMark){
            $response = array(
                'error' => '', 
                'status_code' => '200',
                'data' => $WebdefacmentImageMark
            );
            return response()->json($response);
        }else{
            return response()->json(['error' => 'Not Found', 'status_code' => '404']);
        }
    }

    public function remove_item(Request $request){
        $WebdefacmentImageMark = WebdefacmentImageMark::find($request->web_defacment_image_mark_id);
        if($WebdefacmentImageMark){
            $WebdefacmentImageMark -> delete();
            $response = array(
                'error' => '', 
                'status_code' => '200',
                'data' => $request->web_defacment_image_mark_id
            );
            return response()->json($response);
        }else{
            return response()->json(['error' => 'Not Found', 'status_code' => '404']);
        }
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
