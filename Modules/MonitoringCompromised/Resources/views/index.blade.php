@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a> --}}
            <div class="bc-head">@langapp('compromised') > @langapp('monitoring')</div>
        </header>
        <section class="scrollable wrapper">
            <section class="panel panel-default">

                <div class="row" style="padding:2rem 1rem 1rem 1rem">
                    <div class="col-lg-4 col-md-4">
                        <ul class="total-count">
                            <li><h1>HOST</h1><span class="color-purple">10</span></li>
                        </ul>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <ul class="total-count">
                            <li><h1>Account</h1><span class="color-green">10</span></li>
                        </ul>
                    </div>
                    <div class="col-lg-4 col-md-4">
                        <ul class="total-count">
                            <li><h1>Email</h1><span class="color-red">310</span></li>
                        </ul>
                    </div>  
                </div>


                <div class="table-responsive">
                    <table  class="table table-striped" id="table-monitor-compromised-template">
                        <thead>
                            <tr>
                                <th class="hide"></th>
                                <th class="no-sort">
                                    <label>
                                        <input name="select_all" value="1" id="select-all" type="checkbox" />
                                        <span class="label-text"></span>
                                    </label>
                                </th>
                                <th>@langapp('group')</th>
                                <th>@langapp('name')</th>
                                <th>@langapp('item_name')</th>
                                <th>@langapp('update')</th>
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
    $('#table-monitor-compromised-template').DataTable({
        processing: true,
        order: [[ 0, "desc" ]],
    });
});
</script>
@endpush
@endsection