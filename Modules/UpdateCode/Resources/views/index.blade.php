@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a> --}}
            <div class="bc-head">@langapp('update_code')</div>
            <button type="submit" id="button" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt')
                    @langapp('delete')</span>
            </button>
            <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip"
                title="@langapp('export') CSV">
                @icon('solid/download') CSV
            </a>
            <a href="#"
                class="btn btn-sm btn-{{ get_option('theme_color') }} pull-right"
                data-toggle="modal" data-target="#m_updatecode">
                @icon('solid/plus') @langapp('create')
            </a>
            <a href="#" class="btn btn-sm btn-{{ get_option('theme_color') }} pull-right"  >
                Site Version
            </a>
        </header>

        <section class="scrollable wrapper">
            <section class="panel panel-default">

                <form id="frm-updatecode" method="POST">
                    <div class="table-responsive">
                        @php
                            // dd(lastMonth());
                        @endphp
                        <table class="table table-striped" id="table-updatecode-template">
                            <thead>
                                <tr>
                                    <th class="no-sort">
                                        <label>
                                            <input name="select_all" value="1" id="select-all" type="checkbox" />
                                            <span class="label-text"></span>
                                        </label>
                                    </th>
                                    <th class="">Name</th>
                                    <th class="">Version</th>
                                    <th>Mode</th>
                                    <th>Deploy Status</th>
                                    <th>Download</th>
                                    <th>Status</th>
                                    <th>Update</th>
                                    <th class="no-sort" width="10%">Action</th> 
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </form>
            </section>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

       <!-- Modal Update Code -->
       <div class="modal in fixed-left" id="m_updatecode" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        Update Code
                    </h4>
                </div>
                <form action="">
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-lg-3 control-label">Name <span class="text-danger">*</span> </label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 control-label">Description <span class="text-danger">*</span> </label>
                            <div class="col-lg-9">
                               <textarea name="" id="" cols="30" rows="10" class="form-control"></textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 control-label">Name <span class="text-danger">*</span> </label>
                            <div class="col-lg-9">
                                <div class="col-12">
                                    <input type="file" class="form-control">
                                </div>
                                <div class="col-12" style="margin-top: 5px">
                                    <span>รองรับเฉพาะ .zip เท่านั้น</span>
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
                        <div class="text-danger pull-left mt20px">*required field</div>
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
@include('stacks.css.datepicker')
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.fullscreen')

<script>
    $(function () {
        $('#table-updatecode-template').DataTable({
            processing: true,
            order: [[0, "desc"]],
        });
    });
</script>
@endpush
@endsection