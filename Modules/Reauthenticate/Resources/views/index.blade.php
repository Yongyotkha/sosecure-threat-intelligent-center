@extends('layouts.auth')

@section('content')

    <section id="content" class="m-t-lg wrapper-md content">
        <div id="login-darken"></div>
        <div id="login-form" class="container aside-xxl animated fadeInUp">
        <span class="navbar-brand block"> 
                <img src="" class="img-responsive thumb-sm m-r-sm">
        </span>

        <section class="panel panel-default bg-white m-t-lg b-r-cust">
            <header class="panel-heading text-center"><strong>Setting Password</strong> </header>
                

            {{-- <form class="panel-body wrapper-lg" method="POST" action=""> --}}
            {!! Form::open(['route' => ['reauth.verify_update_pass', 'id' => $User->code], 'class' => 'panel-body wrapper-lg ajaxifyForm validator', 'novalidate' => '', 'method' => 'PUT', 'files' => false]) !!}
                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    <label><b>Welcome Site: </b>{{$User->get_SiteSettings->name}}</label>
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
                    <button type="submit" class="btn btn-success btn-block"> @icon('solid/unlock-alt') @langapp('confirm')</button>

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
