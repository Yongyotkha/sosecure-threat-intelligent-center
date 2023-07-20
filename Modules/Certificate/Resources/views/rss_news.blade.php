@php
    use Carbon\Carbon;
@endphp
@extends('layouts.app')
@section('content')

    <section id="content" class="bg">
        <section class="hbox stretch">
            <aside id="hide-settings" class="aside aside-md b-r" style="display: none">
                <section class="vbox">
                    <header class="dk header b-b">
                        <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                            data-target="#setting-nav">@icon('solid/bars')</a>
                        <a
                            class="hide-setting btn btn-icon btn-default btn-sm pull-right btn-h-vis-menu">@icon('solid/bars')</a>
                        <p class="h3 text-elipse-setting">Sub menu</p>
                    </header>
                    <section class="scrollable">
                        <section id="setting-nav" class="hidden-xs">
                            <ul class="nav nav-pills nav-stacked no-radius">
                                <li class="active">
                                    <a href="{{ route('rssfeedsettings.news') }}">
                                        @icon('solid/angle-right', 'text-' . get_option('theme_color'))
                                        News
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('rssfeedsettings.rss_data') }}">
                                        @icon('solid/angle-right', 'text-' . get_option('theme_color'))
                                        RSS Data
                                    </a>
                                </li>
                                {{-- <li>
                                <a href="{{route('rssfeedsettings.index')}}">
                            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                            RSS Settings
                            </a>
                            </li> --}}
                            </ul>
                        </section>
                    </section>
                </section>
            </aside>

            <aside>
                <section class="vbox">


                    <header class="header panel-heading bg-white b-b b-light">
                        <div class="header-flex-overflow" style="height: 48px;">
                            <div class="fwb-16">
                                <button
                                    class="show-setting btn btn-icon btn-default btn-sm m-r-xs">@icon('solid/bars')</button>
                                <span>
                                    @langapp('news')
                                </span>
                            </div>

                            <div class="ml-2 text-right news-btn">
                                @if (!empty(get_role_custom()))
                                    @if (@get_role_custom()['client'] != 1)
                                        <div class="btn-group" role="group"
                                            aria-label="Button group with nested dropdown">
                                            {{-- <button type="button" class="btn btn-secondary">1</button>
                                        <button type="button" class="btn btn-secondary">2</button> --}}

                                            <div class="btn-group" role="group">
                                                <button
                                                    class="btn btn-sm btn-{{ get_option('theme_color') }} dropdown-toggle"
                                                    data-toggle="dropdown"> @icon('solid/plus') @langapp('add')
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-left">
                                                    <li>
                                                        <a href="{{ route('rssfeedsettings.rss_news_create_news') }}"
                                                            data-toggle='ajaxModal'>
                                                            Create News
                                                        </a>
                                                        <a href="{{ route('rssfeedsettings.rss_data') }}" id="">
                                                            RSS Data
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>

                                        <a id="btn_client_view" href="{{ site_url('/news_client') }}"
                                            class="btn btn-sm btn-{{ get_option('theme_color') }}">
                                            <span data-rel="tooltip" title="Site View" data-placement="bottom"><i
                                                    class="fas fa-eye"></i><span class="hide-text"> Site View</span></span>
                                        </a>

                                        <a id="btn_rss_setting" href="{{ site_url('/rssfeedsettings') }}"
                                            class="btn btn-sm btn-{{ get_option('theme_color') }}">
                                            <span data-rel="tooltip" title="Setting" data-placement="bottom"><i
                                                    class="fas fa-cog icon"></i></span>
                                        </a>
                                    @endif
                                @endif

                                <a id="advance-search" href="#hide-advance-search"
                                    class="btn btn-sm btn-{{ get_option('theme_color') }}">
                                    <span data-rel="tooltip" title="Filter" data-placement="bottom"><i
                                            class="fas fa-filter"></i><span
                                            class="hide-text">@langapp('Search_Advance')</span></span>
                                </a>

                                @if (!empty(get_role_custom()))
                                    @if (@get_role_custom()['client'] != 1)
                                        <button type="button" id="btn_news_del_select" class="btn btn-sm btn-danger"
                                            value="bulk-delete" disabled>
                                            <span data-rel="tooltip" title="Delete"
                                                data-placement="bottom">@icon('solid/trash-alt')<span
                                                    class="hide-text">@langapp('delete')</span> </span>
                                        </button>
                                    @endif
                                @endif

                            </div>
                        </div>
                    </header>




                    {{-- Search --}}
                    {{-- Tab Content --}}
                    <section class="scrollable wrapper">
                        <section class="panel panel-default" id="hide-advance-search" style="display: none;">
                            <header class="panel-heading font-bold panel-header-blue">
                                <div class="row">
                                    <div class="col-md-12">
                                        <i class="fas fa-filter"></i> Filter
                                    </div>
                            </header>
                            <div class="panel-body" style="padding: 0 !important">
                                <div class="container-fluid" style="padding: 2rem;">

                                    <div class="row">
                                        <div class="col-lg-6 mb-1">
                                            <h5 class="font-weight-bold">Title</h5>
                                            <input type="text" class="form-control" name="keywords" id="keywords">
                                        </div>

                                        <div class="col-lg-6 mb-1">
                                            <h5 class="font-weight-bold">Public Date</h5>
                                            <div id="date_srange" class="text-center form-control"
                                                style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                                <i class="fa fa-calendar"></i>&nbsp;
                                                <span></span> <i class="fa fa-caret-down"></i>
                                            </div>
                                        </div>


                                        <div class="col-lg-6 col-md-6 mb-1">
                                            <h5 class="font-weight-bold">Filter By</h5>
                                            <div id="groupby-btn" class="btn-group special">
                                                <button id="source_btn" class="btn btn-grey check_group_by active">
                                                    <span> Source </span>
                                                </button>
                                                <button id="category_btn" class="btn btn-grey check_group_by">
                                                    <span> Category </span>
                                                </button>
                                            </div>

                                            <div id="source_search" class="m-t-10">
                                                <select name="news_source[]" id="news_source"
                                                    class="select2-option form-control" multiple="multiple">
                                                </select>
                                            </div>

                                            <div id="category_search" class="m-t-10">
                                                <select name="news_category[]" id="news_category"
                                                    class="select2-option form-control" multiple="multiple">
                                                    {{-- <option value="" >All</option> --}}
                                                    @foreach (@$category as $cate)
                                                        <option value="{{ $cate->id }}">{{ $cate->name }}</option>
                                                    @endforeach
                                                    {{-- <option value="1" selected>All</option> --}}
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 col-md-6 mb-1">
                                            <h5 class="font-weight-bold">Serverity</h5>
                                            <div id="btngroup_status" class="btn-group special mb-2">
                                                <button class="btn btn-grey check_serverity active" value=""
                                                    id="btn_search_all">
                                                    <span> All </span>
                                                </button>
                                                <button class="btn check_serverity btn-grey" value="critical">
                                                    <span> Critical </span>
                                                </button>
                                                <button class="btn check_serverity btn-grey" value="high">
                                                    <span> High </span>
                                                </button>
                                                <button class="btn check_serverity btn-grey" value="medium">
                                                    <span> Medium </span>
                                                </button>
                                                <button class="btn check_serverity btn-grey" value="low">
                                                    <span> Low </span>
                                                </button>
                                                <button class="btn check_serverity btn-grey" value="none">
                                                    <span> Information </span>
                                                </button>
                                            </div>
                                            <h5 class="font-weight-bold">Status</h5>
                                            <div id="groupby-status" class="btn-group special">
                                                <button class="btn btn-grey check_status active" id="all"
                                                    value="">
                                                    <span> All </span>
                                                </button>
                                                <button class="btn btn-grey check_status" value="1">
                                                    <span> Active </span>
                                                </button>
                                                <button class="btn btn-grey check_status" value="2">
                                                    <span> Inactive </span>
                                                </button>
                                            </div>

                                        </div>



                                    </div>
                                    <!-- ของเดิม
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <label for="">Status</label>
                                                    <select id="status_news" class="select2-option form-control">
                                                        <option value="">All</option>
                                                        <option value="1">Public</option>
                                                        <option value="2">Darft</option>
                                                    </select>
                                                </div>
                                            </div>
                                            -->
                                </div>
                            </div>
                            <div class="panel-footer">
                                <div class="row">
                                    <div class="col-lg-12 text-right">
                                        <button type="button" class="btn btn-info btn-responsive btn-fz-13"
                                            onclick="search()">
                                            <i class="fas fa-search"></i>
                                            @langapp('apply')
                                        </button>
                                        <button type="button" id="btn_rss_news_reset"
                                            class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                            <i class="fas fa-broom"></i>
                                            <span> Clear </span>
                                        </button>
                                        <button type="button" id="close_filter"
                                            class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                            <i class="fas fa-times"></i>
                                            <span> Close </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="m-b-10">
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 col-md-12">
                                    <div class="row">
                                        <div class="col-md-6 col-lg-6 mb-small-5px">
                                            <div class="loadhost backdrop-loader">
                                                <div class="loader4 centerloader"></div>
                                                <div class="loadding-text">Loading ...</div>
                                            </div>
                                            <div class="box-chart-color bg-white">
                                                <div class="d-flex align-items-center header-chart-p">
                                                    <img src="{{ asset('images/bar-chart.png') }}" alt=""
                                                        height="30px">
                                                    <h1 class="text-blue bold-500">Top 10 Source</h1>
                                                </div>
                                                <div class="divider-dark"></div>
                                                <div id="chart-top-source" class="h-chart"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-6 mb-small-5px">
                                            <div class="category backdrop-loader">
                                                <div class="loader4 centerloader"></div>
                                                <div class="loadding-text">Loading ...</div>
                                            </div>
                                            <div class="box-chart-color bg-white">
                                                <div class="d-flex align-items-center header-chart-p">
                                                    <img src="{{ asset('images/pie-chart.png') }}" alt=""
                                                        height="30px">
                                                    <h1 class="text-blue bold-500">Top 10 Categories</h1>
                                                </div>
                                                <div class="divider-dark"></div>
                                                <div id="chart-top-category" class="h-chart"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="scrollable">
                            <section class="panel panel-default">
                                <header class="panel-heading font-bold panel-header-blue">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <i class="fas fa-table"></i> Table News
                                        </div>
                                    </div>
                                </header>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="table-rss-news-template">
                                            <thead>
                                                <tr>
                                                    @if (!empty(get_role_custom()))
                                                        @if (@get_role_custom()['client'] != 1)
                                                            <th width="5%" class="no-sort">
                                                                <label>
                                                                    <input name="select_all" value="1"
                                                                        id="select-all" type="checkbox"
                                                                        class="select-chk" />
                                                                    <span class="label-text"></span>
                                                                </label>
                                                            </th>
                                                        @endif
                                                    @endif

                                                    {{-- <th>Site Name</th> --}}
                                                    <th style="width: 80%">Content</th>
                                                    {{-- <th>Source</th>
                                                <th>Title</th>
                                                <th>Category</th>
                                                <th>Serverity</th>
                                                <th>Data Status</th>
                                                <th>Public Date</th> --}}
                                                    {{-- <th width="30px">Modified Date</th> --}}
                                                    {{-- <th>View Count</th> 
                                                <th>Category</th>
                                                <th>Serverity</th>
                                                <th>Actor</th> --}}
                                                    @if (!empty(get_role_custom()))
                                                        @if (@get_role_custom()['client'] != 1)
                                                            <th width="5%">Status</th>
                                                        @endif
                                                    @endif

                                                    <th width="5%">Link</th>

                                                    @if (!empty(get_role_custom()))
                                                        @if (@get_role_custom()['client'] != 1)
                                                            <th width="5%">Action</th>
                                                        @endif
                                                    @endif

                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </section>
                        </section>


                    </section>
                </section>
            </aside>
        </section>

        <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
        <div class="modal" id="delete_rss_new_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
            aria-hidden="true" style="left: unset">
            <div class="modal-dialog modal-dialog-aside" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">@langapp('delete')</h4>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <p class="text-danger">@langapp('delete_warning') </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-default btn-rounded" data-dismiss="modal"><i
                                class="fas fa-times text-muted"></i> Close</a>
                        <button type="button" class="btn btn-info submit btn-rounded delete_domain_submit"
                            onclick="delete_rssNews_select_confirm()"><i class="fas fa-paper-plane"></i> OK</button>
                    </div>
                </div>
            </div>
        </div>

    </section>

    @push('pagestyle')
        @include('stacks.css.datatables')
        @include('stacks.css.form')
        @include('stacks.css.datepicker')
        @include('stacks.css.summernote')
        @include('stacks.css.highchart')
        <link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
        @include('stacks.css.multitext')
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
        @include('stacks.js.multitext')


        <script>
            active_btn('#groupby-btn .btn-grey');
            active_btn('#groupby-status .btn-grey');
            active_btn('#btngroup_status .btn-grey');


            $(function() {
                load_top_source();
                load_top_category();
                datatable();


                if ($('#source_btn').hasClass('active')) {
                    $('#source_search').show();
                    $('#category_search').hide();

                } else if ($('#category_btn').hasClass('active')) {
                    $('#category_search').show();
                    $('#source_search').hide();
                } else if ($('#source_all').hasClass('active')) {
                    $('#source_search').hide();
                    $('#category_search').hide();
                }
            });

            $('#source_btn').on('click', function() {

                if ($('#source_btn').hasClass('active')) {
                    $('#source_search').show();
                    $('#category_search').hide();
                    $("#news_category").val('').trigger("change");
                }

            });

            $('#category_btn').on('click', function() {

                if ($('#category_btn').hasClass('active')) {
                    $('#category_search').show();
                    $('#source_search').hide();
                    $("#news_source").val('').trigger("change");
                }
            });

            $(".check_status").click(function() {
                status_news = $(this).val();

            });

            $(".check_serverity").click(function() {
                status_serverity = $(this).val();

            });



            var search_val = 0;
            var keywords = null;
            var start_date = null;
            var end_date = null;
            var status_news = null;
            var status_serverity = null;
            var news_source = [];
            var news_category = [];
            var startDate = null;
            var endDate = null;
            var isDateSearch = null;

            function search() {
                search_val = 1;
                keywords = $('#keywords').val();
                start_date = $('#start_date').val();
                end_date = $('#end_date').val();

                news_source = $('#news_source').val();
                news_category = $('#news_category').val();
                startDate = $("#date_srange").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
                endDate = $("#date_srange").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');
                {{-- console.log(startDate);
        console.log(status_news);
        console.log(news_source);
        console.log(news_category);
        console.log(keywords); --}}
                {{-- load_top_source();
        load_top_category(); --}}
                datatable();

            }

            $(function() {

                var start = moment();
                {{-- moment().startOf('hour') --}} {{-- moment().subtract(1, 'year').startOf('year') --}}
                var end = moment();
                {{-- moment().startOf('hour').add(32, 'hour') --}} {{-- moment().subtract(0, 'year').endOf('year') --}}

                function cb(start, end) {
                    $('#date_srange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));

                }

                $('#date_srange').daterangepicker({
                    timePicker: true,
                    {{-- timePicker24Hour: true, --}}
                    startDate: start,
                    endDate: end,
                    locale: {
                        format: 'M/DD hh:mm A'
                        {{-- format: 'M/DD HH:mm A' --}}
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

                $('#date_srange').on('apply.daterangepicker', function(ev, picker) {
                    isDateSearch = 1;
                    if (!picker.startDate.isValid() || !picker.endDate.isValid()) {

                    }
                });

                cb(start, end);

                $("#btn_rss_news_reset").click(function() {

                    search_val = 0;
                    status_news = null;
                    status_serverity = null;
                    $("#keywords").val('');
                    $("#start_date").val('');
                    $("#end_date").val('');
                    $("#news_source").val('').trigger("change");
                    $("#news_category").val('').trigger("change");
                    $('.check_group_by').removeClass('active');
                    $('#source_btn').addClass('active');
                    $('.check_status').removeClass('active');
                    $('#all').addClass('active');
                    $('.check_serverity').removeClass('active');
                    $('#btn_search_all').addClass('active');
                    $('#source_search').show();
                    $('#category_search').hide();
                    start = moment();
                    end = moment();
                    cb(start, end);
                    isDateSearch = null;
                    {{-- load_top_source();
            load_top_category(); --}}
                    datatable();

                });

            });




            function change_news_active(code) {
                let checkState = $("#news-active-" + code).is(":checked") ? 1 : 0;
                axios.post('{{ route('rssfeedsettings.change_status_news') }}', {
                    active: checkState,
                    code: code,
                }).then(function(response) {
                    toastr.success(response.data.message, '@langapp('response_status')');
                    window.location.href = response.data.redirect;
                }).catch(function(error) {
                    var errors = error.errors;
                    var errorsHtml = "";
                    $.each(errors, function(key, value) {
                        errorsHtml += "<li>" + value[0] + "</li>";
                    });
                    toastr.error(errorsHtml, '@langapp('response_status')');
                });
            }
            $(function() {

                $('.datetimepicker-input').datetimepicker({
                    showClose: true,
                    showClear: true
                });
                {{-- $(document).ready(function () {
        $('#keywords').select2({
            tags: true,
            tokenSeparators: [' ']
        });
        $('#news_source').select2();


    }); --}}
            });

            $('#table-rss-news-template').on('click', '.select-chk', function() {
                if ($(this).is(':checked')) {

                    $('#btn_news_del_select').prop("disabled", false);
                } else {

                    if ($('.select-chk').filter(':checked').length < 1) {

                        $('#btn_news_del_select').attr('disabled', true);
                    }
                }
            });

            $('#table-rss-news-template').on('click', '.rss_new_id', function() {
                if ($(this).is(':checked')) {
                    $('#btn_news_del_select').prop("disabled", false);
                    {{-- if($('.rss_new_id').filter(':checked').length >= 5){
                document.getElementById("select-all").checked = true;
            } --}}
                } else {
                    document.getElementById("select-all").checked = false;
                    if ($('.rss_new_id').filter(':checked').length < 1) {
                        $('#btn_news_del_select').attr('disabled', true);
                    }
                }
            });

            let del_rss_new_select = [];

            $("#btn_news_del_select").click(function() {
                del_rss_new_select = [];
                $('#delete_rss_new_modal').modal('show');
            });

            function delete_rssNews_select_confirm() {

                $(".rss_new_id").each(function() {
                    if ($(this).is(":checked")) {
                        del_rss_new_select.push($(this).val());
                    }
                });



                $.ajax({
                    type: "POST",
                    url: "{{ route('rssfeedsettings.rss_news_delete_select') }}",
                    data: {
                        id: del_rss_new_select
                    },
                    beforeSend: function() {
                        $('.delete_domain_submit').html('Processing..<i class="fas fa-spin fa-spinner"></i>');
                        $('.delete_domain_submit').attr('disabled', true);
                    },
                    success: function(response) {
                        $('.delete_domain_submit').html('<i class="fas fa-check"></i> @langapp('save') </span>');
                        toastr.success(response.message, '@langapp('response_status')');
                        window.location.href = response.redirect;
                    },
                    error: function(error) {
                        $('.delete_domain_submit').attr('disabled', false);
                        var errors = error.response.data.errors;
                        var errorsHtml = '';
                        $.each(errors, function(key, value) {
                            errorsHtml += '<li>' + value[0] + '</li>';
                        });
                        toastr.error(errorsHtml, '@langapp('response_status') ');
                    }
                });
            }

            function find_source(source) {
                search_val = 1;
                news_source = [];
                news_category = [];
                news_source.push(source);
                {{-- load_top_source(); --}}
            }

            function find_category(category) {
                search_val = 1;
                news_category = [];
                news_source = [];
                news_category.push(category);
                {{-- load_top_category(); --}}
            }

            function datatable() {
                $('#table-rss-news-template').DataTable({
                    pageLength: 50,
                    processing: true,
                    serverSide: true,
                    destroy: true,
                    "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
                    ajax: {

                        type: "POST",
                        url: '{!! route('rssfeedsettings.rss_news_table') !!}',
                        data: function(d) {
                            d.keywords = keywords;
                            d.status_news = status_news;
                            d.status_serverity = status_serverity;
                            d.news_source = news_source;
                            d.news_category = news_category;
                            d.search_val = search_val;
                            d.startDate = startDate;
                            d.endDate = endDate;
                            d.isDateSearch = isDateSearch;
                            {{-- return JSON.stringify( d ); --}}
                            return d;

                        },
                    },
                    "fnDrawCallback": function(oSettings) {
                        var numofChar = 100;
                        var showmores = document.querySelectorAll(".showmore");
               
                        showmores.forEach(showmore => {
                            var text = showmore.textContent.replace("ดูเพิ่มเติม",'');
                            if (text.length < numofChar) {
                                showmore.lastChild.style.display = "none";
                            } else {
                                var displaytext = text.slice(0, numofChar);
                                var moretext = text.slice(numofChar);
                                var button = showmore.lastChild;
                                console.log(button);
                                showmore.innerHTML = displaytext + '<span class="dots"> ... </span><span class="hidemore" style="display:none;">' + moretext + '</span>';
                                showmore.appendChild(button);
                            }
                        });
                    },
                    columns: [
                        @if (!empty(get_role_custom()))
                            @if (@get_role_custom()['client'] != 1)
                                {
                                    data: 'chk',
                                    orderable: false,
                                    searchable: false,
                                    sortable: false,
                                    className: 'w-10'
                                },
                            @endif
                        @endif

                        {{-- {
                    data: 'site_name',
                    name: 'site_name'
                }, --}} {
                            orderable: false,
                            data: 'content_detail',
                            name: 'content_detail'
                        },
                        {{-- {
                    data: 'title',
                    name: 'title',
                },
                {
                    data: 'cate',
                    name: 'cate',
                    className: 'w-130'
                },
                {
                    data: 'serverity',
                    name: 'serverity',
                    className: 'text-center'
                },
                {
                    data: 'data_status',
                    name: 'data_status',
                    className: 'w-10 text-center'
                },
                {
                    data: 'public_date',
                    name: 'public_date',
                    className: 'no-wrap'
                },
                {
                    data: 'view',
                    name: 'view',
                    className: 'w-10 text-center'
                },
         
                {
                    data: 'category',
                    name: 'category',
                    className: 'w-10 text-center'
                },
                {
                    data: 'serverity',
                    name: 'serverity',
                    className: 'w-10 text-center'
                },
                {
                    data: 'actor',
                    name: 'actor',
                    className: 'no-wrap text-center'
                },
                --}}
                        @if (!empty(get_role_custom()))
                            @if (@get_role_custom()['client'] != 1)
                                {
                                    data: 'status',
                                    name: 'status',
                                    className: 'w-10 text-center'
                                },
                            @endif
                        @endif

                        {
                            data: 'link',
                            name: 'link',
                            className: 'no-wrap'
                        },
                        @if (!empty(get_role_custom()))
                            @if (@get_role_custom()['client'] != 1)
                                {
                                    data: 'action',
                                    name: 'action',
                                    className: 'no-wrap'
                                },
                            @endif
                        @endif

                    ]
                });
            }


            {{-- columnDefs: [
                    {
                        targets: 2,
                        render: function (data, type, full, meta) {
                            let new_html = '';
                            let date_day = '2021-01-01';
                            let date = full.public_date;
                            let date_sp = date.split(" ");
                            if(date_sp.length > 0) {
                                date_day = date_sp[0];
                            }
                            let date_now = '{{Carbon::now()}}';
                            let date_now_sp = date_now.split(" ");
                            if(date_now_sp.length > 0) {
                                var date_now_day = date_now_sp[0];
                            }
                            
                            if ((date_day) == (date_now_day)) {
                                new_html += `<img src="{{asset('images/icon/new.png')}}" style="width:40px; border-radius: 10px;">`;
                                new_html += `<span class="badge" style="background-color: #2196f3;">New</span>`;
                                console.log(new_html);
                            }

                            return new_html+full.title;
                        },
                    },
                ] --}}



            $(document).ready(function() {

                $('#source_create').select2({
                    allowClear: true,
                    tags: true,
                    width: '100%',
                    ajax: {
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'post',
                        dataType: "json",
                        url: '/rssfeedsettings/rss_data/source',
                        delay: 250,
                        data: function(params) {
                            return {
                                searchTerm: params.term || '',
                                pageNum: params.page || 1,
                            }
                        },
                        processResults: function(data) {
                            return {
                                results: $.map(data, function(item) {
                                    return {
                                        text: item.name,
                                        id: item.name,
                                        value: item.name
                                    }
                                })
                            };
                        },
                    }
                });

                $('#news_source').select2({
                    allowClear: false,
                    tags: true,
                    width: '100%',
                    ajax: {
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        type: 'post',
                        dataType: "json",
                        url: '/rssfeedsettings/rss_data/source',
                        delay: 250,
                        data: function(params) {
                            return {
                                searchTerm: params.term || '',
                                pageNum: params.page || 1,
                            }
                        },
                        processResults: function(data) {
                            return {
                                results: $.map(data, function(item) {
                                    return {
                                        text: item.name,
                                        id: item.name,
                                        value: item.id
                                    }
                                })
                            };
                        },
                    }
                });

                {{-- $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') }); --}}
                $('.datetimepicker-input').datetimepicker({
                    showClose: true,
                    showClear: true
                });
            });
            var form_save = '.formSaving';
            $('.formPreview').click(function() {
                form_save = '.formPreview';
            });
            $('.formDraft').click(function() {
                form_save = '.formDraft';
            });
            var number = 0;
            $('.ajaxifyFormCreate').submit(function(event) {
                number++;
                if (number == 1) {
                    let category = $('#category option:selected').val();
                    {{-- let topic = $('#topic option:selected').val(); --}}
                    let title_th = $('#title_th').val();
                    let detail_th = $('#detail_th').val();
                    let source = $('#source_create').val();
                    if (form_save == '.formSaving') {
                        if (category == undefined) {
                            $('#label_category').css('color', '#a94442');
                            $('#category').css('border-color', '#a94442');
                        } else {
                            $('#label_category').css('color', '#656d78');
                            $('#category').css('border-color', '#656d78');
                        }
                        if (source == undefined) {
                            $('#label_source').css('color', '#a94442');
                            $('#source_create').css('border-color', '#a94442');
                        } else {
                            $('#label_source').css('color', '#656d78');
                            $('#source_create').css('border-color', '#656d78');
                        }
                        if (topic == undefined) {
                            $('#label_topic').css('color', '#a94442');
                            $('#topic').css('border-color', '#a94442');
                        } else {
                            $('#label_topic').css('color', '#656d78');
                            $('#topic').css('border-color', '#656d78');
                        }
                        if (title_th == '') {
                            $('#label_title_th').css('color', '#a94442');
                            $('#title_th').css('border-color', '#a94442');
                        } else {
                            $('#label_title_th').css('color', '#656d78');
                            $('#title_th').css('border-color', '#656d78');
                        }
                        if (detail_th == '') {
                            $('#label_detail_th').css('color', '#a94442');
                            $('#detail_th').css('border-color', '#a94442');
                        } else {
                            $('#label_detail_th').css('color', '#656d78');
                            $('#detail_th').css('border-color', '#656d78');
                        }

                        if (category == undefined || title_th == '' || detail_th == '' || source == undefined) {
                            return false;
                        }
                    } else if (form_save == '.formDraft') {
                        if (title_th == '') {
                            $('#label_title_th').css('color', '#a94442');
                            $('#title_th').css('border-color', '#a94442');
                        } else {
                            $('#label_title_th').css('color', '#656d78');
                            $('#title_th').css('border-color', '#656d78');
                        }
                        if (detail_th == '') {
                            $('#label_detail_th').css('color', '#a94442');
                            $('#detail_th').css('border-color', '#a94442');
                        } else {
                            $('#label_detail_th').css('color', '#656d78');
                            $('#detail_th').css('border-color', '#656d78');
                        }
                        if (title_th == '' || detail_th == '') {
                            return false;
                        }
                    }
                    $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
                    $('.formSaving').attr('disabled', true);
                    event.preventDefault();
                    var data = new FormData(this);
                    if (form_save == '.formPreview') {
                        data.append('formsubmit', 'formPreview');
                    } else if (form_save == '.formDraft') {
                        data.append('formsubmit', 'formDraft');
                    }
                    axios.post($(this).attr("action"), data).then(function(response) {
                        toastr.success(response.data.message, '@langapp('response_status') ');
                        $(form_save).html('<i class="fas fa-check"></i> @langapp('save') </span>');
                        window.location.href = response.data.redirect;
                    }).catch(function(error) {
                        if (error.response.data.exception) {
                            $('.formSaving').attr('disabled', false);
                            toastr.error('@langapp('request_failed')', '@langapp('response_status') ');
                            $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                        } else {
                            $('.formSaving').attr('disabled', false);
                            var errors = error.response.data.errors;
                            var errorsHtml = '';
                            $.each(errors, function(key, value) {
                                errorsHtml += '<li>' + value[0] + '</li>';
                            });
                            toastr.error(errorsHtml, '@langapp('response_status') ');
                            $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                        }
                    });
                }
            });

            function showMore(btn){
                var post = btn.parentElement;
                post.querySelector(".dots").style.display = post.querySelector(".dots").style.display === "none" ? '' : 'none';
                post.querySelector(".hidemore").style.display = post.querySelector(".hidemore").style.display=== "none" ? '' : 'none';
                post.querySelector(".btn-showmore").textContent = post.querySelector(".btn-showmore").textContent === "ดูเพิ่มเติม" ? "ย่อ" : 'ดูเพิ่มเติม';

            }

            function copy_link(value) {
                var tempInput = document.createElement("input");
                tempInput.style = "position: absolute; left: -1000px; top: -1000px";
                tempInput.value = value;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand("copy");
                document.body.removeChild(tempInput);
            }

            function load_top_source() {
                if (search_val == 1 || search_val == 0) {
                    $("#chart-top-source").html('');
                }

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{!! route('rssfeedsettings.load_top_source') !!}',
                    type: "POST",
                    data: ({
                        keywords: keywords,
                        status_news: status_news,
                        status_serverity: status_serverity,
                        news_source: news_source,
                        news_category: news_category,
                        search_val: search_val,
                        startDate: startDate,
                        endDate: endDate,
                        isDateSearch: isDateSearch,

                    }),
                    beforeSend: function() {
                        $('.loadhost').show();

                    },
                }).done(function(data) {

                    $('.loadhost').hide();
                    const chart_top_source = Highcharts.chart('chart-top-source', {
                        chart: {
                            type: 'column',
                            scrollablePlotArea: {
                                minWidth: 400,
                            },
                        },
                        title: {
                            text: null
                        },
                        xAxis: {
                            type: 'category',
                            crosshair: true,
                            labels: {
                                overflow: 'justify',
                                autoRotation: false,
                                textAlign: 'center',
                            }

                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: 'Values'
                            }
                        },
                        tooltip: {
                            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                            pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                '<td style="padding:0"><b>{point.y:.1f}</b></td></tr>',
                            footerFormat: '</table>',
                            shared: true,
                            useHTML: true
                        },
                        plotOptions: {
                            column: {
                                pointPadding: 0.2,
                                borderWidth: 0
                            },
                            series: {
                                pointWidth: 30,
                                color: '#ffc107',
                                align: 'center',
                                cursor: 'pointer',
                                point: {
                                    events: {
                                        click: function() {
                                            search_val = 1;
                                            news_source = [];
                                            news_category = [];
                                            news_source.push(this.name == 'None' ? "" : this.name);
                                            datatable();
                                        }
                                    }
                                }
                            },
                            style: {
                                background: '#fff'
                            }
                        },
                        legend: {
                            enabled: false
                        },
                        series: [{
                            name: 'Population',
                            data: data,
                            dataLabels: {
                                enabled: true,
                                color: '#333',
                                align: 'center',
                                format: '{point.y{{-- :.1f --}}}',
                                y: 0,
                                style: {
                                    fontSize: '13px',
                                    fontFamily: 'Verdana, sans-serif',
                                }
                            }
                        }]
                    });

                }).fail(function(jqXHR, ajaxOptions, thrownError) {
                    $('.loadhost').hide();
                    console.log("No response from server");
                });

            }

            function load_top_category() {
                if (search_val == 1 || search_val == 0) {
                    $("#chart-top-category").html('');
                }

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{!! route('rssfeedsettings.load_top_category') !!}',
                    type: "POST",
                    data: ({
                        keywords: keywords,
                        status_news: status_news,
                        status_serverity: status_serverity,
                        news_source: news_source,
                        news_category: news_category,
                        search_val: search_val,
                        startDate: startDate,
                        endDate: endDate,
                        isDateSearch: isDateSearch,

                    }),
                    beforeSend: function() {
                        $('.category').show();

                    },
                }).done(function(data) {

                    $('.category').hide();
                    const chart_top_category = Highcharts.chart('chart-top-category', {
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
                                style: {
                                    background: '#fff'
                                },
                                align: 'center',
                                cursor: 'pointer',
                                point: {
                                    events: {
                                        click: function() {
                                            search_val = 1;
                                            news_source = [];
                                            news_category = [];
                                            news_category.push(this.data);
                                            datatable();
                                        }
                                    }
                                }
                            }
                        },
                        series: [{
                            colorByPoint: false,
                            data: data,
                        }],
                    });


                }).fail(function(jqXHR, ajaxOptions, thrownError) {
                    $('.loadhost').hide();
                    console.log("No response from server");
                });

            }
        </script>
    @endpush
@endsection
