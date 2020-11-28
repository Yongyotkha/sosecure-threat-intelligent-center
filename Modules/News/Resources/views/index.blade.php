@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">

            {{-- Head --}}
            <header class="header panel-heading bg-white b-b b-light">
                <div class="bc-head">@langapp('news')</div>    
                <div class="pull-right" style="margin-top: 8px;">
                    <select name="" id="" class="select2-option form-control select-site" style="min-width: 100px">
                        <option value="1">All Site</option>
                    </select>
                </div>
                <button id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                    <span>Search Advance</span>
                 </button>
            </header>

            {{-- Search --}}
            {{-- Tab Content --}}
            <section class="scrollable wrapper bg-grey">
                <section class="panel panel-default" id="hide-advance-search">
                    <div class="container-fluid" style="padding: 2rem;">
                        <div class="row m-b-md">
                            <div class="col-lg-12">
                                <div class="row d-flex align-items-center">
                                    <label for="" class="col-sm-1 col-xs-12 col-form-label">Search</label>
                                    <div class="col-sm-11 col-xs-12">
                                        <input type="text" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="row d-flex align-items-center">
                                    <label for="" class="col-sm-3 col-xs-12 col-form-label">Sources</label>
                                    <div class="col-sm-9 col-xs-12">
                                        <section id="sources_select" class="select2-option form-control">
                                            <option value="1" selected>All</option>
                                        </section>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 text-center">
                                <div id="newsrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div>
                            </div>
                            <div class="col-lg-4 text-center">
                                <div style="margin-top: 8px;">
                                    <label class="mr-3">
                                        <input type="checkbox" name="" value="TRUE">
                                        <span class="label-text" style="font-size: 16px;">Related News</span>
                                    </label>
    
                                    <label class="mr-3">
                                        <input type="checkbox" name="" value="TRUE">
                                        <span class="label-text" style="font-size: 16px;">Thai</span>
                                    </label>
    
                                    <label class="mr-3">
                                        <input type="checkbox" name="" value="TRUE">
                                        <span class="label-text" style="font-size: 16px;">English</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 text-right mt-2">
                                <button class="btn btn-info btn-responsive">
                                    <i class="fas fa-search"></i>
                                    Search
                                </button>
                                <button class="btn btn-default btn-responsive" style="white-space: nowrap">
                                    <i class="fas fa-broom"></i>
                                    <span> Clear </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="panel panel-default">
                    <div class="container-fluid" style="padding:1rem 2rem;">
                        <div class="row">
                            <div class="col-lg-12">
                                <h3 class="mb-3">Topics</h3>
                            </div>
                            <div class="col-lg-12">
                                <ul class="topics-news-list">
                                    @foreach($topic as $key => $data)
                                    <li>
                                        <a href="javascript:void(0);" onclick="load_more_topic({{{ $data -> id }}})">{{ $data -> name }} 
                                            <?php $number[$data -> id] = 0 ?>
                                            @if(@$NewsTopic[$key]->topic_id == $data -> id)
                                                <?php $number[$data -> id]++ ?>
                                                @if(@$ReadTopic[$key]->topic_id == $data -> id)
                                                    <?php $number[$data -> id]-- ?>
                                                @endif
                                                @if(@$number[$data -> id] !== 0)
                                                    <span id="count_topic_{{ $data -> id }}">
                                                        <span class="count-alert">{{ $number[$data -> id] }}</span>
                                                    </span> 
                                                @endif
                                            @endif
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>
                <div class="tabbable">
                    <ul class="nav nav-tabs nav-tabs-highlight">
                        <li class="active"><a href="#tab_related_news" data-toggle="tab">News (<span id="count_news"></span>)</a></li>
                        <li id="tab-bookmark"><a href="#tab_lastest_news" data-toggle="tab">My Bookmarks (<span id="count_news_bookmark"></span>)</a></li>   
                        <li class="pull-right">
                            <button id="" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                                <span>Bookmarks</span>
                             </button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_related_news">
                            <section class="panel panel-default border-n">
                                <div id="main-list" class="row m-b-md">
                                    <div class="col-md-12">
                                        <div id="list_news"></div>
                                        
                                        {{-- <div class="list-news">
                                            <div class="checkbox-news-select">
                                                <label class="mr-3">
                                                    <input type="checkbox" name="" class="chk-bookmark">
                                                    <span class="label-text checkbox-news-input"></span>
                                                </label>
                                            </div>
                                            <div class="content-news-text">
                                                <a href="{{route('news.news_detail')}}">
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
                                <div class="row m-b-md">
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
    <link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css"/>
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.form')

<script>
    var page = 1; 
    var page_stop = true;
    load_more(page);
    load_more_book_mark(page);
    $('.tab-content').scroll(function(event) {
        if($('.tab-content').scrollTop() + $('.tab-content').height() >= $(document).height()) {
            page++;
            if(page_stop){
                load_more(page);
            }
        }
    });

    function load_more_book_mark(page){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/news/jqueryLoadMoreNewsBookmark?page=" + page,
            type: "get",
            datatype: "html",
            beforeSend: function(){
                $('.ajax-loading').show();
            },
        }).done(function(data){
            if(data.html.length == 0){
                page_stop = false;
                $('.ajax-loading').html("");
                $('#count_news_bookmark').text(0);
                return;
            }
            $('#count_news_bookmark').text(data.count);
            $('.ajax-loading').hide();
            $("#list_news_book_mark").append(data.html);   
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
            },
        }).done(function(data){
            if(data.html.length == 0){
                page_stop = false;
                $('.ajax-loading').html("");
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
            },
        }).done(function(data){
            if(data.html.length == 0){
                page_stop = false;
                $('.ajax-loading').html("");
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
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/news/bookmark?news_id=" + news_id,
            type: "get",
            datatype: "json",
        }).done(function(data){
            $(ele).addClass("bookmark-active"); 
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


    $('#hide-advance-search').hide();
    $('#advance-search').click(function(){
        $('#hide-advance-search').toggle();
    });

    $('#sources_select').select2();
    $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') });

</script>

<script type="text/javascript">
    $(function() {
    
        var start = moment().startOf('hour');
        var end = moment().startOf('hour').add(32, 'hour');
    
        function cb(start, end) {
            $('#newsrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        }
    
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
    
    });
    </script>
@endpush
@endsection