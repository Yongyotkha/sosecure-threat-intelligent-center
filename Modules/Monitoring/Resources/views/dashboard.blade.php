<style>
    .wdfm-header-upper{

}
</style>
@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        {{-- Head --}}

        <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
            <div class="header-flex-overflow m-t-10">
                <div class="fwb-16">
                    <span>
                        Monitoring > Dashboard
                    </span>
                </div>

                <div class="ml-2 text-right">
                <a href="https://insight.sosecure.co.th/monitoring/darkweb" id="advance-"
                    class="btn btn-sm btn-{{ get_option('theme_color')  }} m-l-xs">
                    <span data-rel="tooltip" title="Filter" data-placement="bottom"> Search Dark Web</span></span>
                </a>
                    {{-- <div class="text-left max-w-select" style="display:inline-block;">
                        <select name="site" id="site" class="select2-option form-control select-site">
                            <option value="">All Site</option>
                            @if($SiteSettings)
                            @foreach($SiteSettings as $SiteSettings_val)
                            <option value="{{$SiteSettings_val->id}}">{{$SiteSettings_val->name}}</option>
                    @endforeach
                    @endif
                    </select>
                </div>

          

                @if(@get_role_custom()['superadmin'] == 1 || @get_role_custom()['client'] == 1)
                <a href="#" id="btn_md_create" class="btn btn-sm btn-{{ get_option('theme_color')  }}"
                    data-toggle="modal" data-target="#wdfm_website">
                    @icon('solid/plus') @langapp('add')
                </a>
                @endif --}}
            </div>
            </div>
        </header>



        <section class="scrollable wrapper">
            {{-- Search --}}
            <section class="panel panel-default" id="hide-advance-search" style="display: none">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-md-12">
                            <i class="fas fa-filter"></i> Filter
                        </div>
                </header>
                <div class="panel-body" style="padding: 0 !important">
                    <div class="container-fluid" style="padding: 2rem;">
                        <div class="row">
                            <div class="col-lg-4 mb-1">
                                <h5 class="font-weight-bold">Name & URL</h5>
                                <input type="text" id="keywords" class="form-control">
                            </div>


                            <div class="col-lg-8 mb-1">
                                <h5 class="font-weight-bold">CVSS</h5>
                                <div id="btngroup_status" class="btn-group special mb-2">
                                    <button type="button" class="btn btn-grey active" onclick="set_level(null);">
                                        <span> All </span>
                                    </button>
                                    <button type="button" class="btn btn-grey" onclick="set_level('High');">
                                        <span> High </span>
                                    </button>
                                    <button type="button" class="btn btn-grey" onclick="set_level('Medium');">
                                        <span> Medium </span>
                                    </button>
                                    <button type="button" class="btn btn-grey" onclick="set_level('Normal');">
                                        <span> Normal </span>
                                    </button>
                                </div>

                                {{-- <h5 class="font-weight-bold">Status</h5>
                                <a href="#" id="all" class="btn-chart d-il-flex mr-3">
                                    <span class="dot-all" style="height:8px;"></span>
                                    All
                                </a>
                                <a href="#" id="high" class="btn-chart d-il-flex mr-3">
                                    <span class="dot critical"></span>
                                    High
                                </a>
                                <a href="#" id="medium" class="btn-chart d-il-flex mr-3">
                                    <span class="dot high"></span>
                                    Medium
                                </a>
                                <a href="#" id="normal" class="btn-chart d-il-flex">
                                    <span class="dot low"></span>
                                    Normal
                                </a> --}}
                            </div>

                            {{-- <div class="col-lg-4">
                                <div class="form-group">
                                    <div class="row d-flex align-items-center">
                                        <label for="" class="col-sm-3 col-xs-12 col-form-label">Status</label>
                                        <div class="col-sm-9 col-xs-12">
                                            <select id="status" class="select2-option form-control"  multiple="multiple">
                                                <option value="critical">Critical</option>
                                                <option value="high">High</option>
                                                <option value="meduim">Meduim</option>
                                                <option value="normal">Normal</option>
                                                <option value="none">None</option>
                                            </select>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}

                            <!--
                            <div class="col-lg-12">
                                <div class="form-group row">
                                    <label for="" class="col-sm-3 col-xs-12 col-form-label">Status</label>
                                    <div class="col-sm-9 col-xs-12">
                                        <select name="" id="datatype" class="select2-option form-control"
                                            multiple="multiple">
                                            <option value="High">High</option>
                                            <option value="Medium">Medium</option>
                                            <option value="Normal">Normal</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            -->

                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-lg-12 text-right">
                            <button type="button" id="btn_news_search" class="btn btn-info btn-responsive btn-fz-13"
                                onclick="search()">
                                <i class="fas fa-search"></i>
                                @langapp('apply')
                            </button>
                            <button type="button" id="btn_news_reset" class="btn btn-default btn-responsive btn-fz-13"
                                style="white-space: nowrap" onclick="clear_search()">
                                <i class="fas fa-broom"></i>
                                <span> Clear </span>
                            </button>
                            <button type="button" id="close_filter" class="btn btn-default btn-responsive btn-fz-13"
                                style="white-space: nowrap">
                                <i class="fas fa-times"></i>
                                <span> Close </span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!--
            <section class="m-b-10">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <div class="row">
                            <div class="col-md-6 col-lg-6 mb-small-5px">
                                {{-- <div class="status_l backdrop-loader">
                                    <div class="loader4 centerloader"></div>
                                    <div class="loadding-text">Loading ...</div>
                                </div> --}}
                                <div class="box-chart-color bg-white">
                                    <div class="d-flex align-items-center header-chart-p">
                                        <img src="{{asset('images/pie-chart.png')}}" alt="" height="30px">
                                        <h1 class="text-blue bold-500">Status</h1>
                                    </div>
                                    <div class="divider-dark"></div>
                                    <div id="status1" class="h-chart"></div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6 mb-small-5px">
                                {{-- <div class="category backdrop-loader">
                                    <div class="loader4 centerloader"></div>
                                    <div class="loadding-text">Loading ...</div>
                                </div> --}}
                                <div class="box-chart-color bg-white">
                                    <div class="d-flex align-items-center header-chart-p">
                                        <img src="{{asset('images/pie-chart.png')}}" alt="" height="30px">
                                        <h1 class="text-blue bold-500">Category</h1>
                                    </div>
                                    <div class="divider-dark"></div>
                                    <div id="category" class="h-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        -->

            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-lg-12 col-sm-12">
                            <i class="fas fa-table"></i> Site - Server
                        </div>
                    </div>
                </header>
                <div class="panel-body" style="background: #f2f2f2;">
                    <div class="wdfm-container" id='data_card'>

                        {{-- <div class="item-wdfm mdasbord-inner">
                            <div class="wdfm-card">
                                <center > 
                                    <div style="width:200px; height:200px;"><img id="preview-image_logo" src="https://insight.sosecure.co.th/images/logo_site/1611069788.png" onerror="setDefaultPic(this)" style="width:100%;height:100%; object-fit:contain;" alt="..."></div>
                                </center>

                                <div class="wdfm-footer start-top" >
                                    <div class="wdfm-ft-left flex">
                                        <div><strong>Site:</strong> Demo</div>
                                        <div class="status-flex"><strong>Status:</strong> &nbsp; <span class="dot low"></span> Online
                                        </div>
                                        <div><strong>Catagory:</strong> Software</div>
                                        <div><strong>Last Online:</strong> 2021-03-05 14:16:33</div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                        {!!$htmlCard!!}
                    </div>

                </div>
              
            </section>


            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-lg-12 col-sm-12">
                            <i class="fas fa-table"></i> Domain -Scan
                        </div>
                    </div>
                </header>
                <div class="panel-body" style="background: #f2f2f2;">
                <div class="table-responsive">
                                            <table  class="table table-striped" id="table-domain-template">
                                                <thead>
                                                    <tr>
                                                        {{-- <th class="hide"></th> --}}
                                                        <th class="no-sort">
                                                            <label>
                                                                <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk" />
                                                                <span class="label-text"></span>
                                                            </label>
                                                        </th>
                                                        {{-- <th class="">No.</th> --}}
                                                        <th>Name</th>
                                                        <th>Domain</th>
                                                        <th>Default</th>
                                                        <th>Started</th>
                                                        <th>Finished</th>
                                                        <th>Elements</th>
                                                        <th>Progress</th>
                                                        {{-- <th>@langapp('status')</th> --}}
                                                        <th class="no-sort" width="10%">@langapp('action')</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    
                                                </tbody>
                                            </table>
                                        </div>
              
            </section>


            
            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-lg-12 col-sm-12">
                            <i class="fas fa-table"></i> RSS Feed  
                        </div>
                    </div>
                </header>
                <div class="panel-body" style="background: #f2f2f2;">
                <div class="table-responsive">
                <table class="table table-striped" id="table-rss-setting-template-rss">
                            <thead>
                                <tr>
                                    <th class="hide"></th>
                                    <th class="no-sort">
                                        <label>
                                            <input name="select_all" value="1" id="select-all" type="checkbox"
                                                class="select-chk" />
                                            <span class="label-text"></span>
                                        </label>
                                    </th>
                                    <th>@langapp('name')</th>
                                    <th>URL</th>
                                    {{-- <th>@langapp('keyword')</th>
                                            <th>Interval (Day)</th>
                                            <th>Start Date Feed</th>
                                            <th>Last Date Feed</th>
                                            <th>Data Feed</th>
                                            <th>Data Error</th> --}}
                                            <th>Last Feel</th>
                                    <th>Last Count</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                                        </div>
              
            </section>

            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-lg-12 col-sm-12">
                            <i class="fas fa-table"></i> Social  Feed  
                        </div>
                    </div>
                </header>
                <div class="panel-body" style="background: #f2f2f2;">
                <div class="table-responsive">
                <table class="table table-striped" id="table-rss-setting-template-social">
                            <thead>
                                <tr>
                                    <th class="hide"></th>
                                    <th class="no-sort">
                                        <label>
                                            <input name="select_all" value="1" id="select-all" type="checkbox"
                                                class="select-chk" />
                                            <span class="label-text"></span>
                                        </label>
                                    </th>
                                    <th>@langapp('name')</th>
                                    <th>URL</th>
                                    {{-- <th>@langapp('keyword')</th>
                                            <th>Interval (Day)</th>
                                            <th>Start Date Feed</th>
                                            <th>Last Date Feed</th>
                                            <th>Data Feed</th>
                                            <th>Data Error</th> --}}
                                            <th>Last Feel</th>
                                    <th>Last Count</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                                        </div>
              
            </section>



        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>


    {{-- <div class="item-wdfm wdfm-inner">
        <div class="wdfm-card">
            <div class="wdfm-header">
                <div class="wdfm-img">
                    <a href="#">
                        <img src="'.asset($key->image_last).'" onerror="setDefaultPic(this)"/>
                    </a>
                </div>
            </div>
            <div class="wdfm-body">
                <div class="wdfm-btn">
                    <a href="'.route('webdefacement.detail',['code' => $key->code]).'" class="btn btn-icon btn-default btn-sm" data-rel="tooltip" title="View" data-placement="bottom">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
                <h4 class="wdfm-elip">'.@$key->name.'</h4>
                <p class="mdfm-text-muted">'.@$key->url.'</p>
            </div>
            <div class="wdfm-footer start-top">
                <div class="wdfm-ft-left flex">
                    <div><strong>Site </strong> : '.@$key->get_site->name.'</div>
                    <div class="status-flex"><strong>Status</strong> : &nbsp; '.get_webdefacment_status($key->status_val,'color').'</div>
                    <div>Hash '.@$key->webdefacment_data_original_last($key->id)->hash.'</div>
                    <div>Filesize '.formatSizeUnits(@$key->webdefacment_data_original_last($key->id)->filesize).'</div>
                    <div>Element '.@$key->webdefacment_data_original_last($key->id)->element.'</div>
                    <div>Image Screen 
                        <a href="'.asset($key->image_last).'" data-lightbox="name-img-2" class="btn btn-info btn-xs"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M569.354 231.631C512.969 135.949 407.81 72 288 72 168.14 72 63.004 135.994 6.646 231.631a47.999 47.999 0 0 0 0 48.739C63.031 376.051 168.19 440 288 440c119.86 0 224.996-63.994 281.354-159.631a47.997 47.997 0 0 0 0-48.738zM288 392c-75.162 0-136-60.827-136-136 0-75.162 60.826-136 136-136 75.162 0 136 60.826 136 136 0 75.162-60.826 136-136 136zm104-136c0 57.438-46.562 104-104 104s-104-46.562-104-104c0-17.708 4.431-34.379 12.236-48.973l-.001.032c0 23.651 19.173 42.823 42.824 42.823s42.824-19.173 42.824-42.823c0-23.651-19.173-42.824-42.824-42.824l-.032.001C253.621 156.431 270.292 152 288 152c57.438 0 104 46.562 104 104z"></path></svg></a>
                    </div>
                    <div class="text-sm-date" style="margin-top:5px;">Last Online: '.@$key->last_online.'</div><!-- 10 second ago -->
                    <div class="text-sm-date">Last Check: '.@$key->last_check.'</div>
                    <div class="text-sm-date">Last Update: '.@$key->updated_at.'</div>
                </div>
           
            </div>
            <div class="wdfm-footer-action">
                <div>
                    <strong>Update Original</strong>
                </div>
                <div class="flex-end">
                    <a href="'.route('webdefacement.detail',['code' => $key->code]).'?site_code='.@$site_code->code.'" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> View</a>
                    <a href="#" onclick="btn_click_edit_webdefacement(\''.$key->code.'\')" class="btn btn-info btn-sm"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.94 74.17l-60.11-60.11c-18.75-18.75-49.16-18.75-67.91 0l-56.55 56.55 128.02 128.02 56.55-56.55c18.75-18.75 18.75-49.15 0-67.91zm-246.8-20.53c-15.62-15.62-40.94-15.62-56.56 0L75.8 172.43c-6.25 6.25-6.25 16.38 0 22.62l22.63 22.63c6.25 6.25 16.38 6.25 22.63 0l101.82-101.82 22.63 22.62L93.95 290.03A327.038 327.038 0 0 0 .17 485.11l-.03.23c-1.7 15.28 11.21 28.2 26.49 26.51a327.02 327.02 0 0 0 195.34-93.8l196.79-196.79-82.77-82.77-84.85-84.85z"></path></svg> Edit</a>
                    <a href="#" onclick="btn_click_del_webdefacement('.$key->id.')" class="btn btn-danger btn-sm btn_del_webdefacment"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M0 84V56c0-13.3 10.7-24 24-24h112l9.4-18.7c4-8.2 12.3-13.3 21.4-13.3h114.3c9.1 0 17.4 5.1 21.5 13.3L312 32h112c13.3 0 24 10.7 24 24v28c0 6.6-5.4 12-12 12H12C5.4 96 0 90.6 0 84zm416 56v324c0 26.5-21.5 48-48 48H80c-26.5 0-48-21.5-48-48V140c0-6.6 5.4-12 12-12h360c6.6 0 12 5.4 12 12zm-272 68c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208zm96 0c0-8.8-7.2-16-16-16s-16 7.2-16 16v224c0 8.8 7.2 16 16 16s16-7.2 16-16V208z"></path></svg> Delete</a>
                </div>
            </div>
        </div>
    </div> --}}


<!-- Modal example -->
<div class="modal in fixed-left" id="modal_example" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-aside" role="document">
        <div class="modal-content">
            <div class="modal-header bg-blue">
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                <h4 class="modal-title text-white">
                    <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                    Name
                </h4>
            </div>
            <form action="">
                <div class="modal-body">
                    
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="submit" class="btn btn-info btn-rounded">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

















    

</section>
<input type="hidden" id="url_id">
<input type="hidden" id="webdefacment_setting_id">

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
@include('stacks.css.highchart')
@include('stacks.css.datepicker')
@include('stacks.css.form')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@include('stacks.css.lightbox')
@endpush

@push('pagescript')
@include('stacks.js.highchart')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.lightbox')
@include('stacks.js.advanced_search')
@include('stacks.js.activebutton')
@include('stacks.js.defaultpic')

<script>
    $(document).ready(function(){
        $('.wdfm-card').hover(function(){
            $(this).find('.wdfm-header').addClass('wdfm-header-upper');
        }); 
        $('.wdfm-card').mouseleave(function(){
            $(this).find('.wdfm-header').removeClass('wdfm-header-upper');
        });
        setInterval(function(){ load_card(); }, 30000);
    });

    Highcharts.chart('status1', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Browser market shares in January, 2018'
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            accessibility: {
                point: {
                valueSuffix: '%'
                }
            },
            plotOptions: {
                pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                }
                }
            },
            series: [{
                name: 'Brands',
                colorByPoint: true,
                data: [{
                name: 'Chrome',
                y: 61.41,
                }, {
                name: 'Internet Explorer',
                y: 11.84
                }, {
                name: 'Firefox',
                y: 10.85
                }, {
                name: 'Edge',
                y: 4.67
                }]
            }]
    });

    Highcharts.chart('category', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Browser market shares in January, 2018'
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            accessibility: {
                point: {
                valueSuffix: '%'
                }
            },
            plotOptions: {
                pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                }
                }
            },
            series: [{
                name: 'Brands',
                colorByPoint: true,
                data: [{
                name: 'Chrome',
                y: 61.41,
                }, {
                name: 'Internet Explorer',
                y: 11.84
                }, {
                name: 'Firefox',
                y: 10.85
                }, {
                name: 'Edge',
                y: 4.67
                }]
            }]
    });



    function load_card(){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{!! route('monitoring.load_card') !!}',
            type: "post",
            datatype: "html",
            beforeSend: function(){
                
            },
        }).done(function(data){
            data.card_data.forEach(function(card_data) {
               
                $(`#data_status_${card_data.code}`).html(`<strong>Status:</strong> &nbsp; <span class="${card_data.statusDotClass}"></span>${card_data.statusDotName}`);
                $(`#data_lastcheck_${card_data.code}`).html(`<strong>Last Online:</strong> ${card_data.transcation_date_start}`);
                if(card_data.MonitoringSystem){
                $(`#data_monitoring_server_${card_data.code}`).html(`<strong>Monitoring:</strong> ${card_data.MonitoringSystem.content}`);
                $(`#data_monitoring_server_date_${card_data.code}`).html(`<strong>Monitoring  Last:</strong> ${card_data.MonitoringSystem.updated_at}`);
                }
            });
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            
            console.log("No response from server");
        });
    }


</script>


<script>
    $(document).ready(function () {
        $('#scan_interval').select2();
    });
    
    $('ul.role-group-sub').hide();
    function openrole(onck,id){
        $('#'+id).slideToggle(150);
    }

    $('#table-domain-template').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {

            $('#btn_del_select').prop("disabled", false);
        } else {
            
            if ($('.select-chk').filter(':checked').length < 1){

                $('#btn_del_select').attr('disabled',true);
            }
        }
    });

    $('#table-domain-template').on('click', '.domain_id', function () {
        if ($(this).is(':checked')) {
            $('#btn_del_select').prop("disabled", false);
            {{--if($('.domain_id').filter(':checked').length >= 5){
                document.getElementById("select-all").checked = true;
            }--}}
        } else {
            document.getElementById("select-all").checked = false;
            if ($('.domain_id').filter(':checked').length < 1){
                $('#btn_del_select').attr('disabled',true);
            }
        }
    });


    $(function () {

        var table = $('#table-domain-template').DataTable({
            processing: true,
            serverSide: true,
            
            "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
            ajax: {
                url: '{!! route('domainsettings.data') !!}',
                data: {
                    "site_code":'{{ Request::segment(3) }}'
                },
                type: "POST",
            },
            order: [
                [0, "desc"]
            ],
            columns: [
                {
                    data: 'chk',
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    className: 'w-10',
                    "visible": false
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'domain',
                    name: 'domain'
                },
                {
                    data: 'domain_default',
                    name: 'domain_default',
                    className: 'w-10 text-center'
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    className: 'text-center w100px no-wrap'
                },
                {
                    data: 'completed_date',
                    name: 'completed_date',
                    className: 'text-center w100px no-wrap'
                },
                {
                    data: 'elements',
                    name: 'elements',
                    className: 'w-10 text-center'
                },
                {
                    data: 'progress',
                    name: 'progress',
                    className: 'w100px'
                },
                {
                    data: 'action',
                    name: 'action',
                    className: 'no-wrap',
                    "visible": false
                },
                
            ]
        });




    });
    let del_domain_select = [];
    function delete_domain_select(){
        del_domain_select = [];
        $('#delete_domain_modal').modal('show');
    }

    function delete_domain_select_confirm(){
        
        $("input[type='checkbox'][name='checked']").each(function(){
            if($(this).is(":checked")) {
                del_domain_select.push($(this).val());
            }
        });
        console.log(del_domain_select);
        $.ajax({
            type:"POST",
            url:"{{ route('domainsettings.del_domain_select') }}",
            data:{
                id:del_domain_select
            },
            beforeSend: function(){
                $('.delete_domain_submit').html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            },
            success:function(response) {
                $('.delete_domain_submit').html('<i class="fas fa-check"></i> @langapp('save') </span>');
                toastr.success(response.message, '@langapp('response_status')');
                window.location.href = response.redirect;
            },
            error: function (error){
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status') ');
            }
        });
    }

    function del_cate_select(id) {
        axios.post('{{ route('domainsettings.bulk.delete') }}', {checked: id})
        .then(function (response) {
            toastr.warning(response.data.message, '@langapp('response_status')');
            window.location.href = response.data.redirect;
        })
        .catch(function (error) {
            var errors = error.response.data.errors;
            var errorsHtml = '';
            $.each(errors, function (key, value) {
                errorsHtml += '<li>' + value[0] + '</li>';
            });
            toastr.error(errorsHtml, '@langapp('response_status') ');
        });
    }


    function change_category_active (id) {
        $.ajax({
            type:"POST",
            url:"{{ route('domainsettings.change_status') }}",
            data:{id:id},
            beforeSend: function(){
            },
            success:function(response) {
                console.log(response);

                toastr.warning(response.data.message, '@langapp('response_status')');
                window.location.href = response.data.redirect;
            
            },
            error: function (error){
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status') ');
            }
        });
    }


    function change_domain_active(code) {
        let checkState = $("#domain_active_" + code).is(":checked") ? 1 : 0;
        axios.post('{{route('domainsettings.change_status')}}', {
            active: checkState,
            code: code,
        }).then(function (response) {
            toastr.success(response.data.message, '@langapp('response_status')');
            window.location.href = response.data.redirect;
        }).catch(function (error) {
            var errors = error.response.data.errors;
            var errorsHtml = "";
            $.each(errors, function (key, value) {
                errorsHtml += "<li>" + value[0] + "</li>";
            });
            toastr.error(errorsHtml, '@langapp('response_status')');
        });
    }



</script>



<script>
    $(document).ready(function () {
        $('#keywords').select2({
            tags: true,
            tokenSeparators: [' ']
        });

        $('#interval').select2({
            tags: true,
            tokenSeparators: [' ']
        });

        $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') });

        $('#show_end_exp_date').hide();

        $('#set_exp').on('change',function(){
            if($(this).prop('checked')){
                $('#show_end_exp_date').show();
            }else{
                $('#show_end_exp_date').hide();
            }
        });
    });

    
    $('#table-rss-setting-template-rss').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {

            $('#btn_del_select').prop("disabled", false);
        } else {
            
            if ($('.select-chk').filter(':checked').length < 1){

                $('#btn_del_select').attr('disabled',true);
            }
        }
    });

    $('#table-rss-setting-template-rss').on('click', '.rss_id', function () {
        if ($(this).is(':checked')) {
            $('#btn_del_select').prop("disabled", false);
            {{--if($('.rss_id').filter(':checked').length >= 5){
                document.getElementById("select-all").checked = true;
            }--}}
        } else {
            document.getElementById("select-all").checked = false;
            if ($('.rss_id').filter(':checked').length < 1){
                
                $('#btn_del_select').attr('disabled',true);
            }
        }
    }); 

    $(function () {
        

        var table = $('#table-rss-setting-template-rss').DataTable({
            pageLength: 50,
            processing: true,
            serverSide: true,
            destroy: true,
            "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
            ajax: {
                url: '{!! route('rssfeedsettings.rss_setting_table') !!}',
                data: function ( d ) {
                    {{--d.keywords = keywords;--}}
                    {{--return JSON.stringify( d );--}}
                    return d;
                },
                type: "POST",
            },
            order: [[ 0, "desc" ]],
            columns: [
                { data: 'id', name: 'id',  },
                { data: 'chk', name: 'chk', orderable: false, searchable: false, sortable: false, className: 'w-10',"visible": false  },
                { data: 'name', name: 'name' },
                { data: 'url', name: 'url' },
                { data: 'feed_last', name: 'feed_last' },
                { data: 'transactionRssData_count', name: 'transactionRssData_count' },
                {
                    data: 'status',
                    name: 'status',
                    className: 'w-25',
                    "visible": false
                   
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    className: 'w-50 no-wrap',
                    "visible": false
                }
            ]
        });



    });


    





</script>



<script>
    $(document).ready(function () {
        $('#keywords').select2({
            tags: true,
            tokenSeparators: [' ']
        });

        $('#interval').select2({
            tags: true,
            tokenSeparators: [' ']
        });

        $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') });

        $('#show_end_exp_date').hide();

        $('#set_exp').on('change',function(){
            if($(this).prop('checked')){
                $('#show_end_exp_date').show();
            }else{
                $('#show_end_exp_date').hide();
            }
        });
    });

    
    $('#table-rss-setting-template-social').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {

            $('#btn_del_select').prop("disabled", false);
        } else {
            
            if ($('.select-chk').filter(':checked').length < 1){

                $('#btn_del_select').attr('disabled',true);
            }
        }
    });

    $('#table-rss-setting-template-social').on('click', '.rss_id', function () {
        if ($(this).is(':checked')) {
            $('#btn_del_select').prop("disabled", false);
            {{--if($('.rss_id').filter(':checked').length >= 5){
                document.getElementById("select-all").checked = true;
            }--}}
        } else {
            document.getElementById("select-all").checked = false;
            if ($('.rss_id').filter(':checked').length < 1){
                
                $('#btn_del_select').attr('disabled',true);
            }
        }
    }); 

    $(function () {
        

        var table = $('#table-rss-setting-template-social').DataTable({
            pageLength: 50,
            processing: true,
            serverSide: true,
            destroy: true,
            "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
            ajax: {
                url: '{!! route('socialfeedsettings.rss_setting_table') !!}',
                data: function ( d ) {
                    {{--d.keywords = keywords;--}}
                    {{--return JSON.stringify( d );--}}
                    return d;
                },
                type: "POST",
            },
            order: [[ 0, "desc" ]],
            columns: [
                { data: 'id', name: 'id',  },
                { data: 'chk', name: 'chk', orderable: false, searchable: false, sortable: false, className: 'w-10',"visible": false  },
                { data: 'source', name: 'source' },
                { data: 'url', name: 'url' },
                { data: 'feed_last_mongodb', name: 'feed_last_mongodb' },
                { data: 'transactionRssData_count', name: 'transactionRssData_count' },
                {
                    data: 'status',
                    name: 'status',
                    className: 'w-25',
                    "visible": false
                   
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    className: 'w-50 no-wrap',
                    "visible": false
                }
            ]
        });



    });


    





</script>
@endpush
@endsection

