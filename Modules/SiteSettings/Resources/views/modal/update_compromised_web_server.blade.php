<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();"
                    datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> @langapp('make_changes')</h4>
        </div>

        {!! Form::open(['route' => ['compromised_web_server.web_server_edit', 'id' => $CompromisedServer->id], 'class'
        => 'ajaxifyForm validator ajaxifyForm_custom', 'novalidate' => '', 'method' => 'POST', 'files' => true]) !!}



        <div class="modal-body">
            
            <input type="hidden" id="site" name="site"  value="{{@$CompromisedServer->site_id}}">

            <div class="form-group row">
                <label style="padding-top: 7px" class="col-lg-3 control-label">IP <span class="text-danger">*</span>
                </label>
                <div class="col-lg-8">
                    <input type="text" id="ip" name="ip" class="form-control" value="{{@$CompromisedServer->ip}}"
                        required>
                </div>
            </div>
            <div class="form-group row">
                <label style="padding-top: 7px" class="col-lg-3 control-label">User <span class="text-danger">*</span>
                </label>
                <div class="col-lg-8">
                    <input type="text" id="user" name="user" class="form-control" value="{{@$CompromisedServer->user}}"
                        required>
                </div>
            </div>
            <div class="form-group row">
                <label style="padding-top: 7px" class="col-lg-3 control-label">Password <span
                        class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <input type="text" id="password" name="password" class="form-control"
                        value="{{@$CompromisedServer->password}}" required>
                </div>
            </div>
            <div class="form-group row">
                <label style="padding-top: 7px" class="col-lg-3 control-label">Root Path <span
                        class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <input type="text" id="root_path" name="root_path" class="form-control"
                        value="{{@$CompromisedServer->path}}" required>
                </div>
            </div>

            <div class="form-group row">
                <label style="padding-top: 7px" class="col-lg-3 control-label">OS<span class="text-danger">*</span>
                </label>
                <div class="col-lg-8">
                    <select id="os" name="os" class="form-control" required>
                        <option value="Linux" {{ $CompromisedServer->os=='Linux'  ? 'selected="selected"' : "" }}>Linux
                        </option>
                        <option value="Windows" {{ $CompromisedServer->os=='Windows'  ? 'selected="selected"' : "" }}>
                            Windows</option>
                    </select>
                </div>
            </div>

            <div class="form-group row" style="padding-top: 7px">
                <label class="col-lg-3 control-label">Status</label>
                <div class="col-lg-8">
                    <label class="switch">
                        <input type="checkbox" id="status" name="status"
                            {{$CompromisedServer->active == 1 ? 'checked' : ''}} value="1">
                        <span></span>
                    </label>
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