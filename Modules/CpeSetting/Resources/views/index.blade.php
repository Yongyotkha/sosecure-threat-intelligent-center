@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">

            <header class="header panel-heading bg-white b-b b-light">
                <div class="bc-head">CPE Settings</div>
                <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                    @icon('solid/download') CSV
                </a>

                <button type="submit" id="button" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                    <span>@icon('solid/trash-alt') @langapp('delete')</span>
                </button>
    
                <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal" data-target="#create-cpe">
                    @icon('solid/plus') @langapp('create')
                </a>
          
            </header>

            <section class="scrollable wrapper">
                <section class="panel panel-default">
                    <header class="panel-heading font-bold panel-header-blue">
                        <div class="row">
                            <div class="col-xs-12">
                                <i class="fas fa-table"></i> Table CPE
                            </div>
                        </div>
                    </header>
                    <div class="panel-body">
                        <table class="table table-striped table-bordered" id="table-cpe-template">
                            <thead>
                                <tr>
                                    <th class="no-sort">
                                        <label>
                                            <input name="select_all" value="1" id="select-all" type="checkbox" />
                                            <span class="label-text"></span>
                                        </label>
                                    </th>
                                    <th>CPE Name</th>
                                    <th>OS</th>
                                    <th>Last Update</th>
                                    <th style="width: 20px" class="text-center">Status</th>
                                    <th style="width: 20px" class="text-center no-wrap">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <label>
                                            <input name="select" value="1" id="" type="checkbox" />
                                            <span class="label-text"></span>
                                        </label>
                                    </td>
                                    <td>
                                        cpe:2.3:o:canonical:ubuntu_linux:5.04:*:*
                                    </td>
                                    <td>
                                        Ubuntu_linux/5.05
                                    </td>
                                    <td>
                                        2020-11-12 12:15
                                    </td>
                                    <td class="text-center">
                                        <label class="switch">
                                            <input type="hidden" value="FALSE" name="">
                                            <input type="checkbox" name="" value="TRUE">
                                            <span></span>
                                        </label>
                                    </td>
                                    <td class="no-wrap">
                                        <button type="submit" class="btn btn-sm btn-info m-xs">
                                            <span>@icon('solid/edit')
                                        </button>
        
                                        <button type="submit" class="btn btn-sm btn-danger m-xs">
                                            <span>@icon('solid/trash-alt')
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

            </section>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

    <div class="modal in fixed-left" id="create-cpe" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        CPE
                    </h4>
                </div>

                <form action="">
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Name <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                            <input type="text" class="form-control" name="name" value="">    
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 control-label">OS <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                            <select name="" id="os" class="select2-option form-control" multiple="multiple"></select>  
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Status </label>
                        <div class="col-lg-6">
                            <label class="switch">
                                <input type="hidden" value="FALSE" name="">
                                <input type="checkbox" name="" value="TRUE">
                                <span></span>
                            </label>
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
    </div>


</section>

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
@endpush

@push('pagescript')
@include('stacks.js.datatables');
@include('stacks.js.form')
@include('stacks.js.fullscreen')
<script>
        $(document).ready(function () {
            $('#os').select2();
        });
        
        $(function () {
            $('#table-cpe-template').DataTable({
                "dom": '<B><"d-flex d-inline-flex justify-content-between"lf>rt<"bottom"ip><"clear">',
                processing: true,
                order: [[0, "desc"]],
            });
        });
</script>
@endpush

@endsection
