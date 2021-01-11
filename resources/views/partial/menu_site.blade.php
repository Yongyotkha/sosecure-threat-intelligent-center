<ul class="nav nav-pills nav-stacked no-radius">
    <li class="{{ $page === 'SiteSettings' ? 'active' : '' }}">
        <a href="{{route('sitesettings.edit', ['id' => $siteSettings->code])}}">
            Site
        </a>
    </li>
    <li class="{{ $page === 'SystemSettings' ? 'active' : '' }}">
        <a href="{{route('systemsetting.index', ['id' => $siteSettings->code])}}">
            System
        </a>
    </li>
    
    <li class="{{ $page === 'DataSetting' ? 'active' : '' }}">
        <a href="{{route('datasettings.index', ['id' => $siteSettings->code])}}">
            Permission & Config
        </a>
    </li>

    <li class="{{ $page === 'Users' ? 'active' : '' }}">
        <a href="{{route('userssettings.index', ['id' => $siteSettings->code])}}">
            Users
        </a>
    </li>

    <li class="{{ $page === 'Domain' ? 'active' : '' }}">
        <a href="{{route('domain.index', ['id' => $siteSettings->code])}}">
            Domain
        </a>
    </li>

    <li class="{{ $page === 'Keyword Setting' ? 'active' : '' }}">
        <a href="{{route('keyword.index', ['id' => $siteSettings->code])}}">
            Keywords
        </a>
    </li>

    <li class="{{ $page === 'Assets' ? 'active' : '' }}">
        <a href="{{route('assetssite.index', ['id' => $siteSettings->code])}}">
            Assets
        </a>
    </li>

    {{-- <li {{ $page === 'News' ? 'active' : '' }}>
        <a href="#">
            News
        </a>
    </li> --}}

    <li class="{{ $page === 'Indicators Logs' ? 'active' : '' }} main-link">
        <a href="#">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Indicators
        </a>
        <ul class="nav nav-pills nav-stacked no-radius ul_submenu">
            <li style="padding-left:2rem">
                <a href="{{route('indisetting.indi_logs', ['id' => $siteSettings->code])}}">
                    Logs
                </a>
            </li>
        </ul>
    </li>

    <li class="{{ $page === 'Vulnerability Assets' ? 'active' : '' }} main-link">
        <a href="#">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Vulnerability
        </a>
        <ul class="nav nav-pills nav-stacked no-radius ul_submenu">
            <li style="padding-left:2rem">
                <a href="{{route('vulsetting.vul_assets', ['id' => $siteSettings->code])}}">
                    Assets
                </a>
            </li>
            <li style="padding-left:2rem">
                <a href="{{route('vulsetting.vul_logs', ['id' => $siteSettings->code])}}">
                    Logs
                </a>
            </li>
        </ul>
    </li>

    <li class="{{ $page === 'Compromised' ? 'active' : '' }} main-link">
        <a href="#">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Compromised
        </a>
       <ul class="nav nav-pills nav-stacked no-radius ul_submenu">
            <li style="padding-left:2rem">
                <a href="{{route('compromised_feed.index', ['id' => $siteSettings->code])}}">
                    Compromised Feed
                </a>
            </li>

            <li style="padding-left:2rem">
                <a href="{{route('compromised_data.index', ['id' => $siteSettings->code])}}">
                    Compromised Data
                </a>
            </li>
        </ul>
    </li>

    <li class="{{ $page === 'Social Datas' ? 'active' : '' }} main-link">
        <a href="#">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Data Leak
        </a>
       <ul class="nav nav-pills nav-stacked no-radius ul_submenu">
            <li style="padding-left:2rem">
                <a href="{{route('socialdatas.index', ['id' => $siteSettings->code])}}">
                    Data Leak Feed
                </a>
            </li>

            <li style="padding-left:2rem">
                <a href="{{route('darkweb_datas.index', ['id' => $siteSettings->code])}}">
                    Data Leak Data
                </a>
            </li>
        </ul>
    </li>
    
    <li class="{{ $page === 'Webdefacement' ? 'active' : '' }}">
        <a href="{{route('webdefacement_website.index', ['id' => $siteSettings->code])}}">
            Web Defacement
        </a>
    </li>

    {{-- <li class="{{ $page === 'Webdefacement' ? 'active' : '' }} main-link">
        <a href="#">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Web Defacement
        </a>
       <ul class="nav nav-pills nav-stacked no-radius ul_submenu">
            <li style="padding-left:2rem">
                <a href="{{route('webdefacement_website.index', ['id' => $siteSettings->code])}}">
                    Website
                </a>
            </li>

            <li style="padding-left:2rem">
                <a href="{{route('webdefacement_server.index', ['id' => $siteSettings->code])}}">
                    Server
                </a>
            </li>
        </ul>
    </li> --}}




</ul>