@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        {{-- Head --}}

        <header class="header panel-heading bg-white b-b b-light bar-header-overflow">
            <div class="header-flex-overflow m-t-10">
                <div class="fwb-16">
                    <span>
                        Monitoring > Dashboard
                    </span>
                </div>

                <div class="ml-2 text-right">
                    {{-- <div class="text-left max-w-select" style="display:inline-block;">
                        <select name="site" id="site" class="select2-option form-control select-site">
                            <option value="">All Site</option>
                            @if($SiteSettings)
                            @foreach($SiteSettings as $SiteSettings_val)
                            <option value="{{$SiteSettings_val->id}}">{{$SiteSettings_val->name}}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>

                    <a href="#hide-advance-search" id="advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} m-l-xs">
                        <span data-rel="tooltip" title="Filter" data-placement="bottom"><i class="fas fa-filter"></i><span class="hide-text">@langapp('Search_Advance')</span></span>
                    </a>

                    @if(@get_role_custom()['superadmin'] == 1 || @get_role_custom()['client'] == 1)
                    <a href="#" id="btn_md_create"
                        class="btn btn-sm btn-{{ get_option('theme_color')  }}" data-toggle="modal"
                        data-target="#wdfm_website">
                        @icon('solid/plus') @langapp('add')
                    </a>
                    @endif --}}
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
                                    <button type="button" class="btn btn-grey active" onclick="set_level(null);">
                                        <span> All </span>
                                    </button>
                                    <button type="button" class="btn btn-grey" onclick="set_level('High');">
                                        <span> High </span>
                                    </button>
                                    <button type="button" class="btn btn-grey" onclick="set_level('Medium');">
                                        <span> Medium </span>
                                    </button>
                                    <button type="button" class="btn btn-grey" onclick="set_level('Normal');">
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
                            <i class="fas fa-table"></i> Site
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


    {{-- <div class="modal" id="delete_web_modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
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
    </div> --}}

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


    $(document).ready(function(){
        $('.wdfm-card').hover(function(){
            $(this).find('.wdfm-header').addClass('wdfm-header-upper');
        }); 
        $('.wdfm-card').mouseleave(function(){
            $(this).find('.wdfm-header').removeClass('wdfm-header-upper');
        }); 
    });



    function load_card(){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{!! route('webdefacement.load_card') !!}',
            type: "post",
            datatype: "html",
            beforeSend: function(){
                f_loading(null, '#data_card');
                $("#data_card").html('');  
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





</script>
@endpush
@endsection
