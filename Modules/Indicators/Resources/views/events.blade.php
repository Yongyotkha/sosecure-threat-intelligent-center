@extends('layouts.app')
@section('content')
<style type="">
select.c-tags {
    min-width: 300px;
}
.select2-container--default .select2-selection--multiple {
    min-width: 300px !important;
}
.btn-published {
    background-color: #22c55e; /* สีเขียว */
}

.btn-unpublished {
    background-color: #ef4444; /* สีแดง */
}

.tag-container {
    max-height: 2.5em; /* หรือประมาณ 1 บรรทัด */
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.tag-container.expanded {
    max-height: 500px; /* แสดง tag ทั้งหมด */
}

.tag-label {
    display: inline-block;
    background-color: #f0f0f0;
    margin: 2px;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.8em;
}
.select2-wrapper.collapsed {
    max-height: 38px; /* ความสูงพอดี 1 บรรทัด */
    overflow: hidden;
    transition: max-height 0.3s ease;
}

.select2-wrapper.expanded {
    max-height: 300px; /* หรือ auto ถ้าคุณแน่ใจเรื่องขนาด */
} 
.resizable-select2 {
  background-image: url('data:image/svg+xml;utf8,<svg fill="%23999" xmlns="http://www.w3.org/2000/svg" width="10" height="10"><path d="M0 10 L10 0 M3 10 L10 3 M6 10 L10 6" stroke="%23999"/></svg>');
  background-repeat: no-repeat;
  background-position: bottom right;
  background-size: 12px 12px;
}
.select2-selection__rendered {
  resize: both;
  overflow: auto;
  padding: 4px;
  min-width: 200px;
  min-height: 40px;
  display: inline-block;
  border: 1px solid #ccc;
  border-radius: 4px;
  height: 55px;
}

.select2-container {
  width: 100% !important;
}

/* Loading Modal Overlay */
.loading-modal-overlay {
    display: none;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
    z-index: 999999 !important;
    justify-content: center !important;
    align-items: center !important;
    margin: 0 !important;
    padding: 0 !important;
}
.loading-modal-overlay.show {
    display: flex !important;
}
.loading-modal-content {
    position: relative !important;
    background: linear-gradient(145deg, #ffffff, #f5f7fa);
    padding: 50px 70px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.4);
    max-width: 450px;
    margin: auto !important;
    animation: modalPulse 0.3s ease-out;
}
@keyframes modalPulse {
    from {
        transform: scale(0.9);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}
.loading-modal-content .spinner-icon {
    width: 70px;
    height: 70px;
    border: 5px solid #e8e8e8;
    border-top: 5px solid #22c55e;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 25px auto;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.loading-modal-content h4 {
    margin: 0 0 12px 0;
    font-size: 22px;
    font-weight: 600;
    color: #1a1a2e;
}
.loading-modal-content p {
    margin: 0;
    color: #6b7280;
    font-size: 15px;
    line-height: 1.6;
}
.loading-modal-content .record-count {
    display: inline-block;
    background: #22c55e;
    color: #fff;
    padding: 8px 20px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    margin-top: 15px;
}
.loading-modal-content .result-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px auto;
    font-size: 36px;
}
.loading-modal-content .result-icon.success {
    background: #dcfce7;
    color: #22c55e;
}
.loading-modal-content .result-icon.error {
    background: #fee2e2;
    color: #ef4444;
}
.loading-modal-content .close-btn {
    display: inline-block;
    background: #22c55e;
    color: #fff;
    padding: 12px 40px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    margin-top: 25px;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}
.loading-modal-content .close-btn:hover {
    background: #16a34a;
    transform: translateY(-2px);
}
.loading-modal-content .close-btn.error {
    background: #ef4444;
}
.loading-modal-content .close-btn.error:hover {
    background: #dc2626;
}
.loading-modal-content .confirm-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px auto;
    font-size: 36px;
    background: #fef3c7;
    color: #f59e0b;
}
.loading-modal-content .btn-group {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 25px;
}
.loading-modal-content .confirm-btn {
    padding: 12px 35px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}
.loading-modal-content .confirm-btn.yes {
    background: #22c55e;
    color: #fff;
}
.loading-modal-content .confirm-btn.yes:hover {
    background: #16a34a;
    transform: translateY(-2px);
}
.loading-modal-content .confirm-btn.no {
    background: #e5e7eb;
    color: #374151;
}
.loading-modal-content .confirm-btn.no:hover {
    background: #d1d5db;
    transform: translateY(-2px);
}

.enrichment-toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: linear-gradient(145deg, #ffffff, #f8fafc);
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    padding: 16px 20px;
    min-width: 320px;
    max-width: 400px;
    z-index: 99999;
    border-left: 4px solid #22c55e;
    animation: slideIn 0.3s ease-out;
}
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
.enrichment-toast.hiding {
    animation: slideOut 0.3s ease-in forwards;
}
@keyframes slideOut {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}
.enrichment-toast .toast-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.enrichment-toast .toast-title {
    font-weight: 600;
    font-size: 14px;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 8px;
}
.enrichment-toast .toast-title i {
    color: #22c55e;
}
.enrichment-toast .toast-close {
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    font-size: 16px;
    padding: 0;
    line-height: 1;
}
.enrichment-toast .toast-close:hover {
    color: #6b7280;
}
.enrichment-toast .toast-body {
    font-size: 13px;
    color: #4b5563;
    margin-bottom: 12px;
}
.enrichment-toast .toast-event {
    font-weight: 500;
    color: #1f2937;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.enrichment-toast .toast-progress-bar {
    background: #e5e7eb;
    border-radius: 6px;
    height: 6px;
    overflow: hidden;
    margin-bottom: 8px;
}
.enrichment-toast .toast-progress-fill {
    background: linear-gradient(90deg, #22c55e, #4ade80);
    height: 100%;
    transition: width 0.3s ease;
}
.enrichment-toast .toast-stats {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #6b7280;
}
.enrichment-toast .toast-stats .success { color: #22c55e; }
.enrichment-toast .toast-stats .fail { color: #ef4444; }

  .dropdown {
    position: relative;
    display: inline-block;
    }

    .dropdown-content {
     display: none;
     position: absolute;
     top: 100%;          /* ให้อยู่ใต้ปุ่ม */
     right: 0;           /* ชิดขอบขวาของปุ่ม */
     background-color: #f1f1f1;
     min-width: 160px;
     z-index: 1;
     box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
     border-radius: 6px;
     overflow: hidden;
    }

    .dropdown-content button {
     width: 100%;
     padding: 10px;
     background: none;
     border: none;
     text-align: left;
     cursor: pointer;
     margin: 0;                /* เอา margin ที่ดันออกไปทางขวาออก */
    }

    .dropdown:hover .dropdown-content {
     display: block;
     }

    .dropdown-content button:hover {
    background-color: #ddd;
    }

    .btn-custom {
        background-color: #ffffffff; 
        border: #3869d4 solid 0.7px;
        color: #3869d4;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        cursor: pointer;
        border-radius: 4px;
        transition-duration: 0.3s;
    }

    .btn-custom:hover {
        background-color: #3869d4; 
        color: #ffffffff;
        border: #3869d4 solid 0.7px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        transition-duration: 0.3s;
    }

    .btn-custom-green {
        background-color: #ffffffff; 
        border: #22c55e solid 0.7px;
        color: #22c55e;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        cursor: pointer;
        border-radius: 4px;
        transition-duration: 0.3s;
    }

    .btn-custom-green:hover {
        background-color: #22c55e; 
        color: #ffffffff;
        border: #22c55e solid 0.7px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        transition-duration: 0.3s;
    }

    /* Custom card stats - ให้ทั้งสองฝั่งเท่ากัน */
    .card-ev-body-custom {
        display: flex;
        align-items: stretch;
    }

    .ev-stat-box {
        flex: 1;
        padding: 0.5rem 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .ev-stat-box:first-child {
        border-right: 1px solid #eee;
    }

    .ev-stat-box .ev-number {
        font-size: 38px;
        font-weight: 700;
        line-height: 1.2;
    }

    .ev-stat-box .ev-number.cl-orange {
        color: #f59e0b;
    }

    .ev-stat-box .ev-label {
        font-size: 18px;
        font-weight: 600;
        margin-top: 0.25rem;
    }

    .dataTables_processing {
        display: none !important;
    }
    

</style>
    <section id="content" class="bg">
        <section class="vbox">

            <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
                <div class="header-flex-overflow m-t-10">
                    <div class="fwb-16">
                        <span>
                            Events
                        </span>
                    </div>
                    <div class="ml-2 text-right">
                        {{-- <a id="to_top" href="#area_search" class="">test</a> --}}
                        {{-- <div class="text-left" style="min-width: 270px;display:inline-block">
                    <select name="site" id="site" class="select2-option form-control select-site" style="min-width: 270px;">
                        <option value="">All Site</option>
                        @if ($SiteSettings)
                        @foreach ($SiteSettings as $SiteSettings_val)
                        <option value="{{$SiteSettings_val->code}}">{{$SiteSettings_val->name}}</option>
                        @endforeach
                        @endif
                    </select>
                </div> --}}

                        <a id="" href="" class="btn btn-sm btn-info d-none">
                            <span data-rel="tooltip" title="Setting Format Log" data-placement="bottom"><i
                                    class="fas fa-eye icon"></i><span class="hide-text">Setting Format Log</span></span>
                        </a>
                        @if (!empty(get_role_custom()))
                            @if (@get_role_custom()['client'] != 1)
                                <a id="" href="{{ url('/monitoring/send_logs') }}?type=indicator"
                                    class="btn btn-sm btn-info">
                                    <span data-rel="tooltip" title="View Send Log" data-placement="bottom"><i
                                            class="fas fa-eye"></i><span class="hide-text">View Send Log</span></span>
                                </a>
                            @endif
                        @endif


                        <a id="advance-search" href="#area_search" class="btn btn-sm btn-{{ get_option('theme_color') }}">
                            <span data-rel="tooltip" title="Filter" data-placement="bottom"><i
                                    class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></span>
                        </a>

                        


                    </div>
                </div>
            </header>

            <section class="scrollable wrapper">
                <section id="hide-advance-search" class="panel panel-default" style="display: none;">
                    {{-- <div class="panel-heading">
                        <a class="text-primary" href="{{ route('indicators.events') }}">Events</a>
                        |
                        <a href="{{ route('indicators.attributes') }}" class="text-muted">Attributes</a>
                    </div> --}}
                    <header class="panel-heading font-bold panel-header-blue">
                        <div class="row">
                            <div class="col-md-12">
                                <i class="fas fa-filter"></i> Filter
                            </div>
                    </header>
                    <div class="panel-body" style="padding: 0 !important">
                        <div class="container-fluid" style="padding: 2rem;">
                            <div class="row">
                                <div class="col-lg-3 mb-1">
                                    <h5 class="font-weight-bold">Event Name</h5>
                                    <input type="text" class="form-control" name="event_name" id="event_name"
                                        placeholder="Search">
                                </div>
                                <div class="col-lg-3 mb-1">
                                    <h5 class="font-weight-bold">Keyword</h5>
                                    <input type="text" class="form-control" name="keyword_search" id="keyword_search"
                                        placeholder="Search">
                                </div>
                                <!--<div class="col-md-4">
                                                            <div class="form-group">
                                                            <label for="" class="">Group</label>                                                                                                                                                                                                                                                                                                                                                                                                                                                               {{-- <select name="group[]" id="type" class="select2-option form-control"
                                    multiple="multiple">
                                 </select> --}}                                                                                                                                                                                                                                                                                                                                                                                                                               </div>-->
                                <div class="col-lg-3 mb-1">
                                    <h5 class="font-weight-bold">Date</h5>
                                    <div id="event_date" class="text-center form-control"
                                        style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                        &nbsp;
                                        <span>Select date range</span> 
                                    </div>
                                </div>
                                <div class="col-lg-3 mb-1">
                                    <h5 class="font-weight-bold">Published</h5>
                                    <div id="groupby-published" class="btn-group special">
                                        <button class="btn btn-grey check_published  active" id="all" value="">
                                            <span>All</span>
                                        </button>
                                        <button class="btn btn-grey check_published" value="1">
                                            <span>Published</span>
                                        </button>
                                        <button class="btn btn-grey check_published" value="2">
                                            <span> UnPublished </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button class="btn btn-info" id="btn_search_data">
                                    <i class="fas fa-search btn-fz-13"></i>
                                    <span> @langapp('apply') </span>
                                </button>
                                <button class="btn btn-default btn-fz-13" id="btn_reset">
                                    <i class=" fas fa-broom"></i>
                                    <span> Clear </span>
                                </button>
                                <button class="btn btn-default btn-fz-13" id="close_filter">
                                    <i class=" fas fa-times"></i>
                                    <span> Close </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="container-fluid" style="margin-bottom:10px;">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="row">
                                <div class="col-lg-12 nopadding">
                                    <div class="card-ev">
                                        <div class="header-ev">
                                            Events
                                        </div>
                                        <div class="card-ev-body-custom">
                                            <div class="ev-stat-box">
                                                <span class="ev-number cl-orange">{{ @number_format(TYPE_WEB == 'center' ? $attr_current->event_count : $attr_current['event_count']) }}</span>
                                                <span class="ev-label">New Event</span>
                                            </div>
                                            <div class="ev-stat-box">
                                                <span class="ev-number">{{ @number_format(TYPE_WEB == 'center' ? $attr_all->event_count : $attr_all['event_count']) }}</span>
                                                <span class="ev-label">All</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12 nopadding">
                                    <div class="card-ev">
                                        <div class="header-ev">
                                            Attribute
                                        </div>
                                        <div class="card-ev-body-custom">
                                            <div class="ev-stat-box">
                                                <span class="ev-number cl-orange">{{ @number_format(TYPE_WEB == 'center' ? $attr_current->attribute_count : $attr_current['attribute_count']) }}</span>
                                                <span class="ev-label">New Attribute</span>
                                            </div>
                                            <div class="ev-stat-box">
                                                <span class="ev-number">{{ @number_format(TYPE_WEB == 'center' ? $attr_all->attribute_count : $attr_all['attribute_count']) }}</span>
                                                <span class="ev-label">All</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8 nopadding">
                            <div class="" style="background: #fff">
                                <span class="header-txt-chart">Top 10 Attribute Type</span>
                                <div id="chart-pack" style="height: 251px"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tabbable">
                    <ul class="nav nav-tabs nav-tabs-highlight">
                        <li class="active">
                            <a href="#tab_event" data-toggle="tab">Event</a>
                        </li>
                        <li style="display: none;"><a href="#tab_summary_type" data-toggle="tab">Summary Type</a>
                        </li>
                        {{-- <li><a href="#tab_otx" data-toggle="tab">OTX (0)</a></li>   
                <li><a href="#tab_misp" data-toggle="tab">MISP (0)</a></li>    --}}
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_event">

                            <section class="panel panel-default">
                                <header class="panel-heading font-bold panel-header-blue">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <i class="fas fa-table"></i> Table Event
                                        </div>
                                    </div>
                                </header>


                                <div class="panel-body">

                                
                                        <!-- <input type="file" id="fileInput" accept=".csv" style="display: none;"> -->
                                        <div class="row" style="text-align: left;">
                                            <!-- <div id="loader" style="display:none; color: green;">⏳ Computing...</div> -->
                                            <!-- <div class="dropdown">
                                                <button class="btn btn-success">Actions <i class="fa fa-chevron-circle-down"></i></button>
                                                <div class="dropdown-content">
                                                <button onclick="exportCSV(this)" style="text-align: left;" value="1"><i class="fa fa-download"></i>  Export Event</button>
                                                <button onclick="exportCSV(this)" style="text-align: left;" value="2"><i class="fa fa-download"></i>  Export Event & Attributes</button>
                                                <button onclick="openModal()" style="text-align: left;"><i class="fa fa-upload"></i>  Import CSV</button>
                                                </div>
                                            </div> -->

                                               <!-- <select name="action" id="action-event" class="form-control w-100 custom-select">
                                                <option value="0">Actions</option>
                                                <option value="1">Export Event</option>
                                                <option value="2">Event & Attributes</option>
                                                <option value="3">Import CSV</option>
                                            </select> -->

                                            
                                            <div class="col-lg-12" style="text-align: left; overflow: visible;">
                                                <button onclick="exportCSV(this)" class="btn btn-custom" style="text-align: left;" value="1">
                                                    <i class="fa fa-arrow-circle-down"></i>  Event
                                                </button>
                                                <button onclick="exportCSV(this)" class="btn btn-custom" style="text-align: left;" value="2">
                                                    <i class="fa fa-arrow-circle-down"></i>  Event & Attributes
                                                </button>
                                                <button onclick="openModal()" class="btn btn-custom" style="text-align: left;">
                                                    <i class="fa fa-arrow-circle-up"></i>  Import CSV
                                                </button>
                                                <button onclick="showBulkIocInfo()" class="btn btn-custom-green" style="text-align: left;">
                                                    <i class="fas fa-atom"></i> Enrichment
                                                </button>
                                            </div>
                                        </div>

                                        

                                    <div class="table-responsive">
                                        <table class="table table-striped" id="table_events">
                                            <thead>
                                                <tr>
                                                    <th>

                                                    <label>
                                                        <b>      </b>  
                                                    </th>
                                                    
                                                    <th>Industries</th>
                                                    <th>Event Name</th>
                                                    <th>Creator org</th>
                                            
                                                    <th>Tags</th>
                                                    <th>Group</th>
                                                    <th style="width: 270px;">Actor / Campainge</th>
                                                    <th>Published</th>
                                                    <th>Last Status</th>
                                                    <th class="nowrap">Modified DateTime</th>
                                                    <th>Attribute</th>
                                                    <th>Action</th>

                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                        <div id="showing_amount_text" class="pull-left"
                                            style="margin-top: 5px; margin-left: 15px;">
                                        </div>
                                        <div class="pull-right" style="padding-right: 10px;" id="pagination_custom">
                                        </div>
                                    </div>

                                </div>
                            </section>

                        </div>
                        <div class="tab-pane" id="tab_summary_type">

                            <section class="panel panel-default">
                                <header class="panel-heading font-bold panel-header-blue">
                                    <div class="row">
                                        <div class="col-xs-8">
                                            <i class="fas fa-table"></i> Table Summary Type
                                        </div>
                                        <div class="col-xs-4" id='lastdate' style="text-align: right">
                                        </div>
                                    </div>
                                </header>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped vt-top" id="table_summary" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Year</th>
                                                    <th>Month</th>
                                                    <th>Attribute Type</th>
                                                    <th>Count</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </section>
        </section>

        <div id="progressModal" class="modal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content text-center p-3">
            <h5 class="mb-2">Importing...</h5>
            <div class="progress mb-2" style="height: 20px;">
                <div id="importProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                    role="progressbar" style="width: 0%">0%</div>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="$('#progressModal').modal('hide')">Hide</button>
            </div>
        </div>
        </div>


            <div class="modal" id="import-modal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                    <div class="modal-header bg-blue">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true" class="text-white">&times;</span>
                        </button>
                        <h4 class="modal-title text-white" id="exampleModalLabel">Import File</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-6">
                                
                                <div class="radio-group">
                                    <label><strong>Select Type Of Import</strong></label><br>
                                    <label>
                                        <input type="radio" class="ratio-import" name="import_type" value="event" checked> Events
                                    </label>

                                    <label>
                                        <input type="radio" class="ratio-import" name="import_type" value="attribute"> Attribute
                                    </label>
                                    </div>

                                    <style>
                                    input[type="radio"] {
                                    all: unset; 
                                    appearance: auto;
                                    -webkit-appearance: radio;
                                    display: inline-block;
                                    width: 16px;
                                    height: 16px;
                                    margin-right: 6px;
                                    vertical-align: middle;
                                }

                                .radio-group label {
                                    display: inline-flex;
                                    align-items: center;
                                    font-size: 16px;
                                    margin-right: 20px;
                                    cursor: pointer;
                                }
                                    .dropzone {
                                    border: 2px dashed #ccc;
                                    border-radius: 5px;
                                    padding: 30px;
                                    cursor: pointer;
                                    height: auto;
                                    text-align: center;
                                    }

                                    </style>

                            
                            </div>
                            </div> 
                            <br>
                            <div id="body_detail" class="mb-3">
                                <label for="fileInput" class="form-label"><i class="fa fa-file"></i> Choose or Drop file to import :</label>
                                <input class="form-control dropzone" type="file" id="fileInput" style="border-radius: 7px;" accept=".csv" style="border-radius: 7px; height: 70px; font-size: 16px; padding: 10px;">
                            </div>
                           
                        
                    </div> 


                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" id="btnClose" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                        </button>
                        <button type="button" class="btn btn-info" onclick="importCSV()">
                        <i class="fas fa-paper-plane"></i> Import
                        </button>
                    </div>
                    </div>
                </div>
            </div>


        <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
    </section>

    @push('pagestyle')
        @include('stacks.css.datatables')
        @include('stacks.css.form')
        @include('stacks.css.datepicker')
        @include('stacks.css.highchart')
        <link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
    @endpush

    @push('pagescript')
        @include('stacks.js.datatables')
        @include('stacks.js.form')
        @include('stacks.js.datepicker')
        @include('stacks.js.highchart')
        @include('stacks.js.daterangpicker')
        @include('stacks.js.advanced_search')
        <script src="{{ getAsset('plugins/Highcharts-Stock/code/modules/timeline.js') }}"></script>
        @include('stacks.js.activebutton')

        <script>
            $('#fillter_click .btn-selector').on('click', function() {
                $(this).siblings().removeClass('active');
                $(this).addClass('active');
            });

            active_btn('#fillter_click_group .btn-selector');
            active_btn('#groupby-published .btn-grey');

            Highcharts.setOptions({
                lang: {
                    decimalPoint: '.',
                    thousandsSep: ','
                }
            });

            $(".sl_group").select2({
                placeholder: "Select",
                allowClear: true,
                minimumResultsForSearch: Infinity,
                customClass: "Myselectbox",
            });

            $('#industries_box').hide();
            $('#group_box').hide();

            $(".sl_group").on('change', function() {
                if ($(this).val() == '1') {
                    $('#industries_box').show();
                    $('#group_box').hide();
                } else if ($(this).val() == '2') {
                    $('#group_box').show();
                    $('#industries_box').hide();
                } else {
                    $('#industries_box').hide();
                    $('#group_box').hide();
                }
            });

            $('.select2-option').select2();

            var start_date = '';
            var end_date = '';
            var f_search = 1;
            var event_name = '';
            var count_page = -1;
            var isDateSearch = 0;
            var datatable = [];
            var check_published = null;
            var industries = "";
            var group = "";

            $(".check_published").click(function() {
                check_published = $(this).val();

            });

            $(function() {
                load_industries();
                load_group();
                var chart = new Highcharts.chart('chart-pack', {
                    chart: {
                        type: 'bar',
                        height: '251px'
                    },
                    title: {
                        text: null
                    },
                    xAxis: {
                        categories: ['Attribute']
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: null
                        }
                    },
                    legend: {
                        reversed: true,
                        itemMarginTop: 5,
                    },
                    plotOptions: {
                        series: {
                            stacking: 'normal'
                        }
                    },
                    series: load_graph()
                });

                var start = moment().startOf('day'); 
                var end = moment().endOf('day');    


                let first_load = true;

                function cb(start, end) {
                    if (first_load) {
                        $('#event_date span').html('Please select date range');
                        startDate = start;
                        endDate = end;
                        first_load = false;
                        return;
                    } else {
                        if (!start) {
                            $('#event_date span').html('Please select date range');
                            startDate = moment().startOf('day');
                            endDate = moment().endOf('day');
                        } else {
                            $('#event_date span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                        startDate = start;
                        endDate = end;
                        }
                    }
                }

                $('#event_date').daterangepicker({
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
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                            'month').endOf('month')]
                    }
                }, cb);
                $('#event_date').on('apply.daterangepicker', function(ev, picker) {
                    isDateSearch = 1;
                    if (!picker.startDate.isValid() || !picker.endDate.isValid()) {

                    }
                });

                cb(start, end);

                $("#btn_search_data").click(function() {
                    {{-- console.log(startDate.format('YYYY-MM-DD hh:mm A')); --}}



                    start_date = startDate;
                    end_date = endDate;
                    event_name = $("#event_name").val();
                    keyword_search = $("#keyword_search").val();


                    search_table(1);
                });


                $("#btn_reset").click(function() {
                    $("#event_name").val('');
                    check_published = null;
                    $(".btn-grey").removeClass("active");
                    $("#all").addClass("active");
                    $("#keyword_search").val('');
                     clearDateToEmpty();
                    load_table(1);
                });
                
                function clearDateToEmpty() {
                    const $el = $('#event_date');
                    const picker = $el.data('daterangepicker');

                    $el.find('span').text('Please select date range');
                    if ($el.is('input')) { $el.val(''); }    
                    startDate = null;
                    endDate   = null;
                    const s = moment().startOf('day');
                    const e = moment().endOf('day');
                    picker.setStartDate(s);
                    picker.setEndDate(e);
                    picker.chosenLabel = undefined;    
                    picker.updateView();
                    picker.updateCalendars();
                    }



            });

            $(function() {

                if ({!! json_encode($Search_Link_All) !!} === "") {
                    load_table(1);
                } else {
                    event_name = {!! json_encode($Search_Link_All) !!};
                    search_table(1);
                }


            });

            function convertToCSV(arr) {
                var array = [Object.keys(arr[0])].concat(arr);

                return array.map(it => {
                    return Object.values(it).toString()
                }).join('\n');
            }


            var table_summary = $("#table_summary").DataTable({
                dom: '<"#bse.button_summary_export"B>rtip',
                order: [
                    [0, 'desc']
                ],
                buttons: [{
                    extend: 'csv',
                    text: '<i class="fas fa-download"></i> CSV',
                    action: function(e, dt, node, config) {
                        var arrmin = $('#rangepickermin').val().split("/");
                        var arrmax = $('#rangepickermax').val().split("/");
                        var a = document.querySelector('td');

                        if (!a.classList.contains("dataTables_empty")) {
                            $.ajax({
                                type: "POST",
                                url: "{{ route('indicators.table_summary_export') }}",
                                data: {
                                    minmonth: arrmin[0],
                                    maxmonth: arrmax[0],
                                    minyear: arrmin[1],
                                    maxyear: arrmax[1]
                                },
                                success: function(res) {
                                    console.log(res);
                                    if (res.length != 0) {
                                        var csv = convertToCSV(res);
                                        var csvContent = "data:text/csv;charset=utf-8," + csv;
                                        var encodedUri = encodeURI(csvContent);
                                        var link = document.createElement("a");
                                        link.setAttribute("href", encodedUri);
                                        link.setAttribute("download",
                                            "Indicators-Summary Type.csv");
                                        document.body.appendChild(link);
                                        link.click();
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'เเจ้งเตือน',
                                            text: 'ไม่มีข้อมูล',
                                        })
                                    }
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เเจ้งเตือน',
                                text: 'ไม่มีข้อมูล',
                            })
                        }
                    }
                }],
                ajax: {
                    url: "{{ route('indicators.table_summary') }}",
                    type: "GET",
                },
                columns: [{
                        data: 'year',
                    },
                    {
                        data: 'month',
                        render: function(data, type, row) {
                            var arr_months = ['', 'January', 'February', 'March', 'April', 'May', 'June',
                                'July ', 'August', 'September', 'October', 'November', 'December'
                            ];
                            return arr_months[data];
                        }
                    },
                    {
                        data: 'group_industries_name',
                        render: function(data, type, row) {
                            return data.split("\n").join("<br>");
                        }
                    },
                    {
                        data: 'group_sumc',
                        render: function(data, type, row) {
                            return data.split("\n").join("<br>");
                        }
                    },
                ],
                infoCallback: function(settings, start, end, max, total, pre) {
                    if(settings.json){
                        document.getElementById('lastdate').innerHTML="";
                        document.getElementById('lastdate').insertAdjacentText('beforeend',"Latest updated : "+settings.json.dateday);
                    }
                },
            });
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var arrmin = $('#rangepickermin').val().split("/");
                    var arrmax = $('#rangepickermax').val().split("/");
                    var min = parseInt(arrmin[1], 10);
                    var max = parseInt(arrmax[1], 10);
                    var year = parseFloat(data[0]) || 0;
                    if ((isNaN(min) && isNaN(max)) || (isNaN(min) && year <= max) || (min <= year && isNaN(max)) || (min <=
                            year && year <= max)) {
                        return true;
                    }
                    return false;
                }
            );

            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var arr_months = ['', 'January', 'February', 'March', 'April', 'May', 'June',
                        'July ', 'August', 'September', 'October', 'November', 'December'
                    ];
                    var arrmin = $('#rangepickermin').val().split("/");
                    var arrmax = $('#rangepickermax').val().split("/");
                    var min = parseInt(arrmin[0], 10);
                    var max = parseInt(arrmax[0], 10);
                    var month = arr_months.indexOf(data[1]) || 0;
                    if ((isNaN(min) && isNaN(max)) || (isNaN(min) && month <= max) || (min <= month && isNaN(max)) || (
                            min <= month && month <= max)) {
                        return true;
                    }
                    return false;
                }
            );

            $('#bse,#rangepickermin,#rangepickermax').on('change', function() {
                table_summary.draw();
            });

            var a = '<div class="dt-buttons btn-group flex-wrap">' +
                '<label for="" class="fdd">Select Range</label>' +
                '<input id="rangepickermin" class="fdd" name="rangepickermin">' +
                '  -  ' +
                '<input id="rangepickermax" class="fdd" name="rangepickermax">' +
                '</div>';
            document.getElementById('bse').insertAdjacentHTML('beforeend', a);
            var date = new Date();
            document.getElementById("rangepickermin").value = (date.getMonth() + 1) + '/' + date.getFullYear();
            document.getElementById("rangepickermax").value = (date.getMonth() + 1) + '/' + date.getFullYear();

            $('#rangepickermin').datepicker({
                format: 'mm/yyyy',
                startView: "months",
                minViewMode: "months",
                autoclose: true
            });
            $('#rangepickermax').datepicker({
                format: 'mm/yyyy',
                startView: "months",
                minViewMode: "months",
                autoclose: true
            });

            function load_table(page = 1) {
                loading('load');
                
                $('#table_events').DataTable({
                    ordering: true,
                    pageLength: 25,
                    processing: false,
                    serverSide: true,
                    destroy: true,
                    order: [
                        [7, "desc"]
                    ],
                    "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
                    ajax: {
                        type: "POST",
                        url: '{!! route('indicators.events_table') !!}',
                        beforeSend: function() {
                             loading('load');
                        },
                        dataSrc: function(json) {
                            count_page = json.recordsTotal;
                            loading('stop_load');
                            return json.data;
                        },
                        data: function(d) {

                            d.count_page = count_page;
                        },
                        error: function() {
                            loading('stop_load');
                        }
                    },
                    initComplete: function(settings, json) {
                        datatable = json.cursor;
                        $('[data-toggle="tooltip"]').tooltip();
                        loading('stop_load');
                    },
                    "fnDrawCallback": function(oSettings) {

                        $(".c-tags").select2({
                        tags: true,
                                 width: 'resolve',
                                 
                        });
                        $(document).on('change', '.select2-option', function() {
                            const pulseId = $(this).data('plus'); 
                            const selectedValues = $(this).val(); 

                            const selectedString = selectedValues ? selectedValues.join(',') : '';
                            f_change_tags(pulseId,selectedString);

                          
                        });

                       
                        $('.rss_new_id').click(function(){

                            const $btn = $(this);
                            const pulseId = $btn.data('id');
                            const currentStatus = $btn.data('status'); 
                            const newStatus = currentStatus === 1 ? 0 : 1;
                                                    
                            const pulse_id = pulseId;
                            const is_checked = newStatus;
                            $btn.data('status', newStatus)
                            .toggleClass('btn-published btn-unpublished')
                            .text(newStatus === 1 ? 'Published' : 'Unpublished');

                            f_change_publice(pulse_id,is_checked);



                        });



                    },


                    columns: [

                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            className: 'text-center',
                            render: function(data, type, row, meta) {
                                const isChecked = row.public == 0 ? 'checked' : '';
                                    return 
                                       ` <label>
                                            <input type="checkbox"   style="min-width: 200px;" name="checked" class="check_rss_new_id" value="${row.pulse_id}" >
                                            <span class="label-text"></span>
                                        </label>`
                                    ;
                            }
                        }   ,


                        {
                            data: 'industries',
                            "visible": false,
                        },
                        {
                            data: 'name',
                        },
                        {
                            data: 'creator_org',
                        },
                     
                        {
                            data: 'tags',
                            render: function(data, type, row, meta) {
                            
                                const tags_list = (row.tags_list || "")
                                    .split(",")
                                    .map(tag => tag.trim())
                                    .filter(tag => tag !== "");

                                const options = tags_list.map(tag => {
                                    if(tag){
                                        const selected = 'selected';
                                        return `<option value="${tag}" ${selected}>${tag}</option>`;
                                    }
                                
                                }).join('');

                                return `
                                    <select data-plus="${row.pulse_id}" name="tag[]" class="c-tags select2-option form-control" multiple="multiple">
                                        ${options}
                                    </select>
                                `;
                            }
                        },
                        {
                            data: 'groups',
                        },
                        {
                            data: 'actor_and_campainge',
                            "visible": false,
                        },
                        {
                            data: 'public',
                            visible: false,
                        },
                        {
                            data: 'is_modified',
                            "visible": false,
                            orderable: false,
                        },
                        {
                            data: 'modified',
                            className: 'nowrap'
                        },
                        {
                            data: 'attrCount',
                            orderable: false,
                            searchable: false,
                            sortable: false,
                            render: function(data, type, row) {
                                if (typeof data === 'number') {
                                    return data.toLocaleString(); 
                                }
                                return data;
                            }
                        },
                        {
                            data: 'pulse_id',
                            orderable: false,
                            searchable: false,
                            sortable: false,
                     
                        },

                    ],
                    
                    columnDefs: [{
                            targets: 2,
                            render: function(data, type, row) {
                                var inner = '';
                                inner = '<div><a href="{{ route('indicators.events_detail') }}' + '/' +
                                    row
                                    .pulse_id + '">' + row.name + '</a></div>';
                                return inner;
                            }

                        },
                        {
                            targets: 6,
                            render: function(data, type, row) {
                                var inner = ``;
                                const test = row.actor;
                                if (row.count_actor > 0) {
                                    inner = `  
                                    <div>
                                        <strong>Actor : </strong>
                                        <span style="display: inline-flex;align-items: center;">
                            `;
                                    for (let rows in row.actor) {
                                        let array_rows = 1;
                                        const data_actor = row.actor[rows];
                                        inner += `
                                        
                                            `;
                                        if (array_rows == row.count_actor) {
                                            inner += `
                                            <a href="/actor/detail?_id=${data_actor.adversary_name}&mode=cve">
                                                ${data_actor.adversary_name}
                                            </a> 
                            `;
                                        } else {
                                            inner += `      
                                            <a href="/actor/detail?_id=${data_actor.adversary_name}&mode=cve">
                                                ${data_actor.adversary_name}
                                            </a> , 
                            `;
                                        }
                                        array_rows++;

                                    }
                                    inner += `  </span>
                                    </div>
                            `;
                                }
                                if (row.count_camp > 0) {
                                    inner += `
                                <div>
                                    <strong>Campainge : </strong> 
                                    <span> 
                                    `;
                                    let array_row = 1;
                                    for (let rows in row.camp) {
                                        const data_camp = row.camp[rows];
                                        if (array_row == row.count_camp) {
                                            inner += `
                                    <a href="{{ route('actor.campainge_detail') }}` + `?_id=${data_camp.adversary_uuid}&mode=indi">
                                        ${data_camp.adversary_name}
                                    </a>
                            `;
                                        } else {
                                            inner += `
                                    <a href="{{ route('actor.campainge_detail') }}` + `?_id=${data_camp.adversary_uuid}&mode=indi">
                                        ${data_camp.adversary_name}
                                    </a>,  
                                `;
                                        }
                                        array_row++;
                                    }
                                    inner += ` 
                                    </span>
                                </div>
                        `;
                                }
                                return inner;
                            }

                        },
                        {
                            targets: 7,
                            className: 'text-center',
                            render: function(data, type, row) {
                                var inner = '';
                                if (row.public == 1) {
                                    inner = '<i class="fas fa-check text-success"></i>';
                                } else {
                                    inner = '<i class="fas fa-times text-danger"></i>';
                                }
                                return inner;
                            }

                        },
                        {
                            targets: 8,
                            render: function(data, type, row) {
                                var inner = '';
                                if (row.is_modified == true) {
                                    inner = 'Modified';
                                } else {
                                    inner = 'Created';
                                }
                                return inner;
                            }

                        },
                        {
                            targets: 9,
                            render: function(data, type, row) {
                                var inner = '';
                                if (row.modified) {
                                    inner = row.modified;
                                } else {
                                    inner = row.modified;
                                }
                                return inner;
                            }

                        },

                        {
                            targets: 11,
                            className: 'nowrap',
                            render: function(data, type, row) {
                                var inner = '';
                                inner += '<div style="display:flex;flex-direction:column;">';
                                
                                inner += `
                                    <button
                                        class="btn btn-xs btn-ioc-enrichment"
                                        data-id="${row.pulse_id}"
                                        data-count="${row.attrCount || 0}"
                                        onclick="iocEnrichment('${row.pulse_id}', '${(row.name || '').replace(/'/g, "\\'")}', ${row.attrCount || 0})"
                                        style="cursor: pointer; max-width:83px; width:100%; background-color: #22c55e; border: 1px solid #22c55e; color: #fff;"
                                        onmouseover="this.style.backgroundColor='#17ae4eff';"
                                        onmouseout="this.style.backgroundColor='#22c55e';"
                                    >
                                        <i class="fas fa-atom"></i> Enrich
                                    </button>
                                `;

                                @if (!empty(get_role_custom()))
                                    @if (@get_role_custom()['client'] != 1)
                                        inner +=
                                            '<a style="max-width:83px;width:100%;" href="{{ route('indicators.modal_tag') }}' +
                                            '?pulse_id=' + row.pulse_id +
                                            '" data-toggle="ajaxModal" class="m-t-xs btn btn-xs btn-info"><i class="fas fa-plus"></i> Mapping</a>';
                                    @endif
                                @endif

                                inner +=
                                    '<a style="max-width:83px;width:100%;" href="{{ route('indicators.events_detail') }}' +
                                    '/' + row.pulse_id +
                                    '" class="m-t-xs btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>';
                                inner += '</div>';
                                const isPublished = row.public == 1;
                                const buttonText = isPublished ? 'Published' : 'Unpublished';
                                const buttonClass = isPublished ? 'btn-published' : 'btn-unpublished';

                                inner +=  `
                                    <button
                                        class="m-t-xs m-t-xs btn btn-xs rss_new_id btn-toggle-status ${buttonClass}"
                                        data-id="${row.pulse_id}"
                                        data-status="${isPublished ? 1 : 0}"
                                        style=" cursor: pointer;color: #fff;max-width:83px;width:100%;"
                                    >
                                        ${buttonText}
                                    </button>
                                `;
                                return inner;
                            }

                        }

                    ]
                });

            }

            function search_table(page = 1) {
                loading('load');
                
                let startDate = $("#event_date").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
                let endDate = $("#event_date").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');

                console.log(industries);
                $('#table_events').DataTable({
                    searching: false,
                    ordering: true,
                    pageLength: 25,
                    processing: false,
                    serverSide: true,
                    destroy: true,
                    order: [
                        [6, "desc"]
                    ],
                    "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
                    ajax: {
                        type: "POST",
                        url: '{!! route('indicators.events_table') !!}',
                        beforeSend: function() {
                             loading('load');
                        },
                        dataSrc: function(json) {

                            count_page = json.recordsTotal;
                            loading('stop_load');
                            return json.data;
                        },
                        data: function(d) {
                            d.count_page = count_page;
                            d.startDate = startDate;
                            d.endDate = endDate;
                            d.f_search = f_search;
                            d.keywords = event_name;
                            d.isDateSearch = isDateSearch;
                            d.check_published = check_published;
                            d.industries = industries;
                            d.groups = group;
                            d.keyword_search = keyword_search;
                        },
                        error: function() {
                            loading('stop_load');
                        }
                    },
                    initComplete: function(settings, json) {
                        datatable = json.cursor;
                        $('[data-rel="tooltip"]').tooltip();
                        loading('stop_load');
                    },
                    "fnDrawCallback": function(oSettings) {

                        $(".c-tags").select2({
                        tags: true,
                                 width: 'resolve'
                        });
                        $(document).on('change', '.select2-option', function() {
                            const pulseId = $(this).data('plus'); 
                            const selectedValues = $(this).val(); 

                            const selectedString = selectedValues ? selectedValues.join(',') : '';
                            f_change_tags(pulseId,selectedString);

                        
                        });


                        $('.rss_new_id').click(function(){
                            const $btn = $(this);
                            const pulseId = $btn.data('id');
                            const currentStatus = $btn.data('status'); 
                            const newStatus = currentStatus === 1 ? 0 : 1;
                                                    
                            const pulse_id = pulseId;
                            const is_checked = newStatus;
                            $btn.data('status', newStatus)
                            .toggleClass('btn-published btn-unpublished')
                            .text(newStatus === 1 ? 'Published' : 'Unpublished');

                            f_change_publice(pulse_id,is_checked);



                        });



                        },

                    columns: [

                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            className: 'text-center',
                            render: function(data, type, row, meta) {
                                const isChecked = row.public == 0 ? 'checked' : '';
                                    return 
                                      `  <label>
                                            <input type="checkbox"   style="min-width: 200px;" name="checked" class="check_rss_new_id" value="${row.pulse_id}" >
                                            <span class="label-text"></span>
                                        </label>`
                                    ;
                            }
                        }   ,

                        {
                            data: 'industries',
                            "visible": false,
                        },
                        {
                            data: 'name',
                        },
                        {
                            data: 'creator_org',
                        },
                     
                        {
                            data: 'tags',
                            render: function(data, type, row, meta) {
                            
                                const tags_list = (row.tags_list || "")
                                        .split(",")
                                        .map(tag => tag.trim())
                                        .filter(tag => tag !== "");

                                const options = tags_list.map(tag => {
                                    if(tag){
                                        const selected = 'selected';
                                        return `<option value="${tag}" ${selected}>${tag}</option>`;
                                    }
                                
                                }).join('');

                                return `
                                    <select data-plus="${row.pulse_id}" name="tag[]" class="c-tags select2-option form-control" multiple="multiple">
                                        ${options}
                                    </select>
                                `;
                            }
                        },
                        {
                            data: 'groups',
                        },
                        {
                            data: 'actor_and_campainge',
                            "visible": false,
                        },
                        {
                            data: 'public',
                            "visible": false,
                        },
                        {
                            data: 'is_modified',
                            "visible": false,
                        },
                        {
                            data: 'modified',
                            className: 'nowrap'
                        },
                        {
                            data: 'attrCount',
                            orderable: false,
                            searchable: false,
                            sortable: false,
                            render: function(data, type, row) {
                                if (typeof data === 'number') {
                                    return data.toLocaleString(); 
                                }
                                return data;
                            }
                        },
                        {
                            data: 'pulse_id',
                            orderable: false,
                            searchable: false,
                            sortable: false,
                       
                        },

                    ],
                    columnDefs: [{
                            targets: 2,
                            render: function(data, type, row) {
                                var inner = '';
                                inner = '<div><a href="{{ route('indicators.events_detail') }}' + '/' +
                                    row
                                    .pulse_id + '">' + row.name + '</a></div>';
                                return inner;
                            }

                        },
                        {
                            targets: 6,
                            render: function(data, type, row) {
                                var inner = ``;
                                const test = row.actor;
                                if (row.count_actor > 0) {
                                    inner = `  
                                    <div>
                                        <strong>Actor : </strong>
                                        <span style="display: inline-flex;align-items: center;">
                            `;
                                    for (let rows in row.actor) {
                                        let array_rows = 1;
                                        const data_actor = row.actor[rows];
                                        inner += `
                                        
                                            `;
                                        if (array_rows == row.count_actor) {
                                            inner += `
                                            <a href="/actor/detail?_id=${data_actor.adversary_name}&mode=cve">
                                                ${data_actor.adversary_name}
                                            </a> 
                            `;
                                        } else {
                                            inner += `      
                                            <a href="/actor/detail?_id=${data_actor.adversary_name}&mode=cve">
                                                ${data_actor.adversary_name}
                                            </a> , 
                            `;
                                        }
                                        array_rows++;

                                    }
                                    inner += `  </span>
                                    </div>
                            `;
                                }
                                if (row.count_camp > 0) {
                                    inner += `
                                <div>
                                    <strong>Campainge : </strong> 
                                    <span> 
                                    `;
                                    let array_row = 1;
                                    for (let rows in row.camp) {
                                        const data_camp = row.camp[rows];
                                        if (array_row == row.count_camp) {
                                            inner += `${data_camp.adversary_name}`;
                                        } else {
                                            inner += `${data_camp.adversary_name} , `;
                                        }
                                        array_row++;
                                    }
                                    inner += ` 
                                    </span>
                                </div>
                        `;
                                }
                                return inner;
                            }

                        },
                        {
                            targets: 7,
                            className: 'text-center',
                            render: function(data, type, row) {
                                var inner = '';
                                if (row.public == 1) {
                                    inner = '<i class="fas fa-check text-success"></i>';
                                } else {
                                    inner = '<i class="fas fa-times text-danger"></i>';
                                }
                                return inner;
                            }

                        },
                        {
                            targets: 8,
                            render: function(data, type, row) {
                                var inner = '';
                                if (row.is_modified == true) {
                                    inner = 'Modified';
                                } else {
                                    inner = 'Created';
                                }
                                return inner;
                            }

                        },
                        {
                            targets: 9,
                            render: function(data, type, row) {
                                var inner = '';
                                if (row.modified) {
                                    inner = row.modified;
                                } else {
                                    inner = row.modified;
                                }
                                return inner;
                            }

                        },
                        {
                            targets: 11,
                            className: 'nowrap',
                            render: function(data, type, row) {
                                var inner = '';
                                inner += '<div style="display:flex;flex-direction:column;">';
                                inner +=
                                    '<a style="max-width:83px;width:100%;" href="{{ route('indicators.modal_tag') }}' +
                                    '?pulse_id=' + row.pulse_id +
                                    '" data-toggle="ajaxModal" class="btn btn-xs btn-info"><i class="fas fa-plus"></i> Mapping</a>';
                                inner +=
                                    '<a style="max-width:83px;width:100%;" href="{{ route('indicators.events_detail') }}' +
                                    '/' + row.pulse_id +
                                    '" class="m-t-xs btn btn-xs btn-info"><i class="far fa-eye"></i> View</a>';
                                inner += '</div>';

                           
                                const isPublished = row.public == 1;
                                const buttonText = isPublished ? 'Published' : 'Unpublished';
                                const buttonClass = isPublished ? 'btn-published' : 'btn-unpublished';

                              
                                inner +=  `
                                    <button
                                        class="m-t-xs m-t-xs btn btn-xs rss_new_id btn-toggle-status ${buttonClass}"
                                        data-id="${row.pulse_id}"
                                        data-status="${isPublished ? 1 : 0}"
                                        style=" cursor: pointer;color: #fff;max-width:83px;width:100%;"
                                    >
                                        ${buttonText}
                                    </button>
                                `;
                            
                                return inner;
                            }

                        }
                    ]

                });

            }

            function load_graph() {

                var graph = {!! json_encode(@$attr_type) !!};
                return graph;

            }

            function load_industries() {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ route('indicators.industries') }}",
                    type: "get",
                    data: ({}),
                    {{-- datatype: "html", --}}
                    beforeSend: function() {},
                }).done(function(data) {
                    if (data.status_code == "00") {
                        var html = "";
                        html +=
                            '  <a class="btn btn-selector click_industries click_industries_all active" href="javascript:void(0);" onclick="click_industries(\'' +
                            '' + '\');">' + 'All' + '</a>';
                        for (var i = data.data.length - 1; i >= 0; i--) {
                            html +=
                                '  <a class="btn btn-selector click_industries" href="javascript:void(0);" onclick="click_industries(\'' +
                                data.data[i].industries_name + '\');">' + data.data[i].industries_name + '</a>';
                        }
                        $('#btn_industrise').html(html);

                        $('.click_industries').click(function() {
                            $('.click_industries').removeClass('active');
                            $(this).addClass('active');
                        });
                    } else {


                    }

                }).fail(function(jqXHR, ajaxOptions, thrownError) {
                    console.log("No response from server");
                });
            }

            function load_group() {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ route('indicators.indicator_group') }}",
                    type: "get",
                    data: ({}),
                    {{-- datatype: "html", --}}
                    beforeSend: function() {},
                }).done(function(data) {
                    if (data.status_code == "00") {
                        var html = "";
                        html +=
                            '  <a class="btn btn-selector click_group click_group_all active" href="javascript:void(0);" onclick="click_group(\'' +
                            '' + '\');">' + 'All' + '</a>';
                        for (var i = data.data.length - 1; i >= 0; i--) {
                            html +=
                                '  <a class="btn btn-selector click_group" href="javascript:void(0);" onclick="click_group(\'' +
                                data.data[i].industries_name + '\');">' + data.data[i].industries_name + '</a>';
                        }
                        $('#btn_group').html(html);

                        $('.click_group').click(function() {
                            $('.click_group').removeClass('active');
                            $(this).addClass('active');
                        });
                    } else {


                    }

                }).fail(function(jqXHR, ajaxOptions, thrownError) {
                    console.log("No response from server");
                });
            }

            function click_industries(industries_name) {
                $('.click_group').removeClass('active');
                $('.click_group_all').addClass('active');
                group = "";
                industries = industries_name.trim();
                search_table(1);
            }

            function click_group(group_name) {
                $('.click_industries').removeClass('active');
                $('.click_industries_all').addClass('active');
                industries = "";
                group = group_name.trim();
                search_table(1);
            }
            function f_change_publice(pulse_id, is_public) {
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('indicators.indicator_public') }}",
                        type: "POST",
                        data: {
                            pulse_id: pulse_id,
                            is_public: is_public
                        },
                        beforeSend: function () {
                     
                        },
                        success: function (data) {
                            toastr.clear();
                            if (data.status_code == "00") {
                           
                                toastr.success('บันทึกสำเร็จ', 'แจ้งแตือน');
                            } else {
                             
                                toastr.error( 'เกิดข้อผิดพลาด' , 'แจ้งแตือน');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log("เกิดข้อผิดพลาดในการเชื่อมต่อกับ server");
                        }
                    });
             }
             function f_change_tags(pulse_id, tags) {
                toastr.options = {
                    preventDuplicates: true,
                    newestOnTop: true,
                    timeOut: 2000,
                    closeButton: true
                };
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "{{ route('indicators.indicator_update_tags') }}",
                        type: "POST",
                        data: {
                            pulse_id: pulse_id,
                            tags: tags
                        },
                        beforeSend: function () {
                     
                        },
                        
                        success: function (data) {
                            toastr.clear();
                            if (data.status_code == "00") {
                           
                                toastr.success('บันทึกสำเร็จ', 'แจ้งแตือน');
                            } else {
                             
                                toastr.error( 'เกิดข้อผิดพลาด' , 'แจ้งแตือน');
                            }
                        },
                        error: function (xhr, status, error) {
                            console.log("เกิดข้อผิดพลาดในการเชื่อมต่อกับ server");
                        }
                    });
             }
             function clearCsvInput() {
                const inp = document.getElementById('fileInput');
                if (!inp) return;

                inp.value = '';

                const label = inp.closest('.custom-file')?.querySelector('.custom-file-label');
                if (label) label.textContent = 'Choose file';
            }

            document.getElementById('btnClose')?.addEventListener('click', clearCsvInput);


             
        </script>
        <script>
            const exportBaseUrl = "{{ route('indicators.export_events_indicators') }}";
            const importBaseUrl = "{{ route('indicators.importToInsight') }}";
        </script>
        <script src="{{ asset('js/exportandimport.js') }}"></script>
        <script>
            function openModal() {
                $('#import-modal').modal('show');
            }

            window.iocEnrichment = function(pulseId, eventName, indicatorCount) {
                var displayName = eventName || pulseId;
                
                var tableHtml = '<div style="max-height:200px;overflow-y:auto;margin:15px 0;border:1px solid #e5e7eb;border-radius:8px;">';
                tableHtml += '<table style="width:100%;border-collapse:collapse;font-size:13px;">';
                tableHtml += '<thead style="position:sticky;top:0;background:#f3f4f6;"><tr>';
                tableHtml += '<th style="padding:10px;text-align:left;border-bottom:1px solid #e5e7eb;">#</th>';
                tableHtml += '<th style="padding:10px;text-align:left;border-bottom:1px solid #e5e7eb;">Event Name</th>';
                tableHtml += '<th style="padding:10px;text-align:right;border-bottom:1px solid #e5e7eb;">Indicators</th>';
                tableHtml += '</tr></thead><tbody>';
                tableHtml += '<tr style="border-bottom:1px solid #f3f4f6;">';
                tableHtml += '<td style="padding:8px 10px;color:#6b7280;">1</td>';
                tableHtml += '<td style="padding:8px 10px;text-align:left;">' + displayName + '</td>';
                tableHtml += '<td style="padding:8px 10px;text-align:right;color:#22c55e;font-weight:600;">' + (indicatorCount || 0) + '</td>';
                tableHtml += '</tr>';
                tableHtml += '</tbody></table></div>';
                tableHtml += '<div style="text-align:center;margin-bottom:10px;">';
                tableHtml += '<span style="background:#22c55e;color:#fff;padding:5px 15px;border-radius:20px;font-size:13px;">';
                tableHtml += '<i class="fas fa-database"></i> Total: ' + (indicatorCount || 0) + ' indicators</span></div>';
                tableHtml += '<small style="color:#f59e0b;">Note: This may take several minutes.</small>';
                
                Swal.fire({
                    title: 'IOC Enrichment',
                    html: 'Do you want to perform IOC Enrichment for <strong>1 event</strong>?' + tableHtml,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Confirm',
                    cancelButtonText: 'Cancel',
                    width: 650
                }).then((result) => {
                    if (result.isConfirmed) {
                        startEnrichment(pulseId, eventName, indicatorCount);
                    }
                });
            };
            
            function startEnrichment(pulseId, eventName, indicatorCount) {
                var displayName = eventName || pulseId;
                if (displayName.length > 40) displayName = displayName.substring(0, 40) + '...';
                
                var btn = $('button[data-id="' + pulseId + '"].btn-ioc-enrichment');
                var originalText = btn.html();
                
                btn.html('<i class="fas fa-spinner fa-spin"></i> Init...').prop('disabled', true);
                

                showSingleEnrichmentToast(displayName, indicatorCount);
                
                $.ajax({
                    type: "POST",
                    url: "{{ route('indicators.ioc_enrichment') }}",
                    data: { 
                        pulse_id: pulseId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.job_id) {
                            pollSingleJob(res.job_id, btn, originalText, displayName, indicatorCount);
                        } else {
                             hideSingleEnrichmentToast();
                             btn.html(originalText).prop('disabled', false);
                             Swal.fire({
                                icon: 'error',
                                title: 'Failed to start',
                                text: res.message || 'Unknown error'
                             });
                        }
                    },
                    error: function(xhr) {
                         hideSingleEnrichmentToast();
                         btn.html(originalText).prop('disabled', false);
                         Swal.fire({
                            icon: 'error',
                            title: 'Connection Error',
                            text: 'Could not contact server to start job'
                         });
                    }
                });
            }
            
            function showSingleEnrichmentToast(eventName, indicatorCount) {
                if ($('#enrichment-toast').length) {
                    $('#enrichment-toast').remove();
                }
                var toastHtml = `
                    <div id="enrichment-toast" class="enrichment-toast">
                        <div class="toast-header">
                            <div class="toast-title">
                                <i class="fas fa-sync fa-spin"></i>
                                <span>IOC Enrichment</span>
                            </div>
                        </div>
                        <div class="toast-body">
                            <div class="toast-event" id="toast-event-name">${eventName}</div>
                            <div class="toast-status-text" id="toast-status-text" style="font-size:11px;color:#6b7280;margin-top:2px;">Starting job...</div>
                        </div>
                        <div class="toast-progress-bar">
                            <div class="toast-progress-fill" id="toast-progress-fill" style="width: 0%"></div>
                        </div>
                        <div class="toast-stats">
                            <span id="toast-count">0 / ${indicatorCount || '?'} indicators</span>
                            <span><span class="success" id="toast-success">0</span> processed</span>
                        </div>
                    </div>
                `;
                $('body').append(toastHtml);
            }
            
            function updateSingleEnrichmentToast(processed, total, status) {
                var pct = 0;
                if (total > 0 && total !== '?') {
                    pct = Math.round((processed / total) * 100);
                }
                $('#toast-progress-fill').css('width', pct + '%');
                $('#toast-count').text(processed + ' / ' + (total || '?') + ' indicators');
                $('#toast-success').text(processed);
                if (status) {
                    $('#toast-status-text').text(status);
                }
            }
            
            function hideSingleEnrichmentToast() {
                $('#enrichment-toast').addClass('hiding');
                setTimeout(function() {
                    $('#enrichment-toast').remove();
                }, 300);
            }

            function pollSingleJob(jobId, btn, originalText, displayName, indicatorCount) {
                var checkInterval = 2000;
                
                var checkStatus = function() {
                    $.ajax({
                        type: "POST",
                        url: "{{ route('indicators.enrichment_status') }}",
                        data: { 
                            job_id: jobId, 
                            _token: '{{ csrf_token() }}' 
                        },
                        success: function(res) {
                            if (res.status === 'completed') {
                                $('#enrichment-toast .toast-title i').removeClass('fa-spin fa-sync').addClass('fa-check-circle');
                                $('#enrichment-toast').css('border-left-color', '#22c55e');
                                $('#toast-event-name').text('Completed!');
                                $('#toast-status-text').text('Enrichment finished successfully.');
                                $('#toast-progress-fill').css('width', '100%');
                                
                                btn.html('<i class="fas fa-check"></i> Done').removeClass('btn-info').addClass('btn-success');
                                
                                setTimeout(function() {
                                    hideSingleEnrichmentToast();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Enrichment Completed',
                                        html: '<b>' + displayName + '</b><br>Processed: ' + (res.processed_count || 0) + ' indicators',
                                        confirmButtonText: 'Refresh',
                                        allowOutsideClick: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                }, 1000);
                            } else if (res.status === 'failed') {
                                $('#enrichment-toast .toast-title i').removeClass('fa-spin fa-sync').addClass('fa-exclamation-circle');
                                $('#enrichment-toast').css('border-left-color', '#ef4444');
                                $('#toast-event-name').text('Failed');
                                $('#toast-status-text').text(res.error || 'Unknown error');
                                
                                setTimeout(function() {
                                    hideSingleEnrichmentToast();
                                    btn.html(originalText).prop('disabled', false);
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Job Failed',
                                        text: res.error || 'Unknown error during processing'
                                    });
                                }, 1500);
                            } else {
                                var processed = res.processed_count || 0;
                                var total = res.total_indicators || indicatorCount || '?';
                                var pct = 0;
                                if (total > 0 && total !== '?') {
                                     pct = Math.round((processed / total) * 100);
                                     btn.html('<i class="fas fa-spinner fa-spin"></i> ' + pct + '%');
                                } else {
                                     btn.html('<i class="fas fa-spinner fa-spin"></i> ' + processed);
                                }
                                
                                updateSingleEnrichmentToast(processed, total, 'Processing indicators...');
                                
                                setTimeout(checkStatus, checkInterval);
                            }
                        },
                        error: function() {
                            setTimeout(checkStatus, 3000);
                        }
                    });
                };
                
                setTimeout(checkStatus, 1000);
            }
            
            window.showBulkIocInfo = function() {
                var selectedEvents = [];
                $('.check_rss_new_id:checked').each(function() {
                    var pulseId = $(this).val();
                    var row = $('#table_events').DataTable().rows().data().toArray().find(r => r.pulse_id === pulseId);
                    if (row) {
                        selectedEvents.push({
                            pulse_id: pulseId,
                            name: row.name || pulseId,
                            indicator_count: row.attrCount || 0
                        });
                    }
                });
                
                if (selectedEvents.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'No Events Selected',
                        text: 'Please select at least one event by checking the checkbox in the first column of the table.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                
                var totalIndicators = selectedEvents.reduce((sum, e) => sum + (e.indicator_count || 0), 0);
                
                var tableHtml = '<div style="max-height:300px;overflow-y:auto;margin:15px 0;border:1px solid #e5e7eb;border-radius:8px;">';
                tableHtml += '<table style="width:100%;border-collapse:collapse;font-size:13px;">';
                tableHtml += '<thead style="position:sticky;top:0;background:#f3f4f6;"><tr>';
                tableHtml += '<th style="padding:10px;text-align:left;border-bottom:1px solid #e5e7eb;">#</th>';
                tableHtml += '<th style="padding:10px;text-align:left;border-bottom:1px solid #e5e7eb;">Event Name</th>';
                tableHtml += '<th style="padding:10px;text-align:right;border-bottom:1px solid #e5e7eb;">Indicators</th>';
                tableHtml += '</tr></thead><tbody>';
                
                selectedEvents.forEach(function(e, i) {
                    tableHtml += '<tr style="border-bottom:1px solid #f3f4f6;">';
                    tableHtml += '<td style="padding:8px 10px;color:#6b7280;">' + (i + 1) + '</td>';
                    tableHtml += '<td style="padding:8px 10px;text-align:left;">' + e.name + '</td>';
                    tableHtml += '<td style="padding:8px 10px;text-align:right;color:#22c55e;font-weight:600;">' + (e.indicator_count || 0) + '</td>';
                    tableHtml += '</tr>';
                });
                
                tableHtml += '</tbody></table></div>';
                tableHtml += '<div style="text-align:center;margin-bottom:10px;">';
                tableHtml += '<span style="background:#22c55e;color:#fff;padding:5px 15px;border-radius:20px;font-size:13px;">';
                tableHtml += '<i class="fas fa-database"></i> Total: ' + totalIndicators + ' indicators</span></div>';
                tableHtml += '<small style="color:#f59e0b;">Note: This may take several minutes.</small>';
                
                Swal.fire({
                    title: 'IOC Enrichment',
                    html: 'Do you want to perform IOC Enrichment for <strong>' + selectedEvents.length + ' events</strong>?' + tableHtml,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Confirm',
                    cancelButtonText: 'Cancel',
                    width: 650
                }).then((result) => {
                    if (result.isConfirmed) {
                        startBulkEnrichment(selectedEvents);
                    }
                });
            };
            
            function startBulkEnrichment(events) {
                var total = events.length;
                var current = 0;
                var successCount = 0;
                var failCount = 0;
                var results = [];
                
                showEnrichmentToast(total);
                processNext();
                
                function showEnrichmentToast(total) {
                    if ($('#enrichment-toast').length) {
                        $('#enrichment-toast').remove();
                    }
                    var toastHtml = `
                        <div id="enrichment-toast" class="enrichment-toast">
                            <div class="toast-header">
                                <div class="toast-title">
                                    <i class="fas fa-sync fa-spin"></i>
                                    <span>IOC Enrichment</span>
                                </div>
                            </div>
                            <div class="toast-body">
                                <div class="toast-event" id="toast-event-name">Preparing...</div>
                                <div class="toast-status-text" id="toast-status-text" style="font-size:11px;color:#6b7280;margin-top:2px;">Starting job...</div>
                            </div>
                            <div class="toast-progress-bar">
                                <div class="toast-progress-fill" id="toast-progress-fill" style="width: 0%"></div>
                            </div>
                            <div class="toast-stats">
                                <span id="toast-count">0 / ${total} events</span>
                                <span><span class="success" id="toast-success">0</span> done</span>
                            </div>
                        </div>
                    `;
                    $('body').append(toastHtml);
                }
                
                function processNext() {
                    if (current >= total) {
                        if (!$('#enrichment-toast').hasClass('showing-result')) {
                            $('#enrichment-toast').addClass('showing-result');
                            showBulkResult();
                        }
                        return;
                    }
                    
                    var event = events[current];
                    var percent = Math.round((current / total) * 100);
                    var displayName = event.name ? event.name.substring(0, 40) : 'Event';
                    if (event.name && event.name.length > 40) displayName += '...';
                    
                    $('#toast-event-name').text(displayName);
                    $('#toast-status-text').text('Starting background job...');
                    $('#toast-count').text((current + 1) + ' / ' + total + ' events');
                    $('#toast-progress-fill').css('width', percent + '%');
                    $('#toast-success').text(successCount);
                    
                    console.log('Bulk: Starting job for pulse_id:', event.pulse_id);
                    
                    $.ajax({
                        type: "POST",
                        url: "{{ route('indicators.ioc_enrichment') }}",
                        data: { 
                            pulse_id: event.pulse_id,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            if (res.success && res.job_id) {
                                console.log('Job started:', res.job_id);
                                if (res.already_running) {
                                     $('#toast-status-text').text('Job already running, connecting...');
                                } else {
                                     $('#toast-status-text').text('Job queued...');
                                }
                                pollJobStatus(res.job_id, event, displayName);
                            } else if (res.complete) {
                                successCount++;
                                results.push({
                                    name: event.name,
                                    status: 'success',
                                    processed: res.processed_count || 0,
                                    message: 'Completed'
                                });
                                current++;
                                processNext();
                            } else {
                                handleFail(event, res.message || 'Failed to start job');
                            }
                        },
                        error: function(xhr) {
                            var msg = 'Request failed';
                            try {
                                var resp = JSON.parse(xhr.responseText);
                                msg = resp.message || msg;
                            } catch(e) {}
                            handleFail(event, msg);
                        }
                    });
                }
                
                function pollJobStatus(jobId, event, displayName) {
                    var errorCount = 0;
                    
                    var checkStatus = function() {
                        $.ajax({
                            type: "POST",
                            url: "{{ route('indicators.enrichment_status') }}",
                            data: { 
                                job_id: jobId,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                if (!res.success) {
                                    handleFail(event, res.message || 'Job status check failed');
                                    return;
                                }
                                
                                var status = res.status;
                                var processed = res.processed_count || 0;
                                var remaining = res.remaining || 0;
                                
                                var statusText = `Status: ${status} | Processed: ${processed}`;
                                if (remaining > 0) statusText += ` | Remaining: ${remaining}`;
                                $('#toast-status-text').text(statusText);
                                
                                if (status === 'completed') {
                                    successCount++;
                                    results.push({
                                        name: event.name,
                                        status: 'success',
                                        processed: processed,
                                        message: 'Completed'
                                    });
                                    current++;
                                    processNext();
                                } else if (status === 'failed') {
                                    handleFail(event, res.error || 'Job failed');
                                } else {
                                    setTimeout(checkStatus, 2000);
                                }
                            },
                            error: function() {
                                errorCount++;
                                if (errorCount > 5) {
                                    handleFail(event, 'Connection lost to status server');
                                } else {
                                    setTimeout(checkStatus, 2000);
                                }
                            }
                        });
                    };
                    
                    setTimeout(checkStatus, 2000);
                }
                
                function handleFail(event, message) {
                    failCount++;
                    results.push({
                        name: event.name,
                        status: 'error',
                        processed: 0,
                        message: message
                    });
                    current++;
                    processNext();
                }
                
                function showBulkResult() {
                    var isAllSuccess = failCount === 0;
                    var totalProcessed = results.reduce((sum, r) => sum + (r.processed || 0), 0);
                    
                    $('#enrichment-toast .toast-title i').removeClass('fa-spin').addClass(isAllSuccess ? 'fa-check-circle' : 'fa-exclamation-circle');
                    $('#enrichment-toast').css('border-left-color', isAllSuccess ? '#22c55e' : '#f59e0b');
                    $('#toast-event-name').text(isAllSuccess ? 'Completed!' : 'Finished with issues');
                    $('#toast-status-text').text('All tasks finished.');
                    $('#toast-progress-fill').css('width', '100%');
                    $('#toast-count').text(total + ' events processed');
                    $('#toast-success').text(successCount);
                    
                    setTimeout(function() {
                        $('#enrichment-toast').addClass('hiding');
                        setTimeout(function() {
                            $('#enrichment-toast').remove();
                            
                            var summaryHtml = '<div style="text-align:center;">';
                            summaryHtml += '<span style="background:#22c55e;color:#fff;padding:5px 12px;border-radius:20px;font-size:12px;margin-right:8px;"><i class="fas fa-check"></i> ' + successCount + ' success</span>';
                            if (failCount > 0) {
                                summaryHtml += '<span style="background:#ef4444;color:#fff;padding:5px 12px;border-radius:20px;font-size:12px;"><i class="fas fa-times"></i> ' + failCount + ' failed</span>';
                            }
                            summaryHtml += '<br><br><span style="color:#6b7280;font-size:13px;"><i class="fas fa-database"></i> Total: ' + totalProcessed + ' indicators processed</span></div>';
                            
                            if (failCount > 0) {
                                summaryHtml += '<div style="margin-top:15px;max-height:150px;overflow-y:auto;border:1px solid #fecaca;border-radius:8px;background:#fef2f2;padding:10px;">';
                                summaryHtml += '<div style="font-weight:600;color:#dc2626;font-size:12px;margin-bottom:8px;"><i class="fas fa-exclamation-triangle"></i> Failed Events:</div>';
                                results.filter(r => r.status === 'error').forEach(function(r) {
                                    var eventName = r.name ? (r.name.length > 40 ? r.name.substring(0, 40) + '...' : r.name) : 'Unknown';
                                    summaryHtml += '<div style="font-size:11px;color:#7f1d1d;margin:4px 0;padding:4px 8px;background:#fee2e2;border-radius:4px;">';
                                    summaryHtml += '<strong>' + eventName + '</strong>: ' + (r.message || 'Unknown error');
                                    summaryHtml += '</div>';
                                });
                                summaryHtml += '</div>';
                            }
                            
                            Swal.fire({
                                icon: isAllSuccess ? 'success' : 'warning',
                                title: isAllSuccess ? 'IOC Enrichment Completed!' : 'IOC Enrichment Finished',
                                html: summaryHtml,
                                confirmButtonText: 'Close & Refresh',
                                allowOutsideClick: false
                            }).then(() => {
                                location.reload();
                            });
                        }, 300);
                    }, 1500);
                }
            }
            </script>

    @endpush
@endsection
