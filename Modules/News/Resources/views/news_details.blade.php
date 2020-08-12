@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        {{-- Head --}}
        <header class="header panel-heading bg-white b-b b-light">
            <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a>
            <div class="bc-head">@langapp('news') > WhatsApp’s new fact-check feature lets users identify fake information</div>    
        </header>

        <div class="section-jumborton">
            <div class="thumnail-img" style="background-image:url('https://images.unsplash.com/photo-1597086657068-7e10f874e8c2?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=986&q=80')"></div>
            <div class="jumborton-description">
                <div class="container-description">
                    <div class="headding-secondary-text">
                        <span class="text-date">August 4th, 2020</span>
                        <div class="mobi-d-block">
                            <span class="badeg-news"><i class="fas fa-newspaper"></i> NEWS</span>
                            <span class="badeg-view"><i class="fas fa-eye"></i> Views 300</span>
                        </div>
                    </div>
                    <div class="headding-primary-text">
                        WhatsApp’s new fact-check feature lets users identify fake information
                    </div>
                    <div class="shared-news">
                        <button class="btn-shared" data-toggle="tooltip"
                        data-placement="top" data-original-title="Shared"><i class="fas fa-share-square"></i></button>
                    </div>
                </div>
            </div>  
        </div>

        <div class="container-fluid" style="background: #fff;">
            <div class="row">
                <div class="col-md-8">
                    <div class="show-content-news">
                        <p>Intel is currently looking into how 20GB of sensitive internal data came to find its way online.</p>

                        <p>The range of documents — some marked “confidential,” “under NDA” or “restricted secret”— were uploaded to file hosting service MEGA by Swiss Android developer Till Kottmann.</p>
                        
                        <p>Before his account was suspended <a href="https://twitter.com/deletescape/status/1291405688204402689" target="_blank" data-feathr-click-track="true">by Twitter</a>, Kottmann explained on the site that “most of the things here have not been published anywhere before.”</p>
                        
                        <p>They include details on chip roadmaps, development and debugging tools, schematics, training videos, process simulator ADKs, sample code, Bringup guides and much more.</p>
                        
                        <p>Affected platforms include Kaby Lake, Snow Ridge, Elkhart Lake and the unreleased 10nm Tiger Lake architecture.</p>
                        
                        <p>Kottmann claimed to have received this data from a third party who found it on an unsecured server via a simple nmap scan. Many of the zip files were reportedly protected with easy-to-guess or crack passwords.</p>
                        
                        <p>However, Intel doesn’t believe the data came from a network breach, and said in a brief statement that it is urgently investigating what may have happened.</p>
                        
                        <p>“The information appears to come from the Intel Resource and Design Center, which hosts information for use by our customers, partners and other external parties who have registered for access,” it continued. “We believe an individual with access downloaded and shared this data."</p>
                        
                        <p>Although there appears to have been no personally identifiable information (PII) exposed in the breach, the compromise of so many sensitive internal documents will be ringing alarm bells at the chipmaker’s HQ — especially as more leaks have been promised.</p>
                        
                        <p>Erich Kron, security awareness advocate at KnowBe4, said the incident highlights supply chain cyber-risk.</p>
                        
                        <p>“There is always a risk when sharing potentially sensitive information to these business partners, however, this is often an unavoidable part of doing business,” he added.</p>
                        
                        <p>“Whenever providing intellectual property access to another organization or individual, it is important to log not only who has access, but when and what data they are accessing. Even better, as in this case with Intel, ensuring that you know where the documents have been shared by potentially marking the document itself, can be very valuable when hunting potential misuse as appears to have occurred here."</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="news_related">
                        <li>
                            <h2>Related to This Story</h2>
                        </li>
                        <li><a href="">Lorem ipsum dolor, sit amet consectetur Lorem ipsum dolor, sit amet consectetur </a></li>
                        <li><a href="">adipisicing elit. Autem quis cum veniam Autem quis cum veniam</a> </li>
                        <li><a href="">laudantium expedita hic illum optio expedita hic illum</a></li>
                        <li><a href="">laudantium expedita hic illum optio expedita hic illum</a></li>
                        <li><a href="">eius alias. Eum id odit pariatur, reprehenderit  odit pariatur, reprehenderit</a></li>
                        <li><a href="">eius alias. Eum id odit pariatur, reprehenderit  odit pariatur, reprehenderit</a></li>
                    </ul>
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
    $('#select_news').select2();
</script>
@endpush
@endsection