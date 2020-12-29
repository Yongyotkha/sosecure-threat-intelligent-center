<ul class="nav nav-pills nav-stacked no-radius">
    <li class="{{ $page === 'Compromised Feed' ? 'active' : '' }}">
        <a href="{{route('compromised_feed.index')}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Compromised Feed
        </a>
    </li>
    <li class="{{ $page === 'Compromised Data' ? 'active' : '' }}">
        <a href="{{route('compromised_data.index')}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Compromised Data
        </a>
    </li>
</ul>