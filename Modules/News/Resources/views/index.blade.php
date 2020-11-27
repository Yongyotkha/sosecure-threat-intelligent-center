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
                                    <li>
                                        <a href="#">ม็อบราษฏร <span class="count-alert"> 1 </span></a>
                                    </li>
                                    <li>
                                        <a href="#">คนล่ะครึ่ง</a>
                                    </li>
                                    <li>
                                        <a href="#">ม็อบราษฏร <span class="count-alert"> 1 </span></a>
                                    </li>
                                    <li>
                                        <a href="#">คนล่ะครึ่ง</a>
                                    </li>
                                    <li>
                                        <a href="#">ม็อบราษฏร <span class="count-alert"> 1 </span></a>
                                    </li>
                                    <li>
                                        <a href="#">คนล่ะครึ่ง</a>
                                    </li>
                                    <li>
                                        <a href="#">ม็อบราษฏร <span class="count-alert"> 1 </span></a>
                                    </li>
                                    <li>
                                        <a href="#">คนล่ะครึ่ง</a>
                                    </li>
                                    <li>
                                        <a href="#">ม็อบราษฏร <span class="count-alert"> 1 </span></a>
                                    </li>
                                    <li>
                                        <a href="#">คนล่ะครึ่ง</a>
                                    </li>
                                    <li>
                                        <a href="#">ม็อบราษฏร <span class="count-alert"> 1 </span></a>
                                    </li>
                                    <li>
                                        <a href="#">คนล่ะครึ่ง</a>
                                    </li>
                                    <li>
                                        <a href="#">ม็อบราษฏร <span class="count-alert"> 1 </span></a>
                                    </li>
                                    <li>
                                        <a href="#">คนล่ะครึ่ง</a>
                                    </li>
                                    <li>
                                        <a href="#">ม็อบราษฏร <span class="count-alert"> 1 </span></a>
                                    </li>
                                    <li>
                                        <a href="#">คนล่ะครึ่ง</a>
                                    </li>
                                    <li>
                                        <a href="#">ม็อบราษฏร <span class="count-alert"> 1 </span></a>
                                    </li>
                                    <li>
                                        <a href="#">คนล่ะครึ่ง</a>
                                    </li>
                                    <li>
                                        <a href="#">ม็อบราษฏร <span class="count-alert"> 1 </span></a>
                                    </li>
                                    <li>
                                        <a href="#">คนล่ะครึ่ง</a>
                                    </li>
                                    <li>
                                        <a href="#">ม็อบราษฏร <span class="count-alert"> 1 </span></a>
                                    </li>
                                    <li>
                                        <a href="#">คนล่ะครึ่ง</a>
                                    </li>
                                    <li>
                                        <a href="#">ม็อบราษฏร <span class="count-alert"> 1 </span></a>
                                    </li>
                                    <li>
                                        <a href="#">คนล่ะครึ่ง</a>
                                    </li>
                                    <li>
                                        <a href="#">ม็อบราษฏร <span class="count-alert"> 1 </span></a>
                                    </li>
                                    <li>
                                        <a href="#">คนล่ะครึ่ง</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>
                <div class="tabbable">
                    <ul class="nav nav-tabs nav-tabs-highlight">
                        <li class="active"><a href="#tab_related_news" data-toggle="tab">News ({{@$RSSNews_count ? $RSSNews_count : 0}})</a></li>
                        <li id="tab-bookmark" class="disabled"><a id="link_tab_bookmark" href="#tab_lastest_news" data-toggle="tab">My Bookmarks</a></li>   
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
                                        <div class="list-news">
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
                                                <div class="entry-meta">
                                                    <span class="entry-date"> <i class="fas fa-calendar-alt"></i> August 4th, 2020</span>
                                                    <span class="entry-view"> <i class="fas fa-eye"></i> 300</span>
                                                    <span>&nbsp;Lorem ipsum dolor sit amet consectetur adipisicing elit. Placeat voluptates delectus praesentium architecto iure reprehenderit soluta qui sapiente quaerat, explicabo non mollitia officiis sit porro consequuntur itaque, iusto ad quas.</span>
                                                </div>
                                            </div>
                                            <div class="content-news-image">
                                                <a href="{{route('news.news_detail')}}">
                                                    <img src="https://images.unsplash.com/photo-1593642703013-5a3b53c965f6?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=925&q=80" alt="">
                                                </a>
                                            </div>
                                            <div class="action-bookmark">
                                                <i class="fas fa-bookmark bookmark-active" id="mark1" onclick="Bookmarks(this)"></i>
                                            </div>
                                        </div>
                                        <div class="list-news">
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
                                        </div>
                                        <div class="list-news">
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
                                                <div class="entry-meta">
                                                    <span class="entry-date"> <i class="fas fa-calendar-alt"></i> August 4th, 2020</span>
                                                    <span class="entry-view"> <i class="fas fa-eye"></i> 300</span>
                                                    <span>&nbsp;Lorem ipsum dolor sit amet consectetur adipisicing elit. Placeat voluptates delectus praesentium architecto iure reprehenderit soluta qui sapiente quaerat, explicabo non mollitia officiis sit porro consequuntur itaque, iusto ad quas.</span>
                                                </div>
                                            </div>
                                            <div class="content-news-image">
                                                <a href="{{route('news.news_detail')}}">
                                                    <img src="https://images.unsplash.com/photo-1593642703013-5a3b53c965f6?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=925&q=80" alt="">
                                                </a>
                                            </div>
                                            <div class="action-bookmark">
                                                <i class="fas fa-bookmark" id="mark2" onclick="Bookmarks(this)"></i>
                                            </div>
                                        </div>
                                        <div class="list-news">
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
                                                <div class="entry-meta">
                                                    <span class="entry-date"> <i class="fas fa-calendar-alt"></i> August 4th, 2020</span>
                                                    <span class="entry-view"> <i class="fas fa-eye"></i> 300</span>
                                                    <span>&nbsp;Lorem ipsum dolor sit amet consectetur adipisicing elit. Placeat voluptates delectus praesentium architecto iure reprehenderit soluta qui sapiente quaerat, explicabo non mollitia officiis sit porro consequuntur itaque, iusto ad quas.</span>
                                                </div>
                                            </div>
                                            <div class="content-news-image">
                                                <a href="{{route('news.news_detail')}}">
                                                    <img src="https://images.unsplash.com/photo-1593642703013-5a3b53c965f6?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=925&q=80" alt="">
                                                </a>
                                            </div>
                                            <div class="action-bookmark">
                                                <i class="fas fa-bookmark" id="mark3" onclick="Bookmarks(this)"></i>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- <div class="col-sm-12">
                                        <div class="shadow-box-news">
                                            <article class="def-rlt">
                                                <figure class="overlay relative">
                                                    <a href="{{route('news.news_detail')}}" class="thumb-overlay-small">
                                                        <img class="img-responsive" src="https://images.unsplash.com/photo-1593642703013-5a3b53c965f6?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=925&q=80" alt="">
                                                    </a>
                                                </figure>
                                                <div class="entry">
                                                    <span class="entry-category">
                                                        <a href="{{route('news.news_detail')}}">Technology News</a>
                                                    </span>
                                                    <h3>
                                                        <a href="{{route('news.news_detail')}}">
                                                            WhatsApp’s new fact-check feature lets users identify fake information
                                                        </a>
                                                    </h3>
                                                    <div class="entry-meta">
                                                        <span class="entry-date"> <i class="fas fa-calendar-alt"></i> August 4th, 2020</span>
                                                        <span class="entry-view"> <i class="fas fa-eye"></i> 300</span>
                                                    </div>
                                                    <div class="description-text hidden-xs">
                                                        WhatsApp's "Search the Web" feature lets users perform web searches on viral messages to confirm their authenticity.
                                                    </div>
                                                </div>
                                            </article>
                                        </div>
                                    </div> --}}
                                    {{-- <div class="col-sm-12">
                                        <div class="shadow-box-news">
                                            <article class="def-rlt">
                                                <figure class="overlay relative">
                                                    <a href="{{route('news.news_detail')}}" class="thumb-overlay-small">
                                                        <img class="img-responsive" src="https://images.unsplash.com/photo-1544890225-2f3faec4cd60?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=925&q=80" alt="">
                                                    </a>
                                                </figure>
                                                <div class="entry">
                                                    <span class="entry-category">
                                                        <a href="{{route('news.news_detail')}}">CYBER CRIME</a>
                                                    </span>
                                                    <h3>
                                                        <a href="{{route('news.news_detail')}}">
                                                            How hackers behind Twitter Bitcoin scam were caught
                                                        </a>
                                                    </h3>
                                                    <div class="entry-meta">
                                                        <span class="entry-date"> <i class="fas fa-calendar-alt"></i> August 4th, 2020</span>
                                                        <span class="entry-view"> <i class="fas fa-eye"></i> 1,000</span>
                                                    </div>
                                                    <div class="description-text hidden-xs">
                                                        The Twitter Bitcoin scam allowed hackers to rake in over £80,000/$100,000.
                                                    </div>
                                                </div>
                                            </article>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="shadow-box-news">
                                            <article class="def-rlt">
                                                <figure class="overlay relative">
                                                    <a href="{{route('news.news_detail')}}" class="thumb-overlay-small">
                                                        <img class="img-responsive" src="https://images.unsplash.com/photo-1516259762381-22954d7d3ad2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1066&q=80" alt="">
                                                    </a>
                                                </figure>
                                                <div class="entry">
                                                    <span class="entry-category">
                                                        <a href="{{route('news.news_detail')}}">PHISHING SCAM</a>
                                                    </span>
                                                    <h3>
                                                        <a href="{{route('news.news_detail')}}">
                                                            Fake Zoom meeting invitation phishing scam harvests Microsoft credentials
                                                        </a>
                                                    </h3>
                                                    <div class="entry-meta">
                                                        <span class="entry-date"> <i class="fas fa-calendar-alt"></i> August 3rd, 2020</span>
                                                        <span class="entry-view"> <i class="fas fa-eye"></i> 300</span>
                                                    </div>
                                                    <div class="description-text hidden-xs">
                                                        Initially targeting Zoom users; the phishing scam aims for Outlook and Office365 credentials.
                                                    </div>
                                                </div>
                                            </article>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="shadow-box-news">
                                            <article class="def-rlt">
                                                <figure class="overlay relative">
                                                    <a href="{{route('news.news_detail')}}" class="thumb-overlay-small">
                                                        <img class="img-responsive" src="https://images.unsplash.com/photo-1568027763595-ef293f388029?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1189&q=80" alt="">
                                                    </a>
                                                </figure>
                                                <div class="entry">
                                                    <span class="entry-category">
                                                        <a href="{{route('news.news_detail')}}">HACKING NEWS</a>
                                                    </span>
                                                    <h3>
                                                        <a href="{{route('news.news_detail')}}">
                                                            Transmission of Pakistani news channel interrupted to display Indian flag
                                                        </a>
                                                    </h3>
                                                    <div class="entry-meta">
                                                        <span class="entry-date"> <i class="fas fa-calendar-alt"></i> August 3rd, 2020</span>
                                                        <span class="entry-view"> <i class="fas fa-eye"></i> 300</span>
                                                    </div>
                                                    <div class="description-text hidden-xs">
                                                        Prominent Pakistani news channel Dawn had its transmission...
                                                    </div>
                                                </div>
                                            </article>
                                        </div>
                                    </div> --}}
                                </div>
                                
                                <div class="row">
                                    <div class="col-xs-12 text-center mb-2">
                                        <div class="paginate-footer">
                                            <button class="btn btn-default btn-icon previos">
                                                <i class="fas fa-chevron-left"></i>
                                            </button>
                                            &nbsp;
                                            <button class="btn btn-default btn-icon next">
                                                <i class="fas fa-chevron-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                        <div class="tab-pane" id="tab_lastest_news">
                            <section class="panel panel-default">
                                <div class="row m-b-md">
                                    <div class="col-sm-12">
                                        <div class="list-news">
                                            <div class="checkbox-news-select">
                                                <label class="mr-3">
                                                    <input type="checkbox" name="" id="chk-bookmark">
                                                    <span class="label-text checkbox-news-input"></span>
                                                </label>
                                            </div>
                                            <div class="content-news-text">
                                                <a href="{{route('news.news_detail')}}">
                                                    <span class="head-news-text">WhatsApp’s new fact-check feature lets users identify fake information</span>
                                                </a>
                                                <div class="entry-meta">
                                                    <span class="entry-date"> <i class="fas fa-calendar-alt"></i> August 4th, 2020</span>
                                                    <span class="entry-view"> <i class="fas fa-eye"></i> 300</span>
                                                    <span>&nbsp;Lorem ipsum dolor sit amet consectetur adipisicing elit. Placeat voluptates delectus praesentium architecto iure reprehenderit soluta qui sapiente quaerat, explicabo non mollitia officiis sit porro consequuntur itaque, iusto ad quas.</span>
                                                </div>
                                            </div>
                                            <div class="content-news-image">
                                                <a href="{{route('news.news_detail')}}">
                                                    <img src="https://images.unsplash.com/photo-1593642703013-5a3b53c965f6?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=925&q=80" alt="">
                                                </a>
                                            </div>
                                            <div class="action-bookmark">
                                                <i class="fas fa-bookmark bookmark-active" id="mark1" onclick="Bookmarks(this)"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row m-md">
                                    <div class="col-xs-12 text-center">
                                        <div class="paginate-footer">
                                            <button class="btn btn-default btn-icon previos">
                                                <i class="fas fa-chevron-left"></i>
                                            </button>
                                            &nbsp;
                                            <button class="btn btn-default btn-icon next">
                                                <i class="fas fa-chevron-right"></i>
                                            </button>
                                        </div>
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

    function Bookmarks(ele){
        $(ele).addClass("bookmark-active");
    }

    $('#tab-bookmark').addClass("disabled");
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