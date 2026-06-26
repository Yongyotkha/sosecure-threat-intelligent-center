<?php

namespace Modules\HoneypotCenter\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\ApiToken;
use App\Menu;
use App\Services\HoneypotDashboardService;
use Modules\SiteSettings\Entities\site_menu_permission;

class HoneypotDashboardController extends Controller
{
    protected $dashboard;

    public function __construct(HoneypotDashboardService $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    public function index(Request $request)
    {
        $this->authorizeDashboardAccess();

        $filters = $this->resolveFilters($request);
        $siteNames = $filters['sites']->pluck('name', 'id')->toArray();
        $selectedAgentLabel = $this->resolveAgentLabel($filters['sensor_token_id'], $filters['agents']);

        $data = [
            'page' => langapp('honeypot'),
            'sites' => $filters['sites'],
            'site_names' => $siteNames,
            'agents' => $filters['agents'],
            'selected_site_id' => $filters['site_id'],
            'selected_sensor_token_id' => $filters['sensor_token_id'],
            'selected_agent_label' => $selectedAgentLabel,
            'can_view_all_sites' => $filters['sites']->count() !== 1,
            'window' => $this->windowSelectValue($filters['window']),
            'date_from' => $filters['window']['from'] ?? '',
            'date_to' => $filters['window']['to'] ?? '',
        ];

        return view('honeypotcenter::dashboard', $data);
    }

    public function data(Request $request)
    {
        $this->authorizeDashboardAccess();

        $filters = $this->resolveFilters($request);

        return response()->json(
            $this->dashboard->getDashboardData(
                $filters['site_id'],
                $filters['window'],
                $filters['sensor_token_id'],
                $filters['allowed_site_ids']
            )
        );
    }

    public function section(Request $request, string $section)
    {
        $this->authorizeDashboardAccess();

        $filters = $this->resolveFilters($request);

        try {
            return response()->json(
                $this->dashboard->getSectionData(
                    $section,
                    $filters['site_id'],
                    $filters['window'],
                    $filters['sensor_token_id'],
                    $filters['allowed_site_ids']
                )
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => 'invalid_section'], 404);
        }
    }

    public function agents(Request $request)
    {
        $this->authorizeDashboardAccess();

        $sites = $this->accessibleSites();
        $selectedSiteId = $this->resolveSiteId($request, $sites);
        if ($selectedSiteId === null && $sites->count() === 1) {
            $selectedSiteId = (int) $sites->first()->id;
        }

        if (!$selectedSiteId) {
            return response()->json(['agents' => []]);
        }

        $agents = $this->getSiteAgents($selectedSiteId)->map(function ($agent) {
            return [
                'id' => (int) $agent->id,
                'name' => (string) $agent->name,
            ];
        })->values();

        return response()->json(['agents' => $agents]);
    }

    public function geoip(string $ip)
    {
        $this->authorizeDashboardAccess();

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return response()->json(['error' => 'invalid_ip'], 400);
        }

        if ($this->isPrivateIp($ip)) {
            return response()->json([
                'ip' => $ip,
                'country' => 'Private Network',
                'city' => 'N/A',
                'lat' => null,
                'lon' => null,
                'private' => true,
            ]);
        }

        $data = Cache::remember('honeypot_geo_' . $ip, 86400, function () use ($ip) {
            try {
                $response = Http::timeout(4)->get("http://ip-api.com/json/{$ip}", [
                    'fields' => 'status,country,city,lat,lon,isp',
                ]);

                if (!$response->ok()) {
                    return null;
                }

                $payload = $response->json();
                if (($payload['status'] ?? '') !== 'success') {
                    return null;
                }

                return [
                    'ip' => $ip,
                    'country' => (string) ($payload['country'] ?? 'Unknown'),
                    'city' => (string) ($payload['city'] ?? ''),
                    'lat' => isset($payload['lat']) ? (float) $payload['lat'] : null,
                    'lon' => isset($payload['lon']) ? (float) $payload['lon'] : null,
                    'isp' => (string) ($payload['isp'] ?? ''),
                    'private' => false,
                ];
            } catch (\Throwable $e) {
                return null;
            }
        });

        if (!$data) {
            return response()->json([
                'ip' => $ip,
                'country' => 'Unknown',
                'city' => 'N/A',
                'lat' => null,
                'lon' => null,
                'private' => false,
            ]);
        }

        return response()->json($data);
    }

    protected function isPrivateIp(string $ip): bool
    {
        return (bool) preg_match(
            '/^(127\.|10\.|192\.168\.|172\.(1[6-9]|2[0-9]|3[01])\.|::1$|localhost$)/',
            $ip
        );
    }

    protected function authorizeDashboardAccess(): void
    {
        $role = @check_role_custom();
        $global = @get_role_custom();

        if (
            @$global['superadmin'] == 1
            || @$role['monitoring'] == 1
            || @$role['role_center'] == 1
            || @$role['dashboard'] == 1
        ) {
            return;
        }

        check_permission403();
    }

    protected function accessibleSites()
    {
        $sites = $this->userSitesFromRole();
        if ($sites->isEmpty()) {
            return collect();
        }

        $honeypotMenuCodes = $this->honeypotMenuCodes();
        if (empty($honeypotMenuCodes)) {
            return $sites->sortBy('name')->values();
        }

        $siteIdsWithMenu = site_menu_permission::query()
            ->whereNull('deleted_at')
            ->whereIn('menu_code', $honeypotMenuCodes)
            ->whereIn('site_id', $sites->pluck('id')->all())
            ->pluck('site_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->all();

        return $sites->filter(function ($site) use ($siteIdsWithMenu) {
            return in_array((int) $site->id, $siteIdsWithMenu, true);
        })->sortBy('name')->values();
    }

    protected function userSitesFromRole()
    {
        $roleContext = @get_role_custom();
        $sites = $roleContext['SiteSettings'] ?? collect();

        if ($sites instanceof \Illuminate\Support\Collection) {
            return $sites;
        }

        if (is_array($sites)) {
            return collect($sites);
        }

        return collect();
    }

    protected function honeypotMenuCodes(): array
    {
        return Menu::query()
            ->whereNull('deleted_at')
            ->where('active', 1)
            ->where(function ($query) {
                $query->where('langapp', 'honeypot')
                    ->orWhere('check_menu_active', 'honeypot');
            })
            ->pluck('code')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function resolveFilters(Request $request): array
    {
        $sites = $this->accessibleSites();
        $selectedSiteId = $this->resolveSiteId($request, $sites);
        if ($selectedSiteId === null && $sites->count() === 1) {
            $selectedSiteId = (int) $sites->first()->id;
        }
        $windowConfig = $this->resolveWindow($request);
        $agents = $this->getSiteAgents($selectedSiteId);
        $selectedSensorTokenId = $this->resolveSensorTokenId($request, $selectedSiteId, $agents);
        $allowedSiteIds = $sites->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        return [
            'sites' => $sites,
            'site_id' => $selectedSiteId,
            'window' => $windowConfig,
            'sensor_token_id' => $selectedSensorTokenId,
            'allowed_site_ids' => $allowedSiteIds,
            'agents' => $agents,
        ];
    }

    protected function resolveWindow(Request $request): array
    {
        $window = (string) $request->query('window', 'today');
        $timezone = new \DateTimeZone('Asia/Bangkok');

        if ($window === 'custom') {
            return [
                'mode' => 'custom',
                'from' => (string) $request->query('date_from', ''),
                'to' => (string) $request->query('date_to', ''),
            ];
        }

        if ($window === 'today') {
            return ['mode' => 'today', 'label' => 'Today'];
        }

        if (in_array($window, ['24', '48', '72', '168'], true)) {
            return [
                'mode' => 'hours',
                'hours' => (int) $window,
                'label' => 'Last ' . $window . 'h',
            ];
        }

        $legacyHours = (int) $request->query('hours', 0);
        if ($legacyHours > 0) {
            $hours = max(1, min($legacyHours, 168));

            return [
                'mode' => 'hours',
                'hours' => $hours,
                'label' => 'Last ' . $hours . 'h',
            ];
        }

        return ['mode' => 'today', 'label' => 'Today'];
    }

    protected function windowSelectValue(array $windowConfig): string
    {
        if (($windowConfig['mode'] ?? '') === 'custom') {
            return 'custom';
        }

        if (($windowConfig['mode'] ?? '') === 'hours') {
            return (string) ($windowConfig['hours'] ?? 24);
        }

        return 'today';
    }

    protected function resolveSiteId(Request $request, $sites): ?int
    {
        $siteId = $request->query('site_id');

        if ($siteId === null || $siteId === '' || $siteId === 'all') {
            return null;
        }

        $siteId = (int) $siteId;
        $allowed = $sites->pluck('id')->map(function ($id) {
            return (int) $id;
        })->toArray();

        if (!in_array($siteId, $allowed, true)) {
            return null;
        }

        return $siteId;
    }

    protected function getSiteAgents(?int $siteId)
    {
        if (!$siteId) {
            return collect();
        }

        $tokenType = (string) config('honeypot.api.token_type', 'honeypot_agent');
        $fromTokens = ApiToken::query()
            ->where('type', $tokenType)
            ->where('scope', ApiToken::SCOPE_SITE)
            ->where('site_id', $siteId)
            ->orderBy('name')
            ->get(['id', 'name', 'last_used_at', 'last_ip']);

        $tokenIds = $fromTokens->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        foreach ($this->dashboard->getAgentsSeenInAlerts($siteId) as $seen) {
            $seenId = (int) ($seen['sensor_token_id'] ?? 0);
            if ($seenId <= 0 || in_array($seenId, $tokenIds, true)) {
                continue;
            }

            $fromTokens->push((object) [
                'id' => $seenId,
                'name' => (string) ($seen['sensor_name'] ?: ('Agent #' . $seenId)),
                'last_used_at' => null,
                'last_ip' => null,
            ]);
        }

        return $fromTokens->sortBy('name')->values();
    }

    protected function resolveSensorTokenId(Request $request, ?int $siteId, $agents): ?int
    {
        if (!$siteId) {
            return null;
        }

        $value = $request->query('sensor_token_id');
        if ($value === null || $value === '' || $value === 'all') {
            return null;
        }

        $sensorTokenId = (int) $value;
        $allowed = $agents->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        if (!in_array($sensorTokenId, $allowed, true)) {
            return null;
        }

        return $sensorTokenId;
    }

    protected function resolveAgentLabel(?int $sensorTokenId, $agents): string
    {
        if (!$sensorTokenId) {
            return 'All Agents';
        }

        $agent = $agents->firstWhere('id', $sensorTokenId);

        return $agent ? (string) $agent->name : ('Agent #' . $sensorTokenId);
    }
}
