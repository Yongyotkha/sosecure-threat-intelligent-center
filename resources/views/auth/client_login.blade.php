@extends('layouts.auth')
<link rel="stylesheet" href="{{ getAsset('css/custom.css') }}" type="text/css"/>
@section('content')

<section id="content" class="content">
    <div class="login-wrapper login-1">
        <div class="login-inner">
    
            <!-- Side container -->
            <!-- Do not display the container on extra small, small and medium screens -->
            <div class="d-none d-lg-flex col-lg-8 align-items-center ui-bg-cover ui-bg-overlay-container p-5"
                style="background-image: url('https://images.unsplash.com/photo-1544256718-3bcf237f3974?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1051&q=80');">
                <div class="ui-bg-overlay bg-dark opacity-50"></div>
    
                <!-- Text -->
                <div class="w-100 text-white text-login" style="z-index: 1">
                    <h1 class="header-text-login font-weight-bolder mb-4">SOSECURE Threat inSight</h1>
                    <div class="secondary-text-login font-weight-light">
                        Lorem ipsum dolor sit, amet consectetur adipisicing elit. Natus expedita officia voluptas ipsam, fugit id velit repudiandae
                        in placeat eum doloremque vel dicta quam provident magni similique ex dolorem nihil.
                    </div>
                </div>
                <!-- /.Text -->
            </div>
            <!-- / Side container -->
    
            <!-- Form container -->
            <div class="right-login d-flex col-lg-4 align-items-center bg-white pd-login-5">
                <!-- Inner container -->
                <!-- Have to add `.d-flex` to control width via `.col-*` classes -->
                <div class="d-flex col-12 mx-auto">
                    <div class="w-100">
                        <!-- Logo -->
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="position-relative">
                                <div class="p-1">
                                    {{-- @php $display = get_option('logo_or_icon'); @endphp
                                    @if ($display == 'logo' || $display == 'logo_title')
                                    <img src="{{ getStorageUrl(config('system.media_dir').'/'.get_option('company_logo')) }}"
                                    class="img-responsive {{ ($display == 'logo' ? '' : 'thumb-sm m-r-sm') }}">
                                    @elseif ($display == 'icon' || $display == 'icon_title')
                                    <i class="{{ get_option('site_icon') }}"></i>
                                    @endif --}}
                                <img src="{{getAsset('images/logo_threat/logo_site.png')}}" class="img-responsive" alt="" style="max-width: 300px">
                                </div>
                            </div>
                        </div>
                        <!-- / Logo -->
                        <h4 class="text-center text-lighter font-weight-normal mt-login-5 mb-0">เข้าสู่บัญชีของคุณ</h4>
    
                        <!-- Form -->
                        {!! Form::open(['route' => 'login']) !!}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email">@langapp('email')</label>
                            
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
                            @if ($errors->has('email'))
                            <span class="help-block">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                            @endif
                            
                        </div>
                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password">@langapp('password')</label>
                            <input id="password" type="password" class="form-control" name="password" required>
                            @if ($errors->has('password'))
                            <span class="help-block">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                            @endif
                            
                        </div>
                        @if(settingEnabled('use_recaptcha'))
                        {!! NoCaptcha::display() !!}
                        @if ($errors->has('g-recaptcha-response'))
                        <span class="help-block text-danger">
                            <strong>{{ $errors->first('g-recaptcha-response') }}</strong>
                        </span>
                        @endif
                        @endif
                        
                        <div class="form-group">
                            
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> @langapp('remember_me')
                                </label>
                            </div>
                            
                        </div>
                        <div class="form-group">
                            {!! renderButton(langapp('sign_in')) !!}
                            <a class="btn btn-link pull-right m-t-xs" href="{{ route('password.request') }}">
                                @langapp('forgot_password')
                            </a>
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
                        
                        
                        {!! Form::close() !!}
                        <!-- / Form -->
    
                        <div class="text-center text-muted">
                            2020 © Sosecure </div>
                    </div>
                </div>
            </div>
            <!-- / Form container -->
        </div>
    </div>
</section>
@endsection
