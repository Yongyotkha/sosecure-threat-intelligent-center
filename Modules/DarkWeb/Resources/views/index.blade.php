@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">

        {{-- Head --}}
        <header class="header panel-heading bg-white b-b b-light">
            <div class="bc-head">@langapp('data_leak') > @langapp('dark_web')</div>    

            <button id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                <span>@langapp('Search_Advance')</span>
             </button>
             <div class="pull-right" style="margin-top: 8px; width: 300px;">
                <select name="site" id="site" class="select2-option form-control select-site" style="min-width: 300px">
                    <option value="">All Site</option>
                    @if($SiteSettings)
                        @foreach($SiteSettings as $SiteSettings_val)
                            <option value="{{$SiteSettings_val->code}}">{{$SiteSettings_val->name}}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </header>

        {{-- Search --}}
        

        {{-- Tab Content --}}
        <section class="scrollable wrapper bg-white">

            <section class="panel panel-default"  id="hide-advance-search" style="display: none">
                <div class="container-fluid" style="padding: 2rem;">
                    <div class="row m-b-md">
                        <div class="col-lg-4">
                            <label for="">Select Source</label>
                            <section id="select_source" class="select2-option form-control">
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


{{--             
            <div class="col-md-12">
                <h3 class="text-dark">Display 300 record</h3>
            </div>
            <div class="row m-b-md">
                <div class="col-sm-12">
                    <div class="shadow-box-news b-d-all">
                        <article class="def-rlt">
                            <div class="entry">
                                <span class="entry-category">
                                    <a href="#">http://gjobqjj7wyczbqie.onion/</a>
                                </span>
                                <h3>
                                    <a href="#">
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
                    <div class="shadow-box-news b-d-all">
                        <article class="def-rlt">
                            <div class="entry">
                                <span class="entry-category">
                                    <a href="#">http://gjobqjj7wyczbqie.onion/</a>
                                </span>
                                <h3>
                                    <a href="#">
                                        How hackers behind Twitter Bitcoin scam were caught
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
            </div> --}}
            

            <div class="tabbable">
                <ul class="nav nav-tabs nav-tabs-highlight">
                    <li class="active"><a href="#tab_related_news" data-toggle="tab">Leak (<span id="count_news"></span>)</a></li>
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
                                    
                                    
                                </div>
                            </div>
                        </section>
                    </div>



                    <div class="tab-pane" id="tab_lastest_news">
                        <section class="panel panel-default border-n">
                            <div class="row m-b-md">
                                <div class="col-sm-12">
                                    
                                    <div id="list_news_book_mark"></div>
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
    $('#select_source').select2();


    $('#hide-advance-search').hide();
   
    $(function() {
        $('#advance-search').click(function(){
        $('#hide-advance-search').toggle();
    });
    });
</script>
@endpush
@endsection