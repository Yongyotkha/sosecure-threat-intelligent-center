@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        {{-- Head --}}
        <header class="header panel-heading bg-white b-b b-light">
            <div class="bc-head"> @langapp('webdefacement')</div>    

            <button id="advance-search" href="#area-advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                <span>@langapp('Search_Advance')</span>
             </button>
             <div class="pull-right" style="margin-top: 8px; width: 300px;">
                <select name="site" id="site" class="select2-option form-control select-site" style="min-width: 300px">
                    <option value="">All Site</option>
                    @if ($SiteSettings)

                    @foreach ($SiteSettings as $SiteSettings)
                    <option value="{{@$SiteSettings->id}}">{{@$SiteSettings->name}}
                    </option>
                    @endforeach

                    @endif
                </select>
            </div>
        </header>

        <section class="scrollable wrapper">
            {{-- Search --}}
            <section class="panel panel-default"   id="area-advance-search" style="display: none">
                <div class="container-fluid" style="padding: 2rem;">
                    <div class="row m-b-md">
                        <div class="col-lg-12">
                            <div class="row d-flex align-items-center">
                                <label for="" class="col-sm-1 col-xs-12 col-form-label">Keywords</label>
                                <div class="col-sm-11 col-xs-12">
                                    <input type="text" id="keywords" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for=""  class="col-sm-3 col-xs-12 col-form-label">Status</label>
                                <div class="col-sm-9 col-xs-12">
                                <select name="" id="datatype" class="select2-option form-control" multiple="multiple">
                                    <option value="critical">Critical</option>
                                    <option value="high">High</option>
                                    <option value="meduim">Meduim</option>
                                    <option value="normal">Normal</option>
                                    <option value="none">None</option>
                                </select>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-lg-12 text-right mt-2">
                            <button type="button" id="btn_news_search" class="btn btn-info btn-responsive" onclick="search()">
                                <i class="fas fa-search"></i>
                                Search
                            </button>
                            <button type="button" id="btn_news_reset" class="btn btn-default btn-responsive" 
                            style="white-space: nowrap"  onclick="clear_search()">
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
                                <a href="#" id="all" class="btn-chart white d-il-flex mr-3">
                                    <span class=""></span>
                                    All
                                </a>
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
                                <a href="#" id="normal" class="btn-chart white d-il-flex mr-3">
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

                    <div class="wdfm-container" id='data_card'>
                        
                
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

    var keywords = null;
    var site = null;
    var datatype = null;
    var level = null;
    var search_ = 0;
  




    $(document).ready(function(){
        $('.wdfm-card').hover(function(){
            $(this).find('.wdfm-header').addClass('wdfm-header-upper');
        }); 
        $('.wdfm-card').mouseleave(function(){
            $(this).find('.wdfm-header').removeClass('wdfm-header-upper');
        }); 
    });

    $('#area-advance-search').hide();
    $('#advance-search').click(function(){
        $('#area-advance-search').toggle();
    });


    $(function () {
        load_card();
    });
    

    
    function load_card(search_){

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
                $('.ajax-loading').show();
            },
        }).done(function(data){

                $('.ajax-loading').hide();
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

    function search (level_=null) {

        search_ = 1;
        keywords = $('#keywords').val();
        datatype = $('#datatype').val(); 
        site = $('#site').val();
        level = level_;
       
        
        load_card(search_);

    }

    function clear_search () {

    search_ = 0;
    datatype = null;
    site = null;
    keywords = null;
    level = null;

    $('#keywords').val('');
    $('#datatype').val('').trigger('change');
    $('#site').val('').trigger('change');
    site = $('#site').val();



    load_card(search_);

}

    $("#critical").click(function() {
        
      
        search ('critical');
 
    });
    $("#high").click(function() {
        
        search ('high');
    });
    $("#medium").click(function() {


        search ('medium');

    });
    $("#normal").click(function() {

    
        search ('normal');

    });
    $("#none").click(function() {

     
        search ('none');

    });

    $("#all").click(function() {

     
    search (null);

    });
  
</script>
@endpush
@endsection