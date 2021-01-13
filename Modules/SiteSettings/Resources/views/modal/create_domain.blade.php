<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Add Domain </h4>
        </div>
       
        {!! Form::open(['route' => ['domain.save',$code], 'class' => 'ajaxifyForm_custom validator', 'novalidate' => '', 'files' => true]) !!}
        <div class="modal-body">
            <div class="form-group row">
                <label class="col-lg-4 control-label">Name <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <input type="text" name="name" class="form-control">
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-4 control-label">Domain <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <input type="text" name="domain" class="form-control">

                    <!-- Example Domain -->
                    <div class="mt-2">
                        <table class="table table-bordered table-striped">
                            <tr ><td>Example : example.com</td></tr>
                        </table>
                    </div>
                </div>
            </div>


            <div class="form-group row" style="display: none;">
                <label class="col-lg-4 control-label">Open Scan </label>
                <div class="col-lg-8">
                    <label class="switch">
                        <input type="hidden" value="FALSE" name="">
                        <input type="checkbox" name="open_scan" value="TRUE">
                        <span></span>
                    </label>
                </div>
            </div>

            <div class="form-group row" style="display: none;">
                <label class="col-lg-4 control-label"></label>
                <div class="col-lg-8">
                    <ul class="role-group">
                        <li>
                            <div class="role-main">
                                <span class="role-click" onclick="openrole(this,'role-1')">@icon('solid/plus')</span>
                                <span class="checkbox chk-inline">
                                    <label>
                                        <input type="checkbox" name="" checked="" value="TRUE">
                                        <span class="label-text" data-rel="tooltip" title="">Content Analysis</span>
                                    </label>
                                </span>
                            </div>
                            <ul id="role-1" class="role-group-sub">
                                <li>
                                    <div class="role-sub">
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                            </label>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <div class="role-main">
                                <span class="role-click" onclick="openrole(this,'role-2')">@icon('solid/plus')</span>
                                <span class="checkbox chk-inline">
                                    <label>
                                        <input type="checkbox" name="" checked="" value="TRUE">
                                        <span class="label-text" data-rel="tooltip" title="">Crawling and Scanning</span>
                                    </label>
                                </span>
                            </div>
                            <ul id="role-2" class="role-group-sub">
                                <li>
                                    <div class="role-sub">
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                            </label>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <div class="role-main">
                                <span class="role-click" onclick="openrole(this,'role-3')">@icon('solid/plus')</span>
                                <span class="checkbox chk-inline">
                                    <label>
                                        <input type="checkbox" name="" checked="" value="TRUE">
                                        <span class="label-text" data-rel="tooltip" title="">DNS</span>
                                    </label>
                                </span>
                            </div>
                            <ul id="role-3" class="role-group-sub">
                                <li>
                                    <div class="role-sub">
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                            </label>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <div class="role-main">
                                <span class="role-click" onclick="openrole(this,'role-4')">@icon('solid/plus')</span>
                                <span class="checkbox chk-inline">
                                    <label>
                                        <input type="checkbox" name="" checked="" value="TRUE">
                                        <span class="label-text" data-rel="tooltip" title="">Leaks, Dumps and Breaches</span>
                                    </label>
                                </span>
                            </div>
                            <ul id="role-4" class="role-group-sub">
                                <li>
                                    <div class="role-sub">
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                            </label>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <div class="role-main">
                                <span class="role-click" onclick="openrole(this,'role-5')">@icon('solid/plus')</span>
                                <span class="checkbox chk-inline">
                                    <label>
                                        <input type="checkbox" name="" checked="" value="TRUE">
                                        <span class="label-text" data-rel="tooltip" title="">Passive DNS</span>
                                    </label>
                                </span>
                            </div>
                            <ul id="role-5" class="role-group-sub">
                                <li>
                                    <div class="role-sub">
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                            </label>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <div class="role-main">
                                <span class="role-click" onclick="openrole(this,'role-6')">@icon('solid/plus')</span>
                                <span class="checkbox chk-inline">
                                    <label>
                                        <input type="checkbox" name="" checked="" value="TRUE">
                                        <span class="label-text" data-rel="tooltip" title="">Real World</span>
                                    </label>
                                </span>
                            </div>
                            <ul id="role-6" class="role-group-sub">
                                <li>
                                    <div class="role-sub">
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                            </label>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <div class="role-main">
                                <span class="role-click" onclick="openrole(this,'role-7')">@icon('solid/plus')</span>
                                <span class="checkbox chk-inline">
                                    <label>
                                        <input type="checkbox" name="" checked="" value="TRUE">
                                        <span class="label-text" data-rel="tooltip" title="">Social Media</span>
                                    </label>
                                </span>
                            </div>
                            <ul id="role-7" class="role-group-sub">
                                <li>
                                    <div class="role-sub">
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                            </label>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>


            <div class="form-group row" style="display: none;">
                <label class="col-lg-4 control-label">Scan Interval <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <select name="" id="scan_interval" class="select2-option form-control" multiple>
                        <option value="1">15</option>
                        <option value="2">30</option>
                        <option value="3">60</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group row">
                <label class="col-lg-4 control-label">Default </label>
                <div class="col-lg-8">
                    <label class="switch">
                        <input type="hidden" value="FALSE" name="">
                        <input type="checkbox" name="default" value="TRUE">
                        <span></span>
                    </label>
                </div>
            </div>
            
            <div class="form-group row">
                <label class="col-lg-4 control-label">Status </label>
                <div class="col-lg-8">
                    <label class="switch">
                        <input type="hidden" value="FALSE" name="">
                        <input type="checkbox" name="status" checked value="TRUE">
                        <span></span>
                    </label>
                </div>
            </div>

        </div>

        {{-- @include('partial.privacy_consent') --}}
        
        <div class="modal-footer">
            {{-- {!! Form::close() !!} --}}
            {{-- <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                <i class="fas fa-times"></i>
                Close
            </button> --}}
            {!! closeModalButton() !!}
            {{-- <button type="submit" class="btn btn-success btn-rounded formSavingAndRun submit">
                <i class="fas fa-play"></i>
                Run Scan And Save Now
            </button> --}}
            {{-- <button type="submit" class="btn btn-info btn-rounded">
                <i class="fas fa-paper-plane"></i>
                Save
            </button> --}}
            {!! renderAjaxButton() !!}
        </div>
        {!! Form::close() !!}
    </div>
</div>

<!-- Modal Crop Image-->
    <div class="modal fade" id="modal_crop_logo" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Cropper</h5>
                    <button type="button" class="close" onclick="close_crop();" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <h1 class="text-center">Crop Image</h1>
                                <div class="img-container">
                                    <img id="crop_img" src="" alt="Picture">
                                </div>
                            </div>
                            <div class="col-md-12 text-center">
                                <h1>Preview Company Logo</h1>
                                <div class="preview_logo"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-rounded" onclick="close_crop();"> <i class="fas fa-times text-muted"></i> Close</button>
                    <button type="button" class="btn btn-info btn-rounded" id="crop"><i class="fas fa-check"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('pagestyle')
@include('stacks.css.form')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.fullscreen')
{{-- @include('partial.ajaxify') --}}

    {{-- Crop Images --}}
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
