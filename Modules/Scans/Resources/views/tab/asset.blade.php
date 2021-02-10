<header class="header b-b clearfix">
    <div class="panel-body">
        <section class="panel panel-default hide-fillter" id="advance-search" style="display: none">
            <header class="panel-heading font-bold panel-header-blue">
                <div class="row">
                    <div class="col-md-12">
                        <i class="fas fa-filter"></i> Filter
                    </div>
            </header>
            <div class="panel-body" style="padding: 0 !important">
                <div class="container-fluid">
                    <div class="row" style="display: none;">
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

                    <div class="row">
                        <div class="col-md-4">
                            <h5 class="font-weight-bold">Filter By</h5>
                            <div id="groupby-btn" class="btn-group special mb-2">
                                <button class="btn btn-grey active" onclick="selectGroupBy('domain')">
                                    <span> Host </span>
                                </button>
                                <button class="btn btn-grey" onclick="selectGroupBy('ip')">
                                    <span> IP </span>
                                </button>
                                <button class="btn btn-grey" onclick="selectGroupBy('cpe')">
                                    <span> CPE </span>
                                </button>
                                <button class="btn btn-grey" onclick="selectGroupBy('os_type')">
                                    <span> OS Type </span>
                                </button>
                            </div>
                            <div class="form-group">
                                <select id="groupby-select" class="form-control">
                                    <option value="">- SELECT -</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <h5 class="font-weight-bold">Status</h5>
                            <div id="groupby-status" class="btn-group special mb-2">
                                <button class="btn btn-grey active" onclick="changeActive('')">
                                    <span> All </span>
                                </button>
                                <button class="btn btn-grey" onclick="changeActive('Active')">
                                    <span> Active </span>
                                </button>
                                <button class="btn btn-grey" onclick="changeActive('Inactive')">
                                    <span> Inactive </span>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <select id="groupby-select2" style="display: none;" class="form-control">
                                    <option value="">Domain All</option>
                                    <option value="">DARK WEB</option> 
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            <div class="panel-footer">
                <div class="row">
                    <div class="col-lg-12 text-right">
                        <button type="button" id="btn_news_search" class="btn btn-info btn-responsive btn-fz-13"
                            onclick="searchTB()">
                            <i class="fas fa-search"></i>
                            @langapp('apply')
                        </button>
                        <button type="button" id="btn_news_reset" class="btn btn-default btn-responsive btn-fz-13"
                            style="white-space: nowrap" onclick="clearTB()">
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

        {{-- <section class="panel panel-default hide-fillter" id="advance-search" style="display: none;">
            <div class="container-fluid" style="padding: 2rem;">
                <div class="row" style="display: none;">
                    <div class="col-md-6">
                        <div class="form-group m-b-md">
                            <label for="" class="">Keyword</label>
                            <input type="text" class="form-control" name="keyword" placeholder="Search">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group m-b-md">
                            <label for="" class="">Referent</label>
                            <input type="text" class="form-control" name="keyword" placeholder="">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <h5 class="font-weight-bold">Group By</h5>
                        <div id="groupby-btn" class="btn-group special mb-2">
                            <button class="btn btn-grey active" onclick="selectGroupBy('domain')">
                                <span> Host </span>
                            </button>
                            <button class="btn btn-grey" onclick="selectGroupBy('ip')">
                                <span> IP </span>
                            </button>
                            <button class="btn btn-grey" onclick="selectGroupBy('cpe')">
                                <span> CPE </span>
                            </button>
                            <button class="btn btn-grey" onclick="selectGroupBy('os_type')">
                                <span> OS Type </span>
                            </button>
                        </div>
                        <div class="form-group">
                            <select id="groupby-select" class="form-control">
                                <option value="">- SELECT -</option>
                            </select>
                        </div>

                    </div>

                    <div class="col-md-4">
                        <h5 class="font-weight-bold">Status</h5>
                        <div id="groupby-status" class="btn-group special mb-2">
                            <button class="btn btn-grey active" onclick="changeActive('')">
                                <span> All </span>
                            </button>
                            <button class="btn btn-grey" onclick="changeActive('active')">
                                <span> Active </span>
                            </button>
                            <button class="btn btn-grey" onclick="changeActive('inactive')">
                                <span> Inactive </span>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <select id="groupby-select2" style="display: none;" class="form-control">
                                <option value="">Domain All</option>
                                <option value="">DARK WEB</option> 
                            </select>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                    <div class="row pull-right">
                        <div class="col-md-12">
                            <div class="form-group m-b-md">
                                <label for="" class="d-block">&nbsp;</label>
                                <button class="btn btn-info btn-fz-13">
                                    <i class="fas fa-search"></i>
                                    <span> @langapp('apply') </span>
                                </button>
                                <button class="btn btn-default btn-fz-13">
                                    <i class="fas fa-broom"></i>
                                    <span> Clear </span>
                                </button>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </section> --}}

        <section class="panel panel-default">
            <header class="panel-heading font-bold panel-header-blue">
                <div class="row">
                    <div class="col-xs-12">
                        <i class="fas fa-table"></i> Table Asset
                    </div>
                </div>
            </header>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="table-assets-template">
                        <thead>
                            <tr>
                                {{-- <th class="no-sort">
                                    <label>
                                        <input name="select_all" value="1" id="select-all" type="checkbox" />
                                        <span class="label-text"></span>
                                    </label>
                                </th>  --}}
                                <th rowspan="2" class="align-middle">Site</th>
                                <th rowspan="2" class="align-middle">Host</th>
                                <th rowspan="2" class="align-middle">IP</th>
                                <th colspan="7" class="text-center">CPE</th>
                                <th rowspan="2" class="align-middle">Status</th>
                                <th rowspan="2" class="align-middle">Action</th>
                                <th rowspan="2" class="align-middle">CPESTRING</th>
                            </tr>
                            <tr>
                                <th>Vendor</th>
                                <th>Title</th>
                                <th>Versions</th>
                                <th>Edition</th>
                                <th>Remark</th>
                                <th>Os Type</th>
                                <th>Delete CPE</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                {{-- <table class="table table-striped table-bordered" id="table-scans-data-assets">
                    <thead>
                        <tr>
                            <th class="no-sort" style="width: 12px">
                                <label>
                                    <input name="select_all" value="1" id="select-all" type="checkbox"
                                        class="select-chk" />
                                    <span class="label-text"></span>
                                </label>
                            </th>
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
                    </tbody> 
                </table>--}}
            </div>
        </section>

</div>
    <div class="modal" id="delete_asset" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"
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
                    <button type="button" class="btn btn-info submit btn-rounded delete_webdefacement_submit"
                        onclick="delete_asset_save()"><i class="fas fa-paper-plane"></i> OK</button>
                </div>
            </div>
        </div>
    </div>

</header>

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
    active_btn('#groupby-status .btn-grey');

    var site_id = "{{$sitecode}}";
    var domaincode = "{{$domaincode}}";
    var active = '';

    function changeActive(act){
        active = act;
    }

    function clearTB(){
        $("#groupby-select").val('').trigger("change");
        $("#select-site").val(0).trigger("change");
        $("#groupby-status>button").removeClass("active");
        $("#groupby-status>button:first").addClass("active");
        active = '';
        searchTB();
    }

    function searchTB(searchLinkAll='',colsearchLinkAll=''){
        
        let selectedValue = $('#groupby-select').children("option:selected").val();
        let columnSearch = selectedGroup;
        if(searchLinkAll!==''){
            selectedValue = colsearchLinkAll;
            selectedValue = searchLinkAll;
        }
        let selectedSiteName = '';
        if($('#select-site').children("option:selected").val()!=0){
            selectedSiteName = $('#select-site').children("option:selected").text();
        }
        
        let active_tb;
        if(active==""){
            active_tb = active;
        }else{
            active_tb = '^'+active+'$';
        }
        
        
        if(columnSearch=='domain'){
            columnSearch = 1;
        }else if(columnSearch=='ip'){
            columnSearch = 2;
        }else if(columnSearch=='cpe'){
            {{--columnSearch = [3, 4,5,6,7,12];--}}
            columnSearch = 12;
        }else if(columnSearch=='os_type'){
            columnSearch = 8;
        }else{
            columnSearch = '';
        }
        t.search( '' ).columns().search( '' ).draw();
        t.column(0).search(selectedSiteName, false, true,false).column(columnSearch).search(selectedValue).column(10).search(active_tb, true, false).draw();

        {{--ads.column(5).search(active_tb).draw();
        t.search( '' ).columns().search( '' ).draw();--}}
    }

    var selectedGroup = '';
    function selectGroupBy(columnGroup){
        if(selectedGroup!=columnGroup){
            selectedGroup = columnGroup;
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{!! route('assets.get_selected_filter') !!}',
                type: "get",
                data: ({
                    selectedGroup:selectedGroup,
                    sitecode:site_id,
                    domaincode:domaincode,
                }),
                datatype: "html",
                beforeSend: function(){
                    loading('load');
                },
            }).done(function(data){
                let groupby_select = '';
                groupby_select += '<option selected value="">- SELECT -</option>';
                $.each(data.selected, function(key, val){
                    groupby_select += '<option value="'+val.val_select+'">'+val.val_select+'</option>';
                });
                $('#groupby-select').html(groupby_select);
                $('#groupby-select').select2();
                loading('stop_load');
            }).fail(function(jqXHR, ajaxOptions, thrownError){
                loading('stop_load');
                console.log("No response from server");
            });
        }
    }

    $('#table-scans-data-assets').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {

            $('#btn_del_select').prop("disabled", false);
        } else {
            
            if ($('.select-chk').filter(':checked').length < 1){

                $('#btn_del_select').attr('disabled',true);
            }
        }
    });

    var del_val = [];
    $("#btn_del_select").click(function() {
        
        $('#delete_asset').modal('show');
        $('.asset_id:checked').each(function () {
            del_val.push(this.value);
        });
    });

    function delete_asset_save(){

        $.ajax({
            type:"POST",
            url:"{{ route('scans.delete_assets') }}",
            data:{
                asset_id: del_val,
                page: 'scan'
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

    $(function () {



        {{--$('#table-scans-data-assets').DataTable({
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
                    d.menu = 'scan';
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
        });--}}
    });

    var number_rows = 0; 
    var number_add_rows = 0;
    var number_tbody_rows = 0;
    var number_table_rows = 1;
    var number_new_rows_assets = 0;
    var base_datatype = []; 
    
    $(document).ready(function () {
        selectGroupBy('domain');

       
        data_table();
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
            toastr.success(response.data.message, '@langapp('response_status')');
            $('#table-assets-template').DataTable().ajax.reload();
            {{--$('#table-scans-data-assets').DataTable().ajax.reload();--}}
            $('#asset-to-use').prop("disabled", true);
            $('#show_asets_manual').html("");
            $('#asset_to_use_manual').modal('hide');
            loading('stop_load');
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

    var t;
    function data_table(){
        t = $('#table-assets-template').DataTable({
            searching: true,
            ordering: true,
            pagination: true,
            pageLength: 25,
            processing: true,
            serverSide: false,
            destroy: true,
            "dom": '<"d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
            order: [[ 0, "asc" ]],
            ajax: {
                type: "POST",
                url: '{!! route('assets.table_asset')!!}',
                data:function(d){
                    d.menu = "{{$menu}}";
                    d.site = "{{$sitecode}}";
                    d.domaincode = "{{$domaincode}}";
                }
            },
            initComplete : function( settings, json){
            },
            columns: [
                {{--{
                    width: '1%',
                    data: 'chk',
                    name: 'chk',
                },--}}
                {
                    width: '25%',
                    data: 'site_name',
                    name: 'site_name',
                    className: 'no-wrap'
                },
                {
                    width: '20%',
                    data: 'domain',
                    name: 'domain',
                },
                {
                    width: '20%',
                    data: 'ip',
                    name: 'ip',
                },
                {
                    data: 'CPE_Vendor',
                    name: 'CPE_Vendor',
                    className: 'padingtablezero text-center no-wrap'
                },
                {
                    data: 'CPE_Title',
                    name: 'CPE_Title',
                    className: 'padingtablezero text-center no-wrap'
                },
                {
                    data: 'CPE_Version',
                    name: 'CPE_Version',
                    className: 'padingtablezero text-center no-wrap'
                },
                {
                    data: 'CPE_Edition',
                    name: 'CPE_Edition',
                    className: 'padingtablezero text-center no-wrap'
                },
                {
                    data: 'CPE_Remark',
                    name: 'CPE_Remark',
                    className: 'padingtablezero text-center no-wrap'
                },
                {
                    data: 'CPE_Ostype',
                    name: 'CPE_Ostype',
                    className: 'padingtablezero text-center no-wrap'
                },
                {
                    data: 'CPE_Del',
                    name: 'CPE_Del',
                    className: 'padingtablezero text-center no-wrap',
                    visible:{{(TYPE_WEB=='center'?json_encode(true):json_encode(false))}},
                },
                {
                    orderable: false,
                    width: '3%',
                    data: 'status',
                    name: 'status',
                    className: 'text-center'
                },  
                {
                    searchable: false,
                    orderable: false,
                    width: '3%',
                    data: 'action',
                    name: 'action',
                    className: 'text-center no-wrap',
                    visible:{{(TYPE_WEB=='center'?json_encode(true):json_encode(false))}},
                },
                {
                    data: 'CPE',
                    name: 'CPE',
                    visible:false

                },
            ],
            columnDefs: [
                {{--{
                    
                    targets: 0,
                    searchable: false,
                    orderable: false,
                    width: '10px',
                    render: function (data, type, row, meta) {
                        return '<label><input type="checkbox" name="checked" class="select-chk asset_id" value="' + row.code + '"><span class="label-text"></span></label>';
                    }
                   
                },--}}
                {
                    targets: 11,
                    render: function (data, type, row, meta) {
                        return row.cpe+row.action;
                        
                    }
                   
                },
                {
                    targets: 10,
                    render: function (data, type, row, meta) {
                        if(row.status==1){
                            return '<span class="badge badge-success">Active</span>';
                        }else{
                            return '<span class="badge badge-danger">Inactive</span>';
                        }
                        
                    }
                   
                },
            ],

        });
    }
</script>


@endpush