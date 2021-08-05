@extends('layouts.app')
@section('content')

<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
            <div class="header-flex-overflow m-t-10">
                <div class="fwb-16">
                    <span>
                        Phishing Detection
                    </span>
                </div>

                <div class="ml-2 text-right">
                    {{-- <a id="to_top" href="#area_search" class="">test</a> --}}
                    <div class="text-left max-w-select m-r-xs" style="display:inline-block">
                        <select name="site" id="site" class="text-left select2-option form-control select-site" onchange="changeSite(value)">
                            <option value="">All Site</option>
                            {{-- @if ($site_settings)

                            @foreach ($site_settings as $site_settings)
                            <option value="{{$site_settings->id}}">{{$site_settings->name}}
                            </option>
                            @endforeach

                            @endif --}}
                        </select>
                    </div>
                    
                    <a id="advance-search" href="#hide-advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }}">
                        <span data-rel="tooltip" title="Filter" data-placement="bottom"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></span>
                    </a>

                </div>
            </div>
        </header>

        {{-- Tab Content --}}
        <section class="scrollable wrapper">

            <section class="panel panel-default" id="hide-advance-search" style="display: none;">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-md-12">
                            <i class="fas fa-filter"></i> Filter
                        </div>
                </header>
                <div class="panel-body" style="padding: 0 !important">
                    <div class="container-fluid" style="padding: 2rem;">

                        <div class="row">
                            <div class="col-lg-6">
                                <div>
                                    <div class="form-group m-b-md">
                                        <label for="" class="">URL</label>
                                        <input type="text" class="form-control" name="" id=""
                                            placeholder="Search">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div>
                                    <div class="form-group m-b-md">
                                        <label for="" class="">IP</label>
                                        <input type="text" class="form-control" name="" id=""
                                            placeholder="Search">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 col-md-6 mb-1">
                                <h5 class="font-weight-bold">Severity</h5>
                                <div id="btngroup_sort_by" class="btn-group special mb-2">
                                    <button class="btn btn-grey filter_agent_all active" value="" id="btn_search_all">
                                        <span> All </span>
                                    </button>
                                    <button class="btn  btn-grey" value="">
                                        <span> Information </span>
                                    </button>
                                    <button class="btn  btn-grey" value="">
                                        <span> Low </span>
                                    </button>
                                    <button class="btn  btn-grey" value="">
                                        <span> Medium </span>
                                    </button>
                                    <button class="btn  btn-grey" value="">
                                        <span> High </span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 mb-1">
                                <div>
                                    <h5 class="font-weight-bold">Type</h5>
                                    <div id="filter-type" class="btn-group special">
                                        <button class="btn btn-grey check_alert active" id="all_type" value="">
                                            <span> All </span>
                                        </button>
                                        <button class="btn btn-grey check_alert" value="1">
                                            <span> Referrer </span>
                                        </button>
                                        <button class="btn btn-grey check_alert" value="1">
                                            <span> Threat Feed </span>
                                        </button>
                                        <button class="btn btn-grey check_alert" value="2">
                                            <span> Domain name </span>
                                        </button>
                                        <button class="btn btn-grey check_alert" value="2">
                                            <span> other </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="filter_alert_main" class="row" style="display: none">
                            <div class="col-lg-4">
                                <div>
                                    <div class="form-group m-b-md">
                                        <label for="" class="">Site Name</label>
                                        <input type="text" class="form-control" name="keyword" id="keyword"
                                            placeholder="Search">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div>
                                    <div class="form-group m-b-md">
                                        <label for="" class="">Description</label>
                                        <input type="text" class="form-control" name="keyword" id="keyword"
                                            placeholder="Search">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label for="" class="">Description</label>
                                <div id="filter-alert" class="btn-group special">
                                    <button class="btn btn-grey check_alert active" id="all" value="">
                                        <span> All </span>
                                    </button>
                                    <button class="btn btn-grey check_alert" value="1">
                                        <span> Scan </span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div id="filter_agent_main" class="row"  style="display: none">
                            <div class="col-lg-3">
                                <div>
                                    <div class="form-group m-b-md">
                                        <label for="" class="">Site Name</label>
                                        <input type="text" class="form-control" name="keyword" id="keyword"
                                            placeholder="Search">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div>
                                    <div class="form-group m-b-md">
                                        <label for="" class="">Device Name</label>
                                        <input type="text" class="form-control" name="keyword" id="keyword"
                                            placeholder="Search">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div>
                                    <div class="form-group m-b-md">
                                        <label for="" class="">IP</label>
                                        <input type="text" class="form-control" name="keyword" id="keyword"
                                            placeholder="Search">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <label for="" class="">OS Type</label>
                                <div id="filter-alert" class="btn-group special">
                                    <button class="btn btn-grey check_os_type active" id="all_os" value="">
                                        <span> All </span>
                                    </button>
                                    <button class="btn btn-grey check_os_type" value="1">
                                        <span> Scan </span>
                                    </button>
                                    <button class="btn btn-grey check_os_type" value="1">
                                        <span> Window </span>
                                    </button>
                                </div>
                            </div>

                         
                        </div>
                        
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-lg-12 text-right">
                            <button type="button" class="btn btn-info btn-responsive btn-fz-13" onclick="search()">
                                <i class="fas fa-search"></i>
                                @langapp('apply')
                            </button>
                            <button type="button" id="btn_rss_news_reset" class="btn btn-default btn-responsive btn-fz-13"
                                style="white-space: nowrap">
                                <i class="fas fa-broom"></i>
                                <span> Clear </span>
                            </button>
                            <button type="button" id="close_filter" class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                <i class="fas fa-times"></i>
                                <span> Close </span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section class="m-b-10">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <div class="row">
                            <div class="col-md-6 col-lg-6 mb-small-5px">
                                {{-- <div class="loadhost backdrop-loader">
                                    <div class="loader4 centerloader"></div>
                                    <div class="loadding-text">Loading ...</div>
                                </div> --}}
                                <div class="box-chart-color bg-white">
                                    <div class="header-chart-p">
                                        <div class="d-flex align-items-center ">
                                            <img src="{{asset('images/bar-chart.png')}}" alt="" height="30px">
                                            <h1 class="text-blue bold-500">Timeline</h1>
                                        </div>
                                        <div id="filter-chart-btn" class="btn-group pull-right" style="margin-top: -25px;">
                                            <a href="javascript:void(0)" class="btn btn-xs btn-chart-fil active">
                                                <i  class="far fa-calendar"></i> Day
                                             </a>
                                            <a href="javascript:void(0)"  class="btn btn-xs btn-chart-fil"><i class="far fa-calendar"></i>
                                                Month
                                             </a>
                                        </div>
                                    </div>

                                    <div class="divider-dark"></div>
                                    <div id="chart-timeline" class="h-chart"></div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6 mb-small-5px">
                                {{-- <div class="category backdrop-loader">
                                    <div class="loader4 centerloader"></div>
                                    <div class="loadding-text">Loading ...</div>
                                </div> --}}
                                <div class="box-chart-color bg-white">
                                    <div class="d-flex align-items-center header-chart-p">
                                        <img src="{{asset('images/bar-chart.png')}}" alt="" height="30px">
                                        <h1 class="text-blue bold-500">Phishing Type</h1>
                                    </div>
                                    <div class="divider-dark"></div>
                                    <div id="chart-type" class="h-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>


            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-xs-12">
                            <i class="fas fa-table"></i> Table Phishing Detection
                        </div>
                    </div>
                </header>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-phishing-template" style="width: 100%">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>URL</th>
                                    <th>IP Address</th>
                                    <th>Type</th>
                                    <th>Score</th>
                                    <th>Severity</th>
                                    <th>Date</th>
                                    <th style="width: 10%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div style="position: relative;width:150px;">
                                            <img src="{{asset('images/webtest3.png')}}" alt="" style="width: 100%">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex-actor">
                                            <strong class="text-elip-phi">
                                                https://online.kasikornbankgroup.com/K-Online/login.jsp?lang=TH
                                            </strong>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>171.123.143.38</strong>
                                    </td>
                                    <td>
                                        <strong> Referrer </strong>
                                    </td>
                                    <td>
                                        <strong> 50 </strong>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: #fcc838;">Medium</span>
                                    </td>
                                    <td>
                                        <strong> 2021-08-08 22:33</strong>
                                    </td>
                                    <td class="text-nowrap text-center">
                                        <a target="_blank" href="https://online.kasikornbankgroup.com/K-Online/login.jsp?lang=TH" class="btn btn-info btn-xs">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div style="position: relative;width:150px;">
                                            <img src="{{asset('images/webtest1.png')}}" alt="" style="width: 100%">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex-actor">
                                            <strong class="text-elip-phi">
                                                http://e-tracking.customs.go.th/ETS/index.jsp?lang=th&left_menu=nmenu_esevice_002
                                            </strong>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>171.123.143.38</strong>
                                    </td>
                                    <td>
                                        <strong> Threat Feed </strong>
                                    </td>
                                    <td>
                                        <strong> 20 </strong>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: #e64732;">High</span>
                                    </td>
                                    <td>
                                        <strong> 2021-05-08 12:05</strong>
                                    </td>
                                    <td class="text-nowrap text-center">
                                        <a target="_blank" href="http://e-tracking.customs.go.th/ETS/index.jsp?lang=th&left_menu=nmenu_esevice_002" class="btn btn-info btn-xs">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div style="position: relative;width:150px;">
                                            <img src="{{asset('images/webtest2.png')}}" alt="" style="width: 100%">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex-actor">
                                            <strong class="text-elip-phi">
                                                https://sosecure.cloudflareaccess.com/cdn-cgi/access/login/www.sosecure.co.th?kid=a1955092418b0afd7188f09818f844ad5c2a46dacdc474fb45fe0f4e0fa01d90&redirect_url=%2Fwp-login.php&meta=eyJraWQiOiJkZjk5ZTBmNzYwYmViYmE2M2UzZTQwMzgwZTlmYTBiNTBkMzI4YjUxMmIzYzBmZjQzMWFhMTQ0NjY5OWU4MGM0IiwiYWxnIjoiUlMyNTYiLCJ0eXAiOiJKV1QifQ.eyJzZXJ2aWNlX3Rva2VuX3N0YXR1cyI6ZmFsc2UsImlhdCI6MTYyODA3OTM5Miwic2VydmljZV90b2tlbl9pZCI6IiIsImF1ZCI6ImExOTU1MDkyNDE4YjBhZmQ3MTg4ZjA5ODE4Zjg0NGFkNWMyYTQ2ZGFjZGM0NzRmYjQ1ZmUwZjRlMGZhMDFkOTAiLCJpc19nYXRld2F5IjpmYWxzZSwibmJmIjoxNjI4MDc5MzkyLCJ0eXBlIjoibWV0YSIsImlzX3dhcnAiOmZhbHNlLCJhdXRoX3N0YXR1cyI6Ik5PTkUifQ.dRiTcWHpHQCBOUjkHnUU8xpRWeFacr7rQY1xn37o-Yww7PurqyDOMTBp_KcsJcGxOj1baqrz5gnftAShwtWAxr8lTrt35Kip8Bz70MDJp1uTXbLyJ5009iDrLF4S3Mb00I4dUZuchf1aB0M1ktgQ_eQW_eJHyi-05xrh5H2X2iWnhPywV4PP1Itwq7MpMaJAS01-CpJhoE9KQAWW6SJjCqbuxlayJn2J9nmUI4FytbZSQMnBdP6TU0Felv3ZEZ-F4kW_7Mr5E-7M9e-InRCIQn4bogi6TMvIAx_GdcO2JJLbeEjsTZDEwcEwx7fppz1U3vBozffp4cwDU940n4x0-A&v=60142ad2
                                            </strong>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>171.123.143.38</strong>
                                    </td>
                                    <td>
                                        <strong> Threat Feed </strong>
                                    </td>
                                    <td>
                                        <strong> 20 </strong>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: #88ce4f;">Low</span>
                                    </td>
                                    <td>
                                        <strong> 2021-04-02 08:44</strong>
                                    </td>
                                    <td class="text-nowrap text-center">
                                        <a target="_blank" href="https://sosecure.cloudflareaccess.com/cdn-cgi/access/login/www.sosecure.co.th?kid=a1955092418b0afd7188f09818f844ad5c2a46dacdc474fb45fe0f4e0fa01d90&redirect_url=%2Fwp-login.php&meta=eyJraWQiOiJkZjk5ZTBmNzYwYmViYmE2M2UzZTQwMzgwZTlmYTBiNTBkMzI4YjUxMmIzYzBmZjQzMWFhMTQ0NjY5OWU4MGM0IiwiYWxnIjoiUlMyNTYiLCJ0eXAiOiJKV1QifQ.eyJzZXJ2aWNlX3Rva2VuX3N0YXR1cyI6ZmFsc2UsImlhdCI6MTYyODA3OTM5Miwic2VydmljZV90b2tlbl9pZCI6IiIsImF1ZCI6ImExOTU1MDkyNDE4YjBhZmQ3MTg4ZjA5ODE4Zjg0NGFkNWMyYTQ2ZGFjZGM0NzRmYjQ1ZmUwZjRlMGZhMDFkOTAiLCJpc19nYXRld2F5IjpmYWxzZSwibmJmIjoxNjI4MDc5MzkyLCJ0eXBlIjoibWV0YSIsImlzX3dhcnAiOmZhbHNlLCJhdXRoX3N0YXR1cyI6Ik5PTkUifQ.dRiTcWHpHQCBOUjkHnUU8xpRWeFacr7rQY1xn37o-Yww7PurqyDOMTBp_KcsJcGxOj1baqrz5gnftAShwtWAxr8lTrt35Kip8Bz70MDJp1uTXbLyJ5009iDrLF4S3Mb00I4dUZuchf1aB0M1ktgQ_eQW_eJHyi-05xrh5H2X2iWnhPywV4PP1Itwq7MpMaJAS01-CpJhoE9KQAWW6SJjCqbuxlayJn2J9nmUI4FytbZSQMnBdP6TU0Felv3ZEZ-F4kW_7Mr5E-7M9e-InRCIQn4bogi6TMvIAx_GdcO2JJLbeEjsTZDEwcEwx7fppz1U3vBozffp4cwDU940n4x0-A&v=60142ad2" class="btn btn-info btn-xs">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
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
@include('stacks.css.summernote')
@include('stacks.css.highchart')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('scripts.summernote')
@include('stacks.js.markdown')
@include('stacks.js.hidesettings')
@include('stacks.js.daterangpicker')
@include('stacks.js.activebutton')
@include('stacks.js.advanced_search')
@include('stacks.js.highchart')

<script>

active_btn('#btngroup_sort_by .btn-grey');
active_btn('#filter-type .btn-grey');


if($('.filter_agent_agent').hasClass('active')){
    $('#filter_alert_main').hide();
    $('#filter_agent_main').show();
} else if($('#category_btn').hasClass('active')){
    $('#filter_alert_main').show();
    $('#filter_agent_main').hide();
}else if($('#btn_search_all').hasClass('active')){
    $('#filter_alert_main').hide();
    $('#filter_agent_main').hide();
}

$('#btngroup_sort_by .btn-grey').on('click',function(){
    if($('.filter_agent_agent').hasClass('active')){
    $('#filter_alert_main').hide();
    $('#filter_agent_main').show();
} else if($('.filter_agent_alert ').hasClass('active')){
    $('#filter_alert_main').show();
    $('#filter_agent_main').hide();
}else if($('#btn_search_all').hasClass('active')){
    $('#filter_alert_main').hide();
    $('#filter_agent_main').hide();
}
});

$('#table-phishing-template').DataTable();

function mychart(myid){
    const chart_top_source = Highcharts.chart(myid, {
        title: {
            text: ''
        },
        yAxis: {
            title: {
                text: ''
            }
        },
        xAxis: {
            categories: ['7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18','19','20','21','22','23','24','25','26','27','28','29','30','31','1','2','3','4','5'],
            accessibility: {
                rangeDescription: ''
            }
        },

        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle',
            enabled: false
        },
        plotOptions: {
            series: {
                label: {
                    connectorAllowed: false
                },
                pointStart: 0
            }
        },


        series: [{
            name: 'Alert',
            data: [0,0,0,0,0,5,0,0,0,0,0,0,10,0,50,30,0,0,0,0,0,0,0,0,0,0,0,0,0,7],
            type: 'area',
            fillColor: '#c8dcf17d',
        }],

        responsive: {
            rules: [{
                condition: {
                    maxWidth: 500
                },
                chartOptions: {
                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    }
                }
            }]
        }
    });
}

function circle_chart(id){
    const chart_pie = new Highcharts.chart(id, {
        chart: {
            height: 223, 
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: ''
        },
        tooltip: {
            pointFormat: 'Amount {point.y}: <b>{point.percentage:.1f}%</b>'
        },
        accessibility: {
            point: {
                valueSuffix: '%'
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                color: ['#e64732', '#fcc838', '#00dcff', '#88ce4f', '#d3d3d3'],
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                },
            }
        },
        series: [{
            colorByPoint: false,
            data: [
            {  name: 'Referrer', y: 5, color: '#e64732'}, 
            {  name: 'Threat Feed',  y: 4 , color: '#fcc838'}, 
            {  name: 'Domain name', y: 4, color: '#00dcff'  }, 
            {  name: 'other', y: 4, color: '#c1c0c0'  }, 
            ]
        }],
    });
}


circle_chart('chart-type');
mychart('chart-timeline');



</script>
@endpush
@endsection