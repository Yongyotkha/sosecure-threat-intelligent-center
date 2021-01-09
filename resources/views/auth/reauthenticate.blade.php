@extends('layouts.auth')
@section('content')
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
            
        <form method="POST" action="{{ route('users.reauthenticate.process') }}">{{ csrf_field() }}
            <div class="login-body">
                <div class="mb-2">
                    <strong>@langapp('confirm_password_to_continue')</strong>
                </div>
            
                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    <label for="password">@langapp('password') <span class="text-danger">*</span></label>
    
                    <input id="password" type="password" class="input-login form-control" name="password" required>
    
                        @if ($errors->has('password'))
                            <span class="help-block">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                        @endif
                </div>

                <p class="text-muted m-t-sm"><strong>Tip:</strong> You are entering sudo mode. You will not be asked for your password for a few hours.</p>
            </div>


                <div>
                    <button type="submit" class="btn btn-login-cus bd-round-b-lr btn-success btn-block">
                        @icon('solid/unlock-alt') @langapp('confirm_password')</button> 
                </div>
            {!! Form::close() !!}
            
            {{-- Footer --}}
            @if (!settingEnabled('hide_branding'))
            @include('partial.branding')
            @endif
            {{-- /Footer --}}
        </section>
    </div>    
    </div>
</section>
@endsection