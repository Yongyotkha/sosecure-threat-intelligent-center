<script>
    var form_save = '.formSaving';
    $('.formSavingAndRun').click(function() {
        form_save = '.formSavingAndRun';
    });
    $('.formPreview').click(function() {
        form_save = '.formPreview';
    });
    $('.formDraft').click(function() {
        form_save = '.formDraft';
    });
    $('.ajaxifyForm').submit(function (event) {
        $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
        event.preventDefault();
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
