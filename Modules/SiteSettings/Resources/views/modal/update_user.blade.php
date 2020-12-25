<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> @langapp('make_changes')  - {{ $user->name }}</h4>
        </div>
        {!! Form::open(['route' => ['user.update', 'id' => $user->code], 'class' => 'ajaxifyForm validator', 'novalidate' => '', 'method' => 'PUT', 'files' => true]) !!}

        <input type="hidden" name="id" value="{{  $user->id  }}">

        <div class="modal-body">


            <div class="form-group row">
                <label class="col-lg-4 control-label">Username (e-mail) <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <input type="email" class="form-control" name="username" value="<?=@$user->email;?>">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-4 control-label">Name <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <input type="text" class="form-control" name="name" value="<?=@$user->name;?>">
                </div>
            </div>

            <button class="btn btn-primary" type="button" id="ch_pass" data-val="0" data-toggle="collapse" data-target="#collapse_ch_pass" aria-expanded="false" aria-controls="collapse_ch_pass">
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

            <div class="form-group row">
                <label class="col-lg-4 control-label">Role <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <select name="role_id" id="role" class="select2-option form-control">
                        {{-- <option value="1">Admin</option>
                        <option value="2">User</option>
                        <option value="3">Customer</option> --}}
                        @if($Roles)
                            @foreach($Roles as $roles_val)
                                <option value="{{$roles_val['id']}}"
                                @if(@$roles_select == $roles_val['id'])
                                selected
                                @endif
                                >{{$roles_val['name']}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-4 control-label">Status </label>
                <div class="col-lg-8">
                    <label class="switch">
                        <input type="hidden" value="FALSE" name="">
                        <input type="checkbox" name="active" value="TRUE" checked>
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
@include('partial.ajaxify')

    <script>
        $("#ch_pass").click(function() {
            if($(this).data("val")== '0') {
                click_enabled()
                $(this).text("Cancel Edit Password");
                $(this).data("val",1);
            } else {
                click_disabled()
                $(this).text("Edit Password");
                $(this).data("val",0);
            }
        });


        function click_enabled() {
            // alert(1);
            $("#password").prop("disabled",false);
            $("#password_re").prop("disabled",false);
            $("#password").prop("required",true);
            $("#password_re").prop("required",true);

        }
        function click_disabled() {
            // alert(0);
            $("#password").prop("disabled",true);
            $("#password_re").prop("disabled",true);
            $("#password").prop("required",false);
            $("#password_re").prop("required",false);
        }
    </script>
@endpush

@stack('pagestyle')
@stack('pagescript')