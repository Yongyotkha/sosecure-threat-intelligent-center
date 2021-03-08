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

                <a href="#hide-advance-search" id="advance-search"
                    class="btn btn-sm btn-{{ get_option('theme_color')  }} m-l-xs">
                    <span data-rel="tooltip" title="Filter" data-placement="bottom"><i class="fas fa-filter"></i><span
                            class="hide-text">@langapp('Search_Advance')</span></span>
                </a>

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
                            <i class="fas fa-table"></i> Site
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
        setInterval(function(){ load_card(); }, 60000);
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
                
            });
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            
            console.log("No response from server");
        });
    }


</script>
@endpush
@endsection