@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header bg-white b-b b-light">
            <div class="bc-head">Events</div>

            {{-- <button id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
            <span>@langapp('Search_Advance')</span>
            </button> --}}

            <a id="advance-search" href="#area_search"
                class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                <span><i class="fas fa-filter"></i> @langapp('Search_Advance')</span>
            </a>
            {{-- <a id="to_top" href="#area_search" class="">test</a> --}}
            <div class="pull-right" style="margin-top: 9px;min-width: 270px;">
                <select name="site" id="site" class="select2-option form-control select-site" style="min-width: 270px;">
                    <option value="">All Site</option>
                    @if($SiteSettings)
                    @foreach($SiteSettings as $SiteSettings_val)
                    <option value="{{$SiteSettings_val->code}}">{{$SiteSettings_val->name}}</option>
                    @endforeach
                    @endif
                </select>
            </div>

        </header>
        <section class="scrollable wrapper">
            <section id="hide-advance-search" class="panel panel-default" style="display: none;">
                {{-- <div class="panel-heading">
                    <a class="text-primary" href="{{ route('indicators.events') }}">Events</a>
                    |
                    <a href="{{ route('indicators.attributes') }}" class="text-muted">Attributes</a>
                </div> --}}
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-md-12">
                            <i class="fas fa-filter"></i> Filter
                        </div>
                </header>
                <div class="panel-body" style="padding: 0 !important">
                    <div class="container-fluid" style="padding: 2rem;">
                        <div class="row">
                            <div class="col-md-6 mb-1">
                                <h5 class="font-weight-bold">Event Name</h5>
                                <input type="text" class="form-control" name="event_name" id="event_name"  placeholder="Search">
                            </div>
                            <!--<div class="col-md-4">
                                <div class="form-group">
                                    <label for="" class="">Group</label>
                                    {{-- <select name="group[]" id="type" class="select2-option form-control"
                                        multiple="multiple">

                                    </select> --}}
                                    <input type="text" class="form-control" name="group" id="group" placeholder="Search">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="" class="">Tag</label>
                                    {{-- <select name="tag[]" id="tag" class="select2-option form-control" multiple="multiple"> --}}
                                        <input type="text" class="form-control" name="tag" id="tag" placeholder="Search">
                                    </select>
                                </div>
                            </div>-->
                            <div class="col-md-6 mb-1">
                                <h5 class="font-weight-bold">Date</h5>
                                <div id="event_date" class="text-center form-control"
                                    style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-1">
                                <h5 class="font-weight-bold">Published</h5>
                                <div id="groupby-published" class="btn-group special">
                                    <button class="btn btn-grey active">
                                        <span> All Published</span>
                                    </button>
                                    <button class="btn btn-grey">
                                        <span> No Published </span>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <button class="btn btn-info" id="btn_search_data">
                                <i class="fas fa-search btn-fz-13"></i>
                                <span> @langapp('apply') </span>
                            </button>
                            <button class="btn btn-default btn-fz-13" id="btn_reset">
                                <i class=" fas fa-broom"></i>
                                <span> Clear </span>
                            </button>
                            <button class="btn btn-default btn-fz-13" id="close_filter">
                                <i class=" fas fa-times"></i>
                                <span> Close </span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <div class="container-fluid" style="margin-bottom:10px;">
                <div class="row">
                    <div class="col-md-4">
                        <div class="row">
                            <div class="col-md-12 nopadding">
                                <div class="card-ev">
                                    <div class="header-ev">
                                        Events
                                    </div>
                                    <div class="card-ev-body">
                                        <div class="ev-left">
                                            <span>{{ @number_format($attr_all->event_count) }}</span>
                                            <span class="ev-text-sec">All</span>
                                        </div>
                                        <div class="ev-right">
                                            <span class="cl-orange">{{ @number_format($attr_current->event_count) }}</span>
                                            <span>New Event</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 nopadding">
                                <div class="card-ev">
                                    <div class="header-ev">
                                        Attribute
                                    </div>
                                    <div class="card-ev-body">
                                        <div class="ev-left">
                                            <span>{{ @number_format($attr_all->attribute_count) }}</span>
                                            <span class="ev-text-sec">All</span>
                                        </div>
                                        <div class="ev-right">
                                            <span class="cl-orange">{{ @number_format($attr_current->attribute_count) }}</span>
                                            <span>New Attribute</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
              
                    <div class="col-md-8 nopadding">
                        <div class="" style="background: #fff">
                            <span class="header-txt-chart">Top 10 Attribute Type</span>
                            <div id="chart-pack" style="height: 251px"></div>
                        </div>
                    </div>
                </div>
            </div>

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
                        <table class="table table-striped" id="table_events">
                            <thead>
                                <tr>

                                    <th>No</th>
                                    <th>Event Name</th>
                                    <th>Group</th>
                                    <th>Tags</th>
                                    <th>Published</th>
                                    <th>Last Status</th>
                                    <th style="width: 200px;">DateTime</th>
                                    <th>Attribute</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- <tr>
                                    <td>
                                        <label>
                                            <input value="" type="checkbox" />
                                            <span class="label-text"></span>
                                        </label>
                                    </td>
                                    <td>1</td>
                                    <td>Suspicious proxy agent</td>
                                    <td>
                                        <a href="">MIST FEED</a>
                                        <a href="">Phishing,UW</a>
                                    </td>
                                    <td>
                                        <a href="">Scan,Agent,</a>
                                        <a href="">Proxy,Spider</a>
                                    </td>
                                    <td>
                                        <a href="">5421</a>
                                    </td>
                                    <td>
                                        <i class="fas fa-check"></i>
                                    </td>
                                    <td>
                                        Modified
                                    </td>
                                    <td>
                                        2020-12-07 11:11
                                    </td>
                                    <td>
                                        152
                                    </td>
                                    <td>
                                        <a href="{{ route('indicators.events_detail') }}" class="btn btn-xs
                                btn-info"><i class="far fa-eye"></i> View</a>
                                </td>
                                </tr> --}}
                            </tbody>
                        </table>
                        <div id="showing_amount_text" class="pull-left" style="margin-top: 5px; margin-left: 15px;">
                        </div>
                        <div class="pull-right" style="padding-right: 10px;" id="pagination_custom"></div>
                    </div>
                </div>
            </section>

        </section>
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
@include('stacks.css.datepicker')
@include('stacks.css.highchart')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.highchart')
@include('stacks.js.daterangpicker')
@include('stacks.js.advanced_search')
<script src="{{ getAsset('plugins/Highcharts-Stock/code/modules/timeline.js') }}"></script>
@include('stacks.js.activebutton')

<script>

active_btn('#groupby-published .btn-grey');

Highcharts.setOptions({
    lang: {
      decimalPoint: '.',
      thousandsSep: ','
    }
});


    $('.select2-option').select2();

    var start_date = '';
    var end_date = '';
    var f_search= 1;
    var event_name = '';
    var count_page = -1;
    var isDateSearch = 0;
    var datatable = [];

  $(function() {
    var chart = new Highcharts.chart('chart-pack', {
        chart: {
            type: 'bar',
            height: '251px'
        },
        title: {
            text: null
        },
        xAxis: {
            categories: ['Attribute']
        },
        yAxis: {
            min: 0,
            title: {
            text: null
            }
        },
        legend: {
            reversed: true
        },
        plotOptions: {
            series: {
            stacking: 'normal'
            }
        },
        series: load_graph()
    });

        var start = moment().startOf('hour');
        var end = moment().startOf('hour').add(32, 'hour');

        function cb(start, end) {
            $('#event_date span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            startDate = start;
            endDate = end;
        }

        $('#event_date').daterangepicker({
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
        $('#event_date').on('apply.daterangepicker', function(ev, picker) {
            isDateSearch = 1;
            if (!picker.startDate.isValid() || !picker.endDate.isValid()) {
              
            }
        });

        cb(start, end);

        $("#btn_search_data").click(function() {
            {{--console.log(startDate.format('YYYY-MM-DD hh:mm A'));--}}
           


            start_date = startDate;
            end_date = endDate;
            event_name = $("#event_name").val();
            search_table(1);
        });


        $("#btn_reset").click(function() {
            $("#event_name").val('');
      
            start = moment();
            end = moment();
            cb(start, end);
            load_table(1);


        });


    });

    $(function() {

        if({!!json_encode($Search_Link_All)!!}===""){
            load_table(1);
        }else{
            event_name = {!!json_encode($Search_Link_All)!!};
            search_table(1);
        }
       
  
    });

    function load_graph() {
        
        var graph = {!!json_encode(@$attr_type)!!};
        return graph;

    }

    function load_table(page=1){
        $('#table_events').DataTable({
            searching: false,
            ordering: true,
            pageLength: 25,
            processing: true,
            serverSide: true,
            destroy: true,
            order: [[ 6, "desc" ]],
            dom: 'Blfrtip',
            ajax: {
                type: "POST",
                url: '{!! route('indicators.events_table')!!}',
                dataSrc: function ( json ) {
                    count_page = json.recordsTotal;
                    return json.data;
                },
                data:function(d){
                    
                    d.count_page = count_page;
                }
            },
            initComplete : function( settings, json){
                datatable = json.cursor;
                $('[data-toggle="tooltip"]').tooltip();
            },

            columns: [

                {
                    data: 'No',
                    orderable: false,
                    searchable: false,
                    sortable: false,
                },
                {
                    data: 'name',
                },
                {
                    data: 'groups',
                },
                {
                    data: 'tags',
                },
                {
                    data: 'public',
                },
                {
                    data: 'is_modified',
                },
                {
                    data: 'modified',
                },
                {
                    data: 'attrCount',
                },
                {
                    data: 'pulse_id',
                    orderable: false,
                    searchable: false,
                    sortable: false,
                },

            ],
            columnDefs: [
                {
                    targets: 1,
                    render: function (data, type, row) {
                        var inner = '';
                        inner =  '<a href="{{route('indicators.events_detail')}}'+'/'+row.pulse_id+'">'+row.name+'</a>';
                        return inner;
                    }
                      
                },
                {
                    targets: 4,
                    render: function (data, type, row) {
                        var inner = '';
                        if(row.public==1) {
                            inner = '<i class="fas fa-check"></i>';
                        } else {
                            inner = '<i class="fas fa-times"></i>';
                        }
                        return inner;
                    }
                      
                },
                {
                    targets: 5,
                    render: function (data, type, row) {
                        var inner = '';
                        if(row.is_modified == true) {
                            inner = 'Modified';
                        } else {
                            inner = 'Created';
                        }
                        return inner;
                    }
                      
                },
                {
                    targets: 8,
                    render: function (data, type, row) {
                        var inner = '';
                        inner =  '<a href="{{route('indicators.events_detail')}}'+'/'+row.pulse_id+'" class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>';
                        return inner;
                    }
                      
                }
            ]
        });

    }

    
    function search_table(page=1){
        let startDate=  $("#event_date").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
        let endDate=  $("#event_date").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');
        $('#table_events').DataTable({
            searching: false,
            ordering: true,
            pageLength: 25,
            processing: true,
            serverSide: true,
            destroy: true,
            order: [[ 6, "desc" ]],
            dom: 'Blfrtip',
            ajax: {
                type: "POST",
                url: '{!! route('indicators.events_table')!!}',
                dataSrc: function ( json ) {
                   
                    count_page = json.recordsTotal;
                    return json.data;
                },
                data:function(d){
                    d.count_page = count_page;
                    d.startDate = startDate;
                    d.endDate = endDate;
                    d.f_search = f_search;
                    d.keywords = event_name;
                    d.isDateSearch = isDateSearch;
                }
            },
            initComplete : function( settings, json){
                datatable = json.cursor;
                $('[data-toggle="tooltip"]').tooltip();
            },

            columns: [

                {
                    data: 'No',
                    orderable: false,
                    searchable: false,
                    sortable: false,
                },
                {
                    data: 'name',
                },
                {
                    data: 'groups',
                },
                {
                    data: 'tags',
                },
                {
                    data: 'public',
                },
                {
                    data: 'is_modified',
                },
                {
                    data: 'modified',
                },
                {
                    data: 'attrCount',
                },
                {
                    data: 'pulse_id',
                    orderable: false,
                    searchable: false,
                    sortable: false,
                },

            ],
            columnDefs: [
                {
                    targets: 1,
                    render: function (data, type, row) {
                        var inner = '';
                        inner =  '<a href="{{route('indicators.events_detail')}}'+'/'+row.pulse_id+'">'+row.name+'</a>';
                        return inner;
                    }
                      
                },
                {
                    targets: 4,
                    render: function (data, type, row) {
                        var inner = '';
                        if(row.public==1) {
                            inner = '<i class="fas fa-check"></i>';
                        } else {
                            inner = '<i class="fas fa-times"></i>';
                        }
                        return inner;
                    }
                      
                },
                {
                    targets: 5,
                    render: function (data, type, row) {
                        var inner = '';
                        if(row.is_modified == true) {
                            inner = 'Modified';
                        } else {
                            inner = 'Created';
                        }
                        return inner;
                    }
                      
                },
                {
                    targets: 8,
                    render: function (data, type, row) {
                        var inner = '';
                        inner =  '<a href="{{route('indicators.events_detail')}}'+'/'+row.pulse_id+'" class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>';
                        return inner;
                    }
                      
                }

            ]

        });

    }

   


    

    

</script>

@endpush
@endsection