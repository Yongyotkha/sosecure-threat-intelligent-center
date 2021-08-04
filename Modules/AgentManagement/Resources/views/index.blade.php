@extends('layouts.app')

<style>
    .dropdown-menu {
        right: 0 !important;
        left: unset !important;
    }
</style>
@section('content')

<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
            <div class="header-flex-overflow m-t-10">
                <div class="fwb-16">
                    <span>
                        Agent Management
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
                            <div class="col-lg-12">
                                <div>
                                    <div class="form-group m-b-md">
                                        <label for="" class="">Keyword</label>
                                        <input type="text" class="form-control" name="keyword" id="keyword"
                                            placeholder="Search">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-3 col-md-3 mb-1">
                                <h5 class="font-weight-bold">Filter By</h5>
                                <div id="btngroup_sort_by" class="btn-group special mb-2">
                                    <button class="btn btn-grey filter_agent_all active" value="" id="btn_search_all">
                                        <span> All </span>
                                    </button>
                                    <button class="btn filter_agent_alert btn-grey" value="alert">
                                        <span> Alert </span>
                                    </button>
                                    <button class="btn filter_agent_agent btn-grey" value="agent">
                                        <span> Agent </span>
                                    </button>
                                </div>
                            </div>
                            {{-- <div class="col-lg-3 col-md-3 mb-1">
                                <div>
                                    <h5 class="font-weight-bold">Alert</h5>
                                    <div id="filter-alert" class="btn-group special">
                                        <button class="btn btn-grey check_alert active" id="all_alert" value="">
                                            <span> All </span>
                                        </button>
                                        <button class="btn btn-grey check_alert" value="1">
                                            <span> Active </span>
                                        </button>
                                        <button class="btn btn-grey check_alert" value="2">
                                            <span> Inactive </span>
                                        </button>
                                    </div>
                                </div>
                            </div> --}}
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

            <div>
                <ul class="list-agt">
                    <li>
                        <div class="agt-card">
                            <span class="agt-score">7</span>
                            <span class="agt-txt-sm">Agent</span>
                        </div>
                    </li>
                    <li>
                        <div class="agt-card">
                            <span class="agt-score">7</span>
                            <span class="agt-txt-sm">Alert</span>
                        </div>
                    </li>
                    <li>
                        <div class="agt-card">
                            <span class="agt-score">5</span>
                            <span class="agt-txt-sm">Rule</span>
                        </div>
                    </li>
                    {{-- <li>
                        <div class="agt-card">
                            <span class="agt-score">4</span>
                            <span class="agt-txt-sm">Enrolled Users</span>
                        </div>
                    </li>
                    <li>
                        <div class="agt-card">
                            <span class="agt-score">2</span>
                            <span class="agt-txt-sm">Detected Blacklisted Apps</span>
                        </div>
                    </li> --}}
                </ul>
            </div>


            <div class="container-fluid nopadding">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <div class="agt-main-box">
                                    <div class="agt-header">
                                        Incident Type
                                    </div>
                                    <div class="agt-body">
                                        <div id="chart-incident-type" style="height: 250px;"></div>
                                    </div>
                                </div>
                                
                            </div>
                            <div class="col-lg-6 mb-3">
                                <div class="agt-main-box">
                                    <div class="agt-header">
                                        Platform Summary
                                    </div>
                                    <div class="agt-body">
                                        <div id="chart-platform-summary" style="height: 250px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-lg-6 mb-3">
                                <div class="agt-main-box">
                                    <div class="agt-header">
                                        Severity
                                    </div>
                                    <div class="agt-body">
                                        <div id="chart-serverity" style="height: 250px;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <div class="agt-main-box">
                                    <div class="agt-header">
                                        Top Rule
                                    </div>
                                    <div class="agt-body">
                                        <div id="chart-top-rule" style="height: 250px;"></div>
                                    </div>
                                </div>
                            </div>
                       
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="agt-main-box">
                            <div class="agt-header">
                                Audit Log Feed
                                <span class="refresh-audit-log pull-right cs-pointer"><i class="fas fa-sync-alt"></i></span>
                            </div>
                            <div class="agt-body">
                                <div id="audit-log" style="height: 559px;;overflow-y:auto;">
                                    <ul class="audit-log-list">
                                        <li>
                                            <div>
                                                <div class="wrapper-audit-log-img">
                                                    <img src="{{asset('asset_salepage/images/AgentBasedDetection.png')}}" alt="">
                                                </div>
                                            </div>
                                            <div>
                                                <span class="audit-log-header">
                                                    The host 192.168.3.2 is connected as admin with Adminstrator role
                                                </span>
                                                <div class="audit-log-time">
                                                    <span class="audit-by">
                                                        admin
                                                    </span>
                                                    <span class="audit-time">
                                                        1 hour ago
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div>
                                                <div class="wrapper-audit-log-img">
                                                    <img src="{{asset('asset_salepage/images/AgentBasedDetection.png')}}" alt="">
                                                </div>
                                            </div>
                                            <div>
                                                <span class="audit-log-header">
                                                    The host 192.168.3.2 is connected as admin with Adminstrator role
                                                </span>
                                                <div class="audit-log-time">
                                                    <span class="audit-by">
                                                        admin
                                                    </span>
                                                    <span class="audit-time">
                                                        1 hour ago
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div>
                                                <div class="wrapper-audit-log-img">
                                                    <img src="{{asset('asset_salepage/images/AgentBasedDetection.png')}}" alt="">
                                                </div>
                                            </div>
                                            <div>
                                                <span class="audit-log-header">
                                                    The host 192.168.3.2 is connected as admin with Adminstrator role
                                                </span>
                                                <div class="audit-log-time">
                                                    <span class="audit-by">
                                                        admin
                                                    </span>
                                                    <span class="audit-time">
                                                        1 hour ago
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div>
                                                <div class="wrapper-audit-log-img">
                                                    <img src="{{asset('asset_salepage/images/AgentBasedDetection.png')}}" alt="">
                                                </div>
                                            </div>
                                            <div>
                                                <span class="audit-log-header">
                                                    The host 192.168.3.2 is connected as admin with Adminstrator role
                                                </span>
                                                <div class="audit-log-time">
                                                    <span class="audit-by">
                                                        admin
                                                    </span>
                                                    <span class="audit-time">
                                                        1 hour ago
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div>
                                                <div class="wrapper-audit-log-img">
                                                    <img src="{{asset('asset_salepage/images/AgentBasedDetection.png')}}" alt="">
                                                </div>
                                            </div>
                                            <div>
                                                <span class="audit-log-header">
                                                    The host 192.168.3.2 is connected as admin with Adminstrator role
                                                </span>
                                                <div class="audit-log-time">
                                                    <span class="audit-by">
                                                        admin
                                                    </span>
                                                    <span class="audit-time">
                                                        1 hour ago
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div>
                                                <div class="wrapper-audit-log-img">
                                                    <img src="{{asset('asset_salepage/images/AgentBasedDetection.png')}}" alt="">
                                                </div>
                                            </div>
                                            <div>
                                                <span class="audit-log-header">
                                                    The host 192.168.3.2 is connected as admin with Adminstrator role
                                                </span>
                                                <div class="audit-log-time">
                                                    <span class="audit-by">
                                                        admin
                                                    </span>
                                                    <span class="audit-time">
                                                        1 hour ago
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <div class="container-fluid nopadding">
                <div class="row">
                    <div class="col-md-12">
                        <div class="tabbable">
                            <ul class="nav nav-tabs nav-tabs-highlight">
                                <li class="active"><a href="#tab_acvt" data-toggle="tab">Alert</a></li>
                                <li><a href="#tab_agent" data-toggle="tab">Agent</a></li>   
                                <li><a href="#tab_schedule" data-toggle="tab">Schedule Task</a></li>   
                            </ul>
                            <div class="tab-content">

                                <div class="tab-pane active" id="tab_acvt">
                                    <section class="panel panel-default">
                                        <header class="panel-heading font-bold panel-header-blue">
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <i class="fas fa-table"></i> Table Alert
                                                </div>
                                            </div>
                                        </header>
                                        <div class="panel-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped" id="table-activities-template" style="width: 100%">
                                                    <thead>
                                                        <tr>
                                                            <th>
                                                                <label><input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                                                                    <span class="label-text"></span>
                                                                </label>
                                                            </th>
                                                            <th>
                                                                Site Name            
                                                            </th>
                                                            <th>
                                                                Description
                                                            </th>
                                                            <th>Status</th>
                                                            <th>Datetime</th>
                                                            <th class="text-center">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <label><input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                                                                    <span class="label-text"></span>
                                                                </label>
                                                            </td>
                                                            <td>Site Demo</td>
                                                            <td>Found 1 matches in "/var/www/webshell/pl/remot shell.pl"   </td>
                                                            <td>Scan</td>
                                                            <td>2021-04-22 08:34:12 </td>
                                                            <td class="text-center">
                                                                <a href="#" class="btn btn-info btn-xs">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </section>
                                </div>

                                <div class="tab-pane" id="tab_agent">
                                    <section class="panel panel-default">
                                        <header class="panel-heading font-bold panel-header-blue">
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <i class="fas fa-table"></i> Table Agent
                                                </div>
                                            </div>
                                        </header>
                                        <div class="panel-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped" id="table-agent-template" style="width: 100%">
                                                    <thead>
                                                        <tr>
                                                            <th>
                                                                <label><input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                                                                    <span class="label-text"></span>
                                                                </label>
                                                            </th>
                                                            <th>Site Name</th>
                                                            <th>Device Name</th>
                                                            <th>OS Type</th>
                                                            <th>OS Description</th>
                                                            <th>System Info</th>
                                                            <th>Domain</th>
                                                            <th>IP</th>
                                                            <th>Last Online</th>
                                                            <th class="text-center">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <label><input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                                                                    <span class="label-text"></span>
                                                                </label>
                                                            </td>
                                                            <td>
                                                                <b>Sosecure</b>
                                                            </td>
                                                            <td>
                                                                <b>DESKTOP-KJAK36N</b>
                                                            </td>
                                                            <td>
                                                                <div class="device-name-txt">
                                                                    <div class="icon-devices">
                                                                        <i class="fab fa-apple"></i>
                                                                    </div>
                                                                    <b class="text-info">Linux</b>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="text-trucate-ovf">
                                                                    OS Name: Microsoft Windows 10 Pro 
                                                                    OS Version:  10.0.19042 N/A Build 19042 
                                                                    OS Manufacturer:  Microsoft Corporation 
                                                                    OS Configuration: Standalone Workstation 
                                                                    OS Build Type:  Multiprocessor Free 
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="text-trucate-ovf">
                                                                    System Boot Time: 8/2/2021, 2:43:47 AM 
                                                                    System Manufacturer: LENOVO
                                                                    System Model:  20N8S0KB00
                                                                    System Type:   x64-based PC
                                                                    Processor(s):  1 Processor(s) Installed.
                                                                    [01]: Intel64 Family 6 Model 142 Stepping 12 GenuineIntel
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <b>WORKGROUP</b>
                                                            </td>
                                                            <td>
                                                                <b>192.168.1.2  </b>
                                                            </td>
                                                            <td>
                                                                <b>04-08-2021 09:31:00 AM</b>
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="btn-group">
                                                                    <button class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown">
                                                                        <i class="fas fa-ellipsis-h"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu">
                                                                      <li><a href=""><i class="fas fa-power-off"></i> Restrat Service</a></li>
                                                                      <li><a href=""><i class="fas fa-search"></i> Quick Scan</a></li>
                                                                      <li><a href=""><i class="fas fa-stop-circle"></i> Stop Service</a></li>
                                                                      <li><a href=""><i class="fas fa-search"></i> Scan Yara</a></li>
                                                                      <li><a href=""><i class="fas fa-eye"></i> View Log Data</a></li>
                                                                      <li><a href=""><i class="fas fa-eye"></i> View Log Error</a></li>
                                                                    </ul>
                                                                  </div>
                                                            </td>
                                                        </tr>

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </section>
                                </div>

                                <div class="tab-pane" id="tab_schedule">
                                    <section class="panel panel-default">
                                        <header class="panel-heading font-bold panel-header-blue">
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <i class="fas fa-table"></i> Table Schedule Task
                                                </div>
                                            </div>
                                        </header>
                                        <div class="panel-body">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped" id="table-schedule-template" style="width: 100%">
                                                    <thead>
                                                        <tr>
                                                            <th>
                                                                <label><input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                                                                    <span class="label-text"></span>
                                                                </label>
                                                            </th>
                                                            <th>Name</th>
                                                            <th>Start Date</th>
                                                            <th>End Date</th>
                                                            <th>User Name</th>
                                                            <th>Status</th>
                                                            <th>Source</th>
                                                            <th class="text-center">Duration</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <label><input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                                                                    <span class="label-text"></span>
                                                                </label>
                                                            </td>
                                                            <td>
                                                                <b>Wake Up Agents</b>
                                                            </td>
                                                            <td>
                                                                <b>9/6/18 10:16:03 AM NZST</b>
                                                            </td>
                                                            <td>
                                                                <b>--</b>
                                                            </td>
                                                            <td>
                                                                <b>admin</b>
                                                            </td>
                                                            <td>
                                                                <b>In Progress (97%)</b>
                                                            </td>
                                                            <td>
                                                                <b>Server Task</b>
                                                            </td>
                                                            <td class="text-center">
                                                                1 hour 45 minute
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <label><input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                                                                    <span class="label-text"></span>
                                                                </label>
                                                            </td>
                                                            <td>
                                                                <b>Wake Up Agents</b>
                                                            </td>
                                                            <td>
                                                                <b>9/6/18 10:16:03 AM NZST</b>
                                                            </td>
                                                            <td>
                                                                <b>--</b>
                                                            </td>
                                                            <td>
                                                                <b>admin</b>
                                                            </td>
                                                            <td>
                                                                <b>In Progress</b>
                                                            </td>
                                                            <td>
                                                                <b>Run Client Task</b>
                                                            </td>
                                                            <td class="text-center">
                                                                1 hour 45 minute
                                                            </td>
                                                        </tr>

                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </section>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

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

@include('stacks.css.c3')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />

@include('stacks.css.multitext')
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
@include('stacks.js.c3')
@include('stacks.js.multitext')

<script>

active_btn('#groupby-btn .btn-grey');
active_btn('#groupby-status .btn-grey');
active_btn('#btngroup_sort_by .btn-grey');

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



$('#table-activities-template').DataTable();

$('#table-agent-template').DataTable({
    "fnDrawCallback": function( oSettings ) {
        multi_readmore()
    },
});
$('#table-schedule-template').DataTable();

function chart_c3(id,value,score_mid) {
   const myc3 = c3.generate({
        bindto: id,
        data: {
            columns: value,
            type : 'donut',
        },
        donut: {
            title: score_mid
        },
        legend: {
            position: 'bottom'
        },
        color: {
            pattern: ['#4398d4', '#40cd8f','#ffaa5b',]
        }
    });
}

function chart_bar(id){
    Highcharts.chart(id, {
        chart: {
            type: 'column',
                scrollablePlotArea: {
                minWidth: 400,
            },
        },
        title: {
            text: null
        },
        xAxis: {
            type: 'category',
            crosshair: true,
            labels: {
                overflow: 'justify',
                autoRotation: false,
                textAlign: 'center',
            }
    
        },
        yAxis: {
            min: 0,
            title: {
            text: 'Values'
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:.1f}</b></td></tr>',
            footerFormat: '</table>',
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            },
            series:{
                pointWidth: 30,
                color : '#ffc107',
                align: 'center',
            },
            style:{
                background: '#fff'
            }
        },
        legend: {
            enabled: false
        },
        series: [{
            name: 'Population',
            data: [
                [ 'Top Rule1',  100],
                [ 'Top Rule2',  30],
                [ 'Top Rule3',  40],
            ],
            dataLabels: {
                enabled: true,
                color: '#333',
                align: 'center',
                format: '{point.y{{--:.1f--}}}',
                y: 0, 
                style: {
                    fontSize: '13px',
                    fontFamily: 'Verdana, sans-serif',
                }
            }
        }]
    });
}

chart_c3('#chart-platform-summary', [['Linux', 30],['Window',10]],7);
chart_c3('#chart-incident-type', [['Corporate', 42.9],['Personal', 57.1]],7);
chart_c3('#chart-serverity', [['White Listed', 273],['Blacklisted', 50]],275);

chart_bar('chart-top-rule');


</script>

@endpush
@endsection