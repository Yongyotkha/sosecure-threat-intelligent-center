@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header bg-white b-b b-light">
            <div class="bc-head">Tag : {{@$id}}</div>

            <a id="advance-search" href="#area_search"
                class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                <span>@langapp('Search_Advance')</span>
            </a>
            {{-- <a id="to_top" href="#area_search" class="">test</a> --}}
            <div class="pull-right" style="margin-top: 8px; width: 300px;">
                <select name="site" id="site" class="select2-option form-control select-site" style="min-width: 300px">
                    <option value="">All Site</option>
                    {{-- @if($SiteSettings)
                    @foreach($SiteSettings as $SiteSettings_val)
                    <option value="{{$SiteSettings_val->code}}">{{$SiteSettings_val->name}}</option>
                    @endforeach
                    @endif --}}
                </select>
            </div>

        </header>
        <section class="scrollable wrapper">
            <section id="area_search" class="panel panel-default">
                <div class="panel-heading">
                    <a class="text-primary" href="{{ route('indicators.events') }}">Events</a>
                    |
                    <a href="{{ route('indicators.attributes') }}" class="text-muted">Attributes</a>
                </div>
                <div id="hide-advance-search" class="container-fluid" style="padding: 2rem; display: none;">
                    <div class="row">
                        {{-- <div class="col-md-8">
                            <div class="form-group m-b-md">
                                <label for="" class="">Keyword</label>
                                <input type="text" class="form-control" name="event_name" id="event_name"
                                    placeholder="Search">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="" class="">Date</label>
                            <div id="event_date" class="text-center"
                                style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                                <i class="fa fa-calendar"></i>&nbsp;
                                <span></span> <i class="fa fa-caret-down"></i>
                            </div>
                        </div>
                        <div class="col-md-12 text-right">
                            <button class="btn btn-info" id="btn_search_data">
                                <i class="fas fa-search"></i>
                                <span> Search </span>
                            </button>
                            <button class="btn btn-default" id="btn_reset">
                                <i class=" fas fa-broom"></i>
                                <span> Clear </span>
                            </button> 
                        </div>--}}
                    </div>
                </div>
            </section>

            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-xs-12">
                            <i class="fas fa-table"></i> Table Tag
                        </div>
                    </div>
                </header>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table_events">
                            <thead>
                                <tr>

                                    <th>No</th>
                                    <th>Event Name</th>
                                    <th>Group</th>
                                    <th>Tags</th>
                                    <th>Published</th>
                                    <th>Last Status</th>
                                    <th>DateTime</th>
                                    <th>View</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                        <div id="showing_amount_text" class="pull-left" style="margin-top: 5px; margin-left: 15px;">
                        </div>
                        <div class="pull-right" style="padding-right: 10px;" id="pagination_custom"></div>
                    </div>
                </div>
            </section>

        </section>
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
@include('stacks.css.datepicker')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.advanced_search')
<script>
    $('.select2-option').select2();

 

    

</script>

@endpush
@endsection