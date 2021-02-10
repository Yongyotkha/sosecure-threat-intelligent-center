<header class="header b-b clearfix">
    <section class="panel panel-default hide-fillter" id="advance-search" style="display: none">
        <header class="panel-heading font-bold panel-header-blue">
            <div class="row">
                <div class="col-md-12">
                    <i class="fas fa-filter"></i> Filter
                </div>
        </header>
        <div class="panel-body">
            <div style="margin-bottom: 1rem">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group m-b-md">
                            <h5 class="font-weight-bold">Keyword</h5>
                            <input type="text" class="form-control" name="keyword" placeholder="Search">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <h5 class="font-weight-bold">Datatype Type</h5>
                            <select name="" id="datatype" class="select2-option form-control" multiple="multiple">
                                <option value="1">All</option>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group m-b-md">
                            <h5 class="font-weight-bold">Referent</h5>
                            <input type="text" class="form-control" name="keyword" placeholder="">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5 class="font-weight-bold">Filter By</h5>
                        <div id="groupby-btn" class="btn-group special mb-2">
                            <button class="btn btn-grey active">
                                <span> Internet Name </span>
                            </button>
                            <button class="btn btn-grey">
                                <span> Domain Name </span>
                            </button>
                            <button class="btn btn-grey">
                                <span> IP Address </span>
                            </button>
                            <button class="btn btn-grey">
                                <span> IPv6 Address </span>
                            </button>
                        </div>
                    </div>


                    {{-- <div class="col-md-4">
                        <div class="form-group">
                            <label for="" class="">module</label>
                            <select name="" id="module" class="select2-option form-control" multiple="multiple">
                                <option value="1">All</option>
                            </select>
                        </div>
                    </div> --}}
                
                    {{-- <div class="col-md-4">
                        <div class="form-group">
                        <label for="" class="">Source</label>
                        <select name="" id="source" class="select2-option form-control">
                            <option value="all">All</option>
                        </select>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button class="btn btn-info btn-fz-13">
                        <i class="fas fa-search"></i>
                        <span> @langapp('apply') </span>
                    </button>
                    <button class="btn btn-default btn-fz-13">
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
                    <i class="fas fa-table"></i> Table Data
                </div>
            </div>
        </header>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="table-scans-data">
                    <thead>
                        <tr>
                            <th class="no-sort">
                                <label>
                                    <input name="select_all" value="1" id="select-all" type="checkbox" />
                                    <span class="label-text"></span>
                                </label>
                            </th>
                            <th>Data Type</th>
                            <th>Asset</th>
                            <th>Referent</th>
                            <th class="text-center">Last Update</th>
                            <th class="text-center">Status</th>
                            {{-- <th>Module</th> --}}
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
</header>

<div class="modal in fixed-left" id="modal_test_scan" style="z-index: 999999" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="false">
    <div class="modal-dialog modal-dialog-aside size-half-50" role="document">
        <div class="modal-content">
            <div class="modal-header bg-blue"><button type="button" class="close text-white"  data-dismiss="modal">×</button>
                <h4 class="modal-title text-white">
                    Test Scan
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <td>List1</td>
                            </tr>
                            <tr>
                                <td>List2</td>
                            </tr>
                            <tr>
                                <td>List3</td>
                            </tr>
                        </table>
                        <div class="form-group">
                            <label for="">Result</label>
                            <textarea name="" id="" cols="30" rows="10" class="form-control" readonly></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-rounded">
                    <i class="fas fa-play"></i> Retry Test </button>
                <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                    <i class="fas fa-times"></i> Close </button>
                <button type="button" class="btn btn-info btn-rounded">
                    <i class="fas fa-paper-plane"></i>Close and Save
                </button>
            </div>
        </div>
    </div>
</div>


@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.activebutton')

<script>

    active_btn('#groupby-btn .btn-grey');

     $(function () {
        $('#asset-to-use').prop("disabled", true);
        $('#table-scans-data').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                contentType: "application/json",
                dataType: 'JSON',
                type: "POST",
                url: '{!! route('scans.data_scans') !!}',
                data: function ( d ) {
                    d.code = '{{ $site->code }}';
                    return JSON.stringify( d );
                }
            },
            
            columns: [
                {
                    data: 'chk',
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    className: 'w-10'
                },
                {
                    data: 'data_type',
                    name: 'data_type'
                },
                {
                    data: 'raw_data',
                    name: 'raw_data'
                },
                {
                    data: 'referent',
                    name: 'referent',
                },
                {
                    data: 'updated_at',
                    name: 'updated_at',
                    className: 'text-center'
                },
                {
                    data: 'use',
                    name: 'use',
                },        
            ],
        });
        $('#table-scans-data').on('click', '.select-chk', function () {
            if ($(this).is(':checked')) {
                $('#asset-to-use').prop("disabled", false);
            } else {
                if ($('.select-chk').filter(':checked').length < 1){
                    $('#asset-to-use').attr('disabled',true);
                }
            }
        });

        $('#table-scans-data').on('click', '#select-all', function () {
            if ($(this).is(':checked')) {
                $('#asset-to-use').prop("disabled", false);
            } else {
                if ($('#select-all').filter(':checked').length < 1){
                    $('#asset-to-use').attr('disabled',true);
                }
            }
        });
        
    });
    var number_rows = 0; 
    var number_add_rows = 0;
    var number_tbody_rows = 0;
    var number_table_rows = 1;
    var number_new_rows_assets = 0;
    var base_datatype = []; 
    $(document).ready(function(){
{{--$("#asset-to-use-manual").click(function(){
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
        });--}}

        $("#asset-to-use").click(function(){
            $('#show_asets').html("");
            loading('load');
            var values = $("input[name='select[]']:checked").map(function(){
                return {'raw_data' : $(this).val(), 'domain_id' : $(this).data('domain') , 'site_id' : $(this).data('site')};
            }).get();
            axios.post('/scans/get_referent', {
                values: values,
            }).then(function (response) {
                loading('stop_load');
                let result = response.data;
                var html = ``;
                let checkAssetDplicate = [];
                for(let i in result.data){
                    const data_referent = result.data[i];
                    if (checkAssetDplicate.indexOf(data_referent.raw_data) == -1) {
                        checkAssetDplicate.push(data_referent.raw_data);
                        const raw_data = data_referent.raw_data;
                        const data_transaction = data_referent.data;
                        html += `<div class="col-md-3">
                            <h4 class="text-dark">${raw_data}</h4>
                        </div>
                        <div class="col-md-9">
                            <table class="table table-bordered asset-table-${i}">
                                <tbody>`;
                                    let count = 0;
                                    let checkSubAssetDplicate = [];
                                    for(let c in data_transaction){
                                        const data_transaction_val = data_transaction[c];
                                        if (checkSubAssetDplicate.indexOf(`${data_transaction_val.data_type}${data_transaction_val.raw_data}`) == -1) {
                                            checkSubAssetDplicate.push(`${data_transaction_val.data_type}${data_transaction_val.raw_data}`);
                                            number_rows++;
                                            count++;
                                            if(count == 1){
                                                html += `<input type="hidden" name="assets[]" class="form-control" value="${raw_data}" data-domain_id="${data_transaction_val.domain_id}" data-site_id="${data_transaction_val.site_id}">`;
                                            }
                                            html += `<tr id="rows_${number_rows}">
                                                <td>
                                                    <select name="data_type[]" class="select2 form-control">`;
                                                    for(let b in result.data_type){
                                                        base_datatype = result.data_type;
                                                        const data_type = result.data_type[b];
                                                        html += `<option value="${data_type.id}" ${data_type.value == data_transaction_val.data_type ? 'selected' : ''} data-raw_data="${raw_data}">${data_type.value}</option>`;
                                                    }
                                                    html += `</select>
                                                </td>
                                                <td>
                                                    <input type="text" name="raw_data[]" class="form-control" value="${data_transaction_val.raw_data}" data-raw_data="${raw_data}">
                                                </td>
                                                <td>
                                                    <!--<button type="button" class="btn btn-sm btn-success m-xs delete-row" onclick="retry_test();">
                                                        <span>@icon('solid/play')
                                                    </button>-->
                                                    <button type="button" class="btn btn-sm btn-danger m-xs delete-row" value="bulk-delete" onclick="delete_tr(${number_rows})">
                                                        <span>@icon('solid/trash-alt')
                                                    </button>
                                                </td>
                                            </tr>`;
                                        }
                                    }
                                html += `</tbody>
                            </table>
                            <div class="text-center">
                                    <button type="button" class="btn btn-sm btn-info m-xs add-row" value="Add Row" onclick="add_assets('${raw_data}','${i}')">
                                        <span>@icon('solid/plus')  Add
                                    </button>
                                </div>
                            </div>`;   

                    }
                }
                $('#show_asets').html(html);
            }).catch(function (error) {
                loading('stop_load');
                var errors = error;
                var errorsHtml = "";
                errorsHtml += "<li>" + errors + "</li>";
                toastr.error(errorsHtml, '@langapp('response_status')');
            });
        });
    });  

    function retry_test(){
        $('#modal_test_scan').modal('show');
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
                            <button type="button" class="btn btn-sm btn-success m-xs delete-row" onclick="retry_test();">
                                <span>@icon('solid/play')
                            </button>
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
                <button type="button" class="btn btn-sm btn-success m-xs delete-row" onclick="retry_test();">
                    <span>@icon('solid/play')
                </button>
                <button type="button" class="btn btn-sm btn-danger m-xs delete-row" onclick="delete_assets_manual(${number_add_rows})">
                    <span>@icon('solid/trash-alt')
                </button>
            </td>
        </tr>
        `;
        $("table.asset-table-manual-"+table_row+" tbody#assets_show_" + tbody_rows).append(markup);
    }

    function add_assets(raw_data,i){
        number_rows++;
        var markup = ``;
        markup = `
        <tr id="rows_${number_rows}">

            <td>
                <select name="data_type[]" class="select2 form-control">`;
                for(let b in base_datatype){
                    const data_type = base_datatype[b];
                    markup += `<option value="${data_type.id}" data-raw_data="${raw_data}">${data_type.value}</option>`;
                }
            markup += `</select>
            </td>
            <td>
                <input type="text" name="raw_data[]" class="form-control" data-raw_data="${raw_data}">
            </td>
            <td>
                <!--<button type="button" class="btn btn-sm btn-success m-xs delete-row" onclick="retry_test();">
                    <span>@icon('solid/play')
                </button>-->
                <button type="button" class="btn btn-sm btn-danger m-xs delete-row" onclick="delete_tr(${number_rows})">
                    <span>@icon('solid/trash-alt')
                </button>
            </td>
        </tr>
        `;
        $("table.asset-table-"+i+" tbody").append(markup);
    }

    function delete_tr(c){
        $('#rows_' + c).remove();
    }

    function delete_assets_manual_main(c){
        $('#new_add_assets_' + c).remove();
        number_new_rows_assets--;
        number_rows_data_manual--;
    }

    function delete_assets_manual(c){
         $('#rows_manual_' + c).remove();
    }

    $(document).ready(function () {
        $('#datatype').select2();
        $('#source').select2();

        $('.select2').select2();

        $('.hide-fillter').hide();
        $('#fillter-advance').click(function(){
            $('.hide-fillter').slideToggle();
        });
        $('#close_filter').click(function(){
            $('.hide-fillter').slideToggle();
        });
    });

    function save_assets(){
        loading('load');
        var values = $("input[name='assets[]']").map(function(){
            return {'raw_data' : $(this).val(), 'domain_id' : $(this).data('domain_id') , 'site_id' : $(this).data('site_id')};
        }).get();
        var raw_data = $("input[name='raw_data[]']").map(function(){
            return {'raw_data' : $(this).val(), 'raw_data_base' : $(this).data('raw_data')};
        }).get();
        var data_type = $("select[name='data_type[]'] option:selected").map(function(){
            return {'data_type' : $(this).val(), 'raw_data_base' : $(this).data('raw_data')};
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
        axios.post('/scans/save_assets', {
            
            assets: values,
            assets_data: res,
        }).then(function (response) {
            
            
            $('#table-scans-data').DataTable().ajax.reload();
            $('#asset-to-use').prop("disabled", true);
            $('#show_asets').html("");
            $('#asset_to_use').modal('hide');
            loading('stop_load');
            console.log(base_url+'/scans/scans-domain/asset/'+'{{ $site->code }}');
            window.location.href = base_url+'/scans/scans-domain/asset/'+'{{ $site->code }}';
        }).catch(function (error) {
            loading('stop_load');
            var errors = error;
            var errorsHtml = "";
            errorsHtml += "<li>" + errors + "</li>";
            toastr.error(errorsHtml, '@langapp('response_status')');
        });
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
            $('#table-scans-data').DataTable().ajax.reload();
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

</script>


@endpush
