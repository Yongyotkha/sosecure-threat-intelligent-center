@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            {{-- <a href="" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a> --}}
            <div class="bc-head">@langapp('dashboard')</div>
        </header>

        <section class="scrollable wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <header class="header-text-badge"><i class="fas fa-network-wired"></i>&nbsp; Asset</header>
                        <div class="main-dash">
                            <div class="main-dash-box">
                                Core Variables
                                <ul class="item-list-dash">
                                    <li>
                                        <div class="item-dash bg-blue">
                                            216.58.193.220
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <div class="main-dash-box">
                                Reset Styles
                                <ul class="item-list-dash">
                                    <li>
                                        <div class="item-dash bg-gray">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-gray">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-gray">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-gray">
                                            216.58.193.220
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <div class="main-dash-box">
                                Core
                                <ul class="item-list-dash">
                                    <li>
                                        <div class="item-dash bg-yellow">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-yellow">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-yellow">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-yellow">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-yellow">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-yellow">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-yellow">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-yellow">
                                            216.58.193.220
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <div class="main-dash-box">
                                Components
                                <ul class="item-list-dash">
                                    <li>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                    </li>
                                    <li>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                    </li>
                                    <li>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-red">
                                            216.58.193.220
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <div class="main-dash-box">
                                Ultilities
                                <ul class="item-list-dash">
                                    <li>
                                        <div class="item-dash bg-purple">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-purple">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-purple">
                                            216.58.193.220
                                        </div>
                                        <div class="item-dash bg-purple">
                                            216.58.193.220
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <section class="panel panel-default">
                        <div class="card-list">
                            <header class="header-text-badge"><i class="fas fa-project-diagram"></i>&nbsp; Top Asset</header>
                            <table class="table table-striped">
                                <tr>
                                    <th width="50%">Asset name</th>
                                    <th width="50%">Data</th>
                                </tr>
                                <tr>
                                    <td>Lorem ipsum dolor sit amet.</td>
                                    <td>Lorem ipsum dolor sit amet </td>
                                </tr>
                                <tr>
                                    <td>Lorem ipsum dolor sit amet.</td>
                                    <td>Lorem ipsum dolor sit amet </td>
                                </tr>
                                <tr>
                                    <td>Lorem ipsum dolor sit amet.</td>
                                    <td>Lorem ipsum dolor sit amet </td>
                                </tr>
                                <tr>
                                    <td>Lorem ipsum dolor sit amet.</td>
                                    <td>Lorem ipsum dolor sit amet </td>
                                </tr>
                            </table>
                        </div>
                        </section>
                    </div>
                    <div class="col-md-9">
                        <div class="row m-b-md">
                            <div class="col-md-3">
                                <div class="dash-card">
                                    <div class="dash-name">
                                        Compromise System
                                    </div>
                                    <div class="dash-count color-purple">
                                        0
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dash-card">
                                    <div class="dash-name">
                                        Compromise Account
                                    </div>
                                    <div class="dash-count color-red">
                                        1
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dash-card">
                                    <div class="dash-name">
                                        CVE Detect
                                    </div>
                                    <div class="dash-count color-yellow">
                                        60
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dash-card">
                                    <div class="dash-name">
                                        Malware Infected
                                    </div>
                                    <div class="dash-count color-blue">
                                        28
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="dash-card">
                                    <div class="dash-name">
                                        Dark Lark (Dark Web)
                                    </div>
                                    <div class="dash-count color-green">
                                        19
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dash-card">
                                    <div class="dash-name">
                                        Data Lark (Social)
                                    </div>
                                    <div class="dash-count color-light-purple">
                                        0
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>



@push('pagestyle')
    @include('stacks.css.datatables')
@endpush

@push('pagescript')
@include('stacks.js.datatables')

<script>

</script>
@endpush
@endsection