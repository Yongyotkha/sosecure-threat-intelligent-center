@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a> --}}
            <div class="bc-head">Dark web</div>
        </header>
        <section class="scrollable wrapper">
            <section class="panel panel-default">

                <div class="row" style="padding:2rem 1rem 1rem 1rem">
                    <div class="col-lg-6 col-md-6">
                        <ul class="total-count">
                            <li><h1>Public</h1><span class="color-purple">10</span></li>
                        </ul>
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <ul class="total-count">
                            <li><h1>Darkweb</h1><span class="color-green">10</span></li>
                        </ul>
                    </div>
                </div>


                <div class="tabbable">
                    <ul class="nav nav-tabs nav-tabs-highlight">
                        <li class="active"><a href="#tab_related_darkweb" data-toggle="tab">Darkweb (1)</a></li>
                        <li id="tab-bookmark"><a href="#tab_lastest_darkweb" data-toggle="tab">My Bookmarks (0)</a></li>   
                        <li class="pull-right">
                            {{-- <button id="" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                                <span>Bookmarks</span>
                             </button> --}}
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_related_darkweb">
                            <section class="panel panel-default border-n">
                                <div id="main-list" class="row m-b-md">
                                    <div class="col-md-12">
                                        <div class="list-news">
                                            <article class="def-rlt">
                                                <div class="entry">
                                                    <span class="entry-category">
                                                        <a href="#">pantip</a>
                                                    </span>
                                                    <h3 style="font-size: 16px;">
                                                        <a href="#" target="_blank" onclick="add_read()">
                                                          Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptatem error dignissimos cum ab provident corporis odio accusamus ex incidunt, 
                                                          nesciunt exercitationem veniam eum voluptatibus illo nihil non debitis facilis rerum?
                                                        </a>
                                                    </h3>
                                                    <div class="entry-meta">
                                                        <span class="entry-date"><i class="fas fa-calendar-alt"></i> 2020-12-24 17:23:26</span>
                                                        <span class="entry-view"><i class="fas fa-eye"></i> 1</span>
                                                    </div>
                                                </div>
                                            </article>
                                            <div class="action-bookmark"><i class="fas fa-bookmark" id="mark153" onclick="Bookmarks()"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                        <div class="tab-pane" id="tab_lastest_darkweb">
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
@endpush

@push('pagescript')
@include('stacks.js.datatables')

<script>
$(function() {
    $('#table-monitor-compromised-template').DataTable({
        processing: true,
        order: [[ 0, "desc" ]],
    });
});
</script>
@endpush
@endsection