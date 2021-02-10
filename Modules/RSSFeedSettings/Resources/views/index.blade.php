@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        {{-- <aside id="hide-settings" class="aside aside-md b-r">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#setting-nav">@icon('solid/bars')
                    </a>
                    <a class="hide-setting btn btn-icon btn-default btn-sm pull-right m-r-xs">@icon('solid/bars')</a>
                    <p class="h3 text-elipse-setting">Name Domain</p>
                </header>
                <section class="scrollable">
                    <div class="slim-scroll" data-color="#333333" data-disable-fade-out="true" data-distance="0" data-height="auto" data-size="3px"> 
                    <section id="setting-nav" class="hidden-xs">
                        <ul class="nav nav-pills nav-stacked no-radius">
                            <li class="active">
                                <a href="{{route('rssfeedsettings.index')}}">
        @icon('solid/angle-right', 'text-'.get_option('theme_color'))
        RSS Settings
        </a>
        </li>
        <li>
            <a href="{{route('rssfeedsettings.rss_data')}}">
                @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                RSS Data
            </a>
        </li>
        <li>
            <a href="{{route('rssfeedsettings.news')}}">
                @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                News
            </a>
        </li>
        </ul>
    </section>
    </div>
</section>
</section>
</aside> --}}

<aside>
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
            @icon('solid/arrow-left')
            </a> --}}
            {{-- <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display:none">@icon('solid/bars')</a> --}}
            <div class="bc-head">@langapp('rss_feed_settings')</div>
            {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip"
            title="@langapp('export') CSV">
            @icon('solid/download') CSV
            </a> --}}
            <button type="submit" id="btn_del_select" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete"
                disabled>
                <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt')
                    @langapp('delete')</span>
            </button>
            <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal"
                data-target="#rss_modal">
                @icon('solid/plus') @langapp('create')
            </a>
            {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal"
            data-target="#rss_modal">
            >_ Run Feed
            </a>
            <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal"
                data-target="#rss_modal">
                @ @langapp('keyword')
            </a>
            <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal"
                data-target="#rss_modal">
                @icon('solid/laptop') Scheldue Update
            </a> --}}
        </header>

        <section class="scrollable wrapper">
            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-xs-12">
                            <i class="fas fa-table"></i> Table RSS Feed Settings
                        </div>
                    </div>
                </header>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-rss-setting-template">
                            <thead>
                                <tr>
                                    <th class="hide"></th>
                                    <th class="no-sort">
                                        <label>
                                            <input name="select_all" value="1" id="select-all" type="checkbox"
                                                class="select-chk" />
                                            <span class="label-text"></span>
                                        </label>
                                    </th>
                                    <th>@langapp('name')</th>
                                    <th>URL</th>
                                    {{-- <th>@langapp('keyword')</th>
                                            <th>Interval (Day)</th>
                                            <th>Start Date Feed</th>
                                            <th>Last Date Feed</th>
                                            <th>Data Feed</th>
                                            <th>Data Error</th> --}}
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </section>
    </section>
</aside>
</section>

<a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

<!-- Modal RSS -->
<div class="modal in fixed-left" id="rss_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-aside" role="document">
        <div class="modal-content">
            <div class="modal-header bg-blue">
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                <h4 class="modal-title text-white">
                    <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip"
                        title="Fullscreen" data-placement="right"></i>
                    RSS Feed
                </h4>
            </div>
            {!! Form::open(['route' => ['rssfeedsettings.save'], 'class' => 'ajaxifyForm_custom','files' => false]) !!}
            {{-- <form action=""> --}}
            <div class="modal-body">
                <div class="form-group row">
                    <label class="col-lg-3 control-label">Name <span class="text-danger">*</span> </label>
                    <div class="col-lg-9">
                        <input type="text" id="name_rss" name="name_rss" class="form-control" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 control-label">URL
                        <span data-rel="tooltip" title="" data-original-title="URL"> <i
                                class="far fa-question-circle"></i></span>
                        <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-9">

                        <input type="url" class="form-control" id="url_rss" name="url_rss" value="" required>
                        {{-- <span  class="input-group-btn">
                                                <button type="submit" class="btn btn-info">Copy</button>
                                            </span> --}}
                        {{-- <span><i class="far fa-check-circle fa-2x text-success"></i></span> --}}


                    </div>
                </div>


                {{-- <div class="form-group row">
                            <label class="col-lg-3 control-label">Keywords </label>
                            <div class="col-lg-9">
                                <select name="" id="keywords" class="select2-option form-control" multiple="multiple">
                                    <option value="1">a</option>
                                    <option value="2">b</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 control-label">Interval </label>
                            <div class="col-lg-9">
                                <select name="" id="interval" class="select2-option form-control" multiple="multiple">
                                    <option value="1">a</option>
                                    <option value="2">b</option>
                                </select>
                            </div>
                        </div> --}}

                {{-- <div class="form-group row">
                            <label class="col-lg-3 control-label">Start</label>
                            <div class="col-lg-9">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="input-group date">
                                            <input id="send_date" type="text" class="form-control datetimepicker-input"
                                            value="{{  timePickerFormat(now()->addHours(1)) }}" name="start_date"
                data-date-format="DD-MM-YYYY hh:mm A" data-date-start-date="moment()" required>
                <div class="input-group-addon">
                    @icon('solid/calendar-alt', 'text-muted')
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="checkbox">
                <label style="padding-left: 0;">
                    <input id="set_exp" type="checkbox" name="" value="TRUE">
                    <span class="label-text" data-rel="tooltip" title="">Set an expire date</span>
                </label>
            </div>
        </div>
    </div>
</div>
</div>

<div id="show_end_exp_date" class="form-group row">
    <label class="col-lg-3 control-label">End</label>
    <div class="col-lg-9">
        <div class="input-group date">
            <input id="send_date" type="text" class="form-control datetimepicker-input"
                value="{{  timePickerFormat(now()->addHours(1)) }}" name="start_date"
                data-date-format="DD-MM-YYYY hh:mm A" data-date-start-date="moment()" required>
            <div class="input-group-addon">
                @icon('solid/calendar-alt', 'text-muted')
            </div>
        </div>
    </div>
</div> --}}

<div class="form-group row">
    <label class="col-lg-3 control-label">Status </label>
    <div class="col-lg-6">
        <label class="switch">
            {{-- <input type="hidden" value="FALSE" name=""> --}}
            <input type="checkbox" name="status_rss" value="TRUE" checked>
            <span></span>
        </label>
    </div>
</div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
        <i class="fas fa-times"></i>
        Close
    </button>

    <button type="submit" class="btn btn-info formSaving btn-rounded">
        <i class="fas fa-paper-plane"></i>
        Save
    </button>
</div>
{!! Form::close() !!}
{{-- </form> --}}
</div>
</div>
</div>

<div class="modal" id="delete_rss_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
    aria-hidden="true" style="left: unset">
    <div class="modal-dialog modal-dialog-aside" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">@langapp('delete')</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <p class="text-danger">@langapp('delete_warning') </p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-default btn-rounded" data-dismiss="modal"><i
                        class="fas fa-times text-muted"></i> Close</a>
                <button type="button" class="btn btn-info submit btn-rounded delete_rss_submit"
                    onclick="delete_rss_select_confirm()"><i class="fas fa-paper-plane"></i> OK</button>
            </div>
        </div>
    </div>
</div>

</section>




@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
@include('stacks.css.datepicker')
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.hidesettings');
@include('stacks.js.fullscreen');

<script>
    $(document).ready(function () {
        $('#keywords').select2({
            tags: true,
            tokenSeparators: [' ']
        });

        $('#interval').select2({
            tags: true,
            tokenSeparators: [' ']
        });

        $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') });

        $('#show_end_exp_date').hide();

        $('#set_exp').on('change',function(){
            if($(this).prop('checked')){
                $('#show_end_exp_date').show();
            }else{
                $('#show_end_exp_date').hide();
            }
        });
    });

    
    $('#table-rss-setting-template').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {

            $('#btn_del_select').prop("disabled", false);
        } else {
            
            if ($('.select-chk').filter(':checked').length < 1){

                $('#btn_del_select').attr('disabled',true);
            }
        }
    });

    $('#table-rss-setting-template').on('click', '.rss_id', function () {
        if ($(this).is(':checked')) {
            $('#btn_del_select').prop("disabled", false);
            {{--if($('.rss_id').filter(':checked').length >= 5){
                document.getElementById("select-all").checked = true;
            }--}}
        } else {
            document.getElementById("select-all").checked = false;
            if ($('.rss_id').filter(':checked').length < 1){
                
                $('#btn_del_select').attr('disabled',true);
            }
        }
    }); 

    $(function () {
        

        var table = $('#table-rss-setting-template').DataTable({
            pageLength: 50,
            processing: true,
            serverSide: true,
            destroy: true,
            "dom": '<"d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
            ajax: {
                url: '{!! route('rssfeedsettings.rss_setting_table') !!}',
                data: function ( d ) {
                    {{--d.keywords = keywords;--}}
                    {{--return JSON.stringify( d );--}}
                    return d;
                },
                type: "POST",
            },
            order: [[ 0, "desc" ]],
            columns: [
                { data: 'id', name: 'id' },
                { data: 'chk', name: 'chk', orderable: false, searchable: false, sortable: false, className: 'w-10' },
                { data: 'name', name: 'name' },
                { data: 'url', name: 'url' },
                {
                    data: 'status',
                    name: 'status',
                    className: 'w-25'
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    className: 'w-50 no-wrap'
                }
            ]
        });



    });


    function change_rss_active(code) {
        let checkState = $("#rss-active-" + code).is(":checked") ? 1 : 0;
        axios.post('{{route('rssfeedsettings.change_status')}}', {
            active: checkState,
            code: code,
        }).then(function (response) {
            toastr.success(response.data.message, '@langapp('response_status')');
            window.location.href = response.data.redirect;
        }).catch(function (error) {
            var errors = error.errors;
            var errorsHtml = "";
            $.each(errors, function (key, value) {
                errorsHtml += "<li>" + value[0] + "</li>";
            });
            toastr.error(errorsHtml, '@langapp('response_status')');
        });
    }
    var rss_id_delete_chang =[];

    $( "#btn_del_select" ).click(function() {
        rss_id_delete_chang =[];
        $('#delete_rss_modal').modal('show');
    });
    
    function delete_rss_select_confirm(){

        $('.rss_id:checked').each(function () {
                rss_id_delete_chang.push(this.value);
                
        });

        $.ajax({
            type:"POST",
            url:"{{ route('rssfeedsettings.rss_feed_seting_delete') }}",
            data:{id_chang: rss_id_delete_chang},
            beforeSend: function(){
                $('.delete_rss_submit').html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            },
            success:function(response) {
                $('.delete_rss_submit').html('<i class="fas fa-check"></i> @langapp('save') </span>');
                $('.delete_rss_submit').prop("disabled", true);
                toastr.success(response.message, '@langapp('response_status')');
                window.location.href = response.redirect;
            },
            error: function (error){
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status') ');
            }
    
        });

    }

    var form_save = '.formSaving';
    $('.ajaxifyForm_custom').submit(function (event) {
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
                        $(form_save).html('<i class="fas fa-paper-plane"></i>  @langapp('save') </span>');
                        window.location.href = response.data.redirect;
            })
            .catch(function (error) {
                if(error.response.data.exception){
                    $('.formSaving').attr('disabled',false);
                    toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }else{
                    $('.formSaving').attr('disabled',false);
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