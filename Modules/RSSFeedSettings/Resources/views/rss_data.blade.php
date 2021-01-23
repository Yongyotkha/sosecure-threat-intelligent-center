@extends('layouts.app')
@section('content')
<style>
    .w-100{
        width: 100px;
    }

</style>
<section id="content" class="bg">

    <section class="hbox stretch">      
        <aside id="hide-settings" class="aside aside-md b-r" style="display: none">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show" data-target="#setting-nav">@icon('solid/bars')</a>
                    <a class="hide-setting btn btn-icon btn-default btn-sm pull-right m-r-xs">@icon('solid/bars')</a>
                    <p class="h3 text-elipse-setting">News</p>
                </header>
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        <ul class="nav nav-pills nav-stacked no-radius">
                            <li>
                                <a href="{{route('rssfeedsettings.news')}}">
                                    News
                                </a>
                            </li>
                            <li class="active">
                                <a href="{{route('rssfeedsettings.rss_data')}}">
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
                    {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                    @icon('solid/arrow-left')
                    </a> --}}
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;">@icon('solid/bars')</a>
                    <a href="{{route('news.index')}}"
                        class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive m-r-5" style="margin-top: 0;">
                        @icon('solid/arrow-left')
                    </a>
                    <div class="bc-head"> @langapp('rss_feed') Data</div>
                    {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip"
                        title="@langapp('export') CSV">
                        @icon('solid/download') CSV
                    </a> --}}
                    
                    <a id="advance-search" href="#area-advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                        <span><i class="fas fa-filter"></i> @langapp('Search_Advance')</span>
                    </a>
                    
                    <button type="submit" id="btn-change-status" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete" disabled>
                        <span data-rel="tooltip" title="Are you sure?" data-placement="bottom">@icon('solid/trash-alt')
                            @langapp('delete')</span>
                    </button>
                    {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal"
                        data-target="#rss_modal">
                        @icon('solid/plus') @langapp('create')
                    </a> --}}
                </header>

                <section class="scrollable wrapper">
                    <section class="panel panel-default" id="area-advance-search" style="display: none;">
                        <div class="container-fluid" style="padding: 2rem;">
                            <div class="row m-b-md">
                                <div class="col-lg-4">
                                    <label for="">Keywords</label>
                                   <input type="text" class="form-control" name="keywords" id="keywords">
                                </div>
                                {{-- <div class="col-lg-8">
                                    <label for="">Public Date</label>
                                    <div class="input-group date">
                                        <input id="public_date" type="text" class="form-control datetimepicker-input" name="public_date"
                                        data-date-format="DD-MM-YYYY" data-date-start-date="moment()" required>
                                        <div class="input-group-addon">
                                            @icon('solid/calendar-alt', 'text-muted')
                                        </div>
                                    </div>
                                </div> --}}
                                <div class="col-lg-4">
                                    <label for="">Select Date</label>
                                    <div id="date_srange" class="text-center" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                        <i class="fa fa-calendar"></i>&nbsp;
                                        <span></span> <i class="fa fa-caret-down"></i>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <label for="">Status</label>
                                    <select id="status" class="select2-option form-control">
                                        <option value="">All</option>
                                        <option value="1">Used</option>
                                        <option value="2">Not Used</option>
                                    </select>
                                </div>
                                
                            </div>
                            {{-- <div class="row">
                                <div class="col-lg-3">
                                    <label for="">Source</label>
                                    <input type="text" class="form-control" name="source" id="source">
                                </div>
                                <div class="col-lg-4">
                                    <label for="">Status</label>
                                    <select id="status" class="select2-option form-control">
                                        <option value="1" selected>All</option>
                                        <option value="2">Used</option>
                                        <option value="3">Not Used</option>
                                    </select>
                                </div>
                            </div> --}}
                            <br>
                            <div class="row">
                                <div class="col-lg-12 text-right">
                                    <button type="button" class="btn btn-info btn-responsive btn-fz-13" onclick="search()">
                                        <i class="fas fa-search"></i>
                                        @langapp('apply')
                                    </button>
                                    <button type="button" id="btn_rss_data_reset" class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                        <i class="fas fa-broom"></i>
                                        <span> Clear </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>
    
                    <section class="panel panel-default">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-xs-12">
                                    <i class="fas fa-table"></i> Table RSS Data
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-rss-data">
                                    <thead>
                                        <tr>
                                            <th class="no-sort">
                                                <label>
                                                    <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk"/>
                                                    <span class="label-text"></span>
                                                </label>
                                            </th>   
                                            <th>Source</th>                                   
                                            <th>Title</th>
                                            <th>Description</th>
                                            <th>Link</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
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
        </aside>
    </section>
    {{-- ------------------- --}}

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
    <div class="modal" id="delete_rss_data_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="left: unset">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">@langapp('delete')</h4>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <p class="text-danger">@langapp('delete_warning')  </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-default btn-rounded" data-dismiss="modal"><i class="fas fa-times text-muted"></i> Close</a>
                    <button type="button" class="btn btn-info submit btn-rounded delete_domain_submit" onclick="delete_rssData_select_confirm()"><i class="fas fa-paper-plane"></i> OK</button>
                </div>
            </div>
        </div>
    </div>

</section>




@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
@include('stacks.css.datepicker')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css"/>
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.hidesettings')

<script>




    $('#area-advance-search').hide();
    $('#advance-search').click(function(){
        $('#area-advance-search').toggle();
    });
    

    $('#table-rss-data').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {

            $('#btn-change-status').prop("disabled", false);
        } else {
            
            if ($('.select-chk').filter(':checked').length < 1){

                $('#btn-change-status').attr('disabled',true);
            }
        }
    });

           

    $('#table-rss-data').on('click', '.rss_id', function () {
        if ($(this).is(':checked')) {
        
            
            $('#btn-change-status').prop("disabled", false);
        } else {
            if ($('.rss_id').filter(':checked').length < 1){

                $('#btn-change-status').attr('disabled',true);
            }
        }
    });

    



    var search_val = false;
    var keywords = null;
    var status = null;
    var startDate = null;
    var endDate = null;
    var isDateSearch = null;
    var rss_id = [];
    function search(){
        search_val = true;
        keywords = $('#keywords').val();
        status = $('#status option:selected').val();
        startDate =  $("#date_srange").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
        endDate =  $("#date_srange").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');

        datatable();
    }

    $( "#btn-change-status" ).click(function() {
        rss_id = [];
        $('#delete_rss_data_modal').modal('show');
    });
    
    function delete_rssData_select_confirm(){
        $('.rss_id:checked').each(function () {
            rss_id.push(this.value);
        });
        $.ajax({
            type:"POST",
            url:"{{ route('RSSFeedSettingsController.delete_checked') }}",
            data:{
                id:rss_id
            },
            beforeSend: function(){
                $('.delete_domain_submit').html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            },
            success:function(response) {
                $('.delete_domain_submit').html('<i class="fas fa-check"></i> @langapp('save') </span>');
                toastr.success(response.message, '@langapp('response_status')');
                window.location.href = response.redirect;
            },
            error: function (error){
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status') ');
            }
        });

    };
        
    $(function () {
        $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true });
        datatable();
        {{--loading('load');--}}
        {{--f_loading(null, '.vbox');--}}

    });
    function datatable(){
        $('#table-rss-data').DataTable({
            pageLength: 50,
            processing: true,
            serverSide: true,
            destroy: true,
            order: [[ 4, "desc" ]],
            ajax: {
                type: "POST",
                url: '{!! route('rssfeedsettings.rss_data_table') !!}',
                data: function ( d ) {
                    d.keywords = keywords;
                    d.status = status;
                    d.search_val = search_val;
                    d.startDate = startDate;
                    d.endDate = endDate;
                    d.isDateSearch = isDateSearch;

                    return d;
                },
            },
         
            initComplete : function( settings, json){
                $('[data-toggle="tooltip"]').tooltip();
               
                
            },
            createdRow: function ( row, data, index ) {
                $(row).attr('id', 'tr' + data.id);
            },

            columnDefs: [
 

                {
                    targets: 0,
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    width: '1px',
                    render: function (data, type, full, meta) {
                        return '<label><input type="checkbox" name="rss_id" class="rss_id"  value="' + full.id + '"><span class="label-text"></span></label>';
                    },
                },
                {
                    targets: 1,
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    width: '1px',
                    render: function (data, type, full, meta) {

                        if(full.get_rss_source!=null){
                           
                            return '<a href="'+full.get_rss_source.url+'" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-link"></i>'+' '+full.get_rss_source.name.charAt(0).toUpperCase() + full.get_rss_source.name.slice(1)+'</a>';
                        }else{
                            return '';
                        }
                       
                    },
                },
                {
                    targets: 2,
                    width: '10px',
                    render: function (data, type, full, meta) {
    
                            return '<div class="text-elip" data-rel="tooltip" title="'+full.title+'">'+full.title+'</div>';
                    },
                },
                {
                    targets: 3,
                    width: '10px',
                    ype: 'html',
                    render: function (data, type, full, meta) {
                       
                        return '<div class="text-elip" data-rel="tooltip" title="'+full.description+'">'+full.description+'</div>';

                    },
                },
                {
                    targets: 4,
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    width: '10px',
                    render: function (data, type, full, meta) {
    
                            return '<a href="'+full.link+'" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-link"></i> Open</a>';


                            
                        
                    },
                },
                
                {
                    targets: 5,
                    width: '60px',
                    render: function (data, type, full, meta) {
              
    
                            return full.pubDate;

                    },
                },

                {
                    targets: 6,
                    width: '10px',
                    render: function (data, type, full, meta) {

                        if(full.get_rss_news!=null){
                            return '<span class="badge badge-success">Used</span>';
                        }else{
                            return '<span class="badge badge-warning" style="background-color: #ffc107;">Not used</span>';
                        }
                         
                    },
                },
                {
                    targets: 7,
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    width: '55px',
                    render: function (data, type, full, meta) {
                        if(full.get_rss_news!=null){
                
                                return '<a href="{{config("base_url")}}delete-rss_data/'+full.code+'" class="btn btn-danger btn-xs" data-toggle="ajaxModal"><i class="fas fa-trash-alt"></i></a>';

                        }else {
                            let html = '';
                            html += `<a href="${base_url}/rssfeedsettings/rss_data/news/create/${full.code}" class="btn btn-{{get_option("theme_color")}} btn-xs" data-toggle="ajaxModal"><i class="fas fa-share-square"></i></a>`;
                            html += `&nbsp <a href="${base_url}/rssfeedsettings/delete-rss_data/${full.code}" class="btn btn-danger btn-xs" data-toggle="ajaxModal"><i class="fas fa-trash-alt"></i></a>`;
                       
                            return html;
                        }
                    },
                },

            ]
       
        });
    }


    $(function() {
    
        var start = moment().subtract(1, 'month').startOf('month');{{--moment().startOf('hour')--}} {{--moment().subtract(1, 'year').startOf('year')--}}
        var end = moment();{{--moment().startOf('hour').add(32, 'hour')--}} {{--moment().subtract(0, 'year').endOf('year')--}}

        function cb(start, end) {
            $('#date_srange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
     
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
        $('#date_srange').on('apply.daterangepicker', function(ev, picker) {
            isDateSearch = 1;
            if (!picker.startDate.isValid() || !picker.endDate.isValid()) {
                
            }
        });

        cb(start, end);

        $("#btn_rss_data_reset").click(function() {
            search_val = false;
            $("#keywords").val('');
            start = moment().subtract(1, 'month').startOf('month');
            end = moment();
            cb(start, end);
            $("#status").val('').trigger("change");
            datatable();
           

        });

    });
</script>
@endpush
@endsection
