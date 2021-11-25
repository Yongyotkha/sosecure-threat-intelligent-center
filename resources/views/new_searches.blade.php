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
                Total Result : 
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
                            <div class="col-md-8">
                                <h3 class="page-header">
                                Information <span id="l_api_limit"></span>
                                </h3>
                            </div>
                            <div class="col-md-4 text-right">
                                <span class="st-dt-leak">
                                    <span class="st-dt vrh" data-toggle="tooltip" data-placement="left" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip vrh'>High</div><div class='text-st-tooltip'> เป็นข้อมูลภัยคุกคามที่อยู่ในรูปแบบของ Malicious Code หรือเป็น C&C Server หรือเป็นข้อมูลที่มีความสอดคล้องกับภัยคุกคามอย่างชัดเจนจากแหล่งข้อมูลต่าง ๆ และอยู่ในระดับรุนแรง</div></div>">High</span>
                                    {{-- <span class="st-dt high" data-toggle="tooltip" data-placement="left" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip high'>High</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้อย่างแพร่หลาย เช่น <br> ออกข่าว หรือมีการแชร์ข้อมูลจากแหล่งข้อมูลที่น่าเชื่อถือและเป็นที่แพร่หลาย <br> และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูล Username ,Password ของลูกค้าหรือเจ้าหน้าที่ดูแลระบบภายในองค์กรซึ่งเป็นข้อมูลที่สามารถนำมาใช้ได้จริง</div></div>">High</span> --}}
                                    <span class="st-dt md" data-toggle="tooltip" data-placement="left" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip md'>Medium</div><div class='text-st-tooltip'> เป็นข้อมูลภัยคุกคามที่อยู่ในรูปแบบของ Malicious Code หรือเป็น C&C Server หรือเป็นข้อมูลที่มีความสอดคล้องกับภัยคุกคามอย่างชัดเจนแต่อยู่ในระดับปานกลาง</div></div>">Medium</span>
                                    <span class="st-dt low" data-toggle="tooltip" data-placement="left" data-html="true" title="<div class='st-flex'><div class='box-st-tooltip low'>Low</div><div class='text-st-tooltip'>เป็นข้อมูลภัยคุกคามที่มีความสอดคล้องกับภัยคุกคามอยู่ในระดับต่ำและมีความสอดคล้องบางแหล่งข้อมูลเท่านั้น ซึ่งอาจจะเป็นข้อมูลที่ไม่ใช่ภัยคุกคาม</div></div>">Low</span>
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
                            </div>
                            <div class="col-md-4 text-right">
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
                            <div class="col-xs-12 col-sm-6 col-md-4">
                                <div id="card-search-int3" class="main-int-card active">
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
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </section>
                    
                    <section class="table-ibmcloud" style="display: none">
                        <h3 class="text-primary-head">IBM X-Force</h1>
                        <table id="table-ibmcloud" class="table">
                            <thead>
                                <tr>
                                    <th style="width: 5px;padding:0;"></th>
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
            <iframe id="iframe_source" onload="load_frame()" name="iframe_source" frameborder="0" width="100%" height="100%" allowfullscreen></iframe>
        </div>
    </div>
</div>
@push('pagestyle')
@include('stacks.css.highchart')
@include('stacks.css.datatables')
@include('stacks.css.multitext')
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
                        result +=  `<a href="/newTags?id=${tag}">${tag}</a> ,`;
                    } else if (type == 'groups') {
                        result +=  `<a href="/newGroups?id=${tag}">${tag}</a> ,`;
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
                        if(history.cats && history.cats.length != 0){
                            html += `<td style="padding:0;line-height: 0;">`;
                            for(let [key, value] of Object.entries(history.cats)){
                               var percent = value;
                               if(percent <= 39){
                                    html += `<div class="risk-status low-risk"></div>`; 
                               }else if(percent <= 69){
                                    html += `<div class="risk-status medium-risk"></div>`; 
                               }else if(percent <= 100){
                                    html += `<div class="risk-status high-risk"></div>`; 
                               }else{
                                   html += `<div class="risk-status low-risk"></div>`; 
                               }
                            }
                           html += ` </td>`;
                        }else{
                            html += `<td style="padding:0;line-height: 0;">
                                        <div class="risk-status low-risk"></div>
                                    </td>`;
                        }

                       
                        
                        if(history.cats){
                            html += `<td>`;
                            for(const [key, value] of Object.entries(history.cats)){
                                html += ` ${key} (${value}%)`;
                            }
                            html += `</td>`;
                        }else{
                            html += `<td></td>`;
                        }
                        
                        html += `<td>${history.reason}</td>`;
                       
                        if(history.geo){
                            html += `<td>${history.geo.country} ${history.geo.countrycode}</td>`;
                        }else{
                            html += `<td></td>`;
                        }
                        
                        if(history.created){
                        html += `<td>
                                ${moment(new Date(history.created)).format('YYYY-MM-DD HH:MM:SS')}
                            </td>`;
                        }else{
                            html += `<td></td>`;
                        }
                        html += `</tr> `;
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
                    "order" : [[4,"desc"]],
                    "columnDefs": [{
                        "targets" : 0,
                        "orderable" : false
                    }]
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

                    html+='<div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> ASN:</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+header.asn+'</div></div></div>';
                    html+='<div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Indicator Facts:</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+''+'</div></div></div>';
                    html+='<div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Country:</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+header.country_name+'</div></div></div>';
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

                    var header = res.data;
                    var header2 = res.data2;

                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Hostname:</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+header.hostname+'</div></div></div>';
                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Domain:</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+header.domain+'</div></div>';

                    var WHOIS ="";
                    if(header.whois){
                        WHOIS ='<a target="_blank" href="'+header.whois+'">'+'WHOIS'+'</a>';

                    }
                    

                    html+=' <div class="col-md-12 m-b-md"><div class="row"><div class="col-xl-2 col-lg-2 col-md-3"><b> Country:</b></div> <div class="col-xl-10 col-lg-10 col-md-9">'+WHOIS+'</div></div></div>';
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
                    var header_analysis = res.data.analysis;
                    var header = res.data2;
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
        }
        multi_readmore_text();
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
let summary_total = (status_value_ibmcloud + status_value_virustotal + status_value_hybrid) / number_new_row;
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
$('.table-ibmcloud').show();

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