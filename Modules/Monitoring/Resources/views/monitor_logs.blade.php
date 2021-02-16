@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
            @icon('solid/arrow-left')
            </a> --}}
            <div class="bc-head">Monitoring > Application Logs</div>
            <button type="button" id="btn_del_select" class="btn btn-sm btn-danger m-xs  pull-right" value="bulk-delete"
                disabled>
                <span data-rel="tooltip" title="Delete" data-placement="bottom">@icon('solid/trash-alt')
                    <span class="hide-text">@langapp('delete')</span></span>
            </button>
            <a href="#hide-advance-search" id="advance-search" class="pull-right btn btn-sm btn-{{ get_option('theme_color')  }} ">
                <span data-rel="tooltip" title="Filter" data-placement="bottom"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></span>
            </a>
            <div class="pull-right max-w-select" style="margin-top: 9px;">
                <select name="site" id="site" class="select2-option form-control select-site"
                    onchange="changeSite()">
                    <option value="">All Site</option>
                    @if($SiteSettings)
                    @foreach($SiteSettings as $SiteSettings_val)
                    <option value="{{$SiteSettings_val->code}}">{{$SiteSettings_val->name}}</option>
                    @endforeach
                    @endif
                </select>
            </div>

        </header>

        <section id="scrollable_news" class="scrollable wrapper">
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
                            <div class="col-lg-6">
                                <h5 class="font-weight-bold">Keyword</h5>
                                <input type="text" id="Keywords" class="form-control">
                            </div>


                            <div class="col-lg-6">
                                <h5 class="font-weight-bold">Date</h5>
                                <div id="newsrange" class="text-center"
                                    style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-lg-12 text-right">
                            <button type="button" id="btn_news_search"
                                class="btn btn-info btn-responsive btn-fz-13">
                                <i class="fas fa-search"></i>
                                @langapp('apply')
                            </button>
                            <button type="button" id="btn_news_reset"
                                class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                <i class="fas fa-broom"></i>
                                <span> Clear </span>
                            </button>
                            <button type="button" id="close_filter" class="btn btn-default btn-responsive btn-fz-13"
                                style="white-space: nowrap">
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
                            <i class="fas fa-table"></i> Table Logs
                        </div>
                    </div>
                </header>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-monitoring-logs">
                            <thead>
                                <tr>
                                    <th class="no-sort w-10">
                                        <label>
                                            <input name="select_all" value="1" id="select-all" type="checkbox"
                                                class="select-chk" />
                                            <span class="label-text"></span>
                                        </label>
                                    </th>
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
                    <a href="#" class="btn btn-default btn-rounded" id="close" data-dismiss="modal"><i
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
@include('stacks.css.multitext')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.form')
@include('stacks.js.advanced_search')
@include('stacks.js.multitext')


<script>
    var id_select_site = 'site';
    var isDateSearch = 0;
    var isSearch = 0;
    var startDate =  '';
    var endDate = '';
    var Keywords = '';
    var select = '';
    var sitecode = '';
    var count_table = 0;
$(function () {
    
    if(get_cookie_site()){
        cookie_change_site("{{route('systemsetting.check_cookie_site')}}",id_select_site);
    }else{
        data_table();
    }
    

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
    set_cookie_site($(`#${id_select_site}`).val());
    isSearch = 1;
    sitecode = $("#site").val();
    data_table();
}

function data_table(){
    var myTable = $('#table-monitoring-logs').DataTable({
            searching: false,
            ordering: true,
            pageLength: 25,
            processing: true,
            serverSide: true,
            destroy: true,
            order: [[ 5, "desc" ]],
            "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
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
            "fnDrawCallback": function( oSettings ) {
                multi_readmore()
            },
            initComplete : function( settings, json){
                $('[data-toggle="tooltip"]').tooltip();
                var table = $('#table-monitoring-logs').DataTable();
                count_table =table.rows().count();
            },
            columnDefs: [
                {
                    targets: 0,
                    orderable: false,
                    width: '1px',
                    render: function (data, type, row) {

                        return  '<label><input type="checkbox" name="log_id" class="log_id" value="' + row.logs + '"><span class="label-text"></span></label>';
                    },
                },
                {
                    width: '10%',
                    targets: 1,
                    name:"site_name",
                    className :'nowrap',
                    render: function (data, type, row) {
                       if(row.site_name){
                        return row.site_name;
                       }else{
                        return '';
                       }
                        
                    }
                },
                {
                    targets: 2,
                    width: '25%',
                    name:"logs.file",
                    render: function (data, type, row) {
                        if(row.file){
                        return '<div class="text-trucate-ovf">'+row.file+'</div>';
                       }else{
                        return '';
                       }
                    }
                },
                {
                    targets: 3,
                    width: '25%',
                    name:"logs.error_summary",
                    render: function (data, type, row) {
                        if(row.error_summary){
                        return '<div class="text-trucate-ovf">'+row.error_summary+'</div>';
                       }else{
                        return '';
                       }
                    }
                },
                {
                    width: '25%',
                    targets: 4,
                    name:"logs.log_trace",
                    render: function (data, type, row) {
                        if(row.log_trace){
                            return '<div class="text-trucate-ovf">'+row.log_trace+'</div>';
                        }else{
                            return '';
                        }
                    }
                },
                {
                    targets: 5,
                    name:"logs.updated_at",
                    className:"no-wrap",
                    render: function (data, type, row) {
                        return '<p><strong>created_at : </strong>'+row.created_at+'</p><p><strong>updated_at : </strong>'+row.updated_at+'</p>';


                    }
                },
                {
                    targets: 6,
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
                id_change: log_id_delete_change,
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

    $('#table-monitoring-logs').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {

            $('#btn_del_select').prop("disabled", false);
        } else {
            
            if ($('.select-chk').filter(':checked').length < 1){

                $('#btn_del_select').attr('disabled',true);
            }
        }
    });

    $('#table-monitoring-logs').on('click', '.log_id', function () {
        if ($(this).is(':checked')) {
            $('#btn_del_select').prop("disabled", false);

            if($('.log_id').filter(':checked').length >= count_table){

                document.getElementById("select-all").checked = true;
            }
        } else {
            document.getElementById("select-all").checked = false;
            if ($('.log_id').filter(':checked').length < 1){
                
                $('#btn_del_select').attr('disabled',true);
            }
        }
    }); 

    var log_id_delete_change =[];
    $("#btn_del_select").click(function() {
        log_id_delete_change =[];
        $('#delete_logs').modal('show');
        $('.log_id:checked').each(function () {
            log_id_delete_change.push(this.value);         
        });
    });  


</script>
@endpush
@endsection