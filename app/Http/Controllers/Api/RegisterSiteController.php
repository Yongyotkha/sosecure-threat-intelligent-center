<?php

namespace App\Http\Controllers\Api;

use App\DeployCode;
use App\DeployHistory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller\Api;
use Carbon\Carbon;
use Modules\SiteSettings\Entities\SiteSettings;
use App\file_version;
use Modules\Users\Entities\User;
use Modules\Users\Entities\Profile;
use Modules\SiteSettings\Entities\Domain;
use Modules\Users\Entities\UserSite;
use App\Entities\Model_has_roles;
use App\Entities\Permissions;
use App\Entities\Role_permissions;
use App\Entities\Roles;

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
                $data_key_decrypt = $this->encrypt_decrypt('decrypt', $data_key['key'], $site['data']['ip_key'],  $site['data']['mac_address_key']);
                if($data_key_decrypt === false){
                    return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400', 'data' => $data_key['key']]);
                }else{
                    $site_explode = explode('&', $data_key_decrypt);
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
                        $domain_name = 'ไม่ได้ระบุโดเมน';
                        $domain = Domain::where('site_id', $site->id)->where('domain_default', 1)->first();
                        if($domain){
                            $domain_name = $domain -> domain;
                        }
                        return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $site, 'domain' => $domain_name, 'logo' => $logo]);
                    }else{
                        return response()->json(['error' => 'The key is invalid', 'status_code' => '401']);
                    }
                }
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
            try {
                $data_key = json_decode($data, true);
                $data_key_decrypt = $this->encrypt_decrypt('decrypt', $data_key['key'], $site['data']['ip_key'],  $site['data']['mac_address_key']);
                if($data_key_decrypt === false){
                    return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400', 'data' => $data_key]);
                }else{
                    $set_user = [
                        "mysql_user" => $site['data']['mysql_user'],
                        "mysql_password" => $site['data']['mysql_password'],
                        "mongo_user" => $site['data']['mongo_user'],
                        "mongo_password" => $site['data']['mongo_password'],
                    ];
                    $data_username = encrypt_decrypt('encrypt', json_encode($set_user, true), $header, $site['data']['ip_key'],  $site['data']['mac_address_key']);
                    $new_version = DeployCode::select('version', 'created_at')->where('status', 1)->where('access_type', 1)->orderBy('version', 'desc')->first();
                    $current_version = DeployCode::select('version', 'created_at')->where('version', $data_key_decrypt)->first();
                    $update_version = DeployCode::select('code','path')->where('status', 1)->where('access_type', 1)->where('version','>', $data_key_decrypt)->get();
                    $indecator = asset('indicator/indicator.zip');
                    return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => ['new_version' => $new_version, 'current_version' => $current_version, 'update_version' => $update_version, 'indecator' => $indecator, 'site_system' => $data_username]]);
                }
            } catch (\Exception $e) {
                $response = array(
                    'status' => 0,
                    'message' => $e -> getMessage(),
                );
                return response()->json($response);
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
                $site->time_zone = $data_key['timezone'];
                $site->your_app_name = $data_key['app_name'];
                $site->save();
                
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'code' => $site->code, 'key' => $site->public_key]);
            } catch (\Exception $e) {
                $response = array(
                    'status' => 0,
                    'message' => $e -> getMessage(),
                );
                return response()->json($response);
            }
            
        }
    } 

    public function tranferUser(Request $request){
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                $find_datas = User::where('site_id', $data['data']['id'])->get();
                $data_users = json_encode($find_datas);
                $datas = encrypt_decrypt('encrypt', $data_users, $header, $data['data']['ip_key'],  $data['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }

    public function tranferUserSite(Request $request){
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                $find_datas = UserSite::where('site_id', $data['data']['id'])->get();
                $data_users = json_encode($find_datas);
                $datas = encrypt_decrypt('encrypt', $data_users, $header, $data['data']['ip_key'],  $data['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }

    public function tranferSite(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                $find_datas = SiteSettings::where('id', $data['data']['id'])->get();
                $data_users = json_encode($find_datas);
                $datas = encrypt_decrypt('encrypt', $data_users, $header, $data['data']['ip_key'],  $data['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }

    public function tranferProfile(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                $find_datas = Profile::where('id', $data['data']['id'])->get();
                $data_users = json_encode($find_datas);
                $datas = encrypt_decrypt('encrypt', $data_users, $header, $data['data']['ip_key'],  $data['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }

    public function tranferModelHasRoles(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                $UserSites = UserSite::where('site_id', $data['data']['id'])->get();
                $user_ids = [];
                foreach($UserSites as $UserSite){
                    $user_ids[] = $UserSite -> user_id;
                }
                $find_datas = Model_has_roles::whereIn('model_id', $user_ids)->get();
                $data_users = json_encode($find_datas);
                $datas = encrypt_decrypt('encrypt', $data_users, $header, $data['data']['ip_key'],  $data['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }

    public function tranferPermissions(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                $find_datas = Permissions::all();
                $data_users = json_encode($find_datas);
                $datas = encrypt_decrypt('encrypt', $data_users, $header, $data['data']['ip_key'],  $data['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }

    public function tranferRoles(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                $find_datas = Roles::where('id', $data['data']['id'])->get();
                $data_users = json_encode($find_datas);
                $datas = encrypt_decrypt('encrypt', $data_users, $header, $data['data']['ip_key'],  $data['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }

    public function tranferRolePermissions(Request $request){
        try{
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request -> data;
            $data = $this -> dataFalse($header, $mode, $data_request);
            if($data === false){
                return response()->json(['error' => 'The request parameters are invalid', 'status_code' => '400']);
            }else{
                $find_datas = Role_permissions::all();
                $data_users = json_encode($find_datas);
                $datas = encrypt_decrypt('encrypt', $data_users, $header, $data['data']['ip_key'],  $data['data']['mac_address_key']);
                return response()->json(['message' => 'Successful', 'error' => '', 'status_code' => '200', 'data' => $datas]);
            }
        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }

    private function dataFalse($bearerToken, $mode, $data){
        try {
            $header = $bearerToken;
            $site = $this->AuthorizationRegister($header, $mode);
            if($site['status_code'] !== '200'){
                return $this->AuthorizationRegister($header, $mode);
            }
            $value = $data;
            $data = encrypt_decrypt('decrypt', $value, $header, $site['data']['ip_key'],  $site['data']['mac_address_key']);

            if($data === false){
                return $data;
            }else{
                return $site;
            }
           
        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }
}
