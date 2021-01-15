<ul class="nav nav-pills nav-stacked no-radius">

    <li class="{{ $page === langapp('compromised_data') ? 'active' : '' }}">
        <a href="{{route('darkweb.index_all_site')}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Compromise Data
            {{-- @langapp('darkweb_data') --}}
        </a>
    </li>
    <li class="{{ $page === langapp('compromised_feed') ? 'active' : '' }}">
        <a href="{{route('datafeed.darkweb_index')}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Compromise Feed
            {{-- @langapp('data_feed_darkweb') --}}
        </a>
    </li>


</ul>