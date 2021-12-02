<?php

namespace App\Http\Controllers\Api;

use App\FXSiteAgents;
use App\RuleFile;
use App\RuleSite;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Hautelook\Phpass\PasswordHash;
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
                $data_key = $data['data'];
                $device_name = $data_key['device_name'];
                $os_type = $data_key['os_type'];
                $os_description = $data_key['os_description'];
                $system_info = $data_key['system_info'];
                $domain = $data_key['domain'];
                $ip_private = $data_key['ip_private'];
                $siteAgentsHasData = FXSiteAgents::where('site_id', $data['site']['data']['id'])->where('ip_private', $ip_private)->first();
                if(empty($siteAgentsHasData)){
                    $siteAgents = new FXSiteAgents();
                    $siteAgents -> site_id = $data['site']['data']['id'];
                    $siteAgents -> device_name = $device_name;
                    $siteAgents -> os_type = $os_type;
                    $siteAgents -> os_description = $os_description;
                    $siteAgents -> system_info = $system_info;
                    $siteAgents -> domain = $domain;
                    $siteAgents -> ip_private = $ip_private;
                    $siteAgents -> status = 0;
                    $siteAgents -> save();

                    $response = [
                        'error' => '', 
                        'status_code' => 200,
                        'data' => $siteAgents
                    ];
                }else{
                    $response = [
                        'error' => 'Has data site agent', 
                        'status_code' => 200,
                        'data' => []
                    ];
                }
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'message' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function loginAgent(Request $request){
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            
            $site = $this->AuthorizationAgent($header, $request->code);
            if($site['status_code'] !== '200'){
                return $this->AuthorizationAgent($header, $request->code);
            }

            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                $response =[
                    'error' => 'The request parameters are invalid',
                    'status_code' => 400,
                    'data' => []
                ];
            } else {
                $data_key = $data['data'];
                $user = User::where('email', $data_key['email'])->where('email_verified_at','!=',null)->where('banned',0)
                ->where('deleted_at',null)->where('active',1)
                ->where('verify',1)->where('site_id', $data['site']['data']['id'])
                ->where('site_role_id', 6)
                ->where(function($q) {
                    $q->whereNull('password_time_expire');
                    $q->orWhereDate('password_time_expire', '<=', date('Y-m-d H:i:s'));
                })->first();

                if ($user != null) {
                    $passwordHasher = new PasswordHash(8, true);
                    $passwordMatch  = $passwordHasher->CheckPassword($data_key['password'], $user->password);
                    if ($passwordMatch) {
                        $token = $this->jwt($user);
                        $user -> access_token = $token;
                        $user -> save();

                        $response = [
                            'error' => '', 
                            'status_code' => 200,
                            'data' => [
                                'user' => $user
                            ]
                        ];
                    }else{
                        $response = [
                            'error' => 'Username or password is incorrect', 
                            'status_code' => 400,
                            'data' => []
                        ];
                    }
                }else{
                    $response = [
                        'error' => 'Username or password is incorrect', 
                        'status_code' => 400,
                        'data' => []
                    ];
                }
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'message' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function checkedAgentApproved(Request $request){
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
                $data_key = $data['data'];
                $ip_private = $data_key['ip_private'];
                $siteAgentsHasData = FXSiteAgents::select('id', 'active_date', 'status')->where('site_id', $data['site']['data']['id'])->where('ip_private', $ip_private)->first();
                if($siteAgentsHasData){
                    if($siteAgentsHasData -> status == 1){
                        $ruleSites = RuleSite::where('site_id', $data['site']['data']['id'])
                        ->where('agent_id', $siteAgentsHasData->id)
                        ->where('transaction_download', 0)
                        ->get();
                        $ruleFiles = [];
                        foreach($ruleSites as $ruleSite){
                            $ruleFiles[] = RuleFile::where('id', $ruleSite -> rule_id)->where('transaction_download_client', 1)->first();
                        }
                        $response = [
                            'error' => '', 
                            'status_code' => 200,
                            'data' => [
                                'agent' => $siteAgentsHasData,
                                'rules' => $ruleFiles
                            ]
                        ];
                    }else{
                        $response = [
                            'error' => '', 
                            'status_code' => 200,
                            'data' => $siteAgentsHasData
                        ];
                    }
                }else{
                    $response = [
                        'error' => 'Data not found', 
                        'status_code' => 200,
                        'data' => []
                    ];
                }
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'message' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function downloadRule(Request $request){
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
                $ruleFiles = RuleFile::where('transaction_download_client', 0)->get();
                $response = [
                    'error' => '', 
                    'status_code' => 200,
                    'data' => $ruleFiles
                ];
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'message' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function downloadRuleComplete(Request $request){
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
                $id = $data['data']['id'];
                $ruleFiles = RuleFile::where('id', $id)->where('transaction_download_client', 0)->first();
                if($ruleFiles){
                    $ruleFiles -> transaction_download_client = 1;
                    $ruleFiles -> save();
                }
                $response = [
                    'error' => '', 
                    'status_code' => 200,
                ];
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'message' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function agentOnlineTimestamp(Request $request){
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
                $data_key = $data['data'];
                $ip_private = $data_key['ip_private'];
                $siteAgentsHasData = FXSiteAgents::where('site_id', $data['site']['data']['id'])->where('ip_private', $ip_private)->first();
                if($siteAgentsHasData){
                    $now = Carbon::now();
                    $siteAgentsHasData -> last_online = $now;
                    $siteAgentsHasData -> save();
                    $response = [
                        'error' => '', 
                        'status_code' => 200,
                        'data' => [
                            'last_online' => $now->toDateTimeString()
                        ]
                    ];
                }else{
                    $response = [
                        'error' => 'Data not found', 
                        'status_code' => 200,
                        'data' => []
                    ];
                }
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'message' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function updateRuleDownload(Request $request){
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
                $data_key = $data['data'];
                $agent_id = $data_key['agent_id'];
                $rule_id = $data_key['rule_id'];

                $ruleSites = RuleSite::where('site_id', $data['site']['data']['id'])
                ->where('agent_id', $agent_id)
                ->where('rule_id', $rule_id)
                ->where('transaction_download', 0)
                ->first();

                if($ruleSites){
                    $ruleSites -> transaction_download = 1;
                    $ruleSites -> save();
                    $response = [
                        'error' => '', 
                        'status_code' => 200,
                        'data' => $ruleSites
                    ];
                }else{
                    $response = [
                        'error' => 'Data not found', 
                        'status_code' => 200,
                        'data' => []
                    ];
                }
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'message' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    protected function jwt($user)
    {
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + env('JWT_EXPIRE_HOUR') * 60 * 60, // Expiration time
        ];

        return JWT::encode($payload, env('JWT_SECRET'));
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
