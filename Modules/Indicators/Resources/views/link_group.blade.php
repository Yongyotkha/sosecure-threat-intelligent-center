@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header bg-white b-b b-light">
            <a href="{{route('indicators.events')}}" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a>
            <div class="bc-head">Group : {{@$id}}</div>

            <a id="advance-search" href="#hide-advance-search"
                class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                <span><i class="fas fa-filter"></i> @langapp('Search_Advance')</span>
            </a>
            {{-- <a id="to_top" href="#area_search" class="">test</a> --}}
            <div class="pull-right" style="margin-top: 8px; width: 300px;">
                <!--<select name="site" id="site" class="select2-option form-control select-site" style="min-width: 300px">
                    <option value="">All Site</option>
                    {{-- @if($SiteSettings)
                    @foreach($SiteSettings as $SiteSettings_val)
                    <option value="{{$SiteSettings_val->code}}">{{$SiteSettings_val->name}}</option>
                    @endforeach
                    @endif --}}
                </select>-->
            </div>

        </header>
        <section class="scrollable wrapper">
            <section id="hide-advance-search" class="panel panel-default" style="display: none;">
                <div class="panel-heading font-bold panel-header-blue">
                    Filter
                </div>
                <div class="container-fluid" style="padding: 2rem;">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group m-b-md">
                                <label for="" class="">Keyword</label>
                                <input type="text" class="form-control" name="keyword" id="keyword"
                                    placeholder="Search">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="" class="">Date</label>
                            <div id="groups_date" class="text-center"
                                style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span></span> <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
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

            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-xs-12">
                            <i class="fas fa-table"></i> Table Group
                        </div>
                    </div>
                </header>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table_group">
                            <thead>
                                <tr>

                                    <th>No</th>
                                    <th>Event Name</th>
                                    <th>Group</th>
                                    <th>Tags</th>
                                    <th>Published</th>
                                    <th>Last Status</th>
                                    <th>DateTime</th>
                                    <th>Attribute</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

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
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.advanced_search')
<script>

    var start_date = '';
    var end_date = '';
    var keyword = '';
    var count_page = -1;
    var isDateSearch = 0;
    var datatable = [];
  

    $('.select2-option').select2();

    $(function() {
  
    var start = moment().startOf('hour');
    var end = moment().startOf('hour').add(32, 'hour');

    function cb(start, end) {
        $('#groups_date span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        startDate = start;
        endDate = end;
    }

    $('#groups_date').daterangepicker({
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
    $('#groups_date').on('apply.daterangepicker', function(ev, picker) {
        isDateSearch = 1;
        if (!picker.startDate.isValid() || !picker.endDate.isValid()) {
            
        }
    });

    cb(start, end);

    $("#btn_search_data").click(function() {

        start_date = startDate;
        end_date = endDate;
        keyword = $("#keyword").val();
        search_table(1);
    });


    $("#btn_reset").click(function() {
        $("#keyword").val('');

        start = moment();
        end = moment();
        cb(start, end);
        load_table(1);


    });


    });

    $(function() {
        load_table(1);
    });


    function load_table(page=1){
        $('#table_group').DataTable({
            searching: false,
            ordering: true,
            pageLength: 25,
            processing: true,
            serverSide: true,
            destroy: true,
            order: [[ 6, "desc" ]],
            "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
            ajax: {
                type: "POST",
                url: '{!! route('indicators.table_groups')!!}',
                dataSrc: function ( json ) {
                    count_page = json.recordsTotal;
                    return json.data;
                },
                data:function(d){
                    d.groups = {!! json_encode($id) !!};
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
        let startDate=  $("#groups_date").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
        let endDate=  $("#groups_date").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');
        $('#table_group').DataTable({
            searching: false,
            ordering: true,
            pageLength: 25,
            processing: true,
            serverSide: true,
            destroy: true,
            order: [[ 6, "desc" ]],
            "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
            ajax: {
                type: "POST",
                url: '{!! route('indicators.table_groups')!!}',
                dataSrc: function ( json ) {
                   
                    count_page = json.recordsTotal;
                    return json.data;
                },
                data:function(d){
                    d.count_page = count_page;
                    d.startDate = startDate;
                    d.endDate = endDate;
                    d.keywords = keyword;
                    d.isDateSearch = isDateSearch;
                    d.groups = {!! json_encode($id) !!};
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