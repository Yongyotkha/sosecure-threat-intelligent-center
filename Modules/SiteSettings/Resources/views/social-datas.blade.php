@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">      
        <aside id="hide-settings" class="aside aside-md b-r">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#setting-nav">@icon('solid/bars')</a>
                        <a class="hide-setting btn btn-icon btn-default btn-sm pull-right m-r-xs">@icon('solid/bars')</a>
                        <p class="h3 text-elipse-setting">{{@$siteSettings->name}}</p>
                </header>
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_site')
                    </section>
                </section>
            </section>
        </aside>
        <aside>
            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light">
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display:none;">@icon('solid/bars')</a>
                    <div class="bc-head">Site Settings > Data Leak Data </div>
                    {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                        @icon('solid/download') CSV
                    </a> --}}
                    <button type="submit" id="btn_del_select" class="btn btn-sm btn-danger m-xs  pull-right" value="bulk-delete" disabled>
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt') @langapp('delete')</span>
                    </button>
                    <button id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                        <span><i class="fas fa-filter"></i> @langapp('Search_Advance')</span>
                     </button>
                </header>
                <section class="scrollable wrapper">
                    <section class="panel panel-default" id="hide-advance-search" style="display: none">
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
                                            <select id="source" class="select2-option form-control">
                                                <option value="1" selected>All</option>
                                                @if ($source)

                                                @foreach ($source as $source)
                                                <option value="{{$source->id}}">{{$source->source}}
                                                </option>
                                                @endforeach

                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 text-center">
                                    <div id="social_datas_date" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                        <i class="fa fa-calendar"></i>&nbsp;
                                        <span></span> <i class="fa fa-caret-down"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 text-right mt-2">
                                    <button type="button" id="btn_news_search" class="btn btn-info btn-responsive btn-fz-13" onclick="table_social_data(1)">
                                        <i class="fas fa-search"></i>
                                        @langapp('apply')
                                    </button>
                                    <button type="button" id="btn_news_reset" class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                        <i class="fas fa-broom"></i>
                                        <span> Clear </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    
                    <section class="panel panel-default">
                        <div class="table-responsive">
                            <table  class="table table-striped" id="table_social_datas">
                                <thead>
                                    <tr>
                                        <th class="no-sort w-10">
                                            <label>
                                                <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk"/>
                                                <span class="label-text"></span>
                                            </label>
                                        </th>
                                        <th width="15%">Keyword Ref</th>
                                        <th>Content</th>
                                        <th width="10%">Feed Type</th>
                                        <th width="10%">Data Feed</th>
                                        <th width="3%">View</th>
                                        <th width="5%">Status</th>
                                        <th class="no-sort" width="5%">@langapp('action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- <tr>
                                        <td>
                                            <label>
                                                <input name="select_all" value="1" type="checkbox" class="select-chk"/>
                                                <span class="label-text"></span>
                                            </label>
                                        </td>
                                        <td>
                                            Pantip
                                        </td>
                                        <td>
                                            Fibre
                                        </td>
                                        <td>
                                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Dignissimos,
                                        </td>
                                        <td class="no-wrap">
                                            2020-12-2020 12:12
                                        </td>
                                        <td>
                                            1
                                        </td>
                                        <td>
                                            <label class="switch">
                                                <input type="hidden" value="FALSE" name="">
                                                <input type="checkbox" name="status" checked value="TRUE">
                                                <span></span>
                                            </label>
                                        </td>
                                        <td class="no-wrap text-center">
                                            <button class="btn btn-danger btn-xs">
                                                @icon('solid/trash-alt')
                                            </button>
                                        </td>
                                    </tr> --}}
                                </tbody>
                            </table>
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
                <form action="">
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="" class="col-md-3">Status</label>
                        <div class="col-md-9">
                            <select name="" id="" class="form-control">
                                <option value="1">Approved</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="submit" class="btn btn-info btn-rounded">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" id="delete_socail_data_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="left: unset">
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
                    <button type="button" class="btn btn-info submit btn-rounded delete_com_data_submit" onclick="delete_social_data_select_confirm()"><i class="fas fa-paper-plane"></i> OK</button>
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
@include('stacks.js.site_hidesettings')
@include('stacks.js.advanced_search')
@include('stacks.js.fullscreen')
<script>

    
$(function() { 
    var start = moment().startOf('hour');
    var end = moment().startOf('hour').add(32, 'hour');
    function cb(start, end) {
        $('#social_datas_date span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }
    $('#social_datas_date').daterangepicker({
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
});

$(function() {
    table_social_data(0);
});

function table_social_data(search_val){
    let search = $('#search').val();
    let keywords = $('#keyword').val();
    let source = $('#source option:selected').val();
    let startDate =  $("#social_datas_date").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
    let endDate =  $("#social_datas_date").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');
    $('#table_social_datas').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            url: '{!! route('socialdatas.socialdatas_datatables') !!}',
            data: {
                "site_code":'{{ Request::segment(3) }}',
                "search_val" : search_val,
                "search" : search,
                "keywords" : keywords,
                "source" : source,
                "start_date" : startDate,
                "end_date" : endDate,
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
                data: 'keyword',
                name: 'keyword'
            },
            {
                data: 'content',
                name: 'content'
            },
            {
                data: 'source',
                name: 'source'
            },
            {
                data: 'data_feed',
                name: 'data_feed'
            },
            {
                data: 'view_count',
                name: 'view_count'
            },
            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'action',
                name: 'action'
            },
        ],
        columnDefs: [
            {
                targets: 2,
                render: function (data, type, full, meta) {
                    var feedcontent = '';
                    if(full.get_data_leak_feed){
                        var feedcontent = full.get_data_leak_feed.feedcontent;
                    }
                    
                    var res = full.keyword.split(",");
                    let content = '';
                    for(let i in res){
                        const data = res[i];
                        content += feedcontent.replaceAll(data, '<span class="badge bg-warning">'+data+'</span>');
                    }
                    return '<div class="text-elip" data-rel="tooltip" title="'+feedcontent+'">'+content+'</div>';
                },
            },
        ]
    });
}

function change_status(code) {
    let checkState = $("#status_" + code).is(":checked") ? 1 : 0;
    axios.post('{{route('socialdatas.change_status')}}', {
        status: checkState,
        code: code,
    }).then(function (response) {
        toastr.success(response.data.message, '@langapp('response_status')');
        window.location.href = response.data.redirect;
    }).catch(function (error) {
        var errors = error.response.data.errors;
        var errorsHtml = "";
        $.each(errors, function (key, value) {
            errorsHtml += "<li>" + value[0] + "</li>";
        });
        toastr.error(errorsHtml, '@langapp('response_status')');
    });
}

    $('#table_social_datas').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {

            $('#btn_del_select').prop("disabled", false);
        } else {
            
            if ($('.select-chk').filter(':checked').length < 1){

                $('#btn_del_select').attr('disabled',true);
            }
        }
    });

    $('#table_social_datas').on('click', '.data_feed_id', function () {
        if ($(this).is(':checked')) {
            $('#btn_del_select').prop("disabled", false);
            {{--if($('.data_feed_id').filter(':checked').length >= 5){
                document.getElementById("select-all").checked = true;
            }--}}
        } else {
            document.getElementById("select-all").checked = false;
            if ($('.data_feed_id').filter(':checked').length < 1){
                
                $('#btn_del_select').attr('disabled',true);
            }
        }
    });
    function delete_social_data_select_confirm(){

        $.ajax({
            type:"POST",
            url:"{{ route('socialdatas.socialdatas_change_delete') }}",
            data:{
                id_change: del_val,

            },
            beforeSend: function(){
                $('.delete_com_data_submit').html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            },
            success:function(response) {
                $('.delete_com_data_submit').html('<i class="fas fa-check"></i> @langapp('save') </span>');
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
    }  
    var del_val = [];
    $("#btn_del_select").click(function() {
        $('#delete_socail_data_modal').modal('show');
        $('.data_feed_id:checked').each(function () {
            del_val.push(this.value);
        });

        {{--Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            heightAuto: false,
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type:"POST",
                    url:"{{ route('socialdatas.socialdatas_change_delete') }}",
                    data:{
                        id_change: del_val,
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
        })--}}
    });

</script>
@endpush
@endsection
