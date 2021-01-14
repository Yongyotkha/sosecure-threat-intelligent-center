<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();"
                    datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> @langapp('make_changes')
                {{  $scans->raw_data  }}</h4>
        </div>

        {!! Form::open(['route' => ['scans_assets.scans_assets', "id" => $scans->id, "code" => $code], 'class'
        => 'ajaxifyForm validator ajaxifyForm_custom', 'novalidate' => '', 'method' => 'POST', 'files' => true]) !!}



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
                    <table class="table table-bordered asset-table-manual-0">
                        <tbody id="assets_show_${number_tbody_rows}">
                            @if ($AssetsData)
                                @foreach ($AssetsData as $keyin => $Data)
                            <tr id="rows_manual_${number_add_rows}">
                                @if ($keyin==0)
                                    <td>
                                        <input type="text" name="assets_manual[]" value="{{@$scans->raw_data}}"  class="form-control">
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
                                        {{-- @if ($DataTypes)
                                        
                                        @foreach ($DataTypes as $DataTypes)
                                        <option value="{{@$DataTypes->id}}">{{@$DataTypes->value}} 
                                        </option>
                                        @endforeach

                                        @endif --}}
                                    </select>
                                   

                                </td>
                                
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                
                <div class="text-right">
                    <button type="button" class="btn btn-sm btn-info m-xs add-row" value="Add Row"
                        onclick="add_assets_manual(0,${number_tbody_rows},0)">
                        <span>@icon('solid/plus') Add Referent
                    </button>
                </div>
            </div>
            <div id="new_assets_show_${number_new_rows_assets}"></div>

            <div class="modal-footer">

                {!! closeModalButton() !!}
                {!! renderAjaxButton('ok') !!}
    
            </div>
            {!! Form::close() !!}
        </div>
    </div>



    @push('pagestyle')
    @include('stacks.css.form')
    @endpush
    @push('pagescript')
    @include('stacks.js.form')
    @include('stacks.js.fullscreen')


    <script>

        console.log({{$AssetsData}});
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

       
    </script>
    @endpush

    @stack('pagestyle')
    @stack('pagescript')