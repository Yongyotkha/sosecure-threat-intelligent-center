@extends('layouts.app')
@section('content')
<style>
    #chart-show-pie-1 .chart-legend,
    #chart-show-pie-2 .chart-legend,
    #chart-show-pie-3 .chart-legend
    {
        display: none
    }
</style>
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a> --}}
            <div class="bc-head">@langapp('compromised') > Assets Setting > baac.or.th</div>
        </header>
        <section class="scrollable wrapper">
            <div class="tabbable">
                <ul class="nav nav-tabs nav-tabs-highlight">
                    <li class="active"><a href="#overview_tab" data-toggle="tab">Overview</a></li>
                    <li><a href="#correlations_tab" data-toggle="tab">Correlations</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="overview_tab">
                        <section class="panel panel-default">
                            <div class="panel-body">
                                <div class="row d-f-j-ct">
                                    <div class="col-lg-3 col-md-3 border-alpha">
                                        <ul class="total-count-com">
                                            <li class="total-p1">
                                                <h3 class="text-dark">Total Data Elements</h3>
                                                <span class="color-purple">10</span>
                                            </li>
                                            <li class="total-p1">
                                                <h3 class="text-dark">Unique Data Elements</h3>
                                                <span class="color-green">2597</span>
                                            </li>
                                            <li class="total-p1">
                                                <h3 class="text-dark">Errors</h3>
                                                <span class="color-red">2597</span>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-lg-3 col-md-3 text-center border-alpha" style="padding: 0">
                                        <h1>Correlations</h1>
                                        <div id="chart-show-pie-1"></div>
                                    </div>
                                    <div class="col-lg-3 col-md-3 text-center border-alpha" style="padding: 0">
                                        <h1>Module Categories</h1>
                                        <div id="chart-show-pie-2"></div>
                                    </div>
                                    <div class="col-lg-3 col-md-3 text-center border-alpha" style="padding: 0">
                                        <h1>Data Sources</h1>
                                        <div id="chart-show-pie-3"></div>
                                    </div>
                                </div>
                            </div>
                        </section>


                        <section class="panel panel-default">
                            <header class="panel-heading font-bold">
                                Data Elements: Unique VS. Non-unique
                            </header>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div id="chart-show-bar"></div>
                                    </div>
                                </div>
                            </div>
                        </section>
                        
                    </div>
                    <div class="tab-pane" id="correlations_tab">
                        <section class="panel panel-default">

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
    <link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css"/>
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.chart')

<script>

    var chart_pie_1 = new frappe.Chart("#chart-show-pie-1", { 
        title: "",
        data:{
            labels: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
            datasets: [
                { values: [18, 40, 30, 35, 8, 52, 17, -4] }
            ]
        },
        type: 'pie',
        height: 300,
        colors: ['#743ee2']
    });

    var chart_pie_2 = new frappe.Chart("#chart-show-pie-2", { 
        title: "",
        data:{
            labels: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
            datasets: [
                { values: [18, 40, 30, 35, 8, 52, 17, -4] }
            ]
        },
        type: 'pie',
        height: 300,
        colors: ['#743ee2']
    });

    var chart_pie_3 = new frappe.Chart("#chart-show-pie-3", { 
        title: "",
        data:{
            labels: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
            datasets: [
                { values: [18, 40, 30, 35, 8, 52, 17, -4] }
            ]
        },
        type: 'pie',
        height: 300,
        colors: ['#743ee2']
    });


    var chart_bar = new frappe.Chart("#chart-show-bar", { 
        title: "",
        data:{
            labels: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
            datasets: [
                {
                    name: "Unique",
                    values: [18, 40, 30, 35, 8, 52, 17, 6],
                    chartType: 'bar'
                },
                {
                    name: "Non-unique",
                    values: [30, 50, 5, 15, 18, 32, 27, 14],
                    chartType: 'bar'
                }
            ]
        },
        type: 'bar',
        height: 300,
        colors: ['#7cd6fd','#a9a9a9']
    });

</script>
@endpush
@endsection