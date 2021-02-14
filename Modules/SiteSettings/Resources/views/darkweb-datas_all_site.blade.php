@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    
@php
// dd(get_role_custom());
// dd($site_admin);
// dd(get_role_custom()['superadmin']);
// dd(get_role_custom()['site_admin']);

@endphp
    <section class="hbox stretch">
        <aside id="hide-settings" class="aside aside-md b-r" style="display: none">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#setting-nav">@icon('solid/bars')</a>
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
        <section class="vbox">
            <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
                <div class="header-flex-overflow" style="height: 47px;">
                    <div class="fwb-16">
                        @if(TYPE_WEB == 'center')
                            @if(@get_role_custom()['superadmin'] == 1 || @get_role_custom()['client'] == 1)
                            <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" >@icon('solid/bars')</a>
                            @endif 
                        @endif
                        <span style="margin-top: 2px">
                            Compromise Data
                        </span>
                    </div>

                    <div class="ml-2 text-right">
                    
                        <div class="text-left max-w-select" style="margin-top: 8px;display:inline-block;">
                            <select name="site" id="site" class="text-left select2-option form-control select-site">
                                <option value="" selected="selected">All Site</option>
                                @if($SiteSettings)
                                @foreach($SiteSettings as $SiteSettings_val)
                                <option value="{{$SiteSettings_val->code}}">{{$SiteSettings_val->name}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>

                        @if(!empty(get_role_custom()))
                        {{-- // var_dump(get_role_custom()['superadmin']);
                            // var_dump(get_role_custom()['site_admin']); --}}
                            @if(TYPE_WEB == 'center')
                                @if(@get_role_custom()['superadmin'] == 1 || @get_role_custom()['client'] == 1)
                                    <a id="btn_compromise_feed" href="{{route('datafeed.darkweb_index')}}"
                                        class="btn btn-sm btn-info  m-xs">
                                        <span data-rel="tooltip" title="Compromise Feed" data-placement="bottom"><i class="fas fa-rss"></i> 
                                            <span class="hide-text">
                                                CompromiseFeed
                                            </span>
                                        </span>
                                    </a>
                                @endif
                            @endif
                        @endif

                        <a href="#hide-advance-search" id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} ">
                            <span data-rel="tooltip" title="Filter" data-placement="bottom"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></span>
                        </a>

                        <button type="button" id="btn_del_select" class="btn btn-sm btn-danger"
                            value="bulk-delete" disabled>
                            <span data-rel="tooltip" title="Delete" data-placement="bottom">@icon('solid/trash-alt')<span class="hide-text">@langapp('delete')</span></span>
                        </button>

                    
                    </div>

                    
                </div>
                {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right"
                data-rel="tooltip" title="@langapp('export') CSV">
                @icon('solid/download') CSV
                </a> --}}
            </header>
            
            <section class="scrollable wrapper">

                <div class="container-fluid" style="margin-bottom:10px;">
                    <div class="row">
                        <div class="col-md-4 nopadding">
                            <a href="">
                                <div class="card-dash-compro none-bg none-shadow">
                                    <div class="left-card">
                                        <div class="img-icon-card ice">
                                            <img src="{{asset('images/icebergline2.png')}}" alt="">
                                        </div>
                                        <h3 class="name-dash-text-compro text-dark text-upper ">Public</h3>
                                        <span class="number-card warning" id='compromise-count'>0</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 nopadding">
                            <a href="">
                                <div class="card-dash-compro none-bg none-shadow">
                                    <div class="left-card">
                                        <div class="img-icon-card ice">
                                            <img src="{{asset('images/icebergline1.png')}}" alt="">
                                        </div>
                                        <h3 class="name-dash-text-compro text-dark text-upper">Dark Web</h3>
                                        <span class="number-card info" id='darkweb-count'>0</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 nopadding">
                            <a href="">
                                <div class="card-dash-compro none-bg none-shadow">
                                    <div class="left-card">
                                        <div class="img-icon-card ice">
                                            <img src="{{asset('images/webserver.png')}}" alt="">
                                        </div>
                                        <h3 class="name-dash-text-compro text-dark text-upper ">Web Server</h3>
                                        <span class="number-card green" id='webserver-count'>{{$webserver}}</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <section class="panel panel-default" id="hide-advance-search" style="display: none">
                    <header class="panel-heading font-bold panel-header-blue">
                        <div class="row">
                            <div class="col-md-12">
                                <i class="fas fa-filter"></i> Filter
                            </div>
                    </header>
                    <div class="panel-body" style="padding: 0 !important">
                        <div class="container-fluid" style="padding: 2rem;">
                            <div class="row">

                                {{-- <div class="col-md-4 mb-1">
                                    <h5 class="font-weight-bold">Source</h5>
                                    <select id="source" class="select2-option form-control">
                                        <option value="">All</option>
                                        <option value="compromise">Public</option>
                                        <option value="darkweb">Darkweb</option>
                                        <option value="webserver">Webserver</option>
                                        <option value="server">Server</option>
                                    </select>
                                </div> --}}

                                <div class="col-lg-4 mb-1">
                                    <h5 class="font-weight-bold">Content</h5>
                                    <input type="text" id="keyword" class="form-control">
                                </div>
                                <div class="col-lg-4 mb-1">
                                    <h5 class="font-weight-bold">Date</h5>
                                    <div id="social_datas_date" class="form-control text-center"
                                        style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                        <i class="fa fa-calendar"></i>&nbsp;
                                        <span></span> <i class="fa fa-caret-down"></i>
                                    </div>
                                </div>
                                <div class="col-lg-4 mb-1">
                                    <h5 class="font-weight-bold">Type</h5>
                                    <div id="groupby-type" class="btn-group special">
                                        <button id="all" class="btn btn-grey active" value="">
                                            <span> All</span>
                                        </button>
                                        <button class="btn btn-grey" value="public">
                                            <span> Public </span>
                                        </button>
                                        <button class="btn btn-grey" value="darkweb">
                                            <span> Dark Web </span>
                                        </button>
                                        <button class="btn btn-grey" value="webserver">
                                            <span> Web Server </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                {{-- <div class="col-lg-4">
                                    <div class="row d-flex align-items-center">
                                        <label for="" class="col-sm-3 col-xs-12 col-form-label">Site</label>
                                        <div class="col-sm-9 col-xs-12">
                                            <select id="site" class="select2-option form-control">
                                                <option value="" selected>All</option>
                                                @if ($site)

                                                @foreach ($site as $data)
                                                <option value="{{$data->id}}">{{$data->name}}
                                        </option>
                                        @endforeach

                                        @endif
                                        </select>
                                    </div>
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-lg-12 text-right">
                                <button type="button" id="btn_news_search" class="btn btn-info btn-responsive btn-fz-13"
                                    onclick="search()">
                                    <i class="fas fa-search"></i>
                                    @langapp('apply')
                                </button>
                                <button type="button" id="social_reset2" class="btn btn-default btn-responsive btn-fz-13"
                                    style="white-space: nowrap">
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
                                <i class="fas fa-table"></i> Table Compromise Data
                            </div>
                        </div>
                    </header>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="table_social_datas">
                                <thead>
                                    <tr>
                                        <th class="no-sort w-10">
                                            <label>
                                                <input name="select_all" value="1" id="select-all" type="checkbox"
                                                    class="select-chk" />
                                                <span class="label-text"></span>
                                            </label>
                                        </th>
                                        <th>Site</th>
                                        <th>Type</th>

                                        <th>Keyword Ref</th>
                                        <th>Content</th>
                                        <th>Remark</th>
                                        <th>Data Feed</th>
                                        <th>View</th>
                                        <th>Status</th>
                                        <th>@langapp('action')</th>
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

    {{-- <div class="modal in fixed-left" id="delete_all" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <span class="modal-title" id="exampleModalLabel">Delete</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="">
                <div class="modal-body">
                    <p class="text-danger">@langapp('delete_warning')  </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="submit" class="delete-all btn btn-danger btn-rounded">
                        <i class="fas fa-paper-plane"></i>
                        Delete
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div> --}}

    <div class="modal" id="delete_all" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"
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
                    <a href="#" class="btn btn-default btn-rounded" data-dismiss="modal"><i
                            class="fas fa-times text-muted"></i> Close</a>
                    <button type="button" class="btn btn-info submit btn-rounded delete-all">
                        <i class="fas fa-paper-plane"></i> OK</button>
                </div>
            </div>
        </div>
    </div>

</section>
@if(@get_role_custom()['superadmin'] == 1 || @get_role_custom()['site_admin'] == 1)
@php $admin = 1; @endphp
@else
@php $admin = 0; @endphp
@endif

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.datepicker')
@include('stacks.css.form')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.menusub')
@include('stacks.js.hidesettings')
@include('stacks.js.advanced_search')
@include('stacks.js.activebutton')

<script>

    active_btn('#groupby-type .btn-grey');
    var id_select_site = 'site';
    var admin = '{{$admin}}';
        var visible_c = '';

        if(admin == 1) {
            visible_c = true;
        } else {
            visible_c = false;
        }


    var search_val = false;
    var keywords = null;
    var site = null;
    var source = null;
    var startDate = null;
    var endDate = null;
    var isDateSearch = null;
    var val_id = [];
    var f_search = 0;
    check_type = null;

    $('#table_social_datas').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {

            $('#btn_del_select').prop("disabled", false);
        } else {  
            if ($('.select-chk').filter(':checked').length < 1){

                $('#btn_del_select').attr('disabled',true);
            }
        }
    });

    $('#table_social_datas').on('click', '.val_id', function () {
        if ($(this).is(':checked')) {

            
            $('#btn_del_select').prop("disabled", false);
        } else {
            if ($('.val_id').filter(':checked').length < 1){
                
                $('#btn_del_select').attr('disabled',true);
            }
        }
    });



    $(function() {
        if(get_cookie_site()){
            cookie_change_site("{{route('systemsetting.check_cookie_site')}}",id_select_site);
        }else{
            table_social_data();
            get_count();
        }
        
    });

    $(".btn-grey").click(function() {
        check_type = $(this).val();
   
    });

    $("#site").change(function() {
        set_cookie_site($(`#${id_select_site}`).val());
        site = this.value;        
        table_social_data();
        get_count();
        });

    function search(){
        search_val = true;
        keywords = $('#keyword').val();
        {{--site = $('#site option:selected').val();--}}
        source = $('#source option:selected').val();
        startDate =  $("#social_datas_date").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
        endDate =  $("#social_datas_date").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');

        table_social_data();
        get_count();
    }

    function get_count() {

        if(search_val == true) {
            f_search = 1;
        } else {
            f_search = 0;
        }


        $.ajax({
            type:"POST",
            url: '{!! route('darkweb.count_val') !!}',
            data: ({
                keywords : keywords,
                site_id : site,
                social : source,
                search_val : search_val,
                startDate : startDate,
                endDate : endDate,
                isDateSearch : isDateSearch,
                f_search : f_search,
                check_type : check_type,
            }),
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
                loading('stop_load');
                $('#darkweb-count').text(response.darkweb);
                $('#compromise-count').text(response.compromise);
                $('#webserver-count').text(response.webserver);
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


    
    function table_social_data(){

        $('#table_social_datas').DataTable({
                pageLength: 50,
                processing: true,
                serverSide: true,
                destroy: true,
                autoWidth:false,
                "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10">>>rt<"bottom"ip><"clear">',
                ajax: {
                    type: "POST",
                    url: '{!! route('socialdatas.darkweb_all_site_tb') !!}',
                    data: function ( d ) {
                        d.keywords = keywords;
                        d.site = site;
                        d.source = source;
                        d.search_val = search_val;
                        d.startDate = startDate;
                        d.endDate = endDate;
                        d.isDateSearch = isDateSearch;
                        d.check_type = check_type;

                        return d;
                    },
                },
            
                initComplete : function( settings, json){
                    $('[data-toggle="tooltip"]').tooltip();

                    {{--console.log(json);--}}
                    
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
                            return '<label><input type="checkbox" name="val_id" class="val_id"  value="' + full.id + '"><span class="label-text"></span></label>';
                        },
                    },
                    {
                        targets: 1,
                        className : 'nowrap',
                        render: function (data, type, full, meta) {
                            let val = full;
                            if(val) {
                                val = full.get_site;
                                if(val) {
                                    val = full.get_site.name;
                                }
                            }
                
        
                            {{--return val+' '+full.id;--}}
                            return val;

                        },
                    },
                    {
                        targets: 2,
                        width: '60px',
                        className:'nowrap',
                        render: function (data, type, full, meta) {
                            let val = full.feel_type;
                            if(val) {
                                val = get_word_leak_compromise(full.feel_type,'compromise');
                            }
        
                            return val;

                        },
                    
                    },
                    {
                        targets: 3,
                        width: '50px',
                        render: function (data, type, full, meta) {
                
        
                            return full.keyword;

                        },
                            
                    
                    },
                    
                    {
                        targets: 4,
                        width: '400px',
                        render: function (data, type, full, meta) {
                                                    
                            let val = '';
                            let content = '';
                            val = full.get_data_leak_feed_one;
                            if(val) {
                                var feedcontent = full.get_data_leak_feed_one.feedcontent;
                                var res = full.keyword.split(",");
                                for(let i in res){
                                    var data = res[i];
                                    content += feedcontent.replaceAll(data, '<span class="badge bg-warning">'+data+'</span>');
                                }
                                
                            }
                            return '<div class="scroll-ovf-content-fixh-60">'+content+'</div>';
                        },
                    },

                    {
                        targets: 5,
                        width: '400px',
                        className : 'nowrap',
                        render: function (data, type, full, meta) {
                            let val = full.get_data_leak_feed_one;
                            if(val) {
                                val = full.get_data_leak_feed_one;
                                if(val) {
                                    val = full.get_data_leak_feed_one.source_name;
                                }
                            }
        
                            return '<div class="scroll-ovf-content-fixh-60">'+val+'</div>';

                        },
                    
                    },

                    {
                        targets: 6,
                        width: '80px',
                        className : 'nowrap',
                        render: function (data, type, full, meta) {
                            let val = '';
                            val = full.get_data_leak_feed_one;
                            if(val) {
                                    val = full.get_data_leak_feed_one.feedtimepost;
                                
                            }
                            return val;
                        },
                    },
                    {
                        targets: 7,
                        width: '10px',
                        render: function (data, type, full, meta) {
                
        
                            return full.view;

                        },
                    },
                    {
                        visible: visible_c,
                        targets: 8,
                        width: '10px',
                        render: function (data, type, full, meta) {

                            var checked_val = null;
                                if (full.status == 1) {
                                    checked_val = 'checked';
                                } else {
                                    checked_val = '';
                                }
                        
                            return  '<label class="switch"><input type="checkbox" id="social_active_' +full.id+  '" onchange="social_active('+full.id+')" '+checked_val+' name="active" value="1"><span class="slider round"></span></label>';

                        }

                    },
                    {
                        targets: 9,
                        className: 'nowrap',
                        width: '10px',
                        render: function (data, type, full, meta) {
                

                            return `
                            <a href="${base_url}/darkweb_data/view_content/${full.code}" class="btn btn-info btn-xs" data-toggle="ajaxModal"><i class="fas fa-eye"></i></a>
                            <a href="${base_url}/darkweb_data/delete_darkwebdata_modal/${full.code}" class="btn btn-danger btn-xs" data-toggle="ajaxModal"><i class="fas fa-trash-alt"></i></a>`;
                            
                        },
                    },

                ]
        
            });
    }

    function social_active(id) {
        let checkState = $("#social_active_" + id).is(":checked") ? 1 : 0;
        axios.post('{{route('DataLeakController.darkweb_data_change_status')}}', {
            active: checkState,
            id: id,
            redirect: '',
        }).then(function (response) {
            {{--console.log(response.data.redirect);--}}
            toastr.success(response.data.message, '@langapp('response_status')');
            {{--table.ajax.reload();--}}
            table_social_data();
            {{--window.location.href = response.data.redirect;--}}
        }).catch(function (error) {
            var errors = error.response.data.errors;
            var errorsHtml = "";
            $.each(errors, function (key, value) {
                errorsHtml += "<li>" + value[0] + "</li>";
            });
            toastr.error(errorsHtml, '@langapp('response_status')');
        });
    }

    $(function() {
    
        var start = moment().subtract(1, 'month').startOf('month');{{--moment().startOf('hour')--}} {{--moment().subtract(1, 'year').startOf('year')--}}
        var end = moment();{{--moment().startOf('hour').add(32, 'hour')--}} {{--moment().subtract(0, 'year').endOf('year')--}}

        function cb(start, end) {
            $('#social_datas_date span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    
        }

        $('#social_datas_date').daterangepicker({
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
        $('#social_datas_date').on('apply.daterangepicker', function(ev, picker) {
            isDateSearch = 1;
            if (!picker.startDate.isValid() || !picker.endDate.isValid()) {
                
        }
    });

    cb(start, end);

            $("#social_reset2").click(function() {
                search_val = false;

                $('#keyword').val('');

                $('#site').val('').trigger('change');
                $('#source').val('').trigger('change');
                startDate =  null;
                endDate =  null;
                keywords =  null;
                site =  null;

                start = moment().subtract(1, 'month').startOf('month');
                end = moment();
                cb(start, end);
                $('.btn-grey').removeClass('active');
                $('#all').addClass('active');
                check_type = null;
                get_count();
                table_social_data();
            });

    });

    $("#btn_del_select").click(function() {
        val_id = [];
        $('.val_id:checked').each(function () {
            val_id.push(this.value);
            
        });

        $('#delete_all').modal('show');
        $('.delete-all').click(function(){
                $.ajax({
                    type:"POST",
                    url:"{{ route('darkweb.delete_select_process') }}",
                    data:{id: val_id},
                    beforeSend: function(){
                        loading('load');
                    },
                    success:function(response) {
                        loading('stop_load');
                        toastr.success(response.message, '@langapp('response_status')');
                        window.location.href = response.redirect;
                        $('#delete_all').modal('hide');
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
        });
    });


</script>
@endpush
@endsection