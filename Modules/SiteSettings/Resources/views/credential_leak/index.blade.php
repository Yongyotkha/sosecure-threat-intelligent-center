@php
    use Carbon\Carbon;
@endphp
@extends('layouts.app')
@section('content')

    <style>
        .text_icon_social {
            font-size: 18px;
            font-weight: 500;
            color: black;
            display: block;
            margin-top: -10px;
            margin-left: 130px;
        }

        .number-card_s {
            font-size: 30px;
            font-weight: 700;
        }

        .border_cicle {
            border: solid 4px #e4dcdc
        }
    </style>
    <section id="content" class="bg">
        <section class="hbox stretch">
            <aside id="hide-settings" class="aside aside-md b-r" style="display: none">
                <section class="vbox">
                    <header class="dk header b-b">
                        <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                            data-target="#setting-nav">@icon('solid/bars')</a>
                        <a
                            class="hide-setting btn btn-icon btn-default btn-sm pull-right btn-h-vis-menu">@icon('solid/bars')</a>
                        <p class="h3 text-elipse-setting">Credential Leak</p>
                    </header>
                    <section class="scrollable">
                        <section id="setting-nav" class="hidden-xs">
                            @include('partial.menu_data_leak')
                        </section>
                    </section>
                </section>
            </aside>
            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
                    <div class="header-flex-overflow m-t-7">
                        <div class="fwb-16">
                            @if (TYPE_WEB == 'center')
                                @if (@get_role_custom()['superadmin'] == 1 || @get_role_custom()['client'] == 1)
                                    <!-- <button
                                        class="show-setting btn btn-icon btn-default btn-sm m-r-xs">@icon('solid/bars')</button> -->
                                @endif
                            @endif
                            <span>
                                Credential Leak
                            </span>
                        </div>

                        <div class="ml-2 text-right">

                            <div class="text-left max-w-select {{ count($SiteSettings) == 1 ? 'd-none' : '' }}"
                                style="display:inline-block;">
                                <select name="site" id="site"
                                    class="text-left select2-option form-control select-site">
                                    {{-- <option value="">All Site</option>
                                    @if ($SiteSettings)
                                    @foreach ($SiteSettings as $SiteSettings_val)
                                    <option value="{{$SiteSettings_val->code}}">{{$SiteSettings_val->name}}</option>
                                    @endforeach
                                    @endif --}}
                                    @if (count($SiteSettings) == 1)
                                        @if ($SiteSettings)
                                            @foreach ($SiteSettings as $SiteSettings_val)
                                                <option value="{{ $SiteSettings_val->code }}" selected
                                                    data-site_id="{{ $SiteSettings_val->id }}"
                                                    data-site_code="{{ $SiteSettings_val->code }}">
                                                    {{ $SiteSettings_val->name }}</option>
                                            @endforeach
                                        @endif
                                    @else
                                        <option value="" selected>All Site</option>
                                        @if ($SiteSettings)
                                            @foreach ($SiteSettings as $SiteSettings_val)
                                                <option value="{{ $SiteSettings_val->code }}">{{ $SiteSettings_val->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    @endif
                                </select>
                            </div>


                            @if (!empty(get_role_custom()))
                                {{-- // var_dump(get_role_custom()['superadmin']);
                                // var_dump(get_role_custom()['site_admin']); --}}
                                @if (TYPE_WEB == 'center')
                                    @if (@get_role_custom()['superadmin'] == 1 || @get_role_custom()['client'] != 1)
                                        <!-- <a id="btn_dataleak_feed" href="{{ site_url('/datafeedsocial') }}"
                                            class="btn btn-sm btn-info m-l-xs"><span> Dataleak Feed</span></a> -->
                                    @endif
                                    @if (@get_role_custom()['client'] != 1)
                                        <a href="{{ route('dataleak.create') }}"
                                            class="btn btn-sm btn-{{ get_option('theme_color') }}" data-toggle="ajaxModal">
                                            <span data-rel="tooltip" title="Add"
                                                data-placement="top">@icon('solid/plus')</span>
                                            <span class="hide-text">@langapp('add')</span>
                                        </a>
                                    @endif
                                @endif
                            @endif

                            <a href="#hide-advance-search" id="advance-search"
                                class="btn btn-sm btn-{{ get_option('theme_color') }} ">
                                <span data-rel="tooltip" title="Filter" data-placement="bottom"><i
                                        class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></span>
                            </a>

                            @if (!empty(get_role_custom()))
                                @if (TYPE_WEB == 'center')
                                    @if (@get_role_custom()['client'] != 1)
                                        <button type="submit" id="btn-change-status" class="btn btn-sm btn-danger"
                                            value="bulk-delete" disabled>
                                            <span data-rel="tooltip" title="Delete" data-placement="bottom">
                                                @icon('solid/trash-alt')<span class="hide-text">@langapp('delete')</span>
                                            </span>
                                        </button>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>
                </header>

                
                <section class="scrollable wrapper">
                    <section class="panel panel-default" id="hide-advance-search" style="display: none">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-md-12">
                                    <i class="fas fa-filter"></i> Filter
                                </div>
                        </header>
                        <div class="panel-body" style="padding: 0 !important">
                            <div class="container-fluid" style="padding: 2rem;">
                                <div class="row">
                                    <div class="col-lg-6 mb-1">
                                        <h5 class="font-weight-bold">Keyword/Content</h5>
                                        <input type="text" id="keyword" class="form-control">
                                    </div>
                                    {{-- <div class="col-lg-4 mb-1">
                                    <h5 class="font-weight-bold">Source </h5>
                                    <select id="source" class="select2-option form-control">
                                        <option value="">All</option>
                                        @if ($source)
        
                                        @foreach ($source as $source)
                                        <option value="{{$source->id}}">{{$source->source}}
                                        </option>
                                        @endforeach
        
                                        @endif
                                    </select>
                                </div> --}}
                                    <div class="col-lg-6 mb-1">
                                        <h5 class="font-weight-bold">Date</h5>
                                        <div id="social_datas_date" class="text-center form-control"
                                            style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                            <i class="fa fa-calendar"></i>&nbsp;
                                            <span></span> <i class="fa fa-caret-down"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4 mb-1">
                                        <h5 class="font-weight-bold">Type</h5>
                                        <div id="groupby-type" class="btn-group special">
                                            <button id="all" class="btn btn-grey check_type active" value="">
                                                <span> All</span>
                                            </button>
                                            <button class="btn btn-grey check_type btn-social-click" value="surface_web">
                                                <span> Surface Web </span>
                                            </button>
                                            <button class="btn btn-grey check_type btn-darkweb-click" value="darkweb">
                                                <span> Darkweb </span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 hide-social" style="display: none">
                                        <h5 class="font-weight-bold">Surface Web</h5>
                                        <div id="groupby-social" class="btn-group special">
                                            <button id="all_surface" class="btn btn-grey active check_social"
                                                value="">
                                                <span> All</span>
                                            </button>
                                            <button class="btn btn-grey check_social" value="Website">
                                                <span> Website/forum</span>
                                            </button>
                                            <button class="btn btn-grey check_social" value="Social">
                                                <span> Social </span>
                                            </button>
                                            <button class="btn btn-grey check_social" value="Community">
                                                <span> Community </span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 hide-darkweb" style="display: none">
                                        <h5 class="font-weight-bold">Dark Web</h5>
                                        <div id="groupby-darkweb" class="btn-group special">
                                            <button id="all_darkweb" class="btn btn-grey active check_darkweb"
                                                value="">
                                                <span> All</span>
                                            </button>
                                            <button class="btn btn-grey check_darkweb" value="Website">
                                                <span> Website/forum</span>
                                            </button>
                                            <button class="btn btn-grey check_darkweb" value="Social">
                                                <span> Social </span>
                                            </button>
                                            <button class="btn btn-grey check_darkweb" value="Community">
                                                <span> Community </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 mb-1">
                                        <h5 class="font-weight-bold">Serverity</h5>
                                        <div id="btngroup_status" class="btn-group special ">

                                            <button class="btn btn-grey check_serverity active" value=""
                                                id="btn_search_all">
                                                <span> All </span>
                                            </button>
                                            <button class="btn check_serverity btn-grey" value="critical">
                                                <span> Critical </span>
                                            </button>
                                            <button class="btn check_serverity btn-grey" value="high">
                                                <span> High </span>
                                            </button>
                                            <button class="btn check_serverity btn-grey" value="medium">
                                                <span> Medium </span>
                                            </button>
                                            <button class="btn check_serverity btn-grey" value="low">
                                                <span> Low </span>
                                            </button>
                                            <button class="btn check_serverity btn-grey" value="information">
                                                <span> Information </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-6 mb-1">
                                        <h5 class="font-weight-bold">Status Monitoring</h5>
                                        <div id="btngroup_monitoring" class="btn-group special ">

                                            <button class="btn btn-grey check_monitoring active" value=""
                                                id="btn_monitoring">
                                                <span> All </span>
                                            </button>
                                            <button class="btn check_monitoring btn-grey" value="reported">
                                                <span> Reported </span>
                                            </button>
                                            <button class="btn check_monitoring btn-grey" value="in_progress">
                                                <span> Progress </span>
                                            </button>
                                            <button class="btn check_monitoring btn-grey" value="close">
                                                <span> Close </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="panel-footer">
                            <div class="row">
                                <div class="col-lg-12 text-right">
                                    <button type="button" id="btn_news_search"
                                        class="btn btn-info btn-responsive btn-fz-13" onclick="search()">
                                        <i class="fas fa-search"></i>
                                        @langapp('apply')
                                    </button>
                                    <button type="button" id="social_reset"
                                        class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                        <i class="fas fa-broom"></i>
                                        <span> Clear </span>
                                    </button>
                                    <button type="button" id="close_filter"
                                        class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                        <i class="fas fa-times"></i>
                                        <span> Close </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="container-fluid m-b-xs" style="margin-top: -15px;">
                        <div class="row m-b-15" style="margin-bottom: 5px;">
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" style="padding-left: 2px; padding-right: 2px;">
                                <a href="javascript:void(0)" onclick="dataType2('reported')" style="text-decoration: none;">
                                    <div style="background-color: #253448; height: 100px; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; border: 1px solid #e4e4e4; border-radius: 4px;">
                                        <div style="display: flex; flex-direction: column; align-items: flex-start;">
                                            <h3 class="text-white text-upper" style="margin: 0 0 5px 0; font-size: 18px;">Reported</h3>
                                            <span class="number-card number_reported" style="font-size: 30px; line-height: 1;color: #2CC470;">0</span>
                                        </div>
                                        <div style="width: 50px; height: 50px;">
                                            <img src="{{ asset('images/icon/dataleak/statistics.png') }}" alt=""
                                                onerror="setDefaultPic(this)" style="width: 100%; height: 100%; object-fit: contain;">
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" style="padding-left: 2px; padding-right: 2px;">
                                <a href="javascript:void(0)" onclick="dataType2('in_progress')" style="text-decoration: none;">
                                    <div style="background-color: #253448; height: 100px; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; border: 1px solid #e4e4e4; border-radius: 4px;">
                                        <div style="display: flex; flex-direction: column; align-items: flex-start;">
                                            <h3 class="text-white text-upper" style="margin: 0 0 5px 0; font-size: 18px;">In Progress</h3>
                                            <span class="number-card number_in_progress" style="font-size: 30px; line-height: 1;color: #00DCFF;">0</span>
                                        </div>
                                        <div style="width: 50px; height: 50px;">
                                            <img src="{{ asset('images/icon/dataleak/process.png') }}" alt=""
                                                onerror="setDefaultPic(this)" style="width: 100%; height: 100%; object-fit: contain;">
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" style="padding-left: 2px; padding-right: 2px;">
                                <a href="javascript:void(0)" onclick="dataType2('close')" style="text-decoration: none;">
                                    <div style="background-color: #253448; height: 100px; display: flex; align-items: center; justify-content: space-between; padding: 0 25px; border: 1px solid #e4e4e4; border-radius: 4px;">
                                        <div style="display: flex; flex-direction: column; align-items: flex-start;">
                                            <h3 class="text-white text-upper" style="margin: 0 0 5px 0; font-size: 18px;">Close</h3>
                                            <span class="number-card number_close" style="font-size: 30px; line-height: 1;color: #FFAB09;">0</span>
                                        </div>
                                        <div style="width: 50px; height: 50px;">
                                            <img src="{{ asset('images/icon/Close .png') }}" alt=""
                                                onerror="setDefaultPic(this)" style="width: 100%; height: 100%; object-fit: contain;">
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="row" style="display: flex; flex-wrap: nowrap; overflow-x: auto; min-width: 900px; align-items: flex-start; gap: 4px;">
                            <div id="all-card-wrapper" style="flex: 0 0 330px; min-width: 330px; align-self: stretch; display: flex;">
                                <div style="border: 1px solid #e4e4e4; border-radius: 4px; overflow: hidden; background: #fff; margin-bottom: 2px; flex: 1; display: flex; flex-direction: column;">
                                    <div onclick="var w=$('#all-card-wrapper'); var c=$('#online-domains-content'); if(c.is(':visible')){w.css('align-self','flex-start');c.slideUp(300);}else{c.slideDown(300,function(){w.css('align-self','stretch');})} $(this).find('.fa-chevron-circle-down').toggleClass('fa-rotate-180');" 
                                        style="background: linear-gradient(to right, #253448 60%, #3b5170ff 100%) !important; padding: 15px 25px; color: white; display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                                        <div style="display: flex; align-items: center;">
                                            <i class="fas fa-table" style="font-size: 18px; margin-right: 10px;"></i>
                                            <h3 style="margin: 0; font-size: 18px; font-weight: 500;">ALL</h3>
                                        </div>
                                        <i class="fas fa-chevron-circle-down fa-rotate-180" style="transition: transform 0.3s; font-size: 24px;"></i>
                                    </div>
                                    <div id="online-domains-content">
                                        <div style="display: flex; flex-wrap: nowrap; height: 100%;">
                                            <div style="flex: 1; border-right: 1px solid #e4e4e4; min-width: 80px;">
                                                <a href="#" onclick="dataType('social')"
                                                    style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 30px; text-decoration: none; color: inherit; height: 100%;">
                                                    <div style="width: 80px; height: 80px; margin-bottom: 10px;">
                                                        <img src="{{ asset('images/icebergline2.png') }}"
                                                            alt="" onerror="setDefaultPic(this)"
                                                            style="width: 100%; height: 100%; object-fit: contain;">
                                                    </div>
                                                    <span class="number-card" style="font-size: 28px; font-weight: 700; color: #3869D4; line-height: 1.2;"
                                                        id="compromise-count">0</span>
                                                    <span style="font-size: 14px; font-weight: 700; color: #000; text-transform: uppercase; min-height: 36px; display: flex; align-items: center;">SURFACE WEB</span>
                                                </a>
                                            </div>
                                            <div style="flex: 1; min-width: 80px;">
                                                <a href="#" onclick="dataType('darkweb_public')"
                                                    style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 30px; text-decoration: none; color: inherit; height: 100%;">
                                                    <div style="width: 80px; height: 80px; margin-bottom: 10px;">
                                                        <img src="{{ asset('images/icebergline1.png') }}"
                                                            alt="" onerror="setDefaultPic(this)"
                                                            style="width: 100%; height: 100%; object-fit: contain;">
                                                    </div>
                                                    <span class="number-card" style="font-size: 28px; font-weight: 700; color: #FCC838; line-height: 1.2;"
                                                        id="darkweb-count">0</span>
                                                    <span style="font-size: 14px; font-weight: 700; color: #000; text-transform: uppercase; min-height: 36px; display: flex; align-items: center;">DARK WEB</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="flex: 1; min-width: 700px;">
                                <div style="display: flex; flex-wrap: nowrap; gap: 4px;">
                                    <div style="flex: 1; min-width: 280px;">
                                        <div style="border: 1px solid #e4e4e4; border-radius: 4px; overflow: hidden; background: #fff; margin-bottom: 2px;">
                                            <div onclick="$('#social-content').slideToggle(300); $(this).find('.fa-chevron-circle-down').toggleClass('fa-rotate-180');" 
                                                style="background: linear-gradient(to right, #253448 60%, #3b5170ff 100%) !important; padding: 15px 25px; color: white; display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                                                <div style="display: flex; align-items: center;">
                                                    <i class="fas fa-table" style="font-size: 18px; margin-right: 10px;"></i>
                                                    <h3 style="margin: 0; font-size: 18px; font-weight: 500;">Surface Web</h3>
                                                </div>
                                                <i class="fas fa-chevron-circle-down fa-rotate-180" style="transition: transform 0.3s; font-size: 24px;"></i>
                                            </div>
                                            <div id="social-content">
                                                <div style="display: flex; flex-wrap: wrap; align-items: stretch;">
                                                    <div style="width: 33.33%; border-right: 1px solid #e4e4e4;">
                                                        <a href="javascript:void(0)" onclick="dataType2('website_s')"
                                                            style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 30px 20px; text-decoration: none; color: inherit; height: 100%;">
                                                            <div style="width: 80px; height: 80px; margin-bottom: 10px;">
                                                                <img src="{{ asset('images/icon/dataleak/s_website.png') }}" alt=""
                                                                    onerror="setDefaultPic(this)"
                                                                    style="width: 100%; height: 100%; object-fit: contain;">
                                                            </div>
                                                            <span id="icon_website_s" class="number-card_s" style="font-size: 28px; font-weight: 700; color: #3869D4; line-height: 1.2;">0</span>
                                                            <span style="font-size: 14px; font-weight: 600; color: #000; text-align: center; min-height: 36px; display: flex; align-items: center;">Website / Forum</span>
                                                        </a>
                                                    </div>
                                                    <div style="width: 33.33%; border-right: 1px solid #e4e4e4;">
                                                        <a href="javascript:void(0)" onclick="dataType2('social_s')"
                                                            style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 30px 20px; text-decoration: none; color: inherit; height: 100%;">
                                                            <div style="width: 80px; height: 80px; margin-bottom: 10px;">
                                                                <img src="{{ asset('images/icon/dataleak/s_social.png') }}" alt=""
                                                                    onerror="setDefaultPic(this)"
                                                                    style="width: 100%; height: 100%; object-fit: contain;">
                                                            </div>
                                                            <span id="icon_social_s" class="number-card_s" style="font-size: 28px; font-weight: 700; color: #FCC838; line-height: 1.2;">0</span>
                                                            <span style="font-size: 14px; font-weight: 600; color: #000; text-align: center; min-height: 36px; display: flex; align-items: center;">Social</span>
                                                        </a>
                                                    </div>
                                                    <div style="width: 33.33%;">
                                                        <a href="javascript:void(0)" onclick="dataType2('community_s')"
                                                            style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 30px 20px; text-decoration: none; color: inherit; height: 100%;">
                                                            <div style="width: 80px; height: 80px; margin-bottom: 10px;">
                                                                <img src="{{ asset('images/icon/dataleak/s_community.png') }}"
                                                                    alt="" onerror="setDefaultPic(this)"
                                                                    style="width: 100%; height: 100%; object-fit: contain;">
                                                            </div>
                                                            <span id="icon_community_s" class="number-card_s" style="font-size: 28px; font-weight: 700; color: #FF0000; line-height: 1.2;">0</span>
                                                            <span style="font-size: 14px; font-weight: 600; color: #000; text-align: center; min-height: 36px; display: flex; align-items: center;">Community</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="flex: 1; min-width: 280px;">
                                        <div style="border: 1px solid #e4e4e4; border-radius: 4px; overflow: hidden; background: #fff; margin-bottom: 2px;">
                                            <div onclick="$('#social-content-2').slideToggle(300); $(this).find('.fa-chevron-circle-down').toggleClass('fa-rotate-180');" 
                                                style="background: linear-gradient(to right, #253448 60%, #3b5170ff 100%) !important; padding: 15px 25px; color: white; display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                                                <div style="display: flex; align-items: center;">
                                                    <i class="fas fa-table" style="font-size: 18px; margin-right: 10px;"></i>
                                                    <h3 style="margin: 0; font-size: 18px; font-weight: 500;">Dark Web</h3>
                                                </div>
                                                <i class="fas fa-chevron-circle-down fa-rotate-180" style="transition: transform 0.3s; font-size: 24px;"></i>
                                            </div>
                                            <div id="social-content-2">
                                                <div style="display: flex; flex-wrap: wrap; align-items: stretch;">
                                                    <div style="width: 33.33%; border-right: 1px solid #e4e4e4;">
                                                        <a href="javascript:void(0)" onclick="dataType2('website_d')"
                                                            style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 30px 20px; text-decoration: none; color: inherit; height: 100%;">
                                                            <div style="width: 80px; height: 80px; margin-bottom: 10px;">
                                                                <img src="{{ asset('images/icon/dataleak/d_website.png') }}" alt=""
                                                                    onerror="setDefaultPic(this)"
                                                                    style="width: 100%; height: 100%; object-fit: contain;">
                                                            </div>
                                                            <span id="icon_website_d" class="number-card_s" style="font-size: 28px; font-weight: 700; color: #6F57E9; line-height: 1.2;">0</span>
                                                            <span style="font-size: 14px; font-weight: 600; color: #000; text-align: center; min-height: 36px; display: flex; align-items: center;">Website / Forum</span>
                                                        </a>
                                                    </div>
                                                    <div style="width: 33.33%; border-right: 1px solid #e4e4e4;">
                                                        <a href="javascript:void(0)" onclick="dataType2('social_d')"
                                                            style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 30px 20px; text-decoration: none; color: inherit; height: 100%;">
                                                            <div style="width: 80px; height: 80px; margin-bottom: 10px;">
                                                                <img src="{{ asset('images/icon/dataleak/d_social.png') }}" alt=""
                                                                    onerror="setDefaultPic(this)"
                                                                    style="width: 100%; height: 100%; object-fit: contain;">
                                                            </div>
                                                            <span id="icon_social_d" class="number-card_s" style="font-size: 28px; font-weight: 700; color: #2CC470; line-height: 1.2;">0</span>
                                                            <span style="font-size: 14px; font-weight: 600; color: #000; text-align: center; min-height: 36px; display: flex; align-items: center;">Social</span>
                                                        </a>
                                                    </div>
                                                    <div style="width: 33.33%;">
                                                        <a href="javascript:void(0)" onclick="dataType2('community_d')"
                                                            style="display: flex; flex-direction: column; align-items: center; justify-content: flex-start; padding: 30px 20px; text-decoration: none; color: inherit; height: 100%;">
                                                            <div style="width: 80px; height: 80px; margin-bottom: 10px;">
                                                                <img src="{{ asset('images/icon/dataleak/d_community.png') }}"
                                                                    alt="" onerror="setDefaultPic(this)"
                                                                    style="width: 100%; height: 100%; object-fit: contain;">
                                                            </div>
                                                            <span id="icon_community_d" class="number-card_s" style="font-size: 28px; font-weight: 700; color: #FFAB09; line-height: 1.2;">0</span>
                                                            <span style="font-size: 14px; font-weight: 600; color: #000; text-align: center; min-height: 36px; display: flex; align-items: center;">Community</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>

                
                    <section class="panel panel-default">
                        <header class="panel-heading font-bold panel-header-blue" style="background: linear-gradient(to right, #253448 60%, #4d6b94ff 100%) !important; color: white !important;">
                            <div class="row">
                                <div class="col-xs-12">
                                    <i class="fas fa-table"></i> Table Data Leak
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">
                            <div class="row" style="display: flex; align-items: flex-end;">
                                <div class="col-md-6">
                                    <h5 class="font-weight-bold"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Severity</h5>
                                    <div class="st-dt-leak">
                                        <span class="st-dt vrh" data-toggle="tooltip" data-placement="right"
                                            data-html="true"
                                            title="<div class='st-flex'><div class='box-st-tooltip vrh'>Critical</div><div class='text-st-tooltip'>Critical	ข้อมูลรั่วไหลและเป็นที่รับรู้อย่างแพร่หลาย เช่น <br> ออกข่าว หรือมีการแชร์ข้อมูลจากแหล่งข้อมูลที่น่าเชื่อถือและเป็นที่แพร่หลาย <br> และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูล Username ,Password ของลูกค้าหรือเจ้าหน้าที่ดูแลระบบภายในองค์กรซึ่งเป็นข้อมูลที่สามารถนำมาใช้ได้จริง</div></div>">Critical</span>
                                        <span class="st-dt high" data-toggle="tooltip" data-placement="right"
                                            data-html="true"title="<div class='st-flex'><div class='box-st-tooltip high'>High</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้อย่างแพร่หลาย เช่น ออกข่าว หรือมีการแชร์ข้อมูลจากแหล่งข้อมูลที่น่าเชื่อถือและเป็นที่แพร่หลาย และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูลส่วนบุคคลซึ่งเป็นข้อมูลที่สามารถนำมาใช้ประโยชน์ต่อได้</div></div>">High</span>
                                        <span class="st-dt md" data-toggle="tooltip" data-placement="right"
                                            data-html="true"
                                            title="<div class='st-flex'><div class='box-st-tooltip md'>Medium</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้ภายในกลุ่มจำกัดหรือยังไม่เป็นที่รับรู้กันอย่างแพร่หลาย และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของระบบเช่นข้อมูล Username ,Password ของลูกค้าหรือเจ้าหน้าที่ดูแลระบบภายในองค์กรซึ่งเป็นข้อมูลที่สามารถนำมาใช้ได้จริง</div></div>">Medium</span>
                                        <span class="st-dt low" data-toggle="tooltip" data-placement="right"
                                            data-html="true"
                                            title="<div class='st-flex'><div class='box-st-tooltip low'>Low</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลและเป็นที่รับรู้ภายในกลุ่มจำกัดหรือยังไม่เป็นที่รับรู้กันอย่างแพร่หลาย และข้อมูลที่รั่วไหลเป็นข้อมูลสำคัญของลูกค้าเช่น ข้อมูลส่วนบุคคลซึ่งเป็นข้อมูลที่สามารถนำมาใช้ประโยชน์ต่อได้</div></div>">Low</span>
                                        <span class="st-dt vrl" data-toggle="tooltip" data-placement="right"
                                            data-html="true"
                                            title="<div class='st-flex'><div class='box-st-tooltip vrl'>Informational</div><div class='text-st-tooltip'>ข้อมูลรั่วไหลที่เป็นข้อมูลทั่วไปหรือเป็นข่าวที่ยังไม่ได้รับการยืนยันว่าเป็นข้อมูลรั่วไหลจริง</div></div>">Very
                                            Low</span>
                                    </div>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button class="btn btn-sm btn-success ms-2" style="margin-left:10px;" id="btn-export-excel" >
                                        <i class="fa fa-download"></i> Export Credential Leak
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped" style="width: 100%;" id="table_social_datas">
                                    <thead>
                                        <tr>
                                            <th class="no-sort w-10">
                                                <label>
                                                    <input name="select_all" value="1" id="select-all"
                                                        type="checkbox" class="select-chk" />
                                                    <span class="label-text"></span>
                                                </label>
                                            </th>
                                            <th>Site</th>
                                            <th>Type</th>
                                            <th>Source</th>
                                            <th>Keyword</th>
                                            <th>Content</th>
                                            <th>Severity</th>
                                            <th>Monitoring</th>
                                            <th>Data Feed</th>
                                            {{-- <th>View</th> --}}
                                            @if (!empty(get_role_custom()))
                                                @if (@get_role_custom()['client'] != 1)
                                                    <th>Status</th>
                                                @endif
                                            @endif
                                            <th>@langapp('action')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- <tr>
                                            <td>
                                                <label>
                                                    <input name="select_all" value="1" type="checkbox" class="select-chk"/>
                                                    <span class="label-text"></span>
                                                </label>
                                            </td>
                                            <td>
                                                Pantip
                                            </td>
                                            <td>
                                                Fibre
                                            </td>
                                            <td>
                                                Lorem ipsum dolor sit amet consectetur adipisicing elit. Dignissimos,
                                            </td>
                                            <td class="no-wrap">
                                                2020-12-2020 12:12
                                            </td>
                                            <td>
                                                1
                                            </td>
                                            <td>
                                                <label class="switch">
                                                    <input type="hidden" value="FALSE" name="">
                                                    <input type="checkbox" name="status" checked value="TRUE">
                                                    <span></span>
                                                </label>
                                            </td>
                                            <td class="no-wrap text-center">
                                                <button class="btn btn-danger btn-xs">
                                                    @icon('solid/trash-alt')
                                                </button>
                                            </td>
                                        </tr> --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </section>
            </section>
        </section>

        @if (@get_role_custom()['superadmin'] == 1 || @get_role_custom()['site_admin'] == 1)
            @php $admin = 1; @endphp
        @else
            @php $admin = 0; @endphp
        @endif

        <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

        {{-- <div class="modal in fixed-left" id="delete_all" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <span class="modal-title" id="exampleModalLabel">Delete</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="">
                <div class="modal-body">
                    <p class="text-danger">@langapp('delete_warning')  </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="submit" class="delete-all btn btn-danger btn-rounded">
                        <i class="fas fa-paper-plane"></i>
                        Delete
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div> --}}

        <div class="modal" id="delete_all" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
            aria-hidden="true" style="left: unset">
            <div class="modal-dialog modal-dialog-aside" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">@langapp('delete')</h4>
                    </div>
                    <div class="modal-body">
                        <div class="container-fluid">
                            <p class="text-danger">@langapp('delete_warning') </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#" class="btn btn-default btn-rounded" data-dismiss="modal"><i
                                class="fas fa-times text-muted"></i> Close</a>
                        <button type="button" class="btn btn-info submit btn-rounded delete-all">
                            <i class="fas fa-paper-plane"></i> OK</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal create_assets_vulnerability -->
        {{-- <div class="modal in fixed-left" id="change_status" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        Confirm Information
                    </h4>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="" class="col-md-3">Status</label>
                        <div class="col-md-9">
                            <select name="" id="" class="form-control">
                                <option value="1">Approved</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="submit" class="btn btn-info btn-rounded">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div> --}}

    </section>


    {{-- for (var i = 0; i < response.model.length; i++) {
    $('#fillter_click_keyword').html(`<a class="btn btn-selector" href="javascript:void(0)" onclick="click_keyword('${response.model[i]['keyword']}')">${response.model[i]['keyword']} (${response.model[i]['count_keyword']})</a>`);
} --}}


    {{-- "fnRowCallback": function( nRow, aData, iDisplayIndex ) {
    var date_day = '2021-01-01';
    var date = aData.get_data_leak_feed_one.feedtimepost;
    var date_sp = date.split(" ");
    if(date_sp.length > 0) {
        date_day = date_sp[0];
    }

    var date_now = '{{Carbon::now()}}';
    let date_now_sp = date_now.split(" ");
    if(date_now_sp.length > 0) {
        var date_now_day = date_now_sp[0];
    }

    console.log(date_day+' now:'+date_now_day);
    if ((date_day) == (date_now_day)) {
        $('td:eq(0)', nRow).addClass("custom_new");
        $('td:eq(1)', nRow).addClass("custom_new");
        $('td:eq(2)', nRow).addClass("custom_new");
        $('td:eq(3)', nRow).addClass("custom_new");
        $('td:eq(4)', nRow).addClass("custom_new");
        $('td:eq(5)', nRow).addClass("custom_new");
        $('td:eq(6)', nRow).addClass("custom_new");
        $('td:eq(7)', nRow).addClass("custom_new");
        $('td:eq(8)', nRow).addClass("custom_new");
        $('td:eq(9)', nRow).addClass("custom_new");
        $('td:eq(10)', nRow).addClass("custom_new");
    }
}, --}}

    @push('pagestyle')
        @include('stacks.css.datatables')
        @include('stacks.css.datepicker')
        @include('stacks.css.form')
        <link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
    @endpush

    @push('pagescript')
        @include('stacks.js.datatables')
        @include('stacks.js.form')
        @include('stacks.js.datepicker')
        @include('stacks.js.daterangpicker')
        @include('stacks.js.menusub')
        @include('stacks.js.hidesettings')
        @include('stacks.js.advanced_search')
        @include('stacks.js.activebutton')
        @include('stacks.js.fullscreen')
        @include('stacks.js.defaultpic')
        <script>
            var click_key = null;
            var table;

            {{-- active_btn('#fillter_click_keyword .btn-selector'); --}}
            active_btn('#groupby-type .btn-grey');
            active_btn('#groupby-social .btn-grey');
            active_btn('#btngroup_status .btn-grey');
            active_btn('#btngroup_monitoring .btn-grey');


            function myFunction(x) {
                let area_logo_os = document.getElementById("area_pp");
                let left1 = document.getElementById("left1");
                let left2 = document.getElementById("left2");
                let left3 = document.getElementById("left3");
                let right1 = document.getElementById("right1");
                let right2 = document.getElementById("right2");
                let right3 = document.getElementById("right3");
                if (x.matches) {
                    if (area_logo_os) area_logo_os.style.minHeight = "0px";
                    if (left1) left1.style.marginLeft = "60px";
                    if (left2) left2.style.marginLeft = "60px";
                    if (left3) left3.style.marginLeft = "60px";
                    if (right1) right1.style.marginLeft = "60px";
                    if (right2) right2.style.marginLeft = "60px";
                    if (right3) right3.style.marginLeft = "60px";
                } else {
                    if (area_logo_os) area_logo_os.style.minHeight = "151px";
                    if (left1) left1.style.marginLeft = "90px";
                    if (left2) left2.style.marginLeft = "90px";
                    if (left3) left3.style.marginLeft = "90px";
                    if (right1) right1.style.marginLeft = "90px";
                    if (right2) right2.style.marginLeft = "90px";
                    if (right3) right3.style.marginLeft = "90px";
                }
            }


            var x = window.matchMedia("(max-width: 990px)");
            myFunction(x);
            x.addListener(myFunction);




            $('.btn').click(function() {
                if ($('.btn-social-click').hasClass('active')) {
                    $('.hide-social').show();
                } else {
                    $('.hide-social').hide();
                }
                if ($('.btn-darkweb-click').hasClass('active')) {
                    $('.hide-darkweb').show();
                } else {
                    $('.hide-darkweb').hide();
                }
            });

            var admin = '{{ $admin }}';
            var visible_c = '';

            if (admin == 1) {
                visible_c = true;
            } else {
                visible_c = false;
            }

            var id_select_site = 'site';
            var search_val = 0;
            var keywords = null;
            var site = null;
            var type = null;
            var source = null;
            var startDate = null;
            var endDate = null;
            var isDateSearch = null;
            var social_id = [];
            var check_type = null;
            var check_serverity = null;
            var check_monitoring = null;
            var check_social = null;
            var check_darkweb = null;
            var click_type = null;
            var click_type2 = null;
            var click_key = null;
            var site_code = null;

            @if (!empty(get_role_custom()))
                @if (@get_role_custom()['client'] == 1)
                    site = $('#site').find(':selected').attr("data-site_id");
                    site_code = $('#site').find(':selected').attr("data-site_code");
                @endif
            @endif
            $('#table_social_datas').on('click', '.select-chk', function() {
                if ($(this).is(':checked')) {

                    $('#btn-change-status').prop("disabled", false);
                } else {

                    if ($('.select-chk').filter(':checked').length < 1) {

                        $('#btn-change-status').attr('disabled', true);
                    }
                }
            });

            $('#table_social_datas').on('click', '.social_id', function() {
                if ($(this).is(':checked')) {


                    $('#btn-change-status').prop("disabled", false);
                } else {
                    if ($('.social_id').filter(':checked').length < 1) {

                        $('#btn-change-status').attr('disabled', true);
                    }
                }
            });

            $(function() {
                if (get_cookie_site()) {
                    cookie_change_site("{{ route('systemsetting.check_cookie_site') }}", id_select_site);
                    count_icon();
                } else {
                    table_social_data();
                    get_count();
                    {{-- count_keyword(); --}}
                    count_icon();
                }
            });

            $(".check_type").click(function() {
                check_type = $(this).val();

            });
            $(".check_social").click(function() {
                check_social = $(this).val();

            });

            $(".check_darkweb").click(function() {
                check_darkweb = $(this).val();
                $('.check_darkweb').removeClass('active');
                $(this).addClass('active');
            });

            $(".check_serverity").click(function() {
                check_serverity = $(this).val();
            });

            $(".check_monitoring").click(function() {
                check_monitoring = $(this).val();
            });

            $("#site").change(function() {
                set_cookie_site($(`#${id_select_site}`).val());
                site = this.value;
                site_code = this.value;
                table_social_data();
                get_count();
                {{-- count_keyword(); --}}
                count_icon();
            });

            function search() {
                click_key = null;
                click_type2 = null;
                click_type = null;
                search_val = 1;
                keywords = $('#keyword').val();
                type = $('#type option:selected').val();
                source = $('#source option:selected').val();
                startDate = $("#social_datas_date").data('daterangepicker').startDate.format('YYYY-MM-DD hh:mm A');
                endDate = $("#social_datas_date").data('daterangepicker').endDate.format('YYYY-MM-DD hh:mm A');
                $('.btn-selector').removeClass('active');

                table_social_data();
                count_icon();
                get_count();
                {{-- get_count(); --}}
            }

            function table_social_data() {
                table = $('#table_social_datas').DataTable({
                    pageLength: 50,
                    processing: true,
                    serverSide: true,
                    destroy: true,
                    "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
                    ajax: {
                        type: "POST",
                        url: '{!! route('socialdatas.credentialdatas_all_site_tb') !!}',
                        data: function(d) {
                            d.keywords = keywords;
                            d.site = site_code;
                            d.type = type;
                            d.source = source;
                            d.search_val = search_val;
                            d.startDate = startDate;
                            d.endDate = endDate;
                            d.isDateSearch = isDateSearch;
                            d.check_type = check_type;
                            d.click_type = click_type;
                            d.click_key = click_key;
                            d.click_type2 = click_type2;
                            d.check_serverity = check_serverity;
                            d.check_monitoring = check_monitoring;
                            d.check_social = check_social;
                            d.check_darkweb = check_darkweb;

                            return d;
                        },
                    },
                    initComplete: function(settings, json) {
                        $('[data-rel="tooltip"]').tooltip();


                    },
                    createdRow: function(row, data, index) {
                        $(row).attr('id', 'tr' + data.id);
                    },
                    "order": [8, 'desc'],
                    columnDefs: [{
                            targets: 0,
                            orderable: false,
                            searchable: false,
                            sortable: false,
                            width: '1px',
                            render: function(data, type, full, meta) {
                                return '<label><input type="checkbox" name="social_id" class="social_id"  value="' +
                                    full.ref_id + '"><span class="label-text"></span></label>';
                            },
                        },
                        {
                            targets: 1,
                            width: '10px',
                            className: 'nowrap',
                            render: function(data, type, full, meta) {

                                val = full.site_name.length > 200 ? full.site_name.substr(0, 20) + ' ...' : full
                                    .site_name;

                                return val;

                            },
                        },
                        {
                            targets: 2,
                            width: '60px',
                            className: 'nowrap',
                            render: function(data, type, full, meta) {
                                if (full.feel_type) {
                                    return get_word_leak_compromise(full.feel_type, 'data_leak');
                                }
                                return '-';

                            },

                        },
                        {
                            targets: 3,
                            width: '60px',
                            className: 'nowrap',
                            render: function(data, type, full, meta) {

                                if (full.source_name) {
                                    return full.source_name;
                                }
                                return '-';

                            },

                        },
                        {
                            targets: 4,
                            width: '10px',
                            render: function(data, type, full, meta) {


                                return full.keyword;
                                if (full.keyword) {
                                    return full.keyword;
                                }
                                return '-';

                            },




                        },
                        {
                            targets: 5,
                            width: '100%',
                            className: 'truncatecontent',
                            render: function(data, type, full, meta) {

                                var new_html = '';
                                var date_day = '2021-01-01';
                                var date = full.feedtimepost;
                                var date_sp = date.split(" ");
                                if (date_sp.length > 0) {
                                    date_day = date_sp[0];
                                }

                                var date_now = '{{ Carbon::now() }}';
                                let date_now_sp = date_now.split(" ");
                                if (date_now_sp.length > 0) {
                                    var date_now_day = date_now_sp[0];
                                }
                                if ((date_day) == (date_now_day)) {
                                    {{-- new_html += `<img src="{{asset('images/icon/new.png')}}" style="width:40px; border-radius: 10px;">`; --}}
                                    new_html +=
                                        `<span class="badge" style="background-color: #2196f3;">New</span>`;
                                }

                                if (full.feedcontent) {
                                    var feedcontent = stripHtml(full.feedcontent);
                                    
                                    var res = full.keyword.split(",");
                                    let content = '';
                                    for (let i in res) {
                                        const data2 = res[i];
                                        content += feedcontent.replaceAll(data2,
                                            '<span class="badge bg-warning">' + data2 + '</span>');
                                    }
                                    
                                    return (new_html + full.feedcontent).replace('<p>','').replace('</p>','');

                                } else {
                                    return '-';
                                }

                            },
                        },
                        {
                            targets: 7,
                            width: '10px',
                            className: 'nowrap',
                            render: function(data, type, full, meta) {

                                if (full.ref_status_monitoring == 'in_progress') {
                                    return '<span class="badge" style="background-color: #FFC107; font-size: 13px; width: 95px; display: inline-block; text-align: center; padding: 8px 0; border-radius: 50px;">Progress</span>';
                                } else if (full.ref_status_monitoring == 'reported') {
                                    return '<span class="badge" style="background-color: #28A745; font-size: 13px; width: 95px; display: inline-block; text-align: center; padding: 8px 0; border-radius: 50px;">Reported</span>';
                                } else if (full.ref_status_monitoring == 'close') {
                                    return '<span class="badge" style="background-color: #DC3545; font-size: 13px; width: 95px; display: inline-block; text-align: center; padding: 8px 0; border-radius: 50px;">Close</span>';
                                } else {
                                    return '-';
                                }

                            },
                        },
                        {
                            targets: 6,
                            width: '8px',
                            className: 'nowrap',
                            render: function(data, type, full, meta) {
                                if (full.ref_serverity == 'critical') {
                                    return '<span class="badge" style="background-color: #b93624; font-size: 13px; width: 95px; display: inline-block; text-align: center; padding: 8px 0; border-radius: 50px;">Critical</span>';
                                } else if (full.ref_serverity == 'high') {
                                    return '<span class="badge" style="background-color: #fcc838; font-size: 13px; width: 95px; display: inline-block; text-align: center; padding: 8px 0; border-radius: 50px;">High</span>';
                                } else if (full.ref_serverity == 'medium') {
                                    return '<span class="badge" style="background-color: #f2ff15;color:#333; font-size: 13px; width: 95px; display: inline-block; text-align: center; padding: 8px 0; border-radius: 50px;">Medium</span>';
                                } else if (full.ref_serverity == 'low') {
                                    return '<span class="badge" style="background-color: #409967; font-size: 13px; width: 95px; display: inline-block; text-align: center; padding: 8px 0; border-radius: 50px;">Low</span>';
                                } else if (full.ref_serverity == 'information') {
                                    return '<span class="badge" style="background-color: #00dcff; font-size: 13px; width: 95px; display: inline-block; text-align: center; padding: 8px 0; border-radius: 50px;">Information</span>';
                                } else {
                                    return '-';
                                }

                            },
                        },
                        {
                            targets: 8,
                            width: '8%',
                            className: 'nowrap',
                            render: function(data, type, full, meta) {

                                if (full.feedtimepost) {
                                    return full.feedtimepost;
                                } else {
                                    return '';
                                }

                            },
                        },
                        @if (!empty(get_role_custom()))
                            @if (@get_role_custom()['client'] != 1)
                                {
                                    visible: visible_c,
                                    targets: 9,
                                    width: '3%',
                                    render: function(data, type, full, meta) {

                                        var checked_val = null;
                                        if (full.ref_status == 1) {
                                            checked_val = 'checked';
                                        } else {
                                            checked_val = '';
                                        }

                                        return '<label class="switch"><input type="checkbox" id="social_active_' +
                                            full.ref_id + '" onchange="social_active( ' + full.ref_id +
                                            ')" ' + checked_val +
                                            ' name="active" value="1"><span class="slider round"></span></label>';

                                    }

                                },
                            @endif
                        @endif

                        {
                            targets: -1,
                            width: '7%',
                            className: 'nowrap',
                            render: function(data, type, full, meta) {
                                @if (!empty(get_role_custom()))
                                    @if (@get_role_custom()['client'] != 1)
                                        return `
                                <a href="${base_url}/socialdatas/activity_modal/${full.code_data}" class="btn btn-info btn-xs" data-toggle="ajaxModal"><i class="far fa-comment-dots"></i></a>
                                <!--<a href="${base_url}/socialdatas/view_content/${full.code_data}" class="btn btn-info btn-xs" data-toggle="ajaxModal"><i class="fas fa-eye"></i></a>-->
                                <a href="${base_url}/dataleak/edit_dataleak_modal/${full.code_data}" class="btn btn-info btn-xs" data-toggle="ajaxModal">
                                    <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                                </a>
                                <a href="${base_url}/socialdatas/delete_dataleakdata_modal/${full.code_data}" class="btn btn-danger btn-xs" data-toggle="ajaxModal"><i class="fas fa-trash-alt"></i></a>
                                `;
                                    @else

                                        return `
                                <a href="${base_url}/socialdatas/activity_modal/${full.code_data}" class="btn btn-info btn-xs" data-toggle="ajaxModal"><i class="far fa-comment-dots"></i></a>
                               `;
                                    @endif
                                @endif
                                {{-- href="${base_url}/rssfeedsettings/delete-rss_data/${full.code}" --}}
                            },
                        },

                    ],
                    createdRow: function(row) {
                        var tdcontent = $(row).find(".truncatecontent");
                        tdcontent.attr("title", tdcontent.text());
                    }

                });
            }

            function stripHtml(html) {
                var temporalDivElement = document.createElement("div");
                temporalDivElement.innerHTML = html;
                return temporalDivElement.textContent || temporalDivElement.innerText || "";
            }

            function social_active(id) {

                let checkState = $("#social_active_" + id).is(":checked") ? 1 : 0;
                axios.post('{{ route('socialdatas.change_status_dataleakdata') }}', {
                    status: checkState,
                    code: id,
                }).then(function(response) {
                    toastr.success(response.data.message, '@langapp('response_status')');
                    window.location.href = response.data.redirect;
                }).catch(function(error) {
                    var errors = error.response.data.errors;
                    var errorsHtml = "";
                    $.each(errors, function(key, value) {
                        errorsHtml += "<li>" + value[0] + "</li>";
                    });
                    toastr.error(errorsHtml, '@langapp('response_status')');
                });
            }

            $(function() {

                var start = moment().subtract(1, 'month').startOf('month');
                {{-- moment().startOf('hour') --}} {{-- moment().subtract(1, 'year').startOf('year') --}}
                var end = moment();
                {{-- moment().startOf('hour').add(32, 'hour') --}} {{-- moment().subtract(0, 'year').endOf('year') --}}

                function cb(start, end) {
                    $('#social_datas_date span').html(start.format('MMMM D, YYYY') + ' - ' + end.format(
                    'MMMM D, YYYY'));

                }

                $('#social_datas_date').daterangepicker({
                    timePicker: true,
                    {{-- timePicker24Hour: true, --}}
                    startDate: start,
                    endDate: end,
                    locale: {
                        format: 'M/DD hh:mm A'
                        {{-- format: 'M/DD HH:mm A' --}}
                    },
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                            'month').endOf('month')]
                    }
                }, cb);
                $('#social_datas_date').on('apply.daterangepicker', function(ev, picker) {
                    isDateSearch = 1;
                    if (!picker.startDate.isValid() || !picker.endDate.isValid()) {

                    }
                });

                cb(start, end);

                $("#social_reset").click(function() {
                    click_key = null;
                    click_type = null;
                    click_type2 = null;
                    keywords = null;
                    type = null;
                    source = null;
                    startDate = null;
                    endDate = null;
                    site = null;
                    search_val = 0;
                    isDateSearch = null;
                    check_serverity = null;

                    $("#keyword").val('');
                    start = moment().subtract(1, 'month').startOf('month');
                    end = moment();
                    cb(start, end);
                    $("#site").val('').trigger("change");
                    $("#source").val('').trigger("change");
                    $('.check_type').removeClass('active');
                    $('.selector').removeClass('active');
                    $('#all').addClass('active');
                    $('.check_serverity').removeClass('active');
                    $('#btn_search_all').addClass('active');
                    $('.check_monitoring').removeClass('active');
                    $('#btn_monitoring').addClass('active');
                    check_monitoring = null;
                    check_type = null;
                    $('.hide-social').hide();
                    table_social_data();
                    get_count();


                });
            });



            function get_count() {
                var local_search_val = (search_val == true) ? 1 : 0;

                $.ajax({
                    type: "POST",
                    url: "{!! route('socialdatas.credentialdatas_count_val') !!}",
                    data: {
                        keywords: keywords,
                        site_id: site_code,
                        type: type,
                        social: source,
                        search_val: local_search_val,
                        startDate: startDate,
                        endDate: endDate,
                        isDateSearch: isDateSearch,
                        check_type: check_type,
                        click_type: click_type,
                        click_type2: click_type2,
                        check_serverity: check_serverity,
                        check_monitoring: check_monitoring,
                        check_social: check_social,
                        check_darkweb: check_darkweb
                    },
                    beforeSend: function() {

                    },
                    success: function(response) {
                        if (response.credential) {
                            $('#credential-count').text(response.credential);
                        } else {
                            $('#credential-count').text(0);
                        }

                        if (response.social) {
                            $('#compromise-count').text(response.social);
                        } else {
                            $('#compromise-count').text(0);
                        }

                        if (response.darkweb) {
                            $('#darkweb-count').text(response.darkweb);
                        } else {
                            $('#darkweb-count').text(0);
                        }
                    },
                    error: function(jqXHR) {
                        if (jqXHR.responseJSON && jqXHR.responseJSON.errors) {
                            var errors = jqXHR.responseJSON.errors;
                            var errorsHtml = '';
                            $.each(errors, function(key, value) {
                                errorsHtml += '<li>' + value[0] + '</li>';
                            });
                            toastr.error(errorsHtml, "@langapp('response_status')");
                        }
                    }
                });
            }

            function dataType(data) {

                click_type = data;
                click_type2 = '';
                search_val = 0;
                click_key = null;
                keywords = null;
                type = null;
                source = null;
                startDate = null;
                endDate = null;
                check_type = null;
                isDateSearch = null;

                $("#keyword").val('');
                $("#source").val('').trigger("change");
                $('.check_type').removeClass('active');
                $('.selector').removeClass('active');
                $('#all').addClass('active');
                $('.check_serverity').removeClass('active');
                $('#btn_search_all').addClass('active');
                check_serverity = null;
                $('.check_monitoring').removeClass('active');
                $('#btn_monitoring').addClass('active');
                check_monitoring = null;
                $('.hide-social').hide();
                table_social_data();
                {{-- get_count(); --}}
            }

            function dataType2(data) {
                click_type = '';
                click_type2 = data;
                search_val = 0;
                click_key = null;
                keywords = null;
                type = null;
                source = null;
                startDate = null;
                endDate = null;
                check_type = null;
                isDateSearch = null;
                $("#keyword").val('');
                $("#source").val('').trigger("change");
                $('.check_type').removeClass('active');
                $('.selector').removeClass('active');
                $('#all').addClass('active');
                $('.check_serverity').removeClass('active');
                $('#btn_search_all').addClass('active');
                check_serverity = null;
                $('.check_monitoring').removeClass('active');
                $('#btn_monitoring').addClass('active');
                check_monitoring = null;
                $('.hide-social').hide();
                table_social_data();
                {{-- get_count(); --}}
            }


            function count_icon() {
                $.ajax({
                    type: "POST",
                    url: '{!! route('socialdatas.credentialdatas_count_icon') !!}',
                    data: ({
                        site_id: site_code,
                        keywords: keywords,
                        startDate: startDate,
                        endDate: endDate,
                        isDateSearch: isDateSearch,
                        check_type: check_type,
                        check_serverity: check_serverity,
                        check_monitoring: check_monitoring,
                        click_type: click_type,
                        click_type2: click_type2,
                        check_social: check_social,
                        check_darkweb: check_darkweb,
                    }),
                    beforeSend: function() {
                        {{-- loading('load'); --}}
                    },
                    success: function(response) {
                        {{-- loading('stop_load'); --}}

                        $("#icon_website_s").html(response.icon_website_s || 0);
                        $("#icon_social_s").html(response.icon_social_s || 0);
                        $("#icon_community_s").html(response.icon_community_s || 0);

                        $("#icon_website_d").html(response.icon_website_d || 0);
                        $("#icon_social_d").html(response.icon_social_d || 0);
                        $("#icon_community_d").html(response.icon_community_d || 0);

                        $(".number_in_progress").html(response.number_in_progress || 0);
                        $(".number_reported").html(response.number_reported || 0);
                        $(".number_close").html(response.number_close || 0);
                    },
                    error: function(jqXHR) {
                        {{-- loading('stop_load'); --}}
                        if (jqXHR.responseJSON && jqXHR.responseJSON.errors) {
                            var errors = jqXHR.responseJSON.errors;
                            var errorsHtml = '';
                            $.each(errors, function(key, value) {
                                errorsHtml += '<li>' + value[0] + '</li>';
                            });
                            toastr.error(errorsHtml, '@langapp('response_status') ');
                        }
                    }

                });
            }

            $('#btn-export-excel').click(function() {
                var params = new URLSearchParams();
                var selectedIds = [];
                
                $('.social_id:checked').each(function() {
                    selectedIds.push($(this).val());
                });
                
                if (site_code) params.append('site', site_code);
                if (keywords) params.append('keywords', keywords);
                if (startDate) params.append('startDate', startDate);
                if (endDate) params.append('endDate', endDate);
                if (search_val == 1) params.append('isDateSearch', 1);
                if (check_type) params.append('check_type', check_type);
                if (check_serverity) params.append('check_serverity', check_serverity);
                if (check_monitoring) params.append('check_monitoring', check_monitoring);
                if (check_social) params.append('check_social', check_social);
                if (check_darkweb) params.append('check_darkweb', check_darkweb);
                if (click_type) params.append('click_type', click_type);
                if (click_type2) params.append('click_type2', click_type2);
                
                if (selectedIds.length > 0) {
                    params.append('ids', selectedIds.join(','));
                }
                
                var exportUrl = '{!! route('socialdatas.credentialdatas_export_excel') !!}?' + params.toString();
                window.location.href = exportUrl;
            });

            $('#btn-change-status').click(function(e) {
                e.preventDefault();
                var selectedIds = [];
                $('.social_id:checked').each(function() {
                    selectedIds.push($(this).val());
                });
                
                if (selectedIds.length === 0) {
                    toastr.warning('Please select at least one item to delete.');
                    return;
                }
                
                $('#delete_all').modal('show');
            });

            $('.delete-all').click(function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var selectedIds = [];
                $('.social_id:checked').each(function() {
                    selectedIds.push($(this).val());
                });
                
                if (selectedIds.length === 0) {
                    toastr.warning('Please select at least one item to delete.');
                    $('#delete_all').modal('hide');
                    return false;
                }
                
                $.ajax({
                    type: "POST",
                    url: '{!! route('socialdatas.credentialdatas_bulk_delete') !!}',
                    data: {
                        ids: selectedIds,
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        $('.delete-all').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
                    },
                    success: function(response) {
                        $('#delete_all').modal('hide');
                        $('.delete-all').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> OK');
                        
                        if (response.success) {
                            setTimeout(function() {
                                window.location.href = '{!! route('credentialleak.index') !!}';
                            }, 500);
                        } else {
                            toastr.error(response.message || 'Failed to delete records.');
                        }
                    },
                    error: function(jqXHR) {
                        $('#delete_all').modal('hide');
                        $('.delete-all').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> OK');
                        
                        var message = 'An error occurred while deleting records.';
                        if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                            message = jqXHR.responseJSON.message;
                        }
                        toastr.error(message);
                    }
                });
                
                return false;
            });
        </script>
    @endpush
@endsection
