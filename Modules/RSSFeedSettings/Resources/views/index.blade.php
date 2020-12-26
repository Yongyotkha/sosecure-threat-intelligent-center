@extends('layouts.app')
@section('content')
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
                            <li class="active">
                                <a href="{{route('rssfeedsettings.index')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    RSS Settings
                                </a>
                            </li>
                            <li>
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
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display:none">@icon('solid/bars')</a>
                    <div class="bc-head">@langapp('settings') > @langapp('rss_feed_settings')</div>
                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip"
                        title="@langapp('export') CSV">
                        @icon('solid/download') CSV
                    </a>
                    <button type="submit" id="button" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt')
                            @langapp('delete')</span>
                    </button>
                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal"
                        data-target="#rss_modal">
                        @icon('solid/plus') @langapp('create')
                    </a>
                    {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal"
                        data-target="#rss_modal">
                        >_ Run Feed
                    </a>
                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal"
                        data-target="#rss_modal">
                        @ @langapp('keyword')
                    </a>
                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal"
                        data-target="#rss_modal">
                        @icon('solid/laptop') Scheldue Update
                    </a> --}}
                </header>

                <section class="scrollable wrapper">
                    <section class="panel panel-default">
                        <div class="table-responsive">
                            <table class="table table-striped" id="table-rss-setting-template">
                                <thead>
                                    <tr>
                                        <th class="hide"></th>
                                        <th class="no-sort">
                                            <label>
                                                <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                <span class="label-text"></span>
                                            </label>
                                        </th>
                                        <th>@langapp('name')</th>
                                        <th>URL</th>
                                        {{-- <th>@langapp('keyword')</th>
                                        <th>Interval (Day)</th>
                                        <th>Start Date Feed</th>
                                        <th>Last Date Feed</th>
                                        <th>Data Feed</th>
                                        <th>Data Error</th> --}}
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

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

    <!-- Modal RSS -->
    <div class="modal in fixed-left" id="rss_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
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
                {!! Form::open(['route' => ['rssfeedsettings.save'], 'class' => 'ajaxifyForm validator', 'novalidate' => '', 'files' => false]) !!}
                {{-- <form action=""> --}}
                    <div class="modal-body">
                        <div class="form-group row">
                            <label class="col-lg-3 control-label">Name <span class="text-danger">*</span> </label>
                            <div class="col-lg-9">
                                <input type="text" id="name_rss" name="name_rss" class="form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-lg-3 control-label">URL
                                <span data-rel="tooltip" title="" data-original-title="URL"> <i class="far fa-question-circle"></i></span>
                                <span class="text-danger">*</span>
                            </label>
                            <div class="col-lg-9">
                                
                                        <input type="url" class="form-control" id="url_rss" name="url_rss" value="" >
                                            {{-- <span  class="input-group-btn">
                                                <button type="submit" class="btn btn-info">Copy</button>
                                            </span> --}}
                                            {{-- <span><i class="far fa-check-circle fa-2x text-success"></i></span> --}}
                                        
                                    
                            </div>
                        </div>


                        {{-- <div class="form-group row">
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
                        </div> --}}

                        {{-- <div class="form-group row">
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
                        </div> --}}

                        <div class="form-group row">
                            <label class="col-lg-3 control-label">Status </label>
                            <div class="col-lg-6">
                                <label class="switch">
                                    {{-- <input type="hidden" value="FALSE" name=""> --}}
                                    <input type="checkbox" name="status_rss" value="TRUE">
                                    <span></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                            <i class="fas fa-times"></i>
                            Close
                        </button>
                        <button type="submit" class="btn btn-info btn-rounded">
                            <i class="fas fa-paper-plane"></i>
                            Save
                        </button>
                    </div>
                    {!! Form::close() !!}
                {{-- </form> --}}
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
@include('stacks.js.hidesettings');
@include('stacks.js.fullscreen');

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
        


        var table = $('#table-rss-setting-template').DataTable({
            pageLength: 50,
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                url: '{!! route('rssfeedsettings.rss_setting_table') !!}',
                data: function ( d ) {
                    {{--d.keywords = keywords;--}}
                    {{--return JSON.stringify( d );--}}
                    return d;
                },
                type: "POST",
            },
            order: [[ 0, "desc" ]],
            columns: [
                { data: 'id', name: 'id' },
                { data: 'chk', name: 'chk', orderable: false, searchable: false, sortable: false, className: 'w-10' },
                { data: 'name', name: 'name' },
                { data: 'url', name: 'url' },
                {
                    data: 'status',
                    name: 'status',
                    className: 'w-25'
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    className: 'w-50 no-wrap'
                }
            ]
        });



    });


    function change_rss_active(code) {
        let checkState = $("#rss-active-" + code).is(":checked") ? 1 : 0;
        axios.post('{{route('rssfeedsettings.change_status')}}', {
            active: checkState,
            code: code,
        }).then(function (response) {
            toastr.success(response.data.message, '@langapp('response_status')');
            window.location.href = response.data.redirect;
        }).catch(function (error) {
            var errors = error.errors;
            var errorsHtml = "";
            $.each(errors, function (key, value) {
                errorsHtml += "<li>" + value[0] + "</li>";
            });
            toastr.error(errorsHtml, '@langapp('response_status')');
        });
    }


</script>
@endpush
@endsection