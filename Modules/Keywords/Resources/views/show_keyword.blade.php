@extends('layouts.app')
@section('content')
@php
    // $segments = Request::segments();
    // $last_segments  = end($segments);
    $last_segments  = $site_code;
    // dd($last_segments);
@endphp
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
                            <h5 class="font-weight-bold">Keyword</h5>
                            <div class="box-item-keyword">
                                <ul id="keyword_main" class="main-list keyword-list">

                                </ul>
                            </div>
                        </div>
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
                            <h5 class="font-weight-bold">Web Defacememt</h5>
                            <div class="box-item-keyword">
                                <ul id="defacement_main" class="main-list defacement-list">

                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <h5 class="font-weight-bold">Credit Cards</h5>
                            <div class="box-item-keyword">
                                <ul id="credit_card_main" class="main-list credit_card-list">
                                    
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

    $( document ).ready(function() {
        get_keyword_main();
        get_keyword_sub('social');
        get_keyword_sub('darkweb');
        get_keyword_sub('defacement');
        get_keyword_sub('credit_card');
    });

    function get_keyword_main() {
        let result = 0;
        $.ajax({
            type:"POST",
            url:"{{ route('KeywordsController.get_keyword_main') }}",
            data:{
                code_site:'{{$last_segments}}'
            },
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
                loading('stop_load');
                if(response.status == 1) {
                    console.log(response);
                    result = 1;
                    if(response && response.data) {
                        let data = response.data;
                        let x;
                        $("ul.keyword-list").html('');
                        var newToDo = ``;
                        {{-- <span class="drag-handle m-r-xs"><i class="fa fa-arrows-alt"></i></span> --}}
                        for (x in data) 
                        {
                            newToDo += `
                                <li class="item-list item--keyword" data-id="${data[x].id}">
                                    <div class="left-side-item">
                                        <span class="text-keyword">${data[x].name}</span>
                                    </div>
                                </li>`;

                        }
                        $("ul.keyword-list").append(newToDo);
                    }
                } else {
                    console.log(response);
                    toastr.warning(response.message, '@langapp('response_status')');
                }

            },
            error: function (error){
                console.log(error);
                result = 0;
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status') ');
            }
        });
        return result;
    }

    function get_keyword_sub(type) {
        let result = 0;
        $.ajax({
            type:"POST",
            url:"{{ route('KeywordsController.get_keyword_sub') }}",
            data:{
                code_site:'{{$last_segments}}',
                type: type
            },
            beforeSend: function(){
                loading('load');
            },
            success:function(response) {
                loading('stop_load');
                if(response.status == 1) {
                    console.log(response);
                    result = 1;
                    if(response && response.data) {
                        let data = response.data;
                        let x;
                        $('ul.'+type+'-list').html('');
                        var newToDo = ``;
                        {{-- <span class="drag-handle m-r-xs"><i class="fa fa-arrows-alt"></i></span> --}}
                        for (x in data) 
                        {
                            newToDo += `
                                <li class="item-list item--keyword" data-id="${data[x].id}" data-keywords_main_id="${data[x].keywords_main_id}">
                                    <div class="left-side-item">
                                        
                                        <span class="text-keyword">${data[x].name}</span>
                                    </div>
                                </li>`;
                        }
                        $('ul.'+type+'-list').append(newToDo);
                    }
                } else {
                    console.log(response);
                    toastr.warning(response.message, '@langapp('response_status')');
                }

            },
            error: function (error){
                console.log(error);
                result = 0;
                loading('stop_load');
                var errors = error.response.data.errors;
                var errorsHtml = '';
                $.each(errors, function (key, value) {
                    errorsHtml += '<li>' + value[0] + '</li>';
                });
                toastr.error(errorsHtml, '@langapp('response_status') ');
            }
        });
        return result;
    }
</script>
@endpush
@endsection
