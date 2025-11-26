@php
// dd($otx_events[0]['name']);
@endphp


@extends('layouts.app')
@section('content')
<style type="">
select.c-tags {
    min-width: 300px;
}
.select2-container--default .select2-selection--multiple {
    min-width: 300px !important;
}

.btn-custom {
        background-color: #ffffffff; 
        border: #3869d4 solid 0.7px;
        color: #3869d4;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        cursor: pointer;
        border-radius: 4px;
        transition-duration: 0.3s;
    }

    .btn-custom:hover {
        background-color: #3869d4; 
        color: #ffffffff;
        border: #3869d4 solid 0.7px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        transition-duration: 0.3s;
    }

    
    .badge-critical {
        color: #ffffffff;
        background-color: #fe0000;
        }

    .badge-medium {
        color: #000000ff;
        background-color: #ffff00;
        }

        .badge-high {
        color: #000000ff;
        background-color: #ffc000;
        }

        .badge-verylow {
        color: #ffffffff;
        background-color: #98fb98;
        }
        .badge-low {
        color: #ffffffff;
        background-color: #00649f;
        }
        .badge-infomation {
        color: #ffffffff;
        background-color: #00649f;
        }
        

</style>
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
                                <input type="hidden" id="pulse-name" value="{{@$otx_events[0]['name']}}" data-name="{{@$otx_events[0]['name']}}">
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
                                {{-- Attributes  ({{@$otx_events[0]['indicator_count']}}) --}}
                                Attributes
                            </a>
                        </li>
                        <li id="tab-attributes">
                            <a href="#tab_malware" data-toggle="tab">
                                Malware
                            </a>
                        </li>
                        <li id="tab-attributes">
                            <a href="#tab_adversaries" data-toggle="tab">
                            Threat Actor
                            </a>
                        </li>
                        <li id="tab-event">
                            <a href="#tab_related_event" data-toggle="tab">
                                Related Event
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

                                
                                    <div class="row">
                                        <div class="col-lg-3" style="margin-top: 10px;margin-bottom: -10px; margin-left: 10px">
                                           
                                            <button class="btn btn-custom" onclick="exportCSV()" style="text-align: center;">
                                               <i class="fa fa-arrow-circle-down"></i>  Export CSV
                                            </button>
                                        </div>
                                    </div>

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
                                                            <th>Tags</th>
                                                            <th>Attribute Score</th>
                                                            <th>Attribute Serverity</th>
                                                            <th>Date</th>
                                                            <th>Role</th>
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
                    "fnDrawCallback": function(oSettings) {

                            $(".c-tags").select2({
                            tags: true,
                             width: 'resolve'
                            });
                            $(document).on('change', '.select2-option', function() {
                                const indicator_id = $(this).data('indicator_id'); 
                                const pulse_id = $(this).data('pulse_id'); 
                                
                                const selectedValues = $(this).val(); 

                                const selectedString = selectedValues ? selectedValues.join(',') : '';
                                f_change_tags(pulse_id,indicator_id,selectedString);


                            });





                            },

                    columns: [

                    {
                        data: 'type',
                    },
                    {
                        data: 'indicator',
                    },
                    {
                            data: 'tags',
                            render: function(data, type, row, meta) {
                            
                                const tags_list = row.tags ? row.tags.split(",").map(tag => tag.trim()) : [];


                                const options = tags_list.map(tag => {
                                    if(tag){
                                        const selected = 'selected';
                                        return `<option value="${tag}" ${selected}>${tag}</option>`;
                                    }
                                
                                }).join('');

                                return `
                                    <select data-pulse_id="${row.pulse_id}"  data-indicator_id="${row.indicator_id}" name="tag[]" class="c-tags select2-option form-control" multiple="multiple">
                                        ${options}
                                    </select>
                                `;
                            }
                        },
                        {
                        data: 'attribute_score',
                        
                    },
                    {
                        data: 'attribute_serverity'
                    },
                        
                    {
                        data: 'role',
                    },
                    {
                        data: 'created',
                    },
                    {
                        data: 'indicator_id',
                        "visible": false,
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
                        targets: 6,
                        render: function (data, type, row) {
                            var inner = '';

                            inner =  '<a href="'+"{{route('indicators.detail_indicator')}}?id="+row.indicator_id+'&type='+row.type+'" class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>';
                            return inner;
                        }

                    },
                    {
                        targets: 5,
                        render: function (data, type, row) {
                            var inner = '';
                            var v = parseInt(row.created.$date.$numberLong);
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

                    },
                    {
                        targets: 3,
                        className: 'text-center',
                        render: function (data, type, row, meta) {
                            if (type !== 'display') return data;

                            var txt = (data === undefined || data === null) ? '' : String(data).trim();
                            if (!txt) return '';
                            if (txt === '0') return '<span class="badge badge-infomation">0</span>';
                            if (txt === '1') return '<span class="badge badge-verylow">1</span>';
                            if (txt === '2') return '<span class="badge badge-success">2</span>';
                            if (txt === '3') return '<span class="badge badge-success">3</span>';
                            if (txt === '4') return '<span class="badge badge-medium">4</span>';
                            if (txt === '5') return '<span class="badge badge-medium">5</span>';
                            if (txt === '6') return '<span class="badge badge-medium">6</span>';
                            if (txt === '7') return '<span class="badge badge-high">7</span>';
                            if (txt === '8') return '<span class="badge badge-high">8</span>';
                            if (txt === '9') return '<span class="badge badge-critical">9</span>';
                            if (txt === '10') return '<span class="badge badge-critical">10</span>';

                        }
                    },
                    {
                        targets: 4,
                        className: 'text-center',
                        render: function (data, type, row) {
                            if (type !== 'display') return data;

                            const raw = (data ?? '').toString().trim();
                            if (!raw) return '<span></span>';

                            const val = raw.toLowerCase();

                            const badgeMap = {
                            critical: 'badge-critical',
                            high: 'badge-high',
                            medium: 'badge-medium',
                            low: 'badge-success',
                            information: 'badge-infomation',
                            informational: 'badge-infomation',
                            info: 'badge-isinfo',
                            "very low": 'badge-verylow',
                            };

                            const cls = badgeMap[val];
                            if (!cls) return `<span>${raw}</span>`;
                            return `<span class="badge ${cls}">${raw}</span>`;
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
          function f_change_tags(pulse_id,indicator_id, tags) {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('indicators.indicator_detail_update_tags') }}",
                        type: "POST",
                        data: {
                            indicator_id: indicator_id,
                            pulse_id:pulse_id,
                            tags: tags
                        },
                        beforeSend: function () {
                     
                        },
                        success: function (data) {
                            if (data.status_code == "00") {
                           
                                toastr.success('บันทึกสำเร็จ', 'แจ้งแตือน');
                            } else {
                             
                                toastr.error( 'เกิดข้อผิดพลาด' , 'แจ้งแตือน');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log("เกิดข้อผิดพลาดในการเชื่อมต่อกับ server");
                        }
                    });
             }





</script>
<script>
            const exportBaseUrl = "{{ route('indicators.export_event_indicators') }}";
        </script>
        <script src="{{ asset('js/exportindicators.js') }}"></script>

@endpush
@endsection
