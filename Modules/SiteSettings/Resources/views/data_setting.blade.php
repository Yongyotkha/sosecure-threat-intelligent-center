@extends('layouts.app')

@section('content')
{{-- {{dd($site_menu_permission)}} --}}

<section id="content" class="bg">
    <section class="hbox stretch">
        
        <aside id="hide-settings" class="aside aside-md b-r">
            <section class="vbox">
                @include('partial.header-select-site')
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_site')
                    </section>
                </section>
            </section>
        </aside>

            <section class="vbox">

                <header class="header bg-white b-b clearfix">
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display:none">@icon('solid/bars')</a>
                    <div class="bc-head">Site Setting &gt; Permission Config</div>
                </header>
                <section class="scrollable wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            {{-- {!! Form::open(['class' => 'bs-example form-horizontal ajaxifyForm validator']) !!} --}}
                            {!! Form::open(['route' => ['datasettings.update.settings', $siteSettings->code], 'class' => 'bs-example form-horizontal ajaxifyForm_custom', 'method' => 'PUT', 'files' => true]) !!}
                            <input type="hidden" name="page_setting" value="site_permission_settings">
                            <section class="panel panel-default">
                            <header class="panel-heading font-bold panel-header-blue">@icon('solid/cogs') Permission & Config Settings  </header>
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
                                                <input type="checkbox" name="site_user_allow" {{$siteSettings->user_allow == 'Y' || $siteSettings->user_allow == null ? 'checked' : '' }} value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Site Add</span>
                                            </label>
                                        </div>
                                    </div>
                                    <label class="col-lg-1 control-label">Limit : </label>
                                    <div class="col-lg-3">
                                        <input type="text" class="form-control touch_spin text-center" name="user_limit" value="@if($siteSettings->user_limit_amount) {{$siteSettings->user_limit_amount}} @else{{$site_user_limit_default->value}}@endif">
                                    </div>
                                </div>
                                <!--
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
                               -->

                                
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Domain Allow </label>
                                    <div class="col-lg-2">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="site_domain_allow" {{$siteSettings->domain_allow == 'Y' || $siteSettings->domain_allow == null ? 'checked' : '' }} value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Site Add</span>
                                            </label>
                                        </div>
                                    </div>
                                    <label class="col-lg-1 control-label">Limit : </label>
                                    <div class="col-lg-3">
                                        <input type="text" class="form-control touch_spin text-center" name="domain_limit" value="@if($siteSettings->domain_limit){{$siteSettings->domain_limit}}@else{{$site_domain_limit_default->value}}@endif">
                                    </div>
                                </div>

                                
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Asset Allow </label>
                                    <div class="col-lg-2">
                                        <div class="checkbox">
                                            <label>
                                                {{-- @php var_dump($siteSettings->name) @endphp --}}
                                                <input type="checkbox" name="site_asset_allow" {{$siteSettings->asset_allow == 'Y' || $siteSettings->asset_allow == null ? 'checked' : '' }} value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Site Add</span>
                                            </label>
                                        </div>
                                    </div>
                                    <label class="col-lg-1 control-label">Limit : </label>
                                    <div class="col-lg-3">
                                        <input type="text" class="form-control touch_spin text-center" name="asset_limit" value="@if($siteSettings->asset_limit){{$siteSettings->asset_limit}}@else{{$site_asset_limit_default->value}}@endif">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Web Defacement</label>
                                    <div class="col-lg-2">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="site_web_defacement_allow" {{$siteSettings->web_defacement_allow == 'Y' || $siteSettings->web_defacement_allow == null ? 'checked' : '' }} value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">URL Add</span>
                                            </label>
                                        </div>
                                    </div>
                                    <label class="col-lg-1 control-label">Limit : </label>
                                    <div class="col-lg-3">
                                        <input type="text" class="form-control touch_spin text-center" name="web_defacement_limit" value="@if($siteSettings->web_defacement_limit){{$siteSettings->web_defacement_limit}}@else{{$site_web_defacement_limit_default->value}}@endif">
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">E-mail Alert </label>
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
                                <hr>

                                <div class="row text-right">
                                        <label class="col-lg-3 control-label m-b-12">Syslog Server Log</label>
                                </div>
                             
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">IP </label>
                                    <div class="col-lg-9">
                                            <input type="text" name="ip" class="form-control" value="@if($siteSettings->server_log_ip){{$siteSettings->server_log_ip}}@else  @endif"><!--192.168.1.1-->
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Protocol </label>
                                    <div class="col-lg-9">
                                        <select name="protocol" id="select-protocol" class="form-control">
                                            <option value="">--Protocal--</option>
                                            <option value="udp" @if($siteSettings->server_log_protocol == 'udp') selected @else  @endif>udp</option>
                                            <option value="tcp" @if($siteSettings->server_log_protocol == 'tcp') selected @else  @endif>tcp</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Port </label>
                                    <div class="col-lg-9">
                                            <input type="number" name="port" class="form-control" value="@if($siteSettings->server_log_port){{$siteSettings->server_log_port}}@else  @endif"><!--80-->
                                    </div>
                                </div>


                            </div>
                            <div class="panel-footer text-right">
                                {{-- {!! closeModalButton() !!} --}}
                                <button type="submit" class="btn btn-info formSaving btn-rounded"><i class="fas fa-paper-plane"></i> Save</button>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </section>
            </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen, open" data-target="#nav,html"></a>
</section>


@push('pagestyle')
@include('stacks.css.form')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.touchspin')
@include('stacks.js.menusub')
@include('stacks.js.site_hidesettings')

<script>
    $(document).ready(function () {

        $('#select-protocol').select2({
            minimumResultsForSearch: -1
        });

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

    var form_save = '.formSaving';
    $('.ajaxifyForm_custom').submit(function (event) {
        event.preventDefault();

            $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            $('.formSaving').attr('disabled',true);
            
            var data = new FormData(this);
            if(form_save == '.formSavingAndRun'){
                data.append('formsubmit', 'formSavingAndRun');
            }else if(form_save == '.formPreview'){
                data.append('formsubmit', 'formPreview');
            }else if(form_save == '.formDraft'){
                data.append('formsubmit', 'formDraft');
            }
            axios.post($(this).attr("action"), data)
                .then(function (response) {
                    
                    toastr.success(response.data.message, '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-paper-plane"></i>  @langapp('save') </span>');
                    window.location.href = response.data.redirect;
            })
            .catch(function (error) {
                if(error.response.data.exception){
                    $('.formSaving').attr('disabled',false);
                    toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }else{
                    $('.formSaving').attr('disabled',false);
                    var errors = error.response.data.errors;
                    var errorsHtml= '';
                    $.each( errors, function( key, value ) {
                        errorsHtml += '<li>' + value[0] + '</li>'; 
                    });
                    toastr.error( errorsHtml , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }
                
                
            }); 
       
     
         
    });

</script>

@endpush

@endsection
