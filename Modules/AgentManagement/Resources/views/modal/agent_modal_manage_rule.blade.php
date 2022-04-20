<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-info">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Manage Rule</h4>
        </div>
        {!! Form::open() !!}
        <div class="modal-body">
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
            </table>
        </div>
        <div class="modal-footer">
            {!! closeModalButton() !!}
            <button type="submit" class="btn btn-info formSaving btn-rounded"><i class="fas fa-paper-plane"></i>Submit</button>
        </div>
        
        {!! Form::close() !!}
    </div>
</div>

<script>
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

