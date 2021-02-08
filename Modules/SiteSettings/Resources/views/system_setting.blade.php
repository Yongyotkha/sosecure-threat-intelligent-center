@extends('layouts.app')

@section('content')

<section id="content" class="bg">
    <section class="hbox stretch">

        <aside id="hide-settings" class="aside aside-md b-r">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#setting-nav">@icon('solid/bars')</a>
                    <a class="hide-setting btn btn-icon btn-default btn-sm pull-right m-r-xs">@icon('solid/bars')</a>
                    <p class="h3 text-elipse-setting">{{@$siteSettings->name}}</p>
                </header>
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_site')
                    </section>
                </section>
            </section>
        </aside>

        <aside>
            <section class="vbox">

                <header class="header bg-white b-b clearfix">
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs"
                        style="margin-top: 0;display:none">@icon('solid/bars')</a>
                    <div class="bc-head">Site Setting > System</div>
                </header>
                <section class="scrollable wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            {!! Form::open(['route' => ['sitesettings.update.settings', $siteSettings->code], 'class' =>
                            'bs-example form-horizontal ajaxifyForm validator', 'novalidate' => '', 'method' => 'PUT'])
                            !!}
                            <section class="panel panel-default">
                                <header class="panel-heading font-bold panel-header-blue">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            @icon('solid/cogs') System Settings
                                        </div>
                                    </div>
                                </header>
                                <input type="hidden" name="page_setting" value="system_settings">
                                <div class="panel-body">
                                    <div class="form-group row">
                                        <label class="col-lg-3 control-label">System Online </label>
                                        <div class="col-lg-3 d-none">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="system_web_online"
                                                        {{ $siteSettings -> system_web_online === 1 ? 'checked' : '' }}
                                                        value="1">
                                                    <span class="label-text" data-rel="tooltip" title="">Web
                                                        Online</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="system_site_online"
                                                        {{ $siteSettings -> system_site_online === 1 ? 'checked' : '' }}
                                                        value="1">
                                                    <span class="label-text" data-rel="tooltip" title="">Site
                                                        System</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 control-label">Start Active <span
                                                class="text-danger">*</span></label>
                                        <div class="col-lg-6">
                                            <div class="input-group date">
                                                <input id="send_date" type="text"
                                                    class="form-control datetimepicker-input"
                                                    value="{{  timePickerFormat( (empty($siteSettings -> start_active)?date('Y-m-d H:i:s'):$siteSettings -> start_active) ) }}"
                                                    name="start_active" data-date-format="DD-MM-YYYY HH:mm:ss"
                                                    data-date-start-date="moment()" required>
                                                <div class="input-group-addon">
                                                    @icon('solid/calendar-alt', 'text-muted')
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 control-label">End Active <span
                                                class="text-danger">*</span></label>
                                        <div class="col-lg-6">
                                            <div class="input-group date">
                                                <input id="send_date" type="text"
                                                    class="form-control datetimepicker-input"
                                                    value="{{  timePickerFormat($siteSettings -> end_active) }}"
                                                    name="end_active" data-date-format="DD-MM-YYYY HH:mm:ss"
                                                    data-date-start-date="moment()" required>
                                                <div class="input-group-addon">
                                                    @icon('solid/calendar-alt', 'text-muted')
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 control-label">IP Private<span
                                                class="text-danger">*</span> </label>
                                        <div class="col-lg-6">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-input">
                                                        <input type="text" class="form-control" id="ip_key"
                                                            name="ip_key" value="{{ $siteSettings -> ip_key }}"
                                                            required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 control-label">IP Public<span
                                                class="text-danger">*</span> </label>
                                        <div class="col-lg-6">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-input">
                                                        <input type="text" class="form-control" id="ip_public"
                                                            name="ip_public" value="{{ $siteSettings -> ip_public }}"
                                                            required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 control-label">Mac Address <span
                                                class="text-danger">*</span> </label>
                                        <div class="col-lg-6">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-input">
                                                        <input type="text" class="form-control" id="mac_address_key"
                                                            name="mac_address_key"
                                                            value="{{ $siteSettings -> mac_address_key }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 control-label">System Key <span data-rel="tooltip"
                                                title="Copy Key ไปใส่ในระบบ Site System"><i
                                                    class="far fa-question-circle"></i></span> <span
                                                class="text-danger">*</span> </label>
                                        <div class="col-lg-6">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="generate_key"
                                                            name="system_key" value="{{ $siteSettings -> system_key }}"
                                                            readonly>
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-info"
                                                                onclick="copy_system_key()">Copy</button>
                                                            {{-- <button type="button" class="btn btn-info" onclick="genarate_system_key()">Gen</button>   --}}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-12">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input id="set_exp" type="checkbox"
                                                                name="no_expiration_active" value="1"
                                                                {{ $siteSettings -> no_expiration_active === 1 ? 'checked' : '' }}>
                                                            <span class="label-text" data-rel="tooltip" title="">Set an
                                                                expiration date</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="show_start_exp_date" class="form-group row">
                                        <label class="col-lg-3 control-label">Start Active <span
                                                class="text-danger">*</span></label>
                                        <div class="col-lg-6">
                                            <div class="input-group date">
                                                <input id="send_date" type="text"
                                                    class="form-control datetimepicker-input"
                                                    value="{{  timePickerFormat($siteSettings -> start_active_key) }}"
                                                    name="start_active_key" data-date-format="DD-MM-YYYY HH:mm:ss"
                                                    data-date-start-date="moment()" required>
                                                <div class="input-group-addon">
                                                    @icon('solid/calendar-alt', 'text-muted')
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="show_end_exp_date" class="form-group row">
                                        <label class="col-lg-3 control-label">End Active <span
                                                class="text-danger">*</span></label>
                                        <div class="col-lg-6">
                                            <div class="input-group date">
                                                <input id="send_date" type="text"
                                                    class="form-control datetimepicker-input"
                                                    value="{{  timePickerFormat($siteSettings -> end_active_key) }}"
                                                    name="end_active_key" data-date-format="DD-MM-YYYY HH:mm:ss"
                                                    data-date-start-date="moment()" required>
                                                <div class="input-group-addon">
                                                    @icon('solid/calendar-alt', 'text-muted')
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-lg-3 control-label">Public Key <span data-rel="tooltip"
                                                title="Copy Key ไปใส่ในระบบ Site System"><i
                                                    class="far fa-question-circle"></i></span> <span
                                                class="text-danger">*</span> </label>
                                        <div class="col-lg-6">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="public_key"
                                                            value="{{ $siteSettings -> public_key }}" readonly>
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-info"
                                                                onclick="copy_public_key()">Copy</button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-lg-3 control-label">Mysql User<span data-rel="tooltip"
                                                title="Copy Key ไปใส่ในระบบ Site System"><i
                                                    class="far fa-question-circle"></i></span> <span
                                                class="text-danger">*</span> </label>
                                        <div class="col-lg-6">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="mysql_user"
                                                            value="{{ $siteSettings -> mysql_user }}" readonly>
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-info"
                                                                onclick="copy_text_id('mysql_user')">Copy</button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-lg-3 control-label">Mysql Password<span data-rel="tooltip"
                                                title="Copy Key ไปใส่ในระบบ Site System"><i
                                                    class="far fa-question-circle"></i></span> <span
                                                class="text-danger">*</span> </label>
                                        <div class="col-lg-6">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="input-group">
                                                        <input type="password" class="form-control"
                                                            id="read_mysql_password"
                                                            value="{{ $siteSettings -> mysql_password }}" readonly>
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-info"
                                                                onclick="copy_password_mysql()">Copy</button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="form-group row">
                                        <label class="col-lg-3 control-label">Mongo User<span data-rel="tooltip"
                                                title="Copy Key ไปใส่ในระบบ Site System"><i
                                                    class="far fa-question-circle"></i></span> <span
                                                class="text-danger">*</span> </label>
                                        <div class="col-lg-6">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="mongo_user"
                                                            value="{{ $siteSettings -> mongo_user }}" readonly>
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-info"
                                                                onclick="copy_text_id('mongo_user')">Copy</button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-lg-3 control-label">Mongo Password<span data-rel="tooltip"
                                                title="Copy Key ไปใส่ในระบบ Site System"><i
                                                    class="far fa-question-circle"></i></span> <span
                                                class="text-danger">*</span> </label>
                                        <div class="col-lg-6">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="input-group">
                                                        <input type="password" class="form-control"
                                                            id="read_mongo_password"
                                                            value="{{ $siteSettings -> mongo_password }}" readonly>
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-info"
                                                                onclick="copy_password_mongo()">Copy</button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- <div class="form-group row">
                                     <div class="col-lg-3 control-label">API Key</div>
                                     <div class="col-lg-6">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="generate_key" value="" readonly>
                                            <span class="input-group-btn">
                                                <button type="submit" class="btn btn-info">Copy</button>  
                                            </span>
                                        </div>
                                     </div>
                                 </div> --}}

                                    <div class="row">
                                        <div class="col-lg- text-center">
                                            <div class="line"></div>
                                            <h4 class="py-3">Client System Status : <span class="text-success">Online
                                                    (Last Update :
                                                    {{$siteSettings->last_client_update==null?'ไม่มีข้อมูล':$siteSettings->last_client_update}})</span>
                                                &nbsp;<button style="display: none;" class="btn btn-xs btn-info"><i
                                                        class="fa fa-sync-alt"></i></button>
                                            </h4>

                                        </div>
                                    </div>
                                    <div class="row pa-sm">
                                        <div class="col-lg-12" style="background: #f3f6f9;">
                                            <div class="m-xs">
                                                <span class="text-dark">Laravel Version</span>: <span
                                                    class="text-muted">{{ $siteSettings -> laravel_version == null ? 'ไม่มีข้อมูล' : $siteSettings -> laravel_version }}</span>
                                                <table>
                                                    <thead>
                                                        <tr>
                                                            <th style="text-align:center;width:50px;">
                                                                <a href="#" onclick="cache_clear();"
                                                                    class="btn btn-xs btn-{{ get_option('theme_color') }}"
                                                                    data-rel="tooltip"
                                                                    title="Clear Cache">cache:clear</a>
                                                            </th>
                                                            <th style="text-align:center;width:50px;">
                                                                <a href="#" onclick="config_cache();"
                                                                    class="btn btn-xs btn-{{ get_option('theme_color') }}"
                                                                    data-rel="tooltip"
                                                                    title="Config Cache">config:cache</a>
                                                            </th>
                                                            <th style="text-align:center;width:50px;">
                                                                <a href="#" onclick="config_clear();"
                                                                    class="btn btn-xs btn-{{ get_option('theme_color') }}"
                                                                    data-rel="tooltip"
                                                                    title="Clear Config">cache:clear</a>
                                                            </th>
                                                            <th style="text-align:center;width:50px;">
                                                                <a href="#" onclick="set_permission();"
                                                                    class="btn btn-xs btn-{{ get_option('theme_color') }}"
                                                                    data-rel="tooltip" title="Set Permission">set
                                                                    permission</a>
                                                            </th>
                                                            <th style="text-align:center;width:50px;">
                                                                <a href="#" onclick="update_code();"
                                                                    class="btn btn-xs btn-{{ get_option('theme_color') }}"
                                                                    data-rel="tooltip" title="Update code">Update
                                                                    Code</a>
                                                            </th>
                                                            <th style="text-align:center;width:50px;">
                                                                <a href="#" onclick="get_status_nginx();"
                                                                    class="btn btn-xs btn-{{ get_option('theme_color') }}"
                                                                    data-rel="tooltip" title="Get status nginx">Get Status Nginx</a>
                                                            </th>
                                                            <th style="text-align:center;width:50px;">
                                                                <a href="#" onclick="restart_nginx();"
                                                                    class="btn btn-xs btn-{{ get_option('theme_color') }}"
                                                                    data-rel="tooltip" title="Restart nginx">Restart Nginx</a>
                                                            </th>
                                                            <th style="text-align:center;width:50px;">
                                                                <a href="#" onclick="get_status_mongo();"
                                                                    class="btn btn-xs btn-{{ get_option('theme_color') }}"
                                                                    data-rel="tooltip" title="Get status mongo">Get Status MongoDB</a>
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <center>
                                                                    {{ @$siteSettings -> get_cache_clear -> updated_at}}
                                                                </center>
                                                            </td>
                                                            <td>
                                                                <center>
                                                                    {{ @$siteSettings -> get_config_cache -> updated_at}}
                                                                </center>
                                                            </td>
                                                            <td>
                                                                <center>
                                                                    {{ @$siteSettings -> get_config_clear -> updated_at}}
                                                                </center>
                                                            </td>
                                                            <td>
                                                                <center>
                                                                    {{ @$siteSettings -> get_set_permission -> updated_at}}
                                                                </center>
                                                            </td>
                                                            <td>
                                                                <center>
                                                                    {{ @$siteSettings -> get_update_code -> updated_at}}
                                                                </center>
                                                            </td>
                                                            <td>
                                                                <center>
                                                                    {{ @$siteSettings -> get_status_nginx -> updated_at}}
                                                                </center>
                                                            </td>
                                                            <td>
                                                                <center>
                                                                    {{ @$siteSettings -> restart_nginx -> updated_at}}
                                                                </center>
                                                            </td>
                                                            <td>
                                                                <center>
                                                                    {{ @$siteSettings -> get_status_mongo -> updated_at}}
                                                                </center>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <table>
                                                    <thead>
                                                        <tr>
                                                            <th style="text-align:center;width:50px;">
                                                                <a href="#" onclick="restart_mongo();"
                                                                    class="btn btn-xs btn-{{ get_option('theme_color') }}"
                                                                    data-rel="tooltip" title="Restart mongo">Restart MongoDB</a>
                                                            </th>
                                                            <th style="text-align:center;width:50px;">
                                                                <a href="#" onclick="get_status_mysql();"
                                                                    class="btn btn-xs btn-{{ get_option('theme_color') }}"
                                                                    data-rel="tooltip" title="Get status mysql">Get Status Mysql</a>
                                                            </th>
                                                            <th style="text-align:center;width:50px;">
                                                                <a href="#" onclick="restart_mysql();"
                                                                    class="btn btn-xs btn-{{ get_option('theme_color') }}"
                                                                    data-rel="tooltip" title="Restart mysql">Restart Mysql</a>
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <center>
                                                                    {{ @$siteSettings -> restart_mongo -> updated_at}}
                                                                </center>
                                                            </td>
                                                            <td>
                                                                <center>
                                                                    {{ @$siteSettings -> get_status_mysql -> updated_at}}
                                                                </center>
                                                            </td>
                                                            <td>
                                                                <center>
                                                                    {{ @$siteSettings -> restart_mysql -> updated_at}}
                                                                </center>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>




                                            </div>
                                            <div class="line"></div>
                                            <div class="m-xs">
                                                <span class="text-dark">Code Version</span>: <span
                                                    class="text-muted">{{ empty($code_version) ? 'ไม่มีข้อมูล' : $code_version }}</span>
                                                <a style="display: none;" href="#"
                                                    class="btn btn-xs btn-{{ get_option('theme_color') }} ml-2"
                                                    id="updatesBtn" data-rel="tooltip"
                                                    title="Check for updates now">@icon('solid/code-branch')
                                                    @langapp('check_for_updates')</a>
                                                <span class="text-danger ml-2">Last Version {{ $last_version }}</span>
                                            </div>
                                            <div class="line"></div>
                                            <div class="m-xs">
                                                <span class="text-dark">OS Version</span>: <span
                                                    class="text-muted">{{ $siteSettings -> os == null ? 'ไม่มีข้อมูล' : $siteSettings -> os }}</span>
                                            </div>
                                            <div class="line"></div>
                                            <div class="m-xs">
                                                <span class="text-dark">Sever Time</span>: <span
                                                    class="text-muted">{{ $siteSettings -> server_time == null ? 'ไม่มีข้อมูล' : $siteSettings -> server_time }}</span>
                                            </div>
                                            <div class="line"></div>
                                            <div class="m-xs">
                                                <span class="text-dark">PHP Version</span>: <span
                                                    class="text-muted">{{ $siteSettings -> php_version == null ? 'ไม่มีข้อมูล' : $siteSettings -> php_version }}</span>
                                            </div>
                                            <div class="line"></div>
                                            <div class="m-xs">
                                                <span class="text-dark">Your App Name</span>: <span
                                                    class="text-muted">{{ $siteSettings -> your_app_name == null ? 'ไม่มีข้อมูล' : $siteSettings -> your_app_name }}</span>
                                            </div>
                                            <div class="line"></div>
                                            <div class="m-xs">
                                                <span class="text-dark">Timezone</span>: <span
                                                    class="text-muted">{{ $siteSettings -> time_zone == null ? 'ไม่มีข้อมูล' : $siteSettings -> time_zone }}</span>
                                            </div>
                                            <div class="line"></div>
                                            {{-- <div class="m-xs">
                                            <span class="text-dark">Key System</span>: <span class="text-muted">{{ $siteSettings -> key_system == null ? 'ไม่มีข้อมูล' : $siteSettings -> key_system }}</span>
                                        </div>
                                        <div class="line"></div> --}}
                                    </div>
                                </div>

                        </div>
                        <div class="panel-footer">
                            {!! closeModalButton() !!}
                            {!! renderAjaxButton() !!}
                        </div>
                        {!! Form::close() !!}
                    </div>
                    </div>
                </section>
            </section>
        </aside>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen, open" data-target="#nav,html">

    </a>
</section>


@push('pagestyle')
@include('stacks.css.form')
@include('stacks.css.datepicker')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.menusub')
@include('stacks.js.site_hidesettings')

<script>
    $(document).ready(function () {
        {{--$('.datetimepicker-input').datetimepicker({
                    showClose: true,
                    showClear: true,
                    minDate: moment().add(-1, 'days')
                });--}}
        $('.datetimepicker-input').datetimepicker({
            showClose: true,
            showClear: true
        });
        $('#show_start_exp_date').hide();
        $('#show_end_exp_date').hide();

        $('#set_exp').on('change', function () {
            if ($(this).prop('checked')) {
                $('#show_start_exp_date').show();
                $('#show_end_exp_date').show();
            } else {
                $('#show_start_exp_date').hide();
                $('#show_end_exp_date').hide();
            }
        });
    });

    function cache_clear() {
        $.ajax({
            type: "POST",
            url: "{{ route('sitesettings.artisan_call') }}",
            data: {
                mode: 'cache_clear',
                site_id: '{{ $siteSettings -> id }}'
            },
            success: function (response) {
                loading('stop_load');
                if (response.status === true) {
                    toastr.success(response.message, '@langapp('response_status ')');
                } else {
                    toastr.error(response.message, '@langapp('response_status ')');
                }

            },
            error: function (error) {
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status ') ');
            }
        });
    }

    function config_cache() {
        $.ajax({
            type: "POST",
            url: "{{ route('sitesettings.artisan_call') }}",
            data: {
                mode: 'config_cache',
                site_id: '{{ $siteSettings -> id }}'
            },
            success: function (response) {
                loading('stop_load');
                if (response.status === true) {
                    toastr.success(response.message, '@langapp('response_status ')');
                } else {
                    toastr.error(response.message, '@langapp('response_status ')');
                }
            },
            error: function (error) {
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status ') ');
            }
        });
    }

    function config_clear() {
        $.ajax({
            type: "POST",
            url: "{{ route('sitesettings.artisan_call') }}",
            data: {
                mode: 'config_clear',
                site_id: '{{ $siteSettings -> id }}'
            },
            success: function (response) {
                loading('stop_load');
                if (response.status === true) {
                    toastr.success(response.message, '@langapp('response_status ')');
                } else {
                    toastr.error(response.message, '@langapp('response_status ')');
                }
            },
            error: function (error) {
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status ') ');
            }
        });
    }

    function set_permission() {
        $.ajax({
            type: "POST",
            url: "{{ route('sitesettings.artisan_call') }}",
            data: {
                mode: 'set_permission',
                site_id: '{{ $siteSettings -> id }}'
            },
            success: function (response) {
                loading('stop_load');
                if (response.status === true) {
                    toastr.success(response.message, '@langapp('response_status ')');
                } else {
                    toastr.error(response.message, '@langapp('response_status ')');
                }
            },
            error: function (error) {
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status ') ');
            }
        });
    }

    function update_code() {
        $.ajax({
            type: "POST",
            url: "{{ route('sitesettings.artisan_call') }}",
            data: {
                mode: 'update_code',
                site_id: '{{ $siteSettings -> id }}'
            },
            success: function (response) {
                loading('stop_load');
                if (response.status === true) {
                    toastr.success(response.message, '@langapp('response_status ')');
                } else {
                    toastr.error(response.message, '@langapp('response_status ')');
                }
            },
            error: function (error) {
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status ') ');
            }
        });
    }

    function get_status_nginx() {
        $.ajax({
            type: "POST",
            url: "{{ route('sitesettings.artisan_call') }}",
            data: {
                mode: 'get_status_nginx',
                site_id: '{{ $siteSettings -> id }}'
            },
            success: function (response) {
                loading('stop_load');
                if (response.status === true) {
                    toastr.success(response.message, '@langapp('response_status ')');
                } else {
                    toastr.error(response.message, '@langapp('response_status ')');
                }
            },
            error: function (error) {
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status ') ');
            }
        });
    }

    function restart_nginx() {
        $.ajax({
            type: "POST",
            url: "{{ route('sitesettings.artisan_call') }}",
            data: {
                mode: 'restart_nginx',
                site_id: '{{ $siteSettings -> id }}'
            },
            success: function (response) {
                loading('stop_load');
                if (response.status === true) {
                    toastr.success(response.message, '@langapp('response_status ')');
                } else {
                    toastr.error(response.message, '@langapp('response_status ')');
                }
            },
            error: function (error) {
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status ') ');
            }
        });
    }

    function get_status_mongo() {
        $.ajax({
            type: "POST",
            url: "{{ route('sitesettings.artisan_call') }}",
            data: {
                mode: 'get_status_mongo',
                site_id: '{{ $siteSettings -> id }}'
            },
            success: function (response) {
                loading('stop_load');
                if (response.status === true) {
                    toastr.success(response.message, '@langapp('response_status ')');
                } else {
                    toastr.error(response.message, '@langapp('response_status ')');
                }
            },
            error: function (error) {
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status ') ');
            }
        });
    }

    function restart_mongo() {
        $.ajax({
            type: "POST",
            url: "{{ route('sitesettings.artisan_call') }}",
            data: {
                mode: 'restart_mongo',
                site_id: '{{ $siteSettings -> id }}'
            },
            success: function (response) {
                loading('stop_load');
                if (response.status === true) {
                    toastr.success(response.message, '@langapp('response_status ')');
                } else {
                    toastr.error(response.message, '@langapp('response_status ')');
                }
            },
            error: function (error) {
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status ') ');
            }
        });
    }

    function get_status_mysql() {
        $.ajax({
            type: "POST",
            url: "{{ route('sitesettings.artisan_call') }}",
            data: {
                mode: 'get_status_mysql',
                site_id: '{{ $siteSettings -> id }}'
            },
            success: function (response) {
                loading('stop_load');
                if (response.status === true) {
                    toastr.success(response.message, '@langapp('response_status ')');
                } else {
                    toastr.error(response.message, '@langapp('response_status ')');
                }
            },
            error: function (error) {
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status ') ');
            }
        });
    }

    function restart_mysql() {
        $.ajax({
            type: "POST",
            url: "{{ route('sitesettings.artisan_call') }}",
            data: {
                mode: 'restart_mysql',
                site_id: '{{ $siteSettings -> id }}'
            },
            success: function (response) {
                loading('stop_load');
                if (response.status === true) {
                    toastr.success(response.message, '@langapp('response_status ')');
                } else {
                    toastr.error(response.message, '@langapp('response_status ')');
                }
            },
            error: function (error) {
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status ') ');
            }
        });
    }

    function genarate_system_key() {
        let system_key = uuidv4();
        $('#generate_key').val(system_key);
    }

    function copy_system_key() {
        var copyText = document.getElementById("generate_key");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        toastr.success("Copied", '@langapp('response_status ')');
    }

    function copy_public_key() {
        var copyText = document.getElementById("public_key");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        toastr.success("Copied", '@langapp('response_status ')');
    }

    function copy_password_mysql() {
        var copyText = document.createElement("textarea");
        document.body.appendChild(copyText);
        copyText.value = '{{ $siteSettings -> mysql_password }}';
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        toastr.success("Copied", '@langapp('response_status ')');
    }

    function copy_password_mongo() {
        var copyText = document.createElement("textarea");
        document.body.appendChild(copyText);
        copyText.value = '{{ $siteSettings -> mongo_password }}';
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        toastr.success("Copied", '@langapp('response_status ')');
    }

    function copy_text_id(keyId) {
        var copyText = document.getElementById(keyId);
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        toastr.success("Copied", '@langapp('response_status ')');
    }

    function uuidv4() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0,
                v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
</script>
@if($siteSettings -> no_expiration_active === 1)
<script>
    $(document).ready(function () {
        $('#show_start_exp_date').show();
        $('#show_end_exp_date').show();
    });
</script>
@endif
@endpush

@endsection
