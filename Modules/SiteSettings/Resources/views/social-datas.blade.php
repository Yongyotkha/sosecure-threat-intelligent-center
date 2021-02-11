@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">      
        <aside id="hide-settings" class="aside aside-md b-r">
            <section class="vbox">
                @include('partial.header-select-site')
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_site')
                    </section>
                </section>
            </section>
        </aside>
        <section class="vbox">
            <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
                <div class="header-flex-overflow" style="height: 47px;">
                    <div class="fwb-16">
                        <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display:none;">@icon('solid/bars')</a>
                        <span style="margin-top: 2px">
                            Site Settings > Data Leak Data
                        </span>
                    </div>

                    <div class="ml-2 text-right">
                    
                        <button type="submit" id="btn_del_select" class="btn btn-sm btn-danger" value="bulk-delete" disabled>
                            <span data-rel="tooltip" title="Delete" data-placement="right">@icon('solid/trash-alt')<span class="hide-text">@langapp('delete')</span></span>
                        </button>
                        <a href="#hide-advance-search" id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} ">
                            <span data-rel="tooltip" title="Filter" data-placement="bottom"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></span>
                        </a>
                    </div>     
                </div>
            </header>
            <section class="scrollable wrapper">
                <section class="panel panel-default" id="hide-advance-search" style="display: none">
                    <header class="panel-heading font-bold panel-header-blue">
                        <div class="row">
                            <div class="col-xs-12">
                                <i class="fas fa-filter"></i> Filter
                            </div>
                        </div>
                    </header>
                    <div class="panel-body">
                        <div class="container-fluid">
                            <div class="row m-b-md">
                                <div class="col-lg-4">
                                    <h5 class="font-weight-bold">Search</h5>
                                    <input type="text" id="search" class="form-control">
                                </div>
                                <div class="col-lg-4">
                                    <h5 class="font-weight-bold">Source</h5>
                                    <select id="source" class="select2-option form-control">
                                        <option value="" selected>All</option>
                                        @if ($source)

                                        @foreach ($source as $source)
                                        <option value="{{$source->id}}">{{$source->source}}
                                        </option>
                                        @endforeach

                                        @endif
                                    </select>
                                </div>
                                <div class="col-lg-4">
                                    <h5 class="font-weight-bold">Date</h5>
                                    <div id="social_datas_date" class="text-center" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                        <i class="fa fa-calendar"></i>&nbsp;
                                        <span></span> <i class="fa fa-caret-down"></i>
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <h5 class="font-weight-bold">Type</h5>
                                    <div id="groupby-type" class="btn-group special">
                                        <button id="all" class="btn btn-grey active" value="">
                                            <span> All</span>
                                        </button>
                                        <button class="btn btn-grey" value="social">
                                            <span> Public </span>
                                        </button>
                                        <button class="btn btn-grey" value="darkweb_public">
                                            <span> Darkweb </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-lg-12 text-right">
                                <button type="button" id="btn_news_search" class="btn btn-info btn-responsive btn-fz-13" onclick="table_social_data(1)">
                                    <i class="fas fa-search"></i>
                                    @langapp('apply')
                                </button>
                                <button type="button" id="btn_news_reset" class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
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
                                <i class="fas fa-table"></i> Table Data Leak Data
                            </div>
                        </div>
                    </header>
                    <div class="panel-body">
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
                                        <th>Site</th>
                                        <th>Type</th>
                                        <th>Source</th>
                                        <th>Keyword Ref</th>
                                        <th>Content</th>
                                        <th>Data Feed</th>
                                        <th>View</th>
                                        <th>Status</th>
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
                    </div>
                </section>
            </section>
        </section>
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
@include('stacks.js.activebutton')
<script>
    var check_type = null;
    active_btn('#groupby-type .btn-grey');
    
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

    
    $("#btn_news_reset").click(function() {
                

                search_val = 0;
                $("#search").val('');
                start = moment().subtract(1, 'month').startOf('month');
                end = moment();
                cb(start, end);
                $("#source").val('').trigger("change");
                $('.btn-grey').removeClass('active');
                $('#all').addClass('active');
                check_type = null;
                table_social_data(0);
            

    });
});


$(function() {
    table_social_data(0);
});

$(".btn-grey").click(function() {
        check_type = $(this).val();
   
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
                "check_type" : check_type,
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
                name: 'site',
                className: 'nowrap',
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
                className: 'nowrap',
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
                name: 'action',    
                orderable: false,
                searchable: false,
                sortable: false,
                className:'no-wrap',
            },
        ],
        columnDefs: [
            {
                targets: 5,
                render: function (data, type, full, meta) {
                    if(full.get_data_leak_feed_one){
                        var feedcontent = full.get_data_leak_feed_one.feedcontent;
                        var res = full.keyword.split(",");
                        let content = '';
                        for(let i in res){
                            const data2 = res[i];
                            console.log(data2);
                            content += feedcontent.replaceAll(data2, '<span class="badge bg-warning">'+data2+'</span>');
                        }
                        return '<div class="text-elip" data-rel="tooltip" title="'+feedcontent+'">'+content+'</div>';
                    }else{
                        return '';
                    }
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
        del_val[];
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
