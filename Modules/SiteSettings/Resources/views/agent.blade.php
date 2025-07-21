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
                    
                    <input type="hidden" name="hd_site_id" id="hd_site_id" value="{{$siteSettings->id}}">


                    <a href="#" class="btn btn-sm btn-{{ get_option('theme_color') }} pull-right">Download</a>
                </header>

                <section class="scrollable wrapper">
                    <div class="row m-b-10">
                        <div class="col-md-6">
                            <div class="card-box-actor bg-2">
                                <div class="card-actor-name">LIMIT AGENT</div>
                                <div class="score-actor"> {{$siteSettings->agent_count}} </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card-box-actor bg-2">
                                <div class="card-actor-name">AGENT ACTIVE</div>
                                <div class="score-actor"> {{$count_agent}}/{{$siteSettings->agent_count}} </div>
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
                                            {{-- <tr>
                                                <td>
                                                    <label>
                                                        <input name="select_all" value="1" id="select-all" type="checkbox" class="select-chk">
                                                        <span class="label-text"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <b>DESKTOP-</b>
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
                                            </tr> --}}
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

    $(document).ready(function(){
        table_agent();
    });

    function table_agent()
    {
        var hd_site_id = $('#hd_site_id').val();
        console.log(hd_site_id);
        $('#table-agent-template').DataTable({
            cache: false,
            processData: false,
            contentType: false,
            processing: true,
            serverSide: true,
            destroy: true,
            ajax: 
            {
                url: "{{route('agent.tb_agent')}}",
                type: "POST",
                data:function(d){
                    d.hd_site_id = hd_site_id;
                    return d ;
                }
            },
            columns: [
                {
                    data: 'chk',
                },
                {
                    data: 'device_name',
                },
                {
                    data: 'os_type',
                },
                {
                    data: 'os_description',
                },
                {
                    data: 'system_info',
                },
                {
                    data: 'ip_private',
                },
                {
                    data: 'last_online',
                },
                {
                    data: 'chk_status',
                },
                {
                    data: 'action',
                },
            ]
        });
    }
    
</script>


@endpush

@endsection
