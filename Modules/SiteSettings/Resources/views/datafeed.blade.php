@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">  
        <aside id="hide-settings" class="aside aside-md b-r">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#setting-nav">@icon('solid/bars')
                    </a>
                    <a class="hide-setting btn btn-icon btn-default btn-sm pull-right m-r-xs">@icon('solid/bars')</a>
                    <p class="h3">@langapp('settings')  </p>
                </header>
                <section class="scrollable">
                    <div class="slim-scroll" data-color="#333333" data-disable-fade-out="true" data-distance="0" data-height="auto" data-size="3px"> 
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_data_dark')
                    </section>
                </div>
                </section>
            </section>
        </aside>

        <aside>
            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light">
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display: none">@icon('solid/bars')</a>
                    <div class="bc-head">Data Leak Feed</div>
                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                        @icon('solid/download') CSV
                    </a>
                    <button type="submit" id="button" class="btn btn-sm btn-danger m-xs  pull-right" value="bulk-delete">
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt') @langapp('delete')</span>
                    </button>
                    <button id="btn-change-status" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal" data-target="#change_status" disabled>
                        Change Status
                    </button>
                    <button id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                        <span>@langapp('Search_Advance')</span>
                     </button>
                </header>
                <section class="scrollable wrapper">
                    <section class="panel panel-default" id="hide-advance-search" style="display: none;">
                        <div class="container-fluid" style="padding: 2rem;">
                            <div class="row m-b-md">
                                <div class="col-lg-12">
                                    <div class="row d-flex align-items-center">
                                        <label for="" class="col-sm-1 col-xs-12 col-form-label">Search</label>
                                        <div class="col-sm-11 col-xs-12">
                                            <input type="text" id="search" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="row d-flex align-items-center">
                                        <label for="" class="col-sm-3 col-xs-12 col-form-label">Source</label>
                                        <div class="col-sm-9 col-xs-12">
                                            <select id="source_select" class="form-control">
                                                <option value="">All</option>
                                                @if($DataLeakSocial)
                                                    @foreach($DataLeakSocial as $DataLeakSocial_val)
                                                        <option value="{{$DataLeakSocial_val->id}}">{{$DataLeakSocial_val->source}}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 text-center">
                                    <div id="datafeed_date" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                        <i class="fa fa-calendar"></i>&nbsp;
                                        <span></span> <i class="fa fa-caret-down"></i>
                                    </div>
                                </div>
                                <div class="col-lg-4 text-center">
                                    <div style="margin-top: 8px;">
                                        <label class="mr-3">
                                            <input type="checkbox" name="check_all" id="check_all" value="TRUE">
                                            <span class="label-text" style="font-size: 16px;">All</span>
                                        </label>
                                        <label class="mr-3">
                                            <input type="checkbox" name="check_pending" id="check_pending" value="TRUE">
                                            <span class="label-text" style="font-size: 16px;">Pending</span>
                                        </label>
                                        <label class="mr-3">
                                            <input type="checkbox" name="check_approved" id="check_approved" value="TRUE">
                                            <span class="label-text" style="font-size: 16px;">Approved</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 text-right mt-2">
                                    <button type="button" id="btn_data_leak_search" class="btn btn-info btn-responsive" <!--onclick="table_social_data();-->">
                                        <i class="fas fa-search"></i>
                                        Search
                                    </button>
                                    <button type="button" id="btn_data_leak_reset" class="btn btn-default btn-responsive" style="white-space: nowrap">
                                        <i class="fas fa-broom"></i>
                                        <span> Clear </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    
                    <section class="panel panel-default">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-xs-12">
                                    <i class="fas fa-table"></i> Table Data Leak Feed
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table  class="table table-striped" id="table_data_feed">
                                    <thead>
                                        <tr>
                                            <th class="no-sort w-10">
                                                <label>
                                                    <input name="select_all" value="1" id="select-all" type="checkbox" class="data_feed_id"/>
                                                    <span class="label-text"></span>
                                                </label>
                                            </th>
                                            <th>Source</th>
                                            <th>Keyword Ref</th>
                                            <th>Content</th>
                                            <th>Data Leak Feed</th>
                                            <th>url</th>
                                            <th class="no-sort">@langapp('action')</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </section>
            </section>
        </aside>
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
    <!-- Modal create_assets_vulnerability -->
    <div class="modal in fixed-left" id="change_status" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside size-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Confirm Information</h4>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="" class="col-md-3">Status</label>
                        <div class="col-md-9">
                            <select id="status_action" class="form-control select2">
                                <option value="1">Approved</option>
                                <option value="2">Cancle</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="button" onclick="change_status()" class="btn btn-info btn-rounded">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal in fixed-left" id="confirm-change-status" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside size-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Confirm Information</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="" class="col-md-3">Status</label>
                        <div class="col-md-9">
                            <select id="status_action" class="form-control select2">
                                <option value="1">Approved</option>
                                <option value="2">Cancle</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="" class="col-md-3">Sent mail</label>
                        <div class="col-md-9">
                            <label><input type="checkbox" name="sent_mail" class="" value="true"><span class="label-text">Sent mail to customers</span></label>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="submit" class="btn btn-info btn-rounded" onclick="confirm_approve()">
                        <i class="fas fa-paper-plane"></i>
                        Yes, approve
                    </button>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal in fixed-left" id="confirm-change-status-cancle" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside size-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Confirm Information</h4>
                </div>
                <div class="modal-body">

                    <div class="form-group row">
                        <label for="" class="col-md-3">Status</label>
                        <div class="col-md-9">
                            <select id="status_action" class="form-control select2">
                                <option value="1">Approved</option>
                                <option value="2">Cancle</option>
                            </select>
                        </div>
                    </div>
                    
                    <span class="modal-title">Are you sure you want to cancel this item?</span>
                    <br>
                  
                    {{-- <label><input type="checkbox" name="sent_mail" class="" value="true"><span class="label-text">Sent mail to customers</span></label> --}}

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="button" class="btn btn-info btn-rounded" onclick="confirm_cancle()">
                        <i class="fas fa-paper-plane"></i>
                        Yes, cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

</section>

@push('pagestyle')
    @include('stacks.css.datatables')
    @include('stacks.css.datepicker')
    @include('stacks.css.form')
    <link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css"/>
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.menusub')
@include('stacks.js.hidesettings')
@include('stacks.js.advanced_search')
@include('stacks.js.fullscreen')
<script>
    var search_val = 0;
    var start_date = '';
    var end_date = '';
    var check_all = false;
    var check_pending = false;
    var check_approved = false;

$(function() {
    table_social_data();
});


$(function() { 
    var start = moment().startOf('hour');
    var end = moment().startOf('hour').add(32, 'hour');
    function cb(start, end) {
        $('#datafeed_date span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }
    $('#datafeed_date').daterangepicker({
        timePicker: true,
        startDate: start,
        endDate: end,
        locale: {
            format: 'M/DD hh:mm A'
        },
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);
    cb(start, end);

    $("#btn_data_leak_reset").click(function() {
        search_val = 0;
        $("#search").val('');
        $("#source_select").val('').trigger('change');
        $("#check_all").prop("checked",false);
        $("#check_pending").prop("checked",false);
        $("#check_approved").prop("checked",false);

        cb(moment().startOf('hour'), moment().startOf('hour').add(32, 'hour'));

        table_social_data();
    });

});



$("#btn_data_leak_search").click(function() {
    search_val = 1;
    if ($('#check_all').is(":checked")) {
        check_all = true;
    } else {
        check_all = false;
    }
    if ($('#check_pending').is(":checked")) {
        check_pending = true;
    } else {
        check_pending = false;
    }
    if ($('#check_approved').is(":checked")) {
        check_approved = true;
    } else {
        check_approved = false;
    }
    start_date = $("#datafeed_date").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
    end_date = $("#datafeed_date").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');

    table_social_data();
});



function table_social_data(){
    let search = $('#search').val();
    let source_select = $('#source_select').val();
    $('#table_data_feed').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        "dom": '<"d-flex d-inline-flex justify-content-between"Bf><"top"l>rt<"bottom"ip><"clear">',
        ajax: {
            url: '{!! route('socialdatas.datafeedsocial_datatables') !!}',
            data: {
                "search_val" : search_val,
                "search" : search,
                "source_select" : source_select,
                "start_date" : start_date,
                "end_date" : end_date,
                "check_all" : check_all,
                "check_pending" : check_pending,
                "check_approved" : check_approved,
            },
            type: "POST",
        },
        order: [
            [0, "desc"]
        ],
        columns: [
            {
                data: 'chk',
                orderable: false,
                searchable: false,
                sortable: false,
                className: 'w-10'
            },  
            {
                data: 'source',
                name: 'source'
            },
            {
                data: 'keyword',
                name: 'keyword'
            },
            {
                data: 'content',
                name: 'content'
            },
            {
                data: 'data_feed',
                name: 'data_feed',
                className: 'no-wrap'
            },
            {
                data: 'url',
                name: 'url',
            },
            {
                data: 'action',
                name: 'action'
            },
        ]
    });
}

$('#source_select').select2();
var data_feed_id = [];
$('#table_data_feed').on('click', '.data_feed_id', function () {
    if ($(this).is(':checked')) {
        $('#btn-change-status').prop("disabled", false);
    } else {
        if ($('.data_feed_id').filter(':checked').length < 1){
            $('#btn-change-status').attr('disabled',true);
        }
    }
});



function approve_dataFeed(id){
    data_feed_id = [];
    data_feed_id.push(id);
}

function cancle_dataFeed(id){
    data_feed_id = [];
    data_feed_id.push(id);
}

function confirm_approve(){
    $('.data_feed_id:checked').each(function () {
        data_feed_id.push(this.value);
    });
    $.ajax({
        type:"POST",
        url:"{{ route('socialdatas.approve_data_feed') }}",
        data:{id: data_feed_id},
        beforeSend: function(){
            loading('load');
        },
        success:function(response) {
            loading('stop_load');
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

function confirm_cancle(){
    $('.data_feed_id:checked').each(function () {
        data_feed_id.push(this.value);
    });
    $.ajax({
        type:"POST",
        url:"{{ route('socialdatas.cancle_data_feed') }}",
        data:{id: data_feed_id},
        beforeSend: function(){
            loading('load');
        },
        success:function(response) {
            loading('stop_load');
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

function change_status(){
    let status_action = $('#status_action :selected').val();
    if(status_action == 1){
        confirm_approve();
    }else{
        confirm_cancle();
    }
}

</script>
@endpush
@endsection
