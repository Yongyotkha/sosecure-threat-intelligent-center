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

            <div class="front-int">
                {{-- <img src="{{asset('images/logo_threat/logo_site.png')}}" style="max-width: 50%;"><br> --}}
                <button class="btn-start-lookup">Inteligence Threat Lookup</button>
            </div>

            <div class="int-lookup-main" style="display: none">
 
                <div class="back-int">

                    <section id="overall-int">
                        <h3 class="page-header">
                            Overall Risk
                        </h3>
                        
                        <div class="row" style="margin-bottom: 1.5rem">

                            <div class="col-md-6">
                                <div class="risk-level-box">
                                    <div class="header-risk">
                                        <h4 class="font-weight-bold text-danger">High Risk Level</h4>
                                        <h4 class="font-weight-bold text-danger">100%</h4>
                                    </div>
                                    <hr>
                                    <div class="risk-level-body">
                                        <div class="high-risk-level">
                                            <div>
                                                <span class="circle-risk high"></span>
                                                <span>High Risk</span>
                                            </div>
                                            <div>
                                                <span class="circle-risk medium"></span>
                                                <span>Medium Risk</span>
                                            </div>
                                            <div>
                                                <span class="circle-risk low"></span>
                                                <span>Low Risk</span>
                                            </div>
                                        </div>
                                        <div class="risk-chart-box">
                                            <div id="chart-risk-level" style="width:100%;height: 100%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="col-md-3">
                                <div class="card-int dark">
                                    <div class="card-int-header">
                                        <span>Summary Score</span>
                                    </div>
                                    <div class="card-int-body">
                                        <div class="icon">
                                              <i class="fas fa-sliders-h"></i> 
                                        </div>
                                        <div class="score">
                                          5
                                        </div>
                                    </div>
                                </div>
                            </div>
                       
                            <div class="col-md-3">
                                <div class="card-int green">
                                    <div class="card-int-header">
                                        <span>Normal Risk</span>
                                    </div>
                                    <div class="card-int-body">
                                        <div class="icon">
                                            <i class="fas fa-sliders-h"></i> 
                                        </div>
                                        <div class="score">
                                        5
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card-int warning">
                                    <div class="card-int-header">
                                        <span>Medium Risk</span>
                                    </div>
                                    <div class="card-int-body">
                                        <div class="icon">
                                            <i class="fas fa-sliders-h"></i> 
                                        </div>
                                        <div class="score">
                                        5
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="card-int danger">
                                    <div class="card-int-header">
                                        <span>High Risk</span>
                                    </div>
                                    <div class="card-int-body">
                                        <div class="icon">
                                            <i class="fas fa-sliders-h"></i> 
                                        </div>
                                        <div class="score">
                                        5
                                        </div>
                                    </div>
                                </div>
                            </div> --}}

                        </div>
                    </section>

                    <section id="collection-overview">
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
                    </section>


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

                                            {{-- <div id="chart-md" style="height: 300px"></div> --}}
                                            <div class="circle-score">
                                                <div class="circle-score-inner">
                                                    <div class="circle-text-score">
                                                        100 <span>/ 80</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="details">
                                                <div class="scan-result">No threat found</div>
                                            </div>
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

                                            {{-- <div id="chart-md2" style="height: 300px"></div> --}}

                                            <div class="circle-score">
                                                <div class="circle-score-inner">
                                                    <div class="circle-text-score">
                                                        0 <span>/ 80</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="details">
                                                <div class="scan-result">4 of 7 are detected as malicious</div>
                                            </div>
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

                                            {{-- <div id="chart-md3" style="height: 300px"></div> --}}

                                            <div class="circle-score">
                                                <div class="circle-score-inner">
                                                    <div class="circle-text-score">
                                                        50 <span>/ 80</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="details">
                                                <div class="scan-result">4 of 7 are detected as malicious</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </section>
   
                    <section class="table-int" style="display: none">
                        <table id="table-int" class="table">
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
                            <tbody>
                                <tr>
                                    <td>ldap60.bin</td>
                                    <td>4e753ef8...e0b17943</td>
                                    <td>-</td>
                                    <td>24% Zusy.Generic</td>
                                    <td><i class="text-danger fas fa-times"></i></td>
                                    <td>
                                        <span class="label label-danger">
                                            malicious
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>ldap60.bin</td>
                                    <td>4e753ef8...e0b17943</td>
                                    <td>-</td>
                                    <td>24% Zusy.Generic</td>
                                    <td><i class="text-success fas fa-check"></i></td>
                                    <td>
                                        <span class="label label-danger">
                                            malicious
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>ldap60.bin</td>
                                    <td>4e753ef8...e0b17943</td>
                                    <td>-</td>
                                    <td>24% Zusy.Generic</td>
                                    <td><i class="text-success fas fa-check"></i></td>
                                    <td>
                                        <span class="label label-danger">
                                            malicious
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>ldap60.bin</td>
                                    <td>4e753ef8...e0b17943</td>
                                    <td>-</td>
                                    <td>24% Zusy.Generic</td>
                                    <td><i class="text-success fas fa-check"></i></td>
                                    <td>
                                        <span class="label label-warning">
                                            suspicious
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>ldap60.bin</td>
                                    <td>4e753ef8...e0b17943</td>
                                    <td>-</td>
                                    <td>24% Zusy.Generic</td>
                                    <td><i class="text-success fas fa-check"></i></td>
                                    <td>
                                        <span class="label label-info">
                                            whitelisted
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </section>
                </div>
            </div>

            <section class="panel panel-default m-b-xs">
                <div class="panel-body" id="table-container">
                    <div class="row">
                        <div class="col-md-12">
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

    $('.btn-start-lookup').on('click',function(){
        $('.int-lookup-main').show();
        $('.front-int').remove();
    });


    $('.table-int').hide();

    $('#show-chart-int .main-int-card').on('click',function(){
        $(this).toggleClass('active');
        if($('.main-int-card').hasClass('active')){
            $('.table-int').show();
        }else{
            $('.table-int').hide();
        }
    });

    $('#table-int').DataTable();

</script>
@endpush
@endsection

