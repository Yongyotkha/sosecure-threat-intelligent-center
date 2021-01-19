@extends('layouts.auth')
@section('content')
<section id="content">
    {{-- <div id="login-darken"></div> --}}
    <div id="login-form" class="container aside-xxl animated fadeInUp">
        <div class="box-login-shadow  bd-round-b-lr block bd-round-t-lr">
        <span class="nav-header-login navbar-brand">
            <img src="{{asset('images/logo_threat/logo_site.png')}}" class="logo-sosecure mt-2">
            @php $display = get_option('logo_or_icon'); @endphp
        </span>
        <section class="panel-default bg-white panel-login">
 
            
            {!! Form::open(['route' => 'password.request', 'class' => 'panel-body wrapper-lg']) !!}
            <div class="login-body">
                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                    <label for="email">@langapp('email')</label>

                    <input id="email" type="email" class="form-control" name="email" placeholder="you@domain.com" value="{{ old('email') }}" required autofocus>

                    @if ($errors->has('email'))
                        <span class="help-block">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    <label for="password">@langapp('password')</label>

                    <input id="password" type="password" class="input-login form-control" name="password" required>

                    @if ($errors->has('password'))
                        <span class="help-block">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                    
                </div>

                <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                    <label for="password-confirm">@langapp('confirm_password')</label>
                    
                        <input id="password-confirm" type="password" class="input-login form-control" name="password_confirmation" required>
                        @if ($errors->has('password_confirmation'))
                            <span class="help-block">
                                <strong>{{ $errors->first('password_confirmation') }}</strong>
                            </span>
                        @endif
                </div>



                @if (settingEnabled('social_login'))
                    <div class="line line-dashed"></div>
                    <p id="social-buttons">
                        <a href="{{url('/redirect/twitter')}}" class="btn btn-sm btn-icon btn-{{ get_option('theme_color') }} m-xs" data-rel="tooltip" title="Login using Twitter">@icon('brands/twitter')</a>
                        <a href="{{url('/redirect/facebook')}}" class="btn btn-sm btn-icon btn-{{ get_option('theme_color') }} m-xs" data-rel="tooltip" title="Login using Facebook">@icon('brands/facebook')</a>
                        <a href="{{url('/redirect/google')}}" class="btn btn-sm btn-icon btn-{{ get_option('theme_color') }} m-xs" data-rel="tooltip" title="Login using Google">@icon('brands/google')</a>
                        <a href="{{url('/redirect/github')}}" class="btn btn-sm btn-icon btn-{{ get_option('theme_color') }} m-xs" data-rel="tooltip" title="Login using Github">@icon('brands/github')</a>
                        <a href="{{url('/redirect/linkedin')}}" class="btn btn-sm btn-icon btn-{{ get_option('theme_color') }} m-xs" data-rel="tooltip" title="Login using LinkedIn">@icon('brands/linkedin')</a>
                        <a href="{{url('/redirect/gitlab')}}" class="btn btn-sm btn-icon btn-{{ get_option('theme_color') }} m-xs" data-rel="tooltip" title="Login using Gitlab">@icon('brands/gitlab')</a>
                    </p>
                @endif
                <div class="line line-dashed"></div>

                {{-- @if (settingEnabled('allow_client_registration'))
                <p class="text-muted text-center">
                    <small>@langapp('do_not_have_an_account') </small>
                </p>
                <a href="{{ url('/register') }}"
                class="btn btn-{{ get_option('theme_color') }} btn-block">@langapp('get_your_account') </a>
                @endif --}}
            </div>
                <div>
                    {{-- {!! renderButton(langapp('sign_in')) !!} --}}
                    <button type="submit" class="btn-login-cus bd-round-b-lr btn btn-info btn-block formSaving submit">@langapp('reset_password')</button>        
                </div>
            {!! Form::close() !!}
            
            {{-- Footer --}}
            @if (!settingEnabled('hide_branding'))
            @include('partial.branding')
            @endif
            {{-- /Footer --}}
        </section>
    </div>    
        <a class="btn btn-link d-block text-center mt-2" href="{{ route('password.request') }}">
            @langapp('forgot_password')
        </a>
    </div>
</section>
@endsection