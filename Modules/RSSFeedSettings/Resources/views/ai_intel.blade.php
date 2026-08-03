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
                    <a class="hide-setting btn btn-icon btn-default btn-sm pull-right btn-h-vis-menu">@icon('solid/bars')</a>
                    <p class="h3 text-elipse-setting">News</p>
                </header>
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        <ul class="nav nav-pills nav-stacked no-radius">
                            <li>
                                <a href="{{route('rssfeedsettings.news')}}">
                                    @icon('solid/angle-right', 'text-' . get_option('theme_color'))
                                    News
                                </a>
                            </li>
                            <li>
                                <a href="{{route('rssfeedsettings.rss_data')}}">
                                    @icon('solid/angle-right', 'text-' . get_option('theme_color'))
                                    RSS Data
                                </a>
                            </li>
                            <li class="active">
                                <a href="{{route('rssfeedsettings.ai_intel')}}">
                                    @icon('solid/angle-right', 'text-' . get_option('theme_color'))
                                    AI Intel
                                </a>
                            </li>
                        </ul>
                    </section>
                </section>
            </section>
        </aside>
    
        <aside>
            <section class="vbox">

                <header class="header panel-heading bg-white b-b b-light" >
                    <div class="header-flex-overflow" style="height: 48px;">
                        <div class="fwb-16">
                            <button class="show-setting btn btn-icon btn-default btn-sm m-r-xs">@icon('solid/bars')</button>
                            <a href="{{route('news.index')}}"
                                class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive m-r-5" style="margin-top: 0;">
                                @icon('solid/arrow-left')
                            </a>
                            <span>
                                AI Intel
                            </span>
                        </div>
        
                        <div class="ml-2 text-right">

                            <button type="button" id="btn_sync_intel" class="btn btn-sm btn-{{ get_option('theme_color') }}">
                                <span data-rel="tooltip" title="Sync Intel" data-placement="bottom"><i class="fas fa-sync-alt"></i><span class="hide-text"> Sync Intel</span></span>
                            </button>

                            <a id="advance-search" href="#hide-advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }}">
                                <span data-rel="tooltip" title="Filter" data-placement="bottom"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span> </span>
                            </a>
                            
                            <button type="submit" id="btn-change-status" class="btn btn-sm btn-danger" value="bulk-delete" disabled>
                                <span data-rel="tooltip" title="Delete" data-placement="bottom">@icon('solid/trash-alt')
                                   <span class="hide-text">@langapp('delete')</span> </span>
                            </button>
                        </div>
                    </div>
                </header>

                <section class="scrollable wrapper">
                    <section class="panel panel-default" id="hide-advance-search" style="display: none;">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-md-12">
                                    <i class="fas fa-filter"></i> Filter
                                </div>
                        </header>
                        <div class="panel-body" style="padding: 0 !important">
                            <div class="container-fluid" style="padding: 2rem;">
                                <div class="row m-b-md">
                                    <div class="col-lg-4">
                                        <label for="">Keywords</label>
                                    <input type="text" class="form-control" name="keywords" id="keywords">
                                    </div>
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
                                <br>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <div class="row">
                                <div class="col-lg-12 text-right">
                                    <button type="button" class="btn btn-info btn-responsive btn-fz-13" onclick="search()">
                                        <i class="fas fa-search"></i>
                                        @langapp('apply')
                                    </button>
                                    <button type="button" id="btn_ai_intel_reset" class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                        <i class="fas fa-broom"></i>
                                        <span> Clear </span>
                                    </button>
                                    <button type="button" id="close_filter" class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                        <i class="fas fa-times"></i>
                                        <span> Close </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>
    
                    <section class="panel panel-default">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-xs-12">
                                    <i class="fas fa-table"></i> Table AI Intel
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-ai-intel">
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
                                            <th>Summary</th>
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

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
    <div class="modal" id="delete_ai_intel_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="left: unset">
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
                    <button type="button" class="btn btn-info submit btn-rounded delete_domain_submit" onclick="delete_aiIntel_select_confirm()"><i class="fas fa-paper-plane"></i> OK</button>
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
@include('stacks.js.advanced_search')

<script>



    $('#table-ai-intel').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {

            $('#btn-change-status').prop("disabled", false);
        } else {
            
            if ($('.select-chk').filter(':checked').length < 1){

                $('#btn-change-status').attr('disabled',true);
            }
        }
    });

           

    $('#table-ai-intel').on('click', '.ai_id', function () {
        if ($(this).is(':checked')) {
        
            
            $('#btn-change-status').prop("disabled", false);
        } else {
            if ($('.ai_id').filter(':checked').length < 1){

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
    var ai_id = [];
    function search(){
        search_val = true;
        keywords = $('#keywords').val();
        status = $('#status option:selected').val();
        startDate =  $("#date_srange").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
        endDate =  $("#date_srange").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');

        datatable();
    }

    $( "#btn-change-status" ).click(function() {
        ai_id = [];
        $('#delete_ai_intel_modal').modal('show');
    });

    $('#btn_sync_intel').click(function () {
        var $btn = $(this);
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fas fa-spin fa-spinner"></i><span class="hide-text"> Syncing...</span>');

        $.ajax({
            type: 'POST',
            url: '{{ route('rssfeedsettings.ai_intel_sync') }}',
            data: {},
            success: function (response) {
                toastr.success(response.message || 'Sync Intel completed.', '@langapp('response_status')');
                if (typeof datatable === 'function') {
                    datatable();
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'Sync failed';
                toastr.error(msg, '@langapp('response_status')');
            },
            complete: function () {
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
    
    function delete_aiIntel_select_confirm(){
        $('.ai_id:checked').each(function () {
            ai_id.push(this.value);
        });
        $.ajax({
            type:"POST",
            url:"{{ route('rssfeedsettings.ai_intel_delete_checked') }}",
            data:{
                id:ai_id
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

    });
    function datatable(){
        $('#table-ai-intel').DataTable({
            pageLength: 50,
            processing: true,
            serverSide: true,
            destroy: true,
            "autoWidth" : false,
            "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
            order: [[ 5, "desc" ]],
            ajax: {
                type: "POST",
                url: '{!! route('rssfeedsettings.ai_intel_table') !!}',
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
                    name:'id',
                    render: function (data, type, full, meta) {
                        return '<label><input type="checkbox" name="ai_id" class="ai_id"  value="' + full.id + '"><span class="label-text"></span></label>';
                    },
                },
                {
                    targets: 1,
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    width: '115px',
                    render: function (data, type, full, meta) {
                        if(full.source){
                            return '<span class="btn btn-xs btn-info"><i class="fas fa-robot"></i> '+full.source+'</span>';
                        }
                        return '<span class="btn btn-xs btn-info"><i class="fas fa-robot"></i> AI</span>';
                    },
                },
                {
                    targets: 2,
                    width: '245px',
                    name:'title',
                    render: function (data, type, full, meta) {
    
                            return '<div class="text-elip" data-rel="tooltip" title="'+ (full.title || '') +'">'+ (full.title || '-') +'</div>';
                    },
                },
                {
                    targets: 3,
                    width: '300px',
                    type: 'html',
                    name:'executive_summary',
                    render: function (data, type, full, meta) {
                        let summary = full.executive_summary || '';
                        try {
                            const parsed = JSON.parse(summary);
                            if (Array.isArray(parsed)) {
                                summary = parsed.join(' ');
                            }
                        } catch (e) {}
                        const plain = $('<div>').html(summary).text();
                        return '<div class="text-elip" data-rel="tooltip" title="'+plain+'">'+plain+'</div>';

                    },
                },
                {
                    targets: 4,
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    name:'source_url',
                    width: '10px',
                    render: function (data, type, full, meta) {
                        if (full.source_url) {
                            return '<a href="'+full.source_url+'" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-link"></i> Open</a>';
                        }
                        return '-';
                    },
                },
                
                {
                    targets: 5,
                    name:'published_at',
                    width: '60px',
                    render: function (data, type, full, meta) {
                        if (!full.published_at) return '-';
                        return (full.published_at || '').toString().substring(0, 19).replace('T', ' ');
                    },
                },

                {
                    targets: 6,
                    width: '10px',
                    render: function (data, type, full, meta) {

                        if(full.get_rss_news!=null || full.status === 'promoted'){
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
                    name:'code',
                    className : 'nowrap',
                    render: function (data, type, full, meta) {
                        if(full.get_rss_news!=null || full.status === 'promoted'){
                
                                return '<a href="'+base_url+'/rssfeedsettings/delete-ai_intel/'+full.code+'" class="btn btn-danger btn-xs" data-toggle="ajaxModal"><i class="fas fa-trash-alt"></i></a>';

                        }else {
                            let html = '';
                            html += `<a href="${base_url}/rssfeedsettings/ai_intel/news/create/${full.code}" class="btn btn-{{get_option("theme_color")}} btn-xs" data-toggle="ajaxModal">Create News</a>`;
                            html += `&nbsp <a href="${base_url}/rssfeedsettings/delete-ai_intel/${full.code}" class="btn btn-danger btn-xs" data-toggle="ajaxModal"><i class="fas fa-trash-alt"></i></a>`;
                       
                            return html;
                        }
                    },
                },

            ]
       
        });
    }


    $(function() {
    
        var start = moment().subtract(1, 'month').startOf('month');
        var end = moment();

        function cb(start, end) {
            $('#date_srange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
     
        }

        $('#date_srange').daterangepicker({
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
        $('#date_srange').on('apply.daterangepicker', function(ev, picker) {
            isDateSearch = 1;
            if (!picker.startDate.isValid() || !picker.endDate.isValid()) {
                
            }
        });

        cb(start, end);

        $("#btn_ai_intel_reset").click(function() {
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
