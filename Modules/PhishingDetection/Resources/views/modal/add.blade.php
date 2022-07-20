<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white">Add</h4>
        </div>

        <div class="modal-body">

            <div class="form-group">
                <label for="" class="control-label">Site</label>
                <select name="" id="" class="text-left sl-2 form-control">
                    <option value=""></option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="" class="control-label">URL Detection</label>
                <input type="text" class="form-control" placeholder="Site">
            </div>

            <div class="form-group">
                <label for="" class="control-label">URL</label>
                <input type="text" class="form-control" placeholder="Site">
            </div>

            <div class="form-group">
                <label for="" class="control-label">IP Address</label>
                <input type="text" class="form-control" placeholder="Site">
            </div>

            <div class="form-group">
                <label for="" class="control-label">Score : <span class="score-text">0</span></label>
                <input type="range" id="score" name="score" class="form-control" list="tickmarks" min="0" max="10" value="0">
                <datalist id="tickmarks">
                    <option value="1" label="0"></option>
                    <option value="2"></option>
                    <option value="3"></option>
                    <option value="4"></option>
                    <option value="5"></option>
                    <option value="6"></option>
                    <option value="7"></option>
                    <option value="8"></option>
                    <option value="9"></option>
                    <option value="10" label="10"></option>
                </datalist>
            </div>

            <div class="form-group">
                <label for="" class="control-label">Serverity</label>
                <select name="" id="" class="text-left sl-2 form-control">
                    <option value=""></option>
                </select>
            </div>

            <div class="form-group">
                <label class="control-label">Status</label>
                <div>
                    <label class="switch">
                        <input type="checkbox" name="" value="TRUE">
                        <span></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            {!! closeModalButton() !!}
            <button type="submit" class="btn btn-info formSaving btn-rounded"><i class="fas fa-paper-plane"></i> Save</button>
        </div>
        
    </div>
</div>
<script>

        $('.sl-2').select2();
        $('#score').change(function(){
            let score = $(this).val();
            $('.score-text').text(score)
        });
        $('.ajaxifyForm_custom').submit(function (event) {
        event.preventDefault();

            $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            $('.formSaving').attr('disabled',true);
            
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
                    window.location.href = response.data.redirect;
            })
            .catch(function (error) {
                if(error.response.data.exception){
                    $('.formSaving').attr('disabled',false);
                    toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }else{
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
