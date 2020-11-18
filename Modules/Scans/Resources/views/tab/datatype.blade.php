<header class="header b-b clearfix">
    <div class="panel-body">
        <div class="hide-fillter" style="margin-bottom: 1rem">
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
            <div class="row">
                <div class="col-md-12 text-right">
                    <button class="btn btn-info">
                        <i class="fas fa-search"></i>
                        <span> Search </span>
                    </button>
                    <button class="btn btn-default">
                        <i class="fas fa-broom"></i>
                        <span> Clear </span>
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th class="no-sort">
                                <label>
                                    <input name="select_all" value="1" id="select-all" type="checkbox" />
                                    <span class="label-text"></span>
                                </label>
                            </th>
                            <th>Data Type</th>
                            <th>Raw Data</th>
                            <th>Referent</th>
                            <th class="text-center">Last Update</th>
                            <th class="text-center">Status</th>
                            {{-- <th>Module</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($TransactionScans as $data)
                        <tr>
                            <td class="text-left">
                                <label>
                                    <input name="select[]" value="{{ $data -> raw_data }}" data-domain="{{ $data -> domain_id }}" data-site="{{ $data -> site_id }}" class="select-chk" type="checkbox" />
                                    <span class="label-text"></span>
                                </label>
                            </td>
                            <td class="text-left">{{ $data -> data_type }}</td>
                            <td class="text-left">{{ $data -> raw_data }}</td>
                            <td class="text-left">{{ $data -> referent }}</td>
                            <td class="text-center">
                                2020-10-01 11:12    
                            </td>
                            <td class="text-center">
                                <span class="badge badge-success">นำไปใช่้งานแล้ว</span>
                                {{-- <span class="badge badge-danger">เพิ่มมาใหม่</span>  --}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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

<script>
    var number_rows = 0; 
    var base_datatype = []; 
    $(document).ready(function(){
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
                for(let i in result.data){
                    const data_referent = result.data[i];
                    const raw_data = data_referent.raw_data;
                    const data_transaction = data_referent.data;
                    html += `<div class="col-md-3">
                        <h4 class="text-dark">${raw_data}</h4>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-bordered asset-table-${i}">
                            <tbody>`;
                                let count = 0;
                                for(let c in data_transaction){
                                    const data_transaction_val = data_transaction[c];
                                    number_rows++;
                                    count++;
                                    if(count == 1){
                                        html += `<input type="hidden" name="assets[]" class="form-control" value="${raw_data}" data-domain_id="${data_transaction_val.domain_id}" data-site_id="${data_transaction_val.site_id}">`;
                                    }
                                    html += `<tr id="rows_${number_rows}">
                                        <td>
                                            <input type="text" name="raw_data[]" class="form-control" value="${data_transaction_val.raw_data}" data-raw_data="${raw_data}">
                                        </td>
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
                                            <button type="button" class="btn btn-sm btn-danger m-xs delete-row" value="bulk-delete" onclick="delete_tr(${number_rows})">
                                                <span>@icon('solid/trash-alt')
                                            </button>
                                        </td>
                                    </tr>`;
                                }
                            html += `</tbody>
                        </table>
                        <div class="text-center">
                                <button type="button" class="btn btn-sm btn-info m-xs add-row" value="Add Row" onclick="add_assets(${i},'${raw_data}')">
                                    <span>@icon('solid/plus')  Add
                                </button>
                            </div>
                        </div>`;   
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

    function add_assets(i, raw_data){
        number_rows++;
        var markup = ``;
        markup = `
        <tr id="rows_${number_rows}">
            <td>
                <input type="text" name="raw_data[]" class="form-control" data-raw_data="${raw_data}">
            </td>
            <td>
                <select name="data_type[]" class="select2 form-control">`;
                for(let b in base_datatype){
                    const data_type = base_datatype[b];
                    markup += `<option value="${data_type.id}" data-raw_data="${raw_data}">${data_type.value}</option>`;
                }
            markup += `</select>
            </td>
            <td>
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

    $(document).ready(function () {
        $('#datatype').select2();
        $('#source').select2();

        $('.select2').select2();

        $('.hide-fillter').hide();
        $('#fillter-advance').click(function(){
            $('.hide-fillter').toggle();
        });
    });


    $('#asset-to-use').prop("disabled", true);
        $('.select-chk').click(function() {
        if ($(this).is(':checked')) {
            $('#asset-to-use').prop("disabled", false);
        } else {
            if ($('.select-chk').filter(':checked').length < 1){
                $('#asset-to-use').attr('disabled',true);
            }
        }
    });

    function save_assets(){
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
            $('#show_asets').html("");
            console.log(response);
        }).catch(function (error) {
            var errors = error;
            var errorsHtml = "";
            errorsHtml += "<li>" + errors + "</li>";
            toastr.error(errorsHtml, '@langapp('response_status')');
        });
    }

</script>


@endpush

