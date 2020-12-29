<ul class="nav nav-pills nav-stacked no-radius">
    <li class="{{ $page === 'Data Leak Feed' ? 'active' : '' }}">
        <a href="{{route('datafeed.index')}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Data Leak Feed
        </a>
    </li>
    <li class="{{ $page == 'DataLeakDatas' ? 'active' : '' }}">
        <a href="{{route('socialdatas.index_all_site')}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Data Leak Datas
        </a>
    </li>
</ul>