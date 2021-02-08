@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        {{-- Head --}}

        <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
            <div class="header-flex-overflow m-t-10">
                <div class="fwb-16">
                    <span>
                        @langapp('webdefacement')
                    </span>
                </div>

                <div class="ml-2 text-right">
                    <div class="text-left" style="min-width:270px;display:inline-block;">
                        <select name="site" id="site" class="select2-option form-control select-site" style="min-width: 270px">
                            <option value="">All Site</option>
                            @if($SiteSettings)
                            @foreach($SiteSettings as $SiteSettings_val)
                            <option value="{{$SiteSettings_val->id}}">{{$SiteSettings_val->name}}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>

                    <button id="advance-search" href="#hide-advance-search"
                        class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                        <span><i class="fas fa-filter"></i> @langapp('Search_Advance')</span>
                    </button>

                    @if(@get_role_custom()['superadmin'] == 1 || @get_role_custom()['client'] == 1)
                    <a href="#" id="btn_md_create"
                        class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal"
                        data-target="#wdfm_website">
                        @icon('solid/plus') @langapp('add')
                    </a>
                    @endif
                </div>
            </div>
        </header>



        <section class="scrollable wrapper">
            {{-- Search --}}
            <section class="panel panel-default" id="hide-advance-search" style="display: none">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-md-12">
                            <i class="fas fa-filter"></i> Filter
                        </div>
                </header>
                <div class="panel-body" style="padding: 0 !important">
                    <div class="container-fluid" style="padding: 2rem;">
                        <div class="row">
                            <div class="col-lg-4 mb-1">
                                <h5 class="font-weight-bold">Name & URL</h5>
                                <input type="text" id="keywords" class="form-control">
                            </div>

                            
                            <div class="col-lg-8 mb-1">
                                <h5 class="font-weight-bold">CVSS</h5>
                                <div id="btngroup_status"  class="btn-group special mb-2">
                                    <button class="btn btn-grey active">
                                        <span> All </span>
                                    </button>
                                    <button class="btn btn-grey">
                                        <span> High </span>
                                    </button>
                                    <button class="btn btn-grey">
                                        <span> Medium </span>
                                    </button>
                                    <button class="btn btn-grey">
                                        <span> Normal </span>
                                    </button>
                                </div>

                                {{-- <h5 class="font-weight-bold">Status</h5>
                                <a href="#" id="all" class="btn-chart d-il-flex mr-3">
                                    <span class="dot-all" style="height:8px;"></span>
                                    All
                                </a>
                                <a href="#" id="high" class="btn-chart d-il-flex mr-3">
                                    <span class="dot critical"></span>
                                    High
                                </a>
                                <a href="#" id="medium" class="btn-chart d-il-flex mr-3">
                                    <span class="dot high"></span>
                                    Medium
                                </a>
                                <a href="#" id="normal" class="btn-chart d-il-flex">
                                    <span class="dot low"></span>
                                    Normal
                                </a> --}}
                            </div>

                            {{-- <div class="col-lg-4">
                                <div class="form-group">
                                    <div class="row d-flex align-items-center">
                                        <label for="" class="col-sm-3 col-xs-12 col-form-label">Status</label>
                                        <div class="col-sm-9 col-xs-12">
                                            <select id="status" class="select2-option form-control"  multiple="multiple">
                                                <option value="critical">Critical</option>
                                                <option value="high">High</option>
                                                <option value="meduim">Meduim</option>
                                                <option value="normal">Normal</option>
                                                <option value="none">None</option>
                                            </select>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}

                            <!--
                            <div class="col-lg-12">
                                <div class="form-group row">
                                    <label for="" class="col-sm-3 col-xs-12 col-form-label">Status</label>
                                    <div class="col-sm-9 col-xs-12">
                                        <select name="" id="datatype" class="select2-option form-control"
                                            multiple="multiple">
                                            <option value="High">High</option>
                                            <option value="Medium">Medium</option>
                                            <option value="Normal">Normal</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            -->

                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-lg-12 text-right">
                            <button type="button" id="btn_news_search" class="btn btn-info btn-responsive btn-fz-13"
                                onclick="search()">
                                <i class="fas fa-search"></i>
                                @langapp('apply')
                            </button>
                            <button type="button" id="btn_news_reset" class="btn btn-default btn-responsive btn-fz-13"
                                style="white-space: nowrap" onclick="clear_search()">
                                <i class="fas fa-broom"></i>
                                <span> Clear </span>
                            </button>
                            <button type="button" id="close_filter" class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                <i class="fas fa-times"></i>
                                <span> Close </span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-lg-12 col-sm-12">
                            <i class="fas fa-table"></i> Website
                        </div>
                    </div>
                </header>
                <div class="panel-body" style="background: #f2f2f2;">
                    <div class="wdfm-container" id='data_card'></div>
                </div>
            </section>

        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

    <div class="modal fade fixed-left" id="wdfm_website" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
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
                {!! Form::open(['route' => ['webdefacement.create_data'], 'class' => 'ajaxifyForm_custom', 'method' =>
                'POST']) !!}
                <input type="hidden" name="mode" id="mode" value="create">
                <div class="modal-body">
                    <div id="site_id_show" class="form-group row">
                        <label class="col-lg-3 control-label"> Site <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                            <select name="site_id" id="site_id" class="select2-option form-control" onchange="get_site(this)">
                                <option value="">Select</option>
                                @foreach ($SiteSettings_add as $SiteSetting)
                                <option value="{{$SiteSetting->id}}">{{$SiteSetting->name}} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-3 control-label"> Name <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                            <input type="text" class="form-control" name="name_web" id="name_web" value="" required>
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

                    <div id="area_check_message_row" class="form-group row" style="display: none;"><label
                            class="col-lg-3 control-label"> </label>
                        <div class="col-lg-9">
                            <div id="area_check_message"></div>
                        </div>
                    </div>

                    <div id="area_option" class="form-group row" style="display: none;">
                        <label class="col-lg-3 control-label">Options</label>
                        <div class="col-sm-9">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="hash" id="hash" value="true">
                                    <span class="label-text">
                                        Hash
                                    </span>
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="file_size" id="file_size" value="true">
                                    <span class="label-text">
                                        Filesize
                                    </span>
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="element" id="element" value="true">
                                    <span class="label-text">
                                        Element
                                    </span>
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="blacklist" id="blacklist" value="true">
                                    <span class="label-text">
                                        Blacklist Keyword
                                    </span>
                                </label>
                            </div>
                            <div id="example-blacklist" style="display: none">
                                <textarea name="blacklist_text" id="blacklist_text" cols="10" rows="5"
                                    class="form-control" placeholder="Ex: hacking,hacked,decript"></textarea>
                                <strong style="margin-top: 10px">Example </strong> <span>hecker,hacker</span>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="delay_screen_shot" id="delay_screen_shot" value="true">
                                    <span class="label-text">
                                        Check image
                                        {{-- Delay Screenshot --}}
                                    </span>
                                </label>
                            </div>
                            <div id="delay_screen_shot_val_div" style="display: none">
                                Delay Screenshot <input type="text" name="delay_screenshot_val"
                                    id="delay_screenshot_val" value="2000"> milliseconds
                                <button type="button" class="btn btn-info" id="btn_screenshot">screen shot</button>
                                {{-- <textarea name="blacklist_text" id="blacklist_text2" cols="10" rows="5" class="form-control"></textarea>
                                <strong style="margin-top: 10px">Example </strong> <span>hecker,hacker</span> --}}
                            </div>
                        </div>
                    </div>

                    <div class="form-group row area_image_screen" style="display: none;">
                        <label class="col-lg-3 control-label">&nbsp;</label>
                        <div class="col-lg-9 review_image_screenshot" style="display: none;">
                            <div class="review-image-capture">
                                {{-- <img src="https://firebasestorage.googleapis.com/v0/b/phish-ai-production.appspot.com/o/LYfzlRVdZPftsYKBQgKf0LkyP3z2%2Fscreenshot%2F92964b45-7858-4725-baf0-f16f5fd1bf89?alt=media&token=63c602fd-a364-4a9c-b89c-4ce09a88ab4a" id="preview-img-wdfm" > --}}

                            </div>
                            <div id="link_edit_image_screenshot" class="edit-capture text-center">
                                {{-- <a href="{{route('webdefacement_website.edit_image',['site_id' => @$siteSettings->id])}}"
                                target="_blank">
                                Edit Image
                                </a> --}}
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="site" id="site">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal"
                        onclick="close_wdfm_website()">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
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

    <div class="modal" id="delete_web_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
        aria-hidden="true" style="left: unset">
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
                    <button type="button" class="btn btn-info submit btn-rounded delete_web_submit"
                        onclick="delete_web_select_confirm()"><i class="fas fa-paper-plane"></i> OK</button>
                </div>
            </div>
        </div>
    </div>

</section>
<input type="hidden" id="url_id">
<input type="hidden" id="webdefacment_setting_id">

@push('pagestyle')
@include('stacks.css.datatables')
@include('stacks.css.form')

@include('stacks.css.datepicker')
@include('stacks.css.form')
<link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css" />
@include('stacks.css.lightbox')
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')
@include('stacks.js.lightbox')
@include('stacks.js.advanced_search')
@include('stacks.js.activebutton')

<script>

    active_btn('#btngroup_status .btn');

    var keywords = null;
    var site = null;
    var datatype = null;
    var level = null;
    var search_ = 0;
    var site_id = 0;
    var site_code = null;


    $('#example-blacklist').hide();
    $('input[type="checkbox"]').on('change',function(){
        if($('#blacklist').prop('checked')){
            $('#example-blacklist').show();
        }else{
            $('#example-blacklist').hide();
        }
    });

    $('#delay_screen_shot_val_div').hide();
    $('input[type="checkbox"]').on('change',function(){
        if($('#delay_screen_shot').prop('checked')){
            $('#delay_screen_shot_val_div').show();
            $('.review_image_screenshot').css("display","block");
        }else{
            $('#delay_screen_shot_val_div').hide();
            $('.review_image_screenshot').css("display","none");
            $(".review-image-capture").html("");
            $("#link_edit_image_screenshot").html("");
        }
        });

    $(document).ready(function(){
        $('.wdfm-card').hover(function(){
            $(this).find('.wdfm-header').addClass('wdfm-header-upper');
        }); 
        $('.wdfm-card').mouseleave(function(){
            $(this).find('.wdfm-header').removeClass('wdfm-header-upper');
        }); 
    });

    var codeSite = null;
    function get_site(selectObject) {
        $.ajax({
            type:"POST",
            url:"{{ route('webdefacement.get_code_site') }}",
            data:{
                id: selectObject.value,

            },
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
                loading('stop_load');
                
                codeSite = response.site_code;
   
            },
            error: function (error){
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status') ');
            }
        });
    }



    $(function () {
        load_card();
    });
    

    
    function load_card(search_){
        console.log(level);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{!! route('webdefacement.load_card') !!}',
            type: "post",
            data: ({

                search_: search_,
                keywords: keywords,
                datatype: datatype,
                site: site,
                level: level,
                              
            }),
            datatype: "html",
            beforeSend: function(){
                f_loading(null, '#data_card');
            },
        }).done(function(data){
            f_loading_stop(null, '#data_card');
                $("#data_card").html(data.html);  

                $('.wdfm-card').hover(function(){
                    $(this).find('.wdfm-header').addClass('wdfm-header-upper');
                }); 
                $('.wdfm-card').mouseleave(function(){
                    $(this).find('.wdfm-header').removeClass('wdfm-header-upper');
                }); 

          
             
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            $('.ajax-loading').hide();
            console.log("No response from server");
        });
    }

    $("#site").change(function() {
        site = this.value;        
        load_card(search_);

    });

    function search (level_=null) {

        search_ = 1;
        keywords = $('#keywords').val();
        datatype = $('#datatype').val(); 
        level = level_;
       
        
        load_card(search_);

    }

    function clear_search () {

    search_ = 0;
    datatype = null;
    site = null;
    keywords = null;
    level = null;
    site = null;
    $('#keywords').val('');
    $('#datatype').val('').trigger('change');
    $('#site').val('').trigger('change');


    {{--load_card(search_);--}}

}


    $("#high").click(function() { 
        search ('High');
    });

    $("#normal").click(function() {
        search ('Normal');
    });

    $("#medium").click(function() {
        search ('Medium');
    });

    $("#all").click(function() {
        search (null);
    });



    $("#btn_check_web").click(function() {
        get_check_site();
    });


    function get_check_site(){
        let url_web = $("#url_web").val();
        let port_web = $("#port_web").val();
        if(url_web && port_web) {

            
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{!! route('webdefacement.get_check_site') !!}',
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
                    {{--$("#data_card").html(data.html);  
                    $('.wdfm-card').hover(function(){
                        $(this).find('.wdfm-header').addClass('wdfm-header-upper');
                    }); 
                    $('.wdfm-card').mouseleave(function(){
                        $(this).find('.wdfm-header').removeClass('wdfm-header-upper');
                    }); --}}

                    if(obj.Result == 1) {
                        console.log(55);
                        let DomainHeaders = JSON.stringify(obj.DomainHeaders);
                        let d_header = DomainHeaders;
                        let message_html = `<div class="form-group row">
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
                        
                    } else {
                        console.log(44);
                        let message_html = `<div class="form-group row">
                                                <div class="col-lg-12">
                                                    <div style="width: 100%; background: #ffebe6;">
                                                        <i class="fas fa-times"></i> ${message}
                                                    </div>
                                                </div>
                                            </div>`;
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
    }

    $("#btn_screenshot").click(function() {
        get_check_image_screenshot();
    });

    function get_check_image_screenshot(){
        let url_web = $("#url_web").val();
        let port_web = $("#port_web").val();
        let delay_screenshot_val = $("#delay_screenshot_val").val();

        var url_id = $("#url_id").val();
        if(!url_id) {
            url_id = 0;
        }
        if(url_web && port_web) {
            if(!delay_screenshot_val) {
                delay_screenshot_val = 0;
            }

            
            let webdefacment_setting_id = $("#webdefacment_setting_id").val();


            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{!! route('webdefacement.get_check_image_screenshot') !!}',
                type: "post",
                data: ({
                    site_id:site_id,
                    url_web:url_web,
                    port_web:port_web,
                    delay_screenshot_val:delay_screenshot_val,
                    url_id:url_id,
                    webdefacment_setting_id:webdefacment_setting_id
                }),
                beforeSend: function(){
                    f_loading(null, '.review_image_screenshot');
                },
            }).done(function(data){
                f_loading_stop(null, '.review_image_screenshot');
                var obj = JSON.parse(data);
                console.log(obj);

                    if(obj.Result == 1) {
                        console.log(55);
                        var url_id_receive = obj.url_id;
                        if(url_id_receive) {
                            $("#url_id").val(url_id_receive);
                        }
                        
                        var image_screenshot = obj.image_url;
                        var part_image = obj.image_path_original;

                        var image_screenshot_html = `<img src="${base_url}${image_screenshot}" id="preview-img-wdfm">`;

                        let webdefacment_setting_id = $("#webdefacment_setting_id").val();
                        let link_edit_image_screenshot_html = `
                                <a href="${base_url}/edit-image/${site_code}/${webdefacment_setting_id}" target="_blank">
                                    Edit Image
                                </a>`;


                        {{--$(".review-image-capture").html(image_screenshot_html);
                        $("#link_edit_image_screenshot").html(link_edit_image_screenshot_html);--}}


               
                            {{--start ajax --}}
                                $.ajax({
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    url: '{!! route('webdefacement.get_update_image_screenshot') !!}',
                                    type: "post",
                                    data: ({
                                        webdefacment_setting_id:webdefacment_setting_id,
                                        image:image_screenshot,
                                        part_image:part_image
                                    }),
                                    beforeSend: function(){
                                        f_loading(null, '.review_image_screenshot');
                                    },
                                }).done(function(obj){
                                    f_loading_stop(null, '.review_image_screenshot');
                                    console.log(obj);

                                        if(obj.status_code == 200) {
                                            console.log(200);
                           
                                            image_screenshot = obj.data.image;
                                            image_screenshot_html = `<img src="${base_url}${image_screenshot}" id="preview-img-wdfm">`;

                                            webdefacment_setting_id = $("#webdefacment_setting_id").val();
                                            link_edit_image_screenshot_html = `
                                                    <a href="${base_url}/edit-image/`+codeSite+`/${webdefacment_setting_id}" target="_blank">
                                                        Edit Image
                                                    </a>`;


                                            $(".review-image-capture").html(image_screenshot_html);
                                            $("#link_edit_image_screenshot").html(link_edit_image_screenshot_html);


                                            

                                            
                                        } else {
                                            console.log(404);
                                            let message_html = ``;
                                            $("#area_check_message").html(message_html);
                                        }


                                }).fail(function(jqXHR, ajaxOptions, thrownError){
                                    f_loading_stop(null, '#review_image_screenshot');
                                    console.log("No response from server");
                                });
                            {{--end ajax --}}
                        
                        
                    } else {
                        console.log(44);
                        let message_html = ``;
                        $("#area_check_message").html(message_html);
                    }


            }).fail(function(jqXHR, ajaxOptions, thrownError){
                f_loading_stop(null, '#review_image_screenshot');
                console.log("No response from server");
            });
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
                        window.location.href = response.data.redirect;
            })
            .catch(function (error) {
                $('.formSaving').attr('disabled',false);
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
    });



    $("#btn_md_create").click(function() {
        get_create_open_md_site_url();
    });

    function get_create_open_md_site_url(){
    
            
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{!! route('webdefacement.get_create_open_md_site_url') !!}',
                type: "post",
                data: ({
                    site_id:site_id
          
                }),
                beforeSend: function(){
                    loading('load');
                },
            }).done(function(obj){
                    loading('stop_load');
                    console.log(obj);

                    if(obj.status_code == 200) {
                        console.log(200);

                        if(obj.data) {
                            let webdefacment_setting_id = obj.data.id;
                            if(webdefacment_setting_id) {
                                $("#webdefacment_setting_id").val(webdefacment_setting_id);
                            }
                        }

                    } else {
                        console.log(404);
                        
                    }


            }).fail(function(jqXHR, ajaxOptions, thrownError){
                loading('stop_load');
                console.log("No response from server");
            });
        
    }

    function btn_click_edit_webdefacement(id){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '/WebDefacement-website/edit_data/' + id,
            type: "get",
            beforeSend: function(){
                loading('load');
            },
        }).done(function(response){
            loading('stop_load');
            if(response.status_code == 200) {
                site_id = response.data.site_id;
                site_code = response.data.site_code;
                $('#site_id_show').hide();
                $('#title_head').text(" Edit Website");
                $("#webdefacment_setting_id").val(response.data.id);
                
                $("input[name='site']").val(site_id);
                $('#mode').val('update');
                $("#btn_save").prop("disabled",false);
                $('#name_web').val(response.data.name);
                $('#url_web').val(response.data.url);
                $('#port_web').val(response.data.port);
                $('#url_web').prop('readonly', true);
                $('#port_web').prop('readonly', true);
                if(response.data.hash == 1){
                    $('#hash').prop('checked', true);
                }else{
                    $('#hash').prop('checked', false);
                }
                if(response.data.filesize == 1){
                    $('#file_size').prop('checked', true);
                }else{
                    $('#file_size').prop('checked', false);
                }
                if(response.data.element == 1){
                    $('#element').prop('checked', true);
                }else{
                    $('#element').prop('checked', false);
                }
                if(response.data.blacklist_keyword == 1){
                    $('#blacklist').prop('checked', true);
                    $('#blacklist_text').val(response.data.blacklist_keyword_content);
                    $('#example-blacklist').show();
                }else{
                    $('#blacklist').prop('checked', false);
                }
                if(response.data.image_check == 1){
                    $('#delay_screen_shot').prop('checked', true);
                    $('#delay_screenshot_val').val(response.data.delay_screen_shot_val);
                    $('#delay_screen_shot_val_div').show();
                    $('.review_image_screenshot').css("display","block");
                    
                    let image_screenshot = response.data.image;
                    let image_screenshot_html = `<img src="${base_url}${image_screenshot}" id="preview-img-wdfm">`;
                    let link_edit_image_screenshot_html = `
                            <a href="${base_url}/edit-image/${response.data.site_code}/${response.data.id}" target="_blank">
                                Edit Image
                            </a>`;
                    $(".review-image-capture").html(image_screenshot_html);
                    $("#link_edit_image_screenshot").html(link_edit_image_screenshot_html);
                }else{
                    $('#delay_screen_shot').prop('checked', false);
                }
                $('#wdfm_website').modal('show');
            } else {
                console.log(404);
            }
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            loading('stop_load');
            console.log("No response from server");
        });
    }

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
        loading('stop_load');
    }

    function btn_click_del_webdefacement(id) {
        web_id=id;
        $('#delete_web_modal').modal('show');
    }

    function delete_web_select_confirm(){


        $.ajax({
            type:"POST",
            url:"{{ route('webdefacement.delete_websefacement_process') }}",
            data:{id: web_id},
            beforeSend: function(){
                $('.delete_web_submit').html('Processing..<i class="fas fa-spin fa-spinner"></i>');
                $('.delete_web_submit').prop("disabled", true);
            },
            success:function(response) {
                
                $('.delete_web_submit').html('<i class="fas fa-check"></i> @langapp('save') </span>');
                toastr.success(response.message, '@langapp('response_status')');
                $('#delete_web_modal').modal('hide');
                clear_search();
                $('.delete_web_submit').prop("disabled", false);    
                {{--window.location.href = response.redirect;--}}
            },
            error: function (error){
                $('.delete_web_submit').prop("disabled", false);
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
  


</script>
@endpush
@endsection