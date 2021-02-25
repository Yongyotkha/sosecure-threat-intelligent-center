@extends('layouts.auth')
@section('content')
@php 
    if(Auth::check()) {
        if(Session::has('check_goto_menu')){
            // dd(123);
            $check_goto_menu = session('check_goto_menu');
            // dd($check_goto_menu);
            header('Location: '.site_url($check_goto_menu));
            dd($check_goto_menu);
        } 
    }
@endphp

<section id="content">
    {{-- <div id="login-darken"></div> --}}
    <div id="login-form" class="container aside-xxl animated fadeInUp">
        <div class="box-login-shadow  bd-round-b-lr block bd-round-t-lr">
        <span class="nav-header-login navbar-brand">
            <img src="{{asset('images/logo_threat/logo_site.png')}}" class="logo-sosecure mt-2">
            @php $display = get_option('logo_or_icon'); @endphp
            {{-- @if ($display == 'logo' || $display == 'logo_title')
            <img src="{{ getStorageUrl(config('system.media_dir').'/'.get_option('company_logo')) }}"
            class="img-responsive logo-sosecure mt-2 {{ ($display == 'logo' ? '' : 'thumb-sm m-r-sm') }}"><br>
            @elseif ($display == 'icon' || $display == 'icon_title')
            <i class="{{ get_option('site_icon') }}"></i>
            @endif
            @if ($display == 'logo_title' || $display == 'icon_title')
            @if (get_option('website_name') == '')
            {{ get_option('company_name') }}
            @else
            {{ get_option('website_name') }}
            @endif
            @endif --}}
        </span>
        <section class="panel-default bg-white panel-login">
            {{-- <header class="panel-heading text-center login-heading">Welcome To {{ get_option('login_title') }}</header> --}}
            {{-- @if (settingEnabled('enable_languages'))
            <div class="panel-body text-right clearfix">
                <div class="btn-group dropdown">
                    <button type="button" class="btn btn-sm dropdown-toggle btn-{{ get_option('theme_color') }}" data-toggle="dropdown" btn-icon="" title="@langapp('languages')  ">
                    @icon('solid/globe')
                    </button>
                    <button type="button" class="btn btn-sm btn-default dropdown-toggle  hidden-nav-xs"
                    data-toggle="dropdown">@langapp('languages')   <span class="caret"></span></button>
                    <!-- Load Languages -->
                    <ul class="dropdown-menu text-left">
                        @foreach (languages() as $lang)
                        @if ($lang['active'] == 1)
                        <li>
                            <a href="{{  route('setLanguage', ['lang' => $lang['code']])  }}"
                                title="{{  ucwords(str_replace('_', ' ', $lang['name']))  }}">
                                {{  ucwords(str_replace('_', ' ', $lang['name']))  }}
                            </a>
                        </li>
                        @endif
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif --}}
            
            {!! Form::open(['route' => 'login', 'class' => '','id' => 'form_login']) !!}
            <div class="login-body">
                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                    <label for="email">@langapp('email')</label>
                    <input id="email" type="email" class="input-login  form-control" name="email" value="{{ old('email') }}" required autofocus>
                    @if ($errors->has('email'))
                    <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                    @endif
                    
                </div>
                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    <label for="password">@langapp('password')</label>
                    <input id="password" type="password" class="input-login  form-control" name="password" required>
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
                    <button type="submit" class="btn-login-cus bd-round-b-lr btn btn-info btn-block formSaving submit">Login</button>        
                </div>
            {!! Form::close() !!}
            
            {{-- Footer --}}
            @if (!settingEnabled('hide_branding'))
            @include('partial.branding')
            @endif
            {{-- /Footer --}}
        </section>
    </div>    
        {{-- <a class="btn btn-link d-block text-center mt-2" href="{{ route('password.request') }}">
            @langapp('forgot_password')
        </a> --}}
    </div>
</section>



{{-- <script src="{{ getAsset('js/app.js') }}"></script> --}}
<script>

    document.querySelector('.formSaving').addEventListener('click', function() {
        document.getElementById("form_login").submit();
        myFunction();
    });

    function myFunction() {
        const button = document.querySelector('.formSaving');
        button.disabled = true;
        button.innerHTML = 'Processing..<i class="fas fa-spin fa-spinner"></i>';
        setTimeout(reTimeout, 8000);
    }


    function reTimeout() {
        const button = document.querySelector('.formSaving');
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-sync"></i> @langapp('try_again')</span>';
    }




    {{--
    $('.ajaxifyForm_custom').submit(function (event) {
        event.preventDefault();
    });
    --}}
</script>



@endsection