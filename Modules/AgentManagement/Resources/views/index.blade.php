@extends('layouts.app')

<style>
    .dropdown-menu {
        right: 0 !important;
        left: unset !important;
    }
    .tooltip-new{
        position: absolute;
        opacity: 0;
        padding: 10px;
        background: #313131;
        border-radius: 5px;
        color: #fff;
    -webkit-transition:all 0.2s ease-in;
      -moz-transition:all 0.2s ease-in;
            transition:all 0.2s ease-in;
     }

    .wrapper-new:hover > .tooltip-new{
        opacity: 0.9;
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
                        <select name="site" id="site" class="text-left select2-option form-control select-site" >
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

            <div>
                <ul class="list-agt">
                    <li>
                        <div class="agt-card">
                            <span class="agt-score" id="text_agent">0</span>
                            <span class="agt-txt-sm">Agent</span>
                        </div>
                    </li>
                    <li>
                        <div class="agt-card">
                            <span class="agt-score" id="text_alert">0</span>
                            <span class="agt-txt-sm">Alert</span>
                        </div>
                    </li>
                    <li>
                        <div class="agt-card">
                            <span class="agt-score" id="text_rule">0</span>
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

                                        <div class="loadder-incident  backdrop-loader" style="background: #fff">
                                            <div class="loader4 centerloader"></div>
                                            <div class="loadding-text">Loading ...</div>
                                        </div>

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

                                        <div class="loadder-platform  backdrop-loader" style="background: #fff">
                                            <div class="loader4 centerloader"></div>
                                            <div class="loadding-text">Loading ...</div>
                                        </div>

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
                                        <div class="loadder-severity  backdrop-loader" style="background: #fff">
                                            <div class="loader4 centerloader"></div>
                                            <div class="loadding-text">Loading ...</div>
                                        </div>
                                        <div id="chart-severity" style="height: 250px;"></div>
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

                                        <div class="loadder-rule  backdrop-loader" style="background: #fff">
                                            <div class="loader4 centerloader"></div>
                                            <div class="loadding-text">Loading ...</div>
                                        </div>

                                        <div id="chart-top-rule" style="height: 300px;"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="agt-main-box">
                                    <div class="agt-header">
                                        Log
                                        <span class="refresh-audit-log pull-right cs-pointer" id="refresh_log"><i class="fas fa-sync-alt"></i></span>
                                    </div>
                                    <div class="agt-body">
                                        <div class="loadder-log  backdrop-loader" style="background: #fff">
                                            <div class="loader4 centerloader"></div>
                                            <div class="loadding-text">Loading ...</div>
                                        </div>
                                        <div id="audit-log" style="height: 300px;;overflow-y:auto;">
                                            <ul class="audit-log-list" id="audit_log_list">
                                                {{-- <li>
                                                    <div>
                                                        <div class="wrapper-audit-log-img">
                                                            <img src="{{asset('asset_salepage/images/AgentBasedDetection.png')}}" alt="">
                                                        </div>
                                                    </div>
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
                                                </li> --}}
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    {{-- <div class="col-md-4">
                        <div class="agt-main-box">
                            <div class="agt-header">
                                Audit Log Feed
                                <span class="refresh-audit-log pull-right cs-pointer"><i class="fas fa-sync-alt"></i></span>
                            </div>
                            <div class="agt-body">
                                <div id="audit-log" style="height: 559px;;overflow-y:auto;">
                                    <ul class="audit-log-list" id="audit_log_list">
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
                    </div> --}}
                </div>


                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="agt-main-box">
                            <div class="agt-header">
                                <div class="row">
                                    <div class="col-md-7">
                                        {{-- <div class="agt-main-box"> --}}
                                            <div class="agt-header"> 
                                                Timeline
                                            </div>
                                        {{-- </div> --}}
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <div class="form-group m-b-md" style="margin-bottom: 0px;">
                                            <div id="filter_date_timeline" class="text-center form-control" style="background: #fff; cursor: pointer; padding: 5px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                                <i class="fa fa-calendar"></i>
                                                &nbsp;<span></span> 
                                                <i class="fa fa-caret-down"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-1 text-left">
                                        <button type="button" class="btn btn-sm btn-info" onclick="search_timeline()"><i class="fas fa-search"></i> Apply </button>
                                    </div>
                                </div>
                            </div>
                            <div class="agt-body">
                                <div class="loadder-timeline  backdrop-loader" style="background: #fff">
                                    <div class="loader4 centerloader"></div>
                                    <div class="loadding-text">Loading ...</div>
                                </div>
                                <div id="chart-time-line" style="height: 250px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <section class="scrollable wrapper">
                <section class="panel panel-default" id="hide-advance-search" style="display: none;">
                    <header class="panel-heading font-bold panel-header-blue">
                        <div class="row">
                            <div class="col-md-12">
                                <i class="fas fa-filter"></i> Filter
                            </div>
                        </div>
                    </header>
                    <div class="panel-body" style="padding: 0 !important">
                        <div class="container-fluid" style="padding: 2rem;">
    
                            <div class="row">
                                <div class="col-lg-2 col-md-2">
                                    {{-- <h5>Filter By</h5> --}}
                                    <label for="" class="">Filter By</label>
                                    <div id="btngroup_sort_by" class="btn-group special">
                                        <button class="btn btn-grey filter_agent_all check_type active" value="" id="btn_search_all">
                                            <span> All </span>
                                        </button>
                                        <button class="btn filter_agent_alert check_type btn-grey" value="alert">
                                            <span> Alert </span>
                                        </button>
                                        <button class="btn filter_agent_agent check_type btn-grey" value="agent">
                                            <span> Agent </span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-lg-5">
                                    <div>
                                        <div class="form-group m-b-md">
                                            <label for="" class="">Keyword</label>
                                            <input type="text" class="form-control" name="keyword_search" id="keyword_search" placeholder="Search">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group m-b-md">
                                        <label for="" class="">Date</label>
                                        <div id="filter_date" class="text-center form-control"style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                            <i class="fa fa-calendar"></i>
                                            &nbsp;<span></span> 
                                            <i class="fa fa-caret-down"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
    
                            {{-- <div class="row">
                                <div class="col-lg-3 col-md-3 mb-1">
                                    <h5 class="font-weight-bold">Filter By</h5>
                                    <div id="btngroup_sort_by" class="btn-group special mb-2">
                                        <button class="btn btn-grey filter_agent_all check_type active" value="" id="btn_search_all">
                                            <span> All </span>
                                        </button>
                                        <button class="btn filter_agent_alert check_type btn-grey" value="alert">
                                            <span> Alert </span>
                                        </button>
                                        <button class="btn filter_agent_agent check_type btn-grey" value="agent">
                                            <span> Agent </span>
                                        </button>
                                    </div>
                                </div>
                            </div> --}}
    
                            <div id="filter_alert_main" class="row" style="display: none">
                                <div class="col-lg-3">
                                    <div>
                                        <div class="form-group m-b-md">
                                            <label for="" class="">Rule</label>
                                            <input type="text" class="form-control" name="filter_alert_rule" id="filter_alert_rule"
                                                placeholder="Search">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div>
                                        <div class="form-group m-b-md">
                                            <label for="" class="">Description</label>
                                            <input type="text" class="form-control" name="filter_alert_des" id="filter_alert_des"
                                                placeholder="Search">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-2">
                                    <label for="" class="">Incident</label>
                                    <div id="filter-alert-incident" class="btn-group special">
                                        <button class="btn btn-grey check_alert active" id="incident-status-all" value="">
                                            <span> All </span>
                                        </button>
                                        <button class="btn btn-grey check_alert" value="Agent">
                                            <span> Agent </span>
                                        </button>
                                        <button class="btn btn-grey check_alert" value="Indicator">
                                            <span> Indicator </span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <label for="" class="">Severity</label>
                                    <div id="filter-alert-severity" class="btn-group special">
                                        <button class="btn btn-grey check_alert_severity active" id="severity-status-all" value="">
                                            <span> All </span>
                                        </button>
                                        <button class="btn btn-grey check_alert_severity" value="Critical">
                                            <span> Critical </span>
                                        </button>
                                        <button class="btn btn-grey check_alert_severity" value="High">
                                            <span> High </span>
                                        </button>
                                        <button class="btn btn-grey check_alert_severity" value="Medium">
                                            <span> Medium </span>
                                        </button>
                                        <button class="btn btn-grey check_alert_severity" value="Low">
                                            <span> Low </span>
                                        </button>
                                        <button class="btn btn-grey check_alert_severity" value="Information">
                                            <span> Information </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
    
                            <div id="filter_agent_main" class="row"  style="display: none">
                                {{-- <div class="col-lg-3">
                                    <div>
                                        <div class="form-group m-b-md">
                                            <label for="" class="">Site Name</label>
                                            <input type="text" class="form-control" name="filter_agent_site_name" id="filter_agent_site_name"
                                                placeholder="Search">
                                        </div>
                                    </div>
                                </div> --}}
                                <div class="col-lg-3">
                                    <div>
                                        <div class="form-group m-b-md">
                                            <label for="" class="">Device Name</label>
                                            <input type="text" class="form-control" name="filter_agent_device" id="filter_agent_device"
                                                placeholder="Search">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div>
                                        <div class="form-group m-b-md">
                                            <label for="" class="">IP</label>
                                            <input type="text" class="form-control" name="filter_agent_ip" id="filter_agent_ip"
                                                placeholder="Search">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div>
                                        <div class="form-group m-b-md">
                                            <label for="" class="">OS Description</label>
                                            <input type="text" class="form-control" name="filter_agent_os_des" id="filter_agent_os_des"
                                                placeholder="Search">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <label for="" class="">OS Type</label>
                                    <div id="filter-agent-os-type" class="btn-group special">
                                        <button class="btn btn-grey check_os_type active" id="all_os" value="">
                                            <span> All </span>
                                        </button>
                                        <button class="btn btn-grey check_os_type" value="1">
                                            <span> Window </span>
                                        </button>
                                        <button class="btn btn-grey check_os_type" value="2">
                                            <span> Linux </span>
                                        </button>
                                        <button class="btn btn-grey check_os_type" value="3">
                                            <span> Redhat </span>
                                        </button>
                                        <button class="btn btn-grey check_os_type" value="4">
                                            <span> Other </span>
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
                                    style="white-space: nowrap" onclick="clear_search()">
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
            </section>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="tabbable">
                            <ul class="nav nav-tabs nav-tabs-highlight">
                                <li class="active"><a href="#tab_acvt" data-toggle="tab" id="tab_acvt_click">Alert</a></li>
                                <li><a href="#tab_agent" data-toggle="tab" id="tab_agent_click">Agent</a></li>   
                                <li><a href="#tab_schedule" data-toggle="tab" id="tab_schedule_click">Schedule Task</a></li>   
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
                                            <div class="row m-b-10">
                                                <div class="col-md-12">
                                                    <h5 class="font-weight-bold">Severity</h5>
                                                    <div class="st-dt-leak">
                                                        <span class="st-dt vrh" data-toggle="tooltip" data-placement="right" data-html="true" title="" data-original-title="<div class='st-flex'><div class='box-st-tooltip vrh'>Critical</div><div class='text-st-tooltip'>Criticalข้อมูลรั่วไหลและเป็นที่รับรู้อย่างแพร่หลาย เช่น <br> ออกข่าว หรือมีการแชร์ข้อมูลจากแหล่งข้อมูลที่น่าเชื่อถือและเป็นที่แพร่หลาย <br> และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูล Username ,Password ของลูกค้าหรือเจ้าหน้าที่ดูแลระบบภายในองค์กรซึ่งเป็นข้อมูลที่สามารถนำมาใช้ได้จริง</div></div>">Critical</span>
                                                        <span class="st-dt high" data-toggle="tooltip" data-placement="right" data-html="true" title="" data-original-title="<div class='st-flex'><div class='box-st-tooltip high'>High</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้อย่างแพร่หลาย เช่น ออกข่าว หรือมีการแชร์ข้อมูลจากแหล่งข้อมูลที่น่าเชื่อถือและเป็นที่แพร่หลาย และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูลส่วนบุคคลซึ่งเป็นข้อมูลที่สามารถนำมาใช้ประโยชน์ต่อได้</div></div>">High</span>
                                                        <span class="st-dt md" data-toggle="tooltip" data-placement="right" data-html="true" title="" data-original-title="<div class='st-flex'><div class='box-st-tooltip md'>Medium</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้ภายในกลุ่มจำกัดหรือยังไม่เป็นที่รับรู้กันอย่างแพร่หลาย และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูล Username ,Password ของลูกค้าหรือเจ้าหน้าที่ดูแลระบบภายในองค์กรซึ่งเป็นข้อมูลที่สามารถนำมาใช้ได้จริง</div></div>">Medium</span>
                                                        <span class="st-dt low" data-toggle="tooltip" data-placement="right" data-html="true" title="" data-original-title="<div class='st-flex'><div class='box-st-tooltip low'>Low</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้ภายในกลุ่มจำกัดหรือยังไม่เป็นที่รับรู้กันอย่างแพร่หลาย และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของลูกค้าเช่น ข้อมูลส่วนบุคคลซึ่งเป็นข้อมูลที่สามารถนำมาใช้ประโยชน์ต่อได้</div></div>">Low</span>
                                                        <span class="st-dt vrl" data-toggle="tooltip" data-placement="right" data-html="true" title="" data-original-title="<div class='st-flex'><div class='box-st-tooltip vrl'>Informational</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลที่เป็นข้อมูลทั่วไปหรือเป็นข่าวที่ยังไม่ได้รับการยืนยันว่าเป็นข้อมูลรั่วไหลจริง</div></div>">Informational</span>
                                                    </div>
                                                </div>
                                            </div>
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
                                                            {{-- <th>
                                                                IP            
                                                            </th> --}}
                                                            <th>
                                                                Detail
                                                            </th>
                                                            {{-- <th>
                                                                Rule
                                                            </th>
                                                            <th>
                                                                Description
                                                            </th>
                                                            <th>Path</th> --}}
                                                            {{-- <th>Date Scan</th>
                                                            <th>Date Last Scan</th> --}}
                                                            <th>Severity</th>
                                                            <th>Datetime</th>
                                                            <th class="text-center">Ignore</th>
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
                                                            <th>Online Status</th>
                                                            <th>Status</th>
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
                                                                {{-- <label><input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                                                                    <span class="label-text"></span>
                                                                </label> --}}
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" name="selectAll" id="selectAll" value="all"/>
                                                                    <label class="custom-control-label font-weight-normal" for="selectAll"></label>
                                                                </div>
                                                            </th>
                                                            <th>Site</th>
                                                            <th>IP</th>
                                                            <th>Mode</th>
                                                            <th>Start Date</th>
                                                            <th>End Date</th>
                                                            <th>Description</th>
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
@include('stacks.js.fullscreen')

<script>
    var start_date_tl = null;
    var end_date_tl = null;

    var site_val = null;

    var keyword_search = null;
    var start_date = null;
    var end_date = null;
    var check_type = null;

    var check_alert = null;
    var check_alert_severity = null;
    var filter_alert_rule = null;
    var filter_alert_des = null;

    var filter_agent_site_name = null;
    var filter_agent_device = null;
    var filter_agent_ip = null;
    var check_os_type = null;
    var filter_agent_os_des = null;

    $(document).ready(function(){
        count_head();
        datachart_Incident();
        datachart_platform();
        datachart_severity();
        datachart_rule();
        dudit_log_feed();
        datachart_timeline();
        datatable_alert();
        datatable_agent();
        datatable_schedule();
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })


       

    });

    active_btn('#groupby-btn .btn-grey');
    active_btn('#groupby-status .btn-grey');
    active_btn('#btngroup_sort_by .btn-grey');
    active_btn('#filter-alert-incident .btn-grey');
    active_btn('#filter-alert-severity .btn-grey');
    active_btn('#filter-agent-os-type .btn-grey');

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

    var start = moment().startOf('hour').add(-30, 'days');
    var end = moment().startOf('hour');

    function cb(start, end) {
        $('#filter_date span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        startDate = start;
        endDate = end;
    }

    function cb_tl(start, end) {
        $('#filter_date_timeline span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        startDate = start;
        endDate = end;
    }

    $('#filter_date').daterangepicker({
        timePicker: true,
        startDate: start,
        endDate: end,
        locale: {
            format: 'M/DD hh:mm A'
        },
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb);

    $('#filter_date').on('apply.daterangepicker', function(ev, picker) {
        isDateSearch = 1;
        if (!picker.startDate.isValid() || !picker.endDate.isValid()) {

        }
    });

    $('#filter_date_timeline').daterangepicker({
        timePicker: true,
        startDate: start,
        endDate: end,
        locale: {
            format: 'M/DD hh:mm A'
        },
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    }, cb_tl);

    $('#filter_date_timeline').on('apply.daterangepicker', function(ev, picker) {
        isDateSearch = 1;
        if (!picker.startDate.isValid() || !picker.endDate.isValid()) {

        }
    });

    cb(start, end);
    cb_tl(start, end);

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

    $('.check_type').click(function(){
        check_type = $(this).val();
    });

    $('.check_alert').click(function(){
        check_alert = $(this).val();
    });

    $('.check_alert_severity').click(function(){
        check_alert_severity = $(this).val();
    });

    $('.check_os_type').click(function(){
        check_os_type = $(this).val();
    });

    function search()
    {
        site_val = $('#site').val();
        keyword_search = $('#keyword_search').val();
        start_date = $("#filter_date").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm:ss');
        end_date = $("#filter_date").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm:ss');

        if(check_type == 'alert')
        {
            filter_alert_rule = $('#filter_alert_rule').val();
            filter_alert_des = $('#filter_alert_des').val();

            datatable_alert(site_val);
            $('#tab_acvt_click').trigger("click");
        }
        else if(check_type == 'agent')
        {
            filter_agent_site_name = $('#filter_agent_site_name').val();
            filter_agent_device = $('#filter_agent_device').val();
            filter_agent_ip = $('#filter_agent_ip').val();
            filter_agent_os_des = $('#filter_agent_os_des').val();

            datatable_agent(site_val);
            $('#tab_agent_click').trigger("click");
        }
        else
        {
            check_type = null;
            check_alert = null;
            check_alert_severity = null;
            check_os_type = null;
            filter_alert_rule = null;
            filter_alert_des = null;
            filter_agent_site_name = null;
            filter_agent_device = null;
            filter_agent_ip = null;
            filter_agent_os_des = null;
            
            datatable_alert(site_val);
            datatable_agent(site_val);
            datatable_schedule(site_val);
        }
    }

    function search_timeline()
    {
        $('.loadder-timeline').show();
        site_val = $('#site').val();
        start_date_tl = $("#filter_date_timeline").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm:ss');
        end_date_tl = $("#filter_date_timeline").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm:ss');

        datachart_timeline(site_val);
    }

    function clear_search()
    {
        keyword_search = null;
        check_type = null;
        check_alert = null;
        check_alert_severity = null;
        check_os_type = null;
        filter_alert_rule = null;
        filter_alert_des = null;
        filter_agent_site_name = null;
        filter_agent_device = null;
        filter_agent_ip = null;
        filter_agent_os_des = null;

        $('.check_type').removeClass('active');
        $('.check_alert').removeClass('active');
        $('.check_alert_severity').removeClass('active');
        $('.check_os_type').removeClass('active');
        $('#btn_search_all').addClass('active');
        $('#incident-status-all').addClass('active');
        $('#severity-status-all').addClass('active');
        $('#all_os').addClass('active');

        $('#keyword_search').val('');
        $('#filter_alert_rule').val('');
        $('#filter_alert_des').val('');
        $('#filter_agent_site_name').val('');
        $('#filter_agent_device').val('');
        $('#filter_agent_ip').val('');
        $('#filter_agent_os_des').val('');

        $('#filter_alert_main').hide();
        $('#filter_agent_main').hide();

        $('#tab_acvt_click').trigger("click");

        datatable_alert(site_val);
        datatable_agent(site_val);
        datatable_schedule(site_val);
    }

    function count_head(site_val)
    {
        {{-- console.log('count - '+site_val); --}}
        let site_log_id = site_val;
        $.ajax({
            url: "{{route('agentmanagement.count_head')}}",
            type: "POST",
            data:{
                site_log_id:site_log_id
            },
            success:function(response){
                $('#text_agent').text(response.count_agent);
                $('#text_alert').text(response.count_alert);
                $('#text_rule').text(response.count_rule);
            }
        });
    }

    function dudit_log_feed(site_val)
    {
        {{-- console.log('log - '+site_val); --}}
        let site_log_id = site_val;

        $.ajax({
            url: "{{route('agentmanagement.dudit_log_feed')}}",
            type: "POST",
            data:{
                site_log_id:site_log_id
            },
            beforesend:function(){
                $('.loadder-log').show();
            },
            success:function(response){
                $('.loadder-log').hide();
                $('#audit_log_list').empty();
                if(response.query.length > 0)
                {
                    let html = ``;
                    for(let rows in response.query)
                    {
                        const data_log = response.query[rows];
                        {{-- html += `
                                <li>
                                    <div class="w-100per">
                                        <div class="audit-log-time">
                                            <span class="audit-by">
                                                Site : ${data_log.site_name}
                                            </span>
                                            <span class="audit-time">
                                                IP : ${data_log.agent_logs_ip_address}
                                            </span>
                                        </div>
                                        <span class="audit-log-header">
                                            ${data_log.agent_logs_created} | ${data_log.agent_logs_description}
                                        </span>
                                    </div>
                                </li>
                            `; --}}

                        html += `
                                <li>
                                    <div class="w-100per">
                                        <div class="audit-log-time">
                                            <span class="audit-by">
                                                ${data_log.site_name}
                                            </span>
                                            <span class="audit-time">
                                                IP : ${data_log.site_agents_ip_private}
                                            </span>
                                        </div>
                                        <span class="audit-log-header">
                                            ${data_log.mode} : ${data_log.created_at}
                                        </span>
                                    </div>
                                </li>
                            `;
                    }

                    $('#audit_log_list').append(html);
                }
                else
                {
                    let html = ``;
                        html += `
                                    <li>
                                        <div class="w-100per">
                                            <div class="audit-log-time">
                                                <span style="text-align:center;">
                                                    ไม่พบข้อมูล
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                `;
                    $('#audit_log_list').append(html);
                }
            }
        });
    }

    $('#refresh_log').click(function(){
        $('.loadder-log').show();
        site_val = $('#site').val();
        dudit_log_feed(site_val);
    });

    function datachart_Incident(site_val)
    {
        let site_id = site_val;
        {{-- console.log('inci - '+site_id); --}}
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{route('agentmanagement.data_chart_incident')}}",
            type: "POST",
            data: {
                {{-- keyword_search:keyword_search, --}}
                site_id:site_id
            },
            beforesend:function(){
                $('.loadder-incident').show();
            },
            success:function(data){
                $('.loadder-incident').hide();
                chart_c3('#chart-incident-type', data.chart, data.count_all);
            }
        });
    }

    function datachart_platform(site_val)
    {
        let site_id = site_val;
        {{-- console.log('plat - '+site_id); --}}
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{route('agentmanagement.data_chart_platform')}}",
            type: "POST",
            data: {
                {{-- keyword_search:keyword_search, --}}
                site_id:site_id
            },
            beforesend:function(){
                $('.loadder-platform').show();
            },
            success:function(data){
                $('.loadder-platform').hide();

                chart_c3('#chart-platform-summary', data.chart, data.count_all);
            }
        });
    }

    function datachart_severity(site_val)
    {
        let site_id = site_val;
        {{-- console.log('seve - '+site_id); --}}
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{route('agentmanagement.data_chart_severity')}}",
            type: "POST",
            {{-- data:function(d){
                d.keyword_search = keyword_search;
                d.site_id = site_id;
                return d ;
            },  --}}
            data: {
                {{-- keyword_search:keyword_search, --}}
                site_id:site_id
            },
            beforesend:function(){
                $('.loadder-severity').show();
            },
            success:function(data){
                $('.loadder-severity').hide();
                chart_c3('#chart-severity', data.chart, data.count_all);
            }
        });
    }

    function datachart_rule(site_val)
    {
        let site_id = site_val;
        {{-- console.log('rule - '+site_id); --}}
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{route('agentmanagement.data_chart_rule')}}",
            type: "POST",
            data: {
                keyword_search:keyword_search,
                site_id:site_id
            },
            beforesend:function(){
                $('.loadder-rule').show();
            },
            success:function(data){
                $('.loadder-rule').hide();
                chart_bar('chart-top-rule',data);
            }
        });
    }

    function datachart_timeline(site_val)
    {
        let site_id = site_val;
        console.log('rule - '+site_id);
        console.log('s - '+start_date_tl);
        console.log('e - '+end_date_tl);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{route('agentmanagement.data_chart_timeline')}}",
            type: "POST",
            data: {
                {{-- keyword_search:keyword_search, --}}
                site_id:site_id,
                start_date:start_date_tl,
                end_date:end_date_tl
            },
            beforesend:function(){
                $('.loadder-timeline').show();
            },
            success:function(data){
                $('.loadder-timeline').hide();
                timeline_chart('chart-time-line', data.day, data.count);
            }
        });
    }

    function datatable_alert(site_val)
    {
        var site_id = site_val;

        tbl_alert = $('#table-activities-template').DataTable({
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
                    d.keyword_search = keyword_search;
                    d.filter_alert_rule = filter_alert_rule;
                    d.filter_alert_des = filter_alert_des;
                    d.check_alert = check_alert;
                    d.check_alert_severity = check_alert_severity;
                    d.start_date = start_date;
                    d.end_date = end_date;
                    return d ;
                }
            },
            initComplete : function( settings, json){
                $('[data-toggle="tooltip"]').tooltip();
            },
            columns: [
                {
                    data: 'chk',
                    "orderable": false,
                },
                {
                    data: 'site_name',
                },
                {
                    data: 'detail_all',
                },
              
                {{-- 
                    {
                        data: 'site_agents_ip_private',
                    },
                    {
                        data: 'agent_alerts_rule',
                    },
                    {
                        data: 'agent_alerts_description',
                    },
                    {
                        data: 'device_name',
                    },
                    {
                        data: 'first_scan',
                    },
                    {
                        data: 'last_scan',
                    },

                --}}
               
                {
                    data: 'severity_status',
                },
                {
                    data: 'agent_alerts_created',
                },
                {
                    data: 'action',
                    "orderable": false,
                }
            ],
        });

        setInterval(() => {   
            tbl_alert.ajax.reload();  
        }, 60000);
    }

    function datatable_agent(site_val)
    {
        var site_id = site_val;

        tbl_agent = $('#table-agent-template').DataTable({
            cache: false,
            processData: false,
            contentType: false,
            processing: true,
            serverSide: true,
            destroy: true,
            "fnDrawCallback": function( oSettings ) {
                multi_readmore()
            },
            ajax: 
            {
                url: "{{route('agentmanagement.tb_agent')}}",
                type: "POST",
                data:function(d){
                    d.site_id = site_id;
                    d.keyword_search = keyword_search;
                    d.filter_agent_site_name = filter_agent_site_name;
                    d.filter_agent_device = filter_agent_device;
                    d.filter_agent_ip = filter_agent_ip;
                    d.check_os_type = check_os_type;
                    d.filter_agent_os_des = filter_agent_os_des;
                    d.start_date = start_date;
                    d.end_date = end_date;
                    return d ;
                }
            },
            columns: [
                {
                    data: 'chk',
                    "orderable": false,
                },
                {
                    data: 'site_name',
                },
                {
                    data: 'site_agents_device_name',
                },
                {
                    data: 'os_type_name',
                },
                {
                    data: 'site_agents_os_description',
                },
                {
                    data: 'site_agents_system_info',
                },
                {
                    data: 'site_agents_domain',
                },
                {
                    data: 'site_agents_ip_private',
                },
                {
                    data: 'site_agents_last_online',
                    className : 'nowrap',
                },
                {
                    targets: 9,
                    className : 'nowrap',
                        render: function (data, type, full, meta) {

                        let html = '';
                        let date1 = new Date(full.site_agents_last_online);

                        let date1_d = date1.getDay();
                        let date1_m = date1.getMonth();
                        let date1_y = date1.getFullYear();
                        let sumdate_1 = date1_d+''+date1_m+''+date1_y;

                        let date2 = new Date();
                        let date2_d = date2.getDay();
                        let date2_m = date2.getMonth();
                        let date2_y = date2.getFullYear();

                        let sumdate_2 = date2_d+''+date2_m+''+date2_y;

                        let onlineTime = date1.getMinutes();
                        let onlineTimeH = date1.getHours();

                    
                        let currentTime = date2.getMinutes();
                        let currentTimeH = date2.getHours();

                        if(sumdate_1 == sumdate_2){
                            if(onlineTimeH == currentTimeH){
                                if(onlineTime < currentTime){
                                    var sumTime = currentTime - onlineTime;
                                    if(sumTime > 2){
                                        html = '<div class="status-flex mr-2"><span class="dot critical "></span> Offline</div>';
                                    }else{
                                        html = '<div class="status-flex mr-2"><span class="dot low"></span> Online</div>'
                                    }
                                }else{
                                    var sumTime = onlineTime - currentTime;
                                    if(sumTime > 2){
                                        html = '<div class="status-flex mr-2"><span class="dot critical"></span> Offline</div>';
                                    }else{
                                        html = '<div class="status-flex mr-2"><span class="dot low"></span> Online</div>'
                                    }
                                }
                            }else{
                                html = '<div class="status-flex mr-2"><span class="dot critical"></span> Offline</div>';
                            }
                        }else{
                            html = '<div class="status-flex mr-2"><span class="dot critical "></span> Offline</div>';
                        }


                        return `${html} `;

                    },
                },
                {
                    data: 'chk_status',
                },
                {
                    data: 'action',
                    "orderable": false,
                }
            ]

        });
        
        setInterval(() => {   
            tbl_agent.ajax.reload();  
        }, 60000);

    }
    
    function datatable_schedule(site_val)
    {
        let site_id = site_val;

        $('#table-schedule-template').DataTable({
            cache: false,
            processData: false,
            contentType: false,
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: 
            {
                url: "{{route('agentmanagement.tb_schedule')}}",
                type: "POST",
                data:function(d){
                    d.site_id = site_id;
                    d.start_date = start_date;
                    d.end_date = end_date;
                    return d ;
                }
            },
            columns: [
                {
                    data: 'chk',
                    "orderable": false,
                },
                {
                    data: 'site_name',
                },
                {
                    data: 'site_ip_key',
                },
                {
                    data: 'mode',
                },
                {
                    data: 'first_scan',
                },
                {
                    data: 'last_scan',
                },
                {
                    data: 'description',
                }
            ]
        });
    }

    function chart_c3(id,value,score_mid) {
        const myc3 = c3.generate({
            bindto: id,
            data: {
                columns: value,
                type : 'donut',
            },
            donut: {
                title: score_mid,
                label: {
                format: function(value, ratio, id) {
                    return value;
                    }
                }
            },
            legend: {
                position: 'bottom'
            },

            color: {
                pattern: ['#4398d4', '#40cd8f','#f4d757','#fcc838','#b93624']
            }
        });
    }

    function chart_bar(id,value){
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
                data: value,
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

    function timeline_chart(id,day,data)
    {
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
                categories: day,
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
                data: data,
                type: 'area',
                fillColor: '#c8dcf17d',
                }
            ]
        });
    }

    {{-- 
    chart_c3('#chart-platform-summary', [['Linux', 0],['Window',100]],1); 
    chart_c3('#chart-incident-type', [['Yara', 100],['Indicator', 0]],1);
    chart_c3(
        '#chart-severity',
         [
             ['Information', 0],
             ['Low', 0],
             ['Medium', 7],
             ['High', 0],
             ['Critical', 0]
        ]
        ,7
    );
    timeline_chart('chart-time-line');
    --}}

    $('#site').change(function(){
        $('.loadder-incident').show();
        $('.loadder-platform').show();
        $('.loadder-severity').show();
        $('.loadder-rule').show();
        $('.loadder-log').show();
        $('.loadder-timeline').show();
        site_val = $('#site').val();
        console.log(site_val);
        count_head(site_val);
        datachart_Incident(site_val);
        datachart_platform(site_val);
        datachart_severity(site_val);
        datachart_rule(site_val);
        dudit_log_feed(site_val);
        datachart_timeline(site_val);
        datatable_alert(site_val);
        datatable_agent(site_val);
        datatable_schedule(site_val);
    });

    function change_status_agent(id) 
    {
        let checkState = $("#agent-status-" + id).is(":checked") ? 1 : 0;
        axios.post('{{route('agentmanagement.update_status_agent')}}', {
            status: checkState,
            id: id,
        }).then(function (response) {
            toastr.success('Update Status Success!!');
        }).catch(function (error) {
            toastr.error('error!!');
        });
    }


    function copyToClipboard(element) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).val()).select();
        setTimeout(function(){ document.execCommand("copy"); }, 1000);
        document.execCommand("copy");
        $temp.remove();
        toastr.success('Copy Success', 'Copy Path');
    }

</script>

@endpush
@endsection