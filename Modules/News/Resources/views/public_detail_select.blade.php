@extends('layouts.public')
@section('image','{{@$RSSNews->logo}}')
@section('title',"@$RSSNews_name")
@section('description',"@$RSSNews_detail")
@section('content')
@php 
    // dd($RSSNews_name);
@endphp
<section id="content" class="bg">
    <section class="hbox stretch">
    <section class="vbox">
        <section class="scrollable wrapper">
            <div class="section-jumborton">
                {{-- <div class="thumnail-img" style="background-image:url('https://images.unsplash.com/photo-1597086657068-7e10f874e8c2?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=986&q=80')"></div> --}}
                {{-- @if(@$RSSNews->logo)
                    <div class="thumnail-img" style="background-image:url('{{@$RSSNews->logo}}')"></div>
                @endif --}}
                
                <div class="jumborton-description">
                    <div class="container-description">
                        <div class="headding-secondary-text" id="detial_new">
                            <span class="text-date">{{@date("F d",strtotime($RSSNews->public_date))}}th{{@date(", Y",strtotime($RSSNews->public_date))}}</span>
                            <div class="mobi-d-block">
                                @if(@$RSSNews->get_cate)
                                @foreach(@$RSSNews->get_cate as $cate)
                                {{-- 1 --}}
                                {{-- {{@var_dump($cate)}} --}}
                                {{-- {{@$cate->cate->name}} --}}
                                <span class="badeg-news"><i class="fas fa-newspaper"></i>
                                    {{@$cate->get_cate_name->name}}</span>
                                @endforeach

                                @endif
                                <span class="badeg-view"><i class="fas fa-eye"></i> Views
                                    {{@$RSSNews->view ? $RSSNews->view : 0}}</span>
                            </div>
                        </div>
                        <div class="headding-primary-text">
                            {!!@$RSSNews_name!!}
                        </div>
                        <div class="shared-news">
                            <div class="pos-rlt">
                                <button class="btn-shared"
                                    onclick="shared_news('{!!@$RSSNews_name!!}','{{route('news.public_detail_select', ['code' => @$RSSNews->code , 'lang' => $lang])}}')"
                                    data-toggle="tooltip" data-placement="bottom" data-original-title="Shared">
                                    <i class="fas fa-share-square"></i>
                                </button>
                                <div class="menu-shared-main <!--d-none-->">
                                    <ul class="menu-shared">
                                        <li>
                                            <a href="javascript:void(0)" id="share_facebook" target="_blank"
                                                onclick="shared_news('{!!@$RSSNews_name!!}','{{route('news.public_detail_select', ['code' => @$RSSNews->code , 'lang' => $lang])}}')">
                                                <i class="fab fa-facebook-f icon-sc face"></i>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="javascript:void(0)" id="share_line" target="_blank"
                                                onclick="shared_news('{!!@$RSSNews_name!!}','{{route('news.public_detail_select', ['code' => @$RSSNews->code , 'lang' => $lang])}}')">
                                                <i class="fab fa-line icon-sc line"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>  
            </div>

            <div class="container-fluid" style="background: #fff;">
                <div class="row">
                    <div class="col-md-8">
                        <div class="show-content-news" id="content_news">
                            {!!@$RSSNews_detail!!}
                            {{-- <p>Intel is currently looking into how 20GB of sensitive internal data came to find its way online.</p>

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
                            
                            <p>“Whenever providing intellectual property access to another organization or individual, it is important to log not only who has access, but when and what data they are accessing. Even better, as in this case with Intel, ensuring that you know where the documents have been shared by potentially marking the document itself, can be very valuable when hunting potential misuse as appears to have occurred here."</p> --}}
                        </div>
                    </div>
                        <div class="col-md-4">
                        @if(@$RSSNews_last10)
                        <ul class="news_related">
                            <li>
                                <h2>Related to This Story</h2>
                            </li>
                            @if(@$RSSNews_last10)
                            @if($lang == 'th')
                            @foreach($RSSNews_last10 as $RSSNews_last10_val)
                                @php
                                    if($RSSNews_last10_val->title_th) {
                                        $name_val_th = $RSSNews_last10_val->title_th;
                                    } else {
                                        $name_val_th = $RSSNews_last10_val->title_en;
                                    }
                                @endphp
                            <li><a
                                    href="{{route('news.news_detail_code',['code' => @$RSSNews_last10_val->code])}}">{{@$name_val_th}}</a>
                            </li>
                            @endforeach
                            @else
                            @foreach($RSSNews_last10 as $RSSNews_last10_val)
                                @php
                                    if($RSSNews_last10_val->title_en) {
                                        $name_val_en = $RSSNews_last10_val->title_en;
                                    } else {
                                        $name_val_en = $RSSNews_last10_val->title_th;
                                    }
                                @endphp
                            <li><a
                                    href="{{route('news.news_detail_code',['code' => @$RSSNews_last10_val->code])}}">{{@$name_val_en}}</a>
                            </li>
                            @endforeach
                            @endif
                            @endif
                            {{-- <li><a href="">Lorem ipsum dolor, sit amet consectetur Lorem ipsum dolor, sit amet consectetur </a></li>
                            <li><a href="">adipisicing elit. Autem quis cum veniam Autem quis cum veniam</a> </li>
                            <li><a href="">laudantium expedita hic illum optio expedita hic illum</a></li>
                            <li><a href="">laudantium expedita hic illum optio expedita hic illum</a></li>
                            <li><a href="">eius alias. Eum id odit pariatur, reprehenderit  odit pariatur, reprehenderit</a></li>
                            <li><a href="">eius alias. Eum id odit pariatur, reprehenderit  odit pariatur, reprehenderit</a></li> --}}
                        </ul>
                        @endif
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

<script>
    $('#select_news').select2();
    $('.menu-shared-main').hide();
    $('.btn-shared').on('click',function(){
        $('.menu-shared-main').toggle();
        $('.menu-shared').addClass('open-shared');
    });

    function shared_news(name,url) {
        console.log(name);
        $("meta[property='og:title']").attr("content",name);
        $("meta[property='og:url']").attr("content",url);

        $("#share_facebook").attr("onClick","js_popup('https://www.facebook.com/sharer.php?u="+url+"',783,600); return false;");
        $("#share_line").attr("onClick","js_popup('https://social-plugins.line.me/lineit/share?url="+url+"',783,600); return false;");
    }

    function js_popup(theURL,width,height) {
        leftpos = (screen.availWidth - width) / 2;
        toppos = (screen.availHeight - height) / 2;
        window.open(theURL, "viewdetails","width=" + width + ",height=" + height + ",left=" + leftpos + ",top=" + toppos);
    }

    function printDiv() { 
        var divContents = document.getElementById("content_news").innerHTML; 
        var divTopic = document.getElementById("topic_news").innerHTML; 
        var divDetail = document.getElementById("detial_new").innerHTML; 
        var a = window.open('', '', 'height=500, width=1000'); 
        a.document.write('<html>'); 
        a.document.write('<body > <h1>'); 
        a.document.write(divTopic);
        a.document.write('</h1> ');
        a.document.write(divDetail);
        a.document.write('<p>'); 
        a.document.write(divContents); 
        a.document.write('</p>'); 
        a.document.write('</body></html>'); 
        a.document.close(); 
        a.print();

    }
</script>
@endpush
@endsection