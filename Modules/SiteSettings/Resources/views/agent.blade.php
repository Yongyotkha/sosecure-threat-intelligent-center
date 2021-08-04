@extends('layouts.app')

@section('content')

<section id="content" class="bg">
    <section class="hbox stretch">

        <aside id="hide-settings" class="aside aside-md b-r">
            <section class="vbox">
                @include('partial.header-select-site')
                <section class="scrollable">
                    <section id="setting-nav" class="hidden-xs">
                        @include('partial.menu_site')
                    </section>
                </section>
            </section>
        </aside>


            <section class="vbox">
                <header class="header panel-heading bg-white b-b b-light">
                    <a class="show-setting btn btn-icon btn-default btn-sm m-r-xs" style="margin-top: 0;display:none;">@icon('solid/bars')</a>
                    <div class="bc-head">Site Setting &gt; Agent </div>
             
                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color') }} pull-right">Download</a>
                </header>

                <section class="scrollable wrapper">
                    <div class="row m-b-10">
                        <div class="col-md-6">
                            <div class="card-box-actor bg-2">
                                <div class="card-actor-name">LIMIT AGENT</div>
                                <div class="score-actor"> 3 </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-box-actor bg-2">
                                <div class="card-actor-name">AGENT ACTIVE</div>
                                <div class="score-actor"> 2/3 </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <section class="panel panel-default">
                            <header class="panel-heading font-bold panel-header-blue">@icon('solid/user') Table Agent</header>
                            <div class="panel-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="table-agent-template" style="width: 100%">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <label><input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                                                        <span class="label-text"></span>
                                                    </label>
                                                </th>
                                                <th>Device Name</th>
                                              
                                                <th>OS Type</th>
                                                <th>OS Description</th>
                                                <th>System Info</th>
                                                <th>IP</th>
                                                <th>Last Online</th>
                                                <th>Status</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <label><input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                                                        <span class="label-text"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <b>DESKTOP-KJAK36N</b>
                                                </td>
                                                <td>
                                                    <div class="device-name-txt">
                                                        <div class="icon-devices">
                                                            <i class="fab fa-apple"></i>
                                                        </div>
                                                        <b class="text-info">Linux</b>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-trucate-ovf">
                                                        OS Name: Microsoft Windows 10 Pro 
                                                        OS Version:  10.0.19042 N/A Build 19042 
                                                        OS Manufacturer:  Microsoft Corporation 
                                                        OS Configuration: Standalone Workstation 
                                                        OS Build Type:  Multiprocessor Free 
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-trucate-ovf">
                                                        System Boot Time: 8/2/2021, 2:43:47 AM 
                                                        System Manufacturer: LENOVO
                                                        System Model:  20N8S0KB00
                                                        System Type:   x64-based PC
                                                        Processor(s):  1 Processor(s) Installed.
                                                        [01]: Intel64 Family 6 Model 142 Stepping 12 GenuineIntel
                                                    </div>
                                                </td>
                                                <td>
                                                    <b>192.168.1.2  </b>
                                                </td>
                                                <td>
                                                    <b>04-08-2021 09:31:00 AM</b>
                                                </td>
                                                <td>
                                                    <label class="switch">
                                                        <input type="checkbox" id="" onchange="" checked="" name="active" value="1">
                                                        <span></span>
                                                    </label>
                                                </td>
                                                <td class="text-center">
                                                    <a href="#" class="btn btn-danger btn-xs">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
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

    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen, open" data-target="#nav,html"></a>

    <!-- Modal New User -->
    <div class="modal in fixed-left" id="create-new-user" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        New User
                    </h4>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Username (e-mail) <span class="text-danger">*</span> </label>
                        <div class="col-lg-8">
                            <input type="text" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Name <span class="text-danger">*</span> </label>
                        <div class="col-lg-8">
                            <input type="text" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Set Password <span class="text-danger">*</span> </label>
                        <div class="col-lg-8">
                            <input type="text" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Re-enter Password <span class="text-danger">*</span> </label>
                        <div class="col-lg-8">
                            <input type="text" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-4 control-label">Role <span class="text-danger">*</span> </label>
                        <div class="col-lg-8">
                            <select name="" id="role" class="select2-option form-control">
                                <option value="1">Admin</option>
                                <option value="1">User</option>
                                <option value="1">Customer</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 control-label"> Login Expire Date <span class="text-danger">*</span> </label>
                        <div class="col-lg-8">
                            <input type="text" class="form-control">
                            <span class="input-group-addon">Day</span>
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
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
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


    <div class="modal in fixed-left" id="support-password" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header bg-blue">
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title text-white">
                        <i class="fas fa-compress fullscreen-btn text-white" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i>
                        Support Password
                    </h4>
                </div>
                <form action="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <button type="submit" class="btn btn-info"> Support Password </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th class="text-center">Password</th>
                                    <th class="text-center">Date Expried</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span>Upvel Admin</span>
                                    </td>
                                    <td class="text-center">
                                        <button type="submit" class="btn btn-success btn-rounded"> Copy Password </button>
                                    </td>
                                    <td class="text-center">
                                        <span>16-11-2020 11:18:39</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-rounded" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                        Close
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

    <div class="modal" id="delete_user" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"
    style="left: unset">
    <div class="modal-dialog modal-dialog-aside" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">@langapp('delete')</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <p class="text-danger">@langapp('delete_warning') </p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" class="btn btn-default btn-rounded" data-dismiss="modal"><i
                        class="fas fa-times text-muted"></i> Close</a>
                <button type="button" class="btn btn-info submit btn-rounded delete_webdefacement_submit"
                    onclick="delete_user_save()"><i class="fas fa-paper-plane"></i> OK</button>
            </div>
        </div>
    </div>
</div>

</section>


@push('pagestyle')
@include('stacks.css.form')
@include('stacks.css.datatables')
@include('stacks.css.multitext')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('stacks.js.datatables')
@include('stacks.js.fullscreen')
@include('partial.ajaxify')
@include('stacks.js.menusub')
@include('stacks.js.site_hidesettings')
@include('stacks.js.fullscreen')
@include('stacks.js.multitext')

<script>
    multi_readmore_text();
    $('#table-agent-template').DataTable();
</script>


@endpush

@endsection
