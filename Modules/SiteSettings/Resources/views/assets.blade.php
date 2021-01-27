@extends('layouts.app')
@section('content')
<style>
    .w-100{
        width: 100px;
    }

</style>
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
                    {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                    @icon('solid/arrow-left')
                    </a> --}}
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display:none">@icon('solid/bars')</a>
                    <div class="bc-head">Site Setting &gt; @langapp('assets')</div>

                    <button type="submit" id="btn_del_select" class="btn btn-sm btn-danger m-xs  pull-right" value="bulk-delete" disabled>
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt') @langapp('delete')</span>
                    </button>

                    <a id="advance-search" href="#hide-fillter" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                        <span><i class="fas fa-filter"></i> @langapp('Search_Advance')</span>
                    </a>

                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal"
                        data-target="#asset_to_use_manual" id="asset-to-use-manual">
                        @icon('solid/plus') @langapp('create')
                    </a>
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
                                    <div class="col-md-4 mb-1">
                                        <h5 class="font-weight-bold">Keyword</h5>
                                        <input type="text" class="form-control" name="keyword" placeholder="Search">
                                    </div>
                                    <div class="col-md-4 mb-1">
                                        <h5 class="font-weight-bold">Data Type</h5>
                                        <select name="" id="datatype" class="form-control" multiple="multiple">
                                            <option value="1">All</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-1">
                                        <h5 class="font-weight-bold">Referent</h5>
                                        <input type="text" class="form-control" name="keyword">
                                    </div>
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
                                    <button type="button" id="btn_news_reset" class="btn btn-default btn-responsive btn-fz-13"
                                        style="white-space: nowrap" onclick="clear_search()">
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
                                    <i class="fas fa-table"></i> Table Assets
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="table-assets-data">
                                    <thead>
                                        <tr>
                                            <th class="no-sort" style="width: 12px">
                                                <label>
                                                    <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk"/>
                                                    <span class="label-text"></span>
                                                </label>
                                            </th>                                      
                                            <th>Asset</th>
                                            <th>Referent</th>
                                            <th style="width: 20px" class="text-center">Status</th>
                                            <th style="width: 20px" class="text-center">Action</th>
                                        </tr>
                                    </thead>
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
    <div class="modal in fixed-left" id="asset_to_use_manual" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside size-half-50" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        Asset To Use
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12">
                            <h3 class="text-dark">Domain</h3>
                            <div id="select_domain"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-3 text-center">
                            <h3 class="text-dark">Assets</h3>
                        </div>
                        <div class="col-xs-9 text-center">
                            <h3 class="text-dark">Referent</h3>
                        </div>
                        <div class="col-md-12">
                            <hr>
                        </div>
                    </div>
                    <div id="show_asets_manual" class="row">
                    </div>
                    <button type="button" class="btn btn-sm btn-info m-xs" onclick="add_new_assets_manual()">
                        <span>@icon('solid/plus')  Add Assets
                    </button>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="button" class="btn btn-info btn-rounded" onclick="save_assets_manual()">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
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
@include('stacks.js.form')
@include('stacks.js.datatables')
@include('stacks.js.fullscreen')
@include('partial.ajaxify')
@include('stacks.js.menusub')
@include('stacks.js.site_hidesettings')
@include('stacks.js.advanced_search')


<script>
    $(function () {
        $('#table-assets-data').DataTable({
            "dom": '<B><"d-flex d-inline-flex justify-content-between"lf>rt<"bottom"ip><"clear">',
        });
    });

    $('#table-assets-data').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {

            $('#btn_del_select').prop("disabled", false);
        } else {
            
            if ($('.select-chk').filter(':checked').length < 1){

                $('#btn_del_select').attr('disabled',true);
            }
        }
    });

    $("#btn_del_select").click(function() {
        let del_val = [];
        $('.asset_id:checked').each(function () {
            del_val.push(this.value);
        });

        Swal.fire({
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
                    url:"{{ route('scans.delete_assets') }}",
                    data:{
                        asset_id: del_val,
                        page: 'site'
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
        })
    });

    $(document).ready(function () {
        $('#datatype').select2();
        $('#source').select2();

    });

    $(function () {
        $('#table-assets-data').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                contentType: "application/json",
                dataType: 'JSON',
                type: "POST",
                url: '{!! route('scans.data_scans_assets') !!}',
                data: function ( d ) {
                    d.code = '{{ $siteSettings->code }}';
                    d.menu = 'site';
                    return JSON.stringify( d );
                }
            },
            columns: [
                {
                    data: 'chk',
                    name: 'chk',
                },
                {
                    data: 'assets',
                    name: 'assets',
                },
                {
                    data: 'referent',
                    name: 'referent',
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
            ],
        });
    });

    var number_rows = 0; 
    var number_add_rows = 0;
    var number_tbody_rows = 0;
    var number_table_rows = 1;
    var number_new_rows_assets = 0;
    var base_datatype = []; 
    $(document).ready(function () {
        $('#datatype').select2();
        $('#source').select2();

        $('.select2').select2();

        $('.hide-fillter').hide();
        $('#fillter-advance').click(function(){
            $('.hide-fillter').toggle();
        });

        $("#asset-to-use-manual").click(function(){
            loading('load');
            $('#select_domain').html();
            $('#show_asets_manual').html("");
            axios.post('/sitesettings/assets/get_domain', {
                site_id: '{{ $siteSettings->id }}',
            }).then(function (response) {
               let html = ``;
               html += `<select id="domain_id_manual" class="select2 form-control">`;
                for(let b in response.data.data){
                    const domain = response.data.data[b];
                    html += `<option value="${domain.id}">${domain.name}</option>`;
                }
                html += `</select>`;
                $('#select_domain').html(html);
            }).catch(function (error) {
                loading('stop_load');
                var errors = error;
                var errorsHtml = "";
                errorsHtml += "<li>" + errors + "</li>";
                toastr.error(errorsHtml, '@langapp('response_status')');
            });
            axios.get('/scans/get_data_type')
            .then(function (response) {
                loading('stop_load');
                 var html = ``;
                 let result = response.data;
                    html += `
                    <input type="hidden" id="site_id_manual" class="form-control" value="{{ $siteSettings->id }}">
                    <div class="col-md-12">
                        <table class="table table-bordered asset-table-manual-0">
                            <tbody id="assets_show_${number_tbody_rows}">
                                <tr id="rows_manual_${number_add_rows}">
                                    <td>
                                        <input type="text" name="assets_manual[]" data-raw_data_manual="${0}" class="form-control">
                                    </td>

                                    <td>
                                        <select name="data_type_manual[]" class="select2 form-control">`;
                                        base_datatype = result.data_type;
                                        for(let b in result.data_type){
                                            const data_type = result.data_type[b];
                                            html += `<option value="${data_type.id}" data-raw_data_manual="${0}">${data_type.value}</option>`;
                                        }
                                        html += `</select>
                                    </td>
                                    <td>
                                        <input type="text" name="raw_data_manual[]" class="form-control" data-raw_data_manual="${0}">
                                    </td>
                                    <td></td>
                                </tr>`;
                        html += `</tbody>
                        </table>
                        <div class="text-center">
                            <button type="button" class="btn btn-sm btn-info m-xs add-row" value="Add Row" onclick="add_assets_manual(0,${number_tbody_rows},0)">
                                <span>@icon('solid/plus')  Add
                            </button>
                        </div>
                    </div>
                    <div id="new_assets_show_${number_new_rows_assets}"></div>`;   
                $('#show_asets_manual').html(html);
            }).catch(function (error) {
                loading('stop_load');
                var errors = error;
                var errorsHtml = "";
                errorsHtml += "<li>" + errors + "</li>";
                toastr.error(errorsHtml, '@langapp('response_status')');
            });
        });
    });

    function add_assets_manual(table_row, tbody_rows, rows_data_manual){
        number_add_rows++;
        var markup = ``;
        markup = `
        <tr id="rows_manual_${number_add_rows}">
            <td></td>

            <td>
                <select name="data_type_manual[]" class="select2 form-control">`;
                for(let b in base_datatype){
                    const data_type = base_datatype[b];
                    markup += `<option value="${data_type.id}" data-raw_data_manual="${rows_data_manual}">${data_type.value}</option>`;
                }
            markup += `</select>
            </td>
            <td>
                <input type="text" name="raw_data_manual[]" class="form-control" data-raw_data_manual="${rows_data_manual}">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger m-xs delete-row" onclick="delete_assets_manual(${number_add_rows})">
                    <span>@icon('solid/trash-alt')
                </button>
            </td>
        </tr>
        `;
        $("table.asset-table-manual-"+table_row+" tbody#assets_show_" + tbody_rows).append(markup);
    }

    var number_rows_data_manual = 0;
    function add_new_assets_manual(){
        number_rows_data_manual++;
        number_add_rows++;
        number_table_rows++;
        number_tbody_rows++;
        var html = ``;
        html += `<div class="col-md-12" id="new_add_assets_${number_new_rows_assets}">
            <table class="table table-bordered asset-table-manual-${number_table_rows}">
                <tbody id="assets_show_${number_tbody_rows}">
                    <tr id="rows_manual_${number_add_rows}">
                        <td>
                            <input type="text" name="assets_manual[]" class="form-control" data-raw_data_manual="${number_rows_data_manual}">
                        </td>

                        <td>
                            <select name="data_type_manual[]" class="select2 form-control">`;
                            for(let b in base_datatype){
                                const data_type = base_datatype[b];
                                html += `<option value="${data_type.id}" data-raw_data_manual="${number_rows_data_manual}">${data_type.value}</option>`;
                            }
                            html += `</select>
                        </td>
                        <td>
                            <input type="text" name="raw_data_manual[]" class="form-control" data-raw_data_manual="${number_rows_data_manual}">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger m-xs delete-row" value="bulk-delete" onclick="delete_assets_manual_main(${number_new_rows_assets})">
                                <span>@icon('solid/trash-alt')
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="text-center">
                <button type="button" class="btn btn-sm btn-info m-xs add-row" value="Add Row" onclick="add_assets_manual(${number_table_rows},${number_tbody_rows},${number_rows_data_manual})">
                    <span>@icon('solid/plus')  Add
                </button>
            </div>
        </div>
        <div id="new_assets_show_${number_new_rows_assets+1}"></div>`;
        $("#new_assets_show_" + number_new_rows_assets).append(html);
        number_new_rows_assets++;
    }
    function delete_assets_manual_main(c){
        $('#new_add_assets_' + c).remove();
        number_new_rows_assets--;
        number_rows_data_manual--;
    }
    function save_assets_manual(){
        loading('load');
        var values = $("input[name='assets_manual[]']").map(function(){
            return {'raw_data' : $(this).val(), 'raw_data_base' : $(this).data('raw_data_manual'), 'domain_id' : $("#domain_id_manual :selected").val() , 'site_id' : $("#site_id_manual").val()};
        }).get();
        var raw_data = $("input[name='raw_data_manual[]']").map(function(){
            return {'raw_data' : $(this).val(), 'raw_data_base' : $(this).data('raw_data_manual')};
        }).get();
        var data_type = $("select[name='data_type_manual[]'] option:selected").map(function(){
            return {'data_type' : $(this).val(), 'raw_data_base' : $(this).data('raw_data_manual')};
        }).get();   
        var res = raw_data.map(function(v, i) {
            if(data_type[i].raw_data_base == v.raw_data_base){
                return {
                    data_type: data_type[i].data_type,
                    raw_data: v.raw_data,
                    raw_data_base: v.raw_data_base
                };
            }
        }); 
        axios.post('/scans/save_assets_new', {
            assets: values,
            assets_data: res,
        }).then(function (response) {
            loading('stop_load');
            $('#table-assets-data').DataTable().ajax.reload();
            $('#asset-to-use').prop("disabled", true);
            $('#show_asets_manual').html("");
            $('#asset_to_use_manual').modal('hide');
        }).catch(function (error) {
            loading('stop_load');
            var errors = error;
            var errorsHtml = "";
            errorsHtml += "<li>" + errors + "</li>";
            toastr.error(errorsHtml, '@langapp('response_status')');
        });
    }

    function delete_assets_manual(c){
         $('#rows_manual_' + c).remove();
    }
</script>
@endpush
@endsection
