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
                    <div class="bc-head">Site Settings > Phishing Detection</div>

                   
                    <button type="submit" id="button" class="btn btn-sm btn-danger m-xs  pull-right" value="bulk-delete" >
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt') @langapp('delete')</span>
                    </button>
                    <a href="#" data-toggle="modal" data-target="#modal_data_leak_url" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('create')">
                        @icon('solid/plus') Add
                    </a>

                </header>
                <section class="scrollable wrapper">                   
                    <section class="panel panel-default">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-xs-12">
                                    <i class="fas fa-table"></i> Table Phishing Detection
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">

                            <div class="table-responsive">
                                <div style="width: 100%">
                                    <table id="tbl_dfm_feed" class="table table-borered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Site Name</th>
                                                <th>URL Detection</th>
                                                <th>Page Name</th>
                                                <th>Key Word</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>

                                                <td>บริษัท เมจิกเทคโซลูชั่น จำกัด</td>
                                                <td>https://demo02.mtsc.co.th/login</td>
                                                <td>Login</td>
                                                <td>password,Login</td>
                                                <td>
                                                    <label class="switch">
                                                        <input type="hidden" value="FALSE" name="">
                                                        <input type="checkbox" name="status" value="TRUE" checked>
                                                        <span></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-info btn-xs">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                
                                                    <button type="button" class="btn btn-danger btn-xs">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
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

    <div class="modal fade fixed-left" id="modal_data_leak_url" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        <span id="title_head"> New Data Leak URL</span>
                    </h4>
                </div>
                {{-- <form action="" class="ajaxifyForm_custom"> --}}
                {!! Form::open(['route' => ['webdefacement.create_data'], 'class' => 'ajaxifyForm_custom', 'method' => 'POST']) !!}
                <input type="hidden" name="mode" id="mode" value="create">
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-lg-2 control-label">Page Name <span class="text-danger">*</span> </label>
                        <div class="col-lg-10">
                            <input type="text" class="form-control" name="name_web" id="name_web" value="" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-2 control-label">URL <span class="text-danger">*</span> </label>
                        <div class="col-lg-10">
                                <input type="text" class="form-control" name="url_web" id="url_web" value="" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-2 control-label">Keyword <span class="text-danger">*</span> </label>
                        <div class="col-lg-10">
                            <select name="" id="" class="form-control select-2-keyword" multiple></select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-2 control-label">Status </label>
                        <div class="col-lg-10">
                            <label class="switch">
                                <input type="hidden" value="FALSE" name="">
                                <input type="checkbox" name="status" value="TRUE" checked>
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
                    <button id="btn_save" type="submit" class="btn btn-info btn-rounded formSaving">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

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
    $('#tbl_dfm_feed').DataTable();
    $('.select-2-keyword').select2();
</script>
@endpush
@endsection
