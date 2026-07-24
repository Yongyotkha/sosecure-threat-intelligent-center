<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-danger">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">@langapp('delete')</h4>
        </div>

        {!! Form::open(['route' => ['socialdatas.delete_dataleakdata',$code], 'class' => 'ajaxifyForm_custom', 'method' => 'GET']) !!}

        <div class="modal-body">
            <p class="text-danger">@langapp('delete_warning')  </p>


        </div>
        <div class="modal-footer">

            {!! closeModalButton() !!}
            {!! renderAjaxButton('ok') !!}

        </div>
        
        {!! Form::close() !!}
    </div>
</div>

<script>
        $('.ajaxifyForm_custom').submit(function (event) {
        event.preventDefault();

        $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
        $('.btn').attr('disabled',true);
        
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
                $(form_save).html('<i class="fas fa-paper-plane"></i>  @langapp('save') </span>');
                $('.btn').attr('disabled', false);
                $('#ajaxModal').modal('hide');
                if (typeof window.refreshDataLeakView === 'function') {
                    window.refreshDataLeakView();
                } else if ($.fn.DataTable.isDataTable('#table_social_datas')) {
                    $('#table_social_datas').DataTable().ajax.reload(null, false);
                }
        })
        .catch(function (error) {
            if(error.response.data.exception){
                $('.btn').attr('disabled',false);
                toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
                $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
            }else{
                $('.btn').attr('disabled',false);
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
