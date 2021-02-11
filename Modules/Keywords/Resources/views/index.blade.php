@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            {{-- <a href="" class="btn btn-{{ get_option('theme_color') }}
            btn-sm btn-responsive pull-left m-r-5">
            @icon('solid/arrow-left')
                </a> --}}
                <div class="bc-head">@langapp('settings') > Keywords</div>


                @can('users_delete')
                    <button type="button" id="btn_del_select" class="btn btn-sm btn-danger m-xs pull-right" value="bulk-delete">
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt')
                            @langapp('delete')</span>
                    </button>
                @endcan

                <a href="#" class="btn btn-sm btn-{{ get_option('theme_color') }}
                    pull-right" data-toggle="modal" data-target="#create_keywords">
                    @icon('solid/plus') @langapp('create')
                </a>

        </header>
        <section class="scrollable wrapper">
            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-xs-12">
                            <i class="fas fa-table"></i> Table Keywords
                        </div>
                    </div>
                </header>
                <div class="panel-body">
                    <form id="frm-keywords" method="POST">
                        <div class="table-responsive">
                            <table class="table table-striped" id="table-keywords-template">
                                <thead>
                                    <tr>
                                        <th class="no-sort w-10">
                                            <label>
                                                <input name="select_all" value="1" onclick="go(); return false;" id="select-all" type="checkbox" />
                                                <span class="label-text"></span>
                                            </label>
                                        </th>
                                        <th>No.</th>
                                        <th>@langapp('name')</th>
                                        <th>@langapp('status')</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </section>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

    <!-- Modal Gen Category -->
    <div class="modal in fixed-left" id="create_keywords" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        Keywords
                    </h4>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Keywords <span class="text-danger">*</span></label>
                        <div class="col-lg-9">
                            <input type="text" class="form-control" name="" value="">
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
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.fullscreen')

<script>
    $('#select_source').select2();
    $(function () {
        $('#table-keywords-template').DataTable({
            "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
            processing: true,
            order: [[0, "desc"]],
        });
    });
</script>
@endpush
@endsection
