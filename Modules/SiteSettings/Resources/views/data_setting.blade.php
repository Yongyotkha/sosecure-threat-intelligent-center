@extends('layouts.app')

@section('content')

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
                            {!! Form::open(['class' => 'bs-example form-horizontal ajaxifyForm validator']) !!}
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
                                                            <input type="checkbox" name="menu[]" checked="" value="{{$menu->code}}">
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
                                                                        <input type="checkbox" name="menu_sub[]" checked="" value="{{$menu_sub->code}}">
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
                                                <input type="checkbox" name="site_user_allow" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Site Add</span>
                                            </label>
                                        </div>
                                    </div>
                                    <label class="col-lg-1 control-label">Limit : </label>
                                    <div class="col-lg-3">
                                        <input type="text" class="form-control touch_spin" name="user_limit" value="0">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Role Allow </label>
                                    <div class="col-lg-2">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="site_role_allow" checked="" value="TRUE">
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
                                                <input type="checkbox" name="site_domain_allow" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Site Add</span>
                                            </label>
                                        </div>
                                    </div>
                                    <label class="col-lg-1 control-label">Limit : </label>
                                    <div class="col-lg-3">
                                        <input type="text" class="form-control touch_spin" name="domain_limit" value="0">
                                    </div>
                                </div>

                                
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Asset Allow </label>
                                    <div class="col-lg-2">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="site_asset_allow" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Site Add</span>
                                            </label>
                                        </div>
                                    </div>
                                    <label class="col-lg-1 control-label">Limit : </label>
                                    <div class="col-lg-3">
                                        <input type="text" class="form-control touch_spin" name="asset_limit" value="0">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">E-mail Alert : </label>
                                    <div class="col-lg-9">
                                        <select name="" id="email-alert" class="select2-option form-control" multiple="multiple">
                                            <option value="1">a</option>
                                            <option value="2">b</option>
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

<script>
    $(document).ready(function () {
        $('#email-alert').select2({
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

</script>

@endpush

@endsection
