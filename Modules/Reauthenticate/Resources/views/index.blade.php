@extends('layouts.auth')

@section('content')

    <section id="content" class="m-t-lg wrapper-md content">
        <div id="login-darken"></div>
        <div id="login-form" class="container aside-xxl animated fadeInUp">
        <span class="" style="display: flex;align-items: center;justify-content: center;text-align: center;"> 
                {{-- <img src="{{ getStorageUrl(config('system.media_dir').'/'.get_option('company_logo'))  }}" class="img-responsive thumb-sm m-r-sm"> --}}
                <img src="{{ site_url('images/logo_threat/logo_site.png')  }}" style="width: 217px;height: 67px;" class="">
        </span>

        <section class="panel panel-default bg-white m-t-lg b-r-cust">
            <header class="panel-heading text-center"><strong>Setting Password</strong> </header>
                

            {{-- <form class="panel-body wrapper-lg" method="POST" action=""> --}}
            {!! Form::open(['route' => ['reauth.verify_update_pass', 'id' => $User->code], 'class' => 'panel-body wrapper-lg ajaxifyForm_custom validator', 'novalidate' => '', 'method' => 'PUT', 'files' => false]) !!}
                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    @php
                    // dd($granted_access);
                        $get_user_site = '';
                        if(@$User->get_user_site(@$User->id)) {
                            foreach($User->get_user_site(@$User->id) as $get_user_site_val) {
                                if(@$get_user_site_val->get_site->name) {
                                    $get_user_site .= $get_user_site_val->get_site->name . ',';
                                }
                            }
                            $get_user_site_all = rtrim($get_user_site,",");

                            if($granted_access_val) {
                                $get_user_site_all = $granted_access_val;
                            } else {

                            }
                        }
                    @endphp
                    <label><b>{{$granted_access}} </b>{{@$get_user_site_all}}</label>
                </div>
                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    <label><b>Username</b></label><br>
                    <label>{{$User->email}}</label>
                </div>
                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    <label><b>@langapp('password')</b><span class="text-danger">*</span></label>
                    <input id="password" type="password" class="form-control" minlength="6" name="password" required>
                </div>
                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    <label><b>Password confirm</b><span class="text-danger">*</span></label>
                    <input id="password_confirm" type="password" class="form-control" minlength="6" name="password_confirm" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success btn-block formSaving"> @icon('solid/unlock-alt') @langapp('confirm')</button>

                    <p class="text-muted m-t-sm">
                        <strong>Tip:</strong> You are entering sudo mode. You will not be asked for your Email for a few hours.</p>
                </div>
                <div class="line line-dashed"> </div>
            {!! Form::close() !!}
            {{-- </form> --}}
                @if (!settingEnabled('hide_branding')) 
                    <footer id="footer" class="copyright-footer">
                        <div class="text-center text-muted padder">
                            <p>
                                @include('partial.copyright')
                            </p>
                        </div>
                    </footer>
                @endif
            </section>
        </div>
    </section>

@endsection




@push('pagestyle')
@include('stacks.css.form')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.fullscreen')
{{-- @include('partial.ajaxify') --}}

    <script>

    var form_save = '.formSaving';
    $('.ajaxifyForm_custom').submit(function (event) {
        event.preventDefault();

            $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            $('.formSaving').attr('disabled',true);
            var data = new FormData(this);
            if(form_save == '.formSavingAndRun'){
                data.append('formsubmit', 'formSavingAndRun');
            } else if(form_save == '.formPreview'){
                data.append('formsubmit', 'formPreview');
            } else if(form_save == '.formDraft'){
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


