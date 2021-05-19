@extends('layouts.app')
@section('content')
@php $role_custom = @check_role_custom(); @endphp
@php 
    $indicators_type = [];
    $count_val = [];
    $count_all = 0;
 foreach (@$dataSearch as $key => $value) {
    if (isset($value["count"])&&$value["count"] > 0) {
        $count_all = @$count_all+@$value["count"];
        $count_val[slugify($key)] = !empty($value["count"]) ? number_format($value["count"]) : 0;
    }

    if(!empty($count_all)) {
        if(is_numeric($count_all)) {
            $count_all = number_format($count_all,0);
        }
    }

    if($key == 'indicators') {
        foreach ($value["queryData"] as $key2 => $value2) {
            $indicators_type[] = substr($value2["content"],6);
        }
    }


 }

    $indicators_type_unique = array_unique($indicators_type);

//  dd($indicators_type_unique);
                           
@endphp

<section id="content">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            <div class="bc-head">@langapp('search_results_for_tag',['keyword' => $keyword])</div>
            <span class="pull-right" style="margin-top: 1.2rem;font-size: 16px;font-weight: bold;">
                Total Result : {{@$count_all}} 
            </span>
        </header>
        <section class="scrollable wrapper bg" id="clauses" style="padding: 8px !important">

            {{-- <div class="front-int">
                <button class="btn-start-lookup">Inteligence Threat Lookup</button>
            </div> --}}

            <section class="panel panel-default m-b-xs">
                <div class="panel-body" id="table-container">
                    <div class="row">
                        <div class="col-md-8">
                            <div id="fillter_click" class="button-group">
                                <a href="javascript:void(0)" data-btn="all" class="btn btn-selector btn_filter active">All {{$count_all ? '('.$count_all.')':'(0)'}}</a>
                                @if($role_custom['news'])
                                    @if(!empty($count_val['news']))
                                    <a href="javascript:void(0)" data-btn="news" class="btn btn-selector btn_filter">News {{@$count_val['news'] ? '('.$count_val["news"].')':'(0)'}}</a>
                                    @endif
                                @endif
                                @if($role_custom['indicators'])
                                    @if(!empty($count_val['events']))
                                    <a href="javascript:void(0)" data-btn="events" class="btn btn-selector btn_filter">Event {{@$count_val['events'] ? '('.$count_val["events"].')':'(0)'}}</a>
                                    @endif
                                @endif
                                @if($role_custom['data_leak'])
                                    @if(!empty($count_val['data-leak']))
                                    <a href="javascript:void(0)" data-btn="data-leak" class="btn btn-selector btn_filter">Data Leak {{@$count_val['data-leak'] ? '('.$count_val["data-leak"].')':'(0)'}}</a>
                                    @endif
                                @endif
                                @if($role_custom['compromised'])
                                    @if(!empty($count_val['compromised']))
                                    <a href="javascript:void(0)" data-btn="compromised" class="btn btn-selector btn_filter">Compromised {{@$count_val['compromised'] ? '('.$count_val["compromised"].')':'(0)'}}</a>
                                    @endif
                                @endif
                                @if($role_custom['vulnerabilities'])
                                    @if(!empty($count_val['vulnerabilities']))
                                    <a href="javascript:void(0)" data-btn="vulnerabilities" class="btn btn-selector btn_filter">Vulnerabilities {{@$count_val['vulnerabilities'] ? '('.$count_val["vulnerabilities"].')':'(0)'}}</a>
                                    @endif
                                @endif
                                @if($role_custom['indicators'])
                                    @if(!empty($count_val['indicators']))
                                    <a href="javascript:void(0)" data-btn="indicators" class="btn btn-selector btn_filter">Indicators {{@$count_val['indicators'] ? '('.$count_val["indicators"].')':'(0)'}}</a>
                                    @endif
                                @endif
                                {{-- <button class="btn btn-selector active">All</button>
                                <button class="btn btn-selector">News</button>
                                <button class="btn btn-selector">Event</button>
                                <button class="btn btn-selector">Compromised</button>
                                <button class="btn btn-selector">Vulnerabilities</button>
                                <button class="btn btn-selector">Indicators</button> --}}
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <button class="btn btn-info lookup">Inteligence Threat Lookup</button>
                        </div>
                    </div>

                    <div class="row type_indicator" style="display: none;">
                        <div class="col-md-12">
                            <label style="margin-top: 5px;"><b>Type</b></label>
                        </div>
                    </div>
                    <div class="row type_indicator" style="display: none;">
                        <div class="col-md-12">
                            <div id="fillter_click" class="button-group">
                                @if($indicators_type_unique)
                                <a href="#table-container" data-btn="all" class="btn btn-selector btn_filter_indicators_type active" data-btn_i_type="type_all">All</a>
                                    @foreach($indicators_type_unique as $indicators_type_unique_val)
                                        <a href="#table-container" data-btn="all" class="btn btn-selector btn_filter_indicators_type" data-btn_i_type="type_{{$indicators_type_unique_val}}">{{$indicators_type_unique_val}}</a>
                                    @endforeach
                                @endif
                                {{-- <a href="#table-container" data-btn="all" class="btn btn-selector btn_filter active">All {{$count_all ? '('.$count_all.')':'(0)'}}</a> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

     
            <div class="int-lookup-main" style="display: none">
 
                <div class="back-int">

                    <section id="overall-int">
                        <div style="display: flex;justify-content: space-between;">
                            <h3 class="page-header">
                                Overall Risk
                            </h3>
                            <span class="st-dt-leak">
                                <span class="st-dt vrh" data-toggle="tooltip" data-placement="left" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip vrh'>High</div><div class='text-st-tooltip'>High Risk	ข้อมูลรั่วไหลและเป็นที่รับรู้อย่างแพร่หลาย เช่น <br> ออกข่าว หรือมีการแชร์ข้อมูลจากแหล่งข้อมูลที่น่าเชื่อถือและเป็นที่แพร่หลาย <br> และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูล Username ,Password ของลูกค้าหรือเจ้าหน้าที่ดูแลระบบภายในองค์กรซึ่งเป็นข้อมูลที่สามารถนำมาใช้ได้จริง</div></div>">High Risk</span>
                                <span class="st-dt high" data-toggle="tooltip" data-placement="left" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip md'>Medium</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้ภายในกลุ่มจำกัดหรือยังไม่เป็นที่รับรู้กันอย่างแพร่หลาย และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูล Username ,Password ของลูกค้าหรือเจ้าหน้าที่ดูแลระบบภายในองค์กรซึ่งเป็นข้อมูลที่สามารถนำมาใช้ได้จริง</div></div>">Medium Risk</span>
                                <span class="st-dt low" data-toggle="tooltip" data-placement="left" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip low'>Low</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้ภายในกลุ่มจำกัดหรือยังไม่เป็นที่รับรู้กันอย่างแพร่หลาย และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของลูกค้าเช่น ข้อมูลส่วนบุคคลซึ่งเป็นข้อมูลที่สามารถนำมาใช้ประโยชน์ต่อได้</div></div>">Low Risk</span>
                            </span>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="ovr-text">
                                    <p>Access Level</p>
                                    <p>Public</p>
                                </div>
                                <div class="ovr-text">
                                    <p>Request rate</p>
                                    <p>172800 requests/minute</p>
                                </div>
                                <div class="ovr-text">
                                    <p>Daily quota</p>
                                    <p>172800 requests/day</p>
                                </div>
                                <div class="ovr-text">
                                    <p>Monthly quota</p>
                                    <p>172800 requests/month</p>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="status-risk danger">
                                   <span class="st-circle-ovr"></span> HIGH RISK
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- <section id="collection-overview">
                        <h3 class="page-header">
                            Unknown Files Collection
                            <div class="pull-right">
                                <a href="#" id="copy-sha256s" class="btn btn-default btn-xs">
                                    <i class="fa fa-clipboard"></i> Copy SHA256s
                                </a>                                              
                            </div>
                        </h3>
                        <div id="overview-container" class="row">  
                            <div class="col-xs-12 col-sm-8 col-md-9 main-content">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-3 text-right-sm"><b>Number of files:</b></div>
                                    <div class="col-xs-12 col-sm-9">
                                        7
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-3 text-right-sm"><b>External Analytics:</b></div>
                                    <div class="col-xs-12 col-sm-9">
                                        5
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-3 text-right-sm"><b>Score :</b></div>
                                    <div class="col-xs-12 col-sm-9" id="last-av-scan-label">
                                        100
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-3 text-right-sm"><b>IP :</b></div>
                                    <div class="col-xs-12 col-sm-9" id="last-av-scan-label">
                                        xx.x.x.x..xxx
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-3 text-right-sm"><b>Created At :</b></div>
                                    <div class="col-xs-12 col-sm-9" id="last-av-scan-label">
                                        05/13/2021 22:15:14 (UTC)
                                    </div>
                                </div>
                            </div>
            
                            <div class="col-xs-12 col-sm-4 col-md-3">
                                <div id="basic-malware-detection-info" class="text-right small">
                                    <div class="main-verdict">
                                        <span class="label label-danger">
                                            malicious
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section> --}}

                    <section id="av-detection">
                        <h3 class="page-header">
                            Anti-Virus Results
                        </h3>
                    
                        <div id="show-chart-int" class="row av-results-wrapper has-falcon-button">
                            <div class="col-xs-12 col-sm-6 col-md-4">
                                <div id="card-search-int1" class="main-int-card">
                                    <div class="text-center av-container border-success" data-chart="chart-cs-ml">
                                        <div class="title">
                                            <img src="https://www.crowdstrike.com/wp-content/uploads/2020/08/FalconPro@2x.svg" alt="" class="icon">
                                             CrowdStrike Falcon</div>
                                        <div class="main-frame">

                                            <div class="load-hybrid loadfixed backdrop-loader white">
                                                <div class="loader4 centerloader"></div>
                                                <div class="loadding-text">Loading ...</div>
                                            </div>

                                            {{-- <div id="chart-md" style="height: 300px"></div> --}}
                                            <div class="circle-score">
                                                <div class="circle-score-inner">
                                                    <div class="circle-text-score">
                                                        <span id="text_hybrid">0</span> <span>/ 100</span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- <div class="details">
                                                <div class="scan-result">No threat found</div>
                                            </div> --}}
                                        </div>
                                    </div>
                                 </div>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-4">
                                <div id="card-search-int2" class="main-int-card">
                                    <div class="text-center av-container border-success" data-chart="chart-cs-ml">
                                        <div class="title">
                                            <img src="https://www.virustotal.com/gui/images/vt-enterprise.svg" alt="" class="icon">
                                            VirusTotal
                                        </div>
                                        <div class="main-frame">

                                            <div class="load-virustotal loadfixed backdrop-loader white">
                                                <div class="loader4 centerloader"></div>
                                                <div class="loadding-text">Loading ...</div>
                                            </div>

                                            {{-- <div id="chart-md2" style="height: 300px"></div> --}}

                                            <div class="circle-score">
                                                <div class="circle-score-inner">
                                                    <div class="circle-text-score">
                                                        <span id="text_virustotal">0</span> <span>/ 100</span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- <div class="details">
                                                <div class="scan-result">4 of 7 are detected as malicious</div>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-4">
                                <div id="card-search-int3" class="main-int-card">
                                    <div class="text-center av-container border-success" data-chart="chart-cs-ml">
                                        <div class="title">
                                            <img src="https://exchange.xforce.ibmcloud.com/images/shortcut-icons/apple-icon-114x114.png" alt="" class="icon">

                                            IBM X-Force
                                        </div>
                                        <div class="main-frame">

                                            <div class="load-ibmcloud loadfixed backdrop-loader white">
                                                <div class="loader4 centerloader"></div>
                                                <div class="loadding-text">Loading ...</div>
                                            </div>

                                            {{-- <div id="chart-md3" style="height: 300px"></div> --}}

                                            <div class="circle-score">
                                                <div class="circle-score-inner">
                                                    <div class="circle-text-score">
                                                        <span id="text_ibmcloud">0</span> <span>/ 100</span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- <div class="details">
                                                <div class="scan-result">4 of 7 are detected as malicious</div>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </section>
   
                    <section class="table-ibmcloud" style="display: none">
                        <h3>IBM X-Force</h1>
                        <table id="table-ibmcloud" class="table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Reason</th>
                                    <th>Location</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-ibmcloud"></tbody>
                        </table>
                    </section>

                    <section class="table-virustotal" style="display: none">
                        <h3>VirusTotal</h1>
                        <table id="table-virustotal" class="table">
                            <thead>
                                <tr>
                                    <th>Engine Name</th>
                                    <th>Category</th>
                                    <th>Method</th>
                                    <th>Result</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-virustotal"></tbody>
                        </table>
                    </section>

                    <section class="table-hybrid" style="display: none">
                        <h3>CrowdStrike Falcon</h1>
                        <table id="table-hybrid" class="table">
                            <thead>
                                <tr>
                                    <th>Filename</th>
                                    <th>SHA256</th>
                                    <th>Tags</th>
                                    <th>AV Result</th>
                                    <th>Sandbox Report</th>
                                    <th>Verdict</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-hybrid"></tbody>
                        </table>
                    </section>


                </div>
            </div>


            <div class="panel-group m-b" id="accordion2">
                <ul class="list no-style" id="clauses-list">

                    {{-- <li class="panel panel-default" id="clause-news">
                        <div class="panel-heading">
                            <a class="accordion-toggle name" data-toggle="collapse" data-parent="#accordion2" href="#{{ slugify('news') }}">
                                @icon('solid/caret-right') {{ humanize("news") }}({{$searchNews["count"]}})
                            </a>
                        </div>
                        <div id="{{ slugify('news') }}" class="panel-collapse collapse">
                            @foreach ($searchNews["news"] as $key => $value)
                                <div class="panel-body clause">
                                    <a href="#{{$value["id"]}}">
                                        {{$value["name"]}}
                                    </a>
                                    <div class='text-ellipsis'>{{$value["content"]}}</div>
                                </div>
                            @endforeach
                        </div>
                    </li> --}}
                    @if (!empty($dataSearch))
                        @foreach ($dataSearch as $key => $value)
                            @if (isset($value["count"])&&$value["count"] > 0)
                                <li id="{{slugify($key)}}_head" class="panel panel-default">
                                    <div class="panel-heading fontw-weight-bold">
                                        <a class="accordion-toggle name" data-toggle="collapse" data-parent="#accordion2" href="#{{ slugify($key) }}">
                                            @icon('solid/caret-right') {{ humanize($key) }} ({{!empty($value["count"]) ? number_format($value["count"]) : 0}})
                                        </a>
                                    </div>
                                    <div id="{{ slugify($key) }}" class="panel-collapse collapse in">
                                        @foreach ($value["queryData"] as $key2 => $value2)
                                            @php
                                                if(slugify($key) == 'indicators') {
                                                    $slug = 'type_'.substr($value2['content'],6);
                                                    $class_indicator = 'div_i_type';
                                                }
                                            @endphp
                                            <div class="panel-body clause {{@$class_indicator}}" data-div_i_type="{!!@$slug!!}">
                                                <a href="{{$value2["link"]}}" target="_blank">
                                                    {{$value2["name"]}}
                                                </a>
                                                <div style="
                                                max-height:100px;
                                                overflow:hidden;
                                                text-overflow: ellipsis;
                                                -webkit-box-orient: vertical;">{!!$value2["content"]!!}</div>
                                            </div>
                                        @endforeach
                                        
                                        @if ($key == "Events" && $value["count"] > 100)
                                            <div class="panel-body clause">
                                                <a href="{{$value["moreDetail"]}}" target="_blank">
                                                    More
                                                </a>
                                                <div style="
                                                max-height:100px;
                                                overflow:hidden;
                                                text-overflow: ellipsis;
                                                -webkit-box-orient: vertical;"></div>
                                            </div>
                                        @endif
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    @else
                        <div class="notfound">
                            <img src="{{asset('images/notfound.png')}}" alt="" style="max-width: 500px;width:100%:">
                            <h1>Sorry. no result found</h1>
                            <p>What you searched was unfortunately <br>not found or doesn't exist.</p>
                        </div>
                    @endif
                    {{-- @foreach (Modules\Contracts\Entities\Clause::orderBy('id', 'desc')->get() as $clause)
                    <li class="panel panel-default" id="clause-{{ $clause->id }}">
                        <div class="panel-heading">
                            <a class="accordion-toggle name" data-toggle="collapse" data-parent="#accordion2" href="#{{ slugify($clause->name) }}">
                                @icon('solid/caret-right') {{ humanize($clause->name) }}
                            </a>
                        </div>
                        <div id="{{ slugify($clause->name) }}" class="panel-collapse collapse">
                            <div class="panel-body clause">
                                @parsedown($clause->clause)
                            </div>
                        </div>
                    </li>
                    @endforeach --}}
                </ul>   
            </div>

        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>
@push('pagestyle')
@include('stacks.css.highchart')
@include('stacks.css.datatables')
@endpush
@push('pagescript')
@include('stacks.js.activebutton')
@include('stacks.js.highchart')
@include('stacks.js.datatables')
<script>
    active_btn('#fillter_click .btn-selector');

    var btn_val;
    var btn_filter_indicators_type;
    $(".btn_filter").click(function() {
        btn_val = $(this).data("btn");
        if(btn_val == 'all') {
            $("#news_head").show();
            $("#events_head").show();
            $("#data-leak_head").show();
            $("#compromised_head").show();
            $("#vulnerabilities_head").show();
            $("#indicators_head").show();

            $(".type_indicator").hide();
        } else if (btn_val == 'news') {
            $("#news_head").show();
            $("#news").collapse("show");
            $("#events_head").hide();
            $("#data-leak_head").hide();
            $("#compromised_head").hide();
            $("#vulnerabilities_head").hide();
            $("#indicators_head").hide();

            $(".type_indicator").hide();
        } else if (btn_val == 'events') {
            $("#news_head").hide();
            $("#events_head").show();
            $("#events").collapse("show");
            $("#data-leak_head").hide();
            $("#compromised_head").hide();
            $("#vulnerabilities_head").hide();
            $("#indicators_head").hide();

            $(".type_indicator").hide();
        } else if (btn_val == 'data-leak') {
            $("#news_head").hide();
            $("#events_head").hide();
            $("#data-leak_head").show();
            $("#data-leak").collapse("show");
            $("#compromised_head").hide();
            $("#vulnerabilities_head").hide();
            $("#indicators_head").hide();

            $(".type_indicator").hide();
        } else if (btn_val == 'compromised') {
            $("#news_head").hide();
            $("#events_head").hide();
            $("#data-leak_head").hide();
            $("#compromised_head").show();
            $("#compromised").collapse("show");
            $("#vulnerabilities_head").hide();
            $("#indicators_head").hide();

            $(".type_indicator").hide();
        } else if (btn_val == 'vulnerabilities') {
            $("#news_head").hide();
            $("#events_head").hide();
            $("#data-leak_head").hide();
            $("#compromised_head").hide();
            $("#vulnerabilities_head").show();
            $("#vulnerabilities").collapse("show");
            $("#indicators_head").hide();

            $(".type_indicator").hide();
        } else if (btn_val == 'indicators') {
            $("#news_head").hide();
            $("#events_head").hide();
            $("#data-leak_head").hide();
            $("#compromised_head").hide();
            $("#vulnerabilities_head").hide();
            $("#indicators_head").show();
            $("#indicators").collapse("show");

            $(".type_indicator").show();
        } 
    });


    $(".btn_filter_indicators_type").click(function() {
        $("#indicators").collapse("show");
        btn_filter_indicators_type = $(this).data("btn_i_type");
        $(".div_i_type").each(function() {
            if(btn_filter_indicators_type == 'type_all') {
                $(this).show();
            } else {
                if($(this).data("div_i_type") == btn_filter_indicators_type) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            }
        });
 
    });

    function click_search(text_search=null,btn_val=null){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/news/jqueryLoadMoreNews?page=" + page,
            type: "post",
            data: ({
                keyword:text_search,
                type:btn_val
            }),
            beforeSend: function(){
                $('.ajax-loading').show();
                {{--loading('load');--}}
                {{--f_loading(1);--}}
            },
        }).done(function(data){
            if(data.html.length == 0){
                $('.ajax-loading').hide();
                {{--f_loading_stop(1);--}}
                {{--$('#count_news').text(0);--}}
                return;
            } else {
                {{--f_loading_stop(1);--}}
                let count_n = $('#count_news').text();
                let count_search = data.count;
                let count_n_all = parseInt(count_n) + parseInt(count_search);
                
                $('#count_news').text(data.count);
                $('.ajax-loading').hide();
                $("#list_news").append(data.html); 
            }

        }).fail(function(jqXHR, ajaxOptions, thrownError){
            console.log("No response from server");
        });
    }

    {{-- 
    
    function chart(id) {
        new Highcharts.chart(id, {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: 0,
                plotShadow: false
            },
            title: {
                text: null,
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>',
                enabled:false
            },
            accessibility: {
                point: {
                    valueSuffix: '%'
                }
            },
            plotOptions: {
                pie: {
                    dataLabels: {
                        enabled: true,
                        distance: -30,
                        style: {
                            fontWeight: 'bold',
                            color: 'white'
                        },
                        format: '{y}%'
                    },
                    startAngle: 0,
                    endAngle: 360,
                    showInLegend: true,
                    colors: ['#e64732', '#65bd77'],
                }
            },
            series: [{
                type: 'pie',
                name: 'Browser share',
                innerSize: '50%',
                data: [
                    {  name: 'malicious', y:58.9}, 
                    {  name: 'clean',  y: 13.29}, 
                ]
            }]
        });
    }

    chart('chart-md');
    chart('chart-md2');
    chart('chart-md3');

    const chart_overall = new Highcharts.chart('chart-risk-level', {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: 0,
            plotShadow: false
        },
        title: {
            text: null,
            align: 'center',
            verticalAlign: 'middle',
            y: 50,
            style: {
                color: '#333',
                fontWeight: 'bold',
                fontSize:'20px'
            }
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>',
            enabled:true
        },
        accessibility: {
            point: {
                valueSuffix: '%'
            }
        },
        plotOptions: {
            dataLabels: {
                enabled: true,
                format: '{y}%'
            },
        },
        series: [{
            type: 'pie',
            name: 'Browser share',
            innerSize: '50%',
            data: [
                ['High Risk', 58.9],
                ['Medium Risk', 58.9],
                ['Low Risk', 58.9],
            ]
        }]
    });

    --}}

    $('.int-lookup-main').hide();

    $('.lookup').on('click',function(){
        $('.int-lookup-main').toggle();
        loadSearchAPI('ibmcloud');
        loadSearchAPI('virustotal');
        {{-- loadSearchAPI('hybrid'); --}}
    });

    var text_search_new = '{{request()->keyword}}';
    function loadSearchAPI(source){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'post',
            url: "/loadSearchAPI",
            data: ({
                keyword:text_search_new,
                source:source
            }),
            beforeSend: function(){
                $('.ajax-loading').show();
            },
        }).done(function(res){
            let data = res.data;
            if(source == 'ibmcloud'){
                if(data.score && data.score > 0){
                    $('#text_' + source).text(data.score * 10);
                }
                
                let html = ``;
                let historyByCats = [];
                for(let i in data.history){
                    const history = data.history[i];
                    if(history.cats[Object.keys(history.cats)] != undefined && !historyByCats.includes(history.cats[Object.keys(history.cats)])){
                        historyByCats.push(history.cats[Object.keys(history.cats)]);
                        html += `
                        <tr>
                            <td>${Object.keys(history.cats)}</td>
                            <td>${history.reason}</td>
                            <td>
                                ${history.geo.country} ${history.geo.countrycode}
                            </td>
                            <td>
                                ${moment(new Date(history.created)).format('DD-MM-YYYY HH:MM:SS')}
                            </td>
                        </tr>
                        `;
                    }
                }
                document.getElementById("tbody-ibmcloud").innerHTML = html;
                $('#table-ibmcloud').DataTable({
                    "dom": 'tp',
                    "searching": false,
                    "bPaginate": true,
                    "bLengthChange": false,
                    "bFilter": false,
                    "bInfo": false,
                    "bAutoWidth": false ,
                });
                $('.load-ibmcloud').remove();
            }else if(source == 'virustotal'){
                if(data.data.attributes.last_analysis_stats.harmless && data.data.attributes.last_analysis_stats.harmless > 0){
                    $('#text_' + source).text(data.data.attributes.last_analysis_stats.harmless);
                }
                let html = ``;
                for(let i in data.data.attributes.last_analysis_results){
                    const last_analysis_results = data.data.attributes.last_analysis_results[i];
                    html += `
                    <tr>
                        <td>${last_analysis_results.engine_name}</td>
                        <td>${last_analysis_results.category}</td>
                        <td>
                            ${last_analysis_results.method} 
                        </td>
                        <td>`;
                            if(last_analysis_results.result == 'malicious'){
                                html += `<span class="label label-danger">
                                    ${last_analysis_results.result}
                                </span>`; 
                            }else if(last_analysis_results.result == 'clean'){
                                html += `<span class="label label-success">
                                    ${last_analysis_results.result}
                                </span>`;
                            }else if(last_analysis_results.result == 'unrated'){
                                html += `<span class="label label-secondary">
                                    ${last_analysis_results.result}
                                </span>`;
                            }
                        html += `</td>
                    </tr>
                    `;
                }
                document.getElementById("tbody-virustotal").innerHTML = html;
                $('#table-virustotal').DataTable({
                    "dom": 'tp',
                    "searching": false,
                    "bPaginate": true,
                    "bLengthChange": false,
                    "bFilter": false,
                    "bInfo": false,
                    "bAutoWidth": false ,
                });
                $('.load-virustotal').remove();
            }else if(source == 'hybrid'){
                $('.load-hybrid').remove();
            }
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            console.log("No response from server");
        });
    }



    $('.table-hybrid').hide();
    $('.table-virustotal').hide();
    $('.table-ibmcloud').hide();

    $('#show-chart-int .main-int-card').on('click',function(){
        $(this).toggleClass('active');
        if($('#card-search-int1').hasClass('active')){
            $('.table-hybrid').show();
        }else{
            $('.table-hybrid').hide();
        }
        if($('#card-search-int2').hasClass('active')){
            $('.table-virustotal').show();
        }else{
            $('.table-virustotal').hide();
        }
        if($('#card-search-int3').hasClass('active')){
            $('.table-ibmcloud').show();
        }else{
            $('.table-ibmcloud').hide();
        }
    });
</script>
@endpush
@endsection
