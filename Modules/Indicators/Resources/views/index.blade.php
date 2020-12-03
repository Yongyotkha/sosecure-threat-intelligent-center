@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header bg-white b-b b-light">
            {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
            @icon('solid/arrow-left')
            </a> --}}
            <div class="bc-head">@langapp('indicators')</div>
            <div class="btn-group pull-right">

                {{-- <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle"
                data-toggle="dropdown">
                @langapp('filter')
                <span class="caret"></span>
                </button>

                <ul class="dropdown-menu">
                    <li>
                        <a href="#">
                            @langapp('Last Hour')
                        </a>
                    </li>
                    <li><a href="#">@langapp('all') </a></li>
                </ul>

                <a href="{{  route('clients.create') }}"
                    class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive" data-toggle="ajaxModal"
                    title="@langapp('create') " data-placement="bottom">
                    @icon('solid/plus') @langapp('create')
                </a>

                <a href="{{  route('clients.import')  }}"
                    class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive"
                    title="@langapp('import_clients') " data-placement="bottom" data-toggle="ajaxModal">
                    @icon('solid/cloud-upload-alt') @langapp('import')
                </a>
                <a href="{{  route('clients.export')  }}"
                    class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive" title="CSV"
                    data-placement="bottom">
                    @icon('solid/cloud-download-alt') CSV
                </a> --}}
                <a href="#" id="seach-advance" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive"
                    title="Advance Search" data-placement="bottom">
                    <i class="fas fa-search"></i> Search
                </a>
            </div>

        </header>
        <section id="scroll_otx" class="scrollable wrapper bg-white">
            <div id="hide-search-advance">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group m-b-md">
                            <label for="" class="">Keyword</label>
                            <input type="text" class="form-control" name="keyword" id="keyword" placeholder="Search">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="" class="">Indicator Type</label>
                            <select name="type[]" id="type" class="select2-option form-control" multiple="multiple">

                                @if ($otx_type)

                                @foreach ($otx_type as $otx_type)
                                <option value="{{$otx_type->name}}">{{$otx_type->name}}</option>
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

            <section class="">
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
                            <ul class="dropdown-menu">
                                <li><a href="#">Recently Modified</a></li>
                                <li><a href="#">Least Recently Modified</a></li>
                                <li><a href="#">Recently Created</a></li>
                                <li><a href="#">Least Recently Created</a></li>
                                <li><a href="#">Name Ascending</a></li>
                                <li><a href="">Name Descending</a></li>
                                <li><a href="">Type Ascending</a></li>
                                <li><a href="">Tyoe Decending</a></li>
                                <li><a href="{{ route('clients.index') }}">@langapp('all') </a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <section id="scrollable_otx" class="show-indicators">
                    <div id="list_otx"></div>
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
    var startDate;
    var endDate;

    var f_search = 0;

    var page = 1; 
    var page_stop = true;
    load_more(page);
    $('#scroll_otx').scroll(function(event) {
            let scrolltop = $('#scroll_otx').scrollTop();
            let tab_height = $('#scroll_otx').height();
            let docu_height = $(document).height();
        console.log(scrolltop+'  '+tab_height+'   '+docu_height);
        if($('#scroll_otx').scrollTop() + $('#scroll_otx').height() >= $(document).height()) {
            page++;
            console.log(555);
            if(page_stop){
                {{--load_more(page);--}}
                load_more_search(page,f_search);
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

        cb(start, end);

        $("#clear_data").click(function() {
            keywords = $('#keyword').val('');
            type = $('#type').val('');
            start = moment();
            end = moment();
                cb(start, end);
            f_search = 0;
            page = 1;
            page_stop = true;

            $('#type').val(null).trigger('change');
            clear_load_more(page,f_search);

          


        });
        $("#search_data").click(function() {
        keywords = $('#keyword').val();
        type = $('#type').val();
        f_search = 1;
        page = 1;
        $('#count_news').text(0);
        page_stop = true;
        load_more_search(page,f_search);
          


        });
    
    });

    

    function load_more(page){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/indicators/LoadMoreOTX?page=" + page,
            type: "get",
            datatype: "html",
            beforeSend: function(){
                loading('load');
                $('.ajax-loading').show();
            },
        }).done(function(data){
            loading('stop_load');
            if(data.html.length == 0){
                
                page_stop = false;
                $('.ajax-loading').hide();
                $('#count_otx').text("We've found "+data.count+" indicators");
                return;
            }
            $('#count_otx').text("We've found "+data.count+" indicators" );
            $('.ajax-loading').hide();
            $("#list_otx").append(data.html);   
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            console.log("No response from server");
        });
    }
    function load_more_search(page,f_search){
        if(page == 1) {
            $("#list_otx").html('');   
        }
        startDate=  $("#indicator_date").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm:ss A');
        endDate=  $("#indicator_date").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm:ss A');
        console.log(startDate);
    console.log(endDate);
    console.log(keywords);
    console.log(type);

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
                f_search:f_search
            }),
            {{--datatype: "html",--}}
            beforeSend: function(){
                loading('load');
                $('.ajax-loading').show();
            },
        }).done(function(data){
            loading('stop_load');
            if(data.html.length == 0){
                page_stop = false;
                $('.ajax-loading').hide();
                $('#count_otx').text("We've found "+data.count+" indicators");
                return;
            }
            console.log(data.html.length);
            let count_n = $('#count_otx').text();
            let count_search = data.count;
            let count_n_all = parseInt(count_n) + parseInt(count_search);
            {{--$('#count_otx').text(data.count);--}}
            $('#count_otx').text("We've found "+data.count+" indicators");
            $('.ajax-loading').hide();
            $('.ajax-loading').addClass('d-none');
            $("#list_otx").append(data.html);   
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            console.log("No response from server");
        });
    }

    function clear_load_more(page,f_search){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "/indicators/LoadMoreOTX?page=" + page,
            type: "get",
            datatype: "html",
            data: ({
                
                f_search:f_search,
            }),
            beforeSend: function(){
                loading('load');
                $('.ajax-loading').show();
            },
        }).done(function(data){
            loading('stop_load');
            if(data.html.length == 0){
                
                page_stop = false;
                $('.ajax-loading').hide();
                $('#count_otx').text("We've found "+data.count+" indicators");
                return;
            }
            $('#count_otx').text("We've found "+data.count+" indicators" );
            $('.ajax-loading').hide();
            $("#list_otx").append(data.html);   
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            console.log("No response from server");
        });
    }



</script>





@endpush
@endsection