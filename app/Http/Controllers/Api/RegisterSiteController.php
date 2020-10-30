<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\SiteSettings\Entities\SiteSettings;
use App\file_version;

class RegisterSiteController extends Controller
{
    public function register_site(Request $request){
        $ip = '192.168.2.1';
        $mac = 'fe80::8c98:dba3:69f3:2ecb%6';
        $value = $request -> key;
        $data = $this->encrypt_decrypt('decrypt', $value, $ip, $mac);
        if($data === false){
            return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
        }else{
            $site_explode = explode('&', $data);
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

    public function test_get(Request $request){
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
        $ip = '192.168.2.1';
        $mac = 'fe80::8c98:dba3:69f3:2ecb%6';
        $value = $request -> key;
        $data = $this->encrypt_decrypt('decrypt', $value, $ip, $mac);
        if($data === false){
            return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
        }else{
            $data_version = json_decode($data, true);
            $file_version = file_version::where('main', 1)->orderBy('created_at', 'desc')->first();
            return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => ['new_version' => $file_version , 'current_version' => $data_version]]);
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
