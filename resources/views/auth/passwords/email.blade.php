@extends('layouts.auth')

@section('content')


<section id="content">
    <div id="login-form" class="container aside-xxl animated fadeInUp">
    <div class="box-login-shadow  bd-round-b-lr block bd-round-t-lr">
    <span class="nav-header-login navbar-brand">
        <img src="{{asset('images/logo_threat/logo_site.png')}}" class="logo-sosecure mt-2">
                {{-- @php $display = get_option('logo_or_icon'); @endphp
                @if ($display == 'logo' || $display == 'logo_title') 
                <img src="{{ getStorageUrl(config('system.media_dir').'/'.get_option('company_logo')) }}"
                     class="img-responsive {{ ($display == 'logo' ? '' : 'thumb-sm m-r-sm') }}">
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
                {{-- <header class="panel-heading text-center login-heading"><strong>{{ get_option('company_name') }} @langapp('reset_password')</strong>
                </header> --}}

        
            
                {!! Form::open(['route' => 'password.email', 'class' => '']) !!}
                <div class="login-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email">@langapp('email') @required</label>

                                <input id="email" type="email" class="input-login form-control" name="email" value="{{ old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            
                        </div>   
                    </div>

                    <div>
                        <button type="submit" class="btn-login-cus btn btn-{{ get_option('theme_color') }} btn-block">@langapp('send_reset_password') </button>

                        <a href="{{ route('login') }}" style="margin-top: 0" class="btn-login-cus bd-round-b-lr btn btn-success btn-block">@langapp('login') </a>
                    </div>

                    {!! Form::close() !!}
                    {{-- Footer --}}
                @if (!settingEnabled('hide_branding')) 
                    @include('partial.branding')
                @endif
                {{-- /Footer --}}

            </div>  
            
        

    </section>
    
</section>
@endsection
