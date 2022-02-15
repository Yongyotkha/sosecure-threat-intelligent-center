@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">      
        <aside id="hide-settings" class="aside aside-md b-r">
            <section class="vbox">
                @include('partial.header-select-site')
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_site')
                    </section>
                </div>
                </section>
            </section>
        </aside>
  
            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light">
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display: none">@icon('solid/bars')</a>
                    <div class="bc-head">Site Settings > Referer Logs</div>
                </header>
                <section class="scrollable wrapper">                   
                    <section class="panel panel-default">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-xs-12">
                                    <i class="fas fa-table"></i> Table Referer Logs
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">

                            <div class="table-responsive">
                                <div style="width: 100%">
                                    <table id="tbl_referer_logs" class="table table-borered table-striped">
                                        <thead>
                                            <tr>
                                                <th>No.</th>
                                                <th>IP</th>
                                                <th>URL Origin</th>
                                                <th>URL Description</th>
                                                <th>Log Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td>123.234.555</td>
                                                <td>http://sosecure/login</td>
                                                <td>http://sosecure/login</td>
                                                <td>21/01/2022 09:10:29</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </section>
                </section>
            </section>
       
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

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
@include('stacks.js.site_hidesettings')
@include('stacks.js.advanced_search')
@include('stacks.js.fullscreen')
<script>
    $('#tbl_referer_logs').DataTable();
</script>
@endpush
@endsection
