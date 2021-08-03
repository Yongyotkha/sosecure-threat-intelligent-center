@extends('layouts.app')
@section('content')

<section id="content" class="bg">
    <section class="vbox">
        <header class="header bg-white b-b b-light" style="display: flex;justify-content:space-between;">
            <div class="bc-head">Phishing Detection</div>
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
            <section class="m-b-10">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12">
                        <div class="row">
                            <div class="col-md-6 col-lg-6 mb-small-5px">
                                {{-- <div class="loadhost backdrop-loader">
                                    <div class="loader4 centerloader"></div>
                                    <div class="loadding-text">Loading ...</div>
                                </div> --}}
                                <div class="box-chart-color bg-white">
                                    <div class="header-chart-p">
                                        <div class="d-flex align-items-center ">
                                            <img src="{{asset('images/bar-chart.png')}}" alt="" height="30px">
                                            <h1 class="text-blue bold-500">Timeline</h1>
                                        </div>
                                        <div id="filter-chart-btn" class="btn-group pull-right" style="margin-top: -25px;">
                                            <a href="javascript:void(0)" class="btn btn-xs btn-chart-fil active">
                                                <i  class="far fa-calendar"></i> Day
                                             </a>
                                            <a href="javascript:void(0)"  class="btn btn-xs btn-chart-fil"><i class="far fa-calendar"></i>
                                                Month
                                             </a>
                                        </div>
                                    </div>

                                    <div class="divider-dark"></div>
                                    <div id="chart-timeline" class="h-chart"></div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-6 mb-small-5px">
                                {{-- <div class="category backdrop-loader">
                                    <div class="loader4 centerloader"></div>
                                    <div class="loadding-text">Loading ...</div>
                                </div> --}}
                                <div class="box-chart-color bg-white">
                                    <div class="d-flex align-items-center header-chart-p">
                                        <img src="{{asset('images/bar-chart.png')}}" alt="" height="30px">
                                        <h1 class="text-blue bold-500">Type</h1>
                                    </div>
                                    <div class="divider-dark"></div>
                                    <div id="chart-type" class="h-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>


            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-xs-12">
                            <i class="fas fa-table"></i> Table Phishing Detection
                        </div>
                    </div>
                </header>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-phishing-template" style="width: 100%">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th style="width: 80%">Content</th>
                                    <th style="width: 10%" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div style="position: relative;width:150px;">
                                            <img src="http://beepeers.com/assets/images/commerces/default-image.jpg" alt="" style="width: 100%">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex-actor">
                                            <div class="details-actor">
                                                <h4 class="text-primary" style="margin-left: 0">URL : http://testtest.com/scb/login.aspx</h4>
                                                <p style="white-space: pre-wrap;">
                                                    <strong>IP Address : 171.123.143.38</strong>
                                                </p>
                                                <div class="btw-text">
                                                    <p style="margin-right: 20px;">
                                                        <strong>Type </strong>: Thailand
                                                    </p>
                                                    <p>
                                                        <strong>Date </strong>: 2021-08-08 22:33
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-nowrap text-center">
                                        <a href="#" class="btn btn-info btn-xs">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="#" class="btn btn-info btn-xs">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div style="position: relative;width:150px;">
                                            <img src="http://beepeers.com/assets/images/commerces/default-image.jpg" alt="" style="width: 100%">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex-actor">
                                            <div class="details-actor">
                                                <h4 class="text-primary" style="margin-left: 0">URL : http://testtest.com/scb/login.aspx</h4>
                                                <p style="white-space: pre-wrap;">
                                                    <strong>IP Address : 171.123.143.38</strong>
                                                </p>
                                                <div class="btw-text">
                                                    <p style="margin-right: 20px;">
                                                        <strong>Type </strong>: Thailand
                                                    </p>
                                                    <p>
                                                        <strong>Date </strong>: 2021-08-08 22:33
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-nowrap text-center">
                                        <a href="#" class="btn btn-info btn-xs">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="#" class="btn btn-info btn-xs">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

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
@include('stacks.js.highchart')

<script>

$('#table-phishing-template').DataTable();

function mychart(myid){
    const chart_top_source = Highcharts.chart(myid, {
    chart: {
        type: 'column'
    },

    title: {
        text: ''
    },

    xAxis: {
        categories: ['1', '2', '3', '4', '5']
    },

    yAxis: {
        allowDecimals: false,
        min: 0,
        title: {
            text: null
        }
    },

    tooltip: {
        formatter: function () {
            return '<b>' + this.x + '</b><br/>' +
                this.series.name + ': ' + this.y + '<br/>' +
                'Total: ' + this.point.stackTotal;
        }
    },

    plotOptions: {
        column: {
            stacking: 'normal'
        },
        dataLabels: {
            enabled: false,
            format: '<b>{point.name}</b>: {point.percentage:.1f} %'
        }
    },
    legend: {
        enabled: false
    },
    series: [{
        name: '123',
        data: [5, 3, 4, 7, 2],
        stack: 'male'
    }, {
        name: '123',
        data: [3, 4, 4, 2, 5],
        stack: 'male'
    }, {
        name: '123',
        data: [2, 5, 6, 2, 1],
        stack: 'female'
    }, {
        name: '123',
        data: [3, 0, 4, 4, 3],
        stack: 'female'
    }]
    });
}

function circle_chart(id){
    const chart_pie = new Highcharts.chart(id, {
        chart: {
            height: 223, 
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: ''
        },
        tooltip: {
            pointFormat: 'Amount {point.y}: <b>{point.percentage:.1f}%</b>'
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
                color: ['#e64732', '#fcc838', '#00dcff', '#88ce4f', '#d3d3d3'],
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                },
            }
        },
        series: [{
            colorByPoint: false,
            data: [
            {  name: 'Type', y: 5, color: '#e64732'}, 
            {  name: 'Type',  y: 4 , color: '#fcc838'}, 
            {  name: 'Type', y: 4, color: '#00dcff'  }, 
            ]
        }],
    });
}

circle_chart('chart-type');
mychart('chart-timeline');



</script>
@endpush
@endsection