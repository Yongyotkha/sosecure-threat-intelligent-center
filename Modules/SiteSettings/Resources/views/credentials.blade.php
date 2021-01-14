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
                        <p class="h3 text-elipse-setting">Name Domain</p>
                </header>
                <section class="scrollable">
                    <div class="slim-scroll" data-color="#333333" data-disable-fade-out="true" data-distance="0" data-height="auto" data-size="3px">
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_site')
                    </section>
                </div>
                </section>
            </section>
        </aside>
        <aside>
            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light">
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display: none">@icon('solid/bars')</a>
                    <div class="bc-head"> Credentials </div>
                    {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                        @icon('solid/download') CSV
                    </a> --}}
                    <button type="button" id="btn_del_select" class="btn btn-sm btn-danger m-xs  pull-right" value="bulk-delete" disabled>
                        <span data-rel="tooltip" title="Are you sure?" data-placement="bottom">@icon('solid/trash-alt') @langapp('delete')</span>
                    </button>
                    <button class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                        <span>Add Credentials</span>
                     </button>
                </header>
                <section class="scrollable wrapper">
                    <section class="panel panel-default">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-xs-12">
                                    <i class="fas fa-table"></i> Table Credentials
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table  class="table table-striped" id="table_credentials">
                                    <thead>
                                        <tr>
                                            <th class="no-sort w-10">
                                                <label>
                                                    <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk" />
                                                    <span class="label-text"></span>
                                                </label>
                                            </th>
                                            <th>User</th>
                                            <th>Password</th>
                                            <th>Reference</th>
                                            <th>Status</th>
                                            <th>@langapp('action')</th>
                                        </tr>
                                    </thead>
                                    <tbody>


                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </section>
            </section>
        </aside>
    </section>
</section>

@push('pagestyle')
    @include('stacks.css.datatables')
    @include('stacks.css.datepicker')
    @include('stacks.css.form')
    <link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css"/>
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.menusub')
@include('stacks.js.hidesettings')
@include('stacks.js.advanced_search')
<script>
       $(function () {
        $('#table_credentials').DataTable({
            "dom": '<"d-flex d-inline-flex justify-content-between"Bf><"top"l>rt<"bottom"ip><"clear">',
        });
    });
</script>
@endpush
@endsection
