@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">

        {{-- Head --}}
        <header class="header panel-heading bg-white b-b b-light">
            <div class="bc-head"> @langapp('dark_web')</div>    

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
        <section id="scrollable_news" class="scrollable wrapper bg-white">
            <section class="panel panel-default"  id="hide-advance-search" style="display: none">
                <div class="container-fluid" style="padding: 2rem;">
                    <div class="row m-b-md">
                        <div class="col-lg-12">
                            <div class="row d-flex align-items-center">
                                <label for="" class="col-sm-1 col-xs-12 col-form-label">Keywords</label>
                                <div class="col-sm-11 col-xs-12">
                                    <input type="text" id="Keywords" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="row d-flex align-items-center">
                                <label for="" class="col-sm-3 col-xs-12 col-form-label">Select Source</label>
                                <div class="col-sm-9 col-xs-12">
                                    <select id="social" class="select2-option form-control">
                                        <option value="" >All</option>
                                        <option value="compromise" >Public</option>
                                        <option value="darkweb" >Darkweb</option>
                                        {{-- <option value="1" selected>All</option> --}}
                                    </select>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="col-lg-4">
                            <div class="row d-flex align-items-center">
                                <label for="" class="col-sm-3 col-xs-12 col-form-label">Sources</label>
                                <div class="col-sm-9 col-xs-12">
                                    <section id="sources_select" class="select2-option form-control">
                                        <option value="1" selected>All</option>
                                    </section>
                                </div>
                            </div>
                        </div> --}}
                        <div class="col-lg-4 text-center">
                            <div id="newsrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span></span> <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                        <div class="col-lg-4 text-center">
                            <div style="margin-top: 8px;">
                                
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 text-right mt-2">
                            <button type="button" id="btn_news_search" class="btn btn-info btn-responsive">
                                <i class="fas fa-search"></i>
                                Search
                            </button>
                            <button type="button" id="btn_news_reset" class="btn btn-default btn-responsive" style="white-space: nowrap">
                                <i class="fas fa-broom"></i>
                                <span> Clear </span>
                            </button>
                        </div>
                    </div>
                </div>
                {{-- <div class="container-fluid" style="padding: 2rem;">
                    <div class="row m-b-md">
                        <div class="col-lg-4">
                            <label for="">Select Source</label>
                            <select id="social" class="select2-option form-control">
                                <option value="" selected>All</option>
                            </select>

                        </div>
                        <div class="col-lg-8">
                            <label for="">Keywords</label>
                            <input type="text" id="Keywords" class="form-control">
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-lg-12 text-right">
                            <button id="btn_news_search" class="btn btn-info btn-responsive">
                                <i class="fas fa-search"></i>
                                Search
                            </button>
                            <button class="btn btn-default btn-responsive" style="white-space: nowrap">
                                <i class="fas fa-broom"></i>
                                <span> Clear </span>
                            </button>
                        </div>
                    </div>
                </div> --}}



                
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
                    <li class="active"><a href="#tab-leak" class="tab_cliick" data-toggle="tab">Leak (<span id="count_news"></span>)</a></li>
                    <li  id="tab-bookmark"><a href="#tab-bookmarks" class="tab_cliick" data-toggle="tab">My Bookmarks (<span id="count_news_bookmark"></span>)</a></li>   
                    <li class="pull-right">
                        {{-- <button id="" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                            <span>Bookmarks</span>
                         </button> --}}
                    </li>
                </ul>
                <div class="tab-content">


                    <div class="tab-pane active" id="tab-leak">
                        <section class="panel panel-default border-n">
                            <div id="main-list" class="row m-b-md">
                                <div class="col-md-12">
                                    <div id="list_news"></div>
                                    <div id="load_list_news" class="ajax-loading loading-more" style="display: none;margin-top:15px;">Loading&nbsp;<span class="content-spinner-loading-inline"></span></div>
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



                    <div class="tab-pane" id="tab-bookmarks">
                        <section class="panel panel-default border-n">
                            <div class="row m-b-md">
                                <div class="col-sm-12">
                                    
                                    <div id="list_news_book_mark"></div>
                                    <div id="load_list_news_book_mark" class="ajax-loading loading-more" style="display: none;margin-top:15px;">Loading&nbsp;<span class="content-spinner-loading-inline"></span></div>
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


                </div>

            </div>

          </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@push('pagestyle')
    @include('stacks.css.datatables')
    @include('stacks.css.form')

    @include('stacks.css.datepicker')
    @include('stacks.css.form')
    <link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css"/>
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')

<script>
    $('#social').select2();
    $('#hide-advance-search').hide();

    var f_search = 0;
    var page = 1; 
    var pagebookmark = 1; 
    var page_stop = true;
    var ck = 1;
    var isDateSearch = false;
    var active_tab = 'leak';
    load_more_search(page);
    load_more_book_mark(pagebookmark);
    $(function() {
        
        $('#advance-search').click(function(){
            $('#hide-advance-search').toggle();
        });

        $('.tab_cliick').click(function (event) {        
            let activeTab = $(this).attr('href').split('-')[1];
            if(activeTab=='bookmarks'){
                alert('bookmark__');
                active_tab = 'bookmarks';
            }else if(activeTab=='leak'){
                alert('leak__');
                active_tab = 'leak';
            }
        });


        $('#newsrange').on('apply.daterangepicker', function(ev, picker) {
            isDateSearch = true;
            if (!picker.startDate.isValid() || !picker.endDate.isValid()) {
                
            }
        });


        $('#scrollable_news').scroll(function(event) {
            let scrolltop = $('#scrollable_news').scrollTop();
            let tab_height = $('#scrollable_news').height();
            let docu_height = $(document).height();
            console.log(scrolltop+'  '+tab_height+'   '+docu_height);
            if($('#scrollable_news').scrollTop() + $('#scrollable_news').height() >= $(document).height()) {
                if(page_stop){
                    if(ck == 1) {
                        ck++;
                        if(active_tab=='leak'){
                            page = page+1;
                            load_more_search(page,f_search);
                        }else if(active_tab=='bookmarks'){
                            ck = 1;
                            pagebookmark = pagebookmark+1;
                            console.log(pagebookmark);
                        }
                    }            
                    setTimeout(function(){ 
                    }, 10000);
                }
            }
        });

        $('#scrollable_news').on('touchmove', function(event) {
            let scrolltop = $(window).scrollTop();
            let tab_height = $(window).height();
            let docu_height = $(document).height();
            console.log(scrolltop+'  '+tab_height+'   '+docu_height);
            if($(window).scrollTop() + $(window).height() >= $(document).height()) {
                if(page_stop){
                    if(ck == 1) {
                        ck++;
                        if(active_tab=='leak'){
                            page = page+1;
                            load_more_search(page,f_search);
                        }else if(active_tab=='bookmarks'){
                            ck = 1;
                            pagebookmark = pagebookmark+1;
                            console.log(pagebookmark);
                        }
                    }            
                    setTimeout(function(){ 
                    }, 10000);
                }
            }
        });
       

        $("#btn_news_search").click(function() {
            f_search = 1;
            page = 1;
            $('#count_news').text(0);
            page_stop = true;
            $("#list_news").html('');   
            load_more_search(page,f_search);
        });


        
    });

    function load_more_search(page,f_search=0){

        if(page == 1) {
            $("#list_news").html('');   
        }
        let startDate = '';
        let endDate = '';
        if(f_search == 1) {
            startDate =  $("#newsrange").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
            endDate =  $("#newsrange").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');
        }

        let news_title_search = $("#Keywords").val();
        let social = $("#social").val();
        let site_id = $("#site").val();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/darkweb/jqueryLoadMoreNews",
            type: "get",
            data: ({
                page:page,
                title:news_title_search,
                social:social,
                date_start:startDate,
                date_end:endDate,
                f_search:f_search,
                isDateSearch:isDateSearch,
                site_id:site_id
            }),
            datatype: "html",
            beforeSend: function(){
                $('.ajax-loading').show();
            },
        }).done(function(data){
            if(page == 1) {
                $("#list_news").html('');   
            }
            if(data.html.length == 0){
                ck = 0;
                page_stop = false;
                $('.ajax-loading').hide();
                return;
            } else {
                ck = 1;
                $('#count_news').text(data.count);
                $("#list_news").append(data.html); 
                $('.ajax-loading').hide();
            }
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            $('.ajax-loading').hide();
            console.log("No response from server");
        });

        
    }

    function add_read(i) {

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/social/add_read?addread=" + i,
            type: "get",
            datatype: "json",
        }).done(function(data){
            if(data.success == 'true') {
                
            } else {
                
            }
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            console.log("No response from server");
        });
    }

    function load_more_book_mark(page){
        $("#list_news_book_mark").empty();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/darkweb/jqueryLoadMoreNewsBookmark?page=" + page,
            type: "get",
            datatype: "html",
            beforeSend: function(){
                $('#load_list_news_book_mark').show();
            },
        }).done(function(data){
            if(data.html.length == 0){
                page_stop = false;
                $('#load_list_news_book_mark').hide();
                $('#count_news_bookmark').text(0);
                return;
            }
            $('#count_news_bookmark').text(data.count);
            
            $("#list_news_book_mark").html(data.html);
            $('#load_list_news_book_mark').hide();
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            $('#load_list_news_book_mark').hide();
            console.log("No response from server");
        });
    }

    function Bookmarks(ele, news_id){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/darkweb/bookmark?news_id=" + news_id,
            type: "get",
            datatype: "json",
        }).done(function(data){
            if($(ele).hasClass("bookmark-active")) {
                $(ele).removeClass("bookmark-active"); 
            } else {
                $(ele).addClass("bookmark-active"); 
            }
            load_more_book_mark(pagebookmark);
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            console.log("No response from server");
        });
    }

    $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') });
</script>


<script type="text/javascript">
 $(function() {
    

    var start = moment();{{--moment().startOf('hour')--}} {{--moment().subtract(1, 'year').startOf('year')--}}
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

    $("#btn_news_reset").click(function() {
        $("#Keywords").val('');
        $("#social").val('').trigger("change");
        isDateSearch = false;
        start = moment();
        end = moment();
        cb(start, end);

        f_search = 0;
        page = 1;
        $('#count_news').text(0);
        page_stop = true;
        load_more_search(page,f_search)

    });

});

</script>
@endpush
@endsection