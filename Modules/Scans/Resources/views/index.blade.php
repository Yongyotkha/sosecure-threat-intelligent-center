@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
            @icon('solid/arrow-left')
            </a> --}}
            <div class="bc-head">Scans</div>
            {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip"
                title="@langapp('export') CSV">
                @icon('solid/download') CSV
            </a> --}}

            {{-- <a href="#" class="btn btn-sm btn-default pull-right">
                <i class="fa fa-sync-alt"></i> Refresh
            </a>
            <button type="submit" id="button" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt')
                    @langapp('delete')</span>
            </button>

            <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal" data-target="#scans_create">
                @icon('solid/plus') @langapp('create')
            </a> --}}

            <a href="{{route('assets.index')}}" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                @icon('solid/link') Assets
            </a>
        </header>
        <section class="scrollable wrapper">
            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-xs-12">
                            <i class="fas fa-table"></i> Table Scans
                        </div>
                    </div>
                </header>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-scans-template">
                            <thead>
                                <tr>
                                    <th class="no-sort">
                                        <label>
                                            <input name="select_all" value="1" id="select-all" type="checkbox" />
                                            <span class="label-text"></span>
                                        </label>
                                    </th>
                                    <th>Site Name</th>
                                    <th>Domain</th>
                                    <th>Started</th>
                                    <th>Finished</th>
                                    <th>Elements</th>
                                    <th>Progress</th>
                                    {{-- <th>Status</th> --}}
                                    <th class="no-sort" width="10%">Action</th>
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
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

    <!-- Modal Scans -->
    <div class="modal in fixed-left" id="scans_create" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        RSS Feed
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
                            <label class="col-lg-3 control-label">URL
                                <span data-rel="tooltip" title="" data-original-title="URL"> <i class="far fa-question-circle"></i></span>
                                <span class="text-danger">*</span>
                            </label>
                            <div class="col-lg-9">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="input-group"><input type="text" class="form-control"  name="generate_key" value="" readonly="">
                                            <span  class="input-group-btn">
                                                <button type="submit" class="btn btn-info">Copy</button>
                                            </span>
                                            <span><i class="far fa-check-circle fa-2x text-success"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="form-group row">
                            <label class="col-lg-3 control-label">Keywords </label>
                            <div class="col-lg-9">
                                <select name="" id="keywords" class="select2-option form-control" multiple="multiple">
                                    <option value="1">a</option>
                                    <option value="2">b</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 control-label">Interval </label>
                            <div class="col-lg-9">
                                <select name="" id="interval" class="select2-option form-control" multiple="multiple">
                                    <option value="1">a</option>
                                    <option value="2">b</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 control-label">Start</label>
                            <div class="col-lg-9">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="input-group date">
                                            <input id="send_date" type="text" class="form-control datetimepicker-input"
                                            value="{{  timePickerFormat(now()->addHours(1)) }}" name="start_date"
                                            data-date-format="DD-MM-YYYY hh:mm A" data-date-start-date="moment()" required>
                                            <div class="input-group-addon">
                                                @icon('solid/calendar-alt', 'text-muted')
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="checkbox">
                                            <label style="padding-left: 0;">
                                                <input id="set_exp" type="checkbox" name="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Set an expire date</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="show_end_exp_date" class="form-group row">
                            <label class="col-lg-3 control-label">End</label>
                            <div class="col-lg-9">
                                <div class="input-group date">
                                    <input id="send_date" type="text" class="form-control datetimepicker-input"
                                    value="{{  timePickerFormat(now()->addHours(1)) }}" name="start_date"
                                    data-date-format="DD-MM-YYYY hh:mm A" data-date-start-date="moment()" required>
                                    <div class="input-group-addon">
                                        @icon('solid/calendar-alt', 'text-muted')
                                    </div>
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
    $(document).ready(function () {
        $('#keywords').select2({
            tags: true,
            tokenSeparators: [' ']
        });

        $('#interval').select2({
            tags: true,
            tokenSeparators: [' ']
        });

        $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') });

        $('#show_end_exp_date').hide();

        $('#set_exp').on('change',function(){
            if($(this).prop('checked')){
                $('#show_end_exp_date').show();
            }else{
                $('#show_end_exp_date').hide();
            }
        });
    });


    $(function () {
        $('#table-scans-template').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            "dom": '<B><"d-flex d-inline-flex justify-content-between"lf>rt<"bottom"ip><"clear">',
            ajax: {
                contentType: "application/json",
                dataType: 'JSON',
                type: "POST",
                url: '{!! route('scans.data') !!}',
                data: function ( d ) {
                    return JSON.stringify( d );
                },
            },
            columns: [
                {
                    data: 'chk',
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    className: 'w-10'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'domain',
                    name: 'domain'
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    className: 'text-center'
                },
                {
                    data: 'updated_at',
                    name: 'updated_at',
                    className: 'text-center'
                },
                {
                    data: 'elements',
                    name: 'elements',
                    className: 'w-10 text-center'
                },
                {
                    data: 'progress',
                    name: 'progress',
                    className: 'w-10'
                },
                {
                    data: 'action',
                    name: 'action',
                    className: 'no-wrap'
                },
                
            ]
        });
    });


</script>
@endpush
@endsection
{{-- {
    data: 'domain',
    name: 'domain'
},
{
    data: 'started',
    name: 'started'
},
{
    data: 'finished',
    name: 'finished',
},
{
    data: 'progress',
    name: 'progress',
},
{
    data: 'elements',
    name: 'elements',
},
{
    data: 'status',
    name: 'status',
},
{
    data: 'action',
    orderable: false,
    searchable: false,
    sortable: false,
    className: 'w-50'
} --}}
