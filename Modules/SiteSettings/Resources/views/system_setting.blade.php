@extends('layouts.app')

@section('content')

<section id="content" class="bg">
    <section class="hbox stretch">
        
        <aside class="aside aside-md b-r">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#setting-nav">@icon('solid/bars')</a>
                    <p class="h3">@langapp('settings')  </p>
                </header>
                <section class="scrollable">
                    <div class="slim-scroll" data-color="#333333" data-disable-fade-out="true" data-distance="0" data-height="auto" data-size="3px"> 
                    <section id="setting-nav" class="hidden-xs">
                        <ul class="nav nav-pills nav-stacked no-radius">
                            <li>
                                <a href="{{route('sitesettings.edit', ['id' => $siteSettings->code])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Site Settings
                                </a>
                            </li>
                            <li class="active">
                                <a href="{{route('systemsetting.index', ['id' => $siteSettings->code])}}">

                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    System Settings
                                </a>
                            </li>
                            <li class="">
                                <a href="{{route('datasettings.index')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Permission & Config Settings
                                </a>
                            </li>
                            <li class="">
                                <a href="{{route('userssettings.index')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Users
                                </a>
                            </li>
                            <li>
                                <a href="{{route('domain.index')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Domain
                                </a>
                            </li>
                        </ul>
                    </section>
                </div>
                </section>
            </section>
        </aside>

        <aside>
            <section class="vbox">

                <header class="header bg-white b-b clearfix">
                <div class="bc-head">Site Setting > {{$siteSettings->name}}</div>
                </header>
                <section class="scrollable wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            {!! Form::open(['route' => ['sitesettings.update.settings', $siteSettings->code], 'class' => 'bs-example form-horizontal ajaxifyForm validator', 'novalidate' => '', 'method' => 'PUT']) !!}
                            <section class="panel panel-default">
                            <header class="panel-heading">@icon('solid/cogs') System Details  </header>
                            <input type="hidden" name="page_setting" value="system_settings">
                            <div class="panel-body">
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">System Online </label>
                                    <div class="col-lg-3">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="system_web_online" {{ $siteSettings -> system_web_online === 1 ? 'checked' : '' }} value="1">
                                                <span class="label-text" data-rel="tooltip" title="">Web Online</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="system_site_online" {{ $siteSettings -> system_site_online === 1 ? 'checked' : '' }} value="1">
                                                <span class="label-text" data-rel="tooltip" title="">Site System</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Start Active <span class="text-danger">*</span></label>
                                    <div class="col-lg-6">
                                        <div class="input-group date">
                                            <input id="send_date" type="text" class="form-control datetimepicker-input"
                                            value="{{  timePickerFormat($siteSettings -> start_active) }}" name="start_active"
                                            data-date-format="DD-MM-YYYY HH:mm:ss" data-date-start-date="moment()" required>
                                            <div class="input-group-addon">
                                                @icon('solid/calendar-alt', 'text-muted')
                                            </div>
                                        </div>
                                    </div>
                                 </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">End Active <span class="text-danger">*</span></label>
                                    <div class="col-lg-6">
                                        <div class="input-group date">
                                            <input id="send_date" type="text" class="form-control datetimepicker-input"
                                            value="{{  timePickerFormat($siteSettings -> end_active) }}" name="end_active"
                                            data-date-format="DD-MM-YYYY HH:mm:ss" data-date-start-date="moment()" required>
                                            <div class="input-group-addon">
                                                @icon('solid/calendar-alt', 'text-muted')
                                            </div>
                                        </div>
                                    </div>
                                 </div>
                                 <div class="form-group row">
                                    <label class="col-lg-3 control-label">System Key <span  data-rel="tooltip" title="Copy Key ไปใส่ในระบบ Site System"><i class="far fa-question-circle"></i></span> <span class="text-danger">*</span> </label>
                                    <div class="col-lg-6">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="generate_key" name="system_key" value="{{ $siteSettings -> system_key }}" readonly>
                                                    <span class="input-group-btn">
                                                        <button type="button" class="btn btn-info" onclick="copy_system_key()">Copy</button>  
                                                        <button type="button" class="btn btn-info" onclick="genarate_system_key()">Gen</button>  
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="checkbox">
                                                    <label>
                                                        <input id="set_exp" type="checkbox" name="no_expiration_active" value="1" {{ $siteSettings -> no_expiration_active === 1 ? 'checked' : '' }}>
                                                        <span class="label-text" data-rel="tooltip" title="">Set an expiration date</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="show_start_exp_date" class="form-group row">
                                    <label class="col-lg-3 control-label">Start Active <span class="text-danger">*</span></label>
                                    <div class="col-lg-6">
                                        <div class="input-group date">
                                            <input id="send_date" type="text" class="form-control datetimepicker-input"
                                            value="{{  timePickerFormat($siteSettings -> start_active_key) }}" name="start_active_key"
                                            data-date-format="DD-MM-YYYY HH:mm:ss" data-date-start-date="moment()" required>
                                            <div class="input-group-addon">
                                                @icon('solid/calendar-alt', 'text-muted')
                                            </div>
                                        </div>
                                    </div>
                                 </div>
                                <div id="show_end_exp_date" class="form-group row">
                                    <label class="col-lg-3 control-label">End Active <span class="text-danger">*</span></label>
                                    <div class="col-lg-6">
                                        <div class="input-group date">
                                            <input id="send_date" type="text" class="form-control datetimepicker-input"
                                            value="{{  timePickerFormat($siteSettings -> end_active_key) }}" name="end_active_key"
                                            data-date-format="DD-MM-YYYY HH:mm:ss" data-date-start-date="moment()" required>
                                            <div class="input-group-addon">
                                                @icon('solid/calendar-alt', 'text-muted')
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
                                        <h4 class="py-3">Client System Status : <span class="text-success">Online (Last Update : 2020-08-29 11:11:23)</span>
                                            &nbsp;<button type="submit" class="btn btn-xs btn-info"><i class="fa fa-sync-alt"></i></button>
                                        </h4> 
                                       
                                     </div>
                                 </div>
                                 <div class="row pa-sm">
                                    <div class="col-lg-12" style="background: #f3f6f9;">
                                        <div class="m-xs">
                                            <span class="text-dark">Laravel Version</span>: <span class="text-muted">5.7.29</span>
                                            <a href="#" class="btn btn-xs btn-{{ get_option('theme_color') }} ml-2" data-rel="tooltip" title="Clear Cache">cache:clear</a>
                                            <a href="#" class="btn btn-xs btn-{{ get_option('theme_color') }} ml-2" data-rel="tooltip" title="Config Cache">config:cache</a>
                                            <a href="#" class="btn btn-xs btn-{{ get_option('theme_color') }} ml-2" data-rel="tooltip" title="Clear Config">cache:clear</a>
                                        </div>
                                        <div class="line"></div>
                                        <div class="m-xs">
                                            <span class="text-dark">Code Version</span>: <span class="text-muted">2.0.5</span>
                                            <a href="#" class="btn btn-xs btn-{{ get_option('theme_color') }} ml-2" id="updatesBtn" data-rel="tooltip" title="Check for updates now">@icon('solid/code-branch') @langapp('check_for_updates')</a>
                                            <span class="text-danger ml-2">Last Version 3.0.5</span>
                                        </div>
                                        <div class="line"></div>
                                        <div class="m-xs">
                                            <span class="text-dark">OS Version</span>: <span class="text-muted">Linux</span>
                                        </div>
                                        <div class="line"></div>
                                        <div class="m-xs">
                                            <span class="text-dark">Sever Time</span>: <span class="text-muted">Sep 29,2020 11:26 AM</span>
                                        </div>
                                        <div class="line"></div>
                                        <div class="m-xs">
                                            <span class="text-dark">PHP Version</span>: <span class="text-muted">7.3.20</span>
                                        </div>
                                        <div class="line"></div>
                                        <div class="m-xs">
                                            <span class="text-dark">Your App Name</span>: <span class="text-muted">Threat-inSight</span>
                                        </div>
                                        <div class="line"></div>
                                        <div class="m-xs">
                                            <span class="text-dark">Timezone</span>: <span class="text-muted">Asia/Bangkok</span>
                                        </div>
                                        <div class="line"></div>
                                        <div class="m-xs">
                                            <span class="text-dark">Key System</span>: <span class="text-muted">xxxxxxxxxxxxxxxxxxxxxx</span>
                                        </div>
                                        <div class="line"></div>
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

<script>
$(document).ready(function(){
    $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') });

    $('#show_start_exp_date').hide();
    $('#show_end_exp_date').hide();

    $('#set_exp').on('change',function(){
        if($(this).prop('checked')){
            $('#show_start_exp_date').show();
            $('#show_end_exp_date').show();
        }else{
            $('#show_start_exp_date').hide();
            $('#show_end_exp_date').hide();
        }
    });
});

function genarate_system_key(){
    let system_key = uuidv4();
    $('#generate_key').val(system_key);
}

function copy_system_key(){
    var copyText = document.getElementById("generate_key");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    toastr.success("Copied", '@langapp('response_status')');
}

function uuidv4() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}

</script>
@if($siteSettings -> no_expiration_active === 1)
    <script>
        $(document).ready(function(){
            $('#show_start_exp_date').show();
            $('#show_end_exp_date').show();
        });
    </script>
@endif
@endpush

@endsection
