@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a> --}}
            <div class="bc-head">@langapp('monitoring')>Batch Job</div>
        </header>

        <section class="scrollable wrapper">
            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-xs-12">
                            <i class="fas fa-table"></i> Table Event
                        </div>
                    </div>
                </header>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-monitoring-batchjob">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Mode</th>
                                    <th>Progress</th>
                                    <th>Transaction Last Start</th>
                                    <th>Transaction Last End</th>
                                    <th>Site</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </section>
    </section>

    {{-- <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a> --}}
    
</section>

@push('pagestyle')
    @include('stacks.css.datatables')
@endpush

@push('pagescript')
@include('stacks.js.datatables')

<script>
$(function () {
    data_table();
});

function data_table(){
        $('#table-monitoring-batchjob').DataTable({
            searching: true,
            ordering: true,
            pageLength: 25,
            processing: true,
            serverSide: true,
            destroy: true,
            order: [[ 5, "desc" ], [ 0, "asc" ]],
            dom: 'Blfrtip',
            ajax: {
                type: "POST",
                url: '{!! route('monitoring.tableMonitor')!!}',
                dataSrc: function ( json ) {
                    return json.data;
                },
                data:function(d){
                }
            },
            initComplete : function( settings, json){
                $('[data-toggle="tooltip"]').tooltip();
            },
            columns: [
                {
                    data: 'name',
                },
                {
                    data: 'mode',
                },
                {
                    data: 'progress',
                },
                {
                    data: 'transcation_date_start',
                },
                {
                    data: 'transcation_date_end',
                },
                {
                    data: 'site_id',
                },
            ],
            columnDefs: [
                {
                    targets: 2,
                    render: function (data, type, row) {
                        let inner = '';
                        if(row.progress == 0){
                            inner = '';
                            inner = '<span class="badge badge-danger" style="background-color: #ea2e49;">Not Working</span';
                        }else if(row.progress == 1){
                            inner = '';
                            inner = '<span class="badge badge-wait" style="background-color: #ea2e49;">Waiting</span';
                        }else if(row.progress == 2){
                            inner = '';
                            inner = '<span class="badge badge-success" style="background-color: #ea2e49;">Progress</span';
                        }else{
                            inner = '';
                            inner = '<span class="badge badge-none" style="background-color: #ea2e49;">Unknow</span';
                        }
                        return inner;
                    }
                },
            ]

        });

    }
</script>
@endpush
@endsection