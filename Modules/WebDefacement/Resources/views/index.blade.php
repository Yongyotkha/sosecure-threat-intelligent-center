@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        {{-- Head --}}
        <header class="header panel-heading bg-white b-b b-light">
            <div class="bc-head"> @langapp('webdefacement')</div>    

            <button id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                <span>@langapp('Search_Advance')</span>
             </button>
             <div class="pull-right" style="margin-top: 8px; width: 300px;">
                <select name="site" id="site" class="select2-option form-control select-site" style="min-width: 300px">
                    <option value="">All Site</option>
                </select>
            </div>
        </header>

        <section class="scrollable wrapper">
            {{-- Search --}}
            <section class="panel panel-default"  id="hide-advance-search" style="display: none">
                <div class="container-fluid" style="padding: 2rem;">
                    <div class="row m-b-md">
                        <div class="col-lg-12">
                            <div class="row d-flex align-items-center">
                                <label for="" class="col-sm-1 col-xs-12 col-form-label">Keywords</label>
                                <div class="col-sm-11 col-xs-12">
                                    <input type="text" id="Keywords" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="row d-flex align-items-center">
                                <label for="" class="col-sm-3 col-xs-12 col-form-label">Status</label>
                                <div class="col-sm-9 col-xs-12">
                                    <select id="social" class="select2-option form-control">
                                        <option value="" >All</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 text-right mt-2">
                            <button type="button" id="btn_news_search" class="btn btn-info btn-responsive">
                                <i class="fas fa-search"></i>
                                Search
                            </button>
                            <button type="button" id="btn_news_reset" class="btn btn-default btn-responsive" style="white-space: nowrap">
                                <i class="fas fa-broom"></i>
                                <span> Clear </span>
                            </button>
                        </div>
                    </div>
                </div> 
            </section>

            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-xs-12">
                            <i class="fas fa-table"></i> Website
                        </div>
                    </div>
                </header>
                <div class="panel-body"  style="background: #f2f2f2;">
                    <div class="row">
                        <div class="col-12 text-right mb-2">
                            <div class="padding-3">
                                <a href="#" id="critical" class="btn-chart white d-il-flex mr-3">
                                    <span class="dot critical"></span>
                                    Critical
                                </a>
                                <a href="#" id="high" class="btn-chart white d-il-flex mr-3">
                                    <span class="dot high"></span>
                                    High
                                </a>
                                <a href="#" id="medium" class="btn-chart white d-il-flex mr-3">
                                    <span class="dot medium"></span>
                                    Meduim
                                </a>
                                <a href="#" id="low" class="btn-chart white d-il-flex mr-3">
                                    <span class="dot low"></span>
                                    Normal
                                </a>
                                <a href="#" id="none" class="btn-chart white d-il-flex">
                                    <span class="dot none"></span>
                                    None
                                </a>
                            </div>
                   
                        </div>
                    </div>

                    <div class="wdfm-container">
                        <div class="item-wdfm wdfm-inner">
                            <div class="wdfm-card">
                                <div class="wdfm-header">
                                    <div class="wdfm-img">
                                        <a href="{{route('webdefacement.detail')}}">
                                            <img src="https://firebasestorage.googleapis.com/v0/b/phish-ai-production.appspot.com/o/LYfzlRVdZPftsYKBQgKf0LkyP3z2%2Fscreenshot%2F92964b45-7858-4725-baf0-f16f5fd1bf89?alt=media&token=63c602fd-a364-4a9c-b89c-4ce09a88ab4a" alt="">
                                        </a>
                                    </div>
                                </div>
                                <div class="wdfm-body">
                                    <div class="wdfm-btn">
                                        <a href="{{route('webdefacement.detail')}}" class="btn btn-icon btn-default btn-sm" data-rel="tooltip" title="View" data-placement="bottom">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                    <h4>Targeted Brand: paypal</h4>
                                    <p class="mdfm-text-muted">https://limited-login-paypai.com/egg.php?secret_key=89k0rwa5zltyjg1f32oiqdv4nhems6</p>
                                </div>
                                <div class="wdfm-footer">
                                    <div class="wdfm-ft-left flex">
                                        <div>Site : ออมสิน</div>
                                        <div class="status-flex mr-2">Status : &nbsp;  <span class="dot low"></span> Normal</div>
                                    </div>
                                    <div class="wdfm-ft-right flex">
                                        <div>Last online: 10 second ago</div>
                                        <div>Last Check: 10 second ago</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="item-wdfm wdfm-inner">
                            <div class="wdfm-card">
                                <div class="wdfm-header">
                                    <div class="wdfm-img">
                                        <a href="{{route('webdefacement.detail')}}">
                                            <img src="https://firebasestorage.googleapis.com/v0/b/phish-ai-production.appspot.com/o/LYfzlRVdZPftsYKBQgKf0LkyP3z2%2Fscreenshot%2F92964b45-7858-4725-baf0-f16f5fd1bf89?alt=media&token=63c602fd-a364-4a9c-b89c-4ce09a88ab4a" alt="">
                                        </a>
                                    </div>
                                </div>
                                <div class="wdfm-body">
                                    <div class="wdfm-btn">
                                        <a href="{{route('webdefacement.detail')}}" class="btn btn-icon btn-default btn-sm" data-rel="tooltip" title="View" data-placement="bottom">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                    <h4>Targeted Brand: paypal</h4>
                                    <p class="mdfm-text-muted">https://limited-login-paypai.com/egg.php?secret_key=89k0rwa5zltyjg1f32oiqdv4nhems6</p>
                                </div>
                                <div class="wdfm-footer">
                                    <div class="wdfm-ft-left flex">
                                        <div>Site : ออมสิน</div>
                                        <div class="status-flex">Status : &nbsp; <span class="dot high"></span> High</div>
                                    </div>
                                    <div class="wdfm-ft-right flex">
                                        <div>Last online: 10 second ago</div>
                                        <div>Last Check: 10 second ago</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="item-wdfm wdfm-inner">
                            <div class="wdfm-card">
                                <div class="wdfm-header">
                                    <div class="wdfm-img">
                                        <a href="{{route('webdefacement.detail')}}">
                                            <img src="https://firebasestorage.googleapis.com/v0/b/phish-ai-production.appspot.com/o/LYfzlRVdZPftsYKBQgKf0LkyP3z2%2Fscreenshot%2F92964b45-7858-4725-baf0-f16f5fd1bf89?alt=media&token=63c602fd-a364-4a9c-b89c-4ce09a88ab4a" alt="">
                                        </a>
                                    </div>
                                </div>
                                <div class="wdfm-body">
                                    <div class="wdfm-btn">
                                        <a href="{{route('webdefacement.detail')}}" class="btn btn-icon btn-default btn-sm" data-rel="tooltip" title="View" data-placement="bottom">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                    <h4>Targeted Brand: paypal</h4>
                                    <p class="mdfm-text-muted">https://limited-login-paypai.com/egg.php?secret_key=89k0rwa5zltyjg1f32oiqdv4nhems6</p>
                                </div>
                                <div class="wdfm-footer">
                                    <div class="wdfm-ft-left flex">
                                        <div>Site : ออมสิน</div>
                                        <div class="status-flex">Status : &nbsp; <span class="dot critical"></span> Critical</div>
                                    </div>
                                    <div class="wdfm-ft-right flex">
                                        <div>Last online: 10 second ago</div>
                                        <div>Last Check: 10 second ago</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

          </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@push('pagestyle')
    @include('stacks.css.datatables')
    @include('stacks.css.form')

    @include('stacks.css.datepicker')
    @include('stacks.css.form')
    <link rel="stylesheet" href="{{ getAsset('plugins/daterangepicker/daterangepicker.css') }}" type="text/css"/>
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.datepicker')
@include('stacks.js.daterangpicker')

<script>
        $(document).ready(function(){
        $('.wdfm-card').hover(function(){
            $(this).find('.wdfm-header').addClass('wdfm-header-upper');
        }); 
        $('.wdfm-card').mouseleave(function(){
            $(this).find('.wdfm-header').removeClass('wdfm-header-upper');
        }); 
    });
</script>
@endpush
@endsection