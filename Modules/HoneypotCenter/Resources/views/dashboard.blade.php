@extends('layouts.app')

@push('pagestyle')
<style>
/* Layout only — inherit font from app theme (Sofia / system_font) */
.hp-dash { color: #333; }
.hp-dash * { box-sizing: border-box; }
.hp-chart { position: relative; height: 280px; overflow: hidden; }
.hp-chart canvas { display: block; max-width: 100%; max-height: 100%; }
.hp-status { display: inline-flex; flex-wrap: wrap; gap: 6px; margin-left: 12px; vertical-align: middle; }
.hp-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 3px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
.hp-badge--online { background: #dff0d8; color: #3c763d; border: 1px solid #d6e9c6; }
.hp-badge--connected { background: #d9edf7; color: #31708f; border: 1px solid #bce8f1; }
.hp-pulse { width: 7px; height: 7px; border-radius: 50%; background: #5cb85c; animation: hp-pulse 2s infinite; }
@keyframes hp-pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.4; } }
.hp-stat-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 15px; margin-bottom: 15px; }
@media (max-width: 991px) { .hp-stat-grid { grid-template-columns: minmax(0, 1fr); } }
.hp-stat { background: #f3f3f3; border: 1px solid #e3e3e3; box-shadow: 0 1px 2px rgba(0,0,0,.08); padding: 16px; display: flex; align-items: center; justify-content: space-between; min-height: 110px; }
.hp-stat-label { font-size: 13px; font-weight: 600; color: #555; text-transform: uppercase; margin-bottom: 6px; }
.hp-stat-value { font-size: 30px; font-weight: 700; margin: 0; line-height: 1; }
.hp-stat-value--red { color: #d9534f; }
.hp-stat-value--orange { color: #f0ad4e; }
.hp-stat-value--blue { color: #0b96c5; }
.hp-stat-icon { font-size: 28px; opacity: .85; }
.hp-grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px; margin-bottom: 15px; }
.hp-grid-2 > .hp-card { min-width: 0; overflow: hidden; }
@media (max-width: 1199px) { .hp-grid-2 { grid-template-columns: minmax(0, 1fr); } }
.hp-card { margin-bottom: 15px; background: #fff; border: 1px solid #e3e3e3; box-shadow: 0 1px 2px rgba(0,0,0,.08); padding: 15px; }
.hp-card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.hp-card-title { font-size: 16px; font-weight: 700; color: #333; margin: 0; }
.hp-card-tag { font-size: 11px; font-weight: 600; color: #777; background: #eee; padding: 3px 8px; border-radius: 3px; text-transform: uppercase; }
.hp-chart--wide { height: 260px; overflow: hidden; }
.hp-grid-map { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; align-items: start; }
.hp-grid-map > .hp-card { min-width: 0; overflow: hidden; }
@media (max-width: 991px) { .hp-grid-map { grid-template-columns: 1fr; } }
.hp-globe { width: 100%; height: 280px; max-height: 280px; background: #020617; border-radius: 4px; overflow: hidden; position: relative; border: 1px solid #1e293b; isolation: isolate; }
#hp-globe-viz { width: 100%; height: 100%; max-width: 100%; overflow: hidden; position: relative; }
#hp-globe-viz canvas { display: block !important; width: 100% !important; height: 100% !important; max-width: 100% !important; object-fit: contain; margin: 0 auto; }
.hp-globe-overlay { position: absolute; left: 10px; bottom: 10px; z-index: 2; background: rgba(15, 23, 42, 0.88); color: #cbd5e1; font-size: 12px; line-height: 1.35; padding: 6px 10px; border-radius: 3px; border: 1px solid #334155; max-width: calc(100% - 20px); pointer-events: none; }
.hp-globe-overlay.is-warn { color: #fbbf24; }
.hp-table { width: 100%; margin-bottom: 0; font-size: 13px; }
.hp-table thead th { font-size: 12px; font-weight: 700; text-transform: uppercase; }
.hp-count { display: inline-block; min-width: 36px; text-align: center; font-weight: 700; font-size: 13px; color: #0b96c5; background: #e8f6fc; padding: 3px 8px; border-radius: 3px; }
.hp-path { font-size: 13px; color: #d9534f; word-break: break-all; }
.hp-ip { font-weight: 700; font-size: 13px; color: #0b96c5; }
.hp-risk { font-size: 14px; font-weight: 700; color: #f0ad4e; text-align: center; }
.hp-tags { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 4px; }
.hp-tag { background: #eee; color: #555; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 3px; text-transform: uppercase; }
.hp-note { font-size: 13px; color: #777; }
.hp-sev { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.hp-sev--critical { background: #f2dede; color: #a94442; }
.hp-sev--high { background: #fcf8e3; color: #8a6d3b; }
.hp-sev--medium { background: #fcf8e3; color: #8a6d3b; }
.hp-sev--low { background: #dff0d8; color: #3c763d; }
.hp-sev--info { background: #f5f5f5; color: #777; }
.hp-node { font-size: 11px; color: #777; font-weight: 600; text-transform: uppercase; margin-top: 2px; }
.hp-payload-box { margin-top: 0; padding: 10px; background: #222; color: #f9a8d4; font-family: Consolas, Monaco, monospace; font-size: 12px; border-radius: 3px; max-height: 140px; overflow: auto; white-space: pre-wrap; word-break: break-all; }
.hp-empty { text-align: center; color: #999; padding: 20px; font-size: 13px; font-style: italic; }
.hp-loading { text-align: center; color: #aaa; padding: 20px; font-size: 13px; }
.hp-stat-value.is-loading { color: #bbb; font-size: 22px; }
.hp-load-wrap { position: relative; }
.hp-table-wrap.hp-load-wrap { min-height: 120px; }
.hp-loader.backdrop-loader { display: none; background: rgba(255, 255, 255, 0.92); }
.hp-loader.backdrop-loader.is-active { display: flex; }
.hp-loader .loadding-text { margin-top: 0; margin-left: 0; text-align: center; color: #777; font-size: 13px; }
.hp-loader-inner { display: flex; flex-direction: column; align-items: center; gap: 12px; }
.hp-globe-loader.backdrop-loader { background: rgba(2, 6, 23, 0.72); }
.hp-globe-loader .loadding-text { color: #cbd5e1; }
.hp-time { font-size: 13px; color: #777; }
</style>
@endpush

@section('content')
@php
    $agentCount = isset($agents) ? $agents->count() : 0;
@endphp
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
            <div class="header-flex-overflow m-t-10">
                <div class="fwb-16">
                    {{ langapp('honeypot') }}
                    <span class="hp-status">
                        <span class="hp-badge hp-badge--online"><span class="hp-pulse"></span> Online</span>
                        @if(!empty($selected_site_id) && ($agentCount ?? 0) > 0)
                            <span class="hp-badge hp-badge--connected">{{ $agentCount }} Agent{{ $agentCount > 1 ? 's' : '' }}</span>
                        @endif
                    </span>
                </div>

                <div class="ml-2 text-right">
                    <form method="get" action="{{ route('honeypotcenter.dashboard') }}" id="honeypot-filter-form" class="d-inline-block">
                        <div class="max-w-select d-inline-block m-r-xs">
                            <select name="site_id" id="hp-site-select" class="form-control" title="Site">
                                @if(!empty($can_view_all_sites))
                                    <option value="all" {{ empty($selected_site_id) ? 'selected' : '' }}>All Sites</option>
                                @endif
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" {{ (int) $selected_site_id === (int) $site->id ? 'selected' : '' }}>
                                        {{ $site->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="max-w-select d-inline-block m-r-xs" id="hp-agent-filter" @if(empty($selected_site_id)) style="display:none;" @endif>
                            <select name="sensor_token_id" id="hp-agent-select" class="form-control" title="Agent" {{ empty($selected_site_id) ? 'disabled' : '' }}>
                                <option value="all" {{ empty($selected_sensor_token_id) ? 'selected' : '' }}>All Agents</option>
                                @foreach(($agents ?? collect()) as $agent)
                                    <option value="{{ $agent->id }}" {{ (int) ($selected_sensor_token_id ?? 0) === (int) $agent->id ? 'selected' : '' }}>
                                        {{ $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="max-w-select d-inline-block m-r-xs">
                            <select name="window" id="hp-window-select" class="form-control" title="Window">
                                <option value="today" {{ ($window ?? 'today') === 'today' ? 'selected' : '' }}>Today</option>
                                @foreach([24, 48, 72, 168] as $option)
                                    <option value="{{ $option }}" {{ ($window ?? 'today') === (string) $option ? 'selected' : '' }}>Last {{ $option }}h</option>
                                @endforeach
                                <option value="custom" {{ ($window ?? 'today') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-{{ get_option('theme_color') }}" id="hp-apply-btn">Apply</button>
                    </form>
                </div>
            </div>
        </header>

        <section class="scrollable wrapper hp-dash">
            @php
                $defaultDateTo = now('Asia/Bangkok')->format('Y-m-d');
                $defaultDateFrom = now('Asia/Bangkok')->subDays(6)->format('Y-m-d');
            @endphp
            <section class="panel panel-default" id="hp-custom-range-panel" @if(($window ?? 'today') !== 'custom') style="display: none;" @endif>
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-md-12">
                            <i class="fas fa-calendar-alt"></i> Custom Date Range
                        </div>
                    </div>
                </header>
                <div class="panel-body" style="padding: 0 !important;">
                    <div class="container-fluid" style="padding: 2rem;">
                        <div class="row">
                            <div class="col-lg-4 col-md-6 mb-1">
                                <h5 class="font-weight-bold">From</h5>
                                <input type="date" form="honeypot-filter-form" name="date_from" id="hp-date-from" class="form-control" value="{{ !empty($date_from) ? $date_from : $defaultDateFrom }}">
                            </div>
                            <div class="col-lg-4 col-md-6 mb-1">
                                <h5 class="font-weight-bold">To</h5>
                                <input type="date" form="honeypot-filter-form" name="date_to" id="hp-date-to" class="form-control" value="{{ !empty($date_to) ? $date_to : $defaultDateTo }}">
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            @php
                $selectedSiteLabel = empty($selected_site_id)
                    ? 'All Sites'
                    : ($site_names[$selected_site_id] ?? 'Site #' . $selected_site_id);
            @endphp

            <div class="hp-stat-grid hp-load-wrap">
                <div id="hp-loader-stats" class="hp-loader backdrop-loader is-active">
                    <div class="hp-loader-inner">
                        <div class="loader4"></div>
                        <div class="loadding-text">Loading ...</div>
                    </div>
                </div>
                <div class="hp-stat">
                    <div>
                        <div class="hp-stat-label">High Risk Alerts</div>
                        <h3 class="hp-stat-value hp-stat-value--red is-loading" id="hp-stat-high-risk">—</h3>
                    </div>
                    <div class="hp-stat-icon">🛡️</div>
                </div>
                <div class="hp-stat">
                    <div>
                        <div class="hp-stat-label">Active Attackers</div>
                        <h3 class="hp-stat-value hp-stat-value--orange is-loading" id="hp-stat-attackers">—</h3>
                    </div>
                    <div class="hp-stat-icon">👾</div>
                </div>
                <div class="hp-stat">
                    <div>
                        <div class="hp-stat-label">Logs Processed</div>
                        <h3 class="hp-stat-value hp-stat-value--blue is-loading" id="hp-stat-logs">—</h3>
                    </div>
                    <div class="hp-stat-icon">📊</div>
                </div>
            </div>

            <div id="hp-visual-section">
            <div class="hp-grid-2">
                <div class="hp-card">
                    <div class="hp-card-head">
                        <h3 class="hp-card-title">Hourly Attack Distribution</h3>
                        <span class="hp-card-tag" id="hp-window-tag">—</span>
                    </div>
                    <div class="hp-chart hp-load-wrap">
                        <div id="hp-loader-hourly" class="hp-loader backdrop-loader is-active">
                            <div class="hp-loader-inner">
                                <div class="loader4"></div>
                                <div class="loadding-text">Loading ...</div>
                            </div>
                        </div>
                        <canvas id="hp-hourly-chart"></canvas>
                    </div>
                </div>
                <div class="hp-card">
                    <div class="hp-card-head">
                        <h3 class="hp-card-title">Daily Attack Trend</h3>
                        <span class="hp-card-tag" id="hp-trend-window-tag">—</span>
                    </div>
                    <div class="hp-chart hp-load-wrap">
                        <div id="hp-loader-daily-trend" class="hp-loader backdrop-loader is-active">
                            <div class="hp-loader-inner">
                                <div class="loader4"></div>
                                <div class="loadding-text">Loading ...</div>
                            </div>
                        </div>
                        <canvas id="hp-trend-chart"></canvas>
                    </div>
                </div>
            </div>

            <div class="hp-card" style="margin-bottom:24px;">
                <div class="hp-card-head">
                    <h3 class="hp-card-title">Attack Types Distribution</h3>
                </div>
                <div class="hp-chart hp-chart--wide hp-load-wrap">
                    <div id="hp-loader-threat-types" class="hp-loader backdrop-loader is-active">
                        <div class="hp-loader-inner">
                            <div class="loader4"></div>
                            <div class="loadding-text">Loading ...</div>
                        </div>
                    </div>
                    <canvas id="hp-types-chart"></canvas>
                </div>
            </div>

            <div class="hp-grid-map">
                <div class="hp-card">
                    <h3 class="hp-card-title" style="margin-bottom:16px;">Global Threat Monitor</h3>
                    <div class="hp-globe hp-load-wrap">
                        <div id="hp-loader-globe" class="hp-loader hp-globe-loader backdrop-loader is-active">
                            <div class="hp-loader-inner">
                                <div class="loader4"></div>
                                <div class="loadding-text">Loading ...</div>
                            </div>
                        </div>
                        <div id="hp-globe-viz"></div>
                        <div class="hp-globe-overlay" id="hp-globe-status">Loading threat map...</div>
                    </div>
                </div>
                <div class="hp-card">
                    <div class="hp-card-head">
                        <h3 class="hp-card-title">Top 10 Suspicious Paths</h3>
                    </div>
                    <div class="hp-table-wrap hp-load-wrap">
                        <div id="hp-loader-top-paths" class="hp-loader backdrop-loader is-active">
                            <div class="hp-loader-inner">
                                <div class="loader4"></div>
                                <div class="loadding-text">Loading ...</div>
                            </div>
                        </div>
                        <table class="table table-bordered table-striped hp-table">
                        <thead>
                            <tr>
                                <th style="width:80px;text-align:center;">Count</th>
                                <th>Target Path</th>
                            </tr>
                        </thead>
                        <tbody id="hp-top-paths-body">
                            <tr><td colspan="2" class="hp-empty">—</td></tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            </div>

            <div class="hp-card" style="margin-bottom:24px;padding:0;overflow:hidden;">
                <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;">
                    <h3 class="hp-card-title">Top 10 High-Risk Attackers</h3>
                </div>
                <div class="hp-table-wrap hp-load-wrap" style="overflow-x:auto;">
                    <div id="hp-loader-top-attackers" class="hp-loader backdrop-loader is-active">
                        <div class="hp-loader-inner">
                            <div class="loader4"></div>
                            <div class="loadding-text">Loading ...</div>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped hp-table">
                        <thead>
                            <tr>
                                <th style="width:18%;">IP Address</th>
                                <th style="width:12%;text-align:center;">Risk Score</th>
                                <th>Tags / Details</th>
                            </tr>
                        </thead>
                        <tbody id="hp-top-attackers-body">
                            <tr><td colspan="3" class="hp-empty">—</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="hp-card" style="padding:0;overflow:hidden;">
                <div style="padding:16px 24px;border-bottom:1px solid #f1f5f9;background:#fafafa;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <h3 class="hp-card-title" style="margin:0;">Recent Event Logs (Last 10)</h3>
                    <span class="hp-note" id="hp-recent-meta">Site: {{ e($selectedSiteLabel) }} · Agent: {{ e($selected_agent_label ?? 'All Agents') }} · Window: —</span>
                </div>
                <div class="hp-table-wrap hp-load-wrap" style="overflow-x:auto;">
                    <div id="hp-loader-recent-logs" class="hp-loader backdrop-loader is-active">
                        <div class="hp-loader-inner">
                            <div class="loader4"></div>
                            <div class="loadding-text">Loading ...</div>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped hp-table">
                        <thead>
                            <tr>
                                <th style="width:110px;">Time</th>
                                <th style="width:180px;">Source IP &amp; Node</th>
                                <th style="width:100px;">Type</th>
                                <th style="width:200px;">Target Path</th>
                                <th>Details / Payload</th>
                            </tr>
                        </thead>
                        <tbody id="hp-recent-logs-body">
                            <tr><td colspan="5" class="hp-empty">—</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </section>
    </section>
</section>
@endsection

@push('pagescript')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="//unpkg.com/globe.gl"></script>
<script>
(function () {
    var siteNames = @json($site_names);
    var agentsUrl = @json(route('honeypotcenter.agents'));
    var sectionUrlTemplate = @json(route('honeypotcenter.dashboard.section', ['section' => '__SECTION__']));
    var dashboardUrl = @json(route('honeypotcenter.dashboard'));
    var geoUrlTemplate = @json(route('honeypotcenter.geoip', ['ip' => '__IP__']));

    var siteSelect = document.getElementById('hp-site-select');
    var agentSelect = document.getElementById('hp-agent-select');
    var agentFilter = document.getElementById('hp-agent-filter');
    var windowSelect = document.getElementById('hp-window-select');
    var customRange = document.getElementById('hp-custom-range-panel');
    var filterForm = document.getElementById('honeypot-filter-form');
    var dateFromInput = document.getElementById('hp-date-from');
    var dateToInput = document.getElementById('hp-date-to');
    var applyBtn = document.getElementById('hp-apply-btn');

    var hourlyChart = null;
    var trendChart = null;
    var typesChart = null;
    var currentWindowLabel = 'Today';
    var loadToken = 0;
    var tickFont = { size: 12, weight: 'normal' };
    var chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } }
    };

    function formatNumber(value) {
        return Number(value || 0).toLocaleString();
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function selectedSiteLabel() {
        if (!siteSelect || siteSelect.value === 'all' || siteSelect.value === '') {
            return 'All Sites';
        }
        var siteId = parseInt(siteSelect.value, 10);
        return siteNames[siteId] || ('Site #' + siteId);
    }

    function selectedAgentLabel() {
        if (!agentSelect || agentSelect.disabled || agentSelect.value === 'all') {
            return 'All Agents';
        }
        var option = agentSelect.options[agentSelect.selectedIndex];
        return option ? option.textContent : 'All Agents';
    }

    function syncCustomRangeVisibility(animate) {
        if (!windowSelect || !customRange) {
            return;
        }

        var isCustom = windowSelect.value === 'custom';
        if (animate && typeof window.jQuery !== 'undefined') {
            if (isCustom) {
                window.jQuery(customRange).stop(true, true).slideDown(200);
            } else {
                window.jQuery(customRange).stop(true, true).slideUp(200);
            }
            return;
        }

        customRange.style.display = isCustom ? 'block' : 'none';
    }

    function syncAgentFilterVisibility() {
        if (!siteSelect || !agentSelect) {
            return;
        }

        var isAllSites = !siteSelect.value || siteSelect.value === 'all';
        if (agentFilter) {
            agentFilter.style.display = isAllSites ? 'none' : 'inline-block';
        }

        agentSelect.disabled = isAllSites;
        if (isAllSites) {
            agentSelect.value = 'all';
            resetAgentOptions([]);
        }
    }

    function buildFilterParams() {
        var params = new URLSearchParams();
        if (siteSelect && siteSelect.value && siteSelect.value !== 'all') {
            params.set('site_id', siteSelect.value);
        }
        if (agentSelect && !agentSelect.disabled && agentSelect.value && agentSelect.value !== 'all') {
            params.set('sensor_token_id', agentSelect.value);
        }
        if (windowSelect) {
            params.set('window', windowSelect.value || 'today');
            if (windowSelect.value === 'custom') {
                if (dateFromInput && dateFromInput.value) {
                    params.set('date_from', dateFromInput.value);
                }
                if (dateToInput && dateToInput.value) {
                    params.set('date_to', dateToInput.value);
                }
            }
        }
        return params;
    }

    function sectionUrl(section) {
        var params = buildFilterParams();
        return sectionUrlTemplate.replace('__SECTION__', encodeURIComponent(section)) + '?' + params.toString();
    }

    function syncFilterUrl() {
        var params = buildFilterParams();
        var nextUrl = dashboardUrl + (params.toString() ? '?' + params.toString() : '');
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, '', nextUrl);
        }
    }

    var sectionLoaders = {
        stats: 'hp-loader-stats',
        hourly: 'hp-loader-hourly',
        'daily-trend': 'hp-loader-daily-trend',
        'threat-types': 'hp-loader-threat-types',
        globe: 'hp-loader-globe',
        'top-paths': 'hp-loader-top-paths',
        'top-attackers': 'hp-loader-top-attackers',
        'recent-logs': 'hp-loader-recent-logs'
    };

    function showSectionLoader(key) {
        var id = sectionLoaders[key];
        if (!id) {
            return;
        }
        var el = document.getElementById(id);
        if (el) {
            el.classList.add('is-active');
        }
    }

    function hideSectionLoader(key) {
        var id = sectionLoaders[key];
        if (!id) {
            return;
        }
        var el = document.getElementById(id);
        if (el) {
            el.classList.remove('is-active');
        }
    }

    function showAllSectionLoaders() {
        Object.keys(sectionLoaders).forEach(showSectionLoader);
    }

    function setStatsLoading() {
        ['hp-stat-high-risk', 'hp-stat-attackers', 'hp-stat-logs'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                el.textContent = '—';
                el.classList.add('is-loading');
            }
        });
    }

    async function fetchSection(section) {
        var response = await fetch(sectionUrl(section), { credentials: 'same-origin' });
        if (!response.ok) {
            throw new Error('Failed to load ' + section);
        }
        return response.json();
    }

    function updateRecentMeta(windowLabel) {
        var meta = document.getElementById('hp-recent-meta');
        if (!meta) {
            return;
        }
        meta.textContent = 'Site: ' + selectedSiteLabel() + ' · Agent: ' + selectedAgentLabel() + ' · Window: ' + (windowLabel || currentWindowLabel);
    }

    function setApplyLoading(isLoading) {
        if (!applyBtn) {
            return;
        }
        applyBtn.disabled = isLoading;
        applyBtn.textContent = isLoading ? 'Loading...' : 'Apply';
    }

    function resetAgentOptions(agents, keepDisabled) {
        if (!agentSelect) {
            return;
        }

        var selected = agentSelect.value;
        agentSelect.innerHTML = '';
        var allOption = document.createElement('option');
        allOption.value = 'all';
        allOption.textContent = 'All Agents';
        agentSelect.appendChild(allOption);

        (agents || []).forEach(function (agent) {
            var option = document.createElement('option');
            option.value = String(agent.id);
            option.textContent = agent.name;
            agentSelect.appendChild(option);
        });

        if (!agents || !agents.length) {
            if (!keepDisabled && siteSelect && siteSelect.value !== 'all') {
                var emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.disabled = true;
                emptyOption.textContent = 'No agents for this site';
                agentSelect.appendChild(emptyOption);
            }
        }

        agentSelect.value = 'all';
        if (selected && selected !== 'all' && agentSelect.querySelector('option[value="' + selected + '"]')) {
            agentSelect.value = selected;
        }
    }

    async function loadAgents(siteId) {
        if (!agentSelect) {
            return;
        }

        if (!siteId || siteId === 'all') {
            agentSelect.disabled = true;
            resetAgentOptions([]);
            return;
        }

        agentSelect.disabled = false;
        try {
            var response = await fetch(agentsUrl + '?site_id=' + encodeURIComponent(siteId), {
                credentials: 'same-origin'
            });
            if (!response.ok) {
                resetAgentOptions([]);
                return;
            }
            var payload = await response.json();
            resetAgentOptions(payload.agents || []);
        } catch (e) {
            resetAgentOptions([]);
        }
    }

    function renderHourlyChart(chartData) {
        var hourlyCanvas = document.getElementById('hp-hourly-chart');
        if (!hourlyCanvas) {
            return;
        }
        if (hourlyChart) {
            hourlyChart.destroy();
            hourlyChart = null;
        }
        hourlyChart = new Chart(hourlyCanvas, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Attacks',
                    data: chartData.values,
                    backgroundColor: 'rgba(59, 130, 246, 0.65)',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: Object.assign({}, chartDefaults, {
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0, color: '#777', font: tickFont }, grid: { color: 'rgba(0,0,0,.06)' } },
                    x: { ticks: { color: '#777', font: tickFont, maxRotation: 0, autoSkip: true, maxTicksLimit: 12 }, grid: { display: false } }
                }
            })
        });
    }

    function renderTrendChart(chartData) {
        var trendCanvas = document.getElementById('hp-trend-chart');
        if (!trendCanvas) {
            return;
        }
        if (trendChart) {
            trendChart.destroy();
            trendChart = null;
        }
        trendChart = new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Threat Volume',
                    data: chartData.values,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.12)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4
                }]
            },
            options: Object.assign({}, chartDefaults, {
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0, color: '#777', font: tickFont }, grid: { color: 'rgba(0,0,0,.06)' } },
                    x: { ticks: { color: '#777', font: tickFont }, grid: { display: false } }
                }
            })
        });
    }

    function renderTypesChart(chartData) {
        var typesCanvas = document.getElementById('hp-types-chart');
        if (!typesCanvas) {
            return;
        }
        if (typesChart) {
            typesChart.destroy();
            typesChart = null;
        }
        typesChart = new Chart(typesCanvas, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Alerts',
                    data: chartData.values,
                    backgroundColor: '#3b82f6',
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0, color: '#777', font: tickFont }, grid: { color: 'rgba(0,0,0,.06)' } },
                    y: { ticks: { color: '#777', font: { size: 12, weight: 'normal' } }, grid: { display: false } }
                }
            }
        });
    }

    function renderTopPaths(rows) {
        var body = document.getElementById('hp-top-paths-body');
        if (!body) return;

        if (!rows || !rows.length) {
            body.innerHTML = '<tr><td colspan="2" class="hp-empty">No suspicious paths detected</td></tr>';
            return;
        }

        body.innerHTML = rows.map(function (row) {
            return '<tr><td style="text-align:center;"><span class="hp-count">' + formatNumber(row.count) + '</span></td>' +
                '<td><span class="hp-path">' + escapeHtml(row.path) + '</span></td></tr>';
        }).join('');
    }

    function renderTopAttackers(rows) {
        var body = document.getElementById('hp-top-attackers-body');
        if (!body) return;

        if (!rows || !rows.length) {
            body.innerHTML = '<tr><td colspan="3" class="hp-empty">No attackers profiled yet</td></tr>';
            return;
        }

        body.innerHTML = rows.map(function (attacker) {
            var tags = (attacker.tags || []).map(function (tag) {
                return '<span class="hp-tag">' + escapeHtml(tag) + '</span>';
            }).join('');

            return '<tr><td><span class="hp-ip">' + escapeHtml(attacker.ip) + '</span></td>' +
                '<td class="hp-risk">' + formatNumber(attacker.risk_score) + '</td>' +
                '<td><div class="hp-tags">' + tags + '</div><div class="hp-note">' + escapeHtml(attacker.summary_note) + '</div></td></tr>';
        }).join('');
    }

    function renderRecentLogs(rows) {
        var body = document.getElementById('hp-recent-logs-body');
        if (!body) return;

        if (!rows || !rows.length) {
            body.innerHTML = '<tr><td colspan="5" class="hp-empty">No activity detected in selected range</td></tr>';
            return;
        }

        body.innerHTML = rows.map(function (log) {
            var sev = String(log.severity || 'HIGH').toLowerCase();
            var sevClass = ['critical', 'high', 'medium', 'low', 'info'].indexOf(sev) >= 0 ? sev : 'high';
            var siteId = log.site_id ? parseInt(log.site_id, 10) : null;
            var nodeLabel = siteId && siteNames[siteId] ? siteNames[siteId] : (siteId ? ('Site #' + siteId) : selectedSiteLabel());
            var sensorLabel = String(log.sensor_name || '').trim();
            var detailText = String(log.payload || '').trim();
            if (log.raw_details && Object.keys(log.raw_details).length) {
                var rawJson = JSON.stringify(log.raw_details);
                detailText = detailText && detailText !== 'No payload data' ? detailText + '\n' + rawJson : rawJson;
            }
            if (!detailText && log.summary) {
                detailText = String(log.summary);
            }

            var sensorHtml = sensorLabel ? '<div class="hp-node">Sensor: ' + escapeHtml(sensorLabel) + '</div>' : '';
            var riskHtml = log.current_risk_score ? '<div class="hp-node">Risk: ' + parseInt(log.current_risk_score, 10) + '</div>' : '';
            var detailsHtml = detailText
                ? '<div class="hp-payload-box">' + escapeHtml(detailText) + '</div>'
                : '<span class="hp-note">' + escapeHtml(String(log.threat_name || '').substring(0, 80)) + '</span>';

            return '<tr>' +
                '<td class="hp-time">' + escapeHtml(log.time_display || log.timestamp) + '</td>' +
                '<td><div class="hp-ip">' + escapeHtml(log.attacker_ip) + '</div><div class="hp-node">Site: ' + escapeHtml(nodeLabel) + '</div>' + sensorHtml + riskHtml + '</td>' +
                '<td><div class="hp-note" style="margin-bottom:4px;">' + escapeHtml(log.threat_name || '-') + '</div><span class="hp-sev hp-sev--' + sevClass + '">' + escapeHtml(log.severity || 'HIGH') + '</span></td>' +
                '<td><span class="hp-path">' + escapeHtml(log.request_path || '/') + '</span></td>' +
                '<td>' + detailsHtml + '</td></tr>';
        }).join('');
    }

    function updateWindowTags(windowLabel) {
        currentWindowLabel = windowLabel || currentWindowLabel;
        var windowTag = document.getElementById('hp-window-tag');
        var trendTag = document.getElementById('hp-trend-window-tag');
        if (windowTag) windowTag.textContent = currentWindowLabel;
        if (trendTag) trendTag.textContent = currentWindowLabel;
        updateRecentMeta(currentWindowLabel);
    }

    function renderStats(stats) {
        var high = document.getElementById('hp-stat-high-risk');
        var attackers = document.getElementById('hp-stat-attackers');
        var logs = document.getElementById('hp-stat-logs');
        if (high) {
            high.textContent = formatNumber(stats.high_risk_alerts);
            high.classList.remove('is-loading');
        }
        if (attackers) {
            attackers.textContent = formatNumber(stats.active_attackers);
            attackers.classList.remove('is-loading');
        }
        if (logs) {
            logs.textContent = formatNumber(stats.logs_processed);
            logs.classList.remove('is-loading');
        }
    }

    function loadDashboardSections() {
        var token = ++loadToken;
        setApplyLoading(true);
        setStatsLoading();
        showAllSectionLoaders();
        syncFilterUrl();

        var statusEl = document.getElementById('hp-globe-status');
        if (statusEl) {
            statusEl.textContent = 'Loading threat map...';
            statusEl.classList.remove('is-warn');
        }

        fetchSection('stats').then(function (data) {
            if (token !== loadToken) return;
            renderStats(data.stats || {});
        }).catch(function () {
            if (token !== loadToken) return;
            renderStats({ high_risk_alerts: 0, active_attackers: 0, logs_processed: 0 });
        }).finally(function () {
            if (token === loadToken) {
                hideSectionLoader('stats');
            }
        });

        fetchSection('hourly').then(function (data) {
            if (token !== loadToken) return;
            if (data.window_label) {
                updateWindowTags(data.window_label);
            }
            renderHourlyChart(data.hourly_chart || { labels: [], values: [] });
        }).catch(function () {
            if (token !== loadToken) return;
            renderHourlyChart({ labels: [], values: [] });
        }).finally(function () {
            if (token === loadToken) {
                hideSectionLoader('hourly');
            }
        });

        fetchSection('daily-trend').then(function (data) {
            if (token !== loadToken) return;
            if (data.window_label) {
                updateWindowTags(data.window_label);
            }
            renderTrendChart(data.trend_7day || { labels: [], values: [] });
        }).catch(function () {
            if (token !== loadToken) return;
            renderTrendChart({ labels: [], values: [] });
        }).finally(function () {
            if (token === loadToken) {
                hideSectionLoader('daily-trend');
            }
        });

        fetchSection('threat-types').then(function (data) {
            if (token !== loadToken) return;
            renderTypesChart(data.threat_types_chart || { labels: [], values: [] });
        }).catch(function () {
            if (token !== loadToken) return;
            renderTypesChart({ labels: [], values: [] });
        }).finally(function () {
            if (token === loadToken) {
                hideSectionLoader('threat-types');
            }
        });

        fetchSection('top-paths').then(function (data) {
            if (token !== loadToken) return;
            renderTopPaths(data.top_paths || []);
        }).catch(function () {
            if (token !== loadToken) return;
            renderTopPaths([]);
        }).finally(function () {
            if (token === loadToken) {
                hideSectionLoader('top-paths');
            }
        });

        fetchSection('top-attackers').then(function (data) {
            if (token !== loadToken) return;
            var rows = data.top_attackers || [];
            renderTopAttackers(rows);
            loadGlobePoints(rows, token);
        }).catch(function () {
            if (token !== loadToken) return;
            renderTopAttackers([]);
            loadGlobePoints([], token);
        }).finally(function () {
            if (token === loadToken) {
                hideSectionLoader('top-attackers');
            }
        });

        fetchSection('recent-logs').then(function (data) {
            if (token !== loadToken) return;
            if (data.window_label) {
                updateWindowTags(data.window_label);
            }
            renderRecentLogs(data.recent_logs || []);
        }).catch(function () {
            if (token !== loadToken) return;
            renderRecentLogs([]);
        }).finally(function () {
            if (token === loadToken) {
                hideSectionLoader('recent-logs');
                setApplyLoading(false);
            }
        });
    }

    function geoUrl(ip) {
        return geoUrlTemplate.replace('__IP__', encodeURIComponent(ip));
    }

    async function loadGlobePoints(attackers, requestToken) {
        var statusEl = document.getElementById('hp-globe-status');
        var container = document.getElementById('hp-globe-viz');

        function setStatus(text, warn) {
            if (!statusEl) return;
            statusEl.textContent = text;
            statusEl.classList.toggle('is-warn', !!warn);
        }

        if (!container || typeof Globe === 'undefined') {
            setStatus('Globe library failed to load', true);
            hideSectionLoader('globe');
            return;
        }

        var points = [];
        if (attackers && attackers.length) {
            setStatus('Resolving IP locations...');
            var results = await Promise.all(attackers.map(async function (attacker) {
                try {
                    var res = await fetch(geoUrl(attacker.ip), { credentials: 'same-origin' });
                    if (!res.ok) return null;
                    var geo = await res.json();
                    if (geo.private || geo.lat == null || geo.lon == null) return null;
                    return {
                        ip: attacker.ip,
                        lat: geo.lat,
                        lng: geo.lon,
                        country: geo.country,
                        size: Math.min(0.15 + (attacker.count * 0.04), 0.9),
                        color: attacker.risk_score >= 100 ? '#ef4444' : '#f97316'
                    };
                } catch (e) {
                    return null;
                }
            }));
            points = results.filter(Boolean);
        }

        if (requestToken !== undefined && requestToken !== loadToken) {
            return;
        }

        container.innerHTML = '';
        var w = container.clientWidth || container.offsetWidth;
        var h = container.clientHeight || 280;
        var globe = Globe()(container)
            .globeImageUrl('https://unpkg.com/three-globe/example/img/earth-night.jpg')
            .backgroundColor('#020617')
            .width(w)
            .height(h)
            .pointsData(points)
            .pointLat(function (d) { return d.lat; })
            .pointLng(function (d) { return d.lng; })
            .pointColor(function (d) { return d.color; })
            .pointAltitude(0.06)
            .pointRadius(function (d) { return d.size; })
            .labelsData(points)
            .labelLat(function (d) { return d.lat; })
            .labelLng(function (d) { return d.lng; })
            .labelText(function (d) { return d.ip + (d.country ? ' · ' + d.country : ''); })
            .labelSize(1.2)
            .labelColor(function () { return 'rgba(226, 232, 240, 0.9)'; })
            .labelDotRadius(0.4)
            .labelResolution(2);

        globe.controls().autoRotate = true;
        globe.controls().autoRotateSpeed = 0.35;
        globe.pointOfView({ lat: 15, lng: 100, altitude: 2.6 });

        if (!attackers || !attackers.length) {
            setStatus('No attacker IPs in selected window');
        } else if (points.length) {
            setStatus(points.length + ' public threat source(s) mapped · ' + attackers.length + ' total IP(s)');
        } else {
            setStatus('No public geo data — attacker IPs are private/local (e.g. 192.168.x)', true);
        }

        if (requestToken === undefined || requestToken === loadToken) {
            hideSectionLoader('globe');
        }
    }

    if (filterForm) {
        filterForm.addEventListener('submit', function (event) {
            event.preventDefault();
            loadDashboardSections();
        });
    }

    if (windowSelect) {
        windowSelect.addEventListener('change', function () {
            syncCustomRangeVisibility(true);
        });
    }

    if (siteSelect && agentSelect) {
        siteSelect.addEventListener('change', async function () {
            agentSelect.value = 'all';
            syncAgentFilterVisibility();

            if (this.value && this.value !== 'all') {
                await loadAgents(this.value);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        syncAgentFilterVisibility();
        syncCustomRangeVisibility(false);
        loadDashboardSections();
        window.requestAnimationFrame(function () {
            [hourlyChart, trendChart, typesChart].forEach(function (chart) {
                if (chart) {
                    chart.resize();
                }
            });
        });
    });
})();
</script>
@endpush
