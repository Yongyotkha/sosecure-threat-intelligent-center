@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">

        {{-- Head --}}
        <header class="header panel-heading bg-white b-b b-light">
            <div class="bc-head"> @langapp('compromised')</div>    

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
                                        <option value="webserver" >Webserver</option>
                                    </select>
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
            </section>


            <div class="container-fluid" style="margin-bottom:10px;">
                <div class="row">
                    <div class="col-md-4 nopadding">
                        <div class="card-dash-compro none-bg none-shadow">
                            <div class="left-card">
                                <div class="img-icon-card ice">
                                    <img src="{{asset('images/icebergline2.png')}}" alt="">
                                </div>
                                <h3 class="name-dash-text-compro text-dark text-upper ">Public</h3>
                                <span class="number-card warning" id='compromise-count'>0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 nopadding">
                        <div class="card-dash-compro none-bg none-shadow">
                            <div class="left-card">
                                <div class="img-icon-card ice">
                                    <img src="{{asset('images/icebergline1.png')}}" alt="">
                                </div>
                                <h3 class="name-dash-text-compro text-dark text-upper">Dark Web</h3>
                                <span class="number-card info"  id='darkweb-count'>0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 nopadding">
                        <div class="card-dash-compro none-bg none-shadow">
                            <div class="left-card">
                                <div class="img-icon-card ice">
                                    <img src="{{asset('images/webserver.png')}}" alt="">
                                </div>
                                <h3 class="name-dash-text-compro text-dark text-upper ">Web Server</h3>
                                <span class="number-card green"  id='webserver-count'>0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tabbable">
                <ul class="nav nav-tabs nav-tabs-highlight">
                    <li class="active"><a href="#tab-leak" class="tab_cliick" data-toggle="tab">Data (<span id="count_news">0</span>)</a></li>
                    <li  id="tab-bookmark"><a href="#tab-bookmarks" class="tab_cliick" data-toggle="tab">My Bookmarks (<span id="count_news_bookmark">0</span>)</a></li>   
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab-leak">
                        <section class="panel panel-default border-n">
                            <div id="main-list" class="row m-b-md">
                                <div class="col-md-12">
                                    <div id="list_news"></div>
                                    <div id="load_list_news" class="ajax-loading loading-more" style="display: none;margin-top:15px;">Loading&nbsp;<span class="content-spinner-loading-inline"></span></div>                                  
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
             
                active_tab = 'bookmarks';
            }else if(activeTab=='leak'){
               
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
                $('#darkweb-count').text(data.darkweb);
                $('#compromise-count').text(data.compromise);
                $('#webserver-count').text(data.webserver);
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
                $('#count_news_bookmark').text(0);
                $('#load_list_news_book_mark').hide();
               
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