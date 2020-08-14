@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">

        {{-- Head --}}
        <header class="header panel-heading bg-white b-b b-light">
            <div class="bc-head">@langapp('news')</div>    
        </header>

        {{-- Search --}}
        

        {{-- Tab Content --}}
        <section class="scrollable wrapper bg-white">
            <section class="panel panel-default">
                <div class="container-fluid" style="padding: 2rem;">
                    <div class="row m-b-md">
                        <div class="col-lg-4">
                            <label for="">Select News</label>
                            <section id="select_news" class="select2-option form-control">
                                <option value="1" selected>All</option>
                            </section>
                        </div>
                        <div class="col-lg-8">
                            <label for="">Keywords</label>
                            <input type="text" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 text-right">
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
            <div class="tabbable">
                <ul class="nav nav-tabs nav-tabs-highlight">
                    <li class="active"><a href="#tab_related_news" data-toggle="tab">Related news (20)</a></li>
                    <li><a href="#tab_lastest_news" data-toggle="tab">Lastest news (42)</a></li>   
                    <li class="pull-right">
                        <button class="btn btn-default actove">TH</button>
                    </li>   
                    <li class="pull-right">
                        <button class="btn btn-default">EN</button>
                    </li>   
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_related_news">
                        <section class="panel panel-default border-n">
                            <div class="row m-b-md">
                                <div class="col-sm-12">
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
                                </div>
                                <div class="col-sm-12">
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
                    <div class="tab-pane" id="tab_lastest_news">
                        <section class="panel panel-default">
                            <div class="row m-b-md">
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
                                </div>
                                <div class="col-sm-12">
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
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@push('pagestyle')
    @include('stacks.css.datatables')
    @include('stacks.css.form')
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')

<script>
    $('#select_news').select2();
</script>
@endpush
@endsection