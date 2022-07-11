@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            <div class="bc-head">Keywords</div>
        </header>
        <section class="scrollable wrapper">
            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row">
                        <div class="col-xs-12">
                            <i class="fas fa-table"></i> Show Keywords
                        </div>
                    </div>
                </header>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-2">
                            <h5 class="font-weight-bold">Social</h5>
                            <div class="box-item-keyword">
                                <ul id="social_main" class="main-list social-list">
                                    
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <h5 class="font-weight-bold">Dark Web</h5>
                            <div class="box-item-keyword">

                                <ul id="darkweb_main" class="main-list darkweb-list">

                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <h5 class="font-weight-bold">Credit</h5>
                            <div class="box-item-keyword">

                                <ul id="defacement_main" class="main-list defacement-list">

                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <h5 class="font-weight-bold">Domain</h5>
                            <div class="box-item-keyword">

                                <ul id="defacement_main" class="main-list defacement-list">

                                </ul>
                            </div>
                        </div>

                        <div class="col-lg-2">
                            <h5 class="font-weight-bold">Email</h5>
                            <div class="box-item-keyword">

                                <ul id="defacement_main" class="main-list defacement-list">

                                </ul>
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
@endpush

@push('pagescript')
@include('stacks.js.datatables')
@include('stacks.js.form')
@include('stacks.js.fullscreen')

<script>

</script>
@endpush
@endsection
