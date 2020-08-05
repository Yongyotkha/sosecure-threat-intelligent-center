{{-- <small>Powered by <a href="{{ config('system.saleurl') }}" target="_blank">Sosecure</a> v{{ getCurrentVersion()['version']  }}<br>
@icon('solid/code') with @icon('solid/heart') by <a href="#"><strong>MTSC</strong></a> &copy; {{  date('Y')  }}
</small> --}}

<p>
    <small>Powered by <a href="{{ config('system.saleurl') }}" target="_blank">Sosecure</a> v{{ getCurrentVersion()['version']  }}
    <br>&copy; {{ date('Y') }} <a href="{{ get_option('company_domain') }}"
    target="_blank">{{ get_option('company_name') }}</a>
    </small>
</p>