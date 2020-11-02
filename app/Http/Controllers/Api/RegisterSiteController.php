<?php

namespace App\Http\Controllers\Api;

use App\DeployCode;
use App\DeployHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\SiteSettings\Entities\SiteSettings;
use App\file_version;

class RegisterSiteController extends Controller
{
    private function AuthorizationRegister($header){
        $site = SiteSettings::where('public_key', '!=', null)->where('public_key', $header)->first();
        if(empty($site)){
            return ['error' => 'Unauthorized', 'status_code' => '401'];
        }else{
            return ['error' => '', 'status_code' => '200', 'data' => $site];
        }
    }

    public function register_site(Request $request){
        $header = $request->bearerToken();
        $site = $this->AuthorizationRegister($header);
        if($site['status_code'] !== '200'){
            return $this->AuthorizationRegister($header);
        }
        
        $value = $request -> data;
        $data = encrypt_decrypt('decrypt', $value, $header, $site['data']->ip_key,  $site['data']->mac_address_key);
        if($data === false){
            return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
        }else{
            $data_key = json_decode($data, true);
            $data_key_decrypt = $this->encrypt_decrypt('decrypt', $data_key['key'], $site['data']->ip_key,  $site['data']->mac_address_key);
            if($data_key_decrypt === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                $site_explode = explode('&', $data_key_decrypt);
                $site = SiteSettings::where('code', $site_explode[0])->first();
                if($site->no_expiration_active === 0){
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $site]);
                }else if($site->no_expiration_active === 1 && ($site->start_active_key <= date("Y-m-d H:i:s") && $site->end_active_key >= date("Y-m-d H:i:s"))){
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $site]);
                }else{
                    return response()->json(['error' => 'The key is invalid', 'status_code' => '401']);
                }
            }
        }
    }

    public function test_get(Request $request){
        $header = $request->bearerToken();
        if($this->AuthorizationRegister($header)['status_code'] !== '200'){
            return $this->AuthorizationRegister($header);
        }

        $ip = '192.168.2.1';
        $mac = 'fe80::8c98:dba3:69f3:2ecb%6';
        $value = '{
            "system_version" : "0.0.0",
            "last_update" : "2020-10-21 13:11:33",
            "main" : 1
        }';
        $data = $this->encrypt_decrypt('encrypt', $value, $ip, $mac);
        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $data]);
    }

    public function site_request_version(Request $request){
        $header = $request->bearerToken();
        $site = $this->AuthorizationRegister($header);
        if($site['status_code'] !== '200'){
            return $this->AuthorizationRegister($header);
        }
        
        $value = $request -> data;
        $data = encrypt_decrypt('decrypt', $value, $header, $site['data']->ip_key,  $site['data']->mac_address_key);
        if($data === false){
            return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
        }else{
            $data_key = json_decode($data, true);
            $data_key_decrypt = $this->encrypt_decrypt('decrypt', $data_key['key'], $site['data']->ip_key,  $site['data']->mac_address_key);
            if($data_key_decrypt === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                $new_version = DeployCode::select('version', 'created_at')->where('status', 1)->where('access_type', 1)->orderBy('version', 'desc')->first();
                $current_version = DeployCode::select('version', 'created_at')->where('version', $data_key_decrypt)->first();
                $update_version = DeployCode::select('code','path')->where('status', 1)->where('access_type', 1)->where('version','>', $data_key_decrypt)->get();
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => ['new_version' => $new_version, 'current_version' => $current_version, 'update_version' => $update_version]]);
            }
        }
    }

    public function save_deploy_history(Request $request){
        $header = $request->bearerToken();
        $site = $this->AuthorizationRegister($header);
        if($site['status_code'] !== '200'){
            return $this->AuthorizationRegister($header);
        }
        
        $value = $request -> data;
        $data = encrypt_decrypt('decrypt', $value, $header, $site['data']->ip_key,  $site['data']->mac_address_key);
        if($data === false){
            return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
        }else{
            $data_key = json_decode($data, true);
            foreach($data_key['code'] as $code){
                $DeployCode = DeployCode::where('code', $code)->first();
                $DeployHistory = new DeployHistory;
                $DeployHistory->code = $DeployCode -> code;
                $DeployHistory->path = $DeployCode -> path;
                $DeployHistory->status = 1;
                $DeployHistory->site_id = $site['data']->id;
                $DeployHistory->save(); 
            }
            $site = SiteSettings::where('id', $site['data']->id)->first(); 
            $site->installed = 1;
            $site->save();
            
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200']);
        }
    }

    private function encrypt_decrypt($action, $string, $ip, $mac) {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'secret-key-!@#$#@!@#$%^' . $ip . '?><!@#$' . $mac;
        $secret_iv = 'secret-iv-!@#$#@!@#$%^' . $ip . '?><!@#$' . $mac;
        // hash
        $key = hash('sha256', $secret_key);
    
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ( $action == 'encrypt' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if( $action == 'decrypt' ) {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }
}
