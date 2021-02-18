<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\DeployCode;
use App\DeployHistory;
use Carbon\Carbon;
use Modules\SiteSettings\Entities\SiteSettings;
use App\file_version;
use App\Log;
use Illuminate\Support\Collection;
use Modules\Users\Entities\User;
use Modules\Users\Entities\UserSite;

class ApiController extends Controller
{
    protected function AuthorizationRegister($header, $mode){
        if($mode == 'site_offline'){
            $site = SiteSettings::where('public_key', '!=', null)->where('public_key', $header)->first();
            if(empty($site)){
                return ['error' => 'Unauthorized', 'status_code' => '401'];
            }else{
                if(($site->start_active <= date("Y-m-d H:i:s") && $site->end_active >= date("Y-m-d H:i:s")) && $site->deleted_at == null){
                    return ['error' => '', 'status_code' => '200', 'data' => $site];
                }else{
                    return ['error' => 'Site expried or Site deleted', 'status_code' => '403'];
                }
            }
        }else{
            $items = [
                'ip_key'  => '192.168.0.1',
                'mac_address_key' => '000:000:000:000',
            ];
            $collection = Collection::make($items);
            $site = $collection->toArray();
            return ['error' => '', 'status_code' => '200', 'data' => $site];
        }
    }

    protected function AuthorizationSite($header, $mode, $user_id, $menu){
        if($mode == 'site_offline'){
            $check_permission_site_custom_api = check_permission_site_custom_api($user_id, $menu);
            if($check_permission_site_custom_api === 0){
                return ['error' => "You don't have permission to access", 'status_code' => '403' ];
            }
            $site = SiteSettings::where('public_key', '!=', null)->where('public_key', $header)->first();
            $user_site = UserSite::select('site_id')->where('user_id', $user_id)->where('active', 1)->get();
            $rows_site = 0;
            foreach($user_site as $data){
                if($site -> id == $data -> site_id){
                    $rows_site++;
                }
            }

            if(empty($site)){
                return ['error' => 'Unauthorized', 'status_code' => '401'];
            }else{
                if(($site->start_active <= date("Y-m-d H:i:s") && $site->end_active >= date("Y-m-d H:i:s")) && $site->deleted_at == null){
                    if($rows_site > 0){
                        return ['error' => '', 'status_code' => '200', 'data' => ''];
                    }else{
                        return ['error' => "You don't have permission to access", 'status_code' => '403'];
                    }
                }else{
                    return ['error' => 'Site expried or Site deleted', 'status_code' => '403'];
                }
            }
        }else{
            $items = [
                'ip_key'  => '192.168.0.1',
                'mac_address_key' => '000:000:000:000',
            ];
            $collection = Collection::make($items);
            $site = $collection->toArray();
            return ['error' => '', 'status_code' => '200', 'data' => $site];
        }
    }

    protected function AuthorizationLogin($header, $mode, $code){
        if($mode == 'site_offline'){
            $site = SiteSettings::where('code', $code)->first();
            if(empty($site)){
                return ['error' => 'The request parameters are invalid', 'status_code' => '400'];
            }else{
                if(($site->start_active <= date("Y-m-d H:i:s") && $site->end_active >= date("Y-m-d H:i:s")) && $site->deleted_at == null){
                    return ['error' => '', 'status_code' => '200', 'data' => $site];
                }else{
                    return ['error' => 'Site expried or Site deleted', 'status_code' => '403'];
                }
            }
        }else{
            $site = SiteSettings::where('code', $code)->first();
            if(empty($site)){
                return ['error' => 'The request parameters are invalid', 'status_code' => '400'];
            }else{
                $site['ip_key'] = '192.168.0.1';
                $site['mac_address_key'] = '000:000:000:000';
                return ['error' => '', 'status_code' => '200', 'data' => $site];
            }
        }
    }

    protected function Authorization($header, $mode, $code){
        if($mode == 'site_offline'){
            $user = User::where('access_token', $header)->first();
            if(empty($user)){
                return ['error' => 'Unauthorized', 'status_code' => '401'];
            }else{
                $site = SiteSettings::where('code', $code)->first();
                return ['error' => '', 'status_code' => '200', 'data' => $site];
            }
        }else{
            $user = User::where('access_token', $header)->first();
            if(empty($user)){
                return ['error' => 'Unauthorized', 'status_code' => '401'];
            }else{
                $site = SiteSettings::where('code', $code)->first();
                $site['ip_key'] = '192.168.0.1';
                $site['mac_address_key'] = '000:000:000:000';
                return ['error' => '', 'status_code' => '200', 'data' => $site];
            }
        }
    }

    protected function encrypt_decrypt($action, $string, $ip, $mac) {
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

    protected function saveLog($site_id, $error){
        $dataArr = [
            'site_id' => $site_id,
            'error_summary'  => $error,
        ];
        Log::create($dataArr);

        return true;
    }
}
