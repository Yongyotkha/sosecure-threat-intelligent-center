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
                <button class="btn btn-info lookup" style="display:none;"><i class="fas fa-search"></i> Threat Lookup</button>
            </span>
        </header>
        <section class="scrollable wrapper bg" id="clauses" style="padding: 8px !important">

            {{-- <div class="front-int">
                <button class="btn-start-lookup">Inteligence Threat Lookup</button>
            </div> --}}

            <section class="panel panel-default m-b-xs" id="table-container">
                <div class="panel-body">
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

     
            <div class="int-lookup-main" style="display: none">
                <div class="back-int">
                    <section id="overall-int">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="page-header">
                                    Information ( 100 / 1000 )
                                </h3>
                            </div>
                            <div class="col-md-4 text-right">
                                <span class="st-dt-leak">
                                    <span class="st-dt vrh" data-toggle="tooltip" data-placement="left" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip vrh'>High</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้อย่างแพร่หลาย เช่น <br> ออกข่าว หรือมีการแชร์ข้อมูลจากแหล่งข้อมูลที่น่าเชื่อถือและเป็นที่แพร่หลาย <br> และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูล Username ,Password ของลูกค้าหรือเจ้าหน้าที่ดูแลระบบภายในองค์กรซึ่งเป็นข้อมูลที่สามารถนำมาใช้ได้จริง</div></div>">High</span>
                                    {{-- <span class="st-dt high" data-toggle="tooltip" data-placement="left" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip high'>High</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้อย่างแพร่หลาย เช่น <br> ออกข่าว หรือมีการแชร์ข้อมูลจากแหล่งข้อมูลที่น่าเชื่อถือและเป็นที่แพร่หลาย <br> และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูล Username ,Password ของลูกค้าหรือเจ้าหน้าที่ดูแลระบบภายในองค์กรซึ่งเป็นข้อมูลที่สามารถนำมาใช้ได้จริง</div></div>">High</span> --}}
                                    <span class="st-dt md" data-toggle="tooltip" data-placement="left" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip md'>Medium</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้ภายในกลุ่มจำกัดหรือยังไม่เป็นที่รับรู้กันอย่างแพร่หลาย และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูล Username ,Password ของลูกค้าหรือเจ้าหน้าที่ดูแลระบบภายในองค์กรซึ่งเป็นข้อมูลที่สามารถนำมาใช้ได้จริง</div></div>">Medium</span>
                                    <span class="st-dt low" data-toggle="tooltip" data-placement="left" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip low'>Low</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้ภายในกลุ่มจำกัดหรือยังไม่เป็นที่รับรู้กันอย่างแพร่หลาย และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของลูกค้าเช่น ข้อมูลส่วนบุคคลซึ่งเป็นข้อมูลที่สามารถนำมาใช้ประโยชน์ต่อได้</div></div>">Low</span>
                                </span>
                            </div>
                        </div>


                        <div class="row">
                            <div class="col-md-8">
                                <div class="ovr-text">
                                    <p>Analysis</p>
                                    <p>{{ request()-> keyword }}</p>
                                </div>
                                <div class="ovr-text">
                                    <p>Type</p>
                                    <p id="type_search"></p>
                                </div>
                                {{-- <div class="ovr-text">
                                    <p>Daily quota</p>
                                    <p>172800 requests/day</p>
                                </div>
                                <div class="ovr-text">
                                    <p>Monthly quota</p>
                                    <p>172800 requests/month</p>
                                </div> --}}
                            </div>
                            <div class="col-md-4 text-right">
                                <div id="text_status_risk"></div>
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
                                            <img src="{{asset('images/hybrid.svg')}}" alt="" class="icon">
                                        </div>
                                        <div class="main-frame">

                                            <div class="load-hybrid loadfixed backdrop-loader white">
                                                <div class="loader4 centerloader"></div>
                                                <div class="loadding-text">Loading ...</div>
                                            </div>

                                            
                                            <div class="loadfixed backdrop-loader white" id="not-hybrid" style="display: none">
                                                <h1>No threat found</h1>
                                            </div>

                                            {{-- <div id="chart-md" style="height: 300px"></div> --}}
                                            <div class="circle-score" id="circle-hybrid">
                                                <div class="circle-score-inner">
                                                    <div class="circle-text-score">
                                                        <span id="text_hybrid">0</span> <span>/ 1</span>
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
                                            <img src="{{asset('images/virus.jpg')}}" alt="" class="icon">
                                        </div>
                                        <div class="main-frame">

                                            <div class="load-virustotal loadfixed backdrop-loader white">
                                                <div class="loader4 centerloader"></div>
                                                <div class="loadding-text">Loading ...</div>
                                            </div>

                                            <div class="loadfixed backdrop-loader white" id="not-virustotal" style="display: none">
                                                <h1>No threat found</h1>
                                            </div>

                                            {{-- <div id="chart-md2" style="height: 300px"></div> --}}

                                            <div class="circle-score" id="circle-virustotal">
                                                <div class="circle-score-inner">
                                                    <div class="circle-text-score">
                                                        <span id="text_virustotal">0</span> <span id="text_virustotal_sum">/ 0</span>
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
                                            <img src="{{asset('images/ibm.png')}}" alt="" class="icon">

                                            IBM X-Force
                                        </div>
                                        <div class="main-frame">

                                            <div class="load-ibmcloud loadfixed backdrop-loader white">
                                                <div class="loader4 centerloader"></div>
                                                <div class="loadding-text">Loading ...</div>
                                            </div>

                                            {{-- <div id="chart-md3" style="height: 300px"></div> --}}

                                            <div class="loadfixed backdrop-loader white" id="not-ibmcloud" style="display: none">
                                                <h1>No threat found</h1>
                                            </div>

                                            <div class="circle-score" id="circle-ibmcloud">
                                                <div class="circle-score-inner">
                                                    <div class="circle-text-score">
                                                        <span id="text_ibmcloud">0</span> <span>/ 10</span>
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
                        <table id="table-ibmcloud-malware" class="table" style="display: none">
                            <thead>
                                <tr>
                                    <th>Hash Type</th>
                                    <th>First Seen</th>
                                    <th>Last Seen</th>
                                    <th>Family Name</th>
                                    <th>Type</th>
                                    <th>Community Coverage</th>
                                    <th>Platform</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-ibmcloud-malware"></tbody>
                        </table>
                        <table id="table-ibmcloud-url" class="table" style="display: none">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>URL</th>
                                    <th>Category DesCriptions</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-ibmcloud-url"></tbody>
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
                                    <th>Environment Description</th>
                                    <th>SHA256</th>
                                    <th>Submit Name</th>
                                    <th>Type Short</th>
                                    <th>Threat Score</th>
                                    <th>AV Detection</th>
                                    <th>Verdict</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-hybrid"></tbody>
                        </table>
                        <table id="table-hybrid-url" class="table" style="display: none">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>URL</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-hybrid-url"></tbody>
                        </table>
                    </section>

                    <section class="scrollable wrapper bg-white otx_indicators" style="padding:0;margin-top: 1.5rem">
                        <div class="row sticky-top-nav">
                            <div class="nav-menu-btn" style="display: none">
                                <ul>
                                    <li class="nav-link active-link">
                                    <a id="to_top" href="#general_details">Indicator</a>
                                    <div class="underline"></div>
                                    </li>
                                    <li class="nav-link" style="display: none;">
                                    <a href="#related_event" id="event_tag">Event</a>
                                    <div class="underline"></div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <section id="general_details" class="">
                            <div id="indicator_basic_info" class="row m-b-lg">
                            <h1 class="b-b">&nbsp;&nbsp;&nbsp;Basic Information</h1>
                            <div id="otx_indicators_loadspinner_basic_info" class="content-spinner-loading" style="display: none;"></div>
                                <span id="otx_indicators_general"></span>
                            </div>
                        </section>
                        <section id="related_event" class="">
                            <h1 class="b-b">Related Event</h1>
                            <section class="panel panel-default" style="margin: 0px;">
                                <header class="panel-heading font-bold panel-header-blue">
                                    <div class="row">
                                    <div class="col-md-12">
                                        <div style="margin-top:5px;">
                                            <i class="fas fa-table">
                                            </i> Table Related 
                                        </div>
                                    </div>
                                    </div>
                                </header>
                                <div class="panel-body" style="padding: 0 !important">
                                    <div class="container-fluid" style="padding:1rem;">
                                    <div class="row m-b-md">
                                        <div class="col-sm-12">
                                            <div class="table-responsive">
                                            <div id="otx_indicators_loadspinner_basic_info_table" class="content-spinner-loading" style="display: none;"></div>
                                            <table class="table table-striped dataTable no-footer" id="table-related-event" role="grid" aria-describedby="table-related-event_info" style="display: none;" >
                                                    <thead>
                                                        <tr role="row">
                                                            <th class="sorting_disabled" rowspan="1" colspan="1">No</th>
                                                            <th class="sorting_disabled" rowspan="1" colspan="1">Event</th>
                                                            <th class="sorting_disabled" rowspan="1" colspan="1"></th>
                                                            <th class="sorting_disabled" rowspan="1" colspan="1"></th>
                                                            <th class="sorting_disabled text-center" rowspan="1" colspan="1"></th>
                                                            <th class="sorting_disabled" rowspan="1" colspan="1"></th>
                                                            <th style="width: 200px;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                                                            <th class="sorting_disabled" rowspan="1" colspan="1"></th>
                                                            <th class="sorting_disabled" rowspan="1" colspan="1">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                
                                                    
                                                        
                                                    </tbody>
                                                </table>
                                            
                                        </div>
                                    </div>
                                    </div>
                                </div>
                            </section>
                        </section>
                     </section>



                     <section class="scrollable wrapper bg-white otx_event" style="padding:0;display:none;margin-top:1rem;">
                                <div class="row sticky-top-nav">
                                    <div class="nav-menu-btn" style="display: none">
                                        <ul>
                                            <li class="nav-link active-link">
                                            <a id="to_top" href="#general_details">Event</a>
                                            <div class="underline"></div>
                                            </li>
                                            <li class="nav-link" style="display: none;">
                                            <a href="#related_event" id="event_tag">Event</a>
                                            <div class="underline"></div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <section id="general_details" class="">
                                    <div class="pd-15">
                                        <div class="row m-b-lg">
                                            <div id="indicator_basic_info" class="">
                                            <div id="otx_event_loadspinner_basic_info" class="content-spinner-loading" style="display: none;"></div>
                                                  <div class="row">
                                                                <div class="col-md-6">
                                                                <span id="otx_event_general"></span>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h1 class="text-center">Type Attributes </h1>
                                                                    <div id="otx_event_chart-show-bar">
                                                          
                                                                    </div>
                                                                </div>
                                                        </div>




                                            </div>
                                        </div>
                                    </div>
                                </section>
                                <section id="related_event" class="">
                                 <h1 class="b-b">Related Indicator</h1>
                                    <section class="panel panel-default" style="margin: 0px;">
                                        <header class="panel-heading font-bold panel-header-blue">
                                            <div class="row">
                                            <div class="col-md-12">
                                                <div style="margin-top:5px;">
                                                    <i class="fas fa-table">
                                                    </i> Table Related 
                                                </div>
                                            </div>
                                            </div>
                                        </header>
                                        <div class="panel-body" style="padding: 0 !important">
                                            <div class="container-fluid" style="padding:1rem;">
                                            <div class="row m-b-md">
                                                <div class="col-sm-12">
                                                    <div class="table-responsive">
                                                    <div id="otx_event_loadspinner_basic_info_table" class="content-spinner-loading" style="display: none;"></div>
                                                    <table class="table table-striped dataTable no-footer" id="table-related-indicator" role="grid" aria-describedby="table-related-event_info" style="display: none;" >
                                                            <thead>
                                                                <tr role="row">
                                                                    <th class="sorting_disabled" rowspan="1" colspan="1">Type</th>
                                                                    <th class="sorting_disabled" rowspan="1" colspan="1">Indicator</th>
                                                                    <th class="sorting_disabled" rowspan="1" colspan="1">Role</th>
                                                                    <th class="sorting_disabled" rowspan="1" colspan="1">Title</th>
                                                                    <th class="sorting_disabled" rowspan="1" colspan="1">Status</th>
                                                                    <th class="sorting_disabled" rowspan="1" colspan="1">Date</th>
                                                                    <th class="sorting_disabled" rowspan="1" colspan="1">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                     
                                                          
                                                              
                                                            </tbody>
                                                        </table>
                                                   
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                    </section>
                                </section>
                     </section>


                     <section class="scrollable wrapper bg-white otx_tag" style="padding:0;display:none">
                                <div class="row sticky-top-nav">
                                    <div class="nav-menu-btn">
                                        <ul>
                                            <li class="nav-link active-link">
                                            <a id="to_top" href="#general_details">Tags</a>
                                            <div class="underline"></div>
                                            </li>
                                            <li class="nav-link" style="display: none;">
                                            <a href="#related_event" id="event_tag">Event</a>
                                            <div class="underline"></div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <section id="general_details" class="">
                                    <div class="pd-15">
                                        <div class="row ">
                                            <div id="indicator_basic_info" class="">
                                            <div id="otx_tag_loadspinner_basic_info" class="content-spinner-loading" style="display: none;"></div>
     
                                                 
               
                                            <span id="otx_tag_general"></span>




                                            </div>
                                        </div>
                                    </div>
                                </section>
                                <section id="related_event" class="">
                                    <section class="panel panel-default" style="margin: 0px;">
                                        <header class="panel-heading font-bold panel-header-blue">
                                            <div class="row">
                                            <div class="col-md-12">
                                                <div style="margin-top:5px;">
                                                    <i class="fas fa-table">
                                                    </i> Table Tags 
                                                </div>
                                            </div>
                                            </div>
                                        </header>
                                        <div class="panel-body" style="padding: 0 !important">
                                            <div class="container-fluid" style="padding:1rem;">
                                            <div class="row m-b-md">
                                                <div class="col-sm-12">
                                                    <div class="table-responsive">
                                                    <div id="otx_tag_loadspinner_basic_info_table" class="content-spinner-loading" style="display: none;"></div>
                                                    <table class="table table-striped dataTable no-footer" id="table-related-tag" role="grid" aria-describedby="table-related-event_info" style="display: none;" >
                                                            <thead>
                                                                <tr role="row">
                                                                  <th class="sorting_disabled" rowspan="1" colspan="1">No</th>
                                                                    <th class="sorting_disabled" rowspan="1" colspan="1">Event</th>
                                                                    <th class="sorting_disabled" rowspan="1" colspan="1"></th>
                                                                    <th class="sorting_disabled" rowspan="1" colspan="1"></th>
                                                                    <th class="sorting_disabled text-center" rowspan="1" colspan="1"></th>
                                                                    <th class="sorting_disabled" rowspan="1" colspan="1"></th>
                                                                    <th style="width: 200px;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                                                                    <th class="sorting_disabled" rowspan="1" colspan="1"></th>
                                                                    <th class="sorting_disabled" rowspan="1" colspan="1">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                     
                                                          
                                                              
                                                            </tbody>
                                                        </table>
                                                        <div id="otx_tag_more_loadspinner_basic_info_table" class="content-spinner-loading" style="display: none;"></div>
                                                        <a href="javascript:void(0);" id="btn_load_tag_nextpag" onclick="f_load_tag_nextpage();" class="btn btn-xs btn-info" style="width: 100%;margin-right: 0px;margin-top: 10px;display:none">Load Data More</a>
                                                   
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                    </section>
                                </section>
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
                        @if(request()->mode !== 'lookup')
                            <div class="notfound">
                                <img src="{{asset('images/notfound.png')}}" alt="" style="max-width: 500px;width:100%:">
                                <h1>Sorry. no result found</h1>
                                <p>What you searched was unfortunately <br>not found or doesn't exist.</p>
                            </div>
                        @endif
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
@include('stacks.js.chart')
<script>
    active_btn('#fillter_click .btn-selector');
    var mode_search = '{{ request()->mode }}';
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

$(function(){
    if(mode_search == 'lookup'){
        $('#table-container').hide();
        $('.int-lookup-main').toggle();
        
        $('#accordion2').toggle();
        loadSearchAPI('ibmcloud');
        loadSearchAPI('virustotal');
        loadSearchAPI('hybrid');
        $('#otx_indicators_loadspinner_basic_info').show();
        $('#otx_indicators_loadspinner_basic_info_table').show();
        $("#table-related-event").hide();

        $('#otx_event_loadspinner_basic_info').show();
        $('#otx_event_loadspinner_basic_info_table').show();
        $("#table-related-indicator").hide();
        loadSearchAPI('otx_indicators');
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
        $('#table-container').hide();
        $('.int-lookup-main').toggle();
        
        $('#accordion2').toggle();
        loadSearchAPI('ibmcloud');
        loadSearchAPI('virustotal');
        loadSearchAPI('hybrid');
        $('#otx_indicators_loadspinner_basic_info').show();
        $('#otx_indicators_loadspinner_basic_info_table').show();
        $("#table-related-event").hide();

        $('#otx_event_loadspinner_basic_info').show();
        $('#otx_event_loadspinner_basic_info_table').show();
        $("#table-related-indicator").hide();
        loadSearchAPI('otx_indicators');
    });

    function click_hybrid(){
        $('#card-search-int1').on('click',function(){
            $(this).toggleClass('active');
            if($('#card-search-int1').hasClass('active')){
                $('.table-hybrid').show();
            }else{
                $('.table-hybrid').hide();
            }
        });      
    }
    function click_virustotal(){
        $('#card-search-int2').on('click',function(){
            $(this).toggleClass('active');
            if($('#card-search-int2').hasClass('active')){
                $('.table-virustotal').show();
            }else{
                $('.table-virustotal').hide();
            }
 
        });      
    }
    function click_ibmcloud(){
        $('#card-search-int3').on('click',function(){
            $(this).toggleClass('active');
            if($('#card-search-int3').hasClass('active')){
                $('.table-ibmcloud').show();
            }else{
                $('.table-ibmcloud').hide();
            }
        });      
    }


    var text_search_new = '{{request()->keyword}}';
    let status_value_ibmcloud = 0;
    let status_value_virustotal = 0;
    let status_value_hybrid = 0;
    let number_risk = 0;
    let number_new_row = 0;
    function loadSearchAPI(source){

$.ajax({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    url: "/loadSearchAPI",
    method: 'post',
    data: ({
        keyword:text_search_new,
        source:source
    }),
    beforeSend: function(){
        $('.ajax-loading').show();
    },
}).done(function(res){
    number_risk++;
    let data = res.data;
    $('#type_search').text(res.type);


    if(source == 'ibmcloud'){
        if(res.status_code == 400){
            $('.load-ibmcloud').remove();
        }else{
            if(data.error){
                $('#not-ibmcloud').show();
                $('#table-ibmcloud').DataTable({
                    "dom": 'tp',
                    "searching": false,
                    "bPaginate": true,
                    "bLengthChange": false,
                    "bFilter": false,
                    "bInfo": false,
                    "bAutoWidth": false ,
                });
            }else{
                number_new_row++;
                let num_total = 0;
                if(data.score && data.score > 0){
                    num_total = data.score;
                }else if(data.malware){
                    const malware = data.malware;
                    num_total = malware.origins.external.detectionCoverage == 0 ? 0 : malware.origins.external.detectionCoverage / 10;
                }else if(data.result.score > 0){
                    num_total = data.result.score;
                }
                
                $('#text_' + source).text(num_total);
                const elem = $("#circle-ibmcloud");
                if(num_total <= 3.9){
                    status_value_ibmcloud = 1;
                }else if(num_total <= 6.9){
                    status_value_ibmcloud = 2;
                    elem[0].style.removeProperty('background-color');
                    elem[0].style.setProperty('background-color', '#f2ff15', 'important');
                    $('#text_' + source).css('color', '#f2ff15');
                }else if(num_total >= 7.0){
                    status_value_ibmcloud = 3;
                    elem[0].style.removeProperty('background-color');
                    elem[0].style.setProperty('background-color', '#fcc838', 'important');
                    $('#text_' + source).css('color', '#fcc838');
                }
            }
            
            
            let html = ``;
            let historyByCats = [];
            if(data.history){
                for(let i in data.history){
                    const history = data.history[i];
                    html += `
                    <tr>`;
                        if(history.cats){
                            html += `<td>`;
                            for(const [key, value] of Object.entries(history.cats)){
                                html += ` ${key} (${value}%)`;
                            }
                            html += `</td>`;
                        }
                        
                        html += `<td>${history.reason}</td>
                        <td>
                            ${history.geo.country} ${history.geo.countrycode}
                        </td>
                        <td>
                            ${moment(new Date(history.created)).format('DD-MM-YYYY HH:MM:SS')}
                        </td>
                    </tr>
                    `;
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
            }else if(data.malware){
                $('#table-ibmcloud').hide();
                $('#table-ibmcloud-malware').show();
                const malware = data.malware;
                html += `
                <tr>
                    <td>${malware.type}</td>
                    <td>${moment(new Date(malware.origins.external.firstSeen)).format('DD-MM-YYYY HH:MM:SS')}</td>
                    <td>${moment(new Date(malware.origins.external.lastSeen)).format('DD-MM-YYYY HH:MM:SS')}</td>
                    <td>${malware.origins.external.family ? malware.origins.external.family[0] : '-'}</td>
                    <td>${malware.origins.external.malwareType ? malware.origins.external.malwareType : '-'}</td>
                    <td>${malware.origins.external.detectionCoverage ? malware.origins.external.detectionCoverage : '-'}</td>
                    <td>${malware.origins.external.platform ? malware.origins.external.platform : '-'}</td>
                </tr>
                `;
                document.getElementById("tbody-ibmcloud-malware").innerHTML = html;
                $('#table-ibmcloud-malware').DataTable({
                    "dom": 'tp',
                    "searching": false,
                    "bPaginate": true,
                    "bLengthChange": false,
                    "bFilter": false,
                    "bInfo": false,
                    "bAutoWidth": false ,
                });
            }else if(res.type == 'Domain'){
                $('#table-ibmcloud').hide();
                $('#table-ibmcloud-url').show();
                const domain = data.result;
                html += `
                <tr>
                    <td>${res.type}</td>
                    <td>${domain.url}</td>`;
                        if(domain.categoryDescriptions){
                            html += `<td>`;
                            for(const [key, value] of Object.entries(domain.categoryDescriptions)){
                                html += `${value}`;
                            }
                            html += `</td>`;
                        }
                        
                html += `</tr>
                `;
                document.getElementById("tbody-ibmcloud-url").innerHTML = html;
                $('#table-ibmcloud-url').DataTable({
                    "dom": 'tp',
                    "searching": false,
                    "bPaginate": true,
                    "bLengthChange": false,
                    "bFilter": false,
                    "bInfo": false,
                    "bAutoWidth": false ,
                });
            }else{
                $('#not-ibmcloud').show();
            }
            
            $('.load-ibmcloud').remove();
            click_ibmcloud();
        }
    }else if(source == 'virustotal'){
        if(res.status_code == 400){
            $('.load-virustotal').remove();
        }else{
            if(data.error){
                $('#not-virustotal').show();
                $('#table-virustotal').DataTable({
                    "dom": 'tp',
                    "searching": false,
                    "bPaginate": true,
                    "bLengthChange": false,
                    "bFilter": false,
                    "bInfo": false,
                    "bAutoWidth": false ,
                });
            }else{
                number_new_row++;
                if(data.data){
                    var last_analysis_stats = data.data.attributes.last_analysis_stats;
                    if(data.data.attributes.last_analysis_stats.malicious && data.data.attributes.last_analysis_stats.malicious > 0){
                        $('#text_' + source).text(data.data.attributes.last_analysis_stats.malicious);
                        const elem = $("#circle-virustotal");
                        if(last_analysis_stats.malicious > 0 && last_analysis_stats.malicious <= 3){
                            status_value_virustotal = 1;
                            elem[0].style.removeProperty('background-color');
                            elem[0].style.setProperty('background-color', '#b93624', 'important');
                            $('#text_' + source).css('color', '#b93624');
                        }else if(last_analysis_stats.malicious <= 5){
                            status_value_virustotal = 2;
                            elem[0].style.removeProperty('background-color');
                            elem[0].style.setProperty('background-color', '#b93624', 'important');
                            $('#text_' + source).css('color', '#b93624');
                        }else if(last_analysis_stats.malicious <= 7){
                            status_value_virustotal = 3;
                            elem[0].style.removeProperty('background-color');
                            elem[0].style.setProperty('background-color', '#b93624', 'important');
                            $('#text_' + source).css('color', '#b93624');
                        }else if(last_analysis_stats.malicious >= 10){
                            status_value_virustotal = 4;
                            elem[0].style.removeProperty('background-color');
                            elem[0].style.setProperty('background-color', '#b93624', 'important');
                            $('#text_' + source).css('color', '#b93624');
                        }
                    }
                    let total = (parseInt(last_analysis_stats.harmless) + parseInt(last_analysis_stats.malicious) + parseInt(last_analysis_stats.suspicious) + parseInt(last_analysis_stats.timeout) + parseInt(last_analysis_stats.undetected));
                    $('#text_virustotal_sum').text('/ '+total);
                    
                    
                    let html = ``;
                    let count_data_virus = 0;
                    for(let i in data.data.attributes.last_analysis_results){
                        count_data_virus++;
                        const last_analysis_results = data.data.attributes.last_analysis_results[i];
                        html += `
                        <tr>
                            <td>${last_analysis_results.engine_name}</td>
                            <td>${last_analysis_results.category}</td>
                            <td>
                                ${last_analysis_results.method} 
                            </td>
                            <td>`;
                                if(last_analysis_results.result == 'malicious' || last_analysis_results.result == 'phishing' || last_analysis_results.result == 'malware'){
                                    html += `<span class="label label-danger">
                                        ${last_analysis_results.result}
                                    </span>`; 
                                }else if(last_analysis_results.result == 'suspicious'){
                                    html += `<span class="label label-warning">
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
                                }else if(last_analysis_results.result == null){
                                    html += `<span class="label label-success">
                                        undetected
                                    </span>`;
                                }else{
                                    html += `<span class="label label-danger">
                                        ${last_analysis_results.result}
                                    </span>`; 
                                }
                            html += `</td>
                        </tr>
                        `;
                    }
                    if(count_data_virus == 0){
                        $('#not-virustotal').show();
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
                }
            }
            $('.load-virustotal').remove();
            click_virustotal();
        }
    }else if(source == 'hybrid'){
        if(res.status_code == 400){
            $('.load-hybrid').remove();
        }else{
            let html = ``;
            let total = 0;
            if(res.type == 'Domain'){
                $('#table-hybrid').hide();
                $('#table-hybrid-url').show();
                for(let i in data.search_terms){
                    const search_terms = data.search_terms[i];
                    total += data.count;
                    html += `
                    <tr>
                        <td>${search_terms.id}</td>
                        <td>${search_terms.value}</td>
                    </tr>
                    `;
                }
                document.getElementById("tbody-hybrid-url").innerHTML = html;
                $('#table-hybrid-url').DataTable({
                    "dom": 'tp',
                    "searching": false,
                    "bPaginate": true,
                    "bLengthChange": false,
                    "bFilter": false,
                    "bInfo": false,
                    "bAutoWidth": false ,
                });
            }else if(data.result){
                for(let i in data.result){
                    const results = data.result[i];
                    if(results.threat_score){
                        total += results.threat_score;
                    }else{
                        total += results.av_detect;
                    }
                    html += `
                    <tr>
                        <td>${results.environment_description}</td>
                        <td>${results.sha256}</td>
                        <td>${results.submit_name}</td>
                        <td>${results.type_short}</td>
                        <td>${results.threat_score ? results.threat_score : 0}/100</td>
                        <td>${results.av_detect ? results.av_detect : 0}%</td>
                        <td>${results.verdict}</td>
                        <td>${moment(new Date(results.analysis_start_time)).format('DD-MM-YYYY HH:MM:SS')}</td>
                    </tr>
                    `;
                }
                if(data.count == 0 || (total / data.count) == 0 || data.validation_errors){
                    
                }
                let total_number = (total / (100 * data.count)).toFixed(2);
                if(data.count && data.count > 0){
                    $('#text_' + source).text(total_number);
                }
                const elem = $("#circle-hybrid");
                if(total_number <= 0.39){
                    status_value_hybrid = 1;
                }else if(total_number <= 0.69){
                    status_value_hybrid = 2;
                    elem[0].style.removeProperty('background-color');
                    elem[0].style.setProperty('background-color', '#f2ff15', 'important');
                    $('#text_' + source).css('color', '#f2ff15');
                }else if(total_number >= 0.70){
                    status_value_hybrid = 3;
                    elem[0].style.removeProperty('background-color');
                    elem[0].style.setProperty('background-color', '#fcc838', 'important');
                    $('#text_' + source).css('color', '#fcc838');
                }
                document.getElementById("tbody-hybrid").innerHTML = html;
                $('#table-hybrid').DataTable({
                    "dom": 'tp',
                    "searching": false,
                    "bPaginate": true,
                    "bLengthChange": false,
                    "bFilter": false,
                    "bInfo": false,
                    "bAutoWidth": false ,
                });
            }else if(data){
                let count = 0;
                for(let i in data){
                    count++;
                    const results = data[i];
                    if(results.threat_score){
                        total += results.threat_score;
                    }else{
                        total += results.av_detect;
                    }
                    
                    html += `
                    <tr>
                        <td>${results.environment_description}</td>
                        <td>${results.sha256}</td>
                        <td>${results.submit_name}</td>
                        <td>${results.type_short}</td>
                        <td>${results.threat_score ? results.threat_score : 0}/100</td>
                        <td>${results.av_detect ? results.av_detect : 0}%</td>
                        <td>${results.verdict}</td>
                        <td>${moment(new Date(results.analysis_start_time)).format('DD-MM-YYYY HH:MM:SS')}</td>
                    </tr>
                    `;
                }
                if(count == 0 || (total / count) == 0 || data.validation_errors){

                }
                let total_number = (total / (100 * count)).toFixed(2);
                if(count && count > 0){
                    $('#text_' + source).text(total_number);
                }

                const elem = $("#circle-hybrid");
                if(total_number <= 0.39){
                    status_value_hybrid = 1;
                }else if(total_number <= 0.69){
                    status_value_hybrid = 2;
                    elem[0].style.removeProperty('background-color');
                    elem[0].style.setProperty('background-color', '#f2ff15', 'important');
                    $('#text_' + source).css('color', '#f2ff15');
                }else if(total_number >= 0.70){
                    status_value_hybrid = 3;
                    elem[0].style.removeProperty('background-color');
                    elem[0].style.setProperty('background-color', '#fcc838', 'important');
                    $('#text_' + source).css('color', '#fcc838');
                }
                document.getElementById("tbody-hybrid").innerHTML = html;
                $('#table-hybrid').DataTable({
                    "dom": 'tp',
                    "searching": false,
                    "bPaginate": true,
                    "bLengthChange": false,
                    "bFilter": false,
                    "bInfo": false,
                    "bAutoWidth": false ,
                });
            }else{
                number_new_row++;
                $('#not-hybrid').show();
            }
            
            $('.load-hybrid').remove();
            click_hybrid();
        }
    }else if(source == 'otx_indicators'){
        if(res.status_code == 400){

        }else{
            $('#otx_indicators_loadspinner_basic_info').show();
               
                var html ="";
                if(res.type == 'IP'){
                    var header = res.data;
                    var header2 = res.data2;

                    html+='<div class="col-md-6 m-b-xs"><b> ASN:</b> '+header.asn+'</div>';
                    html+='<div class="col-md-6 m-b-xs"><b> Indicator Facts:</b> '+''+'</div>';
                    html+='<div class="col-md-6 m-b-xs"><b> Country:</b> '+header.country_name+'</div>';
                    var open_ports="";
                    var issuer = [];
                    var subject =[];
                    var reverse_dns ="";
                    if(header2.facts){
                        if(header2.facts.open_ports){
                            open_ports = header2.facts.open_ports.join(", ")
                        }
                        if(header2.facts.ssl_certificates){
                            for (let index = 0; index < header2.facts.ssl_certificates.length; index++) {
                                const element = header2.facts.ssl_certificates[index];
                                issuer.push(element.issuer);
                                subject.push(element.subject);
                            }

                        }
                        reverse_dns = header2.facts.reverse_dns;
                    }
                    html+=' <div class="col-md-6 m-b-xs"><b> Open Ports:</b> '+open_ports+'</div>';

                    var Tags = [];
                      if(header.pulse_info){
                        if(header.pulse_info.pulses){               
                                       for (let index = 0; index < header.pulse_info.pulses.length; index++) {
                                        if(header.pulse_info.pulses[index].tags){
                                          for (let index2 = 0; index2 < header.pulse_info.pulses[index].tags.length; index2++) {
                                              const tag = header.pulse_info.pulses[index].tags[index2];
                                              Tags.push(tag);
                                          }
                                        }
                                       }                                      
                        }

                  
                      }
                      if(Tags.length > 0){
                        html+=' <div class="col-md-6 m-b-xs"><b> Related Tags:</b> '+Tags.join(",")+' </div>';
                      }
                      if(header.type =="IPv6"){
                        html+=' <div class="col-md-6 m-b-xs"><b> Reverse DNS:</b> '+reverse_dns+'</div>';

                      }else{
                        html+=' <div class="col-md-6 m-b-xs"><b> Certificate Issuer:</b> '+issuer.join(", ")+'</div>';
                        html+=' <div class="col-md-6 m-b-xs"><b> Certificate Subject:</b> '+subject.join(", ")+'</div>';
                      }
                     

                    var table_pulse = "";
                      if(header.pulse_info){
                        if(header.pulse_info.pulses){               
                                       for (let index = 0; index < header.pulse_info.pulses.length; index++) {
                                        var pulse = header.pulse_info.pulses[index];
                                        var Groups = [];
                                        if(pulse.groups){
                                          for (let index2 = 0; index2 < pulse.groups.length; index2++) {
                                                const group = pulse.groups[index2];
                                                Groups.push('<a href="javascript:void(0);" onclick="f_load_puls_group( \' '+group+'\')">'+group+'</a>');  
                                          }
                                        }
                                        var Tags = [];
                                        if(pulse.tags){
                                          for (let index2 = 0; index2 < pulse.tags.length; index2++) {
                                                const tag = pulse.tags[index2];
                                                Tags.push('<a href="javascript:void(0);" onclick="f_load_puls_tag( \' '+tag+'\',\'\')">'+tag+'</a>');  
                                          }
                                        }
                                        var public ="";
                                        if(pulse.public == 1){
                                                public='<i class="fas fa-check text-success"></i>';

                                        }else{

                                        }
                                        var indicator_type = [];
                                        if(pulse.indicator_count){
                                     

                                            $.each(pulse.indicator_type_counts, function(key, value) {
                                                indicator_type.push('<b>'+key+':</b>'+value);  
                                            });
                                        }


                                        table_pulse+='  <tr role="row">';
                                        table_pulse+='      <td style="width:10px;">'+(index+1)+'</td>';
                                        table_pulse+='     <td colspan="6"><a href="javascript:void(0);" onclick="f_load_puls(\''+pulse.id+'\');">'+pulse.name+'</a>';
                                        table_pulse+='     </br> <span>'+pulse.description+'</span>';
                                        table_pulse+='      </br>';
                                        if(Groups.length > 0){
                                          table_pulse+='     </br> <span><b>Groups:</b>'+Groups.join(", ")+'</span>';
                                        }
                                        if(Tags.length > 0){
                                        table_pulse+='     </br> <span><b>Tags:</b>'+Tags.join(", ")+'</span>';
                                        }
                                        table_pulse+=' </br> <span> <b>Created:</b>'+pulse.created+' <b>Modified:</b>'+pulse.modified+'</span>';
                                        table_pulse+='      <td>';
                       
                                        table_pulse+='   <td style="width:10px;"><a href="javascript:void(0);"  onclick="f_load_puls(\''+pulse.id+'\');" class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a></td>';
                                        table_pulse+=' </tr>';

                                       }                                      
                        }
                      }
                      $("#table-related-event tbody").append(table_pulse);







                }else if(res.type == 'Domain'){

                    var header = res.data;
                    var header2 = res.data2;
                    var IP ="";
                    var country_name ="";
                    if(header2.indicators){
                        if(header2.indicators.ip){
                            IP = header2.indicators.ip.indicator;
                            country_name = header2.indicators.ip.country_name;

                        }

                    }
                    var WHOIS ="";
                    if(header.whois){
                        WHOIS ='<a target="_blank" href="'+header.whois+'">'+'WHOIS'+'</a>';

                    }
                    

                    html+=' <div class="col-md-6 m-b-xs"><b> IP Address:</b> '+IP+'</div>';
                    html+=' <div class="col-md-6 m-b-xs"><b> WHOIS:</b> '+WHOIS+'</div>';
                    html+=' <div class="col-md-6 m-b-xs"><b> Country:</b> '+country_name+'</div>';
                    var open_ports="";
                    var issuer = [];
                    var subject =[];
            
               

                    var Tags = [];
                      if(header.pulse_info){
                        if(header.pulse_info.pulses){               
                                       for (let index = 0; index < header.pulse_info.pulses.length; index++) {
                                        if(header.pulse_info.pulses[index].tags){
                                          for (let index2 = 0; index2 < header.pulse_info.pulses[index].tags.length; index2++) {
                                              const tag = header.pulse_info.pulses[index].tags[index2];
                                              Tags.push(tag);
                                          }
                                        }
                                       }                                      
                        }

                  
                      }
                      if(Tags.length > 0){
                        html+=' <div class="col-md-6 m-b-xs"><b> Related Tags:</b> '+Tags.join(",")+' </div>';
                      }
     

                    var table_pulse = "";
                      if(header.pulse_info){
                        if(header.pulse_info.pulses){               
                                       for (let index = 0; index < header.pulse_info.pulses.length; index++) {
                                        var pulse = header.pulse_info.pulses[index];
                                        var Groups = [];
                                        if(pulse.groups){
                                          for (let index2 = 0; index2 < pulse.groups.length; index2++) {
                                                const group = pulse.groups[index2];
                                                Groups.push('<a href="javascript:void(0);" onclick="f_load_puls_group( \' '+group+'\')">'+group+'</a>');  
                                          }
                                        }
                                        var Tags = [];
                                        if(pulse.tags){
                                          for (let index2 = 0; index2 < pulse.tags.length; index2++) {
                                                const tag = pulse.tags[index2];
                                                Tags.push('<a href="javascript:void(0);" onclick="f_load_puls_tag( \' '+tag+'\',\'\')">'+tag+'</a>');  
                                          }
                                        }
                                        var public ="";
                                        if(pulse.public == 1){
                                                public='<i class="fas fa-check text-success"></i>';

                                        }else{

                                        }
                                        var indicator_type = [];
                                        if(pulse.indicator_count){
                                     

                                            $.each(pulse.indicator_type_counts, function(key, value) {
                                                indicator_type.push('<b>'+key+':</b>'+value);  
                                            });
                                        }


                                        table_pulse+='  <tr role="row">';
                                        table_pulse+='      <td style="width:10px;">'+(index+1)+'</td>';
                                        table_pulse+='     <td colspan="6"><a href="javascript:void(0);" onclick="f_load_puls(\''+pulse.id+'\');">'+pulse.name+'</a>';
                                        table_pulse+='     </br> <span>'+pulse.description+'</span>';
                                        table_pulse+='      </br>';
                                        if(Groups.length > 0){
                                          table_pulse+='     </br> <span><b>Groups:</b>'+Groups.join(", ")+'</span>';
                                        }
                                        if(Tags.length > 0){
                                        table_pulse+='     </br> <span><b>Tags:</b>'+Tags.join(", ")+'</span>';
                                        }
                                        table_pulse+=' </br> <span> <b>Created:</b>'+pulse.created+' <b>Modified:</b>'+pulse.modified+'</span>';
                                        table_pulse+='      <td>';
                       
                                        table_pulse+='   <td style="width:10px;"><a href="javascript:void(0);"  onclick="f_load_puls(\''+pulse.id+'\');" class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a></td>';
                                        table_pulse+=' </tr>';

                                       }                                      
                        }
                      }
                      $("#table-related-event tbody").append(table_pulse);


                }else if(res.type == 'URL'){

                    var header = res.data;
                    var header2 = res.data2;

                    html+=' <div class="col-md-6 m-b-xs"><b> Hostname:</b> '+header.hostname+'</div>';
                    html+=' <div class="col-md-6 m-b-xs"><b> Domain:</b> '+header.domain+'</div>';

                    var WHOIS ="";
                    if(header.whois){
                        WHOIS ='<a target="_blank" href="'+header.whois+'">'+'WHOIS'+'</a>';

                    }
                    

                    html+=' <div class="col-md-6 m-b-xs"><b> Country:</b> '+WHOIS+'</div>';
                    var open_ports="";
                    var issuer = [];
                    var subject =[];
                    var reverse_dns ="";
                    if(header2.facts){
                        if(header2.facts.open_ports){
                            open_ports = header2.facts.open_ports.join(", ")
                        }
                        if(header2.facts.ssl_certificates){
                            for (let index = 0; index < header2.facts.ssl_certificates.length; index++) {
                                const element = header2.facts.ssl_certificates[index];
                                issuer.push(element.issuer);
                                subject.push(element.subject);
                            }

                        }
                        reverse_dns = header2.facts.reverse_dns;
                    }
                    html+=' <div class="col-md-6 m-b-xs"><b> Open Ports:</b> '+open_ports+'</div>';

                    var Tags = [];
                      if(header.pulse_info){
                        if(header.pulse_info.pulses){               
                                       for (let index = 0; index < header.pulse_info.pulses.length; index++) {
                                        if(header.pulse_info.pulses[index].tags){
                                          for (let index2 = 0; index2 < header.pulse_info.pulses[index].tags.length; index2++) {
                                              const tag = header.pulse_info.pulses[index].tags[index2];
                                              Tags.push(tag);
                                          }
                                        }
                                       }                                      
                        }

                  
                      }
                      if(Tags.length > 0){
                        html+=' <div class="col-md-6 m-b-xs"><b> Related Tags:</b> '+Tags.join(",")+' </div>';
                      }
                  
                     

                    var table_pulse = "";
                      if(header.pulse_info){
                        if(header.pulse_info.pulses){               
                                       for (let index = 0; index < header.pulse_info.pulses.length; index++) {
                                        var pulse = header.pulse_info.pulses[index];
                                        var Groups = [];
                                        if(pulse.groups){
                                          for (let index2 = 0; index2 < pulse.groups.length; index2++) {
                                                const group = pulse.groups[index2];
                                                Groups.push('<a href="javascript:void(0);" onclick="f_load_puls_group( \' '+group+'\')">'+group+'</a>');  
                                          }
                                        }
                                        var Tags = [];
                                        if(pulse.tags){
                                          for (let index2 = 0; index2 < pulse.tags.length; index2++) {
                                                const tag = pulse.tags[index2];
                                                Tags.push('<a href="javascript:void(0);" onclick="f_load_puls_tag( \' '+tag+'\',\'\')">'+tag+'</a>');  
                                          }
                                        }
                                        var public ="";
                                        if(pulse.public == 1){
                                                public='<i class="fas fa-check text-success"></i>';

                                        }else{

                                        }
                                        var indicator_type = [];
                                        if(pulse.indicator_count){
                                     

                                            $.each(pulse.indicator_type_counts, function(key, value) {
                                                indicator_type.push('<b>'+key+':</b>'+value);  
                                            });
                                        }


                                        table_pulse+='  <tr role="row">';
                                        table_pulse+='      <td style="width:10px;">'+(index+1)+'</td>';
                                        table_pulse+='     <td colspan="6"><a href="javascript:void(0);" onclick="f_load_puls(\''+pulse.id+'\');">'+pulse.name+'</a>';
                                        table_pulse+='     </br> <span>'+pulse.description+'</span>';
                                        table_pulse+='      </br>';
                                        if(Groups.length > 0){
                                          table_pulse+='     </br> <span><b>Groups:</b>'+Groups.join(", ")+'</span>';
                                        }
                                        if(Tags.length > 0){
                                        table_pulse+='     </br> <span><b>Tags:</b>'+Tags.join(", ")+'</span>';
                                        }
                                        table_pulse+=' </br> <span> <b>Created:</b>'+pulse.created+' <b>Modified:</b>'+pulse.modified+'</span>';
                                        table_pulse+='      <td>';
                       
                                        table_pulse+='   <td style="width:10px;"><a href="javascript:void(0);"  onclick="f_load_puls(\''+pulse.id+'\');" class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a></td>';
                                        table_pulse+=' </tr>';

                                       }                                      
                        }
                      }
                      $("#table-related-event tbody").append(table_pulse);
           




                }else if(res.type== 'SHA256' || res.type== 'MD5' || res.type== 'SHA1'){
                    $("#table-related-event tbody").empty();
                    var header_analysis = res.data.analysis;
                    var header = res.data2;
                    if(!jQuery.isEmptyObject(header_analysis)){
                        
                                if(header_analysis){
                                    if(header_analysis.datetime_int){
                                        html+=' <div class="col-md-6 m-b-xs"><b> Analysis Date:</b> '+header_analysis.datetime_int+'</div>';
                                    }
                                    if(header_analysis.info.results.file_type){
                                        html+=' <div class="col-md-6 m-b-xs"><b> File Type:</b> '+header_analysis.info.results.file_type+'</div>';
                                    }
                                }
                                
                                
                                    var Antivirus_Detections = [];
                                    if(header_analysis){
                                            if(header_analysis.plugins.clamav){
                                                if(header_analysis.plugins.clamav.results){
                                                    if( header_analysis.plugins.clamav.results.alerts){
                                                    for (let index = 0; index < header_analysis.plugins.clamav.results.alerts.length; index++) {
                                                        const alert = header_analysis.plugins.clamav.results.alerts[index];
                                                        if(alert == 'Malware infection'){
                                                            Antivirus_Detections.push(header_analysis.plugins.clamav.results.detection);
                                                        }
                                                    }
                                                    }
                                                }

                                            }
                                    
                                        if(header_analysis.plugins.msdefender){
                                            if(header_analysis.plugins.msdefender.results){
                                                if( header_analysis.plugins.msdefender.results.alerts){
                                                for (let index = 0; index < header_analysis.plugins.msdefender.results.alerts.length; index++) {
                                                    const alert = header_analysis.plugins.msdefender.results.alerts[index];
                                                    if(alert == 'Malware infection'){
                                                        Antivirus_Detections.push(header_analysis.plugins.msdefender.results.detection);
                                                    }
                                                }
                                                }
                                            }
                                        }
                                    }


                                    html+=' <div class="col-md-6 m-b-xs"><b> Antivirus Detections:</b> '+Antivirus_Detections.join(",")+'</div>';
                                    if(header_analysis){
                                    html+=' <div class="col-md-6 m-b-xs"><b> Size:</b> '+header_analysis.info.results.filesize+' bytes</div>';
                                    }
                                    
                                
                                    var Yara_Detections = [];
                                    if(header_analysis){
                                        if(header_analysis.plugins.yarad){
                                            if(header_analysis.plugins.yarad.results){
                                                if(header_analysis.plugins.yarad.results.detection){
                                                    for (let index = 0; index <header_analysis.plugins.yarad.results.detection.length; index++) {
                                                        Yara_Detections.push(header_analysis.plugins.yarad.results.detection[index].rule_name);
                                                    }
                                            }
                                            }
                                        }
                                    }
                                    html+=' <div class="col-md-6 m-b-xs"><b> Yara Detections :</b> '+Yara_Detections.join("</br>")+'</div>';
                                    if(header_analysis){
                                    html+=' <div class="col-md-6 m-b-xs"><b> MD5:</b> '+header_analysis.info.results.md5+' </div>';
                                    }


                                    var Alerts = [];
                                    if(header_analysis){
                                        if(header_analysis.plugins.cuckoo){
                                            if(header_analysis.plugins.cuckoo.result){
                                
                                                if(header_analysis.plugins.cuckoo.result.signatures){

                                                    for (let index = 0; index <header_analysis.plugins.cuckoo.result.signatures.length; index++) {
                                                    
                                                        if(header_analysis.plugins.cuckoo.result.signatures[index].severity >=3){
                                                            Alerts.push('<span class="badge" style="background-color: #b93624;">'+header_analysis.plugins.cuckoo.result.signatures[index].name+'</span>');
                                                        }else if(header_analysis.plugins.cuckoo.result.signatures[index].severity >=2){
                                                            Alerts.push('<span class="badge" style="background-color: #ffb000;color:#333;">'+header_analysis.plugins.cuckoo.result.signatures[index].name+'</span>');

                                                        }else{
                                                            Alerts.push('<span class="badge" style="background-color: #88ce4f;">'+header_analysis.plugins.cuckoo.result.signatures[index].name+'</span>');
                                                        }
                                                    
                                                    }
                                            }
                                            }
                                        }
                                    }


                                    html+=' <div class="col-md-6 m-b-xs"><b> Alerts:</b> '+Alerts.join("")+' </div>';
                                    if(header_analysis){
                                    html+=' <div class="col-md-6 m-b-xs"><b> SHA1:</b> '+header_analysis.info.results.sha1+' </div>';
                                    html+=' <div class="col-md-6 m-b-xs"><b> SHA256:</b> '+header_analysis.info.results.sha256+' </div>';
                                    }
                                    var host_name = [];
                                    if(header_analysis){
                                        if(header_analysis.plugins.cuckoo){
                                            if(header_analysis.plugins.cuckoo.result){
                                
                                                if(header_analysis.plugins.cuckoo.result.network){

                                                if(header_analysis.plugins.cuckoo.result.network.hosts){
                                                    for (let index = 0; index < header_analysis.plugins.cuckoo.result.network.hosts.length; index++) {
                                                        const ip = header_analysis.plugins.cuckoo.result.network.hosts[index].ip;
                                                        host_name.push(ip);
                                                    }

                                                }
                                            }
                                            }
                                        }
                                    }

                                    html+=' <div class="col-md-6 m-b-xs"><b> External Hosts:</b> '+host_name.join(",")+' </div>';


                                    var imphash = "";
                                    var pehash="";
                                    if(header_analysis){
                                    if(header_analysis.plugins.pe32info){
                                        if(header_analysis.plugins.pe32info.results){
                                            if(header_analysis.plugins.pe32info.results.imphash){
                                                imphash = header_analysis.plugins.pe32info.results.imphash;
                                            }
                                            if(header_analysis.plugins.pe32info.results.pehash){
                                                pehash = header_analysis.plugins.pe32info.results.pehash;
                                            }
                                        }
                                    }
                                    }

                                    html+=' <div class="col-md-6 m-b-xs"><b> IMPHASH:</b> '+imphash+' </div>';


                                var Tags = [];
                                if(header.pulse_info){
                                    if(header.pulse_info.pulses){               
                                                for (let index = 0; index < header.pulse_info.pulses.length; index++) {
                                                    if(header.pulse_info.pulses[index].tags){
                                                    for (let index2 = 0; index2 < header.pulse_info.pulses[index].tags.length; index2++) {
                                                        const tag = header.pulse_info.pulses[index].tags[index2];
                                                        Tags.push(tag);
                                                    }
                                                    }
                                                }                                      
                                    }
                                }
                                if(Tags.length > 0){
                                    html+=' <div class="col-md-6 m-b-xs"><b> Related Tags:</b> '+Tags.join(",")+' </div>';
                                }
                                    html+=' <div class="col-md-6 m-b-xs"><b> PEHASH:</b> '+pehash+' </div>';
                                    var Groups = [];
                                if(header.pulse_info){
                                    if(header.pulse_info.pulses){               
                                                for (let index = 0; index < header.pulse_info.pulses.length; index++) {
                                                    if(header.pulse_info.pulses[index].groups){
                                                    for (let index2 = 0; index2 < header.pulse_info.pulses[index].groups.length; index2++) {
                                                            const groups = header.pulse_info.pulses[index].groups[index2];
                                                            Groups.push(groups);  
                                                    }
                                                    }
                                                }                                      
                                    }
                                }
                                if(Groups.length > 0){
                                    html+=' <div class="col-md-6 m-b-xs"><b> Related Groups:</b> '+Groups.join(",")+' </div>';
                                }
                    }

                      var table_pulse = "";
                      if(header.pulse_info){
                        if(header.pulse_info.pulses){               
                                       for (let index = 0; index < header.pulse_info.pulses.length; index++) {
                                        var pulse = header.pulse_info.pulses[index];
                                        var Groups = [];
                                        if(pulse.groups){
                                          for (let index2 = 0; index2 < pulse.groups.length; index2++) {
                                                const group = pulse.groups[index2];
                                                Groups.push('<a href="javascript:void(0);" onclick="f_load_puls_group( \' '+group+'\')">'+group+'</a>');  
                                          }
                                        }
                                        var Tags = [];
                                        if(pulse.tags){
                                          for (let index2 = 0; index2 < pulse.tags.length; index2++) {
                                                const tag = pulse.tags[index2];
                                                Tags.push('<a href="javascript:void(0);" onclick="f_load_puls_tag( \' '+tag+'\',\'\')">'+tag+'</a>');  
                                          }
                                        }
                                        var public ="";
                                        if(pulse.public == 1){
                                                public='<i class="fas fa-check text-success"></i>';

                                        }else{

                                        }
                                        var indicator_type = [];
                                        if(pulse.indicator_count){
                                     

                                            $.each(pulse.indicator_type_counts, function(key, value) {
                                                indicator_type.push('<b>'+key+':</b>'+value);  
                                            });
                                        }


                                        table_pulse+='  <tr role="row">';
                                        table_pulse+='      <td style="width:10px;">'+(index+1)+'</td>';
                                        table_pulse+='     <td colspan="6"><a href="javascript:void(0);" onclick="f_load_puls(\''+pulse.id+'\');">'+pulse.name+'</a>';
                                        table_pulse+='     </br> <span>'+indicator_type.join("|")+'</span>';
                                        table_pulse+='     </br> <span>'+pulse.description+'</span>';
                                        table_pulse+='      </br>';
                                        if(Groups.length > 0){
                                          table_pulse+='     </br> <span><b>Groups:</b>'+Groups.join(", ")+'</span>';
                                        }
                                        if(Tags.length > 0){
                                        table_pulse+='     </br> <span><b>Tags:</b>'+Tags.join(", ")+'</span>';
                                        }
                                        table_pulse+=' </br> <span> <b>Created:</b>'+pulse.created+' <b>Modified:</b>'+pulse.modified+'</span>';
                                        table_pulse+='      <td>';
                       
                                        table_pulse+='   <td style="width:10px;"><a href="javascript:void(0);"  onclick="f_load_puls(\''+pulse.id+'\');" class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a></td>';
                                        table_pulse+=' </tr>';

                                       }                                      
                        }
                      }
                      $("#table-related-event tbody").append(table_pulse);




                        
                   
                }else{

                }





                $('#otx_indicators_loadspinner_basic_info').hide();
                $('#otx_indicators_loadspinner_basic_info_table').hide();
                $("#table-related-event").show();
                $('#otx_indicators_general').html(html);
        }
    }



    if(number_risk == 3){
        search_risk();
    }
}).fail(function(jqXHR, ajaxOptions, thrownError){
    console.log("No response from server");
});
}
function hasName(prop, value, data) {
return data.some(function(obj) {
    return prop in obj && obj[prop] === value;
});
}
function f_load_puls(puls_id){
$('#otx_event_general').html('');
$('.otx_indicators').hide();
$('.otx_event').show();
$('.otx_tag').hide();
$.ajax({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    url: "/loadSearchAPI",
    method: 'post',
    data: ({
        keyword:puls_id,
        source:'otx_puls'
    }),
    beforeSend: function(){
        $('.ajax-loading').show();
    },
}).done(function(res){
    var data = res.data;

    var referencess = [];
    if(data.references){
          for (let index = 0; index < data.references.length; index++) {
              const reference = data.references[index];
              referencess.push('<a href="'+reference+'"  target="_blank">'+reference+'</a>');  
          }
        
    }
    var tags = [];
    if(data.tags){
          for (let index = 0; index < data.tags.length; index++) {
              const tag = data.tags[index];
              tags.push('<a href="javascript:void(0);" onclick="f_load_puls_tag( \' '+tag+'\',\'\')">'+tag+'</a>');  
          }
        
    }
    var industries = [];
    if(data.industries){
          for (let index = 0; index < data.industries.length; index++) {
              const industrie = data.industries[index];
              industries.push('<a href="javascript:void(0);" onclick="f_load_puls_industrie( \' '+industrie+'\')">'+industrie+'</a>');  
          }
        
    }
    var targeted_countries = [];
    if(data.targeted_countries){
          for (let index = 0; index < data.targeted_countries.length; index++) {
              const targeted_countrie = data.targeted_countries[index];
              targeted_countries.push('<a href="javascript:void(0);" onclick="f_load_puls_targeted_countries( \' '+targeted_countrie+'\')">'+targeted_countrie+'</a>');  
          }
        
    }
    var attack_ids = [];
    if(data.attack_ids){
          for (let index = 0; index < data.attack_ids.length; index++) {
              const attack_id = data.attack_ids[index];
              attack_ids.push(attack_id);  
          }
        
    }

    
    var html="";
    html+=' <h1>'+data.name+'</h1>';
    html+=' <p>'+data.description+'</p>';
    html+=' <p><b>Reference:</b>'+referencess.join(", ")+'</p>';
    html+=' <p><b>Tags:</b>'+tags.join(", ")+'</p>';
    html+='<p><b>Industry:</b>'+industries.join(", ")+'</p>';
    html+=' <p><b>Targeted Countries:</b>'+targeted_countries.join(", ")+'</p>';
    html+=' <b>ATT&CK IDS:</b>'+attack_ids.join(", ")+'</p>';
    $('#otx_event_general').html(html);

    var indicators = [];
    var indicators_data = [];
    if(data.indicators){
          for (let index = 0; index < data.indicators.length; index++) {
            const indicator = data.indicators[index].type;
            if (jQuery.inArray(indicator, indicators) == -1) {
                indicators.push(indicator);  
                var count = 0;
                for (let index2 = 0; index2 < data.indicators.length; index2++) {
                    if(indicator == data.indicators[index2].type){
                        count++;
                    }
                }
                indicators_data.push(count);  
            }
           
          }
        
    }
  
    $('#otx_event_loadspinner_basic_info').hide();

    const chart = new frappe.Chart("#otx_event_chart-show-bar", { 
            title: "",
            data:{
                labels:indicators,
                datasets: [
                { values:indicators_data}
                ]
            },
            type: 'percentage',
            colors: ['#743ee2']
        });
        f_load_puls_indictor(puls_id)
     



}).fail(function(jqXHR, ajaxOptions, thrownError){
    console.log("No response from server");
});
}

function f_load_puls_indictor(puls_id){
$.ajax({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    url: "/loadSearchAPI",
    method: 'post',
    data: ({
        keyword:puls_id,
        source:'otx_puls_indicator'
    }),
    beforeSend: function(){
        $('.ajax-loading').show();
    },
}).done(function(res){
    var data = res.data;

        var table_indicator ="";
       if(data.results){
          for (let index = 0; index < data.results.length; index++) {
              var indicator = data.results[index];
              var role ="";
              if(indicator.role){
                role = indicator.role;
              }
              var is_active = "";
              if(indicator.is_active == 1){
                is_active ="Active";
              }
                table_indicator+='  <tr role="row">';
                table_indicator+='      <td style="width:100px;">'+indicator.type+'</td>';
                table_indicator+='     <td><a href="javascript:void(0);" onclick="f_load_indicator(\''+indicator.indicator+'\');">'+indicator.indicator+'</a>';
                table_indicator+='     <td>'+role+'</td>';
                table_indicator+='     <td>'+indicator.title+'</td>';
                table_indicator+='     <td>'+is_active+'</td>';
                table_indicator+='     <td>'+indicator.created+'</td>';
                                
                table_indicator+='   <td style="width:10px;"><a href="javascript:void(0);" onclick="f_load_indicator(\''+indicator.indicator+'\');" class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a></td>';
                table_indicator+=' </tr>';
            }
        }
        $("#table-related-indicator tbody").append(table_indicator);
        $("#table-related-indicator").show();
        $("#otx_event_loadspinner_basic_info_table").hide();

}).fail(function(jqXHR, ajaxOptions, thrownError){
    console.log("No response from server");
});

}
var tag_name_old = "";
function f_load_puls_tag(tag_name,nextpage){

$('.otx_indicators').hide();
$('.otx_event').hide();
$('.otx_tag').show();
$("#otx_tag_loadspinner_basic_info_table").show();
$("#otx_tag_loadspinner_basic_info").show();
$('#btn_load_tag_nextpag').hide();

if(tag_name !=''){
    if(tag_name_old != tag_name){
        $("#table-related-tag tbody").empty();
        $('#btn_load_tag_nextpag').hide();
        $('#otx_tag_general').html('');
        $('#btn_load_tag_nextpag').data('');
     }
     tag_name_old = tag_name;
}


var url_next =   $('#btn_load_tag_nextpag').data('url');
if(url_next){
  nextpage =url_next;
}
$.ajax({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    url: "/loadSearchAPI?tags="+tag_name,
    method: 'post',
    data: ({
        keyword:tag_name,
        source:'otx_puls_tag',
        nextpage:nextpage
    }),
    beforeSend: function(){
        $('.ajax-loading').show();
    },
}).done(function(res){

    
    var header = res.data;
    var search = "found "+header.count+" results for \"tag:"+tag_name+"\"";
    $('#otx_tag_general').html('<h1 class="b-b">'+search+'</h1>');
    $("#otx_tag_loadspinner_basic_info").hide();
    if(header.next){

        $('#btn_load_tag_nextpag').data('url',header.next);
        $('#btn_load_tag_nextpag').show();
    }else{
        $('#btn_load_tag_nextpag').data('url','');
        $('#btn_load_tag_nextpag').show();
    }
    $('#otx_tag_more_loadspinner_basic_info_table').hide();
    var tag_row =  $("#table-related-tag tbody tr").length;
    var table_pulse = "";
                      if(header.results){
                              
                                       for (let index = 0; index < header.results.length; index++) {
                                        var pulse = header.results[index];
                                        var Groups = [];
                                        if(pulse.groups){
                                          for (let index2 = 0; index2 < pulse.groups.length; index2++) {
                                                const group = pulse.groups[index2];
                                                Groups.push('<a href="javascript:void(0);" onclick="f_load_puls_group( \' '+group+'\')">'+group+'</a>');  
                                          }
                                        }
                                        var Tags = [];
                                        if(pulse.tags){
                                          for (let index2 = 0; index2 < pulse.tags.length; index2++) {
                                                const tag = pulse.tags[index2];
                                                Tags.push('<a href="javascript:void(0);" onclick="f_load_puls_tag( \' '+tag+'\',\'\')">'+tag+'</a>');  
                                          }
                                        }
                                        var public ="";
                                        if(pulse.public == 1){
                                                public='<i class="fas fa-check text-success"></i>';

                                        }else{

                                        }
                                        var indicator_type = [];
                                        if(pulse.indicator_count){
                                     

                                            $.each(pulse.indicator_type_counts, function(key, value) {
                                                indicator_type.push('<b>'+key+':</b>'+value);  
                                            });
                                        }
                                     
                                        tag_row++;
                                        table_pulse+='  <tr role="row">';
                                        table_pulse+='      <td style="width:10px;">'+(tag_row)+'</td>';
                                        table_pulse+='     <td colspan="6"><a href="javascript:void(0);" onclick="f_load_puls(\''+pulse.id+'\');">'+pulse.name+'</a>';
                                        table_pulse+='     </br> <span>'+indicator_type.join("|")+'</span>';
                                        table_pulse+='     </br> <span>'+pulse.description+'</span>';
                                        table_pulse+='      </br>';
                                        if(Groups.length > 0){
                                          table_pulse+='     </br> <span><b>Groups:</b>'+Groups.join(", ")+'</span>';
                                        }
                                        if(Tags.length > 0){
                                        table_pulse+='     </br> <span><b>Tags:</b>'+Tags.join(", ")+'</span>';
                                        }
                                        table_pulse+=' </br> <span> <b>Created:</b>'+pulse.created+' <b>Modified:</b>'+pulse.modified+'</span>';
                                        table_pulse+='      <td>';
                       
                                        table_pulse+='   <td style="width:10px;"><a href="javascript:void(0);"  onclick="f_load_puls(\''+pulse.id+'\');" class="btn btn-xs btn-info"><i class="far fa-eye"></i> View</a></td>';
                                        table_pulse+=' </tr>';
                                        
                                       }                                      
                        
                      }
                      $("#table-related-tag tbody").append(table_pulse);
                      $("#table-related-tag").show();
                      $("#otx_tag_loadspinner_basic_info_table").hide();


}).fail(function(jqXHR, ajaxOptions, thrownError){
    console.log("No response from server");
});

}

function f_load_tag_nextpage(){
$('#otx_tag_more_loadspinner_basic_info_table').show();
f_load_puls_tag('','');

}

function f_load_indicator(indicator_id){

$('.otx_event').hide();
$('.otx_tage').hide();
$('.otx_indicators').show();
$('#otx_indicators_general').html('');

$('#otx_indicators_loadspinner_basic_info').show();
$('#otx_indicators_loadspinner_basic_info_table').show();
$("#table-related-event").hide();

$('#otx_event_loadspinner_basic_info').show();
$('#otx_event_loadspinner_basic_info_table').show();
$("#table-related-indicator").hide();
text_search_new = indicator_id;
loadSearchAPI('otx_indicators');


}
function search_risk(){
let summary_total = (status_value_ibmcloud + status_value_virustotal + status_value_hybrid) / number_new_row;
let html_status = ``;
if(summary_total <= 1.9){
html_status += `<div class="status-risk success" style="color: #fff;color: #fff !important;">
    Low
</div>`;
}else if(summary_total <= 2.6){
html_status += `<div class="status-risk warning"  style="color: #fff;color: #fff !important;">
    Medium
</div>`;
}else if(summary_total <= 3){
html_status += `<div class="status-risk danger" style="color: #fff;color: #fff !important;">
     High
</div>`;
}else if(summary_total >= 3){
html_status += `<div class="status-risk danger" style="color: #fff;color: #fff !important;">
     High
</div>`;
}
$('#text_status_risk').html(html_status);
}



$('.table-hybrid').hide();
$('.table-virustotal').hide();
$('.table-ibmcloud').hide();

</script>
@endpush
@endsection
