@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">      
        {{-- <aside id="hide-settings" class="aside aside-md b-r">
            <section class="vbox">
                @include('partial.header-select-site')
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_site')
                    </section>
                </div>
                </section>
            </section>
        </aside> --}}
  
            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light">
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display: none">@icon('solid/bars')</a>
                    {{-- <div class="bc-head">Site Settings > Data Leak URL</div> --}}
                    <div class="bc-head">Site Settings > URL Feed</div>

                   
                    {{-- <button type="submit" id="button" class="btn btn-sm btn-danger m-xs  pull-right" value="bulk-delete" >
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt') @langapp('delete')</span>
                    </button> --}}
                    {{-- <a href="#" data-toggle="modal" data-target="#modal_data_leak_url" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('create')">
                        @icon('solid/plus') Add
                    </a> --}}
                    @if(Auth::user()->site_role_id == 1)
                        @if(@get_role_custom()['superadmin'] == 1 || @get_role_custom()['client'] == 1)
                            <a href="#" id="btn_md_create"
                                class="btn btn-sm btn-{{ get_option('theme_color') }} pull-right" data-toggle="modal"
                                data-target="#wdfm_website">
                                @icon('solid/plus') @langapp('add')
                            </a>
                        @endif
                    @endif

                </header>
                <section class="scrollable wrapper">                   
                    <section class="panel panel-default">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-xs-12">
                                    <i class="fas fa-table"></i> Table URL Feed
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">

                            <div class="table-responsive">
                                <div style="width: 100%">
                                    <table id="tbl_url_feed" class="table table-borered table-striped">
                                        <thead>
                                            <tr>
                                                {{-- <th class="no-sort">
                                                    <label>
                                                        <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk" />
                                                        <span class="label-text"></span>
                                                    </label>
                                                </th> --}}
                                                {{-- <th>No.</th> --}}
                                                {{-- <th>Site</th> --}}
                                                <th>URL</th>
                                                <th>Port</th>
                                                <th>Mode</th>
                                                <th>Type</th>
                                                <th>Last Feed</th>
                                                {{-- <th>Web Status</th>
                                                <th>DateTime</th> --}}
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- <tr>
                                                <td>
                                                    <label>
                                                        <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk" />
                                                        <span class="label-text"></span>
                                                    </label>
                                                </td>
                                                <td>1</td>
                                                <td>https://www.thairath.co.th/tags/Cyber%20Security</td>
                                                <td>03/01/2022 15:25:22</td>
                                                <td><span class="text-success">Online</span></td>
                                                <td>
                                                    <label class="switch">
                                                        <input type="hidden" value="FALSE" name="">
                                                        <input type="checkbox" name="status" value="TRUE" checked>
                                                        <span></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-info btn-xs">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                
                                                    <button type="button" class="btn btn-danger btn-xs">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <label>
                                                        <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk" />
                                                        <span class="label-text"></span>
                                                    </label>
                                                </td>
                                                <td>2</td>
                                                <td>https://www.blognone.com/topics/cybersecurity</td>
                                                <td>03/01/2022 15:24:29</td>
                                                <td><span class="text-success">Online</span></td>
                                                <td>
                                                    <label class="switch">
                                                        <input type="hidden" value="FALSE" name="">
                                                        <input type="checkbox" name="status" value="TRUE" checked>
                                                        <span></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-info btn-xs">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                
                                                    <button type="button" class="btn btn-danger btn-xs">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <label>
                                                        <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk" />
                                                        <span class="label-text"></span>
                                                    </label>
                                                </td>
                                                <td>3</td>
                                                <td>https://www.techtalkthai.com/tag/cybersecurity/</td>
                                                <td>03/01/2022 15:24:11</td>
                                                <td><span class="text-success">Online</span></td>
                                                <td>
                                                    <label class="switch">
                                                        <input type="hidden" value="FALSE" name="">
                                                        <input type="checkbox" name="status" value="TRUE" checked>
                                                        <span></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-info btn-xs">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                
                                                    <button type="button" class="btn btn-danger btn-xs">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr> --}}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </section>
                </section>
            </section>
       
    </section>
    {{-- modal fade fixed-left --}}
    {{-- <div class="modal in fixed-left in" id="wdfm_website" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"> --}}
    <div class="modal fixed-left in" id="wdfm_website" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal"
                        onclick="close_wdfm_website()">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();"
                            datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        <span id="title_head"> Add Website</span>
                    </h4>
                </div>
                {{-- <form action="" class="ajaxifyForm_custom"> --}}
                {!! Form::open(['route' => ['urlfeed.insert_url_feed'], 'class' => 'ajaxifyForm_custom', 'method' =>
                'POST']) !!}
                <input type="hidden" name="mode" id="mode" value="create">
                <div class="modal-body">
                    <div class="container-fluid">
                        {{-- <div id="site_id_show" class="form-group row">
                            <label class="col-lg-3 control-label"> Site <span class="text-danger">*</span> </label>
                            <div class="col-lg-9">
                                <select name="site_id" id="site_id" class="select2-option form-control">
                                    <option value="all" selected>All Site</option>
                                    @foreach ($SiteSettings as $data_SiteSetting)
                                    <option value="{{$data_SiteSetting->id}}">{{$data_SiteSetting->name}} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}

                        <div class="form-group row">
                            <label class="col-lg-3 control-label"> Name <span class="text-danger">*</span> </label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control" name="name_web" id="name_web" value="" required>
                            </div>
                        </div>
                        
                        <div class="form-group row">
                            <label class="col-lg-3 control-label"> Type <span class="text-danger">*</span> </label>
                            <div class="col-lg-9">
                                <select name="type_web" id="type_web" class="select2-option form-control">
                                    <option value="Public" selected>Public</option>
                                    <option value="DarkWeb">DarkWeb</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row d-none" id="div_header_create">
                            <label class="col-lg-3 control-label">Header/Cookies <span class="text-danger"></span> </label>
                            <div class="col-lg-9">
                                <textarea class="form-control htmleditor" id="header" name="header" data-id="1" style="display: none;"></textarea>
                                {{-- <textarea name="header" id="header" class="form-control note-codable" cols="30" rows="3"></textarea> --}}
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 control-label">URL <span class="text-danger">*</span> </label>
                            <div class="col-lg-9">
                                <input type="text" class="form-control" name="url_web" id="url_web" value="" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-lg-3 control-label"> Port <span class="text-danger">*</span> </label>
                            <div class="col-lg-9">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="port_web" id="port_web" value="80"
                                        required>
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-info" onclick="get_check_site()">Check</button>
                                    </span>
                                </div>
                            </div>
                        </div>
    
                        <div id="area_check_message_row" class="form-group row" style="display: none;">
                            <label class="col-lg-3 control-label"> </label>
                            <div class="col-lg-9">
                                <div id="area_check_message"></div>
                            </div>
                        </div>

                        <input type="hidden" name="site" id="site">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal"
                        onclick="close_wdfm_website()">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    {{-- disabled --}}
                    <button id="btn_save" type="submit" class="btn btn-info btn-rounded formSaving" disabled>
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
                {!! Form::close() !!}
                {{-- </form> --}}
            </div>
        </div>
    </div>

    <div class="modal fade fixed-left" id="edit_wdfm_website" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    </div>

    <div class="modal fade fixed-left" id="modal_data_leak_url" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        <span id="title_head"> New URL Feed</span>
                    </h4>
                </div>
                {{-- <form action="" class="ajaxifyForm_custom"> --}}
                {!! Form::open(['class' => '', 'method' => 'POST']) !!}
                <input type="hidden" name="mode" id="mode" value="create">
                <div class="modal-body">

                    <div class="form-group row">
                        <label class="col-lg-2 control-label">URL <span class="text-danger">*</span> </label>
                        <div class="col-lg-10">
                                <input type="text" class="form-control" name="url_web" id="url_web" value="" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-2 control-label">Status </label>
                        <div class="col-lg-10">
                            <label class="switch">
                                <input type="hidden" value="FALSE" name="">
                                <input type="checkbox" name="status" value="TRUE" checked>
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
                    <button id="btn_save" type="submit" class="btn btn-info btn-rounded formSaving">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

</section>

@push('pagestyle')
    @include('stacks.css.datatables')
    @include('stacks.css.datepicker')
    @include('stacks.css.form')
    @include('stacks.css.summernote')
    <link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css"/>
@endpush

@push('pagescript')
@include('stacks.js.markdown')
@include('scripts.summernote')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.menusub')
@include('stacks.js.site_hidesettings')
@include('stacks.js.advanced_search')
@include('stacks.js.fullscreen')
<script>
    {{-- $('#tbl_url_feed').DataTable(); --}}
    $('.select-2-keyword').select2();

    var tbl_url_feed;
    $(document).ready(function (){

        tbl_url_feed = $('#tbl_url_feed').DataTable({
            ajax: {
                url: "{{route('urlfeed.tbl_url_feed')}}",
                type: "POST",
            },
            columns: [
                {{-- { data: 'c_site' }, --}}
                { data: 'url' },
                { data: 'port' },
                { data: 'c_mode' },
                { data: 'c_type' },
                { data: 'feel_last' },
                { data: 'status' },
                { data: 'action' }
            ]
        });

    });

    var site_id = 'All';

    {{-- $('#site_id').change(function() {
        site_id = $('#site_id :selected').val();
    }); --}}

    function close_wdfm_website(){
        $('#site_id_show').show();
        $("#btn_save").prop("disabled",true);
        $('#title_head').text(" Add Website");
        $('#mode').val('create');
        $('#name_web').val("");
        $('#url_web').val("");
        $('#port_web').val("80");
        $('#url_web').prop('readonly', false);
        $('#port_web').prop('readonly', false);
        $('#hash').prop('checked', false);
        $('#file_size').prop('checked', false);
        $('#element').prop('checked', false);
        $('#blacklist').prop('checked', false);
        $('#blacklist_text').val("");
        $('#example-blacklist').hide();
        $('#delay_screen_shot').prop('checked', false);
        $('#delay_screenshot_val').val("");
        $('#delay_screen_shot_val_div').hide();
        $("#area_check_message").hide();
        $("#area_option").css("display","none");
        $('#site_id').val("").change();
        $(".review-image-capture").html("");
        $("#link_edit_image_screenshot").html("");
        $('.review_image_screenshot').css("display","none");

        $('#type_web').val('').trigger('change');
        $('#div_header_create').addClass('d-none');
        $('#header').empty();
        loading('stop_load');
    }

    $('#type_web').change(function(){

        let type_value = $('#type_web :selected').val();

        if(type_value == 'Public')
        {
            $('#div_header_create').addClass('d-none');
        }
        else
        {
            $('#div_header_create').removeClass('d-none');
        }

    });

    function get_check_site()
    {
        let url_web = $("#url_web").val();
        let port_web = $("#port_web").val();
        if(url_web && port_web) 
        {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{!! route('urlfeed.get_check_site') !!}',
                type: "post",
                data: ({
                    site_id:site_id,
                    url_web:url_web,
                    port_web:port_web
                }),
                beforeSend: function(){
                    f_loading(null, '#url_web');
                    f_loading(null, '#port_web');
                },
            }).done(function(data){
                f_loading_stop(null, '#url_web');
                f_loading_stop(null, '#port_web');
                var obj = JSON.parse(data);
                console.log(obj);
                var message = obj.message;

                if(obj.Result == 1) 
                {
                    console.log(55);
                    let DomainHeaders = JSON.stringify(obj.DomainHeaders);
                    let d_header = DomainHeaders;
                    let message_html = `
                        <div class="form-group row">
                            <div class="col-lg-12">
                                <div class="bg-success" style="display:inline-block;padding:5px;border-radius:5px;">
                                    <i class="fas fa-check-circle text-white fa-2x"></i> ${message}
                                </div>
                            </div>
                        </div>
                        <p>
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseExample" expanded="false" aria-expanded="false" aria-controls="collapseExample">
                                View header
                            </button>
                        </p>
                        <div class="collapse" id="collapseExample">
                            <div class="card card-body">
                                ${d_header}
                            </div>
                        </div>
                    `;

                    $("#area_check_message_row").css("display","block");
                    $(".area_image_screen").css("display","block");
                    $("#area_check_message").html(message_html);

                    $("#area_option").css("display","block");
                    $("#btn_save").prop("disabled",false);
                    
                } 
                else 
                {
                    console.log(44);
                    let message_html = `
                        <div class="form-group row">
                            <div class="col-lg-12">
                                <div style="width: 100%; background: #ffebe6;">
                                    <i class="fas fa-times"></i> ${message}
                                </div>
                            </div>
                        </div>
                    `;

                    $("#area_check_message_row").css("display","block");
                    $(".area_image_screen").css("display","none");
                    $("#area_check_message").html(message_html);
                    $("#area_option").css("display","none");
                    $("#btn_save").prop("disabled",true);
                }
                
            }).fail(function(jqXHR, ajaxOptions, thrownError){
                f_loading_stop(null, '#url_web');
                f_loading_stop(null, '#port_web');
                console.log("No response from server");
            });
        }
        else
        {
            toastr.error('Enter Url and Port.')
        }
    }

    var form_save = '.formSaving';
    $('.ajaxifyForm_custom').submit(function (event) {
        event.preventDefault();

        $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
        $('.formSaving').attr('disabled',true);
        var data = new FormData(this);
        data.append('channel', 'main_webdefacement');

        let webdefacment_setting_id = $("#webdefacment_setting_id").val();
        if(webdefacment_setting_id) {
            data.append('webdefacment_setting_id', webdefacment_setting_id);
        }

        if(form_save == '.formSavingAndRun'){
            data.append('formsubmit', 'formSavingAndRun');
        }else if(form_save == '.formPreview'){
            data.append('formsubmit', 'formPreview');
        }else if(form_save == '.formDraft'){
            data.append('formsubmit', 'formDraft');
        }
        axios.post($(this).attr("action"), data)
            .then(function (response) {
                    toastr.success(response.data.message, '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-check"></i> @langapp('save') </span>');
                    {{-- window.location.href = response.data.redirect; --}}
                    $('#wdfm_website').modal('toggle');
                    tbl_url_feed.ajax.reload();
        })
        .catch(function (error) {
            $('.formSaving').attr('disabled',false);
            if(error.response.exception){
                toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
                $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
            }else{
                var errors = error.response.errors;
                var errorsHtml= '';
                $.each( errors, function( key, value ) {
                    errorsHtml += '<li>' + value[0] + '</li>'; 
                });
                toastr.error( errorsHtml , '@langapp('response_status') ');
                $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
            }
        }); 
    });

    function update_url_feed()
    {
        {{-- let header_code = $('form#form_update_url_feed').find('div.note-editable').html(); --}}
        let header_code = $("#edit_header").summernote('code');

        var formData = new FormData(document.getElementById('form_update_url_feed'));
        formData.append('header_code', header_code);

        $.ajax({
            url: "{{ route('urlfeed.update_url_feed') }}",
            type: "POST",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
                $('.formSaving').attr('disabled',true);
            },
            success: function(response){
                
                $('.formSaving').attr('disabled',false);

                if(response.status == 'success')
                {
                    $(form_save).html('<i class="fas fa-check"></i> @langapp('save') </span>');
                    toastr.success(response.message);
                    $('#ajaxModal').modal('toggle');
                    tbl_url_feed.ajax.reload();
                    
                }
                else
                {
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                    toastr.error(response.data.message);
                }
            }
        });
    }

    function change_rss_active(code) {
        let checkState = $("#rss-active-" + code).is(":checked") ? 1 : 0;
        axios.post('{{route('urlfeed.change_status')}}', {
            active: checkState,
            code: code,
        }).then(function (response) {
            toastr.success(response.data.message);
            {{-- window.location.href = response.data.redirect; --}}
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
