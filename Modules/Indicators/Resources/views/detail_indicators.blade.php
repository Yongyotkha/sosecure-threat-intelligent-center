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
                    @langapp('indicators') : Type:{{$otxtype}},<span id="otxindicator_text">{{$otxindicator}}</span>
                    <button id="copy_button" class="btn btn-default m-t-0 m-l-5" style="margin-left: 5px !important">
                        @icon('solid/copy')
                    </button>
                </div>

                &nbsp;

            </header>
            <section class="scrollable wrapper bg-white" style="padding:0;">
                <div class="row sticky-top-nav">
                    <div class="nav-menu-btn">
                        <ul>
                            <li class="nav-link active-link">
                                <a id="to_top" href="#general_details">General Detail</a>
                                <div class="underline"></div>
                            </li>
                            <li class="nav-link">
                                <a href="#related_event" id="event_tag">Event</a>
                                <div class="underline"></div>
                            </li>
                            {{-- <li class="nav-link">
                                <a href="#adhesive_dns">Adhesive DNS</a>
                                <div class="underline"></div>
                            </li>
                            <li class="nav-link">
                                <a href="#urls">URLs</a>
                                <div class="underline"></div>
                            </li>
                            <li class="nav-link">
                                <a href="#files">Files</a>
                                <div class="underline"></div>
                            </li> --}}
                        </ul>
                    </div>
                </div>
                <section id="general_details" class="">
                    <div class="pd-15">
                        <div class="row m-b-lg">
                            {{-- Basic Information --}}

                            @if($otxtype=="NIDS")
                            <div id="indicator_description" class="col-md-12">
                                <h1 class="b-b">Description</h1>
                                <div id="loadspinner_basic_info" class="content-spinner-loading">
                                </div>
                                {{-- Inner Basic Information--}}

                            </div>
                            @endif
                            <div id="indicator_basic_info" class="col-md-12">
                                <h1 class="b-b">{{($otxtype=="YARA")?"Rule":"Basic Information"}}</h1>
                                <div id="loadspinner_basic_info" class="content-spinner-loading">
                                </div>
                                {{-- Inner Basic Information--}}

                            </div>

                            {{-- File Identification --}}
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
                                                    <th>View</th>
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
    {{--$('#table-related-event').DataTable();--}}


    var count_page = -1;
    detail_load_general();
    


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


    });
    
    function detail_load_general(){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/indicators/load/general",
            type: "get",
            data: ({
                type:"{{$otxtype}}",
                indicator:"{{$otxindicator}}",
                id:"{{$otxid}}",
            }),
            datatype: "html",
            beforeSend: function(){
                $('#loadspinner_basic_info').show();
            },
        }).done(function(data){
            $('#loadspinner_basic_info').hide();
            $("#indicator_basic_info").append(data.html);

            {{--$("#pulses_related").append(data.html);--}}

            
            if("{{$otxtype}}"=="CVE"){
                console.log("CVE");
            }else{

            }
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            $('#loadspinner_basic_info').hide();
            console.log("No response from server");
        });
    }

    $(function() {
        detail_load_pulses(1);
    });


    function detail_load_pulses(page=1){

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
                url: '{!! route('indicators.load_pulses')!!}',
                dataSrc: function ( json ) {
                    count_page = json.recordsTotal;
                    return json.data;
                },
                data:function(d){
                    
                    d.count_page = count_page;
                    d.id = "{{$otxid}}";
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

    function pagination_goto(page=null) {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{!! route('indicators.load_pulses')!!}',
            type: "post",
            data:({
                page : page,
                id:"{{$otxid}}",
                type:"{{$otxtype}}",
                indicator:"{{$otxindicator}}"

            }),
            beforeSend: function(){
                f_loading(null, '#table-related-event');
            },
        }).done(function(data){
        f_loading_stop(null, '#table-related-event');
            
            $("#table-related-event").html(data.html);
            {{--$("#pagination_custom").html(data.pagination);--}}
            $("#to_top").trigger("click");
        }).fail(function(jqXHR, ajaxOptions, thrownError){
	    {{--loading('stop_load');--}}
        f_loading_stop(null, '#table-related-event');
            console.log("No response from server");
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