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
                    <div class="text-left max-w-select" style="display:inline-block">
                        <select name="site" id="site" class="text-left select2-option form-control select-site">
                            <option value="">All Site</option>
                            @if ($site_settings)

                            @foreach ($site_settings as $site_settings)
                            <option value="{{$site_settings->id}}">{{$site_settings->name}}
                            </option>
                            @endforeach

                            @endif
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
                            <span class="agt-score">1</span>
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
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <div class="agt-main-box">
                                    <div class="agt-header">
                                        Incident Type
                                    </div>
                                    <div class="agt-body">
                                        <div id="chart-incident-type" style="height: 250px;"></div>
                                    </div>
                                </div>
                                
                            </div>
                            <div class="col-lg-4 mb-3">
                                <div class="agt-main-box">
                                    <div class="agt-header">
                                        Platform Summary
                                    </div>
                                    <div class="agt-body">
                                        <div id="chart-platform-summary" style="height: 250px;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="agt-main-box">
                                    <div class="agt-header">
                                        Severity
                                    </div>
                                    <div class="agt-body">
                                        <div id="chart-serverity" style="height: 250px;"></div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-lg-8 mb-3">
                                <div class="agt-main-box">
                                    <div class="agt-header">
                                        Top Rule
                                    </div>
                                    <div class="agt-body">
                                        <div id="chart-top-rule" style="height: 300px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="agt-main-box">
                                    <div class="agt-header">
                                        Log Feed
                                        <span class="refresh-audit-log pull-right cs-pointer"><i class="fas fa-sync-alt"></i></span>
                                    </div>
                                    <div class="agt-body">
                                        <div id="audit-log" style="height: 300px;;overflow-y:auto;">
                                            <ul class="audit-log-list">
                                                <li>
                                                    {{-- <div>
                                                        <div class="wrapper-audit-log-img">
                                                            <img src="{{asset('asset_salepage/images/AgentBasedDetection.png')}}" alt="">
                                                        </div>
                                                    </div> --}}
                                                    <div class="w-100per">
                                                        <div class="audit-log-time">
                                                            <span class="audit-by">
                                                                Site : Sosecure
                                                            </span>
                                                            <span class="audit-time">
                                                                IP : 127.0.0.3
                                                            </span>
                                                        </div>
                                                        <span class="audit-log-header">
                                                            2021-08-05 08:58:50 | Start Scan Yara
                                                        </span>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="w-100per">
                                                        <div class="audit-log-time">
                                                            <span class="audit-by">
                                                                Site : Sosecure
                                                            </span>
                                                            <span class="audit-time">
                                                                IP : 127.0.0.3
                                                            </span>
                                                        </div>
                                                        <span class="audit-log-header">
                                                            2021-08-05 08:59:10|/crypto/crypto_signatures.yar(71): warning: $c0 in rule Big_Numbers5 is slowing down scanning
                                                        </span>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="w-100per">
                                                        <div class="audit-log-time">
                                                            <span class="audit-by">
                                                                Site : Sosecure
                                                            </span>
                                                            <span class="audit-time">
                                                                IP : 127.0.0.3
                                                            </span>
                                                        </div>
                                                        <span class="audit-log-header">
                                                            2021-08-05 09:28:20|Stop Scan Yara
                                                        </span>
                                                    </div>
                                                </li>
                                            </ul>
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
                                    <ul class="audit-log-list" id="audit_log_list">
                                        {{-- <li>
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
                                        </li> --}}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                 
                </div>


                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="agt-main-box">
                            <div class="agt-header">
                                Timeline
                            </div>
                            <div class="agt-body">
                                <div id="chart-time-line" style="height: 250px;"></div>
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
                                                                Rule
                                                            </th>
                                                            <th>
                                                                Description
                                                            </th>
                                                            <th>Incident</th>
                                                            <th>Datetime</th>
                                                            <th class="text-center">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {{-- <tr>
                                                            <td>
                                                                <label><input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                                                                    <span class="label-text"></span>
                                                                </label>
                                                            </td>
                                                            <td>Sosecure</td>
                                                            <td>webshell_mysqlwebsh</td>
                                                            <td>D:/yara/webshell/web-malware-collection-13-06-2012/PHP/mysqlwebsh.php</td>
                                                            <td>Agent</td>
                                                            <td>2021-05-08 09:34:12 </td>
                                                            <td class="text-center">
                                                                <a href="{{route('agentmanagement.view_txt')}}" data-toggle='ajaxModal'  class="btn btn-info btn-xs">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <label><input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                                                                    <span class="label-text"></span>
                                                                </label>
                                                            </td>
                                                            <td>Sosecure</td>
                                                            <td>webshell_jsp_up</td>
                                                            <td>D:/yara/webshell/web-malware-collection-13-06-2012/JSP/up_win32.jsp</td>
                                                            <td>Agent</td>
                                                            <td>2021-05-08 09:45:47 </td>
                                                            <td class="text-center">
                                                                <a href="{{route('agentmanagement.view_txt')}}" data-toggle='ajaxModal'  class="btn btn-info btn-xs">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <label><input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                                                                    <span class="label-text"></span>
                                                                </label>
                                                            </td>
                                                            <td>Sosecure</td>
                                                            <td>NT_Addy_asp</td>
                                                            <td>D:/yara/webshell/web-malware-collection-13-06-2012/ASP/ELMALISEKER Backd00r.asp</td>
                                                            <td>Agent</td>
                                                            <td>2021-05-08 09:58:01 </td>
                                                            <td class="text-center">
                                                                <a href="{{route('agentmanagement.view_txt')}}" data-toggle='ajaxModal'  class="btn btn-info btn-xs">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <label><input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                                                                    <span class="label-text"></span>
                                                                </label>
                                                            </td>
                                                            <td>Sosecure</td>
                                                            <td>MySQL_Web_Interface_Version_0_8_php</td>
                                                            <td>D:/yara/webshell/web-malware-collection-13-06-2012/PHP/MySQL Web Interface Version 0.8.txt</td>
                                                            <td>Agent</td>
                                                            <td>2021-05-08 09:50:16 </td>
                                                            <td class="text-center">
                                                                <a href="{{route('agentmanagement.view_txt')}}" data-toggle='ajaxModal'  class="btn btn-info btn-xs">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <label><input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                                                                    <span class="label-text"></span>
                                                                </label>
                                                            </td>
                                                            <td>Sosecure</td>
                                                            <td>BASE64_table</td>
                                                            <td>D:/yara/webshell/pl/pps-pl/pps-v4.0.pl</td>
                                                            <td>Agent</td>
                                                            <td>2021-05-08 10:20:12 </td>
                                                            <td class="text-center">
                                                                <a href="{{route('agentmanagement.view_txt')}}" data-toggle='ajaxModal'  class="btn btn-info btn-xs">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr> --}}
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
                                                        {{-- <tr>
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
                                                        </tr> --}}
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
                                                        {{-- <tr>
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
                                                        </tr> --}}
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

    var site = null;
    $(document).ready(function(){
        datatable_alert();
        datatable_agent();
        datatable_schedule();
        dudit_log_feed();
        count_head();
    });

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

    function count_head(site_val)
    {
        console.log('count - '+site_val);
        let site_log_id = site_val;
        $.ajax({
            url: "{{route('agentmanagement.count_head')}}",
            type: "POST",
            data:{
                site_log_id:site_log_id
            },
            success:function(response){
                let html = ``;
            }
        });
    }

    function dudit_log_feed(site_val)
    {
        console.log('log - '+site_val);
        let site_log_id = site_val;
        $.ajax({
            url: "{{route('agentmanagement.dudit_log_feed')}}",
            type: "POST",
            data:{
                site_log_id:site_log_id
            },
            success:function(response){
                $('#audit_log_list').empty();
                let html = ``;
                for(let rows in response.query)
                {
                    const data_log = response.query[rows];
                    {{-- <img src="{{asset('asset_salepage/images/AgentBasedDetection.png')}}" alt=""> --}}
                    {{-- alert(data_log.agent_alerts_id); ${data_log.site_logo} --}}
                    html += `
                            <li>
                                <div>
                                    <div class="wrapper-audit-log-img">
                                        <img src="{{asset('asset_salepage/images/AgentBasedDetection.png')}}" alt="">
                                    </div>
                                </div>
                                <div>
                                    <span class="audit-log-header">
                                        ${data_log.site_name} : ${data_log.site_ip_key}<br>
                                        ${data_log.description}
                                    </span>
                                    <div class="audit-log-time">
                                        <span class="audit-by">
                                            ${data_log.site_name}
                                        </span>
                                        <span class="audit-time">
                                            ${data_log.agent_alerts_created}
                                        </span>
                                    </div>
                                </div>
                            </li>
                        `;
                }

                $('#audit_log_list').append(html);
            }
        });
    }

    function datatable_alert(site_val)
    {
        console.log(site_val);
        var site_id = site_val;
        $('#table-activities-template').DataTable({
            cache: false,
            processData: false,
            contentType: false,
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: 
            {
                url: "{{route('agentmanagement.tb_alert')}}",
                type: "POST",
                data:function(d){
                    d.site_id = site_id;
                    return d ;
                }
            },
            columns: [
                {
                    data: 'chk',
                },
                {
                    data: 'site_name',
                },
                {
                    data: 'agent_alerts_description',
                },
                {
                    data: 'chk_status',
                },
                {
                    data: 'agent_alerts_created',
                },
                {
                    data: 'action',
                },
            ],
        });
    }

    function datatable_agent(site_val)
    {
        console.log(site_val);
        $('#table-agent-template').DataTable({
            "fnDrawCallback": function( oSettings ) {
                multi_readmore()
            },
        });
    }
    
    function datatable_schedule(site_val)
    {
        console.log(site_val);
        $('#table-schedule-template').DataTable();
    }
    

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
                pattern: ['#4398d4', '#40cd8f','#f4d757','#fcc838','#b93624']
            }
        });
    }

    function chart_bar(id){
        Highcharts.chart(id, {
            chart: {
                type: 'bar',
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
                },
                labels: {
                    overflow: 'justify',
                    autoRotation: false,
                    textAlign: 'center',
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
                    [ 'webshell_MySQL_Web_Interface_Version_0_8',  90],
                    [ 'MySQL_Web_Interface_Version_0_8_php',  80],
                    [ 'webshell_mysqlwebsh',  50],
                    [ 'NT_Addy_asp',  40],
                    [ 'BASE64_table',  40],
                    [ 'webshell_jsp_up',  30],
                ],
                dataLabels: {
                    enabled: false,
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

    function timeline_chart(id){
        Highcharts.chart(id, {
            title: {
                text: ''
            },
            yAxis: {
                
                title: {
                text: ''
                },
                plotLines: [{
                    color: '#FF0000',
                }]
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
                enabled: false,
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
                data: [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,7],
                type: 'area',
                fillColor: '#c8dcf17d',
                }
            ]
        });
    }

    chart_c3('#chart-platform-summary', [['Linux', 0],['Window',100]],1);
    chart_c3('#chart-incident-type', [['Yara', 100],['Indicator', 0]],1);
    chart_c3(
        '#chart-serverity',
         [
             ['Information', 0],
             ['Low', 0],
             ['Mediumn', 7],
             ['High', 0],
             ['Critical', 0]
             ],7
    );

    chart_bar('chart-top-rule');

    timeline_chart('chart-time-line');

    $('#site').change(function(){
        var site_val = $('#site').val();
        console.log(site_val);
        count_head(site_val);
        dudit_log_feed(site_val);
        datatable_alert(site_val);
        datatable_agent(site_val);
        datatable_schedule(site_val);
    });

</script>

@endpush
@endsection