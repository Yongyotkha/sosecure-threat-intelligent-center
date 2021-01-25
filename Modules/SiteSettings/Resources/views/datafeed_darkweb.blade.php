@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">  
        <aside id="hide-settings" class="aside aside-md b-r" style="display: none">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#setting-nav">@icon('solid/bars')
                    </a>
                    <a class="hide-setting btn btn-icon btn-default btn-sm pull-right m-r-xs">@icon('solid/bars')</a>
                    <p class="h3 text-elipse-setting">Compromise</p>
                </header>
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_data_compromised')
                    </section>
                </section>
            </section>
        </aside>

        <aside>
            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light">
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;">@icon('solid/bars')</a>
                    <div class="bc-head">Compromise Feed  </div>
                    {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                        @icon('solid/download') CSV
                    </a> --}}
                    

                    <button id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                        <span><i class="fas fa-filter"></i> @langapp('Search_Advance')</span>
                     </button>

                     @if(!empty(get_role_custom()))
                        {{-- // var_dump(get_role_custom()['superadmin']);
                        // var_dump(get_role_custom()['site_admin']); --}}
                        @if(@get_role_custom()['superadmin'] == 1 || @get_role_custom()['site_admin'] == 1)
                            <a id="btn_compromise_data" href="{{site_url('/darkweb-datas')}}" class="btn btn-sm btn-info pull-right m-xs"><span> Compromise data</span></a>
                        @endif
                    @endif

                     <button id="btn-change-status" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" onclick="change_status_compromised_feed()" disabled>
                        Change Status
                    </button>

                    <div class="pull-right" style="margin-top: 8px; width: 300px;">
                        <select name="site" id="site" class="select2-option form-control select-site" style="min-width: 300px">
                            <option value="">All Site</option>
                            @if($SiteSettings)
                            @foreach($SiteSettings as $SiteSettings_val)
                            <option value="{{$SiteSettings_val->id}}">{{$SiteSettings_val->name}}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>


                    {{-- <button type="button" id="btn_del_select" class="btn btn-sm btn-danger m-xs  pull-right" value="bulk-delete" disabled>
                        <span data-rel="tooltip" title="Are you sure?" data-placement="bottom">@icon('solid/trash-alt') @langapp('delete')</span>
                    </button> --}}
                    
                    
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
                                    <div class="col-lg-12">
                                        <div class="row d-flex align-items-center">
                                            <label for="" class="col-sm-1 col-xs-12 col-form-label">Search</label>
                                            <div class="col-sm-11 col-xs-12">
                                                <input type="text" id="search" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4">
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
                                    </div>
                                    <div class="col-lg-4 text-center">
                                        <div id="datafeed_date" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                            <i class="fa fa-calendar"></i>&nbsp;
                                            <span></span> <i class="fa fa-caret-down"></i>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 text-center">
                                        <div style="margin-top: 8px;">
                                            <label class="mr-3">
                                                <input type="checkbox" name="check_all" id="check_all" value="TRUE">
                                                <span class="label-text" style="font-size: 16px;">All</span>
                                            </label>
                                            <label class="mr-3">
                                                <input type="checkbox" name="check_pending" id="check_pending" value="TRUE">
                                                <span class="label-text" style="font-size: 16px;">Panding</span>
                                            </label>
                                            <label class="mr-3">
                                                <input type="checkbox" name="check_approved" id="check_approved" value="TRUE">
                                                <span class="label-text" style="font-size: 16px;">Approved</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12 text-right mt-2">
                                        <button type="button" id="btn_darkweb_feed_search" class="btn btn-info btn-responsive btn-fz-13">
                                            <i class="fas fa-search"></i>
                                            @langapp('apply')
                                        </button>
                                        <button type="button" id="btn_darkweb_feed_reset" class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                            <i class="fas fa-broom"></i>
                                            <span> Clear </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    
                    <section class="panel panel-default">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-xs-12">
                                    <i class="fas fa-table"></i> Table Compromise Feed 
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table  class="table table-striped" id="table_darkweb_feed">
                                    <thead>
                                        <tr>
                                            <th class="no-sort w-10">
                                                <label>
                                                    <input name="select_all" value="1" id="select-all" type="checkbox" class="data_feed_id select-chk"/>
                                                    <span class="label-text"></span>
                                                </label>
                                            </th>
                                            <th>Site</th>
                                            <th>Type</th>
                                            <th>Source</th>
                                            <th>Keyword Ref</th>
                                            <th>Content</th>
                                            <th>Data Feed</th>
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
        </aside>
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
    <!-- Modal create_assets_vulnerability -->
    <div class="modal in fixed-left" id="change_status" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        Confirm Information
                    </h4>
                </div>
                
                <div class="modal-body">
                    <div class="form-group row">
                        <div class="col-md-3">
                            <label for="">Content</label>
                        </div>
                        <div class="col-md-9">
                            <div id="show_content_compromise_feed"></div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="" class="col-md-3">Status</label>
                        <div class="col-md-9">
                            <select id="status_action_compromise_feed" class="form-control select2">
                                <option value="1">Approved</option>
                                <option value="2">Cancle</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row sent_mail_compromise_feed_row">
                        <label for="" class="col-md-3">Send Mail</label>
                        <div class="col-md-9">
                            <label>
                                <input type="checkbox" name="sent_mail" id="sent_mail_compromise_feed" class="" value="true">
                                <span class="label-text">Sent mail to customers</span>
                            </label>
                        </div>
                    </div>
                </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="button" onclick="confirm_compromised_feed()" class="btn btn-info btn-rounded">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
                
            </div>
        </div>
    </div>

    <div class="modal in fixed-left" id="confirm-change-status" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        Confirm Information
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="" class="col-md-3">Content</label>
                        <div class="col-md-9">
                            <textarea name="detail_content" class="form-control" id="detail_content" cols="30" rows="10">
                            </textarea>
                        </div>
                    </div>
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
                        <label for="" class="col-md-3">Send Mail</label>
                        <div class="col-md-9">
                            <label>
                                <input type="checkbox" name="sent_mail" id="sent_mail" class="" value="true">
                                <span class="label-text">Sent mail to customers</span>
                            </label>
                        </div>
                    </div>

                    <label></label>

                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="submit" class="btn btn-info btn-rounded" onclick="confirm_approve()">
                        <i class="fas fa-paper-plane"></i>
                        Save
                        {{-- Yes, approve --}}
                    </button>
                </div>
            </div>
        </div>
    </div>

    
    <div class="modal in fixed-left" id="confirm-change-status-cancle" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        Confirm Information
                    </h4>
                </div>
                <div class="modal-body">
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
                        Save
                        {{-- Yes, cancel --}}
                    </button>
                </div>
            </div>
        </div>
    </div>

</section>

@push('pagestyle')
    @include('stacks.css.summernote')
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
@include('scripts.summernote')
<script>

var search_val = 0;
    var keywords = null;
    var site = null;
    var source = null;
    var start_date = null;
    var end_date = null;
    var isDateSearch = null;
    var check_all = false;
    var check_pending = false;
    var check_approved = false;

    var val_id = [];

    $('#table_darkweb_feed').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {
            $('#btn_del_select').prop("disabled", false);
        } else {
            if ($('.select-chk').filter(':checked').length < 1){
                $('#btn_del_select').attr('disabled',true);
            }
        }
    });

    $('#table_darkweb_feed').on('click', '.val_id', function () {
        if ($(this).is(':checked')) {
            $('#btn_del_select').prop("disabled", false);
        } else {
            if ($('.val_id').filter(':checked').length < 1){
                $('#btn_del_select').attr('disabled',true);
            }
        }
    });

    function change_status_compromised_feed(){
        let value_id = [];
        $('.data_feed_id:checked').each(function () {
            value_id.push(this.value);
        });
        $('#show_content_compromise_feed').empty();
        $.ajax({
            type:"POST",
            url:"{{ route('socialdatas.get_data_feed') }}",
            data:({
                id: value_id,
            }),
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
                loading('stop_load');
                if(response.status_code == 200){
                    if(response.data.length > 0){
                        let html = ``;
                        for(let i in response.data){
                            const data = response.data[i];
                            html += `
                                <textarea name="" class="form-control content_compromise_feed" id="content_compromise_feed_${data.id}" cols="30" rows="10"></textarea>
                                <br>
                            `;
                        }
                        $('#show_content_compromise_feed').html(html);
                        $('.content_compromise_feed').summernote();
                        for(let i in response.data){
                            const data = response.data[i];
                            $('#content_compromise_feed_' + data.id).summernote(
                                'code' , data.feedcontent,
                            );
                        }
                    }
                    $('#change_status').modal('show');
                    let status_action = $('#status_action_compromise_feed :selected').val();
                    if(status_action == 1){
                        $('#show_content_compromise_feed').show();
                        $('.sent_mail_compromise_feed_row').show();
                    }

                    $('#status_action_compromise_feed').change(function(){
                        if($(this).val() == 1){
                            $('#show_content_compromise_feed').show();
                            $('.sent_mail_compromise_feed_row').show();
                        }else{
                            $('#show_content_compromise_feed').hide();
                            $('.sent_mail_compromise_feed_row').hide();
                        }
                        
                    })

                }else{

                }
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

    function confirm_compromised_feed(){
        let value_id = [];
        $('.data_feed_id:checked').each(function () {
            value_id.push(this.value);
        });

        let content = [];
        $('.content_compromise_feed').each(function () {
            content.push($(this).val());
        });

        let sent_mail = 0;
        if ($("#sent_mail_compromise_feed").is(':checked')) {
            sent_mail = 1;
        }

        let status_action = $('#status_action_compromise_feed :selected').val();

        $.ajax({
            type:"POST",
            url:"{{ route('socialdatas.approve_compromised_feed') }}",
            data:({
                id: value_id,
                content: content,
                status_action: status_action,
                sent_mail: sent_mail
            }),
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
                loading('stop_load');
                toastr.success(response.message, '@langapp('response_status')');
                table_social_data();
                $('#status_action_compromise_feed').val(1).change();
                $('#sent_mail_compromise_feed').prop('checked', false);
                $('#change_status').modal('hide');
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

$(function() {
    table_social_data();
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

    $("#btn_darkweb_feed_reset").click(function() {
        search_val = 0;
        $("#search").val('');
        $("#source_select").val('').trigger('change');
        $("#check_all").prop("checked",false);
        $("#check_pending").prop("checked",false);
        $("#check_approved").prop("checked",false);

        cb(moment().startOf('hour'), moment().startOf('hour').add(32, 'hour'));

        table_social_data();
    });

});

$("#btn_darkweb_feed_search").click(function() {
    search_val = 1;
    if ($('#check_all').is(":checked")) {
        check_all = true;
    } else {
        check_all = false;
    }
    if ($('#check_pending').is(":checked")) {
        check_pending = true;
    } else {
        check_pending = false;
    }
    if ($('#check_approved').is(":checked")) {
        check_approved = true;
    } else {
        check_approved = false;
    }
    start_date = $("#datafeed_date").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
    end_date = $("#datafeed_date").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');

    table_social_data();
});


function table_social_data(){
    let site = $('#site').val();
    let search = $('#search').val();
    let source_select = $('#source_select').val();

    $('#table_darkweb_feed').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        "dom": '<"d-flex d-inline-flex justify-content-between"Bf><"top"l>rt<"bottom"ip><"clear">',
        ajax: {
            url: '{!! route('datafeed.darkweb_datatables') !!}',
            data: {
                "search_val" : search_val,
                "search" : search,
                "source_select" : source_select,
                "start_date" : start_date,
                "end_date" : end_date,
                "check_all" : check_all,
                "check_pending" : check_pending,
                "check_approved" : check_approved,
                "site" : site
            },
            type: "POST",
        },
        order: [
            [0, "desc"]
        ],
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
                data: 'type',
                name: 'type'
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
                name: 'action'
            },
        ],
        columnDefs: [
            {
                targets: 5,
                render: function (data, type, full, meta) {

                    var feedcontent = full.content;
                    var res = full.keyword.split(",");
                    let content = '';
                    for(let i in res){
                        const data = res[i];
                        content += feedcontent.replace(data, '<span class="badge bg-warning">'+data+'</span>');
                    }
                    return '<div class="text-elip" data-rel="tooltip" title="'+feedcontent+'">'+content+'</div>';
                },
            },
        ]
    });
}

$('#source_select').select2();
var data_feed_id = [];
$('#table_darkweb_feed').on('click', '.data_feed_id', function () {
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

function confirm_approve(){
    $('.data_feed_id:checked').each(function () {
        data_feed_id.push(this.value);
    });

    let sent_mail = 0;
    if ($("#sent_mail").is(':checked')) {
        sent_mail = 1;
    }
    $.ajax({
        type:"POST",
        url:"{{ route('socialdatas.approve_data_feed') }}",
        data:({
            id: data_feed_id,
            sent_mail: sent_mail
        }),
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
        confirm_approve();
    }else{
        confirm_cancle();
    }
}





</script>
@endpush
@endsection
