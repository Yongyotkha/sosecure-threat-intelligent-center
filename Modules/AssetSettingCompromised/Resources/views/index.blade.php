@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            <div class="bc-head">@langapp('compromised') > Assets Setting</div>
            <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                <span data-rel="tooltip" title="@langapp('export') CSV">@icon('solid/download') CSV</span>
            </a>
            <button type="submit" id="button" class="btn btn-sm btn-danger pull-right" value="bulk-delete">
                <span data-rel="tooltip" title="Are You Sure !">@icon('solid/trash-alt') @langapp('delete')</span>
            </button>

            <button type="submit" id="button" class="btn btn-sm btn-default pull-right" value="">
                <span data-rel="tooltip" title="Stop" data-placement="top">@icon('solid/stop')</span>
            </button>

            <button type="submit" id="button" class="btn btn-sm btn-default pull-right" value="">
                <span data-rel="tooltip" title="" data-original-title="Re-run">@icon('solid/redo')</span>
            </button>
            

            <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="ajaxModal">
                @icon('solid/plus') @langapp('create')
            </a>

        </header>

        <section class="scrollable wrapper">
            <section class="panel panel-default">
                <div class="table-responsive">
                    <table  class="table table-striped" id="table-compromised-setting-template">
                        <thead>
                            <tr>
                                <th class="hide"></th>
                                <th class="no-sort">
                                    <label>
                                        <input name="select_all" value="1" id="select-all" type="checkbox" />
                                        <span class="label-text"></span>
                                    </label>
                                </th>
                                <th>@langapp('name')  </th>
                                <th>@langapp('target')</th>
                                <th>@langapp('started')   </th>
                                <th>@langapp('finished')   </th>
                                <th>@langapp('status')   </th>
                                <th>@langapp('element')   </th>
                                <th>@langapp('corelations')   </th>
                                <th>@langapp('update')   </th>
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
    $('#table-compromised-setting-template').DataTable({
        processing: true,
        order: [[ 0, "desc" ]],
    });
});
</script>
@endpush
@endsection