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
            <div class="row m-b-lg">
                {{-- Basic Information --}}
                <div class="col-md-6">
                    <h1 class="b-b">Basic Information</h1> 
                    {{-- Inner Basic Information--}}
                    <div class="row">
                        <div class="col-md-6">
                            IP ADDRESS:
                        </div>
                        <div class="col-md-6">
                            46.166.128.234
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            IP ADDRESS:
                        </div>
                        <div class="col-md-6">
                            46.166.128.234
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            IP ADDRESS:
                        </div>
                        <div class="col-md-6">
                            46.166.128.234
                        </div>
                    </div>
                </div>
                {{-- Validation --}}
                <div class="col-md-6">
                    <h1 class="b-b">Validation</h1> 
                    {{-- Inner Validation--}}
                    <div class="row">
                        <div class="col-md-6">
                            WHITELISTED DOMAIN:Whitelisted Domain Appspot.Com
                        </div>
                        <div class="col-md-6">               
                            WHITELISTED DOMAIN:Whitelisted Domain Appspot.Com
                        </div>
                        <div class="col-md-6">
                            WHITELISTED DOMAIN:Whitelisted Domain Appspot.Com
                        </div>
                        <div class="col-md-6">               
                            WHITELISTED DOMAIN:Whitelisted Domain Appspot.Com
                        </div>
                    </div>
                </div>
                {{-- Inner External Sources--}}
                <div class="off-set-6 col-md-6">
                    <h1 class="b-b">External Sources</h1>
                </div>
            </div>

            <section class="">
                <div class="row header-badge">
                    <div class="col-md-6">
                        <span class="font-weight-bold">Related Pulses</span>
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
                    </ul>
                </div>
            </section>

            <section class="m-t-lg">
                <div class="row header-badge">
                    <div class="col-md-6">
                        <span class="font-weight-bold">Server Response</span>
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