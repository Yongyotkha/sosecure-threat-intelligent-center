<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Edit Data Leak</h4>
        </div>
    {!! Form::open(['route' => ['dataleak.edit_dataleak'], 'class' => 'ajaxifyForm_custom', 'files' => false]) !!}
        <div class="modal-body">
            <input type="hidden" name="id_DataLeakFeed" class="form-control" value="{{$DataLeakFeed->id}}">
            @if ($site)
            <input type="hidden" name="site_code" class="form-control" value="{{$site}}">   
            @endif
            <div class="form-group row">
                <label class="col-lg-3 control-label">Type <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <select name="type" id="type" class="select2-option form-control" required>
                        <option value="social">Public</option>
                        <option value="darkweb_public">Dark Web</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Keyword Ref <span class="text-danger">*</span></label>
                <div class="col-lg-9">
                    <input type="text" name="keyword" class="form-control" value="{{$DataLeakFeed->keyword}}">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Content<span class="text-danger">*</span></label>
                <div class="col-lg-9">
                    
                    <textarea  class="form-control htmleditor" id="content" name="content"  data-id="1" required >
                    {{$DataLeakFeed->feedcontent}}
                    </textarea>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Remark<span class="text-danger">*</span></label>
                <div class="col-lg-9">
                    <textarea  class="form-control htmleditor" id="remark" name="remark" data-id="1" required>
                        {{$DataLeakFeed->source_name}}
                    </textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="" class="col-md-3">Send Mail</label>
                <div class="col-md-9">
                    <label>
                        <input type="checkbox" name="sent_mail" id="sent_mail" class="" value="true">
                        <span class="label-text">Sent mail to customers</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            {!! closeModalButton() !!}
            {!! renderAjaxButton() !!}
        </div>
    {!! Form::close() !!}
    </div>
</div>

@push('pagestyle')
@include('stacks.css.form')
@include('stacks.css.summernote')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.fullscreen')
@include('scripts.summernote')
@include('stacks.js.markdown')
<script>

    $('#type').val(@json($DataLeakFeed->feel_type));

    $('form').each(function () {
        if ($(this).data('validator'))
            $(this).data('validator').settings.ignore = ".note-editor *";
    }); 
    $(document).ready(function () {
        $('.select2-option').select2();
    });
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
                window.location.href = response.data.redirect;
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
@endpush

@stack('pagestyle')
@stack('pagescript')