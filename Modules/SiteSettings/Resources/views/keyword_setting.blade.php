@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">      
        <aside id="hide-settings" class="aside aside-md b-r">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#setting-nav">@icon('solid/bars')</a>
                        <a class="hide-setting btn btn-icon btn-default btn-sm pull-right m-r-xs">@icon('solid/bars')</a>
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
                            <li >
                                <a href="{{route('systemsetting.index', ['id' => $siteSettings->code])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    System Settings
                                </a>
                            </li>
                            <li class="">
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

                            <li>
                                <a href="#">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Assets
                                </a>
                            </li>
                            <li class="main-link active">
                                <a href="#">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Data Leak
                                </a>
                                <ul class="nav nav-pills nav-stacked no-radius ul_submenu">
                                    <li style="padding-left:2rem">
                                        <a href="{{route('keyword.index', ['id' => $siteSettings->code])}}">
                                            Keyboard Setting
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="#">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    News
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
    
                <header class="header panel-heading bg-white b-b b-light">
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0">@icon('solid/bars')</a>
                    <div class="bc-head">Settings > Keyword </div>
                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                        @icon('solid/download') CSV
                    </a>
                    <button type="submit" id="button" class="btn btn-sm btn-danger m-xs  pull-right" value="bulk-delete">
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt') @langapp('delete')</span>
                    </button>
                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal" data-target="#create_assets_vulnerability">
                        @icon('solid/plus') @langapp('create')
                    </a>
                </header>
                <section class="scrollable wrapper">
                    <section class="panel panel-default">
                        <div class="table-responsive">
                            <table  class="table table-striped" id="table_cve_assets">
                                <thead>
                                    <tr>
                                        <th class="no-sort w-10">
                                            <label>
                                                <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                <span class="label-text"></span>
                                            </label>
                                        </th>
                                        <th>@langapp('keyword')</th>
                                        <th>Last @langapp('update')</th>
                                        <th>@langapp('status')</th>
                                        <th class="no-sort">@langapp('action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                            </table>
                        </div>
                    </section>
                </section>
            </section>
        </aside>
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
    <!-- Modal create_assets_vulnerability -->
    <div class="modal fade" id="create_assets_vulnerability" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title" id="exampleModalLabel">Asset</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="" class="col-md-3">Site</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" placeholder="Site">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Status</label>
                        <div class="col-lg-9">
                            <label class="switch">
                                <input type="hidden" value="FALSE" name="">
                                <input type="checkbox" name="" value="TRUE">
                                <span></span>
                            </label>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
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
    </div>

</section>

@push('pagestyle')
    @include('stacks.css.datatables')
    @include('stacks.css.form')
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.menusub')
@include('stacks.js.hidesettings')
<script>
$(function() {
        var table = $('#table_cve_assets').DataTable({
        });
});

</script>
@endpush
@endsection