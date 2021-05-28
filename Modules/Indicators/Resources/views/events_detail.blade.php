@php
// dd($otx_events[0]['name']);
@endphp


@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">

        <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
            <div class="header-flex-overflow m-t-10">
                <div class="fwb-16">
                        @if ($indicator)
                            <a href="{{ route('indicators.detail_indicator').'?id='.$indicator_id.'&type='.$type.'&indicator='.$indicator }}"
                            class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive m-r-5">
                            @icon('solid/arrow-left')
                        </a>
                          <span>{{@$indicator}} > {{@$otx_events[0]['name']}} </span>
                        @else
                            <a href="{{ route('indicators.events') }}"
                            class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive m-r-5">
                            @icon('solid/arrow-left')
                        </a>
                         <span>Events > {{@$otx_events[0]['name']}} </span>
                    @endif
                </div>
            </div>
        </header>


        <section class="scrollable wrapper">
            <section class="panel panel-default">
                <div class="panel-heading">
                    <a class="text-primary" href="{{ route('indicators.events') }}">Events</a>
                    {{-- |
                        <a href="{{ route('indicators.attributes') }}" class="text-muted">Attributes</a> --}}
                    </div>
                    <div class="container-fluid" style="padding: 2rem">
                        <div class="row">
                            <div class="col-md-6">
                                <h1>{{@$otx_events[0]['name']}}</h1>
                                <p>Last Status : {{check_last_status(@$otx_events[0]['is_modified'])}} | Public :
                                {!!check_publish(@$otx_events[0]['public'])!!}</p>

                                {{-- <p>Created : {{change_date_utc_to_thai(@$otx_events[0]['created_at'])}} | Modified :
                                {{change_date_utc_to_thai(@$otx_events[0]['modified'])}}</p>--}}

                                <p>Tags : {!!explode_val(@$otx_events[0]['tags'],'tags')!!}</p>
                                <p>Groups : {!!explode_val(@$otx_events[0]['groups'],'groups')!!}</p>
                                <p>Industries : {!!explode_val(@$otx_events[0]['industries'],'industries')!!}</p>
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
                        <li id="tab-attributes" class="active">
                            <a href="#tab_attributes" data-toggle="tab">
                                Attributes  ({{@$otx_events[0]['indicator_count']}})
                            </a>
                        </li>
                        <li id="tab-attributes">
                            <a href="#tab_malware" data-toggle="tab">
                                Malware ( <span id="number_malware">0</span> )
                            </a>
                        </li>
                        <li id="tab-attributes">
                            <a href="#tab_adversaries" data-toggle="tab">
                            Threat Actor ( <span id="number_adversaries">0</span> )
                            </a>
                        </li>
                        <li id="tab-event">
                            <a href="#tab_related_event" data-toggle="tab">
                                Related Event ({{@$count_related_pulse}})
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_attributes">
                            <section class="panel panel-default">
                                <header class="panel-heading font-bold panel-header-blue">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <i class="fas fa-table"></i> Table Attributes
                                        </div>
                                    </div>
                                </header>
                                <div class="panel-body">
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
                                                            <th>Type</th>
                                                            <th>Attribute Name</th>
                                                            <th>Role</th>
                                                            <th>Date</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div id="showing_amount_text" class="pull-left" style="margin-top: 5px; margin-left: 15px;"></div>
                                            <div class="pull-right" style="padding-right: 10px;" id="pagination_custom"></div>
                                        </div>
                                    </div>
                                </section>
                            </div>


                            <div class="tab-pane" id="tab_malware">
                                <section class="panel-default">
                                    <header class="panel-heading font-bold panel-header-blue" style="margin-bottom: 1rem">
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <i class="fas fa-table"></i> Malware Families
                                            </div>
                                        </div>
                                    </header>
                                    <div id="body_malware"></div>
                                </section>
                            </div>

                            <div class="tab-pane" id="tab_adversaries">
                                <section class="panel-default">
                                    <header class="panel-heading font-bold panel-header-blue" style="margin-bottom: 1rem">
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <i class="fas fa-table"></i> Threat Actor
                                            </div>
                                        </div>
                                    </header>
                                    <div id="body_adversaries"></div>
                                </section>
                            </div>


                        <div class="tab-pane" id="tab_related_event">
                            <section class="panel panel-default">
                                <header class="panel-heading font-bold panel-header-blue">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <i class="fas fa-table"></i> Table Related Event
                                        </div>
                                    </div>
                                </header>
                                <div class="panel-body">
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
                                                            <th>Attribute</th>
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

                                    </div>
                                </div>
                            </section>
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
                    load_adversaries();
                    load_malware();
                });

                function load_adversaries(){
                    $('#body_adversaries').empty();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "/indicators/adversaries",
                        type: "get",
                        data: ({
                            pulse_id:{!! json_encode($pulse_id) !!},
                        }),
                        beforeSend: function(){
                        },
                    }).done(function(data){
                        let number_adversaries = data.data.length;
                        $('#number_adversaries').text(number_adversaries);
                        let html = ``;
                        for(let row in data.data){
                            const element = data.data[row];
                            html += `<div class="panel-body clause shadow">
                                <div class="item-search">
                                    <div style="width: 100%;">
                                        <a href="/indicators/detail_adversary/${element.adversary_uuid}/${element.pulse_id}" class="fz-search-20px">
                                            ${element.adversary_name}
                                        </a>
                                    </div>
                                </div>
                            </div>`;
                        }
                        $('#body_adversaries').html(html);
                    }).fail(function(jqXHR, ajaxOptions, thrownError){
                        console.log("No response from server");
                    });
                }

                function load_malware(){
                    $('#body_malware').empty();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "/indicators/malware",
                        type: "get",
                        data: ({
                            pulse_id:{!! json_encode($pulse_id) !!},
                        }),
                        beforeSend: function(){
                        },
                    }).done(function(data){
                        let number_malware = data.data.length;
                        $('#number_malware').text(number_malware);
                        let html = ``;
                        for(let row in data.data){
                            const element = data.data[row];
                            html += `
                                <div class="panel-body clause shadow">
                                    <div class="item-search">
                                        <div style="width: 90%;">
                                            <a href="/indicators/detail_malware?malware_uuid=${encodeURIComponent(element.malware_uuid)}" class="fz-search-20px">
                                                ${element.malware_name}
                                            </a>
                                            <div>
                                                Category: ${element.malware_catogry}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                        $('#body_malware').html(html);
                    }).fail(function(jqXHR, ajaxOptions, thrownError){
                        console.log("No response from server");
                    });
                }

                function load_table_attributes(){



                    $('#table-attributes-template').DataTable({
                        searching: false,
                        ordering: false,
                        pageLength: 25,
                        processing: true,
                        serverSide: true,
                        destroy: true,
                        "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
                        ajax: {
                            async:true,
                            url: '{!! route('indicators.events_attributes_table')!!}',
                            type: "POST",
                            data:function(d){
                                d.pulse_id = pulse_id;
                                d.count_page = count_page;    
                                d.total_record  =  "{{@$otx_events[0]['indicator_count']}}";         
                            },
                            complete: function (data) {
                               load_table_pulse();
                           },
                       },
                       initComplete : function( settings, json){
                        count_page = json.recordsTotal;
                        $('[data-toggle="tooltip"]').tooltip();
                    },

                    columns: [

                    {
                        data: 'type',
                    },
                    {
                        data: 'indicator',
                    },
                    {
                        data: 'role',
                    },
                    {
                        data: 'transaction_date',
                    },
                    {
                        data: 'indicator_id',
                    },

                    ],
                    columnDefs: [
                    {
                        targets: 1,
                        render: function (data, type, row) {
                            var inner = '';
                            inner =  '<a href="'+"{{route('indicators.detail_indicator')}}?id="+row.indicator_id+'&type='+row.type+'" >'+(row.indicator?row.indicator:"")+'</a>';
                            return inner;
                        }

                    },

                    {
                        targets: 4,
                        render: function (data, type, row) {
                            var inner = '';

                            inner =  '<a href="'+"{{route('indicators.detail_indicator')}}?id="+row.indicator_id+'&type='+row.type+'" class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>';
                            return inner;
                        }

                    },
                    {
                        targets: 3,
                        render: function (data, type, row) {
                            var inner = '';
                            var v = parseInt(row.updated_at.$date.$numberLong);
                            var created_date =  new Date(v);
                            var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                            var year = created_date.getFullYear();
                            var month = created_date.getMonth();
                            var date = created_date.getDate();
                            var hour = created_date.getHours();
                            var min = created_date.getMinutes();
                            var sec = created_date.getSeconds();
                            inner =  year + '-' + (month+1) + '-' + date + ' ' + hour + ':' + min;
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
                        "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
                        ajax: {
                           async:true,
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
