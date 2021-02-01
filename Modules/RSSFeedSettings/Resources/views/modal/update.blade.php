<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> @langapp('make_changes')  - {{ $rssfeedsettings->name }}</h4>
        </div>
        {!! Form::open(['route' => ['rssfeedsettings.update', 'id' => $rssfeedsettings->code], 'class' => 'ajaxifyForm_custom', 'method' => 'PUT', 'files' => false]) !!}

        <input type="hidden" name="id" value="{{  $rssfeedsettings->code  }}">

        <div class="modal-body">

            <div class="form-group row">
                <label class="col-lg-3 control-label">Name <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <input type="text" id="name_rss" name="name_rss" class="form-control" value="{{@$rssfeedsettings->name}}" required>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">URL
                    <span data-rel="tooltip" title="" data-original-title="URL"> <i class="far fa-question-circle"></i></span>
                    <span class="text-danger">*</span>
                </label>
                <div class="col-lg-9">
                    <input type="url" class="form-control" id="url_rss" name="url_rss" value="{{@$rssfeedsettings->url}}" required>
                </div>
            </div>

            
            <div class="form-group row">
                <label class="col-lg-3 control-label">Status </label>
                <div class="col-lg-6">
                    <label class="switch">
                        <input type="checkbox" name="active" value="TRUE" {{$rssfeedsettings->status == 1 ? 'checked' : ''}}>
                        <span></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            {!! closeModalButton() !!}
            <button type="submit" class="btn btn-info formSaving btn-rounded"><i class="fas fa-paper-plane"></i>Save</button>
        </div>
        {!! Form::close() !!}
</div>

@push('pagestyle')
@include('stacks.css.form')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.fullscreen')
<script>
    var form_save = '.formSaving';
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
@endpush

@stack('pagestyle')
@stack('pagescript')
