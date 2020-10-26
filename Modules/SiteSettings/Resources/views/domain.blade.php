@extends('layouts.app')

@section('content')

<section id="content" class="bg">
    <section class="hbox stretch">
        
        <aside class="aside aside-md b-r">
            <section class="vbox">
                <header class="dk header b-b">
                    <a class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#setting-nav">@icon('solid/bars')</a>
                    <p class="h3">@langapp('settings')  </p>
                </header>
                <section class="scrollable">
                    <div class="slim-scroll" data-color="#333333" data-disable-fade-out="true" data-distance="0" data-height="auto" data-size="3px"> 
                    <section id="setting-nav" class="hidden-xs">
                        <ul class="nav nav-pills nav-stacked no-radius">
                            <li>
                                <a href="{{route('sitesettings.edit', ['id' => $siteSettings->code])}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Site Settings
                                </a>
                            </li>
                            <li >
                                <a href="{{route('systemsetting.index')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    System Settings
                                </a>
                            </li>
                            <li class="">
                                <a href="{{route('datasettings.index')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Permission & Config Settings
                                </a>
                            </li>
                            <li>
                                <a href="{{route('userssettings.index')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Users
                                </a>
                            </li>
                            <li class="active">
                                <a href="{{route('domain.index')}}">
                                    @icon('solid/angle-right', 'text-'.get_option('theme_color'))
                                    Domain
                                </a>
                            </li>
                        </ul>
                    </section>
                </div>
                </section>
            </section>
        </aside>

        <aside>
            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light">
                    <div class="bc-head">Site Setting > ธนาคารออมสิน</div>
                    <a href="{{  route('users.export')  }}" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-rel="tooltip" title="@langapp('export') CSV">
                        @icon('solid/download') CSV
                    </a>
                    <button type="submit" id="button" class="btn btn-sm btn-danger pull-right m-xs" value="bulk-delete">
                        <span data-rel="tooltip" title="Are you sure?" data-placement="right">@icon('solid/trash-alt') @langapp('delete')</span>
                    </button>
                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right" data-toggle="modal" data-target="#add-domain">
                        @icon('solid/plus') @langapp('create')
                    </a>
                </header>

                <section class="scrollable wrapper">
                    <div class="row">
                        <div class="col-lg-12">  
                            <section class="panel panel-default">
                            <header class="panel-heading">@icon('solid/user') Domain</header>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table  class="table table-striped" id="table-domain-template">
                                        <thead>
                                            <tr>
                                                <th class="hide"></th>
                                                <th class="no-sort">
                                                    <label>
                                                        <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                        <span class="label-text"></span>
                                                    </label>
                                                </th>
                                                <th>@langapp('name')  </th>
                                                <th>Domain</th>
                                                <th>Started</th>
                                                <th>Finished</th>
                                                <th>Elements</th>
                                                <th>Progress</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            {{-- <div class="panel-footer">
                               
                            </div> --}}
                        </div>
                    </div>
                </section>
            </section>
        </aside>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen, open" data-target="#nav,html"></a>

    <!-- Modal Add Domain -->
    <div class="modal modal-slide" id="add-domain" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title" id="exampleModalLabel">Add Domain</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Name <span class="text-danger">*</span> </label>
                        <div class="col-lg-8">
                            <input type="text" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Domain <span class="text-danger">*</span> </label>
                        <div class="col-lg-8">
                            <input type="text" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Open Scan </label>
                        <div class="col-lg-8">
                            <label class="switch">
                                <input type="hidden" value="FALSE" name="">
                                <input type="checkbox" name="" value="TRUE">
                                <span></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 control-label"></label>
                        <div class="col-lg-8">
                            <ul class="role-group">
                                <li>
                                    <div class="role-main">
                                        <span class="role-click" onclick="openrole(this,'role-1')">@icon('solid/plus')</span>
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Content Analysis</span>
                                            </label>
                                        </span>
                                    </div>
                                    <ul id="role-1" class="role-group-sub">
                                        <li>
                                            <div class="role-sub">
                                                <span class="checkbox chk-inline">
                                                    <label>
                                                        <input type="checkbox" name="" checked="" value="TRUE">
                                                        <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                                    </label>
                                                </span>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <div class="role-main">
                                        <span class="role-click" onclick="openrole(this,'role-2')">@icon('solid/plus')</span>
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Crawling and Scanning</span>
                                            </label>
                                        </span>
                                    </div>
                                    <ul id="role-2" class="role-group-sub">
                                        <li>
                                            <div class="role-sub">
                                                <span class="checkbox chk-inline">
                                                    <label>
                                                        <input type="checkbox" name="" checked="" value="TRUE">
                                                        <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                                    </label>
                                                </span>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <div class="role-main">
                                        <span class="role-click" onclick="openrole(this,'role-3')">@icon('solid/plus')</span>
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">DNS</span>
                                            </label>
                                        </span>
                                    </div>
                                    <ul id="role-3" class="role-group-sub">
                                        <li>
                                            <div class="role-sub">
                                                <span class="checkbox chk-inline">
                                                    <label>
                                                        <input type="checkbox" name="" checked="" value="TRUE">
                                                        <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                                    </label>
                                                </span>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <div class="role-main">
                                        <span class="role-click" onclick="openrole(this,'role-4')">@icon('solid/plus')</span>
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Leaks, Dumps and Breaches</span>
                                            </label>
                                        </span>
                                    </div>
                                    <ul id="role-4" class="role-group-sub">
                                        <li>
                                            <div class="role-sub">
                                                <span class="checkbox chk-inline">
                                                    <label>
                                                        <input type="checkbox" name="" checked="" value="TRUE">
                                                        <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                                    </label>
                                                </span>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <div class="role-main">
                                        <span class="role-click" onclick="openrole(this,'role-5')">@icon('solid/plus')</span>
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Passive DNS</span>
                                            </label>
                                        </span>
                                    </div>
                                    <ul id="role-5" class="role-group-sub">
                                        <li>
                                            <div class="role-sub">
                                                <span class="checkbox chk-inline">
                                                    <label>
                                                        <input type="checkbox" name="" checked="" value="TRUE">
                                                        <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                                    </label>
                                                </span>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <div class="role-main">
                                        <span class="role-click" onclick="openrole(this,'role-6')">@icon('solid/plus')</span>
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Real World</span>
                                            </label>
                                        </span>
                                    </div>
                                    <ul id="role-6" class="role-group-sub">
                                        <li>
                                            <div class="role-sub">
                                                <span class="checkbox chk-inline">
                                                    <label>
                                                        <input type="checkbox" name="" checked="" value="TRUE">
                                                        <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                                    </label>
                                                </span>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                <li>
                                    <div class="role-main">
                                        <span class="role-click" onclick="openrole(this,'role-7')">@icon('solid/plus')</span>
                                        <span class="checkbox chk-inline">
                                            <label>
                                                <input type="checkbox" name="" checked="" value="TRUE">
                                                <span class="label-text" data-rel="tooltip" title="">Social Media</span>
                                            </label>
                                        </span>
                                    </div>
                                    <ul id="role-7" class="role-group-sub">
                                        <li>
                                            <div class="role-sub">
                                                <span class="checkbox chk-inline">
                                                    <label>
                                                        <input type="checkbox" name="" checked="" value="TRUE">
                                                        <span class="label-text" data-rel="tooltip" title="">Role Sub</span>
                                                    </label>
                                                </span>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>


                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Scan Interval <span class="text-danger">*</span> </label>
                        <div class="col-lg-8">
                            <select name="" id="scan_interval" class="select2-option form-control" multiple>
                                <option value="1">Option</option>
                                <option value="1">Option</option>
                                <option value="1">Option</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Status </label>
                        <div class="col-lg-8">
                            <label class="switch">
                                <input type="hidden" value="FALSE" name="">
                                <input type="checkbox" name="" value="TRUE">
                                <span></span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
                    </button>
                    <button type="submit" class="btn btn-success btn-rounded">
                        <i class="fas fa-play"></i>
                        Run Scan And Save Now
                    </button>
                    <button type="submit" class="btn btn-info btn-rounded">
                        <i class="fas fa-paper-plane"></i>
                        Save
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>

</section>


@push('pagestyle')
@include('stacks.css.form')
@include('stacks.css.datatables')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.datatables')

<script>
    $(document).ready(function () {
        $('#scan_interval').select2();
    });
    
    $('ul.role-group-sub').hide();
    function openrole(onck,id){
        $('#'+id).slideToggle(150);
    }

    $(function() {
        $('#table-domain-template').DataTable({
            processing: true,
            order: [[ 0, "desc" ]],
        });
    });
</script>


@endpush

@endsection
