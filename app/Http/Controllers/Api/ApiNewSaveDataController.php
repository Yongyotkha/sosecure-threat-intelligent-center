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
        try {
            $code = $request -> code;

            $siteSettings = SiteSettings::where('code', $code)->first();
            if(!empty($siteSettings)){
                $data_request = $request -> data;
                $data = $this -> dataFalse($data_request);
                $siteSettings->server_log_port = trim($data['data']['port']);
                $siteSettings->server_log_protocol = trim($data['data']['protocol']);
                $siteSettings->server_log_ip = trim($data['data']['ip']);
                $siteSettings->save();
    
                site_config_email_alert::where('site_id', $siteSettings->id)->delete();
                if (!empty($data['data']['email_alert'])) {
                    if (count($data['data']['email_alert']) > 0) {
                        foreach ($data['data']['email_alert'] as $email_alert) {
                            $site_config_email_alert = new site_config_email_alert;
                            $site_config_email_alert->site_id = $siteSettings->id;
                            $site_config_email_alert->email = $email_alert;
                            $site_config_email_alert->save();
                        }
                    }
                }
            }
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200']);  
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'error' => $e->getLine(), 'status_code' => '500']);
        }
    
    }

    private function dataFalse($data){
        try {
            $data_return = [
                'data' => json_decode($data, true),
            ];
            return $data_return;
        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }
}
