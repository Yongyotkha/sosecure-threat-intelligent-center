@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">

            {{-- Head --}}

            <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
                <div class="header-flex-overflow m-t-10">
                    <div class="fwb-16" style="display: flex;align-items: center;">
                        <a href="{{route('news.index')}}" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                            @icon('solid/arrow-left')
                        </a>
                        <span>
                            @langapp('news')
                        </span>
                    </div>

                    <div class="ml-2 text-right">
                        <div class="text-left max-w-select" style="display:inline-block;">
                            <select name="" id="select-site" class="select2-option form-control select-site" onchange="changeSite(value)">
                                <option value="">All Site</option>
                                @if($SiteSettings)
                                @foreach($SiteSettings as $SiteSettings_val)
                                <option value="{{$SiteSettings_val->id}}">{{$SiteSettings_val->name}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>

                        <a id="advance-search" href="#hide-advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                            <span data-rel="tooltip" title="Filter" data-placement="bottom"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></span>
                        </a>

                    </div>
                </div>
            </header>

            {{-- Search --}}
            {{-- Tab Content --}}
            <section id="scrollable_news" class="scrollable wrapper">
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

                                    <div class="col-lg-12 mb-1">
                                        <h5 class="font-weight-bold">Search</h5>
                                        <input type="text" id="news_title_search" class="form-control">
                                    </div>

                                    <div class="col-lg-4 mb-1">
                                        <h5 class="font-weight-bold">Category</h5>
                                        <select id="news_category" class="select2-option form-control">
                                            <option value="" >All</option>
                                            @foreach(@$Category as $cate)
                                            <option value="{{$cate->code}}" >{{$cate->name}}</option>
                                            @endforeach
                                            {{-- <option value="1" selected>All</option> --}}
                                        </select>
                                    </div>

                                    <div class="col-lg-4 mb-1">
                                        <h5 class="font-weight-bold">Date</h5>
                                        <div id="newsrange" class="text-center" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                            <i class="fa fa-calendar"></i>&nbsp;
                                            <span></span> <i class="fa fa-caret-down"></i>
                                        </div>
                                    </div>



                                    <div class="col-lg-4 mb-1">
                                        <h5 class="font-weight-bold">Filter By</h5>
                                        <div id="groupby-btn" class="btn-group special">
                                            <button id="source_btn" class="btn btn-grey check_group_by active">
                                                <span> Related News </span>
                                            </button>
                                            <button id="category_btn" class="btn btn-grey check_group_by">
                                                <span> Thai </span>
                                            </button>
                                            <button id="category_btn" class="btn btn-grey check_group_by">
                                                <span> English </span>
                                            </button>
                                        </div>      
                                    </div>
                                </div>
                            <!--
                            <div class="row">
                                <div class="col-lg-4 text-center">
                                    <div style="margin-top: 8px;">
                                        <label class="mr-3">
                                            <input type="checkbox" name="related_news" id="related_news" value="TRUE">
                                            <span class="label-text" style="font-size: 16px;">Related News</span>
                                        </label>
        
                                        <label class="mr-3">
                                            <input type="checkbox" name="lang_th" id="lang_th" value="TRUE">
                                            <span class="label-text" style="font-size: 16px;">Thai</span>
                                        </label>
        
                                        <label class="mr-3">
                                            <input type="checkbox" name="lang_en" id="lang_en" value="TRUE">
                                            <span class="label-text" style="font-size: 16px;">English</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        -->
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-lg-12 text-right">
                            <button type="button" id="btn_news_search" class="btn btn-info btn-responsive btn-fz-13">
                                <i class="fas fa-search"></i>
                                @langapp('apply')
                            </button>
                            <button type="button" id="btn_news_reset" class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                <i class="fas fa-broom"></i>
                                <span> Clear </span>
                            </button>
                            <button type="button" id="close_filter" class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
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
                                        <img src="{{asset('images/bar-chart.png')}}" alt="" height="30px">
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
                                        <img src="{{asset('images/pie-chart.png')}}" alt="" height="30px">
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

            <div class="tabbable">
                <ul class="nav nav-tabs nav-tabs-highlight">
                    <li class="active"><a href="#tab_related_news" data-toggle="tab">News (<span id="count_news"></span>)</a></li>
                    <li id="tab-bookmark"><a href="#tab_lastest_news" data-toggle="tab">My Bookmarks (<span id="count_news_bookmark"></span>)</a></li>   
                    <li class="pull-right">
                        {{-- <button id="" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                            <span>Bookmarks</span>
                        </button> --}}
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_related_news">
                        <section class="panel panel-default border-n">
                            <div id="main-list" class="row m-b-md">
                                <div class="col-md-12">
                                    <div id="list_news"></div>
                                    <div class="ajax-loading loading-more" style="display: none;margin-top:15px;">Loading&nbsp;<span class="content-spinner-loading-inline"></span></div>
                                    {{-- <div class="list-news">
                                        <div class="checkbox-news-select">
                                            <label class="mr-3">
                                                <input type="checkbox" name="" class="chk-bookmark">
                                                <span class="label-text checkbox-news-input"></span>
                                            </label>
                                        </div>
                                        <div class="content-news-text">
                                            <a href="{{route('news.news_detail')}}">

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
                                            <img src="{{asset('images/bar-chart.png')}}" alt="" height="30px">
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
                                            <img src="{{asset('images/pie-chart.png')}}" alt="" height="30px">
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


                <div class="tabbable">
                    <ul class="nav nav-tabs nav-tabs-highlight">
                        <li class="active"><a href="#tab_related_news" data-toggle="tab">News (<span id="count_news"></span>)</a></li>
                        <li id="tab-bookmark"><a href="#tab_lastest_news" data-toggle="tab">My Bookmarks (<span id="count_news_bookmark"></span>)</a></li>   
                        <li class="pull-right">
                            {{-- <button id="" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                                <span>Bookmarks</span>
                             </button> --}}
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_related_news">
                            <section class="panel panel-default border-n">
                                <div id="main-list" class="row m-b-md">
                                    <div class="col-md-12">
                                        <div id="list_news"></div>
                                        <div class="ajax-loading loading-more" style="display: none;margin-top:15px;">Loading&nbsp;<span class="content-spinner-loading-inline"></span></div>
                                        {{-- <div class="list-news">
                                            <div class="checkbox-news-select">
                                                <label class="mr-3">
                                                    <input type="checkbox" name="" class="chk-bookmark">
                                                    <span class="label-text checkbox-news-input"></span>
                                                </label>
                                            </div>
                                            <div class="content-news-text">

                                                <a href="{{route('news.news_detail')}}">
                                                    <span class="head-news-text">WhatsApp’s new fact-check feature lets users identify fake information</span>
                                                </a>
                                            </a>
                                            <div class="entry-meta">
                                                <span class="entry-date"> <i class="fas fa-calendar-alt"></i> August 4th, 2020</span>
                                                <span class="entry-view"> <i class="fas fa-eye"></i> 300</span>
                                                <span>&nbsp;Lorem ipsum dolor sit amet consectetur adipisicing elit. Placeat voluptates delectus praesentium architecto iure reprehenderit soluta qui sapiente quaerat, explicabo non mollitia officiis sit porro consequuntur itaque, iusto ad quas.</span>
                                            </div>
                                        </div>
                                        <div class="content-news-image">
                                            <a href="{{route('news.news_detail')}}">
                                                <img src="https://images.unsplash.com/photo-1602524207251-8ad5d2b2c303?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1050&q=80" alt="">
                                            </a>
                                        </div>
                                        <div class="action-bookmark">
                                            <i class="fas fa-bookmark" id="mark1" onclick="Bookmarks(this)"></i>
                                        </div>
                                    </div> --}}


                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="tab-pane" id="tab_lastest_news">
                        <section class="panel panel-default">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div id="list_news_book_mark"></div>
                                </div>
                            </div>
                        </section>  
                    </div>
                </div>
            </div>
        </section>
    </section>
</section>
<a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@push('pagestyle')

    @include('stacks.css.datatables')
    @include('stacks.css.datepicker')
    @include('stacks.css.form')
    @include('stacks.css.highchart')
    <link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css"/>

@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.form')
@include('stacks.js.advanced_search')
@include('stacks.js.activebutton')
@include('stacks.js.highchart')

<script>

    active_btn('#groupby-btn .btn-grey');

    var search_val = 0;
    var search_val = 0;
    var keywords = null;
    var start_date = null;
    var end_date = null;
    var status_news = null;
    var news_source = [];
    var news_category = [];
    var startDate = null;
    var endDate = null;
    var isDateSearch = null;


    var f_search = 0;
    var page = 1; 
    var page_stop = true;
    {{--load_more(page);--}}
    load_more_search(page);
    load_more_book_mark(page);
    var ck = 1;
    var site = null;
    var startDate = null;
    var endDate = null;
    var lang_en = null;
    var lang_th = null;
    var related_news = null;
    var news_category = null;
    var news_title_search = null;
    $('#scrollable_news').scroll(function(event) {
        let scrolltop = $('#scrollable_news').scrollTop();
        let tab_height = $('#scrollable_news').height();
        let docu_height = $(document).height();
        console.log(scrolltop+'  '+tab_height+'   '+docu_height);
        if($('#scrollable_news').scrollTop() + $('#scrollable_news').height() >= $(document).height()) {

            console.log(555);
            if(page_stop){
                if(ck == 1) {
                    page = page+1;
                    load_more_search(page,f_search);
                    
                    ck++;
                    
                }
                
                {{--load_more(page);--}}
                
                setTimeout(function(){ 

                }, 10000);
            }
        }
    });

    function load_more_book_mark(page){
        $("#list_news_book_mark").empty();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/news/jqueryLoadMoreNewsBookmark?page=" + page,
            type: "get",
            datatype: "html",
            beforeSend: function(){
                $('.ajax-loading').show();
                $('#count_news_bookmark').text(0);
            },
        }).done(function(data){
            if(data.html.length == 0){
                {{--page_stop = false;--}}
                $('.ajax-loading').hide();
                $('#count_news_bookmark').text(0);
                return;
            }
            $('#count_news_bookmark').text(data.count);
            $('.ajax-loading').hide();
            {{--$("#list_news_book_mark").append(data.html);--}}
            $("#list_news_book_mark").html(data.html);
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            console.log("No response from server");
        });
    }

    function load_more(page){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/news/jqueryLoadMoreNews?page=" + page,
            type: "get",
            datatype: "html",
            beforeSend: function(){
                $('.ajax-loading').show();
                $('#count_news').text(0);
            },
        }).done(function(data){
            if(data.html.length == 0){
                page_stop = false;
                $('.ajax-loading').hide();
                {{--$('#count_news').text(0);--}}
                return;
            }
            $('#count_news').text(data.count);
            $('.ajax-loading').hide();
            $("#list_news").append(data.html);
            page++;
            
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            console.log("No response from server");
        });
    }


    function load_more_search(page,f_search=0){
        if(page == 1) {
            $("#list_news").html('');   
        }
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/news/jqueryLoadMoreNews?page=" + page,
            type: "get",
            data: ({
                title:news_title_search,
                cate:news_category,
                related_news:related_news,
                lang_th:lang_th,
                lang_en:lang_en,
                date_start:startDate,
                date_end:endDate,
                f_search:f_search,
                site_id:site
            }),
            {{--datatype: "html",--}}
            beforeSend: function(){
                $('.ajax-loading').show();
                {{--loading('load');--}}
                {{--f_loading(1);--}}
                $('#count_news').text(0);
            },
        }).done(function(data){
            if(data.html.length == 0){
                ck = 0;
                page_stop = false;
                $('.ajax-loading').hide();
                {{--f_loading_stop(1);--}}
                {{--$('#count_news').text(0);--}}
                return;
            } else {
                ck = 1;
                {{--f_loading_stop(1);--}}
                let count_n = $('#count_news').text();
                let count_search = data.count;
                let count_n_all = parseInt(count_n) + parseInt(count_search);
                {{--$('#count_news').text(data.count);--}}
                $('#count_news').text(data.count);
                $('.ajax-loading').hide();
                $("#list_news").append(data.html); 
            }

        }).fail(function(jqXHR, ajaxOptions, thrownError){
            console.log("No response from server");
        });
    }

    function load_more_topic(topic_id){
        $("#list_news").empty();
        $("#count_topic_" + topic_id).empty();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/news/jqueryLoadMoreNewsTopic?topic_id=" + topic_id,
            type: "get",
            datatype: "html",
            beforeSend: function(){
                $('.ajax-loading').show();
                $('#count_news').text(0);
            },
        }).done(function(data){
            if(data.html.length == 0){
                page_stop = false;
                $('.ajax-loading').hide();
                $('#count_news').text(0);
                return;
            }
            $('#count_news').text(data.count);
            $('.ajax-loading').hide();
            $("#list_news").append(data.html);   
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            console.log("No response from server");
        });
    }
    function Bookmarks(ele, news_id){
        {{--page_stop = true;--}}
        {{--page = 1;--}}
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/news/bookmark?news_id=" + news_id,
            type: "get",
            datatype: "json",
        }).done(function(data){
            if($(ele).hasClass("bookmark-active")) {
                $(ele).removeClass("bookmark-active"); 
            } else {
                $(ele).addClass("bookmark-active"); 
            }
            load_more_book_mark(page);
            {{--load_more_search(page,f_search);--}}
            page = 1;
            page_stop = true;
            load_more_search(page,f_search);
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            console.log("No response from server");
        });
    }

    $('#link_tab_bookmark').attr("href","#");
    $('#main-list').on('click', '.chk-bookmark', function () {
        if ($(this).is(':checked')) {
            $('#tab-bookmark').removeClass("disabled");
            $('#link_tab_bookmark').attr("href","#tab_lastest_news");
        } else {
            if ($('.chk-bookmark').filter(':checked').length < 1){
                $('#tab-bookmark').addClass('disabled');
                $('#link_tab_bookmark').attr("href","#");
            }
        }
    });

    $('#sources_select').select2();
    $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') });

</script>

<script type="text/javascript">
    $(function() {

        var start = moment().subtract(1, 'month');{{--moment().startOf('hour')--}} {{--moment().subtract(1, 'year').startOf('year')--}}
        var end = moment();{{--moment().startOf('hour').add(32, 'hour')--}} {{--moment().subtract(0, 'year').endOf('year')--}}

        function cb(start, end) {
            $('#newsrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            console.log(start.format('YYYY-MM-DD hh:mm A'));
        }

        $('#newsrange').daterangepicker({
            timePicker: true,
            {{--timePicker24Hour: true,--}}
            startDate: start,
            endDate: end,
            locale: {
                format: 'M/DD hh:mm A'{{--format: 'M/DD HH:mm A'--}}
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

        $("#btn_news_search").click(function() {

            startDate=  $("#newsrange").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
            endDate=  $("#newsrange").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');

            news_title_search = $("#news_title_search").val();
            news_category = $("#news_category").val();
            related_news = false;
            if($("#related_news").is(":checked")) {
                related_news = true;
            }
            lang_th = false;
            if($("#lang_th").is(":checked")) {
                lang_th = true;
            }
            lang_en = false;
            if($("#lang_en").is(":checked")) {
                lang_en = true;
            }
            console.log(news_category);
            console.log('related_news '+related_news);
            console.log('lang_th '+lang_th);
            console.log('lang_en '+lang_en);
            f_search = 1;
            page = 1;
            $('#count_news').text(0);
            page_stop = true;
            load_more_search(page,f_search);

        });


        $("#btn_news_reset").click(function() {
            $("#news_title_search").val('');
            $("#news_category").val('').trigger("change");
            $("#related_news").prop("checked",false);
            $("#lang_th").prop("checked",false);
            $("#lang_en").prop("checked",false);
            $("#select-site").val('').trigger("change");
            start = moment();
            end = moment();
            cb(start, end);
            startDate = null;
            endDate = null;
            lang_en = null;
            lang_th = null;
            related_news = null;
            news_category = null;
            news_title_search = null;
            f_search = 0;
            page = 1;
            $('#count_news').text(0);
            page_stop = true;
            load_more_search(page,f_search)

        });
        load_top_source();
        load_top_category();
    });



    function changeSite(value){

        site = value;
        page = 1;
        $('#count_news').text(0);
        page_stop = true;
        load_more_search(page,f_search);
    }


    function load_top_source(){
        if(search_val == 1 || search_val == 0) {
            $("#chart-top-source").html('');             
        }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{!! route('rssfeedsettings.load_top_source') !!}',
            type: "POST",
            data: ({
                keywords : keywords,
                status_news : status_news,
                news_source : news_source,
                news_category : news_category,
                search_val : search_val,
                startDate : startDate,
                endDate : endDate,
                isDateSearch : isDateSearch,

            }),
            beforeSend: function(){
                $('.loadhost').show();

            },
        }).done(function(data){

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
                    series:{
                        pointWidth: 30,
                        color : '#ffc107',
                        align: 'center',
                        cursor: 'pointer',
                        point: {
                            events: {
                                click: function () {
                                    search_val = 1;
                                    news_source = [];
                                    news_category = [];
                                    news_source.push(this.name == 'None' ? "" : this.name);
                                    datatable();
                                }
                            }
                        }
                    },
                    style:{
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
                        format: '{point.y{{--:.1f--}}}',
                        y: 0, 
                        style: {
                            fontSize: '13px',
                            fontFamily: 'Verdana, sans-serif',
                        }
                    }
                }]
            });

        }).fail(function(jqXHR, ajaxOptions, thrownError){
            $('.loadhost').hide();
            console.log("No response from server");
        });
        
    }

    function load_top_category(){
        if(search_val == 1 || search_val == 0) {
            $("#chart-top-category").html('');             
        }

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{!! route('rssfeedsettings.load_top_category') !!}',
            type: "POST",
            data: ({
                keywords : keywords,
                status_news : status_news,
                news_source : news_source,
                news_category : news_category,
                search_val : search_val,
                startDate : startDate,
                endDate : endDate,
                isDateSearch : isDateSearch,

            }),
            beforeSend: function(){
                $('.category').show();

            },
        }).done(function(data){

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
                        style:{
                            background: '#fff'
                        },
                        align: 'center',
                        cursor: 'pointer',
                        point: {
                            events: {
                                click: function () {
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


        }).fail(function(jqXHR, ajaxOptions, thrownError){
            $('.loadhost').hide();
            console.log("No response from server");
        });
        
    }



</script>
@endpush
@endsection