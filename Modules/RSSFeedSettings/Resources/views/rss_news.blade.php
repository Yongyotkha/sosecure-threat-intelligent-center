@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">      
        <aside id="hide-settings" class="aside aside-md b-r">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show" data-target="#setting-nav">@icon('solid/bars')</a>
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
                            <li>
                                <a href="{{route('rssfeedsettings.rss_data')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    RSS Data
                                </a>
                            </li>
                            <li class="active">
                                <a href="{{route('rssfeedsettings.rss_data')}}">
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
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0">@icon('solid/bars')</a>
                    <div class="bc-head">@langapp('rss_feed_settings') | www.xxx.xxx/xxx.xxx.rss</div>
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
                                <section id="select_news" class="select2-option form-control" multiple="multiple">
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
                        </div>
                        <div class="row">
                            <div class="col-lg-12 text-right">
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
                
                <section class="scrollable">              
                    <section class="panel panel-default">
                        <div class="table-responsive">
                            <table  class="table table-striped" id="table-rss-news-template">
                                <thead>
                                    <tr>
                                        <th class="no-sort">
                                            <label>
                                                <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                <span class="label-text"></span>
                                            </label>
                                        </th>
                                        <th>Site Name</th>
                                        <th width="20px">Source name</th>
                                        <th width="20%">Title</th>
                                        <th>Topic</th>
                                        <th width="20px">Data Satatus</th>
                                        <th width="30px">Public Date</th>
                                        <th>View Count</th>
                                        <th>Link</th>
                                        <th>Status</th>
                                        <th width="5%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
        
                                </tbody>
                            </table>   
                        </div>
                    </section>
                </section>

                
            </section>
            </section>
        </aside>
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
     <!-- Modal RSS -->
     <div class="modal modal-slide" id="create-news" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered fullscreen" role="document">
         <div class="modal-content">
             <div class="modal-header">
                 <span class="modal-title" id="exampleModalLabel">News</span>
                 <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                 </button>
             </div>
             {!! Form::open(['route' => ['rssfeedsettings.rss_data_store_news_create'], 'class' => 'ajaxifyFormCreate', 'method' => 'POST', 'files' => true]) !!}
                 <div class="modal-body">
                     <div class="container-fluid">
                         <div class="row">
                            <div class="col-md-12">
                                <h5>Create News</h5>
                            </div>
                         </div>
                         <div class="form-group row">
                            <label class="col-lg-12 control-label">Logo </label>
                            <div class="col-lg-12">
                                <div class="">
                                    <input id="file-input" type="file" class="form-control" name="logo" value="">
                                </div>
                            </div>
                        </div>
                         <div class="row">
                             <label for="" class="col-md-12 control-label" id="label_category">Category <span class="text-danger">*</span></label>
                             <div class="col-md-12">
                                <select name="category_news[]" id="category" class="select2-option form-control" multiple="multiple">
                                    @foreach ($category as $item)
                                        <option value="{{ $item -> id }}">{{ $item -> name }}</option>
                                    @endforeach
                                </select>
                             </div>
                         </div>
                         <br>
                         <div class="form-group row">
                            <label for="" class="col-lg-12 control-label" id="label_source">Source <span class="text-danger">*</span></label>
                            <div class="col-lg-12">
                                <select name="source" id="source_create" class="select2-option form-control"></select>
                            </div>
                        </div>
                        <br>
                         <div class="row">
                            <label for="" class="col-md-12 control-label" id="label_topic">Topic <span class="text-danger">*</span></label>
                            <div class="col-md-12">
                               <select name="topic[]" id="topic" class="select2-option form-control" multiple="multiple"></select>
                            </div>
                        </div>
                         <br>
                         {{-- 
                         <div class="row">
                             <div class="col-lg-12">
                                <div class="form-group">
                                    <label>
                                        <input name="lang" value="th" type="checkbox" checked onclick="return false;"/>
                                        <span class="label-text">TH</span>
                                    </label>
                                    &nbsp;&nbsp;
                                    <label>
                                        <input name="lang" value="en" type="checkbox"/>
                                        <span class="label-text">EN</span>
                                    </label>
                                </div>
                             </div>
                         </div> --}}

                         <div class="row">
                             <div class="col-md-12">
                                <div class="tabbable">
                                    <ul class="nav nav-tabs nav-tabs-highlight">
                                        <li class="active"><a href="#tab_th" data-toggle="tab">TH</a></li>
                                        <li><a href="#tab_en" data-toggle="tab">EN</a></li>   
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="tab_th">
                                            <section class="panel-body border-n">
                                                <div class="form-group row">
                                                    <label for="" class="col-lg-12 control-label" id="label_title_th">Text (TH) <span class="text-danger">*</span></label>
                                                    <div class="col-lg-12">
                                                        <input type="text" class="form-control" name="title_th" id="title_th">
                                                    </div>
                                                </div>
            
                                                <div class="form-group row">
                                                    <label for="" class="col-lg-12 control-label" id="label_detail_th">Detail (TH) <span class="text-danger">*</span></label>
                                                    <div class="col-lg-12">
                                                        <textarea class="form-control htmleditor" name="detail_th" id="detail_th" data-id="1"></textarea>
                                                    </div>
                                                </div>
                                            </section>
                                        </div>
                                        <div class="tab-pane" id="tab_en">
                                            <section class="panel-body border-n">
                                                <div class="form-group row">
                                                    <label for="" class="col-lg-12 control-label">Text (EN) <span class="text-danger">*</span></label>
                                                    <div class="col-lg-12">
                                                        <input type="text" class="form-control" name="title_en" id="title_en">
                                                    </div>
                                                </div>
            
                                                <div class="form-group row">
                                                    <label for="" class="col-lg-12 control-label">Detail (EN) <span class="text-danger">*</span></label>
                                                    <div class="col-lg-12">
                                                        <textarea class="form-control htmleditor" name="detail_en" id="detail_en" data-id="1"></textarea>
                                                    </div>
                                                </div>
                                            </section>
                                        </div>
                                    </div>
                                </div>
                             </div>
                         </div>

                         {{--  --}}
                                <div class="row">
                                   <label for="" class="col-md-12 control-label">Tag
                               </div>
                               <div class="row">
                                <div class="col-md-12">
                                        <select name="tags[]" id="tags" class="select2-option form-control" multiple="multiple"></select>
                                    </div>
                                </div>
                        <br>
                         <div class="form-group row">
                             <div class="col-lg-6">
                                <div class="row">
                                    <label class="col-lg-4 control-label">Public Date </label>
                                    <div class="col-lg-8">
                                        <div class="input-group date">
                                            <input id="send_date" type="text" class="form-control datetimepicker-input"
                                            value="" name="public_date"
                                            data-date-format="DD-MM-YYYY HH:mm:ss" data-date-start-date="moment()">
                                            <div class="input-group-addon">
                                                @icon('solid/calendar-alt', 'text-muted')
                                            </div>
                                        </div>
                                    </div>
                                </div>
                             </div>
                             <div class="col-lg-6">
                                <div class="row">
                                    <label class="col-lg-4 control-label">Status </label>
                                    <div class="col-lg-8">
                                        <label class="switch">
                                            <input type="hidden" value="FALSE" name="">
                                            <input type="checkbox" name="status" checked value="TRUE">
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                             </div>
                        </div>

                     </div>
                 </div>

                 <div class="modal-footer">
                    {!! closeModalButton() !!}
                    <button type="submit" class="btn btn-warning formDraft btn-rounded"><i class="fas fa-save"></i> SaveDraft</button>
                    <button type="submit" class="btn btn-info formSaving submit btn-rounded"><i class="fas fa-paper-plane"></i> Save</button>
                    {{-- {!! renderAjaxButton() !!} --}}
                    {!! Form::close() !!}
                    {{-- <form action="{{ route('rssfeedsettings.rss_data_preview_news') }}" method="post" target="_blank">
                        <input type="text" name="title" id="title_preview">
                        <textarea name="detail" id="detail_preview"></textarea>
                        <button type="submit" class="btn btn-success formPreview btn-rounded"><i class="fas fa-eye"></i> Preview</button>
                    </form> --}}
                 </div>
         </div>
     </div>
 </div>

</section>

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')
@include('stacks.css.datepicker')
@include('stacks.css.summernote')
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('scripts.summernote')
@include('stacks.js.markdown')
@include('stacks.js.hidesettings')

<script>
    function change_news_active(code) {
        let checkState = $("#news-active-" + code).is(":checked") ? 1 : 0;
        axios.post('{{route('rssfeedsettings.change_status_news')}}', {
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
$(function() {

    $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') });
    $(document).ready(function () {
        $('#keywords').select2({
            tags: true,
            tokenSeparators: [' ']
        });
        $('#source').select2();
    });


    $(function () {
        datatable();
    });
    function datatable(){
        $('#table-rss-news-template').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                contentType: "application/json",
                dataType: 'JSON',
                type: "POST",
                url: '{!! route('rssfeedsettings.rss_news_table') !!}',
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
                    data: 'site_name',
                    name: 'site_name'
                },
                {
                    data: 'source',
                    name: 'source'
                },
                {
                    data: 'title',
                    name: 'title',
                },
                {
                    data: 'topic',
                    name: 'topic',
                    className: 'w-10 text-center'
                },
                {
                    data: 'data_status',
                    name: 'data_status',
                    className: 'w-10 text-center'
                },
                {
                    data: 'public_date',
                    name: 'public_date',
                },
                {
                    data: 'view',
                    name: 'view',
                    className: 'w-10 text-center'
                },
                {
                    data: 'link',
                    name: 'link',
                    className: 'no-wrap'
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
});
$(document).ready(function(){
        $("#topic").select2({
            allowClear: true,
            tags: true,
            width: '100%',
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'post',
                dataType: "json",
                url: '/rssfeedsettings/rss_data/topic',
                delay: 250,
                data: function (params) {
                    return {
                        searchTerm: params.term || '',
                        pageNum: params.page || 1,
                    }
                },
                processResults: function (data) {
                    return {
                    results:  $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.id,
                                value: item.name
                            }
                        })
                    };
                },
            }
        });
        $('#source_create').select2({
            allowClear: true,
            tags: true,
            width: '100%',
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'post',
                dataType: "json",
                url: '/rssfeedsettings/rss_data/source',
                delay: 250,
                data: function (params) {
                    return {
                        searchTerm: params.term || '',
                        pageNum: params.page || 1,
                    }
                },
                processResults: function (data) {
                    return {
                    results:  $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.name,
                                value: item.name
                            }
                        })
                    };
                },
            }
        });
        $("#tags").select2({
            allowClear: true,
            tags: true,
            width: '100%',
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'post',
                dataType: "json",
                url: '/rssfeedsettings/rss_data/tags',
                delay: 250,
                data: function (params) {
                    return {
                        searchTerm: params.term || '',
                        pageNum: params.page || 1,
                    }
                },
                processResults: function (data) {
                    return {
                    results:  $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.id,
                                value: item.name
                            }
                        })
                    };
                },
            }
        });
        $('.datetimepicker-input').datetimepicker({showClose: true, showClear: true, minDate: moment().add(-1, 'days') });
    }); 
    var form_save = '.formSaving';
    $('.formPreview').click(function() {
        form_save = '.formPreview';
    });
    $('.formDraft').click(function() {
        form_save = '.formDraft';
    });
    var number = 0;
    $('.ajaxifyFormCreate').submit(function (event) {
        number++;
        if(number == 1){
            let category = $('#category option:selected').val();
            let topic = $('#topic option:selected').val();
            let title_th = $('#title_th').val();
            let detail_th = $('#detail_th').val();
            let source = $('#source_create').val();
            if(form_save == '.formSaving'){
                if(category == undefined){
                    $('#label_category').css('color', '#a94442');
                    $('#category').css('border-color', '#a94442');
                }else{
                    $('#label_category').css('color', '#656d78');
                    $('#category').css('border-color', '#656d78');
                }
                if(source == undefined){
                    $('#label_source').css('color', '#a94442');
                    $('#source_create').css('border-color', '#a94442');
                }else{
                    $('#label_source').css('color', '#656d78');
                    $('#source_create').css('border-color', '#656d78');
                }
                if(topic == undefined){
                    $('#label_topic').css('color', '#a94442');
                    $('#topic').css('border-color', '#a94442');
                }else{
                    $('#label_topic').css('color', '#656d78');
                    $('#topic').css('border-color', '#656d78');
                }
                if(title_th == ''){
                    $('#label_title_th').css('color', '#a94442');
                    $('#title_th').css('border-color', '#a94442');
                }else{
                    $('#label_title_th').css('color', '#656d78');
                    $('#title_th').css('border-color', '#656d78');
                }
                if(detail_th == ''){
                    $('#label_detail_th').css('color', '#a94442');
                    $('#detail_th').css('border-color', '#a94442');
                }else{
                    $('#label_detail_th').css('color', '#656d78');
                    $('#detail_th').css('border-color', '#656d78');
                }

                if(topic == undefined || category == undefined || title_th == '' || detail_th == '' || source == undefined){
                    return false;
                }
            }else if(form_save == '.formDraft'){
                if(title_th == ''){
                    $('#label_title_th').css('color', '#a94442');
                    $('#title_th').css('border-color', '#a94442');
                }else{
                    $('#label_title_th').css('color', '#656d78');
                    $('#title_th').css('border-color', '#656d78');
                }
                if(detail_th == ''){
                    $('#label_detail_th').css('color', '#a94442');
                    $('#detail_th').css('border-color', '#a94442');
                }else{
                    $('#label_detail_th').css('color', '#656d78');
                    $('#detail_th').css('border-color', '#656d78');
                }
                if(title_th == '' || detail_th == ''){
                    return false;
                }
            }
            $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            event.preventDefault();
            var data = new FormData(this);
            if(form_save == '.formPreview'){
                data.append('formsubmit', 'formPreview');
            }else if(form_save == '.formDraft'){
                data.append('formsubmit', 'formDraft');
            }
            axios.post($(this).attr("action"), data).then(function (response) {
                toastr.success(response.data.message, '@langapp('response_status') ');
                $(form_save).html('<i class="fas fa-check"></i> @langapp('save') </span>');
                window.location.href = response.data.redirect;
            }).catch(function (error) {
                if(error.response.data.exception){
                    toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }else{
                    var errors = error.response.data.errors;
                    var errorsHtml= '';
                    $.each( errors, function( key, value ) {
                        errorsHtml += '<li>' + value[0] + '</li>'; 
                    });
                    toastr.error( errorsHtml , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }
            }); 
        }
    });
    function copy_link(value) {
        var tempInput = document.createElement("input");
        tempInput.style = "position: absolute; left: -1000px; top: -1000px";
        tempInput.value = value;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);
    }
</script>
@endpush
@endsection
