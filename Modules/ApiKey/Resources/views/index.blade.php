@extends('layouts.app')

@section('content')

<section id="content" class="bg">

    <section class="vbox">

        <header class="header panel-heading bg-white b-b b-light bar-header-overflow">

            <div class="header-flex-overflow m-t-10">

                <div class="fwb-16">

                    <span>@langapp('settings') > @langapp('api_key')</span>

                </div>

                <div class="ml-2 text-right">

                    <div class="max-w-select d-inline-block m-r-xs" id="site-filter-wrap">

                        <select name="site" id="site" class="select2-option form-control select-site">

                            <option value="">All Site</option>

                            @if(!empty($SiteSettings))

                                @foreach($SiteSettings as $siteItem)

                                    <option value="{{ $siteItem->code }}" data-site-id="{{ $siteItem->id }}">{{ $siteItem->name }}</option>

                                @endforeach

                            @endif

                        </select>

                    </div>

                    <a href="#hide-advance-search" id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color') }}">

                        <span data-rel="tooltip" title="Filter" data-placement="bottom"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></span>

                    </a>

                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color') }} m-l-xs" id="btn-add-api-key">

                        @icon('solid/plus') @langapp('create')

                    </a>

                </div>

            </div>

        </header>



        <section class="scrollable wrapper">

            <ul class="nav nav-tabs m-b-sm" id="api-key-scope-tabs">

                <li class="active">

                    <a href="#" data-scope="system"><i class="fas fa-server m-r-xs"></i> System Keys</a>

                </li>

                <li>

                    <a href="#" data-scope="site"><i class="fas fa-building m-r-xs"></i> Site API Key</a>

                </li>

            </ul>

            <section class="panel panel-default" id="hide-advance-search" style="display: none;">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-md-12">
                            <i class="fas fa-filter"></i> Filter
                        </div>
                    </div>
                </header>
                <div class="panel-body" style="padding: 0 !important">
                    <div class="container-fluid" style="padding: 2rem;">
                        <div class="row m-b-md">
                            <div class="col-lg-4 col-md-12">
                                <h5 class="font-weight-bold">Name</h5>
                                <input type="text" class="form-control" id="filter_name" placeholder="Search by name">
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <h5 class="font-weight-bold">Url</h5>
                                <input type="text" class="form-control" id="filter_url" placeholder="Search by url">
                            </div>
                            <div class="col-lg-4 col-md-12">
                                <h5 class="font-weight-bold">Expire Status</h5>
                                <select id="filter_expire_status" class="select2-option form-control">
                                    <option value="">All</option>
                                    <option value="active">Not Expired</option>
                                    <option value="expired">Expired</option>
                                    <option value="no_expiration">No Expiration</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-lg-12 text-right">
                            <button type="button" id="btn-apply-filter" class="btn btn-info btn-responsive btn-fz-13">
                                <i class="fas fa-search"></i>
                                @langapp('apply')
                            </button>
                            <button type="button" id="btn-clear-filter" class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                <i class="fas fa-broom"></i>
                                <span> Clear </span>
                            </button>
                            <button type="button" id="close_filter" class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                <i class="fas fa-times"></i>
                                <span> Close </span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel panel-default">

                <header class="panel-heading font-bold panel-header-blue">

                    <div class="row">

                        <div class="col-xs-12">

                            <i class="fas fa-table"></i> <span id="api-key-table-title">System Feed Keys</span>

                        </div>

                    </div>

                </header>

                <div class="panel-body">

                    <div class="alert alert-info m-b-sm" id="api-key-scope-hint">

                        Keys for threat intel feeds (VirusTotal, OTX, AbuseIPDB, etc.) shared across all sites.

                    </div>

                    <div class="table-responsive">

                        <table class="table table-striped table-bordered" id="table-api-key">

                            <thead>

                                <tr>

                                    <th id="th-site-col">Scope</th>

                                    <th>Name</th>

                                    <th>Type</th>

                                    <th>Url</th>

                                    <th>Key</th>

                                    <th>Last Update</th>

                                    <th class="text-center no-wrap">Action</th>

                                </tr>

                            </thead>

                            <tbody></tbody>

                        </table>

                    </div>

                </div>

            </section>

        </section>

    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>



    <div class="modal in fixed-left" id="api-key-modal" tabindex="-1" role="dialog" aria-hidden="true">

        <div class="modal-dialog modal-dialog-aside modal-api-key-wide" role="document">

            <div class="modal-content">

                <div class="modal-header bg-blue">

                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>

                    <h4 class="modal-title text-white" id="api-key-modal-title">

                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" data-rel="tooltip" title="Fullscreen" data-placement="right"></i>

                        Add

                    </h4>

                </div>

                <form id="form-api-key">

                    <div class="modal-body">

                        <input type="hidden" id="api_key_type" value="">

                        <input type="hidden" id="api_key_site_id" value="">

                        <input type="hidden" id="api_key_scope" value="system">



                        <div class="form-group row" id="api-key-site-row">

                            <label class="col-lg-3 control-label">Site <span class="text-danger">*</span></label>

                            <div class="col-lg-9">

                                <select class="form-control" id="api_key_site_select">

                                    <option value="">Select Site</option>

                                    @if(!empty($SiteSettings))

                                        @foreach($SiteSettings as $siteItem)

                                            <option value="{{ $siteItem->id }}">{{ $siteItem->name }}</option>

                                        @endforeach

                                    @endif

                                </select>

                            </div>

                        </div>



                        <div class="form-group row" id="api-key-system-provider-row">

                            <label class="col-lg-3 control-label">Provider <span class="text-danger">*</span></label>

                            <div class="col-lg-9">

                                <select class="form-control" id="api_key_provider_select">

                                    <option value="">Select Provider</option>

                                    @if(!empty($systemProviders))

                                        @foreach($systemProviders as $providerKey => $providerLabel)

                                            <option value="{{ $providerKey }}">{{ $providerLabel }}</option>

                                        @endforeach

                                    @endif

                                    <option value="_custom">Other (Custom)</option>

                                </select>

                                <input type="text" class="form-control m-t-sm" id="api_key_name_custom" placeholder="Custom provider key (e.g. shodan)" style="display:none;">

                            </div>

                        </div>



                        <div class="form-group row" id="api-key-site-name-row">

                            <label class="col-lg-3 control-label">Name <span class="text-danger">*</span></label>

                            <div class="col-lg-9">

                                <input type="text" class="form-control" id="api_key_name" placeholder="Provider name">

                            </div>

                        </div>



                        <div class="form-group row">

                            <label class="col-lg-3 control-label">Url</label>

                            <div class="col-lg-9">

                                <input type="text" class="form-control" name="url" id="api_key_url" placeholder="https://">

                            </div>

                        </div>

                        <div class="form-group row">

                            <label class="col-lg-3 control-label"></label>

                            <div class="col-lg-9">

                                <button type="button" class="btn btn-sm btn-{{ get_option('theme_color') }}" id="btn-add-key-row">

                                    <i class="fas fa-plus"></i> Add Key

                                </button>

                            </div>

                        </div>

                        <div class="panel-group api-key-accordion" id="api-key-rows"></div>

                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">

                            <i class="fas fa-times"></i> Close

                        </button>

                        <button type="submit" class="btn btn-info btn-rounded formSaving">

                            <i class="fas fa-paper-plane"></i> Save

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <div class="modal fade api-key-delete-modal-wrap" id="api-key-delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog api-key-delete-dialog" role="document">
            <div class="modal-content api-key-delete-content">
                <div class="modal-header bg-danger">
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-trash-alt m-r-xs"></i> @langapp('delete') API Key Provider
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="api-key-delete-icon-wrap text-center">
                        <span class="api-key-delete-icon-circle">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                    </div>
                    <p class="text-center text-muted api-key-delete-lead m-b-md">
                        Are you sure you want to delete this provider?
                    </p>
                    <div class="api-key-delete-summary">
                        <div class="api-key-delete-summary-row">
                            <span class="api-key-delete-label">Provider</span>
                            <span class="api-key-delete-value" id="api-key-delete-name">-</span>
                        </div>
                        <div class="api-key-delete-summary-row">
                            <span class="api-key-delete-label">Scope</span>
                            <span class="api-key-delete-value" id="api-key-delete-scope">-</span>
                        </div>
                        <div class="api-key-delete-summary-row" id="api-key-delete-site-row">
                            <span class="api-key-delete-label">Site</span>
                            <span class="api-key-delete-value" id="api-key-delete-site">-</span>
                        </div>
                        <div class="api-key-delete-summary-row">
                            <span class="api-key-delete-label">API Keys</span>
                            <span class="api-key-delete-value" id="api-key-delete-key-count">-</span>
                        </div>
                    </div>
                    <p class="api-key-delete-warning text-danger text-center m-b-none">
                        <i class="fas fa-info-circle m-r-xs"></i>
                        @langapp('delete_warning') All keys under this provider will be permanently removed.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times text-muted"></i> Close
                    </button>
                    <button type="button" class="btn btn-danger btn-rounded" id="btn-confirm-delete-api-key">
                        <i class="fas fa-trash-alt"></i> @langapp('delete')
                    </button>
                </div>
            </div>
        </div>
    </div>

</section>



@push('pagestyle')

@include('stacks.css.datatables')

@include('stacks.css.form')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css"/>

<style>

    #api-key-modal .modal-api-key-wide { width: 760px; max-width: 92vw; }

    #api-key-scope-tabs > li > a { font-weight: 600; }

    .api-key-list { width: 100%; }
    .api-key-list div + div { margin-top: 4px; }

    #table-api-key td:nth-child(5) {
        min-width: 200px;
        max-width: 420px;
        word-break: break-all;
    }

    .api-key-token-value {
        display: block;
        width: 100%;
        font-size: 12px;
        background: #f5f5f5;
        padding: 2px 6px;
        border-radius: 3px;
        font-weight: 700;
        word-break: break-all;
        white-space: normal;
    }
    .api-key-token-value.api-key-token-masked {
        cursor: pointer;
    }
    .api-key-token-value.api-key-token-masked:hover {
        background: #ebebeb;
    }
    .api-key-token-active {
        color: #2c6e9e;
    }
    .api-key-token-expired {
        color: #c9302c;
    }

    .api-key-accordion .panel { margin-bottom: 8px; }

    .api-key-accordion .panel-heading { padding: 8px 12px; background: #f7f7f7; }

    .api-key-accordion .panel-title {

        display: flex;

        align-items: center;

        justify-content: space-between;

        font-size: 13px;

        margin: 0;

    }

    .api-key-accordion .panel-title > a {

        flex: 1;

        text-decoration: none;

        color: #333;

    }

    .api-key-accordion .panel-title > a:hover,

    .api-key-accordion .panel-title > a:focus {

        text-decoration: none;

        color: #1a7bb9;

    }

    .api-key-accordion .key-summary {

        color: #888;

        font-weight: normal;

        margin-left: 8px;

        font-size: 12px;

    }

    .api-key-accordion .panel-body { padding-top: 10px; }
    .api-key-value-locked[readonly] {
        background-color: #f5f5f5;
        cursor: default;
    }
    #api-key-modal .api-key-input-group {
        display: flex;
        align-items: stretch;
        width: 100%;
    }
    #api-key-modal .api-key-input-group .api-key-value {
        flex: 1 1 auto;
        min-width: 0;
        margin: 0 !important;
        padding: 6px 12px;
        font-size: 14px;
        line-height: 20px;
        height: auto !important;
        min-height: 34px;
        box-sizing: border-box;
        border-right: 0;
        border-radius: 4px 0 0 4px;
        box-shadow: none;
    }
    #api-key-modal .api-key-input-group .btn-unlock-key {
        flex: 0 0 38px;
        width: 38px;
        min-width: 38px;
        margin: 0 !important;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        align-self: stretch;
        box-sizing: border-box;
        border-left: 0;
        border-radius: 0 4px 4px 0;
        line-height: 1;
    }
    #api-key-modal .api-key-input-group .btn-unlock-key i {
        line-height: 1;
        font-size: 14px;
    }

    #api-key-delete-modal.in,
    #api-key-delete-modal.show {
        display: flex !important;
        align-items: center;
        justify-content: center;
        padding: 20px !important;
    }

    #api-key-delete-modal.in .modal-dialog,
    #api-key-delete-modal.show .modal-dialog {
        margin: 0 auto !important;
        transform: none !important;
        -webkit-transform: none !important;
        width: 460px !important;
        max-width: 100% !important;
        position: relative;
        top: auto;
        left: auto;
        z-index: 1;
        pointer-events: auto;
    }

    .api-key-delete-dialog {
        width: 460px;
        max-width: 92vw;
    }

    .api-key-delete-content {
        border: 0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.18);
    }

    .api-key-delete-content .modal-header {
        border-bottom: 0;
        padding: 16px 20px;
    }

    .api-key-delete-content .modal-body {
        padding: 22px 24px 18px;
    }

    .api-key-delete-content .modal-footer {
        border-top: 1px solid #f0f0f0;
        padding: 14px 20px;
        background: #fafafa;
    }

    .api-key-delete-icon-wrap {
        margin-bottom: 14px;
    }

    .api-key-delete-icon-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: #fff5f5;
        color: #e74c3c;
        font-size: 28px;
        box-shadow: inset 0 0 0 1px #fde2e2;
    }

    .api-key-delete-lead {
        font-size: 15px;
        margin-top: 0;
    }

    .api-key-delete-summary {
        background: #f8fafc;
        border: 1px solid #e8edf3;
        border-radius: 8px;
        padding: 12px 14px;
        margin-bottom: 14px;
    }

    .api-key-delete-summary-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 7px 0;
    }

    .api-key-delete-summary-row + .api-key-delete-summary-row {
        border-top: 1px dashed #e5ebf1;
    }

    .api-key-delete-label {
        color: #7a869a;
        font-size: 13px;
        min-width: 72px;
    }

    .api-key-delete-value {
        color: #2f3b52;
        font-weight: 600;
        font-size: 13px;
        text-align: right;
        word-break: break-word;
    }

    .api-key-delete-warning {
        font-size: 12px;
        line-height: 1.5;
    }

</style>

@endpush



@push('pagescript')

@include('stacks.js.datatables')

@include('stacks.js.form')
@include('stacks.js.daterangpicker')

@include('stacks.js.fullscreen')
@include('stacks.js.advanced_search')

<script>

    var apiKeyTable;

    var keyRowIndex = 0;

    var id_select_site = 'site';

    var currentScope = 'system';



    function escAttr(value) {

        return String(value || '')

            .replace(/&/g, '&amp;')

            .replace(/"/g, '&quot;')

            .replace(/</g, '&lt;')

            .replace(/>/g, '&gt;');

    }



    function maskTokenPreview(value) {

        value = String(value || '');

        if (!value) return '-';

        if (value.length <= 5) return value;

        return '*'.repeat(value.length - 5) + value.substring(value.length - 5);

    }



    function getCurrentScope() {

        return currentScope;

    }



    function getSelectedSiteId() {

        var selected = $('#site').find(':selected');

        return selected.data('site-id') || '';

    }



    function getSelectedSiteCode() {

        return $('#site').val() || '';

    }



    function getProviderNameValue() {

        if (getCurrentScope() === 'system') {

            var provider = $('#api_key_provider_select').val();

            if (provider === '_custom') {

                return $('#api_key_name_custom').val();

            }

            return provider;

        }

        return $('#api_key_name').val();

    }



    function setProviderNameValue(value) {

        value = value || '';

        if (getCurrentScope() === 'system') {

            if ($('#api_key_provider_select option[value="' + value + '"]').length) {

                $('#api_key_provider_select').val(value);

                $('#api_key_name_custom').hide().val('');

            } else if (value) {

                $('#api_key_provider_select').val('_custom');

                $('#api_key_name_custom').show().val(value);

            } else {

                $('#api_key_provider_select').val('');

                $('#api_key_name_custom').hide().val('');

            }

        } else {

            $('#api_key_name').val(value);

        }

    }



    function applyScopeUi(scope) {

        currentScope = scope;

        $('#api_key_scope').val(scope);



        $('#api-key-scope-tabs li').removeClass('active');

        $('#api-key-scope-tabs a[data-scope="' + scope + '"]').parent().addClass('active');



        if (scope === 'system') {

            $('#site-filter-wrap').hide();

            $('#api-key-table-title').text('System Feed Keys');

            $('#th-site-col').text('Scope');

            $('#api-key-scope-hint').show().text('Keys for threat intel feeds (VirusTotal, OTX, AbuseIPDB, etc.) shared across all sites.');

            $('#api-key-site-row').hide();

            $('#api-key-system-provider-row').show();

            $('#api-key-site-name-row').hide();

        } else {

            $('#site-filter-wrap').show();

            $('#api-key-table-title').text('Site API Keys');

            $('#th-site-col').text('Site');

            $('#api-key-scope-hint').show().text('API keys specific to each site (e.g. IoC Feed, Service Receive API).');

            $('#api-key-site-row').show();

            $('#api-key-system-provider-row').hide();

            $('#api-key-site-name-row').show();

        }

    }



    function parseExpiresMoment(value) {
        if (!value) {
            return moment();
        }

        var parsed = moment(value, ['DD-MM-YYYY HH:mm:ss', 'D-M-YYYY H:i:s', 'YYYY-MM-DD HH:mm:ss'], true);

        return parsed.isValid() ? parsed : moment();
    }

    function buildExpiresPickerInput(value) {
        return ''
            + '<div class="api-key-expires-range text-center" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ddd; display:block;margin-bottom:0;">'
            + '  <i class="fa fa-calendar"></i>&nbsp;'
            + '  <span></span> <i class="fa fa-caret-down"></i>'
            + '</div>'
            + '<input type="hidden" class="api-key-expires" value="' + escAttr(value) + '">';
    }

    function updateExpiresRangeLabel($range, date, hasValue) {
        if (hasValue) {
            $range.find('span').html(date.format('MMMM D, YYYY hh:mm A'));
        } else {
            $range.find('span').html('Select expire date');
        }
    }

    function destroyExpiresDatePickers($container) {
        ($container || $('#api-key-rows')).find('.api-key-expires-range').each(function () {
            var drp = $(this).data('daterangepicker');
            if (drp) {
                drp.remove();
            }
        });
    }

    function initExpiresDatePickers($container) {
        $container = $container || $('#api-key-modal');

        $container.find('.api-key-expires-range:visible').each(function () {
            var $range = $(this);

            if ($range.data('daterangepicker')) {
                return;
            }

            var $hidden = $range.siblings('.api-key-expires').first();
            var hasValue = !!$hidden.val();
            var start = parseExpiresMoment($hidden.val());

            function cb(date) {
                updateExpiresRangeLabel($range, date, hasValue);
            }

            $range.daterangepicker({
                singleDatePicker: true,
                timePicker: true,
                showDropdowns: true,
                startDate: start,
                locale: {
                    format: 'M/DD hh:mm A'
                }
            }, cb);

            $range.on('apply.daterangepicker', function (ev, picker) {
                $hidden.val(picker.startDate.format('DD-MM-YYYY HH:mm:ss'));
                updateExpiresRangeLabel($range, picker.startDate, true);
            });

            cb(start);
        });
    }

    function syncExpiresFieldUi($row) {
        var checked = $row.find('.api-key-has-expires').is(':checked');
        var $wrap = $row.find('.api-key-expires-picker-wrap');

        if (checked) {
            $wrap.show();
            initExpiresDatePickers($row);
        } else {
            destroyExpiresDatePickers($row);
            $wrap.hide();
            $row.find('.api-key-expires').val('');
            $row.find('.api-key-expires-range span').html('Select expire date');
        }
    }

    function buildExpiresFieldHtml(item) {
        item = item || {};

        if (getCurrentScope() !== 'system') {
            return ''
                + '      <div class="form-group row">'
                + '        <label class="col-lg-3 control-label">Expires</label>'
                + '        <div class="col-lg-9">' + buildExpiresPickerInput(item.expires_at) + '</div>'
                + '      </div>';
        }

        var hasExpires = !!(item.expires_at);
        var pickerStyle = hasExpires ? '' : ' style="display:none;"';

        return ''
            + '      <div class="form-group row api-key-expires-row">'
            + '        <label class="col-lg-3 control-label">Expires</label>'
            + '        <div class="col-lg-9">'
            + '          <div class="checkbox m-b-sm">'
            + '            <label style="padding-left: 0;">'
            + '              <input type="checkbox" class="api-key-has-expires"' + (hasExpires ? ' checked' : '') + '>'
            + '              <span class="label-text">Set an expire date</span>'
            + '            </label>'
            + '          </div>'
            + '          <div class="api-key-expires-picker-wrap"' + pickerStyle + '>'
            +              buildExpiresPickerInput(item.expires_at)
            + '          </div>'
            + '        </div>'
            + '      </div>';
    }

    function getKeyExpiresValue(row) {
        if (getCurrentScope() === 'system') {
            if (!row.find('.api-key-has-expires').is(':checked')) {
                return '';
            }
        }

        return row.find('.api-key-expires').val();
    }

    function buildWhitelistFieldHtml(item) {
        if (getCurrentScope() === 'system') {
            return '';
        }

        item = item || {};

        return ''
            + '      <div class="form-group row">'
            + '        <label class="col-lg-3 control-label">Whitelist IP</label>'
            + '        <div class="col-lg-9"><textarea class="form-control api-key-whitelist" rows="2" placeholder="1.2.3.4, 5.6.7.8">' + escAttr(item.whitelist_ips) + '</textarea></div>'
            + '      </div>';
    }

    function getKeyWhitelistValue(row) {
        if (getCurrentScope() === 'system') {
            return '';
        }

        return row.find('.api-key-whitelist').val();
    }

    function refreshKeyRowNumbers() {
        $('#api-key-rows .api-key-row').each(function (index) {
            var $row = $(this);
            var num = index + 1;

            $row.attr('data-row', num);

            if (!$row.find('.api-key-name').val()) {
                $row.find('.api-key-title-text').text('Key #' + num);
            }
        });
    }

    function buildKeyRow(item, expand) {

        item = item || {};

        keyRowIndex++;

        var uid = keyRowIndex;
        var displayNum = $('#api-key-rows .api-key-row').length + 1;
        var title = item.name || ('Key #' + displayNum);

        var collapseId = 'api-key-collapse-' + uid;

        var expanded = expand ? 'in' : '';

        var collapsedClass = expand ? '' : 'collapsed';

        var ariaExpanded = expand ? 'true' : 'false';

        var metaHtml = '';



        if (item.id) {

            metaHtml = ''

                + '  <div class="form-group row">'

                + '    <label class="col-lg-3 control-label">Last Used</label>'

                + '    <div class="col-lg-9"><input type="text" class="form-control" value="' + escAttr(item.last_used_at) + '" readonly></div>'

                + '  </div>'

                + '  <div class="form-group row">'

                + '    <label class="col-lg-3 control-label">Last IP</label>'

                + '    <div class="col-lg-9"><input type="text" class="form-control" value="' + escAttr(item.last_ip) + '" readonly></div>'

                + '  </div>'

                + '  <div class="form-group row">'

                + '    <label class="col-lg-3 control-label">Created</label>'

                + '    <div class="col-lg-9"><input type="text" class="form-control" value="' + escAttr(item.created_at) + '" readonly></div>'

                + '  </div>';

        }

        var hasKeyValue = !!(item.key_value);
        var keyFieldHtml = hasKeyValue
            ? ''
                + '        <div class="api-key-input-group">'
                + '          <input type="text" class="form-control api-key-value api-key-value-locked" value="' + escAttr(item.key_value) + '" placeholder="API key" readonly data-locked="1">'
                + '          <button type="button" class="btn btn-default btn-unlock-key" title="Click to edit key"><i class="fas fa-lock"></i></button>'
                + '        </div>'
            : '<input type="text" class="form-control api-key-value" value="" placeholder="API key">';



        return ''

            + '<div class="panel panel-default api-key-row" data-row="' + displayNum + '">'

            + '  <div class="panel-heading">'

            + '    <h4 class="panel-title">'

            + '      <a class="' + collapsedClass + '" data-toggle="collapse" data-parent="#api-key-rows" href="#' + collapseId + '" aria-expanded="' + ariaExpanded + '">'

            + '        <span class="api-key-title-text">' + escAttr(title) + '</span>'

            + '        <span class="key-summary api-key-preview-text">' + escAttr(maskTokenPreview(item.key_value)) + '</span>'

            + '      </a>'

            + '      <button type="button" class="btn btn-link text-danger btn-remove-key-row"><i class="fas fa-times"></i></button>'

            + '    </h4>'

            + '  </div>'

            + '  <div id="' + collapseId + '" class="panel-collapse collapse ' + expanded + '">'

            + '    <div class="panel-body">'

            + '      <input type="hidden" class="api-key-id" value="' + escAttr(item.id) + '">'

            + '      <div class="form-group row">'

            + '        <label class="col-lg-3 control-label">Key Name</label>'

            + '        <div class="col-lg-9"><input type="text" class="form-control api-key-name" value="' + escAttr(item.name) + '" placeholder="Key Name"></div>'

            + '      </div>'

            + '      <div class="form-group row">'

            + '        <label class="col-lg-3 control-label">Key</label>'

            + '        <div class="col-lg-9">' + keyFieldHtml + '</div>'

            + '      </div>'

            + buildExpiresFieldHtml(item)

            + buildWhitelistFieldHtml(item)

            + metaHtml

            + '    </div>'

            + '  </div>'

            + '</div>';

    }



    function resetKeyRows(keys) {

        destroyExpiresDatePickers();

        $('#api-key-rows').empty();

        keyRowIndex = 0;

        if (keys && keys.length) {

            keys.forEach(function (item, index) {

                $('#api-key-rows').append(buildKeyRow(item, index === 0));

            });

        } else {

            $('#api-key-rows').append(buildKeyRow({}, true));

        }

        initExpiresDatePickers();

    }



    function setModalSite(mode, data) {

        var scope = data && data.scope ? data.scope : getCurrentScope();



        if (scope === 'system') {

            $('#api_key_site_id').val('');

            $('#api_key_site_select').val('').prop('disabled', true);

            return;

        }



        if (mode === 'edit') {

            $('#api_key_site_id').val(data && data.site_id ? data.site_id : '');

            $('#api_key_site_select').val(data && data.site_id ? data.site_id : '').prop('disabled', true);

        } else {

            var siteId = getSelectedSiteId();

            $('#api_key_site_id').val(siteId);

            $('#api_key_site_select').val(siteId || '').prop('disabled', false);

        }

    }



    function openApiKeyModal(mode, data) {

        var scope = data && data.scope ? data.scope : getCurrentScope();

        applyScopeUi(scope);



        $('#api_key_type').val(data && data.type ? data.type : '');

        setProviderNameValue(data && data.name ? data.name : '');

        $('#api_key_url').val(data && data.url ? data.url : '');

        setModalSite(mode, data || { scope: scope });



        if (mode === 'edit') {

            $('#api_key_provider_select').prop('disabled', true);

            $('#api_key_name_custom').prop('readonly', true);

            $('#api_key_name').prop('readonly', true);

        } else {

            $('#api_key_provider_select').prop('disabled', false);

            $('#api_key_name_custom').prop('readonly', false);

            $('#api_key_name').prop('readonly', false);

        }



        $('#api-key-modal-title').html(

            '<i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" data-rel="tooltip" title="Fullscreen" data-placement="right"></i> '

            + (mode === 'edit' ? 'Edit' : 'Add')

            + (scope === 'system' ? ' System Feed' : ' Site Key')

        );

        resetKeyRows(data && data.keys ? data.keys : []);

        $('#api-key-modal').modal('show');

    }



    function reloadApiKeyTable() {

        apiKeyTable.ajax.reload(null, false);

    }



    function providerUrl(type, siteId, scope) {

        var url = '{{ url('/apikey/provider') }}/' + encodeURIComponent(type);

        var params = ['scope=' + encodeURIComponent(scope || getCurrentScope())];

        if ((scope || getCurrentScope()) === 'site' && siteId !== '' && siteId !== null && typeof siteId !== 'undefined') {

            params.push('site_id=' + encodeURIComponent(siteId));

        }

        return url + '?' + params.join('&');

    }



    $(function () {

        applyScopeUi('system');

        $('#api-key-modal').on('shown.bs.modal', function () {
            initExpiresDatePickers();
        });

        if (typeof get_cookie_site === 'function' && get_cookie_site()) {

            cookie_change_site("{{ route('systemsetting.check_cookie_site') }}", id_select_site);

        }



        apiKeyTable = $('#table-api-key').DataTable({

            processing: false,

            serverSide: false,

            destroy: true,

            pageLength: 25,

            dom: '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f>>rt<"bottom"ip><"clear">',

            ajax: {

                url: '{{ route('apikey.data') }}',

                type: 'POST',

                data: function (d) {

                    d.filter_name = $('#filter_name').val();

                    d.filter_url = $('#filter_url').val();

                    d.filter_expire_status = $('#filter_expire_status').val();

                    d.sitecode = getSelectedSiteCode();

                    d.scope = getCurrentScope();

                },

                dataSrc: 'data',

                beforeSend: function () {

                    loading('load');

                },

                complete: function () {

                    loading('stop_load');

                }

            },

            order: [[5, 'desc']],

            columns: [

                { data: 'site_name', name: 'site_name' },

                { data: 'name', name: 'name' },

                { data: 'provider_type', name: 'provider_type' },

                { data: 'url', name: 'url' },

                { data: 'keys_display', name: 'keys_display', orderable: false, searchable: false },

                { data: 'last_update', name: 'updated_at' },

                { data: 'action', orderable: false, searchable: false, className: 'text-center no-wrap' }

            ]

        });



        $(document).on('click', '.api-key-token-value.api-key-token-masked', function () {

            var el = $(this);

            var full = el.attr('data-full') || '';

            if (el.hasClass('api-key-token-revealed')) {

                el.removeClass('api-key-token-revealed').text(maskTokenPreview(full)).attr('title', 'Click to reveal');

                return;

            }

            el.addClass('api-key-token-revealed').text(full).attr('title', 'Click to hide');

        });



        $('#api-key-scope-tabs a').on('click', function (e) {

            e.preventDefault();

            applyScopeUi($(this).data('scope'));

            reloadApiKeyTable();

        });



        $('#site').on('change', function () {

            if (typeof set_cookie_site === 'function') {

                set_cookie_site($(this).val());

            }

            reloadApiKeyTable();

        });



        $('#api_key_provider_select').on('change', function () {

            if ($(this).val() === '_custom') {

                $('#api_key_name_custom').show().focus();

            } else {

                $('#api_key_name_custom').hide().val('');

            }

        });



        $('#btn-add-api-key').on('click', function (e) {

            e.preventDefault();

            openApiKeyModal('add', { scope: getCurrentScope() });

        });



        $('#btn-add-key-row').on('click', function () {

            $('#api-key-rows .panel-collapse.in').collapse('hide');

            $('#api-key-rows').append(buildKeyRow({}, true));
            initExpiresDatePickers($('#api-key-rows .api-key-row').last());
        });



        $(document).on('change', '.api-key-has-expires', function () {
            var $row = $(this).closest('.api-key-expires-row');
            syncExpiresFieldUi($row);
        });

        $(document).on('click', '.btn-unlock-key', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var btn = $(this);
            var input = btn.closest('.api-key-input-group').find('.api-key-value');
            var icon = btn.find('i');

            if (input.prop('readonly')) {
                input.prop('readonly', false).removeClass('api-key-value-locked').focus().select();
                icon.removeClass('fa-lock').addClass('fa-lock-open');
                btn.attr('title', 'Click to lock key');
            } else {
                input.prop('readonly', true).addClass('api-key-value-locked').blur();
                icon.removeClass('fa-lock-open').addClass('fa-lock');
                btn.attr('title', 'Click to edit key');
            }
        });

        $(document).on('input', '.api-key-name, .api-key-value', function () {

            var row = $(this).closest('.api-key-row');

            var name = row.find('.api-key-name').val() || ('Key #' + ($('#api-key-rows .api-key-row').index(row) + 1));

            var preview = maskTokenPreview(row.find('.api-key-value').val());

            row.find('.api-key-title-text').text(name);

            row.find('.api-key-preview-text').text(preview);

        });



        $(document).on('click', '.btn-remove-key-row', function (e) {

            e.preventDefault();

            e.stopPropagation();

            var rows = $('#api-key-rows .api-key-row');

            if (rows.length <= 1) {

                rows.find('input:not([readonly])').val('');

                rows.find('textarea').val('');

                rows.find('.api-key-value').val('').prop('readonly', false).removeClass('api-key-value-locked');

                rows.find('.btn-unlock-key i').removeClass('fa-lock-open').addClass('fa-lock');

                rows.find('.api-key-has-expires').prop('checked', false);

                syncExpiresFieldUi(rows.find('.api-key-expires-row'));

                rows.find('.api-key-title-text').text('Key #1');

                rows.find('.api-key-preview-text').text('-');

                return;

            }

            var $row = $(this).closest('.api-key-row');

            destroyExpiresDatePickers($row);
            $row.remove();
            refreshKeyRowNumbers();

        });



        $(document).on('click', '.btn-edit-api-key', function () {

            var type = $(this).data('type');

            var siteId = $(this).data('site-id');

            var scope = $(this).data('scope') || getCurrentScope();

            loading('load');

            axios.get(providerUrl(type, siteId, scope))

                .then(function (response) {

                    openApiKeyModal('edit', response.data);

                })

                .catch(function () {

                    toastr.error('@langapp('request_failed')', '@langapp('response_status')');

                })

                .finally(function () {

                    loading('stop_load');

                });

        });



        var pendingApiKeyDelete = null;

        function resetDeleteApiKeyButton() {
            $('#btn-confirm-delete-api-key')
                .prop('disabled', false)
                .html('<i class="fas fa-trash-alt"></i> @langapp('delete')');
        }

        function clearPendingApiKeyDelete() {
            pendingApiKeyDelete = null;
            resetDeleteApiKeyButton();
        }

        $(document).on('click', '.btn-delete-api-key', function () {
            var $btn = $(this);
            var scope = $btn.data('scope') || getCurrentScope();
            var siteName = $btn.data('site-name') || '-';
            var keyCount = parseInt($btn.data('key-count'), 10) || 0;

            pendingApiKeyDelete = {
                type: $btn.data('type'),
                siteId: $btn.data('site-id'),
                scope: scope
            };

            $('#api-key-delete-name').text($btn.data('name') || pendingApiKeyDelete.type || '-');
            $('#api-key-delete-scope').text($btn.data('scope-label') || (scope === 'system' ? 'System' : 'Site'));
            $('#api-key-delete-key-count').text(keyCount + (keyCount === 1 ? ' key' : ' keys'));

            if (scope === 'system') {
                $('#api-key-delete-site-row').hide();
            } else {
                $('#api-key-delete-site-row').show();
                $('#api-key-delete-site').text(siteName);
            }

            resetDeleteApiKeyButton();
            $('#api-key-delete-modal').modal('show');
        });

        $('#api-key-delete-modal').on('hidden.bs.modal', function () {
            clearPendingApiKeyDelete();
        });

        $('#btn-confirm-delete-api-key').on('click', function () {
            if (!pendingApiKeyDelete) {
                return;
            }

            var $confirmBtn = $(this);
            var deleteData = pendingApiKeyDelete;

            $confirmBtn.prop('disabled', true);
            loading('load');

            axios.delete(providerUrl(deleteData.type, deleteData.siteId, deleteData.scope))
                .then(function (response) {
                    $('#api-key-delete-modal').modal('hide');
                    toastr.success(response.data.message, '@langapp('response_status')');
                    reloadApiKeyTable();
                })
                .catch(function () {
                    toastr.error('@langapp('request_failed')', '@langapp('response_status')');
                })
                .finally(function () {
                    loading('stop_load');
                    resetDeleteApiKeyButton();
                });
        });



        $('#btn-apply-filter').on('click', function () {

            reloadApiKeyTable();

        });



        $('#btn-clear-filter').on('click', function () {

            $('#filter_name, #filter_url').val('');

            $('#filter_expire_status').val('').trigger('change');

            reloadApiKeyTable();

        });



        $('#api_key_site_select').on('change', function () {

            $('#api_key_site_id').val($(this).val());

        });



        $('#form-api-key').on('submit', function (e) {

            e.preventDefault();

            var type = $('#api_key_type').val();

            var siteId = $('#api_key_site_id').val();

            var scope = getCurrentScope();

            var providerName = getProviderNameValue();

            var url = type

                ? providerUrl(type, siteId, scope)

                : '{{ route('apikey.store') }}';

            var method = type ? 'put' : 'post';

            var payload = {

                name: providerName,

                url: $('#api_key_url').val(),

                scope: scope,

                site_id: scope === 'site' && siteId !== '' ? siteId : null,

                keys: []

            };



            $('#api-key-rows .api-key-row').each(function (index) {

                var keyId = $(this).find('.api-key-id').val();

                var keyName = $(this).find('.api-key-name').val();

                var keyItem = {

                    name: keyName || ('Key Name ' + (index + 1)),

                    key_value: $(this).find('.api-key-value').val(),

                    expires_at: getKeyExpiresValue($(this)),

                    whitelist_ips: getKeyWhitelistValue($(this))

                };

                if (keyId) keyItem.id = keyId;

                payload.keys.push(keyItem);

            });



            $('.formSaving').prop('disabled', true);
            loading('load');

            axios({ method: method, url: url, data: payload })

                .then(function (response) {

                    toastr.success(response.data.message, '@langapp('response_status')');

                    $('#api-key-modal').modal('hide');

                    reloadApiKeyTable();

                })

                .catch(function (error) {

                    if (error.response && error.response.data) {

                        var responseData = error.response.data;

                        var errorsHtml = '';

                        if (responseData.errors) {

                            $.each(responseData.errors, function (key, value) {

                                if ($.isArray(value)) {

                                    $.each(value, function (_, message) {

                                        errorsHtml += '<li>' + message + '</li>';

                                    });

                                } else {

                                    errorsHtml += '<li>' + value + '</li>';

                                }

                            });

                        }

                        if (errorsHtml) {

                            var title = responseData.message || 'Validation failed';

                            toastr.error('<strong>' + title + '</strong><ul class="m-t-xs m-b-none">' + errorsHtml + '</ul>', '@langapp('response_status')');

                        } else if (responseData.message) {

                            toastr.error(responseData.message, '@langapp('response_status')');

                        } else {

                            toastr.error('@langapp('request_failed')', '@langapp('response_status')');

                        }

                    } else {

                        toastr.error('@langapp('request_failed')', '@langapp('response_status')');

                    }

                })

                .finally(function () {

                    loading('stop_load');

                    $('.formSaving').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Save');

                });

        });

    });

</script>

@endpush

@endsection


