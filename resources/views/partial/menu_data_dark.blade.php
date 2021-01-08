<ul class="nav nav-pills nav-stacked no-radius">
    <li class="{{ $page === 'Data Leak Feed' ? 'active' : '' }}">
        <a href="{{route('datafeed.index')}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Data Leak Feed
        </a>
    </li>
    {{-- <li class="{{ $page === 'Data Feed(darkweb)' ? 'active' : '' }}">
        <a href="{{route('datafeed.darkweb_index')}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            @langapp('data_feed_darkweb')
        </a>
    </li> --}}
    <li class="{{ $page === 'DataLeakDatas' ? 'active' : '' }}">
        <a href="{{route('socialdatas.index_all_site')}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Data Leak Data
            {{-- @langapp('social_data') --}}
        </a>
    </li>
    {{-- <li class="{{ $page === 'Dark Web Datas' ? 'active' : '' }}">
        <a href="{{route('darkweb.index_all_site')}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            @langapp('darkweb_data')
        </a>
    </li> --}}
</ul>