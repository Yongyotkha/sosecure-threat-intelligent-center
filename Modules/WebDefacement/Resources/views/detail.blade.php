@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        {{-- Head --}}
        <header class="header bg-white b-b b-light head-d-flex-nowrap"
        style="white-space: nowrap;overflow-x: auto;">
        <div class="bc-head m-none">
            <a href="{{route('webdefacement.index')}}"
                class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive m-r-5">
                @icon('solid/arrow-left')
            </a>
            @langapp('webdefacement') > Targeted Brand:Paypal
        </div>

        &nbsp;

    </header>

        <section class="scrollable wrapper">
            
            <section class="panel panel-default">
                <header class="panel-heading font-bold panel-header-blue">
                    <div class="row d-flex-center">
                        <div class="col-xs-6">
                            <i class="fas fa-chart-pie"></i> Web Defacement
                        </div>
                        <div class="col-xs-6 text-right">
                            <button id="togglecollapsechart" style="margin-left:5px;" class="btn text-dark" onclick="collpase_chart('#details_webdefacement','#togglecollapsechart')">
                                <i class="fas fa-minus-square"></i>Collapse
                            </button>
                        </div>
                    </div>
                </header>
                <div class="panel-body" id="details_webdefacement">
                    <table class="table table-striped table-bordered table-hover">
                        <tr>
                            <th>Name Page</th>
                            <td>Targeted Brand : Paypay</td>
                        </tr>
                        <tr>
                            <th>URL</th>
                            <td>https//www.xxxx.com/xxxx.html</td>
                        </tr>
                        <tr>
                            <th>Domain</th>
                            <td>html//www.xxxxx.com</td>
                        </tr>
                        <tr>
                            <th>User Agent</th>
                            <td>Mozila / 5.0 (x11.) Lorem ipsum dolor sit amet. <a href="#" class=""></a></td>
                        </tr>
                        <tr>
                            <th>Site</th>
                            <td>ออมสิน</td>
                        </tr>
                        <tr>
                            <th>Create Date</th>
                            <td>2021-01-01 11:11</td>
                        </tr>
                        <tr>
                            <th>Last Online</th>
                            <td>10 second ago</td>
                        </tr>
                        <tr>
                            <th>Last Check</th>
                            <td>10 second ago</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td> <span class="dot low"></span> &nbsp; Normal</td>
                        </tr>
                        <tr>
                            <th>Hash</th>
                            <td>xxxxxxxxxxxxxxxxxxxxxx</td>
                        </tr>
                        <tr>
                            <th>File Size</th>
                            <td>10k</td>
                        </tr>
                        <tr>
                            <th>Element</th>
                            <td>100 tag</td>
                        </tr>
                    </table>
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
    function collpase_chart(id,text){
        $(id).slideToggle();
        if($(text).text() == 'Expanded'){
            $(text).html('<i class="fas fa-minus-square"></i>Collapse');
        }else{
            $(text).html('<i class="fas fa-plus-square"></i>Expanded');
        }
    }
</script>
@endpush
@endsection