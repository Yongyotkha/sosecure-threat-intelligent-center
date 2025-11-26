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
                                        <div class="card-ev-body">
                                            <div class="ev-left">
                                                <span>{{ @number_format(TYPE_WEB == 'center' ? $attr_all->event_count : $attr_all['event_count']) }}</span>
                                                <span class="ev-text-sec">All</span>
                                            </div>
                                            <div class="ev-right">
                                                <span
                                                    class="cl-orange">{{ @number_format(TYPE_WEB == 'center' ? $attr_current->event_count : $attr_current['event_count']) }}</span>
                                                <span>New Event</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12 nopadding">
                                    <div class="card-ev">
                                        <div class="header-ev">
                                            Attribute
                                        </div>
                                        <div class="card-ev-body">
                                            <div class="ev-left">
                                                <span>{{ @number_format(TYPE_WEB == 'center' ? $attr_all->attribute_count : $attr_all['attribute_count']) }}</span>
                                                <span class="ev-text-sec">All</span>
                                            </div>
                                            <div class="ev-right">
                                                <span
                                                    class="cl-orange">{{ @number_format(TYPE_WEB == 'center' ? $attr_current->attribute_count : $attr_current['attribute_count']) }}</span>
                                                <span>New Attribute</span>
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
                $('#table_events').DataTable({
                    ordering: true,
                    pageLength: 25,
                    processing: true,
                    serverSide: true,
                    destroy: true,
                    order: [
                        [7, "desc"]
                    ],
                    "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
                    ajax: {
                        type: "POST",
                        url: '{!! route('indicators.events_table') !!}',
                        dataSrc: function(json) {
                            count_page = json.recordsTotal;
                            return json.data;
                        },
                        data: function(d) {

                            d.count_page = count_page;
                        }
                    },
                    initComplete: function(settings, json) {
                        datatable = json.cursor;
                        $('[data-toggle="tooltip"]').tooltip();
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
                                @if (!empty(get_role_custom()))
                                    @if (@get_role_custom()['client'] != 1)
                                        inner +=
                                            '<a style="max-width:83px;width:100%;" href="{{ route('indicators.modal_tag') }}' +
                                            '?pulse_id=' + row.pulse_id +
                                            '" data-toggle="ajaxModal" class="btn btn-xs btn-info"><i class="fas fa-plus"></i> Mapping</a>';
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
                let startDate = $("#event_date").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
                let endDate = $("#event_date").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');

                console.log(industries);
                $('#table_events').DataTable({
                    searching: false,
                    ordering: true,
                    pageLength: 25,
                    processing: true,
                    serverSide: true,
                    destroy: true,
                    order: [
                        [6, "desc"]
                    ],
                    "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
                    ajax: {
                        type: "POST",
                        url: '{!! route('indicators.events_table') !!}',
                        dataSrc: function(json) {

                            count_page = json.recordsTotal;
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
                        }
                    },
                    initComplete: function(settings, json) {
                        datatable = json.cursor;
                        $('[data-rel="tooltip"]').tooltip();
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
            </script>

    @endpush
@endsection
