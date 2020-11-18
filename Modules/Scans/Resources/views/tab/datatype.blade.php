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
       
    $(document).ready(function(){
        $("#asset-to-use").click(function(){
            var values = $("input[name='select[]']:checked").map(function(){
                return {'raw_data' : $(this).val(), 'domain_id' : $(this).data('domain') , 'site_id' : $(this).data('site')};
            }).get();
            axios.post('/scans/get_referent', {
                values: values,
            }).then(function (response) {
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
                        <table class="table table-bordered asset-table">
                            <tbody>`;
                                for(let c in data_transaction){
                                    const data_transaction_val = data_transaction[c];
                                    html += `<tr>
                                        <td>
                                            <input type="text" class="form-control" value="${data_transaction_val.raw_data}">
                                        </td>
                                        <td>
                                            <select name="" class="select2 form-control">`;
                                            for(let b in result.data_type){
                                                const data_type = result.data_type[b];
                                                html += `<option value="${data_type.value}" ${data_type.value == data_transaction_val.data_type ? 'selected' : ''}>${data_type.value}</option>`;
                                            }
                                            html += `</select>
                                        </td>
                                        <td>
                                            <button type="submit" class="btn btn-sm btn-danger m-xs delete-row" value="bulk-delete">
                                                <span>@icon('solid/trash-alt')
                                            </button>
                                        </td>
                                    </tr>`;
                                }
                            html += `</tbody>
                        </table>
                        <div class="text-center">
                                <button type="submit" class="btn btn-sm btn-info m-xs add-row" value="Add Row">
                                    <span>@icon('solid/plus')  Add
                                </button>
                            </div>
                        </div>`;      
                }
                $('#show_asets').html(html);

                $(".add-row").click(function(){
                    var markup = `
                    <tr>
                        <td>
                            <input type="text" class="form-control">
                        </td>
                        <td>
                            <select name="" class="select2 form-control">
                                <option value="all">IPv6 Address</option>
                            </select>
                        </td>
                        <td>
                            <button type="submit" class="btn btn-sm btn-danger m-xs delete-row">
                                <span>@icon('solid/trash-alt')
                            </button>
                        </td>
                    </tr>
                    `;
                    $("table.asset-table tbody").append(markup);
                });

                $(".asset-table").on('click','.delete-row',function(){
                    $(this).closest('tr').remove();
                });

            }).catch(function (error) {
                var errors = error;
                var errorsHtml = "";
                errorsHtml += "<li>" + errors + "</li>";
                toastr.error(errorsHtml, '@langapp('response_status')');
            });
        });
    });  

    $(document).ready(function () {
        $('#datatype').select2();
        $('#source').select2();

        $('.select2').select2();

        $('.hide-fillter').hide();
        $('#fillter-advance').click(function(){
            $('.hide-fillter').toggle();
        });
    });

    $('.select-chk').click(function() {
        if($(this).is(':checked')){
            if($('#asset-to-use').is(':disabled')) {
            $('#asset-to-use').removeAttr('disabled');
            } else {
                $('#asset-to-use').attr('disabled', 'disabled');
            }
        }else{
            $('#asset-to-use').attr('disabled', 'disabled');
        }
    });

</script>


@endpush

