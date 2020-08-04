@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header bg-white b-b b-light">
            <div class="btn-group">
                <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" data-toggle="dropdown">
                    @langapp('filter')
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a href="#">
                            @langapp('Last Hour')
                        </a>
                  </li>
                    <li><a href="#">@langapp('all') </a></li>
                </ul>
            </div>
            <a href="{{  route('clients.create') }}"
                class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive" data-toggle="ajaxModal"
                title="@langapp('create') " data-placement="bottom">
                @icon('solid/plus') @langapp('create')
            </a>
            
            <a href="{{  route('clients.import')  }}" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive" title="@langapp('import_clients') " data-placement="bottom" data-toggle="ajaxModal">
                @icon('solid/cloud-upload-alt') @langapp('import')
            </a>
            <a href="{{  route('clients.export')  }}"
                class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive" title="CSV" data-placement="bottom">
                @icon('solid/cloud-download-alt') CSV
            </a>
        </header>
        <section class="scrollable wrapper bg-white">
            <div class="row">
                <div class="col-md-12">
                    <h1>Indicators Search</h1> 
                </div>
                <div class="col-md-4">
                    <select name="" id="indicator_type" class="select2-option form-control" multiple="multiple">
                        <option value="1">test</option>
                        <option value="2">test</option>
                        <option value="3">test</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="" id="role" class="select2-option form-control" multiple="multiple">
                        <option value="1">test</option>
                        <option value="2">test</option>
                        <option value="3">test</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="form-group m-b-md">
                        <div class="input-group">
                            <input type="text" class="form-control" name="keyword" placeholder="Search">
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-info btn-icon">
                                    <i class="fas fa-search"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <section class="">
                <div class="row header-badge">
                    <div class="col-md-6">
                        <span class="font-weight-bold">We've found 29 indicators</span>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="btn-group">
                            <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" data-toggle="dropdown">
                                @langapp('sort')
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="#">
                                        @langapp('sort')
                                    </a>
                                </li>
                                <li><a href="{{ route('clients.index') }}">@langapp('all') </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="show-indicators">
                    <ul class="list-indicators">
                        <li>
                            <a href="{{route('indicators.detail_indicators')}}">
                                <h1 class="primary-text">93.51.50.171</h1>
                                <span class="secondary-text">Type : IPv4</span>
                            </a>  
                        </li>
                        <li>
                            <a href="{{route('indicators.detail_indicators')}}">
                                <h1 class="primary-text">93.51.50.171</h1>
                                <span class="secondary-text">Type : IPv4</span>
                            </a>  
                        </li>
                        <li>
                            <a href="{{route('indicators.detail_indicators')}}">
                                <h1 class="primary-text">93.51.50.171</h1>
                                <span class="secondary-text">Type : IPv4</span>
                            </a>  
                        </li>
                        <li>
                            <a href="{{route('indicators.detail_indicators')}}">
                                <h1 class="primary-text">93.51.50.171</h1>
                                <span class="secondary-text">Type : IPv4</span>
                            </a>  
                        </li>
                        <li>
                            <a href="{{route('indicators.detail_indicators')}}">
                                <h1 class="primary-text">93.51.50.171</h1>
                                <span class="secondary-text">Type : IPv4</span>
                            </a>  
                        </li>
                        <li>
                            <a href="{{route('indicators.detail_indicators')}}">
                                <h1 class="primary-text">93.51.50.171</h1>
                                <span class="secondary-text">Type : IPv4</span>
                            </a>  
                        </li>
                    </ul>
                </div>
            </section>
        </section>
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

<script>
    $(document).ready(function () {
        $('#indicator_type').select2({
            placeholder:'Indicator Type',
        });
        $('#role').select2({
            placeholder:'Role',
        });
    });
</script>
@endpush
@endsection