@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">

        <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
            <div class="header-flex-overflow m-t-10">
                <div class="fwb-16">
                    <span>
                        Monitoring > Schedule Task
                    </span>
                </div>

                <div class="ml-2 text-right">
                    <div class="max-w-select">
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

                    <a href="#hide-advance-search" id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} m-l-xs">
                        <span data-rel="tooltip" title="Filter" data-placement="bottom"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></span>
                    </a>

                    <button type="button" id="btn_del_select" class="btn btn-sm btn-danger" value="bulk-delete"disabled>
                    <span data-rel="tooltip" title="Delete" data-placement="bottom">@icon('solid/trash-alt')
                        <span class="hide-text">@langapp('delete')</span></span>
                    </button>
                 
                </div>
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
                            <div class="col-lg-4 col-md-6">
                                <h5 class="font-weight-bold">Keywords</h5>
                                <input type="text" id="Keywords" class="form-control">
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <h5 class="font-weight-bold">Progress</h5>
                                <select id="select_val" class="select2-option form-control">
                                    <option value="" selected>All</option>
                                    <option value="0" >Not Working</option>
                                    <option value="1" >Waiting</option>
                                    <option value="2" >Progress</option>
                                </select>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <h5 class="font-weight-bold">Date</h5>
                                <div id="newsrange" class=" text-center" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
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
            </section>

            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-xs-12">
                            <i class="fas fa-table"></i> Table Event
                        </div>
                    </div>
                </header>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-monitoring-batchjob">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Mode</th>
                                    <th>Progress</th>
                                    <th>Transaction Last Start</th>
                                    <th>Transaction Last End</th>
                                    <th>Site</th>
                                    <th>Message</th>
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
    var id_select_site = 'site';
    var isDateSearch = 0;
    var isSearch = 0;
    var startDate =  '';
    var endDate = '';
    var Keywords = '';
    var select = '';
    var sitecode = '';
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
        select = $("#select_val").val();
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
    var myTable = $('#table-monitoring-batchjob').DataTable({
            searching: false,
            ordering: true,
            pageLength: 25,
            processing: true,
            serverSide: true,
            destroy: true,
            order: [[ 5, "desc" ], [ 0, "asc" ]],
            "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
            ajax: {
                type: "POST",
                url: '{!! route('monitoring.tableMonitor')!!}',
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
            columns: [
                {
                    name:'transaction_batchjob.name',
                    data: 'name',
                },
                {
                    name:'transaction_batchjob.mode',
                    data: 'mode',
                },
                {
                    name:'transaction_batchjob.progress',
                    data: 'progress',
                },
                {
                    name:'transaction_batchjob.transcation_date_start',
                    data: 'transcation_date_start',
                },
                {
                    name:'transaction_batchjob.transcation_date_end',
                    data: 'transcation_date_end',
                },
                {
                    name:'site.name',
                    data: 'site_id',
                    className : 'nowrap'
                },
                {
                    name:'transaction_batchjob.message',
                    data: 'message',
                    className : 'text-center'
                },
            ],
            columnDefs: [
                {
                    targets: 2,
                    render: function (data, type, row) {
                        let inner = '';
                        if(row.progress == 0){
                            inner = '';
                            inner = '<span class="badge badge-danger" style="background-color: #ea2e49;">Not Working</span';
                        }else if(row.progress == 1){
                            inner = '';
                            inner = '<span class="badge badge-wait" style="background-color: #ea2e49;">Waiting</span';
                        }else if(row.progress == 2){
                            inner = '';
                            inner = '<span class="badge badge-success" style="background-color: #ea2e49;">Progress</span';
                        }else if(row.progress == 3){
                            inner = '';
                            inner = '<span class="badge badge-success" style="background-color: #00b303;">Complete</span';
                        }else{
                            inner = '';
                            inner = '<span class="badge badge-none" style="background-color: #ea2e49;">Unknow</span';
                        }
                        return inner;
                    }
                },
            ]

        });

    }
</script>
@endpush
@endsection