<div class="modal-dialog modal-dialog-aside" id="edit_credentials_md">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();"
                    datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> @langapp('make_changes')</h4>
        </div>

        {{-- {!! Form::open(['route' => ['compromised_web_server.web_server_edit', 'id' => $CompromisedServer->id], 'class'
        => 'ajaxifyForm validator ajaxifyForm_custom', 'novalidate' => '', 'method' => 'POST', 'files' => true]) !!} --}}


        <form id='edit_credentials_click' method="POST">
            <div class="modal-body">
                <div class="form-group row">
                    <label class="col-lg-4 control-label">Name <span class="text-danger">*</span> </label>
                    <div class="col-lg-8">
                        <input type="text" name="name" id="edit_name" class="form-control" value="{{$Credentials->name}}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-4 control-label">User <span class="text-danger">*</span> </label>
                    <div class="col-lg-8">
                        <input type="text" name="" id="edit_user" class="form-control" value="{{$Credentials->user}}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-4 control-label">Password <span class="text-danger">*</span> </label>
                    <div class="col-lg-8">
                        <input type="password" name="" id="edit_password" class="form-control" value="{{$Credentials->password}}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-4 control-label">Status </label>
                    <div class="col-lg-8">
                        <label class="switch">
                            <input type="checkbox" id="edit_status" name="status" {{$Credentials->status == 1 ? 'checked' : ''}}   value="1">
                            <span></span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Close
                </button>
                <button type="submit" class="btn btn-info btn-rounded">
                    <i class="fas fa-paper-plane"></i>
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

@push('pagestyle')
@include('stacks.css.form')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.fullscreen')


<script>
 
    var edit_name = null;
    var edit_user = null;
    var edit_password = null;
    var edit_check=null;

    $("#edit_status").on('change', function(e) {
        if ($(this).is(':checked')) {
            $(this).attr('value', '1');
        } else {
            $(this).attr('value', '0');
        }
        
        edit_check= $('#edit_status').val();
  
    });

    $("#edit_credentials_click").submit(function(e) {
        edit_name = $('#edit_name').val();
        edit_user = $('#edit_user').val();
        edit_password = $('#edit_password').val();

        $.ajax({
            type:"POST",
            url:"{{ route('credentials.credentials_edit') }}",
            data:{
                check:Number(edit_check),
                name:edit_name,
                password:edit_password,
                user:edit_user,
                code:@json($code),
            },
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
                window.location.href = response.redirect;
                loading('stop_load');
                toastr.success(response.message, '@langapp('response_status')');
                
                
            },
            error: function (error){
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status') ');

            }

        });
        e.preventDefault();
    });
   
</script>
@endpush

@stack('pagestyle')
@stack('pagescript')