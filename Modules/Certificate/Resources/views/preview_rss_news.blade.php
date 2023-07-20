@extends('layouts.public')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <div class="section-jumborton">
            <div class="thumnail-img" style="background-image:url('https://images.unsplash.com/photo-1597086657068-7e10f874e8c2?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=986&q=80')"></div>
            <div class="jumborton-description">
                <div class="container-description">
                    <div class="headding-secondary-text">
                        <span class="text-date">August 4th, 2020</span>
                        <div class="mobi-d-block">
                            <span class="badeg-news"><i class="fas fa-newspaper"></i> NEWS</span>
                            <span class="badeg-view"><i class="fas fa-eye"></i> Views 0</span>
                        </div>
                    </div>
                    <div class="headding-primary-text">
                        WhatsApp’s new fact-check feature lets users identify fake information
                    </div>
                </div>
            </div>  
        </div>

        <div class="container-fluid" style="background: #fff;">
            <div class="row">
                <div class="col-md-12">
                    <div class="show-content-news">
                        <p></p>
                    </div>
                </div>
            </div>
        </div>

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

</script>
@endpush
@endsection
