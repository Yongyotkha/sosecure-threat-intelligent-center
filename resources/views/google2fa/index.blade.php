@extends('layouts.auth')
@section('content')
<div class="content">
    <section id="content" class="m-t-lg wrapper-md m-t-cust">
        <div id="login-darken"></div>
        <div id="login-form" class="container aside-xxl animated fadeInUp" style="max-width: 800px !important">

            {{-- <span class="navbar-brand block {{  (get_option('blur_login') == 'TRUE') ? 'text' : '' }}">
                @php $display = get_option('logo_or_icon'); @endphp
                @if ($display == 'logo' || $display == 'logo_title')
                <img src="{{ getStorageUrl(config('system.media_dir').'/'.get_option('company_logo')) }}"
                class="img-responsive {{ ($display == 'logo' ? '' : 'thumb-sm m-r-sm') }}">
                @elseif ($display == 'icon' || $display == 'icon_title')
                @icon('solid/'.get_option('site_icon'))
                @endif
                @if ($display == 'logo_title' || $display == 'icon_title')
                @if (get_option('website_name') == '')
                {{ get_option('company_name') }}
                @else
                {{ get_option('website_name') }}
                @endif
                @endif
            </span> --}}

            <span class="nav-header-login navbar-brand">
                <img src="{{asset('images/logo_threat/logo_site.png')}}" class="logo-sosecure mt-2" style="margin-bottom: 6rem;">
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


            <section class="panel panel-default bg-white-cust m-t-lg b-r-cust">
                <div class="panel-body text-center">
                    <h3 style="margin-bottom: 2rem;color:#333;">Two-factor authentication</h3>
                    <form class="form-horizontal" method="POST" action="{{ route('2fa.auth') }}">
                        {{ csrf_field() }}
                        @if ($errors->has('message'))
                        <div class="alert alert-danger">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            {{ $errors->first('message') }}
                        </div>
                        @endif
                        <div class="form-group">
                            <div class="col-md-12">
                                <input id="one_time_password" type="text" class="form-control" name="one_time_password" required autofocus style="font-size:1.8rem;height: 40px !important;">
                            </div>
                        </div>
                        <div style="margin:2rem 0 2rem 0">
                            <button type="submit" class="btn btn-info btn-block"><h4 style="margin: .8rem 0 !important">Verify</h4></button>
                        </div>
                        {{-- <div class="m-sm">
                            <a href="{{ url('/logout') }}">@langapp('cancel')</a> | <a href="{{ route('2fa.reset') }}">@langapp('verify')</a>
                        </div> --}}
                        
                    </form>
                </div>
                {{-- footer --}}
                @if (get_option('hide_branding') == 'FALSE')
                <footer id="footer copyright-footer">
                    <div class="text-center text-muted padder">
                        <p>
                            @include('partial.copyright')
                        </p>
                    </div>
                </footer>
                @endif
               {{-- /footer --}}
            </section>
        </div>
    </section>
</div>
@endsection