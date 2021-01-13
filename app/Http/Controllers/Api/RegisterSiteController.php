<?php

namespace App\Http\Controllers\Api;

use App\DeployCode;
use App\DeployHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller\Api;
use Carbon\Carbon;
use Modules\SiteSettings\Entities\SiteSettings;
use App\file_version;
use Modules\SiteSettings\Entities\Domain;

class RegisterSiteController extends ApiController
{
    public function register_site(Request $request){
        $header = $request->bearerToken();
        $site = $this->AuthorizationRegister($header, $request->mode);
        if($site['status_code'] !== '200'){
            return $this->AuthorizationRegister($header, $request->mode);
        }
        
        $value = $request -> data;
        $data = encrypt_decrypt('decrypt', $value, $header, $site['data']['ip_key'],  $site['data']['mac_address_key']);
        if($data === false){
            return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
        }else{
            try {
                $data_key = json_decode($data, true);
                // $data_key_decrypt = $this->encrypt_decrypt('decrypt', $data_key['key'], $site['data']['ip_key'],  $site['data']['mac_address_key']);
                // if($data_key_decrypt === false){
                //     return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400', 'data' => $data_key['key']]);
                // }else{
                //     $site_explode = explode('&', $data_key_decrypt);
                    $site = SiteSettings::where('code', $site['data']['code'])->first();
                    $logo = url('/').'/'.$site->logo;
                    if($site->no_expiration_active === 0){
                        $domain_name = 'ไม่ได้ระบุโดเมน';
                        $domain = Domain::where('site_id', $site->id)->where('domain_default', 1)->first();
                        if($domain){
                            $domain_name = $domain -> domain;
                        }
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $site, 'domain' => $domain_name, 'logo' => $logo]);
                    }else if($site->no_expiration_active === 1 && ($site->start_active_key <= date("Y-m-d H:i:s") && $site->end_active_key >= date("Y-m-d H:i:s"))){
                        $domain = Domain::where('site_id', $site->id)->where('domain_default', 1)->first();
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $site, 'domain' => $domain_name, 'logo' => $logo]);
                    }else{
                        return response()->json(['error' => 'The key is invalid', 'status_code' => '401']);
                    }
                // }
            } catch (\Exception $e) {
                $response = array(
                    'status' => 0,
                    'message' => $e -> getMessage(),
                );
                return response()->json($response);
            }
        }
    }

    public function site_request_version(Request $request){
        $header = $request->bearerToken();
        $site = $this->AuthorizationRegister($header, $request->mode);
        if($site['status_code'] !== '200'){
            return $this->AuthorizationRegister($header, $request->mode);
        }
        
        $value = $request -> data;
        $data = encrypt_decrypt('decrypt', $value, $header, $site['data']['ip_key'],  $site['data']['mac_address_key']);
        if($data === false){
            return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
        }else{
            $data_key = json_decode($data, true);
            $data_key_decrypt = $this->encrypt_decrypt('decrypt', $data_key['key'], $site['data']['ip_key'],  $site['data']['mac_address_key']);
            if($data_key_decrypt === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400', 'data' => $data_key]);
            }else{
                $new_version = DeployCode::select('version', 'created_at')->where('status', 1)->where('access_type', 1)->orderBy('version', 'desc')->first();
                $current_version = DeployCode::select('version', 'created_at')->where('version', $data_key_decrypt)->first();
                $update_version = DeployCode::select('code','path')->where('status', 1)->where('access_type', 1)->where('version','>', $data_key_decrypt)->get();
                $indecator = asset('indicator/indicator.zip');
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => ['new_version' => $new_version, 'current_version' => $current_version, 'update_version' => $update_version, 'indecator' => $indecator]]);
            }
        }
    }

    public function save_deploy_history(Request $request){
        $header = $request->bearerToken();
        $site = $this->AuthorizationRegister($header, $request->mode);
        if($site['status_code'] !== '200'){
            return $this->AuthorizationRegister($header, $request->mode);
        }
        
        $value = $request -> data;
        $data = encrypt_decrypt('decrypt', $value, $header, $site['data']['ip_key'],  $site['data']['mac_address_key']);
        if($data === false){
            return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
        }else{
            $data_key = json_decode($data, true);
            foreach($data_key['code'] as $code){
                $DeployCode = DeployCode::where('code', $code)->first();
                $DeployHistory = new DeployHistory;
                $DeployHistory->code = $DeployCode -> code;
                $DeployHistory->path = $DeployCode -> path;
                $DeployHistory->version = $DeployCode -> version;
                $DeployHistory->status = 1;
                $DeployHistory->site_id = $site['data']['id'];
                $DeployHistory->save(); 
            }
            $site = SiteSettings::where('id', $site['data']['id'])->first(); 
            $site->installed = 1;
            $site->save();
            
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'code' => $site->code, 'key' => $site->public_key]);
        }
    } 

    public function save_deploy_system(Request $request){
        $header = $request->bearerToken();
        $site = $this->AuthorizationRegister($header, $request->mode);
        if($site['status_code'] !== '200'){
            return $this->AuthorizationRegister($header, $request->mode);
        }
        
        $value = $request -> data;
        $data = encrypt_decrypt('decrypt', $value, $header, $site['data']['ip_key'],  $site['data']['mac_address_key']);
        if($data === false){
            return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
        }else{
            try {
                $data_key = json_decode($data, true);
                $site = SiteSettings::where('id', $site['data']['id'])->first(); 
                $site->laravel_version = $data_key['laravel_version'];
                $site->os = $data_key['os'];
                $site->server_time = $data_key['server_time']['date'];
                $site->php_version = $data_key['php_version'];
                $site->code_version = $data_key['code_version'];
                $site->save();
                
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200']);
            } catch (\Exception $e) {
                $response = array(
                    'status' => 0,
                    'message' => $e -> getMessage(),
                );
                return response()->json($response);
            }
            
        }
    } 
}
