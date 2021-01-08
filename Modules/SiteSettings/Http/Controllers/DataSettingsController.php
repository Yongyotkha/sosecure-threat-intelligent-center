<?php

namespace Modules\sitesettings\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\Menu;
use Modules\SiteSettings\Entities\Menu_sub;
use Modules\SiteSettings\Entities\site_config_email_alert;
use Modules\SiteSettings\Entities\site_menu_permission;
use Modules\SiteSettings\Entities\site_menu_sub_permission;
use DB;
use Auth;
use Carbon\Carbon;

class DataSettingsController extends Controller
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
    public function datasetting($id)
    {
        $get_data = $this->siteSettings->get_data($id);
        $Menu = Menu::where('deleted_at',null)->where('active',1)->orderBy('order','asc')->get();
        $data['site_user_limit_default'] = DB::table("config")->where("config_key","site_user_limit_default")->first();
        $data['site_domain_limit_default'] = DB::table("config")->where("config_key","site_domain_limit_default")->first();
        $data['site_asset_limit_default'] = DB::table("config")->where("config_key","site_asset_limit_default")->first();
        $result_menu_permission = DB::table("site_menu_permission")->select('menu_code')->where("site_id",$get_data->id)->where("deleted_at",null)->get()->toArray();
        $result_menu_sub_permission = DB::table("site_menu_sub_permission")->select('menu_sub_code')->where("site_id",$get_data->id)->where("deleted_at",null)->get()->toArray();
        // dd($data['site_menu_permission']);
        // var_dump($data['site_menu_permission']);
        // exit();
        $arr_menu_permission = array();
        foreach($result_menu_permission as $row)
        {
            array_push($arr_menu_permission,$row->menu_code);
        }
        $data['site_menu_permission'] = $arr_menu_permission;

        $arr_menu_sub_permission = array();
        foreach($result_menu_sub_permission as $row)
        {
            array_push($arr_menu_sub_permission,$row->menu_sub_code);
        }
        $data['site_menu_sub_permission'] = $arr_menu_sub_permission;

        $data['siteSettings'] = $get_data;
        $data['menus'] = $Menu;
        $data['page'] = 'DataSetting';
        return view('sitesettings::data_setting')->with($data);
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
    public function update($id, Request $request)
    {
        $SiteSettings = SiteSettings::where('code',$id)->first();
        if($request->page_setting == 'site_permission_settings'){
            $SiteSettings->user_allow = $request->site_user_allow ? 'Y' : 'N';
            if($request->user_limit) {
                $SiteSettings->user_limit_amount = $request->user_limit;
            }
            $SiteSettings->role_allow_admin = $request->site_role_allow ? 'Y' : 'N';
            $SiteSettings->domain_allow = $request->site_domain_allow ? 'Y' : 'N';
            if($request->domain_limit) {
                $SiteSettings->domain_limit = $request->domain_limit;
            }
            $SiteSettings->asset_allow = $request->site_asset_allow ? 'Y' : 'N';
            if($request->asset_limit) {
                $SiteSettings->asset_limit = $request->asset_limit;
            }
            $SiteSettings->save();
        }
            // $SiteSettings->active = $request->active ? 1 : 0;
        // }else if($request->page_setting == 'system_settings'){
        //     $SiteSettings->system_web_online = $request->system_web_online ? 1 : 0;
        //     $SiteSettings->system_site_online = $request->system_site_online ? 1 : 0;
        //     $SiteSettings->no_expiration_active = $request->no_expiration_active ? 1 : 0;
        //     if($request->no_expiration_active){
        //         $SiteSettings->start_active_key = Carbon::parse($request->start_active_key);
        //         $SiteSettings->end_active_key = Carbon::parse($request->end_active_key);
        //     }
        //     $SiteSettings->start_active = Carbon::parse($request->start_active);
        //     $SiteSettings->end_active = Carbon::parse($request->end_active);
        //     $SiteSettings->ip_key = $request->ip_key;
        //     $SiteSettings->ip_public = $request->ip_public;
        //     $SiteSettings->mac_address_key = $request->mac_address_key;
        //     $SiteSettings->system_key = $this->encrypt_decrypt('encrypt', $id.'&'.$request->ip_key.'&'.$request->mac_address_key ,$request->ip_key, $request->mac_address_key);
        // }
        // $SiteSettings->save();
        if($request->page_setting == 'site_permission_settings'){
            // SiteCategory::where('site_id', $SiteSettings -> id)->delete();
            // foreach($request->category AS $category) {
            //     $SiteCategory = new SiteCategory;
            //     $SiteCategory->site_id = $SiteSettings->id;
            //     $SiteCategory->category_id = $category;
            //     $SiteCategory->save();
            // }
            site_menu_permission::where('site_id', $SiteSettings -> id)->delete();
            if($request->menu) {
                if(count($request->menu) > 0) {
                    foreach($request->menu AS $menu) {
                        $tb_menu = Menu::select("id")->where("code",$menu)->first();
                        $site_menu_permission = new site_menu_permission;
                        $site_menu_permission->site_id = $SiteSettings->id;
                        $site_menu_permission->menu_id = $tb_menu->id;
                        $site_menu_permission->menu_code = $menu;
                        $site_menu_permission->save();
                    }
                }
            }

            site_menu_sub_permission::where('site_id', $SiteSettings -> id)->delete();
            if($request->menu_sub) {
                if(count($request->menu_sub) > 0) {
                    foreach($request->menu_sub AS $menu_sub) {
                        $tb_menu_sub = Menu_sub::select("id")->where("code",$menu_sub)->first();
                        $site_menu_sub_permission = new site_menu_sub_permission;
                        $site_menu_sub_permission->site_id = $SiteSettings->id;
                        $site_menu_sub_permission->menu_sub_id = $tb_menu_sub->id;
                        $site_menu_sub_permission->menu_sub_code = $menu_sub;
                        $site_menu_sub_permission->save();
                    }
                }
            }

            site_config_email_alert::where('site_id', $SiteSettings -> id)->delete();
            if($request->email_alert) {
                if(count($request->email_alert) > 0) {
                    foreach($request->email_alert AS $email_alert) {
                        $site_config_email_alert = new site_config_email_alert;
                        $site_config_email_alert->site_id = $SiteSettings->id;
                        $site_config_email_alert->email = $email_alert;
                        $site_config_email_alert->save();
                    }
                }
            }


            // Tags_site::where('site_id', $SiteSettings -> id)->delete();
            // foreach($request->tag AS $tag) {
            //     $Tags_site = new Tags_site;
            //     $Tags_site->site_id = $SiteSettings->id;
            //     $Tags_site->tag_id = $tag;
            //     $Tags_site->save();
            // }
    
        }

            return ajaxResponse(
                [
                    'id'       => $SiteSettings->id,
                    'message'  => langapp('changes_saved_successful'),
                    'redirect' => route('datasettings.index', ['id' => $SiteSettings->code]),
                ],
                true,
                Response::HTTP_OK
            );
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
