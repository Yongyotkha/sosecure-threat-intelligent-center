@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">
            <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
                <div class="header-flex-overflow m-t-10">
                    <div class="fwb-16">
                        <span>
                            {{ (request()->is('asset')) ? 'Assets' : '' }}
                            {{(request()->is('asset/all')) ? 'Settings > Assets' : ''}}
                        </span>
                    </div>
    
                    <div class="ml-2 text-right">
                        <div class="text-left pull-left max-w-select {{ (is_countable($SiteSettings) || is_array($SiteSettings)) && count($SiteSettings) == 1 ? 'd-none' : '' }}">
                            <select name="" id="select-site" class="select2-option select-site" onchange="changeSite(value)">

                                @if((is_countable($SiteSettings) || is_array($SiteSettings)) && count($SiteSettings) == 1)
                                    @foreach ($SiteSettings as $SiteSettings_val)
                                        <option value="{{$SiteSettings_val->code}}" selected data-site_code="{{ $SiteSettings_val->code }}">{{$SiteSettings_val->name}}</option>
                                    @endforeach
                                @else
                                    <option value="" selected>All Site</option>
                                    @if ($SiteSettings && (is_countable($SiteSettings) || is_array($SiteSettings)))
                                        @foreach ($SiteSettings as $SiteSettings_val)
                                            <option value="{{$SiteSettings_val->code}}">{{$SiteSettings_val->name}}</option>
                                        @endforeach
                                    @endif
                                @endif

                                {{-- <option value="">All Site</option>
                                @if($SiteSettings)
                                @foreach($SiteSettings as $SiteSettings_val)
                                <option value="{{$SiteSettings_val->code}}">{{$SiteSettings_val->name}}</option>
                                @endforeach
                                @endif --}}
                            </select>
                        </div>
                        
                        @if(TYPE_WEB=='center')
                            @if(!empty(get_role_custom()))
                                @if(@get_role_custom()['client'] != 1)
                                    <a href="{{route("assets.assets_redirect_add_modal")}}" data-toggle="ajaxModal" class="m-l-xs btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle">@icon('solid/plus') Add</a>
                            
                                    <a href="{{route("scans.home")}}" class="btn btn-sm btn-{{ get_option('theme_color')  }}">
                                        <span data-rel="tooltip" title="Assets Scan" data-placement="bottom"><i class="fas fa-search"></i><span class="hide-text">Assets Scan</span> </span>
                                    </a>

                                    <a id="advance-search" href="#hide-advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }}">
                                        <span data-rel="tooltip" title="Filter" data-placement="bottom"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span> </span>
                                    </a>
                                @endif
                            @endif
                            <input type="hidden" value="" id="site_code">
                        @else
                            <a id="advance-search" href="#hide-advance-search" class="m-l-xs btn btn-sm btn-{{ get_option('theme_color')  }}">
                                <span data-rel="tooltip" title="Filter" data-placement="bottom"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></span>
                            </a>
                            <input type="hidden" value="{{ @$SiteSettings[0]->code }}" id="site_code">
                        @endif
                   
                        

                        <div class="button-control d-none">
                            <div class="btn-group d-none">
                                <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="fillter-advance">Import Asset</button>
                            </div>
        
                            <div class="btn-group d-none">
                                <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" data-toggle="dropdown">Group By
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-left">
                                    <li>
                                        <a href="#">
                                            Internet Name
                                        </a>
                                    </li>   
                                    <li>
                                        <a href="#">
                                            Affiliate - Internet Name
                                        </a>
                                    </li>   
                                    <li>
                                        <a href="#">
                                            Affiliate - Domain Name
                                        </a>
                                    </li>   
                                    <li>
                                        <a href="#">
                                            Domain Name
                                        </a>
                                    </li>  
                                    <li>
                                        <a href="#">
                                            IP Address
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            IPv6 Address
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            Malicious Internet Name
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            Human Name
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            Internet Name
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            Email Address
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            Domain Name (Parent)
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            Phone Number
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>



                    </div>
                </div>
            </header>


            <section class="scrollable wrapper">

                <div class="container-fluid" style="margin-bottom:10px;">
                    <div class="row">
                        <div class="col-md-3 nopadding">
                            <a href="javascript:void(0)" onclick="searchTB('','clearFilter')">
                                <div class="card-dash-compro none-bg none-shadow">
                                    <div class="left-card">
                                        <div class="img-icon-card ice">
                                            <img src="{{asset('images/icon/assets.png')}}" alt="">
                                        </div>
                                        <h3 class="name-dash-text-compro text-dark text-upper ">Assets</h3>
                                        <span class="number-card warning" id="count_assets">0</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 nopadding">
                            <a href="javascript:void(0)" onclick="searchTB('Windows','os_type')">
                            <div class="card-dash-compro none-bg none-shadow">
                                <div class="left-card">
                                        <div class="img-icon-card ice">
                                            <img src="{{asset('images/icon/windows_a.png')}}" alt="">
                                        </div>
                                        <h3 class="name-dash-text-compro text-dark text-upper">Windows</h3>
                                        <span class="number-card info" id="count_windows">0</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 nopadding">
                            <a href="javascript:void(0)" onclick="searchTB('Linux','os_type')">
                            <div class="card-dash-compro none-bg none-shadow">
                                <div class="left-card">
                                        <div class="img-icon-card ice">
                                            <img src="{{asset('images/icon/linux_a.png')}}" alt="">
                                        </div>
                                        <h3 class="name-dash-text-compro text-dark text-upper ">Linux</h3>
                                        <span class="number-card green" id="count_linux">0</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 nopadding">
                            <a href="javascript:void(0)" onclick="searchTB('OthER','os_type')">
                            <div class="card-dash-compro none-bg none-shadow">
                                <div class="left-card">
                                        <div class="img-icon-card ice">
                                            <img src="{{asset('images/icon/Other.png')}}" alt="">
                                        </div>
                                        <h3 class="name-dash-text-compro text-dark text-upper ">OTHER</h3>
                                        <span class="number-card" id="count_other">0</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <section class="panel panel-default" id="hide-advance-search" style="display: none;">
                    <header class="panel-heading font-bold panel-header-naviblue">
                        <div class="row">
                            <div class="col-md-12">
                                <div style="margin-top:5px;">
                                    <i class="fas fa-filter"></i> Filter
                                </div>
                            </div>
                    </header>
                    <div class="panel-body" style="padding: 0 !important">
                        <div class="container-fluid" style="padding: 2rem;">
                            <div class="row">
                                <div class="col-md-3">
                                    <h5 class="font-weight-bold">Filter By</h5>
                                    <div id="groupby-btn" class="btn-group special mb-2">
                                        <button class="btn btn-grey active" onclick="selectGroupBy('domain')">
                                            <span> Host </span>
                                        </button>
                                        <button class="btn btn-grey" onclick="selectGroupBy('ip')">
                                            <span> Assets </span>
                                        </button>
                                        <button class="btn btn-grey" onclick="selectGroupBy('cpe')">
                                            <span> CPE </span>
                                        </button>
                                        <button class="btn btn-grey" onclick="selectGroupBy('os_type')">
                                            <span> OS Type </span>
                                        </button>
                                    </div>
                                    {{-- <div class="form-group">
                                        <select id="groupby-select" class="form-control">
                                            <option value="">- SELECT -</option>
                                        </select>
                                    </div> --}}
                                </div>

                                <div class="col-md-5">
                                    <h5 class="font-weight-bold"> </h5>
                                    <div class="form-group">
                                        <select id="groupby-select" class="form-control">
                                            <option value="">- SELECT -</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4" style="display: none;">
                                    <h5 class="font-weight-bold">Status</h5>
                                    <div id="groupby-status" class="btn-group special mb-2">
                                        <button class="btn btn-grey active" onclick="changeActive('')">
                                            <span> All </span>
                                        </button>
                                        <button class="btn btn-grey" onclick="changeActive('Active')">
                                            <span> Active </span>
                                        </button>
                                        <button class="btn btn-grey" onclick="changeActive('Inactive')">
                                            <span> Inactive </span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <select id="groupby-select2" style="display: none;" class="form-control">
                                            <option value="">Domain All</option>
                                            <option value="">DARK WEB</option> 
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-lg-12 text-right">
                                <button type="button" class="btn btn-info btn-responsive btn-fz-13" onclick="searchTB()">
                                    <i class="fas fa-search"></i>
                                    @langapp('apply')
                                </button>
                                <button type="button" id="btn_rss_data_reset" class="btn btn-default btn-responsive btn-fz-13" onclick="clearTB()" style="white-space: nowrap">
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

                <section class="panel panel-default">
                    <header class="panel-heading font-bold panel-header-naviblue">
                        <div class="row">
                            <div class="col-xs-12">
                                <i class="fas fa-table"></i> Table Assets
                            </div>
                        </div>
                    </header>
                    <div class="panel-body">
                        <div id="custom-asset-list-container"></div>
                        <div class="table-responsive">

                            <!-- id table อันเดิม table-assets-template table-assets-template-test ส่วนปัจจุบันเป็นแค่หน้าบ้านแสดงตัวอย่าง ถ้าเปลี่ยน id กลับแล้ว อย่าลืม ลบ script ด้านล่างออกด้วยนะครับ-->
                            <table class="table table-striped table-bordered" id="table-assets-template">
                                <thead>
                                    <tr>
                                        {{-- <th class="no-sort">
                                            <label>
                                                <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                <span class="label-text"></span>
                                            </label>
                                        </th> --}}
                                        <th rowspan="2" class="align-middle">Site</th>
                                        <th rowspan="2" class="align-middle">Data Type</th>
                                        <th rowspan="2" class="align-middle">Host</th>
                                        <th rowspan="2" class="align-middle">Assets</th>
                                        @if(!empty(get_role_custom()))
                                            @if(@get_role_custom()['client'] != 1)
                                                <th rowspan="2" class="align-middle">Open Ports</th>
                                               
                                            @endif
                                        @endif
                                            
                              
                               
                                       
                                        <th colspan="7" class="text-center">CPE</th>
                                        <th rowspan="2" class="align-middle">Status</th>
                                        @if(!empty(get_role_custom()))
                                        @if(@get_role_custom()['client'] != 1)
                                        <th rowspan="2" class="align-middle">Action</th>
                                        @endif
                                    @endif
                                                
                                                <th rowspan="2" class="align-middle">CPESTRING</th>
                                 

                                        <th rowspan="2" class="align-middle">Asset ID</th>
                                        @if(!empty(get_role_custom()))
                                        @if(@get_role_custom()['client'] != 1)
                                        <th rowspan="2" class="align-middle">CPE_OtherCheck</th>
                                        @endif
                                    @endif
                                    </tr>
                                    <tr>
                                        <th>Vendor</th>
                                        <th>Title</th>
                                        <th>Versions</th>
                                        <th>Edition</th>
                                        <th>Remark</th>
                                        <th>OS Type</th>
                                        @if(!empty(get_role_custom()))
                                        @if(@get_role_custom()['client'] != 1)
                                        <th>Delete CPE</th>
                                        @else 
                                        <th></th>
                                        @endif
                                    @endif
                                               
                                  
                                                
                                   
                              
                                       
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- <tr>
                                        <td rowspan="2">บริษัท เมจิก</td>
                                        <td rowspan="2">mtsc.co.th</td>
                                        <td rowspan="2">104.24.14.205</td>
                                        <td>Microsoft</td>
                                        <td>Windows_10</td>
                                        <td>r2</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></a>
                                        </td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                                            <a href="#" class="btn btn-xs btn-info"><i class="fas fa-plus"></i> Add</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="d-none">บริษัท เมจิก</td>
                                        <td class="d-none">mtsc.co.th</td>
                                        <td class="d-none">104.24.14.205</td>
                                        <td>Microsoft</td>
                                        <td>Windows_10</td>
                                        <td>r2</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></a>
                                        </td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                                            <a href="#" class="btn btn-xs btn-info"><i class="fas fa-plus"></i> Add</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td rowspan="2">บริษัท เมจิก</td>
                                        <td rowspan="2">mtsc.co.th</td>
                                        <td rowspan="2">104.24.14.205</td>
                                        <td>Microsoft</td>
                                        <td>Windows_10</td>
                                        <td>r2</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></a>
                                        </td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                                            <a href="#" class="btn btn-xs btn-info"><i class="fas fa-plus"></i> Add</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="d-none">บริษัท เมจิก</td>
                                        <td class="d-none">mtsc.co.th</td>
                                        <td class="d-none">104.24.14.205</td>
                                        <td>Microsoft</td>
                                        <td>Windows_10</td>
                                        <td>r2</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></a>
                                        </td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                                            <a href="#" class="btn btn-xs btn-info"><i class="fas fa-plus"></i> Add</a>
                                        </td>
                                    </tr> --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

            </section>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>


        <!-- Modal Scans -->
    <div class="modal in fixed-left" id="asset_to_use" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside size-half-50" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        Asset To Use
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-3 text-center">
                            <h3 class="text-dark">Assets</h3>
                        </div>
                        <div class="col-xs-9 text-center">
                            <h3 class="text-dark">Referent</h3>
                        </div>
                        <div class="col-md-12">
                            <hr>
                        </div>
                    </div>
                    <div id="show_asets" class="row">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="button" class="btn btn-info btn-rounded" onclick="save_assets()">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>

</section>

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
<style>
    .host-group-wrapper {
        margin-bottom: 0;
        border: none;
        border-bottom: 1px solid #e8e8e8;
        border-radius: 0;
        background: #fff;
        box-shadow: none;
        overflow: visible;
    }
    .host-header-item {
        background: #fff;
        padding: 12px 18px;
        border-bottom: none;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.2s;
    }
    .host-header-item:hover {
        background: #f5f7fa;
    }
    .host-header-item .host-info {
        display: flex;
        flex-direction: column;
    }
    .host-header-item .host-name {
        font-size: 14px;
        font-weight: 700;
        color: #26478D;
    }
    .host-header-item .site-name {
        font-size: 11px;
        color: #95a5a6;
        margin-top: 2px;
    }
    .host-chevron {
        width: 28px;
        height: 28px;
        min-width: 28px;
        background-color: #26478D;
        border-radius: 50%;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        text-align: center;
        transition: background-color 0.2s, transform 0.3s;
    }
    .host-chevron::before {
        color: #fff !important;
        font-size: 12px;
        line-height: 28px;
    }
    .host-chevron:hover {
        background: #325DC4;
    }
    .ip-container-wrapper {
        padding: 8px 12px;
        background: #fff;
    }
    .ip-box-item {
        margin-bottom: 6px;
        border-radius: 4px;
        overflow: hidden;
    }
    .ip-box-header {
        background: #26478D; /* Blue from Monitoring */
        color: #fff;
        padding: 7px 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        font-weight: 600;
        font-size: 14px;
        transition: background 0.2s;
    }
    .ip-box-header:hover {
        background: #325DC4;
    }
    .ip-box-header i {
        margin-right: 10px;
        font-size: 12px;
        transition: transform 0.2s;
    }
    .ip-box-header.expanded i {
        transform: rotate(90deg);
    }
    .ip-details-area {
        background: #fff;
        border: 1px solid #26478D;
        border-top: none;
        padding: 0;
        border-bottom-left-radius: 4px;
        border-bottom-right-radius: 4px;
        overflow: hidden;
    }
    .cpe-text-content {
        font-size: 12px;
        color: #2f3640;
        flex: 1;
        margin-right: 12px;
        word-break: break-all;
    }
    .status-action-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .badge-status {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }
    .badge-active { background: #2ecc71; color: #fff; }
    .badge-inactive { background: #e74c3c; color: #fff; }
    
    .btn-delete-cpe { background: #e74c3c; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; font-size: 11px; }
    .btn-add-cpe-custom { background: #3498db; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; font-size: 11px; }
    .btn-edit-asset-custom { background: #3498db; color: #fff; border: none; padding: 4px 10px; border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; }

    .mapping-detail-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        background: #fff;
    }
    .mapping-detail-table th {
        background: #f8f9fa;
        color: #555;
        font-weight: 600;
        font-size: 12px;
        padding: 8px 12px;
        text-align: left;
        border-bottom: 2px solid #dee2e6;
    }
    .mapping-detail-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
        font-size: 13px;
    }
    .mapping-detail-table tbody tr:last-child td {
        border-bottom: none;
    }
    .mapping-type-tag {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        background: #e9ecef;
        color: #495057;
        border: 1px solid #ced4da;
    }
    .type-port { background: #e3f2fd; color: #0d47a1; border-color: #bbdefb; }
    .type-cpe { background: #f3e5f5; color: #4a148c; border-color: #e1bee7; }
    
    .port-item-box {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-right: 15px;
        margin-bottom: 5px;
    }
    .bg-port {
        background: #3056d3 !important;
        color: #fff !important;
        padding: 3px 12px;
        border-radius: 20px;
        font-weight: 700;
        display: inline-block;
        min-width: 45px;
        text-align: center;
        font-size: 12px;
        box-shadow: 0 2px 4px rgba(48, 86, 211, 0.2);
    }
</style>
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.activebutton')
@include('stacks.js.advanced_search')

<script>
    var loadingCount = 0;
    var originalLoading = (typeof window.loading === "function") ? window.loading : function(){};
    window.loading = function(mode){
        if(mode == 'load'){
            loadingCount++;
            if(loadingCount == 1) originalLoading('load');
        }else if(mode == 'stop_load'){
            loadingCount--;
            if(loadingCount <= 0){
                loadingCount = 0;
                originalLoading('stop_load');
            }
        }
    };
    
    active_btn('#groupby-btn .btn-grey');
    active_btn('#groupby-status .btn-grey');
    var id_select_site = 'select-site';
    var site_code = null;
    var menu = null;
    @if(!empty(get_role_custom()))
        @if(@get_role_custom()['client'] == 1)
        site_code = $('#select-site').find(':selected').attr("data-site_code");
        menu = 'site';
        @endif
    @endif
    $(document).ready(function () {
        selectGroupByFirst();

        $('#source').select2();
        $('#select-site').select2();
        
        $('.hide-fillter').hide();
        $('#fillter-advance').click(function(){
            $('.hide-fillter').toggle();
        });
        
        $.when(data_table()).then(cookie_change_site("{{route('systemsetting.check_cookie_site')}}",id_select_site));
        
    });

    var site_id = 0;
    $(function () {
        
    });

    var active = '';
    function changeActive(act){
        active = act;
    }

    var selectedGroup = 'domain';
    function selectGroupBy(columnGroup, force = false){
        if(selectedGroup!=columnGroup || force){
            selectedGroup = columnGroup;
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{!! route('assets.get_selected_filter') !!}',
                type: "get",
                data: ({
                    selectedGroup:selectedGroup,
                    sitecode:site_id,
                }),
                datatype: "html",
                beforeSend: function(){
                    
                },
            }).done(function(data){
                let groupby_select = '';
                groupby_select += '<option selected value="">- SELECT -</option>';
                $.each(data.selected, function(key, val){
                    groupby_select += '<option value="'+val.val_select+'">'+val.val_select+'</option>';
                });
                $('#groupby-select').html(groupby_select);
                $('#groupby-select').select2();
                
            }).fail(function(jqXHR, ajaxOptions, thrownError){
                
                console.log("No response from server");
            });
        }
    }

    function clearTB(){
        $("#groupby-select").val('').trigger("change");
        $("#select-site").val("").trigger("change");
        $("#groupby-status>button").removeClass("active");
        $("#groupby-status>button:first").addClass("active");
        active = '';
        searchTB();
    }

    function selectGroupByFirst(){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{!! route('assets.get_selected_filter') !!}',
            type: "get",
            data: ({
                selectedGroup:selectedGroup,
                sitecode:site_id,
            }),
            datatype: "html",
            beforeSend: function(){
                

            },
        }).done(function(data){
            let groupby_select = '';
            groupby_select += '<option selected value="">- SELECT -</option>';
            $.each(data.selected, function(key, val){
                groupby_select += '<option value="'+val.val_select+'">'+val.val_select+'</option>';
            });
            $('#groupby-select').html(groupby_select);
            $('#groupby-select').select2();
            
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            
            console.log("No response from server");
        });
    }

    function searchTB(searchLinkAll='',colsearchLinkAll=''){

        let selectedValue = $('#groupby-select').children("option:selected").val();
        let columnSearch = selectedGroup;
        let otherval = null;

        if(colsearchLinkAll!==''){
            if(colsearchLinkAll=='clearFilter'){
                columnSearch = '';
                selectedValue = '';
            }else{
                columnSearch = colsearchLinkAll;
                selectedValue = searchLinkAll;
            }
        }

        let selectedSiteCode = $('#select-site').val();
        let selectedSiteName = '';
        if(selectedSiteCode != 0 && selectedSiteCode != ''){
            selectedSiteName = $('#select-site').children("option:selected").text();
        }

        console.log(columnSearch+' / '+selectedValue);

        let active_tb;
        if(active==""){
            active_tb = active;
        }else{
            active_tb = '^'+active+'$';
        }
        
        if(columnSearch=='domain'){
            columnSearch = 'domain:name';
            if(selectedValue != null && selectedValue != " " && selectedValue != [])
            {   
                selectedValue = '^'+selectedValue+'$';
            }
        }else if(columnSearch=='ip'){
            columnSearch = 'ip:name';
        }else if(columnSearch=='cpe'){
            columnSearch = 'CPE:name';
        }else if(columnSearch == 'os_type'){
            if(selectedValue == 'OthER'){
                columnSearch = 'CPE_OtherCheck:name';
                selectedValue = '1';
            }
            else {
                columnSearch = 'CPE_Ostype:name';
            }
        }else if(columnSearch=='ip_asset_id'){
 
        }else{
            columnSearch = 100;
            selectedValue = '';
        }

        t.search( '' ).columns().search( '' ).draw();
        
        loading('load');
        setTimeout(function() {
            let allData = t.rows().data().toArray();
            
            if (allData.length > 0) {
                let w = 0, l = 0, o = 0, total = 0;
                
                allData.forEach(function(row) {
                    let matchSite = (selectedSiteCode === '' || selectedSiteCode == 0) || (row.site_code && row.site_code === selectedSiteCode);
                    let statusText = row.status == 1 ? 'Active' : 'Inactive';
                    let matchStatus = (active_tb === '') || (new RegExp(active_tb).test(statusText));

                    if (matchSite && matchStatus) {
                        total++;
                        let os = row.CPE_Ostype ? row.CPE_Ostype.toString().toLowerCase() : '';
                        if (os.includes('window')) w++;
                        if (os.includes('linux')) l++;
                        if (row.CPE_OtherCheck == 1) o++;
                    }
                });

                let currentAssetsHtml = $('#count_assets').html() || "";
                let limitMatch = currentAssetsHtml.match(/\/(\d+)$/);
                let limitText = limitMatch ? "/" + limitMatch[1] : "";
                
                $('#count_assets').html(total + limitText);
                $('#count_windows').html(w);
                $('#count_linux').html(l);
                $('#count_other').html(o);
            }
            
            let siteCol = t.column('site_code:name');
            let searchCol = columnSearch ? t.column(columnSearch) : null;
            let statusCol = t.column('status:name');

            if(siteCol.length) siteCol.search(selectedSiteCode ? '^' + $.fn.dataTable.util.escapeRegex(selectedSiteCode) + '$' : '', true, false, false);
            if(searchCol && searchCol.length) {
                if(columnSearch == 'domain:name' || columnSearch == 'ip_asset_id:name'){
                    searchCol.search(selectedValue, true, false);
                } else {
                    searchCol.search(selectedValue);
                }
            }
            if(statusCol.length) statusCol.search(active_tb, true, false);
            
            t.draw();
            loading('stop_load');
        }, 50);
    }

    var t;
    function changeSite(val){
        loading('load');
        site_id = $(`#${id_select_site}`).val();
        set_cookie_site(site_id);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{!! route('assets.countAssets') !!}',
            type: "get",
            data: ({
                sitecode:$('#select-site').val(),
            }),
            datatype: "html",
            beforeSend: function(){
                
            },
        }).done(function(data){
            let currentAssetsHtml = $('#count_assets').html() || "";
            let currentTotal = currentAssetsHtml.split('/')[0];
            if(data.assetLimit){
                $('#count_assets').html(currentTotal+"/"+data.assetLimit);
            }else{
                $('#count_assets').html(currentTotal+"");
            }
            selectGroupBy(selectedGroup, true);
            searchTB();
            loading('stop_load');
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            loading('stop_load');
            console.log("No response from server");
        });
    }

    function data_table(){
        t = $('#table-assets-template').DataTable({
            ordering: true,
            pagination: true,
            pageLength: 25,
            processing: true,
            serverSide: false,
            "searching": true,
            destroy: true,
            "dom": '<"btnaction"><"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"<"m-l-10"B>>>rt<"bottom"ip><"clear">',
            order: [[ 0, "asc" ]],
            "drawCallback": function( settings ) {
                renderCustomUI(this.api());
            },
            ajax: {
                type: "POST",
                url: '{!! route('assets.table_asset')!!}',
                data:function(d){
                    d.menu = menu ? menu : "{{$menu}}";
                    d.site = site_code != null ? site_code : $('#site_code').val();
                },
                beforeSend: function(){
                    loading('load');
                }
            },
            initComplete : function( settings, json){
                if(json.assetLimit){
                    $('#count_assets').html(json.countAssets+"/"+json.assetLimit);
                }else{
                    $('#count_assets').html(json.countAssets+"");
                }
                $('#count_windows').html(json.countWindows+"");
                $('#count_linux').html(json.countLinux+"");
                $('#count_other').html(json.countOther+"");
                
                let check = {!!json_encode($Search_Link_All)!!};
                if(check===""){
                    searchTB();
                }else{
                    searchTB(check,'ip_asset_id');
                }
                loading('stop_load');
            },
            columns: [
                {{--{
                    width: '1%',
                    data: 'chk',
                    name: 'chk',
                },--}}
                {
                    width: '25%',
                    data: 'site_name',
                    name: 'site_name',
                    className: 'no-wrap'
                },
                {
                    data: 'site_code',
                    name: 'site_code',
                    visible: false,
                },
                {
                    data: 'data_type',
                    name: 'data_type',
                    visible: false,
                },
                {
                    width: '20%',
                    data: 'domain',
                    name: 'domain',
                },
                {
                    width: '20%',
                    data: 'ip',
                    name: 'ip',
                },
                @if(!empty(get_role_custom()))
                    @if(@get_role_custom()['client'] != 1)
                        {
                            data: 'port',
                            name: 'port',
                            className: 'no-wrap'
                        }, 
                    @endif
                @endif
                                            
                    
                
                {
                    data: 'CPE_Vendor',
                    name: 'CPE_Vendor',
                    className: 'padingtablezero text-center no-wrap'
                },
                {
                    data: 'CPE_Title',
                    name: 'CPE_Title',
                    className: 'padingtablezero text-center no-wrap'
                },
                {
                    data: 'CPE_Version',
                    name: 'CPE_Version',
                    className: 'padingtablezero text-center no-wrap'
                },
                {
                    data: 'CPE_Edition',
                    name: 'CPE_Edition',
                    className: 'padingtablezero text-center no-wrap'
                },
              
                {
                    data: 'CPE_Remark',
                    name: 'CPE_Remark',
                    className: 'padingtablezero text-center no-wrap'
                },
                {
                    data: 'CPE_Ostype',
                    name: 'CPE_Ostype',
                    className: 'padingtablezero text-center no-wrap'
                },
                @if(!empty(get_role_custom()))
                    @if(@get_role_custom()['client'] != 1)
                    {
                            data: 'CPE_Del',
                            name: 'CPE_Del',
                            className: 'padingtablezero text-center no-wrap',
                            visible:{{(TYPE_WEB=='center'?json_encode(true):json_encode(false))}},
                        },
    
                    @endif
                @endif
                    
                        
               
                {
                    orderable: false,
                    width: '3%',
                    data: 'status',
                    name: 'status',
                    className: 'text-center'
                },  
                @if(!empty(get_role_custom()))
                    @if(@get_role_custom()['client'] != 1)
                    {
                        orderable: false,
                        width: '3%',
                        data: 'action',
                        name: 'action',
                        className: 'text-center no-wrap',
                        visible:{{(TYPE_WEB=='center'?json_encode(true):json_encode(false))}},
                    }, 
                    @endif
                @endif
                    
                        {
                            data: 'CPE',
                            name: 'CPE',
                            visible:false
                        },
                {
                    data: 'ip_asset_id',
                    name: 'ip_asset_id',
                    visible:false
                },
                {
                    data: 'CPE_OtherCheck',
                    name: 'CPE_OtherCheck',
                    visible:false
                },
            ],
            columnDefs: [
                {{--{
                    
                    targets: 0,
                    searchable: false,
                    orderable: false,
                    width: '10px',
                    render: function (data, type, row, meta) {
                        return '<label><input type="checkbox" name="checked" class="select-chk asset_id" value="' + row.code + '"><span class="label-text"></span></label>';
                    }
                   
                },--}}
                {{-- {
                    targets: 9,
                    render: function (data, type, row, meta) {
                        return row.cpe+row.action;
                        
                    }
                   
                }, --}}
                {
                    targets: 13,
                    render: function (data, type, row, meta) {
                        return row.cpe+row.action+`<span style="visibility: hidden;width: 0px;overflow: hidden;display: inline-block;">${row.CPE}</span>`;
                        
                    }
                   
                },
                {
                    @if(!empty(get_role_custom()))
                    @if(@get_role_custom()['client'] != 1)
                    targets: 12,
                    @else 
                    targets: 10,
                    @endif
                @endif
                    render: function (data, type, row, meta) {
                        if(row.status==1){
                            return '<span class="badge badge-success">Active</span>';
                        }else{
                            return '<span class="badge badge-danger">Inactive</span>';
                        }
                        
                    }
                   
                },
            ],


        });


        {{-- $("div.btnaction").html('<button id="btn_view_1" class="btn btn-info" onclick="btn_view(1)" >Host Info</button> <button  id="btn_view_2" onclick="btn_view(2)" class="btn">Service / Port</button>'); --}}
        $("div.btnaction").html('<button id="btn_view_1" class="btn btn-info" onclick="btn_view(1)" >Host Info</button>');
    }

    function formatCPE(row) {
        let parts = [];
        if (row.CPE_Vendor) parts.push(stripTags(row.CPE_Vendor).trim());
        if (row.CPE_Title) parts.push(stripTags(row.CPE_Title).trim());
        if (row.CPE_Version) parts.push(stripTags(row.CPE_Version).trim());
        if (row.CPE_Edition) parts.push(stripTags(row.CPE_Edition).trim());
        if (row.CPE_Remark) parts.push(stripTags(row.CPE_Remark).trim());
        if (row.CPE_Ostype) parts.push(stripTags(row.CPE_Ostype).trim());
        
        return parts.filter(p => p !== '' && p !== '-' && p !== '&nbsp;').join(' | ');
    }

    function stripTags(html) {
        if (!html) return "";
        let tmp = document.createElement("DIV");
        tmp.innerHTML = html;
        return tmp.textContent || tmp.innerText || "";
    }

    function unzipCPE(row) {
        try {
            let delimiter = '<hr class="m-0" style="border: 1px solid #efefef;">';
            
            let getArr = (val) => {
                if (!val) return [];
                if (Array.isArray(val)) return val;
                if (typeof val === 'string') return val.split(delimiter);
                return [val];
            };

            let vendors = getArr(row.CPE_Vendor);
            let titles = getArr(row.CPE_Title);
            let versions = getArr(row.CPE_Version);
            let editions = getArr(row.CPE_Edition);
            let remarks = getArr(row.CPE_Remark);
            let ostypes = getArr(row.CPE_Ostype);
            let deletes = getArr(row.CPE_Del);
            
            let cpes = [];
            let maxLen = Math.max(vendors.length, titles.length, versions.length);
            
            for (let i = 0; i < maxLen; i++) {
                let v = stripTags(vendors[i] || '').trim();
                let t = stripTags(titles[i] || '').trim();
                if (v || t) {
                    cpes.push({
                        vendor: v,
                        title: t,
                        version: stripTags(versions[i] || '').trim(),
                        edition: stripTags(editions[i] || '').trim(),
                        remark: stripTags(remarks[i] || '').trim(),
                        ostype: stripTags(ostypes[i] || '').trim(),
                        del_btn: deletes[i] || ''
                    });
                }
            }
            return cpes;
        } catch (e) {
            console.error("Error in unzipCPE:", e);
            return [];
        }
    }

    function isIP(str) {
        return /^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/.test(str);
    }

    function generateCustomPagination(currentPage, totalPages) {
        if (totalPages <= 1) return '';
        let html = '<div class="dataTables_paginate paging_simple_numbers"><ul class="pagination" style="margin: 0;">';
        
        let prevClass = currentPage === 1 ? 'disabled' : '';
        html += `<li class="paginate_button previous ${prevClass}"><a href="javascript:void(0);" ${currentPage !== 1 ? 'onclick="goToCustomPage(' + (currentPage - 1) + ')"' : ''}>Previous</a></li>`;
        
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);
        
        if (startPage > 1) {
            html += `<li class="paginate_button"><a href="javascript:void(0);" onclick="goToCustomPage(1)">1</a></li>`;
            if (startPage > 2) html += `<li class="paginate_button disabled"><a href="javascript:void(0);">...</a></li>`;
        }
        
        for (let i = startPage; i <= endPage; i++) {
            let activeClass = i === currentPage ? 'active' : '';
            html += `<li class="paginate_button ${activeClass}"><a href="javascript:void(0);" ${i !== currentPage ? 'onclick="goToCustomPage(' + i + ')"' : ''}>${i}</a></li>`;
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += `<li class="paginate_button disabled"><a href="javascript:void(0);">...</a></li>`;
            html += `<li class="paginate_button"><a href="javascript:void(0);" onclick="goToCustomPage(${totalPages})">${totalPages}</a></li>`;
        }
        
        let nextClass = currentPage === totalPages || totalPages === 0 ? 'disabled' : '';
        html += `<li class="paginate_button next ${nextClass}"><a href="javascript:void(0);" ${currentPage !== totalPages && totalPages !== 0 ? 'onclick="goToCustomPage(' + (currentPage + 1) + ')"' : ''}>Next</a></li>`;
        
        html += '</ul></div>';
        return html;
    }

    function goToCustomPage(page) {
        let api = $('#table-assets-template').DataTable();
        renderCustomUI(api, page);
    }

    var customCurrentPage = 1;
    function renderCustomUI(apiInstance, pageOverride) {
        try {
            let api = apiInstance || t;
            if (!api || typeof api.rows !== 'function') return;
            
            let rows = api.rows({ filter: 'applied' }).data().toArray();
            let container = $('#custom-asset-list-container');
            
            if (rows.length === 0) {
                container.empty().hide();
                $('.table-responsive').show();
                $('#table-assets-template').show();
                return;
            }
            $('#table-assets-template').hide();
            $('.table-responsive').hide();
            $('#table-assets-template_processing').hide();
            container.empty().show();

            let length = $('#table-assets-template_length').clone(true);
            if (length.length || search.length) {
                container.append(`
                    <div class="custom-top-controls" style="padding: 12px 15px; background: #fff; border-radius: 8px 8px 0 0; border-bottom: 1px solid #eee; margin-bottom: 10px;">
                        <div style="margin-bottom: 12px;">
                            <button class="btn btn-primary" style="background-color: #3056d3; border-color: #3056d3; border-radius: 20px; padding: 6px 20px; font-weight: 500; font-size: 14px; box-shadow: 0 4px 10px rgba(48, 86, 211, 0.3);">Host Info</button>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div class="custom-length-area"></div>
                        </div>
                    </div>
                `);
                container.find('.custom-length-area').append(length);
                
                let lengthSelect = container.find('.dataTables_length select');
                lengthSelect.addClass('form-control input-sm').css({'width': 'auto', 'display': 'inline-block', 'margin': '0 5px'});
                
                lengthSelect.on('change', function() {
                    let val = $(this).val();
                    $('#table-assets-template_length select').val(val).trigger('change');
                });
            }

            let groups = {};
            rows.forEach(function(row) {
                let host = row.domain || '-';
                let site = row.site_name || '-';
                let groupKey = host + '@@@' + site;
                
                if (!groups[groupKey]) {
                    groups[groupKey] = {
                        host: host,
                        site: site,
                        assetGroups: {} 
                    };
                }
                
                let assetId = row.ip_asset_id || row.id;
                if (!groups[groupKey].assetGroups[assetId]) {
                    groups[groupKey].assetGroups[assetId] = [];
                }
                groups[groupKey].assetGroups[assetId].push(row);
            });

    
            let groupKeys = Object.keys(groups);
            let pageLength = api.page.len();
            if (pageLength === -1) pageLength = groupKeys.length;
            
            let totalHosts = groupKeys.length;
            let totalPages = Math.ceil(totalHosts / pageLength);
            
            if (pageOverride !== undefined) {
                customCurrentPage = pageOverride;
            } else {
                customCurrentPage = 1; 
            }
            if (customCurrentPage > totalPages) customCurrentPage = totalPages || 1;
            
            let startIndex = (customCurrentPage - 1) * pageLength;
            let endIndex = startIndex + pageLength;
            let paginatedKeys = groupKeys.slice(startIndex, endIndex);

            paginatedKeys.forEach(function(key) {
                let group = groups[key];
                let hostId = 'host-' + Math.random().toString(36).substr(2, 9);
                
                let ipBoxesHtml = Object.keys(group.assetGroups).map(assetId => {
                    let assetRows = group.assetGroups[assetId].filter(r => {
                        let dt = (r.data_type || '').toString().toLowerCase();
                        return dt !== 'cve' && dt !== '16';
                    });
                    if (assetRows.length === 0) return '';

                    let ipId = 'ip-' + Math.random().toString(36).substr(2, 9);
                    
                    let displayLabel = '';
                    assetRows.forEach(r => {
                        if (isIP(r.ip)) displayLabel = r.ip;
                    });
                    if (!displayLabel) displayLabel = assetRows[0].ip;

                    return `
                    <div class="ip-box-item">
                        <div class="ip-box-header" onclick="$('#${ipId}').slideToggle(250); $(this).toggleClass('expanded');">
                            <i class="fas fa-chevron-right"></i>
                            <span>${displayLabel}</span>
                        </div>
                        <div class="ip-details-area" id="${ipId}" style="display:none; padding: 0;">
                            <table class="mapping-detail-table">
                                <thead>
                                    <tr>
                                        <th width="15%">Type</th>
                                        <th width="50%">Information</th>
                                        <th width="15%" style="text-align: center;">Status</th>
                                        <th width="20%" style="text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${(() => {
                                        if (assetRows.length === 0) return '';
                                        
                                        let globalHtml = '';
                                        let firstRow = assetRows[0];
                                        
                                        let cpes = [];
                                        try {
                                            cpes = unzipCPE(firstRow) || [];
                                        } catch (e) {
                                            console.error('Error in unzipCPE:', e);
                                        }
                                        
                                        let hasRealPort = firstRow.port && 
                                                        firstRow.port.trim() !== '' && 
                                                        firstRow.port.indexOf('>-<') === -1 && 
                                                        firstRow.port !== '-';
                                        
                                        if (hasRealPort) {
                                            const ports = firstRow.port.split('<div>').filter(p => p.trim());
                                            globalHtml += ports.map((p, idx) => `
                                            <tr>
                                                ${idx === 0 ? `<td rowspan="${ports.length}" style="vertical-align: middle;"><span class="mapping-type-tag">Network Port</span></td>` : ''}
                                                <td>${p}</td>
                                                <td style="text-align: center;"><span class="badge-status badge-active">Open</span></td>
                                                <td style="vertical-align: middle; text-align: center;">
                                                    ${firstRow.action || ''}
                                                </td>
                                            </tr>`).join('');
                                        }

                    
                                        if (cpes.length > 0) {
                                            globalHtml += cpes.map((cpe, idx) => `
                                            <tr>
                                                ${idx === 0 ? `
                                                <td rowspan="${cpes.length}" style="vertical-align: middle;">
                                                    <span class="mapping-type-tag">CPE</span>
                                                </td>` : ''}
                                                <td style="vertical-align: middle;">
                                                    <strong>${cpe.vendor}</strong> | ${cpe.title} | ${cpe.version} 
                                                    ${cpe.edition ? '| ' + cpe.edition : ''} 
                                                    ${cpe.ostype ? '<br><small class="text-muted"><i class="fas fa-desktop"></i> <strong> OS :</strong> ' + cpe.ostype + '</small>' : ''}
                                                    ${cpe.remark && cpe.remark !== '-' ? '<br><small class="text-muted"><i class="fas fa-sticky-note"></i> <strong> Remark :</strong> ' + cpe.remark + '</small>' : ''}
                                                </td>
                                                <td style="vertical-align: middle; text-align: center;">
                                                    ${firstRow.status == 1 ? '<span class="badge-status badge-active">Active</span>' : '<span class="badge-status badge-inactive">Inactive</span>'}
                                                </td>
                                                <td style="vertical-align: middle; text-align: center;">
                                                    ${cpe.del_btn}
                                                </td>
                                            </tr>`).join('');
                                        }

                                  
                                        let hasRenderedFallback = false;
                                        assetRows.forEach((rowData, rIdx) => {
                                            let dtStr = (rowData.data_type || '').toString().toLowerCase();
                                            let isIPAddressType = (dtStr === '5' || dtStr === 'ip address');
                                            let isNetworkType = (dtStr === '18' || dtStr === 'network' || dtStr.includes('fddd3fd7'));

                                            if (isNetworkType || (dtStr !== '' && !isIPAddressType)) {
                                                let metaTag = rowData.data_type || '';
                                                if (isNetworkType) metaTag = 'Network';
                                                
                                                if (!metaTag) {
                                                    if (rowData.ip && rowData.ip.toUpperCase().startsWith('AS')) metaTag = 'ASN';
                                                    else if (rowData.ip && rowData.ip.indexOf(' ') > -1) metaTag = 'Network Info';
                                                    else metaTag = 'Metadata';
                                                }

                                                let showAddCpe = !hasRealPort && rIdx === 0;

                                                globalHtml += `
                                                <tr>
                                                    <td><span class="mapping-type-tag">${metaTag}</span></td>
                                                    <td class="text-muted">${rowData.ip}</td>
                                                    <td>-</td>
                                                    <td style="vertical-align: middle; text-align: center;">
                                                        ${showAddCpe ? (firstRow.cpe || '') : ''}
                                                        ${rowData.action || ''}
                                                    </td>
                                                </tr>`;
                                                hasRenderedFallback = true;
                                            }
                                        });

                                        if (globalHtml === '' && !hasRenderedFallback && assetRows.length > 0) {
                                            let rowData = firstRow;
                                            let metaTag = rowData.data_type || 'Metadata';
                                            globalHtml += `
                                            <tr>
                                                <td><span class="mapping-type-tag">${metaTag}</span></td>
                                                <td class="text-muted">${rowData.ip}</td>
                                                <td style="text-align: center;">-</td>
                                                <td style="vertical-align: middle; text-align: center;">
                                                    ${!hasRealPort ? (firstRow.cpe || '') : ''}
                                                    ${rowData.action || ''}
                                                </td>
                                            </tr>`;
                                        }

                                        return globalHtml;
                                    })()}
                                </tbody>
                            </table>
                        </div>
                    </div>`;
                }).join('');

                if (ipBoxesHtml.trim() === '') return;

                let groupHtml = `
                    <div class="host-group-wrapper">
                        <div class="host-header-item" onclick="$('#${hostId}').slideToggle(300); $(this).find('.host-chevron').toggleClass('fa-chevron-right fa-chevron-down');">
                            <div class="host-info">
                                <span class="host-name">${group.host}</span>
                                <span class="site-name">Site : ${group.site}</span>
                            </div>
                            <i class="fas fa-chevron-right host-chevron"></i>
                        </div>
                        <div class="ip-container-wrapper" id="${hostId}" style="display:none;">
                            ${ipBoxesHtml}
                        </div>
                    </div>
                `;
                container.append(groupHtml);
            });
            
          
            let controlsHtml = `
                <div class="custom-table-controls" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; background: #fff; border-radius: 0 0 8px 8px; border-top: 1px solid #eee; margin-top: -10px; margin-bottom: 20px;">
                    <div class="custom-info-area">
                        <div class="dataTables_info" style="padding-top: 5px; font-size: 13px; color: #666;">
                            Showing ${totalHosts === 0 ? 0 : startIndex + 1} to ${Math.min(endIndex, totalHosts)} of ${totalHosts} hosts
                        </div>
                    </div>
                    <div class="custom-pagination-area">
                        ${generateCustomPagination(customCurrentPage, totalPages)}
                    </div>
                </div>
            `;
            container.append(controlsHtml);

        } catch (e) {
            console.error("Error in renderCustomUI:", e);
            $('.table-responsive').show();
            $('#table-assets-template').show();
        } finally {
            if (typeof window.loading === "function") {
                window.loading('stop_load');
            }
        }
    }

    function data_table_host(){
        t = $('#table-assets-template').DataTable({
            ordering: true,
            pagination: true,
            pageLength: 25,
            processing: true,
            serverSide: false,
            "searching": true,
            destroy: true,
            "dom": '<"btnaction"><"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
            order: [[ 0, "asc" ]],
                ajax: {
                type: "POST",
                url: '{!! route('assets.table_asset_host')!!}',
                data:function(d){
                    d.menu = menu ? menu : "{{$menu}}";
                    d.site = site_code != null ? site_code : $('#site_code').val();
                },
                beforeSend: function(){
                    loading('load');
                }
            },
            initComplete : function( settings, json){
                if(json.assetLimit){
                    $('#count_assets').html(json.countAssets+"/"+json.assetLimit);
                }else{
                    $('#count_assets').html(json.countAssets+"");
                }
                $('#count_windows').html(json.countWindows+"");
                $('#count_linux').html(json.countLinux+"");
                $('#count_other').html(json.countOther+"");
                
                let check = {!!json_encode($Search_Link_All)!!};
                if(check===""){
                    searchTB();
                }else{
                    searchTB(check,'ip_asset_id');
                }
                loading('stop_load');
            },
            columns: [
                {{--{
                    width: '1%',
                    data: 'chk',
                    name: 'chk',
                },--}}
                {
                    width: '25%',
                    data: 'site_name',
                    name: 'site_name',
                    className: 'no-wrap'
                },
                {
                    data: 'site_code',
                    name: 'site_code',
                    visible: false,
                },
                {
                    data: 'data_type',
                    name: 'data_type',
                    className: 'no-wrap',
                },
                {
                    width: '20%',
                    data: 'domain',
                    name: 'domain',
                },
                {
                    width: '20%',
                    data: 'ip',
                    name: 'ip',
                },
                {
                    data: 'port',
                    name: 'port',
                    className: 'no-wrap',
                    "visible": false,
                    
                },
                
                {
                    data: 'CPE_Vendor',
                    name: 'CPE_Vendor',
                    className: 'padingtablezero text-center no-wrap',
                    visible:false
                },
                {
                    data: 'CPE_Title',
                    name: 'CPE_Title',
                    className: 'padingtablezero text-center no-wrap',
                    visible:false
                },
                {
                    data: 'CPE_Version',
                    name: 'CPE_Version',
                    className: 'padingtablezero text-center no-wrap',
                    visible:false
                },
                {
                    data: 'CPE_Edition',
                    name: 'CPE_Edition',
                    className: 'padingtablezero text-center no-wrap',
                    visible:false
                },
                {
                    data: 'CPE_Remark',
                    name: 'CPE_Remark',
                    className: 'padingtablezero text-center no-wrap',
                    visible:false
                },
                {
                    data: 'CPE_Ostype',
                    name: 'CPE_Ostype',
                    className: 'padingtablezero text-center no-wrap',
                    visible:false
                },
 
                        {
                            data: 'CPE_Del',
                            name: 'CPE_Del',
                            className: 'padingtablezero text-center no-wrap',
                            visible:{{(TYPE_WEB=='center'?json_encode(true):json_encode(false))}},
                            visible:false
                        },
    

                {
                    orderable: false,
                    width: '3%',
                    data: 'status',
                    name: 'status',
                    className: 'text-center',
                    visible:false
                },  
                {
                    orderable: false,
                    width: '3%',
                    data: 'action',
                    name: 'action',
                    className: 'text-center no-wrap',
                    visible:{{(TYPE_WEB=='center'?json_encode(true):json_encode(false))}},
                    visible:false
                },
                {
                    data: 'CPE',
                    name: 'CPE',
                    visible:false
                },
                {
                    data: 'ip_asset_id',
                    name: 'ip_asset_id',
                    visible:false
                },
                {
                    data: 'CPE_OtherCheck',
                    name: 'CPE_OtherCheck',
                    visible:false
                },
            ],
            columnDefs: [
                {{--{
                    
                    targets: 0,
                    searchable: false,
                    orderable: false,
                    width: '10px',
                    render: function (data, type, row, meta) {
                        return '<label><input type="checkbox" name="checked" class="select-chk asset_id" value="' + row.code + '"><span class="label-text"></span></label>';
                    }
                   
                },--}}
                {
                    targets: 12,
                    render: function (data, type, row, meta) {
                        return row.cpe+row.action;
                        
                    }
                   
                },
                {
                    targets: 11,
                    render: function (data, type, row, meta) {
                        if(row.status==1){
                            return '<span class="badge badge-success">Active</span>';
                        }else{
                            return '<span class="badge badge-danger">Inactive</span>';
                        }
                        
                    }
                   
                },
            ],
            "drawCallback": function ( settings ) {
            var api = this.api();
            var rows = api.rows( {page:'current'} ).nodes();
            var last=null;
 
            api.column(4, {page:'current'} ).data().each( function ( group, i ) {
                if ( last !== group ) {
                    $(rows).eq( i ).before(
                        '<tr class="group"><td colspan="4">'+group+'</td></tr>'
                    );
 
                    last = group;
                }
            } );
        }


        });


        $("div.btnaction").html('<button id="btn_view_1" class="btn btn-info" onclick="btn_view(1)" >Host Info</button> <button  id="btn_view_2" onclick="btn_view(2)" class="btn">Service / Port</button>');
    }
    function btn_view(mode){
            if(mode == 2){
                $('#custom-asset-list-container').hide();
                $('.table-responsive').show();
                $('#table-assets-template').show();
                data_table_host();
                $('#btn_view_1').removeClass( "btn-info" );
                $('#btn_view_2').addClass( "btn-info" );
            }else{
                data_table();
                $('#btn_view_2').removeClass( "btn-info" );
                $('#btn_view_1').addClass( "btn-info" );
            }
    }

</script>
<style>
tr.group,
tr.group:hover {
    background-color: #ddd !important;
}
tr.group td,
tr.group td:hover {
    background-color: #ddd !important;
}

</style>
@endpush

@endsection
