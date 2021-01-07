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
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display:none">@icon('solid/bars')</a>
                    <div class="bc-head">Webdefacement > Website </div>
                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal" data-target="#wdfm_website">
                        @icon('solid/plus') @langapp('create')
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
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" class="text-white">&times;</span>
                    </button>
                    <h4 class="modal-title text-white" id="exampleModalLabel">Add Website</h4>
                </div>
                <form action="" class="">
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-lg-3 control-label"> Name <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                            <input type="text" class="form-control" name="name_web" id="name_web" value="">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-3 control-label">URL <span class="text-danger">*</span> </label>
                        <div class="col-lg-9">
                                <input type="text" class="form-control" name="url_web" id="url_web" value="">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-3 control-label"> Port <span class="text-danger">*</span> </label>
                        <div class="col-lg-6">
                            <div class="input-group">
                                <input type="text" class="form-control" name="port_web" id="port_web" value="80">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-info" id="btn_check_web">Check</button>  
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
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
                                Delay Screenshot <input type="text" value="2000"> milliseconds
                                {{-- <textarea name="blacklist_text" id="blacklist_text2" cols="10" rows="5" class="form-control"></textarea>
                                <strong style="margin-top: 10px">Example </strong> <span>hecker,hacker</span> --}}
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 control-label">&nbsp;</label>
                        <div class="col-lg-9">
                            <div class="review-image-capture">
                                <img src="https://firebasestorage.googleapis.com/v0/b/phish-ai-production.appspot.com/o/LYfzlRVdZPftsYKBQgKf0LkyP3z2%2Fscreenshot%2F92964b45-7858-4725-baf0-f16f5fd1bf89?alt=media&token=63c602fd-a364-4a9c-b89c-4ce09a88ab4a" id="preview-img-wdfm" >
                            </div>
                            <div class="edit-capture text-center">
                                <a href="{{route('webdefacement_website.edit_image')}}" target="_blank">
                                    Edit Image
                                </a>
                            </div>
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
@include('stacks.js.hidesettings')
@include('stacks.js.lightbox')
 

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
            }else{
                $('#delay_screen_shot_val_div').hide();
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


    $(function () {
        load_card();
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



</script>

@endpush
@endsection