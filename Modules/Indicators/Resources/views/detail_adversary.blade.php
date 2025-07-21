@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">
            <header class="header bg-white b-b b-light head-d-flex-nowrap"
                style="white-space: nowrap;overflow-x: auto;">
                <div class="bc-head m-none">
                    <a href="{{route('indicators.attributes')}}"
                        class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive m-r-5">
                        @icon('solid/arrow-left')
                    </a>
                    Threat Actor : {{ @$adversary[0] -> name }}
                </div>

                &nbsp;

            </header>
            <section class="scrollable wrapper bg-white" style="padding:0;">
                <div class="row sticky-top-nav d-none">
                    <div class="nav-menu-btn">
                        <ul>
                            <li class="nav-link active-link">
                                <a id="to_top" href="#general_details">General Detail</a>
                                <div class="underline"></div>
                            </li>
                        </ul>
                    </div>
                </div>
                <section id="general_details" class="">
                    <div class="row header-badge-full">
                        <div class="col-md-12">
                            <span class="font-weight-bold">Basic Information</span>
                        </div>
                    </div>
                    <div class="pd-15">
                        <div class="row m-b-xs">
                            <div class="col-md-12">
                                <h1> {{ @$adversary[0] -> name }}</h1>
                            </div>
                            <div class="col-md-12">
                                <b> Description</b>
                                <p>
                                    {!! @$adversary[0] -> description !!}
                                </p>
                            </div>
                    
                            <div class="col-md-12">
                                <p>
                                    <b> ALSO KNOWN AS</b> : <a>{{ @$adversary[0] -> synonyms == null ? '-' :  $adversary[0] -> synonyms }}</a>
                                </p>
                            </div>
                            <div class="col-md-12">
                                <p>
                                    <b>POSSIBLE LOCATION</b> : <a>{{ @$adversary[0] -> country == null ? '-' :  $adversary[0] -> country }}</a>
                                </p>
                            </div>

                        </div>
                    </div>
                </section>
                <section id="related_event" class="">
                    <div class="row header-badge-full">
                        <div class="col-md-12">
                            <span class="font-weight-bold">Related Event</span>
                        </div>
                    </div>
                    <section class="panel panel-default" style="margin: 10px;">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-md-12">
                                    <div style="margin-top:5px;">
                                        <i class="fas fa-table"></i> Table Related
                                    </div>
                                </div>
                        </header>
                        <div class="panel-body" style="padding: 0 !important">
                            <div class="container-fluid" style="padding:1rem;">
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
                                            </table>
                                            {{--<div class="pull-right" style="padding-right: 10px;" id="pagination_custom"></div>--}}
                                        </div>
                                    </div>
                                </div>
                                {{-- <div class="show-indicators">
                                    <div id="loadspinner_related_pulse" class="content-spinner-loading">
                                </div> --}}
                            </div>
                        </div>
                    </section>
                </section>
            </section>
        </section>

    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')

<script>

    $(document).ready(function () {
        $('#indicator_type').select2({
            placeholder:'Indicator Type',
        });
        $('#role').select2({
            placeholder:'Role',
        });

        $('.nav-link').on('click',function(){
            $('.active-link').removeClass();
            $(this).addClass('active-link')
        });

        $("#copy_button").click(function(){
            copy_clipboard("otxindicator_text");
        });
        related_event();

    });
    var count_page = -1;
    var count_page2 = -1;
    let adversary_uuid = '{{ request()->adversary_uuid }}';
    function related_event(){
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
                url: '{!! route('indicators.load_adversary_tb')!!}',
                dataSrc: function ( json ) {
                    count_page2 = json.recordsTotal;
                    return json.data;
                },
                data:function(d){
                    d.adversary_uuid = adversary_uuid;
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
    
    function copy_clipboard(id) {
        var copyText = document.getElementById(id);
        var textArea = document.createElement("textarea");
        textArea.value = copyText.textContent;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand("Copy");
        textArea.remove();
    }

</script>
@endpush
@endsection
