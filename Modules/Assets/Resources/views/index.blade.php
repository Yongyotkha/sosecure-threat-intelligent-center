@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">
            <header class="header bg-white b-b b-light head-d-flex-nowrap" style="white-space: nowrap;">
                <div class="bc-head m-none" style="width:100%;">
                     Setting > Assets
                </div>

                <div class="pull-right" style="min-width: 270px;">
                    <select name="" id="select-site" class="select2-option form-control" style="min-width: 270px">
                        <option value="allsite">All Site</option>
                    </select>
                </div>

                <div class="button-control">
                    <div class="btn-group">
                        <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="fillter-advance">@icon('solid/plus') Add</button>
                    </div>

                    <div class="btn-group">
                        <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="fillter-advance">Import Asset</button>
                    </div>

                    <div class="btn-group">
                        <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" data-toggle="dropdown">Group By
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left">
                            <li>
                                <a href="#">
                                    Internet Name
                                </a>
                            </li>   
                            <li>
                                <a href="#">
                                    Affiliate - Internet Name
                                </a>
                            </li>   
                            <li>
                                <a href="#">
                                    Affiliate - Domain Name
                                </a>
                            </li>   
                            <li>
                                <a href="#">
                                    Domain Name
                                </a>
                            </li>  
                            <li>
                                <a href="#">
                                    IP Address
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    IPv6 Address
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    Malicious Internet Name
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    Human Name
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    Internet Name
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    Email Address
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    Domain Name (Parent)
                                </a>
                            </li>
                            <li>
                                <a href="#">
                                    Phone Number
                                </a>
                            </li>
                        </ul>
                    </div>
            
                    <div class="btn-group">
                        <a id="advance-search" href="#area-advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
                            <span><i class="fas fa-filter"></i> @langapp('Search_Advance')</span>
                        </a>
                    </div>

                </div>
            </header>

            <section class="scrollable wrapper">

                <div class="container-fluid" style="margin-bottom:10px;">
                    <div class="row">
                        <div class="col-md-4 nopadding">
                            <div class="card-dash-compro none-bg none-shadow">
                                <div class="left-card">
                                    <div class="img-icon-card ice">
                                        <img src="{{asset('images/database.png')}}" alt="">
                                    </div>
                                    <h3 class="name-dash-text-compro text-dark text-upper ">Assets</h3>
                                    <span class="number-card warning">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 nopadding">
                            <div class="card-dash-compro none-bg none-shadow">
                                <div class="left-card">
                                    <div class="img-icon-card ice">
                                        <img src="{{asset('images/windows.png')}}" alt="">
                                    </div>
                                    <h3 class="name-dash-text-compro text-dark text-upper">Windows</h3>
                                    <span class="number-card info">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 nopadding">
                            <div class="card-dash-compro none-bg none-shadow">
                                <div class="left-card">
                                    <div class="img-icon-card ice">
                                        <img src="{{asset('images/linux.png')}}" alt="">
                                    </div>
                                    <h3 class="name-dash-text-compro text-dark text-upper ">Linux</h3>
                                    <span class="number-card green">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <section class="panel panel-default" id="area-advance-search" style="display: none;">
                    <div class="container-fluid" style="padding: 2rem;">
                        <div class="row m-b-md">
                            <div class="col-md-8">
                                <div class="form-group m-b-md">
                                    <label for="" class="">Keyword</label>
                                    <input type="text" class="form-control" name="keyword" placeholder="Search">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group m-b-md">
                                    <label for="" class="">Referent</label>
                                    <input type="text" class="form-control" name="keyword" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 text-right">
                                <button type="button" class="btn btn-info btn-responsive btn-fz-13" onclick="search()">
                                    <i class="fas fa-search"></i>
                                    @langapp('apply')
                                </button>
                                <button type="button" id="btn_rss_data_reset" class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                    <i class="fas fa-broom"></i>
                                    <span> Clear </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="panel panel-default">
                    <header class="panel-heading font-bold panel-header-blue">
                        <div class="row">
                            <div class="col-xs-12">
                                <i class="fas fa-table"></i> Table Assets
                            </div>
                        </div>
                    </header>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="table-assets-template">
                                <thead>
                                    <tr>
                                        <th class="no-sort">
                                            <label>
                                                <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                <span class="label-text"></span>
                                            </label>
                                        </th>
                                        <th>Domain</th>
                                        <th>IP</th>
                                        <th>OS Type</th>
                                        <th>CPE</th>
                                        <th style="width: 20px" class="text-center">Status</th>
                                        <th style="width: 20px" class="text-center">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </section>

            </section>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>


        <!-- Modal Scans -->
        <div class="modal in fixed-left" id="asset_to_use" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside size-half-50" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        Asset To Use
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-3 text-center">
                            <h3 class="text-dark">Assets</h3>
                        </div>
                        <div class="col-xs-9 text-center">
                            <h3 class="text-dark">Referent</h3>
                        </div>
                        <div class="col-md-12">
                            <hr>
                        </div>
                    </div>
                    <div id="show_asets" class="row">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="button" class="btn btn-info btn-rounded" onclick="save_assets()">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>

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

    $('#area-advance-search').hide();
    $('#advance-search').click(function(){
        $('#area-advance-search').toggle();
    });
    

    $(document).ready(function () {
        $('#source').select2();
        $('#select-site').select2();

        $('.hide-fillter').hide();
        $('#fillter-advance').click(function(){
            $('.hide-fillter').toggle();
        });
    });
    var site_id = 0;
    $(function () {
        $('#table-assets-template').DataTable({
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: {
                contentType: "application/json",
                dataType: 'JSON',
                type: "POST",
                url: '{!! route('scans.data_scans_assets') !!}',
                data: function ( d ) {
                    d.menu = 'system';
                    d.site_id = site_id;
                    return JSON.stringify( d );
                }
            },
            columns: [
                {
                    data: 'chk',
                    name: 'chk',
                },
                {
                    data: 'site',
                    name: 'site',
                },
                {
                    data: 'assets',
                    name: 'assets',
                },
                {
                    data: 'referent',
                    name: 'referent',
                }, 
                {
                    data: 'cpe',
                    name: 'cpe',
                }, 
                {
                    data: 'status',
                    name: 'status',
                    className: 'w-10 text-center'
                },  
                {
                    data: 'action',
                    name: 'action',
                    className: 'no-wrap'
                },    
            ],
        });
    });
</script>
@endpush

@endsection
