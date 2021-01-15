@extends('layouts.app')
@section('content')
<style>
    .w-100{
        width: 100px;
    }

</style>
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
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_site')
                    </section>
                </section>
            </section>
        </aside>
    
        <aside>
            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light">
                    {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                    @icon('solid/arrow-left')
                    </a> --}}
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display:none">@icon('solid/bars')</a>
                    <div class="bc-head">@langapp('settings') > @langapp('assets')</div>
                    <button type="submit" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt')
                            @langapp('delete')</span>
                    </button>

                    <a id="advance-search" href="#hide-fillter" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                        <span><i class="fas fa-filter"></i> @langapp('Search_Advance')</span>
                    </a>

                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal"
                        data-target="#rss_modal">
                        @icon('solid/plus') @langapp('create')
                    </a>
                </header>

                <section class="scrollable wrapper">

                    <div class="row">
                        <div class="col-lg-12">
                            <div id="hide-advance-search" class="panel panel-default container-fluid" style="padding: 2rem;display:none;">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group m-b-md">
                                            <label for="" class="">Keyword</label>
                                            <input type="text" class="form-control" name="keyword" placeholder="Search">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="" class="">Datatype Type</label>
                                            <select name="" id="datatype" class="select2-option form-control" multiple="multiple">
                                                <option value="1">All</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group m-b-md">
                                            <label for="" class="">Referent</label>
                                            <input type="text" class="form-control" name="keyword" placeholder="">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group m-b-md">
                                            <label for="" class="d-block">&nbsp;</label>
                                            <button class="btn btn-info">
                                                <i class="fas fa-search"></i>
                                                <span> Search </span>
                                            </button>
                                            <button class="btn btn-default">
                                                <i class="fas fa-broom"></i>
                                                <span> Clear </span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <section class="panel panel-default">
                                <header class="panel-heading font-bold panel-header-blue">
                                    <div class="row">
                                        <div class="col-xs-12">
                                            <i class="fas fa-table"></i> Table Assets
                                        </div>
                                    </div>
                                </header>
                                <div class="panel-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="table-assets-data">
                                            <thead>
                                                <tr>
                                                    <th class="no-sort">
                                                        <label>
                                                            <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk"/>
                                                            <span class="label-text"></span>
                                                        </label>
                                                    </th>                                      
                                                    <th>Asset</th>
                                                    <th>Referent</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </section>
            </section>
        </aside>
    </section>
    {{-- ------------------- --}}

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>


</section>




@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
@include('stacks.css.datepicker')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css"/>
@endpush

@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.datatables')
@include('stacks.js.fullscreen')
@include('partial.ajaxify')
@include('stacks.js.menusub')
@include('stacks.js.site_hidesettings')
@include('stacks.js.advanced_search')


<script>
    $(function () {
        $('#table-assets-data').DataTable({
            "dom": '<"d-flex d-inline-flex justify-content-between"Bf><"top"l>rt<"bottom"ip><"clear">',
        });
    });
    $(document).ready(function () {
        $('#datatype').select2();
        $('#source').select2();

        $('.select2').select2();

        $('.hide-fillter').hide();
        $('#fillter-advance').click(function(){
            $('.hide-fillter').toggle();
        });
    });
</script>
@endpush
@endsection
