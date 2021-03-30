@extends('layouts.app')
@section('content')
@php
    $segments = Request::segments();
    $last_segments  = end($segments);
    // dd($last_segments);
@endphp
<section id="content" class="bg">
    <section class="hbox stretch">      
        <aside id="hide-settings" class="aside aside-md b-r">
            <section class="vbox">
                @include('partial.header-select-site')
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_site')
                    </section>
                </section>
            </section>
        </aside>
       
            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light">
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display:none;">@icon('solid/bars')</a>
                    <div class="bc-head">Site Setting &gt; Keywords </div>
                    {{-- <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                        @icon('solid/download') CSV
                    </a> --}}
                    {{-- <button type="submit" id="btn-change-status" class="btn btn-sm btn-danger m-xs  pull-right" value="bulk-delete" disabled>
                        <span data-rel="tooltip" title="Are you sure?" data-placement="left">@icon('solid/trash-alt') @langapp('delete')</span>
                    </button>
                    <a href="{{route('keyword.create', $siteSettings->code) }}" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="ajaxModal">
                        @icon('solid/plus') @langapp('add')
                    </a> --}}
                </header>
                <section class="scrollable wrapper">
                    <section class="panel panel-default d-none">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-xs-12">
                                    <i class="fas fa-table"></i> Table Keywords
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table  class="table table-striped" id="table_cve_assets">
                                    <thead>
                                        <tr>
                                            <th class="no-sort w-10">
                                                <label>
                                                    <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk" />
                                                    <span class="label-text"></span>
                                                </label>
                                            </th>
                                            <th>@langapp('keyword')</th>
                                            <th>@langapp('type')</th>
                                            <th style="width: 150px;">Last @langapp('update')</th>
                                            <th style="width: 100px;">@langapp('status')</th>
                                            <th class="no-sort" style="width: 100px;">@langapp('action')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="panel panel-default">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-xs-12">
                                    <i class="fas fa-table"></i> Keywords
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <form class="form-inline">
                                            <div class="form-group">
                                                <label class="font-weight-bold m-r-xs" style="font-size: 14px;">Keyword</label>
                                                <input type="text" class="form-control" id="add-todo" placeholder="">
                                            </div>
                                            <button type="button" class="btn-add-keyword btn btn-info m-l-xs">@icon('solid/plus') @langapp('add')</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <hr>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <h5 class="font-weight-bold">Keyword</h5>
                                        <div class="box-item-keyword">
                                            <ul id="keyword_main" class="main-list keyword-list">
                                                {{-- <li class="item-list item--keyword">
                                                    <div class="left-side-item">
                                                        <span class="drag-handle m-r-xs"><i class="fa fa-arrows-alt"></i></span>
                                                        <span class="text-keyword">Keyword 1</span>
                                                    </div>
                                                    <div class="action-keyword">
                                                        <a href="#" class="text-white m-r-xs edit-keyword" data-target="#edit_keyword" data-toggle="modal"><i class="fas fa-ellipsis-v"></i></a>
                                                        <a href="#" class="text-white delete-item-keyword"><i class="fas fa-trash-alt"></i></a>
                                                    </div>
                                                </li> --}}
                                            </ul>
                                        </div>

                                    </div>
                                    <div class="col-lg-4">
                                        <h5 class="font-weight-bold">Social</h5>
                                        <div class="box-item-keyword">
                                            <ul id="social_main" class="main-list social-list">
                                                
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <h5 class="font-weight-bold">Dark Web</h5>
                                        <div class="box-item-keyword">

                                            <ul id="darkweb_main" class="main-list darkweb-list">

                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                </section>
            </section>
     
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
    <!-- Modal create_assets_vulnerability -->
    <div class="modal fade" id="create_assets_vulnerability" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title" id="exampleModalLabel">Asset</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="" class="col-md-3">Site</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control" placeholder="Site">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 control-label">Status</label>
                        <div class="col-lg-9">
                            <label class="switch">
                                <input type="hidden" value="FALSE" name="">
                                <input type="checkbox" name="" value="TRUE">
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
                </form>
            </div>
        </div>
    </div>


    {{-- <div class="modal in fixed-left" id="delete_all" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <span class="modal-title" id="exampleModalLabel">Delete</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="">
                <div class="modal-body">
                    <p class="text-danger">@langapp('delete_warning')  </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="submit" class="delete-all btn btn-danger btn-rounded">
                        <i class="fas fa-paper-plane"></i>
                        Delete
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div> --}}

    <div class="modal" id="delete_all" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="left: unset">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">@langapp('delete')</h4>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <p class="text-danger">@langapp('delete_warning')  </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-default btn-rounded" data-dismiss="modal"><i class="fas fa-times text-muted"></i> Close</a>
                    <button type="button" class="btn btn-info submit btn-rounded delete-all"><i class="fas fa-paper-plane"></i> OK</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="delete_select" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="left: unset">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">@langapp('delete')</h4>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <p class="text-danger">@langapp('delete_warning')  </p>
                    </div>
                    <input type="hidden" id="keywords_main_id_del" name="keywords_main_id_del">
                    <input type="hidden" id="keywords_sub_id_del" name="keywords_sub_id_del">
                    <input type="hidden" id="keywords_type_del" name="keywords_type_del">
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-default btn-rounded" data-dismiss="modal"><i class="fas fa-times text-muted"></i> Close</a>
                    <button type="button" id="btn_delete_keyword" class="btn btn-info submit btn-rounded delete-select"><i class="fas fa-paper-plane"></i> OK</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="edit_keyword" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" style="left: unset">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">Edit Keyword</h4>
                </div>
                <div class="modal-body">
                    <div class="container-fluid">
                        <form action="">
                            <div class="form-group row">
                                <h5 class="font-weight-bold">Keyword</h5>
                                <input type="hidden" id="keywords_main_id_edit" name="keywords_main_id_edit">
                                <input type="text" id="keyword_input" name="keyword" class="form-control">
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-default btn-rounded" data-dismiss="modal"><i class="fas fa-times text-muted"></i> Close</a>
                    <button type="button" id="btn_edit_keyword" class="btn btn-info submit btn-rounded"><i class="fas fa-paper-plane"></i> OK</button>
                </div>
            </div>
        </div>
    </div>

</section>

@push('pagestyle')
    @include('stacks.css.datatables')
    @include('stacks.css.form')
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.menusub')
@include('stacks.js.site_hidesettings')
@include('stacks.js.sort')
<script>

$( document ).ready(function() {
    get_keyword_main();
    get_keyword_sub('social');
    get_keyword_sub('darkweb');
});

$("#btn_edit_keyword").click(function() {
    edit_keyword_process();
});
$("#btn_delete_keyword").click(function() {
    del_keyword_process();
});


function edit_keyword() {
    $('.edit-item-keyword').on("click",function(){
        let keywords_main_id_edit = $(this).data('keywords_main_id');
        let keywords_main_name_edit = $(this).data('keywords_main_name');
        console.log(keywords_main_id_edit);
        $('#keywords_main_id_edit').val(keywords_main_id_edit);
        $('#keyword_input').val(keywords_main_name_edit);
        $("#edit_keyword").modal("show");

    });
}
function delete_keyword_main() {
    $('.delete-item-keyword-main').on("click",function(){
        let keywords_main_id = $(this).data('keywords_main_id');
        console.log(keywords_main_id);
        $('#keywords_main_id_del').val(keywords_main_id);
        $('#keywords_sub_id_del').val('');
        $('#keywords_type_del').val('main');
        $("#delete_select").modal("show");
        {{--$(this).closest('li').remove();--}}
    });
}
function delete_keyword_sub() {
    $('.delete-item-keyword-sub').on("click",function(){
        let keywords_sub_id_del = $(this).data('keywords_sub_id');
        console.log(keywords_sub_id_del);
        $('#keywords_sub_id_del').val(keywords_sub_id_del);
        $('#keywords_main_id_del').val('');
        $('#keywords_type_del').val('sub');
        $("#delete_select").modal("show");
        {{--$(this).closest('li').remove();--}}
    });
}

edit_keyword();
delete_keyword_main();
delete_keyword_sub();

$('.btn-add-keyword').on("click",function (){
    let keyword_text = $("#add-todo").val();
    if(!keyword_text) {
        toastr.warning('Keyword name cannot be empty!' , '@langapp('response_status') ');
        return false;
    } else {
        if(save_add_keyword_main()){
            get_keyword_main();
        }
    }
});

function save_add_keyword_main() {
    let result = 0;
    let keyword_name = $("#add-todo").val();
    $.ajax({
        type:"POST",
        url:"{{ route('KeywordsController.save_add_keyword_main') }}",
        data:{
            code_site:'{{$last_segments}}',
            keyword_name: keyword_name
        },
        beforeSend: function(){
            loading('load');
        },
        success:function(response) {
            loading('stop_load');
            if(response.status == 1) {
                console.log(response);
                result = 1;
                $('#add-todo').val('');
                get_keyword_main();
                toastr.success(response.message, '@langapp('response_status')');
                {{--window.location.href = response.redirect;--}}
            } else {
                console.log(response);
                toastr.warning(response.message, '@langapp('response_status')');
            }

        },
        error: function (error){
            console.log(error);
            result = 0;
            loading('stop_load');
            var errors = error.response.data.errors;
            var errorsHtml = '';
            $.each(errors, function (key, value) {
                errorsHtml += '<li>' + value[0] + '</li>';
            });
            toastr.error(errorsHtml, '@langapp('response_status') ');
        }
    });
    return result;
}

function get_keyword_main() {
    let result = 0;
    $.ajax({
        type:"POST",
        url:"{{ route('KeywordsController.get_keyword_main') }}",
        data:{
            code_site:'{{$last_segments}}'
        },
        beforeSend: function(){
            loading('load');
        },
        success:function(response) {
            loading('stop_load');
            if(response.status == 1) {
                console.log(response);
                result = 1;
                if(response && response.data) {
                    let data = response.data;
                    let x;
                    $("ul.keyword-list").html('');
                    for (x in data) {
                        var newToDo = `
                            <li class="item-list item--keyword" data-id="${data[x].id}">
                                <div class="left-side-item">
                                    <span class="drag-handle m-r-xs"><i class="fa fa-arrows-alt"></i></span>
                                    <span class="text-keyword">${data[x].name}</span>
                                </div>
                                <div class="action-keyword">
                                    <a href="#" class="text-white m-r-xs edit-item-keyword margin-right: 5px;" data-keywords_main_id="${data[x].id}" data-keywords_main_name="${data[x].name}"><svg class="svg-inline--fa" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z"></path></svg></a>
                                    <a href="#" class="text-white delete-item-keyword-main" data-keywords_main_id="${data[x].id}"><i class="fas fa-trash-alt"></i></a>
                                </div>
                            </li>`;
                        $("ul.keyword-list").append(newToDo);{{--prepend--}}
                        edit_keyword();
                        delete_keyword_main();

                    }
                }
            } else {
                console.log(response);
                toastr.warning(response.message, '@langapp('response_status')');
            }

        },
        error: function (error){
            console.log(error);
            result = 0;
            loading('stop_load');
            var errors = error.response.data.errors;
            var errorsHtml = '';
            $.each(errors, function (key, value) {
                errorsHtml += '<li>' + value[0] + '</li>';
            });
            toastr.error(errorsHtml, '@langapp('response_status') ');
        }
    });
    return result;
}

function get_keyword_sub(type) {
    let result = 0;
    $.ajax({
        type:"POST",
        url:"{{ route('KeywordsController.get_keyword_sub') }}",
        data:{
            code_site:'{{$last_segments}}',
            type: type
        },
        beforeSend: function(){
            loading('load');
        },
        success:function(response) {
            loading('stop_load');
            if(response.status == 1) {
                console.log(response);
                result = 1;
                if(response && response.data) {
                    let data = response.data;
                    let x;
                    $('ul.'+type+'-list').html('');
                    for (x in data) {
                        var newToDo = `
                            <li class="item-list item--keyword" data-id="${data[x].id}" data-keywords_main_id="${data[x].keywords_main_id}">
                                <div class="left-side-item">
                                    <span class="drag-handle m-r-xs"><i class="fa fa-arrows-alt"></i></span>
                                    <span class="text-keyword">${data[x].name}</span>
                                </div>
                                <div class="action-keyword">
                                    <a href="#" class="text-white delete-item-keyword-sub" data-keywords_sub_id="${data[x].id}" data-keywords_main_id="${data[x].keywords_main_id}"><i class="fas fa-trash-alt"></i></a>
                                </div>
                            </li>`;
                        $('ul.'+type+'-list').append(newToDo);{{--prepend--}}
                        delete_keyword_sub();
                    }
                }
            } else {
                console.log(response);
                toastr.warning(response.message, '@langapp('response_status')');
            }

        },
        error: function (error){
            console.log(error);
            result = 0;
            loading('stop_load');
            var errors = error.response.data.errors;
            var errorsHtml = '';
            $.each(errors, function (key, value) {
                errorsHtml += '<li>' + value[0] + '</li>';
            });
            toastr.error(errorsHtml, '@langapp('response_status') ');
        }
    });
    return result;
}

function edit_keyword_process() {
    let result = 0;
    let keywords_main_id_edit = $("#keywords_main_id_edit").val();
    let keyword_input = $("#keyword_input").val();
    if(!keyword_input) {
        toastr.warning('Keyword name cannot be empty!' , '@langapp('response_status') ');
        return false;
    }
    $.ajax({
        type:"POST",
        url:"{{ route('KeywordsController.edit_keyword_process') }}",
        data:{
            code_site:'{{$last_segments}}',
            keywords_main_id_edit:keywords_main_id_edit,
            keyword_input:keyword_input
        },
        beforeSend: function(){
            loading('load');
        },
        success:function(response) {
            loading('stop_load');
            if(response.status == 1) {
                console.log(response);
                result = 1;
                if(response && response.message) {
                    $("#edit_keyword").modal("hide");
                    get_keyword_main();
                    get_keyword_sub('social');
                    get_keyword_sub('darkweb');
                    toastr.success(response.message, '@langapp('response_status')');
                    
                }
            } else {
                console.log(response);
                toastr.error('@langapp('request_failed')', '@langapp('response_status')');
                get_keyword_main();
                get_keyword_sub('social');
                get_keyword_sub('darkweb');
            }
        },
        error: function (error){
            console.log(error);
            result = 0;
            loading('stop_load');
            var errors = error.response.data.errors;
            var errorsHtml = '';
            $.each(errors, function (key, value) {
                errorsHtml += '<li>' + value[0] + '</li>';
            });
            toastr.error(errorsHtml, '@langapp('response_status') ');
            {{--toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');--}}
        }
    });
    return result;
}
function del_keyword_process() {
    let result = 0;
    let keywords_main_id_del = $("#keywords_main_id_del").val();
    let keywords_sub_id_del = $("#keywords_sub_id_del").val();
    let keywords_type_del = $("#keywords_type_del").val();
    $.ajax({
        type:"POST",
        url:"{{ route('KeywordsController.del_keyword_process') }}",
        data:{
            code_site:'{{$last_segments}}',
            keywords_main_id_del:keywords_main_id_del,
            keywords_sub_id_del:keywords_sub_id_del,
            keywords_type_del:keywords_type_del
        },
        beforeSend: function(){
            loading('load');
        },
        success:function(response) {
            loading('stop_load');
            if(response.status == 1) {
                console.log(response);
                result = 1;
                if(response && response.message) {
                    $("#delete_select").modal("hide");
                    get_keyword_main();
                    get_keyword_sub('social');
                    get_keyword_sub('darkweb');
                    toastr.success(response.message, '@langapp('response_status')');
                    
                }
            } else {
                console.log(response);
                toastr.error('@langapp('request_failed')', '@langapp('response_status')');
                get_keyword_main();
                get_keyword_sub('social');
                get_keyword_sub('darkweb');
            }
        },
        error: function (error){
            console.log(error);
            result = 0;
            loading('stop_load');
            var errors = error.response.data.errors;
            var errorsHtml = '';
            $.each(errors, function (key, value) {
                errorsHtml += '<li>' + value[0] + '</li>';
            });
            toastr.error(errorsHtml, '@langapp('response_status') ');
            {{--toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');--}}
        }
    });
    return result;
}

function check_insert_keyword_process(from_id,to_id,attributes_id) {
    let result = 0;

    $.ajax({
        type:"POST",
        url:"{{ route('KeywordsController.check_insert_keyword_process') }}",
        data:{
            code_site:'{{$last_segments}}',
            from_id:from_id,
            to_id:to_id,
            attributes_id:attributes_id
        },
        beforeSend: function(){
            loading('load');
        },
        success:function(response) {
            loading('stop_load');
            if(response.status == 1) {
                console.log(response);
                result = 1;
                if(response && response.message) {
                    $("#delete_select").modal("hide");
                    get_keyword_main();
                    get_keyword_sub('social');
                    get_keyword_sub('darkweb');
                    toastr.success(response.message, '@langapp('response_status')');
                    
                }
            } else {
                console.log(response);
                toastr.error('@langapp('request_failed')', '@langapp('response_status')');
                get_keyword_main();
                get_keyword_sub('social');
                get_keyword_sub('darkweb');
            }
        },
        error: function (error){
            console.log(error);
            result = 0;
            loading('stop_load');
            var errors = error.response.data.errors;
            var errorsHtml = '';
            $.each(errors, function (key, value) {
                errorsHtml += '<li>' + value[0] + '</li>';
            });
            toastr.error(errorsHtml, '@langapp('response_status') ');
            {{--toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');--}}
        }
    });
    return result;
}






var keyword_item = document.getElementById('keyword_main'),
	social_item = document.getElementById('social_main'),
	darkweb_item = document.getElementById('darkweb_main');

new Sortable(keyword_item, {
	group: {
        name: 'shared',
        pull: 'clone',
        put: false,
        revertClone: true
    },
    sort: false,
	animation: 150,
    dataIdAttr: 'data-id',
    fallbackClass: "sortable-fallback",
    onSort: function (evt) {
		{{--console.log(evt);--}}
	},
    onAdd: function (evt) {
		console.log(evt);
	},
});

new Sortable(social_item, {
    group: {
        name: 'shared'
    },
    sort: false,
    animation: 150,
    onSort: reportActivity(2),
    onAdd: function (evt) {
        console.log(evt.from.id);
        console.log(evt.to.id);
        console.log(evt.item.attributes['data-id'].value);
		console.log(evt);
        console.log(evt);
        let from_id = evt.from.id;
        let to_id = evt.to.id;
        let attributes_id = evt.item.attributes['data-id'].value;
        check_insert_keyword_process(''+from_id+'',''+to_id+'',attributes_id);
	},
});
new Sortable(darkweb_item, {
    group: {
        name: 'shared'
    },
    sort: false,
    animation: 150,
    onSort: reportActivity(3),
    onAdd: function (evt) {
        console.log(evt.from.id);
        console.log(evt.to.id);
        console.log(evt.item.attributes['data-id'].value);
		console.log(evt);
        let from_id = evt.from.id;
        let to_id = evt.to.id;
        let attributes_id = evt.item.attributes['data-id'].value;
        check_insert_keyword_process(''+from_id+'',''+to_id+'',attributes_id);
	},
});

function reportActivity(val) {
    console.log('The sort order has changed : '+val);
};


{{--
    new Sortable(keyword_item, {
	group: {
        name: 'shared'
    },
	animation: 150,
    sort: false,
    dataIdAttr: 'data-id',
    removeCloneOnHide: true
});

new Sortable(social_item, {
	group: {
        name: 'shared',
        pull: 'true',
        put: true
    },
	animation: 150,
    dataIdAttr: 'data-id'
});
new Sortable(darkweb_item, {
	group: {
        name: 'shared',
        pull: 'true',
        put: true
    },
	animation: 150,
    dataIdAttr: 'data-id'
});
    --}}



 
    $(function() {

        $('#table_cve_assets').on('click', '.select-chk', function () {
                if ($(this).is(':checked')) {

                    $('#btn-change-status').prop("disabled", false);
                } else {
                    
                    if ($('.select-chk').filter(':checked').length < 1){

                        $('#btn-change-status').attr('disabled',true);
                    }
                }
            });

           

            $('#table_cve_assets').on('click', '.keyword_id', function () {
                if ($(this).is(':checked')) {
                
                    
                    $('#btn-change-status').prop("disabled", false);
                } else {
                    if ($('.keyword_id').filter(':checked').length < 1){

                        $('#btn-change-status').attr('disabled',true);
                    }
                }
        });

        var keyword_id = [];
     
        $("#btn-change-status").click(function() {
            keyword_id=[];
            $('.keyword_id:checked').each(function () {
                keyword_id.push(this.value);
            });
            $('#delete_all').modal('show');
            $('.delete-all').click(function(){
                $.ajax({
                    type:"POST",
                    url:"{{ route('KeywordsController.delete_checked') }}",
                    data:{id: keyword_id},
                    beforeSend: function(){
                        loading('load');
                    },
                    success:function(response) {
                        loading('stop_load');
                        toastr.success(response.message, '@langapp('response_status')');
                        window.location.href = response.redirect;
                        $('#delete_all').modal('hide');
                    },
                    error: function (error){
                        loading('stop_load');
                        var errors = error.response.data.errors;
                        var errorsHtml = '';
                        $.each(errors, function (key, value) {
                            errorsHtml += '<li>' + value[0] + '</li>';
                        });
                        toastr.error(errorsHtml, '@langapp('response_status') ');
                    }
    
                });
            });
        });


        var table = $('#table_cve_assets').DataTable({
            pageLength: 50,
            processing: true,
            serverSide: true,
            destroy: true,
            "dom": '<"column-xs-flex d-flex justify-content-between m-t-10"l<"d-flex"f<"m-l-10"B>>>rt<"bottom"ip><"clear">',
            ajax: {
                url: '{!! route('KeywordsController.data') !!}',
                data: {
                    "site_code":'{{ Request::segment(3) }}'
                },
                type: "POST",
            },
            order: [
                [0, "desc"]
            ],
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
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'last_update',
                    name: 'created_at'
                },
                {
                    data: 'status',
                    name: 'status',
                    className: 'text-center'
                },
                {
                    data: 'action',
                    name: 'action',
                    className: 'no-wrap'
                },
                
            ]
        });
    });

    function change_keyword_active(code) {
        let checkState = $("#keyword_active_" + code).is(":checked") ? 1 : 0;
        axios.post('{{route('keyword.change_status')}}', {
            active: checkState,
            code: code,
        }).then(function (response) {
            toastr.success(response.data.message, '@langapp('response_status')');
            window.location.href = response.data.redirect;
        }).catch(function (error) {
            var errors = error.response.data.errors;
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