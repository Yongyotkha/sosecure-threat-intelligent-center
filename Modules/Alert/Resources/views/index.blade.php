@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            <div class="bc-head">@langapp('alert')</div>
        </header>
        <section class="scrollable wrapper">
                <div class="tabbable">
                    <ul class="nav nav-tabs nav-tabs-highlight">
                        <li class="active"><a href="#alert_tab" data-toggle="tab">Asset</a></li>
                    </ul>
                    <div class="tab-content">
                        
                        <div class="tab-pane active" id="alert_tab">
                            
                            <section class="panel panel-default">
                            <div class="table-responsive">
                                <table  class="table table-striped" id="table-alert-template">
                                    <thead>
                                        <tr>
                                            <th class="hide"></th>
                                            <th>@langapp('type')  </th>
                                            <th>@langapp('ip_address')</th>
                                            <th>@langapp('hostname')   </th>
                                            <th>@langapp('site')   </th>
                                            <th class="no-sort"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                    </tbody>
                                </table>
                            </div>
                        </section>
                        </div>
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