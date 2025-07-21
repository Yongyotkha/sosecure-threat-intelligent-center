<?php

namespace Modules\sitesettings\Http\Controllers;

use DB;
use App\FXSiteAgents;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\Menu;
use Modules\SiteSettings\Entities\Menu_sub;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\site_config_email_alert;
use Modules\SiteSettings\Entities\site_menu_permission;
use Modules\SiteSettings\Entities\site_menu_sub_permission;
use App\Entities\Logs_setting;

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
        // dd($get_data);
        $Menu = Menu::where('deleted_at', null)->whereNotIn('id', [8,9,10])->where('active', 1)->orderBy('order', 'asc')->get();
        $data['site_user_limit_default'] = DB::table("config")->where("config_key", "site_user_limit_default")->first();
        $data['site_domain_limit_default'] = DB::table("config")->where("config_key", "site_domain_limit_default")->first();
        $data['site_asset_limit_default'] = DB::table("config")->where("config_key", "site_asset_limit_default")->first();
        $data['site_web_defacement_limit_default'] = DB::table("config")->where("config_key", "site_web_defacement_limit_default")->first();
        $result_menu_permission = DB::table("site_menu_permission")->select('menu_code')->where("site_id", $get_data->id)->where("deleted_at", null)->get()->toArray();
        $result_menu_sub_permission = DB::table("site_menu_sub_permission")->select('menu_sub_code')->where("site_id", $get_data->id)->where("deleted_at", null)->get()->toArray();
        // dd($data['site_menu_permission']);
        // var_dump($data['site_menu_permission']);
        // exit();
        $arr_menu_permission = array();
        foreach ($result_menu_permission as $row) {
            array_push($arr_menu_permission, $row->menu_code);
        }
        $data['site_menu_permission'] = $arr_menu_permission;

        $arr_menu_sub_permission = array();
        foreach ($result_menu_sub_permission as $row) {
            array_push($arr_menu_sub_permission, $row->menu_sub_code);
        }
        $data['site_menu_sub_permission'] = $arr_menu_sub_permission;

        $data['siteSettings'] = $get_data;
        // dd($get_data);
        $data['menus'] = $Menu;
        $data['page'] = 'DataSetting';
        $data['page'] = langapp('data_setting');

        $data['file_agent_name'] = $data['siteSettings']['name'].'_Agent-'.$data['siteSettings']['id'].'.zip';



        $Logs_setting_data =  Logs_setting::where('type', 'indicator')
        ->where('site_id', $get_data->id)->select('content')->first();
        $data['log_format_indicator'] = '[[M]] [[d]] [[H:i:s]] SOSECURE CEF:0|SOSECURE|Threat inSights|1.0|100|[[Event name]]|[[Attribute Type]]=[[Attribute Name]]|Tags=[[Tags]]|Event Description=[[Event Description]]';
        if($Logs_setting_data){
            $data['log_format_indicator'] =$Logs_setting_data->content;

        }

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
        
        $SiteSettings = SiteSettings::where('code', $id)->first();
        if ($request->page_setting == 'site_permission_settings') {
            $SiteSettings->user_allow = $request->site_user_allow ? 'Y' : 'N';
            if ($request->user_limit) {
                $SiteSettings->user_limit_amount = $request->user_limit;
            }
            $SiteSettings->role_allow_admin = $request->site_role_allow ? 'Y' : 'Y';//defalut Y ไว้ ไม่ให้ลูกค้าเลือก
            $SiteSettings->domain_allow = $request->site_domain_allow ? 'Y' : 'N';
            if ($request->domain_limit) {
                $SiteSettings->domain_limit = $request->domain_limit;
            }
            $SiteSettings->asset_allow = $request->site_asset_allow ? 'Y' : 'N';
            if ($request->asset_limit) {
                $SiteSettings->asset_limit = $request->asset_limit;
            }
            $SiteSettings->web_defacement_allow = $request->web_defacement_allow ? 'Y' : 'N';
            if ($request->web_defacement_limit) {
                $SiteSettings->web_defacement_limit = $request->web_defacement_limit;
            }

            $SiteSettings->allow_api_api_loookup = $request->allow_api_api_loookup ? 'Y' : 'N';
            if ($request->web_defacement_limit) {
                $SiteSettings->search_api_loookup_limit = $request->search_api_loookup_limit;
            }

            $SiteSettings->allow_agent = $request->allow_agent ? 'Y' : 'N';
            if ($request->allow_agent_limit) {
                $SiteSettings->agent_count = (int)$request->allow_agent_limit;
            }

            $SiteSettings->server_log_port = trim($request->port);
            $SiteSettings->server_log_protocol = trim($request->protocol);
            $SiteSettings->server_log_ip = trim($request->ip);
            $SiteSettings->log_storage_quotas = $request -> log_storage_quotas;
            $SiteSettings->save();

            //update log sync
            $Logs_setting_Check_Count =  Logs_setting::where('site_id',$SiteSettings->id)->where('type','indicator')->count();
            if($Logs_setting_Check_Count > 0){
                Logs_setting::where('type', 'indicator')
                ->where('site_id', $SiteSettings->id)
                ->update([
                    'content' => $request -> format, // แทน column_name และ new_value ด้วยค่าที่ต้องการ
                    // เพิ่มคอลัมน์อื่น ๆ ถ้าต้องการ
                ]);

            }else{
                $Logs_setting_Create = new Logs_setting();
                $Logs_setting_Create->type ="indicator";
                $Logs_setting_Create->site_id =$SiteSettings->id;
                $Logs_setting_Create->content = $request -> format;
                $Logs_setting_Create->save();


            }
       
            




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
        if ($request->page_setting == 'site_permission_settings') {
            // SiteCategory::where('site_id', $SiteSettings -> id)->delete();
            // foreach($request->category AS $category) {
            //     $SiteCategory = new SiteCategory;
            //     $SiteCategory->site_id = $SiteSettings->id;
            //     $SiteCategory->category_id = $category;
            //     $SiteCategory->save();
            // }

            site_menu_permission::where('site_id', $SiteSettings->id)->delete();
            if ($request->menu) {
                if (count($request->menu) > 0) {
                    foreach ($request->menu as $menu) {
                        $tb_menu = Menu::select("id")->where("code", $menu)->first();
                        $site_menu_permission = new site_menu_permission;
                        $site_menu_permission->site_id = $SiteSettings->id;
                        $site_menu_permission->menu_id = $tb_menu->id;
                        $site_menu_permission->menu_code = $menu;
                        $site_menu_permission->save();
                    }
                }
            }

            site_menu_sub_permission::where('site_id', $SiteSettings->id)->delete();
            if ($request->menu_sub) {
                if (count($request->menu_sub) > 0) {
                    foreach ($request->menu_sub as $menu_sub) {
                        $tb_menu_sub = Menu_sub::select("id")->where("code", $menu_sub)->first();
                        $site_menu_sub_permission = new site_menu_sub_permission;
                        $site_menu_sub_permission->site_id = $SiteSettings->id;
                        $site_menu_sub_permission->menu_sub_id = $tb_menu_sub->id;
                        $site_menu_sub_permission->menu_sub_code = $menu_sub;
                        $site_menu_sub_permission->save();
                    }
                }
            }

            site_config_email_alert::where('site_id', $SiteSettings->id)->delete();
            if ($request->email_alert) {
                if (count($request->email_alert) > 0) {
                    foreach ($request->email_alert as $email_alert) {
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
                'id' => $SiteSettings->id,
                'message' => langapp('changes_saved_successful'),
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
