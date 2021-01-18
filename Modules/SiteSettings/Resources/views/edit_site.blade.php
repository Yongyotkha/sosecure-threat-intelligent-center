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
                        <p class="h3 text-elipse-setting">Name Domain</p>
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

                <header class="header bg-white b-b clearfix">
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display:none">@icon('solid/bars')</a>
                    <div class="bc-head">Site Setting  &gt; {{ $siteSettings->name }}</div>
                </header>
                <section class="scrollable wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            {!! Form::open(['route' => ['sitesettings.update.settings', $siteSettings->code], 'class' => 'bs-example form-horizontal ajaxifyForm validator', 'novalidate' => '', 'method' => 'PUT', 'files' => true]) !!}
                            <section class="panel panel-default">
                                
                            <header class="panel-heading font-bold panel-header-blue">@icon('solid/cogs') Site Details  </header>
                            <input type="hidden" name="page_setting" value="site_settings">
                            <div class="panel-body">
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Logo </label>
                                    <div class="col-lg-6">
                                        <div class="">
                                            <input id="file-input-demo" type="file" class="form-control" name="logo">
                                            <span>Remark Upload File Extension (.png .jpg) <span class="text-danger">Max Size 2MB</span></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div id="preview-image-logo"></div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Site Name <span class="text-danger">*</span> </label>
                                    <div class="col-lg-6">
                                        <div class="">
                                            <input type="text" class="form-control" name="name" value="{{$siteSettings->name}}">
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Site Description</label>
                                    <div class="col-lg-6">
                                        <div class="">
                                            <textarea id="descript" name="descript" rows="4" class="form-control">{{$siteSettings->descript}}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Site Address</label>
                                    <div class="col-lg-6">
                                        <div class="">
                                            <textarea id="address" name="address" rows="4" class="form-control">{{$siteSettings->address}}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="" class="col-lg-3 control-label">Categorys</label>
                                    <div class="col-lg-6">
                                        <select name="category[]" id="categorys" class="select2-option form-control" multiple="multiple">
                                            @foreach($categories as $key => $category)
                                                <option value="{{ $category->id  }}"
                                                    @foreach($siteSettings->get_categorys as $siteCategory)
                                                        {{ $siteCategory->category_id === $category -> id ? 'selected' : ''}}
                                                    @endforeach
                                                >
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="" class="col-lg-3 control-label">Tags</label>
                                    <div class="col-lg-6">
                                        <select name="tag[]" id="tags" class="select2-option form-control" multiple="multiple">
                                            @foreach($tags as $key => $tag)
                                                <option value="{{ $tag->id  }}"
                                                    @foreach($siteSettings->get_tags as $site_tag)
                                                        {{ $site_tag->tag_id === $tag->id ? 'selected' : ''}}
                                                    @endforeach
                                                >
                                                    {{ $tag->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                {{-- <div class="form-group row">
                                    <label class="col-lg-3 control-label">Remark </label>
                                    <div class="col-lg-9">
                                        <div class="">
                                            <textarea id="remark" name="remark" rows="4" class="form-control">{{$siteSettings->remark}}</textarea>
                                        </div>
                                    </div>
                                </div> --}}
                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Status </label>
                                    <div class="col-lg-6">
                                        <label class="switch">
                                            <input type="checkbox" name="active" value="1" {{$siteSettings->active == 1 ? 'checked' : ''}}>
                                            <span></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-footer">
                                {!! closeModalButton() !!}
                                {!! renderAjaxButton() !!}
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </section>
            </section>
        </aside>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen, open" data-target="#nav,html">

    </a>


{{-- Modal Crop Logo --}}
<div class="modal in fixed-left" id="upload_image_logo_modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-aside" role="document">
        <div class="modal-content">
            <div class="modal-header bg-blue">
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                <h4 class="modal-title text-white">
                    <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                    ปรับขนาดโลโก้
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <img id="crop-img-logo" src="https://images.unsplash.com/photo-1593642634627-6fdaf35209f4?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1050&q=80" alt="Picture" style="width: 100%">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mx-auto" style="padding:5%;">
                        <h1 class="text-center">ตัวอย่างโลโก้</h1>
                        <div class="preview_logo" style="width:200px;height:150px;overflow:hidden;margin:0 auto;border:1px solid #eee"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success upload-image-logo" data-dismiss="modal">Upload</button>
            </div>
         </div>
    </div>
</div>

</section>


@push('pagestyle')
@include('stacks.css.form')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.select2checkbox')
@include('partial.ajaxify')
@include('stacks.js.menusub')
@include('stacks.js.site_hidesettings')
@include('stacks.js.fullscreen')
@include('stacks.js.cropple')
{{-- var cropper_logo;
    var imgs_logo = null;
    window.addEventListener('DOMContentLoaded', function () {
        var image = document.getElementById('crop-img-logo');
        var input_logo = $('#file-input');
        var cropBoxData;
        var canvasData;
        var $modal = $('#upload_image_logo_modal');
        var result = document.getElementById('result');

            input_logo.change(function(event) {
                var files = event.target.files;
                var done = function(url){
                    image.src = url;
                    $modal.modal('show');
                };

                if (files && files.length > 0)
                {
                    reader = new FileReader();
                    reader.onload = function(event)
                    {
                        done(reader.result);
                    };
                    reader.readAsDataURL(files[0]);
                }
            });

            $modal.on('shown.bs.modal', function () {
                cropper_logo = new Cropper(image, {
                    dragMode: 'move',
                    aspectRatio: 16 / 9,
                    restore: false,
                    guides: false,
                    center: false,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                    preview:'.preview_logo',
                    ready: function () {
                        cropper_logo.setCropBoxData(cropBoxData).setCanvasData(canvasData);
                    },
                });
            }).on('hidden.bs.modal', function () {
                cropBoxData = cropper_logo.getCropBoxData();
                canvasData = cropper_logo.getCanvasData();
                cropper_logo.destroy();
            });
        });

        $('.upload-image-logo').on('click', function (ev) {
            canvas = cropper_logo.getCroppedCanvas({
                width: 200,
                height: 150,
            }).toDataURL();
            imgs_logo = canvas;
            html = '<img src="' + imgs_logo + '" />';
            $("#preview_cer_img").html(html);
            $("#preview-image_logo").html("");
        }); --}}
<script>

    $(document).ready(function(){
        $("#tags").select2({
        tags: true
        });
    });
     </script>
@endpush

@endsection
