@extends('layouts.app')
@section('content')
<style>
    .tooltip-inner {
        max-width: 500px;
        /* If max-width does not work, try using width instead */
        width: 500px;
    }

    .w-200 {
        width: 200px !important;
    }
</style>
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            <a href="{{route('dashboardnew.index')}}" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
            @icon('solid/arrow-left')
            </a>
            <div class="bc-head">@langapp('vulnerabilitys') > Microsoft(windows_server_2008)</div>
        </header>

        <section class="scrollable wrapper">
            <div class="row">
                <div class="col-md-12">
                    <section class="panel panel-default">
                        <header class="panel-heading font-bold">
                                Asset Inventory
                        </header>

                        <div class="panel-body" id="chart-container">
                            <table class="table table-striped table-bordered">
                                <tr>
                                    <th>Vendor</th>
                                    <td>Microsoft</td>
                                </tr>
                                <tr>
                                    <th>Title</th>
                                    <td>windows_server_2008</td>
                                </tr>
                                <tr>
                                    <th>Version</th>
                                    <td>r2</td>
                                </tr>
                                <tr>
                                    <th>Edition</th>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <th>Site</th>
                                    <td>Site-01</td>
                                </tr>
                                <tr>
                                    <th>IP</th>
                                    <td>10.10.1.11</td>
                                </tr>
                                <tr>
                                    <th>Hosting</th>
                                    <td>www.10.10.1.11</td>
                                </tr>
                            </table>
                        </div>
                    </section>
                </div>

                <div class="col-md-12">
                    <section class="panel panel-default">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row d-flex-center">
                                <div class="col-md-6">
                                   Assets Inventory Details
                                </div>
                                <div class="col-md-6 text-right">
                                    <div id="date-rang"
                                        style="color:#333;background: #efefef; cursor: pointer; padding: 7px 10px; border: 1px solid #ddd; display:inline-block;margin-right: 5px;margin-bottom:0;">
                                        <i class="fa fa-calendar"></i>&nbsp;
                                        <span></span> <i class="fa fa-caret-down"></i>
                                    </div>

                                    <div class="btn-group">
                                        <button
                                            class="btn text-dark dropdown-toggle"
                                            data-toggle="dropdown">Action
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-left">
                                            <li>
                                                <a href="#">
                                                    Export To CSV
                                                </a>
                                                <a href="javascript:void(0)" id="btn_change_fix">
                                                    Change Status
                                                </a>

                                            </li>
                                        </ul>
                                    </div>

                                    <button id="togglecollapsetable" style="margin-left:5px;" class="btn text-dark" onclick="collpase_chart('#table-container','#togglecollapsetable')">
                                        <i class="fas fa-minus-square"></i>Collapse
                                    </button>

                                </div>
                            </div>
                        </header>
                        <div class="panel-body" id="table-container">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-vulnerabilitys-monitoring">
                                    <thead>
                                        <tr>
                                            <th class="no-sort w-10">
                                                <label>
                                                    <input name="select_all" value="1" id="select-all" type="checkbox"
                                                        class="select-chk" />
                                                    <span class="label-text"></span>
                                                </label>
                                            </th>
                                            <th>Name CVE</th>
                                            <th>description</th>
                                            <th>@langapp('cvss_severity')</th>
                                            <th>@langapp('transaction')</th>
                                            <th>Is Fixed</th>
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
    $('#table-vulnerabilitys-monitoring').DataTable();
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

    $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') });

    $(function() {
    
    var start = moment().startOf('hour');
    var end = moment().startOf('hour').add(32, 'hour');

    function cb(start, end) {
        $('#date-rang span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        startDate = start;
        endDate = end;
    }

        $('#date-rang').daterangepicker({
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
        $('#date-rang').on('apply.daterangepicker', function(ev, picker) {
            isDateSearch = 1;
            if (!picker.startDate.isValid() || !picker.endDate.isValid()) {
                
            }
        });

        cb(start, end);
    });

</script>

@endpush
@endsection