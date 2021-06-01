<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white">
                <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                New User
            </h4>
        </div>
       
        {!! Form::open(['route' => ['user.save',$code], 'class' => 'ajaxifyForm_custom','files' => true]) !!}
        <div class="modal-body">
            {{-- <div class="form-group row">
                <label class="col-lg-3 control-label">Username (e-mail) <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <input type="email" class="form-control" name="username">
                </div>
            </div> --}}
            <div class="form-group row">
                <label class="col-lg-3 control-label">Name <!--<span class="text-danger">*</span>--> </label>
                <div class="col-lg-9">
                    <input type="text" class="form-control" name="name" required>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Email <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <input type="email" class="form-control" name="email" required>
                </div>
            </div>
            {{-- <div class="form-group row">
                <label class="col-lg-3 control-label">Set Password <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <input type="password" class="form-control" name="password">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Re-enter Password <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <input type="password" class="form-control" name="password_re">
                </div>
            </div> --}}
            <div class="form-group row">
                <label class="col-lg-3 control-label">Role <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <select name="role_id" id="role" class="select2-option form-control" required>
                        {{-- <option value="1">Admin</option>
                        <option value="2">User</option> --}}

                        @if($Roles)
                            @foreach($Roles as $roles_val)
                                <option value="{{$roles_val['id']}}" >{{$roles_val['name']}}</option>
                            @endforeach
                        @endif


                        {{-- <option value="3">Customer</option> --}}
                    </select>
                </div>
            </div>





            <div class="form-group row">
                <label class="col-lg-3 control-label">Permission Menu <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <ul class="role-group">
                        @php  $i=1;  @endphp
                        @foreach(@$menus AS $menu)
                        <li>
                            <div class="role-main">
                                <span class="role-click" onclick="openrole(this,'role-{{$i}}')">@if(count($menu->get_menu_sub) > 0)@icon('solid/plus')@else <i class="fas fa-minus icon"></i>  @endif</span>
                                <span class="checkbox chk-inline">
                                    <label>
                                        <input type="checkbox" name="menu[]" value="{{$menu->code}}">
                                        <span class="label-text" data-rel="tooltip" title="">{{$menu->name}}</span>
                                    </label>
                                </span>
                            </div>
                            
                            @if(!empty($Menu_sub))
                                <ul id="role-{{$i}}" class="role-group-sub">
                                @foreach(@$Menu_sub as $menu_sub) 
                                    <li>
                                        <div class="role-sub">
                                            <span class="checkbox chk-inline">
                                                <label>
                                                    <input type="checkbox" name="menu_sub[]" value="{{$menu_sub->code}}">
                                                    <span class="label-text" data-rel="tooltip" title="">{{$menu_sub->name}}</span>
                                                </label>
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                                </ul>
                            @endif
                            

                        </li>
                        @php $i++; @endphp
                        @endforeach
                        
                    </ul>
                </div>
            </div>


            <div class="form-group row">
                <label class="col-lg-3 control-label"> Login Expire Date <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <div class="input-group">
                        <input type="text" class="form-control" value="90" name="password_days_expire">
                        <span class="input-group-addon">Day</span>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-3 control-label">Status </label>
                <div class="col-lg-9">
                    <label class="switch">
                        <input type="hidden" value="FALSE" name="">
                        <input type="checkbox" name="active" value="TRUE" checked>
                        <span></span>
                    </label>
                </div>
            </div>
        </div>

        {{-- @include('partial.privacy_consent') --}}
        
        <div class="modal-footer">
            {!! Form::close() !!}
            {{-- <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                <i class="fas fa-times"></i>
                Close
            </button> --}}
            {!! closeModalButton() !!}
            {{-- <button type="button" class="btn btn-success btn-rounded">
                <i class="fas fa-play"></i>
                Run Scan And Save Now
            </button> --}}

            {{-- <button type="submit" class="btn btn-info btn-rounded">
                <i class="fas fa-paper-plane"></i>
                Save
            </button> --}}
            {!! renderAjaxButton("Create_User") !!}
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

        function openrole(onck,id){
            $('#'+id).slideToggle(150);
        }

        $(document).ready(function () {
            $('#role').select2();
        });


   
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
                            $(form_save).html('<i class="fas fa-check"></i> @langapp('save') </span>');
                            window.location.href = response.data.redirect;
                })
                .catch(function (error) {
                    $('.formSaving').attr('disabled',false);
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
