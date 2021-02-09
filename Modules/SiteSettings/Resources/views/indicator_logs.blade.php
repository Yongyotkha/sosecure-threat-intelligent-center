@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">      
        <aside id="hide-settings" class="aside aside-md b-r">
            <section class="vbox">
                @include('partial.header-select-site')
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_site')
                    </section>
                </section>
            </section>
        </aside>
        <aside>
            <section class="vbox">
    
                <header class="header panel-heading bg-white b-b b-light">
                    {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                    @icon('solid/arrow-left')
                    </a> --}}
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display:none;">@icon('solid/bars')</a>
                    <div class="bc-head">@langapp('settings') > Indicators Logs</div>
                    {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip"
                        title="@langapp('export') CSV">
                        @icon('solid/download') CSV
                    </a>
                    <button type="submit" id="button" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt')
                            @langapp('delete')</span>
                    </button>
                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal"
                        data-target="#rss_modal">
                        @icon('solid/plus') @langapp('create')
                    </a> --}}
                </header>

                {{-- <section class="scrollable wrapper">
                    <div class="row">
                        <div class="col-lg-12"></div>
                        <div class="col-lg-12 d-none">
                            {!! Form::open(['route' => ['indisetting.upsert', 'id' => $siteSettings->code], 'class' => 'ajaxifyForm validator', 'novalidate' => '', 'method' => 'PUT', 'files' => true]) !!}
                           
                                <section class="panel panel-default">
                                    <header class="panel-heading accordion">
                                        Config Syslog
                                    </header>
                                    <div class="panel-body panel-accordion">
                                        <div style="padding: 2rem">
                                            <div class="form-group">
                                                <label for="">Syslog <span class="text-danger">*</span></label>
                                                
                                                <input type="text" class="form-control" name="syslog" value="{{@$LogsSetting->link}}" >
                                             </div>
                                             <div class="form-group">
                                                <label for="">IP Address<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="ip_address" value="{{@$LogsSetting->ip}}">
                                             </div>
                                             <div class="form-group">
                                                <label for="">Protocol <span class="text-danger">*</span></label>
                                                <select name="protocal" id="" class="select-option form-control">
                                                    <option value="udp" {{@$LogsSetting->protocal=="udp"?"selected":""}}>udp</option>
                                                    <option value="tcp" {{@$LogsSetting->protocal=="tcp"?"selected":""}}>tcp</option>   
                                                </select>
                                             </div>
                                             <div class="form-group">
                                                 <label for="">Port <span class="text-danger">*</span></label>
                                                 <input type="text" class="form-control" name="port"  value="{{@$LogsSetting->port}}">
                                              </div>
                                        </div>  
                                        <div class="panel-footer bg-white">


                                            <button type="submit" class="btn btn-info submit btn-rounded"  id="btn-submitA"><i
                                                    class="fas fa-paper-plane"></i>
                                                Save
                                            </button>
                                        </div>
                                    </div>
                                    
                                </section>
                  
                            {!! Form::close() !!}

                        </div>
                    </div> --}}
        
                    <div class="row">
                        <div class="col-lg-12">
                            {{-- <form method="" action="" accept-charset="UTF-8" class="bs-example form-horizontal"> --}}
                                {!! Form::open(['route' => ['indisetting.upsertSys', 'id' => $siteSettings->code], 'class' => 'ajaxifyForm_custom1 validator', 'novalidate' => '', 'method' => 'PUT', 'files' => true]) !!}
                                <section class="panel panel-default">
                                    <header class="panel-heading accordion">
                                        Log Format
                                    </header>
                                    <div class="panel-body panel-accordion">
                                        <div style="padding: 2rem">
                                             {{-- <div class="form-group">
                                                <label for="">Protocol <span class="text-danger">*</span></label>
                                                <select name="protocal_format" id="vlogs_protocal_format" class="select-option form-control" value="C">
                                                    <option value="1" {{@$LogsSetting->protocal_format=="1"?"selected":""}}>CEF Format</option>
                                                    <option value="2" {{@$LogsSetting->protocal_format=="2"?"selected":""}}>Syslog Format</option>
                                                </select>
                                             </div> --}}
                                             <div class="form-group">
                                                <label for="">Data Format <span class="text-danger">*</span></label>
                                             </div>

                                             <div class="form-group row">
                                                <div class="col-md-3">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="copy_eventname" name="eventname" value="[[Event name]]" readonly>
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-info" onclick="copy_btn('copy_eventname')">Copy</button>  
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="copy_group" name="group" value="[[Group]]" readonly>
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-info" onclick="copy_btn('copy_group')">Copy</button>  
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="copy_tags" name="tags" value="[[Tags]]" readonly>
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-info" onclick="copy_btn('copy_tags')">Copy</button>  
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="copy_datetime" name="datetime" value="[[DateTime]]" readonly>
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-info" onclick="copy_btn('copy_datetime')">Copy</button>  
                                                        </span>
                                                    </div>
                                                </div>
                                             </div>

                                             <div class="form-group row">
                                                <div class="col-md-3">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="copy_attrcount" name="attrcount" value="[[Attribute Count]]" readonly>
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-info" onclick="copy_btn('copy_attrcount')">Copy</button>  
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="copy_attrname" name="attrname" value="[[Attribute Name]]" readonly>
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-info" onclick="copy_btn('copy_attrname')">Copy</button>  
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="copy_attrtype" name="attrtype" value="[[Attribute Type]]" readonly>
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-info" onclick="copy_btn('copy_attrtype')">Copy</button>  
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" id="copy_attrdatetime" name="attrdatetime" value="[[Attribute DateTime]]" readonly>
                                                        <span class="input-group-btn">
                                                            <button type="button" class="btn btn-info" onclick="copy_btn('copy_attrdatetime')">Copy</button>  
                                                        </span>
                                                    </div>
                                                </div>
                                             </div>

                                             <div class="form-group">
                                                 <textarea name="text_protocal_format" id="text_protocal_format" cols="30" rows="5"  class="form-control">
                                                    {{@$content}}
                                                 </textarea>
                                              </div>
                                        </div>  
                                        <div class="panel-footer bg-white">
                                            {{-- <button type="submit" class="btn btn-info formSaving submit btn-rounded"><i
                                                    class="fas fa-paper-plane"></i>
                                                Save
                                            </button> --}}

                                            <button type="submit" class="btn btn-info submit btn-rounded formSaving1" id="btn-submitB" value="2"><i
                                                class="fas fa-paper-plane"></i>
                                            Save
                                        </button>
                                        </div>
                                    </div>
                                </section>
                                
                            {{-- </form> --}}
                            {!! Form::close() !!}

                        </div>
                    </div>
        
                    <div class="row">
                        <div class="col-lg-12">
                            {!! Form::open(['route' => ['indisetting.indicator_log', 'id' => $siteSettings->code], 'class' => 'ajaxifyForm_custom2 validator', 'novalidate' => '', 'method' => 'PUT', 'files' => true]) !!}
                            {{-- <form method="PUT" action="" accept-charset="UTF-8" class="ajaxifyForm_custom2 bs-example form-horizontal"> --}}
                                <section class="panel panel-default">
                                    <header class="panel-heading accordion">
                                        Send Log
                                    </header>
                                    <div class="panel-body panel-accordion">
                                        <div style="padding: 2rem">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="">Start Date <span class="text-danger">*</span></label>
                                                        <div class="input-group date">
                                                            <input id="start_date" type="text" class="form-control datetimepicker-input"
                                                            value="{{  timePickerFormat(now()->addHours(-1)) }}" name="start_date"
                                                            data-date-format="DD-MM-YYYY hh:mm A" data-date-start-date="moment()" required>
                                                            <div class="input-group-addon">
                                                                @icon('solid/calendar-alt', 'text-muted')
                                                            </div>
                                                        </div>
                                                     </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="">End Date <span class="text-danger">*</span></label>
                                                        <div class="input-group date">
                                                            <input id="end_date" type="text" class="form-control datetimepicker-input"
                                                            value="{{  timePickerFormat(now()->addHours(1)) }}" name="end_date"
                                                            data-date-format="DD-MM-YYYY hh:mm A" data-date-start-date="moment()" required>
                                                            <div class="input-group-addon">
                                                                @icon('solid/calendar-alt', 'text-muted')
                                                            </div>
                                                        </div>
                                                     </div>
                                                </div>
                                            </div>
                                        </div>  
                                        <div class="panel-footer bg-white">

                                             {{-- <button type="submit" class="btn btn-info formSaving submit btn-rounded" id="btn-submit2"><i
                                                    class="fas fa-paper-plane"></i>
                                                Send Log
                                            </button>  --}}
                                            <span id="tooltip_span" data-rel="tooltip" {!!$LogsSentBTN!==1?' title="'.$LogsSentMessage.'"':'title=""'!!}>
                                                <button type="submit" class="btn btn-info submit btn-rounded formSaving2" id="btn-submitC" {!!$LogsSentBTN!==1?'disabled style="pointer-events: none;"':''!!}><i
                                                    class="fas fa-paper-plane"></i>
                                                    Send Log
                                                </button> 
                                            </span>
                                        </div>
                                    </div>
                                </section>
                            </form>
                        </div>
                    </div>
                </section>
            </section>
        </aside>
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>


@push('pagestyle')
@include('stacks.css.datepicker')
@endpush

@push('pagescript')
@include('stacks.js.datepicker')
@include('stacks.js.menusub')
@include('stacks.js.site_hidesettings')

@push('pagestyle')
@include('stacks.css.form')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.fullscreen')
{{-- @include('partial.ajaxify') --}}
@endpush

<script>
$('[data-toggle="tooltip"]').tooltip();
function copy_btn(id){
    var copyText = document.getElementById(id);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    toastr.success("Copied", '@langapp('response_status')');
}

$('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') });

var acc = document.getElementsByClassName("accordion");
var i;

for (i = 0; i < acc.length; i++) {
  acc[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var panel = this.nextElementSibling;
    if (panel.style.display === "none") {
      panel.style.display = "block";
    } else {
      panel.style.display = "none";
    }
  });
}

$(document).ready(function() {

    $("#btn-submitA").click(function(){
        $( "#btn-submitA" ).addClass( "formSaving" );
        $( "#btn-submitB" ).removeClass( "formSaving" );
    }); 

    $("#btn-submitB").click(function(){
        $( "#btn-submitB" ).addClass( "formSaving" );
        $( "#btn-submitA" ).removeClass( "formSaving" );
    });

    $("#btn-submitC").click(function(){
        $( "#btn-submitC" ).addClass( "formSaving" );
        {{--$( "#btn-submitA" ).removeClass( "formSaving" );--}}
    });
    
    $('#vlogs_protocal_format').change(function() {
        if($(this).val() == 1){
            $("#text_protocal_format").html('CEF:0|SOSecure|INDICATOR|1.0|101|$Event_Name|1| dst=$ip_dst dhost=$Destination_Hostname dvchost=$Site cs1Label=$INDICATOR_ID cs1=$INDICATOR_ID_value cs2Label=$INDICATOR_Vendor cs2=$INDICATOR_Vendor_value cs3Label=$INDICATOR_Description cs3=$INDICATOR_Description_value requestUrl=$INDICATOR_URL'); 
        }else{
            $("#text_protocal_format").html('SYS FORMAT'); 
        }
    });


});


$('.ajaxifyForm_custom1').submit(function (event) {
    let form_save = '.formSaving1';
    event.preventDefault();
    $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
    $('.formSaving').attr('disabled',true);
    var data = new FormData(this);
    if(form_save == '.formSavingAndRun'){
        data.append('formsubmit', 'formSavingAndRun');
    }else if(form_save == '.formPreview'){
        data.append('formsubmit', 'formPreview');
    }else if(form_save == '.formDraft'){
        data.append('formsubmit', 'formDraft');
    }
    axios.post($(this).attr("action"), data)
        .then(function (response) {
                toastr.success(response.data.message, '@langapp('response_status') ');
                $(form_save).html('<i class="fas fa-check"></i> @langapp('save') </span>');
                window.location.href = response.data.redirect;
    })
    .catch(function (error) {
        $('.formSaving').attr('disabled',false);
        if(error.response.data.exception){
            toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
            $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
        }else{
            var errors = error.response.data.errors;
            var errorsHtml= '';
            $.each( errors, function( key, value ) {
                errorsHtml += '<li>' + value[0] + '</li>'; 
            });
            toastr.error( errorsHtml , '@langapp('response_status') ');
            $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
        }
    }); 
});

$('.ajaxifyForm_custom2').submit(function (event) {
    let form_save = '.formSaving2';
    event.preventDefault();
    $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
    var data = new FormData(this);
    if(form_save == '.formSavingAndRun'){
        data.append('formsubmit', 'formSavingAndRun');
    }else if(form_save == '.formPreview'){
        data.append('formsubmit', 'formPreview');
    }else if(form_save == '.formDraft'){
        data.append('formsubmit', 'formDraft');
    }
    axios.post($(this).attr("action"), data)
        .then(function (response) {
                $('#btn-submitC').prop('disabled', true);
                $('#tooltip_span').attr('data-original-title', '{{$LogsSentMessage}}');
                $("#btn-submitC").css("pointer-events","none");
                toastr.success(response.data.message, '@langapp('response_status') ');
                $(form_save).html('<i class="fas fa-check"></i> @langapp('save') </span>');

                {{--window.location.href = response.data.redirect;--}}
    })
    .catch(function (error) {
        if(error.response.data.exception){
            toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
            $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
        }else{
            var errors = error.response.data.errors;
            var errorsHtml= '';
            $.each( errors, function( key, value ) {
                errorsHtml += '<li>' + value[0] + '</li>'; 
            });
            toastr.error( errorsHtml , '@langapp('response_status') ');
            $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
        }
    }); 
});


</script>


@endpush
@endsection
