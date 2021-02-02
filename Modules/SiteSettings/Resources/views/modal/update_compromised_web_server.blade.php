<style>
    .block {
        display: block;
        width: 100%;
        border: none;


        font-size: 16px;
        cursor: pointer;
        text-align: center;
    }   
</style>

<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();"
                    datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> @langapp('make_changes')</h4>
        </div>

        {{-- {!! Form::open(['route' => ['compromised_web_server.web_server_edit', 'id' => $CompromisedServer->id], 'class'
        => 'ajaxifyForm validator ajaxifyForm_custom', 'novalidate' => '', 'method' => 'POST', 'files' => true]) !!} --}}


        <form onsubmit="add_asset_click_edit()">
            <div class="modal-body">

                <input type="hidden" name="site" id="site_edit" value="{{@$CompromisedServer->site_id}}">

                <div class="form-group row">
                    <label style="padding-top: 7px" class="col-lg-3 control-label">OS<span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-8">
                        <select name="os" id="os_edit" class="form-control check_test_select" required>
                            <option value="Linux" {{ $CompromisedServer->os=='Linux'  ? 'selected="selected"' : "" }}>
                                Linux
                            </option>
                            <option value="Windows"
                                {{ $CompromisedServer->os=='Windows'  ? 'selected="selected"' : "" }}>
                                Windows</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label style="padding-top: 7px" class="col-lg-3 control-label">IP <span class="text-danger">*</span>
                    </label>
                    <div class="col-lg-8">
                        <input type="text" name="ip" id="ip_edit" class="form-control check_test"
                            value="{{@$CompromisedServer->ip}}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label style="padding-top: 7px" class="col-lg-3 control-label">Port <span
                            class="text-danger">*</span>
                    </label>
                    <div class="col-lg-8">
                        <input type="text" name="port" id="port_edit" class="form-control check_test"
                            value="{{@$CompromisedServer->port}}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label style="padding-top: 7px" class="col-lg-3 control-label"> </label>
                    
                    <div class="col-lg-8">
                        <button type="button" class="btn btn-{{ get_option('theme_color')  }} block"
                            onclick="test_data_edit()">Test Connection</button>
                    </div>
                </div>

                <div class="form-group row">
                    <label style="padding-top: 7px" class="col-lg-3 control-label">Root Path <span
                            class="text-danger">*</span> </label>
                    <div class="col-lg-8">
                        <input type="text" id="root_path_edit" name="root_path" class="form-control"
                            value="{{@$CompromisedServer->path}}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label style="padding-top: 7px" class="col-lg-3 control-label">Type Extension<span
                            class="text-danger">*</span></label>
                    <div class="col-lg-8">
                        <select class="js-example-basic-multiple"  name="type" id="type_edit" multiple="multiple"
                            required>
                            <option value=".php">.PHP</option>
                            <option value=".js">.JS</option>
                            <option value=".asp">.ASP</option>
                            <option value=".exe">.EXE</option>
                            <option value=".dll">.DLL</option>
                            <option value=".cs">.CS</option>
                            <option value=".cshtml">.CSHTML</option>
                            <option value=".config">.CONFIG</option>
                            <option value=".htaccess">.HTACCESS</option>
                            <option value=".xml">.XML</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row" style="padding-top: 7px">
                    <label class="col-lg-3 control-label">Status</label>
                    <div class="col-lg-8">
                        <label class="switch">
                            <input type="checkbox" name="status_edit" id="status_edit"
                                {{$CompromisedServer->active == 1 ? 'checked' : ''}} value="1">
                            <span></span>
                        </label>
                    </div>
                </div>
                {{-- {!! Form::close() !!} --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                    Close
                </button>
                <button type="submit" value="Submit" class="btn btn-info btn-rounded" id="button_save_edit">
                    <i class="fas fa-paper-plane"></i>
                    Save
                    {{-- Yes, approve --}}
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
    $(document).ready(function(){
        $('.js-example-basic-multiple').select2();
    
    });
    $('#type_edit').val(@json($type));


    $(function() {
        $(".check_test").keypress(function() {

            $('#button_save_edit').prop("disabled", true);

        });

        $(".check_test_select").change(function() {

            $('#button_save_edit').prop("disabled", true);

        });


        
        
        
    });

    var check_edit = {{$CompromisedServer->active}};

    $("#status_edit").on('change', function() {
        if ($(this).is(':checked')) {
            $(this).attr('value', '1');
        } else {
            $(this).attr('value', '0');
        }
        
        check_edit = $('#status_edit').val();
        


    });

    
    

    function add_asset_click_edit() {
        var ip_edit = $('#ip_edit').val();
        var port_edit = $('#port_edit').val();
        var user_edit = $('#user_edit').val();
        var password_edit = $('#password_edit').val();
        var root_path_edit = $('#root_path_edit').val();
        var os_edit = $('#os_edit').val();
        var type_edit = $('#type_edit').val();
        var compromised_id = {{$CompromisedServer->id}};
        $.ajax({
            type:"POST",
            url:`${base_url}/compromised_web_server/web_server_edit/${compromised_id}`,
            data:{
                status:Number(check_edit),
                os:os_edit,
                root_path:root_path_edit,
                password:password_edit,
                user:user_edit,
                ip:ip_edit,
                port:port_edit,
                site:{{@$CompromisedServer->site_id}},
                type:type_edit.join(),
            },
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
                $('#button_save_edit').prop("disabled", true);
                loading('stop_load');
                toastr.success(response.message, '@langapp('response_status')');
                window.location.href = response.redirect;
            },
            error: function (error){
                loading('stop_load');
                $('#button_save_edit').prop("disabled", false);
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status') ');
            }

        });
        
        
    }

    function test_data_edit(){

        ip_edit = $('#ip_edit').val();
        port_edit = $('#port_edit').val();
        user_edit = {{$CompromisedServer->credentials_id}};
        os_edit = $('#os_edit').val();


        $.ajax({
            type:"POST",
            url:"{{ route('compromised_web_server.checkWebserverIP') }}",
            data:{
                ip:ip_edit,
                port:port_edit,
                user:user_edit,
                os:os_edit,
            },
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
                loading('stop_load');
                if(response.webserverConnect==true){
                    toastr.success(response.message, '@langapp('response_status')');
                    $('#button_save_edit').prop("disabled", false);
                   
                }else{
                    toastr.error(response.message, '@langapp('response_status')');
                    
                }
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

   }






    {{-- var form_save = '.formSaving';
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
        
        
            
    });--}}
</script>
@endpush

@stack('pagestyle')
@stack('pagescript')