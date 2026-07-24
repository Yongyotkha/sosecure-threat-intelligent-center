<?php

namespace App\Http\Controllers\Api;

use App\YaraLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApiAgentSsdeepController extends ApiController
{
    private function dataFalse($bearerToken, $mode, $data)
    {
        try {
            $header = $bearerToken;
            $site = $this->AuthorizationRegister($header, $mode);
            if ($site['status_code'] !== '200') {
                return $this->AuthorizationRegister($header, $mode);
            }

            $value = $data;
            $data = $this->encrypt_decrypt('decrypt', $value, $header, $site['data']['ip_key'], $site['data']['mac_address_key']);

            if ($data === false) {
                return $data;
            }

            $data_return = [
                'site' => $site,
                'data' => json_decode($data, true),
            ];

            return $data_return;
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'error' => $e->getMessage(),
            );
            return response()->json($response);
        }
    }

    public function sendLogSsdeep(Request $request)
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
                $ssdeep = $data_key['ssdeep'] ?? [];

                $ids = [];
                foreach ($ssdeep as $item) {
                    // Minimal validation: if missing required fields, skip the item to avoid crashing.
                    $agent_id = $item['agent_id'] ?? null;
                    $path = $item['path'] ?? null;
                    $engine = $item['engine'] ?? '';
                    $rule = $item['rule'] ?? '';
                    if (empty($agent_id) || empty($path) || empty($rule)) {
                        continue;
                    }

                    // Prevent collisions with existing YARA logs:
                    // - sendLogYara uses `rule` as-is
                    // - sendLogSsdeep will store rule under a prefixed key
                    $rule_key = 'ssdeep_engine:' . $engine . ':' . $rule;

                    $description = $item['description'] ?? '';
                    $device_name = $item['device_name'] ?? '';
                    $file_text = $item['ssdeep'] ?? '';
                    $detected_at_raw = $item['detected_at'] ?? null;
                    $detected_at = $detected_at_raw ? Carbon::parse($detected_at_raw) : Carbon::now();

                    $yaraLog = YaraLog::where('agent_id', $agent_id)
                        ->where('site_id', $data['site']['data']['id'])
                        ->where('path', $path)
                        ->where('rule', $rule_key)
                        ->first();

                    if ($yaraLog) {
                        $yaraLog->last_scan = $detected_at;
                        $yaraLog->description = $description;
                        $yaraLog->device_name = $device_name;
                        $yaraLog->file_text = $file_text;
                        $yaraLog->mode = $mode;
                        $yaraLog->save();
                    } else {
                        $yaraLog = new YaraLog();
                        $yaraLog->agent_id = $agent_id;
                        $yaraLog->site_id = $data['site']['data']['id'];
                        $yaraLog->path = $path;
                        $yaraLog->rule = $rule_key;
                        $yaraLog->description = $description;
                        $yaraLog->device_name = $device_name;
                        $yaraLog->file_text = $file_text;
                        $yaraLog->first_scan = $detected_at;
                        $yaraLog->last_scan = $detected_at;
                        $yaraLog->mode = $mode;
                        $yaraLog->save();
                    }

                    $ids[] = $yaraLog->id;
                }

                $response = [
                    'error' => '',
                    'status_code' => 200,
                    'data' => [
                        'accepted' => count($ids),
                        'ids' => $ids,
                    ],
                ];
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header ?? '', $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'error' => $e->getMessage(),
            );
            // Best-effort: encrypt with whatever keys are available.
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header ?? '', '192.168.0.1', '000:000:000:000');
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function getSsdeep(Request $request)
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
                $now = Carbon::now();
                $ssdeep_version = env('SSDEEP_DB_VERSION', '0');
                $response = [
                    'error' => '',
                    'status_code' => 200,
                    'data' => [
                        'ssdeep_db' => [
                            'version' => $ssdeep_version,
                            'signature_count' => 0,
                            'updated_at' => $now->toIso8601String(),
                            'min_agent_build' => '0.0.0',
                        ],
                    ],
                ];
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header ?? '', $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'error' => $e->getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header ?? '', '192.168.0.1', '000:000:000:000');
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function downloadSsdeepSite(Request $request)
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
                // ยังไม่ implement ระบบ signatures DB (เพื่อไม่ให้พัง/กระทบ flow เดิม)
                $response = [
                    'error' => '',
                    'status_code' => 200,
                    'data' => [
                        'signatures' => [],
                    ],
                ];
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header ?? '', $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'error' => $e->getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header ?? '', '192.168.0.1', '000:000:000:000');
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function downloadSsdeepSiteComplete(Request $request)
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
                $response = [
                    'error' => '',
                    'status_code' => 200,
                    'data' => [
                        'ok' => true,
                    ],
                ];
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header ?? '', $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'error' => $e->getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header ?? '', '192.168.0.1', '000:000:000:000');
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function updateSsdeepDownload(Request $request)
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
                $response = [
                    'error' => '',
                    'status_code' => 200,
                    'data' => [],
                ];
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header ?? '', $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'error' => $e->getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header ?? '', '192.168.0.1', '000:000:000:000');
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }

    public function sendSsdeepCandidate(Request $request)
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
                $candidates = $data_key['candidates'] ?? [];
                $accepted = is_array($candidates) ? count($candidates) : 0;

                $response = [
                    'error' => '',
                    'status_code' => 200,
                    'data' => [
                        'accepted' => $accepted,
                        'queue_ids' => [],
                    ],
                ];
            }

            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header ?? '', $data['site']['data']['ip_key'], $data['site']['data']['mac_address_key']);
            return response()->json(['error' => '', 'status_code' => 200, 'data' => $datas]);
        } catch (\Exception $e) {
            $response = array(
                'status_code' => 500,
                'error' => $e->getMessage(),
            );
            $data_transcation = json_encode($response);
            $datas = encrypt_decrypt('encrypt', $data_transcation, $header ?? '', '192.168.0.1', '000:000:000:000');
            return response()->json(['error' => '', 'status_code' => 500, 'data' => $datas]);
        }
    }
}

