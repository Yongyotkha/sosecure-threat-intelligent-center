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
                            Site Settings > Compromise Data 
                        </span>
                    </div>

                    <div class="ml-2 text-right">
                    

                        <a href="#hide-advance-search" id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} ">
                            <span data-rel="tooltip" title="Filter" data-placement="bottom"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></span>
                        </a>
                        <a href="{{route('compromise.create') }}?site={{@$siteCode}}" class="btn btn-sm btn-{{ get_option('theme_color') }}" data-toggle="ajaxModal">
                            <span data-rel="tooltip" title="Delete" data-placement="top">@icon('solid/plus')</span>
                            <span class="hide-text">@langapp('add')</span>
                        </a>
                        <button type="button" id="btn_del_select" class="btn btn-sm btn-danger"
                            value="bulk-delete" disabled>
                            <span data-rel="tooltip" title="Delete" data-placement="bottom">@icon('solid/trash-alt')<span class="hide-text">@langapp('delete')</span></span>
                        </button>



                        

                    </div>     
                </div>
            </header>

            <section class="scrollable wrapper">
                <section class="panel panel-default" id="hide-advance-search" style="display: none">
                    <header class="panel-heading font-bold panel-header-blue">
                        <div class="row">
                            <div class="col-md-12">
                                <i class="fas fa-filter"></i> Filter
                            </div>
                    </header>
                    <div class="panel-body" style="padding: 0 !important">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-lg-6 mb-1">
                                    <h5 class="font-weight-bold">Content</h5>
                                    <input type="text" id="keyword" class="form-control">
                                </div>
                                {{-- <div class="col-lg-3 mb-1">
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
                                </div> --}}
                                <div class="col-lg-6 mb-1">
                                    <h5 class="font-weight-bold">Date</h5>
                                    <div id="social_datas_date" class="text-center" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                        <i class="fa fa-calendar"></i>&nbsp;
                                        <span></span> <i class="fa fa-caret-down"></i>
                                    </div>
                                </div>

                                <div class="col-lg-12 mb-1">
                                    <h5 class="font-weight-bold">Type</h5>
                                    <div id="groupby-type" class="btn-group special">
                                        <button id="all" class="btn btn-grey active" value="">
                                            <span> All</span>
                                        </button>
                                        <button class="btn btn-grey" value="public">
                                            <span> Public </span>
                                        </button>
                                        <button class="btn btn-grey" value="darkweb">
                                            <span> Darkweb </span>
                                        </button>
                                        <button class="btn btn-grey" value="webserver">
                                            <span> Web Server </span>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-lg-12 text-right">
                                <button type="button" id="btn_news_search" class="btn btn-info btn-responsive btn-fz-13" onclick="search()">
                                    <i class="fas fa-search"></i>
                                    @langapp('apply')
                                </button>
                                <button type="button" id="social_reset" class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
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
                                <i class="fas fa-table"></i> Table Compromise Data
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
                                                <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk" />
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

                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </section>
        </section>
    </section>

    {{-- <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
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
    </div> --}}

    <div class="modal" id="delete_com_data_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="left: unset">
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
                    <button type="button" class="btn btn-info submit btn-rounded delete_com_data_submit" onclick="delete_com_data_select_confirm()"><i class="fas fa-paper-plane"></i> OK</button>
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
@include('stacks.js.readmore')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.menusub')
@include('stacks.js.site_hidesettings')
@include('stacks.js.advanced_search')
@include('stacks.js.activebutton')

<script>

active_btn('#groupby-type .btn-grey');

    var search_val = false;
    var keywords = null;

    var source = null;
    var startDate = null;
    var endDate = null;
    var isDateSearch = null;
    var val_id = [];
    var check_type = null;
    var siteCode = @json($siteCode);


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

    $(".btn-grey").click(function() {
        check_type = $(this).val();
   
    });

    $(function() {
        table_social_data();
    });

    function search(){
        search_val = true;
        keywords = $('#keyword').val();

        source = $('#source option:selected').val();
        startDate =  $("#social_datas_date").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
        endDate =  $("#social_datas_date").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');
        
        table_social_data();
    }





    function table_social_data(){
        $('#table_social_datas').DataTable({
                pageLength: 50,
                processing: true,
                serverSide: true,
                destroy: true,
                "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
                ajax: {
                    type: "POST",
                    url: '{!! route('compromised_feed.darkweb_all_site_tb') !!}',
                    data: function ( d ) {
                        d.keywords = keywords;
                        d.source = source;
                        d.search_val = search_val;
                        d.startDate = startDate;
                        d.endDate = endDate;
                        d.isDateSearch = isDateSearch;
                        d.check_type =check_type;
                        d.site_id = {!!json_encode($siteID)!!};

                        return d;
                },
                    },
            
                initComplete : function( settings, json){
                    $('[data-rel="tooltip"]').tooltip();

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
                        width: '10px',
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
                        className : 'nowrap',
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
                        width: '10px',
                        render: function (data, type, full, meta) {
                
        
                            return full.keyword;

                        },
                            
                    
                    },
                    
                    {
                        targets: 4,
                        className:'nowrap',
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
        
                            return '<div>'+feedcontent+'</div>';
                        },
                    },
                    {
                        targets: 5,
                        className:'nowrap',
                        render: function (data, type, full, meta) {
                            let val = full.get_data_leak_feed_one;
                            if(val) {
                                val = full.get_data_leak_feed_one;
                                if(val) {
                                    val = full.get_data_leak_feed_one.source_name;
                                }
                            }
        
                            return '<div>'+val+'</div>';


                        },
                    
                    },
                    {
                        targets: 6,
                        className:'nowrap',
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
                        width: '10px',
                        className:'nowrap',
                        render: function (data, type, full, meta) {
                

                            return `
                            <a href="${base_url}/darkweb_data/view_content/${full.code}" class="btn btn-info btn-xs" data-toggle="ajaxModal"><i class="fas fa-eye"></i></a>
                            <a href="${base_url}/darkweb_data/edit_darkwebdata_modal/${full.code}?site=${siteCode}" class="btn btn-info btn-xs" data-toggle="ajaxModal">
                                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                            </a>
                            <a href="${base_url}/compromised_feed/delete_compromised_feed_modal/${full.code}" class="btn btn-danger btn-xs" data-toggle="ajaxModal"><i class="fas fa-trash-alt"></i></a>`;
                            
                        },
                    },

                ]
        
            });
    }

    function social_active(id) {
        let checkState = $("#social_active_" + id).is(":checked") ? 1 : 0;
        axios.post('{{route('compromised_feed.change_status')}}', {
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

            $("#social_reset").click(function() {
                search_val = false;
                $('#keyword').val('');

                $('#source').val('').trigger('change');
                startDate =  null;
                endDate =  null;

                start = moment().subtract(1, 'month').startOf('month');
                end = moment();
                cb(start, end);

                check_type = null;
                $('.btn-grey').removeClass('active');
                $('#all').addClass('active');
                
                table_social_data();
            });

    });


    function delete_com_data_select_confirm(){

        $.ajax({
            type:"POST",
            url:"{{ route('compromised_feed.delete_select_process') }}",
            data:{
                id: val_id,
                site_id : {!!json_encode($siteID)!!},
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

    $("#btn_del_select").click(function() {
        $('#delete_com_data_modal').modal('show');
        val_id = [];
        $('.val_id:checked').each(function () {
            val_id.push(this.value);
            
        });
    });





</script>
@endpush
@endsection
