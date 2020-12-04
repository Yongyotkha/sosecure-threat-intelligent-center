@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">
            <header class="header bg-white b-b b-light head-d-flex-nowrap"
                style="white-space: nowrap;overflow-x: auto;">
                <div class="bc-head m-none">
                    <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive m-r-5">
                        @icon('solid/arrow-left')
                    </a>
                    @langapp('indicators') : Type:{{$otxtype}},<span id="otxindicator_text">{{$otxindicator}}</span>
                    <button id="copy_button" class="btn btn-default m-t-0 m-l-5">
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
                                <a href="#pulses">Pulses</a>
                                <div class="underline"></div>
                            </li>
                            <li class="nav-link">
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
                            </li>
                        </ul>
                    </div>
                </div>
                <div id="general_details" class="pd-15">
                    <div class="row m-b-lg">
                        {{-- Basic Information --}}
                        <div id="indicator_basic_info" class="col-md-6">
                            <h1 class="b-b">Basic Information</h1>
                            <div id="loadspinner_basic_info" class="content-spinner-loading">
                            </div>
                            {{-- Inner Basic Information--}}

                        </div>
                        {{-- File Identification --}}
                        <div class="col-md-6">
                            <h1 class="b-b">File Identification</h1>
                            {{-- Inner File Identification--}}
                            <div class="row m-b-xs">
                                <div class="col-md-3">
                                    FILE TYPE:
                                </div>
                                <div class="col-md-9 text-right">
                                    application/x-executable (ELF)
                                </div>
                            </div>
                            <div class="row m-b-xs">
                                <div class="col-md-3">
                                    FILEMAGIC:
                                </div>
                                <div class="col-md-9 text-right">
                                    ELF 32-bit MSB executable, SPARC, version 1 (SYSV)
                                </div>
                            </div>
                            <div class="row m-b-xs">
                                <div class="col-md-3">
                                    MD5:
                                </div>
                                <div class="col-md-9 text-right">
                                    <a href="">439cc55e2e5cd09ccec453f166e34daa</a>
                                </div>
                            </div>
                            <div class="row m-b-xs">
                                <div class="col-md-3">
                                    SHA256:
                                </div>
                                <div class="col-md-9 text-right">
                                    <a href="">1bd1822b6799615b5c7fc13bf219a9f15722d16cc2070256c46437684276ab5b</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row m-b-lg">
                        {{-- Validation --}}
                        <div id="indicator_validation" class="col-md-6">
                            <h1 class="b-b">Validation</h1>
                            {{-- Inner Validation--}}
                            <div id="loadspinner_indicator_validation" class="content-spinner-loading">
                            </div>
                        </div>
                        {{-- Inner External Sources--}}
                        <div class="col-md-6">
                            {{-- Inner External Sources--}}
                            <h1 class="b-b">External Sources</h1>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="external-content">
                                        <a href="">
                                            @icon('solid/alexa')
                                            <span></span>Alexa
                                        </a>
                                        <a href="">
                                            <i class="fas fa-comment text-info icon"></i>
                                            Whois
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <section id="#pulses" class="">
                    <div class="row header-badge-full">
                        <div class="col-md-12">
                            <span class="font-weight-bold">Related Pulses</span>
                        </div>
                    </div>
                    <div id="pulses_related" class="show-indicators">
                        <div id="loadspinner_related_pulse" class="content-spinner-loading">
                    </div>
                </section>

                <section id="adhesive_dns" class="m-t-lg">
                    <div class="row header-badge-full">
                        <div class="col-md-12">
                            <span class="font-weight-bold">Server Response</span>
                        </div>
                    </div>
                    <div class="row pd-15">
                        <div class="col-md-12">
                            <div class="data-server-response m-b-xs">
                                <span>CONTENT-LENGTH: <span class="text-dark fz-10">76160</span></span>
                            </div>
                            <div class="data-server-response m-b-xs">
                                <span>ACCEPT-RANGES: <span class="text-dark fz-10">bytes</span></span>
                            </div>
                            <div class="data-server-response m-b-xs">
                                <span>SERVER: <span class="text-dark fz-10">Apache/2.2.15 (CentOS)</span></span>
                            </div>
                            <div class="data-server-response m-b-xs">
                                <span>CLAST-MODIFIED: <span class="text-dark fz-10">Fri, 27 Mar 2020 04:49:10
                                        GMT</span></span>
                            </div>
                            <div class="data-server-response m-b-xs">
                                <span>CONNECTION: <span class="text-dark fz-10">close</span></span>
                            </div>
                            <div class="data-server-response m-b-xs">
                                <span>ETAG: <span class="text-dark fz-10">"e2b4f-12980-5a1ced27982ba"</span></span>
                            </div>
                            <div class="data-server-response m-b-xs">
                                <span>DATE: <span class="text-dark fz-10">Mon, 30 Mar 2020 16:48:58 GMT</span></span>
                            </div>
                            <div class="data-server-response m-b-xs">
                                <span>CONTENT-TYPE: <span class="text-dark fz-10">text/plain;
                                        charset=UTF-8</span></span>
                            </div>
                        </div>
                    </div>
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
            $('#loadspinner_indicator_validation').hide();
            $("#indicator_validation").append(data.html2);
            $("#pulses_related").append(data.html);
            


            
            if("{{$otxtype}}"=="CVE"){
                console.log("CVE");
            }else{
                {{--ajax type URL--}}
                $.ajax(req_load_url_list(data.generalData)).done(function(data2){
                    $('#loadspinner_basic_info').hide();
                    console.log(data2);
                    $("#indicator_basic_info").append(data2.html);
                }).fail(function(jqXHR, ajaxOptions, thrownError){
                    console.log("No response from server2");
                });

                if(data.sections.indexOf("url_list")!= -1){
                    console.log("sec","url_list");
                }

            }
        }).fail(function(jqXHR, ajaxOptions, thrownError){
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
            }),
            datatype: "html",
            beforeSend: function(){
                $('#loadspinner_indicator_validation').show();
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