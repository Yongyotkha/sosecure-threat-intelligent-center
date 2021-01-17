<ul class="nav nav-pills nav-stacked no-radius">
    
    <li class="{{ $page === langapp('dataleak_data') || $page === langapp('data_leak') ? 'active' : '' }}">
        <a href="{{route('socialdatas.index_all_site')}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Data Leak Data
            {{-- @langapp('social_data') --}}
        </a>
    </li>
    <li class="{{ $page === langapp('data_leak_feed') ? 'active' : '' }}">
        <a href="{{route('datafeed.index')}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Data Leak Feed
        </a>
    </li>
</ul>