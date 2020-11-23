@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">

            <header class="header panel-heading bg-white b-b b-light">
                <div class="bc-head">Settings Function Command</div>
                <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                    @icon('solid/download') CSV
                </a>

                <button type="submit" id="button" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                    <span>@icon('solid/trash-alt') @langapp('delete')</span>
                </button>
    
                <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal" data-target="#create-function">
                    @icon('solid/plus') @langapp('create')
                </a>
          
            </header>

            <section class="scrollable wrapper">
                <section class="panel panel-default">
                    <table class="table table-striped table-bordered" id="table-functioncommand-template">
                        <thead>
                            <tr>
                                <th class="no-sort">
                                    <label>
                                        <input name="select_all" value="1" id="select-all" type="checkbox" />
                                        <span class="label-text"></span>
                                    </label>
                                </th>
                                <th>Name</th>
                                <th>Command</th>
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
                                    GET IP
                                </td>
                                <td>
                                    curl ifoonfig.me
                                </td>
                                <td>
                                    Linux
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
                                    <button type="submit" class="btn btn-sm btn-secondary m-xs">
                                        <span>@icon('solid/play') Test
                                    </button>

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
                </section>

            </section>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>


    <!-- Modal Gen Key -->
    <div class="modal modal-slide" id="create-function" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Function Command </h4>
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
                        <label class="col-lg-3 control-label">Parameter <span class="text-danger">*</span> </label>
                        <div class="col-lg-4">
                            <div class="input-group">
                            <input type="text" class="form-control" name="" value="" readonly>
                                <span class="input-group-btn">
                                    <button type="submit" class="btn btn-info">Copy</button>  
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Command <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                            <textarea name="" id="" cols="30" rows="5" class="form-control"></textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 control-label">OS <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                            <select name="" id="os" class="select2-option form-control" multiple="multiple"></select>  
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Test</label>
                        <div class="col-lg-9">
                            <div class="row">
                                <div class="col-xs-12 mb-2">
                                    <input type="text" class="form-control" name="" value="">    
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" name="" value="" placeholder="Root name">    
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" name="" value="" placeholder="Password">    
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row d-none">
                        <label class="col-lg-3 control-label">Result</label>
                        <div class="col-lg-9">
                            <div class="">
                                <textarea name="" id="" cols="30" rows="10"  class="form-control"></textarea>  
                            </div>
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
@include('stacks.js.fullscreen')

<script>
        $(document).ready(function () {
            $('#os').select2();
        });
        

    $(function () {
        $('#table-functioncommand-template').DataTable({
            processing: true,
            order: [[0, "desc"]],
        });
    });
</script>
@endpush

@endsection
