@extends('layouts.app')

@section('content')
{{-- {{dd($site_menu_permission)}} --}}

<section id="content" class="bg">
    <section class="hbox stretch">
        
        <aside class="aside aside-md b-r">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="modal" data-target="#setting-nav">@icon('solid/bars')</a>
                    <p class="h3">@langapp('settings')  </p>
                </header>
                <section class="scrollable">
                    <div class="slim-scroll" data-color="#333333" data-disable-fade-out="true" data-distance="0" data-height="auto" data-size="3px"> 
                    <section id="setting-nav" class="hidden-xs">
                        <ul class="nav nav-pills nav-stacked no-radius">
                            <li>
                                <a href="{{route('sitesettings.edit', ['id' => $siteSettings->code])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Site Settings
                                </a>
                            </li>
                            <li>
                                <a href="{{route('systemsetting.index', ['id' => $siteSettings->code])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    System Settings
                                </a>
                            </li>
                            <li class="active">
                                <a href="{{route('datasettings.index', ['id' => $siteSettings->code])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Permission & Config Settings
                                </a>
                            </li>
                            <li>
                                <a href="{{route('userssettings.index', ['id' => $siteSettings->code])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Users
                                </a>
                            </li>
                            <li>
                                <a href="{{route('domain.index', ['id' => $siteSettings->code])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Domain
                                </a>
                            </li>
                            <li class="main-link">
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
                        </ul>
                    </section>
                </div>
                </section>
            </section>
        </aside>

        <aside>
            <section class="vbox">

                <header class="header bg-white b-b clearfix">
                    <div class="bc-head">Site Setting &gt; {{ $siteSettings->name }}</div>
                </header>
                <section class="scrollable wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            {{-- {!! Form::open(['class' => 'bs-example form-horizontal ajaxifyForm validator']) !!} --}}
                            {!! Form::open(['route' => ['datasettings.update.settings', $siteSettings->code], 'class' => 'bs-example form-horizontal ajaxifyForm validator', 'novalidate' => '', 'method' => 'PUT', 'files' => true]) !!}
                            <input type="hidden" name="page_setting" value="site_permission_settings">
                            <section class="panel panel-default">
                            <header class="panel-heading">@icon('solid/cogs') Permission & Config Settings  </header>
                            <div class="panel-body">
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Permission Menu </label>
                                    <div class="col-lg-9">

                                        <ul class="role-group">
                                            @php  $i=1;  @endphp
                                            @foreach($menus AS $menu)
                                            <li>
                                                <div class="role-main">
                                                    <span class="role-click" onclick="openrole(this,'role-{{$i}}')">@if(count($menu->get_menu_sub) > 0)@icon('solid/plus')@else <i class="fas fa-minus icon"></i>  @endif</span>
                                                    <span class="checkbox chk-inline">
                                                        <label>
                                                            @if(in_array($menu->code,$site_menu_permission))
                                                            @php $checked = 'checked'; @endphp
                                                            @else
                                                            @php $checked = ''; @endphp
                                                            @endif
                                                            <input type="checkbox" name="menu[]" {{$checked}} {{--checked=""--}} value="{{$menu->code}}">
                                                            <span class="label-text" data-rel="tooltip" title="">{{$menu->name}}</span>
                                                        </label>
                                                    </span>
                                                </div>
                                                
                                                @if(count($menu->get_menu_sub) > 0)
                                                    <ul id="role-{{$i}}" class="role-group-sub">
                                                    @foreach($menu->get_menu_sub as $menu_sub) 
                                                    
                                                        <li>
                                                            <div class="role-sub">
                                                                <span class="checkbox chk-inline">
                                                                    <label>
                                                                        @if(in_array($menu_sub->code,$site_menu_sub_permission))
                                                                        @php $checked = 'checked'; @endphp
                                                                        @else
                                                                        @php $checked = ''; @endphp
                                                                        @endif
                                                                        <input type="checkbox" name="menu_sub[]" {{$checked}} {{--checked=""--}} value="{{$menu_sub->code}}">
                                                                        <span class="label-text" data-rel="tooltip" title="">{{$menu_sub->name}}</span>
                                                                    </label>
                                                                </span>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                    </ul>
                                                @endif
                                                

                                            </li>
                                            @php $i++; @endphp
                                            @endforeach
                                            
                                        </ul>

                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">User Allow </label>
                                    <div class="col-lg-2">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="site_user_allow" {{$siteSettings->user_allow == 'Y' ? 'checked' : '' }} value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Site Add</span>
                                            </label>
                                        </div>
                                    </div>
                                    <label class="col-lg-1 control-label">Limit : </label>
                                    <div class="col-lg-3">
                                        <input type="text" class="form-control touch_spin" name="user_limit" value="@if($siteSettings->user_limit_amount) {{$siteSettings->user_limit_amount}} @else{{$site_user_limit_default->value}}@endif">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Role Allow </label>
                                    <div class="col-lg-2">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="site_role_allow" {{$siteSettings->role_allow_admin == 'Y' ? 'checked' : '' }} value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Admin</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        {{-- <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Read Only</span>
                                            </label>
                                        </div> --}}
                                    </div>
                                    <div class="col-lg-2">
                                        {{-- <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Customer <a href="#" data-rel="tooltip" title="ติดต่อผู้ดูแลระบบ คลิก"><i class="far fa-question-circle"></i></a></span>
                                            </label>
                                        </div> --}}
                                    </div>
                                </div>

                                
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Domain Allow </label>
                                    <div class="col-lg-2">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="site_domain_allow" {{$siteSettings->domain_allow == 'Y' ? 'checked' : '' }} value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Site Add</span>
                                            </label>
                                        </div>
                                    </div>
                                    <label class="col-lg-1 control-label">Limit : </label>
                                    <div class="col-lg-3">
                                        <input type="text" class="form-control touch_spin" name="domain_limit" value="@if($siteSettings->domain_limit){{$siteSettings->domain_limit}}@else{{$site_domain_limit_default->value}}@endif">
                                    </div>
                                </div>

                                
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Asset Allow </label>
                                    <div class="col-lg-2">
                                        <div class="checkbox">
                                            <label>
                                                {{-- @php var_dump($siteSettings->name) @endphp --}}
                                                <input type="checkbox" name="site_asset_allow" {{$siteSettings->asset_allow == 'Y' ? 'checked' : '' }} value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Site Add</span>
                                            </label>
                                        </div>
                                    </div>
                                    <label class="col-lg-1 control-label">Limit : </label>
                                    <div class="col-lg-3">
                                        <input type="text" class="form-control touch_spin" name="asset_limit" value="@if($siteSettings->asset_limit){{$siteSettings->asset_limit}}@else{{$site_asset_limit_default->value}}@endif">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">E-mail Alert : </label>
                                    <div class="col-lg-9">
                                        <select name="email_alert[]" id="email_alert" class="select2-option form-control" multiple="multiple">
                                            {{-- <option value="1">a</option>
                                            <option value="2">b</option> --}}
                                            
                                                @foreach($siteSettings->get_site_config_email_alert as $site_config_email_alert)
                                                    <option value="{{ $site_config_email_alert->email  }}" selected >{{ $site_config_email_alert->email }}</option>
                                                @endforeach
                                        </select>

                                        <div class="text-muted">Alert (News,Other)</div>
                                    </div>
                                </div>


                            </div>
                            <div class="panel-footer">
                                {{-- {!! closeModalButton() !!} --}}
                                {!! renderAjaxButton() !!}
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </section>
            </section>
        </aside>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen, open" data-target="#nav,html">

    </a>
</section>


@push('pagestyle')
@include('stacks.css.form')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.touchspin')
@include('stacks.js.menusub')

<script>
    $(document).ready(function () {
        $('#email_alert').select2({
            tags: true,
            tokenSeparators: [' ']
        });
        
        $(".touch_spin").TouchSpin({
            min: 0,
            step: 1,
            boostat: 5,
            maxboostedstep: 10,
        });

    });

    $('ul.role-group-sub').hide();
    function openrole(onck,id){
        $('#'+id).slideToggle(150);
    }

   
        if($("input[name='site_user_allow']").is(':checked')) {
            $("input[name='user_limit']").prop("disabled",false);
        } else {
            $("input[name='user_limit']").prop("disabled",true);
        }
    
        if($("input[name='site_domain_allow']").is(':checked')) {
            $("input[name='domain_limit']").prop("disabled",false);
        } else {
            $("input[name='domain_limit']").prop("disabled",true);
        }
    
        if($("input[name='site_asset_allow']").is(':checked')) {
            $("input[name='asset_limit']").prop("disabled",false);
        } else {
            $("input[name='asset_limit']").prop("disabled",true);
        }


    $("input[name='site_user_allow']").click(function() {
        if($(this).is(':checked')) {
            $("input[name='user_limit']").prop("disabled",false);
        } else {
            $("input[name='user_limit']").prop("disabled",true);
        }
    });
    $("input[name='site_domain_allow']").click(function() {
        if($(this).is(':checked')) {
            $("input[name='domain_limit']").prop("disabled",false);
        } else {
            $("input[name='domain_limit']").prop("disabled",true);
        }
    });
    $("input[name='site_asset_allow']").click(function() {
        if($(this).is(':checked')) {
            $("input[name='asset_limit']").prop("disabled",false);
        } else {
            $("input[name='asset_limit']").prop("disabled",true);
        }
    });

</script>

@endpush

@endsection
