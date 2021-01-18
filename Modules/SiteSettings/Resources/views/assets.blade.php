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
                        <p class="h3 text-elipse-setting">Name Domain</p>
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
                    <div class="bc-head">@langapp('settings') > @langapp('assets')</div>
                    {{-- <button type="submit" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt')
                            @langapp('delete')</span>
                    </button>

                    <a id="advance-search" href="#hide-fillter" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                        <span><i class="fas fa-filter"></i> @langapp('Search_Advance')</span>
                    </a>

                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal"
                        data-target="#rss_modal">
                        @icon('solid/plus') @langapp('create')
                    </a> --}}


                    <div class="button-control pull-right">
                        
                        <div class="btn-group">
                            <button data-target="#asset_to_use" data-toggle="modal" class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="asset-to-use" disabled="disabled"> Asset To Use</button>
                        </div>

                        <div class="btn-group">
                            <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" data-toggle="dropdown">Group By
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left">
                                <li>
                                    <a href="#">
                                        Internet Name
                                    </a>
                                </li>   
                                <li>
                                    <a href="#">
                                        Affiliate - Internet Name
                                    </a>
                                </li>   
                                <li>
                                    <a href="#">
                                        Affiliate - Domain Name
                                    </a>
                                </li>   
                                <li>
                                    <a href="#">
                                        Domain Name
                                    </a>
                                </li>  
                                <li>
                                    <a href="#">
                                        IP Address
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        IPv6 Address
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        Malicious Internet Name
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        Human Name
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        Internet Name
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        Email Address
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        Domain Name (Parent)
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        Phone Number
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                </header>

                <section class="scrollable wrapper">

                    <div class="row">
                        <div class="col-lg-12">
                            <div id="hide-advance-search" class="panel panel-default container-fluid" style="padding: 2rem;display:none;">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group m-b-md">
                                            <label for="" class="">Keyword</label>
                                            <input type="text" class="form-control" name="keyword" placeholder="Search">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="" class="">Datatype Type</label>
                                            <select name="" id="datatype" class="select2-option form-control" multiple="multiple">
                                                <option value="1">All</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group m-b-md">
                                            <label for="" class="">Referent</label>
                                            <input type="text" class="form-control" name="keyword" placeholder="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group m-b-md">
                                            <label for="" class="d-block">&nbsp;</label>
                                            <button class="btn btn-info">
                                                <i class="fas fa-search"></i>
                                                <span> @langapp('apply') </span>
                                            </button>
                                            <button class="btn btn-default">
                                                <i class="fas fa-broom"></i>
                                                <span> Clear </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

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
                                        <table class="table table-striped table-bordered" id="table-scans-data-assets">
                                            <thead>
                                                <tr>
                                                    <th>Asset</th>
                                                    <th>Referent</th>
                                                    <th style="width: 20px" class="text-center">Status</th>
                                                    <th style="width: 20px" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            {{-- <tbody>
                                                <tr>
                                                    <td>
                                                        secureserver.net
                                                    </td>
                                                    <td>
                                                        <ul class="asset-list-tb">
                                                            <li>Ip-166-62-28-135.ip.secureserver.net</li>
                                                            <li>admin.sosecure.co.th</li>
                                                        </ul>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-success">Active</span>
                                                    </td>
                                                    <td class="no-wrap">
                                                        <button type="submit" class="btn btn-sm btn-info m-xs">
                                                            <span>@icon('solid/edit')
                                                        </button>
                        
                                                        <button type="submit" class="btn btn-sm btn-danger m-xs">
                                                            <span>@icon('solid/trash-alt')
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        secureserver.net
                                                    </td>
                                                    <td>
                                                        <ul class="asset-list-tb">
                                                            <li>Ip-166-62-28-135.ip.secureserver.net</li>
                                                            <li>admin.sosecure.co.th</li>
                                                        </ul>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge badge-danger">Inactive</span>
                                                    </td>
                                                    <td class="no-wrap">
                                                        <button type="submit" class="btn btn-sm btn-info m-xs">
                                                            <span>@icon('solid/edit')
                                                        </button>
                        
                                                        <button type="submit" class="btn btn-sm btn-danger m-xs">
                                                            <span>@icon('solid/trash-alt')
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody> --}}
                                        </table>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </section>
            </section>
        </aside>
    </section>
    {{-- ------------------- --}}

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>


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
        $('#table-scans-data-assets').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                contentType: "application/json",
                dataType: 'JSON',
                type: "POST",
                url: '{!! route('scans.data_scans_assets') !!}',
                data: function ( d ) {
                    d.code = '{{ $site->code }}';
                    return JSON.stringify( d );
                }
            },
            columns: [
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
            $('#show_asets_manual').html("");
            axios.get('/scans/get_data_type')
            .then(function (response) {
                loading('stop_load');
                 var html = ``;
                 let result = response.data;
                    html += `
                    <input type="hidden" id="domain_id_manual" class="form-control" value="{{ $site->domain_id }}">
                    <input type="hidden" id="site_id_manual" class="form-control" value="{{ $site->site_id }}">
                    <div class="col-md-12">
                        <table class="table table-bordered asset-table-manual-0">
                            <tbody id="assets_show_${number_tbody_rows}">
                                <tr id="rows_manual_${number_add_rows}">
                                    <td>
                                        <input type="text" name="assets_manual[]" data-raw_data_manual="${0}" class="form-control">
                                    </td>
                                    <td>
                                        <input type="text" name="raw_data_manual[]" class="form-control" data-raw_data_manual="${0}">
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
                <input type="text" name="raw_data_manual[]" class="form-control" data-raw_data_manual="${rows_data_manual}">
            </td>
            <td>
                <select name="data_type_manual[]" class="select2 form-control">`;
                for(let b in base_datatype){
                    const data_type = base_datatype[b];
                    markup += `<option value="${data_type.id}" data-raw_data_manual="${rows_data_manual}">${data_type.value}</option>`;
                }
            markup += `</select>
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
                            <input type="text" name="raw_data_manual[]" class="form-control" data-raw_data_manual="${number_rows_data_manual}">
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
    function save_assets_manual(){
        loading('load');
        var values = $("input[name='assets_manual[]']").map(function(){
            return {'raw_data' : $(this).val(), 'raw_data_base' : $(this).data('raw_data_manual'), 'domain_id' : $("#domain_id_manual").val() , 'site_id' : $("#site_id_manual").val()};
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
            $('#table-scans-data-assets').DataTable().ajax.reload();
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
