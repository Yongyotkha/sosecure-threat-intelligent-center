@extends('layouts.app')
@section('content')

<section id="content" class="bg">
    <section class="vbox">
        <header class="header bg-white b-b b-light" style="display: flex;justify-content:space-between;">
            <div class="bc-head">Agent Management</div>
            <div class="max-w-select" style="margin-top: 8px;">
                <select name="site" id="site" class="select2-option form-control select-site"
                    onchange="changeSite(value)">
                    <option value="0" selected>All Site</option>
                    {{-- @if ($site_settings)

                    @foreach ($site_settings as $site_settings)
                    <option value="{{$site_settings->code}}">{{$site_settings->name}}
                    </option>
                    @endforeach

                    @endif --}}
                </select>
            </div>
        </header>

        {{-- Tab Content --}}
        <section class="scrollable wrapper">
            <div>
                <ul class="list-agt">
                    <li>
                        <div class="agt-card">
                            <span class="agt-score">7</span>
                            <span class="agt-txt-sm">Enrolled Devices</span>
                        </div>
                    </li>
                    <li>
                        <div class="agt-card">
                            <span class="agt-score">7</span>
                            <span class="agt-txt-sm">Inactive Devices</span>
                        </div>
                    </li>
                    <li>
                        <div class="agt-card">
                            <span class="agt-score">5</span>
                            <span class="agt-txt-sm">Enrollment Pending</span>
                        </div>
                    </li>
                    <li>
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
                    </li>
                </ul>
            </div>


            <div class="container-fluid nopadding">
                <div class="row">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <div class="agt-main-box">
                                    <div class="agt-header">
                                        Device Type
                                    </div>
                                    <div class="agt-body">
                                        <div id="chart-device-type" style="height: 250px;"></div>
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
                                        Severity
                                    </div>
                                    <div class="agt-body">
                                        <div id="chart-serverity" style="height: 250px;"></div>
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
                                <li class="active"><a href="#tab_acvt" data-toggle="tab">Activities</a></li>
                                <li><a href="#tab_agent" data-toggle="tab">Agent</a></li>   
                                <li><a href="#tab_schedule" data-toggle="tab">Schedule Task</a></li>   
                            </ul>
                            <div class="tab-content">

                                <div class="tab-pane active" id="tab_acvt">
                                    <section class="panel panel-default">
                                        <header class="panel-heading font-bold panel-header-blue">
                                            <div class="row">
                                                <div class="col-xs-12">
                                                    <i class="fas fa-table"></i> Table Activities
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
                                                            <th>Device Name</th>
                                                            <th>Owned By</th>
                                                            <th>Entollment Type</th>
                                                            <th>User Name</th>
                                                            <th>Email</th>
                                                            <th>Domain</th>
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
                                                                <div class="device-name-txt">
                                                                    <div class="icon-devices">
                                                                        <i class="fab fa-apple"></i>
                                                                    </div>
                                                                    <b class="text-info">Ipad</b>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <b>Corporate</b>
                                                            </td>
                                                            <td>
                                                                <b>invite</b>
                                                            </td>
                                                            <td>
                                                                <b>admin</b>
                                                            </td>
                                                            <td>
                                                                <b>admin@zylker.com</b>
                                                            </td>
                                                            <td>
                                                                <b>MDM</b>
                                                            </td>
                                                            <td class="text-center">
                                                                <a href="#" class="btn btn-info btn-xs">
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
                                                            <td>
                                                                <div class="device-name-txt">
                                                                    <div class="icon-devices">
                                                                        <i class="fab fa-windows"></i>
                                                                    </div>
                                                                    <b class="text-info">Laptop</b>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <b>Corporate</b>
                                                            </td>
                                                            <td>
                                                                <b>invite</b>
                                                            </td>
                                                            <td>
                                                                <b>admin</b>
                                                            </td>
                                                            <td>
                                                                <b>admin@zylker.com</b>
                                                            </td>
                                                            <td>
                                                                <b>MDM</b>
                                                            </td>
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
{{-- @include('stacks.css.highchart') --}}
@include('stacks.css.c3')
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
@include('stacks.js.c3')

<script>
    
$('#table-activities-template').DataTable();
$('#table-agent-template').DataTable();
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

chart_c3('#chart-device-type', [['Desktop', 30]],7);
chart_c3('#chart-platform-summary', [['iOS', 30],['Linux', 30],['Window',10]],7);
chart_c3('#chart-incident-type', [['Corporate', 42.9],['Personal', 57.1]],7);
chart_c3('#chart-serverity', [['White Listed', 273],['Blacklisted', 50]],275);




</script>

@endpush
@endsection