<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Users\Entities\model_has_roles;
use Modules\Users\Entities\User;
use Modules\Users\Entities\UserSite;

class ApiAgentController extends ApiController
{
    public function dataInfo(Request $request){
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                $response =[
                    'error' => 'The request parameters are invalid',
                    'status_code' => 400,
                    'data' => []
                ];
            } else {
                $response = [
                    'error' => '', 
                    'status_code' => 200,
                    'data' => []
                ];
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        }
    }

    public function loginAgent(Request $request){
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            
            $site = $this->AuthorizationLogin($header, $request->mode, $request->code);
            if($site['status_code'] !== '200'){
                return $this->AuthorizationLogin($header, $request->mode, $request->code);
            }

            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                $response =[
                    'error' => 'The request parameters are invalid',
                    'status_code' => 400,
                    'data' => []
                ];
            } else {
                $data_key = json_decode($data, true);
                $user_check = User::where('email', $data_key['email'])->where('email_verified_at','!=',null)->where('banned',0)->where('deleted_at',null)->where('active',1)->where('verify',1)->first();
                $UserSite = UserSite::where('user_id',@$user_check->id)->where('active',1)->get()->pluck('site_id')->toArray();

                $role_status = 0;
                $user = User::where('email', $data_key['email'])->where('site_id', $site['data']['id'])->where('email_verified_at','!=',null)->where('banned',0)->where('deleted_at',null)->where('active',1)->where('verify',1);
                $user = $user->where(function($q) {
                    $q->whereNull('password_time_expire');
                    $q->orWhereDate('password_time_expire', '<=', date('Y-m-d H:i:s'));
                });
                $user = $user->whereHas('get_user_site_many', function($q) use ($UserSite) {
                    $q->whereIn('site_id', $UserSite);
                });

                $user = $user->first();
                $model_has_roles = model_has_roles::where('role_id',@$user->get_model_has_roles->role_id)->first();
                if($model_has_roles) {
                    if($model_has_roles->role_id == 1 || $model_has_roles->role_id == 2) {
                        $role_status = 0;
                    } else if($model_has_roles->role_id == 4 || $model_has_roles->role_id == 5 || $model_has_roles->role_id == 6 || $model_has_roles->role_id == 9 || $model_has_roles->role_id == 10) {
                        $role_status = 1;
                    } else {
                        $role_status = 0;
                    }
                }

                $response = [
                    'error' => '', 
                    'status_code' => 200,
                    'data' => [
                        'user' => $user
                    ]
                ];
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'status' => 0,
                'message' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
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
                $data_return = [
                    'site' => $site,
                    'data' => json_decode($data, true),
                ];
                return $data_return;
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
