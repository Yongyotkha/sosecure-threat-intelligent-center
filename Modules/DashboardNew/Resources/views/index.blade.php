@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header bg-white b-b b-light">
            <div class="bc-head">Summary Dashboard</div>

            <div class="pull-right" style="margin-top: 15px">
                Current Date : <i class="fas fa-calendar"></i> <span id="current-date"></span>
            </div>
        </header>

        <section class="scrollable wrapper">
            <div id="load_chart">
                <div class="row">
                    <div class="col-lg-12 col-md-12" id="count_cve"></div>
                    <div class="col-md-12">
                        <section class="">
                            <div class="panel-body" id="chart-container">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-12 padding-small-5px mb-2">
                                        <div class="row">
                                            <div class="col-lg-12 col-md-6 mb-small-5px">
                                                <div class="card-dash">
                                                    <div class="left-card">
                                                        <div class="img-icon-card">
                                                            <img src="{{asset('images/database.png')}}" alt="">
                                                        </div>
                                                        <h3 class="name-dash-text text-dark text-upper ">Asset</h3>
                                                        <span class="number-card info">{{@$count_CVEAssets}}</span>
                                                    </div>
                                                   
                                                </div>
                                            </div>
                                            <div class="col-lg-12 col-md-6 mb-small-5px">
                                                <div class="card-dash">
                                                    <div class="left-card">
                                                        <div class="img-icon-card">
                                                            <img src="{{asset('images/antivirus.png')}}" alt="">
                                                        </div>
                                                        <h3 class="name-dash-text text-dark text-upper ">Vulnerability</h3>
                                                        <span class="number-card green">{{@$count_CVEMapping}}</span>
                                                    </div>
                                             
                                                </div>
                                            </div>
                                            <div class="col-lg-12 col-md-6 mb-small-5px">
                                                <div class="card-dash">
                                                    <div class="left-card">
                                                        <div class="img-icon-card">
                                                            <img src="{{asset('images/compromise.png')}}" alt="">
                                                        </div>
                                                        <h3 class="name-dash-text text-dark text-upper ">Compromised</h3>
                                                        <span class="number-card warning">{{@$count_compromised}}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12 col-md-6 mb-small-5px">
                                                <div class="card-dash">
                                                    <div class="left-card">
                                                        <div class="img-icon-card">
                                                            <img src="{{asset('images/dataleak.png')}}" alt="">
                                                        </div>
                                                        <h3 class="name-dash-text text-dark text-upper ">Data Leak</h3>
                                                        <span class="number-card dark">2,500</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-9 col-lg-9 col-md-12">
                                        <div class="row">
                                            <div class="col-md-6 col-lg-6 nopadding mb-small-5px">
                                                <div class="box-chart-color">
                                                    <div class="d-flex align-items-center header-chart-p">
                                                        <img src="{{asset('images/bar-chart.png')}}" alt="" height="30px">
                                                        <h1 class="text-blue bold-500">Vulnerability Severity</h1>
                                                    </div>
                                                    <div class="divider-dark"></div>
                                                    <div id="chart-show-hl"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-6 nopadding mb-small-5px">
                                                <div class="box-chart-color">
                                                    <div class="d-flex align-items-center header-chart-p">
                                                        <img src="{{asset('images/pie-chart.png')}}" alt="" height="30px">
                                                        <h1 class="text-blue bold-500">Severity </h1>
                                                    </div>
                                                    <div class="divider-dark"></div>
                                                    <div id="chart-show-pie"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 col-lg-6 nopadding mb-small-5px">
                                                <div class="box-chart-color">
                                                    <div class="d-flex align-items-center header-chart-p">
                                                        <img src="{{asset('images/line-chart.png')}}" alt="" height="30px">
                                                        
                                                        <h1 class="text-blue bold-500">Indicators</h1>
                                                    </div>
                                                    <div class="divider-dark"></div>
                                                    <div id="chart-show-line"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-6 nopadding mb-small-5px">
                                                <div class="box-chart-color">
                                                    <div class="d-flex align-items-center header-chart-p">
                                                        <img src="{{asset('images/table.png')}}" alt="" height="30px">
                                                        <h1 class="text-blue bold-500">Assets</h1>
                                                    </div>
                                                    <div class="divider-dark"></div>
                                                    <div class="table-responsive" style="max-height:280px;min-height: 400px;overflow: auto;">
                                                        <table class="table table-striped" id="table-assets">
                                                            <thead>
                                                                <tr>
                                                                    <th>Assets</th>
                                                                    <th>Referent</th>
                                                                    <th>View</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @if ($get_CVEAssets)
                                                                @foreach ($get_CVEAssets as $get_CVEAssets)
                                                                <tr>
                                                                    <td>
                                                                        <span>{{@$get_CVEAssets->vendor}}</span>
                                                                    </td>
                                                                    <td>
                                                                        <span>{{@$get_CVEAssets->IP}}</span>
                                                                    </td>
                                                                    <td>
                                                                        <a href="{{@$get_CVEAssets->site_id}}" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.datepicker')
@include('stacks.css.form')
@include('stacks.css.highchart')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.chart')
@include('stacks.js.form')
@include('stacks.js.highchart')

<script>

    var today_date = new Date();
    var dd = String(today_date.getDate()).padStart(2, '0');
    var mm = String(today_date.getMonth() + 1).padStart(2, '0');
    var yyyy = today_date.getFullYear();
    today_date = mm + '-' + dd + '-' + yyyy;
    document.getElementById('current-date').innerHTML = today_date;
    

    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip(); 
    });

    function collpase_chart(id,text){
        $(id).slideToggle();
        if($(text).text() == 'Expanded'){
            $(text).html('<i class="fas fa-minus-square"></i>Collapse');
        }else{
            $(text).html('<i class="fas fa-plus-square"></i>Expanded');
        }
    }

    $('#area-advance-search').hide();
    $('#advance-search').click(function(){
        $('#area-advance-search').toggle();
    });

</script>

<script type="text/javascript">
 $(function() {
    
        var start = moment().startOf('hour');
        var end = moment().startOf('hour').add(32, 'hour');

        function cb(start, end) {
            $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            startDate = start;
            endDate = end;
        }

        $('#reportrange').daterangepicker({
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
        $('#reportrange').on('apply.daterangepicker', function(ev, picker) {
            isDateSearch = 1;
            if (!picker.startDate.isValid() || !picker.endDate.isValid()) {
                
            }
        });

        cb(start, end);
    });

    $(function () {
        load_chart();
    });

    function load_chart(){

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{!! route('dashboardnew.load_chart') !!}',
            type: "POST",
            data: ({
            }),
            beforeSend: function(){
                {{--loading('load');--}}
                f_loading(null, '#chart-show-pie');

            },
        }).done(function(data){

            f_loading_stop(null, '#chart-show-pie');

            const chart_pie = new Highcharts.chart('chart-show-pie', {
                chart: {
                    height: 280, 
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
                        color: ['#e64732', '#fcc838', '#ffe46d', '#88ce4f', '#d3d3d3'],
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                        },
                    }
                },
                series: [{
                    colorByPoint: false,
                    data: [
                    {  name: 'Critical', y: data.count_critical, color: '#e64732'}, 
                    {  name: 'High',  y: data.count_high , color: '#fcc838'}, 
                    {  name: 'Medium', y: data.count_medium, color: '#ffe46d'  }, 
                    {  name: 'Low',   y: data.count_low, color: '#88ce4f'  },
                    {  name: 'Information',   y: data.count_none, color: '#d3d3d3'  },
                    ]
                }],
            });
        }).fail(function(jqXHR, ajaxOptions, thrownError){
                f_loading_stop(null, '#chart-show-pie');
                console.log("No response from server");
            });
        
    }
  

       
    const chartstack = new Highcharts.chart('chart-show-hl', {
        chart: {
            height: 400, 
            type: 'bar'
        },
        title: {
            text: null
        },
        xAxis: {
            categories: ['Host name 1', 'Host name 2', 'Host name 3', 'Host name 4', 'Host name 5']
        },
        yAxis: {
            min: 0,
            title: {
            text: null
            }
        },
        legend: {
            reversed: true
        },
        plotOptions: {
            series: {
            stacking: 'normal',
            dataLabels: {
                enabled: true,
                color:'#333',
            },
            
            }
        },
        series: [{
            name: 'Critical',
            data: [5, 3, 4, null, 2],
            color: '#e64732',
        }, {
            name: 'High',
            data: [2, 2, null, 2, 1],
            color: '#fcc838'
        }, {
            name: 'Medium',
            data: [3, null, 4, 2, 5],
            color: '#ffe46d'
        }, {
            name: 'Low',
            data: [null, 4, 4, 2, 5],
            color: '#88ce4f '
        }, {
            name: 'infomation',
            data: [3, 4, 4, 2, null],
            color: '#d3d3d3'
        },
        ]
        });


       {{-- const chart_pie = new Highcharts.chart('chart-show-pie', {
            chart: {
                height: 400, 
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: ''
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
                    color: ['#e64732', '#fcc838', '#ffe46d', '#88ce4f ', '#d3d3d3'],
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                    },
                }
            },
            series: [{
                colorByPoint: false,
                data: [
                {  name: 'Critical',   y: 12, color: '#e64732'  },
                {  name: 'High',  y: 14 , color: '#fcc838'}, 
                {  name: 'Medium', y: 12, color: '#ffe46d'  }, 
                {  name: 'Low',   y: 33, color: '#88ce4f '  },
                {  name: 'Information',   y: 0, color: '#d3d3d3'  },
                ],  
            }]
        });--}}


        const chart_line = new Highcharts.chart('chart-show-line', {
            chart: {
                height: 400, 
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'line'
            },
            title: {
                text: ''
            },
            subtitle: {
                text: ''
            },
            xAxis: {
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
            },
            yAxis: {
                title: {
                text: 'Number (Months)'
                }
            },
            plotOptions: {
                line: {
                dataLabels: {
                    enabled: true
                },
                enableMouseTracking: false
                }
            },
            series: [
            {
                name: 'Number of Months',
                data: [7.0, 6.9, 9.5, 14.5, 18.4, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6],
                color: '#3984e7'
            }, 
            ]
        });


</script>

@endpush
@endsection