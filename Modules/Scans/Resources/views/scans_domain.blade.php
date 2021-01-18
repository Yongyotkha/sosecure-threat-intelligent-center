@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">
            <header class="header bg-white b-b b-light head-d-flex-nowrap" style="white-space: nowrap;">
                <div class="bc-head m-none" style="width:100%;">
                    <a href="javascript:history.back()" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive m-r-5">
                        @icon('solid/arrow-left')
                    </a>
                     Site Setting > {{ $site -> get_site -> name }} | Scan Domain : {{ $site -> get_domain -> domain }}
                </div>
                @if($tab == 'datatype' )
                    <div class="button-control pull-right">
                        
                        <div class="btn-group">
                            <button data-target="#asset_to_use" data-toggle="modal" class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="asset-to-use" disabled="disabled"> Asset To Use</button>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="fillter-advance"> @langapp('filter') Advance</button>
                        </div>
                        <div class="btn-group">
                            <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" data-toggle="dropdown">Group By
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left">
                                <li>
                                    <a href="#">
                                        Internet Name
                                    </a>
                                </li>   
                                <li>
                                    <a href="#">
                                        Affiliate - Internet Name
                                    </a>
                                </li>   
                                <li>
                                    <a href="#">
                                        Affiliate - Domain Name
                                    </a>
                                </li>   
                                <li>
                                    <a href="#">
                                        Domain Name
                                    </a>
                                </li>  
                                <li>
                                    <a href="#">
                                        IP Address
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        IPv6 Address
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        Malicious Internet Name
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        Human Name
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        Internet Name
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        Email Address
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        Domain Name (Parent)
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        Phone Number
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
                @if($tab == 'asset' )
                <div class="button-control pull-right">
                    <div class="btn-group">
                        <button data-target="#asset_to_use_manual" data-toggle="modal" class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="asset-to-use-manual">Add Asset To Use</button>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="fillter-advance">@langapp('Search_Advance')</button>
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
                        <li class="{{ ($tab == 'asset') ? 'active' : '' }}">
                            <a href="{{ route('scans.index', ['tab' => 'asset', 'site_code' => $site -> code]) }}">
                                @icon('solid/folder-open') Asset
                            </a>
                        </li>
                        {{-- <li class="{{ ($tab == 'settings') ? 'active' : '' }}">
                            <a href="{{ route('scans.index', ['tab' => 'settings', 'site_code' => $site -> code]) }}">
                                @icon('solid/life-ring') @langapp('settings')
                            </a>
                        </li>
                        <li class="{{ ($tab == 'logs') ? 'active' : '' }}">
                            <a href="{{ route('scans.index', ['tab' => 'logs', 'site_code' => $site -> code]) }}">
                            @icon('solid/clock') @langapp('logs')
                            </a>
                        </li> --}}
                    </ul>

                </div>

                @include('scans::tab.'.$tab)
                
            </section>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>


        <!-- Modal Scans -->
        <div class="modal in fixed-left" id="asset_to_use" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside size-half-50" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        Asset To Use
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-3 text-center">
                            <h3 class="text-dark">Assets</h3>
                        </div>
                        <div class="col-xs-9 text-center">
                            <h3 class="text-dark">Referent</h3>
                        </div>
                        <div class="col-md-12">
                            <hr>
                        </div>
                    </div>
                    <div id="show_asets" class="row">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="button" class="btn btn-info btn-rounded" onclick="save_assets()">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>

     <!-- Modal Scans -->
        <div class="modal in fixed-left" id="asset_to_use_manual" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside size-half-50" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        Asset To Use
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-3 text-center">
                            <h3 class="text-dark">Assets</h3>
                        </div>
                        <div class="col-xs-9 text-center">
                            <h3 class="text-dark">Referent</h3>
                        </div>
                        <div class="col-md-12">
                            <hr>
                        </div>
                    </div>
                    <div id="show_asets_manual" class="row">
                    </div>
                    <button type="button" class="btn btn-sm btn-info m-xs" onclick="add_new_assets_manual()">
                        <span>@icon('solid/plus')  Add Assets
                    </button>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="button" class="btn btn-info btn-rounded" onclick="save_assets_manual()">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>

</section>

@push('pagestyle')
@endpush

@push('pagescript')
@include('stacks.js.fullscreen')
@endpush
@endsection
