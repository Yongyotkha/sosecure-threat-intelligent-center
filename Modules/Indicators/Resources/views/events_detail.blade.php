@php
// dd($otx_events[0]['name']);
@endphp


@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header bg-white b-b b-light">
            @if ($indicator)
            <a href="{{ route('indicators.detail_indicator').'?id='.$indicator_id.'&type='.$type.'&indicator='.$indicator }}"
                class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a>
            <div class="bc-head">{{@$indicator}} > {{@$otx_events[0]['name']}}</div>
            @else
            <a href="{{ route('indicators.events') }}"
                class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a>
            <div class="bc-head">Events > {{@$otx_events[0]['name']}}</div>
            @endif





            <!--<button id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                <span>@langapp('Search_Advance')</span>
            </button>
            <div class="pull-right" style="margin-top: 8px; width: 300px;">
                <select name="site" id="site" class="select2-option form-control select-site" style="min-width: 300px">
                    <option value="">All Site</option>
                    {{-- @if($SiteSettings)
                    @foreach($SiteSettings as $SiteSettings_val)
                    <option value="{{$SiteSettings_val->code}}">{{$SiteSettings_val->name}}</option>
                    @endforeach
                    @endif --}}
                </select>
            </div>-->

        </header>
        <section class="scrollable wrapper">
            <section class="panel panel-default">
                <div class="panel-heading">
                    <a class="text-primary" href="{{ route('indicators.events') }}">Events</a>
                    |
                    <a href="{{ route('indicators.attributes') }}" class="text-muted">Attributes</a>
                </div>
                <div class="container-fluid" style="padding: 2rem">
                    <div class="row">
                        <div class="col-md-6">
                            <h1>{{@$otx_events[0]['name']}}</h1>
                            <p>Last Status : {{check_last_status(@$otx_events[0]['is_modified'])}} | Public :
                                {!!check_publish(@$otx_events[0]['public'])!!}</p>
                            <p>Created : {{change_date_utc_to_thai(@$otx_events[0]['created_at'])}} | Modified :
                                {{change_date_utc_to_thai(@$otx_events[0]['modified'])}}</p>
                            <p>Tags : {!!explode_val(@$otx_events[0]['tags'],'tags')!!}</p>
                            <p>Groups : {!!explode_val(@$otx_events[0]['groups'],'groups')!!}</p>
                        </div>
                        <div class="col-md-6">
                            <h1 class="text-center">Type Attributes {{@$indicator_type_counts}}
                                ({{@$otx_events[0]['indicator_count']}})
                            </h1>
                            <div id="chart-show-bar"></div>
                        </div>
                    </div>
                </div>
            </section>


            <div class="tabbable">
                <ul class="nav nav-tabs nav-tabs-highlight">
                    <li id="tab-attributes" class="active"><a href="#tab_attributes" data-toggle="tab">Attributes
                            ({{@$otx_events[0]['indicator_count']}})</a></li>
                    <li id="tab-event"><a href="#tab_related_event" data-toggle="tab">Related Event
                            ({{@$count_related_pulse}})</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_attributes">
                        <section class="panel panel-default">
                            <div id="main-list" class="row m-b-md">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="table-attributes-template">
                                            <thead>
                                                <tr>
                                                    {{-- <th>
                                                        <label>
                                                            <input name="select_all" value="1" id="select-all"
                                                                type="checkbox" />
                                                            <span class="label-text"></span>
                                                        </label>
                                                    </th> --}}
                                                    <th>TYPE</th>
                                                    <th>Attribute Name</th>
                                                    <th>ROLE</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="showing_amount_text" class="pull-left"
                                        style="margin-top: 5px; margin-left: 15px;"></div>
                                    <div class="pull-right" style="padding-right: 10px;" id="pagination_custom">
                                    </div>
                                </div>
                        </section>
                    </div>
                    <div class="tab-pane" id="tab_related_event">
                        <section class="panel panel-default">
                            <div class="row m-b-md">
                                <div class="col-sm-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="table-related-event">
                                            <thead>
                                                <tr>

                                                    <th>No</th>
                                                    <th>Event Name</th>
                                                    <th>Group</th>
                                                    <th>Tags</th>
                                                    <th>Published</th>
                                                    <th>Last Status</th>
                                                    <th style="width: 200px;">DateTime</th>
                                                    <th>View</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="showing_amount_text_pulse" class="pull-left"
                                        style="margin-top: 5px; margin-left: 15px;"></div>
                                    <div class="pull-right" style="padding-right: 10px;" id="pagination_custom_pulse">
                                    </div>
                                </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
@include('stacks.css.datepicker')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.chart')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.advanced_search')

<script>
    $('.select2-option').select2();

    {{--$('#table-attributes-template').DataTable();
    $('#table-related-event').DataTable();--}}
    count_view_event();
    var pulse_id={!! json_encode($pulse_id) !!};
    var total_page = 0;
    var count_page = -1;
    var count_page2 = -1;
    const chart = new frappe.Chart("#chart-show-bar", { 
        title: "",
        data:{
            labels: {!! json_encode($countKey) !!},
            datasets: [
                { values: {!! json_encode($countVal) !!}}
            ]
        },
        type: 'percentage',
        colors: ['#743ee2']
    });

  

  $(function() {
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

        cb(start, end);
    });

  

    $(function() {
        load_table_attributes();

    });
    
    $(function() {
    
        load_table_pulse();
    });

    function load_table_attributes(){

      
       
        $('#table-attributes-template').DataTable({
            searching: false,
            ordering: false,
            pageLength: 25,
            processing: true,
            serverSide: true,
            destroy: true,
            dom: 'Blfrtip',
            ajax: {
                url: '{!! route('indicators.events_attributes_table')!!}',
                type: "POST",
                data:function(d){
                    d.pulse_id = pulse_id;
                    d.count_page = count_page;                
                },
            },
            initComplete : function( settings, json){
                count_page = json.recordsTotal;
                $('[data-toggle="tooltip"]').tooltip();
            },
            columns: [

                {
                    data: 'TYPE',
                },
                {
                    data: 'Attribute Name',
                },
                {
                    data: 'ROLE',
                },
                {
                    data: 'Date',
                },
                {
                    data: 'Action',
                },

            ],
            columnDefs: [
 
                {
                    targets: 4,
                    render: function (data, type, row) {
                        var inner = '';
                        inner =  '<a href="'+row.Action+'" class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>';
                        return inner;
                    }
                      
                }

            ]
        });
    }

    

  

    function load_table_pulse(){

        $('#table-related-event').DataTable({
            searching: false,
            ordering: false,
            pageLength: 25,
            processing: true,
            serverSide: true,
            destroy: true,
            order: [[ 6, "desc" ]],
            dom: 'Blfrtip',
            ajax: {
                type: "POST",
                url: '{!! route('indicators.events_pulse_table')!!}',
                dataSrc: function ( json ) {
                    count_page2 = json.recordsTotal;
                    return json.data;
                },
                data:function(d){
                    d.pulse_id = pulse_id;
                    d.count_page = count_page2;
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
                    data: 'count_view',
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

    function count_view_event(){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/indicators/count_view_event",
            type: "get",
            data: ({
                pulse_id:{!! json_encode($pulse_id) !!},
            }),
            datatype: "html",
            beforeSend: function(){
            },
        }).done(function(data){
            console.log("sss");
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            console.log("No response from server");
        });
    }


    

            
</script>

@endpush
@endsection