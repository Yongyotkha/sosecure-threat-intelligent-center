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
                    <div class="col-lg-6 col-md-12">
                        <div class="form-group m-b-md">
                            <h5 class="font-weight-bold">Keyword</h5>
                            <input type="text" class="form-control" name="keyword" placeholder="Search">
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="form-group">
                            <h5 class="font-weight-bold">Datatype Type</h5>
                            <select name="" id="datatype" class="select2-option form-control" multiple="multiple">
                                <option value="1">All</option>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-12">
                        <div class="form-group m-b-md">
                            <h5 class="font-weight-bold">Referent</h5>
                            <input type="text" class="form-control" name="keyword" placeholder="">
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-12">
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
                            <th style="min-width: 320px;">Asset</th>
                            <th class="text-center">Status</th>
                            <th>Data Type</th>
                            <th>Referent</th>
                            <th class="text-center">Last Update</th>
                            <th class="text-center">Source</th>
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
<style>
    .asset-info-group div { margin-bottom: 2px; white-space: nowrap; }
    .asset-info-group .text-muted { font-size: 11px; color: #999; width: 60px; display: inline-block; }
    .asset-info-group .font-bold { color: #333; font-weight: 600; }
    .asset-info-group .text-info { color: #17a2b8; }
    #table-scans-data td { vertical-align: middle !important; }
    .merged-cell { border-bottom: none !important; }
    .asset-table-unified > tbody > tr > td {
        vertical-align: top !important;
        padding-top: 10px !important;
        padding-bottom: 10px !important;
    }
    .cve-list-container {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        transition: max-height 0.25s ease-in-out, opacity 0.2s ease-in-out;
        width: 100%;
        box-sizing: border-box;
    }
    .cve-list-container.open {
        max-height: 150px;
        opacity: 1;
    }
</style>
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
            "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
            aLengthMenu: [[25, 50, 100, 200, 500, 1000, -1], [25, 50, 100, 200, 500, 1000, 'All']],
            iDisplayLength:25,
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
                { data: 'chk', orderable: false, searchable: false, sortable: false, className: 'w-10' },
                { data: 'asset_html', name: 'asset_html' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'data_type', name: 'data_type' },
                { data: 'referent', name: 'referent' },
                { data: 'updated_at', name: 'updated_at', className: 'text-center' },
                { data: 'source', name: 'source', className: 'text-center' },
            ],
            "drawCallback": function(settings) {
                var api = this.api();
                var rows = api.rows({ page: 'current' }).nodes();
                var last = null;
                var columnsToMerge = [3, 4, 5, 6]; 

                api.rows({ page: 'current' }).data().each(function(rowData, i) {
                    var group = rowData.group_key;
                    if (last === group && group !== null && !group.startsWith('orphan_')) {
                        columnsToMerge.forEach(function(colIdx) {
                            var cell = $(rows).eq(i).find('td').eq(colIdx);
                            cell.css('display', 'none');
                            
                            var prevIdx = i - 1;
                            while(prevIdx >= 0) {
                                var prevRowData = api.row(prevIdx).data();
                                if (prevRowData.group_key !== group) break;
                                
                                var prevCell = $(rows).eq(prevIdx).find('td').eq(colIdx);
                                if (prevCell.css('display') !== 'none') {
                                    var currentSpan = prevCell.attr('rowspan') ? parseInt(prevCell.attr('rowspan')) : 1;
                                    prevCell.attr('rowspan', currentSpan + 1);
                                    break;
                                }
                                prevIdx--;
                            }
                        });
                    }
                    last = group;
                });
            }
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
                return {
                    'raw_data' : $(this).val(), 
                    'ids' : $(this).data('ids'),
                    'domain_id' : $(this).data('domain'), 
                    'site_id' : $(this).data('site'), 
                    'data_type' : $(this).data('type'),
                    'ip_address' : $(this).data('ip'),
                    'referent' : $(this).data('referent')
                };
            }).get();
            axios.post('/scans/get_referent', {
                values: values,
            }).then(function (response) {
                loading('stop_load');
                let result = response.data;
                var html = ``;
                
                let groupedByType = {};
                result.data.forEach((item, i) => {
                    const type = item.selected_type || 'Other';
                    if (!groupedByType[type]) groupedByType[type] = [];
                    let origValue = values[i];
                    item.ip_address = origValue ? origValue.ip_address : '';
                    item.referent = origValue ? origValue.referent : '';
                    groupedByType[type].push(item);
                });

                let table_idx = 0;
                html += `<table class="table table-bordered asset-table-unified">
                    <thead>
                        <tr>
                            <th>Asset</th>
                            <th>Data Type</th>
                            <th>Value</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>`;

                let all_assets_info = [];

                for(let type in groupedByType){
                    for(let i in groupedByType[type]){
                        const data_referent = groupedByType[type][i];
                        const raw_data = data_referent.raw_data;
                        const data_transaction = data_referent.data;
                        const selected_type = data_referent.selected_type;
                        
                        let count = 0;
                        let checkSubAssetDplicate = [];

                        let parent_asset = data_referent.referent ? data_referent.referent : raw_data;

                        all_assets_info.push({
                            raw_data: parent_asset,
                            domain_id: (data_transaction && data_transaction[0]) ? data_transaction[0].domain_id : '',
                            site_id: (data_transaction && data_transaction[0]) ? data_transaction[0].site_id : ''
                        });

                        if (data_transaction && data_transaction.length > 0) {
                            let firstCveRowId = null;
                            let uniqueCves = [];
                            let cveFindings = [];
                            data_transaction.forEach(val => {
                                let label = '';
                                for(let b in result.data_type){
                                    const dt = result.data_type[b];
                                    if (val.data_type && dt.value && dt.value.trim().toLowerCase() == val.data_type.trim().toLowerCase()) {
                                        label = dt.value.trim().toLowerCase();
                                        break;
                                    }
                                }
                                if (label === 'cve' || label === 'direct cve') {
                                    cveFindings.push(val.raw_data);
                                }
                            });
                            uniqueCves = [...new Set(cveFindings)];

                            let cveListHtml = '';
                            uniqueCves.forEach(cve => {
                                cveListHtml += `<li style="border-bottom: 1px solid #f5f5f5;">
                                    <a href="https://nvd.nist.gov/vuln/detail/${cve}" target="_blank" style="padding: 8px 15px; color: #337ab7; font-weight: bold; text-decoration: none; display: block; font-size: 13px; transition: background 0.15s;" onmouseover="this.style.background='#f5f5f5';" onmouseout="this.style.background='transparent';">${cve}</a>
                                </li>`;
                            });

                            for(let c in data_transaction){
                                const data_transaction_val = data_transaction[c];
                                
                                let matchedTypeId = null;
                                let typeLabel = '';
                                for(let b in result.data_type){
                                    const dt = result.data_type[b];
                                    if (data_transaction_val.data_type && dt.value && dt.value.trim().toLowerCase() == data_transaction_val.data_type.trim().toLowerCase()) {
                                        matchedTypeId = dt.id;
                                        typeLabel = dt.value.trim().toLowerCase();
                                        break;
                                    }
                                }
                                if (!matchedTypeId && selected_type && data_transaction_val.raw_data == raw_data) {
                                    for(let b in result.data_type){
                                        const dt = result.data_type[b];
                                        if (dt.value && dt.value.trim().toLowerCase() == selected_type.trim().toLowerCase()) {
                                            matchedTypeId = dt.id;
                                            typeLabel = dt.value.trim().toLowerCase();
                                            break;
                                        }
                                    }
                                }

                                let nameTypes = ['domain name', 'subdomain', 'internet name', 'affiliate - internet name', 'affiliate - domain name', 'host'];
                                let isNameType = nameTypes.indexOf(typeLabel) !== -1;
                                
                                let uniqueKey = isNameType ? `NAME_GROUP|${data_transaction_val.raw_data}` : `${matchedTypeId || 'Other'}|${data_transaction_val.raw_data}`;
                                if (checkSubAssetDplicate.indexOf(uniqueKey) !== -1) continue;
                                checkSubAssetDplicate.push(uniqueKey);

                                number_rows++;
                                count++;
                                let row_parent_asset = (String(data_transaction_val.raw_data).trim() == String(raw_data).trim()) ? parent_asset : (data_transaction_val.referent ? data_transaction_val.referent : parent_asset);

                                let isCve = (typeLabel === 'cve' || typeLabel === 'direct cve');
                                let trAttrs = '';
                                if (isCve) {
                                    if (firstCveRowId === null) {
                                        firstCveRowId = number_rows;
                                        trAttrs = `data-cve-group="${firstCveRowId}"`;
                                    } else {
                                        trAttrs = `data-cve-group="${firstCveRowId}" style="display: none;"`;
                                    }
                                }

                                html += `<tr id="rows_${number_rows}" ${trAttrs}>
                                    <td style="vertical-align: middle;">
                                        <strong>${row_parent_asset}</strong>
                                        <input type="hidden" name="assets[]" value="${row_parent_asset}" data-domain_id="${data_transaction_val.domain_id || ''}" data-site_id="${data_transaction_val.site_id || ''}">
                                    </td>
                                    <td>
                                        <select name="data_type[]" class="select2 form-control">`;
                                        for(let b in result.data_type){
                                            base_datatype = result.data_type;
                                            const data_type = result.data_type[b];
                                            let isSelected = (data_type.id == matchedTypeId);

                                            html += `<option value="${data_type.id}" ${isSelected ? 'selected' : ''} data-raw_data="${row_parent_asset}">${data_type.value}</option>`;
                                        }
                                        html += `</select>
                                    </td>
                                    <td>`;
                                    if (isCve && firstCveRowId === number_rows) {
                                        let cvesJson = JSON.stringify(uniqueCves).replace(/'/g, "&#39;");
                                        html += `<div style="display:flex; align-items:flex-start; width: 100%;">
                                            <div style="flex-grow:1; margin-right: 5px; position: relative;">
                                                <div class="btn btn-default btn-block text-left" onclick="toggleCveList(this)" style="display: flex; justify-content: space-between; align-items: center; border: 1px solid #ccc; text-align: left; padding: 6px 12px; background: #fff; width: 100%; border-radius: 4px; box-shadow: none; height: 34px; cursor: pointer; user-select: none;">
                                                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 13px; color: #555;">Select Detail</span>
                                                    <span class="caret-icon" style="transition: transform 0.2s; display: inline-block; line-height: 1;"><span class="caret"></span></span>
                                                </div>
                                                <div class="cve-list-container">
                                                    <div class="cve-list-inner" style="max-height: 150px; overflow-y: auto; border: 1px solid #ccc; border-top: none; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; background: #fff; margin-top: -1px; box-sizing: border-box;">
                                                        <ul style="list-style: none; padding: 0; margin: 0;">
                                                            ${cveListHtml}
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="raw_data[]" value="${data_transaction_val.raw_data}" data-raw_data="${row_parent_asset}" data-ip="${data_referent.ip_address || ''}">
                                            <button type="button" class="btn btn-xs btn-info" onclick="view_cve_details('${data_transaction_val.domain_id || ''}', '${data_transaction_val.site_id || ''}', this)" data-cves='${cvesJson}' data-ip="${data_referent.ip_address || ''}" data-domain="${parent_asset}" title="View CVE Details" style="padding: 6px 12px; white-space: nowrap; height: 34px; display: inline-flex; align-items: center; justify-content: center;"><i class="fas fa-search" style="margin-right: 5px;"></i> View Detail</button>
                                        </div>`;
                                    } else {
                                        html += `<input type="text" name="raw_data[]" class="form-control" value="${data_transaction_val.raw_data}" data-raw_data="${row_parent_asset}" data-ip="${data_referent.ip_address || ''}">`;
                                    }
                                    html += `</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger m-xs delete-row" value="bulk-delete" onclick="delete_tr(${number_rows})">
                                            <span>@icon('solid/trash-alt')
                                        </button>
                                    </td>
                                </tr>`;
                            }
                        } else {
                            number_rows++;
                            html += `<tr id="rows_${number_rows}">
                                <td style="vertical-align: middle;">
                                    <strong>${parent_asset}</strong>
                                    <input type="hidden" name="assets[]" value="${parent_asset}">
                                </td>
                                <td>
                                    <select name="data_type[]" class="select2 form-control">`;
                                    for(let b in result.data_type){
                                        base_datatype = result.data_type;
                                        const data_type = result.data_type[b];
                                        let isSelected = (selected_type && data_type.value && data_type.value.trim().toLowerCase() == selected_type.trim().toLowerCase());
                                        html += `<option value="${data_type.id}" ${isSelected ? 'selected' : ''} data-raw_data="${parent_asset}">${data_type.value}</option>`;
                                    }
                                    html += `</select>
                                </td>
                                <td>
                                    <input type="text" name="raw_data[]" class="form-control" value="${raw_data}" data-raw_data="${parent_asset}" data-ip="${data_referent.ip_address || ''}">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-danger m-xs delete-row" value="bulk-delete" onclick="delete_tr(${number_rows})">
                                        <span>@icon('solid/trash-alt')
                                    </button>
                                </td>
                            </tr>`;
                        }
                    }
                }
                html += `</tbody></table>
                <div class="text-center">
                    <button type="button" class="btn btn-sm btn-info m-xs add-row" value="Add Row" onclick="add_assets_unified()">
                        <span>@icon('solid/plus')  Add
                    </button>
                </div>`;

                window.current_assets_list = all_assets_info; 

                $('#show_asets').html(html);
                $('.select2').select2({
                    width: '100%'
                });
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
                            <input type="hidden" id="ip_manual_${number_rows_data_manual}" value="">
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
                <input type="hidden" id="ip_manual_${rows_data_manual}" value="">
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

    function add_assets_unified(){
        number_rows++;
        var markup = ``;
        markup = `
        <tr id="rows_${number_rows}">
            <td>
                <select name="assets[]" class="select2 form-control">`;
                if(window.current_assets_list){
                    for(let i in window.current_assets_list){
                        let asset = window.current_assets_list[i];
                        markup += `<option value="${asset.raw_data}" data-domain_id="${asset.domain_id}" data-site_id="${asset.site_id}">${asset.raw_data}</option>`;
                    }
                }
                markup += `</select>
            </td>
            <td>
                <select name="data_type[]" class="select2 form-control">`;
                for(let b in base_datatype){
                    const data_type = base_datatype[b];
                    markup += `<option value="${data_type.id}">${data_type.value}</option>`;
                }
            markup += `</select>
            </td>
            <td>
                <input type="text" name="raw_data[]" class="form-control" value="">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger m-xs delete-row" onclick="delete_tr(${number_rows})">
                    <span>@icon('solid/trash-alt')
                </button>
            </td>
        </tr>
        `;
        $("table.asset-table-unified tbody").append(markup);
        $('.select2').select2({
            width: '100%'
        });
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
        $('.select2').select2({
            width: '100%'
        });
    }

    function toggleCveList(btn) {
        let container = $(btn).siblings('.cve-list-container');
        let caret = $(btn).find('.caret-icon');
        let isOpen = container.hasClass('open');
        if (isOpen) {
            container.removeClass('open');
            setTimeout(function() {
                if (!container.hasClass('open')) {
                    $(btn).css({
                        'border-bottom-left-radius': '4px',
                        'border-bottom-right-radius': '4px'
                    });
                }
            }, 250);
            caret.css('transform', 'rotate(0deg)');
        } else {
            $(btn).css({
                'border-bottom-left-radius': '0',
                'border-bottom-right-radius': '0'
            });
            container.addClass('open');
            caret.css('transform', 'rotate(180deg)');
        }
    }

                                    function delete_tr(c){
        let row = $('#rows_' + c);
        let group = row.attr('data-cve-group');
        if (group) {
            $(`tr[data-cve-group="${group}"]`).remove();
        } else {
            row.remove();
        }
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
        if ($("input[name='raw_data[]']").length === 0) {
            toastr.warning('Please add at least one asset before saving.', '@langapp('response_status')');
            return;
        }

        loading('load');
        var values = [];
        var seenAssets = {};
        $("input[name='assets[]']").each(function(){
            var val = $(this).val();
            if (!seenAssets[val]) {
                values.push({
                    'raw_data' : val, 
                    'domain_id' : $(this).data('domain_id'), 
                    'site_id' : $(this).data('site_id')
                });
                seenAssets[val] = true;
            }
        });

        var sourceIds = [];
        $("input[name='select[]']:checked").each(function(){
            var ids = $(this).data('ids');
            if (Array.isArray(ids)) {
                sourceIds = sourceIds.concat(ids);
            } else if (ids) {
                sourceIds.push(ids);
            }
        });

        var raw_data = $("input[name='raw_data[]']").map(function(){
            return {
                'raw_data' : $(this).val(), 
                'raw_data_base' : $(this).data('raw_data'),
                'ip_address' : $(this).data('ip')
            };
        }).get();
        var data_type = $("select[name='data_type[]'] option:selected").map(function(){
            return {'data_type' : $(this).val(), 'raw_data_base' : $(this).data('raw_data')};
        }).get();    
        var res = raw_data.map(function(v, i) {
            if(data_type[i].raw_data_base == v.raw_data_base){
                return {
                    data_type: data_type[i].data_type,
                    raw_data: v.raw_data,
                    raw_data_base: v.raw_data_base,
                    ip_address: v.ip_address
                };
            }
        }).filter(item => item !== undefined); 

        axios.post('/scans/save_assets', {
            assets: values,
            assets_data: res,
            ids: sourceIds
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
                    raw_data_base: v.raw_data_base,
                    ip_address: $('#ip_manual_' + v.raw_data_base).val() || ''
                };
            }
        });

        axios.post('/scans/save_assets_new', {
            assets: values,
            assets_data: res
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
