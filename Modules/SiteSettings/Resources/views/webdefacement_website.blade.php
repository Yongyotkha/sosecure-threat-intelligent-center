@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">      
        <aside id="hide-settings" class="aside aside-md b-r">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#setting-nav">@icon('solid/bars')</a>
                        <a class="hide-setting btn btn-icon btn-default btn-sm pull-right m-r-xs">@icon('solid/bars')</a>
                        <p class="h3 text-elipse-setting" title="{{@$siteSettings->name ? 'site: '.$siteSettings->name: ''}}">{{@$siteSettings->name}}</p>
                </header>
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_site')
                    </section>
                </div>
                </section>
            </section>
        </aside>
        <aside>
            <section class="vbox">
    
                <header class="header panel-heading bg-white b-b b-light">
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display:none;">@icon('solid/bars')</a>
                    <div class="bc-head">Webdefacement > Website </div>
                    <a href="#" id="btn_md_create" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal" data-target="#wdfm_website">
                        @icon('solid/plus') @langapp('add')
                    </a>
                </header>
                <section class="scrollable wrapper">
                    <section class="panel panel-default">
                        <header class="panel-heading font-bold panel-header-blue">
                            <div class="row">
                                <div class="col-xs-12">
                                    <i class="fas fa-table"></i> Website
                                </div>
                            </div>
                        </header>
                        <div class="panel-body">
                            <div class="wdfm-container" id='data_card'>
                                {{-- <div class="item-wdfm wdfm-inner">
                                    <div class="wdfm-card">
                                        <div class="wdfm-header">
                                            <div class="wdfm-img">
                                                <a href="#">
                                                    <img src="https://firebasestorage.googleapis.com/v0/b/phish-ai-production.appspot.com/o/LYfzlRVdZPftsYKBQgKf0LkyP3z2%2Fscreenshot%2F92964b45-7858-4725-baf0-f16f5fd1bf89?alt=media&token=63c602fd-a364-4a9c-b89c-4ce09a88ab4a" alt="">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="wdfm-body">
                                            <div class="wdfm-btn">
                                                <a class="btn btn-icon btn-default btn-sm" data-rel="tooltip" title="View" data-placement="bottom">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                            <h4>Targeted Brand: paypal</h4>
                                            <p class="mdfm-text-muted">https://limited-login-paypai.com/egg.php?secret_key=89k0rwa5zltyjg1f32oiqdv4nhems6</p>
                                        </div>
                                        <div class="wdfm-footer start-top">
                                            <div class="wdfm-ft-left flex">
                                                <div>Site : ออมสิน</div>
                                                <div class="status-flex">Status : &nbsp; <span class="dot high"></span> High</div>
                                                <div>Hash xxxxxxx</div>
                                                <div>Filesize 12kb</div>
                                                <div>Element 522</div>
                                                <div>Image Screen 
                                                    <a href="https://firebasestorage.googleapis.com/v0/b/phish-ai-production.appspot.com/o/LYfzlRVdZPftsYKBQgKf0LkyP3z2%2Fscreenshot%2F92964b45-7858-4725-baf0-f16f5fd1bf89?alt=media&token=63c602fd-a364-4a9c-b89c-4ce09a88ab4a" data-lightbox="name-img-2" class="btn btn-info btn-xs">@icon('solid/eye')</a>
                                                </div>
                                            </div>
                                            <div class="wdfm-ft-right flex">
                                                <div>Last Online: 10 second ago</div>
                                                <div>Last Check: 10 second ago</div>
                                                <div>&nbsp;</div>
                                                <div>Last Update: 2021-01-01 11:11</div>
                                            </div>
                                        </div>
                                        <div class="wdfm-footer-action">
                                            <div>
                                                <strong>Update Original</strong>
                                            </div>
                                            <div class="flex-end">
                                                <a href="" class="btn btn-info btn-sm">@icon('solid/pen-alt') Edit</a>
                                                <a href="" class="btn btn-danger btn-sm">@icon('solid/trash-alt') Delete</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="item-wdfm wdfm-inner">
                                    <div class="wdfm-card">
                                        <div class="wdfm-header">
                                            <div class="wdfm-img">
                                                <a href="#">
                                                    <img src="https://firebasestorage.googleapis.com/v0/b/phish-ai-production.appspot.com/o/LYfzlRVdZPftsYKBQgKf0LkyP3z2%2Fscreenshot%2F92964b45-7858-4725-baf0-f16f5fd1bf89?alt=media&token=63c602fd-a364-4a9c-b89c-4ce09a88ab4a" alt="">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="wdfm-body">
                                            <div class="wdfm-btn">
                                                <a class="btn btn-icon btn-default btn-sm" data-rel="tooltip" title="View" data-placement="bottom">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                            <h4>Targeted Brand: paypal</h4>
                                            <p class="mdfm-text-muted">https://limited-login-paypai.com/egg.php?secret_key=89k0rwa5zltyjg1f32oiqdv4nhems6</p>
                                        </div>
                                        <div class="wdfm-footer start-top">
                                            <div class="wdfm-ft-left flex">
                                                <div>Site : ออมสิน</div>
                                                <div class="status-flex">Status : &nbsp; <span class="dot critical"></span> Critical</div>
                                                <div>Hash xxxxxxx</div>
                                                <div>Filesize 12kb</div>
                                                <div>Element 522</div>
                                                <div>Image Screen 
                                                    <a href="https://firebasestorage.googleapis.com/v0/b/phish-ai-production.appspot.com/o/LYfzlRVdZPftsYKBQgKf0LkyP3z2%2Fscreenshot%2F92964b45-7858-4725-baf0-f16f5fd1bf89?alt=media&token=63c602fd-a364-4a9c-b89c-4ce09a88ab4a" data-lightbox="name-img-2" class="btn btn-info btn-xs">@icon('solid/eye')</a>
                                                </div>
                                            </div>
                                            <div class="wdfm-ft-right flex">
                                                <div>Last Online: 10 second ago</div>
                                                <div>Last Check: 10 second ago</div>
                                                <div>&nbsp;</div>
                                                <div>Last Update: 2021-01-01 11:11</div>
                                            </div>
                                        </div>
                                        <div class="wdfm-footer-action">
                                            <div>
                                                <strong>Update Original</strong>
                                            </div>
                                            <div class="flex-end">
                                                <a href="" class="btn btn-info btn-sm">@icon('solid/pen-alt') Edit</a>
                                                <a href="" class="btn btn-danger btn-sm">@icon('solid/trash-alt') Delete</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="item-wdfm wdfm-inner">
                                    <div class="wdfm-card">
                                        <div class="wdfm-header">
                                            <div class="wdfm-img">
                                                <a href="#">
                                                    <img src="https://firebasestorage.googleapis.com/v0/b/phish-ai-production.appspot.com/o/LYfzlRVdZPftsYKBQgKf0LkyP3z2%2Fscreenshot%2F92964b45-7858-4725-baf0-f16f5fd1bf89?alt=media&token=63c602fd-a364-4a9c-b89c-4ce09a88ab4a" alt="">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="wdfm-body">
                                            <div class="wdfm-btn">
                                                <a class="btn btn-icon btn-default btn-sm" data-rel="tooltip" title="View" data-placement="bottom">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                            <h4>Targeted Brand: paypal</h4>
                                            <p class="mdfm-text-muted">https://limited-login-paypai.com/egg.php?secret_key=89k0rwa5zltyjg1f32oiqdv4nhems6</p>
                                        </div>
                                        <div class="wdfm-footer start-top">
                                            <div class="wdfm-ft-left flex">
                                                <div>Site : ออมสิน</div>
                                                <div class="status-flex">Status : &nbsp; <span class="dot low"></span> Normal</div>
                                                <div>Hash xxxxxxx</div>
                                                <div>Filesize 12kb</div>
                                                <div>Element 522</div>
                                                <div>Image Screen 
                                                    <a a href="https://firebasestorage.googleapis.com/v0/b/phish-ai-production.appspot.com/o/LYfzlRVdZPftsYKBQgKf0LkyP3z2%2Fscreenshot%2F92964b45-7858-4725-baf0-f16f5fd1bf89?alt=media&token=63c602fd-a364-4a9c-b89c-4ce09a88ab4a" data-lightbox="name-img-2" class="btn btn-info btn-xs">@icon('solid/eye')</a>
                                                </div>
                                            </div>
                                            <div class="wdfm-ft-right flex">
                                                <div>Last Online: 10 second ago</div>
                                                <div>Last Check: 10 second ago</div>
                                                <div>&nbsp;</div>
                                                <div>Last Update: 2021-01-01 11:11</div>
                                            </div>
                                        </div>
                                        <div class="wdfm-footer-action">
                                            <div>
                                                <strong>Update Original</strong>
                                            </div>
                                            <div class="flex-end">
                                                <a href="" class="btn btn-info btn-sm">@icon('solid/pen-alt') Edit</a>
                                                <a href="" class="btn btn-danger btn-sm">@icon('solid/trash-alt') Delete</a>
                                            </div>
                                        </div>
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                    </section>
                </section>
            </section>
        </aside>
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

    <div class="modal fade fixed-left" id="wdfm_website" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal" onclick="close_wdfm_website()">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        <span id="title_head"> Add Website</span>
                    </h4>
                </div>
                {{-- <form action="" class="ajaxifyForm_custom"> --}}
                {!! Form::open(['route' => ['webdefacement.create_data'], 'class' => 'ajaxifyForm_custom', 'method' => 'POST']) !!}
                <input type="hidden" name="mode" id="mode" value="create">
                <div class="modal-body">
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
                                <input type="text" class="form-control" name="port_web" id="port_web" value="80" required>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-info" onclick="get_check_site()">Check</button>  
                                </span>
                            </div>
                        </div>
                    </div>

                    <div id="area_check_message_row" class="form-group row" style="display: none;"><label class="col-lg-3 control-label"> </label>
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
                                <textarea name="blacklist_text" id="blacklist_text" cols="10" rows="5" class="form-control" placeholder="Ex: hacking,hacked,decript"></textarea>
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
                                Delay Screenshot <input type="text" name="delay_screenshot_val" id="delay_screenshot_val" value="2000"> milliseconds
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
                                {{-- <a href="{{route('webdefacement_website.edit_image',['site_id' => @$siteSettings->id])}}" target="_blank">
                                    Edit Image
                                </a> --}}
                            </div>
                        </div>
                    </div>



                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal" onclick="close_wdfm_website()">
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

    <input type="hidden" id="url_id">
    <input type="hidden" id="webdefacment_setting_id">

</section>

@push('pagestyle')
    @include('stacks.css.datatables')
    @include('stacks.css.form')
    @include('stacks.css.lightbox')
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('partial.ajaxify')
@include('stacks.js.menusub')
@include('stacks.js.site_hidesettings')
@include('stacks.js.lightbox')
@include('stacks.js.fullscreen')
 

<script>

    var site_id = '';
    
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
            }
        });
        
        $(document).ready(function(){
            load_card();
        {{--setInterval(function(){ 
            load_card(); 
        }, 1000);--}}
            $('.wdfm-card').hover(function(){
                $(this).find('.wdfm-header').addClass('wdfm-header-upper');
            }); 
            $('.wdfm-card').mouseleave(function(){
                $(this).find('.wdfm-header').removeClass('wdfm-header-upper');
            }); 
        });


    
    

    
    function load_card(){
        site_id = '{{$siteSettings->id}}';
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{!! route('webdefacement.load_card_by_site') !!}',
            type: "post",
            data: ({
                site_id:site_id
            }),
            datatype: "html",
            beforeSend: function(){
                {{--$('.ajax-loading').show();--}}
                {{--loading('load');--}}
                f_loading(null, '#data_card');
            },
        }).done(function(data){

            {{--$('.ajax-loading').hide();--}}
            {{--loading('stop_load');--}}
            f_loading_stop(null, '#data_card');
                $("#data_card").html(data.html);  

                $('.wdfm-card').hover(function(){
                    $(this).find('.wdfm-header').addClass('wdfm-header-upper');
                }); 
                $('.wdfm-card').mouseleave(function(){
                    $(this).find('.wdfm-header').removeClass('wdfm-header-upper');
                }); 

          
             
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            {{--$('.ajax-loading').hide();--}}
            {{--loading('stop_load');--}}
            f_loading_stop(null, '#data_card');
            console.log("No response from server");
        });
    }

    $("#btn_check_web").click(function() {
        get_check_site();
    });


    function get_check_site(){
        let url_web = $("#url_web").val();
        let port_web = $("#port_web").val();
        if(url_web && port_web) {

            site_id = '{{$siteSettings->id}}';
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

            site_id = '{{$siteSettings->id}}';
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
                        site_code = '{{$siteSettings->code}}';
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
                                            site_code = '{{$siteSettings->code}}';
                                            link_edit_image_screenshot_html = `
                                                    <a href="${base_url}/edit-image/${site_code}/${webdefacment_setting_id}" target="_blank">
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
            
            var data = new FormData(this);
            data.append('site_id', site_id);

            let webdefacment_setting_id = $("#webdefacment_setting_id").val();
            if(webdefacment_setting_id) {
                data.append('webdefacment_setting_id', webdefacment_setting_id);
            }
            data.append('site_id', site_id);

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
    
            site_id = '{{$siteSettings->id}}';
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
                $('#title_head').text("Edit Website");
                $("#webdefacment_setting_id").val(response.data.id);
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
        $('#title_head').text("Add Website");
        $('#mode').val('create');
        $('#name_web').val("");
        $('#url_web').val("");
        $('#port_web').val("");
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
        $("#area_check_message").empty();
    }



    function btn_click_del_webdefacement(id) {
     
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            heightAuto: false,
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log(id);
                $.ajax({
                    type:"POST",
                    url:"{{ route('webdefacement.delete_websefacement_process') }}",
                    data:{id: id},
                    beforeSend: function(){
                        loading('load');
                    },
                    success:function(response) {
                        loading('stop_load');
                        toastr.success(response.message, '@langapp('response_status')');
                        load_card();
                        {{--window.location.href = response.redirect;--}}
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
        })
     }



</script>

@endpush
@endsection
