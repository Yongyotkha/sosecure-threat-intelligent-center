<ul class="nav nav-pills nav-stacked no-radius">
    <li class="{{ $page === 'SiteSettings' ? 'active' : '' }}">
        <a href="{{route('sitesettings.edit', ['id' => $siteSettings->code])}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Site Settings
        </a>
    </li>
    <li class="{{ $page === 'SystemSettings' ? 'active' : '' }}">
        <a href="{{route('systemsetting.index', ['id' => $siteSettings->code])}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            System Settings
        </a>
    </li>
    <li class="{{ $page === 'DataSetting' ? 'active' : '' }}">
        <a href="{{route('datasettings.index', ['id' => $siteSettings->code])}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Permission & Config Settings
        </a>
    </li>
    <li class="{{ $page === 'Users' ? 'active' : '' }}">
        <a href="{{route('userssettings.index', ['id' => $siteSettings->code])}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Users
        </a>
    </li>
    <li class="{{ $page === 'Domain' ? 'active' : '' }}">
        <a href="{{route('domain.index', ['id' => $siteSettings->code])}}">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Domain
        </a>
    </li>

    <li class="{{ $page === 'Assets' ? 'active' : '' }}">
        <a href="#">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Assets
        </a>
    </li>
    <li class="{{ $page === 'Keyword Setting' ? 'active' : '' || $page === 'Social Datas' ? 'active' : '' }} main-link">
        <a href="#">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            Data Leak
        </a>
       <ul class="nav nav-pills nav-stacked no-radius ul_submenu">
            <li style="padding-left:2rem">
                <a href="{{route('keyword.index', ['id' => $siteSettings->code])}}">
                    Keyword Setting
                </a>
            </li>

            <li style="padding-left:2rem">
                <a href="{{route('socialdatas.index', ['id' => $siteSettings->code])}}">
                    Social Datas
                </a>
            </li>

            <li style="padding-left:2rem">
                <a href="{{route('darkweb_datas.index', ['id' => $siteSettings->code])}}">
                    Dark web Data
                </a>
            </li>
        </ul>
    </li>
    {{-- <li {{ $page === 'News' ? 'active' : '' }}>
        <a href="#">
            @icon('solid/angle-right', 'text-'.get_option('theme_color'))
            News
        </a>
    </li> --}}

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
</ul>