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
                                <a href="#general_details">General Detail</a>
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
                                                    <th>TYPE</th>
                                                    <th>Attribute Name</th>
                                                    <th>ROLE</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>   
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <label>
                                                            <input value="" type="checkbox" />
                                                            <span class="label-text"></span>
                                                        </label>
                                                    </td>
                                                    <td>FileHash-SHA256</td>
                                                    <td><a href="">Lorem ipsum dolor sit amet.Lorem ipsum dolor sit amet.</a></td>
                                                    <td>
                                                        -
                                                    </td>
                                                    <td>
                                                        -
                                                    </td>
                                                    <td>
                                                        <a href="" class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>
                                                    </td>
                                                </tr>    
                                            </tbody> 
                                        </table>
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

    $('#table-related-event').DataTable();

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
        $.ajax(req_load_general()).done(function(data){
            $('#loadspinner_related_pulse').hide();
            $('#loadspinner_basic_info').hide();
            $("#indicator_basic_info").append(data.html);

            {{--$("#pulses_related").append(data.html);--}}

            
            if("{{$otxtype}}"=="CVE"){
                console.log("CVE");
            }else{

            }
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            $('#loadspinner_related_pulse').hide();
            $('#loadspinner_basic_info').hide();
            console.log("No response from server");
        });
    }

    function req_load_general(){
        dataout = {
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
                $('#loadspinner_related_pulse').show();
                $('#loadspinner_basic_info').show();
            },
        };
        return dataout;
    }

    function req_load_url_list(data_general){
        dataout = {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/indicators/load/url_list",
            type: "post",
            data: ({
                type:"{{$otxtype}}",
                indicator:"{{$otxindicator}}",
                data_general:data_general,
            }),
            datatype: "html",
            beforeSend: function(){
            },
        };
        return dataout;
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