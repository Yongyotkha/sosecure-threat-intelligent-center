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
                    <div class="slim-scroll" data-color="#333333" data-disable-fade-out="true" data-distance="0" data-height="auto" data-size="3px">
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
                    <button type="submit" id="btn-change-status" class="btn btn-sm btn-danger m-xs  pull-right" value="bulk-delete" disabled>
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt') @langapp('delete')</span>
                    </button>
                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="ajaxModal">
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
                            <div class="wdfm-container">
                                <div class="wdfm-inner">
                                    <div class="wdfm-card">
                                        <div class="wdfm-header">
                                            <div class="wdfm-img">
                                                <a href="" class="wdfm-btn-a">
                                                    <span>
                                                        <img src="https://firebasestorage.googleapis.com/v0/b/phish-ai-production.appspot.com/o/LYfzlRVdZPftsYKBQgKf0LkyP3z2%2Fscreenshot%2F92964b45-7858-4725-baf0-f16f5fd1bf89?alt=media&token=63c602fd-a364-4a9c-b89c-4ce09a88ab4a" alt="">
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="wdfm-body">
                                            <div class="wdfm-btn">
                                                {{-- <a class="btn btn-icon btn-default btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a> --}}
                                            </div>
                                            <h4>Targeted Brand: paypal</h4>
                                            <p class="mdfm-text-muted">https://limited-login-paypai.com/egg.php?secret_key=89k0rwa5zltyjg1f32oiqdv4nhems6</p>
                                        </div>
                                        <div class="wdfm-footer">
                                            <div class="wdfm-ft-left">
                                                <h4 class="wdfm-text-ft">Sun Dec 06 2020 22:21:15 GMT+0700 (Indochina Time)</h4>
                                            </div>
                                            <div class="wdfm-ft-right">
                                                Icon
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="wdfm-inner">
                                    <div class="wdfm-card">
                                        <div class="wdfm-header">
                                            <div class="wdfm-img">
                                                <a href="">
                                                    <img src="https://firebasestorage.googleapis.com/v0/b/phish-ai-production.appspot.com/o/LYfzlRVdZPftsYKBQgKf0LkyP3z2%2Fscreenshot%2F92964b45-7858-4725-baf0-f16f5fd1bf89?alt=media&token=63c602fd-a364-4a9c-b89c-4ce09a88ab4a" alt="">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="wdfm-body">
                                            <div class="wdfm-btn">
                                                {{-- <a class="btn btn-icon btn-default btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a> --}}
                                            </div>
                                            <h4>Targeted Brand: paypal</h4>
                                            <p class="mdfm-text-muted">https://limited-login-paypai.com/egg.php?secret_key=89k0rwa5zltyjg1f32oiqdv4nhems6</p>
                                        </div>
                                        <div class="wdfm-footer">
                                            <div class="wdfm-ft-left">
                                                <h4 class="wdfm-text-ft">Sun Dec 06 2020 22:21:15 GMT+0700 (Indochina Time)</h4>
                                            </div>
                                            <div class="wdfm-ft-right">
                                                Icon
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="wdfm-inner">
                                    <div class="wdfm-card">
                                        <div class="wdfm-header">
                                            <div class="wdfm-img">
                                                <a href="">
                                                    <img src="https://firebasestorage.googleapis.com/v0/b/phish-ai-production.appspot.com/o/LYfzlRVdZPftsYKBQgKf0LkyP3z2%2Fscreenshot%2F92964b45-7858-4725-baf0-f16f5fd1bf89?alt=media&token=63c602fd-a364-4a9c-b89c-4ce09a88ab4a" alt="">
                                                </a>
                                            </div>
                                        </div>
                                        <div class="wdfm-body">
                                            <div class="wdfm-btn">
                                                {{-- <a class="btn btn-icon btn-default btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a> --}}
                                            </div>
                                            <h4>Targeted Brand: paypal</h4>
                                            <p class="mdfm-text-muted">https://limited-login-paypai.com/egg.php?secret_key=89k0rwa5zltyjg1f32oiqdv4nhems6</p>
                                        </div>
                                        <div class="wdfm-footer">
                                            <div class="wdfm-ft-left">
                                                <h4 class="wdfm-text-ft">Sun Dec 06 2020 22:21:15 GMT+0700 (Indochina Time)</h4>
                                            </div>
                                            <div class="wdfm-ft-right">
                                                Icon
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </section>
            </section>
        </aside>
    </section>

    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>

</section>

@push('pagestyle')
    @include('stacks.css.datatables')
    @include('stacks.css.form')
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.menusub')
@include('stacks.js.hidesettings')

<script>
    $('.wdfm-inner').hover(function(){
        $(this).find('.wdfm-header').toggleClass('wdfm-header-upper');
    })
</script>
@endpush
@endsection