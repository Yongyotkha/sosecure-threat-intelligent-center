<?php

namespace Modules\HoneypotCenter\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Services\HoneypotIngestService;

class HoneypotIngestController extends Controller
{
    protected $ingest;

    public function __construct(HoneypotIngestService $ingest)
    {
        $this->ingest = $ingest;
    }

    public function store(Request $request)
    {
        $siteId = (int) $request->attributes->get('site_id');

        try {
            $validated = $this->validatePayload($request);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $alerts = [];
        foreach (($validated['alerts'] ?? []) as $index => $alert) {
            $alerts[] = array_merge(
                (array) $request->input("alerts.$index", []),
                $alert
            );
        }
        $summary = $validated['summary'] ?? null;

        if (empty($alerts) && $summary === null) {
            return response()->json([
                'message' => 'At least one alert or summary payload is required.',
            ], 422);
        }

        try {
            $sensor = $this->resolveSensorContext($request);
            $result = $this->ingest->ingest($siteId, $alerts, $summary, $sensor);
        } catch (\Throwable $e) {
            Log::error('HONEYPOT.INGEST failed', [
                'site_id' => $siteId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Ingest failed'], 500);
        }

        Log::info('HONEYPOT.INGEST ok', array_merge([
            'site_id' => $siteId,
            'sensor_token_id' => $sensor['sensor_token_id'] ?? null,
            'sensor_name' => $sensor['sensor_name'] ?? null,
        ], $result));

        return response()->json([
            'status' => 'accepted',
            'site_id' => $siteId,
            'sensor_name' => $sensor['sensor_name'] ?? null,
            'alerts_ingested' => $result['alerts_ingested'],
            'alerts_updated' => $result['alerts_updated'],
            'summary_ingested' => $result['summary_ingested'],
        ], 202);
    }

    protected function resolveSensorContext(Request $request): array
    {
        $token = $request->attributes->get('api_token');

        return [
            'sensor_token_id' => $token ? (int) $token->id : 0,
            'sensor_name' => trim((string) $request->attributes->get('honeypot_agent_name', '')),
        ];
    }

    protected function validatePayload(Request $request): array
    {
        return $request->validate([
            'alerts' => 'nullable|array',
            'alerts.*.alert_id' => 'required|string|max:128',
            'alerts.*.timestamp' => 'required',
            'alerts.*.attacker_ip' => 'required|string|max:45',
            'alerts.*.severity' => 'required|string|max:32',
            'alerts.*.threat_name' => 'required|string|max:255',
            'alerts.*.payload' => 'nullable|string|max:65535',
            'alerts.*.request_path' => 'nullable|string|max:2048',
            'alerts.*.title' => 'nullable|string|max:255',
            'alerts.*.summary' => 'nullable|string|max:2048',
            'alerts.*.origin' => 'nullable|string|max:128',
            'alerts.*.path' => 'nullable|string|max:2048',
            'alerts.*.threat_level' => 'nullable|string|max:32',
            'alerts.*.current_risk_score' => 'nullable|integer|min:0',
            'alerts.*.raw_details' => 'nullable|array',
            'summary' => 'nullable|array',
            'summary.period_start' => 'required_with:summary',
            'summary.timestamp' => 'nullable',
            'summary.total_hits' => 'nullable|integer|min:0',
            'summary.unique_ips' => 'nullable|integer|min:0',
            'summary.by_severity' => 'nullable|array',
        ]);
    }
}
