@extends('layouts.app')
@section('content')
<style>
    .modal-dialog {
        width: 100% !important;
        max-width: none !important;
        height: 100% !important;
        margin: 0 !important;
    }

    .modal-content {
        height: 100% !important;
        border: 0 !important;
        border-radius: 0 !important;
    }

    .modal-body {
        overflow-y: auto !important;
    }

    .modal, .modal.fade.in {
        overflow-y: auto !important;
    }
</style>
@php $role_custom = @check_role_custom(); @endphp
<section id="content">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            <div class="bc-head">@langapp('search_results_for_tag',['keyword' => $keyword])</div>
            <span class="pull-right" style="margin-top: 1.2rem;font-size: 16px;font-weight: bold;">
                Total Result : <span id="total_all">0</span>
                <button class="btn btn-info lookup" style="display:none;"><i class="fas fa-search"></i> Threat Lookup</button>
            </span>
        </header>
        <section class="scrollable wrapper bg" id="clauses" style="padding: 8px !important">
            <section class="panel panel-default m-b-xs">
                <div class="panel-body" id="table-container">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="fillter_click" class="button-group">
                                <a href="javascript:void(0)" data-btn="all" class="btn btn-selector btn_filter active">All <span id="all">0</span></a>
                                @if($role_custom['news'])
                                    <a href="javascript:void(0)" data-btn="news" class="btn btn-selector btn_filter">News <span id="news">0</label></a>
                                @endif
                                @if($role_custom['indicators'])
                                    <a href="javascript:void(0)" data-btn="events" id="" class="btn btn-selector btn_filter">Event <span id="events">0</label></a>
                                @endif
                                @if($role_custom['data_leak'])
                                    <a href="javascript:void(0)" data-btn="data-leak" id="" class="btn btn-selector btn_filter">Data Leak <span id="data_leak">0</label></a>
                                @endif
                                @if($role_custom['compromised'])
                                    <a href="javascript:void(0)" data-btn="compromised" id="" class="btn btn-selector btn_filter">Compromised <span id="compromised">0</span></a>
                                @endif
                                @if($role_custom['vulnerabilities'])
                                    <a href="javascript:void(0)" data-btn="vulnerabilities" id="" class="btn btn-selector btn_filter">Vulnerabilities <span id="vulnerabilities">0</span></a>
                                @endif
                                
                                {{-- @if($role_custom['indicators'])
                                    <a href="javascript:void(0)" data-btn="indicators" id="" class="btn btn-selector btn_filter">Indicators <span id="indicators">0</span></a>
                                @endif --}}

                                     
                                @if($role_custom['indicators'])
                                    <a href="javascript:void(0)" data-btn="adversaries" id="" class="btn btn-selector btn_filter">Threat Actor <span id="adversaries">0</span></a>
                                @endif
                                     
                                @if($role_custom['indicators'])
                                    <a href="javascript:void(0)" data-btn="malware" id="" class="btn btn-selector btn_filter">Malware <span id="malware">0</span></a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- <div class="row type_indicator" style="display: none;">
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
                            </div>
                        </div>
                    </div> --}}
                </div>
            </section>

            <div class="int-lookup-main" style="display: none">
                <div class="back-int">
                    <section id="overall-int">
                        <div class="row">
                            <div class="col-md-5">
                                <h3 class="page-header">
                                Information <span id="l_api_limit"></span>
                                </h3>
                            </div>
                            <div class="col-md-7 text-right">
                                <span class="st-dt-leak">
                                    <span class="st-dt crit" data-toggle="tooltip" data-placement="left" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip crit'>Critical</div><div class='text-st-tooltip'> ภัยคุกคามระดับวิกฤต มีความรุนแรงและผลกระทบสูงสุด จำเป็นต้องตอบสนองหรือจัดการทันที</div></div>">Critical</span>
                                    <span class="st-dt vrh" data-toggle="tooltip" data-placement="left" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip vrh'>High</div><div class='text-st-tooltip'> เป็นข้อมูลภัยคุกคามที่อยู่ในรูปแบบของ Malicious Code หรือเป็น C&C Server หรือเป็นข้อมูลที่มีความสอดคล้องกับภัยคุกคามอย่างชัดเจนจากแหล่งข้อมูลต่าง ๆ และอยู่ในระดับรุนแรง</div></div>">High</span>
                                    {{-- <span class="st-dt high" data-toggle="tooltip" data-placement="left" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip high'>High</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้อย่างแพร่หลาย เช่น <br> ออกข่าว หรือมีการแชร์ข้อมูลจากแหล่งข้อมูลที่น่าเชื่อถือและเป็นที่แพร่หลาย <br> และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูล Username ,Password ของลูกค้าหรือเจ้าหน้าที่ดูแลระบบภายในองค์กรซึ่งเป็นข้อมูลที่สามารถนำมาใช้ได้จริง</div></div>">High</span> --}}
                                    <span class="st-dt md" data-toggle="tooltip" data-placement="left" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip md'>Medium</div><div class='text-st-tooltip'> เป็นข้อมูลภัยคุกคามที่อยู่ในรูปแบบของ Malicious Code หรือเป็น C&C Server หรือเป็นข้อมูลที่มีความสอดคล้องกับภัยคุกคามอย่างชัดเจนแต่อยู่ในระดับปานกลาง</div></div>">Medium</span>
                                    <span class="st-dt low" data-toggle="tooltip" data-placement="left" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip low'>Low</div><div class='text-st-tooltip'>เป็นข้อมูลภัยคุกคามที่มีความสอดคล้องกับภัยคุกคามอยู่ในระดับต่ำและมีความสอดคล้องบางแหล่งข้อมูลเท่านั้น ซึ่งอาจจะเป็นข้อมูลที่ไม่ใช่ภัยคุกคาม</div></div>">Low</span>
                                    <span class="st-dt info" data-toggle="tooltip" data-placement="left" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip info'>Informational</div><div class='text-st-tooltip'>เป็นข้อมูลทั่วไปที่ไม่จัดเป็นภัยคุกคาม หรือเป็นเพียงข้อมูลที่ให้รายละเอียดเพิ่มเติมเท่านั้น</div></div>">Informational</span>
                                </span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-5">
                                <div class="ovr-text">
                                    <p>Analysis</p>
                                    <p>{{ request()-> keyword }}</p>
                                </div>
                                <div class="ovr-text">
                                    <p>Type</p>
                                    <p id="type_search"></p>
                                </div>
                            </div>
                            <div class="col-md-7 text-right">
                                <div id="text_status_risk"></div>
                            </div>
                        </div>
                    </section>

                    <section id="av-detection">
                        <h3 class="page-header">
                            Anti-Virus Results
                        </h3>
                        <div id="show-chart-int" class="row av-results-wrapper has-falcon-button">
                            <div class="col-xs-12 col-sm-6 col-md-4">
                                <div id="card-search-int1" class="main-int-card active">
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

                                            <div class="circle-score" id="circle-hybrid">
                                                <div class="circle-score-inner">
                                                    <div class="circle-text-score">
                                                        <span id="text_hybrid">0</span> <span>/ 1</span>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                 </div>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-4">
                                <div id="card-search-int2" class="main-int-card active">
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

                                            <div class="circle-score" id="circle-virustotal">
                                                <div class="circle-score-inner">
                                                    <div class="circle-text-score">
                                                        <span id="text_virustotal">0</span> <span id="text_virustotal_sum">/ 0</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-4" id="container-abuseipdb">
                                <div id="card-search-abuseipdb" class="main-int-card active">
                                    <div class="text-center av-container border-success" data-chart="chart-cs-ml">
                                        <div class="title">
                                            <span class="text-title-logo title-abuseipdb">AbuseIPDB</span>
                                        </div>
                                        <div class="main-frame">

                                            <div class="load-abuseipdb loadfixed backdrop-loader white">
                                                <div class="loader4 centerloader"></div>
                                                <div class="loadding-text">Loading ...</div>
                                            </div>

                                            <div class="loadfixed backdrop-loader white" id="not-abuseipdb" style="display: none">
                                                <h1>No threat found</h1>
                                            </div>

                                            <div class="circle-score" id="circle-abuseipdb">
                                                <div class="circle-score-inner">
                                                    <div class="circle-text-score">
                                                        <span id="text_abuseipdb">0</span> <span id="text_abuseipdb_sum">/ 100</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-4" id="container-threatfox">
                                <div id="card-search-threatfox" class="main-int-card active">
                                    <div class="text-center av-container border-success" data-chart="chart-cs-ml">
                                        <div class="title">
                                            <span class="text-title-logo title-threatfox">ThreatFox</span>
                                        </div>
                                        <div class="main-frame">

                                            <div class="load-threatfox loadfixed backdrop-loader white">
                                                <div class="loader4 centerloader"></div>
                                                <div class="loadding-text">Loading ...</div>
                                            </div>

                                            <div class="loadfixed backdrop-loader white" id="not-threatfox" style="display: none">
                                                <h1>No threat found</h1>
                                            </div>

                                            <div class="circle-score" id="circle-threatfox">
                                                <div class="circle-score-inner">
                                                    <div class="circle-text-score">
                                                        <span id="text_threatfox">0</span> <span id="text_threatfox_sum">/ 100</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-4" id="container-rstcloud">
                                <div id="card-search-rstcloud" class="main-int-card active">
                                    <div class="text-center av-container border-success" data-chart="chart-cs-ml">
                                        <div class="title">
                                            <span class="text-title-logo title-rstcloud">RST Cloud</span>
                                        </div>
                                        <div class="main-frame">

                                            <div class="load-rstcloud loadfixed backdrop-loader white">
                                                <div class="loader4 centerloader"></div>
                                                <div class="loadding-text">Loading ...</div>
                                            </div>

                                            <div class="loadfixed backdrop-loader white" id="not-rstcloud" style="display: none">
                                                <h1>No threat found</h1>
                                            </div>

                                            <div class="circle-score" id="circle-rstcloud">
                                                <div class="circle-score-inner">
                                                    <div class="circle-text-score">
                                                        <span id="text_rstcloud">0</span> <span id="text_rstcloud_sum">/ 100</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-6 col-md-4" id="container-otx">
                                <div id="card-search-otx" class="main-int-card active">
                                    <div class="text-center av-container border-success" data-chart="chart-cs-ml">
                                        <div class="title">
                                            <span class="text-title-logo title-otx">AlienVault OTX</span>
                                        </div>
                                        <div class="main-frame">

                                            <div class="load-otx_indicators loadfixed backdrop-loader white">
                                                <div class="loader4 centerloader"></div>
                                                <div class="loadding-text">Loading ...</div>
                                            </div>

                                            <div class="loadfixed backdrop-loader white" id="not-otx" style="display: none">
                                                <h1>No threat found</h1>
                                            </div>

                                            <div class="circle-score" id="circle-otx">
                                                <div class="circle-score-inner">
                                                    <div class="circle-text-score">
                                                        <span id="text_otx_indicators">0</span> <span id="text_otx_indicators_sum">/ 50</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    


                    <section class="table-virustotal" style="display: none">
                        <h3 class="text-primary-head">VirusTotal</h1>
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
                        <h3 class="text-primary-head">CrowdStrike Falcon</h1>
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

                    <section class="table-abuseipdb" style="display: none">
                        <h3 class="text-primary-head">AbuseIPDB Details</h3>
                        <table id="table-abuseipdb" class="table">
                            <thead>
                                <tr>
                                    <th>IP Address</th>
                                    <th>Abuse Confidence Score</th>
                                    <th>ISP</th>
                                    <th>Country</th>
                                    <th>Usage Type</th>
                                    <th>Domain</th>
                                    <th>Total Reports</th>
                                    <th>Last Reported At</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-abuseipdb"></tbody>
                        </table>
                    </section>

                    <section class="table-threatfox" style="display: none">
                        <h3 class="text-primary-head">ThreatFox Details</h3>
                        <table id="table-threatfox" class="table">
                            <thead>
                                <tr>
                                    <th>IOC</th>
                                    <th>Threat Type</th>
                                    <th>IOC Type</th>
                                    <th>Malware</th>
                                    <th>Confidence Level</th>
                                    <th>First Seen</th>
                                    <th>Reporter</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-threatfox"></tbody>
                        </table>
                    </section>

                    <section class="table-rstcloud" style="display: none">
                        <h3 class="text-primary-head">RST Cloud Details</h3>
                        <table id="table-rstcloud" class="table">
                            <thead>
                                <tr>
                                    <th>IOC</th>
                                    <th>Type</th>
                                    <th>Total Score</th>
                                    <th>Severity</th>
                                    <th>Threats</th>
                                    <th>Category</th>
                                    <th>First Seen</th>
                                    <th>Last Seen</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-rstcloud"></tbody>
                        </table>
                    </section>

                    <section class="table-internal-events" style="display: none; margin-top: 2rem;">
                        <h3 class="text-primary-head">Internal Events Found</h3>
                        <div class="table-responsive">
                            <table id="table-internal-events" class="table table-striped dataTable no-footer" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>Source</th>
                                        <th>Event ID</th>
                                        <th>Event Name</th>
                                        <th>Tags</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-internal-events"></tbody>
                            </table>
                        </div>
                    </section>

                    <section class="bg-white otx_indicators" style="margin-top: 1.5rem">
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

                        <section id="general_details">
                            <div id="indicator_basic_info" class="m-b-lg">
                            <h1 class="b-b">Basic Information</h1>
                            <div id="otx_indicators_loadspinner_basic_info" class="content-spinner-loading" style="display: none;"></div>
                                <span id="otx_indicators_general" style="    display: inline-block; margin-top: 2rem;"></span>
                            </div>
                        </section>

                        <section id="related_event">
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

                     <section class="bg-white otx_event" style="padding:0;display:none;margin-top:1rem;">
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
                            <div class="m-b-lg">
                                <div id="indicator_basic_info" class="">
                                <div id="otx_event_loadspinner_basic_info" class="content-spinner-loading" style="display: none;"></div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <span id="otx_event_general"></span>
                                        </div>
                                            <div class="col-md-6">
                                                <h1 class="text-center">Type Attributes </h1>
                                                <div id="otx_event_chart-show-bar"></div>
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

             <section class="bg-white otx_tag" style="padding:0;display:none">
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
                    
                </ul>
            </div>
        </section>
        <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
    </section>
</section>

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
                <div class="header-flex-overflow m-t-10 float-right">
                    <div class="fwb-16">
                        <a href="javascript:void(0);" onclick="close_modal_frame();"
                                class="btn btn-danger btn-sm btn-responsive m-r-5">
                                @icon('solid/times')
                    </div>
                </div>
            </header>
            <iframe id="iframe_source" onload="typeof load_frame === 'function' && load_frame()" name="iframe_source" frameborder="0" width="100%" height="100%" allowfullscreen></iframe>
        </div>
    </div>
</div>
@push('pagestyle')
@include('stacks.css.highchart')
@include('stacks.css.datatables')
@include('stacks.css.multitext')
<style>
    /* Flexbox grid layout to prevent float-wrap layout bugs */
    .av-results-wrapper {
        display: flex !important;
        flex-wrap: wrap !important;
    }
    
    .av-results-wrapper > [class*="col-"] {
        display: flex !important;
        flex-direction: column !important;
    }
    
    .av-results-wrapper .main-int-card {
        flex: 1 !important;
        display: flex !important;
        flex-direction: column !important;
        margin-bottom: 25px !important;
    }
    
    .av-results-wrapper .av-container {
        flex: 1 !important;
        display: flex !important;
        flex-direction: column !important;
        height: 100% !important;
    }
    
    /* Ensure uniform title container heights so all content lines up vertically */
    section#av-detection .av-container .title {
        height: 50px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 10px 0 !important;
    }
    
    /* Text-based titles styling to make them look premium and consistent with logos */
    .text-title-logo {
        font-family: 'Outfit', 'Inter', sans-serif;
        font-weight: 800 !important;
        font-size: 22px !important;
        letter-spacing: -0.5px;
        margin: 0 !important;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .title-abuseipdb {
        color: #d35400 !important; /* Premium dark orange */
    }
    
    .title-threatfox {
        color: #2c3e50 !important; /* Premium deep slate */
    }
    
    .title-rstcloud {
        color: #2980b9 !important; /* Premium deep sky blue */
    }
    
    .title-otx {
        color: #16a085 !important; /* Premium teal/green */
    }
</style>
@endpush
@push('pagescript')
@include('stacks.js.activebutton')
@include('stacks.js.highchart')
@include('stacks.js.datatables')
@include('stacks.js.chart')
@include('stacks.js.multitext')
<script>
    active_btn('#fillter_click .btn-selector');
    var text_search_new = '{{request()->keyword}}';
    var mode_search = '{{ request()->mode }}';
    var btn_val;
    var btn_filter_indicators_type;
    var count_all = 0;
    var count_rows = 0;
    var count_rows_finish = 0;
    $(".btn_filter").click(function() {
        btn_val = $(this).data("btn");
        if(btn_val == 'all') {
            $("#news_head").show();
            $("#events_head").show();
            $("#data-leak_head").show();
            $("#compromised_head").show();
            $("#vulnerabilities_head").show();
            $("#indicators_head").show();
            $("#malware_head").hide();
            $("#adversaries_head").hide();
            $(".type_indicator").hide();
        } else if (btn_val == 'news') {
            $("#news_head").show();
            $("#news").collapse("show");
            $("#events_head").hide();
            $("#data-leak_head").hide();
            $("#compromised_head").hide();
            $("#vulnerabilities_head").hide();
            $("#indicators_head").hide();
            $("#malware_head").hide();
            $("#adversaries_head").hide();
            $(".type_indicator").hide();
        } else if (btn_val == 'events') {
            $("#news_head").hide();
            $("#events_head").show();
            $("#events").collapse("show");
            $("#data-leak_head").hide();
            $("#compromised_head").hide();
            $("#vulnerabilities_head").hide();
            $("#indicators_head").hide();
            $("#malware_head").hide();
            $("#adversaries_head").hide();
            $(".type_indicator").hide();
        } else if (btn_val == 'data-leak') {
            $("#news_head").hide();
            $("#events_head").hide();
            $("#data-leak_head").show();
            $("#data-leak").collapse("show");
            $("#compromised_head").hide();
            $("#vulnerabilities_head").hide();
            $("#indicators_head").hide();
            $("#malware_head").hide();
            $("#adversaries_head").hide();
            $(".type_indicator").hide();
        } else if (btn_val == 'compromised') {
            $("#news_head").hide();
            $("#events_head").hide();
            $("#data-leak_head").hide();
            $("#compromised_head").show();
            $("#compromised").collapse("show");
            $("#vulnerabilities_head").hide();
            $("#indicators_head").hide();
            $("#malware_head").hide();
            $("#adversaries_head").hide();
            $(".type_indicator").hide();
        } else if (btn_val == 'vulnerabilities') {
            $("#news_head").hide();
            $("#events_head").hide();
            $("#data-leak_head").hide();
            $("#compromised_head").hide();
            $("#vulnerabilities_head").show();
            $("#vulnerabilities").collapse("show");
            $("#indicators_head").hide();
            $("#malware_head").hide();
            $("#adversaries_head").hide();
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
            $("#malware_head").hide();
            $("#adversaries_head").hide();
        } else if (btn_val == 'adversaries') {
            $("#news_head").hide();
            $("#events_head").hide();
            $("#data-leak_head").hide();
            $("#compromised_head").hide();
            $("#vulnerabilities_head").hide();
            $("#malware_head").hide();
            $("#adversaries_head").show();
            $("#adversaries").collapse("show");

            $(".type_indicator").show();
        } else if (btn_val == 'malware') {
            $("#news_head").hide();
            $("#events_head").hide();
            $("#data-leak_head").hide();
            $("#compromised_head").hide();
            $("#vulnerabilities_head").hide();
            $("#adversaries_head").hide();
            $("#malware_head").show();
            $("#malwere").collapse("show");
            

            $(".type_indicator").show();
        } 
    });


    function modal_iframe_source(url){
        loading('load');
        $('#iframe_source').attr('src', url + '?iframe=1');
        $('#exampleModal').modal('show');
    }

    function load_frame(){
        loading('stop_load');
    }

    function close_modal_frame(){
        $('#exampleModal').modal('hide');
        $('#iframe_source').attr('src', '');
        loading('stop_load');
    }
var interval;
$(function(){
    if(mode_search == 'lookup'){
  
        $('#table-container').hide();
        $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "/loadSearchAPI?mode=check_api_search_limit",
                method: 'post',
                data: ({
                    keyword:text_search_new,
                    source:'check_api_search_limit'
                }),
                beforeSend: function(){
                    $('.ajax-loading').show();
                },
            }).done(function(res){

                $('#l_api_limit').html('('+res.site_request_limit_api_count+'/'+res.center_search_api_loookup_limit+')');
                if(res.search_api_loookup_allow ==1){
                    $('#table-container').hide();
                        $('.int-lookup-main').toggle();
                            resetRiskCalculation(res.type);

                            if (res.type && res.type.toLowerCase() === 'ip') {
                                if ($('.load-abuseipdb').length === 0) {
                                    $('#circle-abuseipdb').before('<div class="load-abuseipdb loadfixed backdrop-loader white"><div class="loader4 centerloader"></div><div class="loadding-text">Loading ...</div></div>');
                                }
                                $('.load-abuseipdb').show();
                                $('#not-abuseipdb').hide();
                                loadSearchAPI('abuseipdb');
                            } else {
                                $('.load-abuseipdb').remove();
                                $('#not-abuseipdb').show();
                            }

                            if ($('.load-virustotal').length === 0) {
                                $('#circle-virustotal').before('<div class="load-virustotal loadfixed backdrop-loader white"><div class="loader4 centerloader"></div><div class="loadding-text">Loading ...</div></div>');
                            }
                            $('.load-virustotal').show();
                            $('#not-virustotal').hide();

                            if ($('.load-hybrid').length === 0) {
                                $('#circle-hybrid').before('<div class="load-hybrid loadfixed backdrop-loader white"><div class="loader4 centerloader"></div><div class="loadding-text">Loading ...</div></div>');
                            }
                            $('.load-hybrid').show();
                            $('#not-hybrid').hide();

                            if ($('.load-threatfox').length === 0) {
                                $('#circle-threatfox').before('<div class="load-threatfox loadfixed backdrop-loader white"><div class="loader4 centerloader"></div><div class="loadding-text">Loading ...</div></div>');
                            }
                            $('.load-threatfox').show();
                            $('#not-threatfox').hide();

                            if ($('.load-rstcloud').length === 0) {
                                $('#circle-rstcloud').before('<div class="load-rstcloud loadfixed backdrop-loader white"><div class="loader4 centerloader"></div><div class="loadding-text">Loading ...</div></div>');
                            }
                            $('.load-rstcloud').show();
                            $('#not-rstcloud').hide();

                            if ($('.load-otx_indicators').length === 0) {
                                $('#circle-otx').before('<div class="load-otx_indicators loadfixed backdrop-loader white"><div class="loader4 centerloader"></div><div class="loadding-text">Loading ...</div></div>');
                            }
                            $('.load-otx_indicators').show();
                            $('#not-otx').hide();

                            loadSearchAPI('virustotal');
                            loadSearchAPI('hybrid');
                            loadSearchAPI('threatfox');
                            loadSearchAPI('rstcloud');
                            loadSearchAPI('internal_events');

                            $('#otx_indicators_loadspinner_basic_info').show();
                            $('#otx_indicators_loadspinner_basic_info_table').show();
                            $("#table-related-event").hide();

                            $('#otx_event_loadspinner_basic_info').show();
                            $('#otx_event_loadspinner_basic_info_table').show();
                            $("#table-related-indicator").hide();
                            loadSearchAPI('otx_indicators');

                }else{
                        
                        if(res.status_code ==401){
                            window.location.href = "/search?keyword="+text_search_new;
                        }else{
                            if(res.message == 'allow only type ( IP,Domain,URL,MD5, SHA1 or SHA256 ) Please contact the system administrator.'){

                            }else{
                                swal.fire(res.message);
                            }
                     
                        }
                   

                }
        
       
                    }).fail(function(jqXHR, ajaxOptions, thrownError){
                console.log("No response from server");
        });
    }else{
        @if($role_custom['news'])
            loadNews();
            count_rows++;
        @endif
        @if($role_custom['vulnerabilities'])
            loadVulnerabilities();
            count_rows++;
        @endif
        @if($role_custom['compromised'])
            loadCompromised();
            count_rows++;
        @endif
        @if($role_custom['data_leak'])
            loadDataLeak();
            count_rows++;
        @endif
        @if($role_custom['web_defacement'])
            loadWebDefacement();
            count_rows++;
        @endif
        @if($role_custom['indicators'])
            loadEvents();
            count_rows++;
        @endif
        @if($role_custom['indicators'])
            loadAdversaries();
            count_rows++;
        @endif
        @if($role_custom['indicators'])
            loadMalware();
            count_rows++;
        @endif

        interval = setInterval(
            function(){ 
                if(count_rows == count_rows_finish){
                    $('#all').text(count_all);
                    $('#total_all').text(count_all);
                    if(count_all == 0){
                        let html = '';
                        html += `<div class="notfound">
                            <img src="{{asset('images/notfound.png')}}" alt="" style="max-width: 500px;width:100%:">
                            <h1>Sorry. no result found</h1>
                            <p>What you searched was unfortunately <br>not found or doesn't exist.</p>
                        </div>`;
                        $('#clauses-list').append(html);
                    }
                    clearInterval(interval);
                }
            }
        , 1000);
    }
    
});

function loadNews(){
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "/newSearchAPI",
        method: 'post',
        data: ({
            keyword:text_search_new,
            type:'news',
        }),
    }).done(function(res){
        let data = [];
        for(let i in res.dataSearch){
            const news = res.dataSearch[i];
            data = news;
        }
        $('#news').text(data.count);
        let html = '';
        if(parseInt(data.count) > 0){
            html += `
            <li id="news_head" class="panel panel-default">
                <div class="panel-heading fontw-weight-bold">
                    <a class="accordion-toggle name" data-toggle="collapse" data-parent="#accordion2" href="#news_coll">
                        @icon('solid/caret-right') 
                        News ${data.count ? parseInt(data.count) : 0}
                    </a>
                </div>
                <div id="news_coll" class="panel-collapse collapse in">`;
                    for(let i in data.queryData){
                        const news = data.queryData[i];
                        html += `<div class="panel-body clause" data-div_i_type="">
                            <div class="item-search">
                                <div style="width: 90%;">
                                    <a href="javascript:void(0);" onclick="modal_iframe_source('${news.link}')" class="fz-search-20px">
                                        ${news.name}
                                    </a>

                                    ${news.content}
                                </div>
                                <div style="width: 10%" class="text-center">
                                    <a href="javascript:void(0);" onclick="modal_iframe_source('${news.link}')" class="btn btn-info"><i class="fas fa-eye"></i> View</a>
                                </div>
                            </div>
                        </div>`;
                    }
                html += `</div>
            </li>`;
        }
        $('#clauses-list').append(html);
        count_all += data.count ? parseInt(data.count) : 0;
        count_rows_finish++;
    }).fail(function(jqXHR, ajaxOptions, thrownError){
        console.log("No response from server");
    });
}

function loadVulnerabilities(){
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "/newSearchAPI",
        method: 'post',
        data: ({
            keyword:text_search_new,
            type:'vulnerabilities',
        }),
    }).done(function(res){
        let data = [];
        for(let i in res.dataSearch){
            const data_leak = res.dataSearch[i];
            data = data_leak;
        }
        $('#vulnerabilities').text(data.count);
        let html = '';
        if(parseInt(data.count) > 0){
            html += `
            <li id="vulnerabilities_head" class="panel panel-default">
                <div class="panel-heading fontw-weight-bold">
                    <a class="accordion-toggle name" data-toggle="collapse" data-parent="#accordion2" href="#vulnerabilities_coll">
                        @icon('solid/caret-right') 
                        Vulnerabilities ${data.count ? parseInt(data.count) : 0}
                    </a>
                </div>
                <div id="vulnerabilities_coll" class="panel-collapse collapse in">`;
                    for(let i in data.queryData){
                        const vulnerabilities = data.queryData[i];
                        html += `<div class="panel-body clause" data-div_i_type="">
                            <div class="item-search">
                                <div style="width: 90%;">
                                    <a href="javascript:void(0);" onclick="modal_iframe_source('${vulnerabilities.link}')" class="fz-search-20px">
                                        ${vulnerabilities.name}
                                    </a>

                                    ${vulnerabilities.content}
                                </div>
                                <div style="width: 10%" class="text-center">
                                    <a href="javascript:void(0);" onclick="modal_iframe_source('${vulnerabilities.link}')" class="btn btn-info"><i class="fas fa-eye"></i> View</a>
                                </div>
                            </div>
                        </div>`;
                    }
                html += `</div>
            </li>`;
        }
        $('#clauses-list').append(html);
        count_all += data.count ? parseInt(data.count) : 0;
        count_rows_finish++;
    }).fail(function(jqXHR, ajaxOptions, thrownError){
        console.log("No response from server");
    });
}

function loadCompromised(){
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "/newSearchAPI",
        method: 'post',
        data: ({
            keyword:text_search_new,
            type:'compromised',
        }),
    }).done(function(res){
        let data = [];
        for(let i in res.dataSearch){
            const data_leak = res.dataSearch[i];
            data = data_leak;
        }
        $('#compromised').text(data.count);
        let html = '';
        if(parseInt(data.count) > 0){
            html += `
            <li id="compromised_head" class="panel panel-default">
                <div class="panel-heading fontw-weight-bold">
                    <a class="accordion-toggle name" data-toggle="collapse" data-parent="#accordion2" href="#compromised_coll">
                        @icon('solid/caret-right') 
                        Compromised ${data.count ? parseInt(data.count) : 0}
                    </a>
                </div>
                <div id="compromised_coll" class="panel-collapse collapse in">`;
                    for(let i in data.queryData){
                        const compromised = data.queryData[i];
                        html += `<div class="panel-body clause" data-div_i_type="">
                            <div class="item-search">
                                <div style="width: 90%;">
                                    <a href="javascript:void(0);" onclick="modal_iframe_source('${compromised.link}')" class="fz-search-20px">
                                        ${compromised.name}
                                    </a>

                                    ${compromised.content}
                                </div>
                                <div style="width: 10%" class="text-center">
                                    <a href="javascript:void(0);" onclick="modal_iframe_source('${compromised.link}')" class="btn btn-info"><i class="fas fa-eye"></i> View</a>
                                </div>
                            </div>
                        </div>`;
                    }
                html += `</div>
            </li>`;
        }
        $('#clauses-list').append(html);
        count_all += data.count ? parseInt(data.count) : 0;
        count_rows_finish++;
    }).fail(function(jqXHR, ajaxOptions, thrownError){
        console.log("No response from server");
    });
}

function loadDataLeak(){
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "/newSearchAPI",
        method: 'post',
        data: ({
            keyword:text_search_new,
            type:'data_leak',
        }),
    }).done(function(res){
        let data = [];
        for(let i in res.dataSearch){
            const data_leak = res.dataSearch[i];
            data = data_leak;
        }
        $('#data_leak').text(data.count);
        let html = '';
        if(parseInt(data.count) > 0){
            html += `
            <li id="data_leak_head" class="panel panel-default">
                <div class="panel-heading fontw-weight-bold">
                    <a class="accordion-toggle name" data-toggle="collapse" data-parent="#accordion2" href="#data_leak_coll">
                        @icon('solid/caret-right') 
                        Data Leak ${data.count ? parseInt(data.count) : 0}
                    </a>
                </div>
                <div id="data_leak_coll" class="panel-collapse collapse in">`;
                    for(let i in data.queryData){
                        const data_leak = data.queryData[i];
                        html += `<div class="panel-body clause" data-div_i_type="">
                            <div class="item-search">
                                <div style="width: 90%;">
                                    <a href="javascript:void(0);" onclick="modal_iframe_source('${data_leak.link}')" class="fz-search-20px">
                                        ${data_leak.name}
                                    </a>

                                    ${data_leak.content}
                                </div>
                                <div style="width: 10%" class="text-center">
                                    <a href="javascript:void(0);" onclick="modal_iframe_source('${data_leak.link}')" class="btn btn-info"><i class="fas fa-eye"></i> View</a>
                                </div>
                            </div>
                        </div>`;
                    }
                html += `</div>
            </li>`;
        }
        $('#clauses-list').append(html);
        count_all += data.count ? parseInt(data.count) : 0;
        count_rows_finish++;
    }).fail(function(jqXHR, ajaxOptions, thrownError){
        console.log("No response from server");
    });
}

function loadWebDefacement(){
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "/newSearchAPI",
        method: 'post',
        data: ({
            keyword:text_search_new,
            type:'web_defacement',
        }),
    }).done(function(res){
        let data = [];
        for(let i in res.dataSearch){
            const data_leak = res.dataSearch[i];
            data = data_leak;
        }
        $('#web_defacement').text(data.count);
        let html = '';
        if(parseInt(data.count) > 0){
            html += `
            <li id="web_defacement_head" class="panel panel-default">
                <div class="panel-heading fontw-weight-bold">
                    <a class="accordion-toggle name" data-toggle="collapse" data-parent="#accordion2" href="#web_defacement_coll">
                        @icon('solid/caret-right') 
                        Web Defacement ${data.count ? parseInt(data.count) : 0}
                    </a>
                </div>
                <div id="web_defacement_coll" class="panel-collapse collapse in">`;
                    for(let i in data.queryData){
                        const web_defacement = data.queryData[i];
                        html += `<div class="panel-body clause" data-div_i_type="">
                            <div class="item-search">
                                <div style="width: 90%;">
                                    <a href="javascript:void(0);" onclick="modal_iframe_source('${web_defacement.link}')" class="fz-search-20px">
                                        ${web_defacement.name}
                                    </a>

                                    ${web_defacement.content}
                                </div>
                                <div style="width: 10%" class="text-center">
                                    <a href="javascript:void(0);" onclick="modal_iframe_source('${web_defacement.link}')" class="btn btn-info"><i class="fas fa-eye"></i> View</a>
                                </div>
                            </div>
                        </div>`;
                    }
                html += `</div>
            </li>`;
        }
        $('#clauses-list').append(html);
        count_all += data.count ? parseInt(data.count) : 0;
        count_rows_finish++;
    }).fail(function(jqXHR, ajaxOptions, thrownError){
        console.log("No response from server");
    });
}

function loadEvents(){
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "/newSearchAPI",
        method: 'post',
        data: ({
            keyword:text_search_new,
            type:'events',
        }),
    }).done(function(res){
        let data = [];
        for(let i in res.dataSearch){
            const data_leak = res.dataSearch[i];
            data = data_leak;
        }
        $('#events').text(data.count);
        let html = '';
        if(parseInt(data.count) > 0){
            html += `
            <li id="events_head" class="panel panel-default">
                <div class="panel-heading fontw-weight-bold">
                    <a class="accordion-toggle name" data-toggle="collapse" data-parent="#accordion2" href="#events_coll">
                        @icon('solid/caret-right') 
                        Events ${data.count ? parseInt(data.count) : 0}
                    </a>
                </div>
                <div id="events_coll" class="panel-collapse collapse in">`;
                    for(let i in data.queryData){
                        const events = data.queryData[i];
                        html += `<div class="panel-body clause" data-div_i_type="">
                            <div class="item-search">
                                <div style="width: 90%;">
                                    <a href="javascript:void(0);" onclick="modal_iframe_source('${events.link}')" class="fz-search-20px">
                                        ${events.name}
                                    </a>

                                    <p class="">Last Status : ${js_check_last_status(events.is_modified)} | Public : ${js_check_publish(events.public)}</p>
                                                           
                                    <p>Tags : ${js_explode_val(events.tags,'tags')}</p>
                                    <p>Groups : ${js_explode_val(events.groups,'groups')}</p>
                                    <p>Industries : ${js_explode_val(events.industries,'industries')}</p>
                                </div>
                                <div style="width: 10%" class="text-center">
                                    <a href="javascript:void(0);" onclick="modal_iframe_source('${events.link}')" class="btn btn-info"><i class="fas fa-eye"></i> View</a>
                                </div>
                            </div>
                        </div>`;
                    }
                html += `</div>
            </li>`;
        }
        $('#clauses-list').append(html);
        count_all += data.count ? parseInt(data.count) : 0;
        count_rows_finish++;
    }).fail(function(jqXHR, ajaxOptions, thrownError){
        console.log("No response from server");
    });
}

function loadAdversaries(){
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "/newSearchAPI",
        method: 'post',
        data: ({
            keyword:text_search_new,
            type:'adversaries',
        }),
    }).done(function(res){
        let data = [];
        for(let i in res.dataSearch){
            const data_leak = res.dataSearch[i];
            data = data_leak;
        }
        $('#adversaries').text(data.count);
        let html = '';
        if(parseInt(data.count) > 0){
            html += `
            <li id="adversaries_head" class="panel panel-default">
                <div class="panel-heading fontw-weight-bold">
                    <a class="accordion-toggle name" data-toggle="collapse" data-parent="#accordion2" href="#adversaries_coll">
                        @icon('solid/caret-right') 
                        Threat Actor ${data.count ? parseInt(data.count) : 0}
                    </a>
                </div>
                <div id="adversaries_coll" class="panel-collapse collapse in">`;
                    for(let i in data.queryData){
                        const adversaries = data.queryData[i];
                        html += `<div class="panel-body clause" data-div_i_type="">
                            <div class="item-search">
                                <div style="width: 90%;">
                                    <a href="javascript:void(0);" onclick="modal_iframe_source('${adversaries.link}')" class="fz-search-20px">
                                        ${adversaries.name}
                                    </a>

                                    ${adversaries.content}
                                </div>
                                <div style="width: 10%" class="text-center">
                                    <a href="javascript:void(0);" onclick="modal_iframe_source('${adversaries.link}')" class="btn btn-info"><i class="fas fa-eye"></i> View</a>
                                </div>
                            </div>
                        </div>`;
                    }
                html += `</div>
            </li>`;
        }
        $('#clauses-list').append(html);
        count_all += data.count ? parseInt(data.count) : 0;
        count_rows_finish++;
    }).fail(function(jqXHR, ajaxOptions, thrownError){
        console.log("No response from server");
    });
}

function loadMalware(){
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: "/newSearchAPI",
        method: 'post',
        data: ({
            keyword:text_search_new,
            type:'malware',
        }),
    }).done(function(res){
        let data = [];
        for(let i in res.dataSearch){
            const data_leak = res.dataSearch[i];
            data = data_leak;
        }
        $('#malware').text(data.count);
        let html = '';
        if(parseInt(data.count) > 0){
            html += `
            <li id="malware_head" class="panel panel-default">
                <div class="panel-heading fontw-weight-bold">
                    <a class="accordion-toggle name" data-toggle="collapse" data-parent="#accordion2" href="#malware_coll">
                        @icon('solid/caret-right') 
                        Malware ${data.count ? parseInt(data.count) : 0}
                    </a>
                </div>
                <div id="malware_coll" class="panel-collapse collapse in">`;
                    for(let i in data.queryData){
                        const malware = data.queryData[i];
                        html += `<div class="panel-body clause" data-div_i_type="">
                            <div class="item-search">
                                <div style="width: 90%;">
                                    <a href="javascript:void(0);" onclick="modal_iframe_source('/indicators/detail_malware?malware_uuid=${encodeURIComponent(malware.name)}')" class="fz-search-20px">
                                        ${malware.name}
                                    </a>

                                    ${malware.content}
                                </div>
                                <div style="width: 10%" class="text-center">
                                    <a href="javascript:void(0);" onclick="modal_iframe_source('/indicators/detail_malware?malware_uuid=${encodeURIComponent(malware.name)}')" class="btn btn-info"><i class="fas fa-eye"></i> View</a>
                                </div>
                            </div>
                        </div>`;
                    }
                html += `</div>
            </li>`;
        }
        $('#clauses-list').append(html);
        count_all += data.count ? parseInt(data.count) : 0;
        count_rows_finish++;
    }).fail(function(jqXHR, ajaxOptions, thrownError){
        console.log("No response from server");
    });
}

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

    function js_check_last_status(val) {
        if(val== true) {
            result = 'Modified';
        } else {
            result = 'Created';
        }
        return result;
    }

    function js_check_publish(val) {
        if(val==1) {
            let result = '<i class="fas fa-check"></i>';
        } else {
            let result = '';
        }
        return result;
    }

    function js_explode_val(val, type) {
        let result = '';
        if(val) {
            let val_arr = val.split(',');
            if(val_arr) {
                result += '<div>';
                for(let i in val_arr){
                    const tag = val_arr[i];
                    if(type == 'tags') {
                        result +=  `<a href="/indicators/newTags?id=${tag}">${tag}</a> ,`;
                    } else if (type == 'groups') {
                        result +=  `<a href="/indicators/newGroups?id=${tag}">${tag}</a> ,`;
                    } else {
                        result +=  `<a href="#">${tag}</a> ,`;
                    }
                }
                    
                result += '</div>';
                result += result.rtrim(',');
            }
        } else {
            result = '';
        }
        return result;
    }

    String.prototype.rtrim = function (s) {
        if (s == undefined)
            s = '\\s';
        return this.replace(new RegExp("[" + s + "]*$"), '');
    };



    $('.int-lookup-main').hide();

    $('.lookup').on('click',function(){
     

              
        $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "/loadSearchAPI?mode=check_api_search_limit",
                method: 'post',
                data: ({
                    keyword:text_search_new,
                    source:'check_api_search_limit'
                }),
                beforeSend: function(){
                    $('.ajax-loading').show();
                },
            }).done(function(res){

                $('#l_api_limit').html('('+res.site_request_limit_api_count+'/'+res.center_search_api_loookup_limit+')');
                if(res.search_api_loookup_allow ==1){
                    $('#table-container').hide();
                $('.int-lookup-main').toggle();
                            resetRiskCalculation(res.type);
                            if (res.type && res.type.toLowerCase() === 'ip') {
                                if ($('.load-abuseipdb').length === 0) {
                                    $('#circle-abuseipdb').before('<div class="load-abuseipdb loadfixed backdrop-loader white"><div class="loader4 centerloader"></div><div class="loadding-text">Loading ...</div></div>');
                                }
                                $('.load-abuseipdb').show();
                                $('#not-abuseipdb').hide();
                                loadSearchAPI('abuseipdb');
                            } else {
                                $('.load-abuseipdb').remove();
                                $('#not-abuseipdb').show();
                            }

                            if ($('.load-virustotal').length === 0) {
                                $('#circle-virustotal').before('<div class="load-virustotal loadfixed backdrop-loader white"><div class="loader4 centerloader"></div><div class="loadding-text">Loading ...</div></div>');
                            }
                            $('.load-virustotal').show();
                            $('#not-virustotal').hide();

                            if ($('.load-hybrid').length === 0) {
                                $('#circle-hybrid').before('<div class="load-hybrid loadfixed backdrop-loader white"><div class="loader4 centerloader"></div><div class="loadding-text">Loading ...</div></div>');
                            }
                            $('.load-hybrid').show();
                            $('#not-hybrid').hide();

                            if ($('.load-threatfox').length === 0) {
                                $('#circle-threatfox').before('<div class="load-threatfox loadfixed backdrop-loader white"><div class="loader4 centerloader"></div><div class="loadding-text">Loading ...</div></div>');
                            }
                            $('.load-threatfox').show();
                            $('#not-threatfox').hide();

                            if ($('.load-rstcloud').length === 0) {
                                $('#circle-rstcloud').before('<div class="load-rstcloud loadfixed backdrop-loader white"><div class="loader4 centerloader"></div><div class="loadding-text">Loading ...</div></div>');
                            }
                            $('.load-rstcloud').show();
                            $('#not-rstcloud').hide();

                            if ($('.load-otx_indicators').length === 0) {
                                $('#circle-otx').before('<div class="load-otx_indicators loadfixed backdrop-loader white"><div class="loader4 centerloader"></div><div class="loadding-text">Loading ...</div></div>');
                            }
                            $('.load-otx_indicators').show();
                            $('#not-otx').hide();

                            loadSearchAPI('virustotal');
                            loadSearchAPI('hybrid');
                            loadSearchAPI('threatfox');
                            loadSearchAPI('rstcloud');
                            loadSearchAPI('internal_events');

                            $('#otx_indicators_loadspinner_basic_info').show();
                            $('#otx_indicators_loadspinner_basic_info_table').show();
                            $("#table-related-event").hide();

                            $('#otx_event_loadspinner_basic_info').show();
                            $('#otx_event_loadspinner_basic_info_table').show();
                            $("#table-related-indicator").hide();
                            loadSearchAPI('otx_indicators');

                }else{
                
                    swal.fire(res.message);

                }
        
       
                    }).fail(function(jqXHR, ajaxOptions, thrownError){
                console.log("No response from server");
        });
    
        
       
  
       




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

    function click_abuseipdb(){
        $('#card-search-abuseipdb').off('click').on('click',function(){
            $(this).toggleClass('active');
            if($(this).hasClass('active')){
                $('.table-abuseipdb').show();
            }else{
                $('.table-abuseipdb').hide();
            }
        });
    }

    function click_threatfox(){
        $('#card-search-threatfox').off('click').on('click',function(){
            $(this).toggleClass('active');
            if($(this).hasClass('active')){
                $('.table-threatfox').show();
            }else{
                $('.table-threatfox').hide();
            }
        });
    }

    function click_rstcloud(){
        $('#card-search-rstcloud').off('click').on('click',function(){
            $(this).toggleClass('active');
            if($(this).hasClass('active')){
                $('.table-rstcloud').show();
            }else{
                $('.table-rstcloud').hide();
            }
        });
    }

    function click_otx(){
        $('#card-search-otx').off('click').on('click',function(){
            $(this).toggleClass('active');
            if($(this).hasClass('active')){
                $('.otx_indicators').show();
            }else{
                $('.otx_indicators').hide();
            }
        });
    }

    let threatScores = {};
    let expectedSources = [];
    let completedSources = [];

    const THRESHOLDS_JS = {
        vt: [
            [10, 10], [7, 8], [4, 6], [2, 4], [1, 2]
        ],
        abuse_score: [
            [80, 5], [40, 4], [25, 2], [15, 1]
        ],
        abuse_reports: [
            [50, 5], [20, 3], [10, 2], [5, 1]
        ],
        tf: [
            [95, 10], [85, 8], [75, 6], [50, 4], [25, 2]
        ],
        otx: [
            [50, 10], [30, 8], [20, 6], [10, 4], [5, 2], [1, 1]
        ]
    };

    const WEIGHTS_JS = {
        vt: 0.35, abuse: 0.20, tf: 0.10, otx: 0.25, rst: 0.10
    };

    function mapScoreJS(val, thresholds) {
        for (let i = 0; i < thresholds.length; i++) {
            if (val >= thresholds[i][0]) {
                return thresholds[i][1];
            }
        }
        return 0;
    }

    function resetRiskCalculation(type) {
        threatScores = {
            virustotal: 0,
            abuseipdb: 0,
            threatfox: 0,
            otx_indicators: 0,
            rstcloud: 0,
            hybrid: 0
        };
        completedSources = [];
        expectedSources = ['virustotal', 'hybrid', 'threatfox', 'rstcloud', 'otx_indicators'];
        
        let typeLower = type ? type.toLowerCase() : '';
        if (typeLower === 'ip') {
            expectedSources.push('abuseipdb');
        }
        
        $('.table-virustotal').hide();
        $('.table-hybrid').hide();
        $('.table-abuseipdb').hide();
        $('.table-threatfox').hide();
        $('.table-rstcloud').hide();
        $('.otx_indicators').hide();

        $('#card-search-int1').removeClass('active');
        $('#card-search-int2').removeClass('active');
        $('#card-search-abuseipdb').removeClass('active');
        $('#card-search-threatfox').removeClass('active');
        $('#card-search-rstcloud').removeClass('active');
        $('#card-search-otx').removeClass('active');

        $('#not-virustotal').hide();
        $('#not-hybrid').hide();
        $('#not-abuseipdb').hide();
        $('#not-threatfox').hide();
        $('#not-rstcloud').hide();
        $('#not-otx').hide();

        $('#text_status_risk').html('<span class="label label-secondary">Analyzing...</span>');
    }

    function checkAndCalculateRisk(source, value) {
        if (!completedSources.includes(source)) {
            completedSources.push(source);
        }
        if (value !== undefined) {
            threatScores[source] = value;
        }

        let allFinished = true;
        for (let i = 0; i < expectedSources.length; i++) {
            if (!completedSources.includes(expectedSources[i])) {
                allFinished = false;
                break;
            }
        }

        if (allFinished) {
            calculateWeightedScore();
        }
    }

    function calculateWeightedScore() {
        let typeLower = $('#type_search').text() ? $('#type_search').text().toLowerCase() : '';
        
        let activeWeights = {
            vt: WEIGHTS_JS.vt,
            tf: WEIGHTS_JS.tf,
            otx: WEIGHTS_JS.otx,
            rst: WEIGHTS_JS.rst
        };
        
        if (typeLower === 'ip') {
            activeWeights.abuse = WEIGHTS_JS.abuse;
        } else {
            activeWeights.vt += WEIGHTS_JS.abuse;
        }

        let scores = {
            vt: threatScores.virustotal || 0,
            tf: threatScores.threatfox || 0,
            otx: threatScores.otx_indicators || 0,
            rst: threatScores.rstcloud || 0
        };
        if (typeLower === 'ip') {
            scores.abuse = threatScores.abuseipdb || 0;
        }

        let totalWeight = 0;
        let weightedSum = 0;

        for (let key in activeWeights) {
            let score = scores[key];
            if (score === -1 || score === undefined) {
                continue;
            }
            if (key === 'rst' && score === 0) {
                continue; 
            }
            totalWeight += activeWeights[key];
            weightedSum += score * activeWeights[key];
        }

        let finalScore = 0;
        if (totalWeight > 0) {
            finalScore = Math.round(weightedSum / totalWeight);
        }

        renderOverallStatus(finalScore);
    }

    function renderOverallStatus(finalScore) {
        let level = "Informational";
        let colorClass = "info";
        let bgColor = "#22b9ff";

        if (finalScore >= 9) {
            level = "Critical";
            colorClass = "danger";
            bgColor = "#8b0000";
        } else if (finalScore >= 7) {
            level = "High";
            colorClass = "danger";
            bgColor = "#b93624";
        } else if (finalScore >= 4) {
            level = "Medium";
            colorClass = "warning";
            bgColor = "#ffb000";
        } else if (finalScore >= 2) {
            level = "Low";
            colorClass = "success";
            bgColor = "#409967";
        } else if (finalScore >= 1) {
            level = "Very Low";
            colorClass = "success";
            bgColor = "#409967";
        }

        let labelStyle = `background-color: ${bgColor} !important; color: ${bgColor === '#ffb000' ? '#333' : '#fff'} !important; font-weight: bold; padding: 10px 20px; border-radius: 6px; display: inline-block; font-size: 22px;`;
        let html_status = `<span style="${labelStyle}">${level} (${finalScore}/10)</span>`;
        $('#text_status_risk').html(html_status);
    }

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

    if(source == 'virustotal'){
        let vtScore = 0;
        if(res.status_code == 400 || !data || data.error){
            $('#not-virustotal').show();
            if ($.fn.DataTable.isDataTable('#table-virustotal')) {
                $('#table-virustotal').DataTable().destroy();
            }
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
            checkAndCalculateRisk('virustotal', -1);
        }else{
            number_new_row++;
            if(data.data){
                var last_analysis_stats = data.data.attributes.last_analysis_stats;
                if(last_analysis_stats){
                    var malicious = parseInt(last_analysis_stats.malicious || 0);
                    var suspicious = parseInt(last_analysis_stats.suspicious || 0);
                    vtScore = mapScoreJS(malicious, THRESHOLDS_JS.vt) + (suspicious >= 5 ? 2 : (suspicious >= 3 ? 1 : 0));
                    if(vtScore > 10) vtScore = 10;
                    
                    if(malicious > 0){
                        $('#text_' + source).text(malicious);
                        const elem = $("#circle-virustotal");
                        if(malicious > 0 && malicious <= 3){
                            status_value_virustotal = 1;
                            elem[0].style.removeProperty('background-color');
                            elem[0].style.setProperty('background-color', '#b93624', 'important');
                            $('#text_' + source).css('color', '#b93624');
                        }else if(malicious <= 5){
                            status_value_virustotal = 2;
                            elem[0].style.removeProperty('background-color');
                            elem[0].style.setProperty('background-color', '#b93624', 'important');
                            $('#text_' + source).css('color', '#b93624');
                        }else if(malicious <= 7){
                            status_value_virustotal = 3;
                            elem[0].style.removeProperty('background-color');
                            elem[0].style.setProperty('background-color', '#b93624', 'important');
                            $('#text_' + source).css('color', '#b93624');
                        }else if(malicious >= 10){
                            status_value_virustotal = 4;
                            elem[0].style.removeProperty('background-color');
                            elem[0].style.setProperty('background-color', '#b93624', 'important');
                            $('#text_' + source).css('color', '#b93624');
                        }
                    }
                    let total = (parseInt(last_analysis_stats.harmless || 0) + parseInt(last_analysis_stats.malicious || 0) + parseInt(last_analysis_stats.suspicious || 0) + parseInt(last_analysis_stats.timeout || 0) + parseInt(last_analysis_stats.undetected || 0));
                    $('#text_virustotal_sum').text('/ '+total);
                }
                
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
                if ($.fn.DataTable.isDataTable('#table-virustotal')) {
                    $('#table-virustotal').DataTable().destroy();
                }
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
            $('.load-virustotal').remove();
            click_virustotal();
            checkAndCalculateRisk('virustotal', vtScore);
        }
    }else if(source == 'hybrid'){
        if(res.status_code == 400){
            $('.load-hybrid').remove();
            checkAndCalculateRisk('hybrid', -1);
        }else{
            if(res && data){
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
                    if(data.count == 0 || (total / data.count) == 0 || data.validation_errors || !data.result || data.result.length === 0){
                        $('#not-hybrid').show();
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
                    if(count == 0 || (total / count) == 0 || data.validation_errors || !data.result || data.result.length === 0){
                        $('#not-hybrid').show();
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
                checkAndCalculateRisk('hybrid', 0);
            }else{
                $('.load-hybrid').remove();
                checkAndCalculateRisk('hybrid', -1);
            }
        }
    }else if(source == 'otx_indicators'){
        if(res.status_code == 400 || !data || data.error || data.detail){
            $('#otx_indicators_loadspinner_basic_info').hide();
            $('#otx_indicators_loadspinner_basic_info_table').hide();
            $('.load-otx_indicators').remove();
            $('#not-otx').show();
            checkAndCalculateRisk('otx_indicators', -1);
        }else{
            $('#otx_indicators_loadspinner_basic_info').show();
                var html ="";
                if(res.type == 'IP'){
                    var header = res.data || {};
                    var header2 = res.data2 || {};

                    html+='<div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> ASN:</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+(header.asn || '')+'</div></div></div>';
                    html+='<div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Indicator Facts:</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+''+'</div></div></div>';
                    html+='<div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Country:</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+(header.country_name || '')+'</div></div></div>';
                    var open_ports="";
                    var issuer = [];
                    var subject =[];
                    var reverse_dns ="";
                    if(header2 && header2.facts){
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
                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Open Ports:</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+open_ports+'</div></div></div>';

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
                        html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Related Tags:</b></div><div class="col-xl-10 col-lg-10 col-md-9"> <div class="text-trucate-ovf">'+Tags.join(",")+' </div></div></div></div>';
                      }
                      if(header.type =="IPv6"){
                        html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Reverse DNS:</b></div> <div class="col-xl-10 col-lg-10 col-md-9"> '+reverse_dns+'</div></div></div>';

                      }else{
                        html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Certificate Issuer:</b></div><div class="col-xl-10 col-lg-10 col-md-9"> '+issuer.join(", ")+'</div></div></div>';
                        html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Certificate Subject:</b></div><div class="col-xl-10 col-lg-10 col-md-9"> '+subject.join(", ")+'</div></div></div>';
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

                    var header = res.data || {};
                    var header2 = res.data2 || {};
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
                    

                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> IP Address:</b></div><div class="col-xl-10 col-lg-10 col-md-9"> '+IP+'</div></div></div>';
                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> WHOIS:</b></div><div class="col-xl-10 col-lg-10 col-md-9"> '+WHOIS+'</div></div></div>';
                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Country:</b></div><div class="col-xl-10 col-lg-10 col-md-9"> '+country_name+'</div></div></div>';
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
                        html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Related Tags:</b></div> <div class="col-xl-10 col-lg-10 col-md-9"> <div class="text-trucate-ovf">'+Tags.join(",")+'</div></div></div></div>';
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

                    var header = res.data || {};
                    var header2 = res.data2 || {};

                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Hostname:</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+(header.hostname || '')+'</div></div></div>';
                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Domain:</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+(header.domain || '')+'</div></div>';

                    var WHOIS ="";
                    if(header.whois){
                        WHOIS ='<a target="_blank" href="'+header.whois+'">'+'WHOIS'+'</a>';

                    }
                    

                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Country:</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+WHOIS+'</div></div></div>';
                    var open_ports="";
                    var issuer = [];
                    var subject =[];
                    var reverse_dns ="";
                    if(header2 && header2.facts){
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
                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Open Ports:</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+open_ports+'</div></div></div>';

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
                        html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Related Tags:</b></div> <div class="col-xl-10 col-lg-10 col-md-9"><div class="text-trucate-ovf">'+Tags.join(",")+'</div></div></div></div>';
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
                    var header_analysis = (res.data && res.data.analysis) ? res.data.analysis : {};
                    var header = res.data2 || {};
                    if(!jQuery.isEmptyObject(header_analysis)){
                        
                                if(header_analysis){
                                    if(header_analysis.datetime_int){
                                        html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"> <b> Analysis Date:</b></div>  <div class="col-xl-10 col-lg-10 col-md-9">'+header_analysis.datetime_int+'</div></div></div>';
                                    }
                                    if(header_analysis.info.results.file_type){
                                        html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"> <b> File Type:</b></div>  <div class="col-xl-10 col-lg-10 col-md-9">'+header_analysis.info.results.file_type+'</div></div></div>';
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


                                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"> <b> Antivirus Detections:</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+Antivirus_Detections.join(",")+'</div></div></div>';
                                    if(header_analysis){
                                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"> <b> Size:</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+header_analysis.info.results.filesize+' bytes</div></div></div>';
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
                                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"> <b>Yara Detections</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+Yara_Detections.join("</br>")+'</div></div></div>';
                                    if(header_analysis){
                                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"> <b> MD5:</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+header_analysis.info.results.md5+' </div></div></div>';
                                    }


                                    var Alerts = [];
                                    if(header_analysis){
                                        if(header_analysis.plugins.cuckoo){
                                            if(header_analysis.plugins.cuckoo.result){
                                
                                                if(header_analysis.plugins.cuckoo.result.signatures){

                                                    for (let index = 0; index <header_analysis.plugins.cuckoo.result.signatures.length; index++) {
                                                    
                                                        if(header_analysis.plugins.cuckoo.result.signatures[index].severity >=3){
                                                            Alerts.push('<span class="badge" style="background-color: #b93624;margin:2px;">'+header_analysis.plugins.cuckoo.result.signatures[index].name+'</span>');
                                                        }else if(header_analysis.plugins.cuckoo.result.signatures[index].severity >=2){
                                                            Alerts.push('<span class="badge" style="background-color: #ffb000;color:#333;margin:2px;">'+header_analysis.plugins.cuckoo.result.signatures[index].name+'</span>');

                                                        }else{
                                                            Alerts.push('<span class="badge" style="background-color: #409967;margin:2px;">'+header_analysis.plugins.cuckoo.result.signatures[index].name+'</span>');
                                                        }
                                                    
                                                    }
                                            }
                                            }
                                        }
                                    }


                                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Alerts:</b> </div><div class="col-xl-10 col-lg-10 col-md-9">'+Alerts.join("")+'</div> </div></div>';
                                    if(header_analysis){
                                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> SHA1:</b> </div><div class="col-xl-10 col-lg-10 col-md-9">'+header_analysis.info.results.sha1+'</div> </div></div>';
                                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> SHA256:</b> </div> <div class="col-xl-10 col-lg-10 col-md-9">'+header_analysis.info.results.sha256+'</div> </div></div>';
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

                                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> External Hosts:</b></div><div class="col-xl-10 col-lg-10 col-md-9"><div class="text-trucate-ovf" style="display:inline-block;word-break: break-word;">'+host_name.join(",")+'</div></div></div></div>';


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

                                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b>IMPHASH:</b>  </div><div class="col-xl-10 col-lg-10 col-md-9"> '+imphash+'</div> </div></div>';


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
                                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Related Tags:</b> </div> <div class="col-xl-10 col-lg-10 col-md-9"><div class="text-trucate-ovf"> '+Tags.join(",")+'</div></div> </div></div>';
                                }
                                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> PEHASH:</b> </div><div class="col-xl-10 col-lg-10 col-md-9"> '+pehash+'</div></div></div>';
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
                                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Related Groups:</b> </div> <div class="col-xl-10 col-lg-10 col-md-9"> '+Groups.join(",")+'</div> </div></div>';
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

                let pulseCount = 0;
                let otxHeader = res.data;
                if (otxHeader && otxHeader.pulse_info) {
                    if (otxHeader.pulse_info.count !== undefined) {
                        pulseCount = parseInt(otxHeader.pulse_info.count || 0);
                    } else if (otxHeader.pulse_info.pulses) {
                        pulseCount = otxHeader.pulse_info.pulses.length;
                    }
                }
                $('.load-otx_indicators').remove();
                $('#text_otx_indicators').text(pulseCount);
                const elemOTX = $("#circle-otx");
                if (elemOTX && elemOTX[0]) {
                    elemOTX[0].style.removeProperty('background-color');
                    if (pulseCount === 0) {
                        $('#not-otx').show();
                    } else if (pulseCount >= 30) {
                        elemOTX[0].style.setProperty('background-color', '#b93624', 'important');
                        $('#text_otx_indicators').css('color', '#b93624');
                    } else if (pulseCount >= 10) {
                        elemOTX[0].style.setProperty('background-color', '#ffb000', 'important');
                        $('#text_otx_indicators').css('color', '#ffb000');
                    } else {
                        elemOTX[0].style.setProperty('background-color', '#409967', 'important');
                        $('#text_otx_indicators').css('color', '#409967');
                    }
                }
                click_otx();
                $('#card-search-otx').addClass('active');
                $('.otx_indicators').show();
                let otxScore = mapScoreJS(pulseCount, THRESHOLDS_JS.otx);
                checkAndCalculateRisk('otx_indicators', otxScore);
        }
        multi_readmore_text();
    }else if(source == 'abuseipdb'){
        $('.load-abuseipdb').remove();
        if(res.status_code == 400 || res.status_code == 401 || res.status_code == 429 || res.status_code == 500 || !data || data.error || data.errors){
            $('#not-abuseipdb').show();
            checkAndCalculateRisk('abuseipdb', -1);
        }else{
            let item = data.data;
            let abuseScore = 0;
            let confidence = 0;
            let reports = 0;
            if(item){
                confidence = parseInt(item.abuseConfidenceScore || 0);
                reports = parseInt(item.totalReports || 0);
                abuseScore = mapScoreJS(confidence, THRESHOLDS_JS.abuse_score) + mapScoreJS(reports, THRESHOLDS_JS.abuse_reports);
                if (abuseScore > 10) abuseScore = 10;
            }
            
            $('#text_abuseipdb').text(confidence);
            const elem = $("#circle-abuseipdb");
            elem[0].style.removeProperty('background-color');
            if (confidence >= 50) {
                elem[0].style.setProperty('background-color', '#b93624', 'important');
                $('#text_abuseipdb').css('color', '#b93624');
            } else if (confidence >= 25) {
                elem[0].style.setProperty('background-color', '#ffb000', 'important');
                $('#text_abuseipdb').css('color', '#ffb000');
            } else {
                elem[0].style.setProperty('background-color', '#409967', 'important');
                $('#text_abuseipdb').css('color', '#409967');
            }

            if(item){
                let html = `
                <tr>
                    <td>${item.ipAddress || ''}</td>
                    <td>${item.abuseConfidenceScore || 0}%</td>
                    <td>${item.isp || ''}</td>
                    <td>${item.countryName || ''}</td>
                    <td>${item.usageType || ''}</td>
                    <td>${item.domain || ''}</td>
                    <td>${item.totalReports || 0}</td>
                    <td>${item.lastReportedAt ? moment(new Date(item.lastReportedAt)).format('DD-MM-YYYY HH:mm:ss') : 'N/A'}</td>
                </tr>`;
                document.getElementById("tbody-abuseipdb").innerHTML = html;
            }else{
                $('#not-abuseipdb').show();
            }

            $('#table-abuseipdb').DataTable({
                "dom": 'tp',
                "searching": false,
                "bPaginate": true,
                "bLengthChange": false,
                "bFilter": false,
                "bInfo": false,
                "bAutoWidth": false
            });
            click_abuseipdb();
            checkAndCalculateRisk('abuseipdb', abuseScore);
        }
    }else if(source == 'threatfox'){
        $('.load-threatfox').remove();
        if(res.status_code == 400 || !data || data.query_status !== 'ok' || !data.data || data.data.length === 0){
            if (data && data.query_status === 'user_blacklisted') {
                $('#not-threatfox h1').text('API Blacklisted').css('color', '#b93624');
            } else {
                $('#not-threatfox h1').text('No threat found').css('color', '');
            }
            $('#not-threatfox').show();
            checkAndCalculateRisk('threatfox', -1);
        }else{
            let maxConf = 0;
            let html = '';
            data.data.forEach(item => {
                let conf = parseInt(item.confidence_level || 0);
                if (conf > maxConf) maxConf = conf;
                
                html += `
                <tr>
                    <td>${item.ioc || ''}</td>
                    <td>${item.threat_type || ''}</td>
                    <td>${item.ioc_type || ''}</td>
                    <td>${item.malware || ''}</td>
                    <td>${item.confidence_level || 0}%</td>
                    <td>${item.first_seen || ''}</td>
                    <td>${item.reporter || ''}</td>
                </tr>`;
            });

            let tfScore = mapScoreJS(maxConf, THRESHOLDS_JS.tf);

            $('#text_threatfox').text(maxConf);
            const elemTF = $("#circle-threatfox");
            elemTF[0].style.removeProperty('background-color');
            if (maxConf >= 75) {
                elemTF[0].style.setProperty('background-color', '#b93624', 'important');
                $('#text_threatfox').css('color', '#b93624');
            } else if (maxConf >= 50) {
                elemTF[0].style.setProperty('background-color', '#ffb000', 'important');
                $('#text_threatfox').css('color', '#ffb000');
            } else {
                elemTF[0].style.setProperty('background-color', '#409967', 'important');
                $('#text_threatfox').css('color', '#409967');
            }

            document.getElementById("tbody-threatfox").innerHTML = html;
            $('#table-threatfox').DataTable({
                "dom": 'tp',
                "searching": false,
                "bPaginate": true,
                "bLengthChange": false,
                "bFilter": false,
                "bInfo": false,
                "bAutoWidth": false
            });
            click_threatfox();
            checkAndCalculateRisk('threatfox', tfScore);
        }
    }else if(source == 'rstcloud'){
        $('.load-rstcloud').remove();
        if(res.status_code == 400 || !data || data.error){
            $('#not-rstcloud').show();
            checkAndCalculateRisk('rstcloud', -1);
        }else{
            let rstScore = 0;
            let rstTotal = 0;
            if (data.score && data.score.total !== undefined) {
                rstTotal = parseFloat(data.score.total || 0);
                rstScore = Math.min(rstTotal / 10.0, 10);
            }

            $('#text_rstcloud').text(rstTotal);
            const elemRST = $("#circle-rstcloud");
            elemRST[0].style.removeProperty('background-color');
            if (rstTotal >= 70) {
                elemRST[0].style.setProperty('background-color', '#b93624', 'important');
                $('#text_rstcloud').css('color', '#b93624');
            } else if (rstTotal >= 40) {
                elemRST[0].style.setProperty('background-color', '#ffb000', 'important');
                $('#text_rstcloud').css('color', '#ffb000');
            } else {
                elemRST[0].style.setProperty('background-color', '#409967', 'important');
                $('#text_rstcloud').css('color', '#409967');
            }

            let threat_types = '';
            if (data.threat && Array.isArray(data.threat) && data.threat.length > 0) {
                threat_types = data.threat.join(', ');
            } else if (data.tags && data.tags.str && Array.isArray(data.tags.str)) {
                threat_types = data.tags.str.join(', ');
            }
            
            let category = '';
            if (data.industry && Array.isArray(data.industry) && data.industry.length > 0) {
                category = data.industry.join(', ');
            } else if (data.asn && data.asn.org) {
                category = data.asn.org;
            }
            
            let first_seen = '';
            if (data.fseen) {
                first_seen = moment(data.fseen * 1000).format('YYYY-MM-DD HH:mm:ss');
            }
            let last_seen = '';
            if (data.lseen) {
                last_seen = moment(data.lseen * 1000).format('YYYY-MM-DD HH:mm:ss');
            }
            
            let severity = '';
            if (rstTotal >= 70) severity = 'High';
            else if (rstTotal >= 40) severity = 'Medium';
            else if (rstTotal > 0) severity = 'Low';

            let resolved_ips = '';
            if (data.resolved && data.resolved.ip) {
                resolved_ips = data.resolved.ip.map(ipObj => ipObj.ip).join(', ');
            }

            let html = `
            <tr>
                <td>${data.ioc_value || data.ioc || ''}</td>
                <td>${data.ioc_type || ''}</td>
                <td>${data.score && data.score.total !== undefined ? data.score.total : 0}</td>
                <td>${severity}</td>
                <td>${threat_types}</td>
                <td>${category}</td>
                <td>${first_seen}</td>
                <td>${last_seen}</td>
            </tr>`;

            document.getElementById("tbody-rstcloud").innerHTML = html;
            $('#table-rstcloud').DataTable({
                "dom": 'tp',
                "searching": false,
                "bPaginate": true,
                "bLengthChange": false,
                "bFilter": false,
                "bInfo": false,
                "bAutoWidth": false
            });
            click_rstcloud();
            checkAndCalculateRisk('rstcloud', rstScore);
        }
    }else if(source == 'internal_events'){
        if(res.status_code == 200 && res.data && res.data.length > 0) {
            $('.table-internal-events').show();
            let html = '';
            for(let i=0; i<res.data.length; i++) {
                let item = res.data[i];
                let eventLink = `/indicators/events/events_detail/${item.event_id}`;
                html += `<tr>
                    <td>${item.source}</td>
                    <td><a href="${eventLink}" target="_blank" style="text-decoration: underline; color: #0b96c5;">${item.event_id}</a></td>
                    <td>${item.event_name}</td>
                    <td>${item.tags}</td>
                    <td>${item.date}</td>
                </tr>`;
            }
            if ($.fn.DataTable.isDataTable('#table-internal-events')) {
                $('#table-internal-events').DataTable().destroy();
            }
            $('#tbody-internal-events').html(html);
            $('#table-internal-events').DataTable({
                pageLength: 10,
                responsive: true,
                order: [[4, 'desc']]
            });
        } else {
            $('.table-internal-events').show();
            if ($.fn.DataTable.isDataTable('#table-internal-events')) {
                $('#table-internal-events').DataTable().clear().draw();
            } else {
                $('#tbody-internal-events').html('');
                $('#table-internal-events').DataTable({
                    pageLength: 10,
                    responsive: true,
                    order: [[4, 'desc']]
                });
            }
        }
    }
    }).fail(function(jqXHR, ajaxOptions, thrownError){
        console.log("No response from server");
        if (source === 'virustotal') $('.load-virustotal').remove();
        if (source === 'hybrid') $('.load-hybrid').remove();
        if (source === 'abuseipdb') $('.load-abuseipdb').remove();
        if (source === 'threatfox') $('.load-threatfox').remove();
        if (source === 'rstcloud') $('.load-rstcloud').remove();
        if (source === 'otx_indicators') {
            $('.load-otx_indicators').remove();
            $('#not-otx').show();
            $('#otx_indicators_loadspinner_basic_info').hide();
            $('#otx_indicators_general').html('<p class="text-danger text-center">No response from server</p>');
        }
        checkAndCalculateRisk(source, -1);
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
        f_load_puls_indictor(puls_id);

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
    let summary_total = (status_value_virustotal + status_value_hybrid) / number_new_row;
let html_status = ``;
if(summary_total <= 1.9){
html_status += `<div class="status-risk success" style="color: #fff;color: #fff !important;">
    Low
</div>`;
}else if(summary_total <= 2.6){
    html_status += `<div class="status-risk warning"  style="background-color: #f2ff15!important;color: #000 !important;">
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



$('.table-hybrid').show();
$('.table-virustotal').show();


$('');

function chart_time_line(id){
    Highcharts.chart(id, {
        title: {
            text: ''
        },
        yAxis: {
            title: {
                text: ''
            }
        },

        xAxis: {
            categories: ['Jan 1,2013','Jan 5,2013','Jan 6,2013','Jan 8,2013','Oct 9,2015','Dec 9,2016','May 9,2017'],
            accessibility: {
                rangeDescription: ''
            }
            
        },

        legend: {
            align: 'right',
            verticalAlign: 'top',
            borderWidth: 0
        },

        plotOptions: {
            series: {
                label: {
                    connectorAllowed: false,
                    enabled : false
                },
                step: 'left',
                marker: {
                    enabled: false
                },
            }
        },
        tooltip:{
            formatter: function () {
                return '<b>Oct 13,2013 </b> <br>'+ this.series.name  +' :' + this.y + '';
            },	
        },
        series: [{
            name: 'Dynamic IPs',
            data: [10, 10, 10, 50, 50, 80, 80]
        }, {
            name: 'Botnet Command and Control Server',
            data: [null, null, 20, 20, 20, 30, 10]
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

multi_readmore_text();
</script>
@endpush
@endsection