@extends('layouts.auth')

@section('content')

    <section id="content" class="m-t-lg wrapper-md content">
        <div id="login-darken"></div>
        <div id="login-form" class="container aside-xxl animated fadeInUp">
        <span class="navbar-brand block"> 
                <img src="" class="img-responsive thumb-sm m-r-sm">
        </span>

        <section class="panel panel-default bg-white m-t-lg b-r-cust">
            <header class="panel-heading text-center"><strong>Setting Email or Password</strong> </header>
                

            <form class="panel-body wrapper-lg" method="POST" action="">
                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                    <label>@langapp('email')</label>
                    <input id="email" type="email" class="form-control" name="email" required>
                </div>
                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                    <label>@langapp('password')</label>
                    <input id="password" type="password" class="form-control" name="password" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success btn-block"> @icon('solid/unlock-alt') @langapp('confirm')</button>

                    <p class="text-muted m-t-sm">
                        <strong>Tip:</strong> You are entering sudo mode. You will not be asked for your Email for a few hours.</p>
                </div>
                <div class="line line-dashed"> </div>
            </form>
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
