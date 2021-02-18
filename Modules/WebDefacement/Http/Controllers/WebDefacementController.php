<?php

namespace Modules\WebDefacement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\WebDefacement\Entities\WebdefacmentSetting;
use Modules\WebDefacement\Entities\WebdefacmentDataOriginal;
use Modules\SiteSettings\Entities\SiteSettings;
use Auth;
use Artisan;
use Modules\Users\Entities\User;
use Modules\Users\Entities\UserSite;
use Modules\WebDefacement\Entities\WebdefacmentDataCheck;

class WebDefacementController extends Controller
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
        if(!$role_custom['web_defacement']) {
            check_permission403();
        }
        $data['page'] = langapp('webdefacement');
        // $data['SiteSettings'] = SiteSettings::where("active",1)->where("deleted_at",null)->get();
        // <><><>
        // if(Auth::check()) {

        //     $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
        //     if(Auth::user()->hasRole('admin')) {//if admin
        //         // dd(777);
        //         $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->get();
        //         $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)->get();

        //     } else { //if notAdmin
        //         // dd(888);
        //         if(@Auth::user()->site_role_id && @Auth::user()->site_id) {
        //             if(@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) {//support and admin
        //                 // dd(99);

        //                 $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
        //                 ->whereIn('id', $site_id_arr)//['49', '56']
        //                 ->get();

        //                 $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)
        //                 ->whereIn('id', $site_id_arr)
        //                 ->get();
             

        //             } else {//not support and admin
        //                 $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
        //                 ->whereIn('id', $site_id_arr)//['49', '56']
        //                 ->get();

        //                 $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)
        //                 ->whereIn('id', $site_id_arr)
        //                 ->get();
        //             }
        //         }
        //     }
        // }
        $get_role_custom_first = @get_role_custom();
        $site_id_arr = @$get_role_custom_first['site_id_arr'];
        if(@$get_role_custom_first['superadmin'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->get();
            $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)->get();
        }else if(@$get_role_custom_first['client'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)//['49', '56']
            ->get();

            $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)
            ->get();
        }else if(@$get_role_custom_first['site_support'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)//['49', '56']
            ->get();

            $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)
            ->get();
        }else if(@$get_role_custom_first['site_admin'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)//['49', '56']
            ->get();

            $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)
            ->get();
        }else if(@$get_role_custom_first['site_client'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)//['49', '56']
            ->get();

            $SiteSettings_add = SiteSettings::select('id', 'name')->where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)
            ->get();
        }

        $data['SiteSettings'] = $SiteSettings;
        $data['SiteSettings_add'] = $SiteSettings_add;

        return view('webdefacement::index')->with($data);
    }

    public function detail($code,Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['web_defacement']) {
            check_permission403();
        }
       
        $WebdefacmentSetting = WebdefacmentSetting::where("code",$code)->where('deleted_at', null)->where('active', 1)->with('get_webdefacment_data_original_detail')->with('get_webdefacment_data_check_detail')->with('get_webdefacment_data_log_detail');
        

        
        //<><><>
        // if(Auth::check()) {

        //     $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
        //     if(Auth::user()->hasRole('admin')) {//if admin
        //         // dd(777);
        //         // $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->get();

        //     } else { //if notAdmin
        //         // dd(888);
        //         if(@Auth::user()->site_role_id && @Auth::user()->site_id) {
        //             if(@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) {//support and admin
        //                 // dd(99);

        //                 $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
        //                 ->whereIn('id', $site_id_arr)//['49', '56']
        //                 ->get();
             

        //             } else {//not support and admin
        //                 $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
        //                 ->whereIn('id', $site_id_arr)//['49', '56']
        //                 ->get();
        //             }
        //         }
        //     }
        // }

        $get_role_custom_first = @get_role_custom();
        $site_id_arr = @$get_role_custom_first['site_id_arr'];
        if(@$get_role_custom_first['superadmin'] == 1) {
            
        }else if(@$get_role_custom_first['client'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)//['49', '56']
            ->get();
        }else if(@$get_role_custom_first['site_support'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)//['49', '56']
            ->get();
        }else if(@$get_role_custom_first['site_admin'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)//['49', '56']
            ->get();
        }else if(@$get_role_custom_first['site_client'] == 1) {
            $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)
            ->whereIn('id', $site_id_arr)//['49', '56']
            ->get();
        }

        if(count($site_id_arr) > 0) {
            $WebdefacmentSetting = $WebdefacmentSetting->whereIn('site_id' , $site_id_arr);
        }
        $WebdefacmentSetting = $WebdefacmentSetting->first();
        // dd($WebdefacmentSetting);




        // dd($code);
        $data['page'] = langapp('webdefacement');
        $data['code'] = @$code;
        $data['webdefacement'] = $WebdefacmentSetting;
        // WebdefacmentSetting::where("code",$code)->where('deleted_at', null)->where('active', 1)
        // ->where("hash",1)
        // ->where("filesize",1)
        // ->where("element",1)
        // ->where("blacklist_keyword",1)
        // ->where("image_check",1)
        // ->first();
        $data['webdefacment_data_original']=@$data['webdefacement']->get_webdefacment_data_original_detail[0];
        $data['webdefacment_data_check']=@$data['webdefacement']->get_webdefacment_data_check_detail[0];
        $data['webdefacment_data_log']=@$data['webdefacement']->get_webdefacment_data_log_detail;
        $data['site_code']=@$request->site_code;

            // dd( $data['webdefacement']->blacklist_keyword_content);
        
        return view('webdefacement::detail')->with($data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('webdefacement::create');
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
        return view('webdefacement::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('webdefacement::edit');
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

    public function load_card(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['web_defacement']) {
            check_permission403();
        }
        $html = '';
     
        $modal = WebdefacmentSetting::where("active", '=', 1)->where("deleted_at",null);

        //<><><>
        // if(Auth::check()) {

        //     $site_id_arr = UserSite::select('site_id')->where('user_id', @Auth::user()->id)->get();
        //     if(Auth::user()->hasRole('admin')) {//if admin
        //         // dd(777);
        //         // $SiteSettings = SiteSettings::where("active",1)->where("deleted_at",null)->get();

        //     } else { //if notAdmin
        //         // dd(888);
        //         if(@Auth::user()->site_role_id && @Auth::user()->site_id) {
        //             if(@Auth::user()->site_role_id == 99 || @Auth::user()->site_role_id == 4) {//support and admin
        //                 // dd(99);
        //                 $modal = $modal->whereIn('site_id' , $site_id_arr);
        //             } else {//not support and admin
        //                 $modal = $modal->whereIn('site_id', $site_id_arr);
        //             }
        //         }
        //     }
        // }
        $get_role_custom_first = @get_role_custom();
        $site_id_arr = @$get_role_custom_first['site_id_arr'];
        if(@$get_role_custom_first['superadmin'] == 1) {
            
        }else if(@$get_role_custom_first['client'] == 1) {
            $modal = $modal->whereIn('site_id', $site_id_arr);
        }else if(@$get_role_custom_first['site_support'] == 1) {
            $modal = $modal->whereIn('site_id', $site_id_arr);
        }else if(@$get_role_custom_first['site_admin'] == 1) {
            $modal = $modal->whereIn('site_id', $site_id_arr);
        }else if(@$get_role_custom_first['site_client'] == 1) {
            $modal = $modal->whereIn('site_id', $site_id_arr);
        }


        if ($request->search_ == 1) {
    

            if ($request->keywords) {
                $modal = $modal->where('name', 'LIKE', '%' . $request->keywords . '%')
                ->orWhere('url', 'LIKE', '%' . $request->keywords . '%');
            }

            if ($request->datatype) {
                // dd($request->datatype);
                
                $modal = $modal->whereIn('status_val', $request->datatype);
                // dd($modal);
            }
            
            
        }

        if ($request->site) {
            $modal = $modal->where('site_id', '=', $request->site);
            
        }

        if($request->level){
            if($request->level =='High'){
                $modal = $modal->where('status_val', 'High');
            }
            else if($request->level =='Normal'){
                $modal = $modal->where('status_val', 'Normal');
            }
            else if($request->level =='Medium'){
                $modal = $modal->where('status_val', 'Medium');
            }
            // else{
            //     $modal = $modal;
            // }
        }
            
        $modal = $modal->get();


        foreach ($modal as $key) {
            $html .= 
            '<div class="item-wdfm wdfm-inner-4">
                <div class="wdfm-card">
                    <div class="wdfm-header">
                        <div class="wdfm-img">
                            <a href="'.config('app.URL_CENTER_PUBLISH').@$key->image_last.'" data-lightbox="name-img-2" >
                                <img src="'.config('app.URL_CENTER_PUBLISH').@$key->image_last.'" onerror="setDefaultPic(this)"/>
                            </a>
                        </div>
                    </div>
                    <div class="wdfm-body">
                        <div class="wdfm-btn">
                            <a href="'.route('webdefacement.detail',['code'=>@$key->code]).'" class="btn btn-icon btn-default btn-sm" data-rel="tooltip" title="View" data-placement="bottom">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                        <h4 class="wdfm-elip">'.@$key->name.'</h4>
                        <p class="mdfm-text-muted">'.@$key->url.'</p>
                    </div>
                    <div class="wdfm-footer">
                        <div class="wdfm-ft-left flex">
                            <div><strong>Site </strong>: '.@$key->get_site->name.'</div>
                            <div class="status-flex mr-2"><strong>Status</strong> : &nbsp; '.@get_webdefacment_status($key->status_val,'color').'</div>
                            <div class="text-sm-date">Last Online: '.@$key->last_online.'</div>
                            <div class="text-sm-date">Last Check: '.@$key->last_check.'</div>
                        </div>
                    </div>
                    <div class="wdfm-footer-action">
                        <div style="display: flex;justify-content:center;">';
                
                
            
                $html .= '<a href="'.route('webdefacement.detail',['code' => $key->code]).'" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a>';
                if(@get_role_custom()['superadmin'] == 1 || @get_role_custom()['client'] == 1) {
                    $html .= '<a href="#" onclick="btn_click_edit_webdefacement(\''.$key->code.'\')" class="btn btn-info btn-sm"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.94 74.17l-60.11-60.11c-18.75-18.75-49.16-18.75-67.91 0l-56.55 56.55 128.02 128.02 56.55-56.55c18.75-18.75 18.75-49.15 0-67.91zm-246.8-20.53c-15.62-15.62-40.94-15.62-56.56 0L75.8 172.43c-6.25 6.25-6.25 16.38 0 22.62l22.63 22.63c6.25 6.25 16.38 6.25 22.63 0l101.82-101.82 22.63 22.62L93.95 290.03A327.038 327.038 0 0 0 .17 485.11l-.03.23c-1.7 15.28 11.21 28.2 26.49 26.51a327.02 327.02 0 0 0 195.34-93.8l196.79-196.79-82.77-82.77-84.85-84.85z"></path></svg> Edit</a>
                        <a href="#" onclick="btn_click_del_webdefacement('.$key->id.')" class="btn btn-danger btn-sm btn_del_webdefacment"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z"></path></svg> Delete</a>
                    ';
                }
         

                $html .= '
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

    public function change_status(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['web_defacement']) {
            check_permission403();
        }

        $data = WebdefacmentSetting::where('id', $request->id)->first();
        $data->webdeflacement_progress = 1;
        $data->status_val = 'Normal';
        
        $data->save();

        $webdefacement = WebdefacmentSetting::where('id', $request->id)->first();

        $html='';
        if ($webdefacement->status_val != 'Normal')   {
            $html= '<a href="#" id="accept_risk"
            class="btn btn- '.get_option('theme_color').' btn-sm btn-responsive">
            Accept Risk
            </a>';
        }
        
        return ajaxResponse(
            [
                'html'  => $html,
                'message'  => langapp('changes_saved_successful'),
                // 'redirect' => route('webdefacement.detail',['code' => $webdefacement->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }  

    public function update_original(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['web_defacement']) {
            check_permission403();
        }

        $webdefacment_id = $request->id;


        // dd($port_web);
        $command = 'app:WebDefacementUpdateOriginal';

        $params = [
                'webdefacment_id' => $webdefacment_id,
        ];

            Artisan::call($command, $params);
            $result = Artisan::output();

        
        // return ajaxResponse(
        //     [
        //         'html'  => $html,
        //         'message'  => langapp('changes_saved_successful'),
        //         // 'redirect' => route('webdefacement.detail',['code' => $webdefacement->code]),
        //     ],
        //     true,
        //     Response::HTTP_OK
        // );
    }  

    public function deface_now(Request $request){
        $role_custom = @check_role_custom();
        if(!$role_custom['web_defacement']) {
            check_permission403();
        }
        $webdefacment_id = $request->id;
        $command = 'app:WebDefacementProccessbyWebdefacment_id';
        $params = [
            'webdefacment_id' => $webdefacment_id,
        ];

        Artisan::call($command, $params);
    }

    public function update_original_detail(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['web_defacement']) {
            check_permission403();
        }
        $webdefacement = WebdefacmentSetting::where('id', $request->id)->first();
        $webdefacement_original = WebdefacmentDataOriginal::where('webdefacment_setting_id', $request->id)->orderBy('created_at', 'desc')->first();
        $html_h = $webdefacement_original->hash;
        $html_f = $webdefacement_original->filesize;
        $html_e = $webdefacement_original->element;
        $html_b = $webdefacement->blacklist_keyword_content;
        $html_l = $webdefacement_original->last_update;


        
        return ajaxResponse(
            [
                'html_h'  => $html_h,
                'html_f'  => $html_f,
                'html_e'  => $html_e,
                'html_b'  => $html_b,
                'html_l'  => $html_l,
                'message'  => langapp('changes_saved_successful'),
                // 'redirect' => route('webdefacement.detail',['code' => $webdefacement->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }  

    public function deface_now_detail(Request $request){
        $role_custom = @check_role_custom();
        if(!$role_custom['web_defacement']) {
            check_permission403();
        }
        $webdefacement = WebdefacmentSetting::where('id', $request->id)->first();
        $webdefacement_check = WebdefacmentDataCheck::where('webdefacment_setting_id', $request->id)->first();
        $html_h = $webdefacement_check->hash_new;
        $html_f = formatSizeUnits($webdefacement_check->filesize_new) . ' (Difference ' . $webdefacement_check->filesize_percent . '%)';
        $html_e = $webdefacement_check->element_new;
        $html_b = $webdefacement->blacklist_keyword_current;
        $html_l = $webdefacement_check->last_update;


        
        return ajaxResponse(
            [
                'html_h'  => $html_h,
                'html_f'  => $html_f,
                'html_e'  => $html_e,
                'html_b'  => $html_b,
                'html_l'  => $html_l,
                'message'  => langapp('changes_saved_successful'),
                // 'redirect' => route('webdefacement.detail',['code' => $webdefacement->code]),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function update_image(Request $request)
    {
        $role_custom = @check_role_custom();
        if(!$role_custom['web_defacement']) {
            check_permission403();
        }
        $webdefacement = WebdefacmentSetting::where('id', $request->id)->first();

        if($webdefacement->get_webdefacment_data_original_detail[0]->url_id){
            $url_id = $webdefacement->get_webdefacment_data_original_detail[0]->url_id;
        }else{
            $url_id = 0;
        }
      
        $html = ''; 
        $site_id = $webdefacement->site_id;
        $url_web = $webdefacement->url;
        $port_web = $webdefacement->port;
        // $url_id = $webdefacement->url_id;
        $delay_screenshot_val = $webdefacement->delay_screen_shot_val;
   
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
    }  

    function get_code_site(Request $request){
        
        $SiteSettings = SiteSettings::where("id",$request->id)->first();

        return ajaxResponse(
            [
                'site_code'  => @$SiteSettings->code,

            ],
            true,
            Response::HTTP_OK
        );


    }
}
