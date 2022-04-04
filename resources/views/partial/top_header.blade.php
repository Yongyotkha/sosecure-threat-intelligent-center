<header class="bg-{{ get_option('top_bar_color') }} header navbar navbar-fixed-top-xs nav-z">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="btn btn-link visible-xs" data-toggle="class:nav-off-screen" data-target="#nav">
                @icon('solid/bars')
            </a>
            <a href="{{  url('/')  }}" class="navbar-brand">
                <img src="{{asset('images/logo_threat/logo.png')}}" class="m-r-sm" onerror="setDefaultPic(this)">
                {{-- @php $display = get_option('logo_or_icon'); @endphp
                @if ($display == 'logo' || $display == 'logo_title')
                <img src="{{ getStorageUrl(config('system.media_dir').'/'.get_option('company_logo'))  }}" class="m-r-sm" onerror="setDefaultPic(this)">
                @elseif ($display == 'icon' || $display == 'icon_title')
                <i class="fa {{  get_option('site_icon')  }}"></i>
                @endif
                @if ($display == 'logo_title' || $display == 'icon_title')
                @if (get_option('website_name') == '')
                {{ get_option('company_name') }}
                @else
                {{ get_option('website_name') }}
                @endif
                @endif --}}
                {{-- <span class="md-text-logo"> SOSECURE <br> <span class="sm-text-logo"> Threat inSight </span> </span> --}}
            </a>
            <a class="btn btn-link visible-xs" data-toggle="dropdown" data-target=".nav-user">
                @icon('solid/cog')
            </a>
        </div>

        <ul class="nav navbar-nav hidden-xs" id="todolist">
            <li class="">
                <div style="margin-top:8px;margin-left:0px;">
                    {{-- <a href="{{ route('calendar.todos') }}" class="" data-rel="tooltip" title="@langapp('tasks') " data-placement="bottom">
                        @icon('solid/tasks')
                        <span class="badge badge-sm up bg-danger m-l-n-sm display-inline">{{ \Auth::user()->todoToday() }}</span>
                    </a>
                    <a href="{{ route('calendar.index') }}" class="m-l" data-rel="tooltip" title="@langapp('calendar')" data-placement="bottom">
                        @icon('solid/calendar-alt')
                    </a>
                    <a href="{{ route('calendar.appointments') }}" class="m-l" data-rel="tooltip" title="@langapp('appointments') " data-placement="bottom">
                        @icon('solid/calendar-check')
                    </a>  --}}
                    {{-- <a class="btn btn-link" id="collapse-menu">
                        @icon('solid/bars')
                    </a> --}}
                </div>
            </li>
        </ul>
        <ul class="nav navbar-nav hidden-xs navbar-center">
            
            <li class="dropdown hidden-xs">
                <form action="{{ route('search.page') }}" method="GET" role="search">
                    {!! csrf_field() !!}
                    <div class="form-group form-custom" style="margin-bottom:0;">
                        <div class="input-group w-400px" style="width:500px;padding: .8rem;">
                            <span class="input-group-btn icon-search">
                                <i class="fas fa-search"></i>
                            </span>
                            <div class="input-group-custom">
                                <input type="text" id="search_input" class="form-control form-transparent" name="keyword" placeholder="Type tag keyword" style="margin-top: 2px;margin-left: 1rem;" value="{{@$keyword}}">
                                <div class="input-group-prepend-custom">
                                   <button class="btn btn-dark">Search</button>
                                   <button type="button" class="btn btn-dark lookup"><i class="fas fa-search"></i> Threat Lookup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </li>



        </ul>

        <ul class="nav navbar-nav navbar-right hidden-xs nav-user" style="margin-top: 1rem;">

            @if(count(runningTimers()) > 0)
            <li class="">
                <a href="{{ route('timetracking.timers') }}" title="@langapp('timers')" data-toggle="ajaxModal" data-rel="tooltip" data-placement="bottom">  
                    @icon('solid/clock', 'fa-spin fa-lg text-warning')
                    <span class="badge badge-sm up bg-info m-l-n-sm display-inline">{{ count(runningTimers()) }}</span>
                </a>
            </li>
            @endif

            {{-- @include('partial.notifications') --}}
            



            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    <span class="thumb-sm avatar pull-left">
                        <img src="{{ avatar() }}" class="img-circle" onerror="setDefaultPic(this)">
                    </span>
                    {{ Auth::user()->name }} <b class="caret"></b>
                </a>
                <ul class="dropdown-menu animated fadeInRight">
                    <li class="arrow top"></li>
                    <li><a href="{{ route('users.profile') }}">@langapp('settings')</a></li>
                    {{-- <li><a href="{{ route('tell.friend') }}" data-toggle="ajaxModal">@langapp('tell_friend')  </a></li>
                    <li><a href="{{ route('users.reminders') }}">@langapp('reminders')</a></li> --}}
                    {{-- <li><a href="{{ route('users.notifications') }}">@langapp('notifications') </a></li> --}}
                    {{-- <li><a href="{{ route('extras.user.templates') }}">@langapp('canned_responses')</a></li> --}}
                    @admin
                    {{-- <li><a href="{{ route('support.ticket') }}" data-toggle="ajaxModal">Need Help?</a></li> --}}
                    @endadmin
                    <li class="divider"></li>
                    @if(Auth::user()->isImpersonating())
                    <li><a href="{{ route('users.stopimpersonate') }}">@langapp('stop_impersonate')</a></li>
                    @endif
                    <li>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                            @icon('solid/sign-out-alt') Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="display-none">
                            {{ csrf_field() }}
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</header>

@push('pagescript')
    <script>
        $('.lookup').click(function(){
            let keyword = $('#search_input').val();
            window.location.href = '/newSearch?keyword=' + keyword + '&mode=lookup';
        })
    </script>
@endpush
