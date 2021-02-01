<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white">@langapp('delete')   {{  $categorySettings->name  }}</h4>
        </div>

        {!! Form::open(['route' => ['categorysettings.delete_process', $categorySettings->id], 'class' => 'ajaxifyForm_custom', 'method' => 'DELETE']) !!}

        <div class="modal-body">
            <p class="text-danger">@langapp('delete_warning')  </p>

            <input type="hidden" name="checked[]" value="{{  $categorySettings->id  }}">

        </div>
        <div class="modal-footer">

            {!! closeModalButton() !!}
            {!! renderAjaxButton('ok') !!}

        </div>
        
        {!! Form::close() !!}
    </div>
</div>
<script>
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