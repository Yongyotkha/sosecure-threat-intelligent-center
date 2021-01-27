@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="hbox stretch">
        <section class="vbox">
            <header class="header bg-white b-b b-light head-d-flex-nowrap" style="white-space: nowrap;">
                <div class="bc-head m-none" style="width:100%;">
                    Assets
                </div>

                <div class="pull-right" style="min-width: 270px;">
                    <select name="" id="select-site" class="select2-option form-control" style="min-width: 270px" onchange="changeSite(value)">
                        <option value="0">All Site</option>
                        @if ($SiteSettings)
                        @foreach ($SiteSettings as $site_settings)
                        <option value="{{$site_settings->code}}">{{$site_settings->name}}
                        </option>
                        @endforeach
                        @endif
                    </select>
                </div>

                <div class="button-control pull-right">
                    <div class="btn-group">
                        <a href="{{route("assets.assets_redirect_add_modal")}}" data-toggle="ajaxModal" class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle">@icon('solid/plus') Add</a>
                    </div>

                    <div class="btn-group d-none">
                        <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle" id="fillter-advance">Import Asset</button>
                    </div>

                    <div class="btn-group d-none">
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
                        <a id="advance-search" href="#hide-advance-search" class="btn btn-sm btn-{{ get_option('theme_color')  }} pull-right">
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
                                    <span class="number-card warning" id="count_assets">0</span>
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
                                    <span class="number-card info" id="count_windows">0</span>
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
                                    <span class="number-card green" id="count_linux">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <section class="panel panel-default" id="hide-advance-search" style="display: none;">
                    <header class="panel-heading font-bold panel-header-blue">
                        <div class="row">
                            <div class="col-md-12">
                                <div style="margin-top:5px;">
                                    <i class="fas fa-filter"></i> Filter
                                </div>
                            </div>
                    </header>
                    <div class="panel-body" style="padding: 0 !important">
                        <div class="container-fluid" style="padding: 2rem;">
                            <div class="row">
                                <div class="col-md-4">
                                    <h5 class="font-weight-bold">Group By</h5>
                                    <div id="groupby-btn" class="btn-group special mb-2">
                                        <button class="btn btn-grey active" onclick="selectGroupBy('domain')">
                                            <span> Domain </span>
                                        </button>
                                        <button class="btn btn-grey" onclick="selectGroupBy('ip')">
                                            <span> IP </span>
                                        </button>
                                        <button class="btn btn-grey" onclick="selectGroupBy('cpe')">
                                            <span> CPE </span>
                                        </button>
                                        <button class="btn btn-grey" onclick="selectGroupBy('os_type')">
                                            <span> OS Type </span>
                                        </button>
                                    </div>
                                    <div class="form-group">
                                        <select id="groupby-select" class="form-control">
                                            <option value="">- SELECT -</option>
                                        </select>
                                    </div>

                                </div>

                                <div class="col-md-4">
                                    <h5 class="font-weight-bold">Status</h5>
                                    <div id="groupby-status" class="btn-group special mb-2">
                                        <button class="btn btn-grey active" onclick="changeActive('')">
                                            <span> All </span>
                                        </button>
                                        <button class="btn btn-grey" onclick="changeActive('active')">
                                            <span> Active </span>
                                        </button>
                                        <button class="btn btn-grey" onclick="changeActive('inactive')">
                                            <span> Inactive </span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <select id="groupby-select2" style="display: none;" class="form-control">
                                            <option value="">Domain All</option>
                                            <option value="">DARK WEB</option> 
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-lg-12 text-right">
                                <button type="button" class="btn btn-info btn-responsive btn-fz-13" onclick="searchTB()">
                                    <i class="fas fa-search"></i>
                                    @langapp('apply')
                                </button>
                                <button type="button" id="btn_rss_data_reset" class="btn btn-default btn-responsive btn-fz-13" onclick="clearTB()" style="white-space: nowrap">
                                    <i class="fas fa-broom"></i>
                                    <span> Clear </span>
                                </button>
                                <button type="button" id="close_filter" class="btn btn-default btn-responsive btn-fz-13" style="white-space: nowrap">
                                    <i class="fas fa-times"></i>
                                    <span> Close </span>
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

                            <!-- id table อันเดิม table-assets-template ส่วนปัจจุบันเป็นแค่หน้าบ้านแสดงตัวอย่าง ถ้าเปลี่ยน id กลับแล้ว อย่าลืม ลบ script ด้านล่างออกด้วยนะครับ-->
                            <table class="table table-striped table-bordered" id="table-assets-template-test">
                                <thead>
                                    <tr>
                                        {{-- <th class="no-sort">
                                            <label>
                                                <input name="select_all" value="1" id="select-all" type="checkbox" />
                                                <span class="label-text"></span>
                                            </label>
                                        </th> --}}

                                        <!-- ปล. เมื่อมีการเพิ่ม ให้ rowspan ++ -->
                                        <th rowspan="2" class="align-middle">Site</th>
                                        <th rowspan="2" class="align-middle">Domain</th>
                                        <th rowspan="2" class="align-middle">IP</th>
                                        <th colspan="6" class="text-center">CPE</th>
                                        <th rowspan="2" class="align-middle">Status</th>
                                        <th rowspan="2" class="align-middle">Action</th>
                                    </tr>
                                    <tr>
                                        <th>Vendor</th>
                                        <th>Title</th>
                                        <th>Versions</th>
                                        <th>Edition</th>
                                        <th>Remark</th>
                                        <th>Delete CPE</th>
                                    </tr>
                                </thead>
                                <tbody>
                   
                                    <tr>
                                        <td rowspan="2">บริษัท เมจิก</td>
                                        <td rowspan="2">mtsc.co.th</td>
                                        <td rowspan="2">104.24.14.205</td>
                                        <td>Microsoft</td>
                                        <td>Windows_10</td>
                                        <td>r2</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></a>
                                        </td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                                            <a href="#" class="btn btn-xs btn-info"><i class="fas fa-plus"></i> Add</a>
                                        </td>
                                    </tr>

                                    <tr>
                                        <!-- ตัวอย่างเวลา rowspan แถวต่อไป ไม่ต้องเอาค่า 3 อันแรกมาแสดง ถ้าเพิ่มใน rowspan ทำแบบนี้ไปเรื่อยๆ แล้วให้ rowspan ด้านบน ++ -->    
                                        <td>Microsoft</td>
                                        <td>Windows_10</td>
                                        <td>r2</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></a>
                                        </td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                                            <a href="#" class="btn btn-xs btn-info"><i class="fas fa-plus"></i> Add</a>
                                        </td>
                                    </tr>

                                    <!-- ถ้าคนล่ะ Site ก็เพิ่มเหมือนเดิมแล้ว rowspan ++ เหมือนเดิมครับ -->
                                    <tr>
                                        <td rowspan="2">บริษัท เมจิก</td>
                                        <td rowspan="2">mtsc.co.th</td>
                                        <td rowspan="2">104.24.14.205</td>
                                        <td>Microsoft</td>
                                        <td>Windows_10</td>
                                        <td>r2</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></a>
                                        </td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                                            <a href="#" class="btn btn-xs btn-info"><i class="fas fa-plus"></i> Add</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Microsoft</td>
                                        <td>Windows_10</td>
                                        <td>r2</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></a>
                                        </td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>
                                            <a href="#" class="btn btn-xs btn-info"><i class="fas fa-edit"></i></a>
                                            <a href="#" class="btn btn-xs btn-info"><i class="fas fa-plus"></i> Add</a>
                                        </td>
                                    </tr>
                                </tbody>
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
@include('stacks.js.activebutton')
@include('stacks.js.advanced_search')

<script>

    active_btn('#groupby-btn .btn-grey');
    active_btn('#groupby-status .btn-grey');

    $('#table-assets-template-test').DataTable();
    
    $(document).ready(function () {
        selectGroupByFirst();

        $('#source').select2();
        $('#select-site').select2();
        $('#groupby-select').select2();
        $('.hide-fillter').hide();
        $('#fillter-advance').click(function(){
            $('.hide-fillter').toggle();
        });

        data_table();
        
    });

    var site_id = 0;
    $(function () {
        {{--$('#table-assets-template').DataTable({
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
        });--}}

        
    });

    var active = '';
    function changeActive(act){
        active = act;
    }

    var selectedGroup = 'domain';
    function selectGroupBy(columnGroup){
        if(selectedGroup!=columnGroup){
            selectedGroup = columnGroup;
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{!! route('assets.get_selected_filter') !!}',
                type: "get",
                data: ({
                    selectedGroup:selectedGroup,
                    sitecode:site_id,
                }),
                datatype: "html",
                beforeSend: function(){
                    loading('load');
                },
            }).done(function(data){
                let groupby_select = '';
                groupby_select += '<option selected value="">- SELECT -</option>';
                $.each(data.selected, function(key, val){
                    groupby_select += '<option value="'+val.val_select+'">'+val.val_select+'</option>';
                });
                $('#groupby-select').html(groupby_select);
                loading('stop_load');
            }).fail(function(jqXHR, ajaxOptions, thrownError){
                loading('stop_load');
                console.log("No response from server");
            });
        }
    }

    function clearTB(){
        $("#groupby-select").val('').trigger("change");
        $("#select-site").val(0).trigger("change");
        $("#groupby-status>button").removeClass("active");
        $("#groupby-status>button:first").addClass("active");
        active = '';
        searchTB();
    }

    function selectGroupByFirst(){
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{!! route('assets.get_selected_filter') !!}',
            type: "get",
            data: ({
                selectedGroup:selectedGroup,
                sitecode:site_id,
            }),
            datatype: "html",
            beforeSend: function(){
                loading('load');

            },
        }).done(function(data){
            let groupby_select = '';
            groupby_select += '<option selected value="">- SELECT -</option>';
            $.each(data.selected, function(key, val){
                groupby_select += '<option value="'+val.val_select+'">'+val.val_select+'</option>';
            });
            $('#groupby-select').html(groupby_select);
            loading('stop_load');
        }).fail(function(jqXHR, ajaxOptions, thrownError){
            loading('stop_load');
            console.log("No response from server");
        });
    }

    function searchTB(searchLinkAll='',colsearchLinkAll=''){
        
        let selectedValue = $('#groupby-select').children("option:selected").val();
        let columnSearch = selectedGroup;
        if(searchLinkAll!==''){
            selectedValue = colsearchLinkAll;
            selectedValue = searchLinkAll;
        }
        let selectedSiteName = '';
        if($('#select-site').children("option:selected").val()!=0){
            selectedSiteName = $('#select-site').children("option:selected").text();
        }
        
        let active_tb = active;
        
        
        if(columnSearch=='domain'){
            columnSearch = 1;
        }else if(columnSearch=='ip'){
            columnSearch = 2;
        }else if(columnSearch=='cpe'||columnSearch=='os_type'){
            columnSearch = 3;
        }else{
            columnSearch = '';
        }
        t.search( '' ).columns().search( '' ).draw();
        t.column(0).search(selectedSiteName).column(columnSearch).search(selectedValue).column(4).search(active_tb).draw();
       
       
        {{--ads.column(5).search(active_tb).draw();
        t.search( '' ).columns().search( '' ).draw();--}}
    }

    var t;
    function changeSite(val){
        searchTB();
        {{--site_id = val;
        selectGroupByFirst();--}}
    }

    function data_table(){
        t = $('#table-assets-template').DataTable({
            searching: true,
            ordering: true,
            pagination: true,
            pageLength: 25,
            processing: true,
            serverSide: false,
            destroy: true,
            "dom": '<B><"d-flex d-inline-flex justify-content-between"lf>rt<"bottom"ip><"clear">',
            order: [[ 0, "asc" ]],
            ajax: {
                type: "POST",
                url: '{!! route('assets.table_asset')!!}',
                data:function(d){
                }
            },
            initComplete : function( settings, json){
                $('#count_assets').html(json.countAssets+"");
                $('#count_windows').html(json.countWindows+"");
                $('#count_linux').html(json.countLinux+"");
            },
            columns: [
                {{--{
                    width: '1%',
                    data: 'chk',
                    name: 'chk',
                },--}}
                {
                    width: '25%',
                    data: 'site_name',
                    name: 'site_name',
                },
                {
                    width: '20%',
                    data: 'domain',
                    name: 'domain',
                },
                {
                    width: '20%',
                    data: 'ip',
                    name: 'ip',
                },
                {
                    width: '29%',
                    data: 'CPE',
                    name: 'CPE',
                }, 
                {
                    width: '3%',
                    data: 'status',
                    name: 'status',
                    className: 'text-center'
                },  
                {
                    width: '3%',
                    data: 'action',
                    name: 'action',
                    className: 'text-center no-wrap'
                },
            ],
            columnDefs: [
                {{--{
                    
                    targets: 0,
                    searchable: false,
                    orderable: false,
                    width: '10px',
                    render: function (data, type, row, meta) {
                        return '<label><input type="checkbox" name="checked" class="select-chk asset_id" value="' + row.code + '"><span class="label-text"></span></label>';
                    }
                   
                },--}}
                {
                    targets: 5,
                    render: function (data, type, row, meta) {
                        return row.CPE+"<br>"+row.cpe;
                        
                    }
                   
                },
                {
                    targets: 4,
                    render: function (data, type, row, meta) {
                        if(row.status==1){
                            return '<span class="badge badge-success">Active</span>';
                        }else{
                            return '<span class="badge badge-danger">Inactive</span>';
                        }
                        
                    }
                   
                },
            ]

        });

        let check = {!!json_encode($Search_Link_All)!!};
        console.log(check);
        if(check===""){
            searchTB();
        }else{
            searchTB(check,'domain');
        }
    }
</script>
@endpush

@endsection
