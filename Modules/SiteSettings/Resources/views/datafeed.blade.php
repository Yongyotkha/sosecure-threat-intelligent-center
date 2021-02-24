@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">  
        <aside id="hide-settings" class="aside aside-md b-r" style="display: none">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show" data-target="#setting-nav">@icon('solid/bars')</a>
                    <a class="hide-setting btn btn-icon btn-default btn-sm pull-right btn-h-vis-menu">@icon('solid/bars')</a>
                    <p class="h3 text-elipse-setting">Data Leak</p>
                </header>
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_data_leak')
                    </section>
                </section>
            </section>
        </aside>

            <section class="vbox">

                <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
                    <div class="header-flex-overflow m-t-10">
                        <div class="fwb-16">
                            @if(@get_role_custom()['superadmin'] == 1 || @get_role_custom()['site_admin'] == 1)
                            <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs">@icon('solid/bars')</a>
                            <a href="{{ url('/socialdatas') }}" class="btn btn-info btn-sm btn-responsive m-r-5"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M257.5 445.1l-22.2 22.2c-9.4 9.4-24.6 9.4-33.9 0L7 273c-9.4-9.4-9.4-24.6 0-33.9L201.4 44.7c9.4-9.4 24.6-9.4 33.9 0l22.2 22.2c9.5 9.5 9.3 25-.4 34.3L136.6 216H424c13.3 0 24 10.7 24 24v32c0 13.3-10.7 24-24 24H136.6l120.5 114.8c9.8 9.3 10 24.8.4 34.3z"></path></svg></a>
                            @endif
                            <span>
                                Data Leak Feed
                            </span>
                        </div>

                        <div class="ml-2 text-right">
                            <div class="text-left max-w-select" style="margin-right:5px;display:inline-block;">
                                <select name="site" id="site" class="text-left select2-option form-control select-site">
                                    <option value="">All Site</option>
                                    @if($SiteSettings)
                                    @foreach($SiteSettings as $SiteSettings_val)
                                    <option value="{{$SiteSettings_val->code}}">{{$SiteSettings_val->name}}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
    
                            <button type="button" id="button" class="btn btn-sm btn-danger m-xs " value="bulk-delete" disabled style="display: none;">
                                <span data-rel="tooltip" title="Are you sure?" data-placement="bottom">@icon('solid/trash-alt') @langapp('delete')</span>
                            </button>
        
                            <button id="btn-change-status" class="btn btn-sm btn-{{ get_option('theme_color')  }}" data-toggle="modal" data-target="#change_status" disabled>
                                Change Status
                            </button>

                            <a href="#hide-advance-search" id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} ">
                                <span data-rel="tooltip" title="Filter" data-placement="bottom"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></span>
                            </a>
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
                                <div class="row">
                                    <div class="col-lg-12 mb-1">
                                        <h5 class="font-weight-bold">Content</h5>
                                        <input type="text" id="search" class="form-control">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 mb-1">
                                        <h5 class="font-weight-bold">Type</h5>
                                        <select id="type" class="form-control select-2-type">
                                            <option value="">All</option>
                                            <option value="social">PUBLIC</option>
                                            <option value="darkweb_public">DARK WEB</option> 
                                        </select>
                                    </div>
                                    {{-- <div class="col-lg-4">
                                        <div class="row d-flex align-items-center">
                                            <label for="" class="col-sm-3 col-xs-12 col-form-label">Source</label>
                                            <div class="col-sm-9 col-xs-12">
                                                <select id="source_select" class="form-control">
                                                    <option value="">All</option>
                                                    @if($DataLeakSocial)
                                                        @foreach($DataLeakSocial as $DataLeakSocial_val)
                                                            <option value="{{$DataLeakSocial_val->id}}">{{$DataLeakSocial_val->source}}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div> --}}
                                    <div class="col-lg-6 mb-1">
                                        <h5 class="font-weight-bold">Date</h5>
                                        <div id="datafeed_date" class="text-center" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                            <i class="fa fa-calendar"></i>&nbsp;
                                            <span></span> <i class="fa fa-caret-down"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12 mb-1">
                                        <h5 class="font-weight-bold">Status</h5>
                                        <div id="groupby-status" class="btn-group special">
                                            <button class="btn btn-grey check_status active" id="all" value="">
                                                <span> All </span>
                                            </button>
                                            <button class="btn btn-grey check_status" value="1">
                                                <span> Panding </span>
                                            </button>
                                            <button class="btn btn-grey check_status" value="2">
                                                <span> Approved </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <div class="row">
                                <div class="col-lg-12 text-right">
                                    <button type="button" id="btn_data_leak_search" class="btn btn-info btn-responsive btn-fz-13" <!--onclick="table_social_data();-->">
                                        <i class="fas fa-search"></i>
                                        @langapp('apply')
                                    </button>
                                    <button type="button" id="btn_data_leak_reset" class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
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
                                    <i class="fas fa-table"></i> Table Data Leak Feed
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">

                            {{-- <div class="row">
                                <div class="col-md-12">
                                    <h5 class="font-weight-bold">Keyword</h5>
                                    <div id="fillter_click_keyword" class="button-group">
                                        <a class="btn btn-selector active" href="#">Event (0)</a>
                                        <a class="btn btn-selector" href="#">Attribute (0)</a>
                                        <a class="btn btn-selector" href="#">OTX (0)</a>
                                        <a class="btn btn-selector" href="#">MISP (0)</a>
                                    </div>
                                </div>
                            </div> --}}

                            <div class="table-responsive">
                                <table  class="table table-striped" id="table_data_feed">
                                    <thead>
                                        <tr>
                                            <th class="no-sort w-10">
                                                <label>
                                                    <input name="select_all" value="1" id="select-all" type="checkbox" class="data_feed_id"/>
                                                    <span class="label-text"></span>
                                                </label>
                                            </th>
                                            <th>Site</th>
                                            <th>Type</th>
                                            <th>Keyword</th>
                                            <th>Content</th>
                                            <th>Data Leak Feed</th>
                                            <th>URL</th>
                                            <th class="no-sort">@langapp('action')</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </section>
            </section>
     
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
    <!-- Modal create_assets_vulnerability -->
    <div class="modal in fixed-left" id="change_status" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside size-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Confirm Information</h4>
                </div>
                
                <div class="modal-body">
                    <form action="">
                    <div class="form-group row">
                        <label for="" class="col-md-3">Status</label>
                        <div class="col-md-9">
                            <select id="status_action" class="form-control select2">
                                <option value="1">Approved</option>
                                <option value="2">Cancle</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="" class="col-md-3">Sent mail</label>
                        <div class="col-md-9">
                            <label><input type="checkbox" name="sent_mail" id="sent_mail" value="true"><span class="label-text">Sent mail to customers</span></label>
                        </div>
                    </div>
                </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="button" onclick="change_status()" class="btn btn-info btn-rounded">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
               
            </div>
        </div>
    </div>

    <div class="modal in fixed-left" id="confirm-change-status" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside size-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Confirm Information</h4>
                </div>
                <div class="modal-body">
                    {{-- <div class="form-group row">
                        <label for="" class="col-md-3">Status</label>
                        <div class="col-md-9">
                            <select id="status_action" class="form-control select2">
                                <option value="1">Approved</option>
                                <option value="2">Cancle</option>
                            </select>
                        </div>
                    </div> --}}
                    <div class="form-group row">
                        <label for="" class="col-md-3">Sent mail</label>
                        <div class="col-md-9">
                            <label><input type="checkbox" name="sent_mail" class="sent_mail" value="true"><span class="label-text">Sent mail to customers</span></label>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="submit" class="btn btn-info btn-rounded" onclick="confirm_approve('one')">
                        <i class="fas fa-paper-plane"></i>
                        Yes, approve
                    </button>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal in fixed-left" id="confirm-change-status-cancle" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside size-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Confirm Information</h4>
                </div>
                <div class="modal-body">

                    {{-- <div class="form-group row">
                        <label for="" class="col-md-3">Status</label>
                        <div class="col-md-9">
                            <select id="status_action" class="form-control select2">
                                <option value="1">Approved</option>
                                <option value="2">Cancle</option>
                            </select>
                        </div>
                    </div> --}}
                    
                    <span class="modal-title">Are you sure you want to cancel this item?</span>
                    <br>
                  
                    {{-- <label><input type="checkbox" name="sent_mail" class="" value="true"><span class="label-text">Sent mail to customers</span></label> --}}

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="button" class="btn btn-info btn-rounded" onclick="confirm_cancle()">
                        <i class="fas fa-paper-plane"></i>
                        Yes, cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

</section>

@push('pagestyle')
    @include('stacks.css.datatables')
    @include('stacks.css.datepicker')
    @include('stacks.css.form')
    <link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css"/>
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.menusub')
@include('stacks.js.hidesettings')
@include('stacks.js.advanced_search')
@include('stacks.js.fullscreen')
@include('stacks.js.activebutton')
<script>
    active_btn('#fillter_click_keyword .btn-selector');
    active_btn('#groupby-status .btn-grey');
    var search_val = 0;
    var start_date = '';
    var end_date = '';
    var check_type = null;
    var search = null;
    var site = null;
    var type = null;


    $(function() {
        table_social_data();
    });


    $('.select-2-type').select2();
    $('select').select2({
    minimumResultsForSearch: -1
    });
    
    $(function() { 
        var start = moment().startOf('hour');
        var end = moment().startOf('hour').add(32, 'hour');
        function cb(start, end) {
            $('#datafeed_date span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        }
        $('#datafeed_date').daterangepicker({
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

        $("#btn_data_leak_reset").click(function() {
            search_val = 0;
            $("#search").val('');
            $("#site").val('').trigger('change');
            $("#type").val('').trigger('change');
            $(".btn-grey").removeClass("active");
            $("#all").addClass ( "active" );
            check_type = null;
            search = null;
            type = null;
            start_date = null;
            end_date = null;

            cb(moment().startOf('hour'), moment().startOf('hour').add(32, 'hour'));

            table_social_data();
        });

    });

    $(".btn-grey").click(function() {
        check_type = $(this).val();
   
    });

    $("#site").change(function() {
        site = this.value;     
        table_social_data();

    });



    $("#btn_data_leak_search").click(function() {
        search_val = 1;
        search = $('#search').val();
        type = $('#type').val();
        start_date = $("#datafeed_date").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
        end_date = $("#datafeed_date").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');

        table_social_data();
    });



    function table_social_data(){

        $('#table_data_feed').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
            ajax: {
                url: '{!! route('socialdatas.datafeedsocial_datatables') !!}',
                data: {
                    "search_val" : search_val,
                    "search" : search,
                    "type" : type,
                    "start_date" : start_date,
                    "end_date" : end_date,
                    "check_type" : check_type,
                    "site" : site,
                },
                type: "POST",
            },
            "order": [ 5, 'desc' ],
            columns: [
                {
                    data: 'chk',
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    className: 'w-10'
                },  
                {
                    data: 'site',
                    name: 'site'
                },
                {
                    data: 'source',
                    name: 'source'
                },
                {
                    data: 'keyword',
                    name: 'keyword'
                },
                {
                    data: 'content',
                    name: 'content'
                },
                {
                    data: 'data_feed',
                    name: 'data_feed',
                    className: 'no-wrap'
                },
                {
                    data: 'url',
                    name: 'url',
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                },
            ],
            columnDefs: [
                {
                    targets: 4,
                    render: function (data, type, full, meta) {
                        var feedcontent = full.feedcontent;
                        var res = full.keyword.split(",");
                        let content = '';
                        for(let i in res){
                            const data = res[i];
                            content += feedcontent.replaceAll(data, '<span class="badge bg-warning">'+data+'</span>');
                        }
                        
                        return '<div>'+content+'</div>';
                    },
                },
            ]
        });
    }

    $('#source_select').select2();
    var data_feed_id = [];
    $('#table_data_feed').on('click', '.data_feed_id', function () {
        if ($(this).is(':checked')) {
            $('#btn-change-status').prop("disabled", false);
        } else {
            if ($('.data_feed_id').filter(':checked').length < 1){
                $('#btn-change-status').attr('disabled',true);
            }
        }
    });



    function approve_dataFeed(id){
        data_feed_id = [];
        data_feed_id.push(id);
    }

    function cancle_dataFeed(id){
        data_feed_id = [];
        data_feed_id.push(id);
    }

    function confirm_approve(mode){
        $('.data_feed_id:checked').each(function () {
            data_feed_id.push(this.value);
        });
        let sent_mail = 0;
        if(mode == 'one'){
            if ($(".sent_mail").is(':checked')) {
                sent_mail = 1;
            }
        }else{
            if ($("#sent_mail").is(':checked')) {
                sent_mail = 1;
            }
        }
        
        $.ajax({
            type:"POST",
            url:"{{ route('socialdatas.approve_data_feed') }}",
            data:{
                id: data_feed_id,
                sent_mail: sent_mail
            },
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
                loading('stop_load');
                toastr.success(response.message, '@langapp('response_status')');
                window.location.href = response.redirect;
            },
            error: function (error){
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status') ');
            }
        });
    }

    function confirm_cancle(){
        $('.data_feed_id:checked').each(function () {
            data_feed_id.push(this.value);
        });
        $.ajax({
            type:"POST",
            url:"{{ route('socialdatas.cancle_data_feed') }}",
            data:{id: data_feed_id},
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
                loading('stop_load');
                toastr.success(response.message, '@langapp('response_status')');
                window.location.href = response.redirect;
            },
            error: function (error){
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status') ');
            }
        });
    }

    function change_status(){
        let status_action = $('#status_action :selected').val();
        if(status_action == 1){
            confirm_approve('many');
        }else{
            confirm_cancle();
        }
    }

</script>
@endpush
@endsection
