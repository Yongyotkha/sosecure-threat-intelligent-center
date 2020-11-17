@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">
            <header class="header bg-white b-b b-light head-d-flex-nowrap" style="white-space: nowrap;">
                <div class="bc-head m-none" style="width:100%;">
                    <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive m-r-5">
                        @icon('solid/arrow-left')
                    </a>
                     Site Setting > {{ $site -> get_site -> name }} | Scan Domain : {{ $site -> get_domain -> domain }}
                </div>
                @if($tab == 'datatype')
                    <div class="button-control pull-right">
                        <div class="btn-group">
                            <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="asset-to-use" disabled> Asset To Use</button>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="fillter-advance"> @langapp('filter') Advance</button>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" data-toggle="dropdown">Group By
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="#">
                                        Default
                                    </a>
                                </li>   
                                <li>
                                    <a href="#">
                                        Datatype
                                    </a>
                                </li>   
                                <li>
                                    <a href="#">
                                        Source
                                    </a>
                                </li>   
                                <li>
                                    <a href="#">
                                        Module
                                    </a>
                                </li>   
                            </ul>
                        </div>
                    </div>
                    @endif
                &nbsp;

            </header>
            <section class="scrollable wrapper bg-white" style="padding:0;">
                <div class="sub-tab text-uc small m-b-sm">

                    <ul class="nav pro-nav-tabs nav-tabs-dashed">
                        <li class="{{ ($tab == 'overview') ? 'active' : '' }}">
                            <a href="{{ route('scans.index', ['tab' => 'overview', 'site_code' => $site -> code]) }}">
                                @icon('solid/database') @langapp('overview')
                            </a>
                        </li>

                        <li class="{{ ($tab == 'datatype') ? 'active' : '' }}">
                            <a href="{{ route('scans.index', ['tab' => 'datatype', 'site_code' => $site -> code]) }}">
                                @icon('solid/folder-open') Data
                            </a>
                        </li>
                        <li class="{{ ($tab == 'settings') ? 'active' : '' }}">
                            <a href="{{ route('scans.index', ['tab' => 'settings', 'site_code' => $site -> code]) }}">
                                @icon('solid/life-ring') @langapp('settings')
                            </a>
                        </li>
                        <li class="{{ ($tab == 'logs') ? 'active' : '' }}">
                            <a href="{{ route('scans.index', ['tab' => 'logs', 'site_code' => $site -> code]) }}">
                            @icon('solid/clock') @langapp('logs')
                            </a>
                        </li>
                    </ul>

                </div>

                @include('scans::tab.'.$tab)
                
            </section>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>
@endsection
