@extends('layouts.app2')
@section('content')
       
<section id="content">
    <div class="policy-container" style="background: #eee;padding: 2rem 0">
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
                        <input type="password" id="password" name="" class="form-control form-policy" autocomplete="new-password" onKeyUp="checkPasswordStrength();">
                    </div>
                    <div class="form-group">
                        <h4>Re-enter Password</h4>
                        <input type="password" name="" class="form-control form-policy">
                    </div>

                    <div id="password_st" style="display:none">
                        <p class="st-pass">Password Strength</p>
                        <div class="progress-pass" >
                            <div id="password-strength-status" class="progress-pass-inner"></div>
                            <div id="password-strength-text" class="progress-pass-text"></div>
                        </div>
                    </div>

                    <ul class="condition-policy">
                        <li class="minimum">Be a minimum of 8 characters</li>
                        <li class="lowercase">Include at least one lowercase letter (a-z)</li>
                        <li class="uppercase">Include at least one uppercase letter (A-Z)</li>
                        <li class="number">Include at least one number (0-9)</li>
                        <li class="special">Include at least one special character</li>
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
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@push('pagestyle')

@endpush
@push('pagescript')

<script>
function checkPasswordStrength() {
    $('#password_st').show();
    var number = /([0-9])/;
    var alphabets = /([a-zA-Z])/;
    var lowercase = /([a-z])/;
    var uppercase = /([A-Z])/;
    var special_characters = /([~,!,@,#,$,%,^,&,*,-,_,+,=,?,>,<])/;
    if ($('#password').val().length < 8) {
        $('#password-strength-status').removeClass();
        $('#password-strength-text').empty();
        $('#password-strength-status').addClass('progress-pass-inner bg-danger');
        $('#password-strength-text').html("Weak");

        $('.minimum').css('color', '');
        $('.minimum').css('color', 'red');

        if($('#password').val().match(number)){
            $('.number').css('color', '');
            $('.number').css('color', 'green');
        }else{
            $('.number').css('color', '');
            $('.number').css('color', 'red');
        }

        if($('#password').val().match(lowercase)){
            $('.lowercase').css('color', '');
            $('.lowercase').css('color', 'green');
        }else{
            $('.lowercase').css('color', '');
            $('.lowercase').css('color', 'red');
        }

        if($('#password').val().match(uppercase)){
            $('.uppercase').css('color', '');
            $('.uppercase').css('color', 'green');
        }else{
            $('.uppercase').css('color', '');
            $('.uppercase').css('color', 'red');
        }

        if($('#password').val().match(special_characters)){
            $('.special').css('color', '');
            $('.special').css('color', 'green');
        }else{
            $('.special').css('color', '');
            $('.special').css('color', 'red');
        }

    } else {
        $('.minimum').css('color', '');
        $('.minimum').css('color', 'green');

        if($('#password').val().match(number)){
            $('.number').css('color', '');
            $('.number').css('color', 'green');
        }else{
            $('.number').css('color', '');
            $('.number').css('color', 'red');
        }

        if($('#password').val().match(lowercase)){
            $('.lowercase').css('color', '');
            $('.lowercase').css('color', 'green');
        }else{
            $('.lowercase').css('color', '');
            $('.lowercase').css('color', 'red');
        }

        if($('#password').val().match(uppercase)){
            $('.uppercase').css('color', '');
            $('.uppercase').css('color', 'green');
        }else{
            $('.uppercase').css('color', '');
            $('.uppercase').css('color', 'red');
        }

        if($('#password').val().match(special_characters)){
            $('.special').css('color', '');
            $('.special').css('color', 'green');
        }else{
            $('.special').css('color', '');
            $('.special').css('color', 'red');
        }

        if ($('#password').val().match(number) && $('#password').val().match(alphabets) && $('#password').val().match(special_characters)) {
            $('#password-strength-status').removeClass();
            $('#password-strength-text').empty();
            $('#password-strength-status').addClass('progress-pass-inner bg-success');
            $('#password-strength-text').html("Strong");
        }else if($('#password').val().match(number) && $('#password').val().match(alphabets)){
            $('#password-strength-status').removeClass();
            $('#password-strength-text').empty();
            $('#password-strength-status').addClass('progress-pass-inner bg-info');
            $('#password-strength-text').html("Medium");
        }else if ($('#password').val().match(alphabets) || $('#password').val().match(number)) {
            $('#password-strength-status').removeClass();
            $('#password-strength-text').empty();
            $('#password-strength-status').addClass('progress-pass-inner bg-warning');
            $('#password-strength-text').html("Low");
        }
    }
}
</script>
@endpush
@endsection
