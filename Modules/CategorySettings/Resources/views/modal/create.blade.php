<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> New Category </h4>
        </div>
        {!! Form::open(['route' => 'categorysettings.api.save', 'class' => 'ajaxifyForm_custom validator', 'novalidate' => '', 'files' => true]) !!}

        <div class="modal-body">
            <div class="form-group row">
                <label class="col-lg-3 control-label">Name <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <div class="">
                        <input type="text" class="form-control" name="name" value="">
                        
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Status </label>
                <div class="col-lg-6">
                    <label class="switch">
                        <input type="hidden" value="FALSE" name="">
                        <input type="checkbox" checked name="active" value="1">
                        <span></span>
                    </label>
                </div>
            </div>
        </div>

        {{-- @include('partial.privacy_consent') --}}
        
        <div class="modal-footer">
            {!! closeModalButton() !!}
            {!! renderAjaxButton() !!}
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

