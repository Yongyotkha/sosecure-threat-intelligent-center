<?php

namespace App\Http\Controllers\Api;

use App\AgentScanLog;
use App\Entities\Sites;
use App\FXSiteAgents;
use App\RuleFile;
use App\RuleSite;
use App\RuleNameSite;
use App\RuleFileSiteDownload;
use App\RuleFileSiteAgentDownload;
use App\YaraLog;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Hautelook\Phpass\PasswordHash;
use Illuminate\Http\Request;
use Modules\Users\Entities\model_has_roles;
use Modules\Users\Entities\User;
use Modules\Users\Entities\UserSite;
use App\SiteAgentExtention;
use App\SiteAgentIgnore;
use App\HashDetection;
use DB;
use MongoDB\Client as MongoClient;
use MongoDB\BSON\UTCDateTime;
use Symfony\Polyfill\Intl\Idn\Resources\unidata\Regex;

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

                $error = [];
                if(empty($device_name) || empty($os_type) || empty($os_description) || empty($system_info) || empty($domain) || empty($ip_private)){
                    if(empty($device_name)){
                        $error['device_name'] = 'Device name is empty';
                    }
    
                    if(empty($os_type)){
                        $error['os_type'] = 'OS Type is empty';
                    }
    
                    if(empty($os_description)){
                        $error['os_description'] = 'OS Description is empty';
                    }
    
                    if(empty($system_info)){
                        $error['system_info'] = 'System Info is empty';
                    }
    
                    if(empty($domain)){
                        $error['domain'] = 'Domain is empty';
                    }
    
                    if(empty($ip_private)){
                        $error['ip_private'] = 'IP Private is empty';
                    }

                    $response = [
                        'error' => $error, 
                        'status_code' => 400,
                        'data' => []
                    ];
                }else{
                    $siteAgentsHasData = FXSiteAgents::where('site_id', $data['site']['data']['id'])->where('ip_private', $ip_private)->first();
                    if(empty($siteAgentsHasData)){
                        $site = Sites::select('agent_count')->where('id', $data['site']['data']['id'])->first();
                        $siteAgentsRows = FXSiteAgents::where('site_id', $data['site']['data']['id'])->count();
                        if(!empty($site)){
                            if($site -> agent_count < $siteAgentsRows){
                                $siteAgents = new FXSiteAgents();
                                $siteAgents -> site_id = $data['site']['data']['id'];
                                $siteAgents -> device_name = $device_name;
                                $siteAgents -> os_type = $os_type;
                                $siteAgents -> os_description = $os_description;
                                $siteAgents -> system_info = $system_info;
                                $siteAgents -> domain = $domain;
                                $siteAgents -> ip_private = $ip_private;
                                $siteAgents -> status = 1;
                                $siteAgents -> save();
                            }else{
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
                            }
                        }
                        
    
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
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'error' => $e -> getMessage(),
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
                'status_code' => 500,
                'error' => $e -> getMessage(),
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
                'status_code' => 500,
                'error' => $e -> getMessage(),
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
                $ruleFileSiteDownload = RuleFileSiteDownload::where('site_id', $data['site']['data']['id'])->where('transaction_download_client', 1)->get();
                if(!empty($ruleFileSiteDownload)){
                    $items = [];
                    foreach($ruleFileSiteDownload as $item){
                        $ruleFile = RuleFile::select('path','rule_name')->where('id', $item -> rule_files_id)->first();
                        $items[] = [
                            'path' => $ruleFile -> path,
                            'rule_name' => $ruleFile -> rule_name,
                            'id' => $item -> id,
                        ];

                        $item -> transaction_download_client = 2;
                        $item -> save();
                    }

                    $response = [
                        'error' => '', 
                        'status_code' => 200,
                        'data' => $items
                    ];
                }else{
                    $response =[
                        'error' => 'Not Found',
                        'status_code' => 404,
                        'data' => []
                    ];
                }
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'error' => $e -> getMessage(),
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
                $ruleFileSiteDownload = RuleFileSiteDownload::where('id', $id)->where('transaction_download_client', 2)->first();
                if($ruleFileSiteDownload){
                    $ruleFileSiteDownload -> transaction_download_client = 3;
                    $ruleFileSiteDownload -> save();
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
                'status_code' => 500,
                'error' => $e -> getMessage(),
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
                $is_login = $data_key['is_login'];
                $siteAgentsHasData = FXSiteAgents::where('site_id', $data['site']['data']['id'])->where('ip_private', $ip_private)->first();
                if($siteAgentsHasData){
                    $now = Carbon::now();
                    $siteAgentsHasData -> last_online = $now;
                    $siteAgentsHasData -> save();
                    $response = [
                        'error' => '', 
                        'status_code' => 200,
                        'data' => [
                            'last_online' => $now->toDateTimeString(),
                            'is_login' => $is_login
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
                'status_code' => 500,
                'error' => $e -> getMessage(),
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
                'status_code' => 500,
                'error' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function sendLogYara(Request $request){
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
                $yara = $data_key['yara'];

                $yaraLogs = [];
                foreach($yara as $yaraData){
                    $agent_id = $yaraData['agent_id'];
                    $path = $yaraData['path'];
                    $rule = $yaraData['rule'];
                    $description = $yaraData['description'];
                    $device_name = $yaraData['device_name'];
                    $file_text = $yaraData['file_text'];
                    $first_scan = $yaraData['first_scan'];
                    $last_scan = $yaraData['last_scan'];

                    $yaraLog = YaraLog::where('agent_id', $agent_id)->where('site_id', $data['site']['data']['id'])
                    ->where('path', $path)
                    ->where('rule', $rule)
                    ->first();

                    if($yaraLog){
                        $yaraLog -> last_scan = Carbon::parse($last_scan);
                        $yaraLog -> save();

                        $yaraLog -> mode = $mode;
                    }else{
                        $yaraLog = new YaraLog();
                        $yaraLog -> agent_id = $agent_id;
                        $yaraLog -> site_id = $data['site']['data']['id'];
                        $yaraLog -> path = $path;
                        $yaraLog -> rule = $rule;
                        $yaraLog -> description = $description;
                        $yaraLog -> device_name = $device_name;
                        $yaraLog -> file_text = $file_text;
                        $yaraLog -> first_scan = Carbon::parse($first_scan);
                        $yaraLog -> last_scan = Carbon::parse($last_scan);
                        $yaraLog -> save();
                    }

                    $yaraLogs[] = $yaraLog;
                }

                $response = [
                    'error' => '', 
                    'status_code' => 200,
                    'data' => $yaraLogs
                ];
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'error' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function sendAgentScanLog(Request $request){
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
                $agentScan = $data_key['agent_scan'];

                $agentScanLogs = [];
                foreach($agentScan as $agentScanData){
                    $agent_id = $agentScanData['agent_id'];
                    $description = $agentScanData['description'];
                    $time_stamp = $agentScanData['time_stamp'];
                    $mode_new = $agentScanData['mode'];
                    $type = $agentScanData['type'];

                    $agentScanLog = AgentScanLog::where('agent_id', $agent_id)->where('site_id', $data['site']['data']['id'])
                    ->where('mode', $mode_new)
                    ->first();

                    if($agentScanLog){
                        if($type == 'start'){
                            $agentScanLog -> description = null;
                            $agentScanLog -> first_scan = Carbon::parse($time_stamp);
                            $agentScanLog -> last_scan = null;
                            $agentScanLog -> save();
                        }else{
                            $agentScanLog -> description = $description;
                            $agentScanLog -> last_scan = Carbon::parse($time_stamp);
                            $agentScanLog -> save();
                        }
                    }else{
                        $agentScanLog = new AgentScanLog();
                        $agentScanLog -> agent_id = $agent_id;
                        $agentScanLog -> site_id = $data['site']['data']['id'];
                        $agentScanLog -> description = $description;
                        $agentScanLog -> first_scan = Carbon::parse($time_stamp);
                        $agentScanLog -> mode = $mode_new;
                        $agentScanLog -> save();
                    }
                    $agentScanLogs[] = $agentScanLog;
                }

                $response = [
                    'error' => '', 
                    'status_code' => 200,
                    'data' => $agentScanLogs
                ];
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'error' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function downloadRuleSite(Request $request){
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
                $statusCode = 200;
                $error = '';
                $items = [];

                $ruleFileSiteDownload = RuleFileSiteDownload::select('rule_files_id')->where('site_id', $data['site']['data']['id'])
                ->where('transaction_download_client', 3)
                ->get();
                if(!empty($ruleFileSiteDownload)){
                    $ruleFilesID = [];
                    foreach($ruleFileSiteDownload as $item){
                        $ruleFilesID[] = $item -> rule_files_id;
                    }

                    $ip_private = $data['data']['ip_private'];
                    $siteAgent = FXSiteAgents::select('id', 'active_date', 'status')
                    ->where('site_id', $data['site']['data']['id'])
                    ->where('ip_private', $ip_private)
                    ->where('status', 1)
                    ->where('deleted_at', null)
                    ->first();

                    if(!empty($siteAgent)){
                        $ruleFileSiteAgentDownload = RuleFileSiteAgentDownload::whereIn('rule_files_id', $ruleFilesID)
                        ->where('site_id', $data['site']['data']['id'])
                        ->where('agent_id', $siteAgent -> id)
                        ->where('transaction_download_client', '!=', 3)
                        ->where('status', 'Y')
                        ->get();

                        foreach($ruleFileSiteAgentDownload as $item){
                            $ruleFile = RuleFile::select('path','rule_name')->where('id', $item -> rule_files_id)->first();
                            $items[] = [
                                'path' => $ruleFile -> path,
                                'rule_name' => $ruleFile -> rule_name,
                                'id' => $item -> id,
                            ];
                            $item -> transaction_download_client = 2;
                            $item -> save();
                        }
                    }
                    
                }else{
                    $statusCode = 404;
                    $error = 'Not Found';
                }

                $response = [
                    'error' => $error, 
                    'status_code' => $statusCode,
                    'data' => $items
                ];
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'error' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function downloadRuleSiteComplete(Request $request){
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
                $ruleFileSiteAgentDownload = RuleFileSiteAgentDownload::where('id', $id)->where('transaction_download_client', 2)->first();
                if($ruleFileSiteAgentDownload){
                    $ruleFileSiteAgentDownload -> transaction_download_client = 3;
                    $ruleFileSiteAgentDownload -> save();
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
                'status_code' => 500,
                'error' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function updateConfig(Request $request){
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
                $batchjob_everydate = $data_key['batchjob_everydate'];
                $real_time_protection = $data_key['real_time_protection'];
                $usb_protection = $data_key['usb_protection'];

                $FXSiteAgents = FXSiteAgents::where('site_id', $data['site']['data']['id'])
                ->where('ip_private', $ip_private)
                ->where('status', 1)
                ->first();

                if($FXSiteAgents){
                    $FXSiteAgents -> batchjob_everydate = $batchjob_everydate;
                    $FXSiteAgents -> real_time_protection = $real_time_protection;
                    $FXSiteAgents -> usb_protection = $usb_protection;
                    $FXSiteAgents -> save();
                    $response = [
                        'error' => '', 
                        'status_code' => 200,
                        'data' => ''
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
                'status_code' => 500,
                'error' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function getConfig(Request $request){
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
                $siteAgentsHasData = FXSiteAgents::select('id', 'batchjob_everydate', 'real_time_protection', 'extention_all_flag','usb_protection')->where('site_id', $data['site']['data']['id'])->where('ip_private', $ip_private)->first();
                if($siteAgentsHasData){
                    $ignore = SiteAgentIgnore::select('ref_id')->where('site_id', $data['site']['data']['id'])
                    ->where('agent_id', $siteAgentsHasData->id)
                    ->where('type', 'extention')
                    ->where('status', 'Y')
                    ->pluck('ref_id');

                    $extentions = SiteAgentExtention::select('site_agent_extention.extention_id','rule_category.name')->where('site_id', $data['site']['data']['id'])
                    ->where('site_agent_extention.agent_id', $siteAgentsHasData->id)
                    ->whereNotIn('site_agent_extention.extention_id', $ignore)
                    ->join('rule_category', 'rule_category.id', '=', 'site_agent_extention.extention_id')
                    ->get();

                    $response = [
                        'error' => '', 
                        'status_code' => 200,
                        'data' => [
                            'agent' => $siteAgentsHasData,
                            'extentions' => $extentions
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
                'status_code' => 500,
                'error' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function getRule(Request $request){
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
                $siteAgentsHasData = FXSiteAgents::select('id', 'batchjob_everydate', 'real_time_protection', 'extention_all_flag')->where('site_id', $data['site']['data']['id'])->where('ip_private', $ip_private)->first();
                if($siteAgentsHasData){
                    $ignore = SiteAgentIgnore::select(DB::raw('"N" AS status'), 'rule_name.file_name','rule_name.rule_name','rule_name.description','rule_name.severity')
                    ->join('rule_name', 'rule_name.id', '=', 'site_agent_ignore.ref_id')
                    ->where('site_agent_ignore.site_id', $data['site']['data']['id'])
                    ->where('site_agent_ignore.agent_id', $siteAgentsHasData->id)
                    ->where('site_agent_ignore.type', 'rule')
                    ->where('site_agent_ignore.status', 'Y');

                    $ignoreRef = SiteAgentIgnore::select('ref_id')
                    ->where('site_id', $data['site']['data']['id'])
                    ->where('agent_id', $siteAgentsHasData->id)
                    ->where('type', 'rule')
                    ->where('status', 'Y')
                    ->pluck('ref_id');

                    $rules = RuleNameSite::select(DB::raw('"Y" AS status'), 'rule_name.file_name','rule_name.rule_name','rule_name.description','rule_name.severity')
                    ->join('rule_name', 'rule_name.id', '=', 'rule_name_site.rule_id')
                    ->where('rule_name_site.site_id', $data['site']['data']['id'])
                    ->whereNotIn('rule_name_site.rule_id', $ignoreRef)
                    ->union($ignore)
                    ->orderBy('rule_name', 'ASC')
                    ->get();

                    $response = [
                        'error' => '', 
                        'status_code' => 200,
                        'data' => $rules
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
                'status_code' => 500,
                'error' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function sendHash(Request $request){
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
                $hash_data = $data_key['data'];
                $dataFound = [];

                $siteAgentsHasData = FXSiteAgents::select('id', 'batchjob_everydate', 'real_time_protection', 'extention_all_flag')->where('site_id', $data['site']['data']['id'])->where('ip_private', $ip_private)->first();
                if($siteAgentsHasData){
                    foreach($hash_data as $item){
                        $hash = $item['hash'];
                        $type = $item['type'];
                        $path = $item['path'];

                        $DB_MONGO_KEY = config("app.DB_MONGO_DEV");
                        $clientMD = new MongoClient($DB_MONGO_KEY);
                        $col_fx_otx_indicator_detail = $clientMD->sosecure_threatintelligent->fx_otx_indicator_detail;

                        $options = [
                            'sort' => [
                                'updated_at' => -1
                            ]
                        ];

                        $dt1 = date("Y-m-d", strtotime("-90 day"));
                        $dt2 = date("Y-m-d", strtotime("-1 day"));
                            
                        $query = array( 
                            // 'created_at' => array('$gte' => new UTCDateTime(strtotime("$dt1")* 1000), '$lte' => new UTCDateTime(strtotime("$dt2")* 1000)),
                            'type' => $type,
                            'indicator_name' => $hash
                        );

                        $cursor = $col_fx_otx_indicator_detail->find($query,$options);
                        if(!empty($cursor)){
                            foreach ($cursor as $document){
                                $hashDetection = new HashDetection();
                                $hashDetection -> site_id = $data['site']['data']['id'];
                                $hashDetection -> agent_id = $siteAgentsHasData -> id;
                                $hashDetection -> indicator_id = $document['indicator_id'];
                                $hashDetection -> type = $document["type"];
                                $hashDetection -> path = $path;
                                $hashDetection -> hash = $document['indicator_name'];
                                $hashDetection -> status = 1;
                                $hashDetection -> save();

                                $dataFound[] = $hashDetection;
                            }
                        }
                    }

                    $response = [
                        'error' => '', 
                        'status_code' => 200,
                        'data' => $dataFound
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
                'status_code' => 500,
                'error' => $e -> getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    protected function jwt($user){
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
                'status_code' => 500,
                'error' => $e -> getMessage(),
            );
            return response()->json($response);
        }
    }
}
