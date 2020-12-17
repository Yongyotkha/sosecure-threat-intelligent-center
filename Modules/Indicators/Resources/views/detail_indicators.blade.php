@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">
            <header class="header bg-white b-b b-light head-d-flex-nowrap"
                style="white-space: nowrap;overflow-x: auto;">
                <div class="bc-head m-none">
                    <a href="{{route('indicators.attributes')}}" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive m-r-5">
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
                                <a href="#event">Pulses</a>
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
                <div id="general_details" class="pd-15">
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
                <section id="#event" class="">
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
                                                    <th>
                                                        <label>
                                                            <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                            <span class="label-text"></span>
                                                        </label>
                                                    </th>
                                                    <th>No</th>
                                                    <th>Event Name</th>
                                                    <th>Group</th>
                                                    <th>Tags</th>
                                                    <th>Attr</th>
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

    detail_load_general();
    detail_load_pulses();


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
        load_table(1);
    });



    function detail_load_pulses(page=1){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{!! route('indicators.load_pulses')!!}',
            type: "post",
            data:({
                page : page,
                id:"{{$otxid}}"
            }),
            beforeSend: function(){
                f_loading(null,'#table-related-event');
            },
        }).done(function(data){
            f_loading_stop(null,'#table-related-event');
            $("#table-related-event").html(data.html);
            {{--$("#pagination_custom").html(data.pagination);--}}
        }).fail(function(jqXHR, ajaxOptions, thrownError){
	    loading('stop_load');
            f_loading_stop(null,'#table-related-event');
            console.log("No response from server");
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
                id:"{{$otxid}}"

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

    function detail_load_pulses1(){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/indicators/load/pulses",
            type: "post",
            data: ({
                id:"{{$otxid}}",
            }),
            datatype: "html",
            beforeSend: function(){
                $('#loadspinner_related_pulse').show();
            },
        }).done(function(data){
            $('#loadspinner_related_pulse').hide();

            {{--$("#pulses_related").append(data.html);--}}

        }).fail(function(jqXHR, ajaxOptions, thrownError){
            $('#loadspinner_related_pulse').hide();
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