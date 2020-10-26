@extends('layouts.app')

@section('content')

<section id="content" class="bg">
    <section class="hbox stretch">
        
        <aside class="aside aside-md b-r">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#setting-nav">@icon('solid/bars')</a>
                    <p class="h3">@langapp('settings')  </p>
                </header>
                <section class="scrollable">
                    <div class="slim-scroll" data-color="#333333" data-disable-fade-out="true" data-distance="0" data-height="auto" data-size="3px"> 
                    <section id="setting-nav" class="hidden-xs">
                        <ul class="nav nav-pills nav-stacked no-radius">
                            <li class="active">
                                <a href="{{route('sitesettings.edit', ['id' => $siteSettings->code])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Site Settings
                                </a>
                            </li>
                            <li class="">
                                <a href="{{route('systemsetting.index', ['id' => $siteSettings->code])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    System Settings
                                </a>
                            </li>
                            <li class="">
                                <a href="{{route('datasettings.index')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Permission & Config Settings
                                </a>
                            </li>
                            <li class="">
                                <a href="{{route('userssettings.index')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Users
                                </a>
                            </li>
                            <li>
                                <a href="{{route('domain.index')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Domain
                                </a>
                            </li>
                        </ul>
                    </section>
                </div>
                </section>
            </section>
        </aside>

        <aside>
            <section class="vbox">

                <header class="header bg-white b-b clearfix">
                    <div class="bc-head">Site Setting  &gt; {{ $siteSettings->name }}</div>
                </header>
                <section class="scrollable wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            {!! Form::open(['route' => ['sitesettings.api.update', 'id' => $siteSettings->code], 'class' => 'bs-example form-horizontal ajaxifyForm validator', 'novalidate' => '', 'method' => 'PUT', 'files' => true]) !!}
                            <section class="panel panel-default">
                            <header class="panel-heading">@icon('solid/cogs') Site Details  </header>
                            <div class="panel-body">
                                <input type="hidden" name="id" value="{{  $siteSettings->id  }}">

                                <div class="form-group row">
                                    <label class="col-lg-3 control-label">Logo </label>
                                    <div class="col-lg-6">
                                        <div class="">
                                            <input type="file" class="form-control" name="logo" value="">
                                        </div>
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
                                        <select name="category" id="categorys" class="select2-option form-control" multiple="multiple">
                                            <?php 
                                                $category_site_id = [];
                                                foreach($siteSettings->get_categorys as $siteCategory){
                                                    $category_site_id[] = $siteCategory->category_id;
                                                }
                                            ?>
                                            @foreach($categories as $key => $category)
                                                <option value="{{ $category->id  }}"
                                                     {{ $category_site_id[$key] ? $category_site_id[$key] === $category -> id ? 'selected' : '' : ''}}
                                                >
                                                    {{ $category->name }}
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
</section>


@push('pagestyle')
@include('stacks.css.form')
@endpush
@push('pagescript')
@include('stacks.js.form')
@endpush

@endsection
