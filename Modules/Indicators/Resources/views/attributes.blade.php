@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header bg-white b-b b-light">

            {{-- <div class="bc-head">@langapp('indicators')</div>
            <div class="btn-group pull-right">
                <div class="pull-right" style="margin-bottom: 8px; width: 300px;">
                    <select name="site" id="site" class="select2-option form-control select-site"
                        style="min-width: 300px">
                        <option value="">All Site</option>

                    </select>
                </div>
                <a href="#" id="seach-advance" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive"
            title="Advance Search" data-placement="bottom">
            <i class="fas fa-search"></i> Search
            </a>

            </div> --}}
            <div class="bc-head">Attributes</div>

            <button id="seach-advance" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
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
        <section id="scroll_otx" class="scrollable wrapper bg-white">
            <section class="panel panel-default">
                <div class="panel-heading">
                    <a class="text-muted" href="{{ route('indicators.events') }}">Events</a>
                    |
                    <a href="{{ route('indicators.attributes') }}" class="text-primary">Attributes</a>
                </div>
                <div id="hide-search-advance" class="container-fluid" style="padding: 2rem">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group m-b-md">
                                <label for="" class="">Keyword</label>
                                <input type="text" class="form-control" name="keyword" id="keyword"
                                    placeholder="Search">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="">Indicator Type</label>
                                <select name="type[]" id="type" class="select2-option form-control" multiple="multiple"
                                    onchange="changeSite(value)">

                                    @if ($cursor)

                                    {{-- @foreach ($otx_type as $otx_type)
                                    <option value="{{$otx_type->name}}">{{$otx_type->name}}</option>
                                    @endforeach --}}
                                    @foreach ($cursor as $document) {
                                    <option value="{{$document->name}}">{{$document->name}}</option>
                                    }
                                    @endforeach
                                    @endif



                                </select>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <label for="" class="">Date</label>
                            <div id="indicator_date" class="text-center"
                                style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span></span> <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="" class="d-block">&nbsp;</label>
                            <button class="btn btn-info" id="search_data">
                                <i class="fas fa-search"></i>
                                <span> Search </span>
                            </button>
                            <button class="btn btn-default" id="clear_data">
                                <i class=" fas fa-broom"></i>
                                <span> Clear </span>
                            </button>
                        </div>
                    </div>
                    <div class="row">

                    </div>
                </div>
            </section>

            <section class="panel panel-default">
                <div class="row header-badge">
                    <div class="col-md-6 p-l-r-0">
                        <span class="font-weight-bold" id="count_otx"></span>
                    </div>
                    <div class="col-md-6 p-l-r-0 text-right">
                        <div class="btn-group">
                            <button class="btn btn-dark btn-sm dropdown-toggle" data-toggle="dropdown">
                                @langapp('sort_by')
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" onclick="sort_by(event)">
                                <li value="1"><a href="#">Recently Modified</a></li>
                                <li value="2"><a href="#">Least Recently Modified</a></li>
                                <li value="3"><a href="#">Name Ascending</a></li>
                                <li value="4"><a href="#">Name Descending</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <section id="scrollable_otx" class="show-indicators">
                    <div id="list_otx"></div>
                    <div class="ajax-loading loading-more" style="display: none;margin-top:15px;">Loading&nbsp;<span
                            class="content-spinner-loading-inline"></span>
                    </div>
                </section>
            </section>
        </section>
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
@include('stacks.css.datepicker')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
<script>
    var keywords = null;
    var type = null;
    var startDate= null;
    var endDate= null;
    var target=null;
    var f_search = 0;

    var page = 1; 
    var page_stop = true;
    var ck = 1;

    var isDateSearch = false;
    load_more(page);
    $('#scroll_otx').scroll(function(event) {
            let scrolltop = $('#scroll_otx').scrollTop();
            let tab_height = $('#scroll_otx').height();
            let docu_height = $(document).height();

        if($('#scroll_otx').scrollTop() + $('#scroll_otx').height() >= $(document).height()) {

            if(page_stop){
                
                if(ck == 1) {
                    page = page+1;
                    load_more_search(page,f_search);
                    
                    ck++;
                    
                }

                setTimeout(function(){ 
                
                }, 10000);
            }
            
        }
    });

    $(document).ready(function () {

        $('#hide-search-advance').hide();
        $('#seach-advance').click(function(){
            $('#hide-search-advance').toggle();
        });
        $('#indicator_type').select2({
            placeholder: 'Indicator Type',
        });
        $('#date').select2({
            placeholder: 'Role',
        });

        
    });


    $(function() {
        var start = moment().startOf('hour');
        var end = moment().startOf('hour').add(32, 'hour');

        function cb(start, end) {
            $('#indicator_date span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            startDate = start;
            endDate = end;
        }
        
        $('#indicator_date').daterangepicker({
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
        $('#indicator_date').on('apply.daterangepicker', function(ev, picker) {
            isDateSearch = true;
            if (!picker.startDate.isValid() || !picker.endDate.isValid()) {
                
            }
        });
        
        

        cb(start, end);

        $("#clear_data").click(function() {
            $('#keyword').val('');
            start = moment();
            end = moment();
            cb(start, end);
            f_search = 0;
            page = 1;
            page_stop = true;
            
            $('#type').val('').trigger('change');
            load_more_search(page,f_search);
          


        });

        $("#search_data").click(function() {
           
            keywords = $('#keyword').val();
            type = $('#type').val();
            f_search = 1;
            page = 1;
            {{--$('#count_news').text(0);--}}
            page_stop = true;
            load_more_search(page,f_search);
        
       
          


        });
    
    });


    function sort_by(event) {
        target = event.target.innerHTML;
        page = 1;
        page_stop = true;
        if(f_search == 0){
            load_more(page);
        }else{
            load_more_search(page,f_search);
        }
    }

    

    function load_more(page){
        if(page == 1) {
            $("#list_otx").html('');   
        }
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/indicators/LoadMoreOTX?page=" + page,
            type: "get",
            data: ({

                target:target,
            }),
            datatype: "html",
            beforeSend: function(){
                {{--loading('load');--}}
                $('.ajax-loading').show();
            },
        }).done(function(data){
            {{--loading('stop_load');--}}

            if(data.html.length == 0){
                ck = 0;
                page_stop = false;
                $('.ajax-loading').hide();
                f_loading_stop(1);
                $('#count_otx').text("We've found "+data.count+" indicators");
                return;
            }else{
                ck = 1;
                f_loading_stop(1);
                $('#count_otx').text("We've found "+data.count+" indicators" );
                $('.ajax-loading').hide();
                $("#list_otx").append(data.html);  

            }
             
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            $('.ajax-loading').hide();
            console.log("No response from server");
        });
    }

    
    function load_more_search(page,f_search){
        if(page == 1) {
            $("#list_otx").html('');   
        }
        startDate=  $("#indicator_date").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm:ss A');
        endDate=  $("#indicator_date").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm:ss A');


        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/indicators/LoadMoreOTX?page=" + page,
            type: "get",
            data: ({
                startDate:startDate,
                endDate:endDate,
                keywords:keywords,
                type:type,
                f_search:f_search,
                isDateSearch:isDateSearch,
                target:target,
            }),
            {{--datatype: "html",--}}
            beforeSend: function(){
                $('.ajax-loading').show();
                {{--loading('load');--}}
                
            },
        }).done(function(data){
            if(page == 1) {
                $("#list_otx").html('');   
            }
            {{--loading('stop_load');--}}
            if(data.html.length == 0){
                $('.ajax-loading').hide();
                ck = 0;
                page_stop = false;
                $('#count_otx').text("We've found "+data.count+" indicators");
                return;
            }
            ck = 1;
            let count_n = $('#count_otx').text();
            let count_search = data.count;
            let count_n_all = parseInt(count_n) + parseInt(count_search);
            {{--$('#count_otx').text(data.count);--}}
            $('#count_otx').text("We've found "+data.count+" indicators");
            $('.ajax-loading').hide();
            {{--$('.ajax-loading').addClass('d-none');--}}
            $("#list_otx").append(data.html);   
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            $('.ajax-loading').hide();
            console.log("No response from server : search");
        });
    }

    function changeSite(value) {
        console.log($('#type').val());
   

   }

   



</script>





@endpush
@endsection