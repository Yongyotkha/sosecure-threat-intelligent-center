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
                                <a href="{{route('sitesettings.edit', ['id' => 2])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Site Settings
                                </a>
                            </li>
                            <li class="active">
                                <a href="{{route('systemsetting.index')}}">

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
                    <div class="bc-head">Site Setting > ธนาคารออมสิน</div>
                </header>
                <section class="scrollable wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            {!! Form::open(['class' => 'bs-example form-horizontal ajaxifyForm validator']) !!}
                            <section class="panel panel-default">
                            <header class="panel-heading">@icon('solid/cogs') System Details  </header>
                            <div class="panel-body">
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">System Online </label>
                                    <div class="col-lg-3">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Web Online</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
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
                                            value="{{  timePickerFormat(now()->addHours(1)) }}" name="start_date"
                                            data-date-format="DD-MM-YYYY hh:mm A" data-date-start-date="moment()" required>
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
                                            value="{{  timePickerFormat(now()->addHours(1)) }}" name="start_date"
                                            data-date-format="DD-MM-YYYY hh:mm A" data-date-start-date="moment()" required>
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
                                                    <input type="text" class="form-control" name="generate_key" value="" readonly>
                                                    <span class="input-group-btn">
                                                        <button type="submit" class="btn btn-info">Copy</button>  
                                                        <button type="submit" class="btn btn-info">Gen</button>  
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="checkbox">
                                                    <label>
                                                        <input id="set_exp" type="checkbox" name="" value="TRUE">
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
                                            value="{{  timePickerFormat(now()->addHours(1)) }}" name="start_date"
                                            data-date-format="DD-MM-YYYY hh:mm A" data-date-start-date="moment()" required>
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
                                            value="{{  timePickerFormat(now()->addHours(1)) }}" name="start_date"
                                            data-date-format="DD-MM-YYYY hh:mm A" data-date-start-date="moment()" required>
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

</script>
@endpush

@endsection
