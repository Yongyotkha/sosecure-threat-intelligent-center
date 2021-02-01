<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();"
                    datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> @langapp('make_changes')
                {{  $scans->raw_data  }}</h4>
        </div>

        {{-- {!! Form::open(['route' => ['scans_assets.scans_assets', "id" => $scans->id, "code" => $code], 'class'
        => 'ajaxifyForm validator ajaxifyForm_custom', 'novalidate' => '', 'method' => 'POST', 'files' => true]) !!} --}}



        <div class="modal-body">

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
                <div class="col-md-12">
                    <table class="table table-bordered asset-table-manual-0" id='table-data-assets'>
                        <tbody id="assets_show_${number_tbody_rows}" class='test'>
                            {{-- @if ($AssetsData)
                                @foreach ($AssetsData as $keyin => $Data)
                            <tr id="rows_manual_${number_add_rows}" >
                                @if ($keyin==0)
                                    <td>
                                        <input type="text" name="assets_manual[]" value="{{@$scans->raw_data}}"
                            class="form-control">
                            </td>
                            @else
                            <td>

                            </td>

                            @endif
                            <td>


                                <input type="text" name="raw_data_manual[]" class="form-control"
                                    data-raw_data_manual="${0}" value="{{@$Data->value}}">
                            </td>

                            <td>
                                <select name="data_type_manual[]" class="select2 form-control">

                                    @if(is_array($DataTypes) || is_object($DataTypes))
                                    @foreach ($DataTypes as $DataTypes)
                                    <option value="{{@$DataTypes->id}}"
                                        {{$AssetsData->data_type_id == $DataTypes->id ? 'selected' : ''}}>
                                        {{@$DataTypes->value}}
                                    </option>
                                    @endforeach
                                    @endif

                                </select>

                            </td>

                            </tr>
                            @endforeach
                            @endif --}}
                        </tbody>
                    </table>
                </div>

                <div class="text-right" id='add_referent'>
                    {{-- <button type="button" class="btn btn-sm btn-info m-xs add-row" onclick="add(number_table_rows,number_tbody_rows,number_rows_data_manual)">
                        <span>@icon('solid/plus') Add Referent
                    </button> --}}
                </div>
            </div>
            <div id="new_assets_show_${number_new_rows_assets}"></div>

            {{-- <div class="modal-footer">

                {!! closeModalButton() !!}
                {!! renderAjaxButton('ok') !!}

            </div>
            {!! Form::close() !!} --}}
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

    <input type="hidden" id="page" value="{{ $page }}">



    @push('pagestyle')
    @include('stacks.css.form')
    @endpush
    @push('pagescript')
    @include('stacks.js.form')
    @include('stacks.js.fullscreen')


    <script>
    var number_rows_data_manual = 0;
    var number_rows = 0; 
    var number_add_rows = 0;
    var number_tbody_rows = 0;
    var number_table_rows = 1;
    var number_new_rows_assets = 0;
    var base_datatype = []; 
    var i = 0;



    $(document).ready(function () {

        loading('load');
        axios.get('/scans/get_data_type')
        .then(function (response) {
            loading('stop_load');
            var html = ``;
            var html2 = ``;
            let result = response.data;
            for(i;i<=@json($AssetsData).length;++i){
                
                if(@json($AssetsData)[i]!=undefined){
                
        
            
                    html += `<tr id="rows_manual_${i}">
                        <td>`;
                        if(i==0){
                            html += `
                            <input type="hidden" id="domain_id_manual" class="form-control" value="{{ @$site->domain_id }}">
                            <input type="hidden" id="site_id_manual" class="form-control" value="{{ @$site->site_id }}">
                            <input type="text" name="assets_manual[]" data-raw_data_manual="${0}" value = "${@json($scans->raw_data)}" class="form-control">`;
                        }else{
                            html +='';
                        }
                            
                        html += `</td>

                        <td>
                            <select name="data_type_manual[]" class="select2 form-control">`;
                            base_datatype = result.data_type;
                            for(let b in result.data_type){
                                const data_type = result.data_type[b];
                                html += `<option value="${data_type.id},${@json($AssetsData)[i]['id']}" ${data_type.id == @json($AssetsData)[i]['data_type_id'] ? 'selected' : ''}  data-raw_data_manual="${0}">${data_type.value}</option>`;
                            }
                            html += `</select>
                        </td>
                        <td>
                            <input type="text" name="raw_data_manual[]" class="form-control" value = "${@json($AssetsData)[i]['value']}" data-raw_data_manual="${0}">
                        </td>
                        <td>`;
                            if(i==0){
                            html += ``;
                        }else{
                            html += `<button type="button" class="btn btn-sm btn-danger m-xs delete-row" value="bulk-delete" onclick="delete_assets_manual(${i})">
                                        <span>@icon('solid/trash-alt')
                                    </button>`;
                        }
                        
                        html +=`</td>
                        
                    </tr>`;
                }
                
            }

            html2 += `<div class="text-center"><button type="button"  class="btn btn-sm btn-info m-xs add-row"  value="Add Row" onclick="add_assets_manual(0,${number_tbody_rows},0)">
                        <span>@icon('solid/plus')  Add
                    </button></div>`;  
                    
            $('.test').html(html);
            $('#add_referent').html(html2);
        }).catch(function (error) {
            loading('stop_load');
            var errors = error;
            var errorsHtml = "";
            errorsHtml += "<li>" + errors + "</li>";
            toastr.error(errorsHtml, '@langapp('response_status')');
        });

     

        
        var form_save = '.formSaving';
        $('.ajaxifyForm_custom').submit(function (event) {
            event.preventDefault();
            $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            
            var data = new FormData(this);
            if(form_save == '.formSavingAndRun'){
                data.append('formsubmit', 'formSavingAndRun');
            }else if(form_save == '.formPreview'){
                data.append('formsubmit', 'formPreview');
            }else if(form_save == '.formDraft'){
                data.append('formsubmit', 'formDraft');
            }
           
            axios.post($(this).attr("action"), data)
                .then(function (response) {
                        toastr.success(response.data.message, '@langapp('response_status') ');
                        $(form_save).html('<i class="fas fa-check"></i> @langapp('save') </span>');
                        window.location.href = response.data.redirect;
            })
            .catch(function (error) {
                if(error.response.data.exception){
                    toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }else{
                    var errors = error.response.data.errors;
                    var errorsHtml= '';
                    $.each( errors, function( key, value ) {
                        errorsHtml += '<li>' + value[0] + '</li>'; 
                    });
                    toastr.error( errorsHtml , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }
            }); 
        });
    });

    function add_assets_manual(table_row, tbody_rows, rows_data_manual){

        i++;
        var markup = ``;
        markup = `
        <tr id="rows_manual_${i}">
            <td></td>

            <td>
                <select name="data_type_manual[]" class="select2 form-control">`;
                for(let b in base_datatype){
                    const data_type = base_datatype[b];
                    markup += `<option value="${data_type.id}," data-raw_data_manual="${rows_data_manual}">${data_type.value}</option>`;
                }
            markup += `</select>
            </td>
            <td>
                <input type="text" name="raw_data_manual[]" class="form-control" data-raw_data_manual="${rows_data_manual}">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger m-xs delete-row" onclick="delete_assets_manual(${i})">
                    <span>@icon('solid/trash-alt')
                </button>
            </td>
        </tr>
        `;
        $('.test').append(markup);
    }

    function delete_assets_manual(c){
         $('#rows_manual_' + c).remove();
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
        $('.btn').attr('disabled',true);
        axios.post('{{ route('scans_assets.scans_assets_edit') }}', {
            code_assets:'{{ $code_asset }}',
            code:'{{ $code }}',
            assets: values,
            assets_data: res,
            page : $('#page').val()
        }).then(function (response) {
            loading('stop_load');
            toastr.success(response.data.message, '@langapp('response_status')');
            window.location.href = response.data.redirect;
            {{-- $('#table-data-assets').DataTable().ajax.reload();
            $('#asset-to-use').prop("disabled", true);
            $('#show_asets_manual').html("");
            $('#asset_to_use_manual').modal('hide');--}}
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

    @stack('pagestyle')
    @stack('pagescript')
