@extends('layouts.app2')
@section('content')
                           
<section id="content">
    <section class="vbox">
        <section class="scrollable wrapper bg">

            <div class="policy-container">
                <div class="policy-inner policy-shadow">
                    <div class="policy-header">
                        <img src="{{asset('images/lockerpass.png')}}" alt="" style="max-height: 115px">
                        <h1>Your Password has expired</h1>
                        <p>You must change your password now and login again!</p>
                    </div>

                    <div class="policy-body">
                        <form action="">
                            <div class="form-group">
                                <h4>Current Password</h4>
                                <input type="password" name="" class="form-control form-policy">
                            </div>
                            <div class="form-group">
                                <h4>New Password</h4>
                                <input type="password" name="" class="form-control form-policy">
                            </div>
                            <div class="form-group">
                                <h4>Re-enter Password</h4>
                                <input type="password" name="" class="form-control form-policy">
                            </div>

                            <div>
                                <p class="st-pass">Password Strength</p>
                                <div class="progress-pass">
                                    <div class="progress-pass-inner" style="background: orangered;width:50%"></div>
                                    <div class="progress-pass-text">Weak (Should be atleast 8 Characters)</div>
                                </div>
                            </div>

                            <ul class="condition-policy">
                                <li>Be a minimum of 8 characters</li>
                                <li>Include at least one lowercase letter (a-z)</li>
                                <li>Include at least one uppercase letter (A-Z)</li>
                                <li>Include at least one number (0-9)</li>
                                <li>Include at least one special character</li>
                            </ul>

                            <div class="text-center m-b-xs">
                                <button class="btn btn-info">
                                   <h2 style="margin: 0;color:#fff;">Confirm</h2> 
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@push('pagestyle')

@endpush
@push('pagescript')

<script>

</script>
@endpush
@endsection
