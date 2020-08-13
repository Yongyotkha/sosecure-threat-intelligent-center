@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            <div class="bc-head">@langapp('alert')</div>
            <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                @icon('solid/download') CSV
            </a>
            <button type="submit" id="button" class="btn btn-sm btn-danger m-xs  pull-right" value="bulk-delete">
                <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt') @langapp('delete')</span>
            </button>
        </header>
        <section class="scrollable wrapper">
            <section class="panel panel-default">
                <div class="table-responsive">
                    <table  class="table table-striped" id="table-alert-template">
                        <thead>
                            <tr>
                                <th class="hide"></th>
                                <th class="no-sort">
                                    <label>
                                        <input name="select_all" value="1" id="select-all" type="checkbox" />
                                        <span class="label-text"></span>
                                    </label>
                                </th>
                                <th>@langapp('title')  </th>
                                <th>@langapp('type')</th>
                                <th>@langapp('date')   </th>
                                <th class="no-sort"></th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@push('pagestyle')
    @include('stacks.css.datatables')
@endpush

@push('pagescript')
@include('stacks.js.datatables')

<script>
$(function() {
    $('#table-alert-template').DataTable({
        processing: true,
        order: [[ 0, "desc" ]],
    });
});
</script>
@endpush
@endsection