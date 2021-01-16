@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">      
        <aside id="hide-settings" class="aside aside-md b-r" style="display: none">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show" data-target="#setting-nav">@icon('solid/bars')</a>
                    <a class="hide-setting btn btn-icon btn-default btn-sm pull-right m-r-xs">@icon('solid/bars')</a>
                    <p class="h3 text-elipse-setting">Sub menu</p>
                </header>
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        <ul class="nav nav-pills nav-stacked no-radius">
                            <li class="active">
                                <a href="{{route('rssfeedsettings.news')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    News
                                </a>
                            </li>
                            <li>
                                <a href="{{route('rssfeedsettings.rss_data')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    RSS Data
                                </a>
                            </li>
                            {{-- <li>
                                <a href="{{route('rssfeedsettings.index')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    RSS Settings
                                </a>
                            </li> --}}
                        </ul>
                    </section>
                </section>
            </section>
        </aside>
    
        <aside>
            <section class="vbox">
    
                <header class="header panel-heading bg-white b-b b-light">
                    <button class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;">@icon('solid/bars')</button>
                    <div class="bc-head">News</div>
                    {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                        @icon('solid/download') CSV
                    </a> --}}
                    {{-- <button type="submit" id="button" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                        <span>@icon('solid/trash-alt') @langapp('delete_all')</span>
                    </button> --}}

                    <a id="btn_rss_setting" href="{{site_url('/rssfeedsettings')}}" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right m-xs">
                        <span><i class="fas fa-cog icon"><b class="bg-info"></b></i></span>
                    </a>

                    <a id="btn_client_view" href="{{site_url('/news_client')}}" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right m-xs">
                        <span><i class="fas fa-eye"></i> Client view</span>
                    </a>

                    <a id="advance-search" href="#area-advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                        <span><i class="fas fa-filter"></i> @langapp('Search_Advance')</span>
                    </a>

                    <button type="button" id="btn_news_del_select" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete" disabled>
                        <span>@icon('solid/trash-alt') @langapp('delete')</span>
                    </button>
        
                    <div class="btn-group pull-right" role="group" aria-label="Button group with nested dropdown">
                        {{-- <button type="button" class="btn btn-secondary">1</button>
                        <button type="button" class="btn btn-secondary">2</button> --}}
                      
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-{{ get_option('theme_color')  }} dropdown-toggle" data-toggle="dropdown"> @icon('solid/plus') @langapp('add')
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left">
                                <li>
                                    <a href="{{route('rssfeedsettings.rss_news_create_news')}}"  data-toggle='ajaxModal'>
                                        Create News
                                    </a>
                                    <a href="{{route('rssfeedsettings.rss_data')}}" id="">
                                        RSS Feed
                                    </a>
    
                                </li>
                            </ul>
                        </div>

                        
                    </div>
                      
                      
                    {{-- <a href="{{route('rssfeedsettings.rss_news_create_news')}}" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle='ajaxModal'>
                        @icon('solid/plus') @langapp('add')
                    </a> --}}
              
                </header>

            {{-- Search --}}
            {{-- Tab Content --}}
            <section class="scrollable wrapper bg-grey">
                <section class="panel panel-default" id="area-advance-search" style="display: none;">
                    <div class="container-fluid" style="padding: 2rem;">
                        <div class="row m-b-md">
                            <div class="col-lg-3">
                                <label for="">Title</label>
                                <input type="text" class="form-control" name="keywords" id="keywords">
                            </div>
                            <div class="col-lg-6">
                                <label for="">Select Date</label>
                                    <div id="date_srange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                        <i class="fa fa-calendar"></i>&nbsp;
                                        <span></span> <i class="fa fa-caret-down"></i>
                                    </div>
                            </div>
                            <div class="col-lg-3">
                                <label for="">Status</label>
                                <select id="status_news" class="select2-option form-control">
                                    <option value="">All</option>
                                    <option value="1">Public</option>
                                    <option value="2">Darft</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-3">
                                <label for="">Source</label>
                                <select name="news_source[]" id="news_source" class="select2-option form-control" multiple="multiple">
                                   
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <label for="">Category</label>
                                <select name="news_category[]" id="news_category" class="select2-option form-control" multiple="multiple">
                                    {{-- <option value="" >All</option> --}}
                                    @foreach(@$category as $cate)
                                        <option value="{{$cate->id}}" >{{$cate->name}}</option>
                                    @endforeach
                                    {{-- <option value="1" selected>All</option> --}}
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 text-right">
                                <button type="button" class="btn btn-info btn-responsive" onclick="search()">
                                    <i class="fas fa-search"></i>
                                    @langapp('apply')
                                </button>
                                <button type="button" id="btn_rss_news_reset" class="btn btn-default btn-responsive" style="white-space: nowrap">
                                    <i class="fas fa-broom"></i>
                                    <span> Clear </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
                
                <section class="scrollable">              
                    <section class="panel panel-default">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-xs-12">
                                    <i class="fas fa-table"></i> Table News
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table  class="table table-striped" id="table-rss-news-template">
                                    <thead>
                                        <tr>
                                            <th class="no-sort">
                                                <label>
                                                    <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                    <span class="label-text"></span>
                                                </label>
                                            </th>
                                            {{-- <th>Site Name</th> --}}
                                            <th width="20px">Source name</th>
                                            <th width="20%">Title</th>
                                            <th>Category</th>
                                            <th width="20px">Data Satatus</th>
                                            <th width="30px">Public Date</th>
                                            <th>View Count</th>
                                            <th width="40px">Link</th>
                                            <th>Status</th>
                                            <th width="5%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
            
                                    </tbody>
                                </table>   
                            </div>
                        </div>
                    </section>
                </section>

                
            </section>
            </section>
        </aside>
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
 

</section>

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
@include('stacks.css.datepicker')
@include('stacks.css.summernote')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css"/>
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('scripts.summernote')
@include('stacks.js.markdown')
@include('stacks.js.hidesettings')
@include('stacks.js.daterangpicker')

<script>

    $('#area-advance-search').hide();
    $('#advance-search').click(function(){
        $('#area-advance-search').toggle();
    });

    var search_val = false;
    var keywords = null;
    var start_date = null;
    var end_date = null;
    var status_news = null;
    var news_source = null;
    var news_category = null;
    var startDate = null;
    var endDate = null;
    function search(){
        search_val = true;
        keywords = $('#keywords').val();
        start_date = $('#start_date').val();
        end_date = $('#end_date').val();
        status_news = $('#status_news').val();
        news_source = $('#news_source').val();
        news_category = $('#news_category').val();
        startDate =  $("#date_srange").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
        endDate =  $("#date_srange").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');
        console.log(start_date);
        console.log(status_news);
        console.log(news_source);
        console.log(news_category);
        datatable();
    }

        $("#btn_rss_news_reset").click(function() {
            search_val = false;
            $("#keywords").val('');
            $("#start_date").val('');
            $("#end_date").val('');
            $("#status_news").val('').trigger("change");
            $("#news_source").val('');
            $("#news_category").val('');
            datatable();
        });


    function change_news_active(code) {
        let checkState = $("#news-active-" + code).is(":checked") ? 1 : 0;
        axios.post('{{route('rssfeedsettings.change_status_news')}}', {
            active: checkState,
            code: code,
        }).then(function (response) {
            toastr.success(response.data.message, '@langapp('response_status')');
            window.location.href = response.data.redirect;
        }).catch(function (error) {
            var errors = error.errors;
            var errorsHtml = "";
            $.each(errors, function (key, value) {
                errorsHtml += "<li>" + value[0] + "</li>";
            });
            toastr.error(errorsHtml, '@langapp('response_status')');
        });
    }
$(function() {

    $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true });
    $(document).ready(function () {
        $('#keywords').select2({
            tags: true,
            tokenSeparators: [' ']
        });
        $('#news_source').select2();


    });


    $(function () {
        datatable();
    });

});

    function datatable(){
        $('#table-rss-news-template').DataTable({
            pageLength: 50,
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                {{--contentType: "application/json",
                dataType: 'JSON',--}}
                type: "POST",
                url: '{!! route('rssfeedsettings.rss_news_table') !!}',
                data: function ( d ) {
                    d.keywords = keywords;
                    d.start_date = start_date;
                    d.end_date = end_date;
                    d.status_news = status_news;
                    d.news_source = news_source;
                    d.news_category = news_category;
                    d.search_val = search_val;
                    d.startDate = startDate;
                    d.endDate = endDate;
                    {{--return JSON.stringify( d );--}}
                    return d;

                },
            },
            columns: [
                {
                    data: 'chk',
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    className: 'w-10'
                },
                {{--{
                    data: 'site_name',
                    name: 'site_name'
                },--}}
                {
                    data: 'source',
                    name: 'source'
                },
                {
                    data: 'title',
                    name: 'title',
                },
                {
                    data: 'cate',
                    name: 'cate',
                    className: 'w-10 text-center'
                },
                {
                    data: 'data_status',
                    name: 'data_status',
                    className: 'w-10 text-center'
                },
                {
                    data: 'public_date',
                    name: 'public_date',
                },
                {
                    data: 'view',
                    name: 'view',
                    className: 'w-10 text-center'
                },
                {
                    data: 'link',
                    name: 'link',
                    className: 'no-wrap'
                },
                {
                    data: 'status',
                    name: 'status',
                    className: 'w-10 text-center'
                },
                {
                    data: 'action',
                    name: 'action',
                    className: 'no-wrap'
                },
            ]
        });
    }


$(document).ready(function(){
       
        $('#source_create').select2({
            allowClear: true,
            tags: true,
            width: '100%',
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'post',
                dataType: "json",
                url: '/rssfeedsettings/rss_data/source',
                delay: 250,
                data: function (params) {
                    return {
                        searchTerm: params.term || '',
                        pageNum: params.page || 1,
                    }
                },
                processResults: function (data) {
                    return {
                    results:  $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.name,
                                value: item.name
                            }
                        })
                    };
                },
            }
        });

        $('#news_source').select2({
            allowClear: true,
            tags: true,
            width: '100%',
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'post',
                dataType: "json",
                url: '/rssfeedsettings/rss_data/source',
                delay: 250,
                data: function (params) {
                    return {
                        searchTerm: params.term || '',
                        pageNum: params.page || 1,
                    }
                },
                processResults: function (data) {
                    return {
                    results:  $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.name,
                                value: item.name
                            }
                        })
                    };
                },
            }
        });
 
        {{--$('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') });--}}
        $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true });
    }); 
    var form_save = '.formSaving';
    $('.formPreview').click(function() {
        form_save = '.formPreview';
    });
    $('.formDraft').click(function() {
        form_save = '.formDraft';
    });
    var number = 0;
    $('.ajaxifyFormCreate').submit(function (event) {
        number++;
        if(number == 1){
            let category = $('#category option:selected').val();
            {{--let topic = $('#topic option:selected').val();--}}
            let title_th = $('#title_th').val();
            let detail_th = $('#detail_th').val();
            let source = $('#source_create').val();
            if(form_save == '.formSaving'){
                if(category == undefined){
                    $('#label_category').css('color', '#a94442');
                    $('#category').css('border-color', '#a94442');
                }else{
                    $('#label_category').css('color', '#656d78');
                    $('#category').css('border-color', '#656d78');
                }
                if(source == undefined){
                    $('#label_source').css('color', '#a94442');
                    $('#source_create').css('border-color', '#a94442');
                }else{
                    $('#label_source').css('color', '#656d78');
                    $('#source_create').css('border-color', '#656d78');
                }
                if(topic == undefined){
                    $('#label_topic').css('color', '#a94442');
                    $('#topic').css('border-color', '#a94442');
                }else{
                    $('#label_topic').css('color', '#656d78');
                    $('#topic').css('border-color', '#656d78');
                }
                if(title_th == ''){
                    $('#label_title_th').css('color', '#a94442');
                    $('#title_th').css('border-color', '#a94442');
                }else{
                    $('#label_title_th').css('color', '#656d78');
                    $('#title_th').css('border-color', '#656d78');
                }
                if(detail_th == ''){
                    $('#label_detail_th').css('color', '#a94442');
                    $('#detail_th').css('border-color', '#a94442');
                }else{
                    $('#label_detail_th').css('color', '#656d78');
                    $('#detail_th').css('border-color', '#656d78');
                }

                if(category == undefined || title_th == '' || detail_th == '' || source == undefined){
                    return false;
                }
            }else if(form_save == '.formDraft'){
                if(title_th == ''){
                    $('#label_title_th').css('color', '#a94442');
                    $('#title_th').css('border-color', '#a94442');
                }else{
                    $('#label_title_th').css('color', '#656d78');
                    $('#title_th').css('border-color', '#656d78');
                }
                if(detail_th == ''){
                    $('#label_detail_th').css('color', '#a94442');
                    $('#detail_th').css('border-color', '#a94442');
                }else{
                    $('#label_detail_th').css('color', '#656d78');
                    $('#detail_th').css('border-color', '#656d78');
                }
                if(title_th == '' || detail_th == ''){
                    return false;
                }
            }
            $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            event.preventDefault();
            var data = new FormData(this);
            if(form_save == '.formPreview'){
                data.append('formsubmit', 'formPreview');
            }else if(form_save == '.formDraft'){
                data.append('formsubmit', 'formDraft');
            }
            axios.post($(this).attr("action"), data).then(function (response) {
                toastr.success(response.data.message, '@langapp('response_status') ');
                $(form_save).html('<i class="fas fa-check"></i> @langapp('save') </span>');
                window.location.href = response.data.redirect;
            }).catch(function (error) {
                if(error.response.data.exception){
                    toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }else{
                    var errors = error.response.data.errors;
                    var errorsHtml= '';
                    $.each( errors, function( key, value ) {
                        errorsHtml += '<li>' + value[0] + '</li>'; 
                    });
                    toastr.error( errorsHtml , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }
            }); 
        }
    });
    function copy_link(value) {
        var tempInput = document.createElement("input");
        tempInput.style = "position: absolute; left: -1000px; top: -1000px";
        tempInput.value = value;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);
    }


    $(function() {
    
        var start = moment();{{--moment().startOf('hour')--}} {{--moment().subtract(1, 'year').startOf('year')--}}
        var end = moment();{{--moment().startOf('hour').add(32, 'hour')--}} {{--moment().subtract(0, 'year').endOf('year')--}}

        function cb(start, end) {
            $('#date_srange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            console.log(start.format('YYYY-MM-DD hh:mm A'));
        }

        $('#date_srange').daterangepicker({
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

    });
</script>
@endpush
@endsection
