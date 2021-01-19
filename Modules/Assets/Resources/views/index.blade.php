@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">
            <header class="header bg-white b-b b-light head-d-flex-nowrap" style="white-space: nowrap;">
                <div class="bc-head m-none" style="width:100%;">
                     Setting > Assets
                </div>

                <div class="pull-right">
                    <select name="" id="select-site" class="select2-option form-control" style="min-width: 100px">
                        <option value="allsite">All Site</option>
                    </select>
                </div>

                <div class="button-control">
                    <div class="btn-group">
                        <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="fillter-advance">@icon('solid/plus') Add</button>
                    </div>

                    <div class="btn-group">
                        <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="fillter-advance"><i class="fas fa-filter"></i> Filter</button>
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
                        <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="fillter-advance">Import Asset</button>
                    </div>
                </div>
            </header>

            <section class="scrollable wrapper">
                <div class="hide-fillter" style="margin-bottom: 1rem;display:none;background:#fff;padding:1rem;">
                    <div class="row">
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

                        <div class="col-md-12">
                            <div class="form-group m-b-md pull-right">
                                <button class="btn btn-info">
                                    <i class="fas fa-search"></i>
                                    <span> @langapp('apply') </span>
                                </button>
                                <button class="btn btn-default">
                                    <i class="fas fa-broom"></i>
                                    <span> Clear </span>
                                </button>
                            </div>
        
                        </div>
                    </div>
                    <div class="row">
                       
                    </div>
                </div>

                <section class="panel panel-default">
                    <header class="panel-heading font-bold panel-header-blue">
                        <div class="row">
                            <div class="col-xs-12">
                                <i class="fas fa-table"></i> Table Assets
                            </div>
                        </div>
                    </header>
                    <div class="panel-body">
                        <table class="table table-striped table-bordered" id="table-assets-template">
                            <thead>
                                <tr>
                                    <th class="no-sort">
                                        <label>
                                            <input name="select_all" value="1" id="select-all" type="checkbox" />
                                            <span class="label-text"></span>
                                        </label>
                                    </th>
                                    <th>Site Name</th>
                                    <th>Asset</th>
                                    <th>Referent</th>
                                    <th style="width: 20px" class="text-center">Status</th>
                                    <th style="width: 20px" class="text-center">Action</th>
                                </tr>
                            </thead>
                        </table>
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
