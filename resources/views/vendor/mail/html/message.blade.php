@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => site_url()])
        {{ get_option('company_name') }}
        {{-- <img src="{{asset('images/logo_site.png')}}" style="widht:100px;height:auto;"> --}}
        @endcomponent
    @endslot
    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            &copy; {{ date('Y') }} {{ get_option('company_name') }}. All rights reserved.
        @endcomponent
    @endslot
@endcomponent
