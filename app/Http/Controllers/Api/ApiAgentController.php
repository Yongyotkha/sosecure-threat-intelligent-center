<?php

namespace App\Http\Controllers\Api;

use App\AgentScanLog;
use App\AgentScanFile;
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
use App\SsdeepLog;
use App\SsdeepCandidate;
use App\SsdeepFile;
use App\SsdeepFileSite;
use App\SsdeepFileSiteAgentDownload;
use App\Services\SsdeepAutoPackService;
use App\AgentReleasePackage;
use App\AgentReleaseTarget;
use App\AgentReleaseEvent;
use App\Support\AgentScheduleInterval;
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
                    $siteId = $data['site']['data']['id'];
                    $ip_private = trim((string) $ip_private);

                    $activeAgent = FXSiteAgents::where('site_id', $siteId)
                        ->where('ip_private', $ip_private)
                        ->whereNull('deleted_at')
                        ->orderBy('id', 'asc')
                        ->first();

                    if (!empty($activeAgent)) {
                        $activeAgent->device_name = $device_name;
                        $activeAgent->os_type = $os_type;
                        $activeAgent->os_description = $os_description;
                        $activeAgent->system_info = $system_info;
                        $activeAgent->domain = $domain;
                        $activeAgent->ip_private = $ip_private;
                        $activeAgent->last_online = Carbon::now();
                        $this->syncAgentIpUniqueKey($activeAgent);
                        $activeAgent->save();

                        $response = [
                            'error' => '',
                            'status_code' => 200,
                            'message' => 'Agent already registered for this IP; existing record updated.',
                            'data' => $activeAgent,
                            'reused' => true,
                        ];
                    } else {
                        $site = Sites::select('agent_count')->where('id', $siteId)->first();
                        $siteAgentsRows = FXSiteAgents::where('site_id', $siteId)->whereNull('deleted_at')->count();
                        $status = 0;
                        if (!empty($site)) {
                            // Preserve legacy license status assignment.
                            $status = ($site->agent_count < $siteAgentsRows) ? 1 : 0;
                        }

                        $deletedAgent = FXSiteAgents::where('site_id', $siteId)
                            ->where('ip_private', $ip_private)
                            ->whereNotNull('deleted_at')
                            ->orderBy('id', 'desc')
                            ->first();

                        try {
                            if (!empty($deletedAgent)) {
                                $siteAgents = $deletedAgent;
                                $siteAgents->deleted_at = null;
                                $siteAgents->site_id = $siteId;
                                $siteAgents->device_name = $device_name;
                                $siteAgents->os_type = $os_type;
                                $siteAgents->os_description = $os_description;
                                $siteAgents->system_info = $system_info;
                                $siteAgents->domain = $domain;
                                $siteAgents->ip_private = $ip_private;
                                $siteAgents->status = $status;
                                $siteAgents->last_online = Carbon::now();
                                $this->syncAgentIpUniqueKey($siteAgents);
                                $siteAgents->save();

                                $response = [
                                    'error' => '',
                                    'status_code' => 200,
                                    'message' => 'Reactivated existing agent for this IP.',
                                    'data' => $siteAgents,
                                    'reactivated' => true,
                                ];
                            } elseif (!empty($site)) {
                                $siteAgents = new FXSiteAgents();
                                $siteAgents->site_id = $siteId;
                                $siteAgents->device_name = $device_name;
                                $siteAgents->os_type = $os_type;
                                $siteAgents->os_description = $os_description;
                                $siteAgents->system_info = $system_info;
                                $siteAgents->domain = $domain;
                                $siteAgents->ip_private = $ip_private;
                                $siteAgents->status = $status;
                                $siteAgents->last_online = Carbon::now();
                                $this->syncAgentIpUniqueKey($siteAgents);
                                $siteAgents->save();

                                $response = [
                                    'error' => '',
                                    'status_code' => 200,
                                    'message' => 'Agent registered.',
                                    'data' => $siteAgents,
                                ];
                            } else {
                                $response = [
                                    'error' => 'Site not found',
                                    'status_code' => 400,
                                    'data' => [],
                                ];
                            }
                        } catch (\Illuminate\Database\QueryException $qe) {
                            // Unique (site_id, ip_unique_key) race: return existing active row.
                            $existing = FXSiteAgents::where('site_id', $siteId)
                                ->where('ip_private', $ip_private)
                                ->whereNull('deleted_at')
                                ->first();
                            if ($existing) {
                                $response = [
                                    'error' => '',
                                    'status_code' => 200,
                                    'message' => 'Agent already registered for this IP; existing record reused.',
                                    'data' => $existing,
                                    'reused' => true,
                                ];
                            } else {
                                $response = [
                                    'error' => 'Duplicate IP for this site is not allowed.',
                                    'status_code' => 409,
                                    'data' => [],
                                ];
                            }
                        }
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
            $siteKeys = null;

            $site = $this->AuthorizationAgent($header, $request->code);
            if (!is_array($site) || (string)($site['status_code'] ?? '') !== '200') {
                return response()->json(is_array($site) ? $site : [
                    'error' => 'The request parameters are invalid',
                    'status_code' => 400,
                    'data' => []
                ]);
            }
            $siteKeys = $site['data'];

            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false || !is_array($data) || empty($data['site']['data'])) {
                $response = [
                    'error' => 'The request parameters are invalid',
                    'status_code' => 400,
                    'data' => []
                ];
            } else {
                $siteKeys = $data['site']['data'];
                $data_key = isset($data['data']) && is_array($data['data']) ? $data['data'] : [];
                $email = isset($data_key['email']) ? trim((string) $data_key['email']) : '';
                $password = isset($data_key['password']) ? (string) $data_key['password'] : '';

                // Agent UI login: allow site operator roles (not only site_role_id=6).
                $user = User::where('email', $email)
                    ->where('email_verified_at', '!=', null)
                    ->where('banned', 0)
                    ->whereNull('deleted_at')
                    ->where('active', 1)
                    ->where('verify', 1)
                    ->where('site_id', $data['site']['data']['id'])
                    ->whereIn('site_role_id', [4, 5, 6, 9, 10])
                    ->where(function ($q) {
                        // Valid when never expires, or expiry is still in the future.
                        $q->whereNull('password_time_expire')
                            ->orWhere('password_time_expire', '>', date('Y-m-d H:i:s'));
                    })
                    ->first();

                if ($user != null) {
                    $passwordHasher = new PasswordHash(8, true);
                    $passwordMatch = $passwordHasher->CheckPassword($password, $user->password);
                    if ($passwordMatch) {
                        $token = $this->jwt($user);
                        $user->access_token = $token;
                        $user->save();

                        // Do NOT serialize the full Eloquent model — relations / size can
                        // crash json_encode and surface as nginx 502 HTML to the agent.
                        $response = [
                            'error' => '',
                            'status_code' => 200,
                            'data' => [
                                'user' => [
                                    'id' => (int) $user->id,
                                    'email' => (string) $user->email,
                                    'name' => (string) ($user->name ?? ''),
                                    'site_id' => (int) $user->site_id,
                                    'site_role_id' => (int) $user->site_role_id,
                                ],
                            ],
                        ];
                    } else {
                        $response = [
                            'error' => 'Username or password is incorrect',
                            'status_code' => 400,
                            'data' => []
                        ];
                    }
                } else {
                    $response = [
                        'error' => 'Username or password is incorrect',
                        'status_code' => 400,
                        'data' => []
                    ];
                }
            }

            $data_transcation = json_encode($response);
            $ipKey = is_object($siteKeys) ? $siteKeys->ip_key : ($siteKeys['ip_key'] ?? '');
            $macKey = is_object($siteKeys) ? $siteKeys->mac_address_key : ($siteKeys['mac_address_key'] ?? '');
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $ipKey, $macKey);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = [
                'status_code' => 500,
                'error' => $e->getMessage(),
                'data' => []
            ];
            try {
                $header = $request->bearerToken();
                $site = $this->AuthorizationAgent($header, $request->code);
                if (is_array($site) && (string)($site['status_code'] ?? '') === '200') {
                    $siteKeys = $site['data'];
                    $ipKey = is_object($siteKeys) ? $siteKeys->ip_key : ($siteKeys['ip_key'] ?? '');
                    $macKey = is_object($siteKeys) ? $siteKeys->mac_address_key : ($siteKeys['mac_address_key'] ?? '');
                    $datas = encrypt_decrypt('encrypt', json_encode($response), $header, $ipKey, $macKey);
                    return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
                }
            } catch (\Exception $ignored) {
            }
            return response()->json($response);
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
                $siteAgentsHasData = FXSiteAgents::select('id', 'active_date', 'status')->where('site_id', $data['site']['data']['id'])->where('ip_private', $ip_private)->where('deleted_at', null)->first();
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
                $siteAgentsHasData = FXSiteAgents::where('site_id', $data['site']['data']['id'])->where('ip_private', $ip_private)->where('deleted_at', null)->first();
                
                if($siteAgentsHasData){
                    $now = Carbon::now();
                    $siteAgentsHasData -> last_online = $now;
                    if($is_login == 1 || $is_login == true){
                        $siteAgentsHasData -> login_last_online = $now;
                    }
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
                $yara = isset($data_key['yara']) ? $data_key['yara'] : [];
                $siteId = $data['site']['data']['id'];
                $this->ensureYaraLogHasRunId();

                $yaraLogs = [];
                foreach($yara as $yaraData){
                    $payloadAgentId = isset($yaraData['agent_id']) ? (int)$yaraData['agent_id'] : 0;
                    $agent_id = $this->resolveAgentIdForSite($siteId, $data_key, $payloadAgentId);
                    if ($agent_id <= 0) {
                        continue;
                    }
                    $path = $yaraData['path'];
                    $rule = $yaraData['rule'];
                    $description = $yaraData['description'];
                    $device_name = $yaraData['device_name'];
                    $file_text = $yaraData['file_text'];
                    $first_scan = $yaraData['first_scan'];
                    $last_scan = $yaraData['last_scan'];

                    $yaraLog = YaraLog::where('agent_id', $agent_id)->where('site_id', $siteId)
                    ->where('path', $path)
                    ->where('rule', $rule)
                    ->first();

                    $runId = isset($yaraData['run_id']) ? trim((string) $yaraData['run_id']) : '';
                    $hasRunId = \Schema::hasColumn('yara_log', 'run_id');

                    if($yaraLog){
                        $yaraLog -> last_scan = Carbon::parse($last_scan);
                        if (empty($yaraLog->channel)) {
                            $yaraLog -> channel = 'yara';
                        }
                        if ($hasRunId && $runId !== '') {
                            $yaraLog->run_id = $runId;
                        }
                        $yaraLog -> save();

                        $yaraLog -> mode = $mode;
                    }else{
                        $yaraLog = new YaraLog();
                        $yaraLog -> agent_id = $agent_id;
                        $yaraLog -> site_id = $siteId;
                        $yaraLog -> path = $path;
                        $yaraLog -> rule = $rule;
                        $yaraLog -> description = $description;
                        $yaraLog -> device_name = $device_name;
                        $yaraLog -> file_text = $file_text;
                        $yaraLog -> channel = 'yara';
                        $yaraLog -> status = 1;
                        $yaraLog -> ignore_flag = 'Y';
                        $yaraLog -> first_scan = Carbon::parse($first_scan);
                        $yaraLog -> last_scan = Carbon::parse($last_scan);
                        if ($hasRunId && $runId !== '') {
                            $yaraLog->run_id = $runId;
                        }
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
                $agentScan = isset($data_key['agent_scan']) ? $data_key['agent_scan'] : [];
                $siteId = $data['site']['data']['id'];

                $agentScanLogs = [];
                foreach($agentScan as $agentScanData){
                    $payloadAgentId = isset($agentScanData['agent_id']) ? (int)$agentScanData['agent_id'] : 0;
                    $agent_id = $this->resolveAgentIdForSite($siteId, $data_key, $payloadAgentId);
                    if ($agent_id <= 0) {
                        continue;
                    }
                    $description = $agentScanData['description'];
                    $time_stamp = $agentScanData['time_stamp'];
                    $mode_new = $agentScanData['mode'];
                    $type = $agentScanData['type'];
                    $run_id = isset($agentScanData['run_id']) ? trim((string)$agentScanData['run_id']) : '';
                    $hasRunIdCol = \Schema::hasColumn('agent_scan_log', 'run_id');

                    if($type == 'start'){
                        // Realtime is per-file; keep one rolling row. Other modes get a new history row each start.
                        if($mode_new === 'REALTIME_SCAN'){
                            $agentScanLog = AgentScanLog::where('agent_id', $agent_id)
                                ->where('site_id', $siteId)
                                ->where('mode', $mode_new)
                                ->orderBy('id', 'desc')
                                ->first();
                            if($agentScanLog){
                                $agentScanLog -> description = $description;
                                $agentScanLog -> first_scan = Carbon::parse($time_stamp);
                                $agentScanLog -> last_scan = Carbon::parse($time_stamp);
                                if($hasRunIdCol && $run_id !== ''){
                                    $agentScanLog -> run_id = $run_id;
                                }
                                $agentScanLog -> save();
                            }else{
                                $agentScanLog = new AgentScanLog();
                                $agentScanLog -> agent_id = $agent_id;
                                $agentScanLog -> site_id = $siteId;
                                $agentScanLog -> description = $description;
                                $agentScanLog -> first_scan = Carbon::parse($time_stamp);
                                $agentScanLog -> last_scan = Carbon::parse($time_stamp);
                                $agentScanLog -> mode = $mode_new;
                                if($hasRunIdCol && $run_id !== ''){
                                    $agentScanLog -> run_id = $run_id;
                                }
                                $agentScanLog -> save();
                            }
                        }else{
                            $agentScanLog = new AgentScanLog();
                            $agentScanLog -> agent_id = $agent_id;
                            $agentScanLog -> site_id = $siteId;
                            $agentScanLog -> description = $description;
                            $agentScanLog -> first_scan = Carbon::parse($time_stamp);
                            $agentScanLog -> last_scan = Carbon::parse($time_stamp);
                            $agentScanLog -> mode = $mode_new;
                            if($hasRunIdCol && $run_id !== ''){
                                $agentScanLog -> run_id = $run_id;
                            }
                            $agentScanLog -> save();
                        }
                    }else{
                        $agentScanLog = null;
                        if($hasRunIdCol && $run_id !== ''){
                            $agentScanLog = AgentScanLog::where('agent_id', $agent_id)
                                ->where('site_id', $siteId)
                                ->where('run_id', $run_id)
                                ->orderBy('id', 'desc')
                                ->first();
                        }
                        if(!$agentScanLog){
                            $agentScanLog = AgentScanLog::where('agent_id', $agent_id)
                                ->where('site_id', $siteId)
                                ->where('mode', $mode_new)
                                ->orderBy('id', 'desc')
                                ->first();
                        }

                        if($agentScanLog){
                            $agentScanLog -> description = $description;
                            $agentScanLog -> last_scan = Carbon::parse($time_stamp);
                            if($hasRunIdCol && $run_id !== '' && empty($agentScanLog->run_id)){
                                $agentScanLog -> run_id = $run_id;
                            }
                            $agentScanLog -> save();
                        }else{
                            $agentScanLog = new AgentScanLog();
                            $agentScanLog -> agent_id = $agent_id;
                            $agentScanLog -> site_id = $siteId;
                            $agentScanLog -> description = $description;
                            $agentScanLog -> first_scan = Carbon::parse($time_stamp);
                            $agentScanLog -> last_scan = Carbon::parse($time_stamp);
                            $agentScanLog -> mode = $mode_new;
                            if($hasRunIdCol && $run_id !== ''){
                                $agentScanLog -> run_id = $run_id;
                            }
                            $agentScanLog -> save();
                        }
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
                $force = false;
                if (isset($data['data']['force'])) {
                    $forceVal = $data['data']['force'];
                    $force = ($forceVal === true || $forceVal === 1 || $forceVal === '1' || $forceVal === 'true');
                }

                $siteId = $data['site']['data']['id'];
                $ip_private = isset($data['data']['ip_private']) ? $data['data']['ip_private'] : null;

                // Packs assigned to this site (status Y). Do not require site-level
                // transaction=3 — that blocked agents when rows existed but were already
                // marked complete or never mirrored to agent download rows.
                $sitePacks = RuleFileSiteDownload::where('site_id', $siteId)
                    ->where(function ($q) {
                        $q->where('status', 'Y')->orWhereNull('status');
                    })
                    ->get();

                $siteAgent = null;
                if (!empty($ip_private)) {
                    $siteAgent = FXSiteAgents::select('id', 'active_date', 'status')
                        ->where('site_id', $siteId)
                        ->where('ip_private', $ip_private)
                        ->where('status', 1)
                        ->where('deleted_at', null)
                        ->first();
                }

                if ($sitePacks->isEmpty()) {
                    $statusCode = 404;
                    $error = 'Not Found';
                } elseif (empty($siteAgent)) {
                    $statusCode = 404;
                    $error = 'Agent not found';
                } else {
                    $missing = [];
                    foreach ($sitePacks as $sitePack) {
                        $ruleFile = RuleFile::select('id', 'path', 'rule_name', 'version')
                            ->where('id', $sitePack->rule_files_id)
                            ->first();
                        if (empty($ruleFile) || empty($ruleFile->path)) {
                            continue;
                        }

                        $relPath = $this->ruleFileRelativePath($ruleFile->path);
                        if ($relPath === '' || !is_file(public_path($relPath))) {
                            $missing[] = [
                                'rule_files_id' => $ruleFile->id,
                                'path' => $ruleFile->path,
                                'normalized' => $relPath,
                                'rule_name' => $ruleFile->rule_name,
                            ];
                            // Soft-disable so getRule pack counts / queues stop advertising ghost packs.
                            try {
                                RuleFileSiteDownload::where('id', $sitePack->id)->update(['status' => 'N']);
                                if (\Schema::hasColumn('rule_files', 'status')) {
                                    RuleFile::where('id', $ruleFile->id)->update(['status' => 'N']);
                                }
                            } catch (\Exception $e) {
                                // best-effort
                            }
                            continue;
                        }

                        $row = RuleFileSiteAgentDownload::where('site_id', $siteId)
                            ->where('agent_id', $siteAgent->id)
                            ->where('rule_files_id', $ruleFile->id)
                            ->first();

                        if (empty($row)) {
                            $row = new RuleFileSiteAgentDownload();
                            $row->site_id = $siteId;
                            $row->agent_id = $siteAgent->id;
                            $row->rule_files_id = $ruleFile->id;
                            $row->status = 'Y';
                            $row->transaction_download_client = 0;
                        }

                        // Already completed for this agent — skip unless force resync.
                        if (!$force && (int)$row->transaction_download_client === 3) {
                            continue;
                        }

                        if ($force) {
                            $row->transaction_download_client = 0;
                        }
                        $row->status = 'Y';
                        $row->transaction_download_client = 2;
                        $row->save();

                        $items[] = [
                            'id' => $row->id,
                            'path' => $relPath,
                            'file_name' => basename($relPath),
                            'rule_name' => $ruleFile->rule_name,
                            'version' => isset($ruleFile->version) ? $ruleFile->version : '',
                            'force' => $force ? 1 : 0,
                        ];
                    }
                }

                $response = [
                    'error' => $error,
                    'status_code' => $statusCode,
                    'data' => [
                        'rules' => $items,
                        'packs' => $items,
                        'force' => $force ? 1 : 0,
                        'missing' => isset($missing) ? $missing : [],
                        'counts' => [
                            'queued' => count($items),
                            'site_packs' => $sitePacks ? $sitePacks->count() : 0,
                            'missing' => isset($missing) ? count($missing) : 0,
                        ],
                    ],
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

    /**
     * Force re-queue all site rule packs for this agent (transaction -> 0).
     * Agent calls this when local rule count is behind server catalog.
     */
    public function resetRuleDownload(Request $request){
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                $response = [
                    'error' => 'The request parameters are invalid',
                    'status_code' => 400,
                    'data' => []
                ];
            } else {
                $siteId = $data['site']['data']['id'];
                $ip_private = isset($data['data']['ip_private']) ? $data['data']['ip_private'] : null;
                $reset = 0;

                $siteAgent = FXSiteAgents::select('id')
                    ->where('site_id', $siteId)
                    ->where('ip_private', $ip_private)
                    ->where('status', 1)
                    ->where('deleted_at', null)
                    ->first();

                if (empty($siteAgent)) {
                    $response = [
                        'error' => 'Agent not found',
                        'status_code' => 404,
                        'data' => ['reset' => 0],
                    ];
                } else {
                    $sitePacks = RuleFileSiteDownload::where('site_id', $siteId)
                        ->where(function ($q) {
                            $q->where('status', 'Y')->orWhereNull('status');
                        })
                        ->get();

                    foreach ($sitePacks as $sitePack) {
                        $row = RuleFileSiteAgentDownload::where('site_id', $siteId)
                            ->where('agent_id', $siteAgent->id)
                            ->where('rule_files_id', $sitePack->rule_files_id)
                            ->first();
                        if (empty($row)) {
                            $row = new RuleFileSiteAgentDownload();
                            $row->site_id = $siteId;
                            $row->agent_id = $siteAgent->id;
                            $row->rule_files_id = $sitePack->rule_files_id;
                            $row->status = 'Y';
                        }
                        $row->status = 'Y';
                        $row->transaction_download_client = 0;
                        $row->save();
                        $reset++;
                    }

                    $response = [
                        'error' => '',
                        'status_code' => 200,
                        'data' => [
                            'reset' => $reset,
                            'agent_id' => $siteAgent->id,
                        ],
                    ];
                }
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'error' => $e->getMessage(),
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
                ->where('deleted_at', null)
                ->first();

                if($FXSiteAgents){
                    $agentTs = $this->configUpdatedAtUnix(isset($data_key['config_updated_at']) ? $data_key['config_updated_at'] : 0);
                    $serverTs = 0;
                    if (\Schema::hasColumn('site_agents', 'config_updated_at') && !empty($FXSiteAgents->config_updated_at)) {
                        $serverTs = (int) strtotime($FXSiteAgents->config_updated_at);
                        if ($serverTs < 0) {
                            $serverTs = 0;
                        }
                    }

                    // Last-write-wins: ignore agent push when Center/Control Agent is newer,
                    // or when agent has no timestamp but Center already has one.
                    if ($serverTs > 0 && ($agentTs <= 0 || $agentTs < $serverTs)) {
                        $response = [
                            'error' => '',
                            'status_code' => 200,
                            'data' => [
                                'applied' => false,
                                'reason' => 'stale',
                                'config_updated_at' => $serverTs,
                            ],
                        ];
                    } else {
                        $FXSiteAgents -> batchjob_everydate = AgentScheduleInterval::normalizeDailyTime(
                            $batchjob_everydate,
                            AgentScheduleInterval::DEFAULT_BATCH_TIME
                        );
                        $FXSiteAgents -> real_time_protection = $this->agentFlagToYn($real_time_protection);
                        $FXSiteAgents -> usb_protection = $this->agentFlagToYn($usb_protection);
                        $this->applyOptionalAgentConfigFields($FXSiteAgents, $data_key);
                        if (\Schema::hasColumn('site_agents', 'config_updated_at')) {
                            $writeTs = $agentTs > 0 ? $agentTs : time();
                            $FXSiteAgents->config_updated_at = gmdate('Y-m-d H:i:s', $writeTs);
                        }
                        $FXSiteAgents -> save();
                        $response = [
                            'error' => '',
                            'status_code' => 200,
                            'data' => [
                                'applied' => true,
                                'config_updated_at' => (\Schema::hasColumn('site_agents', 'config_updated_at') && $FXSiteAgents->config_updated_at)
                                    ? $this->configUpdatedAtUnix($FXSiteAgents->config_updated_at)
                                    : ($agentTs > 0 ? $agentTs : time()),
                            ],
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
                $configCols = [
                    'id', 'batchjob_everydate', 'real_time_protection', 'extention_all_flag', 'usb_protection',
                    'agent_name', 'version', 'is_show_btn',
                ];
                foreach ([
                    'ssdeep_enabled', 'ssdeep_threshold', 'ssdeep_report_api',
                    'quarantine_on_detect', 'send_ssdeep_candidate',
                    'auto_scan_on_login', 'exclusion_paths', 'scan_extensions',
                    'quick_scan_paths', 'log_level', 'cache_expiry_hours',
                    'config_updated_at', 'ti_sync_everydate', 'agent_update_schedule',
                ] as $col) {
                    if (\Schema::hasColumn('site_agents', $col)) {
                        $configCols[] = $col;
                    }
                }
                $siteAgentsHasData = FXSiteAgents::select($configCols)->where('site_id', $data['site']['data']['id'])->where('ip_private', $ip_private)->where('deleted_at', null)->first();
                if($siteAgentsHasData){
                    //! ตัวที่ Comment ไว้อาจจะได้ใช้งาน
                    // $ignore = SiteAgentIgnore::select('ref_id')->where('site_id', $data['site']['data']['id'])
                    // ->where('agent_id', $siteAgentsHasData->id)
                    // ->where('type', 'extention')
                    // ->where('status', 'Y')
                    // ->pluck('ref_id');

                    $extentions = SiteAgentExtention::select('site_agent_extention.extention_id','rule_category.name')->where('site_id', $data['site']['data']['id'])
                    // ->where('site_agent_extention.agent_id', $siteAgentsHasData->id)
                    // ->whereNotIn('site_agent_extention.extention_id', $ignore)
                    ->join('rule_category', 'rule_category.id', '=', 'site_agent_extention.extention_id')
                    ->get();

                    $cfg = $this->buildAgentConfigPayload($siteAgentsHasData, $extentions);
                    $response = [
                        'error' => '', 
                        'status_code' => 200,
                        'data' => $cfg
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
                $siteAgentsHasData = FXSiteAgents::select('id', 'batchjob_everydate', 'real_time_protection', 'extention_all_flag')->where('site_id', $data['site']['data']['id'])->where('ip_private', $ip_private)->where('deleted_at', null)->first();
                if($siteAgentsHasData){
                    $ignoreRef = [];
                    if (\Schema::hasTable('site_agent_ignore')) {
                        $ignoreRef = SiteAgentIgnore::where('site_id', $data['site']['data']['id'])
                            ->where('agent_id', $siteAgentsHasData->id)
                            ->where('type', 'rule')
                            ->where('status', 'Y')
                            ->pluck('ref_id')
                            ->map(function ($v) {
                                return (int) $v;
                            })
                            ->toArray();
                    }

                    $rules = RuleNameSite::select(
                        'rule_name_site.rule_id',
                        'rule_name.file_name',
                        'rule_name.rule_name',
                        'rule_name.description',
                        'rule_name.severity'
                    )
                    ->join('rule_name', 'rule_name.id', '=', 'rule_name_site.rule_id')
                    ->where('rule_name_site.site_id', $data['site']['data']['id'])
                    ->where('rule_name_site.deleted_at', null)
                    ->orderBy('rule_name', 'ASC')
                    ->get()
                    ->map(function ($row) use ($ignoreRef) {
                        $rid = (int) $row->rule_id;
                        return [
                            'status' => in_array($rid, $ignoreRef, true) ? 'N' : 'Y',
                            'file_name' => $row->file_name,
                            'rule_name' => $row->rule_name,
                            'description' => $row->description,
                            'severity' => $row->severity,
                        ];
                    });

                    $sitePackRows = RuleFileSiteDownload::where('site_id', $data['site']['data']['id'])
                        ->where(function ($q) {
                            $q->where('status', 'Y')->orWhereNull('status');
                        })
                        ->get();
                    $packCount = 0;
                    $packFilesOnDisk = 0;
                    foreach ($sitePackRows as $sitePack) {
                        $ruleFile = RuleFile::select('id', 'path')->where('id', $sitePack->rule_files_id)->first();
                        if (empty($ruleFile) || empty($ruleFile->path)) {
                            continue;
                        }
                        $relPath = $this->ruleFileRelativePath($ruleFile->path);
                        if ($relPath === '' || !is_file(public_path($relPath))) {
                            continue;
                        }
                        $packCount++;
                        // Approximate unique rule files from pack zip entry count when available.
                        try {
                            $zip = new \ZipArchive();
                            if ($zip->open(public_path($relPath)) === true) {
                                $n = 0;
                                for ($i = 0; $i < $zip->numFiles; $i++) {
                                    $name = $zip->getNameIndex($i);
                                    if ($name === false || substr($name, -1) === '/') {
                                        continue;
                                    }
                                    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                                    if (in_array($ext, ['yar', 'yara'], true)) {
                                        $n++;
                                    }
                                }
                                $zip->close();
                                $packFilesOnDisk += $n;
                            }
                        } catch (\Exception $e) {
                            // ignore zip errors
                        }
                    }
                    $uniqueFiles = collect($rules)->pluck('file_name')->filter()->unique()->count();
                    // Prefer on-disk pack contents for agent sync comparison when we could count them.
                    if ($packFilesOnDisk > 0) {
                        $uniqueFiles = $packFilesOnDisk;
                    }

                    $response = [
                        'error' => '', 
                        'status_code' => 200,
                        // Keep legacy array at top-level "rules" for older agents;
                        // also expose counts for Go agent sync comparison.
                        'data' => [
                            'rules' => array_values($rules->all()),
                            'counts' => [
                                'rule_names' => $rules->count(),
                                'unique_files' => $uniqueFiles,
                                'packs' => $packCount,
                                'packs_on_disk' => $packCount,
                                'pack_files_on_disk' => $packFilesOnDisk,
                            ],
                        ],
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

                $siteAgentsHasData = FXSiteAgents::select('id', 'batchjob_everydate', 'real_time_protection', 'extention_all_flag')->where('site_id', $data['site']['data']['id'])->where('ip_private', $ip_private)->where('deleted_at', null)->first();
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

    public function latestVersion(Request $request){
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
                $name = $data_key['name'];
                $version = $data_key['version'];
                $ip_private = $data_key['ip_private'];
                $siteAgentsHasData = FXSiteAgents::where('site_id', $data['site']['data']['id'])->where('ip_private', $ip_private)->first();
                if($siteAgentsHasData){
                    $siteAgentsHasData -> file_name = $name;
                    $siteAgentsHasData -> version = $version;
                    $siteAgentsHasData -> save();

                    $response = [
                        'error' => '', 
                        'status_code' => 200,
                        'data' => [
                            'name' => 'sosecure_insights-'.$version.'-py3-none-any.whl.zip',
                            'version' => $version
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

    // -------------------------------------------------------------------------
    // Ssdeep agent APIs (new) — do not change existing YARA agent methods above
    // -------------------------------------------------------------------------

    public function sendLogSsdeep(Request $request){
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
                $items = isset($data_key['ssdeep']) ? $data_key['ssdeep'] : [];
                $accepted = 0;
                $ids = [];

                if (is_array($items)) {
                    foreach ($items as $item) {
                        $agent_id = isset($item['agent_id']) ? $item['agent_id'] : null;
                        $path = isset($item['path']) ? $item['path'] : null;
                        $ssdeep = isset($item['ssdeep']) ? $item['ssdeep'] : null;
                        $engine = isset($item['engine']) ? $item['engine'] : null;
                        $rule = isset($item['rule']) ? $item['rule'] : null;
                        $description = isset($item['description']) ? $item['description'] : null;
                        $device_name = isset($item['device_name']) ? $item['device_name'] : null;
                        $detected_at = isset($item['detected_at']) ? $item['detected_at'] : null;

                        if (empty($agent_id) || empty($path) || empty($ssdeep) || empty($engine) || empty($rule) || empty($description) || empty($device_name) || empty($detected_at)) {
                            continue;
                        }

                        $log = new SsdeepLog();
                        $log->site_id = $data['site']['data']['id'];
                        $log->agent_id = $agent_id;
                        $log->path = $path;
                        $log->file_name = isset($item['file_name']) ? $item['file_name'] : null;
                        $log->hash_md5 = isset($item['hash_md5']) ? $item['hash_md5'] : null;
                        $log->ssdeep = $ssdeep;
                        $log->engine = $engine;
                        $log->rule = $rule;
                        $log->score = isset($item['score']) ? (int)$item['score'] : 0;
                        $log->description = $description;
                        $log->device_name = $device_name;
                        $log->detected_at = Carbon::parse($detected_at);
                        $log->save();

                        // Mirror ssdeep-engine hits into yara_log so Agent Management Alert can show them (channel=ssdeep).
                        // Skip engine=yara — those already land via sendLogYara.
                        if (strtolower((string) $engine) === 'ssdeep') {
                            $this->ensureYaraLogHasRunId();
                            $detected = Carbon::parse($detected_at);
                            $score = isset($item['score']) ? (int)$item['score'] : 0;
                            $severity = $this->ssdeepScoreToSeverity($score);
                            $runId = isset($item['run_id']) ? trim((string) $item['run_id']) : '';
                            $hasRunId = \Schema::hasColumn('yara_log', 'run_id');
                            $yaraLog = YaraLog::where('agent_id', $agent_id)
                                ->where('site_id', $data['site']['data']['id'])
                                ->where('path', $path)
                                ->where('rule', $rule)
                                ->first();
                            if ($yaraLog) {
                                $yaraLog->last_scan = $detected;
                                $yaraLog->description = $description;
                                $yaraLog->device_name = $device_name;
                                $yaraLog->file_text = $ssdeep;
                                $yaraLog->channel = 'ssdeep';
                                $yaraLog->severity = $severity;
                                $yaraLog->status = 1;
                                if (empty($yaraLog->ignore_flag)) {
                                    $yaraLog->ignore_flag = 'Y';
                                }
                                if ($hasRunId && $runId !== '') {
                                    $yaraLog->run_id = $runId;
                                }
                                $yaraLog->save();
                            } else {
                                $yaraLog = new YaraLog();
                                $yaraLog->agent_id = $agent_id;
                                $yaraLog->site_id = $data['site']['data']['id'];
                                $yaraLog->path = $path;
                                $yaraLog->rule = $rule;
                                $yaraLog->description = $description;
                                $yaraLog->device_name = $device_name;
                                $yaraLog->file_text = $ssdeep;
                                $yaraLog->channel = 'ssdeep';
                                $yaraLog->severity = $severity;
                                $yaraLog->status = 1;
                                $yaraLog->ignore_flag = 'Y';
                                $yaraLog->first_scan = $detected;
                                $yaraLog->last_scan = $detected;
                                if ($hasRunId && $runId !== '') {
                                    $yaraLog->run_id = $runId;
                                }
                                $yaraLog->save();
                            }
                        }

                        $accepted++;
                        $ids[] = $log->id;
                    }
                }

                $response = [
                    'error' => '',
                    'status_code' => 200,
                    'data' => [
                        'accepted' => $accepted,
                        'ids' => $ids,
                    ]
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

    public function getSsdeep(Request $request){
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
                $siteId = $data['site']['data']['id'];
                $ip_private = isset($data['data']['ip_private']) ? $data['data']['ip_private'] : null;
                $sitePackIds = SsdeepFileSite::where('site_id', $siteId)
                    ->where('status', 'Y')
                    ->pluck('ssdeep_file_id')
                    ->map(function ($v) {
                        return (int) $v;
                    })
                    ->toArray();

                // Per-agent ssdeep ignore (does not change site assignment).
                $ignoredPackIds = [];
                if (!empty($ip_private) && \Schema::hasTable('site_agent_ignore')) {
                    $siteAgent = FXSiteAgents::select('id')
                        ->where('site_id', $siteId)
                        ->where('ip_private', $ip_private)
                        ->where('status', 1)
                        ->where('deleted_at', null)
                        ->first();
                    if (!empty($siteAgent)) {
                        $ignoredPackIds = SiteAgentIgnore::where('site_id', $siteId)
                            ->where('agent_id', $siteAgent->id)
                            ->where('type', 'ssdeep')
                            ->where('status', 'Y')
                            ->pluck('ref_id')
                            ->map(function ($v) {
                                return (int) $v;
                            })
                            ->toArray();
                    }
                }
                $allowedPackIds = array_values(array_diff($sitePackIds, $ignoredPackIds));
                sort($allowedPackIds);

                $latest = null;
                $sigCount = 0;
                if (!empty($allowedPackIds)) {
                    $latest = SsdeepFile::where('status', 'Y')
                        ->whereIn('id', $allowedPackIds)
                        ->orderBy('id', 'DESC')
                        ->first();
                    $sigCount = (int) SsdeepFile::where('status', 'Y')
                        ->whereIn('id', $allowedPackIds)
                        ->sum('signature_count');
                }

                if ($latest) {
                    // Fingerprint allowed pack set so per-agent ignore changes force resync.
                    $fp = substr(sha1(implode(',', $allowedPackIds)), 0, 8);
                    $response = [
                        'error' => '',
                        'status_code' => 200,
                        'data' => [
                            'ssdeep_db' => [
                                'version' => $latest->version . '#' . $fp,
                                'signature_count' => $sigCount,
                                'updated_at' => $latest->updated_at ? $latest->updated_at->toIso8601String() : null,
                            ]
                        ]
                    ];
                } else {
                    $response = [
                        'error' => '',
                        'status_code' => 200,
                        'data' => [
                            'ssdeep_db' => [
                                'version' => '',
                                'signature_count' => 0,
                                'updated_at' => null,
                            ]
                        ]
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

    public function downloadSsdeepSite(Request $request){
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
                $items = [];
                $ip_private = isset($data['data']['ip_private']) ? $data['data']['ip_private'] : null;
                $current_version = isset($data['data']['current_version']) ? $data['data']['current_version'] : null;
                $force = false;
                if (isset($data['data']['force'])) {
                    $forceVal = $data['data']['force'];
                    $force = ($forceVal === true || $forceVal === 1 || $forceVal === '1' || $forceVal === 'true');
                }

                $siteAgent = FXSiteAgents::select('id')
                    ->where('site_id', $data['site']['data']['id'])
                    ->where('ip_private', $ip_private)
                    ->where('status', 1)
                    ->where('deleted_at', null)
                    ->first();

                if (!empty($siteAgent)) {
                    $ignoredPackIds = [];
                    if (\Schema::hasTable('site_agent_ignore')) {
                        $ignoredPackIds = SiteAgentIgnore::where('site_id', $data['site']['data']['id'])
                            ->where('agent_id', $siteAgent->id)
                            ->where('type', 'ssdeep')
                            ->where('status', 'Y')
                            ->pluck('ref_id')
                            ->map(function ($v) {
                                return (int) $v;
                            })
                            ->toArray();
                    }

                    $sitePacks = SsdeepFileSite::where('site_id', $data['site']['data']['id'])
                        ->where('status', 'Y')
                        ->get();

                    // Resolve active files, then keep the latest pack per category.
                    // Uncategorized packs share bucket "_" (only newest kept).
                    // Skip packs ignored for this agent only.
                    $files = [];
                    foreach ($sitePacks as $sitePack) {
                        $packId = (int) $sitePack->ssdeep_file_id;
                        if (in_array($packId, $ignoredPackIds, true)) {
                            continue;
                        }
                        $file = SsdeepFile::where('id', $packId)->where('status', 'Y')->first();
                        if (!empty($file)) {
                            $files[] = $file;
                        }
                    }
                    usort($files, function ($a, $b) {
                        return ((int)$b->id) <=> ((int)$a->id);
                    });
                    if (!$force) {
                        $latestByCategory = [];
                        foreach ($files as $file) {
                            $cat = strtolower(trim((string)($file->category ?? '')));
                            if ($cat === '') {
                                $cat = '_';
                            }
                            if (!isset($latestByCategory[$cat])) {
                                $latestByCategory[$cat] = $file;
                            }
                        }
                        $files = array_values($latestByCategory);
                    }

                    foreach ($files as $file) {
                        if (!$force && !empty($current_version) && $file->version === $current_version) {
                            continue;
                        }

                        $row = SsdeepFileSiteAgentDownload::where('site_id', $data['site']['data']['id'])
                            ->where('agent_id', $siteAgent->id)
                            ->where('ssdeep_file_id', $file->id)
                            ->first();

                        // Already completed for this agent — skip unless force resync.
                        if (!$force && !empty($row) && (int)$row->transaction_download_client === 3) {
                            continue;
                        }

                        if (empty($row)) {
                            $row = new SsdeepFileSiteAgentDownload();
                            $row->site_id = $data['site']['data']['id'];
                            $row->agent_id = $siteAgent->id;
                            $row->ssdeep_file_id = $file->id;
                            $row->status = 'Y';
                        }
                        if ($force) {
                            $row->transaction_download_client = 0;
                        }
                        $row->transaction_download_client = 2;
                        $row->version = $file->version;
                        $row->save();

                        $items[] = [
                            'id' => $row->id,
                            'path' => $file->path,
                            'file_name' => $file->file_name,
                            'version' => $file->version,
                            'format' => $file->format ? $file->format : 'sqlite_zip',
                            'sha256' => $file->sha256,
                            'size_bytes' => $file->size_bytes,
                            'category' => $file->category,
                            'title' => $file->title,
                        ];
                    }
                }

                $response = [
                    'error' => '',
                    'status_code' => 200,
                    'data' => [
                        'signatures' => $items,
                    ]
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

    public function downloadSsdeepSiteComplete(Request $request){
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
                $id = isset($data['data']['id']) ? $data['data']['id'] : null;
                $row = SsdeepFileSiteAgentDownload::where('id', $id)->where('transaction_download_client', 2)->first();
                if ($row) {
                    $row->transaction_download_client = 3;
                    $row->save();
                }
                $response = [
                    'error' => '',
                    'status_code' => 200,
                    'data' => ['ok' => true]
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

    public function updateSsdeepDownload(Request $request){
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
                $agent_id = isset($data_key['agent_id']) ? $data_key['agent_id'] : null;
                $ssdeep_id = isset($data_key['ssdeep_id']) ? $data_key['ssdeep_id'] : null;
                $version = isset($data_key['version']) ? $data_key['version'] : null;

                $row = SsdeepFileSiteAgentDownload::where('id', $ssdeep_id)
                    ->where('site_id', $data['site']['data']['id'])
                    ->where('agent_id', $agent_id)
                    ->first();

                if ($row) {
                    $row->transaction_download_client = 3;
                    if (!empty($version)) {
                        $row->version = $version;
                    }
                    $row->save();
                    $response = [
                        'error' => '',
                        'status_code' => 200,
                        'data' => $row
                    ];
                } else {
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

    public function sendSsdeepCandidate(Request $request){
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
                $ip_private = isset($data_key['ip_private']) ? $data_key['ip_private'] : null;
                $candidates = isset($data_key['candidates']) ? $data_key['candidates'] : [];
                $accepted = 0;
                $discarded = 0;
                $queue_ids = [];
                $autoPack = app(SsdeepAutoPackService::class);
                $autoPack->purgeLegacyDuplicateCandidates();

                if (is_array($candidates)) {
                    foreach ($candidates as $item) {
                        $hash = isset($item['ssdeep']) ? trim((string) $item['ssdeep']) : '';
                        if ($hash !== '' && $autoPack->isKnownHash($hash)) {
                            $discarded++;
                            continue;
                        }

                        $row = new SsdeepCandidate();
                        $row->site_id = $data['site']['data']['id'];
                        $row->agent_id = isset($item['agent_id']) ? $item['agent_id'] : null;
                        $row->ip_private = $ip_private;
                        $row->path = isset($item['path']) ? $item['path'] : null;
                        $row->file_name = isset($item['file_name']) ? $item['file_name'] : null;
                        $row->hash_md5 = isset($item['hash_md5']) ? $item['hash_md5'] : null;
                        $row->hash_sha256 = isset($item['hash_sha256']) ? $item['hash_sha256'] : null;
                        $row->ssdeep = isset($item['ssdeep']) ? $item['ssdeep'] : null;
                        $row->engine = isset($item['engine']) ? $item['engine'] : null;
                        $row->rule = isset($item['rule']) ? $item['rule'] : null;
                        $row->score = isset($item['score']) ? (int)$item['score'] : 0;
                        $row->scan_mode = isset($item['scan_mode']) ? $item['scan_mode'] : null;
                        $row->detected_at = !empty($item['detected_at']) ? Carbon::parse($item['detected_at']) : null;
                        $row->source = isset($item['source']) ? $item['source'] : null;
                        $row->status = 'queued';
                        $row->save();

                        // Auto-merge into category ssdeep pack (best-effort; never fail the API).
                        try {
                            $result = $autoPack->promoteCandidate($row);
                            if ($result === 'discarded' || $result === 'skipped_empty') {
                                $discarded++;
                                continue;
                            }
                        } catch (\Exception $e) {
                            \Log::warning('sendSsdeepCandidate auto-promote: '.$e->getMessage());
                        }

                        $accepted++;
                        $queue_ids[] = $row->id;
                    }
                }

                $response = [
                    'error' => '',
                    'status_code' => 200,
                    'data' => [
                        'accepted' => $accepted,
                        'discarded' => $discarded,
                        'queue_ids' => $queue_ids,
                    ]
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

    /**
     * Keep soft-delete-friendly unique key in sync: active rows store IP, deleted rows store NULL.
     */
    private function syncAgentIpUniqueKey($row)
    {
        if (!$row || !$this->agentsTableHasIpUniqueKey()) {
            return;
        }
        if (!empty($row->deleted_at)) {
            $row->ip_unique_key = null;
            return;
        }
        $ip = isset($row->ip_private) ? trim((string) $row->ip_private) : '';
        $row->ip_unique_key = $ip !== '' ? $ip : null;
    }

    private function agentsTableHasIpUniqueKey()
    {
        // Logical name only — Laravel applies prefix (fx_site_agents).
        return \Schema::hasColumn('site_agents', 'ip_unique_key');
    }

    /**
     * Normalize agent/UI flags to Y|N for site_agents columns (Control Agent UI).
     */
    private function agentFlagToYn($value)
    {
        if ($value === true || $value === 1 || $value === '1') {
            return 'Y';
        }
        if (is_string($value)) {
            $s = strtoupper(trim($value));
            if ($s === 'Y' || $s === 'TRUE' || $s === 'ON') {
                return 'Y';
            }
        }
        return 'N';
    }

    /**
     * Normalize stored flags to 0|1 for getConfig response (Go agent).
     */
    private function agentFlagToInt($value)
    {
        return $this->agentFlagToYn($value) === 'Y' ? 1 : 0;
    }

    /**
     * Clamp ssdeep match threshold to 0–100 (agent default 85).
     */
    private function ssdeepThresholdInt($value)
    {
        $n = (int) $value;
        if ($n < 0) {
            return 0;
        }
        if ($n > 100) {
            return 100;
        }
        return $n;
    }

    private function agentAttrOr($row, $key, $default)
    {
        if (!$row) {
            return $default;
        }
        $attrs = $row->getAttributes();
        if (!array_key_exists($key, $attrs) || $attrs[$key] === null) {
            return $default;
        }
        return $attrs[$key];
    }

    private function defaultExclusionPaths()
    {
        return '';
    }

    private function defaultScanExtensions()
    {
        return '.exe,.dll,.sys,.scr,.com,.pif,.msi,.cpl,' .
            '.bat,.cmd,.ps1,.psm1,.vbs,.vbe,.js,.jse,.wsf,.wsh,.hta,' .
            '.lnk,.url,.scf,.reg,.chm,' .
            '.php,.phtml,.php3,.php4,.php5,.php7,.phps,.phar,' .
            '.asp,.aspx,.ashx,.asmx,.jsp,.jspx,' .
            '.html,.htm,.shtml,.cfm,.cgi,.pl,.py,.rb,.sh,' .
            '.inc,.tpl,' .
            '.docm,.xlsm,.pptm,.jar,' .
            '.txt,.log,.bak,.old,.dat,' .
            '.img,.iso';
    }

    private function defaultQuickScanPaths()
    {
        return '%USERPROFILE%\Downloads;%USERPROFILE%\Desktop;%TEMP%;%APPDATA%';
    }

    private function normalizeExclusionPaths($value)
    {
        $s = str_replace(["\r\n", "\r", "\n"], ';', (string) $value);
        $parts = array_filter(array_map('trim', explode(';', $s)), function ($p) {
            return $p !== '';
        });
        return implode(';', $parts);
    }

    private function normalizeScanExtensions($value)
    {
        $s = str_replace(["\r\n", "\r", "\n", ';'], ',', (string) $value);
        $parts = [];
        foreach (explode(',', $s) as $p) {
            $p = strtolower(trim($p));
            if ($p === '') {
                continue;
            }
            if ($p[0] !== '.') {
                $p = '.' . $p;
            }
            $parts[] = $p;
        }
        return implode(',', array_values(array_unique($parts)));
    }

    private function normalizeQuickScanPaths($value)
    {
        return $this->normalizeExclusionPaths($value);
    }

    private function normalizeLogLevel($value)
    {
        $s = strtolower(trim((string) $value));
        $allowed = ['debug', 'info', 'warn', 'warning', 'error'];
        if (!in_array($s, $allowed, true)) {
            return 'info';
        }
        if ($s === 'warning') {
            return 'warn';
        }
        return $s;
    }

    private function cacheExpiryHoursInt($value)
    {
        $n = (int) $value;
        if ($n < 1) {
            return 1;
        }
        if ($n > 8760) {
            return 8760;
        }
        return $n;
    }

    /**
     * Normalize config_updated_at to unix seconds (UTC). Accepts unix int or datetime string.
     * Datetime strings from DB are treated as UTC (written via gmdate).
     */
    private function configUpdatedAtUnix($value)
    {
        if ($value === null || $value === '' || $value === false) {
            return 0;
        }
        if (is_numeric($value)) {
            $n = (int) $value;
            return $n > 0 ? $n : 0;
        }
        $s = trim((string) $value);
        if ($s === '') {
            return 0;
        }
        try {
            $dt = \DateTime::createFromFormat('Y-m-d H:i:s', $s, new \DateTimeZone('UTC'));
            if ($dt instanceof \DateTime) {
                return (int) $dt->getTimestamp();
            }
        } catch (\Exception $e) {
            // fall through
        }
        $ts = strtotime($s);
        if ($ts === false || $ts < 0) {
            return 0;
        }
        return (int) $ts;
    }

    /**
     * Apply optional getConfig/updateConfig fields onto site_agents when columns exist.
     */
    private function applyOptionalAgentConfigFields($row, array $data_key)
    {
        $ynFields = [
            'ssdeep_enabled', 'ssdeep_report_api', 'quarantine_on_detect',
            'send_ssdeep_candidate', 'auto_scan_on_login',
        ];
        foreach ($ynFields as $field) {
            if (array_key_exists($field, $data_key) && \Schema::hasColumn('site_agents', $field)) {
                $row->{$field} = $this->agentFlagToYn($data_key[$field]);
            }
        }
        // Locked product policy regardless of agent/UI payload.
        if (\Schema::hasColumn('site_agents', 'ssdeep_enabled')) {
            $row->ssdeep_enabled = 'Y';
        }
        if (\Schema::hasColumn('site_agents', 'ssdeep_report_api')) {
            $row->ssdeep_report_api = 'Y';
        }
        if (\Schema::hasColumn('site_agents', 'quarantine_on_detect')) {
            $row->quarantine_on_detect = 'N';
        }
        if (\Schema::hasColumn('site_agents', 'send_ssdeep_candidate')) {
            $row->send_ssdeep_candidate = 'Y';
        }
        if (array_key_exists('ssdeep_threshold', $data_key) && \Schema::hasColumn('site_agents', 'ssdeep_threshold')) {
            $row->ssdeep_threshold = $this->ssdeepThresholdInt($data_key['ssdeep_threshold']);
        }
        if (array_key_exists('exclusion_paths', $data_key) && \Schema::hasColumn('site_agents', 'exclusion_paths')) {
            $row->exclusion_paths = $this->normalizeExclusionPaths($data_key['exclusion_paths']);
        }
        if (array_key_exists('scan_extensions', $data_key) && \Schema::hasColumn('site_agents', 'scan_extensions')) {
            $row->scan_extensions = $this->normalizeScanExtensions($data_key['scan_extensions']);
        }
        // quick_scan_paths ignored while custom-path feature is disabled.
        if (array_key_exists('log_level', $data_key) && \Schema::hasColumn('site_agents', 'log_level')) {
            $row->log_level = $this->normalizeLogLevel($data_key['log_level']);
        }
        if (array_key_exists('cache_expiry_hours', $data_key) && \Schema::hasColumn('site_agents', 'cache_expiry_hours')) {
            $row->cache_expiry_hours = $this->cacheExpiryHoursInt($data_key['cache_expiry_hours']);
        }
        if (array_key_exists('batchjob_everydate', $data_key) && \Schema::hasColumn('site_agents', 'batchjob_everydate')) {
            $row->batchjob_everydate = AgentScheduleInterval::normalizeDailyTime(
                $data_key['batchjob_everydate'],
                AgentScheduleInterval::DEFAULT_BATCH_TIME
            );
        }
        if (array_key_exists('ti_sync_everydate', $data_key) && \Schema::hasColumn('site_agents', 'ti_sync_everydate')) {
            $row->ti_sync_everydate = AgentScheduleInterval::normalize(
                $data_key['ti_sync_everydate'],
                AgentScheduleInterval::DEFAULT_TI_SYNC
            );
        }
        if (array_key_exists('agent_update_schedule', $data_key) && \Schema::hasColumn('site_agents', 'agent_update_schedule')) {
            $row->agent_update_schedule = AgentScheduleInterval::normalizeAgentUpdate(
                $data_key['agent_update_schedule'],
                AgentScheduleInterval::DEFAULT_AGENT_UPDATE
            );
        }
    }

    /**
     * @deprecated use AgentScheduleInterval::normalize
     */
    private function normalizeTiSyncEverydate($value)
    {
        return AgentScheduleInterval::normalize($value, AgentScheduleInterval::DEFAULT_TI_SYNC);
    }

    /**
     * Flat + nested payload for Go agent applyGetConfig.
     */
    private function buildAgentConfigPayload($siteAgentsHasData, $extentions)
    {
        $rtp = $this->agentFlagToInt($siteAgentsHasData->real_time_protection);
        $usb = $this->agentFlagToInt($siteAgentsHasData->usb_protection);
        $batch = AgentScheduleInterval::normalizeDailyTime(
            $siteAgentsHasData->batchjob_everydate,
            AgentScheduleInterval::DEFAULT_BATCH_TIME
        );
        $tiSync = AgentScheduleInterval::normalize(
            $this->agentAttrOr($siteAgentsHasData, 'ti_sync_everydate', AgentScheduleInterval::DEFAULT_TI_SYNC),
            AgentScheduleInterval::DEFAULT_TI_SYNC
        );
        $agentUpdateSchedule = AgentScheduleInterval::normalizeAgentUpdate(
            $this->agentAttrOr($siteAgentsHasData, 'agent_update_schedule', AgentScheduleInterval::DEFAULT_AGENT_UPDATE),
            AgentScheduleInterval::DEFAULT_AGENT_UPDATE
        );
        $ssdeepOn = $this->agentFlagToInt('Y');
        $ssdeepThr = $this->ssdeepThresholdInt($this->agentAttrOr($siteAgentsHasData, 'ssdeep_threshold', 85));
        $ssdeepReport = $this->agentFlagToInt('Y');
        $quarantine = $this->agentFlagToInt('N');
        $sendCand = $this->agentFlagToInt('Y');
        $autoLogin = $this->agentFlagToInt($this->agentAttrOr($siteAgentsHasData, 'auto_scan_on_login', 'N'));
        $excl = $this->normalizeExclusionPaths($this->agentAttrOr($siteAgentsHasData, 'exclusion_paths', $this->defaultExclusionPaths()));
        $scanExt = $this->normalizeScanExtensions($this->agentAttrOr($siteAgentsHasData, 'scan_extensions', $this->defaultScanExtensions()));
        // Quick scan custom paths disabled in product policy; keep key empty for agents.
        $quick = '';
        $logLevel = $this->normalizeLogLevel($this->agentAttrOr($siteAgentsHasData, 'log_level', 'info'));
        $cacheHours = $this->cacheExpiryHoursInt($this->agentAttrOr($siteAgentsHasData, 'cache_expiry_hours', 168));
        $configUpdatedAt = $this->configUpdatedAtUnix($this->agentAttrOr($siteAgentsHasData, 'config_updated_at', 0));

        $flat = [
            'batchjob_everydate' => $batch,
            'ti_sync_everydate' => $tiSync,
            'agent_update_schedule' => $agentUpdateSchedule,
            'real_time_protection' => $rtp,
            'usb_protection' => $usb,
            'ssdeep_enabled' => $ssdeepOn,
            'ssdeep_threshold' => $ssdeepThr,
            'ssdeep_report_api' => $ssdeepReport,
            'quarantine_on_detect' => $quarantine,
            'send_ssdeep_candidate' => $sendCand,
            'auto_scan_on_login' => $autoLogin,
            'exclusion_paths' => $excl,
            'scan_extensions' => $scanExt,
            'quick_scan_paths' => $quick,
            'log_level' => $logLevel,
            'cache_expiry_hours' => $cacheHours,
            'config_updated_at' => $configUpdatedAt,
            'extentions' => $extentions,
        ];

        return array_merge($flat, [
            'agent' => array_merge([
                'id' => (int) $siteAgentsHasData->id,
                'extention_all_flag' => $siteAgentsHasData->extention_all_flag,
                'agent_name' => $siteAgentsHasData->agent_name,
                'version' => $siteAgentsHasData->version,
                'is_show_btn' => $siteAgentsHasData->is_show_btn,
            ], $flat),
        ]);
    }

    /**
     * Lazy-add yara_log.run_id when migration not applied yet.
     */
    private function ensureYaraLogHasRunId()
    {
        try {
            if (\Schema::hasTable('yara_log') && !\Schema::hasColumn('yara_log', 'run_id')) {
                \Schema::table('yara_log', function ($table) {
                    $table->string('run_id', 64)->nullable()->after('agent_id');
                    $table->index(['run_id', 'agent_id'], 'yara_log_run_agent');
                });
            }
        } catch (\Exception $e) {
            // ignore
        }
    }

    /**
     * Persist engine-scanned paths (clean + infected) for Scan History expand.
     * Skipped incremental files are not sent by the agent.
     */
    public function sendScanFileLog(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);

            if ($data === false) {
                return response()->json([
                    'error' => 'The request parameters are invalid',
                    'status_code' => 400,
                    'data' => []
                ]);
            }

            $this->ensureAgentScanFileTable();
            $data_key = $data['data'];
            $rows = isset($data_key['scan_files']) && is_array($data_key['scan_files'])
                ? $data_key['scan_files']
                : [];
            $siteId = $data['site']['data']['id'];
            $inserted = 0;

            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $payloadAgentId = isset($row['agent_id']) ? (int) $row['agent_id'] : 0;
                $agent_id = $this->resolveAgentIdForSite($siteId, $data_key, $payloadAgentId);
                if ($agent_id <= 0) {
                    continue;
                }
                $path = isset($row['path']) ? trim((string) $row['path']) : '';
                if ($path === '') {
                    continue;
                }
                $result = isset($row['result']) ? strtolower(trim((string) $row['result'])) : 'clean';
                if ($result !== 'infected') {
                    $result = 'clean';
                }
                $runId = isset($row['run_id']) ? trim((string) $row['run_id']) : '';
                $rule = isset($row['rule']) ? (string) $row['rule'] : null;
                $engine = isset($row['engine']) ? (string) $row['engine'] : null;
                $score = isset($row['score']) && $row['score'] !== '' && $row['score'] !== null
                    ? $row['score']
                    : null;
                $scannedAt = isset($row['scanned_at']) ? $row['scanned_at'] : null;
                try {
                    $scannedAt = $scannedAt ? Carbon::parse($scannedAt) : Carbon::now();
                } catch (\Exception $e) {
                    $scannedAt = Carbon::now();
                }

                $rec = new AgentScanFile();
                $rec->site_id = $siteId;
                $rec->agent_id = $agent_id;
                $rec->run_id = $runId !== '' ? $runId : null;
                $rec->path = $path;
                $rec->result = $result;
                $rec->rule = $rule;
                $rec->engine = $engine;
                $rec->score = $score;
                $rec->scanned_at = $scannedAt;
                $rec->save();
                $inserted++;
            }

            return response()->json([
                'error' => '',
                'status_code' => 200,
                'data' => ['inserted' => $inserted],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status_code' => 500,
                'data' => []
            ]);
        }
    }

    private function ensureAgentScanFileTable()
    {
        try {
            if (\Schema::hasTable('agent_scan_file')) {
                return;
            }
            \Schema::create('agent_scan_file', function ($table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('site_id')->nullable()->index();
                $table->unsignedBigInteger('agent_id')->nullable()->index();
                $table->string('run_id', 64)->nullable();
                $table->text('path');
                $table->string('result', 16)->default('clean');
                $table->string('rule', 255)->nullable();
                $table->string('engine', 32)->nullable();
                $table->decimal('score', 10, 2)->nullable();
                $table->dateTime('scanned_at')->nullable();
                $table->timestamps();
                $table->index(['run_id', 'agent_id'], 'agent_scan_file_run_agent');
                $table->index(['agent_id', 'scanned_at'], 'agent_scan_file_agent_time');
            });
        } catch (\Exception $e) {
            // ignore
        }
    }

    /**
     * Map ssdeep similarity score (0-100) to the same 5 severity labels used by YARA rules.
     */
    private function ssdeepScoreToSeverity($score)
    {
        $score = (int) $score;
        if ($score >= 95) {
            return 'Critical';
        }
        if ($score >= 85) {
            return 'High';
        }
        if ($score >= 70) {
            return 'Medium';
        }
        if ($score >= 50) {
            return 'Low';
        }
        return 'Information';
    }

    /**
     * Resolve site_agents.id for inbound detection/scan logs.
     * Prefer a valid payload agent_id on this site; otherwise match ip_private.
     */
    private function resolveAgentIdForSite($siteId, array $dataKey, $payloadAgentId = 0)
    {
        $siteId = (int) $siteId;
        $payloadAgentId = (int) $payloadAgentId;
        if ($payloadAgentId > 0) {
            $byId = FXSiteAgents::where('site_id', $siteId)
                ->where('id', $payloadAgentId)
                ->whereNull('deleted_at')
                ->first();
            if ($byId) {
                return (int) $byId->id;
            }
        }
        $ip = isset($dataKey['ip_private']) ? trim((string) $dataKey['ip_private']) : '';
        if ($ip !== '') {
            $byIp = FXSiteAgents::where('site_id', $siteId)
                ->where('ip_private', $ip)
                ->whereNull('deleted_at')
                ->orderBy('id', 'desc')
                ->first();
            if ($byIp) {
                return (int) $byIp->id;
            }
        }
        return $payloadAgentId > 0 ? $payloadAgentId : 0;
    }

    protected function jwt($user){
        $hours = (int) env('JWT_EXPIRE_HOUR', 24);
        if ($hours <= 0) {
            $hours = 24;
        }
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => $user->id, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + ($hours * 60 * 60), // Expiration time
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

    /**
     * Bootstrap crypto salts for the agent (Bearer public_key only, no encrypted body).
     * Returns plaintext ip_key / mac_address_key so the agent can encrypt subsequent calls.
     */
    public function getSiteCrypto(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode ? $request->mode : 'site_offline';
            $site = $this->AuthorizationRegister($header, $mode);
            if ($site['status_code'] !== '200') {
                return response()->json([
                    'error' => isset($site['error']) ? $site['error'] : 'Unauthorized',
                    'status_code' => (int) $site['status_code'],
                    'data' => [],
                ]);
            }
            return response()->json([
                'error' => '',
                'status_code' => 200,
                'data' => [
                    'ip_key' => $site['data']['ip_key'],
                    'mac_address_key' => $site['data']['mac_address_key'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status_code' => 500,
                'data' => [],
            ]);
        }
    }

    /**
     * Authenticated file download — file bytes returned inside the encrypted API envelope
     * (content_b64) so packs are not fetched as public static URLs.
     */
    public function downloadProtectedFile(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                $response = [
                    'error' => 'The request parameters are invalid',
                    'status_code' => 400,
                    'data' => [],
                ];
            } else {
                $kind = isset($data['data']['kind']) ? strtolower(trim($data['data']['kind'])) : '';
                $rel = isset($data['data']['path']) ? $data['data']['path'] : '';
                $rel = ltrim(str_replace('\\', '/', $rel), '/');

                // Allow absolute URLs by extracting path.
                if (preg_match('#^https?://[^/]+/(.+)$#i', $rel, $m)) {
                    $rel = $m[1];
                }

                // Normalize bare filenames / legacy paths for rule/ssdeep packs.
                if ($kind === 'rule' || strpos($rel, 'rule_files/') === 0 || preg_match('/\.zip$/i', $rel)) {
                    if (strpos($rel, 'rule_files/') !== 0 && strpos($rel, 'ssdeep_files/') !== 0 && strpos($rel, 'agent_releases/') !== 0) {
                        $rel = $this->ruleFileRelativePath($rel);
                    }
                }

                $allowed = false;
                if ($kind === 'rule' || strpos($rel, 'rule_files/') === 0) {
                    $allowed = strpos($rel, 'rule_files/') === 0;
                } elseif ($kind === 'ssdeep' || strpos($rel, 'ssdeep_files/') === 0) {
                    $allowed = strpos($rel, 'ssdeep_files/') === 0;
                } elseif ($kind === 'agent_binary' || strpos($rel, 'agent_releases/') === 0) {
                    $allowed = strpos($rel, 'agent_releases/') === 0;
                }

                if (!$allowed || strpos($rel, '..') !== false) {
                    $response = [
                        'error' => 'Invalid file path',
                        'status_code' => 400,
                        'data' => [],
                    ];
                } else {
                    $full = public_path($rel);
                    if (!is_file($full)) {
                        $response = [
                            'error' => 'File not found',
                            'status_code' => 404,
                            'data' => [],
                        ];
                    } else {
                        $bytes = file_get_contents($full);
                        $response = [
                            'error' => '',
                            'status_code' => 200,
                            'data' => [
                                'file_name' => basename($full),
                                'content_b64' => base64_encode($bytes),
                                'sha256' => hash('sha256', $bytes),
                                'size_bytes' => strlen($bytes),
                            ],
                        ];
                    }
                }
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = [
                'status_code' => 500,
                'error' => $e->getMessage(),
            ];
            // Best-effort encrypted error when site context exists.
            try {
                $header = $request->bearerToken();
                $mode = $request->mode;
                $site = $this->AuthorizationRegister($header, $mode);
                if ($site['status_code'] === '200') {
                    $datas = encrypt_decrypt('encrypt', json_encode($response), $header, $site['data']['ip_key'], $site['data']['mac_address_key']);
                    return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
                }
            } catch (\Exception $ignore) {
            }
            return response()->json($response);
        }
    }

    /**
     * Agent reports its currently running binary version.
     */
    public function reportAgentVersion(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                $response = [
                    'error' => 'The request parameters are invalid',
                    'status_code' => 400,
                    'data' => [],
                ];
            } else {
                $data_key = $data['data'];
                $ip_private = isset($data_key['ip_private']) ? $data_key['ip_private'] : null;
                $version = isset($data_key['version']) ? trim((string) $data_key['version']) : '';
                $schedule = isset($data_key['agent_update_schedule'])
                    ? $this->normalizeAgentUpdateSchedule($data_key['agent_update_schedule'])
                    : null;

                $agent = FXSiteAgents::where('site_id', $data['site']['data']['id'])
                    ->where('ip_private', $ip_private)
                    ->where('deleted_at', null)
                    ->first();

                if (!$agent) {
                    $response = [
                        'error' => 'Data not found',
                        'status_code' => 200,
                        'data' => [],
                    ];
                } else {
                    if (\Schema::hasColumn('site_agents', 'agent_version_current') && $version !== '') {
                        $agent->agent_version_current = $version;
                        // Keep legacy version column in sync when present.
                        if (\Schema::hasColumn('site_agents', 'version')) {
                            $agent->version = $version;
                        }
                    }
                    if ($schedule !== null && \Schema::hasColumn('site_agents', 'agent_update_schedule')) {
                        $agent->agent_update_schedule = $schedule;
                    }
                    if (\Schema::hasColumn('site_agents', 'agent_update_checked_at')) {
                        $agent->agent_update_checked_at = Carbon::now();
                    }
                    $agent->save();

                    $response = [
                        'error' => '',
                        'status_code' => 200,
                        'data' => [
                            'version' => $version,
                            'agent_id' => (int) $agent->id,
                        ],
                    ];
                }
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = [
                'status_code' => 500,
                'error' => $e->getMessage(),
            ];
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    /**
     * Decide whether the agent should install a Center-assigned target version.
     */
    public function checkAgentUpdate(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                $response = [
                    'error' => 'The request parameters are invalid',
                    'status_code' => 400,
                    'data' => [],
                ];
            } else {
                $data_key = $data['data'];
                $ip_private = isset($data_key['ip_private']) ? $data_key['ip_private'] : null;
                $current = isset($data_key['current_version']) ? trim((string) $data_key['current_version']) : '';
                $siteId = $data['site']['data']['id'];

                $agent = FXSiteAgents::where('site_id', $siteId)
                    ->where('ip_private', $ip_private)
                    ->where('deleted_at', null)
                    ->first();

                $target = $this->resolveAgentReleaseTarget($siteId);
                $updateAvailable = false;
                $package = null;

                if ($target && $target->target_version !== '') {
                    $pkgQuery = AgentReleasePackage::where('version', $target->target_version)
                        ->where('status', 'Y');
                    if (\Schema::hasColumn('agent_release_packages', 'kind')) {
                        // Never OTA-install a Setup/Inno package.
                        $pkgQuery->where(function ($q) {
                            $q->where('kind', 'agent_binary')
                                ->orWhereNull('kind')
                                ->orWhere('kind', '');
                        });
                    }
                    if (\Schema::hasColumn('agent_release_packages', 'os')) {
                        // Windows agent OTA only.
                        $pkgQuery->where(function ($q) {
                            $q->where('os', 'windows')
                                ->orWhereNull('os')
                                ->orWhere('os', '');
                        });
                    }
                    $package = $pkgQuery->orderBy('id', 'desc')->first();
                    // If only an installer exists for this version, do not offer update.
                    if ($package && \Schema::hasColumn('agent_release_packages', 'kind')) {
                        $k = strtolower(trim((string) ($package->kind ?: 'agent_binary')));
                        if ($k === 'installer') {
                            $package = null;
                        }
                    }
                    if ($package && $current !== '' && version_compare($this->normalizeSemver($current), $this->normalizeSemver($package->version), '!=')) {
                        $updateAvailable = true;
                    } elseif ($package && $current === '') {
                        $updateAvailable = true;
                    }
                }

                if ($agent && \Schema::hasColumn('site_agents', 'agent_version_target') && $target) {
                    $agent->agent_version_target = $target->target_version;
                    if (\Schema::hasColumn('site_agents', 'agent_update_checked_at')) {
                        $agent->agent_update_checked_at = Carbon::now();
                    }
                    if ($current !== '' && \Schema::hasColumn('site_agents', 'agent_version_current')) {
                        $agent->agent_version_current = $current;
                    }
                    $agent->save();
                }

                $relPath = '';
                if ($package) {
                    $relPath = $this->agentReleaseRelativePath($package->path);
                }

                $response = [
                    'error' => '',
                    'status_code' => 200,
                    'data' => [
                        'update_available' => $updateAvailable ? 1 : 0,
                        'current_version' => $current,
                        'target_version' => $target ? $target->target_version : '',
                        'package' => $package ? [
                            'id' => (int) $package->id,
                            'version' => $package->version,
                            'file_name' => $package->file_name,
                            'path' => $relPath,
                            'sha256' => $package->sha256,
                            'size_bytes' => (int) $package->size_bytes,
                            'kind' => (\Schema::hasColumn('agent_release_packages', 'kind') && !empty($package->kind))
                                ? $package->kind
                                : 'agent_binary',
                        ] : null,
                    ],
                ];
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = [
                'status_code' => 500,
                'error' => $e->getMessage(),
            ];
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    /**
     * Return package metadata for the assigned target (download via downloadProtectedFile).
     */
    public function downloadAgentPackage(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                $response = [
                    'error' => 'The request parameters are invalid',
                    'status_code' => 400,
                    'data' => [],
                ];
            } else {
                $data_key = $data['data'];
                $siteId = $data['site']['data']['id'];
                $version = isset($data_key['version']) ? trim((string) $data_key['version']) : '';
                $packageId = isset($data_key['package_id']) ? (int) $data_key['package_id'] : 0;

                $package = null;
                if ($packageId > 0) {
                    $package = AgentReleasePackage::where('id', $packageId)->where('status', 'Y')->first();
                } elseif ($version !== '') {
                    $package = AgentReleasePackage::where('version', $version)->where('status', 'Y')->first();
                } else {
                    $target = $this->resolveAgentReleaseTarget($siteId);
                    if ($target) {
                        $package = AgentReleasePackage::where('version', $target->target_version)->where('status', 'Y')->first();
                    }
                }

                if (!$package) {
                    $response = [
                        'error' => 'Package not found',
                        'status_code' => 404,
                        'data' => [],
                    ];
                } else {
                    $relPath = $this->agentReleaseRelativePath($package->path);
                    $response = [
                        'error' => '',
                        'status_code' => 200,
                        'data' => [
                            'id' => (int) $package->id,
                            'version' => $package->version,
                            'file_name' => $package->file_name,
                            'path' => $relPath,
                            'sha256' => $package->sha256,
                            'size_bytes' => (int) $package->size_bytes,
                            'kind' => 'agent_binary',
                        ],
                    ];
                }
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = [
                'status_code' => 500,
                'error' => $e->getMessage(),
            ];
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    /**
     * Agent/watchdog reports update lifecycle status.
     */
    public function reportAgentUpdateStatus(Request $request)
    {
        try {
            $header = $request->bearerToken();
            $mode = $request->mode;
            $data_request = $request->data;
            $data = $this->dataFalse($header, $mode, $data_request);
            if ($data === false) {
                $response = [
                    'error' => 'The request parameters are invalid',
                    'status_code' => 400,
                    'data' => [],
                ];
            } else {
                $data_key = $data['data'];
                $ip_private = isset($data_key['ip_private']) ? $data_key['ip_private'] : null;
                $status = isset($data_key['status']) ? trim((string) $data_key['status']) : 'checking';
                $message = isset($data_key['message']) ? (string) $data_key['message'] : null;
                $current = isset($data_key['current_version']) ? trim((string) $data_key['current_version']) : null;
                $target = isset($data_key['target_version']) ? trim((string) $data_key['target_version']) : null;
                $siteId = $data['site']['data']['id'];

                $agent = FXSiteAgents::where('site_id', $siteId)
                    ->where('ip_private', $ip_private)
                    ->where('deleted_at', null)
                    ->first();

                $event = new AgentReleaseEvent();
                $event->site_id = $siteId;
                $event->agent_id = $agent ? $agent->id : null;
                $event->ip_private = $ip_private;
                $event->current_version = $current;
                $event->target_version = $target;
                $event->status = $status;
                $event->message = $message;
                $event->started_at = in_array($status, ['checking', 'downloading', 'ready', 'installing'], true)
                    ? Carbon::now()
                    : null;
                $event->finished_at = in_array($status, ['success', 'rollback', 'failed'], true)
                    ? Carbon::now()
                    : null;
                $event->save();

                if ($agent) {
                    if (\Schema::hasColumn('site_agents', 'agent_update_status')) {
                        $agent->agent_update_status = $status;
                    }
                    if ($current !== null && $current !== '' && \Schema::hasColumn('site_agents', 'agent_version_current')) {
                        $agent->agent_version_current = $current;
                        if (\Schema::hasColumn('site_agents', 'version')) {
                            $agent->version = $current;
                        }
                    }
                    if ($target !== null && $target !== '' && \Schema::hasColumn('site_agents', 'agent_version_target')) {
                        $agent->agent_version_target = $target;
                    }
                    if ($status === 'success' && \Schema::hasColumn('site_agents', 'agent_update_applied_at')) {
                        $agent->agent_update_applied_at = Carbon::now();
                    }
                    $agent->save();
                }

                $response = [
                    'error' => '',
                    'status_code' => 200,
                    'data' => [
                        'event_id' => (int) $event->id,
                        'status' => $status,
                    ],
                ];
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = [
                'status_code' => 500,
                'error' => $e->getMessage(),
            ];
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header, $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    private function resolveAgentReleaseTarget($siteId)
    {
        if (!\Schema::hasTable('agent_release_targets')) {
            return null;
        }
        $siteTarget = AgentReleaseTarget::where('site_id', $siteId)->where('status', 'Y')->orderBy('id', 'desc')->first();
        if ($siteTarget) {
            return $siteTarget;
        }
        return AgentReleaseTarget::whereNull('site_id')->where('status', 'Y')->orderBy('id', 'desc')->first();
    }

    private function agentReleaseRelativePath($path)
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }
        if (preg_match('#^https?://[^/]+/(.+)$#i', $path, $m)) {
            $path = $m[1];
        }
        $path = ltrim(str_replace('\\', '/', $path), '/');
        if (strpos($path, 'agent_releases/') === 0) {
            return $path;
        }
        return 'agent_releases/'.basename($path);
    }

    /**
     * Normalize rule pack paths to public/rule_files/<basename>.
     */
    private function ruleFileRelativePath($path)
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }
        if (preg_match('#^https?://[^/]+/(.+)$#i', $path, $m)) {
            $path = $m[1];
        }
        $path = ltrim(str_replace('\\', '/', $path), '/');
        if (strpos($path, 'storage/') === 0) {
            $path = substr($path, strlen('storage/'));
        }
        if (strpos($path, 'rule_files/') === 0) {
            return $path;
        }
        $base = basename($path);
        if ($base === '' || $base === '.' || $base === '..') {
            return '';
        }
        return 'rule_files/'.$base;
    }

    private function normalizeAgentUpdateSchedule($value)
    {
        return AgentScheduleInterval::normalizeAgentUpdate($value, AgentScheduleInterval::DEFAULT_AGENT_UPDATE);
    }

    private function normalizeSemver($version)
    {
        $v = trim((string) $version);
        $v = ltrim($v, 'vV');
        if ($v === '') {
            return '0.0.0';
        }
        $parts = explode('.', $v);
        while (count($parts) < 3) {
            $parts[] = '0';
        }
        return implode('.', array_slice($parts, 0, 3));
    }
}
