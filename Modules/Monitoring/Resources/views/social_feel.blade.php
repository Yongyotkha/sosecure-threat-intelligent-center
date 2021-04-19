@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
            <div class="header-flex-overflow m-t-10">
                <div class="fwb-16">
                    <span>
                        Monitoring > Social Feel
                    </span>
                </div>

                <div class="ml-2 text-right">
                   

                    <a href="#hide-advance-search" id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} m-l-xs">
                        <span data-rel="tooltip" title="Filter" data-placement="bottom"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></span>
                    </a>

                    <button type="button" id="btn_del_select" class="btn btn-sm btn-danger"   value="bulk-delete"
                    disabled>
                    <span data-rel="tooltip" title="Delete" data-placement="bottom">@icon('solid/trash-alt')
                        <span class="hide-text">@langapp('delete')</span></span>
                    </button>
                 
                </div>
            </div>
        </header>


        <section id="scrollable_news" class="scrollable wrapper">
            <section class="panel panel-default" id="hide-advance-search" style="display: none">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-md-12">
                            <i class="fas fa-filter"></i> Filter
                        </div>
                </header>
                <div class="panel-body" style="padding: 0 !important">
                    <div class="container-fluid" style="padding: 2rem;">
                        <div class="row m-b-md">
                            <div class="col-lg-6">
                                <h5 class="font-weight-bold">Keyword</h5>
                                <input type="text" id="Keywords" class="form-control">
                            </div>


                            <div class="col-lg-6">
                                <h5 class="font-weight-bold">Date</h5>
                                <div id="newsrange" class="text-center"
                                    style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-lg-12 text-right">
                            <button type="button" id="btn_news_search"
                                class="btn btn-info btn-responsive btn-fz-13">
                                <i class="fas fa-search"></i>
                                @langapp('apply')
                            </button>
                            <button type="button" id="btn_news_reset"
                                class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                <i class="fas fa-broom"></i>
                                <span> Clear </span>
                            </button>
                            <button type="button" id="close_filter" class="btn btn-default btn-responsive btn-fz-13"
                                style="white-space: nowrap">
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
                            <i class="fas fa-table"></i> Table Social Feel
                        </div>
                    </div>
                </header>
                <div class="panel-body">
                    <div class="table-responsive">
                    <table class="table table-striped" id="table_social_feel">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>feedcontent</th>
                                <th>feedlink</th>
                                <th>feedtimepost</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- <tr>
                                <td>
                                    <label>
                                        <input value="" type="checkbox" />
                                        <span class="label-text"></span>
                                    </label>
                                </td>
                                <td>1</td>
                                <td>Suspicious proxy agent</td>
                                <td>
                                    <a href="">MIST FEED</a>
                                    <a href="">Phishing,UW</a>
                                </td>
                                <td>
                                    <a href="">Scan,Agent,</a>
                                    <a href="">Proxy,Spider</a>
                                </td>
                                <td>
                                    <a href="">5421</a>
                                </td>
                                <td>
                                    <i class="fas fa-check"></i>
                                </td>
                                <td>
                                    Modified
                                </td>
                                <td>
                                    2020-12-07 11:11
                                </td>
                                <td>
                                    152
                                </td>
                                <td>
                                    <a href="{{ route('indicators.events_detail') }}" class="btn btn-xs
                                    btn-info"><i class="far fa-eye"></i> View</a>
                                </td>
                            </tr> --}}
                        </tbody>
                    </table>
                    </div>
                </div>
            </section>
        </section>
    </section>
    <div class="modal" id="delete_logs" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"
        style="left: unset">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">@langapp('delete')</h4>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <p class="text-danger">@langapp('delete_warning') </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-default btn-rounded" id="close" data-dismiss="modal"><i
                            class="fas fa-times text-muted"></i> Close</a>
                    <button type="button" class="btn btn-info submit btn-rounded delete_webdefacement_submit"
                        onclick="delete_logs_click()"><i class="fas fa-paper-plane"></i> OK</button>
                </div>
            </div>
        </div>
    </div>
    {{-- <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a> --}}

</section>

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.datepicker')
@include('stacks.css.form')
@include('stacks.css.multitext')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.form')
@include('stacks.js.advanced_search')
@include('stacks.js.multitext')


<script>
    var id_select_site = 'site';
    var isDateSearch = 0;
    var isSearch = 0;
    var startDate =  '';
    var endDate = '';
    var Keywords = '';
    var select = '';
    var sitecode = '';
    var count_table = 0;
$(function () {
    
    if(get_cookie_site()){
        cookie_change_site("{{route('systemsetting.check_cookie_site')}}",id_select_site);
    }else{
        data_table();
    }
    

    var start = moment();{{--moment().startOf('hour')--}} {{--moment().subtract(1, 'year').startOf('year')--}}
    var end = moment();{{--moment().startOf('hour').add(32, 'hour')--}} {{--moment().subtract(0, 'year').endOf('year')--}}
    

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

    $('#newsrange').on('apply.daterangepicker', function(ev, picker) {
        isDateSearch = 1;
        console.log(isDateSearch);
        if (!picker.startDate.isValid() || !picker.endDate.isValid()) {
            
        }
    });

    $("#btn_news_search").click(function() {
        isSearch = 1;
        startDate=  $("#newsrange").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
        endDate=  $("#newsrange").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');
        Keywords = $("#Keywords").val();
        sitecode = $("#site").val();
        data_table();

    });


    $("#btn_news_reset").click(function() {
        $("#Keywords").val('');
        $("#select_val").val('').trigger("change");
        $("#site").val('').trigger("change");

        isSearch = 0;
        isDateSearch = 0;
        var startDate =  '';
        var endDate =  '';
        start = moment();
        end = moment();
        cb(start, end);

        startDate=  '';
        endDate=  '';
        Keywords = '';
        select = '';
        sitecode = '';
        data_table();

    });
});


function cb(start, end) {
    $('#newsrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
}

function changeSite(){
    set_cookie_site($(`#${id_select_site}`).val());
    isSearch = 1;
    sitecode = $("#site").val();
    data_table();
}

function data_table(){
        let startDate= null;
        let endDate= null;
        let f_search="";
        let event_name="";
        $('#table_social_feel').DataTable({
            searching: false,
            ordering: true,
            pageLength: 25,
            processing: true,
            serverSide: true,
            destroy: true,
            order: [[ 2, "desc" ]],
            "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
            ajax: {
                type: "POST",
                url: '{!! route('monitoring.social_feel_table')!!}',
                dataSrc: function ( json ) {

                    count_page = json.recordsTotal;
                    return json.data;
                },
                data:function(d){
                    d.count_page = 10;
                    d.startDate = startDate;
                    d.endDate = endDate;
                    d.f_search = f_search;
                    d.keywords = event_name;

                }
            },
            initComplete : function( settings, json){
                datatable = json.cursor;
                $('[data-rel="tooltip"]').tooltip();
            },

            columns: [

            {
                data: 'No',
                orderable: false,
                searchable: false,
                sortable: false,
            },

            {
                data: 'name',
            },
            {
                data: 'groups',
            },
            {
                data: 'tags',
            },
            {
                data: 'public',
            },
          

            ],


        });

    }

    var log_id_delete = null;
    function delete_logs_data(id){
        log_id_delete = id;
    }

    function delete_logs_click () {
        $.ajax({
             type:"POST",
            url:"{{ route('monitoring.delete_logs') }}",
            data:{
                id: log_id_delete,
                id_change: log_id_delete_change,
            },
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
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

    $('#table-monitoring-logs').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {

            $('#btn_del_select').prop("disabled", false);
        } else {
            
            if ($('.select-chk').filter(':checked').length < 1){

                $('#btn_del_select').attr('disabled',true);
            }
        }
    });

    $('#table-monitoring-logs').on('click', '.log_id', function () {
        if ($(this).is(':checked')) {
            $('#btn_del_select').prop("disabled", false);

            if($('.log_id').filter(':checked').length >= count_table){

                document.getElementById("select-all").checked = true;
            }
        } else {
            document.getElementById("select-all").checked = false;
            if ($('.log_id').filter(':checked').length < 1){
                
                $('#btn_del_select').attr('disabled',true);
            }
        }
    }); 

    var log_id_delete_change =[];
    $("#btn_del_select").click(function() {
        log_id_delete_change =[];
        $('#delete_logs').modal('show');
        $('.log_id:checked').each(function () {
            log_id_delete_change.push(this.value);         
        });
    });  


</script>
@endpush
@endsection