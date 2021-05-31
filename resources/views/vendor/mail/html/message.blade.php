@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => site_url()])
        {{ get_option('company_name') }}
        {{-- <img src="{{asset('images/logo_site.png')}}" style="widht:100px;height:auto;"> --}}
        @endcomponent

        <div class="miro__header" style="background:#263042;font-family:Helvetica,Arial,sans-serif;height:100%;min-height:100px;padding:0 40px">
            <table class="miro__header-content" style="border-collapse:collapse;border-spacing:0;font-family:Helvetica,Arial,sans-serif;padding:0;text-align:left;vertical-align:top;width:100%">
                <tr style="font-family:Helvetica,Arial,sans-serif;padding:0;text-align:left;vertical-align:top">
                <td class="miro__col-header-logo" style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Helvetica,Arial,sans-serif;font-size:14px;font-weight:400;hyphens:auto;line-height:1.43;margin:0;padding:0;padding-top:32px;text-align:left;vertical-align:top;width:50%;word-wrap:break-word">
                    <a href="{{url('/')}}" target="_blank" style="Margin:0;color:#2a79ff;font-family:Helvetica,Arial,sans-serif;font-weight:400;line-height:1.43;margin:0;padding:0;text-align:left;text-decoration:none">
                        <img src="{{asset('images/logo_threat/logo.png')}}" style="-ms-interpolation-mode:bicubic;border:none;clear:both;display:block;font-family:Helvetica,Arial,sans-serif;height:45px;max-height:100%;max-width:100%;outline:0;text-decoration:none;width:auto">
                    </a>
                </td>
                <td class="miro__col-header-btn" style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;border-collapse:collapse!important;color:#0a0a0a;font-family:Helvetica,Arial,sans-serif;font-size:14px;font-weight:400;hyphens:auto;line-height:1.43;margin:0;padding:0;padding-top:26px;text-align:right;vertical-align:top;width:50%;word-wrap:break-word">
                <a href="{{url('/')}}" class="miro-btn" target="_blank" style="Margin:0;background-color:#fff;border:1px solid #050038;border-radius:4px;box-sizing:border-box;color:#050038!important;cursor:pointer;display:inline-block;font-family:Helvetica,Arial,sans-serif;font-size:16px!important;font-stretch:normal;font-style:normal;font-weight:400;height:48px;letter-spacing:normal;line-height:48px!important;margin:0;padding:0;text-align:center;text-decoration:none;white-space:nowrap;width:170px">
                        <span style="font-family:Helvetica,Arial,sans-serif">
                            Go To Threat inSight
                        </span>
                    </a>
                </td>
                </tr>
            </table>
        </div>
        
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
