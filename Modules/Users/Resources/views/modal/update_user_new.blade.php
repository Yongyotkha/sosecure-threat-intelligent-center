<div class="modal-dialog modal-dialog-aside">
        <div class="modal-content">
            <div class="modal-header bg-blue">
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                <h4 class="modal-title text-white">@icon('solid/plus') @langapp('create')  </h4>
            </div>
    
    
            <div class="modal-body">
    
                <div class="panel-body">
    
                {!! Form::open(['route' => ['users.api.update_process', $user->id], 'class' => 'bs-example form-horizontal ajaxifyForm_custom', 'method' => 'PUT']) !!}
                   
                    <input class="display-none" value="{{$user->code}}" type="hidden" name="user_code"/>
                    {{-- <input class="display-none" type="hidden" name="username"/>
                    <input class="display-none" type="hidden" name="password"/> --}}
    
    
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-12">
                                <label>Name @required</label>
                                <input type="text" name="name" value="{{$user->name}}" class="form-control" required>
                            </div>
                        </div>
                    </div>
    
    
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-12">
                                <label>@langapp('email') @required</label>
                                <input type="email" name="email" value="{{$user->email}}" class="form-control" placeholder="you@domain.com" required>
                            </div>
                        </div>
                    </div>
    
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="display-block">@langapp('roles')</label>
                            <select name="role_id" id="role_select" class="select2-option form-control" onchange="check_role(value)" ><!--multiple="multiple"-->
                                <option value="" selected>- SELECT ROLE -</option>
                                @foreach (Role::whereNotIn('id', [3])->get() as $role)
                                    <option value="{{ $role->id }}" {{  $role->id == @$role_id ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group" id="area_select_site_multi" style="display: none;">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="display-block">Site</label>
                            <select name="site_multi[]" id="select-site_multi" class="select2-option form-control select-site" multiple="multiple">
                                {{-- <option value="">Select Site</option> --}}
                                @if($SiteSettings)
                                @foreach($SiteSettings as $SiteSettings_val)
                                <option {{in_array($SiteSettings_val->id, $site_id_arr) ? 'selected':''}} value="{{$SiteSettings_val->id}}">{{$SiteSettings_val->name}}</option>
                                @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group" id="area_select_site" style="display: none;">
                    <div class="row">

                        <div class="col-md-12">

                            <label class="display-block">Site</label>
                            {{-- <select name="site[]" class="select2-option form-control" multiple="multiple"><!--multiple="multiple"-->
                                @foreach (Modules\SiteSettings\Entities\SiteSettings::select()->get() as $role)
                                    <option value="{{ $role->name }}" {{  $role->name == get_option('default_role') ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                                @endforeach
                            </select> --}}


                            <select name="site" id="select-site" class="select2-option form-control select-site" disabled>
                                {{-- <option value="">Select Site</option> --}}
                                @if($SiteSettings)
                                @foreach($SiteSettings as $SiteSettings_val)
                                <option {{in_array($SiteSettings_val->id, $site_id_arr) ? 'selected':''}} value="{{$SiteSettings_val->id}}">{{$SiteSettings_val->name}}</option>
                                @endforeach
                                @endif
                            </select>

                        </div>
                    </div>
                </div>
                <button class="btn btn-info mb-2" type="button" id="ch_pass" data-val="0" data-toggle="collapse" data-target="#collapse_ch_pass" aria-expanded="false" aria-controls="collapse_ch_pass">
                    Edit Password
                </button>
    
                <div class="collapse" id="collapse_ch_pass">
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Set Password <span class="text-danger">*</span> </label>
                        <div class="col-lg-8">
                            <input type="password" class="form-control" name="password" id="password" minlength="6" maxlength="20" disabled>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Re-enter Password <span class="text-danger">*</span> </label>
                        <div class="col-lg-8">
                            <input type="password" class="form-control" name="password_re" id="password_re" minlength="6" maxlength="20" disabled>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="display-block">Status </label>
                            <div class="col-lg-12">
                                <label class="switch">
                                    <input type="checkbox" name="active" value="TRUE" {{ $user->active == 1 ? 'checked' : '' }}>
                                    <span></span>
                                </label>
                            </div>

                        </div>
                    </div>
                </div>

                

                    @include('partial.privacy_consent')
    
                    <div class="modal-footer">
                        
                        {!! closeModalButton() !!}
                        {!! renderAjaxButton() !!}
    
    
                    </div>
    
                    {!! Form::close() !!}
    
    
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
    {{-- @include('partial/ajaxify') --}}

    <script>

        $(document).ready(function () {
            $('#role').select2();
            $("#ch_pass").click(function() {
                if($(this).data("val")== '0') {
                    click_enabled();
                    $(this).text("Cancel Edit Password");
                    $(this).data("val",1);
                } else {
                    click_disabled();
                    $(this).text("Edit Password");
                    $(this).data("val",0);
                }
            });
            check_role_first({{@json_encode(@$role_id)}});
        });

        
   
        function click_enabled() {
            $("#password").prop("disabled",false);
            $("#password_re").prop("disabled",false);
            $("#password").prop("required",true);
            $("#password_re").prop("required",true);
        }

        function click_disabled() {
            $("#password").prop("disabled",true);
            $("#password_re").prop("disabled",true);
            $("#password").prop("required",false);
            $("#password_re").prop("required",false);
        }

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


        function check_role(val) {

            if(val){
                if(val == 1) {
                    $("#select-site_multi").val('').trigger('change').prop("disabled",true);
                    $("#area_select_site_multi").css("display","none");
                    $("#select-site").val('').trigger('change').prop("disabled",true);
                    $("#area_select_site").css("display","none");
                } else if(val == 4 || val == 5 || val == 6) {
                    $("#select-site_multi").val('').trigger('change').prop("disabled",true);
                    $("#area_select_site_multi").css("display","none");

                    $("#select-site").val('').trigger('change').prop("disabled",false);
                    $("#area_select_site").css("display","block");
                } else {
                    $("#select-site_multi").val('').trigger('change').prop("disabled",false);
                    $("#area_select_site_multi").css("display","block");

                    $("#select-site").val('').trigger('change').prop("disabled",true);
                    $("#area_select_site").css("display","none");
                }
            }else{
                $("#select-site_multi").val('').trigger('change').prop("disabled",true);
                $("#area_select_site_multi").css("display","none");
                $("#select-site").val('').trigger('change').prop("disabled",true);
                $("#area_select_site").css("display","none");
            }
        }

        function check_role_first(val) {
            if(val){
                if(val == 1) {
                    $("#select-site_multi").val('').trigger('change').prop("disabled",true);
                    $("#area_select_site_multi").css("display","none");
                    $("#select-site").val('').trigger('change').prop("disabled",true);
                    $("#area_select_site").css("display","none");
                } else if(val == 4 || val == 5 || val == 6) {
                    $("#select-site_multi").val('').trigger('change').prop("disabled",true);
                    $("#select-site").prop("disabled",false);
                    $("#area_select_site_multi").css("display","none");
                    $("#area_select_site").css("display","block");
                } else {
                    $("#select-site").val('').trigger('change').prop("disabled",true);
                    $("#select-site_multi").prop("disabled",false);
                    $("#area_select_site_multi").css("display","block");
                    $("#area_select_site").css("display","none");
                }
            }
            

        }

    </script>


    
    @endpush
    
    @stack('pagestyle')
    @stack('pagescript')