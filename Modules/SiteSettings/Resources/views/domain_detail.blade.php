@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">
            <header class="header bg-white b-b b-light head-d-flex-nowrap" style="white-space: nowrap;overflow-x: auto;">
                <div class="bc-head m-none">
                    <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive m-r-5">
                        @icon('solid/arrow-left')
                    </a>
                     Site Setting > ธนาคารออมสิน | Scan Domain : baac.or.th
                </div>
                &nbsp;

            </header>
            <section class="scrollable wrapper bg-white" style="padding:0;">
                <div class="sub-tab text-uc small m-b-sm">
                    <ul class="nav pro-nav-tabs nav-tabs-dashed">
                        <li class="{{ ($tab == 'overview') ? 'active' : '' }}">
                            <a href="{{ route('domain_detail.index', ['tab' => 'overview']) }}">
                                @icon('solid/database') @langapp('overview')
                            </a>
                        </li>

                        <li class="{{ ($tab == 'datatype') ? 'active' : '' }}">
                            <a href="{{ route('domain_detail.index', ['tab' => 'datatype']) }}">
                                @icon('solid/folder-open') Datatype
                            </a>
                        </li>
                        <li class="{{ ($tab == 'settings') ? 'active' : '' }}">
                            <a href="{{ route('domain_detail.index', ['tab' => 'settings']) }}">
                                @icon('solid/life-ring') @langapp('settings')
                            </a>
                        </li>
                        <li class="{{ ($tab == 'logs') ? 'active' : '' }}">
                            <a href="{{ route('domain_detail.index', ['tab' => 'logs']) }}">
                            @icon('solid/clock') @langapp('logs')
                            </a>
                        </li>
                    </ul>

                </div>

                @include('sitesettings::tab.'.$tab)
                
            </section>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>
@endsection