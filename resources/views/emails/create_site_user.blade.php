@component('mail::message')
{{-- Share Workice with friend --}}
Hello, there is a register using this email.   
Please press to confirm the application at the link. <a href="{{route('reauth.verify_site_user',['token' => $summary['site_add_user_token']])}}">{{route('reauth.verify_site_user',['token' => $summary['site_add_user_token']])}}</a> นี้ หรือ  
@component('mail::button', ['url' => route('reauth.verify_site_user',['token' => $summary['site_add_user_token']]), 'color' => 'blue'])
Verify Registrer
@endcomponent

@endcomponent