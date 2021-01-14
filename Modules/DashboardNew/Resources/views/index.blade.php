@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header bg-white b-b b-light">
            <div class="bc-head">Summary Dashboard</div>
            <div class="pull-right" style="margin-top: 8px; width: 300px;">
                <select name="site" id="site" class="select2-option form-control select-site" style="min-width: 300px;"
                    onchange="changeSite(value)">
                    <option value="0" selected>All Site</option>
                    @if ($site_settings)

                    @foreach ($site_settings as $site_settings)
                    <option value="{{$site_settings->id}}">{{$site_settings->name}}
                    </option>
                    @endforeach

                    @endif
                </select>
            </div>
        </header>

        <section class="scrollable wrapper">
            <div id="load_chart">
                <div class="row">
                    <div class="col-lg-12 col-md-12" id="count_cve"></div>
                    <div class="col-md-12">
                        <section class="">
                            <div class="panel-body" style="padding: 0 15px;" id="chart-container">
                                <div class="row">
                                    <div class="col-xl-2 col-lg-2 col-md-12 padding-small-5px mb-2">
                                        <div class="row">
                                            <div class="col-lg-12 col-md-6 mb-small-5px">
                                                <a href="#" data-toggle="modal" data-target="#modal_asset">
                                                    <div class="card-dash">
                                                        <div class="left-card">
                                                            <div class="img-icon-card">
                                                                <img src="{{asset('images/database.png')}}" alt="">
                                                            </div>
                                                            <h3 class="name-dash-text text-dark text-upper ">Asset</h3>
                                                            <span class="number-card info number_asset"></span>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                            <div class="col-lg-12 col-md-6 mb-small-5px">
                                                <div class="card-dash">
                                                    <div class="left-card">
                                                        <div class="img-icon-card">
                                                            <img src="{{asset('images/antivirus.png')}}" alt="">
                                                        </div>
                                                        <h3 class="name-dash-text text-dark text-upper ">Vulnerability</h3>
                                                        <a class="number-card green number_vulnerability"></a>
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
                                                        <a class="number-card warning number_compromised"></a>
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
                                                        <a class="number-card dark number_data_leak"></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-10 col-lg-10 col-md-12">
                                        <div class="row">
                                            <div class="col-md-6 col-lg-6 nopadding mb-small-5px">
                                                <div class="loadhost backdrop-loader">
                                                    <div class="loader4 centerloader"></div>
                                                    <div class="loadding-text">Loading ...</div>
                                                </div>
                                                <div class="box-chart-color">
                                                    <div class="d-flex align-items-center header-chart-p">
                                                        <img src="{{asset('images/bar-chart.png')}}" alt="" height="30px">
                                                        <h1 class="text-blue bold-500">Vulnerability Host</h1>
                                                    </div>
                                                    <div class="divider-dark"></div>
                                                    <div id="chart-show-hl" style="height: 227px"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-6 nopadding mb-small-5px">
                                                <div class="loadvulserverity backdrop-loader">
                                                    <div class="loader4 centerloader"></div>
                                                    <div class="loadding-text">Loading ...</div>
                                                </div>
                                                <div class="box-chart-color">
                                                    <div class="d-flex align-items-center header-chart-p">
                                                        <img src="{{asset('images/pie-chart.png')}}" alt="" height="30px">
                                                        <h1 class="text-blue bold-500">Vulnerability Severity </h1>
                                                    </div>
                                                    <div class="divider-dark"></div>
                                                    <div id="chart-show-pie" style="height: 227px"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 col-lg-6 nopadding mb-small-5px">
                                                <div class="loadindicator backdrop-loader">
                                                    <div class="loader4 centerloader"></div>
                                                    <div class="loadding-text">Loading ...</div>
                                                </div>
                                                <div class="box-chart-color">
                                                    <div class="d-flex align-items-center header-chart-p">
                                                        <img src="{{asset('images/line-chart.png')}}" alt="" height="30px">
                                                        
                                                        <h1 class="text-blue bold-500">Indicators</h1>
                                                    </div>
                                                    <div class="divider-dark"></div>
                                                    <div id="chart-show-line" style="height: 227px"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-6 nopadding mb-small-5px">
                                                <div class="loaddertb backdrop-loader">
                                                    <div class="loader4 centerloader"></div>
                                                    <div class="loadding-text">Loading ...</div>
                                                </div>
                                                <div class="box-chart-color">
                                                    <div class="d-flex align-items-center header-chart-p">
                                                        <img src="{{asset('images/table.png')}}" alt="" height="30px">
                                                        <h1 class="text-blue bold-500">Assets</h1>
                                                    </div>
                                                    <div class="divider-dark"></div>
                                                    <div class="table-responsive cve_assets" style="max-height:227px;min-height: 227px;overflow: auto;">
                                                        <table class="table table-striped" id="table-assets">
                                                            <thead>
                                                                <tr>
                                                                    <th>Assets</th>
                                                                    <th>Referent</th>
                                                                    <th>View</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="cve_assets"></tbody>
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

    <div class="modal fade fixed-left" id="modal_asset" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside size-half-50" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="text-white">&times;</span>
                    </button>
                    <h4 class="modal-title text-white" id="exampleModalLabel">Asset All</h4>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table id="table-assets-modal" class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Vendor</th>
                                    <th>Title</th>
                                    <th>Version</th>
                                    <th>Edition</th>
                                    <th>Site</th>
                                    <th>IP</th>
                                    <th>Hosting</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Microsoft</td>
                                    <td>Window server 2008</td>
                                    <td>r2</td>
                                    <td>-</td>
                                    <td>Site-01</td>
                                    <td>10.10.1.11</td>
                                    <td>www.10.10.1.11</td>
                                    <td>
                                        <a href="{{route('detail_asset.index')}}" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>
                                        <a href="#" class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

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
    var site = 0;
    function changeSite(value) {
        site = value;
        count_asset();
        count_vulnerability();
        count_compromised();
        count_data_leak();
        count_vulnerability_host();
        load_chart();
        cve_assets();
    }

    $( document ).ready(function() {
        count_asset();
        count_vulnerability();
        count_compromised();
        count_data_leak();
        count_vulnerability_host();
        load_chart();
        chart_indicators();
        cve_assets();
    });

    function count_asset(){
        $.ajax({
            type: 'POST',
            dataType: "json",
            url: '{{ route("dashboardnew.count_asset") }}',
            data: {
                site : site, 
            },
            beforeSend: function() {
                $('.number_asset').text('0');
            },
            success: function(result){
                f_loading_stop(null, '.number_asset');
                if(result.status_code == 200){
                    $('.number_asset').text(result.data);
                }
            }
        });
    }

    function count_vulnerability(){
        $.ajax({
            type: 'POST',
            dataType: "json",
            url: '{{ route("dashboardnew.count_vulnerability") }}',
            data: {
                site : site, 
            },
            beforeSend: function() {
                $('.number_vulnerability').text('0');
            },
            success: function(result){
                f_loading_stop(null, '.number_vulnerability');
                if(result.status_code == 200){
                    $('.number_vulnerability').text(result.data);
                }
            }
        });
    }

    function count_compromised(){
        $.ajax({
            type: 'POST',
            dataType: "json",
            url: '{{ route("dashboardnew.count_compromised") }}',
            data: {
                site : site, 
            },
            beforeSend: function() {
                $('.number_compromised').text('0');
            },
            success: function(result){
                if(result.status_code == 200){
                    $('.number_compromised').text(result.data);
                }
            }
        });
    }

    function count_data_leak(){
        $.ajax({
            type: 'POST',
            dataType: "json",
            url: '{{ route("dashboardnew.count_data_leak") }}',
            data: {
                site : site, 
            },
            beforeSend: function() {
                $('.number_data_leak').text('0');
            },
            success: function(result){
                if(result.status_code == 200){
                    $('.number_data_leak').text(result.data);
                }
            }
        });
    }

    function chart_indicators(){
        $.ajax({
            type: 'POST',
            dataType: "json",
            url: '{{ route("dashboardnew.chart_indicators") }}',
            beforeSend: function() {
                $(".loadindicator").show();
            },
            success: function(result){
                $(".loadindicator").hide();
                if(result.status_code == 200){
                    const chart_line = new Highcharts.chart('chart-show-line', {
                        chart: {
                            height: 227, 
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
                            name: 'Number of Event',
                            data: result.data.events,
                            color: '#3984e7'
                        }, 
                        {
                            name: 'Number of Attribute',
                            data: result.data.attribute,
                            color: '#e64732'
                        }, 
                        ]
                    });
                }
            }
        });
    }

    function cve_assets(){
        $.ajax({
            type: 'POST',
            dataType: "json",
            url: '{{ route("dashboardnew.cve_assets") }}',
            data: {
                site : site, 
            },
            beforeSend: function() {
                $(".loaddertb").show();
            },
            success: function(result){
                $(".loaddertb").hide();
                if(result.status_code == 200){
                    $('#cve_assets').empty();
                    let html = ``;
                    for(let i in result.data){
                        const data = result.data[i];
                        let raw_data;
                        let referent;
                        if(data.raw_data){
                            raw_data = data.raw_data;
                        }else{
                            raw_data = 'No data';
                        }
                        if(data.referent){
                            referent = data.referent;
                        }else{
                            referent = 'No data';
                        }

                        html += `
                        <tr>
                            <td>
                                <span>${raw_data}</span>
                            </td>
                            <td>
                                <span>${referent}</span>
                            </td>
                            <td>
                                <a href="#" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        `;
                    }
                    $('#cve_assets').html(html);
                }
            }
        });
    }

    $('#table-assets-modal').DataTable();

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

    function load_chart(){

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{!! route('dashboardnew.load_chart') !!}',
            type: "POST",
            data: {
                site : site, 
            },
            beforeSend: function(){
                $(".loadvulserverity").show();
            },
        }).done(function(data){

            $(".loadvulserverity").hide();

            const chart_pie = new Highcharts.chart('chart-show-pie', {
                chart: {
                    height: 227, 
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
                $(".loadvulserverity").hide();
                console.log("No response from server");
            });
        
    }
    function count_vulnerability_host(){
        $.ajax({
            type: 'POST',
            dataType: "json",
            url: '{{ route("dashboardnew.count_vulnerability_host") }}',
            data: {
                site : site, 
            },
            beforeSend: function() {
                $(".loadhost").show();
            },
            success: function(result){
                $(".loadhost").hide();
                if(result.status_code == 200){
                    var critical = [];
                    var high = [];
                    var medium = [];
                    var low = [];
                    var infomation = [];
                    var host_name = [];
                    var data_array = [];
                    for(let b in result.data.host_name){
                        var data_object = {};
                        const data_b = result.data.host_name[b];
                        let total_critical = 0;
                        let total_high = 0;
                        let total_medium = 0;
                        let total_low = 0;
                        let total_infomation = 0;
                        data_object.host_name = data_b;
                        for(let i in result.data.data){
                            const data = result.data.data[i];
                            if(data_b == data.title && data.severity == 'HIGH'){
                                total_high += data.total;
                                data_object.severity_high = data.severity;
                            }else if(data_b == data.title && data.severity == 'CRITICAL'){
                                total_critical += data.total;
                                data_object.severity_critical = data.severity;
                            }else if(data_b == data.title && data.severity == 'MEDIUM'){
                                total_medium += data.total;
                                data_object.severity_medium = data.severity;
                            }else if(data_b == data.title && data.severity == 'LOW'){
                                total_low += data.total;
                                data_object.severity_low = data.severity;
                            }else if(data_b == data.title && data.severity == 'INFOMATION'){
                                total_infomation += data.total;
                                data_object.severity_infomation = data.severity;
                            }
                        }
                        if(total_critical > 0){
                            data_object.total_critical = total_critical;
                        }
                        if(total_high > 0){
                            data_object.total_high = total_high;
                        }
                        if(total_medium > 0){
                            data_object.total_medium = total_medium;
                        }
                        if(total_low > 0){
                            data_object.total_low = total_low;
                        }
                        if(total_infomation > 0){
                            data_object.total_infomation = total_infomation;
                        }
                        data_array.push(data_object);
                    }
                    for(let i in result.data.host_name){
                        const data = result.data.host_name[i];
                        host_name.push(data);
                    }
                    for(let i in data_array){
                        const host_name_check = host_name[i];
                        const data = data_array[i];
                        if(host_name_check == data.host_name){
                            if(data.severity_high){
                                high.push(data.total_high);
                            }else{
                                high.push(null);
                            }
                            if(data.severity_critical){
                                critical.push(data.total_critical);
                            }else{
                                critical.push(null);
                            }
                            if(data.severity_medium){
                                medium.push(data.total_medium);
                            }else{
                                medium.push(null);
                            }
                            if(data.severity_low){
                                low.push(data.total_low);
                            }else{
                                low.push(null);
                            }
                            if(data.severity_infomation){
                                infomation.push(data.total_infomation);
                            }else{
                                infomation.push(null);
                            } 
                        }
                    }
                    const chartstack = new Highcharts.chart('chart-show-hl', {
                        chart: {
                            height: 227, 
                            type: 'bar'
                        },
                        title: {
                            text: null
                        },
                        xAxis: {
                            categories: host_name
                        },
                        yAxis: {
                            min: 0,
                            title: {
                            text: null
                            }
                        },
                        scrollbar: {
                            enabled: true
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
                            data: critical,
                            color: '#e64732',
                        }, {
                            name: 'High',
                            data: high,
                            color: '#fcc838'
                        }, {
                            name: 'Medium',
                            data: medium,
                            color: '#ffe46d'
                        }, {
                            name: 'Low',
                            data: low,
                            color: '#88ce4f '
                        }, {
                            name: 'infomation',
                            data: infomation,
                            color: '#d3d3d3'
                        }]
                    });
                }
            }
        });
    }
       

       {{-- const chart_pie = new Highcharts.chart('chart-show-pie', {
            chart: {
                height: 227, 
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
</script>

@endpush
@endsection
