<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Entities\site_config_email_alert;

class ApiNewSaveDataController extends Controller
{
    public function saveCVE(Request $request){

    }

    public function saveIndicator(Request $request){

    }

    public function saveSetting(Request $request){
        $code = $request -> code;
        $email_alert = $request -> email_alert;
        $ip = $request -> ip;
        $protocol = $request -> protocol;
        $port = $request -> port;

        $siteSettings = SiteSettings::where('code', $code)->first();
        if(!empty($siteSettings)){
            $siteSettings->server_log_port = trim($request->port);
            $siteSettings->server_log_protocol = trim($request->protocol);
            $siteSettings->server_log_ip = trim($request->ip);
            $siteSettings->save();

            site_config_email_alert::where('site_id', $siteSettings->id)->delete();
            if ($request->email_alert) {
                if (count($request->email_alert) > 0) {
                    foreach ($request->email_alert as $email_alert) {
                        $site_config_email_alert = new site_config_email_alert;
                        $site_config_email_alert->site_id = $siteSettings->id;
                        $site_config_email_alert->email = $email_alert;
                        $site_config_email_alert->save();
                    }
                }
            }
        }
    }
}
