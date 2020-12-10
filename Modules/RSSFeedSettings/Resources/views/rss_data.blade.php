@extends('layouts.app')
@section('content')
<style>
    .w-100{
        width: 100px;
    }

</style>
<section id="content" class="bg">

    <section class="hbox stretch">      
        <aside id="hide-settings" class="aside aside-md b-r">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#setting-nav">@icon('solid/bars')
                    </a>
                    <a class="hide-setting btn btn-icon btn-default btn-sm pull-right m-r-xs">@icon('solid/bars')</a>
                    <p class="h3">@langapp('settings')  </p>
                </header>
                <section class="scrollable">
                    <div class="slim-scroll" data-color="#333333" data-disable-fade-out="true" data-distance="0" data-height="auto" data-size="3px"> 
                    <section id="setting-nav" class="hidden-xs">
                        <ul class="nav nav-pills nav-stacked no-radius">
                            <li>
                                <a href="{{route('rssfeedsettings.index')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    RSS Settings
                                </a>
                            </li>
                            <li class="active">
                                <a href="{{route('rssfeedsettings.rss_data')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    RSS Data
                                </a>
                            </li>
                            <li>
                                <a href="{{route('rssfeedsettings.news')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    News
                                </a>
                            </li>
                        </ul>
                    </section>
                </div>
                </section>
            </section>
        </aside>
    
        <aside>
            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light">
                    {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                    @icon('solid/arrow-left')
                    </a> --}}
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0">@icon('solid/bars')</a>
                    <div class="bc-head">@langapp('settings') > @langapp('rss_feed') Data</div>
                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip"
                        title="@langapp('export') CSV">
                        @icon('solid/download') CSV
                    </a>
                    <button type="submit" id="button" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt')
                            @langapp('delete')</span>
                    </button>
                    {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal"
                        data-target="#rss_modal">
                        @icon('solid/plus') @langapp('create')
                    </a> --}}
                </header>

                <section class="scrollable wrapper">
                    <section class="panel panel-default">
                        <div class="container-fluid" style="padding: 2rem;">
                            <div class="row m-b-md">
                                <div class="col-lg-4">
                                    <label for="">Keywords</label>
                                   <input type="text" class="form-control" name="keywords" id="keywords">
                                </div>
                                <div class="col-lg-4">
                                    <label for="">Public Date</label>
                                    <div class="input-group date">
                                        <input id="public_date" type="text" class="form-control datetimepicker-input" name="public_date"
                                        data-date-format="DD-MM-YYYY" data-date-start-date="moment()" required>
                                        <div class="input-group-addon">
                                            @icon('solid/calendar-alt', 'text-muted')
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <label for="">Status</label>
                                    <select id="status" class="select2-option form-control">
                                        <option value="1" selected>All</option>
                                        <option value="2">Used</option>
                                        <option value="3">Not Used</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label for="">Source</label>
                                    <input type="text" class="form-control" name="source" id="source">
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-lg-12 text-right">
                                    <button type="button" class="btn btn-info btn-responsive" onclick="search()">
                                        <i class="fas fa-search"></i>
                                        Search
                                    </button>
                                    <button type="button" id="btn_rss_data_reset" class="btn btn-default btn-responsive" style="white-space: nowrap">
                                        <i class="fas fa-broom"></i>
                                        <span> Clear </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>
    
                    <section class="panel panel-default">
                        <div class="table-responsive">
                            <table class="table table-striped" id="table-rss-data">
                                <thead>
                                    <tr>
                                        <th class="no-sort">
                                            <label>
                                                <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                <span class="label-text"></span>
                                            </label>
                                        </th>
                                        <th>Title</th>
                                        <th>Source</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
        
                                </tbody>
                            </table>
                        </div>
                    </section>
                </section>
            </section>
        </aside>
    </section>
    {{-- ------------------- --}}

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
@include('stacks.js.hidesettings');

<script>
    var keywords = null;
    var public_date = null;
    var status = null;
    var source = null;
    function search(){
        keywords = $('#keywords').val();
        public_date = $('#public_date').val();
        status = $('#status option:selected').val();
        source = $('#source').val();
        datatable();
    }


        $("#btn_rss_data_reset").click(function() {
            $("#keywords").val('');
            $("#public_date").val('');
            $("#status").val('').trigger("change");
            $("#source").val('');
        });

        
    $(function () {
        $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true });
        datatable();
        {{--loading('load');--}}
        {{--f_loading(null, '.vbox');--}}

    });
    function datatable(){
        $('#table-rss-data').DataTable({
            pageLength: 50,
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                {{--contentType: "application/json",
                dataType: 'JSON',--}}
                type: "POST",
                url: '{!! route('rssfeedsettings.rss_data_table') !!}',
                data: function ( d ) {
                    d.keywords = keywords;
                    d.public_date = public_date;
                    d.status = status;
                    d.source = source;
                    {{--return JSON.stringify( d );--}}
                    return d;
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
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'link',
                    name: 'link'
                },
                {
                    data: 'pubDate',
                    name: 'pubDate',
                    className: 'w-10 text-center'
                },
                {
                    data: 'status',
                    name: 'status',
                    className: 'w-10 text-center'
                },
                {
                    data: 'action',
                    name: 'action',
                    className: 'no-wrap'
                },
                
            ]
        });
    }
</script>
@endpush
@endsection
