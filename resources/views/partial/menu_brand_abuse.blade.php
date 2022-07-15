<ul class="nav nav-pills nav-stacked no-radius">
    
    <li class="{{ $page === langapp('brandabuse_data') || $page === langapp('brandabuse') ? 'active' : '' }}">
        <a href="{{route('brandabuse.index_all_site')}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Brand Abuse Data
            {{-- @langapp('social_data') --}}
        </a>
    </li>
    <li class="{{ $page === langapp('brandabusefeed') ? 'active' : '' }}">
        <a href="{{route('brandabusefeed.index')}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Brand Abuse Feed
        </a>
    </li>
</ul>