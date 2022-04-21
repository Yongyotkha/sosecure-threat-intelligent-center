<div class="modal-dialog modal-dialog-aside fullscreen size-half-50">
    <div class="modal-content">
        <div class="modal-header bg-info">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white">
                <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip"
                    title="Fullscreen" data-placement="right"></i>
                Manage Rules
            </h4>
        </div>
        {!! Form::open() !!}
        <div class="modal-body">

            <div class="form-group row">
                <label class="col-lg-2 control-label">Extension<span class="text-danger">*</span> </label>
                <div class="col-lg-10">
                    <select name="" id="extentions" class="select2-option form-control">
                        <option value="1" disabled selected>Choose Select</option>
                        <option value="Y">All</option>
                        <option value="N">Custom</option>
                    </select>
                </div>
            </div>

            <div id="custom_select" class="form-group row" style="display: none">
                <label class="col-lg-2 control-label">Custom<span class="text-danger">*</span> </label>
                <div class="col-lg-10">
                    <select name="" id="scan_interval" class="select2-option form-control">
                        <option value="1" disabled selected>Choose Select</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Category Rule</th>
                            <th>Rule Name</th>
                            <th>Description</th>
                            <th>Serverity</th>
                            <th>Ignore</th>
                            <th>Update Date</th>
                            <th>Update By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ruleNameSite as $item)
                            <tr>
                                <td>{{ $item -> category_name }}</td>
                                <td>{{ $item -> rule_name }}</td>
                                <td>
                                    {{ $item -> description }}
                                </td>
                                <td>{{  $item -> severity }}</td>
                                <td>
                                    <label>
                                        <input name="" value="1" id="-all" type="checkbox" class="">
                                        <span class="label-text"></span>
                                    </label>
                                </td>
                                <td>{{  $item -> created_at }}</td>
                                <td>-</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            {!! closeModalButton() !!}
            <button type="submit" class="btn btn-info formSaving btn-rounded"><i class="fas fa-paper-plane"></i>Submit</button>
        </div>
        
        {!! Form::close() !!}
    </div>
</div>

<script>

    $('#extentions').change(function(){
        if($(this).val() == 'Y'){
            $('#custom_select').hide();
        }else{
            $('#custom_select').show();
        }
    });

    var form_save = '.formSaving';
    $('#form_delete_agent').submit(function (event) {
        event.preventDefault();

        $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
        $('.formSaving').attr('disabled',true);
    
        var data = new FormData(this);
        
        axios.post($(this).attr("action"), data)
            .then(function (response) {
                toastr.success('Deleted Successfully');
                $(form_save).html('<i class="fas fa-paper-plane"></i>  @langapp('save') </span>');
                window.location.href = response.data.redirect;
            })
            .catch(function (error) {
                if(error.response.data.exception)
                {
                    $('.formSaving').attr('disabled',false);
                    toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }
                else
                {
                    $('.formSaving').attr('disabled',false);
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

