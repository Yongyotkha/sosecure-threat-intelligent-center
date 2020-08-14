@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">

        {{-- Head --}}
        <header class="header panel-heading bg-white b-b b-light">
            <div class="bc-head">@langapp('data_leak') > @langapp('social')</div>    
        </header>

        {{-- Search --}}
        

        {{-- Tab Content --}}
        <section class="scrollable wrapper bg-white">
            <section class="panel panel-default">
                <div class="container-fluid" style="padding: 2rem;">
                    <div class="row m-b-md">
                        <div class="col-lg-4">
                            <label for="">Select Social</label>
                            <section id="select_social" class="select2-option form-control">
                                <option value="1" selected>All</option>
                            </section>
                        </div>
                        <div class="col-lg-8">
                            <label for="">Keywords</label>
                            <input type="text" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 text-right">
                            <button class="btn btn-info btn-responsive">
                                <i class="fas fa-search"></i>
                                Search
                            </button>
                            <button class="btn btn-default btn-responsive" style="white-space: nowrap">
                                <i class="fas fa-broom"></i>
                                <span> Clear </span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            <div class="col-md-12">
                <h3 class="text-dark">Display 300 record</h3>
            </div>
            <div class="row m-b-md">
                <div class="col-sm-12">
                    <div class="shadow-box-news b-d-all">
                        <article class="def-rlt">
                            <div class="entry">
                                <span class="entry-category">
                                    <a href="#">facebook.com</a>
                                </span>
                                <h3>
                                    <a href="#">
                                        WhatsApp’s new fact-check feature lets users identify fake information
                                    </a>
                                </h3>
                                <div class="entry-meta">
                                    <span class="entry-date"> <i class="fas fa-calendar-alt"></i> August 4th, 2020</span>
                                    <span class="entry-view"> <i class="fas fa-eye"></i> 300</span>
                                </div>
                                <div class="description-text hidden-xs">
                                    WhatsApp's "Search the Web" feature lets users perform web searches on viral messages to confirm their authenticity.
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="shadow-box-news b-d-all">
                        <article class="def-rlt">
                            <div class="entry">
                                <span class="entry-category">
                                    <a href="#">pantip.com</a>
                                </span>
                                <h3>
                                    <a href="#">
                                        How hackers behind Twitter Bitcoin scam were caught
                                    </a>
                                </h3>
                                <div class="entry-meta">
                                    <span class="entry-date"> <i class="fas fa-calendar-alt"></i> August 4th, 2020</span>
                                    <span class="entry-view"> <i class="fas fa-eye"></i> 300</span>
                                </div>
                                <div class="description-text hidden-xs">
                                    WhatsApp's "Search the Web" feature lets users perform web searches on viral messages to confirm their authenticity.
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
            
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
    $('#select_social').select2();
</script>
@endpush
@endsection