@extends('layouts.auth')

@section('content')

    <section id="content" class="m-t-lg wrapper-md content">
        <div id="login-darken"></div>
        <div id="login-form" class="container aside-xxl animated fadeInUp">
        <span class="navbar-brand block"> 
                {{-- <img src="{{ getStorageUrl(config('system.media_dir').'/'.get_option('company_logo'))  }}" class="img-responsive thumb-sm m-r-sm"> --}}
                <img src="{{asset('images/logo_threat/logo.png')}}" class="m-r-sm" onerror="setDefaultPic(this)">
        
        </span>

        <section class="panel panel-default bg-white m-t-lg b-r-cust">
            <header class="panel-heading text-center" style="height: 80px;"><strong></strong>{{get_option('company_name')}}</header>
                

            {{-- <form class="panel-body wrapper-lg" method="POST" action=""> --}}
            <form class="panel-body wrapper-lg ajaxifyForm_custom validator">
                <h1>Can be signed in at your domain site.</h1>
            </form>  
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


    </script>
@endpush


