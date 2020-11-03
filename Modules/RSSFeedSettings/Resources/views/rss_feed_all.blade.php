@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a>
            <div class="bc-head">@langapp('rss_feed_settings') | ALL</div>
            <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                @icon('solid/download') CSV
            </a>
            <button type="submit" id="button" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                <span>@icon('solid/trash-alt') @langapp('delete_all')</span>
            </button>
            <button type="submit" id="button" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                <span>@icon('solid/trash-alt') @langapp('delete')</span>
            </button>

            <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal" data-target="#create-news">
                @icon('solid/plus') @langapp('create') News
            </a>
      
        </header>
              {{-- Search --}}
            {{-- Tab Content --}}
            <section class="scrollable wrapper bg-grey">
                <section class="panel panel-default">
                    <div class="container-fluid" style="padding: 2rem;">
                        <div class="row m-b-md">
                            
                            <div class="col-lg-3">
                                <label for="">Keywords</label>
                                <select name="" id="keywords" class="select2-option form-control" multiple="multiple">
                                    <option value="1">a</option>
                                    <option value="2">b</option>
                                </select>
                            </div>
                            <div class="col-lg-3">
                                <label for="">Start Date</label>
                                <div class="input-group date">
                                    <input id="send_date" type="text" class="form-control datetimepicker-input"
                                    value="{{  timePickerFormat(now()->addHours(1)) }}" name="start_date"
                                    data-date-format="DD-MM-YYYY hh:mm A" data-date-start-date="moment()" required>
                                    <div class="input-group-addon">
                                        @icon('solid/calendar-alt', 'text-muted')
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <label for="">End Date</label>
                                <div class="input-group date">
                                    <input id="send_date" type="text" class="form-control datetimepicker-input"
                                    value="{{  timePickerFormat(now()->addHours(1)) }}" name="end_date"
                                    data-date-format="DD-MM-YYYY hh:mm A" data-date-start-date="moment()" required>
                                    <div class="input-group-addon">
                                        @icon('solid/calendar-alt', 'text-muted')
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <label for="">Status</label>
                                <section id="select_news" class="select2-option form-control">
                                    <option value="1" selected>All</option>
                                </section>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-3">
                                <label for="">Source</label>
                                <select name="" id="source" class="select2-option form-control" multiple="multiple">
                                    <option value="1">a</option>
                                    <option value="2">b</option>
                                </select>
                            </div>
                            <div class="col-lg-9 text-right">
                                <button class="btn btn-info btn-responsive">
                                    <i class="fas fa-search"></i>
                                    Search
                                </button>
                                <button class="btn btn-default btn-responsive" style="white-space: nowrap">
                                    <i class="fas fa-broom"></i>
                                    <span> Clear </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>
                <div class="tabbable">
                    <ul class="nav nav-tabs nav-tabs-highlight">
                        <li class="active"><a href="#tab_data_feed" data-toggle="tab">Data Feed (20)</a></li>
                        <li><a href="#tab_data_error" data-toggle="tab">Data Error (42)</a></li>   
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_data_feed">
                            <section class="panel panel-default border-n">
                                <div class="table-responsive">
                                    <table  class="table table-striped" id="table-dtfeed-template">
                                        <thead>
                                            <tr>
                                                <th class="hide"></th>
                                                <th class="no-sort">
                                                    <label>
                                                        <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                        <span class="label-text"></span>
                                                    </label>
                                                </th>
                                                <th>Title</th>
                                                <th>Link</th>
                                                <th>Update</th>
                                                <th>Feed Date</th>
                                                <th>Status</th>
                                                <th>News</th>
                                                <th>Action</th>
                                                <th class="no-sort"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                
                                        </tbody>
                                    </table>   
                                </div>
                            </section>
                        </div>
                        <div class="tab-pane" id="tab_data_error">
                            <section class="panel panel-default">
                                <div class="table-responsive">
                                    <table  class="table table-striped" id="table-dtfeed-error-template">
                                        <thead>
                                            <tr>
                                                <th class="hide"></th>
                                                <th class="no-sort">
                                                    <label>
                                                        <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                        <span class="label-text"></span>
                                                    </label>
                                                </th>
                                                <th>Title</th>
                                                <th>Link</th>
                                                <th>Update</th>
                                                <th>Feed Date</th>
                                                <th>Status</th>
                                                <th>News</th>
                                                <th>Action</th>
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
                </div>
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
@include('stacks.js.markdown')

<script>
$(function() {

    $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') });
    $(document).ready(function () {
        $('#keywords').select2({
            tags: true,
            tokenSeparators: [' ']
        });

        $('#category').select2({
            tags: true,
            tokenSeparators: [' ']
        });
        $('#source').select2();
    });

    $('#table-dtfeed-template').DataTable({
        processing: true,
        order: [[ 0, "desc" ]],
    });
    $('#table-dtfeed-error-template').DataTable({
        processing: true,
        order: [[ 0, "desc" ]],
    });
});
</script>
@endpush
@endsection