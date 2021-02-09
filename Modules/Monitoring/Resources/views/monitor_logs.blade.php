@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a> --}}
            <div class="bc-head">@langapp('monitoring') > Logs</div>

            <a href="#hide-advance-search"  id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                <span><i class="fas fa-filter"></i> @langapp('Search_Advance')</span>
            </a>
             <div class="pull-right" style="margin-top: 8px; width: 300px;">
                <select name="site" id="site" class="select2-option form-control select-site" style="min-width: 300px" onchange="changeSite()">
                    <option value="">All Site</option>
                    @if($SiteSettings)
                        @foreach($SiteSettings as $SiteSettings_val)
                            <option value="{{$SiteSettings_val->code}}">{{$SiteSettings_val->name}}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </header>

        <section id="scrollable_news" class="scrollable wrapper" >
            <section class="panel panel-default" id="hide-advance-search" style="display: none">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-md-12">
                            <i class="fas fa-filter"></i> Filter
                        </div>
                </header>
                <div class="panel-body" style="padding: 0 !important">
                    <div class="container-fluid" style="padding: 2rem;">
                        <div class="row m-b-md">
                            <div class="col-lg-12">
                                <div class="row d-flex align-items-center">
                                    <label for="" class="col-sm-1 col-xs-12 col-form-label">Keywords</label>
                                    <div class="col-sm-11 col-xs-12">
                                        <input type="text" id="Keywords" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-5 text-center" style="padding-left: 97px">
                                <div id="newsrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div>
                            </div>
                            <div class="col-lg-4 text-center">
                                <div style="margin-top: 8px;">
                                    
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 text-right mt-2">
                                <button type="button" id="btn_news_search" class="btn btn-info btn-responsive btn-fz-13">
                                    <i class="fas fa-search"></i>
                                    @langapp('apply')
                                </button>
                                <button type="button" id="btn_news_reset" class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
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
                </div>
            </section>

            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-xs-12">
                            <i class="fas fa-table"></i> Table Logs
                        </div>
                    </div>
                </header>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-monitoring-batchjob">
                            <thead>
                                <tr>
                                    <th>Site</th>
                                    <th>File</th>
                                    <th>Error Summary</th>
                                    <th>Log Trace</th>
                                    <th>Date</th>
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
    <div class="modal" id="delete_logs" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"
    style="left: unset">
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
                <button type="button" class="btn btn-info submit btn-rounded delete_webdefacement_submit"
                    onclick="delete_logs_click()"><i class="fas fa-paper-plane"></i> OK</button>
            </div>
        </div>
    </div>
</div>
    {{-- <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a> --}}
    
</section>

@push('pagestyle')
    @include('stacks.css.datatables')
    @include('stacks.css.datepicker')
    @include('stacks.css.form')
    <link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css"/>
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.form')
@include('stacks.js.advanced_search')


<script>
    var isDateSearch = 0;
    var isSearch = 0;
    var startDate =  '';
    var endDate = '';
    var Keywords = '';
    var select = '';
    var sitecode = '';
$(function () {
    
    
    data_table();

    var start = moment();{{--moment().startOf('hour')--}} {{--moment().subtract(1, 'year').startOf('year')--}}
    var end = moment();{{--moment().startOf('hour').add(32, 'hour')--}} {{--moment().subtract(0, 'year').endOf('year')--}}
    

    $('#newsrange').daterangepicker({
        timePicker: true,
        {{--timePicker24Hour: true,--}}
        startDate: start,
        endDate: end,
        locale: {
            format: 'M/DD hh:mm A'{{--format: 'M/DD HH:mm A'--}}
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

    $('#newsrange').on('apply.daterangepicker', function(ev, picker) {
        isDateSearch = 1;
        console.log(isDateSearch);
        if (!picker.startDate.isValid() || !picker.endDate.isValid()) {
            
        }
    });

    $("#btn_news_search").click(function() {
        isSearch = 1;
        startDate=  $("#newsrange").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
        endDate=  $("#newsrange").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');
        Keywords = $("#Keywords").val();
        sitecode = $("#site").val();
        data_table();

    });


    $("#btn_news_reset").click(function() {
        $("#Keywords").val('');
        $("#select_val").val('').trigger("change");
        $("#site").val('').trigger("change");

        isSearch = 0;
        isDateSearch = 0;
        var startDate =  '';
        var endDate =  '';
        start = moment();
        end = moment();
        cb(start, end);

        startDate=  '';
        endDate=  '';
        Keywords = '';
        select = '';
        sitecode = '';
        data_table();

    });
});


function cb(start, end) {
    $('#newsrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
}

function changeSite(){
    isSearch = 1;
    sitecode = $("#site").val();
    data_table();
}

function data_table(){
    var myTable = $('#table-monitoring-batchjob').DataTable({
            searching: false,
            ordering: true,
            pageLength: 25,
            processing: true,
            serverSide: true,
            destroy: true,
            order: [[ 5, "desc" ], [ 0, "asc" ]],
            dom: 'Blfrtip',
            ajax: {
                type: "POST",
                url: '{!! route('monitoring.table_monitor_logs')!!}',
                dataSrc: function ( json ) {
                    return json.data;
                },
                data:function(d){
                    d.isSearch = isSearch;
                    d.isDateSearch = isDateSearch;
                    d.startDate = startDate;
                    d.endDate = endDate;
                    d.Keywords = Keywords;
                    d.select = select;
                    d.sitecode = sitecode;
                }
            },
            initComplete : function( settings, json){
                $('[data-toggle="tooltip"]').tooltip();
            },
            columnDefs: [
                {
                    targets: 0,
                    name:"site_id",
                    render: function (data, type, row) {
                       if(row.site_id){
                        return row.site_id;
                       }else{
                        return '';
                       }
                        
                    }
                },
                {
                    targets: 1,
                    name:"logs.file",
                    render: function (data, type, row) {
                        if(row.file){
                        return row.file;
                       }else{
                        return '';
                       }
                    }
                },
                {
                    targets: 2,
                    name:"logs.error_summary",
                    render: function (data, type, row) {
                        if(row.error_summary){
                        return '<div class="text-elip" data-rel="tooltip" title="'+row.error_summary+'">'+row.error_summary+'</div>';
                       }else{
                        return '';
                       }
                    }
                },
                {
                    targets: 3,
                    name:"logs.log_trace",
                    render: function (data, type, row) {
                        if(row.log_trace){
                        return '<div class="text-elip" data-rel="tooltip" title="'+row.log_trace+'">'+row.log_trace+'</div>';
                       }else{
                        return '';
                       }
                    }
                },
                {
                    targets: 4,
                    name:"logs.created_at",
                    className:"no-wrap",
                    render: function (data, type, row) {
                        return '<p><strong>created_at : </strong>'+row.created_at+'</p><p><strong>updated_at : </strong>'+row.updated_at+'</p>';


                    }
                },
                {
                    targets: 5,
                    orderable: false,
                    render: function (data, type, row) {
                        return`<a href="#" onclick="delete_logs_data(${row.logs})" class="btn btn-danger btn-xs"  data-toggle="modal" data-target="#delete_logs"><i class="fas fa-trash-alt"></i></a>`;
                     
                    }
                },
            ]

        });

    }

    var log_id_delete = null;
    function delete_logs_data(id){
        log_id_delete = id;
    }

    function delete_logs_click () {
        $.ajax({
             type:"POST",
            url:"{{ route('monitoring.delete_logs') }}",
            data:{
                id: log_id_delete,
            },
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
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
</script>
@endpush
@endsection