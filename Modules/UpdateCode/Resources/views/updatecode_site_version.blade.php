@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a> --}}
            <div class="bc-head">@langapp('update_code') / Site Version</div>
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
                        <table class="table table-striped" id="table-siteversion-template">
                            <thead>
                                <tr>
                                    <th class="no-sort">
                                        <label>
                                            <input name="select_all" value="1" id="select-all" type="checkbox" />
                                            <span class="label-text"></span>
                                        </label>
                                    </th>
                                    <th>Site Name</th>
                                    <th>Current Version</th>
                                    <th>Site Status</th>
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

<script>
    $(function () {
        $('#table-siteversion-template').DataTable({
            processing: true,
            order: [[0, "desc"]],
        });
    });
</script>
@endpush
@endsection