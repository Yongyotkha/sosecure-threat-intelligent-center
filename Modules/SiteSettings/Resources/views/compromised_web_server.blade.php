@extends('layouts.app')
@section('content')

<style>
    .block {
        display: block;
        width: 100%;
        border: none;


        font-size: 16px;
        cursor: pointer;
        text-align: center;
    }
</style>
<section id="content" class="bg">
    <section class="hbox stretch">
        <aside id="hide-settings" class="aside aside-md b-r">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#setting-nav">@icon('solid/bars')</a>
                    <a class="hide-setting btn btn-icon btn-default btn-sm pull-right m-r-xs">@icon('solid/bars')</a>
                    <p class="h3 text-elipse-setting">{{@$siteSettings->name}}</p>
                </header>
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_site')
                    </section>
                </section>
            </section>
        </aside>
        <aside>
            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light">
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs"
                        style="margin-top: 0;display: none">@icon('solid/bars')</a>
                    <div class="bc-head">Site Settings > Web Server </div>

                    <button type="button" id="btn_del_select" class="btn btn-sm btn-danger m-xs  pull-right"
                        value="bulk-delete" disabled>
                        <span data-rel="tooltip" title="Are you sure?" data-placement="bottom">@icon('solid/trash-alt')
                            @langapp('delete')</span>
                    </button>
                    <button id="btn-change-status" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right"
                        data-toggle="modal" data-target="#change_status" disabled>
                        Change Status
                    </button>
                    <button id="add_asset" data-toggle="modal" data-target="#add_asset_modal"
                        class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                        <span>@icon('solid/plus') Add</span>
                    </button>
                    {{-- <button id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                    <span><i class="fas fa-filter"></i> @langapp('Search_Advance')</span>
                    </button> --}}
                </header>
                <section class="scrollable wrapper">
                    {{-- <section class="panel panel-default" id="hide-advance-search" style="display: none;">
                        <div class="container-fluid" style="padding: 2rem;">
                            <div class="row m-b-md">
                                <div class="col-lg-12">
                                    <div class="row d-flex align-items-center">
                                        <label for="" class="col-sm-1 col-xs-12 col-form-label">Search</label>
                                        <div class="col-sm-11 col-xs-12">
                                            <input type="text" id="search" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="row d-flex align-items-center">
                                        <label for="" class="col-sm-3 col-xs-12 col-form-label">Source</label>
                                        <div class="col-sm-9 col-xs-12">
                                            <select id="source_select" class="form-control">
                                                <option value="">All</option>
                                                @if($DataLeakSocial)
                                                @foreach($DataLeakSocial as $DataLeakSocial_val)
                                                <option value="{{$DataLeakSocial_val->id}}">
                    {{$DataLeakSocial_val->source}}</option>
                    @endforeach
                    @endif
                    </select>
                    </div>
                    </div>
                    </div>
                    <div class="col-lg-4 text-center">
                        <div id="datafeed_date"
                            style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display:block;margin-bottom:0;">
                            <i class="fa fa-calendar"></i>&nbsp;
                            <span></span> <i class="fa fa-caret-down"></i>
                        </div>
                    </div>
                    <div class="col-lg-4 text-center">
                        <div style="margin-top: 8px;">
                            <label class="mr-3">
                                <input type="checkbox" name="check_all" id="check_all" value="TRUE">
                                <span class="label-text" style="font-size: 16px;">All</span>
                            </label>
                            <label class="mr-3">
                                <input type="checkbox" name="check_pending" id="check_pending" value="TRUE">
                                <span class="label-text" style="font-size: 16px;">Panding</span>
                            </label>
                            <label class="mr-3">
                                <input type="checkbox" name="check_approved" id="check_approved" value="TRUE">
                                <span class="label-text" style="font-size: 16px;">Approved</span>
                            </label>
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 text-right mt-2">
                            <button type="button" id="btn_darkweb_feed_search" class="btn btn-info btn-responsive">
                                <i class="fas fa-search"></i>
                                @langapp('apply')
                            </button>
                            <button type="button" id="btn_darkweb_feed_reset" class="btn btn-default btn-responsive"
                                style="white-space: nowrap">
                                <i class="fas fa-broom"></i>
                                <span> Clear </span>
                            </button>
                        </div>
                    </div>
                    </div>
                </section> --}}


                <section class="panel panel-default">
                    <header class="panel-heading font-bold panel-header-blue">
                        <div class="row">
                            <div class="col-xs-12">
                                <i class="fas fa-table"></i> Table Web Server
                            </div>
                        </div>
                    </header>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped" id="table_web_server">
                                <thead>
                                    <tr>
                                        <th class="no-sort w-10">
                                            <label>
                                                <input name="select_all" value="1" id="select-all" type="checkbox"
                                                    class="select-chk" />
                                                <span class="label-text"></span>
                                            </label>
                                        </th>
                                        <th>No.</th>
                                        <th>Site Name</th>
                                        <th>Name</th>
                                        <th>User</th>
                                        <th>Password</th>
                                        <th>Root Path</th>
                                        <th>Status</th>
                                        <th>Last Update</th>

                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </section>
    </section>
    </aside>
</section>

<a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
<!-- Modal create_assets_vulnerability -->

<div class="modal in fixed-left" id="add_asset_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-aside" role="document">
        <div class="modal-content">

            <div class="modal-header bg-blue">
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                <h4 class="modal-title text-white">
                    <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip"
                        title="Fullscreen" data-placement="right"></i>
                    Add Asset
                </h4>
            </div>
            <form id='add_asset_click' method="POST">
                <div class="modal-body">


                    <div class="form-group row">
                        <label style="padding-top: 7px" class="col-lg-3 control-label">OS<span
                                class="text-danger">*</span> </label>
                        <div class="col-lg-8">
                            <select id="os" class="form-control check_test_select" required>
                                <option selected value="Linux">Linux</option>
                                <option value="Windows">Windows</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label style="padding-top: 7px" class="col-lg-3 control-label">IP <span
                                class="text-danger">*</span> </label>
                        <div class="col-lg-8">
                            <input type="text" id="ip" class="form-control check_test" required="yes">
                            <span style="color:red;"><small id="check_i"></small></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label style="padding-top: 7px" class="col-lg-3 control-label">Port</label>
                        <div class="col-lg-8">
                            <input type="text" id="port" class="form-control check_test">
                        </div>
                    </div>


                    <div class="form-group row">
                        <label style="padding-top: 7px" class="col-lg-3 control-label">User & Password<span
                                class="text-danger">*</span> </label>
                        <div class="col-lg-5">

                            <select id="u_p" class="form-control check_test_select" required>
                                <option value="">Choose an User</option>
                                @if($Credentials)
                                @foreach ($Credentials as $item)
                                <option value="{{@$item->id}}">{{@$item->name}}</option>
                                @endforeach
                                @endif
                            </select>
                            <span style="color:red;"><small id="check_u_p"></small></span>
                        </div>
                        &nbsp;<button type="button" data-toggle="collapse" href="#demo"
                            class="btn btn-{{ get_option('theme_color')  }}"><i class="fas fa-plus"></i>&nbsp; Add
                            New</button>
                    </div>

                    <div id="demo" class="collapse box">

                        <fieldset class="collapsible">

                            <legend>Add Profile</legend>
                            <div class="form-group row">
                                <label style="padding-top: 7px" class="col-lg-3 control-label">Name <span
                                        class="text-danger">*</span> </label>
                                <div class="col-lg-8">
                                    <input type="text" id="name_new" class="form-control check_test">
                                    <span style="color:red;"><small id="check_n"></small></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label style="padding-top: 7px" class="col-lg-3 control-label">User <span
                                        class="text-danger">*</span> </label>
                                <div class="col-lg-8">
                                    <input type="text" id="user_new" class="form-control check_test">
                                    <span style="color:red;"><small id="check_u"></small></span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label style="padding-top: 7px" class="col-lg-3 control-label">Password <span
                                        class="text-danger">*</span> </label>
                                <div class="col-lg-8">
                                    <input type="password" id="pass_new" class="form-control check_test">
                                    <span style="color:red;"><small id="check_p"></small></span>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger btn-rounded" data-toggle="collapse"
                                    data-target="#demo">
                                    <i class="fas fa-times"></i>
                                    Close
                                </button>
                                <button type="button" onclick="new_credentials()" class="btn btn-info btn-rounded">
                                    <i class="fas fa-paper-plane"></i>
                                    Save
                                </button>
                            </div>
                            <hr>
                        </fieldset>



                    </div>


                    <div class="form-group row">
                        <label style="padding-top: 7px" class="col-lg-3 control-label"> </label>
                        <div class="col-lg-8">
                            <button type="button" class="btn btn-{{ get_option('theme_color')  }} block"
                                onclick="test_data()">Test Connection</button>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label style="padding-top: 7px" class="col-lg-3 control-label">Root Path <span
                                class="text-danger">*</span> </label>
                        <div class="col-lg-8">
                            <input type="text" id="root_path" class="form-control " required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label style="padding-top: 7px" class="col-lg-3 control-label">Type Extension<span
                                class="text-danger">*</span></label>
                        <div class="col-lg-8">
                            <select class="select2-option form-control " id="type1" multiple="multiple" required>
                                <option value=".php">.PHP</option>
                                <option value=".js">.JS</option>
                                <option value=".asp">.ASP</option>
                                <option value=".exe">.EXE</option>
                                <option value=".dll">.DLL</option>
                                <option value=".cs">.CS</option>
                                <option value=".cshtml">.CSHTML</option>
                                <option value=".config">.CONFIG</option>
                                <option value=".htaccess">.HTACCESS</option>
                                <option value=".xml">.XML</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row" style="padding-top: 7px">
                        <label class="col-lg-3 control-label">Status </label>
                        <div class="col-lg-8">
                            <label class="switch">
                                <input type="checkbox" id="status" name="status" checked value="1">
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
                    <button type="submit" value="Submit" required class="btn btn-info btn-rounded" id="button_save"
                        disabled>
                        <i class="fas fa-paper-plane"></i>
                        Save
                        {{-- Yes, approve --}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="delete_web_sever" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"
    style="left: unset">
    <div class="modal-dialog modal-dialog-aside" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">@langapp('delete')</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <p class="text-danger">@langapp('delete_warning') </p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-default btn-rounded" data-dismiss="modal"><i
                        class="fas fa-times text-muted"></i> Close</a>
                <button type="button" class="btn btn-info submit btn-rounded delete_webdefacement_submit"
                    onclick="delete_web_server_save()"><i class="fas fa-paper-plane"></i> OK</button>
            </div>
        </div>
    </div>
</div>

{{-- <div class="modal in fixed-left" id="delete_web_sever" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-aside" role="document">
        <div class="modal-content">
            <div class="modal-header bg-blue">
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                <h4 class="modal-title text-white">
                    <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip"
                        title="Fullscreen" data-placement="right"></i>
                    @langapp('delete')
                </h4>
            </div>
            <form action="">
                <div class="modal-body">
                    <p class="text-danger">@langapp('delete_warning') </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="button" onclick="delete_web_server_save()" data-dismiss="modal"
                        class="btn btn-info btn-rounded">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> --}}
<div class="modal" id="change_fix" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"
    style="left: unset">
    <div class="modal-dialog modal-dialog-aside" role="document">
        <div class="modal-content">
            <div class="modal-header bg-blue">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title text-white">
                    <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip"
                        title="Fullscreen" data-placement="right"></i>
                    Confirm Information
                </h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <span class="modal-title">Are you sure you want to change fix this datas?</span>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-default btn-rounded" data-dismiss="modal"><i
                        class="fas fa-times text-muted"></i> Close</a>
                <button type="button" class="btn btn-info submit btn-rounded change_fix_submit"><i
                        class="fas fa-paper-plane"></i> OK</button>
            </div>
        </div>
    </div>
</div>


</section>
{{-- 
<div id='area_modal_credentials'></div> --}}

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.datepicker')
@include('stacks.css.form')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.menusub')
@include('stacks.js.site_hidesettings')
@include('stacks.js.advanced_search')
@include('stacks.js.fullscreen')
@include('stacks.js.modal_create_credentials')
<script>
    {{--$('form').each(function () {
            if ($(this).data('validator'))
                $(this).data('validator').settings.ignore = ".note-editor *";
        });

        $('#detail_content').summernote('destroy');--}}


   
    var ip = null;
    var port = null;
    var user = null;
    var password = null;
    var root_path = null;
    var os = null;
    var check = 1;
    var web_server_id_chang = [];
    var web_server_id_delete_chang = [];
    var web_server_id_delete = null;
    var type = null;

    var user_new = null;
    var password_new = null;
    var name_new = null;




    $('#table_web_server').on('click', '.select-chk', function () {
        if ($(this).is(':checked')) {

            $('#btn-change-status,#btn_del_select').prop("disabled", false);
        } else {
            
            if ($('.select-chk').filter(':checked').length < 1){

                $('#btn-change-status,#btn_del_select').attr('disabled',true);
            }
        }
    });

    $('#table_web_server').on('click', '.web_server_id', function () {
        if ($(this).is(':checked')) {
            $('#btn-change-status,#btn_del_select').prop("disabled", false);
            {{--if($('.web_server_id').filter(':checked').length >= 5){
                document.getElementById("select-all").checked = true;
            }--}}
        } else {
            document.getElementById("select-all").checked = false;
            if ($('.web_server_id').filter(':checked').length < 1){
                
                $('#btn-change-status,#btn_del_select').attr('disabled',true);
            }
        }
    });   





    $(function() {
        table_web_server();
        $(".check_test").keypress(function() {

            $('#button_save').prop("disabled", true);

        });

        

        $(".check_test_select").change(function() {
            if($('#os').val()=='Linux'){
                $("#port").val('22');
            }else{
                $("#port").val('');
            }
            
            $('#button_save').prop("disabled", true);
            $('#check_u_p').html('');

        });
    });

    $(function() { 
        var start = moment().startOf('hour');
        var end = moment().startOf('hour').add(32, 'hour');
        function cb(start, end) {
            $('#datafeed_date span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        }
        $('#datafeed_date').daterangepicker({
            timePicker: true,
            startDate: start,
            endDate: end,
            locale: {
                format: 'M/DD hh:mm A'
            },
            ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);
        cb(start, end);


    });

    $("#status").on('change', function() {
        if ($(this).is(':checked')) {
            $(this).attr('value', '1');
        } else {
            $(this).attr('value', '0');
        }
        
        check= $('#status').val();
      
 
  
    });

    function add_new() {
        $("#name_new").val('');
        $("#user_new").val('');
        $("#pass_new").val('');
        $('#check_p').html('');
        $('#check_u').html('');
        $('#check_n').html('');
        name_new = null;
        user_new = null;
        password_new = null;
    }

    $(function() {

        $("#name_new").keypress(function() {

            $('#check_n').html('');

        });
        $("#user_new").keypress(function() {

            $('#check_u').html('');

        });
        $("#pass_new").keypress(function() {

            $('#check_p').html('');

        });
        $("#ip").keypress(function() {

        $('#check_i').html('');

        $("#u_p").change(function() {

            $('#check_u_p').html('');

        });

        });

    });

    function new_credentials() {
        name_new = $('#name_new').val();
        user_new = $('#user_new').val();
        password_new = $('#pass_new').val();

    
        if(name_new==''){
            $('#check_n').html('Please fill out.');

        }else if(user_new==''){
            $('#check_u').html('Please fill out.');

        }else if(password_new==''){
            $('#check_p').html('Please fill out.');

        }else{
            $.ajax({
                type:"POST",
                url:"{{ route('compromised_web_server.web_server_add_user') }}",
                data:{
                    name:name_new,
                    password:password_new,
                    user:user_new,
                    site:{!!json_encode($siteID)!!},
                },
                beforeSend: function(){
                    loading('load');
                },
                success:function(response) {
                    loading('stop_load');
                    console.log(response.message);
                    
                    if(response.message!=''){   
                        var data = {
                        id: response.id,
                        text: response.name,
                        };
                        var newOption = new Option(data.text, data.id, false, false);
                        $('#u_p').append(newOption).trigger('change');
                        $('#u_p').val(data.id).trigger('change');
                        $("div.box").collapse("hide");
                        
                        toastr.success(response.message, '@langapp('response_status')');
                    }else{

                    toastr.error('There is already this name in the system.', '@langapp('response_status')');

                    }


                    
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
        }

        

        
    }

    $("#add_asset_click").submit(function(e) {

        ip = $('#ip').val();
        port = $('#port').val();
        user = $('#u_p').val();
        root_path = $('#root_path').val();
        os = $('#os').val();
        type = $('#type1').val();

        $.ajax({
            type:"POST",
            url:"{{ route('compromised_web_server.web_server_create') }}",
            data:{
                check:Number(check),
                os:os,
                root_path:root_path,
                user:user,
                ip:ip,
                port:port,
                id_chang: web_server_id_chang,
                site:{!!json_encode($siteID)!!},
                type:type.join(),
            },
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
                loading('stop_load');
                if(response.message!=''){
                    $('#button_save').prop("disabled", true);
                    toastr.success(response.message, '@langapp('response_status')');
                    window.location.href = response.redirect;
                }else{
                    toastr.error('There is already this name in the system.', '@langapp('response_status')');
                }
                
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

        e.preventDefault();
    });


    function add_asset_click() {

     
       
   }
   var set_timeInterval;
   function test_data(){
        ip = $('#ip').val();
        port = $('#port').val();
        user = $('#u_p').val();
        password = $('#password').val();
        os = $('#os').val();

        if(ip==''){
            $('#check_i').html('Please fill out.');
        }
        else if(user==''){
            $('#check_u_p').html('Choose an User.');
        }else{
            $.ajax({
                type:"POST",
                url:"{{ route('compromised_web_server.checkWebserverIP') }}",
                data:{
                    ip:ip,
                    os:os,
                    password:password,
                    user:user,
                    port:port,
                },
                beforeSend: function(){
                    loading('load');
                },
                success:function(response) {
                    if(response.status === true){
                        set_timeInterval = setInterval(function(){ 
                            load_data_connection(response.data); 
                        }, 3000);
                    }else{
                        loading('stop_load');
                        toastr.error(response.message, '@langapp('response_status')');
                    } 
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

        }
    }

    function load_data_connection(key){ 
        $.ajax({
            type:"POST",
            url:"{{ route('compromised_web_server.load_data_connection') }}",
            data:{
                key:key,
            },
            success:function(response) {
                if(response.status == 'complete'){
                    clearInterval(set_timeInterval);
                    loading('stop_load');
                    if(response.webserverConnect==true){
                        toastr.success(response.message, '@langapp('response_status')');
                        $('#button_save').prop("disabled", false);
                    }else{
                        toastr.error(response.message, '@langapp('response_status')');
                    }
                }
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
    }

      

   $("#add_asset").on('click', function() {

        ip = null;
        port = null;
        user = null;
        password = null;
        root_path = null;
        os = null;
        check=1;
        type=null;
        

        document.getElementById("status").checked = true;
        $("#ip").val('');
        $("#port").val('22');
        $("#user").val('');
        $("#password").val('');
        $("#root_path").val('');
        $("#os").val('Linux').trigger('change');
        $('#type1').val('').trigger('change');
      
 
  
    });

   function table_web_server() {
    
    $('#table_web_server').DataTable({
            pageLength: 50,
            processing: true,
            serverSide: true,
            destroy: true,
            order: [[ 8, "desc" ]],
            ajax: {
                type: "POST",
                url: '{!! route('compromised_web_server.table_web_server') !!}',
                data: ({

                        site: {!!json_encode($siteID)!!},

                }),
            },
        
            initComplete : function( settings, json){
                $('[data-toggle="tooltip"]').tooltip();
            
                
            },
            createdRow: function ( row, data, index ) {
                $(row).attr('id', 'tr' + data.id);
            },

            columns: [
                {
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    data: 'chk',
                    className: 'w-10'
                },

                {

                    orderable: false,
                    searchable: false,
                    sortable: false,
                    data: 'no.',
                    name: 'no.',
                    className: 'w-10 text-center'


                },

                {
                    orderable: false,
                    searchable: false,
                    sortable: false,
        
                    data: 'name',
                    name: 'name',
            
                    
                },
                {
                    data: 'ip',
                    name: 'ip',
         
                },
                {
                    data: 'user',
                    name: 'user',
               

                },
                {
                    orderable: false,
                    searchable: false,
                    sortable: false,
 
                    data: 'password',
                    name: 'password',
           
                   
                },
                {
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    data: 'path',
                    name: 'path',
         
                },
                {
                    orderable: false,
                    searchable: false,
                    sortable: false,
      
                    data: 'status',
                    name: 'status',
                    className: 'w-10 text-center'
           
                },
                {
                    data: 'updated_at',
                    name: 'updated_at',
       
                },
                {
                    orderable: false,
                    searchable: false,
                    sortable: false,
                    data: 'action',
                    name: 'action',
                    className: 'w-10 text-center no-wrap'

                },
            ],

            columnDefs: [

                {
                    targets: 0,

                    width: '1px',
                    render: function (data, type, full, meta) {

                        return  '<label><input type="checkbox" name="web_server_id" class="web_server_id" value="' + full.id + '"><span class="label-text"></span></label>';
                    },
                },
                {
                    targets: 1,

                    width: '1px',
                    render: function (data, type, full, meta) {

                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                },
                {
                    targets: 2,
                    width: '10px',
                    render: function (data, type, full, meta) {
                        return full.get_site.name;
                            
                    },
                },
                {
                    targets: 3,
                    width: '10px',
                    
                    render: function (data, type, full, meta) {
                    
            
                        return full.ip+':'+full.port;

                    },
                },
                {
                    targets: 4,
                    width: '10px',
                    render: function (data, type, full, meta) {

                        return full.user;


                            
                        
                    },
                },
                
                {
                    targets: 5,
                    width: '10px',
                    render: function (data, type, full, meta) {

                        

                        return '<span style="-webkit-text-security: disc;">'+full.password+'</span>';

                    },
                },

                {
                    targets: 6,
                    width: '10px',
                    render: function (data, type, full, meta) {

                            
                        return '<div class="text-elip" data-rel="tooltip" title="'+full.path+'">'+full.path+'</div>';
                        

                        
                    },
                },
                {
                    targets: 7,
  
                    width: '5px',
                    render: function (data, type, full, meta) {

                            
                        var checked_val = null;
                                    if (full.active == 1) {
                                        checked_val = 'checked';
                                    } else {
                                        checked_val = '';
                                    }
                            
                                return  '<label class="switch"><input type="checkbox" id="web_server_active_' +full.id+  '" onchange="web_server_active( '+full.id+')" '+checked_val+' name="active" value="1"><span class="slider round"></span></label>';
                        

                        
                    },
                },
                {
                    targets: 8,
                    
                    width: '10px',
                    render: function (data, type, full, meta) {
                
                        if(full.updated_at){
                            return full.updated_at;
                        }else{
                            return '-';
                        }     
                        

                    },
                },
                {
                    targets: 9,

                    width: '70px',
                    class:'nowrap',
                    render: function (data, type, full, meta) {
                        var html = '';
                        html =`<a href="${base_url}/compromised_web_server/web_server_edit_modal/${full.code}" class="btn btn-{{get_option("theme_color") }} btn-xs" data-toggle='ajaxModal'>
                                <svg class='svg-inline--fa' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M497.9 142.1l-46.1 46.1c-4.7 4.7-12.3 4.7-17 0l-111-111c-4.7-4.7-4.7-12.3 0-17l46.1-46.1c18.7-18.7 49.1-18.7 67.9 0l60.1 60.1c18.8 18.7 18.8 49.1 0 67.9zM284.2 99.8L21.6 362.4.4 483.9c-2.9 16.4 11.4 30.6 27.8 27.8l121.5-21.3 262.6-262.6c4.7-4.7 4.7-12.3 0-17l-111-111c-4.8-4.7-12.4-4.7-17.1 0zM124.1 339.9c-5.5-5.5-5.5-14.3 0-19.8l154-154c5.5-5.5 14.3-5.5 19.8 0s5.5 14.3 0 19.8l-154 154c-5.5 5.5-14.3 5.5-19.8 0zM88 424h48v36.3l-64.5 11.3-31.1-31.1L51.7 376H88v48z'></path></svg>
                                </a>`;
                        html +=`<a href="#" onclick="delete_web_server(${full.id})" class="btn btn-danger btn-xs"  data-toggle="modal" data-target="#delete_web_sever"><i class="fas fa-trash-alt"></i></a>`;
                        return html;
                    },
                },


            ]
   
        });
    }
    function web_server_active(id) {

        let checkState = $("#web_server_active_" + id).is(":checked") ? 1 : 0;
        axios.post('{{route('compromised_web_server.web_server_change_status')}}', {
            active: checkState,
            id: id,
            site:{!!json_encode($siteID)!!},
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

    $("#btn-change-status").click(function() {
        $('#change_fix').modal('show');
        $('.web_server_id:checked').each(function () {
            web_server_id_chang.push(this.value);
            
        });
    });
    $(".change_status_submit").click(function() {    

        $.ajax({
             type:"POST",
            url:"{{ route('compromised_web_server.web_server_change_status') }}",
            data:{
                id_chang: web_server_id_chang,
                site:{!!json_encode($siteID)!!},
            },
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
                loading('stop_load');
                toastr.success(response.message, '@langapp('response_status')');
                window.location.href = response.redirect;
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


    function delete_web_server(id){
        web_server_id_delete = id;
        web_server_id_delete_chang=[];
     
    }

    function delete_web_server_save(){
      
        axios.post('{{route('compromised_web_server.web_server_delete')}}', {
            id: web_server_id_delete,
            site:{!!json_encode($siteID)!!},
            id_chang: web_server_id_delete_chang,
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

    $("#btn_del_select").click(function() {
        $('#delete_web_sever').modal('show');
        $('.web_server_id:checked').each(function () {
            web_server_id_delete_chang.push(this.value);
            
        });
    });


</script>
@endpush
@endsection