@component('mail::message')
{{-- Share Workice with friend --}}
Hello,  
ทดสอบส่งเมล
@component('mail::button', ['url' => 'https://workice.com', 'color' => 'blue'])
I'm Interested
@endcomponent

@endcomponent