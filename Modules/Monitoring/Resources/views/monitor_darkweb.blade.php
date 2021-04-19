@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
            <div class="header-flex-overflow m-t-10">
                <div class="fwb-16">
                    <span>
                        Monitoring > Dark Web
                    </span>
                </div>

                <div class="ml-2 text-right">

                    <a href="#hide-advance-search" id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} m-l-xs">
                        <span data-rel="tooltip" title="Filter" data-placement="bottom"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></span>
                    </a>
                </div>
            </div>
        </header>


        <section id="scrollable_news" class="scrollable wrapper">
            <section class="panel panel-default" id="hide-advance-search" style="display: none">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-md-12">
                            <i class="fas fa-filter"></i> Filter
                        </div>
                </header>
                <div class="panel-body" style="padding: 0 !important">
                    <div class="container-fluid" style="padding: 2rem;">
                        <div class="row m-b-md">
                            <div class="col-lg-6">
                                <h5 class="font-weight-bold">payload</h5>
                                <select name="payload" id="payload" class="form-control">
                                    <option value="q" selected>Darkweb</option>
                                    <option value="domain">Domain</option>
                                    <option value="email">Email</option>
                                    <option value="ip">IP</option>
                                </select>
                            </div>
                            <div class="col-lg-6">
                                <h5 class="font-weight-bold">Keyword</h5>
                                <input type="text" id="Keywords" class="form-control">
                            </div>
                        </div>
                        <div class="row m-b-md">
                            <div class="col-lg-12">
                                <h5 class="font-weight-bold">Date</h5>
                                <div id="newsrange" class="text-center"
                                    style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-lg-12 text-right">
                            <button type="button" id="btn_news_search"
                                class="btn btn-info btn-responsive btn-fz-13">
                                <i class="fas fa-search"></i>
                                @langapp('apply')
                            </button>
                            <button type="button" id="btn_news_reset"
                                class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
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

            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-xs-12">
                            <i class="fas fa-table"></i> Table Logs
                        </div>
                    </div>
                </header>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-monitoring-darkweb">
                            <thead>
                                <tr>
                                    <th width="30%">Title</th>
                                    <th>Body</th>
                                    <th width="20%">Date Time</th>
                                </tr>
                            </thead>
                            <tbody id="body-monitoring-darkweb">
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </section>
    </section>
</section>

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.datepicker')
@include('stacks.css.form')
@include('stacks.css.multitext')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.form')
@include('stacks.js.advanced_search')
@include('stacks.js.multitext')

<script>
    var id_select_site = 'site';
    var isDateSearch = 0;
    var isSearch = 0;
    var startDate =  '';
    var endDate = '';
    var Keywords = '';
    var select = '';
    var payload = '';
    var sitecode = '';
    var count_table = 0;
    var start = moment();
    var end = moment();
    

    $('#newsrange').daterangepicker({
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
    
    cb(start, end);

    $('#newsrange').on('apply.daterangepicker', function(ev, picker) {
        isDateSearch = 1;
        if (!picker.startDate.isValid() || !picker.endDate.isValid()) {
            
        }
    });

    function cb(start, end) {
        $('#newsrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }

    $("#btn_news_reset").click(function() {
        $("#Keywords").val('');
        $("#select_val").val('').trigger("change");
        $("#site").val('').trigger("change");

        isSearch = 0;
        isDateSearch = 0;
        var startDate =  '';
        var endDate =  '';
        start = moment();
        end = moment();
        cb(start, end);

        startDate=  '';
        endDate=  '';
        Keywords = '';
        select = '';
        sitecode = '';

        $('#body-monitoring-darkweb').html("");
    });

    $("#btn_news_search").click(function() {
        search();
    });

    function search(){
        startDate=  $("#newsrange").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
        endDate=  $("#newsrange").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');
        Keywords = $("#Keywords").val();
        payload = $("#payload").val();
        $('#body-monitoring-darkweb').html("");
        $.ajax({
             type:"POST",
            url:"{{ route('monitoring.monitor_search_darkweb') }}",
            data:{
                startDate: startDate,
                endDate: endDate,
                Keywords: Keywords,
                payload: payload,
            },
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
                loading('stop_load');
                var jsonObj = JSON.parse(response);
                let html = ``;
                for (let index = 0; index < jsonObj.alldata.results.length; index++) {
                    const element = jsonObj.alldata.results[index];
                    html += `
                    <tr>
                        <td>${element.title}</td>
                        <td><span id="body_text_${index}"></span></td>
                        <td>${element.crawlDate}</td>
                    <tr>
                    `;
                }
                $('#body-monitoring-darkweb').prepend($(html).fadeIn('slow'));

                for (let index = 0; index < jsonObj.alldata.results.length; index++) {
                    const element = jsonObj.alldata.results[index];
                    $('#body_text_' + index).text(element.body);
                }
        
            },
            error: function (error){
                loading('stop_load');
                console.log(1);
            }             
        });
    }

</script>
@endpush
@endsection